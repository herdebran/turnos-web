<?php

if ( ! defined('POROTO')) exit('No direct script access allowed');


$config['titulo_sistema'] = 'Sistema de Gestion';
$config['empresa_nombre'] = 'Empresa SA';
$config['empresa_descripcion'] = 'Empresa descripcion';
$config['empresa_color_titulos'] ='#2e12c1';
$config['empresa_minilogo'] ='isotipo.png';
$config['empresa_color_header_desarrollo'] ='#2e12c1';
$config['path_to_mail_template'] = 'template_aguirre.html';
$config['path_to_mail_inscripcion'] = 'inscripcion_aguirre.txt';
$config['default_timezone'] = 'America/Argentina/Buenos_Aires';
$config['default_controller'] = 'novedades';
$config['default_controller_function'] = 'defentry';

$config['password_constraints_explained'] = 'La contraseña debe contener letras y/o números, y tener una longitud mínima de 8 caracteres';
$config['records_per_page'] = 30;

$config['session_minutes_alive'] = 20;


$config['mail_title_replace_string'] = '%%MAIL-TITLE%%';
$config['mail_body_replace_string'] = '%%MAIL-BODY%%';
$config['mail_nombre_replace_string'] = '%%NOMBRE%%';
$config['mail_documento_replace_string'] = '%%DOCUMENTO%%';
$config['mail_domicilio_replace_string'] = '%%DOMICILIO%%';
$config['mail_instrumento_replace_string'] = '%%INSTRUMENTO%%';
$config['mail_nivel_replace_string'] = '%%NIVEL%%';
$config['mail_carrera_replace_string'] = '%%CARRERA%%';

$config['dominios']['nacionalidad'] = array('ARGENTINA','BOLIVIANA', 'BRASILERA', 'CHILENA', 'COLOMBIANA', 'ECUATORIANA', 'PARAGUAYA','PERUANA','URUGUAYA','VENEZOLANA','RESTO DE AMERICA','EUROPA','ASIA','AFRICANA','OCEANIA');
$config['dominios']['estadocivil'] = array('SOLTERO/A', 'CASADO/A', 'DIVORICADO/A', 'VIUDO/A');
$config['dominios']['sexo'] = array('MASCULINO', 'FEMENINO');
$config['dominios']['turnos'] = array('MAÑANA', 'TARDE', 'VESPERTINO');
$config['dominios']['dias'] = array('LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO');
$config['dominios']['socio'] = array('NO SOCIO', 'ACTIVO', 'ADHERENTE');

$config['dia_default_horario'] = "1975-03-26";
// $config['dominios']['nivelcarrera']=array('PREPARATORIO','NIVEL 1','NIVEL 2','NIVEL 3');

$config['show_poroto_look_and_feel'] =FALSE;
$config['css_poroto_look_and_feel'] = 'poroto';
$config['show_stats_at_foot'] = FALSE;
$config['controler_case_sensitive'] = FALSE;

$config['autoload_libraries'] = array('siteLibrary');

$config['log_sql_filename'] = 'sql-log-'.date('Ymd').'.txt';

//Local en cada PC  
$config['base_url'] = 'http://localhost/';
