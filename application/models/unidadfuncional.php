<?php

//Consultas sobre fideicomisos.
class ModeloUnidadFuncional {

    private $PDO;

    public function __construct($poroto) {
        $this->PDO = $poroto->PDO;
    }

    public function getUnidadFuncional($filtros) {
        //continuar
        $sql = "select a.idunidadfuncional, a.unidadfuncional ,a.idfideicomiso, a.fideicomiso,
                a.tipofideicomiso, a.tipounidadfuncional, a.domicilio, a.legaldomicilio,
                a.numerounidadfuncional, a.numerolote, a.manzana, a.descripcion, a.idtipounidadfuncional,
                a.estadounidadfuncional,a.m2cubiertos, a.m2semicubiertos, a.m2descubiertos, a.valortotal, 
                a.idmoneda, a.moneda, a.observaciones, a.anulada, 
                concat(a.fideicomiso,' (',a.domicilio,')') as fideicomisoinfo,
                c.idcontrato as idcontrato,lpad(c.idcontrato,8,'0') as nrocontrato,estado.descripcion as estadocontrato
                from viewunidadfuncional a
                left join contrato c on c.idunidadfuncional=a.idunidadfuncional
                left join contratoestado ce on ce.idcontrato=c.idcontrato and ce.activo=1
		left join estadocontrato estado on estado.idestadocontrato=ce.idestadocontrato
                WHERE 1=1 ";
        $params = array();

        if (isset($filtros['fideicomiso']) && $filtros['fideicomiso'] >0) {
            $sql .= " and a.idfideicomiso = :idfideicomiso";
            $params[":idfideicomiso"] =  $filtros['fideicomiso'];
        }

        if (isset($filtros['tipo']) && $filtros['tipo'] >0) {
            $sql .= " and a.idtipounidadfuncional = :idtipounidadfuncional";
            $params[":idtipounidadfuncional"] =  $filtros['tipo'];
        }

        if (isset($filtros['descripcion']) && $filtros['descripcion'] != "") {
            $sql .= " and a.descripcion like :descripcion";
            $params[":descripcion"] = "%" . $filtros['descripcion'] . "%";
        }
        
        if (isset($filtros['unidadfuncional']) && $filtros['unidadfuncional'] != "") {
            $sql .= " and a.numerounidadfuncional = :unidadfuncional";
            $params[":unidadfuncional"] = $filtros['unidadfuncional'] ;
        }

        if (isset($filtros['lote']) && $filtros['lote'] != "") {
            $sql .= " and a.numerolote = :lote";
            $params[":lote"] = $filtros['lote'];
        }

        if (isset($filtros['manzana']) && $filtros['manzana'] != "") {
            $sql .= " and a.manzana = :manzana";
            $params[":manzana"] = $filtros['manzana'];
        }
        
        if (isset($filtros['estado']) && $filtros['estado'] >0) {
            $sql .= " and a.idestadounidadfuncional = :idestadounidadfuncional";
            $params[":idestadounidadfuncional"] =  $filtros['estado'];
        }


        if (isset($filtros['chkactivo'])) {
            $sql .= " and a.anulada = :anulada";
            $params[":anulada"] =  0;
        }
        
        $sql .= " order by a.fideicomiso, a.numerounidadfuncional";
        
        $this->PDO->execute($sql, "unidadfuncional/getUnidadFuncional", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function nuevaUnidadFuncional($valores) {
        //recibe todos los datos de la unidad funcional	
        $this->PDO->beginTransaction('unidadfuncional/nuevaUnidadFuncional');
        try {
            //Agrego el fideicomiso
            $sql = "insert into unidadfuncional (idfideicomiso, numerounidadfuncional, numerolote, manzana, descripcion, idtipounidadfuncional, 
                     idestadounidadfuncional,m2cubiertos, m2semicubiertos, m2descubiertos, valortotal, 
                     idmoneda, observaciones, anulada, usucrea, fechacrea)
                     values(:idfideicomiso, :numerounidadfuncional, :numerolote,:manzana ,:descripcion, :idtipounidadfuncional, 
                     :idestadounidadfuncional,:m2cubiertos, :m2semicubiertos, :m2descubiertos, :valortotal, :idmoneda, :observaciones, 0, :usucrea, :fechacrea)";
            $params = array(
                    ":idfideicomiso" => $valores["idfideicomiso"],
                    ":numerounidadfuncional" => $valores["numerounidadfuncional"],
                    ":numerolote" => $valores["numerolote"],
                    ":manzana" => $valores["manzana"],
                    ":descripcion" => $valores["descripcion"],
                    ":idtipounidadfuncional" => $valores["idtipounidadfuncional"],
                    ":idestadounidadfuncional" => $valores["idestadounidadfuncional"],
                    ":m2cubiertos" => $valores["m2cubiertos"],
                    ":m2semicubiertos" => $valores["m2semicubiertos"],
                    ":m2descubiertos" => $valores["m2descubiertos"],
                    ":valortotal" => $valores["valortotal"],
                    ":idmoneda" => $valores["idmoneda"],
                    ":observaciones" => $valores["observaciones"],                                                                                         
                    ":usucrea" => $valores["usuario"],
                    ":fechacrea" => date("Y-m-d H:i:s")
                    );
            
            $this->PDO->execute($sql, 'unidadfuncional/nuevaUnidadFuncional', $params);
            $idUnidadFuncional = $this->PDO->lastInsertId();
            
            $this->PDO->commitTransaction('unidadfuncional/nuevaUnidadFuncional');
            return array("ok" => true, "message" => "La unidad funcional se generó satisfactoriamente.", "idunidadfuncional" => $idUnidadFuncional);
          
        } //del try
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction('unidadfuncional/nuevaUnidadFuncional' . $e->getMessage());
            return array("ok" => false, "message" => "Error al generar la unidad funcional. Comuniquese con el administrador.");
        }
    }
    
