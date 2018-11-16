<?php

if ( ! defined('POROTO')) exit('No direct script access allowed');

$db['default_connection'] = 'turnos'; //Tanto PDO como la conexion comun usan esta conexion por defecto    
$db['turnos']['database'] = "turnos";
$db['turnos']['hostname'] = "localhost";
$db['turnos']['username'] = "root";
$db['turnos']['password'] = "1234";
$_SERVER['Entorno']="DESARROLLO";
