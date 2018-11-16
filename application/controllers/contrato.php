<?php

class Contrato extends Controller {

    private $contrato;
    private $fideicomiso;
    private $unidadfuncional;
    private $persona;
    
    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/contrato.php');
        include($this->POROTO->ModelPath . '/fideicomiso.php');
        include($this->POROTO->ModelPath . '/unidadfuncional.php');
        include($this->POROTO->ModelPath . '/persona.php');
        $this->contrato = new ModeloContrato($this->POROTO);
        $this->fideicomiso = new ModeloFideicomiso($this->POROTO);
        $this->unidadfuncional = new ModeloUnidadFuncional($this->POROTO);
        $this->persona = new ModeloPersona($this->POROTO);
    }


    
    //Hernan 20180607
    public function crearcontrato($idunidadfuncional,$idcontrato = null,$solovista = 0) { 
        $validationErrors = array();
        $db = & $this->POROTO->DB;  //solo se usa para el menu
        $db->dbConnect("fideicomiso/crearcontrato");
        
        $ses = & $this->POROTO->Session;
        $lib =& $this->POROTO->Libraries['siteLibrary'];
        

        if(!$ses->tienePermiso('','Guardar contrato')){
                        $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
                        header("Location: /gestion-unidadesfuncionales", TRUE, 302);
                        exit();
        }

        $newIntegrantes=array();  
        $datosEstado=array();  
        if (isset($_POST['fideicomiso']) || isset($_POST['unidadfuncional'])) {
            $dataFideicomiso = intval($_POST['fideicomiso']);
            $dataUnidadfuncional = intval($_POST['unidadfuncional']);
            $dataAplicaAjusteIcc=isset($_POST['aplicaajusteicc']) ? "1" : "0"; 
            $dataFechaAlta = mb_strtoupper(trim($_POST['fechaalta']), 'UTF-8');
            $dataIndiceBase = trim($_POST['indicebase']);
            $dataValorTotal = trim(intval($_POST['valortotal']));
            $dataMoneda = intval($_POST['moneda']);
            $dataVendedor= intval($_POST['vendedor']);
            $dataComision = (isset($_POST['comisionventa']) && floatval($_POST['comisionventa'])>0) ? trim(floatval($_POST['comisionventa'])) : null;
            $dataDiaPrimerVenc = trim(intval($_POST['primervencimientodias']));
            $dataDiaSegundoVenc=isset($_POST['segundovencimientodias']) ? trim(intval($_POST['segundovencimientodias'])) : null;
            $dataSegundoVencRecargo=isset($_POST['segundovencimientorecargo']) ? $_POST['segundovencimientorecargo'] : "0";
            $dataDiaTercerVenc=isset($_POST['tercervencimientodias']) ? trim(intval($_POST['tercervencimientodias'])) : null; 
            $dataTercerVencRecargo=isset($_POST['tercervencimientorecargo']) ? $_POST['tercervencimientorecargo'] : "0"; 
            $dataTasaPunitorioDiario=(isset($_POST['tasapunitoriodiario']) && floatval($_POST['tasapunitoriodiario'])>0) ? trim(floatval($_POST['tasapunitoriodiario'])) : null; 

            //RECIBE EL ARRAY DE INTEGRANTES
            if (isset($_POST['integrantes'])) {
                foreach ($_POST['integrantes'] as $prof) {
                    $arr = explode("~**~", $prof);
                    if ($prof != null) {
                        if (count($arr) == 6) {
                            $newIntegrantes[] = array("idcontratopersona" => $arr[0],
                                "idpersona" => $arr[1],
                                "entidad" => $arr[2],
                                "idcontratorol" => $arr[3],
                                "rol" => $arr[4],
                                "tieneplancuentas" => ($arr[5]=='plandecuentas')?1:0);
                        } else {
                            $validationErrors['integrantes'] = "Se detectaron valores inválidos en el listado de integrantes";
                        }
                    } 
                }
            } else {
                //No se pasan por POST los integrantes
                if ($idcontrato != null){
                    $newIntegrantes=$this->contrato->getContratoIntegrantesByIdContrato($idcontrato);
                }
            }

            //RECIBE EL ARRAY DE ESTADOS
            if (isset($_POST['datosestado'])) {
                foreach ($_POST['datosestado'] as $estado) {
                    $arr = explode("~**~", $estado);
                    if ($estado != null) {
                        if (count($arr) == 4) {
                            $datosEstado[] = array(
                            "idcontratoestado" => $arr[0],
                            "idestado" => $arr[1],
                            "estado" => $arr[2],
                            "fecha" => $lib->dateDMY2YMD($arr[3]),
                            "usuario" =>$ses->getUsuario());
                        } else {
                            $validationErrors['datosestado'] = "Se detectaron valores inválidos del estado.";
                        }
                    } 
                }
            } else {
                //No se pasan por POST los estados
                if ($idcontrato != null){
                    $datosEstado=$this->contrato->getContratoEstadosByIdContrato($idcontrato);
                }
            }

            $dataObservaciones = trim($_POST['observaciones']);
            
            // STAP! VALIDATION TIME
            if ($dataFideicomiso == "")
                $validationErrors['fideicomiso'] = "El campo Fideicomiso es obligatorio";
            if ($dataUnidadfuncional == "")
                $validationErrors['unidadfuncional'] = "El campo Unidad Funcional es obligatorio";
            if ($dataFechaAlta == "" || $dataFechaAlta == 0)
                $validationErrors['fechaalta'] = "El campo Fecha Alta es obligatorio";
            if ($dataIndiceBase == "" || $dataIndiceBase == 0)
                $validationErrors['indicebase'] = "El campo Indice base es obligatorio";
            if ($dataValorTotal == "" || $dataValorTotal == 0)
                $validationErrors['valortotal'] = "El campo Valor Total es obligatorio";
            if ($dataMoneda == "" || $dataMoneda == 0)
                $validationErrors['moneda'] = "El campo Moneda es obligatorio";
            if ($dataDiaPrimerVenc == "" || $dataDiaPrimerVenc== 0)
                $validationErrors['primervencimientodias'] = "El dia de primer vencimiento es obligatorio";
            if (count($newIntegrantes)==0)
                $validationErrors['integrantes'] = "Debe agregar al menos un integrante al contrato.";
            if (count($datosEstado)==0)
                $validationErrors['idestado'] = "Debe elegir al menos un estado.";
            if (!$this->existeAlMenosUnTitular($newIntegrantes))
                $validationErrors['integrantes'] = "Debe agregar al menos un titular al contrato.";
            
            
            if (count($validationErrors) == 0) {
                $params = array();
                $params["idunidadfuncional"]=$dataUnidadfuncional;
                $params["aplicaajuste"]=$dataAplicaAjusteIcc;
                $params["fechaalta"]=$lib->dateDMY2YMD($dataFechaAlta);
                $params["indicebase"]=$dataIndiceBase;
                $params["valortotal"]=$dataValorTotal;
                $params["idmoneda"]=$dataMoneda;
                $params["primervencimientodias"]=$dataDiaPrimerVenc;
                $params["segundovencimientodias"]=$dataDiaSegundoVenc;
                $params["segundovencimientorecargo"]=$dataSegundoVencRecargo;
                $params["tercervencimientodias"]=$dataDiaTercerVenc;
                $params["tercervencimientorecargo"]=$dataTercerVencRecargo;
                $params["tasapunitoriodiario"]=$dataTasaPunitorioDiario;
                $params["idvendedor"]=$dataVendedor;
                $params["comisionventa"]=$dataComision;
                $params["observacion"]=$dataObservaciones;
                $params["usuario"]=$ses->getUsuario();
                //Integrantes
                $paramsintegrantes["contratopersona"]=$newIntegrantes;
                //Estado
                $paramsestados["contratoestado"]=$datosEstado;
                
                //BEGIN TRANSACION
                $this->app->beginTransaction();
                if ($idcontrato == null){
                    // Alta nuevo contrato
                    $bOk=$this->contrato->nuevoContrato($params);
                    $idcontrato=$bOk["idcontrato"];
                } else {
                    //Update contrato existente
                    $params["idcontrato"]=$idcontrato;
                    $bOk=$this->contrato->modificarContrato($params);
                }
                
                //Borrar integrantes que no esten entre los nuevos
                $conjuntoDeIds = "";
                $conjuntoDeIds = implode(', ', array_column($paramsintegrantes["contratopersona"], 'idcontratopersona'));
                $bOk=$this->contrato->borrarContratoIntegranteConjuntoIds($conjuntoDeIds);

                //Alta de integrantes
                foreach ($paramsintegrantes["contratopersona"] as $contratopersona) {
                    if ($contratopersona["idcontratopersona"] == "0") {
                        $bOk=$this->contrato->nuevoContratoIntegrante($idcontrato,$contratopersona);
                    }
                }

                //Alta de estados
                foreach ($paramsestados["contratoestado"] as $contratoestados) {
                    //foreach ($contratoestados as $contratoestado) {
                        if ($contratoestados["idcontratoestado"] == 0) {
                            $bOk=$this->contrato->nuevoContratoEstado($idcontrato,$contratoestados);
                            break;
                        }
                    //}
                }
                
                //Modificacion Estado UF segun estado de contrato
                $idNuevoEstadoContrato=$contratoestados["idestado"];
                if ($idNuevoEstadoContrato != null){
                    $idNuevoEstadoUF=$this->calcularEstadoUFByEstadoContrato($idNuevoEstadoContrato);
                    $bOk= $this->unidadfuncional->modificarIdEstadoUnidadFuncional($idunidadfuncional, $idNuevoEstadoUF);
                    
                }
                
                if ($bOk["ok"] === false) {
                    $this->app->rollbackTransaction();
                    $ses->setMessage("Se produjo un error guardar los datos. " . $bOk["message"], SessionMessageType::TransactionError);
                    header("Location: /gestion-unidadesfuncionales", TRUE, 302);
                }else{
                    $this->app->commitTransaction();
                    $ses->setMessage("Contrato guardado con éxito", SessionMessageType::Success);
                header("Location: /gestion-unidadesfuncionales", TRUE, 302);
                }
                
                exit();

            } else {
                $ses->setMessage("Complete todos los campos.", SessionMessageType::TransactionError);
            }
        }

        $arrMenu = $this->POROTO->DB->getSQLArray($this->POROTO->getMenuSqlQuery());
        $viewDataFideicomisosActivos=$this->fideicomiso->getFideicomisosActivos();
        $viewDataUnidadFuncional=$this->unidadfuncional->getUnidadFuncional(null);
        $viewDataMoneda= $this->app->getAllMonedas();
        $viewDataVendedor=$this->persona->getVendedoresActivos();
        $viewDataIntegrante=$this->persona->getPersonasActivas();
        $viewDataContratoRol=$this->app->getAllContratoRol(); //Cargar los contratorol.
        $viewDataEstadoContrato=$this->app->getAllEstadoContrato(); //Cargar estados del contrato.
        $viewDataUF=$this->unidadfuncional->getUnidadFuncionalById($idunidadfuncional);
        $viewDataFideicomiso=$this->fideicomiso->getFideicomisoById($viewDataUF['idfideicomiso']);
        $db->dbDisconnect();

        if ($idcontrato == null){
            $status="alta";
            $pageTitle="Alta de contrato";
        } else {
            $status="modificacion";
            $pageTitle="Edición de contrato";
            $viewDataContrato= $this->contrato->getContratoById($idcontrato);
             if (!isset($newIntegrantes) || count($newIntegrantes)==0) {
                $newIntegrantes=$this->contrato->getContratoIntegrantesByIdContrato($idcontrato);
             }
            $datosEstado=$this->contrato->getContratoEstadosByIdContrato($idcontrato);
        }        

        include($this->POROTO->ViewPath . "/-header.php");
        include($this->POROTO->ViewPath . "/crear-contrato.php");
        include($this->POROTO->ViewPath . "/-footer.php");
    }
        /**
     * Recorre lista de integrantes y devuelve true si alguno es titular.
     * @param type $integrantes
     * @return boolean
     */
    public function existeAlMenosUnTitular($integrantes) {
        $result=false;
        $idtitular=1; //TODO: Hardcodeado por ahora, luego ver como hacemos
        foreach ($integrantes as $i){
            if ($i["idcontratorol"]==$idtitular){
                $result=true;
                break;
            }
        }
        
        return $result;
    }

    /**
     * Recibe un id de estado del contrato y devuelve un estado de la UF
     * @param type $idEstadoContrato
     */
    public function calcularEstadoUFByEstadoContrato($idEstadoContrato) {
        $estadoContrato=$this->contrato->getEstadoContratoById($idEstadoContrato);
        
        if ($estadoContrato == 'PRELIMINAR'){
            //estado UF “Contrato preliminar”
            return $this->POROTO->Config['estado_uf_preliminar'];
        } else if ($estadoContrato == 'EN CURSO'){
            //estado UF “Contrato activo”
            return $this->POROTO->Config['estado_uf_activa'];
        } else if ($estadoContrato == 'CEDIDO'){
            //estado UF “Contrato preliminar”
            return $this->POROTO->Config['estado_uf_preliminar'];
        } else if ($estadoContrato == 'FINALIZADO'){
            //estado UF “Vendida”
            return $this->POROTO->Config['estado_uf_vendida'];
        } else if ($estadoContrato == 'ANULADO'){
            //estado UF “En venta”
            return $this->POROTO->Config['estado_uf_enventa'];
        }
    }

}
