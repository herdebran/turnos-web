<?php 
/**
  * General Site Library
  *
  * Lots of useful functions are packed here
  * 
  *
  * @package  poroto
  * @version  1.2
  * @access   public
  * @copyright 2015-2017 7dedos
  * @author Augusto Wloch <agosto@7dedos.com>
  */
if ( ! defined('POROTO')) exit('No direct script access allowed');

class siteLibrary {
	private $POROTO;
	private $DB;
	private $howDarkerIconBorderIs;
	private $acciones;
	private $debug = false;

	function __construct($poroto) {
		$this->POROTO=$poroto;
		$this->howDarkerIconBorderIs = 10;

		// $this->DB =& $this->POROTO->DB; 
		// $this->DB->dbConnect();

	    if ($this->POROTO->Session->isLogged()) { 
	    //   //levanto de la base de datos la info del usuario
	    //   $this->_getUserData($this->usuId,FALSE);
	      
	    } else {
	      if (! $this->isPublicZone()) {
	      	header("Location: /login", TRUE, 302);
	      }
	    }

	    //PARCHE PARA MAGIC QUOTES EN PHP <=5.2
		if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) ) ) {
			foreach ($_POST as $k=>&$v) {
				if (is_array($v)) {
					foreach ($v as $i=>&$j) if (!is_array($j)) $j=stripcslashes($j);
				} else {
					$v=stripcslashes($v);
				}
			}
		}
		//FIN PARCHE

		$this->acciones = array(	"date"   => array("eq"=>"igual a", "ne"=>"distinto de", "gt"=>"luego de", "lt"=>"antes de", "be"=>"entre","nu"=>"vacio","nn"=>"no vacio"),
									"list"   => array("va"=>"valores en","nu"=>"vacio","nn"=>"no vacio"),
									"text"   => array("eq"=>"igual a", "co"=>"contiene","nu"=>"vacio","nn"=>"no vacio"),
									"number" => array("eq"=>"igual a", "gt"=>"mayor a", "lt"=>"menor a","be"=>"entre","nu"=>"vacio","nn"=>"no vacio"),
		 );


	}

	public function getArrayAcciones() {
		return($this->acciones);
	}
	public function getArrayAccionesPorTipo($tipo) {
		return($this->acciones[$tipo]);
	}

	private function isPublicZone() {
		if (count($this->POROTO->URI) == 2 && strtolower($this->POROTO->URI[0]) == 'home') {
			if (strtolower($this->POROTO->URI[1]) == 'mailtest') return(true);
			if (strtolower($this->POROTO->URI[1]) == 'forgot') return(true);
			if (strtolower($this->POROTO->URI[1]) == 'login') return(true);
			if (strtolower($this->POROTO->URI[1]) == 'logout') return(true);
			if (strtolower($this->POROTO->URI[1]) == 'reempadronamiento') return(true);
			if (strtolower($this->POROTO->URI[1]) == 'inscripcion') return(true);
		} 
		if (count($this->POROTO->URI) == 3 && strtolower($this->POROTO->URI[0]) == 'home' && strtolower($this->POROTO->URI[1]) == 'ajaxlocalidades') return(true);
		if (count($this->POROTO->URI) == 3 && strtolower($this->POROTO->URI[0]) == 'home' && strtolower($this->POROTO->URI[1]) == 'ajaxnivelescarrera') return(true);
		if (count($this->POROTO->URI) == 3 && strtolower($this->POROTO->URI[0]) == 'home' && strtolower($this->POROTO->URI[1]) == 'ajaxnivelescarrerainscripcion') return(true);

		return(false);
	}

	public function isPasswordValid ($password) {
		// recordar sincronizar cualquier cambio en estas reglas, con el valor de
		// configuracion: $config['password_constraints_explained']
		$bOk = true;
		if (strlen(trim($password)) < 6) $bOk = false;
		if (!ctype_alnum($password)) $bOk =false;
		return ($bOk);
	}

	public function getImageHandler ($filepath, $type) {
		$im = false;
		switch ($type) { 
			case 1 : 
				$im = imageCreateFromGif($filepath); 
				break; 
			case 2 : 
				$im = imageCreateFromJpeg($filepath); 
				break; 
			case 3 : 
				$im = imageCreateFromPng($filepath); 
				break; 
		}    
		return $im;  		
	}

	public function dateDiff($date1, $date2="") {
		$a = explode("/", $date1);
		if (count($a)!=3) return false;
		if ($date2=="") {
			$b = explode("/", date("d/m/Y"));
		} else {
			$b = explode("/", $date2);
		}
		if (count($b)!=3) return false;

		$f1 = gregoriantojd($a[1], $a[0], $a[2]);
		$f2 = gregoriantojd($b[1], $b[0], $b[2]);

		return ($f2-$f1);
	}

	public function dateDMY2YMD($date) {
		$d = explode("/", $date);
		return ($d[2] . "-" . $d[1] . "-" . $d[0]);
	}
	public function validateDate($date, $format = 'd/m/Y') {
		if (! defined("PHP_VERSION_ID")) define ("PHP_VERSION_ID", 50205);
		if (PHP_VERSION_ID <50300) {
			$bOk = true;
			$d = explode("/", $date);
			if (count($d)!=3) return false;
			if ($d[2]<1800 || $d[2]>2100) $bOk=false; 
			if ($d[1]<1 || $d[1]>12) $bOk=false;
			switch ($d[1]) {
			 	case "1":
			 	case "3":
			 	case "5":
			 	case "7":
			 	case "8":
			 	case "10":
			 	case "12":
					if ($d[0]<1 || $d[0]>31) $bOk=false;
			 		break;
			 	case "2":
			 		if (($d[2]%4) == 0) if ($d[0]<1 || $d[0]>29) $bOk=false; else if ($d[0]<1 || $d[0]>28) $bOk=false;
			 		break;
			 	default:
					if ($d[0]<1 || $d[0]>30) $bOk=false;
			 		break;
			 } 
			return ($bOk); 
		} else {
		    $d = DateTime::createFromFormat($format, $date);
		    return $d && $d->format($format) == $date;
		}
	}

	public function getMonthNameShort($month, $lang="ES") {
		switch (intval($month)) {
			case 1: return "ENE"; break;
			case 2: return "FEB"; break;
			case 3: return "MAR"; break;
			case 4: return "ABR"; break;
			case 5: return "MAY"; break;
			case 6: return "JUN"; break;
			case 7: return "JUL"; break;
			case 8: return "AGO"; break;
			case 9: return "SEP"; break;
			case 10: return "OCT"; break;
			case 11: return "NOV"; break;
			default: return "DIC";
		}
	}

	public function getMonthNameLong($month, $lang="ES") {
		switch (intval($month)) {
			case 1: return "Enero"; break;
			case 2: return "Febrero"; break;
			case 3: return "Marzo"; break;
			case 4: return "Abril"; break;
			case 5: return "Mayo"; break;
			case 6: return "Junio"; break;
			case 7: return "Julio"; break;
			case 8: return "Agosto"; break;
			case 9: return "Septiembre"; break;
			case 10: return "Octubre"; break;
			case 11: return "Noviembre"; break;
			case 12: return "Diciembre"; break;
			default: return "---";
		}
	}

 	public function getRequiredFieldIcon() { return (" <span class=\"required-field glyphicon glyphicon-flag\" aria-hidden=\"true\" title=\"Campo requerido\"></span>"); }
 	public function getCreateRecordIcon()  { return (" <span class=\"glyphicon glyphicon-plus\" title=\"Crear nuevo registro\"></span>"); }
 	public function getCreatePhotoIcon()  { return (" <span class=\"glyphicon glyphicon-camera\" title=\"Fotos\"></span>"); }
 	public function getCreateNotesIcon()  { return (" <span class=\"glyphicon glyphicon-book\" title=\"Notas\"></span>"); }
 	public function getCreateInventoryIcon()  { return (" <span class=\"glyphicon glyphicon-check\" title=\"Arqueos\"></span>"); }
 	public function getCreateDetailIcon()  { return (" <span class=\"glyphicon glyphicon-list\" title=\"Detalles\"></span>"); }
 	
 	public function getDetailRecordIcon()  { return ("<i class=\"fa fa-search-plus\"></i>"); }
 	public function getDeleteRecordIcon()  { return ("<i class=\"fa fa-times\"></i>"); }
 	public function getUpdateRecordIcon()  { return ("<i class=\"fa fa-pencil\"></i>"); }
 	public function getArqueoRecordIcon()  { return ("<i class=\"fa fa-adjust\"></i>"); }
 	
 	private function getAdminIcon()  { return (" <span class=\"glyphicon glyphicon-tower\" title=\"Admin\"></span>"); }
 	public function getUserNameFormated($userName, $userAdmin) {
 		$out = "";
		if ($userAdmin) $out .= $this->getAdminIcon() . "";
		$out .= $userName;
		return ($out);
 	}


	public function getSuperficie ($ancho, $largo) {
		$w = intval($ancho);
		$h = intval($largo);
		$sup = $w*$h;
		$supm3 = round($sup / 10000,2);
		return ($supm3);
	}







