# 🗄️ Trabajando con la Base de Datos de Ejemplo

Esta página explica cómo usar la base de datos de ejemplo incluida en el template para aprender y probar el framework.

## 📊 Esquema de la Base de Datos

El template incluye un archivo `database.sql` con un esquema de ejemplo que demuestra relaciones entre entidades.

### 🏗️ Estructura de Tablas

#### Tabla `usuarios`
```sql
CREATE TABLE `usuarios` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(255),
    `created_at` datetime,
    `updated_at` datetime,
    PRIMARY KEY (`id`)
);
```

#### Tabla `oficinas`
```sql
CREATE TABLE `oficinas` (
    `id` int NOT NULL AUTO_INCREMENT,
    `address` varchar(255),
    `created_at` datetime,
    `updated_at` datetime,
    PRIMARY KEY (`id`)
);
```

#### Tabla `usuario_oficina` (Pivote)
```sql
CREATE TABLE `usuario_oficina` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_usuario` int,
    `id_oficina` int,
    `created_at` datetime,
    `updated_at` datetime,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`),
    FOREIGN KEY (`id_oficina`) REFERENCES `oficinas`(`id`)
);
```

## 📦 Modelos Incluidos

### 🧑 Modelo Users
```php
// App/Models/Users.php
class Users extends DB
{
    protected $table = "usuarios";
    protected $guarded = ["id", "created_at", "updated_at"];

    // Un usuario pertenece a una sola oficina (relación uno a uno)
    public function OfficeUsers()
    {
        return $this->hasOne(OfficeUser::class, "id_usuario", "id");
    }
}
```

### 🏢 Modelo Offices
```php
// App/Models/Offices.php
class Offices extends DB
{
    protected $table = "oficinas";
    protected $guarded = ["id", "created_at", "updated_at"];

    // Una oficina puede tener muchos usuarios (through pivot)
    public function OfficeUsers()
    {
        return $this->hasMany(OfficeUser::class, "id_oficina", "id");
    }
}
```

### 🔗 Modelo OfficeUser (Pivote)
```php
// App/Models/OfficeUser.php
class OfficeUser extends DB
{
    protected $table = "usuario_oficina";
    protected $guarded = ["id", "created_at", "updated_at"];

    // Pertenece a un usuario
    public function User()
    {
        return $this->belongsTo(Users::class, "id_usuario", "id");
    }
    
    // Pertenece a una oficina
    public function Office()
    {
        return $this->belongsTo(Offices::class, "id_oficina", "id");
    }
}
```

## 🚀 Configuración e Instalación

### 1. Importar Base de Datos

```bash
# Desde la raíz del proyecto
mysql -u tu_usuario -p tu_base_datos < database.sql

# O usando phpMyAdmin:
# - Crear nueva base de datos
# - Importar archivo database.sql
```

### 2. Configurar .env

```env
DB_DRIVER=mysql
DB_HOST="localhost"
DB_NAME="tu_base_datos"
DB_USERNAME="tu_usuario"
DB_PASSWORD="tu_contraseña"
```

### 3. Verificar Conexión

Crear un controlador de prueba:

```php
<?php
// App/Controllers/Test.php

class Test extends Controller
{
    public function database()
    {
        try {
            // Probar conexión
            $usuarios = \App\Models\Users::all();
            $oficinas = \App\Models\Offices::all();
            
            echo "<h2>✅ Conexión exitosa</h2>";
            echo "<p>Usuarios: " . count($usuarios) . "</p>";
            echo "<p>Oficinas: " . count($oficinas) . "</p>";
            
        } catch (Exception $e) {
            echo "<h2>❌ Error de conexión</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }
}
```

Acceder a: `http://tu-dominio.com/test/database`

## 💡 Ejemplos de Uso

### Operaciones Básicas

```php
// Crear usuario
$usuario = new \App\Models\Users();
$usuario->name = "Juan Pérez";
$usuario->save();

// Crear oficina
$oficina = new \App\Models\Offices();
$oficina->address = "Calle 123, Centro";
$oficina->save();

// Asignar usuario a oficina
$asignacion = new \App\Models\OfficeUser();
$asignacion->id_usuario = $usuario->id;
$asignacion->id_oficina = $oficina->id;
$asignacion->save();
```

### Consultas con Relaciones

