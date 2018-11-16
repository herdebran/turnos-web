<?php

class Permisos extends Controller {

    private $permisos;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/permisos.php');
        $this->permisos = new ModeloPermisos($this->POROTO);
    }

    function defentry() {
        if ($this->POROTO->Session->isLogged()) {
            $this->index();
        } else {
            include($this->POROTO->ViewPath . "/-login.php");
        }
    }

    public function index() {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $params = array();
        $params['viewDataTipoDocumento'] = $this->app->getAllTipoDocumento();
        $params["roles"] = $this->app->getAllRoles();
        $params["permisos"] = $this->app->getAllPermisos();
        $this->render("/gestion-permisos.php", $params);
    }

    public function personasConFiltro($filter = null) {
        include($this->POROTO->ModelPath . '/persona.php');
        $persona = new ModeloPersona($this->POROTO);
        if (!$filter) {
            $filter = $_POST['filtros'];
        }
        $personas = $this->permisos->getPersonasPermisos($filter);
        $json = array("data" => $personas);

        echo json_encode($json);
    }

    /**
     * 
     * @param type $idpersona
     */
    public function detalle($idpersona) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-permisos.php", TRUE, 302);
            exit();
        }

        include($this->POROTO->ModelPath . '/persona.php');
        $personaModel = new ModeloPersona($this->POROTO);
        $params = array();
        $params["usuario"] = $this->permisos->getUsuarioByIdPersona($idpersona);
        $params["persona"] = $personaModel->getPersonaById($idpersona);
        $params["personaRoles"] = $this->permisos->getPersonaRolesByPersona($idpersona);
        $params["roles"] = $this->app->getAllRoles();
        $params["permisos"] = $this->app->getAllPermisos();
        $params["permisosAsignados"] = $this->permisos->getPermisosAsignados($idpersona);

        $this->render("/gestion-permisos-detalle.php", $params);
    }

    public function gestionroles($idrol = 0) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Acceso desde Menu')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /gestion-permisos.php", TRUE, 302);
            exit();
        }

        include($this->POROTO->ModelPath . '/persona.php');
        $personaModel = new ModeloPersona($this->POROTO);
        $params = array();
        $params["roles"] = $this->app->getAllRoles();
        $params["rolselected"] = $idrol;
        $params["permisos"] = $this->app->getAllPermisos();
        $params["permisosroles"] = $this->permisos->getRolesPermisos();
        if ($idrol > 0)
            $params["permisosactuales"] = $this->permisos->getPermisosDeRol($idrol);
        else {
            $params["permisosactuales"] = [];
        }
        $this->render("/gestion-roles.php", $params);
    }

    public function setpersonarol($idpersona, $idrol, $estado) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Modificar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        if ($this->permisos->setpersonarol($idpersona, $idrol, $estado)) {
            $this->ses->setMessage("El rol se cambio exitosamente.", SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al cambiar el rol.", SessionMessageType::TransactionError);
        }
        header("Location: /permisos/detalle/$idpersona", TRUE, 302);
    }

    public function setpersonapermiso($idpersona, $idpermiso, $estado) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Modificar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }
        if ($this->permisos->setpersonapermiso($idpersona, $idpermiso, $estado)) {
            $this->ses->setMessage("El permiso se cambio exitosamente.", SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al cambiar el permiso.", SessionMessageType::TransactionError);
        }
        header("Location: /permisos/detalle/$idpersona", TRUE, 302);
    }

    public function setpermisorol($idrol, $idpermiso, $estado) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Modificar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }
        if ($this->permisos->setpermisorol($idrol, $idpermiso, $estado)) {
            $this->ses->setMessage("El permiso se cambio exitosamente.", SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al cambiar el permiso.", SessionMessageType::TransactionError);
        }
        header("Location: /permisos/gestionroles/$idrol", TRUE, 302);
    }

    public function setpersonaestado($idpersona, $estado) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Modificar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }
        if ($this->permisos->setpersonaestado($idpersona, $estado)) {
            $this->ses->setMessage("El estado de la persona se cambio exitosamente.", SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al cambiar el estado de la persona.", SessionMessageType::TransactionError);
        }
        header("Location: /permisos/detalle/$idpersona", TRUE, 302);
    }

    public function resetpass($idpersona) {
        if (!$this->ses->tienePermiso('', 'Gestion de Permisos - Modificar')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }
        include($this->POROTO->ModelPath . '/persona.php');
        $personaModel = new ModeloPersona($this->POROTO);
        $persona = $this->permisos->getUsuarioByIdPersona($idpersona);
        if ($this->permisos->resetpass($idpersona, $persona["usuario"])) {
            $this->ses->setMessage("La contraseña se reseteo exitosamente.", SessionMessageType::Success);
        } else {
            $this->ses->setMessage("Error al resetear la contraseña.", SessionMessageType::TransactionError);
        }
        header("Location: /gestion-permisos", TRUE, 302);
    }
}
