# 🔄 CI/CD Workflow

Esta guía explica cómo configurar **Integración Continua** y **Despliegue Continuo** para el PHP MVC Template usando GitHub Actions.

## 🎯 ¿Qué es CI/CD?

- **CI (Continuous Integration)**: Automatiza pruebas y validaciones en cada push
- **CD (Continuous Deployment)**: Automatiza el despliegue a servidores de staging/producción

## 📁 Estructura de Workflows

Los workflows se almacenan en `.github/workflows/`:

```
.github/
└── workflows/
    ├── ci.yml              # Integración continua
    ├── deploy-staging.yml  # Despliegue a staging
    └── deploy-production.yml # Despliegue a producción
```

## 🧪 Workflow de Integración Continua

### Crear `.github/workflows/ci.yml`:

```yaml
name: CI - Tests and Quality

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  tests:
    name: PHP Tests
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php-version: [8.0, 8.1, 8.2]
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_db
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: pdo, pdo_mysql, mbstring, xml, json, tokenizer, openssl
          coverage: xdebug

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: vendor
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Copy environment file
        run: cp .env.example .env

      - name: Configure environment
        run: |
          sed -i 's/DB_HOST="localhost"/DB_HOST="127.0.0.1"/' .env
          sed -i 's/DB_NAME="mi_base_datos"/DB_NAME="test_db"/' .env
          sed -i 's/DB_USERNAME="usuario"/DB_USERNAME="root"/' .env
          sed -i 's/DB_PASSWORD="contraseña_segura"/DB_PASSWORD="root"/' .env

      - name: Setup database
        run: |
          mysql -h 127.0.0.1 -u root -proot test_db < database.sql

      - name: Run PHP syntax check
        run: find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;

      - name: Run Composer validation
        run: composer validate --strict

      - name: Check code style (PHP CS Fixer)
        run: |
          composer require --dev friendsofphp/php-cs-fixer
          ./vendor/bin/php-cs-fixer fix --dry-run --diff

      - name: Run PHPStan static analysis
        run: |
          composer require --dev phpstan/phpstan
          ./vendor/bin/phpstan analyse App --level=5

      - name: Run custom tests
        run: php tests/run_tests.php

  security:
    name: Security Scan
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Security audit
        run: composer audit

      - name: Check for known vulnerabilities
        run: |
          composer require --dev roave/security-advisories:dev-latest || true
```

## 🚀 Workflow de Despliegue a Staging

### Crear `.github/workflows/deploy-staging.yml`:

```yaml
name: Deploy to Staging

on:
  push:
    branches: [ develop ]
  workflow_dispatch:

jobs:
  deploy:
    name: Deploy to Staging Server
    runs-on: ubuntu-latest
    environment: staging
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Create deployment package
        run: |
          tar -czf deployment.tar.gz \
            --exclude='.git' \
            --exclude='.github' \
            --exclude='tests' \
            --exclude='.env' \
            --exclude='*.md' \
            .

      - name: Deploy to staging server
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          port: ${{ secrets.STAGING_PORT }}
          script: |
            # Crear backup
            cd ${{ secrets.STAGING_PATH }}
            if [ -d "current" ]; then
              cp -r current backup-$(date +%Y%m%d-%H%M%S)
            fi
            
            # Limpiar directorio temporal
            rm -rf temp-deploy
            mkdir -p temp-deploy

      - name: Upload files
        uses: appleboy/scp-action@v0.1.4
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          port: ${{ secrets.STAGING_PORT }}
          source: "deployment.tar.gz"
          target: "${{ secrets.STAGING_PATH }}/temp-deploy/"

      - name: Extract and configure
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          port: ${{ secrets.STAGING_PORT }}
          script: |
            cd ${{ secrets.STAGING_PATH }}/temp-deploy
            tar -xzf deployment.tar.gz
            rm deployment.tar.gz
            
            # Copiar configuraciones de staging
            cp ${{ secrets.STAGING_PATH }}/.env .env
            
            # Establecer permisos
            chmod 755 Public/
            chmod 777 App/Uploads/
            chmod 777 App/Views/cache/
            
            # Mover a producción
            cd ${{ secrets.STAGING_PATH }}
            rm -rf current
            mv temp-deploy current
            
            # Reiniciar servicios si es necesario
            sudo systemctl reload nginx
            sudo systemctl restart php8.2-fpm

      - name: Health check
        run: |
          sleep 10
          curl -f ${{ secrets.STAGING_URL }} || exit 1

      - name: Notify deployment
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: "Staging deployment ${{ job.status }}"
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
        if: always()
```

