<?php

class Abmsvarios extends Controller {

    private $db;
    private $especialidad;
    private $obrasocial;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/especialidad.php');
        include($this->POROTO->ModelPath . '/obrasocial.php');
        $db =  $this->POROTO->DB;
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
        $nueva=$db->dbEscape(trim($nueva));
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
    
    public function altanuevaobrasocial($nueva) {
        if (!$this->ses->tienePermiso('', 'Lista de obras sociales - Alta')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }
        $nueva=trim(strtoupper(urldecode(filter_var($nueva)))); //sanitizar datoooo! pasar a upper.
        
        if ($this->obrasocial->existeObraSocialByNombre($nueva)) {
            $existe=true;
        }else {
            $existe=false;
        }
        
        if (!$existe){
            $valores = array();
            $valores["descripcion"]=$nueva;
            if ($this->obrasocial->nuevaObraSocial($valores)) {
                $this->ses->setMessage("Se insertó la obra social exitosamente.", SessionMessageType::Success);
            } else {
                $this->ses->setMessage("Error al insertar obra social.", SessionMessageType::TransactionError);
            }
            header("Location: /abm-obrassociales", TRUE, 302);
        } else {
            $this->ses->setMessage("Ya existe la obra social que desea agregar.", SessionMessageType::TransactionError);
            header("Location: /abm-obrassociales", TRUE, 302);
        }
    }
}
