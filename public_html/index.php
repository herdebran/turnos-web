<?php 
/**
  * INDEX.PHP 
  *
  * MAIN ENTRY POINT
  *
  * @package  
  * @version  1.3
  * @access   public
  */

header("X-Author: prox.com.ar");
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('default_charset', 'UTF-8');

$hVersion = fopen("../version.info", "r") or die("Unable to open version file!");
$version = fgets($hVersion);
fclose($hVersion);

define('POROTO', $version);
$frameworkFolder = '../framework';     //no trailing slash
$applicationFolder = '../application'; //no trailing slash
$tempFolder = '../temp'; //no trailing slash
$webrootFolder = '.'; //no trailing slash

if (is_dir($frameworkFolder))   define('FRAMEWORKPATH', $frameworkFolder.'/');     else exit("Framework missconfigured");
if (is_dir($applicationFolder)) define('APPLICATIONPATH', $applicationFolder.'/'); else exit("Application missconfigured");
if (is_dir($tempFolder)) define('TEMPORARYPATH', $tempFolder.'/'); else exit("Temporary repository missconfigured");
if (is_dir($webrootFolder)) define('WEBROOTPATH', $webrootFolder.'/'); else exit("Webroot missconfigured");

require_once FRAMEWORKPATH.'core/core.php';

global $POROTO;
$POROTO= new Core();
$POROTO->start();