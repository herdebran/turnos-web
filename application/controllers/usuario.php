<?php

class Usuario extends Controller {

    private $usuario;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/usuario.php');
        $this->usuario = new ModeloUsuario($this->POROTO);
    }

    private function cargarDatosAltaUsuario() {
        $usuario['idpersona'] = filter_input(INPUT_POST, 'idpersona');
        $usuario['usuario'] = filter_input(INPUT_POST, 'usuario');
        $usuario['password'] = filter_input(INPUT_POST, 'password');
        $usuario['estado'] = filter_input(INPUT_POST, 'estado');
        $usuario['primeracceso'] = filter_input(INPUT_POST, 'primeracceso');

        return $usuario;
    }


    /**
     * Valida los campos de un usuario.
     * @param type $persona
     * @return string
     */
    private function validacionDatosUsuario($usuario) {
        $validationErrors = array();
        if ($usuario['idpersona'] == "" || $usuario['idpersona'] == 0)
            $validationErrors['idpersona'] = "Es obligatorio elegir una persona para crear su usuario.";
        if ($usuario['usuario'] == "")
            $validationErrors['usuario'] = "El campo Usuario es obligatorio";
        if ($usuario['password'] == "")
            $validationErrors['password'] = "El campo Password es obligatorio";
        if (strlen($usuario['password']) < 8)
            $validationErrors['password'] = "El campo Password debe contener al menos 8 caracteres.";

        return $validationErrors;
    }
    public function crearusuario($idusuario = null) { 
        $validationErrors = array();
        $db = & $this->POROTO->DB;
        $ses = & $this->POROTO->Session;
        $db->dbConnect("usuario/crearusuario");

        if (!$ses->tienePermiso('', 'Crear usuario')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        }

        $params = array();
        if (isset($_SESSION['persona_form_error']) && $_SESSION['persona_form_error']) {
            $params['validationErrors'] = $_SESSION['persona_form_alerts'];
            unset($_SESSION['persona_form_alerts']);
            unset($_SESSION['persona_form_error']);
        } else {
            $params['validationErrors'] = [];
        }
        if ($idusuario == null) {
            //ALTA DE UNA NUEVO USUARIO
            
            $usuario = $this->cargarDatosAltaUsuario();
            $params['pageTitle'] = "Crear Usuario";
            $params['btnText'] = "Crear";
        } else {
            
            //EDICION DE UN USUARIO EXISTENTE
            $usuario = $this->usuario->getUsuarioByIdPersona($idpersona);
            $params['pageTitle'] = "Modificar Usuario";
            $params['btnText'] = "Modificar";
        }
        
        $params['usuario'] = $usuario;
        
        $this->render("crear-usuario.php", $params);
    }

}

?>