## 🏭 Workflow de Despliegue a Producción

### Crear `.github/workflows/deploy-production.yml`:

```yaml
name: Deploy to Production

on:
  release:
    types: [published]
  workflow_dispatch:
    inputs:
      version:
        description: 'Version to deploy'
        required: true
        default: 'latest'

jobs:
  pre-deployment:
    name: Pre-deployment checks
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Run security scan
        run: |
          # Escanear por secretos hardcodeados
          grep -r "password\|secret\|key" App/ --include="*.php" || true

      - name: Validate configuration
        run: |
          # Verificar que archivos críticos existen
          test -f .env.example
          test -f database.sql
          test -f composer.json

  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: pre-deployment
    environment: production
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Create deployment package
        run: |
          # Crear paquete optimizado para producción
          tar -czf production.tar.gz \
            --exclude='.git*' \
            --exclude='tests' \
            --exclude='wiki' \
            --exclude='.env*' \
            --exclude='*.md' \
            --exclude='database.sql' \
            .

      - name: Backup production
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd ${{ secrets.PROD_PATH }}
            
            # Crear backup completo
            backup_name="backup-$(date +%Y%m%d-%H%M%S)"
            mkdir -p backups/$backup_name
            
            if [ -d "current" ]; then
              cp -r current/* backups/$backup_name/
              cp .env backups/$backup_name/
            fi
            
            # Mantener solo últimos 5 backups
            cd backups && ls -t | tail -n +6 | xargs rm -rf

      - name: Deploy to production
        uses: appleboy/scp-action@v0.1.4
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          source: "production.tar.gz"
          target: "${{ secrets.PROD_PATH }}/temp/"

      - name: Activate deployment
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd ${{ secrets.PROD_PATH }}
            
            # Extraer nueva versión
            cd temp
            tar -xzf production.tar.gz
            rm production.tar.gz
            
            # Configurar producción
            cp ${{ secrets.PROD_PATH }}/.env .env
            
            # Verificar configuración crítica
            if ! grep -q "APP_DEBUG=false" .env; then
              echo "ERROR: APP_DEBUG debe ser false en producción"
              exit 1
            fi
            
            # Establecer permisos de producción
            chmod 755 Public/
            chmod 777 App/Uploads/
            chmod 777 App/Views/cache/
            chown -R www-data:www-data .
            
            # Activar nueva versión (atomic switch)
            cd ${{ secrets.PROD_PATH }}
            rm -rf current
            mv temp current
            
            # Optimizar para producción
            cd current
            composer dump-autoload --optimize --no-dev
            
            # Reiniciar servicios
            sudo systemctl reload nginx
            sudo systemctl restart php8.2-fpm

      - name: Health check production
        run: |
          sleep 15
          
          # Verificar que la aplicación responde
          response=$(curl -s -o /dev/null -w "%{http_code}" ${{ secrets.PROD_URL }})
          if [ $response -ne 200 ]; then
            echo "Health check failed: HTTP $response"
            exit 1
          fi
          
          # Verificar endpoint de salud si existe
          curl -f ${{ secrets.PROD_URL }}/health || echo "No health endpoint"

      - name: Rollback on failure
        if: failure()
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd ${{ secrets.PROD_PATH }}
            
            # Buscar último backup
            latest_backup=$(ls -t backups/ | head -n 1)
            
            if [ -n "$latest_backup" ]; then
              echo "Rolling back to $latest_backup"
              rm -rf current
              cp -r backups/$latest_backup current
              sudo systemctl reload nginx
              sudo systemctl restart php8.2-fpm
            fi

      - name: Notify deployment status
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: "Production deployment ${{ job.status }}"
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
        if: always()
```

