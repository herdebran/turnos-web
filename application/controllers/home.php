<?php
class Home {
    private $POROTO;

    function __construct($poroto) {
        $this->POROTO=$poroto;
        $this->POROTO->pageHeader[] = array("label"=>"Dashboard","url"=>"");
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
            $db->dbConnect("seguridad/login");
            $sql = " select p.idpersona, p.apellido,p.nombre,p.documentonro,u.usuario, u.password, u.primeracceso,u.email from usuario ";
            $sql.= " u inner join persona p on p.idpersona=u.idpersona where u.usuario='" . $db->dbEscape($_POST["username"]) . "' AND u.estado=1 AND p.estado=1";
            $arr = $db->getSQLArray($sql);
            if (count($arr)==1) {
                //Envio el mail
                $dataEMail=$arr[0]["email"];
                if ($this->POROTO->Config['override_mail_address'] != "") $mailto = $this->POROTO->Config['override_mail_address']; else $mailto = $dataEMail;
                $mailSubject = $this->POROTO->Config["empresa_descripcion"]." - Recuperar Contraseña";
                $mailBody = "Estimado/a " . trim($arr[0]["nombre"]) . " ". trim($arr[0]["apellido"]) . ", le enviamos los datos ";
                $mailBody.= "para acceder al sitio.<br>";
                $mailBody.= "Usuario: ". trim($arr[0]["usuario"])."<br>";
                $mailBody.= "Contraseña: ".trim($arr[0]["password"]);                    
                $lib->sendMail($mailto, $mailSubject, $mailBody);
                //Logueo
                $loginErrorMessage = "Olvide mi contraseña";
                $sql = "insert into usuarioaccesos (idpersona,fecha,usuario,contraseña,ip,navegador,estado) ";
                $sql.= "select ".$arr[0]["idpersona"].",CURRENT_TIMESTAMP,'".$_POST["username"]."',null,'".$remoteIP."','".$navegador."','".$loginErrorMessage."'";
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
                $db->dbConnect("seguridad/login");

                //check pwd
                $sql = "select p.idpersona, p.documentonro, u.password, u.primeracceso from usuario u inner join persona p on p.idpersona=u.idpersona where u.usuario='" . $db->dbEscape($_POST["username"]) . "' AND u.estado=1 AND p.estado=1";
                $arr = $db->getSQLArray($sql);
                if (count($arr)==1) {
                        if (($arr[0]['password'] != md5($_POST["password"]))) {
                            $loginErrorMessage = "Contraseña errónea";
                            //update last login stamp
                            $sql = "insert into usuarioaccesos (idpersona,fecha,usuario,contraseña,ip,navegador,estado) select  null,CURRENT_TIMESTAMP,'".$_POST["username"]."','". $_POST["password"]."','".$remoteIP."','".$navegador."','".$loginErrorMessage."'";
                            $db->insert($sql);
                            $db->dbDisconnect();
                            include($this->POROTO->ViewPath . "/-login.php");
                            exit();
                            }
                    } else {
                            $loginErrorMessage = "Usuario inválido o deshabilitado";
                            $sql = "insert into usuarioaccesos (idpersona,fecha,usuario,contraseña,ip,navegador,estado) select  null,CURRENT_TIMESTAMP,'".$_POST["username"]."','". $_POST["password"]."','".$remoteIP."','".$navegador."','".$loginErrorMessage."'";
                            $db->insert($sql);
                            $db->dbDisconnect();
                            include($this->POROTO->ViewPath . "/-login.php");
                            exit();
                    }

                $sql = "SELECT p.idpersona, p.apellido, p.nombre, p.estado, u.usuario, ";
                $sql.= " p.email, u.estado, u.primeracceso";
                $sql.= " FROM persona p inner join usuario u on p.idpersona=u.idpersona";
                $sql.= " where p.idpersona=" . $arr[0]['idpersona'];
                $arrUserData = $db->getSQLArray($sql);

                //si tiene mas de un rol, levanto el primero
                    $sql = "select r.idrol, r.nombre from personarol pr inner join rol r on pr.idrol=r.idrol where pr.idpersona=" . $arr[0]['idpersona'] . " order by 1";
                $arrUserRoles = $db->getSQLArray($sql);

                if (count($arrUserRoles) == 0 || count($arrUserData) == 0) {
                            $db->dbDisconnect();
                            $loginErrorMessage = "user misconfigured. contact administrator";
                            include($this->POROTO->ViewPath . "/-login.php");
                            exit();
                }

                    //start session
                $this->POROTO->Session->startSession($arrUserData[0]['idpersona'],$arrUserData[0]['apellido'],$arrUserData[0]['nombre'],$arrUserData[0]['usuario'],$arrUserData[0]['email'], $arrUserRoles[0]['idrol'], $arrUserRoles[0]['nombre']);

                //update last login stamp
                $sql = "insert into usuarioaccesos (idpersona,fecha,usuario,contraseña,ip,navegador,estado) select  ".$arr[0]['idpersona'].",CURRENT_TIMESTAMP,'".$_POST["username"]."','". md5($_POST["password"])."','".$remoteIP."','".$navegador."','Acceso concedido'";
                $db->insert($sql);
                //$sql = "insert into usuarioaccesos (idpersona,fecha) select " . $arr[0]['idpersona'] . ",CURRENT_TIMESTAMP";
                //$db->insert($sql);


                if ($arr[0]['primeracceso'] == 1) {
                            $db->dbDisconnect();
                    header("Location: /primeracceso", TRUE, 302);
                } else { 
                    $sql = "select r.idrol, r.nombre from personarol pr inner join rol r on pr.idrol=r.idrol where pr.idpersona=" . $arrUserData[0]['idpersona'];
                    $arr = $db->getSQLArray($sql);
                    $db->dbDisconnect();
                    if (count($arr)>1) {
                        header("Location: /pickrole", TRUE, 302);
                            //OJO aca revisar porque al entrar por aca no esta entrando a verificar 
                            //los permisos y guardarlos en la sesion.
                    } else {
                        //Asignar permisos
                        $db->dbConnect("seguridad/login");
                        $ses =& $this->POROTO->Session;
                        $idRol=$arr[0]['idrol'];

                        $sql= "select p.idpermiso,p.nombre as nombre ";
                        $sql.="from permisorol pr inner join permiso p on pr.idpermiso=p.idpermiso ";
                        $sql.="where pr.idRol=".$idRol;	
                        $sql.=" union all ";
                        $sql.="select p.idpermiso,pe.nombre as nombre from personapermiso p ";
                        $sql.="inner join permiso pe on p.idpermiso=pe.idpermiso ";
                        $sql.="where p.idpersona= ".$arrUserData[0]['idpersona'];
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

    public function ajaxsendmail() {
        $lib = & $this->POROTO->Libraries['siteLibrary'];
                
        $db = & $this->POROTO->DB;
        $idComision = $_POST["co"];
        $idCarrera = $_POST["ca"];
        $idPersona = $_POST["pe"];
        $idRol = $_POST["ro"];
            
        $db->dbConnect("home/ajaxsendmail/" . $idComision . "/" . $idPersona . "/" . $idRol);

        $sql = "select p.email  from alumnomateria am ";
        $sql .= "inner join estadoalumnomateria eam on am.idestadoalumnomateria=eam.idestadoalumnomateria ";
        $sql .= "inner join comisiones c on c.idmateria=am.idmateria and c.idcomision=am.idcomision ";
        $sql .= "inner join usuarios p on am.idpersona = p.idpersona ";
        $sql .= "left join alumnomaterianota amn on amn.idalumnomateria = am.idalumnomateria and amn.idtipoexamen in (3,4) ";
        $sql .= "left join alumnocarrera ac on ac.idpersona=p.idpersona and ac.idcarrera= 5 ";
        $sql .= "left join instrumentos i on ac.idinstrumento=i.idinstrumento ";
        $sql .= "where c.idcomision=" . $idComision;
        $result = $db->getSQLArray($sql);
        $db->dbDisconnect();
        
        $mailto = "";
        
        foreach ($result as $anEmail => $email)
        {
            $mailto = $mailto . $email["email"].",";
        }
             
        if ($this->POROTO->Config["override_mail_address"] != "") 
             { $mailto = $this->POROTO->Config["override_mail_address"]; }
           
        $mailBody = $_POST["body"];
        $mailSubject = $this->POROTO->Config["empresa_descripcion"]." - Notificacion Comisiones";
        $mailto = $mailto.",".$_POST["sentoadd"];
        try
        {
            $lib->sendMail($mailto, $mailSubject, $mailBody);    
            $response = array ("msj" => "Correo enviado");
            echo json_encode($response);
        } catch (Exception $e ){
            $response = array ("msj" => "Error al enviar el correo: " . $e);
            echo json_encode($response);
        }   
    }

}


