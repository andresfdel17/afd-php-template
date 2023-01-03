<?php
/**
 * Main Controller
 * @package App
 * @author Andrés Felipe Delgado <andresfdel13@hotmail.com>
 */
class Controller extends Core {
   
    /**
     * @access public
     * Este constructor debe ir
     */
    public function __construct()
    {}
    /**
     * @access public
     * Método que valida registros nulos
     * @param mixed $dato
     * Dato a validar
     * @param bool $module
     * Si es vista de módulo
     */
    public function page404($dato, $module = false)
    {
        if (empty($dato) || is_null($dato) || $dato == "") {
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                http_response_code(404);
                die;
            } else {
                return $this->view("AdminPages.404", array(
                    'title'   => 'Oops',
                    'message' => "Este registro no existe",
                    "trouble" => "Registro inexistente",
                )) .
                    die();
            }
        }
    }
}