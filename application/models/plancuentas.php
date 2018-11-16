<?php

class PlanCuentas
{

    private $PDO;

    public function __construct($poroto)
    {
        $this->PDO = $poroto->PDO;
    }

    public function getPlanCuentasById($idplancuentas)
    {
        $sql = "SELECT a.idplancuentas, a.fideicomiso, a.idunidadfuncional, a.unidadfuncional, 
        a.idcontrato, a.fechaalta, a.estadocontrato, a.idcontratopersona, a.idconcepto, a.concepto, 
        a.periodo, a.periodomes, a.periodoanio, a.fechainiciopunitorios, a.idprorrateounidadfuncional,
        a.montobase, a.resta, a.restadescripcion, a.aplicaajusteicc, a.aplicapunitorios, a.cuotaexpiradavencimientos, 
        a.montoajustado, a.montoajustadorestante, a.pagado, a.observaciones
        FROM viewplancuentas p
        where p.idplancuentas=:idplancuentas";
        $params = array(":idplancuentas" => $idplancuentas);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "plancuentas/getPlanCuentasById", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllConceptos($idcontratopersona)
    {
        $sql = "SELECT a.idconcepto, a.descripcion as concepto
                from concepto a
                inner join conceptoutilizacion b on a.idconceptoutilizacion = b.idconceptoutilizacion
                where a.activo=1 and b.descripcion='PLAN DE CUENTAS' and b.activo=1";
        $sql .= " order by a.descripcion";
        $params = array(":idcontratopersona" => $idcontratopersona);
        $this->PDO->execute($sql, 'plancuentas/getConceptosAAplicar', $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getConceptosAAplicar($idcontratopersona)
    {
        $sql = " select a.idcontrato, a.idplancuentasconceptoaaplicar, a.idconceptoplancuentas, b.descripcion as conceptoplancuentas, a.idconceptoaaplicar, c.descripcion as conceptoaaplicar, a.porcentaje, a.ordenaplicacion
                from plancuentasconceptoaaplicar a 
                inner join concepto b on a.idconceptoplancuentas = b.idconcepto
                inner join concepto c on a.idconceptoaaplicar = c.idconcepto
                where a.idcontratopersona=:idcontratopersona";
        $sql .= " order by b.descripcion, a.ordenaplicacion";
        $params = array(":idcontratopersona" => $idcontratopersona);
        $this->PDO->execute($sql, 'plancuentas/getConceptosAAplicar', $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPlanCuentasEncabezadosByIdContrato($idcontrato)
    {
        $sql = "SELECT a.fideicomiso, a.unidadfuncional
        FROM viewplancuentas a
        where a.idcontrato=:idcontrato";
        $params = array(":idcontrato" => $idcontrato);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "plancuentas/getPlanCuentasByIdContratoPersona", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPlanCuentasByIdContratoPersona($idcontratopersona)
    {
        $sql = "SELECT a.idplancuentas, a.periodo, a.concepto, a.aplicaajusteicc, a.aplicapunitorios, a.cuotaexpiradavencimientos, 
        a.idprorrateounidadfuncional, a.montobase, a.montoajustado, a.montoajustadorestante, 
        a.fechainiciopunitorios, a.pagado, a.observaciones
        FROM viewplancuentas a
        where a.idcontratopersona=:idcontratopersona";
        $sql .= " order by a.fechaestado desc";
        $params = array(":idcontratopersona" => $idcontratopersona);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "plancuentas/getPlanCuentasByIdContratoPersona", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getConceptosAAplicarByIdContratoPersona($idcontratopersona)
    {
        $sql = "SELECT a.idplancuentasconceptoaaplicar, b.descripcion as conceptoplancuentas, 
        c.descripcion as conceptoaaplicar, a.porcentaje, 
        a.ordenaplicacion, case a.aplicapunitorios when 1 then 'SI' else 'NO' end as aplicapunitorios
        FROM plancuentasconceptoaaplicar a
		INNER JOIN concepto b on a.idconceptoplancuentas=b.idconcepto
        INNER JOIN concepto c on a.idconceptoaaplicar=b.idconcepto
        where a.idcontratopersona=:idcontratopersona";
        $sql .= " order by b.descripcion, a.ordenaplicacion";
        $params = array(":idcontratopersona" => $idcontratopersona);
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "plancuentas/ajaxAllConceptosAAplicarByIdContratoPersona", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function nuevoPlanCuentas($valores)
    {
        try {
            //Agrego los conceptos
            for ($anio = $valores["aniodesde"]; $anio <= ["aniohasta"]; $anio++) {
                for ($mes = $valores["mesdesde"]; $mes <= $valores["meshasta"]; $mes++) {
                    $sql = "insert into plancuentas (idcontratopersona, idconcepto, periodo,
                            fechainiciopunitorios, idprorrateounidadfuncional, montobase, resta, aplicaajusteicc,
                            aplicapunitorios, cuotaexpiradavencimientos, montoajustado, montoajustadorestante,
                            pagado, observaciones, usucrea, fechacrea)
                            values(:idcontratopersona, :idconcepto, :periodo,:fechainiciopunitorios, 
                            :idprorrateounidadfuncional, :montobase, :resta, :aplicaajusteicc,
                            :aplicapunitorios, :cuotaexpiradavencimientos, :montoajustado, 
                            :montoajustadorestante, :pagado, :observaciones, :usucrea, fechacrea)";
                    $params = array(
                        ":idcontratopersona" => $valores["idcontratopersona"],
                        ":idconcepto" => $valores["idconcepto"],
                        ":periodo" => "01" . $mes . "/" . $anio,
                        ":fechainiciopunitorios" => "01" . $mes . "/" . $anio,
                        ":idprorrateounidadfuncional" => $valores["idprorrateounidadfuncional"],
                        ":montobase" => $valores["montobase"],
                        ":resta" => $valores["resta"],
                        ":aplicaajusteicc" => $valores["aplicaajusteicc"],
                        ":aplicapunitorios" => $valores["aplicapunitorios"],
                        ":cuotaexpiradavencimientos" => $valores["cuotaexpiradavencimientos"],
                        ":montoajustado" => $valores["montoajustado"],
                        ":montoajustadorestante" => $valores["montoajustadorestante"],
                        ":pagado" => $valores["pagado"],
                        ":observaciones" => $valores["observaciones"],
                        ":usucrea" => $valores["usuario"],
                        ":fechacrea" => date("Y-m-d H:i:s")
                    );
                    $this->PDO->execute($sql, "plancuentas/nuevoPlanCuentas", $params);
                    $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
                    if (count($result) > 0) {
                            //Rollback the transaction.
                        return array("ok" => false, "message" => "Error al actualizar el plan de cuentas. Comuniquese con el administrador.");
                        exit();
                    }
                }
            }

            $this->PDO->execute($sql, 'plancuentas/nuevoPlanCuentas', $params);
            return array("ok" => true, "message" => "El plan de cuentas se cargó satisfactoriamente.", "idplancuentas" => $idplancuentas);

        } //del try 
        catch (Exception $e) {
            //Rollback the transaction.
            return array("ok" => false, "message" => "Error al actualizar el plan de cuentas. Comuniquese con el administrador.");
        }
    }

    public function nuevoConceptoAAplicar($valores)
    {
        try {
            $sql = "INSERT into plancuentasconceptoaaplicar (idcontratopersona, idconceptoplancuentas, idconceptoaaplicar, porcentaje, ordenaplicacion, aplicapunitorios, usucrea, fechacrea)
                    values(:idcontrato, :idconceptoplancuentas, :idconceptoaaplicar, :porcentaje, :ordenaplicacion, :usucrea, :fechacrea)";
            $params = array(
                ":idcontratopersona" => $valores["idcontratopersona"],
                ":idconceptoplancuentas" => $valores["idconceptoplancuentas"],
                ":idconceptoaaplicar" => $valores["idconceptoaaplicar"],
                ":porcentaje" => $valores["porcentaje"],
                ":ordenaplicacion" => $valores["ordenaplicacion"],
                ":aplicapunitorios" => $valores["aplicapunitorios"],
                ":usucrea" => $valores["usuario"],
                ":fechacrea" => date("Y-m-d H:i:s")
            );

            $this->PDO->execute($sql, "plancuentas/nuevoConceptoAAplicar", $params);
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                return array("ok" => false, "message" => "Error al actualizar los conceptos aplicados. Comuniquese con el administrador.");
                exit();
            }
            return array("ok" => true, "message" => "El concepto aplicado se cargó satisfactoriamente.", "idplancuentas" => $idplancuentas);
        } //del try 
        catch (Exception $e) {
                //Rollback the transaction.
            $this->PDO->rollbackTransaction('plancuentas/nuevoConceptoAAplicar' . $e->getMessage());
            return array("ok" => false, "message" => "Error al insertar los conceptos a aplicar. Comuniquese con el administrador.");
        }
    }

    public function clonarPlanCuentasCuotasImpagas($valores)
    {
        try {
            //Plan de Cuentas de cuotas impagas
            $sql = "INSERT INTO plancuentas (idcontratopersona, idconcepto, periodo,
                    fechainiciopunitorios, idprorrateounidadfuncional, montobase, resta, aplicaajusteicc,
                    aplicapunitorios, cuotaexpiradavencimientos, montoajustado, montoajustadorestante,
                    pagado, observaciones, usucrea, fechacrea)
                    select :idcontratopersonadestino, idconcepto, periodo,
                    fechainiciopunitorios, idprorrateounidadfuncional, montobase, resta, aplicaajusteicc,
                    aplicapunitorios, cuotaexpiradavencimientos, montoajustado, montoajustadorestante,
                    pagado, observaciones, :usucrea, :fechacrea
                    from plancuentas a
                    where a.idcontratopersona=:idcontratopersonaorigen and a.pagado=0";
            $params = array(
                ":idcontratopersonaorigen" => $valores["idcontratopersonaorigen"],
                ":idcontratopersonadestino" => $valores["idcontratopersonadestino"],
                ":usucrea" => $valores["usuario"],
                ":fechacrea" => date("Y-m-d H:i:s")
            );

            $this->PDO->execute($sql, "plancuentas/clonarPlanCuentasCuotasImpagas", $params);
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                return array("ok" => false, "message" => "Error al clonar el plan de cuentas. Comuniquese con el administrador.");
                exit();
            }
            return array("ok" => true, "message" => "El plan de cuentas se clonó satisfactoriamente.");

        } //del try 
        catch (Exception $e) {
            return array("ok" => false, "message" => "Error al clonar el plan de cuentas. Comuniquese con el administrador.");
        }
    }

    public function clonarPlanCuentasConceptosAAplicar($valores)
    {
        try {
             //Conceptos a aplicar al Plan de Cuentas
            $sql = "INSERT INTO plancuentasconceptoaaplicar (idcontratopersona, idconceptoplancuentas, idconceptoaaplicar, porcentaje, ordenaplicacion, aplicapunitorios)
                    select idcontratopersona, idconceptoplancuentas, idconceptoaaplicar, porcentaje, ordenaplicacion, aplicapunitorios
                    from plancuentasconceptoaaplicar a
                    where a.idcontratopersona=:idcontratopersona";
            $params = array(
                ":idcontratopersona" => $valores["idcontratopersona"],
            );

            $this->PDO->execute($sql, "plancuentas/clonarPlanCuentasConceptosAAplicar", $params);
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                    //Rollback the transaction.
                return array("ok" => false, "message" => "Error al clonar los conceptos a aplicar al plan de cuentas. Comuniquese con el administrador.");
                exit();
            }
            return array("ok" => true, "message" => "Los conceptos a aplicar al plan de cuentas se clonaron satisfactoriamente.");

        } //del try 
        catch (Exception $e) {
            return array("ok" => false, "message" => "Error al clonar los conceptos a aplicar al plan de cuentas. Comuniquese con el administrador.");
        }
    }
}
