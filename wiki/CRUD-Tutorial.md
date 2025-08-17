# 📝 Tutorial CRUD Completo

Esta guía te muestra paso a paso cómo crear un **CRUD completo** (Create, Read, Update, Delete) usando el sistema de ejemplo Usuario-Oficina.

## 🎯 Lo que vamos a crear

Un sistema para gestionar **usuarios** y **oficinas** con:
- ✅ Listar usuarios y oficinas
- ✅ Crear nuevos registros
- ✅ Ver detalles y relaciones
- ✅ Editar información
- ✅ Eliminar registros
- ✅ Asignar usuario a una oficina (relación uno a uno)

## 🗄️ Preparación: Base de Datos

### 1. Importar la base de datos:
```bash
# Desde la raíz del proyecto
mysql -u usuario -p nombre_base_datos < database.sql
```

### 2. Configurar .env:
```env
DB_HOST="localhost"
DB_NAME="tu_base_datos"
DB_USERNAME="tu_usuario"
DB_PASSWORD="tu_contraseña"
```

## 🎮 Paso 1: Controlador de Usuarios

Crear `App/Controllers/Usuarios.php`:

```php
<?php

class Usuarios extends Controller
{
    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        // Obtener usuarios con su oficina asignada
        $usuarios = \App\Models\Users::with('OfficeUsers.Office')->get();
        
        return $this->view("Controllers.Usuarios.index", [
            "title" => "Gestión de Usuarios",
            "usuarios" => $usuarios
        ]);
    }
    
    /**
     * Mostrar formulario para crear usuario
     */
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar datos
            $errores = $this->validarUsuario($_POST);
            
            if (empty($errores)) {
                // Crear usuario
                $usuario = new \App\Models\Users();
                $usuario->name = trim($_POST['name']);
                $usuario->save();
                
                // Redirigir con mensaje de éxito
                $_SESSION['mensaje'] = "Usuario creado exitosamente";
                redirect('/usuarios');
            }
        }
        
        return $this->view("Controllers.Usuarios.crear", [
            "title" => "Crear Usuario",
            "errores" => $errores ?? []
        ]);
    }
    
    /**
     * Ver detalles de un usuario
     */
    public function ver($id)
    {
        // Buscar usuario con sus relaciones
        $usuario = \App\Models\Users::with('OfficeUsers.Office')->find($id);
        $this->page404($usuario); // Validar que existe
        
        // Obtener oficinas disponibles para asignar
        $oficinaAsignadaId = $usuario->OfficeUsers ? $usuario->OfficeUsers->id_oficina : null;
        $oficinasDisponibles = \App\Models\Offices::when($oficinaAsignadaId, function($query, $id) {
            return $query->where('id', '!=', $id);
        })->get();
        
        return $this->view("Controllers.Usuarios.ver", [
            "title" => "Usuario: " . $usuario->name,
            "usuario" => $usuario,
            "oficinasDisponibles" => $oficinasDisponibles
        ]);
    }
    
    /**
     * Editar usuario
     */
    public function editar($id)
    {
        $usuario = \App\Models\Users::find($id);
        $this->page404($usuario);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errores = $this->validarUsuario($_POST, $id);
            
            if (empty($errores)) {
                $usuario->name = trim($_POST['name']);
                $usuario->save();
                
                $_SESSION['mensaje'] = "Usuario actualizado exitosamente";
                redirect('/usuarios/ver/' . $id);
            }
        }
        
        return $this->view("Controllers.Usuarios.editar", [
            "title" => "Editar Usuario",
            "usuario" => $usuario,
            "errores" => $errores ?? []
        ]);
    }
    
    /**
     * Eliminar usuario
     */
    public function eliminar($id)
    {
        $usuario = \App\Models\Users::find($id);
        $this->page404($usuario);
        
        // Eliminar asignaciones primero
        \App\Models\OfficeUser::where('id_usuario', $id)->delete();
        
        // Eliminar usuario
        $usuario->delete();
        
        $_SESSION['mensaje'] = "Usuario eliminado exitosamente";
        redirect('/usuarios');
    }
    
    /**
     * Asignar usuario a oficina (cambiar oficina si ya tiene una)
     */
    public function asignarOficina($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuarioId = $userId;
            $oficinaId = $_POST['oficina_id'];
            
            // Buscar asignación existente
            $asignacionExistente = \App\Models\OfficeUser::where('id_usuario', $usuarioId)->first();
            
            if ($asignacionExistente) {
                // Actualizar oficina existente
                $asignacionExistente->id_oficina = $oficinaId;
                $asignacionExistente->save();
                $_SESSION['mensaje'] = "Oficina del usuario actualizada exitosamente";
            } else {
                // Crear nueva asignación
                $asignacion = new \App\Models\OfficeUser();
                $asignacion->id_usuario = $usuarioId;
                $asignacion->id_oficina = $oficinaId;
                $asignacion->save();
                $_SESSION['mensaje'] = "Usuario asignado a oficina exitosamente";
            }
        }
        
        redirect('/usuarios/ver/' . $userId);
    }
    
    /**
     * Remover usuario de oficina
     */
    public function removerOficina($userId)
    {
        $asignacion = \App\Models\OfficeUser::where('id_usuario', $userId)->first();
            
        if ($asignacion) {
            $asignacion->delete();
            $_SESSION['mensaje'] = "Usuario removido de oficina exitosamente";
        }
        
        redirect('/usuarios/ver/' . $userId);
    }
    
    /**
     * Validar datos del usuario
     */
    private function validarUsuario($datos, $id = null)
    {
        $errores = [];
        
        // Validar nombre
        if (empty($datos['name'])) {
            $errores[] = "El nombre es requerido";
        } elseif (strlen($datos['name']) < 2) {
            $errores[] = "El nombre debe tener al menos 2 caracteres";
        } else {
            // Verificar duplicados
            $query = \App\Models\Users::where('name', trim($datos['name']));
            if ($id) {
                $query->where('id', '!=', $id);
            }
            
            if ($query->exists()) {
                $errores[] = "Ya existe un usuario con ese nombre";
            }
        }
        
        return $errores;
    }
}
```

