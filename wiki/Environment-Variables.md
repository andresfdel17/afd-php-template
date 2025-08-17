# 🌍 Variables de Entorno

Esta página explica cómo configurar correctamente las variables de entorno para el PHP MVC Template.

## 📄 Archivo .env

El framework utiliza el archivo `.env` para gestionar todas las configuraciones de la aplicación. Este archivo **NO debe incluirse en el control de versiones** por seguridad.

### 🚀 Configuración Inicial

1. **Copiar archivo de ejemplo**:
   ```bash
   cp .env.example .env
   ```

2. **Editar configuraciones**:
   ```bash
   nano .env
   ```

## 🔧 Variables Principales

### 📱 Configuración de la Aplicación

```env
# Información básica
APP_URL="http://localhost"     # URL completa de tu aplicación
APP_NAME="Mi Aplicación"       # Nombre de tu aplicación
PHP_SCREEN=false              # Mostrar phpinfo() (solo desarrollo)
MANT=false                    # Modo mantenimiento (true = activa)

# Configuración de desarrollo
APP_DEBUG=true                # Mostrar errores (false en producción)
APP_CONTROLLER="Login"        # Controlador de inicio por defecto
```

**Explicación**:
- `APP_NAME`: Nombre de tu aplicación (aparece en títulos, emails, etc.)
- `APP_URL`: URL completa de tu aplicación (importante para redirecciones)
- `APP_DEBUG`: Muestra errores detallados (solo para desarrollo)
- `APP_CONTROLLER`: Controlador que se carga por defecto
- `MANT`: Si es `true`, muestra página de mantenimiento

### 🗄️ Base de Datos

```env
DB_DRIVER=mysql              # mysql, pgsql, sqlite, sqlsrv
DB_HOST="localhost"          # Servidor de base de datos
DB_NAME="mi_base_datos"      # Nombre de la base de datos
DB_USERNAME="usuario"        # Usuario de la base de datos
DB_PASSWORD="contraseña"     # Contraseña del usuario
DB_CHARSET="utf-8"          # Codificación de caracteres
DB_COLLATION=""             # Collation (opcional)
```

**Drivers soportados**:
- `mysql`: MySQL/MariaDB
- `pgsql`: PostgreSQL
- `sqlite`: SQLite
- `sqlsrv`: SQL Server

### 📧 Configuración de Correo

```env
# Servidor SMTP
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587                      # 587 para TLS, 465 para SSL
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña_app    # Contraseña de aplicación para Gmail

# Información del remitente
MAIL_FROM_NAME="Mi Aplicación"
MAIL_SYSTEM=sistema@miapp.com      # Email del sistema
EMAIL=contacto@miapp.com           # Email de contacto
```

**Configuraciones comunes**:

#### Gmail:
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña_de_aplicacion
```

#### Outlook/Hotmail:
```env
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@outlook.com
MAIL_PASSWORD=tu_contraseña
```

#### Mailtrap (desarrollo):
```env
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
```

### 🔐 Sesiones y Seguridad

```env
# Tiempo de sesión en segundos
SESSION_TIME=7200              # 2 horas

# Claves de seguridad
JWT_SECRET=tu_clave_jwt_muy_segura_de_32_caracteres
APP_KEY=base64:tu_clave_de_encriptacion
```

**Generar claves seguras**:
```bash
# Generar clave JWT (32 caracteres)
openssl rand -base64 32

# Generar APP_KEY
openssl rand -base64 32 | base64
```

## 🌐 Configuraciones por Entorno

### 🔧 Desarrollo Local

```env
APP_DEBUG=true
APP_URL="http://mi-proyecto.local"
DB_HOST=localhost
DB_NAME=mi_proyecto_dev
MAIL_HOST=smtp.mailtrap.io  # Para testing
DEV_MODE=true
LOG_LEVEL=debug
```

### 🚀 Producción

```env
APP_DEBUG=false
APP_URL="https://mi-dominio.com"
DB_HOST=tu_servidor_db
DB_NAME=mi_proyecto_prod
MAIL_HOST=tu_smtp_real
FORCE_HTTPS=true
GZIP_COMPRESSION=true
VIEW_CACHE=true
```

### 🧪 Testing/Staging

```env
APP_DEBUG=true
APP_URL="https://staging.mi-dominio.com"
DB_NAME=mi_proyecto_staging
MAIL_HOST=smtp.mailtrap.io
LOG_LEVEL=info
```

## 📁 Variables Adicionales Opcionales

### 🔒 Seguridad Avanzada

```env
# Forzar HTTPS
FORCE_HTTPS=true

