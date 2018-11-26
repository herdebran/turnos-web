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

    function forgot() {
        //TODO: todo this
        die ("to be implemented soon");
    }

    function menu() { //OK
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


    public function misdatos() {
            $validationErrors = array();
            $db =& $this->POROTO->DB;
            $ses =& $this->POROTO->Session;
            $lib =& $this->POROTO->Libraries['siteLibrary'];


            //detectar si el usuario es profesor y redirigirlo a profesores/misdatos
            if ($ses->getIdRole()==$this->POROTO->Config['rol_profesor_id']) {
                    header("Location: /profesores/misdatos", TRUE, 302);
                    exit();
            }

            //Cambio 38 Leo 20170706
            if(!$ses->tienePermiso('','Ver Datos')){
                            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
                            header("Location: /", TRUE, 302);
                            exit();
            }
            //Fin Cambio 38 Leo 20170706

            $db->dbConnect("home/misdatos");

            //Modificacion
            if (isset($_POST['email'], $_POST['nacionalidad'])) {
                    $db->dbConnect("home/misdatos");
                    $dataNroDoc = $db->dbEscape(intval($_POST['nrodoc']));
                    $dataTipDoc = $db->dbEscape($_POST['tipdoc']);
                    // $dataApellido = $db->dbEscape(trim($_POST['apellido']));
                    // $dataNombre = $db->dbEscape(trim($_POST['nombre']));
                    $dataNacionalidad = $db->dbEscape($_POST['nacionalidad']);
                    $dataFNac = $db->dbEscape($_POST['fnac']);
                    $dataTel1 = $db->dbEscape(trim($_POST['telefono1']));
                    $dataTel2 = $db->dbEscape(trim($_POST['telefono2']));
                    $dataSexo = $db->dbEscape($_POST['sexo']);
                    $dataECiv = $db->dbEscape($_POST['estciv']);
                    if (isset($_POST['certlab'])) $dataCLab = $db->dbEscape(trim($_POST['certlab'])); else $dataCLab = "";

                    $dataProv  = $db->dbEscape(trim($_POST['provincia']));
                    $dataCalle = mb_strtoupper($db->dbEscape(trim($_POST['direccion'])), 'UTF-8');
                    $dataNro   = mb_strtoupper($db->dbEscape(trim($_POST['numero'])), 'UTF-8');
                    $dataEntre = mb_strtoupper($db->dbEscape(trim($_POST['entrecalles'])), 'UTF-8');
                    $dataLocal = $db->dbEscape($_POST['localidad']);
                    $dataPiso  = mb_strtoupper($db->dbEscape(trim($_POST['piso'])), 'UTF-8');
                    $dataDepto = mb_strtoupper($db->dbEscape(trim($_POST['depto'])), 'UTF-8');
                    $dataCP    = mb_strtoupper($db->dbEscape(trim($_POST['codpostal'])), 'UTF-8');
                    $dataTitulo = mb_strtoupper($db->dbEscape(trim($_POST['titulo'])), 'UTF-8');
                    $dataOtorga = mb_strtoupper($db->dbEscape(trim($_POST['otorgadopor'])), 'UTF-8');
                    $dataAnoEgr = $db->dbEscape($_POST['anoegreso']);
                    $dataOSoc = mb_strtoupper($db->dbEscape(trim($_POST['obrasocial'])), 'UTF-8');
                    $dataCont = mb_strtoupper($db->dbEscape(trim($_POST['contactoemergencia'])), 'UTF-8');
                    $dataTele = $db->dbEscape(trim($_POST['telefono']));
                    $dataEnfe = mb_strtoupper($db->dbEscape(trim($_POST['enfermedades'])), 'UTF-8');
                    $dataUser  = $db->dbEscape(trim($_POST['usuario']));
                    $dataEMail = $db->dbEscape(trim($_POST['email']));
                    $dataPass1 = $db->dbEscape(trim($_POST['password1']));
                    $dataPass2 = $db->dbEscape(trim($_POST['password2']));
                    $dataObs = mb_strtoupper($db->dbEscape(trim($_POST['observaciones'])), 'UTF-8');
                    if (isset($_POST['primeracceso'])) $dataPrimerAcceso = $db->dbEscape(trim($_POST['primeracceso'])); else $dataPrimerAcceso = "";
                    $db->dbDisconnect();

                    // STAP! VALIDATION TIME
                    // nacionalidad - seteado y mayor a 0
                    if ($dataNacionalidad=="0" || $dataNacionalidad=="") $validationErrors['nacionalidad'] = "La Nacionalidad es obligatoria";
                    // f.nacim		obligatoria. no mayor a 100 anos ni menor a 5 anios
                    if (!$lib->validateDate($dataFNac)) {
                            $validationErrors['fnac'] = "La Fecha de Nacimiento es inválida";
                    } else {
                    $d = $lib->datediff($dataFNac);
                    if ($d > (365*100)) $validationErrors['fnac'] = "La Fecha de Nacimiento es inválida (>100)";
                    if ($d < (365 * 5)) $validationErrors['fnac'] = "La Fecha de Nacimiento es inválida (BenjaminButton)" . $d;
                    if ($d<0) $validationErrors['fnac'] = "La Fecha de Nacimiento es inválida (futura)";
                    }

                    // telefono fijo obligatorio. entre 6 y 20 caracteres. solo numeros, espacios, parentesis y guion
                    if (! preg_match("/^[0-9\s\(\)\-]{6,20}$/", $dataTel1)) $validationErrors['telefono1'] = "El Teléfono Fijo contiene caracteres inválidos";
                    if (strlen($dataTel1) < 6 || strlen($dataTel1) > 20) $validationErrors['telefono1'] = "El Teléfono Fijo es obligatorio";
                    // telefono celular. si no es blanco, debe contener entre 6 y 20 caracteres. solo numeros, espacios, parentesis y guion
                    if ($dataTel2!="") {
                            if (strlen($dataTel2) < 6 || strlen($dataTel2) > 20) $validationErrors['telefono2'] = "El Teléfono Celular debe contener entre 6 y 20 caracteres";
                            if (! preg_match("/^[0-9\s\(\)\-]{6,20}$/", $dataTel2)) $validationErrors['telefono2'] = "El Teléfono Celular contiene caracteres inválidos";
                    }
                    // sexo 		seteado y mayor a 0
                    if ($dataSexo=="0" || $dataSexo=="") $validationErrors['sexo'] = "El campo Sexo es obligatorio";
                    // estado civil	seteado y mayor a 0
                    if ($dataECiv=="0" || $dataECiv=="") $validationErrors['estciv'] = "El Estado Civil es obligatorio";

                    // calle 	obligatorio. maxlength 45
                    if ($dataCalle=="") $validationErrors['direccion'] = "El campo Calle es obligatorio";
                    if (strlen($dataCalle)>45) $validationErrors['direccion'] = "El campo Calle puede contener como máximo 45 caracteres";
                    // numero 	obligatorio. maxlength 45
                    if ($dataNro=="") $validationErrors['numero'] = "El campo Número es obligatorio";
                    if (strlen($dataNro)>45) $validationErrors['numero'] = "El campo Número puede contener como máximo 45 caracteres";
                    // entre calles 	opcional. maxlength 45
                    if (strlen($dataEntre)>45) $validationErrors['entrecalles'] = "El campo Entre puede contener como máximo 45 caracteres";
                    // provincia 	obligatorio	seteado y mayor a 0
                    if ($dataProv =="0" || $dataProv=="") $validationErrors['provincia'] = "El campo Provincia es obligatorio";
                    // localidad	obligatorio	seteado y mayor a 0
                    if ($dataLocal =="0" || $dataLocal=="") $validationErrors['localidad'] = "El campo Localidad es obligatorio";
                    // piso 	opcional. maxlength 45
                    if (strlen($dataPiso)>45) $validationErrors['piso'] = "El campo Piso puede contener como máximo 45 caracteres";
                    // depto 	opcional. maxlength 45
                    if (strlen($dataDepto)>45) $validationErrors['depto'] = "El campo Depto puede contener como máximo 45 caracteres";
                    // codigo postal 	obligatorio. maxlength 45
                    if ($dataCP=="") $validationErrors['codpostal'] = "El campo Cod.Postal es obligatorio";
                    if (strlen($dataCP)>45) $validationErrors['codpostal'] = "El campo Cod.Postal puede contener como máximo 45 caracteres";

                    // titulo 	opcional. maxlength 45
                    if (strlen($dataTitulo)>45) $validationErrors['titulo'] = "El campo Título puede contener como máximo 45 caracteres";
                    // otorgado por 	opcional. maxlength 45
                    if (strlen($dataOtorga)>45) $validationErrors['otorgadopor'] = "El campo Otorgado Por puede contener como máximo 45 caracteres";
                    // ano egreso opcional. numerico entre 1930 y el año actual
                    if (trim($dataAnoEgr)!="") {
                            if (is_int($dataAnoEgr)) {
                                    if (intval($dataAnoEgr) < 1930) $validationErrors['anoegreso'] = "El campo Año Egreso no puede ser anterior a 1930";
                                    if (intval($dataAnoEgr) > date("Y")) $validationErrors['anoegreso'] = "El campo Año Egreso no puede ser posterior a " . date("Y");
                            }
                    }

                    // obra social 			opcional maxlength 45
                    if (strlen($dataOSoc)>45) $validationErrors['obrasocial'] = "El campo Obra Social puede contener como máximo 45 caracteres";
                    // contacto emergencia 	obligatorio maxlength 45
                    if ($dataCont=="") $validationErrors['contactoemergencia'] = "El campo Contacto Emergencia es obligatorio";
                    if (strlen($dataCont)>45) $validationErrors['contactoemergencia'] = "El campo Contacto Emergencia puede contener como máximo 45 caracteres";
                    // telef emergencia 	obligatorio maxlength 45
                    if ($dataTele=="") $validationErrors['telefono'] = "El campo Teléfono Contacto es obligatorio";
                    if (strlen($dataTele)>45) $validationErrors['telefono'] = "El campo Teléfono Contacto puede contener como máximo 45 caracteres";
                    if ($dataTele!="") {
                            if (! preg_match("/^[0-9\s\(\)\-]{6,20}$/", $dataTele)) $validationErrors['telefono'] = "El Teléfono Contacto contiene caracteres inválidos";
                    }
                    // enfermedades 		opcional maxlength 500
                    if (strlen($dataEnfe)>500) $validationErrors['enfermedades'] = "El campo Enfermedades puede contener como máximo 500 caracteres";

                    // email 	obligatorio maxlength 45 a@b.c
                    if ($dataEMail=="") $validationErrors['email'] = "El campo Email es obligatorio";
                    if (strlen($dataEMail)>45) $validationErrors['email'] = "El campo Email puede contener como máximo 45 caracteres";
                    if (!filter_var($dataEMail, FILTER_VALIDATE_EMAIL)) $validationErrors['email'] = "El campo Email es inválido";
                    // password 	obligatorio maxlength 45 - deben coincidir password1 y password2 minlength 6
                    if ($dataPass1!="") { 
                            if (! $lib->isPasswordValid($dataPass1)) $validationErrors['password1'] = "Contraseña inválida. " . $this->POROTO->Config['password_constraints_explained'];

                            if (strlen($dataPass1) > 45) {
                                    $validationErrors['password1'] = "El campo Contraseña puede contener como máximo 45 caracteres";
                            } else {
                                    if ($dataPass1 != $dataPass2) {
                                            $validationErrors['password2'] = "El campo Contraseña no coincide con su validación";
                                    }
                            }

                    }

                    // observaciones 	opcional. maxlength 5000
                    if (strlen($dataObs) > 5000) $validationErrors['observaciones'] = "El campo Observaciones puede contener como máximo 5000 caracteres. Contiene " . strlen($dataObs);

                    if (count($validationErrors) == 0) {  
                            $db->dbConnect("home/misdatos");

                            $sqlP = "UPDATE personas SET ";
                            $sqlP.= "nacionalidad='" . $dataNacionalidad . "'";
                            if ($dataFNac == "") $sqlP.= ",fechanac=null"; else $sqlP.= ",fechanac='" . $lib->dateDMY2YMD($dataFNac) . "'";
                            $sqlP.= ",telefono1='" . $dataTel1 . "'";
                            $sqlP.= ",telefono2='" . $dataTel2 . "'";
                            $sqlP.= ",sexo='" . $dataSexo . "'";
                            $sqlP.= ",estadocivil='" . $dataECiv . "'";
                            $sqlP.= ",direccion='" . $dataCalle . "'";
                            $sqlP.= ",numero='" . $dataNro . "'";
                            $sqlP.= ",entrecalles='" . $dataEntre . "'";
                            $sqlP.= ",idlocalidad=" . $dataLocal;
                            $sqlP.= ",piso='" . $dataPiso . "'";
                            $sqlP.= ",depto='" . $dataDepto . "'";
                            $sqlP.= ",codpostal='" . $dataCP . "'";
                            $sqlP.= ",usumodi='" . $ses->getUsuario() . "'";
                            $sqlP.= ",fechamodi=CURRENT_TIMESTAMP";
                            $sqlP.= " WHERE idpersona=" . $ses->getIdPersona();

                            $db->begintrans();
                            $bOk = $db->update($sqlP, '', true);

                            $sqlA = "INSERT INTO alumnos (idpersona,titulosecundario,otorgadopor,aniooegreso,";
                            $sqlA.= "estadoalumno_id,certificadotrabajo,observaciones,usucrea,fechacrea,usumodi,fechamodi) SELECT ";
                            $sqlA.= $ses->getIdPersona();
                            $sqlA.= ",'" . $dataTitulo . "'";
                            $sqlA.= ",'" . $dataOtorga . "'";
                            $sqlA.= "," . ($dataAnoEgr!='' ? $dataAnoEgr : 'null');
                            $sqlA.= "," . $this->POROTO->Config['estado_alumno_ingresante_id'];
                            $sqlA.= "," . ($dataCLab == "certlab" ? "1" : "0");
                            $sqlA.= ",'" . $dataObs . "'";
                            $sqlA.= ",'" . $ses->getUsuario() . "'";
                            $sqlA.= ",CURRENT_TIMESTAMP ";
                            $sqlA.= ",NULL ";
                            $sqlA.= ",NULL ";
                            $sqlA.= " ON DUPLICATE KEY UPDATE ";
                            $sqlA.= "titulosecundario='" . $dataTitulo . "'";
                            $sqlA.= ",certificadotrabajo=".($dataCLab == "certlab" ? "1" : "0");
                            $sqlA.= ",otorgadopor='" . $dataOtorga . "'";
                            $sqlA.= ",aniooegreso=" . ($dataAnoEgr!='' ? $dataAnoEgr : 'null');
                            $sqlA.= ",observaciones='" . $dataObs . "'";
                            $sqlA.= ",usumodi='" . $ses->getUsuario() . "'";
                            $sqlA.= ",fechamodi=CURRENT_TIMESTAMP";
                            if ($bOk!==false) $bOk = $db->insert($sqlA, '', true);

                            $sqlF = "INSERT INTO fichamedica (idpersona,obrasocial,contactoemergencia,telefono,enfermedades) SELECT ";
                            $sqlF.= $ses->getIdPersona();
                            $sqlF.= ",'" . $dataOSoc . "'";
                            $sqlF.= ",'" . $dataCont . "'";
                            $sqlF.= ",'" . $dataTele . "'";
                            $sqlF.= ",'" . $dataEnfe . "'";
                            $sqlF.= " ON DUPLICATE KEY UPDATE ";
                            $sqlF.= "obrasocial='" . $dataOSoc . "'";;
                            $sqlF.= ",contactoemergencia='" . $dataCont . "'";;
                            $sqlF.= ",telefono='" . $dataTele . "'";;
                            $sqlF.= ",enfermedades='" . $dataEnfe . "'";;
                            if ($bOk!==false) $bOk = $db->insert($sqlF, '', true);

                            $sqlU = "UPDATE usuarios SET ";
                            $sqlU.= "email='" . $dataEMail . "'";
                            if ($dataPass1!="") $sqlU.= ",password='" . $dataPass1 . "'";
                            $sqlU.= ",primeracceso=" . ($dataPrimerAcceso == "primeracceso" ? "1" : "0");
                            $sqlU.= ",usumodi='" . $ses->getUsuario() . "'";
                            $sqlU.= ",fechamodi=CURRENT_TIMESTAMP";
                            $sqlU.= " WHERE idpersona=" . $ses->getIdPersona();
                            if ($bOk!==false) $bOk = $db->update($sqlU, '', true);

                            if ($bOk === false) {
                                    $db->rollback();
                                    $ses->setMessage("Se produjo un error realizando la modificación", SessionMessageType::TransactionError);
                                    header("Location: /", TRUE, 302);
                                    exit();

                            } else {
                                    $db->commit();
                            }
                            $db->dbDisconnect();

                            $ses->setMessage("La modificación se completó con éxito", SessionMessageType::Success);
                            header("Location: /", TRUE, 302);
                            exit();

                    }

            }

            //Carga de datos en el formulario
            $db->dbConnect("home/misdatos");
            $sql = "SELECT id,descripcion FROM tipodoc order by id";
            $viewDataTipoDocumento = $db->getSQLArray($sql);

            $sql = "select p.apellido, p.nombre, p.nacionalidad, date_format(fechanac, '%d/%m/%Y') fnac_dmy, p.tipodoc, td.descripcion, p.documentonro, p.telefono1, p.telefono2, p.sexo, p.estadocivil  ";
            $sql.= " ,p.direccion, p.entrecalles, p.numero, p.piso, p.depto, p.idlocalidad, l.descripcion localidad_descripcion, l.idprovincia, p.codpostal, a.certificadotrabajo certlab";
            $sql.= " ,a.titulosecundario, a.otorgadopor, a.aniooegreso anoegreso";
            $sql.= " ,fm.obrasocial, fm.contactoemergencia, fm.telefono, fm.enfermedades";
            $sql.= " ,u.usuario, u.password, u.email, u.primeracceso";
            $sql.= " ,a.observaciones";
            $sql.= " ,a.estadoalumno_id, ea.descripcion";
            $sql.= " from personas p inner join tipodoc td on td.id=p.tipodoc inner join usuarios u on u.idpersona=p.idpersona";
            $sql.= " left join localidades l on l.id=p.idLocalidad";
            $sql.= " left join alumnos a on a.idpersona=p.idpersona left join estadoalumno ea on a.estadoalumno_id=ea.id";
            $sql.= " left join fichamedica fm on fm.idpersona=p.idpersona";
            $sql.= " where p.idpersona=" . $ses->getIdPersona();
            $viewData = $db->getSQLArray($sql);

            $sql = "SELECT idcarrera,nombre,descripcion FROM carreras WHERE estado=1 order by nombre";
            $viewDataCarreras = $db->getSQLArray($sql);

            $sql = "select idarea,nombre from areas";
            $viewDataAreas = $db->getSQLArray($sql);

            $sql = "select idinstrumento,nombre,descripcion from instrumentos order by nombre";
            $viewDataInstrumentos = $db->getSQLArray($sql);

            $sql = "select id,descripcion from provincias";
            $viewDataProvincias = $db->getSQLArray($sql);

            $idprov = (isset($_POST['provincia']) ? $_POST['provincia'] : $viewData[0]['idprovincia']);
            if ($idprov=="") $idprov=0;
            $sql = "select id,descripcion,cp from localidades where idprovincia=" . $idprov . " order by descripcion";
            $viewDataLocalidades = $db->getSQLArray($sql);

            $arrMenu = $this->POROTO->DB->getSQLArray($this->POROTO->getMenuSqlQuery());	

            $sql = "SELECT date_format(ac.fechainscripcion, '%d/%m/%Y') fechainscripcion_dmy, c.nombre carrera_nombre, c.descripcion carrera_descripcion, i.nombre instrumento_nombre, i.descripcion instrumento_descripcion, a.nombre area_nombre, eac.descripcion estado, cn.descripcion nivelDescripcion";
            $sql.= " FROM alumnocarrera ac inner join estadoalumnocarrera eac on ac.estado=eac.id";
            $sql.= " inner join carreras c on ac.idcarrera=c.idcarrera";
            $sql.= " inner join instrumentos i on ac.idinstrumento=i.idinstrumento";
            $sql.= " inner join carreraniveles cn on ac.idnivel=cn.id";
            $sql.= " left join areas a on ac.idarea=a.idarea";
            $sql.= " WHERE ac.idpersona=" . $db->dbEscape($ses->getIdPersona());
            $viewDataAlumnoCarreras = $db->getSQLArray($sql);

            $db->dbDisconnect();

            $viewDataSexo = $this->POROTO->Config['dominios']['sexo'];
            $viewDataNacionalidad = $this->POROTO->Config['dominios']['nacionalidad'];
            $viewDataEstadoCivil = $this->POROTO->Config['dominios']['estadocivil'];
            $newCarreras = array();

            $status = "misdatos";
            $pageTitle="Mis datos";
            include($this->POROTO->ViewPath . "/-header.php");
            include($this->POROTO->ViewPath . "/ver-alumno.php");
            include($this->POROTO->ViewPath . "/-footer.php");
    }


    public function ajaxlocalidades($provinciaId) {
            $db =& $this->POROTO->DB;
            $db->dbConnect("home/ajaxLocalidades");
            $sql = "SELECT id,descripcion,cp FROM localidades where idprovincia=" . $db->dbEscape($provinciaId) . " order by descripcion";
            $arrData = $db->getSQLArray($sql);
            $db->dbDisconnect();
            echo json_encode($arrData);
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