## 🎮 Paso 2: Controlador de Oficinas

Crear `App/Controllers/Oficinas.php`:

```php
<?php

class Oficinas extends Controller
{
    public function index()
    {
        $oficinas = \App\Models\Offices::with('OfficeUsers.User')->get();
        
        return $this->view("Controllers.Oficinas.index", [
            "title" => "Gestión de Oficinas",
            "oficinas" => $oficinas
        ]);
    }
    
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errores = $this->validarOficina($_POST);
            
            if (empty($errores)) {
                $oficina = new \App\Models\Offices();
                $oficina->address = trim($_POST['address']);
                $oficina->save();
                
                $_SESSION['mensaje'] = "Oficina creada exitosamente";
                redirect('/oficinas');
            }
        }
        
        return $this->view("Controllers.Oficinas.crear", [
            "title" => "Crear Oficina",
            "errores" => $errores ?? []
        ]);
    }
    
    public function ver($id)
    {
        $oficina = \App\Models\Offices::with('OfficeUsers.User')->find($id);
        $this->page404($oficina);
        
        return $this->view("Controllers.Oficinas.ver", [
            "title" => "Oficina: " . $oficina->address,
            "oficina" => $oficina
        ]);
    }
    
    public function editar($id)
    {
        $oficina = \App\Models\Offices::find($id);
        $this->page404($oficina);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errores = $this->validarOficina($_POST, $id);
            
            if (empty($errores)) {
                $oficina->address = trim($_POST['address']);
                $oficina->save();
                
                $_SESSION['mensaje'] = "Oficina actualizada exitosamente";
                redirect('/oficinas/ver/' . $id);
            }
        }
        
        return $this->view("Controllers.Oficinas.editar", [
            "title" => "Editar Oficina",
            "oficina" => $oficina,
            "errores" => $errores ?? []
        ]);
    }
    
    public function eliminar($id)
    {
        $oficina = \App\Models\Offices::find($id);
        $this->page404($oficina);
        
        // Eliminar asignaciones primero
        \App\Models\OfficeUser::where('id_oficina', $id)->delete();
        
        // Eliminar oficina
        $oficina->delete();
        
        $_SESSION['mensaje'] = "Oficina eliminada exitosamente";
        redirect('/oficinas');
    }
    
    private function validarOficina($datos, $id = null)
    {
        $errores = [];
        
        if (empty($datos['address'])) {
            $errores[] = "La dirección es requerida";
        } elseif (strlen($datos['address']) < 5) {
            $errores[] = "La dirección debe tener al menos 5 caracteres";
        }
        
        return $errores;
    }
}
```

