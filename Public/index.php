<?php
/**
 * Definir versión de PHP
 */
header("X-Powered-By:PHP/" . phpversion());
/**
 * Definir idioma
 */
date_default_timezone_set("America/Bogota");
setlocale(LC_ALL, "es_ES.UTF-8");
/**
 * Starter Framework
 * @package Starter
 * @author Andrés Felipe Delgado, <andresfdel13@hotmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader Composer
|--------------------------------------------------------------------------
|
| (ES) Cargador de clases mediante composer para toda la aplicacion
|
*/
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}

/*
|--------------------------------------------------------------------------
| Application settings
|--------------------------------------------------------------------------
|
| (EN) Here are the application settings
| (ES) Aquí se incluyen las configuraciones de la aplicacion
|
*/
if (file_exists('../App/Config/Config.php')) {
    require '../App/Config/Config.php';
}

/*
|--------------------------------------------------------------------------
| Register Autoloader
|--------------------------------------------------------------------------
|
| (EN) Automatic class loader
| (ES) Cargador de clases automatico
|
*/
spl_autoload_register(function ($className) {

    if (file_exists('../App/Lib/' . str_replace('\\', '/', $className) .  '.php')) {
        require_once '../App/Lib/' . str_replace('\\', '/', $className) .  '.php';
    } else if (file_exists('../App/Models/' . str_replace('\\', '/', $className) .  '.php')) {
        require_once '../App/Models/' . str_replace('\\', '/', $className) .  '.php';
    } else {
        $class = explode('\\', $className);
        if (file_exists(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . str_replace('\\', '/',  join("\\", array_unique($class))) .  '.php')) :
            if ($className != 'int') :
                require_once  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . str_replace('\\', '/',  join("\\", array_unique($class))) .  '.php';
            endif;
        endif;
    }
});
/*
| (ES) inicia en una sola linea
*/
if (config()->PHP_SCREEN == "true") {
    phpinfo();
} else {
    $start = new Core;
}