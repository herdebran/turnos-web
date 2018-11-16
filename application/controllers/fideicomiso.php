<?php

class Fideicomiso extends Controller {

    private $fideicomiso;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/fideicomiso.php');
        $this->fideicomiso = new ModeloFideicomiso($this->POROTO);
    }

    function gestion() {
        if (!$this->ses->tienePermiso('', 'Gestión de Fideicomisos - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        // ---------------------- Logica del metodo ----------------------------
        $fideicomiso = $this->fideicomiso;
        $params = array();
        $params['pageTitle'] = "Busqueda de fideicomisos";

        $params['viewDataTipoFideicomiso'] = $this->app->getAllTipoFideicomiso();
        $params['viewDataEstadoFideicomiso'] = $this->app->getAllEstadoFideicomiso();
        $params["roles"] = $this->app->getAllRoles();
        // ---------------------- Fin logica del metodo ------------------------
        $this->render("/gestion-fideicomiso.php", $params);
    }

    public function fideicomisosConFiltro($filter = null) {
        $fideicomiso = $this->fideicomiso;
        if (!$filter) {
            $filter = $_POST['filtros'];
        }
        $fideicomisos = $fideicomiso->getFideicomiso($filter);
        $json = array("data" => $fideicomisos);

        echo json_encode($json);
    }

    public function ajaxmunicipios($idprovincia) {
        $municipios = $this->app->ajaxAllMunicipios($idprovincia);
        echo json_encode($municipios);
    }

    public function ajaxlocalidades($idmunicipio) {
        $localidades = $this->app->ajaxAllLocalidades($idmunicipio);
        echo json_encode($localidades);
    }

    //Hernan 20180607
    public function crearfideicomiso($idfideicomiso = null) {
        $validationErrors = array();
        $db = & $this->POROTO->DB;
        $ses = & $this->POROTO->Session;
        $lib = & $this->POROTO->Libraries['siteLibrary'];
        $db->dbConnect("fideicomiso/guardarfideicomiso");

        if (!$ses->tienePermiso('', 'Guardar fideicomiso')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-fideicomisos", TRUE, 302);
            exit();
        }

        if (isset($_POST['descripcion'])) {
            $dataDescripcion = mb_strtoupper($db->dbEscape(trim($_POST['descripcion'])), 'UTF-8');
            $dataFechaConstitucion = mb_strtoupper($db->dbEscape(trim($_POST['fechaconst'])), 'UTF-8');
            $dataTipo = $db->dbEscape(trim(intval($_POST['tipo'])));
            $dataEstado = $db->dbEscape(trim(intval($_POST['estado'])));
            $dataCalle = mb_strtoupper($db->dbEscape(trim($_POST['calle'])), 'UTF-8');
            $dataNumero = mb_strtoupper($db->dbEscape(trim($_POST['numero'])), 'UTF-8');
            $dataLocalidad = $db->dbEscape(trim(intval($_POST['localidad'])));
            $dataLegalCalle = mb_strtoupper($db->dbEscape(trim($_POST['legalcalle'])), 'UTF-8');
            $dataLegalNumero = mb_strtoupper($db->dbEscape(trim($_POST['legalnumero'])), 'UTF-8');
            $dataLegalLocalidad = $db->dbEscape(trim(intval($_POST['legallocalidad'])));
            $dataDiaPrimerVenc = $db->dbEscape(trim(intval($_POST['diaprimervenc'])));
            $dataSegundoVencLiquida = isset($_POST['segundovencliquida']) ? "1" : "0";
            $dataDiaSegundoVenc = isset($_POST['segundovencdia']) ? $db->dbEscape(trim(intval($_POST['segundovencdia']))) : null;
            $dataSegundoVencRecargo = isset($_POST['segundovencrecargo']) ? $_POST['segundovencrecargo'] : "0";
            $dataTercerVencLiquida = isset($_POST['tercervencliquida']) ? "1" : "0";
            $dataDiaTercerVenc = isset($_POST['tercervencdia']) ? $db->dbEscape(trim(intval($_POST['tercervencdia']))) : null;
            $dataTercerVencRecargo = isset($_POST['tercervencrecargo']) ? $_POST['tercervencrecargo'] : "0";
            $dataTasaPunitorioDiario = isset($_POST['tasapunitoriodiario']) ? $_POST['tasapunitoriodiario'] : "0";

            // STAP! VALIDATION TIME
            if ($dataDescripcion == "")
                $validationErrors['descripcion'] = "El campo Descripcion es obligatorio";
            if ($dataFechaConstitucion == "") {
                $validationErrors['fechaconst'] = "El campo Fecha de constitucion es obligatorio";
            } else {
                if (!$lib->validateDate($dataFechaConstitucion))
                    $validationErrors['fechaconst'] = "El campo Fecha de constitucion es inválido";
            }
            if ($dataTipo == "" || $dataTipo == 0)
                $validationErrors['tipo'] = "El campo Tipo es obligatorio";
            if ($dataEstado == "" || $dataEstado == 0)
                $validationErrors['estado'] = "El campo Estado es obligatorio";
            if ($dataCalle == "")
                $validationErrors['calle'] = "El campo Calle es obligatorio";
            if ($dataNumero == "")
                $validationErrors['numero'] = "El campo Numero es obligatorio";
            if (!is_numeric($_POST['numero']))
                $validationErrors['numero'] = "El campo Número debe ser numérico";
            if ($dataLocalidad == "" || $dataLocalidad == 0)
                $validationErrors['localidad'] = "El campo Localidad es obligatorio";
            if ($dataLegalCalle == "")
                $validationErrors['legalcalle'] = "El campo Calle Legal es obligatorio";
            if ($dataLegalNumero == "")
                $validationErrors['legalnumero'] = "El campo Numero Legal es obligatorio";
            if (!is_numeric($_POST['legalnumero']))
                $validationErrors['legalnumero'] = "El campo Número Legal debe ser numérico";
            if ($dataLegalLocalidad == "" || $dataLegalLocalidad == 0)
                $validationErrors['legallocalidad'] = "El campo Localidad Legal es obligatorio";
            if ($dataDiaPrimerVenc == "")
                $validationErrors['diaprimervenc'] = "El dia de primer vencimiento es obligatorio";
            if (!is_numeric($_POST['diaprimervenc']))
                $validationErrors['diaprimervenc'] = "El dia de primer vencimiento debe ser numérico";
            //buscar tipo y nro de doc si ya existen

            if (count($validationErrors) == 0) {
                $params = array();
                $params["descripcion"] = $dataDescripcion;
                $params["fechaconstitucion"] = $lib->dateDMY2YMD($dataFechaConstitucion);
                $params["idtipofideicomiso"] = $dataTipo;
                $params["idestadofideicomiso"] = $dataEstado;
                $params["calle"] = $dataCalle;
                $params["numero"] = $dataNumero;
                $params["idlocalidad"] = $dataLocalidad;
                $params["legalcalle"] = $dataLegalCalle;
                $params["legalnumero"] = $dataLegalNumero;
                $params["legalidlocalidad"] = $dataLegalLocalidad;
                $params["diaprimervencimiento"] = $dataDiaPrimerVenc;
                $params["segundovencimientoliquida"] = $dataSegundoVencLiquida;
                $params["segundovencimientodia"] = $dataDiaSegundoVenc;
                $params["segundovencimientorecargo"] = $dataSegundoVencRecargo;
                $params["tercervencimientoliquida"] = $dataTercerVencLiquida;
                $params["tercervencimientodia"] = $dataDiaTercerVenc;
                $params["tercervencimientorecargo"] = $dataTercerVencRecargo;
                $params["tasapunitoriodiario"] = $dataTasaPunitorioDiario;
                $params["usuario"] = $ses->getUsuario();
                $fideicomiso = $this->fideicomiso;

                if ($idfideicomiso == null) {
                    // Alta nuevo fideicomiso
                    $bOk = $fideicomiso->nuevoFideicomiso($params);
                } else {
                    //Update fideicomiso existente
                    $params["idfideicomiso"] = $idfideicomiso;
                    $bOk = $fideicomiso->modificarFideicomiso($params);
                }
                if ($bOk["ok"] === false) {
                    $ses->setMessage("Se produjo un error al persistir." . $bOk["message"], SessionMessageType::TransactionError);
                    header("Location: /gestion-fideicomisos", TRUE, 302);
                    exit();
                }

                $ses->setMessage("Fideicomiso guardado con éxito", SessionMessageType::Success);
                header("Location: /gestion-fideicomisos", TRUE, 302);
                exit();
            } else {
                $ses->setMessage("Complete todos los campos.", SessionMessageType::TransactionError);
            }
        }
        $arrMenu = $this->POROTO->DB->getSQLArray($this->POROTO->getMenuSqlQuery());

        $viewDataProvincias = $this->app->getAllProvincias();
        $viewDataTipo = $this->app->getAllTipoFideicomiso();
        $viewDataEstado = $this->app->getAllEstadoFideicomiso();
        $db->dbDisconnect();

        if ($idfideicomiso == null) {
            $status = "alta";
            $pageTitle = "Alta de Fideicomiso";
        } else {
            $status = "modificacion";
            $pageTitle = "Edición de Fideicomiso";
            $viewDataFideicomiso = $this->fideicomiso->getFideicomisoById($idfideicomiso);
            $viewDataMunicipios=$this->app->ajaxAllMunicipios($viewDataFideicomiso['idprovincia']);
            $viewDataLocalidades=$this->app->ajaxAllLocalidades($viewDataFideicomiso['idmunicipio']);
            $viewDataLegalMunicipios=$this->app->ajaxAllMunicipios($viewDataFideicomiso['legalidprovincia']);
            $viewDataLegalLocalidades=$this->app->ajaxAllLocalidades($viewDataFideicomiso['legalidmunicipio']);
        }
        include($this->POROTO->ViewPath . "/-header.php");
        include($this->POROTO->ViewPath . "/crear-fideicomiso.php");
        include($this->POROTO->ViewPath . "/-footer.php");
    }

    public function activarfideicomiso($idfideicomiso, $valor) {
        $params = array();
        $ses = & $this->POROTO->Session;
        $params["activo"] = $valor;
        $params["idfideicomiso"] = $idfideicomiso;
        $params["usumodi"] = $ses->getUsuario();
        $fideicomiso = $this->fideicomiso;
        $bOk = $fideicomiso->inactivarFideicomiso($params);

        if ($bOk["ok"] === false) {
            $ses->setMessage("Se produjo un error al persistir." . $bOk["message"], SessionMessageType::TransactionError);
        } else {
            $ses->setMessage("Modificacion exitosa." . $bOk["message"], SessionMessageType::Success);
        }
        header("Location: /gestion-fideicomisos", TRUE, 302);
        exit();
    }

}
