<?php

use App\Lib\Session;

/**
 * @access public 
 * Controlador encargado de servir recursos o archivos publicos y privados.
 * 
 * @author Andrés Felipe Delgado <andresfdel13@hotmail.com>
 * @package Vmers
 * @subpackage Controllers
 */
class Assets extends Controller
{
    /**
     * @var string
     * (EN) Contains the path of public resources or files.
     * (ES) Contiene la ruta de los recursos o archivos publicos.
     */
    public $path_public = DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR;
    /**
     * @var string
     * (EN) Contains the path of private resources or files.
     * (ES) Contiene la ruta de los recursos o archivos privados.
     */
    public $path_private = DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR;

    /**
     * @access public
     * Ruta de Archivos cargados
     * @var string
     */
    public $path_uploads = RUTA_UPLOAD;

    /**
     * @var array
     * (EN) Contains the extension of the supported files.
     * (ES) Contiene la extensión de los archivos soportados.
     */
    public $file_types = [
        'path',
        'img',
        'png',
        'jpg',
        'jpeg',
        'pdf',
        'js',
        'jsx',
        'json',
        'css',
        'xml',
        'eot',
        'ttf',
        'woff',
        'woff2',
        'svg',
        'dotm',
        'doc',
        'docx',
        'docm',
        'xlsx',
        'xlsm',
        'xls',
        'xml',
        'ppt',
        'pptx',
        'csv',
        'zip',
        'font'
    ];
    public function index()
    {
        redirect();
    }

    /**
     * Public
     * (EN) Method is in charge of serving resources or public files.
     * (ES) Método encarga de servir recursos o archivos de tipo publico.
     * @access public
     * @param string $file
     * (EN) Specify the url path to serve files or resources in the genesis format.
     * (ES) Especifica la ruta de url para servir archivos o recursos en el formato genesis.
     */
    public function Public(string $file = "")
    {
        if (isset($_GET['file']) && isset($_GET['type'])) {
            if (file_exists(dirname(dirname(__DIR__)) . $this->path_public . $_GET['file']) && isset($_GET['type']) && !empty($_GET['type']) && $this->fileValidate($_GET['type'])) :
                //http_response_code(202);
                $this->renderFile(dirname(dirname(__DIR__)) . $this->path_public . $_GET['file'], $_GET['type']);
            else :
                http_response_code(404);
            endif;
        } else {
            $file = ($file != "") ? explode("=>", $file) : array();
            if (count($file) > 1) {
                if (file_exists(dirname(dirname(__DIR__)) . $this->path_public . join(DIRECTORY_SEPARATOR, $file)) && isset($_GET['type']) && !empty($_GET['type']) && $this->fileValidate($_GET['type'])) :
                    http_response_code(202);
                    $this->renderFile(dirname(dirname(__DIR__)) . $this->path_public . join(DIRECTORY_SEPARATOR, $file), $_GET['type']);
                else :
                    http_response_code(404);
                endif;
            }
        }
    }
    /**
     * Private
     * (EN) Method is in charge of serving resources or private files.
     * (ES) Método encarga de servir recursos o archivos de tipo privado.
     * @access public
     * (EN) Specify the url path to serve files or resources in the genesis format.
     * (ES) Especifica la ruta de url para servir archivos o recursos en el formato genesis.
     */
    public function Private()
    {
        if ((Session::sessionValidator([], true) || (isset($_GET["file_pass"]) && $_GET["file_pass"] == "afd1094" && isset($_GET["type"]) && $_GET["type"] == "img"))) {
            if (file_exists(dirname(dirname(__DIR__)) . $this->path_private . $_GET['file']) && isset($_GET['type']) && !empty($_GET['type']) && $this->fileValidate($_GET['type'])) :
                //http_response_code(202);
                $this->renderFile(dirname(dirname(__DIR__)) . $this->path_private . $_GET['file'], $_GET['type']);
            else :
                http_response_code(404);
            endif;
        } else {
            http_response_code(401);
        }
    }
    /**
     * Access storage files
     */
    public function UploadFiles()
    {
        if (file_exists($this->path_uploads . $_GET['file']) && isset($_GET['type']) && !empty($_GET['type']) && $this->fileValidate($_GET['type'])) :
            //http_response_code(202);
            $this->renderFile($this->path_uploads . $_GET['file'], $_GET['type']);
        else :
            http_response_code(404);
        endif;
    }
    /**
     * fileValidate
     * (EN) This method is responsible for validating whether the file type.
     * (ES) Este método se encarga de validar si el type de archivo.
     * esta disponible.
     * @access public
     * @param string $type
     * @return bool
     */
    public function fileValidate(string $type)
    {
        $vigilant = false;
        foreach ($this->file_types as $key) {
            if ($key === $type) :
                $vigilant = true;
                break;
            endif;
        }

        return $vigilant;
    }
    /**
     * renderFile
     * (EN) This method is responsible for reading and setting the file type according to its extension.
     * (ES) Este método se encarga de leer y establecer el tipo archivo segun su extensión.
     * @access public
     * @param string $file
     * @param string $type
     */
    public function renderFile(string $file, $type)
    {
        switch ($type) {
            case 'img':
                header('Content-Type: image/jpeg');
                break;
            case 'pdf':
                header('Content-type: application/pdf');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'css':
                header("Content-type: text/css");
                break;
            case 'eot':
                header("Content-type: application/vnd.ms-fontobject");
                break;
            case 'ttf':
                header("Content-type: font/x-font-ttf");
                break;
            case 'woff':
                header("Content-type: font/font-woff");
                break;
            case 'woff2':
                header("Content-type: font/font-woff2");
                break;
            case 'svg':
                header("Content-type: image/svg+xml");

                break;
            default:

                switch ($type) {
                    case "zip":
                        $ctype = "application/zip";
                        break;
                    case "docx":
                        $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                        break;
                    case "doc":
                        $ctype = "application/msword";
                        break;
                    case "csv":
                    case "xls":
                        $ctype = "application/vnd.ms-excel";
                        break;
                    case "xlsx":
                        $ctype = "application/vnd.ms-excel";
                        break;
                    case "ppt":
                        $ctype = "application/vnd.ms-powerpoint";
                        break;
                    case "pptx":
                        $ctype = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
                        break;
                    default:
                        $ctype = "application/force-download";
                }

                header("Content-type: {$ctype}");
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                if (ob_get_contents() && ob_get_length() > 0) {
                    ob_end_clean();
                    flush();
                }
                break;
        }
        readfile($file);
    }
}