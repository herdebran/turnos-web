<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo($this->POROTO->Config['empresa_descripcion']); ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/empa.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="/javascripts/jquery-3.1.1.js"></script>
  </head>

  <body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top"
         <?php 
            if ($_SERVER['Entorno'] != "PRODUCCION") { echo "style='background: ".$this->POROTO->Config['empresa_color_header_desarrollo']."'";}
         ?>>
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
            
          <a class="" href="/ "><img src="/images/<?php echo $this->POROTO->Config['empresa_minilogo']; ?>"  style="margin-right: 20px; margin-top: -3px; width: 46px; height: 53px" /></a>
  
          <?php

              if ($_SERVER['Entorno'] != "PRODUCCION") {
                  echo "ENTORNO: " . $_SERVER['Entorno'];
              }
           ?>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container" role="main">

