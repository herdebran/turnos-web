<?php
class procesos {
	private $POROTO;
	function __construct($poroto) {
		$this->POROTO=$poroto;
		$this->POROTO->pageHeader[] = array("label"=>"Dashboard","url"=>"");		
	}

	function defentry() {
		if ($this->POROTO->Session->isLogged()) { 		
			header("Location: /", TRUE, 302);
		} else {
			include($this->POROTO->ViewPath . "/-login.php");
		}

	}
	
	function verprocesos() {
		$db =& $this->POROTO->DB;
		$ses =& $this->POROTO->Session;
		$lib =& $this->POROTO->Libraries['siteLibrary'];
		$validationErrors = array();

		
		if(!$ses->tienePermiso('','Ver Procesos Automaticos Acceso desde Menu')){
				$ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
				header("Location: /", TRUE, 302);
				exit();
		}
			
		$db->dbConnect("procesos/verprocesos");
		
		$arrMenu = $this->POROTO->DB->getSQLArray($this->POROTO->getMenuSqlQuery());
		
		//cargo arrays tipos de examenes (contemplando que profesores solo pueden crear cierto tipo).
	    $sql = "SELECT idtipoexamen id,descripcion FROM tipoexamen";
		$sql.= " WHERE idtipoexamen in (3,4,5)";
	    $sql.= " ORDER BY id";
		$viewDataTipoExamen = $db->getSQLArray($sql);	
		$viewData = array(array('tipexa'=>0,
								'fecha'=>date("d/m/Y"),
			        ));
		
		$db->dbDisconnect();
		$pageTitle="Procesos";
		include($this->POROTO->ViewPath . "/-header.php");
		include($this->POROTO->ViewPath . "/procesos-automaticos.php");
		include($this->POROTO->ViewPath . "/-footer.php");
	}
	
        function downloadFile(){
            //Funcion para descargar el archivo txt de logs.
            $file = basename($_GET['file']);
            $file = "../temp/".$file; 
            if (!$file) { // file does not exist
                die('file not found');
            } else {
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$file");
                header("Content-Type: application/txt");
                header("Content-Transfer-Encoding: binary");
                // read the file from disk
                readfile($file);
            }
        }
        
