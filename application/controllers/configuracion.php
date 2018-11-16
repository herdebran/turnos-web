<?php

class configuracion extends Controller {

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->LibrariesPath . 'fpdf181/fpdf.php');
    }

    function getConfiguracionUsuario() {
        if (!$this->ses->tienePermiso('', 'Ver Permisos y Configuracion')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }
        $persona["nombrecompleto"] = $this->ses->getApellido() . ", " . $this->ses->getNombre();
        $persona["usuario"] = $this->ses->getUsuario();
        $persona["rolname"] = $this->ses->getRoleName();
        $permisosAsignados = $this->ses->getPermisos();
        //$configuraciones = $this->app->getAllConfiguraciones();
        $configuraciones= $this->ses->getConfiguracion();   


        // ---------------------- Logica del metodo ----------------------------
        $params = array();
        $params['pageTitle'] = "Configuracion";
        $params['persona'] = $persona;
        $params['permisos'] = $permisosAsignados;
        $params['configuraciones'] = $configuraciones;
        // ---------------------- Fin logica del metodo ------------------------
        $this->render("/configuracion-usuario.php", $params);
    }

    public function ajaxswitchconf() {
        $params = $_POST["params"];
        $response = array();
        $response["ok"] = false;
        try {
            $config = $this->app->getConfiguracionByParametro($params["parametro"]);
            
            if (($params["valor"] == "N" || $params["valor"] == "Y") && $params["valor"] != $config["valor"]) {
                $this->app->changeConfigurationValue($params);
                $response["message"] = "La configuracion se cambio correctamente";
                $response["ok"] = true;
                
                //Actualizo las configuraciones en la sesion actual.
                $this->ses->clearConfiguracion();
                $results = $this->app->getAllConfiguraciones();
                foreach ($results as $conf) {
                      $this->ses->agregarConfiguracion($conf["parametro"],$conf["valor"]);
                }
            } else {
                $response["message"] = "La configuracion no se pudo actualizar";
            }
        } catch (Exception $e) {
            $response["message"] = "Ocurrio un error: " . $e->getMessage();
        }

        echo json_encode($response);
    }

}
