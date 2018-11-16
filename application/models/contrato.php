<?php

class ModeloContrato
{

    private $PDO;

    public function __construct($poroto)
    {
        $this->PDO = $poroto->PDO;
    }


    public function getContratoById($idcontrato)
    {
        $sql = "SELECT a.idcontrato,a.idfideicomiso,a.fideicomiso, a.idunidadfuncional, a.unidadfuncional ,a.aplicaajuste, 
				ifnull(date_format(a.fechaalta, '%d/%m/%Y'),'') as fechaalta, 
                a.indicebase, a.valortotal, a.idmoneda, a.moneda, a.primervencimientodias, a.segundovencimientodias, 
                a.segundovencimientorecargo, a.tercervencimientodias, a.tercervencimientorecargo, a.tasapunitoriodiario, 
                a.idvendedor, a.vendedor,a.comisionventa, a.idestadocontrato, a.estadocontrato, a.observacion, a.adjunto
        FROM viewcontrato a
        where a.idcontrato=:idcontrato";
        $params = array(":idcontrato" => $idcontrato);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "contrato/getContratoById", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getContratoEstadosByIdContrato($idcontrato)
    {
        $sql = "SELECT b.idcontratoestado, ifnull(date_format(b.fechaestado, '%d/%m/%Y'),'')as fecha, c.descripcion as estado, b.activo, c.idestadocontrato as idestado
        FROM viewcontrato a
        INNER JOIN contratoestado b on a.idcontrato=b.idcontrato
        INNER JOIN estadocontrato c on b.idestadocontrato=c.idestadocontrato
        where a.idcontrato=:idcontrato";
        $sql .= " order by b.fechaestado desc";
        $params = array(":idcontrato" => $idcontrato);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "contrato/getContratoEstadosByIdContrato", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getContratoEstadoUltimoByIdContrato($idcontrato)
    {
        $sql = "SELECT max(b.idcontratoestado), ifnull(date_format(b.fechaestado, '%d/%m/%Y'),'')as fechaestado, c.descripcion as estadocontrato, b.activo, c.idestadocontrato
        FROM viewcontrato a
        INNER JOIN contratoestado b on a.idcontrato=b.idcontrato
        INNER JOIN estadocontrato c on b.idestadocontrato=c.idestadocontrato
        where a.idcontrato=:idcontrato AND activo=1";
        $sql .= " group by b.fechaestado, c.descripcion, b.activo, c.idestadocontrato";
        $params = array(":idcontrato" => $idcontrato);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "contrato/getContratoEstadoUltimoByIdContrato", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getContratoIntegrantesByIdContrato($idcontrato)
    {
        $sql = "SELECT a.idcontratopersona, p.idpersona,p.entidad as entidad,
        a.idcontratorol, c.descripcion as rol,a.tieneplancuentas
        FROM contratopersona a
        INNER JOIN contratorol c on a.idcontratorol=c.idcontratorol
        INNER JOIN viewpersona p ON p.idpersona=a.idpersona
        where a.idcontrato=:idcontrato";
        $sql .= " order by p.entidad asc";
        $params = array(":idcontrato" => $idcontrato);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "contrato/getContratoIntegrantesByIdContrato", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function nuevoContrato($valores)
    {
        try {
            //Agrego el contrato
            $sql = "insert into contrato (idunidadfuncional, aplicaajuste, fechaalta, indicebase, valortotal,
                    idmoneda, primervencimientodias, segundovencimientodias, segundovencimientorecargo, 
                    tercervencimientodias, tercervencimientorecargo, tasapunitoriodiario, 
                    idvendedor, comisionventa, observacion, usucrea, fechacrea)
                    values(:idunidadfuncional, :aplicaajuste, :fechaalta, :indicebase, :valortotal,
                    :idmoneda, :primervencimientodias, :segundovencimientodias, :segundovencimientorecargo, 
                    :tercervencimientodias, :tercervencimientorecargo, :tasapunitoriodiario,
                    :idvendedor, :comisionventa,:observacion, :usucrea, :fechacrea)";
            $params = array(
                ":idunidadfuncional" => $valores["idunidadfuncional"],
                ":aplicaajuste" => $valores["aplicaajuste"],
                ":fechaalta" => $valores["fechaalta"],
                ":indicebase" => $valores["indicebase"],
                ":valortotal" => $valores["valortotal"],
                ":idmoneda" => $valores["idmoneda"],
                ":primervencimientodias" => $valores["primervencimientodias"],
                ":segundovencimientodias" => $valores["segundovencimientodias"],
                ":segundovencimientorecargo" => $valores["segundovencimientorecargo"],
                ":tercervencimientodias" => $valores["tercervencimientodias"],
                ":tercervencimientorecargo" => $valores["tercervencimientorecargo"],
                ":tasapunitoriodiario" => $valores["tasapunitoriodiario"],
                ":idvendedor" => $valores["idvendedor"],
                ":comisionventa" => $valores["comisionventa"],
                ":observacion" => $valores["observacion"],
                ":usucrea" => $valores["usuario"],
                ":fechacrea" => date("Y-m-d H:i:s")
            );

            $this->PDO->execute($sql, 'contrato/nuevoContrato', $params);
            $idcontrato = $this->PDO->lastInsertId();

            return array("ok" => true, "message" => "", "idcontrato" => $idcontrato);

        } 
        catch (Exception $e) {
            //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    public function nuevoContratoEstado($idcontrato,$valores)
    {
        try {
            $sql = "update contratoestado set activo=0
                where idcontrato=:idcontrato";
            $params = array(
                ":idcontrato" => $idcontrato
            );

            $this->PDO->execute($sql, 'contrato/nuevoContratoEstado', $params);

            //Agrego el Estado
            $sql = "insert into contratoestado
                    (idcontrato, idestadocontrato, fechaestado, activo, 
                    usucrea, fechacrea)
                    values
                    (:idcontrato, :idestado,:fechaestado ,1,
                    :usucrea, :fechacrea)";
            $params = array(
                ":idcontrato" => $idcontrato,
                ":fechaestado" => $valores["fecha"],
                ":idestado" => $valores["idestado"],
                ":usucrea" => $valores["usuario"],
                ":fechacrea" => date("Y-m-d H:i:s")
            );

            $this->PDO->execute($sql, 'contrato/nuevoContratoEstado', $params);
            $idcontratoestado = $this->PDO->lastInsertId();

            return array("ok" => true, "message" => "", "idcontratoestado" => $idcontratoestado);

        } 
        catch (Exception $e) {
            //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    public function nuevoContratoIntegrante($idcontrato,$valores)
    {
        try {
            //Agrego el Integrante
            $sql = "insert into contratopersona 
                            (idcontrato, idpersona, idcontratorol, 
                            tieneplancuentas)
                            values
                            (:idcontrato, :idpersona, :idcontratorol, 
                            :tieneplancuentas)";
            $params = array(
                ":idcontrato" => $idcontrato,
                ":idpersona" => $valores["idpersona"],
                ":idcontratorol" => $valores["idcontratorol"],
                ":tieneplancuentas" => $valores["tieneplancuentas"]
            );
            $this->PDO->execute($sql, 'contrato/nuevoContratoIntegrante', $params);
            $idcontratopersona = $this->PDO->lastInsertId();

            return array("ok" => true, "message" => "", "idcontratopersona" => $idcontratopersona);

        } 
        catch (Exception $e) {
            //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    /**
     * 
     * @param type $valores
     * @return type
     */
    public function modificarContrato($valores)
    {

        try {
            //Modifico el contrato
            $sql = "update contrato set 
                    idunidadfuncional=:idunidadfuncional, aplicaajuste=:aplicaajuste, fechaalta=:fechaalta, indicebase=:indicebase, 
                    valortotal=:valortotal,idmoneda=:idmoneda, primervencimientodias=:primervencimientodias, segundovencimientodias=:segundovencimientodias, 
                    segundovencimientorecargo=:segundovencimientorecargo, tercervencimientodias=:tercervencimientodias, tercervencimientorecargo=:tercervencimientorecargo, 
                    tasapunitoriodiario=:tasapunitoriodiario, idvendedor=:idvendedor, comisionventa=:comisionventa, observacion=:observacion, 
                    usumodi=:usumodi, fechamodi=:fechamodi";
            $sql .= " where idcontrato = :idcontrato";

            $params = array(
                ":idcontrato" => $valores["idcontrato"],
                ":idunidadfuncional" => $valores["idunidadfuncional"],
                ":aplicaajuste" => $valores["aplicaajuste"],
                ":fechaalta" => $valores["fechaalta"],
                ":indicebase" => $valores["indicebase"],
                ":valortotal" => $valores["valortotal"],
                ":idmoneda" => $valores["idmoneda"],
                ":primervencimientodias" => $valores["primervencimientodias"],
                ":segundovencimientodias" => $valores["segundovencimientodias"],
                ":segundovencimientorecargo" => $valores["segundovencimientorecargo"],
                ":tercervencimientodias" => $valores["tercervencimientodias"],
                ":tercervencimientorecargo" => $valores["tercervencimientorecargo"],
                ":tasapunitoriodiario" => $valores["tasapunitoriodiario"],
                ":idvendedor" => $valores["idvendedor"],
                ":comisionventa" => $valores["comisionventa"],
                ":observacion" => $valores["observacion"],
                ":usumodi" => $valores["usuario"],
                ":fechamodi" => date("Y-m-d H:i:s")
            );

            $this->PDO->execute($sql, "contrato/modificarContrato", $params);
            return array("ok" => true, "message" => "", "idcontrato" => $valores["idcontrato"]);
        } 
        catch (Exception $e) {
            //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    /**
     * 
     * @param type $valores
     * @return type
     */
    public function borrarContratoEstado($valores)
    {
        try {
            $sql = "DELETE from contratoestado 
            WHERE idcontratoestado =:idcontratoestado";
            $params = array(
                ":idcontratoestado" => $valores["idcontratoestado"]
            );

            $this->PDO->execute($sql, "contrato/borrarContratoEstado", $params);
            return array("ok" => true, "message" => "");
        } 
        catch (Exception $e) {
            //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    /**
     * 
     * @param type $valores
     * @return type
     */
    public function borrarContratoIntegrante($valores)
    {
        try {
            $sql = "DELETE from contratopersona 
            WHERE idcontratopersona =:idcontratopersona";
            $params = array(
                ":idcontratopersona" => $valores["idcontratopersona"]
            );

            $this->PDO->execute($sql, "contrato/borrarContratoIntegrante", $params);
            return array("ok" => true, "message" => "");
        } 
        catch (Exception $e) {
        //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    /**
     * 
     * @param type $conjuntoDeIds
     * @return type
     */
    public function borrarContratoIntegranteConjuntoIds($conjuntoDeIds)
    {
        try {
            $sql = "DELETE from contratopersona 
            WHERE idcontratopersona not in ($conjuntoDeIds)";

            $this->PDO->execute($sql, "contrato/borrarContratoIntegranteConjuntoIds");
            return array("ok" => true, "message" => "");
        } 
        catch (Exception $e) {
        //Rollback the transaction.
            return array("ok" => false, "message" => $e->getMessage());
        }
    }

    /**
     * 
     * @param type $valores
     * @return type
     */
    public function modificarEstadoContrato($valores)
    {
        //Modificar estado de contrato
        $this->PDO->beginTransaction('contrato/modificarestadoContrato');
        try {
            $idestadocambio = $valores["idestadonuevo"];
            $sql = "SELECT idcontratoestado FROM contratoestado WHERE idcontrato =:idcontrato AND idestadocontrato=:idestadocontrato AND activo=1";
            $params = array(
                ":idcontrato" => $valores["idcontrato"],
                ":idestadocontrato" => $valores["idestadocontrato"]
            );
            $this->PDO->prepare($sql);
            $this->PDO->execute($sql, "contrato/modificarestadoContrato", $params);
            $result = $this->PDO->fetch(PDO::FETCH_ASSOC);

            $idestadoactual = $result["idestadocontrato"];
            if ($idestadoactual != "" && $idestadoactual != $idestadonuevo) {
                        //Estado
                $sql = "update contratoestado set activo=0
                        where idcontrato=:idcontrato";
                $params = array(
                    ":idcontrato" => $valores["idcontrato"]
                );
                $this->PDO->execute($sql, 'contrato/modificarestadoContrato', $params);

                $sql = "INSERT INTO contratoestado (idcontrato, idestadocontrato, fechaestado, activo, usucrea, fechacrea)
                        values(:idcontrato, :idestadocontrato, :fechaestado, 1, :usucrea, :fechacrea)";
                $params = array(
                    ":idcontrato" => $valores["idcontrato"],
                    ":idestadocontrato" => $valores["idestadocontrato"],
                    ":fechaestado" => $valores["fechaestado"],
                    ":usucrea" => $valores["usuario"],
                    ":fechacrea" => date("Y-m-d H:i:s")
                );

                $this->PDO->execute($sql, 'contrato/modificarestadoContrato', $params);
            }
            $this->PDO->commitTransaction('contrato/modificarestadoContrato');
            return array("ok" => true, "message" => "El contrato cambió su estado satisfactoriamente.");
        } 
        catch (Exception $e) {
                    //Rollback the transaction.
            $this->PDO->rollbackTransaction('contrato/modificarestadoContrato' . $e->getMessage());
            return array("ok" => false, "message" => "Error al cambiar el estado al contrato. Comuniquese con el administrador.");
        }
    }
    
    /**
     * Devuelve la descripcion de un estado con el id enviado
     * @param type $idestado
     * @return type
     */
    public function getEstadoContratoById($idestado)
    {
        $sql = "SELECT descripcion as estado FROM estadocontrato where idestadocontrato=:idestado";
        $params = array(":idestado" => $idestado);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "contrato/getEstadoById", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result['estado'];
    }
}
