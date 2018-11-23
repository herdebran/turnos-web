<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo($this->POROTO->Config['empresa_descripcion'] . ' - ' . $this->POROTO->Config['titulo_sistema']);?></title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <script src="/javascripts/jquery-3.1.1.js"></script>
        <style>
            body {
                background-color: #efededf7;
                padding-top: 40px;
                padding-bottom: 40px;
                color: #111;
                font-size: 10px;
                height: 100%;
            }
            html { height: 100%; }

            
            h1 { font-weight: bold;margin-bottom: 0;color: <?php echo $this->POROTO->Config['empresa_color_titulos'];?>;}
            h2 { margin-top: 0; font-size: 24px; color: <?php echo $this->POROTO->Config['empresa_color_titulos'];?>; }
            h3 { margin: 20px 0; font-size: 24px; color: #e7d326; font-weight: bold; text-align: center; }
            
            .form-signin {
                max-width: 350px;
                padding: 15px;
                margin: 0 auto;
                background-color: #fff;
                border-radius: 20px;
                border: 1px solid #ccc ;
            }

            .form-signin .form-control {
                position: relative;
                height: auto;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                padding: 10px;
                font-size: 12px;
            }
            .form-signin .form-control:focus {
                z-index: 2;
            }
            .form-signin input[type="text"] {
                margin-bottom: 20px;
                border-bottom-right-radius: 0;
                border-bottom-left-radius: 0;
            }
            .form-signin input[type="password"] {
                margin-bottom: 20px;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
            }
            .footer {
                margin-top: 30px;
                font-size: 11px;
                width: 100%;
                text-align: center;
            }
            .container {
                min-height: 94%;        
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="page-header text-center">
                <h1><?php echo($this->POROTO->Config['empresa_descripcion']);?></h1>
                <h2><?php echo($this->POROTO->Config['titulo_sistema']);?></h2>
            </div>

            <form class="form-signin" id="loginform" name="login" action="" method="POST" accept-charset="utf-8">
                <h3>Iniciar Sesion</h3>
                <label for="username" class="">Usuario</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ingrese su usuario" required autofocus autocomplete="off" />
                <label for="password" class="">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required/>
                <div><a href="#" id="olvidelink">Olvide mi contraseña</a></div><br>
                <input type="text" id="olvide" name="olvide" class="form-control hidden" value=""/>

                <?php if (isset($loginErrorMessage)) { ?>
                    <div class="alert alert-danger" role="alert"><?php echo $loginErrorMessage; ?></div>
                <?php } ?>

                <button class="btn btn-lg btn-primary btn-block" type="submit">Ingresar</button>
            </form>
        </div> <!-- /container -->

        <div class="footer">
            diseñado y desarrollado por <a href='mailto:hernanmarzullo@gmail.com'>Hernan Marzullo</a>
        </div>

    </body>

</html>
<script type="text/javascript">
    sessionStorage.clear();
    
    $().ready(function () {
        $("#olvide").val(""); //Blanqueo
        $("#olvidelink").click(function () {
            if($("#username").val()!=""){
            $("#olvide").val("si");
            $("#loginform").submit();
        }else{
            alert("Debe ingresar su usuario para recuperar la contraseña.");
        }
        });
    })

</script>
