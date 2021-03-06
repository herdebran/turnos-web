<?php

class ModeloPersona {

    private $PDO;
    private $_session;

    public function __construct($poroto) {
        $this->PDO = $poroto->PDO;
        $this->_session = $poroto->Session;
    }

    /**
     * 
     * @param type $idpersona
     * @return type
     */
    public function getPersonaById($idpersona) {
        $sql = "SELECT distinct p.idpersona, p.email,
                p.tipodoc tdoc, p.documentonro,
		concat(p.apellido,' ',p.nombre) as apeynom,
        concat(td.descripcion,' ',p.documentonro) as tipodocynro,
		r.nombre as rol,
		p.email
        from 
        persona p 
        INNER JOIN tipodoc td on p.tipodoc=td.id
	left join personarol pr on p.idpersona=pr.idpersona
	left join rol r on pr.idrol=r.idrol
        where p.idpersona=:idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "persona/getPersonaById", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPersona2ById($idpersona) {
        $filtros = array();
        $filtros['idpersona']=$idpersona;
        return $this->getPersona($filtros);
    }

    public function getPersona($filtros) {
        //continuar
        $sql = "SELECT a.idpersona, a.idtipopersona,tp.descripcion as tipopersona,
                case when a.razonsocial = '' or a.razonsocial is null then concat(a.apellido,' ',a.nombre) 
                else a.razonsocial end as denominacion,a.apellido, a.nombre,a.razonsocial,
                a.tipodoc as idtipodocumento, td.descripcion as tipodocumento, a.documentonro, 
                concat(td.descripcion,' ',a.documentonro) as tipodocynro, 
                a.iddomicilio, do.calle as domiciliocalle, do.nro as domicilionro, 
                do.piso as domiciliopiso,do.depto as domiciliodepto, 
                do.idlocalidad as domicilioidlocalidad,ifnull(lo.descripcion,'') as domiciliolocalidad, 
                mu.idmunicipio as domicilioidmunicipio, mu.descripcion as domiciliomunicipio, 
                prov.idprovincia as domicilioidprovincia, prov.descripcion as domicilioprovincia,  
                concat(ifnull(do.calle,''),' ',ifnull(do.nro,''), ' ',ifnull(lo.descripcion,'')) as domicilio,
                a.telefono1, a.telefono2, a.telefono3, a.email, a.web,
                a.observaciones, a.estado, us.usuario as usuario,
                a.usucrea, a.fechacrea, a.usumodi, a.fechamodi
                FROM persona a
                INNER JOIN tipopersona tp on a.idtipopersona=tp.idtipopersona
                INNER JOIN tipodoc td on a.tipodoc=td.id
                LEFT JOIN domicilio do on a.iddomicilio=do.iddomicilio
                LEFT JOIN localidad lo on do.idlocalidad=lo.idlocalidad
                LEFT JOIN municipio mu on mu.idmunicipio=lo.idmunicipio
                LEFT JOIN provincia prov on prov.idprovincia=mu.idprovincia
                LEFT JOIN usuario us on a.idpersona=us.idpersona
                WHERE 1=1 ";
        $params = array();
        if (isset($filtros['idpersona']) && $filtros['idpersona'] > 0) {
            $sql .= " and a.idpersona = :idpersona";
            $params[":idpersona"] = $filtros['idpersona'];
        }

        if (isset($filtros['idtipopersona']) && $filtros['idtipopersona'] > 0) {
            $sql .= " and a.idtipopersona = :idtipopersona";
            $params[":idtipopersona"] = $filtros['idtipopersona'];
        }
        if (isset($filtros['razonsocialapellido']) && $filtros['razonsocialapellido'] != "") {
            $sql .= " and (a.razonsocial like :razonsocialapellido or a.apellido like :razonsocialapellido or a.nombre like :razonsocialapellido)";
            $params[":razonsocialapellido"] = "%" . $filtros['razonsocialapellido'] . "%";
        }

        if (isset($filtros['tipopersona'])) {
            $sql .= " and a.idtipopersona = :tipopersona";
            $params[":tipopersona"] = $filtros['tipopersona'];
        }

        if (isset($filtros['estado'])) {
            $sql .= " and a.estado = :estado";
            $params[":estado"] = $filtros['estado'];
        }

        $sql .= " order by tp.descripcion, a.razonsocial, a.apellido";

        
        $this->PDO->execute($sql, "persona/getPersona", $params);
        
        if (isset($filtros['idpersona']) && $filtros['idpersona'] > 0) {
            $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        }else{
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Crea una nueva persona y retorna el id de insercion.
     * @param type $valores
     * @return type
     */
    public function nuevaPersona($valores) {
        //Agrego la persona
        $sql = "insert into persona (idtipopersona, razonsocial, apellido, nombre, tipodoc,
                    documentonro, iddomicilio, telefono1, telefono2,
                    telefono3, email, web, observaciones, estado, usucrea, fechacrea)
                    values(:idtipopersona, :razonsocial, :apellido, :nombre, :tipodoc,
                    :documentonro, :iddomicilio, :telefono1, :telefono2,
                    :telefono3, :email, :web, :observaciones, 1, :usucrea, :fechacrea)";
        $params = array(
            ":idtipopersona" => $valores["idtipopersona"],
            ":razonsocial" => $valores["razonsocial"],
            ":apellido" => $valores["apellido"],
            ":nombre" => $valores["nombre"],
            ":tipodoc" => $valores["tipodoc"],
            ":documentonro" => $valores["documentonro"],
            ":iddomicilio" => $valores['domicilio']["iddomicilio"],
            ":telefono1" => $valores["telefono1"],
            ":telefono2" => $valores["telefono2"],
            ":telefono3" => $valores["telefono3"],
            ":email" => $valores["email"],
            ":web" => $valores["web"],
            ":observaciones" => $valores["observaciones"],
            ":usucrea" => $this->_session->getUsuario(),
            ":fechacrea" => date("Y-m-d H:i:s")
        );

        $this->PDO->execute($sql, 'persona/nuevaPersona', $params);
        return $this->PDO->lastInsertId();
    }

    /**
     * Retorna un domicilio especifico
     * @param type $iddomicilio
     * @return type
     */
    public function getDomicilio($iddomicilio){
        $sql = "select * from domicilio where iddomicilio = :iddomicilio";
        $params = array(
            ":iddomicilio" => $iddomicilio,
            );
        return $this->PDO->execute($sql, 'persona/nuevoDomicilio', $params);
    }
    /**
     * Crea un nuevo domicilio en la db
     * @param array<domicilio> $domicilio Arreglo representativo de un domicilio con los
     * indices calle, nro, piso, depto, idlocalidad, cp y usuario
     * @return Int El id del domicilio que se acaba de insertar.
     */
    public function nuevoDomicilio($domicilio) {
        $sql = "insert into domicilio (calle, nro, piso, depto, idlocalidad,
        usucrea, fechacrea)
        values(:calle, :nro, :piso, :depto, :idlocalidad, :usucrea, :fechacrea)";
        $params = array(
            ":calle" => $domicilio["calle"],
            ":nro" => $domicilio["nro"],
            ":piso" => $domicilio["piso"],
            ":depto" => $domicilio["depto"],
            ":idlocalidad" => $domicilio["idlocalidad"],
            ":usucrea" => $this->_session->getUsuario(),
            ":fechacrea" => date("Y-m-d H:i:s"));
        $this->PDO->execute($sql, 'persona/nuevoDomicilio', $params);
        return $this->PDO->lastInsertId();
    }

    /**
     * Modifica un domicilio
     * @param int $iddomicilio id del domicilio a modificar
     * @param array<domicilio> $domicilio Arreglo representativo de un domicilio con los
     * indices calle, nro, piso, depto, idlocalidad, cp y usuario
     */
    public function modificarDomicilio($domicilio) {
        $sql = "UPDATE domicilio SET
        calle=:calle, nro=:nro, piso=:piso, depto=:depto, idlocalidad=:idlocalidad, 
        usumodi=:usumodi, fechamodi=:fechamodi";
        $sql .= " where iddomicilio = :iddomicilio";
        $params = array(
            ":calle" => $domicilio["calle"],
            ":nro" => $domicilio["nro"],
            ":piso" => $domicilio["piso"],
            ":depto" => $domicilio["depto"],
            ":idlocalidad" => $domicilio["idlocalidad"],
            ":usumodi" => $this->_session->getUsuario(),
            ":fechamodi" => date("Y-m-d H:i:s"),
            ":iddomicilio" => $domicilio['iddomicilio']
        );
        $this->PDO->execute($sql, 'persona/modificarDomicilio', $params);
    }

    /**
     * Modifica los datos de una persona y/o domicilios asociados.
     * @param type $valores Arreglo con los datos para domicilio, domicio fiscal
     * y persona
     * @return array Arreglo con valores de respuesta
     */
    public function modificarPersona($persona) {
        $sql = "UPDATE personas set 
            idtipopersona=:idtipopersona, razonsocial=:razonsocial, apellido=:apellido, nombre=:nombre, tipodoc=:tipodoc,
            documentonro=:documentonro, cargoenempresa=:cargoenempresa, iddomicilio=:iddomicilio, 
            iddomiciliofacturacion=:iddomiciliofacturacion, telefono1=:telefono1, telefono2=:telefono2,
            telefono3=:telefono3, vendedorporcganancia=:vendedorporcganancia, email=:email, web=:web, 
            idtipocondicioniva=:idtipocondicioniva, informacionpago=:informacionpago, idtipofactura=:idtipofactura,
            facturanombre=:facturanombre, observaciones=:observaciones, usumodi=:usumodi, fechamodi=:fechamodi";
        $sql .= " where idpersona = :idpersona";

        $params = array(
            ":idpersona" => $persona['idpersona'],
            ":idtipopersona" => $persona["idtipopersona"],
            ":razonsocial" => $persona["razonsocial"],
            ":apellido" => $persona["apellido"],
            ":nombre" => $persona["nombre"],
            ":tipodoc" => $persona["tipodoc"],
            ":documentonro" => $persona["documentonro"],
            ":cargoenempresa" => $persona["cargoenempresa"],
            ":iddomicilio" => $persona["domicilio"]["iddomicilio"],
            ":iddomiciliofacturacion" => $persona["domiciliofacturacion"]["iddomicilio"],
            ":telefono1" => $persona["telefono1"],
            ":telefono2" => $persona["telefono2"],
            ":telefono3" => $persona["telefono3"],
            ":vendedorporcganancia" => $persona["vendedorporcganancia"],
            ":email" => $persona["email"],
            ":web" => $persona["web"],
            ":idtipocondicioniva" => $persona["idtipocondicioniva"],
            ":informacionpago" => $persona["informacionpago"],
            ":idtipofactura" => $persona["idtipofactura"],
            ":facturanombre" => $persona["facturanombre"],
            ":observaciones" => $persona["observaciones"],
            ":usumodi" => $this->_session->getUsuario(),
            ":fechamodi" => date("Y-m-d H:i:s")
        );
        $this->PDO->execute($sql, "persona/modificarPersona", $params);
    }

    public function modificarestadoPersona($valores) { //Revisar el return si esta bien!!!!
        //Anular una operacion
        $this->PDO->beginTransaction('persona/modificarestadoPersona');
        try {
            $sql = " update persona set ";
            $sql .= " motivo =:motivo, estado=:estado, usumodi=:usumodi, fechamodi=:fechamodi";
            $sql .= " where idpersona = :idpersona";

            $params = array(
                ":idpersona" => $valores["idpersona"],
                ":estado" => $valores["estado"],
                ":usumodi" => $valores["usumodi"],
                ":fechamodi" => date("Y-m-d H:i:s")
            );


            $this->PDO->execute($sql, 'persona/modificarestadoPersona', $params);
            $this->PDO->commitTransaction('persona/modificarestadoPersona');
            return array("ok" => true, "message" => "La entidad cambió su estado satisfactoriamente.");
        } //del try
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction('persona/modificarestadoPersona' . $e->getMessage());
            return array("ok" => false, "message" => "Error al cambiar el estado al fideicomiso. Comuniquese con el administrador.");
        }
    }

    //Hernan 20180720
    //Lista todas las personas activas
    public function getPersonasActivas() {
        $f = array();
        $f['estado'] = 1;

        $result = $this->getPersona($f);
        return $result;
    }

    public function getVendedoresActivos() {
        $f = array();
        $f['estado'] = 1;
        $f['tipopersona'] = "VENDEDOR";

        $result = $this->getPersona($f);
        return $result;
    }

    /**
     * Dado un id de persona devuelve un saludo amigable utilizando el nombre de la persona.
     * @param type $idpersona
     * @return string
     */
    public function obtenerSaludoHome($idpersona){
        $result = "¡Hola!";
        $sql = "select nombre from persona where idpersona = :idpersona";
        $params = array(":idpersona" => $idpersona);
 
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, 'persona/obtenerSaludoHome', $params);
        $arrSaludo=$this->PDO->fetch(PDO::FETCH_ASSOC); 
        
        if ($arrSaludo!= null) {
            if ($arrSaludo['nombre'] != null) { 
                $result = "¡Hola " .$arrSaludo['nombre'] . "!";
            }
        }
        return $result;
    }
}
