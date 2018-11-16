<?php

/**
 * Databases access class 
 *
 * You can access databases using this class. It has an associated
 * configuration file: /site.application/config/db.php
 *
 * @package  poroto
 * @version  1.2
 * @access   public
 * @copyright 2015-2017 7dedos
 * @author Augusto Wloch <agosto@7dedos.com>
 */
if (!defined('POROTO'))
    exit('No direct script access allowed');

class Databasespdo {

    private $POROTO;
    private $DbConfig;
    private $pdo;
    private $Caller; //to track in log, who's invoking sql query
    private $stmt;
    private $LogFile;

    public function __construct($poroto) {
        $this->POROTO = $poroto;
        include $this->POROTO->ConfigPath . 'db.php';
        $this->DbConfig = $db;
        $connId = $this->DbConfig['default_connection'];   
        $hostname = $this->DbConfig[$connId]["hostname"];
        $dbname = $this->DbConfig[$connId]["database"];
        $username = $this->DbConfig[$connId]["username"];
        $pass = $this->DbConfig[$connId]["password"];

        try {
            $this->pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES  \'UTF8\''));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "¡Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        if ($this->POROTO->Config['log_sql_filename'] != '')
            $this->LogFile = $this->POROTO->TempPath . $this->POROTO->Config['log_sql_filename'];
        else
            $this->LogFile = '';
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function closePdo() {
        return $this->pdo = null;
    }

    public function prepare($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute($sql, $caller, $params = null) {
        $this->prepare($sql);
        if ($this->LogFile !== '') {
            if ($params) {
                $this->POROTO->log2file($this->LogFile, $sql, $caller . "/" . implode("/", $params));
            } else {
                $this->POROTO->log2file($this->LogFile, $sql, $caller);
            }
        }
        if (!$this->stmt->execute($params)) {
            if ($this->LogFile !== '')
                $this->POROTO->log2file($this->LogFile, $this->pdo->errorInfo()); //mysqli_error($this->DbConnections[$connId]), $this->Caller);
            $this->POROTO->show_error("Database error " . $this->pdo->errorInfo());
        }
    }

    public function fetchall($conf = null) {
        //OJO
        //Si acepta el parametro cuando devuelve los resultados los va a devolver como un array que los titulos seran los campos
        //Si no le paso ningun parametro devolvera los resultados en un array con los titulos y a su vez con indices
        //o sea que podria llamar a un dato por su indice [0] en vez de [id]
        //El fetchall con parametro se dejo para usarlo desde los datatables donde UNICAMENTE puedo usar esa opcion, sino duplicaria los registros.
        
        return $this->stmt->fetchAll($conf);
    }

    public function fetch($conf = null) {
        return $this->stmt->fetch($conf);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction($caller) {
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, "BEGIN", $caller);
        return $this->pdo->beginTransaction();
    }

    public function commitTransaction($caller) {
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, "COMMIT", $caller);
        return $this->pdo->commit();
    }

    public function rollBackTransaction($caller) {
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, "ROLLBACK", $caller);
        return $this->pdo->rollBack();
    }

}
