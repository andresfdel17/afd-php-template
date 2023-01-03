<?php

use Illuminate\Database\Capsule\Manager as DB;
/*
|-------------------------------------------------------------------
| (EN) Load environment variables
| (ES) Carga de variables de entorno
|-------------------------------------------------------------------
|
| (EN) Dotenv class instance for using environment variables
| (ES) Instancia de la clase Dotenv para el uso de variables de entorno
*/

$env = Dotenv\Dotenv::createMutable(dirname(dirname(__FILE__)) . '../../');
$env->load();

/*
|-------------------------------------------------------------------
| (EN) Boot Eloquent ORM
| (ES) Iniciar conexión a base de datos
|-------------------------------------------------------------------
|
|
*/
$capsule = new DB;
$capsule->addConnection([
    'driver'    => $_ENV['DB_DRIVER'],
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => $_ENV['DB_CHARSET'],
    'collation' => $_ENV['DB_COLLATION'],
    'prefix'    => '',
], "default");
$capsule->setAsGlobal();
$capsule->bootEloquent();

/*
|-------------------------------------------------------------------
| (EN) Debug Mode
| (ES) Modo depuración
|-------------------------------------------------------------------
|
| (EN) Validate if debug mode is active 
| (ES) Validar si el modo depuración está activo.
|
*/
if ($_ENV['APP_DEBUG'] == "true") :
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
endif;
ini_set("memory_limit", "128M");


/*
|-------------------------------------------------------------------
| (EN) Helpers Funtions
| (ES) Funciones Auxiliares
|-------------------------------------------------------------------
|
*/
require_once dirname(dirname(__FILE__)) . '/Lib/Helpers.php';

/*
|-------------------------------------------------------------------
| (ES) Otras constantes
|-------------------------------------------------------------------
|
*/
//Ruta de la app
define('RUTA_APP', dirname(dirname(__FILE__)));
//Ruta de logs
define("RUTA_LOGS", RUTA_APP . DIRECTORY_SEPARATOR . 'Logs' . DIRECTORY_SEPARATOR);
//Ruta de directorio para la carga de archivos
define('RUTA_UPLOAD', RUTA_APP . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR);
//Ruta de directorio para la carga de archivos
define('RUTA_REPORTS', RUTA_APP . DIRECTORY_SEPARATOR . 'Reports' . DIRECTORY_SEPARATOR);
//Ruta Plugins
define('RUTA_PLUGINS', RUTA_APP . DIRECTORY_SEPARATOR . 'Plugins' . DIRECTORY_SEPARATOR);
//Ruta Publica
define('RUTA_PUBLIC', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "Public" . DIRECTORY_SEPARATOR);
//Método para definir la sesion
define('__AUTH__', 'session');
//Cookie de sesion
define('SESSION_TIME', 7200);

/*
|-------------------------------------------------------------------
| Tiempo de sesión
*/
ini_set("session.cookie_lifetime", SESSION_TIME);
ini_set("session.gc_maxlifetime", SESSION_TIME);