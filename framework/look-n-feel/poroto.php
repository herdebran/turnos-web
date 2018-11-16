<?php 
/**
  * Poroto Base Look and Feel
  *
  * Just a quick L&F to start working fast with some degree
  * of beauty and grace ;-)
  *
  * @package  poroto
  * @version  1.2
  * @access   public
  * @copyright 2015-2017 7dedos
  * @author Augusto Wloch <agosto@7dedos.com>
  */
if ( ! defined('POROTO')) exit('No direct script access allowed');

echo "
body,html { 
  border: 0px; margin: 0px; padding: 10px;
  font-family: Verdana, Sans-serif; font-size: 12px; 
  background-color: #fcfafc;
  font-size: 12px;
}

a, a:active, a:visited {
	color: #f1baca;
	color: #fcaa1c;
	text-decoration: none;
}
a:hover {
	color: #fc3030;
}

.avatar {
	border-radius: 25px;
	border: 1px dashed #cccacc;
	vertical-align: middlec;
}
h1,h2,h3,h4,h5,h6 { margin:0; padding: 0; }

h1 { color: #d9d9d0; font-size: 24px; text-align: right;}
h2 { color: #333; font-size: 20px; }
h3 { color: #222; font-size: 14px;  }

p { font-size: 11px; margin: 0; padding: 0; }

table { border-collapse: collapse; }

table, th, td { border: 1px solid #eee; }
thead th { background-color: #ccc; }

div.poroto-footer {
	width: 100%;
	text-align: right;
}

.initially-hidden {
	// display: none;
}

.aForm,
.createForm,
.loginForm{
	border: 1px dashed #ccc;
	margin: 30px;
	padding: 30px;
}

.aForm label,
.createForm label,
.loginForm label {
	padding: 5px;
	font-weight: bold;
	color: #ccc;
}

.aForm input,
.createForm input,
.createForm select,
.loginForm input {
	font-family: Verdana, Sans-serif; font-size: 12px; 
	display: block;
	margin-bottom: 10px;
	padding: 5px;
}

.aForm label,
.createForm label,
.loginForm label {
	float: left;
	text-align: right;
	width: 100px;
	margin-right: 20px;
}

.aForm input[type=\"submit\"],
.loginForm input[type=\"submit\"] {
  color: #aaa;
  font-weight: bold;
  text-transform: uppercase;
  width: 300px;
}

";

