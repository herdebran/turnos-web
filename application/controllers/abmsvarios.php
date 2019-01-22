<?php

class Abmsvarios extends Controller {

    private $especialidad;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/especialidad.php');
        $this->especialidad = new ModeloEspecialidad($this->POROTO);
    }

    public function listarEspecialidades() {
        $especialidades = $this->especialidad->getAllEspecialidades();
        $json = array("data" => $especialidades);

        echo json_encode($json);
    }
    
    function abrirlistaespecialidades() {
        if (!$this->ses->tienePermiso('', 'Lista de especialidades - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

// ---------------------- Logica del metodo ----------------------------
        $params = array();
        $params['pageTitle'] = "Lista de especialidades";
// ---------------------- Fin logica del metodo ------------------------
        $this->render("/abms-varios.php", $params);
    }
}