    public function anularUnidadFuncional($valores) { //Revisar el return si esta bien!!!!
//Anular una operacion
        $this->PDO->beginTransaction('unidadfuncional/anularUnidadFuncional');
        try {
            $sql = " update unidadfuncional set ";
            $sql.= " anulada =:anulada, usumodi=:usumodi, fechamodi=:fechamodi";
            $sql.= " where idunidadfuncional = :idunidadfuncional";

            $params = array(
                    ":idunidadfuncional" => $valores["idunidadfuncional"],
                    ":anulada" => $valores["anulada"],
                    ":usumodi" => $valores["usumodi"],
                    ":fechamodi" => date("Y-m-d H:i:s")
                    );

            
            $this->PDO->execute($sql, 'unidadfuncional/anularUnidadFuncional', $params);
            $this->PDO->commitTransaction('unidadfuncional/anularUnidadFuncional');
            return array("ok" => true, "message" => "La unidad funcional se anuló satisfactoriamente.");
} //del try
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction('unidadfuncional/anularUnidadFuncional' . $e->getMessage());
            return array("ok" => false, "message" => "Error al anular la unidad funcional. Comuniquese con el administrador.");
        }            
    }
    
    public function modificarUnidadFuncional($valores){
        $this->PDO->beginTransaction('unidadfuncional/modificarUnidadFuncional');
        try {
            $sql = "update unidadfuncional set
                    numerounidadfuncional=:numerounidadfuncional, 
                    numerolote=:numerolote, 
                    manzana=:manzana,
                    descripcion=:descripcion, 
                    idtipounidadfuncional=:idtipounidadfuncional, 
                    idestadounidadfuncional=:idestadounidadfuncional, 
                    m2cubiertos=:m2cubiertos, 
                    m2semicubiertos=:m2semicubiertos, 
                    m2descubiertos=:m2descubiertos, 
                    valortotal=:valortotal, 
                    idmoneda=:idmoneda, 
                    observaciones=:observaciones, 
                    usumodi=:usumodi, 
                    fechamodi=:fechamodi";
            $sql.= " where idunidadfuncional = :idunidadfuncional";

            $params = array(
                    ":numerounidadfuncional" => $valores["numerounidadfuncional"],
                    ":numerolote" => $valores["numerolote"],
                    ":manzana" => $valores["manzana"],
                    ":descripcion" => $valores["descripcion"],
                    ":idtipounidadfuncional" => $valores["idtipounidadfuncional"],
                    ":idestadounidadfuncional" => $valores["idestadounidadfuncional"],
                    ":m2cubiertos" => $valores["m2cubiertos"],
                    ":m2semicubiertos" => $valores["m2semicubiertos"],
                    ":m2descubiertos" => $valores["m2descubiertos"],
                    ":valortotal" => $valores["valortotal"],
                    ":idmoneda" => $valores["idmoneda"],
                    ":observaciones" => $valores["observaciones"],                                                                                
                    ":usumodi" => $valores["usuario"],
                    ":fechamodi" => date("Y-m-d H:i:s"),
                    ":idunidadfuncional" => $valores["idunidadfuncional"]
                    );

            $this->PDO->execute($sql, "unidadfuncional/modificarUnidadFuncional", $params);
            $this->PDO->commitTransaction('unidadfuncional/modificarUnidadFuncional');
            return array("ok" => true, "message" => "La unidad funcional se modificó satisfactoriamente.");
            
            
        } //del try
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction('unidadfuncional/modificarUnidadFuncional' . $e->getMessage());
            return array("ok" => false, "message" => "Error al modificar la unidad funcional. Comuniquese con el administrador." . $e->getMessage());
        }        
    }
    
    public function getUnidadFuncionalById($idunidadfuncional) {
        $sql = "select a.idunidadfuncional, a.idfideicomiso, a.idtipounidadfuncional, a.tipounidadfuncional, a.unidadfuncional,
            a.numerounidadfuncional,a.numerolote, a.manzana, a.idestadounidadfuncional, a.estadounidadfuncional,
            a.m2cubiertos, a.m2semicubiertos, a.m2descubiertos,
            a.valortotal, a.idmoneda, a.moneda, a.observaciones, a.anulada, a.descripcion, a.usucrea, a.fechacrea, a.usumodi, a.fechamodi
            from viewunidadfuncional a
            where a.idunidadfuncional=:idunidadfuncional";

        $params = array(":idunidadfuncional" => $idunidadfuncional);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "unidadfuncional/getUnidadFuncionalById", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function ajaxAllUnidadesFuncionales($idfideicomiso) {
        $sql = " select idunidadfuncional, numerounidadfuncional, descripcion";
        $sql.= " from unidadfuncional";
        $sql.= " where idunidadfuncional=:idunidadfuncional and anulada=0";
        $sql.= " order by descripcion";
        $params = array(":idfideicomiso"=>$idfideicomiso);
        $this->PDO->execute($sql, 'unidadfuncional/ajaxAllUnidadesFuncionales',$params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;        
    }
    
    
        public function modificarIdEstadoUnidadFuncional($idUnidadFuncional, $idNuevoEstado){
        try {
            $sql = "update unidadfuncional set
                    idestadounidadfuncional=:idestadounidadfuncional";
            $sql.= " where idunidadfuncional = :idunidadfuncional";

            $params = array(
                    ":idestadounidadfuncional" => $idNuevoEstado,
                    ":idunidadfuncional" => $idUnidadFuncional
                    );

            $this->PDO->execute($sql, "unidadfuncional/modificarIdEstadoUnidadFuncional", $params);
            return array("ok" => true, "message" => "Cambio de estado realizado exitosamente.");
            
            
        } //del try
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction('unidadfuncional/modificarIdEstadoUnidadFuncional' . $e->getMessage());
            return array("ok" => false, "message" => "Error al cambiar el estado de la unidad funcional. Comuniquese con el administrador." . $e->getMessage());
        }        
    }

}
