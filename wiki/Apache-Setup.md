# 🔴 Configuración Apache

Esta guía te ayudará a configurar Apache para trabajar con el PHP MVC Template.

## 📋 Requisitos Previos

- Apache 2.4+
- PHP 8.0+ con módulo Apache
- Módulo `mod_rewrite` habilitado

### Verificar Módulos Necesarios
```bash
# Verificar si mod_rewrite está habilitado
apache2ctl -M | grep rewrite

# Si no está habilitado:
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 🏠 Configuración para Desarrollo Local

### Opción 1: Virtual Host (Recomendado)

#### 1. Crear archivo de configuración:
```bash
sudo nano /etc/apache2/sites-available/mi-proyecto.conf
```

#### 2. Contenido del Virtual Host:
```apache
<VirtualHost *:80>
    # Nombre del servidor
    ServerName mi-proyecto.local
    ServerAlias www.mi-proyecto.local
    
    # Ruta del proyecto
    DocumentRoot /ruta/completa/a/mi-proyecto
    
    # Configuración del directorio
    <Directory /ruta/completa/a/mi-proyecto>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Configuración adicional de seguridad
        Options -ExecCGI -Includes
        DirectoryIndex index.php index.html
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/mi-proyecto_error.log
    CustomLog ${APACHE_LOG_DIR}/mi-proyecto_access.log combined
    
    # Configuración PHP (opcional)
    php_admin_value upload_max_filesize 64M
    php_admin_value post_max_size 64M
    php_admin_value memory_limit 256M
    php_admin_value max_execution_time 300
</VirtualHost>
```

#### 3. Habilitar el sitio:
```bash
# Habilitar el sitio
sudo a2ensite mi-proyecto.conf

# Recargar Apache
sudo systemctl reload apache2

# Agregar al archivo hosts
echo "127.0.0.1 mi-proyecto.local" | sudo tee -a /etc/hosts
```

#### 4. Acceder:
```
http://mi-proyecto.local
```

### Opción 2: Subdirectorio de htdocs

Si usas XAMPP, LAMPP o similar:

```bash
# Copiar proyecto a htdocs
cp -r mi-proyecto /opt/lampp/htdocs/
# O en Windows: C:\xampp\htdocs\

# Acceder via:
http://localhost/mi-proyecto
```

## 🌐 Configuración para Producción

### Virtual Host con SSL:
```apache
# HTTP - Redirección a HTTPS
<VirtualHost *:80>
    ServerName mi-dominio.com
    ServerAlias www.mi-dominio.com
    DocumentRoot /var/www/mi-proyecto
    
    # Redireccionar todo a HTTPS
    Redirect permanent / https://mi-dominio.com/
</VirtualHost>

# HTTPS
<VirtualHost *:443>
    ServerName mi-dominio.com
    ServerAlias www.mi-dominio.com
    DocumentRoot /var/www/mi-proyecto
    
    # Configuración SSL
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    # Headers de seguridad
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    <Directory /var/www/mi-proyecto>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Seguridad adicional
        <Files ".env">
            Require all denied
        </Files>
        
        <Files "composer.json">
            Require all denied
        </Files>
        
        <Files "composer.lock">
            Require all denied
        </Files>
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/mi-proyecto_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/mi-proyecto_ssl_access.log combined
</VirtualHost>
```

## 📄 Archivos .htaccess

El template incluye dos archivos `.htaccess` importantes:

### 1. .htaccess Principal (Raíz del proyecto):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Permitir acceso a ciertos archivos/carpetas
    RewriteCond $1 !^(index\.php|images|styles|scripts|Public|robots\.txt|favicon\.ico)
    
    # Redireccionar todo a la carpeta Public
    RewriteRule ^(.*)$ /Public/$1 [L]
</IfModule>

# Configuración de PHP
<IfModule mime_module>
    # Especificar versión de PHP (ejemplo para cPanel)
    AddHandler application/x-httpd-ea-php82 .php .php8 .phtml
</IfModule>

# Seguridad adicional
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Bloquear acceso a carpetas sensibles
RedirectMatch 403 ^.*/App/.*$
RedirectMatch 403 ^.*/vendor/.*$
```

