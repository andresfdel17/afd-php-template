<?php

use Jenssegers\Blade\Blade;

/**
 * @access public
 * Clase Core
 * @author Andrés Felipe Delgado <andresfdel13@hotmail.com>
 */
class Core
{
    /**
     *
     * (EN) Current method for the application during execution.
     * (ES) Define el método actual de la aplicación durante la ejecución.
     */
    protected $currentController;

    /**
     *
     * (EN) Current method for the application during execution.
     * (ES) Define el método actual de la aplicación durante la ejecución.
     */
    protected $currentMethod;

    /**
     *  @var array
     * (EN) url parameters.
     * (ES) parámetros url.
     */
    protected $parameters = [];

    /**
     * @var string
     * (EN) Controllers Route
     * (ES) Ruta de controladores
     */
    protected $ControllerRoute = "";

    /**
     * @access public
     * Arreglo de carpetas del sistema en la Raiz
     * @var array
     */
    public $folders = [
        "Uploads",
        "Uploads" . DIRECTORY_SEPARATOR . "img",
        "Uploads" . DIRECTORY_SEPARATOR . "pdf"
    ];
    public function __construct()
    {
        $this->currentController = $_ENV['APP_CONTROLLER'];
        $this->currentMethod = $_ENV['APP_INDEX'];
        $this->ControllerRoute = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR;
        try {
            $this->SetSystemFolders();
            $this->errorTry();
            $this->hostUri();
            $url = $this->getUrl();
            //(EN) Search in Controllers if the called controller exists.
            //(ES) Buscar en Controladores si el controlador llamado existe.
            
            if (file_exists($this->ControllerRoute . ucwords($url[0]) . '.php')) {
                // (EN) If the controller exists it is set as the default controller.
                // (ES) Si el controlador existe se setea como controlador por defecto.
                $this->currentController = ucwords($url[0]);
                //Unset index
                unset($url[0]);
                require_once $this->ControllerRoute . ucwords($this->currentController) . '.php';
                $this->currentController = new $this->currentController;
            } 
            // (EN) Check the second part of the url, the method the action.
            // (ES) Chequear la segunda parte de la url, el método la acción.
            if (isset($url[1])) {
                if (method_exists($this->currentController, $url[1])) {
                    //Chequeamos el método
                    $this->currentMethod = $url[1];
                    unset($url[1]);
                }
            }
            // (EN) Get parameters
            // (ES) Obtener parametros
            $this->parameters = $url ? array_values($url) : [];
            // (EN) Callback with an array of parameters.
            // (ES) Llamada de retorno con un array de parámetros.
            call_user_func_array([$this->currentController, $this->currentMethod], $this->parameters);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * getUrl
     * (EN) This method is responsible for mapping the url variable and setting its values ​​in an array.
     * (ES) Este método se encarga de mapear la variable url y setear sus valores en un arraeglo.
     * @access public
     * @return array
     */
    public function getUrl(): array
    {

        if (isset($_GET['url'])) {
            // (EN) We clean the spaces that are to the right of the url.
            // (ES) Limpiamos los espacios que estan a la derecha de la url.
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        } else {
            $url = urldecode(
                parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
            );

            $url = rtrim($url, '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            $url = array_values($url);
            unset($url[0]);
            $url = array_values($url);
            if (empty($url)) {
                $url[0] = $_ENV['APP_CONTROLLER'];
            }
        }
        return array_values($url);
    }

    /**
     *hostUri()
     * (EN) This method verifies the environment server.
     * (ES) Este método Verifica el servidor de entorno.
     * @access public 
     *  
     */
    public function hostUri()
    {
        //(EN) Default url of the application.
        $set = explode("://", $_ENV['APP_URL']);
        if ($set[1] != $_SERVER['HTTP_HOST']) {
            throw new ErrorException("Your APP URL is wrong");
        }
    }

    /**
     * errorTry
     * (EN) This method is responsible for capturing all errors, warnings and news generated during execution.
     * (ES) Este método se encarga de capturar todos los errores, advertencias y noticias generadas durante la ejecución.
     * @access public 
     */
    public function errorTry()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (0 === error_reporting()) {
                return false;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    /**
     * @access public
     * Método para crear las carpetas del GitIgnore
     */
    public function SetSystemFolders()
    {
        foreach ($this->folders as $key => $value) {
            if (!file_exists(RUTA_APP . DIRECTORY_SEPARATOR . $value)) {
                mkdir(RUTA_APP . DIRECTORY_SEPARATOR . $value, 0777, true);
            }
        }
    }


    /**
     * @access public
     * VIsta de mantenimiento
     */
    public function MantMode()
    {
        if (config()->MANT == "true") {
            $this->view("AdminPages.Maintenance", [
                "title" =>  "Mantenimiento"
            ], "", false, false, false);
            die;
        }
    }

    /**
     * 
     * view
     * (EN) This method is responsible for rendering the views.
     * (ES) Este método se encarga de renderizar las vistas.
     * @param string $view
     * (EN) Name and path of the view file
     * (ES) Nombre y ruta del archivo de vista
     * @param array  $data
     * (EN) Array containing variables that will be passed to the view.
     * (ES) Array que contiene variables que seran pasadas a la vista.
     */
    public function view(string $view, array $data = [], bool $minify = false, bool $debug = false, bool $validate = true)
    {
        //Se validan los filtros de acceso
        if ($validate) {
            $this->MantMode();
        }
        $blade = new Blade(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Views', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'cache');
        $render = ($minify != false ? $blade->make($view, $data)->render() : html_minify($blade->make($view, $data)->render()));
        echo $render;
        if (!$debug) rmdir_(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'cache', true);
    }
}
