# 🗄️ Creando Modelos con Eloquent

Esta guía explica cómo crear y usar modelos con **Eloquent ORM** en el PHP MVC Template.

## 🎯 ¿Qué son los Modelos?

Los modelos representan las tablas de tu base de datos y manejan toda la lógica de acceso a datos usando **Eloquent ORM** de Laravel.

## 🏗️ Estructura Básica de un Modelo

### Crear un modelo básico:

```php
<?php
// App/Models/MiModelo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;

class MiModelo extends DB
{
    // Nombre de la tabla (opcional si sigue convención)
    protected $table = "mi_tabla";
    
    // Campos que NO se pueden asignar masivamente
    protected $guarded = ["id", "created_at", "updated_at"];
    
    // O campos que SÍ se pueden asignar (alternativa a $guarded)
    // protected $fillable = ["name", "email", "address"];
}
```

## 📊 Ejemplo Práctico: Sistema Usuario-Oficina

El template incluye un ejemplo completo con **3 modelos** que demuestran diferentes tipos de relaciones:

### 📁 Estructura de Base de Datos

```sql
-- Tabla usuarios
CREATE TABLE `usuarios` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(255),
    `created_at` datetime,
    `updated_at` datetime,
    PRIMARY KEY (`id`)
);

-- Tabla oficinas  
CREATE TABLE `oficinas` (
    `id` int NOT NULL AUTO_INCREMENT,
    `address` varchar(255),
    `created_at` datetime,
    `updated_at` datetime,
    PRIMARY KEY (`id`)
);

-- Tabla pivote usuario_oficina (relación muchos a muchos)
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

### 🔗 Modelos con Relaciones

#### 1. Modelo Users (Usuario)

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;

class Users extends DB
{
    protected $table = "usuarios";
    protected $guarded = ["id", "created_at", "updated_at"];

    // Relación: Un usuario pertenece a una sola oficina
    public function OfficeUsers()
    {
        return $this->hasOne(OfficeUser::class, "id_usuario", "id");
    }
    
    // Método auxiliar para obtener columnas de la tabla
    public function getColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
```

#### 2. Modelo Offices (Oficina)

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;

class Offices extends DB
{
    protected $table = "oficinas";
    protected $guarded = ["id", "created_at", "updated_at"];

    // Relación: Una oficina puede tener muchos usuarios
    public function OfficeUsers()
    {
        return $this->hasMany(OfficeUser::class, "id_oficina", "id");
    }
    
