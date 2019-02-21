<?php

class Abmsvarios extends Controller {

    private $especialidad;
    private $obrasocial;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/especialidad.php');
        include($this->POROTO->ModelPath . '/obrasocial.php');
        $this->especialidad = new ModeloEspecialidad($this->POROTO);
        $this->obrasocial = new ModeloObraSocial($this->POROTO);
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
        if ($this->especialidad->existeEspecialidadByNombre($nueva)) {
            $existe=true;
        }else {
            $existe=false;
        }
        
        if (!$existe){
            $valores = array();
            $valores["descripcion"]=$nueva;
            if ($this->especialidad->nuevaEspecialidad($valores)) {
                $this->ses->setMessage("Se insertó la especialidad exitosamente.", SessionMessageType::Success);
            } else {
                $this->ses->setMessage("Error al insertar especialidad.", SessionMessageType::TransactionError);
            }
            header("Location: /abm-especialidades", TRUE, 302);
        } else {
            $this->ses->setMessage("Ya existe el elemento que desea agregar.", SessionMessageType::TransactionError);
            header("Location: /abm-especialidades", TRUE, 302);
        }
    }
    public function desactivarespecialidad($id, $valor) {
        if (!$this->ses->tienePermiso('', 'Lista de especialidades - Desactivar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $resultado=$this->especialidad->setearActivoEspecialidad($id,$valor);
        if ($resultado["ok"]) {
            $this->ses->setMessage($resultado["message"] , SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al cambiar valor en especialidad.", SessionMessageType::TransactionError);
        }
        header("Location: /abm-especialidades", TRUE, 302);
    }
    
    public function abrirlistaobrassociales() {
        if (!$this->ses->tienePermiso('', 'Lista de obras sociales - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $params = array();
        $params['pageTitle'] = "Lista de Obras Sociales";
        $params['tipo'] = "os";
        $this->render("/abms-varios.php", $params);
    }    
    
      public function listarObrasSociales() {
        $ooss = $this->obrasocial->getAllObrasSociales();
        $json = array("data" => $ooss);

        echo json_encode($json);
    }
}