## 🧪 Configurar Tests Básicos

### Crear `tests/run_tests.php`:

```php
<?php
/**
 * Simple test runner for the PHP MVC Template
 */

require_once __DIR__ . '/../vendor/autoload.php';

class TestRunner
{
    private $passed = 0;
    private $failed = 0;
    
    public function test($name, $callback)
    {
        echo "Testing: {$name}... ";
        
        try {
            $result = $callback();
            if ($result) {
                echo "✅ PASSED\n";
                $this->passed++;
            } else {
                echo "❌ FAILED\n";
                $this->failed++;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            $this->failed++;
        }
    }
    
    public function assert($condition, $message = "Assertion failed")
    {
        if (!$condition) {
            throw new Exception($message);
        }
        return true;
    }
    
    public function results()
    {
        echo "\n--- Test Results ---\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total: " . ($this->passed + $this->failed) . "\n";
        
        return $this->failed === 0;
    }
}

// Configurar entorno de pruebas
$_ENV['APP_DEBUG'] = 'true';
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_NAME'] = 'test_db';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'root';

$test = new TestRunner();

// Test 1: Verificar carga de archivos principales
$test->test("Core files exist", function() use ($test) {
    $test->assert(file_exists(__DIR__ . '/../App/Lib/Core.php'), "Core.php not found");
    $test->assert(file_exists(__DIR__ . '/../App/Config/Config.php'), "Config.php not found");
    $test->assert(file_exists(__DIR__ . '/../Public/index.php'), "index.php not found");
    return true;
});

// Test 2: Verificar modelos
$test->test("Models load correctly", function() use ($test) {
    $test->assert(class_exists('App\Models\Users'), "Users model not found");
    $test->assert(class_exists('App\Models\Offices'), "Offices model not found");
    $test->assert(class_exists('App\Models\OfficeUser'), "OfficeUser model not found");
    return true;
});

// Test 3: Verificar conexión a base de datos
$test->test("Database connection", function() use ($test) {
    try {
        $user = new \App\Models\Users();
        $columns = $user->getColumns();
        $test->assert(is_array($columns), "getColumns should return array");
        $test->assert(in_array('id', $columns), "id column should exist");
        return true;
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
});

// Test 4: Verificar helpers
$test->test("Helper functions", function() use ($test) {
    $test->assert(function_exists('config'), "config() function not found");
    $test->assert(function_exists('redirect'), "redirect() function not found");
    $test->assert(function_exists('RandomString'), "RandomString() function not found");
    
    // Test RandomString
    $random1 = RandomString(10);
    $random2 = RandomString(10);
    $test->assert(strlen($random1) === 10, "RandomString should return 10 chars");
    $test->assert($random1 !== $random2, "RandomString should return different values");
    
    return true;
});

// Test 5: Verificar configuración de producción
$test->test("Production configuration", function() use ($test) {
    if (file_exists(__DIR__ . '/../.env')) {
        $env = file_get_contents(__DIR__ . '/../.env');
        
        // En CI, debería tener configuración de prueba
        if (getenv('CI')) {
            $test->assert(strpos($env, 'APP_DEBUG=true') !== false, "Should have debug enabled in CI");
        }
    }
    return true;
});

// Ejecutar tests y mostrar resultados
$success = $test->results();

// Exit code para CI/CD
exit($success ? 0 : 1);
```

## ⚙️ Configurar Secrets en GitHub

### Secrets necesarios:

#### Para Staging:
- `STAGING_HOST`: IP o dominio del servidor
- `STAGING_USER`: Usuario SSH
- `STAGING_SSH_KEY`: Clave privada SSH
- `STAGING_PORT`: Puerto SSH (22 por defecto)
- `STAGING_PATH`: Ruta en el servidor
- `STAGING_URL`: URL para health check

