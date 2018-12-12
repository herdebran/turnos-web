<?php

class seguridad {

    private $POROTO;
    private $usuario;

    function __construct($poroto) {
        $this->POROTO = $poroto;
        $this->POROTO->pageHeader[] = array("label" => "Dashboard", "url" => "");
        include($this->POROTO->ModelPath . '/usuario.php');
        $this->usuario = new ModeloUsuario($this->POROTO);
    }

    function defentry() {
        if ($this->POROTO->Session->isLogged()) {
            header("Location: /", TRUE, 302);
        } else {
            include($this->POROTO->ViewPath . "/-login.php");
        }
    }

    function pickrole() { //OK
        $db = & $this->POROTO->DB;
        $db->dbConnect("seguridad/pickrole");
        $ses = & $this->POROTO->Session;

        if (isset($_POST["role"])) {
//            $sql = "select pr.idrol, r.nombre from personarol pr "
//                    . "inner join rol r on pr.idrol=r.idrol "
//                    . "where pr.idpersona=" . $ses->getIdPersona() 
//                    . " and pr.idrol=" . $db->dbEscape($_POST['role']);
//            $arr = $db->getSQLArray($sql);

          
// REEMPLAZAR LA QUERY POR ESTO DE ABAJO.

  $arr=$this->usuario->obtenerRolPersona($ses->getIdPersona() , $db->dbEscape($_POST['role']));


            if (!$arr) {
                header("Location: /", TRUE, 302);
            } else {
                $ses->setRole($arr['idrol'], $arr['nombre']);

                //Asignar permisos
                $idRol = $arr['idrol'];

                $result=$this->usuario->obtenerPermisos($idRol,$ses->getIdPersona());
                $ses->clearPermisos(); //Cambio 65 Leo 20171025
                foreach ($result as $permiso) {
                    $ses->agregarPermiso($permiso["idpermiso"], $permiso["nombre"]);
                }

                $sql = "select parametro,valor from configuracion order by orden";
                $result = $db->getSQLArray($sql);
                $ses->clearConfiguracion();
                foreach ($result as $conf) {
                    $ses->agregarConfiguracion($conf["parametro"], $conf["valor"]);
                }
            }
            header("Location: /", TRUE, 302);
        } else {
//            $sql = "select r.idrol, r.nombre from personarol pr"
//                    . " inner join rol r on pr.idrol=r.idrol "
//                    . "where pr.idpersona=" . $ses->getIdPersona();
//            $viewDataRoles = $db->getSQLArray($sql);
            $viewDataRoles=$this->usuario->obtenerRolesByPersonaId($ses->getIdPersona());
            include($this->POROTO->ViewPath . "/-pick-role.php");
        }
        $db->dbDisconnect();
    }

    public function habilitar($idpersona, $sino, $params = "") {  //OK
        //Habilitar Usuario de los Alumnos
        $db = & $this->POROTO->DB;
        $ses = & $this->POROTO->Session;

        //Cambio 38 Leo 20170706
        if (!$ses->tienePermiso('', 'Gestion de Usuarios Habilitacion y Cambio de contraseña')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-alumnos", TRUE, 302);
            exit();
        }
        //Fin Cambio 38 Leo 20170706

        $db->dbConnect("seguridad/habilitar/" . $idpersona . "/" . $sino . "/" . $params);
        $sql = "update usuarios set estado=" . ($sino == "no" ? "0" : "1") . " where idpersona=" . $db->dbEscape($idpersona);
        $db->update($sql);
        $db->dbDisconnect();

        $ses->setMessage("Estado modificado", SessionMessageType::Success, $params);
        header("Location: /gestion-alumnos", TRUE, 302);
    }

    public function resetpassword($idpersona, $params = "") { //OK se usa solo para alumnos
        $db = & $this->POROTO->DB;
        $ses = & $this->POROTO->Session;
        $lib = & $this->POROTO->Libraries['siteLibrary'];
        $db->dbConnect("seguridad/resetpassword/" . $idpersona . "/" . $params);

        //Cambio 38 Leo 20170706
        if (!$ses->tienePermiso('', 'Gestion de Usuarios Habilitacion y Cambio de contraseña')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-alumnos", TRUE, 302);
            exit();
        }
        //Fin Cambio 38 Leo 20170706

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $newPwd = '';
        for ($i = 0; $i < 8; $i++) {
            $newPwd .= $characters[rand(0, strlen($characters) - 1)];
        }

        $sql = "update usuarios set password='" . $newPwd . "', primeracceso=1 where idpersona=" . $db->dbEscape($idpersona);
        $db->update($sql);

        $sql = "select usuario, password, email from usuarios where idpersona=" . $db->dbEscape($idpersona);
        $arr = $db->getSQLArray($sql);

        $db->dbDisconnect();

        if ($this->POROTO->Config['override_mail_address'] != "")
            $mailto = $this->POROTO->Config['override_mail_address'];
        else
            $mailto = $arr[0]['email'];
        $mailSubject = $this->POROTO->Config["empresa_descripcion"]." - Acceso";
        $mailBody = "Le contamos que puede ingresar al sitio web de alumnos con el usuario <b>" . $arr[0]['usuario'] . "</b> y la contraseña <b>" . $arr[0]['password'] . "</b>";
        $lib->sendMail($mailto, $mailSubject, $mailBody);

        $ses->setMessage("Clave reseteada y notificación enviada", SessionMessageType::Success, $params);
        header("Location: /gestion-alumnos", TRUE, 302);
    }

    public function verifSession() {
                $response = array();

        if (!isset($_SESSION["llamadoAnterior"])) {
            $_SESSION["llamadoAnterior"] = time();
        }
        $tmp =  $_SESSION["llamadoAnterior"];
        
        $_SESSION["llamadoNuevo"] = time();
        $_SESSION["llamadoDiferencia"] = $_SESSION["llamadoNuevo"] - $_SESSION["llamadoAnterior"];
        $_SESSION["llamadoAnterior"] = time();
        
        $response["llamadoNuevo"] = $_SESSION["llamadoNuevo"];
        $response["llamadoDiferencia"] = $_SESSION["llamadoDiferencia"];
        $response["llamadoAnterior"] = $tmp;
//        $response["ok"] = true;
//        $response["time"] = time();
//        $response["lastactivity"] = $_SESSION['lastactivity'];
//        $response["tiempotranscurrido"] = time() - $_SESSION['lastactivity'];
        $response["tiempoconfigurado"] = $this->POROTO->Config['session_minutes_alive'] * 60;
        echo json_encode($response);
    }

}

?>