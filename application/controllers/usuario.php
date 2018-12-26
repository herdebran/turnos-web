<?php

class Usuario extends Controller {

    private $usuario;
    private $persona;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/usuario.php');
        include($this->POROTO->ModelPath . '/persona.php');
        $this->usuario = new ModeloUsuario($this->POROTO);
        $this->persona = new ModeloPersona($this->POROTO);
    }

    private function cargarDatosAltaUsuario($edicion) {
        $usuario['idpersona'] = filter_input(INPUT_POST, 'idpersona');
        $usuario['password'] = filter_input(INPUT_POST, 'password');
        $usuario['repitepassword'] = filter_input(INPUT_POST, 'repitepassword');
        if (! $edicion)
            $usuario['usuario'] = filter_input(INPUT_POST, 'usuario');
        else
            $usuario['usuario']=$edicion['usuario']!=null?$edicion['usuario']:'';

        return $usuario;
    }

    private function crearUsuarioEnBlanco() {
        $usuario = array();
        $usuario['idpersona'] = '';
        $usuario['usuario'] = '';
        $usuario['password'] = '';
        $usuario['repitepassword'] ='';

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
        if ($usuario['repitepassword'] == "")
            $validationErrors['repitepassword'] = "Debe repetir su Password obligatoriamente";
        if ($usuario['password'] != $usuario['repitepassword'])
            $validationErrors['password'] = "Las contraseñas no coinciden. Intente nuevamente.";
        if (strlen($usuario['password']) < 8 || strlen($usuario['repitepassword']) < 8)
            $validationErrors['password'] = "El campo Password debe contener al menos 8 caracteres.";

        return $validationErrors;
    }
    public function crearusuario($idpersona) { 
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
            $usuario=$_SESSION['usuario_con_error'];
            unset($_SESSION['persona_form_alerts']);
            unset($_SESSION['persona_form_error']);
            unset($_SESSION['usuario_con_error']);
        } else {
            $params['validationErrors'] = [];
        }
        
        if (!isset($usuario))
            $usuario = $this->crearUsuarioEnBlanco();

        if (!isset($persona))
            $persona=$this->persona->getPersonaById($idpersona);
        
        $usuario['idpersona']=$persona['idpersona'];
        $params['usuario'] = $usuario;
        $params['persona'] = $persona;
        $params['pageTitle'] = "Crear Usuario";
        $params['btnText'] = "Crear";

        $this->render("crear-usuario.php", $params);
    }

    /**
     * Valida y llama al metodo persistir usuario del modelo
     * No se permite el update de un usuario.
     */
    public function guardarusuario() {
        $ses = & $this->POROTO->Session;

        if (!$ses->tienePermiso('', 'Guardar usuario')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        }
        $idpersona= filter_input(INPUT_POST, 'idpersona');
        $edicion=$this->usuario->getUsuarioByIdPersona($idpersona);
        $usuario = $this->cargarDatosAltaUsuario($edicion);

        $params = array();
        $params['validationErrors'] = [];
        $params['usuario'] = $usuario;
        $params['validationErrors'] = $this->validacionDatosUsuario($usuario);

        if (count($params['validationErrors']) == 0) {
            // Alta nuevo usuario
            try {
                $this->app->beginTransaction('usuario/guardarusuario');
                // Agregar IF para saber si esta solo editando pass
                if ($edicion){
                    //Solo se actualiza la pass
                    $this->usuario->resetearPassUsuario($usuario['usuario'], md5($usuario['password']));
                    $mensajeSatisfactorio="Se ha modificado la contraseña satisfactoriamente.";
                } else {
                    //Se inserta un nuevo usuario para la persona
                    $idusuario = $this->usuario->persistirUsuario($usuario);
                    $mensajeSatisfactorio="Se ha creado el usuario satisfactoriamente.";
                }
                // ---
                $this->app->commitTransaction('usuario/guardarusuario');
                $bOk = array("ok" => true, "message" => $mensajeSatisfactorio);
            } catch (PDOException $e) {
                $this->app->rollbackTransaction('usuario/guardarusuario');
                $bOk = array("ok" => false, "message" => "Se produjo un error al persistir.");
            }

            
            if ($bOk["ok"] === false) {
                $ses->setMessage("Se produjo un error al persistir." . $bOk["message"], SessionMessageType::TransactionError);
            } else {
                $ses->setMessage("Usuario generado con éxito", SessionMessageType::Success);
            }
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        } else {
            $_SESSION['persona_form_error'] = true;
            $_SESSION['persona_form_alerts'] = $params['validationErrors'];
            $_SESSION['usuario_con_error']=$usuario;
            $ses->setMessage("Complete todos los campos.", SessionMessageType::TransactionError);
            
            header("Location: /crear-usuario/" . $usuario['idpersona'], TRUE, 302);
        }

    }
    
    /**
     * Dado un idpersona, busca el usuario asociado y llama a la vista crear-usuario.php
     * con la intención de poder poner un nuevo pass para dicho usuario.
     * @param type $idpersona
     */
    public function resetearpass($idpersona) {
        $ses = & $this->POROTO->Session;
        if (!$ses->tienePermiso('', 'Resetear password')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        }

        $validationErrors = array();
        $param=array();

        if (isset($_SESSION['persona_form_error']) && $_SESSION['persona_form_error']) {
            $params['validationErrors'] = $_SESSION['persona_form_alerts'];
            $usuario=$_SESSION['usuario_con_error'];
            unset($_SESSION['persona_form_alerts']);
            unset($_SESSION['persona_form_error']);
            unset($_SESSION['usuario_con_error']);
        } else {
            $params['validationErrors'] = [];
        }
        if (!isset($usuario))
            $usuario=$this->usuario->getUsuarioByIdPersona($idpersona);

        if (!isset($persona))
            $persona=$this->persona->getPersonaById($idpersona);
        
        $params['usuario'] = $usuario;
        $params['persona'] = $persona;
        $params['pageTitle'] = "Cambiar password";
        $params['btnText'] = "Cambiar";

        $this->render("crear-usuario.php", $params);
 
    } 
}

?>