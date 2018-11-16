<?php
$ses = & $this->POROTO->Session;
$message = $ses->getMessage();
$alertType = "alert-info";
switch ($message['type']) {
    case SessionMessageType::Success: $alertType = "alert-success";
        break;
    case SessionMessageType::TransactionError: $alertType = "alert-danger";
        break;
}
?>
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
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
        <!-- Bootstrap core CSS -->
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <link href="/css/empa.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="/javascripts/jquery-3.1.1.js"></script>
        <script src="/javascripts/tinymce/tinymce.min.js?apiKey=t5r11o76f1i1aiuuobeuevva0us4a6srjkc8qe222vokg8t3"></script>

        <!-- Libreria dataTables -->
        <link rel="stylesheet" type="text/css" href="/css/datatable/datatables.min.css"/>

        <!-- Estilos datepicker -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="/css/datatable/exportar/buttons.dataTables.min.css"/>
        <link rel="stylesheet" type="text/css" href="/css/botones.css"/>
       
        <script type="text/javascript" src="/javascripts/datatable/datatables.min.js"></script>
        <script src="/javascripts/datatable/exportar/dataTables.buttons.min.js"></script>
        <script src="/javascripts/datatable/exportar/buttons.flash.min.js"></script>
        <script src="/javascripts/datatable/exportar/jszip.min.js"></script>
        <script src="/javascripts/datatable/exportar/pdfmake.min.js"></script>
        <script src="/javascripts/datatable/exportar/vfs_fonts.js"></script>
        <script src="/javascripts/datatable/exportar/buttons.html5.min.js"></script>
        <script src="/javascripts/datatable/exportar/buttons.print.min.js"></script>
        <script src="/javascripts/jquery.countdown.min.js"></script>
        <script src="/javascripts/moment.js"></script>


        <!-- Jscript datepicker -->
        <script src="/javascripts/jquery-ui.js"></script>
        <script>
            // Muestra un alerta del color que se pase en la claseBootrapAlert (alert-success, alert-warning, alert-danger) y mensaje en mensaje...
            // Inspidara en la tediosa necesidad de reinicar la pagina para ver el alerta lindo...
            function addGlobalMessage(claseBootstrapAlert, mensaje) {
                var div = $("<div>").addClass("alert alert-dismissible animated fadeInDown " + claseBootstrapAlert).attr('role', 'alert');
                var boton = $("<button>").addClass("close")
                        .attr("type", "button")
                        .attr("data-dismiss", "alert")
                        .attr("aria-label", "Close");
                var span = $("<span>").attr("aria-hidden", "true").html("&times;")
                boton.append(span);
                div.append(boton);
                div.append(mensaje);
                $('div[role="main"]').prepend(div);
            }
        </script>
        <script src="/javascripts/sessionclok.js"></script>

    </head>

    <body>

        <!-- Fixed navbar -->
        <nav class="navbar navbar-empa navbar-fixed-top empa-top"  
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
                    <ul class="nav navbar-nav navbar-right">

                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                MENÚ <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <?php
                                foreach ($arrMenu as $menuItem) {
                                    echo "<li><a href=\"/" . $menuItem['formulario'] . "\">" . $menuItem['nombre'] . "</a></li>";
                                } //For   
                                ?>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span> <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="user-card">
                                    <h4><?php echo $ses->getApellido() . "," . $ses->getNombre(); ?></h4>
                                    <small>USUARIO</small> <strong><?php echo $ses->getUsuario(); ?></strong><br />
                                    <small>ROL</small> <strong><?php echo $ses->getRoleName(); ?></strong><br/><br/>
                                    <a href="/configuracion/getConfiguracionUsuario">ver configuraciones</a>
                                </div>
                            </div>
                        </li>


                        <!--Inicio-->
                        <li>
                            <a href="javascript: window.location.href='/';"class="glyphicon glyphicon-home" aria-hidden="true"></a>
                        </li>
                        
                        <!--Cerrar Sesion-->
                        <li>
                            <a href="javascript: sessionStorage.clear(); window.location.href='/logout';"class="glyphicon glyphicon-off" aria-hidden="true"></a>
                        </li>




                    </ul>
                </div><!--/.nav-collapse -->
                
                
                
            </div>
        </nav>


        <div class="container" role="main">
            <?php if (array_key_exists("text", $message) && $message['text'] != '') { ?>
                <div class="alert <?php echo $alertType; ?> alert-dismissible animated fadeInDown" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php echo $message['text']; ?>
                </div>
            <?php } ?>  