```php
// Usuario con su oficina
$usuario = \App\Models\Users::with('OfficeUsers.Office')->find(1);

if ($usuario->OfficeUsers) {
    echo "Oficina: " . $usuario->OfficeUsers->Office->address . "\n";
} else {
    echo "Usuario sin oficina asignada\n";
}

// Oficina con sus usuarios
$oficina = \App\Models\Offices::with('OfficeUsers.User')->find(1);

foreach ($oficina->OfficeUsers as $asignacion) {
    echo "Usuario: " . $asignacion->User->name . "\n";
}

// Usuarios que trabajan en oficinas del centro
$usuarios = \App\Models\Users::whereHas('OfficeUsers', function($query) {
    $query->whereHas('Office', function($q) {
        $q->where('address', 'like', '%Centro%');
    });
})->get();
```

### Consultas Avanzadas

```php
// Contar usuarios por oficina
$stats = \App\Models\Offices::withCount('OfficeUsers')->get();

foreach ($stats as $oficina) {
    echo $oficina->address . ": " . $oficina->office_users_count . " usuarios\n";
}

// Usuarios sin oficina asignada (relación uno a uno)
$sinOficina = \App\Models\Users::doesntHave('OfficeUsers')->get();

// Oficinas con más de 2 usuarios
$oficinasGrandes = \App\Models\Offices::has('OfficeUsers', '>', 2)->get();
```

## 🎯 Casos de Uso Prácticos

### 1. Dashboard de Estadísticas

```php
public function dashboard()
{
    $stats = [
        'total_usuarios' => \App\Models\Users::count(),
        'total_oficinas' => \App\Models\Offices::count(),
        'total_asignaciones' => \App\Models\OfficeUser::count(),
        'usuarios_sin_oficina' => \App\Models\Users::doesntHave('OfficeUsers')->count(),
        'oficinas_vacias' => \App\Models\Offices::doesntHave('OfficeUsers')->count()
    ];
    
    return $this->view("dashboard", compact('stats'));
}
```

### 2. Reporte de Asignaciones

```php
public function reporte()
{
    $reporte = \App\Models\OfficeUser::with(['User', 'Office'])
        ->orderBy('created_at', 'desc')
        ->get();
        
    return $this->view("reportes.asignaciones", compact('reporte'));
}
```

### 3. Búsqueda Inteligente

```php
public function buscar($termino)
{
    $usuarios = \App\Models\Users::where('name', 'like', "%{$termino}%")
        ->with('OfficeUsers.Office')
        ->get();
        
    $oficinas = \App\Models\Offices::where('address', 'like', "%{$termino}%")
        ->with('OfficeUsers.User')
        ->get();
        
    return $this->view("busqueda.resultados", compact('usuarios', 'oficinas'));
}
```

## 🔧 Extensiones Sugeridas

### Agregar Más Campos

```sql
-- Extender tabla usuarios
ALTER TABLE usuarios ADD COLUMN email VARCHAR(255) UNIQUE;
ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20);
ALTER TABLE usuarios ADD COLUMN activo BOOLEAN DEFAULT 1;

-- Extender tabla oficinas
ALTER TABLE oficinas ADD COLUMN telefono VARCHAR(20);
ALTER TABLE oficinas ADD COLUMN ciudad VARCHAR(100);
ALTER TABLE oficinas ADD COLUMN activa BOOLEAN DEFAULT 1;

-- Agregar campos a la relación
ALTER TABLE usuario_oficina ADD COLUMN cargo VARCHAR(100);
ALTER TABLE usuario_oficina ADD COLUMN fecha_inicio DATE;
ALTER TABLE usuario_oficina ADD COLUMN salario DECIMAL(10,2);
```

### Nuevos Modelos Relacionados

```sql
-- Tabla departamentos
CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    oficina_id INT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (oficina_id) REFERENCES oficinas(id)
);

-- Tabla proyectos
CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    descripcion TEXT,
    fecha_inicio DATE,
    fecha_fin DATE,
    created_at DATETIME,
    updated_at DATETIME
);

-- Tabla usuario_proyecto (muchos a muchos)
CREATE TABLE usuario_proyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    proyecto_id INT,
    rol VARCHAR(100),
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
);
```

## 📚 Recursos Adicionales

- **[[Tutorial-CRUD|CRUD-Tutorial]]** - Tutorial completo usando estos modelos
- **[[Creando-Modelos|Creating-Models]]** - Guía detallada de modelos Eloquent
- **[Documentación Eloquent](https://laravel.com/docs/eloquent)** - Documentación oficial

---

**¡Experimenta con la base de datos de ejemplo para dominar Eloquent! 🚀**
