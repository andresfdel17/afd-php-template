<?php
/**
 * Helpers
 * @author Andrés Felipe Delgado Gutierrez <andresfdel13@hotmail.com>
 */
if (!function_exists('html_minify')) {
    /**
     * (EN) Html minify
     * (ES) Minificaodr de código html
     */
    function html_minify($html)
    {
        $busca     = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $reemplaza = array('>', '<', '\\1');
        return preg_replace($busca, $reemplaza, $html);
    }
}
if (!function_exists('rmdir_')) {
    /**
     */
    function rmdir_($path, bool $views = false)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException("{$path} is not a directory");
        }
        if (substr($path, strlen($path) - 1, 1) != '/') {
            $path .= '/';
        }
        $dotfiles = glob($path . '.*', GLOB_MARK);
        $files    = glob($path . '*', GLOB_MARK);
        $files    = array_merge($files, $dotfiles);
        foreach ($files as $file) {
            if (in_array(basename($file), [".", "..", ".gitkeep"])) {
                continue;
            } else if (is_dir($file)) {
                rmdir_($file);
            } else {
                unlink($file);
            }
        }
        if (!$views && rmdir($path)) {
            return true;
        } else if ($views) {
            return true;
        }
    }
}
if (!function_exists('redirect')) {
    /**
     * (EN) Redirect.
     * (ES) Redireccionar.
     * @param  string  $page
     * 
     */
    function redirect($page = null)
    {
        header("Location: " . $_ENV['APP_URL'] . $page ?? '/');
    }
}
if (!function_exists('config')) {
    /**
     */
    function config()
    {
        $_ENV['APP_LANG'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? "es", 0, 2);
        return (object) $_ENV;
    }
}
if (!function_exists('print_debug')) {
    /**
     */
    function print_debug($data = null, bool $dump = false)
    {
        if ($dump) {
            var_dump($data ?? $_POST);
        } else {
            echo "<pre>";
            print_r($data ?? $_POST);
            echo "</pre>";
        }
        die;
    }
}
if (!function_exists('RandomString')) {
    function RandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
if (!function_exists("getIP")) {
    function getIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}