	function proceso1($testing) {
	/*
	PROCESO A EJECUTARSE EN MARZO UNA VEZ TERMINADOS LOS FINALES Y ANTES DE LA MATRICULACION
	1)SE RECORRE ALUMNOMATERIAS TODAS AQUELLAS MATERIAS CUYO AÑO DE CURSADA SEA MENOR AL ACTUAL
	SI TIENEN ESTADO LIBRE, SE PONE DESAPROBADA.
	2)SE RECORRE ALUMNOMATERIAS TODAS AQUELLAS MATERIAS CUYO AÑO DE CURSADA SEA HACE 5 AÑOS
	SI TIENE ESTADO LIBRE O CURSADA APROBADA, SE PONE DESAPROBADA.
	*/
	$db =& $this->POROTO->DB; 
	$ses =& $this->POROTO->Session;
	$lib =& $this->POROTO->Libraries['siteLibrary'];
	
	if(!$ses->tienePermiso('','Proceso 1 - Proceso Anual AlumnoMateria')){
				$ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
				header("Location: /verprocesos", TRUE, 302);
				exit();
	}
			
	$db->dbConnect("procesos/proceso1/");
	$db->begintrans();	
	$anioActual=date ("Y");
        $bOk=true;
        $cambios=array();
	
        //Obtengo los registros cambiados.
        $sql=  " select am.idalumnomateria,p.apellido,p.nombre,am.aniocursada,vm.materiacompleta as materia,eam.descripcion as estado ";
        $sql.= " from alumnomateria am inner join personas p on am.idpersona=p.idpersona ";
        $sql.= " inner join viewmaterias vm on (am.idmateria=vm.idmateria and am.idcarrera=vm.idcarrera) ";
        $sql.= " inner join estadoalumnomateria eam on am.idestadoalumnomateria=eam.idestadoalumnomateria ";
	$sql.= " where am.aniocursada<".$anioActual." and am.idestadoalumnomateria=".$this->POROTO->Config['estado_alumnomateria_libre'];
	$result = $db->getSQLArray($sql);
        
        if($testing==0){
            $cambios[]="EJECUCION";
        }
        else
        {
            $cambios[]="SIMULACION";
        }
        
        if(count($result)>0){
            //Paso a desaprobada la materia para todos aquellos que hayan cursado años anteriores al actual.
            $sql=  "update alumnomateria set idestadoalumnomateria=".$this->POROTO->Config['estado_alumnomateria_desaprobada']. ", ";
            $sql.= " usumodi='" . $ses->getUsuario() . "',";
            $sql.= " fechamodi=CURRENT_TIMESTAMP";
            $sql.= " where aniocursada<".$anioActual." and idestadoalumnomateria=".$this->POROTO->Config['estado_alumnomateria_libre'];
            if($testing==0){
                $bOk = $db->update($sql, '', true);
            }
            $cambios[]="Listado de Materias desaprobadas por estar libre de años anteriores.";
            foreach ($result as $alumno){
                $cambios[]=$alumno["idalumnomateria"]." "." Alumno: ".$alumno["apellido"]." ".$alumno["nombre"]." Materia: ".$alumno["materia"]." Año Cursada: ".$alumno["aniocursada"]." Estado: ".$alumno["estado"]." Nuevo Estado: DESAPROBADA";
            }
        }
        
        //Obtengo los registros cambiados.
        $sql=  " select am.idalumnomateria,p.apellido,p.nombre,am.aniocursada,vm.materiacompleta as materia,eam.descripcion as estado ";
        $sql.= " from alumnomateria am inner join personas p on am.idpersona=p.idpersona ";
        $sql.= " inner join viewmaterias vm on (am.idmateria=vm.idmateria and am.idcarrera=vm.idcarrera) ";
        $sql.= " inner join estadoalumnomateria eam on am.idestadoalumnomateria=eam.idestadoalumnomateria ";
	$sql.= " where am.aniocursada+5<".$anioActual." and ";
	$sql.= " am.idestadoalumnomateria in (".$this->POROTO->Config['estado_alumnomateria_libre'].",";
	$sql.= $this->POROTO->Config['estado_alumnomateria_cursadaaprobada'].")";
        $result = $db->getSQLArray($sql);
        if(count($result)>0){
            //Paso a desaprobada la materia para todos aquellos que hayan aprobado la cursada hace mas de 5 años.
            $sql=  "update alumnomateria set idestadoalumnomateria=".$this->POROTO->Config['estado_alumnomateria_desaprobada']. ", ";
            $sql.= " usumodi='" . $ses->getUsuario() . "',";
            $sql.= " fechamodi=CURRENT_TIMESTAMP";
            $sql.= " where aniocursada+5<".$anioActual." and ";
            $sql.= " idestadoalumnomateria in (".$this->POROTO->Config['estado_alumnomateria_libre'].",";
            $sql.= $this->POROTO->Config['estado_alumnomateria_cursadaaprobada'].")";
            if($testing==0){           
                if ($bOk!==false)  $bOk = $db->update($sql, '', true);
            }
            $cambios[]="Listado de Materias desaprobadas por estar libre o cursada hace 5 años.";
            foreach ($result as $alumno){
                $cambios[]=$alumno["idalumnomateria"]." "." Alumno: ".$alumno["apellido"]." ".$alumno["nombre"]." Materia: ".$alumno["materia"]." Año Cursada: ".$alumno["aniocursada"]." Estado: ".$alumno["estado"]." Nuevo Estado: DESAPROBADA";
            }
        }
        

	if ($bOk === false) {
		$db->rollback();
		$ses->setMessage("Se produjo un error al ejecutar el proceso.", SessionMessageType::TransactionError);
			header("Location: /verprocesos", TRUE, 302);
			exit();
	}
	 else 
	{
             if(count($cambios)>0){
                    $log=$this->POROTO->TempPath."proceso1-log-".date('Ymd')."-".date('Hi').".txt";
                    $ruta="proceso1-log-".date('Ymd')."-".date('Hi').".txt";
                    for($i=0;$i<count($cambios);$i++){
                        file_put_contents($log, $cambios[$i]."\n", FILE_APPEND);
                    }
                    $ses->setMessage("Proceso ejecutado con éxito. <br> <a href='/procesos/downloadFile?file=".$ruta."' >Ver log de acción</a><br>", SessionMessageType::Success);
             }else{
                 $ses->setMessage("Proceso ejecutado con éxito. <br> ", SessionMessageType::Success);
             }
	$db->commit();
	}
	$db->dbDisconnect();
	header("Location: /verprocesos", TRUE, 302);
	}
	
                
	function proceso2() {
	/*
	CREACIÓN AUTOMATICA DE EXAMENES
	SOLO FUNCIONA PARA EXAMENES CUATRIMESTRALES Y PROMOCION DONDE EN AMBOS SI O SI SE CREAN LOS EXAMENES
	PARA TODAS LAS COMISIONES DE LAS MATERIAS.
	*/
	$db =& $this->POROTO->DB; 
	$ses =& $this->POROTO->Session;
	$lib =& $this->POROTO->Libraries['siteLibrary'];
	
	$db->dbConnect("procesos/proceso2/");
	
	if(!$ses->tienePermiso('','Proceso 2 - Creacion de Examenes Automatica')){
				$ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
				header("Location: /verprocesos", TRUE, 302);
				exit();
	}
	
	
	$bOk =false;
	$dataTipoExamen = $_POST['tipexa'];
	$dataFecha  = $_POST['fecha'];
	$dataAnio  = $_POST['anio'];
        $dataAnioAnterior  = $_POST['anio'] -1;
	if (isset($_POST['crearfaltantes'])) $crearFaltantes = "1"; else $crearFaltantes = "0";
	//Booleano que si esta habilitado permite crear examenes si ya existen de ese tipo
	//solo que NO crea repetidos, si ya existe examen para esa comision, no lo vuelve a crear, crea de las que
	//no tengan.
	
	$errores="";
	
	//Validaciones
	//Verifica que no existan examenes del tipo elegido en el año actual de cursada. Si existen se deben borrar previamente.
	//Si es 1° cuatrimestre los crea.
	//Si es 2° cuatrimestre verifica que no existan examenes 1° cuatrimestre sin finalizar.
	//Si es promocion verifica que no existan examenes 2° cuatrimestre sin finalizar.
	
	if($errores=="" && $dataTipoExamen!=3 && $dataTipoExamen!=4 && $dataTipoExamen!=5){
		$errores.="Tipo de Examen inválido. ";
	}
	if($errores==""){ //Valido fecha
		if (!$lib->validateDate($dataFecha)) {
							   $errores.= "La Fecha es inválida";
		} 
		else {
			$d = $lib->datediff($dataFecha);
			if ($d > (365*10)) $errores.= "La Fecha es inválida (>10)";
			if ($d < (365*-10)) $errores.= "La Fecha es inválida (<10)";
		}
	}
	if($errores=="" && $dataAnio!=date("Y")){
		$errores.="Solo se pueden crear exámenes en el año de cursada actual. ";
		}
		
	if($errores=="" && $crearFaltantes==0){ 
		//Valido que no existan examenes de este tipo.	
		$sql = "select * from examenes e inner join comisiones c on e.idcomision=c.idcomision ";
		$sql.= "where e.estado=1 and e.idtipoexamen=".$dataTipoExamen." and c.estado=1 and c.anio=".$dataAnio;
		$result = $db->getSQLArray($sql);
		if (count($result)>0) {
			$errores.="Ya existen examenes del tipo seleccionado. Para ejecutar el proceso automático no debe haber ninguno.";
		}
	}
	
        if($errores=="" && ($dataTipoExamen==3 || $dataTipoExamen==4)){ //1 y 2° cuatrimestre valido si quedo alguno sin finalizar de año anterior.
		$sql = "select * from examenes e inner join comisiones c on e.idcomision=c.idcomision ";
		$sql.= "where e.estado=1 and e.examenfinalizado=0 and e.idtipoexamen in (3,4) and c.estado=1 and c.anio=".$dataAnioAnterior;
		$result = $db->getSQLArray($sql);
		if (count($result)>0) {
			$errores.="No es posible crear examenes cuatrimestrales del año actual si aun hay examenes cuatrimestrales del año anterior sin finalizar. Debe finalizarlos o eliminarlos si no son necesarios. ";
		}
	}
        
	if($errores=="" && $dataTipoExamen==4){ //2° cuatrimestre
		$sql = "select * from examenes e inner join comisiones c on e.idcomision=c.idcomision ";
		$sql.= "where e.estado=1 and e.examenfinalizado=0 and e.idtipoexamen=3 and c.estado=1 and c.anio=".$dataAnio;
		$result = $db->getSQLArray($sql);
		if (count($result)>0) {
			$errores.="Los examenes del 1° Cuatrimestre deben estar finalizados antes de crear los del 2° Cuatrimestre. ";
		}
	}
	if($errores=="" && $dataTipoExamen==5){ //Promocion
                $sql = "select valor from configuracion where parametro='promocion_automatizada'";
                $arrConfigDb = $db->getSQLArray($sql);
                if (count($arrConfigDb) == 1 && $arrConfigDb[0]['valor'] == 'Y') {
                            $errores.= "El tipo de examen PROMOCION esta deshabilitado por estar el parametro PROMOCION_AUTOMATIZADA habilitado";
                }
        }
        
        if($errores=="" && $dataTipoExamen==5){ //Promocion
		$sql = "select * from examenes e inner join comisiones c on e.idcomision=c.idcomision ";
		$sql.= "where e.estado=1 and e.examenfinalizado=0 and e.idtipoexamen=4 and c.estado=1 and c.anio=".$dataAnio;
		$result = $db->getSQLArray($sql);
		if (count($result)>0) {
			$errores.="Los examenes de 2° Cuatrimestre deben estar finalizados antes de crear los de Promoción. ";
		}
	}
	
	if($errores!=""){
		$ses->setMessage("Se produjo un error al ejecutar el proceso<br>".$errores, SessionMessageType::TransactionError);
		header("Location: /verprocesos", TRUE, 302);
		exit();
	}
	
	
	//Traigo todas las comisiones del año.
	$sql = "select distinct m.idmateria,c.idcomision, m.materiacompleta as descripcion, e.idexamen";
	$sql.= " from viewmaterias m inner join comisiones c on m.idmateria=c.idmateria ";
	$sql.= " left join examenes e on c.idcomision=e.idcomision and e.estado=1 and e.idtipoexamen=".$dataTipoExamen;
	$sql.= " where c.estado=1 ";
	$sql.= " and m.estado=1 and c.anio=".$dataAnio;
	if($dataTipoExamen==5) 	$sql.= " and m.promocionable=1";
	$sql.= " order by m.idmateria,c.idcomision";

	$result = $db->getSQLArray($sql);
	
	$db->begintrans();	
	
	$i=0;
	foreach ($result as $examen) {
		//echo($examen['idcomision']." ".$examen['idexamen']."<br>");
		if($examen['idexamen']==""){	//Condicion que obliga a no crear repetidos.
		$i++;
		//Inserto el examen de la comision
		$sql = "INSERT INTO examenes (idtipoexamen, idcomision, idmateria, ";
		$sql.= " idinstrumento, idarea, nombre, fecha,";
		$sql.= " aula, cupo, examenfinalizado, estado, usucrea, fechacrea, usumodi, fechamodi) ";
		$sql.= " SELECT " . $dataTipoExamen;
		$sql.= "," . $examen['idcomision'];
		$sql.= "," . $examen['idmateria'];
		$sql.= ",0";
		$sql.= ",0";
		$sql.= ",null";
		$sql.= ",'" . $lib->dateDMY2YMD($dataFecha)."'";
		$sql.= ",null";
		$sql.= ",0"; //cupo
		$sql.= ",0"; //Examen Finalizado
		$sql.= ",1"; //estado activo
		$sql.= ",'" . $ses->getUsuario() . "'";
		$sql.= ",CURRENT_TIMESTAMP";
		$sql.= ",null";
		$sql.= ",null";
		$newIdExamen = $db->insert($sql,'',true);
		$bOk = $newIdExamen;
		
		//Le cargo sus profesores
		$sql = "INSERT INTO examenprofesor (idexamen, idpersona) ";
		$sql.= "SELECT ".$newIdExamen . ",idpersona from comprofesor where idcomision=".$examen['idcomision'];
		if ($bOk!==false) $bOk = $db->insert($sql,'',true);

		//1° Cuatrimestre y 2° Cuatrimestre. crea el examen para todos los alumnos de la comision insertando 
		if ($dataTipoExamen=="3" || $dataTipoExamen=="4") {
			//traigo los alumnos de la comisión pero solo los que 
			//esten en estado CURSANDO (Cambio Leo 38 20170710)
			$sql = "INSERT INTO examenalumno (idexamen, idpersona, asistencia, nota, libro, ";
			$sql.= "tomo, folio, usucrea, fechacrea, usumodi, fechamodi) ";
			$sql.= "SELECT ".$newIdExamen.",ca.idpersona,null,null,null,null,null,";
			$sql.= "'" . $ses->getUsuario() . "',";
			$sql.= "CURRENT_TIMESTAMP,null,null ";
			$sql.= "FROM comalumno ca ";
			$sql.= "left join alumnomateria am on ca.idpersona=am.idpersona and ca.idcomision=am.idcomision ";
			$sql.= "WHERE ca.estado=1 AND ca.idcomision=".$examen['idcomision']." and ";
			$sql.= "am.idestadoalumnomateria=".$this->POROTO->Config['estado_alumnomateria_cursando'];
			if ($bOk!==false) $bOk = $db->insert($sql,'',true);
		}
		
		//Promocion agrego los inscriptos automaticamente
		if ($dataTipoExamen=="5") {
			$sql ="select am.idpersona,p.apellido,";
			$sql.="sum(case when amn.idtipoexamen=3 and amn.notaexamen >=";
			$sql.= $this->POROTO->Config['nota_parcial_aprobado_materia_no_promocionable'] ;
			$sql.=" then 1 else 0 end) primerparcialaprobado,";
			$sql.=" sum(case when amn.idtipoexamen=4 and amn.notaexamen >=";
			$sql.= $this->POROTO->Config['nota_segundo_parcial_aprobado_materia_promocionable'];
			$sql.=" then 1 else 0 end) segundoparcialaprobado,";
			$sql.="max(case when amn.idtipoexamen=3 then amn.notaexamen  else 0 end) primerparcialmax,";
			$sql.="max(case when amn.idtipoexamen=4 then amn.notaexamen  else 0 end) segundoparcialmax,";
			$sql.="(max(case when amn.idtipoexamen=3 then amn.notaexamen  else 0 end) + ";
			$sql.="max(case when amn.idtipoexamen=4 then amn.notaexamen  else 0 end)) / 2 promedio ";
			$sql.="from alumnomateria am inner join alumnomaterianota amn on amn.idalumnomateria=am.idalumnomateria ";
			$sql.="left join personas p on am.idpersona=p.idpersona where ";
			$sql.="am.idmateria=".$examen['idmateria']." and am.idcomision=".$examen['idcomision']." ";
			$sql.="and am.idestadoalumnomateria=";
			$sql.=$this->POROTO->Config['estado_alumnomateria_cursadaaprobada']." "; //Cursada aprobada
			$sql.="group by am.idpersona ";
			$sql.="having primerparcialaprobado>0 and segundoparcialaprobado>0 ";
			$arrAlumnosInscriptosAutomaticos = $db->getSQLArray($sql);
			foreach ($arrAlumnosInscriptosAutomaticos as $alu) {
				$sql = "INSERT INTO examenalumno (idexamen, idpersona, asistencia, nota, ";
				$sql.= "libro, tomo, folio, usucrea, fechacrea, usumodi, fechamodi)";
				$sql.= " SELECT " . $newIdExamen;
				$sql.= "," . $alu['idpersona'];
				$sql.= ", 1, ".$alu['promedio'].", null, null, null";
				$sql.= ",'" . $ses->getUsuario() . "'";
				$sql.= ",CURRENT_TIMESTAMP";
				$sql.= ",null";
				$sql.= ",null";
				if ($bOk!==false) $bOk = $db->insert($sql,'',true);
			}
		}
		}
		else{//del if no tiene examen asociada.
			$bOk=true; //El registro existe, no hay error.
		}
	}
	
	if ($bOk === false) {
		$db->rollback();
		$ses->setMessage("Se produjo un error al ejecutar el proceso.<br>".$errores, SessionMessageType::TransactionError);
	}
	 else 
	{
		$ses->setMessage("Proceso ejecutado con éxito. <br> Se crearon automaticamente ".$i." exámenes.", SessionMessageType::Success);
		$db->commit();
	}
	
	$db->dbDisconnect();
	
	
	header("Location: /verprocesos", TRUE, 302);
	}
        
