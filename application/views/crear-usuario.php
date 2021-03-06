<?php
if (isset($params['usuario'])){
    $usuario =  $params['usuario'];
    $estiloCampoNombre=($usuario['usuario']!= null)?"disabled":"";
} else {
    $usuario =  null;   
    $estiloCampoNombre='';
}

?>
<style>
    .form-group.required .control-label:after { 
        content:"*";
        color:red;
    }
    input, textarea { text-transform: uppercase; }
</style>
<?php if (isset($params['validationErrors']) && count($params['validationErrors']) > 0) { ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($params['validationErrors'] as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php } ?>

<body onLoad = "enfocar()">

<form class="form-horizontal" action="/guardar-usuario" method="POST" accept-charset="utf-8">  
    <h3><?php echo $params['pageTitle']; ?></h3>

    <input type="number" hidden id="idpersona" name="idpersona" value="<?php echo $params['usuario']['idpersona']; ?>" />

    
    <div class="panel-group" id="accordion-datos-personales" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-datos-personales" href="#collapse-datos-personales" aria-expanded="true" aria-controls="collapseOne">
                        Datos del usuario para <?php echo strtoupper($params["persona"]["apeynom"]); ?>
                    </a>
                </h4>
            </div>
            <div id="collapse-datos-personales" class="panel-collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <fieldset>
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-6 required <?php if (array_key_exists('usuario', $params['validationErrors'])) echo "has-error"; ?>">
                                <label for="usuario" class="col-md-4 control-label">Usuario</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="" maxlength="45" value="<?php echo $params['usuario']['usuario']; ?>" <?php echo $estiloCampoNombre; ?> autocomplete="off" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 col-lg-6 required <?php if (array_key_exists('password', $params['validationErrors'])) echo "has-error"; ?>">
                                <label for="password" class="col-md-4 control-label">Contraseña</label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="" maxlength="45" value="" autocomplete="off" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 col-lg-6 required <?php if (array_key_exists('repitepassword', $params['validationErrors'])) echo "has-error"; ?>">
                                <label for="repitepassword" class="col-md-4 control-label">Repita Contraseña</label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" id="repitepassword" name="repitepassword" placeholder="" maxlength="45" value="" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                    </fieldset>                
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class=" col-xs-12">
            <button type="submit" class="btn btn-primary"><?php echo $params['btnText']; ?></button>
            <a href="/gestion-personas" class="btn btn-default">Cancelar</a>
        </div>
    </div>
</form>
</body>

<script>
function enfocar()
{
    if ($("#usuario").val() == '') {
        usuario.focus();
    } else {
        password.focus();
    }
}
</script>