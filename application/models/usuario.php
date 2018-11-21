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
            ":password" => $valores["password"],
            ":usucrea" => $this->_session->getUsuario(),
            ":fechacrea" => date("Y-m-d H:i:s")
        );

        $this->PDO->execute($sql, 'usuario/persistirUsuario', $params);
        return $this->PDO->lastInsertId();
    }

    public function getUsuarioByIdPersona($idpersona) {
        $sql = "select * from usuario where idpersona = :idpersona";
        $params = array(":idpersona" => $idpersona);
        $this->PDO->execute($sql, "ModeloUsuario/getUsuarioByIdPersona", $params);
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

}
