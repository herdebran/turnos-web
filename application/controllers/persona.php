<?php

class Persona extends Controller {

    private $persona;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/persona.php');
        $this->persona = new ModeloPersona($this->POROTO);
    }


    /**
     * Guarda una persona con los datos recibidos por POST.
     */
    public function guardarpersona() {
        $ses = & $this->POROTO->Session;
        $lib = & $this->POROTO->Libraries['siteLibrary'];

        if (!$ses->tienePermiso('', 'Guardar persona')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        }

        $idpersona = filter_input(INPUT_POST, 'idpersona');

        $persona = $this->cargarDatosAltaEntidad();

        $params = array();
        $params['validationErrors'] = [];
        $params['persona'] = $persona;
        $params['validationErrors'] = $this->validacionDatosPersonales($persona);
        if (count($params['validationErrors']) == 0) {
            // Alta nueva persona
            if ($idpersona == null) {
                // Alta nueva persona
                try {
                    $this->app->beginTransaction('persona/crearPersona');
                    $persona['domicilio']['iddomicilio'] = $this->persona->nuevoDomicilio($persona['domicilio']);
                    $idpersona = $this->persona->nuevapersona($persona);
                    $this->app->commitTransaction('persona/crearPersona');
                    $bOk = array("ok" => true, "message" => "La persona se generó satisfactoriamente.", "idpersona" => $idpersona);
                } catch (PDOException $e) {
                    $this->app->rollbackTransaction('persona/crearPersona');
                    $bOk = array("ok" => false, "message" => "Se produjo un error al persistir..", "idpersona" => $idpersona);
                }
            } else {
                //Update persona existente
                try {
                    $this->app->beginTransaction('persona/crearPersona');
                    $this->persona->modificarDomicilio($persona['domicilio']);
                    $this->persona->modificarPersona($persona);
                    $this->app->commitTransaction('persona/crearPersona');
                    $bOk = array("ok" => true, "message" => "La persona se generó satisfactoriamente.", "idpersona" => $idpersona);
                } catch (PDOException $e) {
                    $this->app->rollbackTransaction('persona/crearPersona');
                    $bOk = array("ok" => false, "message" => "Se produjo un error al editar la persona.", "idpersona" => $idpersona);
                }
            }
            if ($bOk["ok"] === false) {
                $ses->setMessage("Se produjo un error al persistir." . $bOk["message"], SessionMessageType::TransactionError);
            } else {
                $ses->setMessage("Entidad guardada con éxito", SessionMessageType::Success);
            }
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        } else {
            $_SESSION['persona_form_error'] = true;
            $_SESSION['persona_form_alerts'] = $params['validationErrors'];
            $ses->setMessage("Complete todos los campos.", SessionMessageType::TransactionError);
        }
        if ($idpersona == null) {
            header("Location: /crear-persona", TRUE, 302);
        } else {
            header("Location: /crear-persona/" . $idpersona, TRUE, 302);
        }
    }