### 2. Public/.htaccess (Carpeta Public):
```apache
<IfModule mod_rewrite.c>
    Options -Multiviews
    RewriteEngine On
    RewriteBase /Public/
    
    # Si el archivo o directorio no existe, redireccionar al index
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]
</IfModule>

# Configuración de caché para assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 month"
</IfModule>

# Compresión GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE image/x-icon
    AddOutputFilterByType DEFLATE image/svg+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/x-font
    AddOutputFilterByType DEFLATE application/x-font-truetype
    AddOutputFilterByType DEFLATE application/x-font-ttf
    AddOutputFilterByType DEFLATE application/x-font-otf
    AddOutputFilterByType DEFLATE application/x-font-opentype
    AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
    AddOutputFilterByType DEFLATE font/ttf
    AddOutputFilterByType DEFLATE font/otf
    AddOutputFilterByType DEFLATE font/opentype
</IfModule>
```

## 🔧 Configuraciones Avanzadas

### Configuración PHP en Apache:
```apache
# En el Virtual Host o .htaccess
php_admin_value upload_max_filesize 64M
php_admin_value post_max_size 64M
php_admin_value memory_limit 256M
php_admin_value max_execution_time 300
php_admin_value max_input_vars 3000
php_admin_value session.gc_maxlifetime 7200

# Configuración de errores (solo desarrollo)
php_admin_flag display_errors On
php_admin_flag log_errors On
php_admin_value error_log /var/log/apache2/php_errors.log
```

### Headers de Seguridad:
```apache
<IfModule mod_headers.c>
    # Prevenir clickjacking
    Header always append X-Frame-Options DENY
    
    # Prevenir MIME sniffing
    Header always set X-Content-Type-Options nosniff
    
    # XSS Protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # HSTS (solo para HTTPS)
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    
    # Content Security Policy (personalizar según necesidades)
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"
    
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

## 🚨 Solución de Problemas Apache

### Error: "Internal Server Error (500)"
```bash
# Verificar logs de error
sudo tail -f /var/log/apache2/error.log

# Verificar sintaxis de Apache
sudo apache2ctl configtest

# Verificar permisos
ls -la /ruta/a/mi-proyecto/
```

### Error: "mod_rewrite not working"
```bash
# Habilitar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verificar AllowOverride en configuración
grep -r "AllowOverride" /etc/apache2/
```

### Error: "Permission denied"
```bash
# Cambiar propietario
sudo chown -R www-data:www-data /ruta/a/mi-proyecto/

# Permisos correctos
sudo chmod 755 /ruta/a/mi-proyecto/
sudo chmod 777 /ruta/a/mi-proyecto/App/Uploads/
sudo chmod 777 /ruta/a/mi-proyecto/App/Views/cache/
```

### Error: "Unable to load .env file"
```bash
# Verificar que existe el archivo .env
ls -la /ruta/a/mi-proyecto/.env

# Verificar permisos de lectura
chmod 644 /ruta/a/mi-proyecto/.env
```

## 📱 Configuración para Hosting Compartido

### cPanel/Hosting típico:
1. **Subir archivos** via FTP a `public_html/`
2. **Configurar .htaccess** (ya incluido en el template)
3. **Crear base de datos** desde el panel de control
4. **Configurar .env** con credenciales del hosting
5. **Ajustar rutas** si es necesario en Config.php

### Estructura en hosting compartido:
```
public_html/
├── .htaccess          (redirige a Public/)
├── App/
├── vendor/
├── .env
└── Public/
    ├── index.php      (punto de entrada)
    └── .htaccess
```

## ✅ Verificar Configuración

### 1. Verificar Virtual Host:
```bash
apache2ctl -S
```

### 2. Verificar sintaxis:
```bash
apache2ctl configtest
```

### 3. Verificar módulos:
```bash
apache2ctl -M | grep rewrite
```

### 4. Probar en navegador:
- `http://mi-proyecto.local` (desarrollo)
- Debe mostrar la página de login del template

---

**¡Tu aplicación está lista para funcionar con Apache! 🎉**

Para continuar: [[Variables-de-Entorno|Environment-Variables]]
