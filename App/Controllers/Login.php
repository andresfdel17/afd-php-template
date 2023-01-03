<?php

/**
 * @access public 
 * Login controller que sirve para manejar los inicios de sesión
 * @package Template
 * @subpackage Controllers
 */
class Login extends Controller
{
    public function index()
    {
        return $this->view("Controllers.Login.index", [
            "title" => config()->APP_NAME
        ]);
    }
}
