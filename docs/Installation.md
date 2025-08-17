# 📦 Instalación

Esta página te guiará a través del proceso de instalación del PHP MVC Template.

## 📋 Requisitos del Sistema

### Requisitos Obligatorios
- **PHP 8.0 o superior**
- **Composer** (para gestión de dependencias)
- **Servidor web** (Apache o Nginx)

### Requisitos Opcionales
- **MySQL/PostgreSQL** (para base de datos)
- **Node.js** (para compilación de assets frontend)
- **Git** (para control de versiones)

### Extensiones de PHP Requeridas
```bash
# Verificar extensiones instaladas
php -m | grep -E "(pdo|json|mbstring|openssl|tokenizer|xml|ctype|fileinfo)"
```

Extensiones necesarias:
- `pdo_mysql` o `pdo_pgsql` (para base de datos)
- `json`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `fileinfo`

## 🚀 Proceso de Instalación

### Paso 1: Descargar el Template

#### Opción A: Clonar desde Git
```bash
git clone https://github.com/[usuario]/[repositorio].git mi-proyecto
cd mi-proyecto
```

#### Opción B: Descargar ZIP
1. Descarga el archivo ZIP desde GitHub
2. Extrae en tu directorio de proyectos
3. Renombra la carpeta a `mi-proyecto`

### Paso 2: Instalar Dependencias

```bash
# Navegar al directorio del proyecto
cd mi-proyecto

# Instalar dependencias de PHP con Composer
composer install

# Si es para producción, usar:
composer install --optimize-autoloader --no-dev
```

### Paso 3: Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar configuraciones con tus datos específicos
nano .env

# Configurar al menos estas variables obligatorias:
# - APP_URL (tu dominio)
# - APP_NAME (nombre de tu aplicación)
# - DB_NAME, DB_USERNAME, DB_PASSWORD (base de datos)
```

### Paso 4: Configurar Permisos

```bash
# Dar permisos de escritura a carpetas necesarias
chmod 755 Public/
chmod 777 App/Uploads/
chmod 777 App/Views/cache/

# Si existe la carpeta Logs
chmod 777 App/Logs/
```

### Paso 5: Configurar Base de Datos (Opcional)

Si vas a usar base de datos:

```sql
-- Crear base de datos
CREATE DATABASE mi_proyecto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario (opcional)
CREATE USER 'mi_usuario'@'localhost' IDENTIFIED BY 'mi_contraseña';
GRANT ALL PRIVILEGES ON mi_proyecto.* TO 'mi_usuario'@'localhost';
FLUSH PRIVILEGES;
```

## 🌐 Configuración del Servidor Web

Después de la instalación, necesitas configurar tu servidor web:

- **Para Apache**: Ver [[Configuración-Apache|Apache-Setup]]
- **Para Nginx**: Ver [[Configuración-Nginx|Nginx-Setup]]

## ✅ Verificar Instalación

### 1. Verificar Dependencias
```bash
composer show
```

### 2. Verificar Configuración
```bash
php -v
php -m
```

### 3. Verificar Estructura de Archivos
```
mi-proyecto/
├── App/
│   ├── Config/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Lib/
├── Public/
│   └── index.php
├── vendor/
├── .env
└── composer.json
```

### 4. Probar en el Navegador

1. Configura tu servidor web
2. Visita: `http://localhost/mi-proyecto` o `http://mi-proyecto.local`
3. Deberías ver la página de inicio (Login)

## 🔧 Configuración de Desarrollo

### Configurar Entorno de Desarrollo Local

#### Con Apache (XAMPP/LAMPP/MAMP)
```bash
# Copiar proyecto a htdocs
cp -r mi-proyecto /opt/lampp/htdocs/

# Acceder via: http://localhost/mi-proyecto
```

#### Con Servidor PHP Integrado
```bash
# Navegar a la carpeta Public
cd mi-proyecto/Public

# Iniciar servidor
php -S localhost:8000

# Acceder via: http://localhost:8000
```

#### Con Docker (Opcional)
```dockerfile
# Dockerfile básico
FROM php:8.0-apache

# Instalar extensiones
RUN docker-php-ext-install pdo pdo_mysql

# Copiar archivos
COPY . /var/www/html/

# Configurar Apache
RUN a2enmod rewrite
```

## 🚨 Solución de Problemas de Instalación

### Error: "composer: command not found"
```bash
# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Error: "Class not found"
```bash
# Regenerar autoloader
composer dump-autoload
```

### Error: "Permission denied"
```bash
# Verificar propietario
sudo chown -R www-data:www-data mi-proyecto/

# O con tu usuario
sudo chown -R $USER:$USER mi-proyecto/
```

### Error: "Database connection error"
1. Verificar que MySQL/PostgreSQL esté ejecutándose
2. Comprobar credenciales en `.env`
3. Verificar que la base de datos exista

### Error: "500 Internal Server Error"
1. Verificar logs del servidor web
2. Comprobar permisos de archivos
3. Verificar configuración de `.htaccess`
4. Activar `APP_DEBUG=true` en `.env`

## 📝 Próximos Pasos

Después de una instalación exitosa:

1. **Configurar variables de entorno**: [[Variables-de-Entorno|Environment-Variables]]
2. **Entender la estructura**: [[Estructura-del-Proyecto|Project-Structure]]
3. **Crear tu primer controlador**: [[Creando-Controladores|Creating-Controllers]]
4. **Seguir el tutorial CRUD**: [[Tutorial-CRUD|CRUD-Tutorial]]

---

**¡Instalación completada! Ahora puedes empezar a desarrollar tu aplicación. 🎉**
