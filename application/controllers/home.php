<?php
class Home {
    private $POROTO;
    private $usuario;


    function __construct($poroto) {
        $this->POROTO=$poroto;
        $this->POROTO->pageHeader[] = array("label"=>"Dashboard","url"=>"");
        include($this->POROTO->ModelPath . '/usuario.php');
        $this->usuario = new ModeloUsuario($this->POROTO);
    }

    function defentry() {
        if ($this->POROTO->Session->isLogged()) {
            $this->menu();
        } else {
            include($this->POROTO->ViewPath . "/-login.php");
        }
    }

    function menuBORRAR() { //OK
        $db =& $this->POROTO->DB;
        $ses =& $this->POROTO->Session;
        $lib =& $this->POROTO->Libraries['siteLibrary'];
        $db->dbConnect("home/menu");

        $arrMenu = $this->POROTO->DB->getSQLArray($this->POROTO->getMenuSqlQuery());	

        include($this->POROTO->ViewPath . "/-header.php");

        echo "<hr />";
        echo "<h3>Novedades</h3><br>";

        include($this->POROTO->ViewPath . "/-footer.php");
    }

	
	
    function login() { //OK ojo si sacamos del home ver sitelibrary.
        $navegador = substr($_SERVER['HTTP_USER_AGENT'],0,40);
        $remoteIP=$_SERVER['REMOTE_ADDR'];
        $lib =& $this->POROTO->Libraries['siteLibrary'];
        //Cambio 20180224 Para olvide mi contraseña

        if(isset($_POST["olvide"],$_POST["username"]) and $_POST["olvide"] == "si"){
            $db =& $this->POROTO->DB; 
            $db->dbConnect("home/login");
            $arr=$this->usuario->getUsuarioByUsername($db->dbEscape($_POST["username"]));

            if ($arr) {
                //Envio el mail
                $dataEMail=$arr["email"];
                if ($this->POROTO->Config['override_mail_address'] != "") $mailto = $this->POROTO->Config['override_mail_address']; else $mailto = $dataEMail;
                $mailSubject = $this->POROTO->Config["empresa_descripcion"]." - Recuperar Contraseña";
                $mailBody = "Estimado/a " . trim($arr["nombre"]) . " ". trim($arr["apellido"]) . ", le enviamos los datos ";
                $mailBody.= "para acceder al sitio.<br>";
                $mailBody.= "Usuario: ". trim($arr["usuario"])."<br>";
                $mailBody.= "Contraseña: ".trim($arr["password"]);                    
                $lib->sendMail($mailto, $mailSubject, $mailBody);
                //Logueo
                $loginErrorMessage = "Olvide mi contraseña";
                $sql = "insert into usuarioaccesos (idpersona,fecha,usuario,contraseña,ip,navegador,estado) ";
                $sql.= "select ".$arr["idpersona"].",CURRENT_TIMESTAMP,'".$_POST["username"]."',null,'".$remoteIP."','".$navegador."','".$loginErrorMessage."'";
                $db->insert($sql);
                $loginErrorMessage = "Se ha enviado la contraseña al correo ".$dataEMail;
            }else
            {
                $loginErrorMessage = "Contactese con administración. Su usuario no existe o esta deshabilitado.";
            }
            $db->dbDisconnect();
            include($this->POROTO->ViewPath . "/-login.php");
            exit();
        }
        //Fin Cambio 20180224 Para olvide mi contraseña



            if (isset($_POST["username"], $_POST["password"])) {
                $db =& $this->POROTO->DB; 
                $db->dbConnect("home/login");
                $us=$db->dbEscape($_POST["username"]);
                $passMD5=md5($_POST["password"]);
                
                $logueo=$this->obtenerArrayLogueo($us,$passMD5);
                if (!$logueo["ok"]) {
                    $loginErrorMessage =$logueo["message"];
                    $this->guardarAccesoUsuario(null,$us,$passMD5,$remoteIP,$navegador,$logueo["message"]);

                    include($this->POROTO->ViewPath . "/-login.php");
                    exit();
                   
                } else {
                    $usuario=$logueo["usuario"];
                }
                
                //si tiene mas de un rol, levanto el primero
                //$sql = "select r.idrol, r.nombre from personarol pr inner join rol r on pr.idrol=r.idrol where pr.idpersona=" . $usuario['idpersona'] . " order by 1";
                $arrUserRoles = $this->usuario->obtenerRolesByPersonaId($usuario['idpersona']);

                if (count($arrUserRoles) == 0 ) {
                    $db->dbDisconnect();
                    $loginErrorMessage = "Usuario mal configurado sin roles. contacte administrador";
                    include($this->POROTO->ViewPath . "/-login.php");
                    exit();
                }

                //start session
                $this->POROTO->Session->startSession($usuario['idpersona'],$usuario['apellido'],$usuario['nombre'],$usuario['usuario'],$usuario['email'], $arrUserRoles[0]['idrol'], $arrUserRoles[0]['nombre']);

                //update last login stamp
                $this->guardarAccesoUsuario($usuario['idpersona'],$us,$passMD5,$remoteIP,$navegador,"Acceso concedido");



                if ($usuario['primeracceso'] == 1) {
                            $db->dbDisconnect();
                    header("Location: /primeracceso", TRUE, 302);
                } else { 
//                    $sql = "select r.idrol, r.nombre from personarol pr inner join rol r on pr.idrol=r.idrol where pr.idpersona=" . $usuario['idpersona'];
//                    $arr = $db->getSQLArray($sql);
                    $db->dbDisconnect();
                    if (count($arrUserRoles)>1) {
                        header("Location: /pickrole", TRUE, 302);
                            //OJO aca revisar porque al entrar por aca no esta entrando a verificar 
                            //los permisos y guardarlos en la sesion.
                    } else {
                        //Asignar permisos
                        $db->dbConnect("home/login");
                        $ses =& $this->POROTO->Session;
                        $idRol=$arrUserRoles[0]['idrol'];

                        $result=$this->usuario->obtenerPermisos($idRol, $usuario['idpersona']);
                        $ses->clearPermisos(); //Cambio 65 Leo 20171025
                        foreach ($result as $permiso) {
                            $ses->agregarPermiso($permiso["idpermiso"],$permiso["nombre"]);
                        }

                        //Cambio 20180222
                        $sql="select parametro,valor from configuracion order by orden";
                        $result = $db->getSQLArray($sql);
                        $ses->clearConfiguracion();
                        foreach ($result as $conf) {
                            $ses->agregarConfiguracion($conf["parametro"],$conf["valor"]);
                        }
                        //Fin Cambio 20180222

                        $db->dbDisconnect();
                        //Fin Cambio Leo 20170706 Leo

                        header("Location: /", TRUE, 302);
                    }
            }

            } else {
                include($this->POROTO->ViewPath . "/-login.php");
            }
    }

