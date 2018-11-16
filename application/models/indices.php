<?php

class Indices
{

    private $PDO;

    public function __construct($poroto)
    {
        $this->PDO = $poroto->PDO;
    }

    public function getUltimoIndice()
    {
        $sql = "SELECT max(a.idindiceconstruccion) as idindiceconstruccion, a.indiceconstruccion
        FROM indiceconstruccion a
        group by a.indiceconstruccion";

        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "indices/getUltimoIndice");
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getIndices()
    {
        $sql = "SELECT a.idindiceconstruccion, a.indiceconstruccion, a.observacion, a.usuacrea, a.fechacrea
        FROM indiceconstruccion a
        order by b.idindiceconstruccion asc";
        $this->PDO->prepare($sql);
        $this->PDO->execute($sql, "indices/getIndices");
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function nuevoIndiceConstruccion($valores) {
        //recibe todos los datos del indice	
        $this->PDO->beginTransaction('indices/nuevoIndiceConstruccion');
        try {
            //Agrego el indice
            $sql = "insert into indiceconstruccion (indiceconstruccion, observacion, usucrea, fechacrea)
                    values(:indiceconstruccion, :observacion, :usucrea, :fechacrea)";
            $params = array(
                    ":indiceconstruccion" => $valores["indiceconstruccion"],
                    ":observacion" => $valores["observacion"],                                                                           
                    ":usucrea" => $valores["usuario"],
                    ":fechacrea" => date("Y-m-d H:i:s")
                    );
            
            $this->PDO->execute($sql, 'indices/nuevoIndiceConstruccion', $params);
            $idIndiceConstruccion = $this->PDO->lastInsertId();
            
            $this->PDO->commitTransaction('indices/nuevoIndiceConstruccion');
            return array("ok" => true, "message" => "El índice de construccion se guardó satisfactoriamente.", "idindiceconstruccion" => $idIndiceConstruccion);
          
        } //del try
        catch (Exception $e) {
            //Rollback the transaction.
            $this->PDO->rollbackTransaction('indices/nuevoIndiceConstruccion' . $e->getMessage());
            return array("ok" => false, "message" => "Error al gardar el indice de construcción. Comuniquese con el administrador.");
        }
    }
}
