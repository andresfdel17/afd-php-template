# 🟢 Configuración Nginx

Esta guía te ayudará a configurar Nginx para trabajar con el PHP MVC Template.

## 📋 Requisitos Previos

- Nginx 1.18+
- PHP-FPM 8.0+
- Supervisor (opcional, para gestión de procesos)

### Verificar Instalaciones
```bash
# Verificar Nginx
nginx -v

# Verificar PHP-FPM
php-fpm8.2 -v

# Verificar que PHP-FPM está corriendo
sudo systemctl status php8.2-fpm
```

## 🏠 Configuración para Desarrollo Local

### 1. Crear archivo de configuración:
```bash
sudo nano /etc/nginx/sites-available/mi-proyecto
```

### 2. Configuración básica de desarrollo:
```nginx
server {
    listen 80;
    listen [::]:80;
    
    # Nombre del servidor
    server_name mi-proyecto.local www.mi-proyecto.local;
    
    # Configuración básica
    root /ruta/completa/a/mi-proyecto;
    index index.php index.html index.htm;
    
    # Límites
    client_max_body_size 64M;
    client_body_timeout 60s;
    client_header_timeout 60s;
    
    # Logs
    access_log /var/log/nginx/mi-proyecto_access.log;
    error_log /var/log/nginx/mi-proyecto_error.log warn;
    
    # Redireccionar todo a la carpeta Public
    location / {
        # Redirección principal a Public/
        rewrite ^/$ /Public/ break;
        rewrite ^(.*)$ /Public/$1 break;
        try_files $uri/ /Public/index.php?$args;
    }
    
    # Manejar archivos en Public/
    location /Public/ {
        try_files $uri $uri/ @rewrite;
    }
    
    # Reescritura para el framework
    location @rewrite {
        rewrite ^/Public/(.*)$ /Public/index.php?url=$1 last;
    }
    
    # Procesar archivos PHP
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        
        # Socket o puerto PHP-FPM (ajustar según configuración)
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        # O si usas puerto TCP:
        # fastcgi_pass 127.0.0.1:9000;
        
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # Parámetros FastCGI
        include fastcgi_params;
        
        # Timeouts
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }
    
    # Denegar acceso a archivos sensibles
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(composer\.(json|lock)|\.env)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Bloquear acceso a carpetas del framework
    location ~ ^/(App|vendor)/ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Cache para assets estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

### 3. Habilitar el sitio:
```bash
# Crear enlace simbólico
sudo ln -s /etc/nginx/sites-available/mi-proyecto /etc/nginx/sites-enabled/

# Verificar configuración
sudo nginx -t

# Recargar Nginx
sudo systemctl reload nginx

# Agregar al archivo hosts
echo "127.0.0.1 mi-proyecto.local" | sudo tee -a /etc/hosts
```

### 4. Acceder:
```
http://mi-proyecto.local
```

## 🌐 Configuración para Producción

### Configuración completa con SSL:
```nginx
# Redirección HTTP a HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name mi-dominio.com www.mi-dominio.com;
    
    # Redireccionar todo a HTTPS
    return 301 https://$server_name$request_uri;
}

# Servidor HTTPS principal
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name mi-dominio.com www.mi-dominio.com;
    root /var/www/mi-proyecto;
    index index.php;
    
    # Límites de subida
    client_max_body_size 200M;
    client_body_timeout 120s;
    client_header_timeout 120s;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_trusted_certificate /path/to/chain.crt;
    
    # SSL Security
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;
    ssl_stapling on;
    ssl_stapling_verify on;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options DENY always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
    
    # Logs
    access_log /var/log/nginx/mi-proyecto_ssl_access.log;
    error_log /var/log/nginx/mi-proyecto_ssl_error.log warn;
    
    # Framework routing
    location / {
        rewrite ^/$ /Public/ break;
        rewrite ^(.*)$ /Public/$1 break;
        try_files $uri/ /Public/index.php?$args;
    }
    
    location /Public/ {
        try_files $uri $uri/ @rewrite;
    }
    
    location @rewrite {
        rewrite ^/Public/(.*)$ /Public/index.php?url=$1 last;
    }
    
    # PHP Processing
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Performance tuning
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 8 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    
    # Security - Block sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(composer\.(json|lock)|\.env|README\.md)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ ^/(App|vendor|wiki)/ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Static assets caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|pdf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        
        # CORS para fuentes web
        location ~* \.(woff|woff2|ttf|eot)$ {
            add_header Access-Control-Allow-Origin "*";
        }
    }
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_comp_level 6;
    gzip_min_length 1000;
    gzip_types
        text/plain
        text/css
        application/json
        application/javascript
        text/xml
        application/xml
        application/xml+rss
        text/javascript
        image/svg+xml;
}
```

## ⚙️ Configuración PHP-FPM

### Optimizar PHP-FPM para el proyecto:

```bash
sudo nano /etc/php/8.2/fpm/pool.d/mi-proyecto.conf
```

```ini
[mi-proyecto]
user = www-data
group = www-data

