<?php

class ModeloEspecialidad {

    private $PDO;
    private $_session;

    public function __construct($poroto) {
        $this->PDO = $poroto->PDO;
        $this->_session = $poroto->Session;
    }

    /**
     * Inserta nueva especialidad y retorna el id de insercion.
     * @param type $valores
     * @return type
     */
    public function nuevaEspecialidad($valores) {
        //Agrego la persona
        $sql = "insert into especialidad (descripcion, activo)
                    values(:descripcion, 1)";
        $params = array(
            ":descripcion" => $valores["descripcion"]
        );

        $this->PDO->execute($sql, 'especialidad/nuevaEspecialidad', $params);
        return $this->PDO->lastInsertId();
    }

    //Lista todas las especialidades
    public function getAllEspecialidades() {
        $sql = "SELECT e.id, e.descripcion, e.activo 
                FROM especialidad e
                WHERE 1=1 ";
        $params = array();

        $sql .= " order by e.descripcion asc";

        
        $this->PDO->execute($sql, "especialidad/getAllEspecialidades", $params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;    }

}
