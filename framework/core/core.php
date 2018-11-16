<?php

/**
 * Poroto Core class 
 *
 * This is the heart of the framework. Everything runs through this class. Any
 * other class of the framework is accessed from this one.
 * There are tow configuration files associated: 
 *   /site.application/config/config.php {framework general configuration}
 *   /site.application/config/route.php {routing rules}
 *
 * @package  poroto
 * @version  1.2
 * @access   public
 * @copyright 2015-2017 7dedos
 * @author Augusto Wloch <agosto@7dedos.com>
 */
if (!defined('POROTO'))
    exit('No direct script access allowed');

class Core {

    public $ControllerPath;
    public $MailPath;
    public $ViewPath;
    public $LibrariesPath;
    public $ConfigPath;
    public $CorePath;
    public $TempPath;
    public $LookAndFeelPath;
    public $Config;
    public $Routes;
    public $URI;
    public $ControllerName;
    public $ControllerFunctionName;
    public $Benchmark;
    public $DB;
    public $PDO;
    public $Session;
    public $Notifications; //user notifications. array de ["id","stamp","text","uri"]
    public $Libraries;
    public $BaseURL;
    public $currentMenu; //indica en el header, que menu esta pre-seleccionado
    public $pageHeader; //indica en el header, el titulo para la pagina

    public function __construct() {
        $this->pageHeader = array();
        $this->ControllerPath = APPLICATIONPATH . 'controllers/';
        require_once $this->ControllerPath . 'Controller.php';
        $this->ModelPath = APPLICATIONPATH . 'models/';
        $this->MailPath = APPLICATIONPATH . 'mail/';
        $this->ViewPath = APPLICATIONPATH . 'views/';
        $this->LibrariesPath = APPLICATIONPATH . 'libraries/';
        $this->ConfigPath = APPLICATIONPATH . 'config/';
        $this->CorePath = FRAMEWORKPATH . 'core/';
        $this->LookAndFeelPath = FRAMEWORKPATH . 'look-n-feel/';
        $this->TempPath = TEMPORARYPATH;
        $this->WebRootPath = WEBROOTPATH;
        $this->Notifications = array();
        require_once $this->ConfigPath . 'config.php';
        $this->Config = $config;
        require_once $this->ConfigPath . 'route.php';
        $this->Routes = $route;
        require_once $this->CorePath . 'benchmark.php';
        $this->Benchmark = new Benchmark();
        require_once $this->CorePath . 'databases.php';
        $this->DB = new Databases($this);
        require_once $this->CorePath . 'databasespdo.php';
        $this->PDO = new Databasespdo($this);
        require_once $this->CorePath . 'session.php';
        $this->Session = new Session($this);
        date_default_timezone_set($this->Config['default_timezone']);
        $this->BaseURL = $this->Config['base_url'];

        $this->Benchmark->addBenchmark("poroto core constructor init");
        $this->processURI();

        //auto-load de librerias
        foreach ($this->Config['autoload_libraries'] as $k) {
            $this->loadLibrary($k);
        }
    }

    public function loadLibrary($libraryName) {
        include $this->LibrariesPath . $libraryName . ".php";
        $this->Libraries[$libraryName] = new $libraryName($this);
    }