    public function getColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
```

#### 3. Modelo OfficeUser (Tabla Pivote)

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;

class OfficeUser extends DB
{
    protected $table = "usuario_oficina";
    protected $guarded = ["id", "created_at", "updated_at"];

    // Relación: Pertenece a un usuario
    public function User()
    {
        return $this->belongsTo(Users::class, "id_usuario", "id");
    }
    
    // Relación: Pertenece a una oficina
    public function Office()
    {
        return $this->belongsTo(Offices::class, "id_oficina", "id");
    }
    
    public function getColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
```

## 🔗 Tipos de Relaciones en Eloquent

### 1. **hasOne** (Uno a Uno)
```php
// Un usuario tiene una asignación a oficina
public function OfficeUsers()
{
    return $this->hasOne(OfficeUser::class, "id_usuario", "id");
    //                   Modelo,         FK,          PK
}
```

### 2. **belongsTo** (Pertenece a)
```php
// Una asignación pertenece a un usuario
public function User()
{
    return $this->belongsTo(Users::class, "id_usuario", "id");
    //                       Modelo,       FK,          PK
}
```

### 3. **hasMany** (Uno a Muchos)
```php
// Una oficina puede tener muchos usuarios
public function OfficeUsers()
{
    return $this->hasMany(OfficeUser::class, "id_oficina", "id");
    //                    Modelo,         FK,          PK
}
```

### 4. **belongsToMany** (Muchos a Muchos)
```php
// Relación directa muchos a muchos (sin modelo pivote)
// Ejemplo para otros casos de uso:
public function roles()
{
    return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
}
```

## 💼 Usando los Modelos en Controladores

### Operaciones Básicas (CRUD)

```php
<?php
// En un controlador

class UsuariosController extends Controller
{
    public function index()
    {
        // Obtener todos los usuarios
        $usuarios = \App\Models\Users::all();
        
        // Con paginación
        $usuarios = \App\Models\Users::paginate(10);
        
        return $this->view("usuarios.index", [
            "usuarios" => $usuarios
        ]);
    }
    
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Crear nuevo usuario
            $usuario = new \App\Models\Users();
            $usuario->name = $_POST['name'];
            $usuario->save();
            
            // O crear con fill/create
            \App\Models\Users::create([
                'name' => $_POST['name']
            ]);
            
            redirect('/usuarios');
        }
        
        return $this->view("usuarios.crear");
    }
    
    public function ver($id)
    {
        // Buscar usuario por ID
        $usuario = \App\Models\Users::find($id);
        $this->page404($usuario); // Validar que existe
        
        // Cargar relaciones
        $usuario->load('OfficeUsers.Office');
        
        return $this->view("usuarios.ver", [
            "usuario" => $usuario
        ]);
    }
    
    public function actualizar($id)
    {
        $usuario = \App\Models\Users::findOrFail($id);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario->name = $_POST['name'];
            $usuario->save();
            
            redirect('/usuarios/' . $id);
        }
        
        return $this->view("usuarios.editar", [
            "usuario" => $usuario
        ]);
    }
    
    public function eliminar($id)
    {
        $usuario = \App\Models\Users::findOrFail($id);
        $usuario->delete();
        
        redirect('/usuarios');
    }
}
```

### Trabajando con Relaciones

```php
// Obtener usuario con su oficina
$usuario = \App\Models\Users::with('OfficeUsers.Office')->find(1);

// Acceder a la oficina del usuario (relación uno a uno)
if ($usuario->OfficeUsers) {
    echo "Oficina: " . $usuario->OfficeUsers->Office->address;
} else {
    echo "Usuario sin oficina asignada";
}

// Asignar usuario a una oficina (solo puede tener una)
$asignacion = new \App\Models\OfficeUser();
$asignacion->id_usuario = 1;
$asignacion->id_oficina = 2;
$asignacion->save();

// O usando relaciones
$usuario = \App\Models\Users::find(1);
$usuario->OfficeUsers()->create([
    'id_oficina' => 2
]);
```

## 🔍 Consultas Avanzadas

### Query Builder
```php
// Filtros
$usuarios = \App\Models\Users::where('name', 'like', '%Juan%')->get();

// Ordenamiento
$usuarios = \App\Models\Users::orderBy('created_at', 'desc')->get();

// Límites
$usuarios = \App\Models\Users::take(5)->get();

// Consultas complejas - usuarios en oficinas del centro
$usuarios = \App\Models\Users::whereHas('OfficeUsers', function($query) {
    $query->whereHas('Office', function($q) {
        $q->where('address', 'like', '%Centro%');
    });
})->get();
```

### Scopes (Consultas Reutilizables)
```php
// En el modelo Users
public function scopeActivos($query)
{
    return $query->where('activo', 1);
}

public function scopeConOficinas($query)
{
    return $query->with('OfficeUsers.Office');
}

// Uso en controlador
$usuarios = \App\Models\Users::activos()->conOficina()->get();
```

## 🛠️ Métodos Útiles del Template

### Método getColumns()
```php
// Obtener columnas de la tabla dinámicamente
$usuario = new \App\Models\Users();
$columnas = $usuario->getColumns();
// Resultado: ['id', 'name', 'created_at', 'updated_at']

// Útil para formularios dinámicos
foreach ($columnas as $columna) {
    if (!in_array($columna, ['id', 'created_at', 'updated_at'])) {
        echo "<input name='{$columna}' placeholder='{$columna}'>";
    }
}
```

## 🎨 Integración con Vistas Blade

### En las vistas:
```blade
{{-- usuarios/index.blade.php --}}
@extends('app')

@section('content')
<h1>Usuarios</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Oficinas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $usuario)
        <tr>
            <td>{{ $usuario->id }}</td>
            <td>{{ $usuario->name }}</td>
            <td>
                @if($usuario->OfficeUsers)
                    <span class="badge">{{ $usuario->OfficeUsers->Office->address }}</span>
                @else
                    <em>Sin oficina</em>
                @endif
            </td>
            <td>
                <a href="/usuarios/ver/{{ $usuario->id }}">Ver</a>
                <a href="/usuarios/editar/{{ $usuario->id }}">Editar</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

## 🔒 Validaciones y Seguridad

### Mass Assignment Protection
```php
// Usar $guarded para proteger campos
protected $guarded = ["id", "created_at", "updated_at"];

// O $fillable para permitir solo campos específicos
protected $fillable = ["name", "email"];
```

### Validaciones en Controlador
```php
public function crear()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validaciones básicas
        if (empty($_POST['name'])) {
            $error = "El nombre es requerido";
            return $this->view("usuarios.crear", ["error" => $error]);
        }
        
        // Verificar duplicados
        $existe = \App\Models\Users::where('name', $_POST['name'])->first();
        if ($existe) {
            $error = "El usuario ya existe";
            return $this->view("usuarios.crear", ["error" => $error]);
        }
        
        // Crear usuario
        \App\Models\Users::create(['name' => $_POST['name']]);
        redirect('/usuarios');
    }
}
```

## 📝 Mejores Prácticas

### ✅ Recomendaciones:

1. **Usar namespaces**: Siempre `namespace App\Models;`
2. **Proteger campos**: Usar `$guarded` o `$fillable`
3. **Nombres consistentes**: Tabla plural, modelo singular
4. **Cargar relaciones**: Usar `with()` para evitar N+1 queries
5. **Validar existencia**: Usar `findOrFail()` o `page404()`

### ❌ Evitar:

1. **No usar SQL directo** en modelos
2. **No cargar relaciones innecesarias**
3. **No hacer consultas en loops**
4. **No exponer campos sensibles**

---

**¡Los modelos Eloquent facilitan enormemente el trabajo con bases de datos! 🚀**

Siguiente: [[CRUD-Tutorial|CRUD-Tutorial]] - Tutorial completo paso a paso