listen = /var/run/php/mi-proyecto-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

; Límites específicos para la aplicación
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_vars] = 3000

; Solo para desarrollo
php_admin_flag[display_errors] = off
php_admin_flag[log_errors] = on
php_admin_value[error_log] = /var/log/nginx/php_errors.log

; Configuración de sesiones
php_admin_value[session.gc_maxlifetime] = 7200
php_admin_value[session.save_path] = /var/lib/php/sessions
```

### Reiniciar PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

## 🔄 Configuración con Docker

### Dockerfile para Nginx + PHP:
```dockerfile
# nginx.dockerfile
FROM nginx:alpine

# Copiar configuración
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Exponer puerto
EXPOSE 80
```

### docker-compose.yml:
```yaml
version: '3.8'

services:
  nginx:
    build:
      context: .
      dockerfile: nginx.dockerfile
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
    depends_on:
      - php-fpm
    
  php-fpm:
    build:
      context: .
      dockerfile: php.dockerfile
    volumes:
      - ./:/var/www/html
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=mi_proyecto
```

## 🚨 Solución de Problemas Nginx

### Error: "502 Bad Gateway"
```bash
# Verificar que PHP-FPM está corriendo
sudo systemctl status php8.2-fpm

# Verificar socket
ls -la /var/run/php/php8.2-fpm.sock

# Verificar logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.2-fpm.log
```

### Error: "File not found"
```bash
# Verificar ruta del documento
grep "root" /etc/nginx/sites-available/mi-proyecto

# Verificar permisos
ls -la /ruta/a/mi-proyecto/

# Verificar configuración de FastCGI
grep "fastcgi_param SCRIPT_FILENAME" /etc/nginx/sites-available/mi-proyecto
```

### Error: "413 Request Entity Too Large"
```nginx
# Aumentar límite en configuración
client_max_body_size 200M;
```

### Error de sintaxis en configuración:
```bash
# Verificar sintaxis
sudo nginx -t

# Ver detalles del error
sudo nginx -T
```

## 📊 Monitoreo y Logs

### Configurar rotación de logs:
```bash
sudo nano /etc/logrotate.d/mi-proyecto
```

```
/var/log/nginx/mi-proyecto_*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 www-data adm
    postrotate
        if [ -f /var/run/nginx.pid ]; then
            kill -USR1 `cat /var/run/nginx.pid`
        fi
    endscript
}
```

### Monitoreo en tiempo real:
```bash
# Logs de acceso
sudo tail -f /var/log/nginx/mi-proyecto_access.log

# Logs de error
sudo tail -f /var/log/nginx/mi-proyecto_error.log

# Logs de PHP
sudo tail -f /var/log/php8.2-fpm.log
```

## ⚡ Optimización de Rendimiento

### Buffer y caché:
```nginx
# En el bloque server
fastcgi_cache_path /var/cache/nginx/fastcgi levels=1:2 keys_zone=phpcache:100m max_size=10g inactive=60m use_temp_path=off;

# En location ~ \.php$
fastcgi_cache phpcache;
fastcgi_cache_valid 200 302 10m;
fastcgi_cache_valid 404 1m;
fastcgi_cache_bypass $http_pragma $http_authorization;
add_header X-Cache-Status $upstream_cache_status;
```

### Rate limiting:
```nginx
# En el bloque http
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

# En location específico
location /login {
    limit_req zone=login burst=3 nodelay;
    # ... resto de configuración
}
```

## ✅ Verificar Configuración

### 1. Verificar sintaxis:
```bash
sudo nginx -t
```

### 2. Verificar sitios habilitados:
```bash
sudo nginx -T | grep server_name
```

### 3. Verificar PHP-FPM:
```bash
sudo systemctl status php8.2-fpm
```

### 4. Probar en navegador:
- `http://mi-proyecto.local` (desarrollo)
- Debe mostrar la página de login del template

---

**¡Tu aplicación está lista para funcionar con Nginx! 🚀**

Para continuar: [[Variables-de-Entorno|Environment-Variables]]