    private function processURI() {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
        $parts = preg_split('#\?#i', $uri, 2);

        $uri = $parts[0];
        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        } else {
            $_SERVER['QUERY_STRING'] = '';
            $_GET = array();
        }
        if ($uri == '')
            $uri = '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        $bad = array('$', '(', ')', '%28', '%29');
        $good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');
        $this->URI = array();
        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $uri)) as $val) {
            $val = str_replace($bad, $good, $val);
            if ($val != '')
                $this->URI[] = $val;
        }

        $this->parse_routes();

        if (isset($this->URI[0])) {
            $this->ControllerName = $this->URI[0];
        } else {
            $this->ControllerName = $this->Config['default_controller'];
        }

        if (isset($this->URI[1])) {
            $this->ControllerFunctionName = $this->URI[1];
        } else {
            $this->ControllerFunctionName = $this->Config['default_controller_function'];
        }
    }

    private function parse_routes() {
        // Turn the segment array into a URI string
        $uri = strtolower(implode('/', $this->URI));

        // Is there a literal match?  If so we're done
        if (isset($this->Routes[$uri])) {
            $this->URI = array();
            foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->Routes[$uri])) as $val) {
                if ($val != '')
                    $this->URI[] = $val;
            }
            return true;
        }

        // Loop through the route array looking for wild-cards
        foreach ($this->Routes as $key => $val) {
            // Convert wild-cards to RegEx
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

            // Does the RegEx match?
            if (preg_match('#^' . $key . '$#', $uri)) {
                // Do we have a back-reference?
                // echo strpos($key, '(');
                if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE) {
                    $val = preg_replace('#^' . $key . '$#', $val, $uri);
                }

                $this->URI = array();
                foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $val)) as $v) {
                    if ($v != '')
                        $this->URI[] = $v;
                }
                return true;
            }
        }
    }

    public function getHtmlTableFromArray($arr) {
        $out = "<table>\n";
        $out .= "<thead><tr>\n";
        foreach ($arr[0] as $k => $v) {
            $out .= "<th>" . $k . "</th>\n";
        }
        $out .= "</tr></thead>\n";
        $out .= "<tbody>\n";
        foreach ($arr as $row) {
            $out .= "<tr>\n";
            foreach ($row as $v) {
                $out .= "<td>" . $v . "</td>\n";
            }
            $out .= "</tr>\n";
        }
        $out .= "</tbody>\n";
        $out .= "</table>\n";
        return($out);
    }

    public function runControllerFunction() {


        if (!file_exists($this->ControllerPath . $this->ControllerName . '.php')) {
            $this->show_error('Unable to load your controller. Please make sure the controller file specified is valid.');
        }

        include $this->ControllerPath . $this->ControllerName . '.php';
        if (!class_exists($this->ControllerName)) {
            $this->show_error('Unable to load your controller. Please make sure the controller class is defined it\'s file.');
        }
        $obj = new $this->ControllerName($this);

        $methods = get_class_methods($obj);
        if (isset($this->Config['controler_case_sensitive']) && $this->Config['controler_case_sensitive']) {
            if (!in_array($this->ControllerFunctionName, $methods))
                $this->show_404();
        } else {
            $esta = false;
            foreach ($methods as $method) {
                if (strtolower($this->ControllerFunctionName) == strtolower($method))
                    $esta = true;
            }
            if (!$esta)
                $this->show_404();
        }
        call_user_func_array(array(&$obj, $this->ControllerFunctionName), array_slice($this->URI, 2));
    }

    function log2file($filename, $text, $caller) {
        file_put_contents($filename, date('Y.m.d H.i.s') . " [" . $caller . "] " . $text . "\n", FILE_APPEND);
    }

    public function show_error($description) {
        $errorDescription = $description;
        include($this->ViewPath . "/-error.php");
        exit();
    }

    public function show_404() {
        header("HTTP/1.0 404 Not Found");
        $errorDescription = "La p&aacute;gina solicitada no existe";
        include($this->ViewPath . "/-error.php");
        exit();
    }

    public function getPaswordHash($pwd) {
        $options = array('cost' => 11/* , 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM) */);
        return (password_hash($pwd, PASSWORD_BCRYPT, $options));
    }

    public function isPasswordOk($pwd, $hash) {
        return (password_verify($pwd, $hash));
    }

    public function getMenuSqlQuery() {
        $sql= " select distinct m.idMenu,m.nombre, m.accion as formulario from menu m inner join menupermiso mp on m.idMenu=mp.idMenu ";
        $sql.=" where mp.idpermiso in ( ";
        $sql.=" select idpermiso from personapermiso where idpersona=". $this->Session->getIdPersona();
        $sql.=" union all select idpermiso from permisorol where idrol=". $this->Session->getIdRole();
        $sql.=" ) and m.activo=1 order by m.nombre asc";
        
        return ($sql);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function start() {
        if (isset($this->Config['show_poroto_look_and_feel']) && $this->Config['show_poroto_look_and_feel']) {
            echo '<!DOCTYPE html>' . "\n" . '<html>' . "\n" . '<head>' . "\n" . '  <meta charset="utf-8" />' . "\n";
            echo '  <title>POROTO</title>' . "\n" . '  <style type="text/css">' . "\n";
            include $this->LookAndFeelPath . $this->Config['css_poroto_look_and_feel'] . '.php';
            echo '  </style>' . "\n" . '</head>' . "\n" . '<body>' . "\n" . '  <section id="content">' . "\n" . '    <article>' . "\n";
        }

        $this->Benchmark->addBenchmark("connecting db");
        $this->runControllerFunction();
        $this->Benchmark->addBenchmark("closing");


        if (isset($this->Config['show_poroto_look_and_feel']) && $this->Config['show_poroto_look_and_feel']) {
            echo '    </article>' . "\n" . '  </section>' . "\n" . '  <footer>' . "\n";
        }


        if (isset($this->Config['show_stats_at_foot']) && $this->Config['show_stats_at_foot']) {
            echo '<div class="poroto-footer">' . "\n";
            echo '<p>POROTO ' . POROTO . ' sculpted this page in <span class="poroto-seconds poroto-hover-show">' . $this->Benchmark->getTotalTime() . '</span>' . "\n";
            //echo '<div class="poroto-hoveree">' . $this->getHtmlTableFromArray($this->getBenchmarksArray()) . '</div>' . "\n";
            echo "</p></div>\n";
        }

        if (isset($this->Config['show_poroto_look_and_feel']) && $this->Config['show_poroto_look_and_feel']) {
            echo '  </footer>' . "\n" . '</body>' . "\n" . '</html>' . "\n";
        }
    }

}
