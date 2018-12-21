<?php 
/**
  * Site Routing Rules
  *
  * The $route array contains the rules. Just like codeigniter, the
  * (:any) wildcard could be used
  *
  * @package  poroto
  * @version  1.2
  * @access   public
  * @copyright 2015-2017 7dedos
  * @author Augusto Wloch <agosto@7dedos.com>
  */
if ( ! defined('POROTO')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| If this is not set then CodeIgniter will guess the protocol, domain and
| path to your installation.
|
*/

$route['login'] = 'home/login';
$route['logout'] = 'home/logout';
$route['forgot'] = 'home/forgot';
$route['primeracceso'] = 'home/primeracceso';
$route['habilitar/(:any)/(:any)'] = 'seguridad/habilitar/$1/si/$2';
$route['deshabilitar/(:any)/(:any)'] = 'seguridad/habilitar/$1/no/$2';
$route['resetpassword/(:any)/(:any)'] = 'seguridad/resetpassword/$1/$2';
$route['pickrole'] = 'seguridad/pickrole';
$route['gestion-personas'] = 'persona/gestionpersonas';
$route['crear-persona'] = 'persona/crearpersona';
$route['crear-persona/(:any)'] = 'persona/crearpersona/$1';
$route['guardar-persona'] = 'persona/guardarpersona';
$route['misdatos'] = 'home/misdatos';
$route['ajaxlocalidades/(:any)'] = 'home/ajaxlocalidades/$1';
$route['proceso1/(:any)'] = 'procesos/proceso1/$1';
$route['proceso2'] = 'procesos/proceso2';
$route['proceso3/(:any)'] = 'procesos/proceso3/$1';
$route['proceso4/(:any)/(:any)'] = 'procesos/proceso4/$1/$2';
$route['proceso5/(:any)/(:any)'] = 'procesos/proceso5/$1/$2';
$route['verprocesos'] = 'procesos/verprocesos';
$route['reportes-dinamicos'] = 'reportedinamico/reportes';
$route['gestion-permisos'] = 'permisos';
$route['ajaxmunicipios/(:any)'] = 'fideicomiso/ajaxmunicipios/$1';
$route['ajaxlocalidades/(:any)'] = 'fideicomiso/ajaxlocalidades/$1';
$route['crear-usuario/(:any)'] = 'usuario/crearusuario/$1';
$route['guardar-usuario'] = 'usuario/guardarusuario';
$route['resetear-pass/(:any)'] = 'usuario/resetearpass/$1';
