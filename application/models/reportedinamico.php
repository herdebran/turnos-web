<?php

/*
  public function consultaReporte() {
  $sql = "select * from tabla";
  $this->PDO->execute($sql, "ReporteDinamicoModel/consultaReporte");
  $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
  return $result;
  }
 */

class ReporteDinamicoModel {

    private $PDO;

    public function __construct($poroto) {
        $this->PDO = $poroto->PDO;
    }

    public function consultaManual($headers = true, $sql) {
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/consultaManual");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    private function getEncabezados($sql) {
        $sql = $sql . " limit 1";
        $this->PDO->execute($sql, "ReporteDinamicoModel/getEncabezados");
        $result = array_keys($this->PDO->fetch(PDO::FETCH_ASSOC));
        return $result;
    }

    public function getMenuReportes() {
        $sql = "select * from reportes where activo = 1 order by nombre";
        $this->PDO->execute($sql, "ReporteDinamicoModel/getMenuReportes");
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function alumnomateria($headers = true) {
        $sql = "SELECT* FROM alumnomateria";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/getAlumnoMateria");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    public function usuarioacceso($headers = true) {
        //Traer las ultimas 100 operaciones pagas de la persona
        $sql = "SELECT * FROM usuarioaccesos;";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/getCarreras");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }
    
    public function matriculaciones2018($headers = true) {
        
        $sql = "select am.idalumnomateria,p.apellido as Apellido,p.nombre as Nombre,p.documentonro as Documento,p.sexo as Sexo,
        c.nombre as Carrera,vm.materiacompleta as Materia,co.nombre as Comision,am.aniocursada as Año,
        eam.descripcion as Estado,am.fechaaprobacion as FechaAprobacion,am.usucrea as Usucrea,date_format(am.fechacrea,'%d/%m/%Y') as Fechacrea from alumnomateria am
        inner join viewmaterias vm on (am.idmateria=vm.idmateria and am.idcarrera=vm.idcarrera)
        left join comisiones co on am.idcomision=co.idcomision
        left join estadoalumnomateria eam on am.idestadoalumnomateria=eam.idestadoalumnomateria
        left join carreras c on am.idcarrera=c.idcarrera
        left join personas p on am.idpersona=p.idpersona where am.aniocursada=2018 order by am.fechacrea asc";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/matriculaciones2018");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    public function alumnoscondicionales($headers = true) {
        //Traer las ultimas 100 operaciones pagas de la persona
        $sql = "select p.apellido,p.nombre,p.documentonro,m.materiacompleta,c.descripcion as carrera,am.aniocursada, 
                eam.descripcion as estado,
                (case when condicional=1 then 'SI' else 'NO' end) as condicional,
                (case when condicionalregla6=1 then 'SI' else 'NO' end) as condicionalsimultaneo
                from alumnomateria am inner join alumnomateria_condicionalidades amc on
                am.idalumnomateria=amc.idalumnomateria
                inner join alumnocarrera ac on am.idalumnocarrera=ac.idalumnocarrera
                inner join personas p on am.idpersona=p.idpersona
                inner join carreras c on ac.idcarrera=c.idcarrera
                inner join viewmaterias m on am.idmateria=m.idmateria and ac.idcarrera=m.idcarrera
                inner join estadoalumnomateria eam on am.idestadoalumnomateria=eam.idestadoalumnomateria
                where am.aniocursada=2018";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/getAlumnosCondicionales");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }
    
    public function getComisionesporSexo($headers = true) {
        //Traer las ultimas 100 operaciones pagas de la persona
        $sql = "select 
            vm.materiaCompleta as Materia,c.nombre as Comision,
            sum(case when p.sexo='MASCULINO' then 1 else 0 end) as Masculino,
            sum(case when p.sexo='MASCULINO' then 0 else 1 end) as Femenino
            from alumnomateria am
            inner join alumnocarrera ac on am.idalumnocarrera=ac.idalumnocarrera
            inner join viewmaterias vm on am.idmateria=vm.idmateria and vm.idcarrera=ac.idcarrera
            inner join comisiones c on am.idcomision=c.idcomision
            inner join personas p on am.idpersona=p.idpersona
            where am.aniocursada=2018 and am.idestadoalumnomateria=2
            group by vm.materiacompleta,c.nombre,am.idestadoalumnomateria
            order by am.idmateria,am.idcomision";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/getComisionesporSexo");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }
    
    public function getComisionesporInstrumento($headers = true) {
        //Traer las ultimas 100 operaciones pagas de la persona
        $sql = "select 
            vm.materiaCompleta as Materia,
            i.nombre as instrumento,
            count(i.nombre) as cantidad
            from alumnomateria am
            inner join alumnocarrera ac on am.idalumnocarrera=ac.idalumnocarrera
            inner join viewmaterias vm on am.idmateria=vm.idmateria and vm.idcarrera=ac.idcarrera
            inner join comisiones c on am.idcomision=c.idcomision
            inner join personas p on am.idpersona=p.idpersona
            left join areas a on ac.idarea=a.idarea	
            left join instrumentos i on ac.idinstrumento=i.idinstrumento
            where am.aniocursada=2018 and am.idestadoalumnomateria=2
            and vm.materiacompleta like '%instrumento%'
            group by vm.materiaCompleta,i.nombre
            order by vm.materiaCompleta,i.nombre
            ";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/getComisionesporInstrumento");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }
    
    public function getComisionesporArea($headers = true) {
        //Traer las ultimas 100 operaciones pagas de la persona
        $sql = "
        select 
        vm.materiaCompleta as Materia,
        a.nombre as area,
        count(a.nombre) as cantidad
        from alumnomateria am
        inner join alumnocarrera ac on am.idalumnocarrera=ac.idalumnocarrera
        inner join viewmaterias vm on am.idmateria=vm.idmateria and vm.idcarrera=ac.idcarrera
        inner join comisiones c on am.idcomision=c.idcomision
        inner join personas p on am.idpersona=p.idpersona
        left join areas a on ac.idarea=a.idarea	
        left join instrumentos i on ac.idinstrumento=i.idinstrumento
        where am.aniocursada=2018 and am.idestadoalumnomateria=2
        and vm.materiacompleta like '%instrumento%'
        group by vm.materiaCompleta,a.nombre

            ";
        if ($headers) {
            $result = $this->getEncabezados($sql);
        } else {
            $this->PDO->execute($sql, "ReporteDinamicoModel/getComisionesporArea");
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

}