        function proceso3($testing) {
	/*
	PROCESO A EJECUTARSE EN MARZO UNA VEZ QUE SE FINALIZARON TODAS LAS MATERIAS DEL AÑO ANTERIOR PARA ACTUALIZAR
        EL ESTADO DE LA CARRERA DE CADA ALUMNO, POR EL MOMENTO PARA FOBA
        FOBA CON TODO TERCERO CURSADA APROBADA Y PRACTICA DE CONJ APROBADA --> PRE FINALIZADA
        FOBA TODO APROBADO --> FINALIZADA
	*/
        include($this->POROTO->ModelPath . '/alumnocarrera.php');
        $claseAlumnoCarrera = new AlumnoCarreraModel($this->POROTO);
        
	$db =& $this->POROTO->DB; 
	$ses =& $this->POROTO->Session;
	$lib =& $this->POROTO->Libraries['siteLibrary'];
	
        $db->dbConnect("procesos/proceso3/");
	$db->begintrans();	
        $bOk=true;
        $cambios=array();
        
	if(!$ses->tienePermiso('','Proceso 3 - Proceso Finalizar Carreras')){
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /verprocesos", TRUE, 302);
            exit();
	}
	
        $result = $claseAlumnoCarrera->getAlumnoCarrera(); //OBtengo AlumnoCarrera sin importar de que carrera sea
        
        if($testing==0){
            $cambios[]="EJECUCION";
        }
        else
        {
            $cambios[]="SIMULACION";
        }
        //1) Chequeo FINALIZADA.
        $cambios[]="Listado de alumnos con carrera para finalizar.";
        foreach ($result as $alumnocarrera) {
            $sMsg=$claseAlumnoCarrera->verificarCambioEstadoCarrera($alumnocarrera["idalumnocarrera"],2); 
            if($sMsg==""){
                $params["idalumnocarrera"] = $alumnocarrera["idalumnocarrera"];
                $params["idestado"] = 2;
                $params["usuario"] = $ses->getUsuario();
                if($testing==0){
                    $claseAlumnoCarrera->guardarCambioEstadoCarrera($params);
                }
                $cambios[]="Alumno: ".$alumnocarrera["idpersona"]." ".$alumnocarrera["alumno"]." Carrera:".$alumnocarrera["idalumnocarrera"]." ".$alumnocarrera["carrera"]." Instrumento:".$alumnocarrera["instrumento"]." Carrera FINALIZADA";
            }
        }
        
        //2) Chequeo PRE FINALIZADA solo para las FOBA.
        $cambios[]="Listado de alumnos con carrera para pre finalizar en FOBA.";
        foreach ($result as $alumnocarrera) {
            $idpersona=$alumnocarrera['idpersona'];
            $idcarrera=$alumnocarrera['idcarrera'];
            $idinstrumento=$alumnocarrera['idinstrumento'];
            $idalumnocarrera=$alumnocarrera['idalumnocarrera'];
            if($idcarrera==1 || $idcarrera==5){ //FOBA
                $sMsg=$claseAlumnoCarrera->verificarCambioEstadoCarrera($idalumnocarrera, 3);
                if($sMsg==""){
                    $params["idalumnocarrera"] = $alumnocarrera["idalumnocarrera"];
                    $params["idestado"] = 3;
                    $params["usuario"] = $ses->getUsuario();
                    if($testing==0){
                        $claseAlumnoCarrera->guardarCambioEstadoCarrera($params);
                    }
                    $cambios[]="Alumno: ".$alumnocarrera["idpersona"]." ".$alumnocarrera["alumno"]." Carrera:".$alumnocarrera["idalumnocarrera"]." ".$alumnocarrera["carrera"]." Instrumento:".$alumnocarrera["instrumento"]." Carrera PREFINALIZADA";
                }
            } //Alguna de las FOBA
        } //Por cada alumnocarrera
        
	if ($bOk === false) {
		$db->rollback();
		$ses->setMessage("Se produjo un error al ejecutar el proceso.<br>", SessionMessageType::TransactionError);
	}
	 else 
	{
             if(count($cambios)>0){
                    $log=$this->POROTO->TempPath."proceso3-log-".date('Ymd')."-".date('Hi').".txt";
                    $ruta="proceso3-log-".date('Ymd')."-".date('Hi').".txt";
                    for($i=0;$i<count($cambios);$i++){
                        file_put_contents($log, $cambios[$i]."\n", FILE_APPEND);
                    }
                    $ses->setMessage("Proceso ejecutado con éxito. <br> <a href='/procesos/downloadFile?file=".$ruta."' >Ver log de acción</a><br>", SessionMessageType::Success);
             }else{
                 $ses->setMessage("Proceso ejecutado con éxito. <br> ", SessionMessageType::Success);
             }
		$db->commit();
	}
	
	$db->dbDisconnect();
	header("Location: /verprocesos", TRUE, 302);
	}
        
