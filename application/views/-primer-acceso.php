<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo($this->POROTO->Config['empresa_descripcion']);?></title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <script src="/javascripts/jquery-3.1.1.js"></script>

    <style>
      body {
        background-color: rgb(234, 234, 212);
        padding-top: 40px;
        padding-bottom: 40px;
        color: #111;
        font-size: 12px;
      }
      
      h1 { font-weight: bold;margin-bottom: 0;color: <?php echo $this->POROTO->Config['empresa_color_titulos'];?>;}
      h2 { margin-top: 0; font-size: 24px; color: <?php echo $this->POROTO->Config['empresa_color_titulos'];?>; }
      h3 { margin: 20px 0; font-size: 24px; color: #e7d326; font-weight: bold; text-align: center; }
            
      
      .form-signin {
        max-width: 450px;
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
        font-size: 16px;
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
    </style>
  </head>

  <body>
    <div class="container">
      <div class="page-header text-center">
        <h1><?php echo($this->POROTO->Config['empresa_descripcion']);?></h1>
      </div>

      <form class="form-signin" id="login-form" name="login" action="" method="POST" accept-charset="utf-8">
        <h3>¿DESEA MODIFICAR SU CONTRASEÑA?</h3>
        <label for="password" class="">Nueva Contraseña</label>
        <input type="password" id="login-form-pwd" name="password" class="form-control" placeholder="Nueva Contraseña" autofocus required>
        <input type="hidden" name="noModify" id="noModify" value="0" />

            <p><?php echo $passwordExplanied; ?></p>

        <?php if (isset($validationErrors)) { ?>
        <div class="alert alert-danger" role="alert"><?php echo $validationErrors; ?></div>
        <?php } ?>

        <div class="col-xs-6">
          <button class="btn btn-primary btn-block" type="submit">Modificar Contraseña</button>
        </div>
        <div class="col-xs-6">
          <input type="button" class="btn btn-default btn-block" id="btnNoModify" value="No Modificar Contraseña" />
        </div>
        <div class="clearfix"></div>
      </form>
    </div> <!-- /container -->


  </body>
  <script type="text/javascript">
    $("#btnNoModify").click(function() {
      $("#noModify").val("noModify");
      $("#login-form").submit();
    });
  </script>
</html>


