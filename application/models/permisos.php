<?php

//Consultas sobre personas, aca podemos agregar la consulta que viene de busqueda cooperadora.
class ModeloPermisos {

    private $PDO;
    private $poroto;

    public function __construct($poroto) {
        $this->poroto = $poroto;
        $this->PDO = $poroto->PDO;
        $this->SES = $poroto->Session;
    }

    public function getUsuarioByIdPersona($idpersona) {
        $sql = "select * from usuario where idpersona = :idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, "ModeloPermisos/getUsuario", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPersonaRolesByPersona($idpersona) {
        $sql = "select * from personarol where idpersona = :idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, 'ModeloPermisos/getPersonaRolesByPersona', $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getRolesPermisos() {
        $sql = "select * from permisorol";
        $this->PDO->execute($sql, 'ModeloPermisos/getPersonaRolesByPersona');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPermisosDeRol($idrol) {
        $sql = "select * from permisorol where idrol = :idrol";
        $params = array(":idrol" => $idrol);
        $this->PDO->execute($sql, 'ModeloPermisos/getPermisosDeRol', $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPersonaPermisosByPersona($idpersona) {
        $sql = "select * from personapermisos where idpersona = :idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, 'ModeloPermisos/getPersonaRolesByPersona', $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPermisosAsignados($idpersona) {
        $sql = "select distinct idpersona,idpermiso,'' as rol 
                from personapermiso 
                where idpersona = :idpersona
                union all
                select distinct :idpersona as idpersona,pr2.idpermiso,r.nombre as rol 
                from personarol pr 
                inner join permisorol pr2 on pr.idrol=pr2.idrol
                inner join rol r on pr.idrol=r.idrol
                where pr.idpersona=:idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, 'ModeloPermisos/getPermisosAsignados', $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPersonasPermisos($filtros) {
        //continuar

        $sql = "select distinct p.idpersona,t.descripcion tdoc, p.documentonro,
		concat(p.apellido,' ',p.nombre) as apeynom,p.apellido,p.nombre,
                concat(t.descripcion,' ',p.documentonro) as tipodocynro, 
                (case when u.estado=1 then 'SI' else 'NO' end) as estado,
                (select group_concat(rol.nombre separator '-') 
                from personarol prol 
                inner join rol on prol.idrol=rol.idrol 
                where prol.idpersona=p.idpersona) as rol
        from 
        persona p 
	left join personarol pr on p.idpersona=pr.idpersona
	left join rol r on pr.idrol=r.idrol
        left join permisorol pr2 on r.idrol=pr2.idrol
        left join personapermiso pp on p.idpersona=pp.idpersona
        inner join usuario u on u.idpersona=p.idpersona
        inner join tipodoc t on t.id=p.tipodoc where 1=1 ";
        $params = [];
        if (isset($filtros['apellido']) && $filtros['apellido'] != "") {
            $sql .= " and p.apellido like :apellido";
            $params[":apellido"] = "%" . $filtros['apellido'] . "%";
        }
        if (isset($filtros['nombre']) && $filtros['nombre'] != "") {
            $sql .= " and p.nombre like :nombre";
            $params[":nombre"] = "%" . $filtros['nombre'] . "%";
        }
        if (isset($filtros['tipdoc']) && $filtros['tipdoc'] != "" && $filtros['tipdoc'] != "0") {
            $sql .= " and t.id=:tipdoc";
            $params[":tipdoc"] = $filtros['tipdoc'];
        }
        if (isset($filtros['nrodoc']) && $filtros['nrodoc'] != "") {
            $sql .= " and p.documentonro = :nrodoc";
            $params[":nrodoc"] = $filtros['nrodoc'];
        }
        if (isset($filtros['rol']) && $filtros['rol'] != "" && $filtros['rol'] != "0") {
            $sql .= " and pr.idrol=:rol";
            $params[":rol"] = $filtros['rol'];
        }
        if (isset($filtros['permiso']) && $filtros['permiso'] != "" && $filtros['permiso'] != "0") {
            $sql .= " and (pr2.idpermiso=:permiso or pp.idpermiso=:permiso) ";
            $params[":permiso"] = $filtros['permiso'];
        }
        $sql .= " GROUP by p.idpersona order by p.apellido asc,p.nombre asc";

        $this->PDO->execute($sql, "Permiso/getPersonasPermisos", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function setpersonarol($idpersona, $idrol, $estado) {
        try {

            $this->PDO->beginTransaction('desde permisos');

            if ($estado == 1) { //Asignar Rol
                $sql = "insert into personarol(idpersona,idrol,usucrea,fechacrea) values(:idpersona,:idrol,:usucrea,CURRENT_TIMESTAMP)";
                $params = array(":idpersona" => $idpersona, ":idrol" => $idrol, ":usucrea" => $this->poroto->Session->getUsuario());
                $this->PDO->execute($sql, 'ModeloPermisos/setPersonaRol', $params);
                if ($idrol == 1) {
                    $sql = "select * from alumnos where idpersona = :idpersona";
                    $params = array(":idpersona" => $idpersona);
                    $this->PDO->execute($sql, 'ModeloPermisos/setPersonaRol/getalumnos', $params);
                    $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
                    if (sizeof($result) == 0) {
                        $sql = "insert into alumnos(idpersona,estadoalumno_id,usucrea,fechacrea) values(:idpersona,2,:usucrea,CURRENT_TIMESTAMP)";
                        $params = array(":idpersona" => $idpersona, ":usucrea" => $this->poroto->Session->getUsuario());
                        $this->PDO->execute($sql, 'ModeloPermisos/setPersonaRol/creaAlumno', $params);
                    }
                } elseif ($idrol == 4) {
                    $sql = "select * from profesores where idpersona = :idpersona";
                    $params = array(":idpersona" => $idpersona);
                    $this->PDO->execute($sql, 'ModeloPermisos/setPersonaRol/getprofesores', $params);
                    $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
                    if (sizeof($result) == 0) {
                        $sql = "insert into profesores(idpersona,usucrea,fechacrea) values(:idpersona,:usucrea,CURRENT_TIMESTAMP)";
                        $params = array(":idpersona" => $idpersona, ":usucrea" => $this->poroto->Session->getUsuario());
                        $consistencia = $this->PDO->execute($sql, 'ModeloPermisos/setPersonaRol/creaProfesor', $params);
                    }
                }
            } else {
                $sql = "delete from personarol where idpersona=:idpersona and idrol=:idrol";
                $params = array(":idpersona" => $idpersona, ":idrol" => $idrol);
                $this->PDO->execute($sql, 'ModeloPermisos/setPersonaRol', $params);
            }
            $this->PDO->commitTransaction('desde el permisos');
            return true;
        } catch (PDOException $e) {
            $this->PDO->rollBackTransaction('desde el permisos');
            return false;
        }
    }

    public function setpermisorol($idrol, $idpermiso, $estado) {
        if ($estado == 1) { //Asignar Rol
            $sql = "insert into permisosroles(idpermiso,idrol) values(:idpermiso,:idrol)";
        } else {
            $sql = "delete from permisosroles where idpermiso=:idpermiso and idrol=:idrol";
        }
        try {
            $params = array(":idpermiso" => $idpermiso, ":idrol" => $idrol);
            $this->PDO->execute($sql, 'ModeloPermisos/setpermisorol', $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setpersonapermiso($idpersona, $idpermiso, $estado) {
        if ($estado == 1) { //Asignar Rol
            $sql = "insert into personapermisos(idpersona,idpermiso) values(:idpersona,:idpermiso)";
        } else {
            $sql = "delete from personapermisos where idpersona=:idpersona and idpermiso=:idpermiso";
        }
        try {
            $params = array(":idpersona" => $idpersona, ":idpermiso" => $idpermiso);
            $this->PDO->execute($sql, 'ModeloPermisos/setpersonapermiso', $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setpersonaestado($idpersona, $estado) {
        $sql = "update usuarios set estado = :estado where idpersona=:idpersona";
        try {
            $params = array(":idpersona" => $idpersona, ":estado" => $estado);
            $this->PDO->execute($sql, 'ModeloPermisos/setpersonaestado', $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function resetpass($idpersona, $usuario) {
        $sql = "update usuarios set password = :usuario, primeracceso = 1 where idpersona=:idpersona";
        try {
            $params = array(":idpersona" => $idpersona, ":usuario" => $usuario);
            $this->PDO->execute($sql, 'ModeloPermisos/setpersonaestado', $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

}