        function proceso4($testing,$desaprobar){
        /*
            PROCESO QUE RECORRE ALUMNOMATERIA Y A TODOS AQUELLOS REGISTROS QUE ESTAN CONDICIONALES, EN PRIMER LUGAR
         * VUELVE A EJECUTAR LAS REGLAS Y EN CASO DE QUE LAS CUMPLA, QUITA LOS TILDES DE CONDICIONALES.
         * A SU VEZ SI LA VARIABLE DESAPROBAR ESTA ACTIVADA, VUELVE A RECORRER TODOS LOS CONDICIONALES Y LOS DESAPRUEBA.
	*/
        //include($this->POROTO->ModelPath . '/alumnocarrera.php');
        //$claseAlumnoCarrera = new AlumnoCarreraModel($this->POROTO);
        include($this->POROTO->ModelPath . '/alumnomateria.php');
        include($this->POROTO->ControllerPath . '/correlativas.php');
        
	$db =& $this->POROTO->DB; 
	$ses =& $this->POROTO->Session;
	$lib =& $this->POROTO->Libraries['siteLibrary'];
	
        $db->dbConnect("procesos/proceso4/");
	$db->begintrans();	
        $bOk=true;
        $cambios=array();
  
	if(!$ses->tienePermiso('','Proceso 4 - Proceso Actualizar/Desaprobar Condicionales')){
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /verprocesos", TRUE, 302);
            exit();
	}
	   
        if($testing==0){
            $cambios[]="EJECUCION Desaprobar:".$desaprobar;
        }
        else
        {
            $cambios[]="SIMULACION Desaprobar:".$desaprobar;
        }
        $cambios[]=" ";
        
        $claseAlumnoMateria = new AlumnoMateriaModel($this->POROTO);
        $result=$claseAlumnoMateria->getAlumnoMateriaCondicionales();
        $claseCorrelativas = new correlativas($this->POROTO);
        
        $cambios[]="Listado de alumnos actualizar condicionalidades.";
        
        foreach ($result as $alumnomateria) {
            //Obtengo las correlativas y si las cumple o no.
            $resultados=$claseCorrelativas->getCorrelativasAlumno($alumnomateria["idcarrera"], $alumnomateria["idmateria"], $alumnomateria["idcomision"], $alumnomateria["idpersona"],$alumnomateria["idalumnocarrera"]);
            $valida=true;
            foreach($resultados as $regla){
                if ($regla["idregla"]!=6 && $regla["estado"]==false) $valida=false;
            }

            if($valida){//Si cumple con las correlativas, quito la condicionalidad
                if($testing==0){
                    //Actualizar registro
                    $res=$claseAlumnoMateria->quitarCondicionalidad($alumnomateria["idalumnomateria"]);   
                }
                $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]." ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]." Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]." Materia:".$alumnomateria["nombre"]." Quitar Condicionalidad";    
                  
            }else{
                //No quito la condicionalidad porque sigue condicional
                $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]." ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]." Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]." Materia:".$alumnomateria["nombre"]." Continua Cond.";
            }
        }
        
        if($desaprobar==1 && $testing==0){
        $result=$claseAlumnoMateria->getAlumnoMateriaCondicionales();       
        $cambios[]="---";
        $cambios[]="Listado de alumnos desaprobar condicionales.";
        foreach ($result as $alumnomateria) {
            $claseAlumnoMateria->desaprobarCondicionales($alumnomateria["idalumnocarrera"],$alumnomateria["idpersona"],$alumnomateria["idalumnomateria"],$this->POROTO->Config['estado_alumnomateria_desaprobada'],$ses->getUsuario(),0);
            $res=$claseAlumnoMateria->quitarCondicionalidad($alumnomateria["idalumnomateria"]);   
            $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]." ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]." Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]." Materia:".$alumnomateria["nombre"]." DESAPROBADA.";
        }
        }

        
	if ($bOk === false) {
		$db->rollback();
		$ses->setMessage("Se produjo un error al ejecutar el proceso.<br>", SessionMessageType::TransactionError);
	}
	 else 
	{
             if(count($cambios)>0){
                    $log=$this->POROTO->TempPath."proceso4-log-".date('Ymd')."-".date('Hi').".txt";
                    $ruta="proceso4-log-".date('Ymd')."-".date('Hi').".txt";
                    for($i=0;$i<count($cambios);$i++){
                        file_put_contents($log, $cambios[$i]."\n", FILE_APPEND);
                    }
                    $ses->setMessage("Proceso ejecutado con éxito. <br> <a href='/procesos/downloadFile?file=".$ruta."' >Ver log de acción</a><br>", SessionMessageType::Success);
             }else{
                 $ses->setMessage("Proceso ejecutado con éxito. <br> ", SessionMessageType::Success);
             }
		$db->commit();
	}
	
	$db->dbDisconnect();
	header("Location: /verprocesos", TRUE, 302);
        }
        
        function proceso5($testing,$desaprobar){
        
        include($this->POROTO->ModelPath . '/alumnomateria.php');
        include($this->POROTO->ControllerPath . '/correlativas.php');
        
	$db =& $this->POROTO->DB; 
	$ses =& $this->POROTO->Session;
	$lib =& $this->POROTO->Libraries['siteLibrary'];
	
        $db->dbConnect("procesos/proceso5/");
	$db->begintrans();	
        $bOk=true;
        $cambios=array();
  
	if(!$ses->tienePermiso('','Proceso 5 - Proceso Actualizar/Desaprobar Condicionales')){
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /verprocesos", TRUE, 302);
            exit();
	}
	   
        if($testing==0){
            $cambios[]="EJECUCION Desaprobar:".$desaprobar;
        }
        else
        {
            $cambios[]="SIMULACION Desaprobar:".$desaprobar;
        }
        $cambios[]=" ";
        $materiasADesaprobar = explode(",",$ses->getParametroConfiguracion("reglasimultaneidad_materias_desaprobar"));
        $claseAlumnoMateria = new AlumnoMateriaModel($this->POROTO);
        $result=$claseAlumnoMateria->getAlumnoMateriaCondicionalesSimultaneos();
        $claseCorrelativas = new correlativas($this->POROTO);
        
        $cambios[]="Listado de alumnos actualizar condicionalidades regla simultaneidad.";
        
        foreach ($result as $alumnomateria) {
            //1 Obtengo las correlativas para verificar nuevamente si cumple
            $resultados=$claseCorrelativas->getCorrelativasAlumno($alumnomateria["idcarrera"], $alumnomateria["idmateria"], $alumnomateria["idcomision"], $alumnomateria["idpersona"],$alumnomateria["idalumnocarrera"]);
            $valida=true;
            //2 Guardo si cumple regla 6 de simultaneas
            foreach($resultados as $regla){
                if ($regla["idregla"]==6 && $regla["estado"]==false) $valida=false;
            }

            if($valida){
            //3 Si cumple la regla debo quitar tilde de condicional simultaneo
                if($testing==0){
            //4 Quito tilde de condicional simultaneo
                    $res=$claseAlumnoMateria->quitarCondicionalidadSimultaneos($alumnomateria["idalumnomateria"]);   
                }
                $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]."| ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]."| Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]."| Materia:".$alumnomateria["materia"]."| Estado:".$alumnomateria["estado"]."| Accion: Quitar tilde Condicionalidad Simultanea";    
            }else{
            //5 Sigue sin cumplir con la regla de condicionalidad simultanea
            if($alumnomateria["idestadoalumnomateria"]==$this->POROTO->Config['estado_alumnomateria_libre'] || $alumnomateria["idestadoalumnomateria"]==$this->POROTO->Config['estado_alumnomateria_desaprobada'] || $alumnomateria["idestadoalumnomateria"]==$this->POROTO->Config['estado_alumnomateria_cancelada'] || $alumnomateria["idestadoalumnomateria"]==$this->POROTO->Config['estado_alumnomateria_aprobada']  || $alumnomateria["idestadoalumnomateria"]==$this->POROTO->Config['estado_alumnomateria_aprobadaxequiv'] || $alumnomateria["idestadoalumnomateria"]==$this->POROTO->Config['estado_alumnomateria_cursadaaprobada']){
            //6 Si el estado actual es LIBRE, DESAPROBADA, CANCELADA, APROBADA, EQUIV, CURSADA APROBADA quito el tilde ya que no es necesario.    
                    if($testing==0){
                        $res=$claseAlumnoMateria->quitarCondicionalidadSimultaneos($alumnomateria["idalumnomateria"]);   
                    }
                    $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]."| ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]."| Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]."| Materia:".$alumnomateria["materia"]."| Estado:".$alumnomateria["estado"]."| Accion: Quitar tilde Condicionalidad Simultanea por estado de la materia";
            }else{
            //7 Continua condicionalidad simultanea
                
            //8 Si se puso el tilde de desaprobar
            if($desaprobar==1){
                if(in_array($alumnomateria["idmateria"],$materiasADesaprobar)){
            //9 Verifico si la materia esta entre las que deberia desaprobar por no cumplir la regla.
                if($testing==0){
                    $claseAlumnoMateria->desaprobarCondicionales($alumnomateria["idalumnocarrera"],$alumnomateria["idpersona"],$alumnomateria["idalumnomateria"],$this->POROTO->Config['estado_alumnomateria_desaprobada'],$ses->getUsuario(),0);
                    $res=$claseAlumnoMateria->quitarCondicionalidadSimultaneos($alumnomateria["idalumnomateria"]);   
                }
                $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]."| ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]."| Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]."| Materia:".$alumnomateria["materia"]."| Estado:".$alumnomateria["estado"]."| Accion: Quitar tilde Condicionalidad Simultanea y DESAPROBAR.";
                }
                else{
                $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]."| ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]."| Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]."| Materia:".$alumnomateria["materia"]."| Estado:".$alumnomateria["estado"]."| Continua Condicionalidad Simultanea.";                    
                }
            }else{
                $cambios[]="Alumno: ".$alumnomateria["idalumnomateria"]."| ".$alumnomateria["alumno"]." ".$alumnomateria["documento"]."| Carrera:".$alumnomateria["idalumnocarrera"]." ".$alumnomateria["carrera"]."| Materia:".$alumnomateria["materia"]."| Estado:".$alumnomateria["estado"]."| Continua Condicionalidad Simultanea.";                    
            }
            }   
            }
        } 
        
        if ($bOk === false) {
		$db->rollback();
		$ses->setMessage("Se produjo un error al ejecutar el proceso.<br>", SessionMessageType::TransactionError);
	}
	 else 
	{
             if(count($cambios)>0){
                    $log=$this->POROTO->TempPath."proceso5-log-".date('Ymd')."-".date('Hi').".txt";
                    $ruta="proceso5-log-".date('Ymd')."-".date('Hi').".txt";
                    for($i=0;$i<count($cambios);$i++){
                        file_put_contents($log, $cambios[$i]."\n", FILE_APPEND);
                    }
                    $ses->setMessage("Proceso ejecutado con éxito. <br> <a href='/procesos/downloadFile?file=".$ruta."' >Ver log de acción</a><br>", SessionMessageType::Success);
             }else{
                 $ses->setMessage("Proceso ejecutado con éxito. <br> ", SessionMessageType::Success);
             }
		$db->commit();
	}
	
	$db->dbDisconnect();
	header("Location: /verprocesos", TRUE, 302);
        
        }
}

?>