## 🎨 Paso 3: Vistas Blade

### Vista principal de usuarios (`App/Views/Controllers/Usuarios/index.blade.php`):

```blade
@extends('app')

@section('page-title'){{ $title }}@endsection

@section('styles')
<style>
    .mensaje { padding: 10px; margin: 10px 0; border-radius: 4px; }
    .mensaje.exito { background: #d4edda; color: #155724; }
    .mensaje.error { background: #f8d7da; color: #721c24; }
    .tabla { width: 100%; border-collapse: collapse; margin: 20px 0; }
    .tabla th, .tabla td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    .tabla th { background: #f8f9fa; }
    .btn { padding: 8px 16px; margin: 4px; text-decoration: none; border-radius: 4px; display: inline-block; }
    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: black; }
    .btn-danger { background: #dc3545; color: white; }
    .badge { padding: 4px 8px; background: #6c757d; color: white; border-radius: 12px; font-size: 12px; margin: 2px; }
</style>
@endsection

@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    
    @if(isset($_SESSION['mensaje']))
        <div class="mensaje exito">
            {{ $_SESSION['mensaje'] }}
            @php unset($_SESSION['mensaje']); @endphp
        </div>
    @endif
    
    @if(isset($_SESSION['error']))
        <div class="mensaje error">
            {{ $_SESSION['error'] }}
            @php unset($_SESSION['error']); @endphp
        </div>
    @endif
    
    <div style="margin: 20px 0;">
        <a href="/usuarios/crear" class="btn btn-primary">➕ Crear Nuevo Usuario</a>
        <a href="/oficinas" class="btn btn-success">🏢 Gestionar Oficinas</a>
    </div>
    
    @if(count($usuarios) > 0)
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Oficinas Asignadas</th>
                    <th>Fecha Creación</th>
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
                        <em>Sin oficina asignada</em>
                    @endif
                    </td>
                    <td>{{ date('d/m/Y H:i', strtotime($usuario->created_at)) }}</td>
                    <td>
                        <a href="/usuarios/ver/{{ $usuario->id }}" class="btn btn-primary">👁️ Ver</a>
                        <a href="/usuarios/editar/{{ $usuario->id }}" class="btn btn-warning">✏️ Editar</a>
                        <a href="/usuarios/eliminar/{{ $usuario->id }}" 
                           onclick="return confirm('¿Estás seguro de eliminar este usuario?')" 
                           class="btn btn-danger">🗑️ Eliminar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="mensaje">
            <p>No hay usuarios registrados. <a href="/usuarios/crear">Crear el primero</a></p>
        </div>
    @endif
</div>
@endsection
```

### Formulario crear usuario (`App/Views/Controllers/Usuarios/crear.blade.php`):

```blade
@extends('app')

@section('page-title'){{ $title }}@endsection

@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    
    <div style="margin: 20px 0;">
        <a href="/usuarios">← Volver a la lista</a>
    </div>
    
    @if(!empty($errores))
        <div class="mensaje error">
            <ul>
                @foreach($errores as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" style="max-width: 500px;">
        <div style="margin: 15px 0;">
            <label for="name">Nombre del Usuario:</label><br>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ $_POST['name'] ?? '' }}"
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                   required>
        </div>
        
        <div style="margin: 20px 0;">
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="/usuarios" class="btn" style="background: #6c757d; color: white;">Cancelar</a>
        </div>
    </form>
</div>
@endsection
```

### Vista detalle usuario (`App/Views/Controllers/Usuarios/ver.blade.php`):

