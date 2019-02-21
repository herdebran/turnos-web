<?php

class ModeloObraSocial {

    private $PDO;
    private $_session;

    public function __construct($poroto) {
        $this->PDO = $poroto->PDO;
        $this->_session = $poroto->Session;
    }

    /**
     * Inserta nueva os y retorna el id de insercion.
     * @param type $valores
     * @return type
     */
    public function nuevaObraSocial($valores) {
        //Agrego la persona
        $sql = "insert into obrasocial (descripcion, activo)
                    values(:descripcion, 1)";
        $params = array(
            ":descripcion" => $valores["descripcion"]
        );

        $this->PDO->execute($sql, 'obrasocial/nuevaObraSocial', $params);
        return $this->PDO->lastInsertId();
    }

    //Lista todas las obras sociales
    public function getAllObrasSociales() {
        $sql = "SELECT e.id, e.descripcion, e.activo 
                FROM obrasocial e
                WHERE 1=1 ";
        $params = array();

        $sql .= " order by e.descripcion asc";

        
        $this->PDO->execute($sql, "obrasocial/getAllObrasSociales", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;    
    }

    /**
     * Dado un id se desactiva la os en cuestion
     * @param type $id
     * @return type
     */
    public function setearActivoObraSocial($id,$valor) {
        //Agrego la persona (el if es feo pero no funciona de otra manera)
        if ($valor==1)
            $sql = "update obrasocial set activo=1  where id=:id";
        else if ($valor==0)
            $sql = "update obrasocial set activo=0  where id=:id";
        
        $params = array(
            ":id" => $id
        );

        $this->PDO->execute($sql, 'obrasocial/setearActivoObraSocial', $params);
        return array("ok" => true, "message" => "Obra social desactivada ok.");        
    }
    
    public function existeObraSocialByNombre($nombre) {
        $sql = "SELECT * 
                FROM obrasocial 
                WHERE descripcion=:nombre ";
        $params = array(":nombre" => $nombre);

        $this->PDO->execute($sql, "obrasocial/existeObraSocialByNombre", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result==null?false:true;    
    }
}
