<?php

class Aplicacion {

    private $PDO;
    private $POROTO;
    private $SES;

    public function __construct($poroto) {
        $this->POROTO = $poroto;
        $this->PDO = $poroto->PDO;
        $this->SES = $poroto->Session;
    }

    /**
     * Funciones a utilizar desde los distintos controladores (que tienen un objeto App) para que desde el controlador
     * se inicie la transaccion y se haga commit o rollback
     */
    public function beginTransaction(){
        return $this->PDO->beginTransaction("BEGIN TRANSACTION");
    }
    
    public function commitTransaction(){
        return $this->PDO->commitTransaction("COMMIT TRANSACTION");
    }
       
    public function rollbackTransaction(){
        return $this->PDO->rollBackTransaction("ROLLBACK TRANSACTION");
    }
             
    public function getMenu() {
        $this->PDO->execute($this->POROTO->getMenuSqlQuery(), 'Aplicacion/getMenu');
        return $this->PDO->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPersonas() {
        $sql = "SELECT idpersona, nombre, legajo, documentonro, fechanac,  direccion FROM persona";
        $this->PDO->execute($sql, 'Aplicacion/getAllCarreras');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function getAllProvincias() {
        $sql = "SELECT idprovincia, descripcion FROM provincia";
        $this->PDO->execute($sql, 'Aplicacion/getAllProvincias');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function ajaxAllMunicipios($idprovincia) {
        $sql = " select idmunicipio, descripcion";
        $sql.= " from municipio";
        $sql.= " where idprovincia=:idprovincia";
        $sql.= " order by descripcion";
        $params = array(":idprovincia"=>$idprovincia);
        $this->PDO->execute($sql, 'Aplicacion/ajaxAllMunicipios',$params);
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;        
    }
    
    public function ajaxAllLocalidades($idmunicipio) {
            $sql = " select idlocalidad, descripcion, codigopostal";
            $sql.= " from localidad";
            $sql.= " where idmunicipio=:idmunicipio";
            $sql.= " order by descripcion";
            $params = array(":idmunicipio"=>$idmunicipio);
            $this->PDO->execute($sql, 'Aplicacion/ajaxAllLocalidades',$params);
            $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
            return $result;        
    }
    
    public function getAllTipoDocumento() {
        $sql = "SELECT id, descripcion FROM tipodoc order by id";
        $this->PDO->execute($sql, 'Aplicacion/getAllTipoDocumento');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 
     * @return type
     */
    public function getAllRoles() {
        $sql = "select * from rol order by nombre";
        $this->PDO->execute($sql, 'Aplicacion/getAllRoles');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * 
     * @return type
     */
    public function getAllPermisos() {
        $sql = "select * from permiso order by nombre";
        $this->PDO->execute($sql, 'Aplicacion/getAllPermisos');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllConfiguraciones() {
        $sql = "select parametro,valor from configuracion order by orden";
        $this->PDO->execute($sql, 'Aplicacion/getAllConfiguraciones');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getConfiguracionByParametro($parametro) {
        $sql = "select parametro,valor from configuracion where parametro = :parametro";
        $this->PDO->execute($sql, 'Aplicacion/getConfiguracionByParametro', array(":parametro" => $parametro));
        $result = $this->PDO->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function changeConfigurationValue($array) {
        $sql = "UPDATE configuracion"
                . " SET valor = :valor, usumodi= :usumodi, fechamodi= CURRENT_TIMESTAMP"
                . " where parametro = :parametro";
        $params = array(":valor" => $array["valor"], ":parametro" => $array["parametro"], ":usumodi" => $this->SES->getUsuario() );
        $this->PDO->execute($sql, 'Aplicacion/changeConfigurationValue', $params);
    }

    public function getAllTipoFideicomiso() {
        $sql = "select idtipofideicomiso, descripcion from tipofideicomiso order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllTipoFideicomiso');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getAllEstadoFideicomiso() {
        $sql = "select idestadofideicomiso, descripcion from estadofideicomiso order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllEstadoFideicomiso');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    //Hernan 20180612
    public function getAllTipoUnidadFuncional() {
        $sql = "select idtipounidadfuncional, descripcion from tipounidadfuncional order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllTipoUnidadFuncional');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    //Hernan 20180620
    public function getAllEstadoUnidadFuncional() {
        $sql = "select idestadounidadfuncional, descripcion from estadounidadfuncional order by idestadounidadfuncional";
        $this->PDO->execute($sql, 'Aplicacion/getAllEstadoUnidadFuncional');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
  
    public function getAllTipoPersona() {
        $sql = "select idtipopersona, descripcion from tipopersona order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllTipoPersona');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllTipoCondicionIVA() {
        $sql = "select idtipocondicioniva, descripcion from tipocondicioniva order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllTipoCondicionIVA');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllTipoFactura() {
        $sql = "select idtipofactura, descripcion from tipofactura order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllTipoFactura');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllContratoRol() {
        $sql = "select idcontratorol, descripcion from contratorol order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllContratoRol');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getAllMonedas() {
        $sql = "SELECT idmoneda, descripcion FROM moneda";
        $this->PDO->execute($sql, 'Aplicacion/getAllMonedas');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getAllEstadoContrato() {
        $sql = "select idestadocontrato, descripcion from estadocontrato order by descripcion";
        $this->PDO->execute($sql, 'Aplicacion/getAllEstadoContrato');
        $result = $this->PDO->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }    
}