#### Para Producción:
- `PROD_HOST`: IP o dominio del servidor
- `PROD_USER`: Usuario SSH
- `PROD_SSH_KEY`: Clave privada SSH
- `PROD_PATH`: Ruta en el servidor
- `PROD_URL`: URL para health check

#### Opcionales:
- `SLACK_WEBHOOK`: Para notificaciones

### Configurar en GitHub:
```
Repositorio → Settings → Secrets and variables → Actions → New repository secret
```

## 🔧 Configuración del Servidor

### Script de preparación del servidor (`scripts/setup-server.sh`):

```bash
#!/bin/bash
# Setup script for production server

echo "Setting up server for PHP MVC Template..."

# Crear estructura de directorios
sudo mkdir -p /var/www/mi-app/{current,backups,temp}
sudo chown -R www-data:www-data /var/www/mi-app
sudo chmod 755 /var/www/mi-app

# Configurar permisos sudo para deployment
echo "www-data ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx, /bin/systemctl restart php8.2-fpm" | sudo tee /etc/sudoers.d/deployment

# Crear archivo .env de producción
sudo tee /var/www/mi-app/.env << EOF
APP_URL="https://tu-dominio.com"
APP_NAME="Mi Aplicación"
APP_DEBUG=false
MANT=false

DB_DRIVER=mysql
DB_HOST="localhost"
DB_NAME="produccion_db"
DB_USERNAME="prod_user"
DB_PASSWORD="contraseña_muy_segura"
DB_CHARSET="utf-8"
DB_COLLATION=""

# Más configuraciones...
EOF

sudo chmod 600 /var/www/mi-app/.env
sudo chown www-data:www-data /var/www/mi-app/.env

echo "Server setup completed!"
```

## 📊 Monitoreo y Alertas

### Health Check Endpoint (`App/Controllers/Health.php`):

```php
<?php

class Health extends Controller
{
    public function index()
    {
        header('Content-Type: application/json');
        
        $status = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'checks' => []
        ];
        
        // Check database
        try {
            \App\Models\Users::count();
            $status['checks']['database'] = 'ok';
        } catch (Exception $e) {
            $status['checks']['database'] = 'error';
            $status['status'] = 'error';
        }
        
        // Check disk space
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        $status['checks']['disk'] = $usagePercent < 90 ? 'ok' : 'warning';
        $status['disk_usage'] = round($usagePercent, 2) . '%';
        
        // Check uploads directory
        $status['checks']['uploads'] = is_writable(RUTA_UPLOAD) ? 'ok' : 'error';
        
        http_response_code($status['status'] === 'ok' ? 200 : 503);
        echo json_encode($status, JSON_PRETTY_PRINT);
    }
}
```

## 🚀 Workflow de Release

### Crear `.github/workflows/release.yml`:

```yaml
name: Create Release

on:
  push:
    tags:
      - 'v*'

jobs:
  release:
    name: Create Release
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Create Release
        uses: actions/create-release@v1
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          tag_name: ${{ github.ref }}
          release_name: Release ${{ github.ref }}
          body: |
            ## Changes in this Release
            - Bug fixes and improvements
            - Updated dependencies
            
            ## Installation
            ```bash
            git clone -b ${{ github.ref }} https://github.com/tu-usuario/tu-repo.git
            cd tu-repo
            composer install
            cp .env.example .env
            # Configure .env file
            ```
          draft: false
          prerelease: false
```

## 📋 Checklist de Deployment

### Pre-deployment:
- [ ] ✅ Tests pasan en CI
- [ ] 🔒 Secrets configurados
- [ ] 🌍 Variables de entorno actualizadas
- [ ] 📊 Monitoreo configurado
- [ ] 💾 Backup programado

### Post-deployment:
- [ ] 🩺 Health check exitoso
- [ ] 📈 Métricas normales
- [ ] 🔍 Logs sin errores
- [ ] 👥 Notificación al equipo

---

**¡Con estos workflows tendrás un proceso de CI/CD profesional! 🚀**

El sistema automatiza pruebas, despliegues y rollbacks, asegurando la calidad y disponibilidad de tu aplicación.
