<?php 
/**
  * Session management class 
  *
  * With this class a persistent serverside session could be stored.
  * It relays on internal php sessions
  *
  * @package  poroto
  * @version  1.2
  * @access   public
  * @copyright 2015-2017 7dedos
  * @author Augusto Wloch <agosto@7dedos.com>
  */
if ( ! defined('POROTO')) exit('No direct script access allowed');

abstract class SessionMessageType {
    const Undetermined = 0;
    const Success = 1;
    const TransactionError = 2;
}

class Session {
	private $POROTO;

	public function __construct($poroto) {
		if (array_key_exists('SERVER_ID', $_SERVER)) { //production
			session_save_path("");
			session_start();
		} else {
			session_start();
		}
		$this->POROTO=$poroto;

		//valido si la ultima vez que use la session fue hace mas de 1 minuto, cierro la sesion
		if (isset($_SESSION['lastactivity'])) {
			if (time()-$_SESSION['lastactivity'] > ($poroto->Config['session_minutes_alive'] * 60) ) {
				$this->endSession();
			} else {
				$_SESSION['lastactivity'] = time();
			}
		}
	}

	public function release() {
		session_write_close();
	}

	public function isLogged() {
		return (isset($_SESSION['idpersona']) && $_SESSION['idpersona'] > 0);
	}

	// public function setIdPersona($val) {	$_SESSION['idpersona'] = $val;  }
	public function getIdPersona()     { return($_SESSION['idpersona']);	}

	public function setLegajo($val) {	$_SESSION['legajo'] = $val;  }
	public function getLegajo()     { return($_SESSION['legajo']);	}

	public function setNombre($val) {	$_SESSION['nombre'] = $val;  }
	public function getNombre()     { return($_SESSION['nombre']);	}

	public function setApellido($val) {	$_SESSION['apellido'] = $val;  }
	public function getApellido()     { return($_SESSION['apellido']);	}

	public function setUsuario($val) {	$_SESSION['usuario'] = $val;  }
	public function getUsuario()     { return($_SESSION['usuario']);	}

	public function setMail($val) {	$_SESSION['email'] = $val;  }
	public function getMail()     { return($_SESSION['email']);	}

	public function setRole($id, $desc) {	$_SESSION['idrol'] = $id; $_SESSION['role'] = $desc;   }
	public function getIdRole()     { return($_SESSION['idrol']);	}
	public function getRoleName()     { return($_SESSION['role']);	}

	public function getMessage() {
		$msg = array("text"=>$_SESSION['usermessagetext'], "type"=>$_SESSION['usermessagetype'], "param"=>$_SESSION['usermessageparameters']);
		$_SESSION['usermessagetext'] = "";
		$_SESSION['usermessagetype'] = SessionMessageType::Undetermined;
		$_SESSION['usermessageparameters'] = "";
		return ($msg);
	}
	public function setMessage($msg, $type, $param="") {
		$_SESSION['usermessagetext'] = $msg;
		$_SESSION['usermessagetype'] = $type;
		$_SESSION['usermessageparameters'] = $param;
	}


	public function startSession($idpersona, $apellido, $nombre, $usuario, $email, $idrol, $role) {
		$_SESSION['idpersona'] = $idpersona;
		$_SESSION['apellido'] = $apellido;
		$_SESSION['nombre'] = $nombre;
		$_SESSION['usuario'] = $usuario;
		$_SESSION['email'] = $email;
		$_SESSION['idrol'] = $idrol;
		$_SESSION['role'] = $role;
		$_SESSION['lastactivity'] = time();
		$_SESSION['usermessagetext'] = "";
		$_SESSION['usermessageparameters'] = "";
		$_SESSION['usermessagetype'] = SessionMessageType::Undetermined;
		$_SESSION['permisos']=array();
                $_SESSION['configuracion']=array();
	}

	public function endSession() {
		$_SESSION['idpersona'] = 0;
		$_SESSION['legajo'] = '';
		$_SESSION['apellido'] = '';
		$_SESSION['nombre'] = '';
		$_SESSION['usuario'] = '';
		$_SESSION['email'] = '';
		$_SESSION['idrol'] = 0;
		$_SESSION['role'] = '';
		$_SESSION['lastactivity'] = time();
		$_SESSION['usermessagetext'] = "";
		$_SESSION['usermessageparameters'] = "";
		$_SESSION['usermessagetype'] = SessionMessageType::Undetermined;
		$_SESSION['permisos']=array();
                $_SESSION['configuracion']=array();
	}

	//Cambio 65
	public function clearPermisos(){
			$_SESSION['permisos']=array();
	}
	//Fin cambio 65
	
        public function clearConfiguracion(){
			$_SESSION['configuracion']=array();
	}
        
	public function agregarPermiso($idPermiso,$nombrePermiso) {
			$_SESSION['permisos'][count($_SESSION['permisos'])+1] = array($idPermiso,$nombrePermiso);
	}
        
        public function agregarConfiguracion($parametro,$valor) {
			$_SESSION['configuracion'][count($_SESSION['configuracion'])+1] = array($parametro,$valor);
	}
	
	public function getPermisos(){
		return $_SESSION['permisos'];
	}
        
        public function getConfiguracion(){
		return $_SESSION['configuracion'];
	}
	
	public function tienePermiso($idPermiso,$nombrePermiso) {
		//devuelve verdadero en caso de tener el permiso asignado.
		for($i=1;$i<=count($_SESSION['permisos']);$i++ ){
			if ($idPermiso!=''){
				if ($_SESSION['permisos'][$i][0]==$idPermiso){
					return(true);	
				}
			}
			if ($nombrePermiso!=''){
				if ($_SESSION['permisos'][$i][1]==$nombrePermiso){
					return(true);	
				}
			}
		}
	}
        
        //Parametros de tabla configuracion
        public function getParametroConfiguracion($parametro) {
            for($i=1;$i<=count($_SESSION['configuracion']);$i++){
                //echo($_SESSION['configuracion'][$i][0]." ".$_SESSION['configuracion'][$i][1]."<br>");
                    if ($_SESSION['configuracion'][$i][0]==$parametro){
                            if ($_SESSION['configuracion'][$i][1]=="Y"){
                                return(true);	
                            } else {
                                if ($_SESSION['configuracion'][$i][1]=="N"){
                                    return(false);
                                }else{
                                    return($_SESSION['configuracion'][$i][1]); //Valor
                                }	
                            }
                             
                    }

            }
	}
}