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

class Databases {

    private $DbConnections;
    private $POROTO;
    private $DbConfig;
    private $LogFile;
    private $Caller; //to track in log, who's invoking sql query

    public function __construct($poroto) {
        $this->POROTO = $poroto;
        $this->DbConnections = array();
        include $this->POROTO->ConfigPath . 'db.php';
        $this->DbConfig = $db;
        if ($this->POROTO->Config['log_sql_filename'] != '')
            $this->LogFile = $this->POROTO->TempPath . $this->POROTO->Config['log_sql_filename'];
        else
            $this->LogFile = '';
    }

    function dbConnect($caller = ':(', $connId = '') {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        $dbcfg = $this->DbConfig[$connId]; //levanto local la configuracion de esta conexion
        $this->Caller = $caller;

        $this->DbConnections[$connId] = mysqli_connect($dbcfg['hostname'], $dbcfg['username'], $dbcfg['password'], $dbcfg['database']);
        $this->DbConnections[$connId]->set_charset("utf8");

        if (!$this->DbConnections[$connId]) {
            exit('Unable to connect to the database');
        }
    }

    function dbDisconnect($connId = '') {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        mysqli_close($this->DbConnections[$connId]);
    }

    function dbEscape($string, $connId = '') {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        return (mysqli_real_escape_string($this->DbConnections[$connId], $string));
    }

    function getSQLArray($sql, $connId = '') {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        if ($result === false) {
            if ($this->LogFile !== '')
                $this->POROTO->log2file($this->LogFile, mysqli_error($this->DbConnections[$connId]), $this->Caller);
            $this->POROTO->show_error("Database error");
        }
        $out = array();

        while ($row = $result->fetch_array(MYSQLI_ASSOC))
            $out[] = $row;
        return ($out);
    }

    function isOnlyOneRecord($sql, $connId = '') {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        if ($result === false) {
            if ($this->LogFile !== '')
                $this->POROTO->log2file($this->LogFile, mysqli_error($this->DbConnections[$connId]), $this->Caller);
            $this->POROTO->show_error("Database error");
        }

        return (mysqli_affected_rows($this->DbConnections[$connId]) == 1);
    }

    function insert($sql, $connId = '', $noBreakOnError = false) {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        if ($result === false) {
            if ($this->LogFile !== '')
                $this->POROTO->log2file($this->LogFile, mysqli_error($this->DbConnections[$connId]), $this->Caller);
            if ($noBreakOnError) {
                return(false);
            } else {
                $this->POROTO->show_error("Database error");
            }
        }
        return mysqli_insert_id($this->DbConnections[$connId]);
    }

    function update($sql, $connId = '', $noBreakOnError = false) {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        if ($result === false) {
            if ($this->LogFile !== '')
                $this->POROTO->log2file($this->LogFile, mysqli_error($this->DbConnections[$connId]), $this->Caller);
            if ($noBreakOnError) {
                return(false);
            } else {
                $this->POROTO->show_error("Database error");
            }
        }
        return $result;
    }

    function delete($sql, $connId = '', $noBreakOnError = false) {
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        if ($result === false) {
            if ($this->LogFile !== '')
                $this->POROTO->log2file($this->LogFile, mysqli_error($this->DbConnections[$connId]), $this->Caller);
            if ($noBreakOnError) {
                return(false);
            } else {
                $this->POROTO->show_error("Database error");
            }
        }
        return $result;
    }

    function begintrans($connId = '') {
        $sql = "BEGIN";
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        return $result;
    }

    function commit($connId = '') {
        $sql = "COMMIT";
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        return $result;
    }

    function rollback($connId = '') {
        $sql = "ROLLBACK";
        if ($connId === '')
            $connId = $this->DbConfig['default_connection'];
        if ($this->LogFile !== '')
            $this->POROTO->log2file($this->LogFile, $sql, $this->Caller);
        $result = mysqli_query($this->DbConnections[$connId], $sql);
        return $result;
    }

}