# Headers de seguridad
SECURITY_HEADERS=true

# Rate limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_PER_MINUTE=60
```

### 📂 Gestión de Archivos

```env
# Límites de subida
MAX_UPLOAD_SIZE=64              # MB
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx

# Almacenamiento
STORAGE_DRIVER=local            # local, s3, ftp
```

### 🔍 APIs Externas

```env
# Google Services
GOOGLE_API_KEY=tu_clave_google
GOOGLE_CLIENT_ID=tu_client_id
GOOGLE_CLIENT_SECRET=tu_client_secret

# Redes sociales
FACEBOOK_APP_ID=tu_app_id
FACEBOOK_APP_SECRET=tu_app_secret

# Pagos
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
PAYPAL_CLIENT_ID=tu_client_id
```

### 📊 Monitoreo y Analytics

```env
# Error tracking
SENTRY_DSN=https://tu-dsn@sentry.io/proyecto

# Analytics
GOOGLE_ANALYTICS_ID=UA-xxxxxxxxx-x

# Logs
LOG_CHANNEL=daily              # single, daily, slack
LOG_LEVEL=info                 # debug, info, warning, error
```

## 🛠️ Uso en el Código

### Acceder a variables:

```php
// Función helper config()
$config = config();
echo $config->APP_NAME;
echo $config->DB_HOST;
echo $config->MAIL_HOST;

// Acceso directo a $_ENV
echo $_ENV['APP_NAME'];
echo $_ENV['DB_HOST'];
```

### Valores por defecto:

```php
// Con valor por defecto si no existe
$debug = $_ENV['APP_DEBUG'] ?? false;
$timeout = $_ENV['SESSION_TIME'] ?? 3600;
```

### Validaciones:

```php
// Verificar que variables críticas existen
if (!isset($_ENV['DB_HOST'])) {
    throw new Exception('DB_HOST no está configurado');
}

if (empty($_ENV['JWT_SECRET'])) {
    throw new Exception('JWT_SECRET es requerido');
}
```

## ⚠️ Mejores Prácticas de Seguridad

### ✅ Lo que SÍ debes hacer:

1. **Nunca subir .env a Git**:
   ```bash
   # Agregar al .gitignore
   echo ".env" >> .gitignore
   ```

2. **Usar contraseñas fuertes**:
   - Minimum 32 caracteres para JWT_SECRET
   - Contraseñas únicas para cada entorno
   - Contraseñas de aplicación para Gmail

3. **Separar configuraciones por entorno**:
   - `.env.local` para desarrollo
   - `.env.staging` para testing
   - `.env.production` para producción

4. **Respaldar configuraciones de forma segura**:
   - Usar gestores de secretos (AWS Secrets Manager, etc.)
   - Documentar variables pero no sus valores

### ❌ Lo que NO debes hacer:

1. **No hardcodear valores sensibles en el código**
2. **No usar valores por defecto en producción**
3. **No compartir archivos .env por email/chat**
4. **No usar la misma configuración en todos los entornos**

## 🔧 Configuración en Hosting Compartido

### cPanel/Hosting típico:

1. **Crear .env via File Manager**
2. **Configurar variables específicas del hosting**:
   ```env
   DB_HOST=localhost          # Generalmente localhost
   DB_NAME=cpanel_user_dbname # Formato usuario_nombredb
   DB_USERNAME=cpanel_user_dbuser
   ```

3. **Ajustar rutas si es necesario**:
   ```env
   APP_URL="https://tu-dominio.com"
   ```

## 🚨 Solución de Problemas

### Error: "Unable to load .env file"
```bash
# Verificar que existe
ls -la .env

# Verificar permisos
chmod 644 .env

# Verificar sintaxis (no espacios alrededor del =)
# ✅ Correcto: APP_NAME="Mi App"
# ❌ Incorrecto: APP_NAME = "Mi App"
```

### Error: "Database connection error"
```bash
# Verificar credenciales
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_NAME

# Verificar que la base de datos existe
mysql -u root -p -e "SHOW DATABASES;"
```

### Error: "Mail sending failed"
```bash
# Para Gmail, usar contraseñas de aplicación
# Verificar configuración SMTP
telnet smtp.gmail.com 587
```

---

**¡Tu aplicación está configurada correctamente! 🎯**

Siguiente paso: [[Estructura-del-Proyecto|Project-Structure]]