/******************************************************************************************************************************
 * CLASS::METHOD  SiteLibrary::uploadImage
 * DESCRIPTION    subo una foto. genera el thumbnail
 * TYPE (p/f/t/o) transactional
 * RETURN         boolean - true if no error raised, false if something went wrong (in validationErrors will be the detail)
 *
 * DATA VALIDATIONS ***********************************************************************************************************
 *
 * FORM VALIDATIONS ***********************************************************************************************************
 *
 * PARAMETERS *****************************************************************************************************************
 *-> fileIndexName          name of the uploaded file into $_FILES
 *-> imageFolderName        folder into webroot/images , where the full image will be stored
 *-> tmbFolderName          folder into webroot/images , where the thumbnail image will be stored
 *-> imageObjectId          id of the record associated to the image
 *-> validationErrors [O]   empty array that will be filled with error descriptions raised in the function
 *-> outputFileName   [O]   empty string that will be filled with the filename generated for the image
 *
 *****************************************************************************************************************************/
	public function uploadImage($fileIndexName, $imageFolderName, $tmbFolderName, $imageObjectId, &$validationErrors, &$outputFileName) { 
	    if (!isset($_FILES[$fileIndexName])) $validationErrors[] = "Im&aacute;gen no recibida";
	    $imageInfo = getimagesize($_FILES[$fileIndexName]['tmp_name']);
	    if ($imageInfo == false) $validationErrors[] = "File is not an image."; // Check if image file is a actual image or fake image
		if ($_FILES[$fileIndexName]['size'] > 10000000) $validationErrors[] = "Sorry, your file is too large [" . round($_FILES[$fileIndexName]['size']/1024) . " KB].";

		$file_name = basename($_FILES[$fileIndexName]['name']);
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		$imageWidth = $imageInfo[0];
		$imageHeight = $imageInfo[1];
		switch ($imageInfo[2]) {
			case 1: $realExtension = 'gif'; break;
			case 2: $realExtension = 'jpg'; break;
			case 3: $realExtension = 'png'; break;
			default:
				$validationErrors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		}

		if (count($validationErrors)==0) {
			$outputFileName = substr("000000" . $imageObjectId,-6) . date("ymdHis") . substr(uniqid(),-2) . "." . $realExtension;
			$tempFile = $this->POROTO->TempPath . $outputFileName;
		    if (move_uploaded_file($_FILES[$fileIndexName]['tmp_name'], $tempFile)) {
				$targetRatio = $this->POROTO->Config['fotos_import_width'] / $this->POROTO->Config['fotos_import_height'];
				$imageRatio = $imageWidth / $imageHeight;

				if ($targetRatio > $imageRatio) { //la imagen deberia ser mas ancha y menos alta (recorto altura)
					$tempHeight = $imageWidth / $targetRatio;
					$deltaHeight = $imageHeight - $tempHeight;
					$finalWidth = ($imageWidth < $this->POROTO->Config['fotos_import_width'] ? $imageWidth : $this->POROTO->Config['fotos_import_width']);
					$finalHeight = ($tempHeight < $this->POROTO->Config['fotos_import_height'] ? $tempHeight : $this->POROTO->Config['fotos_import_height']);
					$img_temp = imagecreatetruecolor($finalWidth, $finalHeight);
					$img_old = $this->getImageHandler($tempFile,$imageInfo[2]);
					imagecopyresampled($img_temp, $img_old, 0, 0, 0, $deltaHeight/2, $finalWidth, $finalHeight, $imageWidth, $tempHeight);

					switch ($imageInfo[2]) {
						case 1: imagegif($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
						case 2: imagejpeg($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
						case 3: imagepng($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
					}
				} else if ($targetRatio < $imageRatio) { //la imagen deberia ser mas angosta y mas alta (recorto ancho)
					$tempWidth = $imageHeight * $targetRatio;
					$deltaWidth = $imageWidth - $tempWidth;
					$finalWidth = ($tempWidth < $this->POROTO->Config['fotos_import_width'] ? $tempWidth : $this->POROTO->Config['fotos_import_width']);
					$finalHeight = ($imageHeight < $this->POROTO->Config['fotos_import_height'] ? $imageHeight : $this->POROTO->Config['fotos_import_height']);
					$img_temp = imagecreatetruecolor($finalWidth, $finalHeight);
					$img_old = $this->getImageHandler($tempFile,$imageInfo[2]);
					imagecopyresampled($img_temp, $img_old, 0, 0, $deltaWidth/2, 0, $finalWidth, $finalHeight, $tempWidth, $imageHeight);

					switch ($imageInfo[2]) {
						case 1: imagegif($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
						case 2: imagejpeg($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
						case 3: imagepng($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
					}
				}  else { //same ratio! just resize if neededd
					$finalWidth = ($imageWidth < $this->POROTO->Config['fotos_import_width'] ? $imageWidth : $this->POROTO->Config['fotos_import_width']);
					$finalHeight = ($imageHeight < $this->POROTO->Config['fotos_import_height'] ? $imageHeight : $this->POROTO->Config['fotos_import_height']);

					$img_temp = imagecreatetruecolor($finalWidth, $finalHeight);
					$img_old = $this->getImageHandler($tempFile,$imageInfo[2]);
					imagecopyresampled($img_temp, $img_old, 0, 0, 0, 0, $finalWidth, $finalHeight, $imageWidth, $imageHeight);

					switch ($imageInfo[2]) {
						case 1: imagegif($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
						case 2: imagejpeg($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
						case 3: imagepng($img_temp, $this->POROTO->WebRootPath . "images/" . $tmbFolderName . "/" . $outputFileName); break;
					}

				}

			    rename($tempFile, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName);
			 //    copy($tempFile, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName);
				// unlink ($tempFile); //delete temp image
		    } else {
		        $validationErrors[] = "There was an error moving/uploading your file";
		    }
		} 

		return (count($validationErrors)==0);
	}



/******************************************************************************************************************************
 * CLASS::METHOD  SiteLibrary::uploadAvatar
 * DESCRIPTION    sube un avatar. bajando la imagen a la resolucion indicada
 * TYPE (p/f/t/o) transactional
 * RETURN         boolean - true if no error raised, false if something went wrong (in validationErrors will be the detail)
 *
 * DATA VALIDATIONS ***********************************************************************************************************
 *
 * FORM VALIDATIONS ***********************************************************************************************************
 *
 * PARAMETERS *****************************************************************************************************************
 *-> fileIndexName          name of the uploaded file into $_FILES
 *-> imageFolderName        folder into webroot/images , where the image will be stored
 *-> imageObjectId          id of the record associated to the image
 *-> validationErrors [O]   empty array that will be filled with error descriptions raised in the function
 *-> outputFileName   [O]   empty string that will be filled with the filename generated for the image
 *
 *****************************************************************************************************************************/
	public function uploadAvatar($fileIndexName, $imageFolderName, $imageObjectId, &$validationErrors, &$outputFileName) { 
	    if (!isset($_FILES[$fileIndexName])) $validationErrors[] = "Im&aacute;gen no recibida";
	    $imageInfo = getimagesize($_FILES[$fileIndexName]['tmp_name']);
	    if ($imageInfo == false) $validationErrors[] = "File is not an image."; // Check if image file is a actual image or fake image
		if ($_FILES[$fileIndexName]['size'] > 10000000) $validationErrors[] = "Sorry, your file is too large [" . round($_FILES[$fileIndexName]['size']/1024) . " KB].";

		$file_name = basename($_FILES[$fileIndexName]['name']);
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		$imageWidth = $imageInfo[0];
		$imageHeight = $imageInfo[1];
		switch ($imageInfo[2]) {
			case 1: $realExtension = 'gif'; break;
			case 2: $realExtension = 'jpg'; break;
			case 3: $realExtension = 'png'; break;
			default:
				$validationErrors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		}

		if (count($validationErrors)==0) {
			$outputFileName = substr("000000" . $imageObjectId,-6) . date("ymdHis") . substr(uniqid(),-2) . "." . $realExtension;
			$tempFile = $this->POROTO->TempPath . $outputFileName;
		    if (move_uploaded_file($_FILES[$fileIndexName]['tmp_name'], $tempFile)) {
				$finalWidth = ($imageWidth < $this->POROTO->Config['avatar_import_width'] ? $imageWidth : $this->POROTO->Config['avatar_import_width']);
				$finalHeight = ($imageHeight < $this->POROTO->Config['avatar_import_height'] ? $imageHeight : $this->POROTO->Config['avatar_import_height']);

				$img_temp = imagecreatetruecolor($finalWidth, $finalHeight);
				$img_old = $this->getImageHandler($tempFile,$imageInfo[2]);
				imagecopyresampled($img_temp, $img_old, 0, 0, 0, 0, $finalWidth, $finalHeight, $imageWidth, $imageHeight);

				switch ($imageInfo[2]) {
					case 1: imagegif($img_temp, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName); break;
					case 2: imagejpeg($img_temp, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName); break;
					case 3: imagepng($img_temp, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName); break;
				}

			    //rename($tempFile, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName);
			 //    copy($tempFile, $this->POROTO->WebRootPath . "images/" . $imageFolderName . "/" . $outputFileName);
				unlink ($tempFile); //delete temp image
		    } else {
		        $validationErrors[] = "There was an error moving/uploading your file";
		    }
		} 

		return (count($validationErrors)==0);
	}



	public function procesarFiltro(&$filtroDef, &$filtroWhere, &$filtroHaving) {
		foreach ($_GET as $k=>$v) {
			$ope = substr($v,0,2);
			$val = substr($v,2);
			if (array_key_exists($k, $filtroDef)) {
				if (!array_key_exists(strtolower($filtroDef[$k]["tipo"]), $this->acciones)) return (false);
				if (!array_key_exists(strtolower($ope), $this->acciones[strtolower($filtroDef[$k]["tipo"])])) return (false);

				$filtroDef[$k]["parsed"] = $this->acciones[strtolower($filtroDef[$k]["tipo"])][strtolower($ope)];

				switch (strtolower($filtroDef[$k]["tipo"])) {
					case "list": 
						switch (strtolower($ope)) {
							case "va": //lista de valores separados por coma
								$values = explode(",", strtolower($val));
								if (count($values) == 0) return (false);
								foreach ($values as &$val) $val = "'" . $val . "'";
								$newFilter = $filtroDef[$k]["field"] . " in (" . implode(",", $values) . ")";
								$filtroDef[$k]["parsed"] .=  " (" . implode(",", $values) . ")";
								break;
							case "nu":
							case "nn":
								break;
							default:
								return (false);
						}
						break;


					case "date": //eq gt lt be nu nn
						if (strtolower($ope)!="nu" && strtolower($ope)!="nn" && strtolower($ope)!="be") {
							if (strlen($val)!=8) return(false);
							if (! $this->validateDate($val, "Ymd")) return (false);
							$dateValue="'" . substr($val, 0,4) . "-" . substr($val, 4, 2) . "-" . substr($val, 6,2) . "'";
							$readableValue = substr($val, 6,2) . "-" . substr($val, 4, 2) . "-" . substr($val, 0,4);
						}
						if (strtolower($ope)=="be") {
							if (strlen($val)!=16) return(false);
							if (! $this->validateDate(substr($val,0,8), "Ymd")) return (false);
							if (! $this->validateDate(substr($val,8,8), "Ymd")) return (false);
						}

						switch (strtolower($ope)) {
							case "eq":
								$newFilter = $filtroDef[$k]["field"] . " = " . $dateValue;
								$filtroDef[$k]["parsed"] .= " " . $readableValue;
								break;
							case "gt":
								$newFilter = $filtroDef[$k]["field"] . " > " . $dateValue;
								$filtroDef[$k]["parsed"] .= " " . $readableValue;
								break;
							case "lt":
								$newFilter = $filtroDef[$k]["field"] . " < " . $dateValue;
								$filtroDef[$k]["parsed"] .= " " . $readableValue;
								break;
							case "be":
								$dateValue1="'" . substr($val, 0,4) . "-" . substr($val, 4, 2) . "-" . substr($val, 6,2) . "'";
								$dateValue2="'" . substr($val, 8,4) . "-" . substr($val, 12, 2) . "-" . substr($val, 14,2) . "'";
								$readableValue1 = substr($val, 6,2) . "-" . substr($val, 4, 2) . "-" . substr($val, 0,4);
								$readableValue2 = substr($val, 14,2) . "-" . substr($val, 12, 2) . "-" . substr($val, 8,4);
								$newFilter = $filtroDef[$k]["field"] . " BETWEEN " . $dateValue1 . " AND " . $dateValue2;
								$filtroDef[$k]["parsed"] .= " "  . $readableValue1 . " / " . $readableValue2;
								break;
							case "nu":
							case "nn":
								break;
							default:
								return (false);
						}
						break;


					case "number": //eq ne gt lt be nu nn
						if (strtolower($ope)!="nu" && strtolower($ope)!="nn" && strtolower($ope)!="be") {
							if (strlen($val)==0) return(false);
							if (! is_numeric($val)) return (false);
						}

						switch (strtolower($ope)) {
							case "eq":
								$newFilter = $filtroDef[$k]["field"] . " = " . $val;
								$filtroDef[$k]["parsed"] .= " " . $val;
								break;
							case "ne":
								$newFilter = $filtroDef[$k]["field"] . " != " . $val;
								$filtroDef[$k]["parsed"] .= " " . $val;
								break;
							case "gt":
								$newFilter = $filtroDef[$k]["field"] . " > " . $val;
								$filtroDef[$k]["parsed"] .= " " . $val;
								break;
							case "lt":
								$newFilter = $filtroDef[$k]["field"] . " < " . $val;
								$filtroDef[$k]["parsed"] .= " " . $val;
								break;
							case "be":
								$values = explode(",", strtolower($val));
								if (count($values) != 2) return (false);
								if (! is_numeric($values[0])) return (false);
								if (! is_numeric($values[1])) return (false);
								$newFilter = $filtroDef[$k]["field"] . " BETWEEN " . $values[0] . " AND " . $values[1];
								$filtroDef[$k]["parsed"] .= " " . $values[0] . " / " . $values[1];
								break;
							case "nu":
							case "nn":
								break;
							default:
								return (false);
						}
						break;


					case "text": //eq co nu nn
						if (strtolower($ope)!="nu" && strtolower($ope)!="nn") {
							if (strlen($val)==0) return(false);
						}

						switch (strtolower($ope)) {
							case "eq":
								$newFilter = "LOWER(" . $filtroDef[$k]["field"] . ") = '" . strtolower($val) . "'";
								$filtroDef[$k]["parsed"] .= " '" . $val . "'";
								break;
							case "co":
								$newFilter = $filtroDef[$k]["field"] . " LIKE '%" . $val . "%'";
								$filtroDef[$k]["parsed"] .= " '" . $val . "'";
								break;
							case "nu":
							case "nn":
								break;
							default:
								return (false);
						}
						break;


					default:
						return (false);
				}

				//sin importar el tipo de dato nu y nn simempre son iguales
				if (strtolower($ope) == "nu") $newFilter = $filtroDef[$k]["field"] . " is null";
				if (strtolower($ope) == "nn") $newFilter = $filtroDef[$k]["field"] . " is not null";

				if (strtolower($filtroDef[$k]["filter"])=='where') 
					$filtroWhere .= " AND " . $newFilter;
				else 
					$filtroHaving .= " AND " . $newFilter;
			} // parametro GET q no es parte de filtro. skipeado
		}
		return (true);
	}


	public function CheckPhysicalFiles(&$dataArray, $filenameColumn, $outputCountColumn, $outputSizeColumn, $physicalFolder) {
		foreach ($dataArray as &$row) {
			$fn = $physicalFolder . $row[$filenameColumn];
			if (file_exists($fn)) { 
				$row[$outputCountColumn] = "1";
				$row[$outputSizeColumn] = filesize($fn);
			}
		}
	}

	public function getSize($bytes, $round=2) {
		$out = "";
		if ($bytes < 1024) {
			$out =  $bytes . " bytes";
		} else {
			if ($bytes < 1048576) {
				$out =  round($bytes/1024, $round) ." Kbytes";
			} else {
				$out =  round($bytes/1048576, $round) ." Mbytes";
			}
		}
		return ($out);
	}

	public function deletePhysicalFile ($file) {
		unlink ($file);
	}

	public function sendMail ($to, $title, $body) {
		ini_set('SMTP', $this->POROTO->Config['smtp_server_phpmailer']);  //OJO aca se cambio este mail por el smtp_server_address
		ini_set('sendmail_from', $this->POROTO->Config['smtp_mail_from']);
		$template = file_get_contents($this->POROTO->MailPath . $this->POROTO->Config['path_to_mail_template']);
		$template = str_replace($this->POROTO->Config['mail_title_replace_string'], $title, $template);
		$template = str_replace($this->POROTO->Config['mail_body_replace_string'], $body, $template);
		$email = wordwrap($template, 70, "\r\n"); 

		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=UTF-8";
		$headers[] = "From: '".$this->POROTO->Config['smtp_mail_from_name']."' <".$this->POROTO->Config['smtp_mail_from'].">";
		if ($this->POROTO->Config['carbon_copy_mail_address'] != "") $headers[] = "Bcc: " . $this->POROTO->Config['carbon_copy_mail_address'];
		return (mail($to, $title, $email, implode("\r\n", $headers)));
	}

}