//Hernan 20180617
    function gestionpersonas() {
        if (!$this->ses->tienePermiso('', 'Gestión de Personas - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

// ---------------------- Logica del metodo ----------------------------
        $persona = $this->persona;
        $params = array();
        $params['pageTitle'] = "Búsqueda de Entidades";

        $params['viewDataTipo'] = $this->app->getAllTipoPersona();
        $params["roles"] = $this->app->getAllRoles();
// ---------------------- Fin logica del metodo ------------------------
        $this->render("/gestion-personas.php", $params);
    }

    public function personasConFiltro($filter = null) {
        $persona = $this->persona;
        if (!$filter) {
            $filter = $_POST['filtros'];
        }
        $personas = $persona->getpersona($filter);
        $json = array("data" => $personas);

        echo json_encode($json);
    }

    /**
     * Obtiene los datos de una persona que vengan de un formulario y forma un array organizado con los datos de la misma.
     * @param type $idpersona
     * @return type
     */
    private function cargarDatosAltaEntidad() {
// Datos Personales
        $persona['idpersona'] = filter_input(INPUT_POST, 'idpersona');
        $persona['idtipopersona'] = filter_input(INPUT_POST, 'idtipopersona');
        $persona['apellido'] = filter_input(INPUT_POST, 'apellido');
        $persona['nombre'] = filter_input(INPUT_POST, 'nombre');
        $persona['tipodoc'] = filter_input(INPUT_POST, 'tipodoc');
        $persona['documentonro'] = filter_input(INPUT_POST, 'documentonro');
        $persona['razonsocial'] = filter_input(INPUT_POST, 'razonsocial');
// Domicilio
        $domicilio['iddomicilio'] = filter_input(INPUT_POST, 'iddomicilio');
        $domicilio['calle'] = filter_input(INPUT_POST, 'calle');
        $domicilio['nro'] = filter_input(INPUT_POST, 'numero');
        $domicilio['piso'] = filter_input(INPUT_POST, 'piso');
        $domicilio['depto'] = filter_input(INPUT_POST, 'depto');
        $domicilio['provincia'] = filter_input(INPUT_POST, 'provincia');
        $domicilio['municipio'] = filter_input(INPUT_POST, 'municipio');
        $domicilio['idlocalidad'] = filter_input(INPUT_POST, 'localidad');
        $persona['domicilio'] = $domicilio;
// Contacto
        $persona['telefono1'] = filter_input(INPUT_POST, 'telefono1');
        $persona['telefono2'] = filter_input(INPUT_POST, 'telefono2');
        $persona['telefono3'] = filter_input(INPUT_POST, 'telefono3');
        $persona['email'] = filter_input(INPUT_POST, 'email');
        $persona['web'] = filter_input(INPUT_POST, 'web');
// Otros Datos
        $persona['observaciones'] = filter_input(INPUT_POST, 'observaciones');
        return $persona;
    }

    /**
     * Obtiene los datos de una persona y forma un array organizado con los datos de la misma.
     * @param type $idpersona
     * @return type
     */
    private function personaConFormato($idpersona) {
        $personaDb = $this->persona->getPersona2ById($idpersona);
// Datos Personales
        $persona['idpersona'] = $personaDb['idpersona'];
        $persona['idtipopersona'] = $personaDb['idtipopersona'];
        $persona['apellido'] = $personaDb['apellido'];
        $persona['nombre'] = $personaDb['nombre'];
        $persona['tipodoc'] = $personaDb['idtipodocumento'];
        $persona['documentonro'] = $personaDb['documentonro'];
        $persona['razonsocial'] = $personaDb['razonsocial'];
// Domicilio
        $domicilio['iddomicilio'] = $personaDb['iddomicilio'];
        $domicilio['calle'] = $personaDb['domiciliocalle'];
        $domicilio['nro'] = $personaDb['domicilionro'];
        $domicilio['piso'] = $personaDb['domiciliopiso'];
        $domicilio['depto'] = $personaDb['domiciliodepto'];
//$domicilio['provincia'] = $personaDb['domicilioidprovincia'];
        $domicilio['provincia'] = $personaDb['domicilioidprovincia'];
        $domicilio['municipio'] = $personaDb['domicilioidmunicipio'];
        $domicilio['idlocalidad'] = $personaDb['domicilioidlocalidad'];
        $persona['domicilio'] = $domicilio;
// Contacto
        $persona['telefono1'] = $personaDb['telefono1'];
        $persona['telefono2'] = $personaDb['telefono2'];
        $persona['telefono3'] = $personaDb['telefono3'];
        $persona['email'] = $personaDb['email'];
        $persona['web'] = $personaDb['web'];
// Otros Datos
        $persona['observaciones'] = $personaDb['observaciones'];
        return $persona;
    }

    /**
     * Valida los campos de una persona; Campos que se definen en el metodo.
     * @param type $persona
     * @return string
     */
    private function validacionDatosPersonales($persona) {
        $validationErrors = array();
        if ($persona['idtipopersona'] == "" || $persona['idtipopersona'] == 0)
            $validationErrors['idtipopersona'] = "El campo Tipo es obligatorio";
        if ($persona['apellido'] == "")
            $validationErrors['apellido'] = "El campo Apellido es obligatorio";
        if (strlen($persona['apellido']) > 45)
            $validationErrors['apellido'] = "El campo Apellido puede contener como máximo 45 caracteres";
        if ($persona['nombre'] == "")
            $validationErrors['nombre'] = "El campo Nombre es obligatorio";
        if (strlen($persona['nombre']) > 45)
            $validationErrors['nombre'] = "El campo Nombre puede contener como máximo 45 caracteres";
        if ($persona['tipodoc'] == "" || $persona['tipodoc'] == "0")
            $validationErrors['tipodoc'] = "El campo Tipo Documento es obligatorio";
        if (!is_numeric($persona['documentonro']) || !in_array(strlen($persona['documentonro']), [6, 7, 8, 9, 10, 11]))
            $validationErrors['nrodoc'] = "Ingrese un Documento válido";

        return $validationErrors;
    }
    public function crearpersona($idpersona = null) { //OK
        $validationErrors = array();
        $db = & $this->POROTO->DB;
        $ses = & $this->POROTO->Session;
        $db->dbConnect("persona/crear-persona");

        if (!$ses->tienePermiso('', 'Crear persona')) {
            $ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-personas", TRUE, 302);
            exit();
        }

        $params = array();
        if (isset($_SESSION['persona_form_error']) && $_SESSION['persona_form_error']) {
            $params['validationErrors'] = $_SESSION['persona_form_alerts'];
            unset($_SESSION['persona_form_alerts']);
            unset($_SESSION['persona_form_error']);
        } else {
            $params['validationErrors'] = [];
        }
        if ($idpersona == null) {
            //ALTA DE UNA NUEVA PERSONA
            
            $persona = $this->cargarDatosAltaEntidad();
            $params['pageTitle'] = "Crear Persona";
            $params['btnText'] = "Crear";
        } else {
            
            //EDICION DE UNA PERSONA EXISTENTE
            $persona = $this->personaConFormato($idpersona);
            $params['pageTitle'] = "Modificar Persona";
            $params['btnText'] = "Modificar";
        }
        
        // armo las provincias, municipios y localidades antes de mostrar.
        $params['provincias'] = $this->app->getAllProvincias();
        $params['municipiosD'] = $this->app->ajaxAllMunicipios($persona['domicilio']['provincia']);
        $params['localidadesD'] = $this->app->ajaxAllLocalidades($persona['domicilio']['municipio']);
        
        $params['persona'] = $persona;
        $params['tipopersona'] = $this->app->getAllTipoPersona();
        $params['tipodoc'] = $this->app->getAllTipoDocumento();
        
        $this->render("crear-persona.php", $params);
    }

}

?>