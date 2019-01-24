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


        //CONTINUA DE ACA PARA ABAJO.

//        if ($this->permisos->setpersonapermiso($idpersona, $idpermiso, $estado)) {
//            $this->ses->setMessage("El permiso se cambio exitosamente.", SessionMessageType::Success);
//        } else {
//            $this->ses->setMessage("Error al cambiar el permiso.", SessionMessageType::TransactionError);
//        }
        header("Location: /permisos/detalle/$idpersona", TRUE, 302);
    }
}
