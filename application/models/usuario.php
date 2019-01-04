<?php

class ModeloUsuario {

    private $PDO;
    private $_session;

    public function __construct($poroto) {
        $this->PDO = $poroto->PDO;
        $this->_session = $poroto->Session;
    }

    /**
     * Crea un nuevo usuario y retorna el id de insercion.
     * @param type $valores
     * @return type
     */
    public function persistirUsuario($valores) {
        //Agrego la persona
        $sql = "insert into usuario (idpersona,usuario,password,estado,primeracceso, usucrea, fechacrea)
                    values(:idpersona,:usuario,:password,1,1, :usucrea, :fechacrea)";
        $params = array(
            ":idpersona" => $valores["idpersona"],
            ":usuario" => $valores["usuario"],
            ":password" => md5($valores["password"]),
            ":usucrea" => $this->_session->getUsuario(),
            ":fechacrea" => date("Y-m-d H:i:s")
        );

        $this->PDO->execute($sql, 'usuario/persistirUsuario', $params);
        return $this->PDO->lastInsertId();
    }

    /**
     * Devuelve un array con datos del usuario coincidentes con el idpersona
     * @param type $idpersona
     * @return type
     */
    public function getUsuarioByIdPersona($idpersona) {
        $sql = "select * from usuario where idpersona = :idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, "ModeloUsuario/getUsuarioByIdPersona", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Se inserta un registro en usuarioaccesos con los detalles del ingreso
     * @param type $param
     * @return type
     */
    public function persistirAccesoUsuario($valores) {
        $nombreCaller='usuario/persistirAccesoUsuario';
        $this->PDO->beginTransaction($nombreCaller);

        try {
        
            $sql = "insert into usuarioaccesos (idpersona,fecha,usuario,contraseña,ip,navegador,estado) "
                    . "values(:idpersona,CURRENT_TIMESTAMP,:usuario,:contrasena,:ip,:navegador,:estado)";

            $params = array(
                ":idpersona" => $valores["idpersona"],
                ":usuario" => $valores["usuario"],
                ":contrasena" => $valores["contrasena"],
                ":ip" => $valores["ip"],
                ":navegador" => $valores["navegador"],
                ":estado" => $valores["estado"]
            );

            $this->PDO->execute($sql, $nombreCaller, $params);
            $this->PDO->commitTransaction($nombreCaller);
            return array("ok" => true, "message" => "Se insertó satisfactoriamente.");

        }
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction($nombreCaller . $e->getMessage());
            return array("ok" => false, "message" => "Error al persistir en usuarioaccesos.");
        }
    }
    
    /**
     * Dado el nombre de usuario devuelve sus datos siempre y cuando esté activo como persona y usuario.
     * @param type $username
     * @return type
     */
    public function getUsuarioByUsername($username) {
        $sql = "select p.idpersona,p.documentonro, p.apellido, p.nombre, p.estado, "
            . "u.usuario,u.password,p.email, u.estado, u.primeracceso "
            . "from usuario u inner join persona p on p.idpersona=u.idpersona "
            . "where u.usuario=:username AND u.estado=1 AND p.estado=1";
        $params = array(":username" => $username);
        $this->PDO->execute($sql, "ModeloUsuario/getUsuarioByIdPersona", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function obtenerRolesByPersonaId($idpersona) {
        $sql = "select r.idrol, r.nombre "
                . "from personarol pr "
                . "inner join rol r on pr.idrol=r.idrol "
                . "where pr.idpersona=:idpersona order by 1";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, "ModeloUsuario/obtenerRolesByPersonaId", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Dado un id de rol y un id de persona devuelve la lista de permisos
     * @param type $idrol
     * @param type $idpersona
     * @return type
     */
    public function obtenerPermisos($idrol,$idpersona){
        $sql= "select p.idpermiso,p.nombre as nombre ";
        $sql.="from permisorol pr inner join permiso p on pr.idpermiso=p.idpermiso ";
        $sql.="where pr.idRol=:idrol";	
        $sql.=" union all ";
        $sql.="select p.idpermiso,pe.nombre as nombre from personapermiso p ";
        $sql.="inner join permiso pe on p.idpermiso=pe.idpermiso ";
        $sql.="where p.idpersona=:idpersona ";
        $sql.=" order by nombre ";

        $params = array(
            ":idrol" => $idrol,
            ":idpersona" => $idpersona);
        $this->PDO->execute($sql, "ModeloUsuario/obtenerPermisos", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function resetearPassUsuario($username,$nuevapassMD5) {
        $nombreCaller='ModeloUsuario/resetearPassUsuario';

        $sql = "update usuario set password=:password, primeracceso=1 "
            . "where usuario=:usuario ";
        $params = array(":password" => $nuevapassMD5,
            ":usuario" => $username);

        $this->PDO->execute($sql, $nombreCaller, $params);
        return array("ok" => true, "message" => "Se actualizo password satisfactoriamente.");
        
    }
    
    public function obtenerRolPersona($idpersona, $idrol) {
        $sql = "select pr.idrol, r.nombre "
                . "from personarol pr "
                . "inner join rol r on pr.idrol=r.idrol "
                . "where pr.idpersona=:idpersona "
                . "and pr.idrol=:idrol";
        
        $params = array(":idpersona" => $idpersona,
        $params = ":idrol" => $idrol);
        $this->PDO->execute($sql, "ModeloUsuario/obtenerRolPersona", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }    
}
