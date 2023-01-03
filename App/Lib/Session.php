<?php

namespace App\Lib;

use App\Models\Users;

/**
 * (EN) management class of session status
 * (ES) clase para manejar sesiones
 * @author Andrés Felipe Delgado
 * 
 */
class Session
{
    /**
     * SessionStart()
     * @access public 
     * (ES) Método que inicia sesíon 
     * @param object $user
     * Datos de usuario a guardar en variable global sesion
     */
    public static function SessionStart(object $user)
    {
        if (session_status() == 1) {
            self::SetSessionCookie();
            session_cache_expire(SESSION_TIME);
            session_start();
            session_regenerate_id(true);
        }
        $_SESSION['id']           = $user->id;
        $_SESSION['login_status'] = 1;
        if (count($_SESSION) == 7) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @access public
     * Valida si está logueado en los controladores publicos
     */
    public static function islogged()
    {
        if (session_status() == 1) {
            self::SetSessionCookie();
            session_cache_expire(SESSION_TIME);
            session_start();
        }
        if (isset($_SESSION) && count($_SESSION) > 0) {
            redirect("/" . config()->HOME);
        }
    }
    /**
     * sessionValidator()
     * @access public 
     * (ES) Método que valida el token del cliente y inicia el ORM
     */
    public static function sessionValidator(array $exc = [], bool $validate = false)
    {
        if (session_status() == 1) {
            self::SetSessionCookie();
            session_cache_expire(SESSION_TIME);
            session_start();
        }        
        if (isset(self::getUrl()[1]) && strpos(self::getUrl()[1], "cron") !== false) {
            //Corre crontab
        } elseif (!empty($exc) && isset(self::getUrl()[1])) {
            foreach ($exc as $key => $value) {
                if (self::getUrl()[1] == $value) {
                } else {
                    continue;
                }
            }
        } else {
            if ((!$validate && count($_SESSION) == 0)) {
                if ($_SERVER['REQUEST_METHOD'] == "POST") {
                    http_response_code(401);
                    die;
                } else {
                    redirect();
                }
            } else if (($validate && count($_SESSION) > 0)) {
                return true;
            } else if (($validate && count($_SESSION) == 0)) {
                return false;
            } else {
                return false;
            }
        }
    }
    /**
     * sessionDestroy()
     * @access public
     * Método que destruye la session
     */
    public static function sessionDestroy(bool $redirect = true)
    {
        if (session_status() == 1) {
            session_start();
        }
        $_SESSION = [];
        if (count($_SESSION) == 0) {
            session_destroy();
        }
        if ($redirect) redirect();
    }
    /**
     * @access public
     * Genera un array de La URL
     */
    public static function getUrl()
    {
        //echo $_GET['url'];
        if (isset($_GET['url'])) {
            //Limpiamos los espacios que estan a la derecha de la url
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }

    /**
     * @access public
     * Método que crea la cookie del referido
     * @param int 
     * Id del referido
     */
    public static function SetRefCookie(int $id = null, bool $delete = false)
    {
        date_default_timezone_set("America/Bogota");

        if ($delete && isset($_COOKIE['aff_value'])) {
            setcookie("aff_value", null, [
                "expires" => (-1),
                "path" => "/",
            ]);
            unset($_COOKIE['aff_value']);
        }
        if ($id != null) {
            setcookie("aff_value", $id, [
                "expires" => (time() + 7200),
                "path" => "/",
                "secure" => (isset($_SERVER['HTTPS']) ? true : false),
                "httponly" => true,
                "samesite" => "Strict"
            ]);
            redirect("/");
        }
    }

    /**
     * @access public
     * Setea la cookie para la sesión
     */
    public static function SetSessionCookie(){
        session_set_cookie_params([
            "lifetime" => config()->SESSION_TIME,
            "path" => "/",
            "secure" => (isset($_SERVER['HTTPS']) ? true : false),
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }
}