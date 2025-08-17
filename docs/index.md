---
layout: default
title: PHP MVC Template - Documentación
---

# 🏠 PHP MVC Template - Documentación

<div style="background: #f4f4f4; padding: 1rem; margin: 1rem 0; border-radius: 8px; text-align: center;">
  <strong>📚 Navegación Rápida:</strong>
  <a href="Installation.html" style="margin: 0 10px; color: #159957;">📦 Instalación</a> |
  <a href="Creating-Models.html" style="margin: 0 10px; color: #159957;">🗄️ Modelos</a> |
  <a href="CRUD-Tutorial.html" style="margin: 0 10px; color: #159957;">📝 Tutorial CRUD</a> |
  <a href="CI-CD-Workflow.html" style="margin: 0 10px; color: #159957;">🚀 CI/CD</a>
</div>

¡Bienvenido a la documentación oficial del **PHP MVC Template**!

## 📚 Documentación Disponible

### 🚀 Primeros Pasos
- **[📦 Instalación](Installation.html)** - Configuración inicial del proyecto
- **[🔴 Configuración Apache](Apache-Setup.html)** - Setup para servidor Apache  
- **[🟢 Configuración Nginx](Nginx-Setup.html)** - Setup para servidor Nginx
- **[🌍 Variables de Entorno](Environment-Variables.html)** - Configuración del archivo .env

### 💼 Desarrollo de la Aplicación
- **[🗄️ Creando Modelos](Creating-Models.html)** - Trabajando con modelos Eloquent y relaciones
- **[📝 Tutorial CRUD](CRUD-Tutorial.html)** - Tutorial completo: sistema Usuario-Oficina
- **[🔍 Ejemplos Base de Datos](Database-Examples.html)** - Trabajando con la BD de ejemplo

### 🚀 Despliegue y Producción  
- **[🔄 CI/CD Workflow](CI-CD-Workflow.html)** - Integración y despliegue continuo

### 📖 Documentación Adicional

> **Nota**: Esta documentación cubre las características principales del framework. 
> Para funcionalidades adicionales como creación de controladores, vistas, autenticación, APIs REST, etc., 
> consulta los ejemplos en el código fuente o revisa el [Tutorial CRUD](CRUD-Tutorial.html) que incluye 
> ejemplos prácticos de controladores y vistas.

---

## 🎯 ¿Qué es este Template?

Este template implementa el patrón **Model-View-Controller (MVC)** con características modernas de PHP, proporcionando una base sólida para aplicaciones web escalables.

### ✨ Características Principales

- 🏗️ **Arquitectura MVC** limpia y organizada
- 🛣️ **Sistema de rutas** personalizable y amigable
- 🎨 **Motor de plantillas Blade** (Laravel)
- 🗄️ **Eloquent ORM** para base de datos
- 📧 **Sistema de correos** integrado
- 🔐 **Gestión de sesiones** segura
- 🌍 **Variables de entorno** (.env)
- 📦 **Composer** para dependencias

### 🔧 Tecnologías Incluidas

- **PHP 8.0+**
- **Blade Templates** (Jenssegers)
- **Eloquent ORM** (Illuminate Database)
- **PHPMailer** para correos
- **JWT** para autenticación
- **Dotenv** para configuración

---

## 🚀 Inicio Rápido

1. **Clona el repositorio**:
   ```bash
   git clone [URL_DEL_REPO] mi-proyecto
   cd mi-proyecto
   ```

2. **Instala dependencias**:
   ```bash
   composer install
   ```

3. **Configura el entorno**:
   ```bash
   cp .env.example .env
   # Edita .env con tus configuraciones
   ```

4. **Configura tu servidor web**: Ver [Configuración Apache](Apache-Setup.html) o [Configuración Nginx](Nginx-Setup.html)

5. **¡Listo!** Visita tu aplicación en el navegador.

---

## 🆘 ¿Necesitas Ayuda?

- 📖 **Lee la documentación**: Revisa las páginas de esta wiki
- 🐛 **Reporta problemas**: Abre un issue en GitHub
- 💡 **Sugerencias**: ¡Contribuye con mejoras!

---

**¡Comienza a construir tu aplicación web! 🚀**