    function logout() {  //OK ojo si sacamos del home ver sitelibrary.
        $this->POROTO->Session->endSession();
        header("Location: /", TRUE, 302);
    }

    function primeracceso() { //OK pero modificarlo para que tome el ROL y de acuerdo a eso asigne los permisos, como login.
            $passwordExplanied = $this->POROTO->Config['password_constraints_explained'];
            if (isset($_POST["password"])) {
                    if ($_POST["noModify"]=="0") {
                            $db =& $this->POROTO->DB; 
                            $db->dbConnect("home/primeracceso");
                            $ses =& $this->POROTO->Session;
                            $lib =& $this->POROTO->Libraries['siteLibrary'];
                            if ($lib->isPasswordValid(trim($_POST['password']))) {
                                // cambio ok
                                $sql = "update usuario set primeracceso=0, password='" . $db->dbEscape($_POST['password']) . "' where idpersona=" . $ses->getIdPersona();
                                $db->update($sql);

                                $sql = "select r.idrol, r.nombre from personarol pr inner join rol r on pr.idrol=r.idrol where pr.idpersona=" . $ses->getIdPersona();
                                $arr = $db->getSQLArray($sql);

                                if (count($arr)>1) {
                                    $db->dbDisconnect();
                                    header("Location: /pickrole", TRUE, 302);
                                } else {
                                    //Agregar permisos
                                    $idRol=$arr[0]['idrol'];

                                    $sql= "select p.idpermiso,p.nombre as nombre ";
                                    $sql.="from permisorol pr inner join permiso p on pr.idpermiso=p.idpermiso ";
                                    $sql.="where pr.idRol=".$idRol;	
                                    $sql.=" union all ";
                                    $sql.="select p.idpermiso,pe.nombre as nombre from personapermiso p ";
                                    $sql.="inner join permiso pe on p.idpermiso=pe.idpermiso ";
                                    $sql.="where p.idpersona= ".$ses->getIdPersona();
                                    $sql.=" order by nombre ";

                                    $result = $db->getSQLArray($sql);
                                    $ses->clearPermisos(); //Cambio 65 Leo 20171025
                                    foreach ($result as $permiso) {
                                                    $ses->agregarPermiso($permiso["idpermiso"],$permiso["nombre"]);
                                    }

                                    //Cambio 20180222
                                    $sql="select parametro,valor from configuracion order by orden";
                                    $result = $db->getSQLArray($sql);
                                    $ses->clearConfiguracion();
                                    foreach ($result as $conf) {
                                                $ses->agregarConfiguracion($conf["parametro"],$conf["valor"]);
                                    }
                                    //Fin Cambio 20180222
                                    $db->dbDisconnect();
                                    //Fin Cambio Leo 20170706 Leo
                                    header("Location: /", TRUE, 302);
                                }
                            } else {
                                    $validationErrors = "La contraseña no cumple con las reglas";
                                    include($this->POROTO->ViewPath . "/-primer-acceso.php");
                            }
                    } else {
                        $db =& $this->POROTO->DB; 
                        $db->dbConnect("home/primeracceso");
                        $ses =& $this->POROTO->Session;
                        $sql = "update usuario set primeracceso=0 where idpersona=" . $ses->getIdPersona();
                        $db->update($sql);

                        $sql = "select r.idrol, r.nombre from personarol pr inner join rol r on pr.idrol=r.idrol where pr.idpersona=" . $ses->getIdPersona();
                        $arr = $db->getSQLArray($sql);

                        if (count($arr)>1) {
                            $db->dbDisconnect();
                            header("Location: /pickrole", TRUE, 302);
                        } else {
                            //Agregar permisos
                            $idRol=$arr[0]['idrol'];

                            $sql= "select p.idpermiso,p.nombre as nombre ";
                            $sql.="from permisorol pr inner join permiso p on pr.idpermiso=p.idpermiso ";
                            $sql.="where pr.idRol=".$idRol;	
                            $sql.=" union all ";
                            $sql.="select p.idpermiso,pe.nombre as nombre from personapermiso p ";
                            $sql.="inner join permiso pe on p.idpermiso=pe.idpermiso ";
                            $sql.="where p.idpersona= ".$ses->getIdPersona();
                            $sql.=" order by nombre ";

                            $result = $db->getSQLArray($sql);
                            $ses->clearPermisos(); //Cambio 65 Leo 20171025
                            foreach ($result as $permiso) {
                                $ses->agregarPermiso($permiso["idpermiso"],$permiso["nombre"]);
                            }

                            //Cambio 20180222
                            $sql="select parametro,valor from configuracion order by orden";
                            $result = $db->getSQLArray($sql);
                            $ses->clearConfiguracion();
                            foreach ($result as $conf) {
                                        $ses->agregarConfiguracion($conf["parametro"],$conf["valor"]);
                            }
                            //Fin Cambio 20180222
                            $db->dbDisconnect();
                            //Fin Cambio Leo 20170706 Leo					
                            header("Location: /", TRUE, 302);
                        }
                    }
            } else {
                include($this->POROTO->ViewPath . "/-primer-acceso.php");
            }
    }

    /**
     * Arma un array y llama al modelo para persistir el acceso
     */
    function guardarAccesoUsuario($idpersona,$usuario,$contraseña,$remoteIP,$navegador,$estado){
        $valores = array();
        $valores["idpersona"]=$idpersona;
        $valores["usuario"]=$usuario;
        $valores["contrasena"]=$contraseña;
        $valores["ip"]=$remoteIP;
        $valores["navegador"]=$navegador;
        $valores["estado"]=$estado;
        $this->usuario->persistirAccesoUsuario($valores);        
    }
    
    function obtenerArrayLogueo($username,$passMD5){
        
        $usuario=$this->usuario->getUsuarioByUsername($username);
        
        if ($usuario) {
            if ($usuario['password'] != $passMD5) {
                $loginErrorMessage = "Contraseña errónea";
                $ok = false;
            } else {
                //Logueo exitoso
                $loginErrorMessage = "";
                $ok = true;
            }
        } else {
            $loginErrorMessage = "Usuario inválido o deshabilitado";
            $ok = false;
        }
        
        return array("ok" => $ok, "message" => $loginErrorMessage,"usuario" => $usuario);
    }
}