```blade
@extends('app')

@section('page-title'){{ $title }}@endsection

@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    
    <div style="margin: 20px 0;">
        <a href="/usuarios">← Volver a la lista</a>
        <a href="/usuarios/editar/{{ $usuario->id }}" class="btn btn-warning">✏️ Editar</a>
    </div>
    
    @if(isset($_SESSION['mensaje']))
        <div class="mensaje exito">
            {{ $_SESSION['mensaje'] }}
            @php unset($_SESSION['mensaje']); @endphp
        </div>
    @endif
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3>Información del Usuario</h3>
        <p><strong>ID:</strong> {{ $usuario->id }}</p>
        <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
        <p><strong>Fecha de creación:</strong> {{ date('d/m/Y H:i', strtotime($usuario->created_at)) }}</p>
        <p><strong>Última actualización:</strong> {{ date('d/m/Y H:i', strtotime($usuario->updated_at)) }}</p>
    </div>
    
    <div style="margin: 30px 0;">
        <h3>Oficina Asignada</h3>
        
        @if($usuario->OfficeUsers)
            <div style="background: #e9ecef; padding: 15px; border-radius: 8px; margin: 15px 0;">
                <p><strong>Oficina Actual:</strong> {{ $usuario->OfficeUsers->Office->address }}</p>
                <p><strong>Fecha de Asignación:</strong> {{ date('d/m/Y', strtotime($usuario->OfficeUsers->created_at)) }}</p>
                <a href="/usuarios/remover-oficina/{{ $usuario->id }}"
                   onclick="return confirm('¿Remover usuario de esta oficina?')"
                   class="btn btn-danger">🗑️ Remover de Oficina</a>
            </div>
        @else
            <p><em>El usuario no está asignado a ninguna oficina.</em></p>
        @endif
        
        <h4>{{ $usuario->OfficeUsers ? 'Cambiar' : 'Asignar' }} Oficina</h4>
        @if(count($oficinasDisponibles) > 0)
            <form method="POST" action="/usuarios/asignar-oficina/{{ $usuario->id }}" style="margin: 15px 0;">
                <select name="oficina_id" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                    <option value="">Seleccionar oficina...</option>
                    @foreach($oficinasDisponibles as $oficina)
                        <option value="{{ $oficina->id }}">{{ $oficina->address }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-success">
                    {{ $usuario->OfficeUsers ? 'Cambiar' : 'Asignar' }}
                </button>
            </form>
        @else
            @if(!$usuario->OfficeUsers)
                <p><em>No hay oficinas disponibles para asignar.</em></p>
            @endif
        @endif
    </div>
</div>
@endsection
```

## 🔗 Paso 4: Rutas

El sistema funciona con URLs amigables:

```
/usuarios              → Usuarios::index()
/usuarios/crear        → Usuarios::crear()
/usuarios/ver/1        → Usuarios::ver(1)
/usuarios/editar/1     → Usuarios::editar(1)
/usuarios/eliminar/1   → Usuarios::eliminar(1)
/usuarios/asignar-oficina/1 → Usuarios::asignarOficina(1)
/usuarios/remover-oficina/1 → Usuarios::removerOficina(1)

/oficinas              → Oficinas::index()
/oficinas/crear        → Oficinas::crear()
/oficinas/ver/1        → Oficinas::ver(1)
```

## ✅ Paso 5: Probar el Sistema

1. **Importar BD**: `mysql -u usuario -p base_datos < database.sql`
2. **Acceder**: `http://tu-dominio.com/usuarios`
3. **Crear usuarios y oficinas**
4. **Probar todas las operaciones CRUD**
5. **Asignar usuarios a oficinas**

## 🔒 Características de Seguridad Incluidas

- ✅ **Validación de existencia** con `page404()`
- ✅ **Protección Mass Assignment** con `$guarded`
- ✅ **Validación de datos** antes de guardar
- ✅ **Confirmación** antes de eliminar
- ✅ **Mensajes de estado** con sesiones
- ✅ **Manejo de errores** robusto

## 🚀 Extensiones Posibles

- 📄 **Paginación** para listas grandes
- 🔍 **Búsqueda y filtros**
- 📊 **Exportación** a Excel/PDF
- 🔒 **Permisos de usuario**
- 📧 **Notificaciones por email**
- 🎨 **Interfaz más moderna** con CSS frameworks

---

**¡Ya tienes un CRUD completo funcionando! 🎉**

Este ejemplo te sirve de base para crear cualquier sistema de gestión en tu aplicación.
