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
    
    public function abrirlistaespecialidades() {
        if (!$this->ses->tienePermiso('', 'Lista de especialidades - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $params = array();
        $params['pageTitle'] = "Lista de especialidades";
        $params['tipo'] = "esp";
        $this->render("/abms-varios.php", $params);
    }
    
    public function altanuevaespecialidad($nueva) {
        if (!$this->ses->tienePermiso('', 'Lista de especialidades - Alta')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $valores = array();
        $valores["descripcion"]=$nueva;
        if ($this->especialidad->nuevaEspecialidad($valores)) {
            $this->ses->setMessage("Se insertó la especialidad exitosamente.", SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al insertar especialidad.", SessionMessageType::TransactionError);
        }
        header("Location: /abm-especialidades", TRUE, 302);
    }
    public function desactivarespecialidad($id) {
        if (!$this->ses->tienePermiso('', 'Lista de especialidades - Desactivar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $resultado=$this->especialidad->desactivarEspecialidad($id);
        if ($resultado["ok"]) {
            $this->ses->setMessage($resultado["message"] , SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al desactivar especialidad.", SessionMessageType::TransactionError);
        }
        header("Location: /abm-especialidades", TRUE, 302);
    }
}
