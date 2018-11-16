<?php
$persona = isset($params['persona']) ? $params['persona'] : null;

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

<form class="form-horizontal" action="/guardar-persona" method="POST" accept-charset="utf-8">  
    <h3><?php echo $params['pageTitle']; ?></h3>

    <input type="number" hidden id="idpersona" name="idpersona" value="<?php echo $persona['idpersona']; ?>" />

    <div class="panel-group" id="accordion-datos-personales" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-datos-personales" href="#collapse-datos-personales" aria-expanded="true" aria-controls="collapseOne">
                        Datos Personales
                    </a>
                </h4>
            </div>
            <div id="collapse-datos-personales" class="panel-collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <fieldset>
                        <div class="form-group col-md-6 col-lg-6 required <?php if (array_key_exists('idtipopersona', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="tipo" class="col-md-4 control-label">Tipo</label>
                            <div class="col-md-8">
                                <select class="form-control" id="idtipopersona" name="idtipopersona">
                                    <option value="0">SELECCIONAR</option>
                                    <?php foreach ($params['tipopersona'] as $tipo) { ?>
                                        <option value="<?php echo $tipo['idtipopersona']; ?>" <?php if ($persona['idtipopersona'] == $tipo['idtipopersona']) echo "selected"; ?>><?php echo $tipo['descripcion']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 required <?php if (array_key_exists('apellido', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="apellido" class="col-md-4 control-label">Apellido</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="" maxlength="45" value="<?php echo $persona['apellido']; ?>" <?php /* solo-valider="" dacoines-post required */ ?> autocomplete="off" />
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 required <?php if (array_key_exists('nombre', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="nombre" class="col-md-4 control-label">Nombre</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="" maxlength="45" value="<?php echo $persona['nombre']; ?>" <?php /* solo-valider="" dacoines-post required */ ?> autocomplete="off" />
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6">
                            <label for="tipodoc" class="col-md-4  col-lg-4 control-label">Documento</label>
                            <div class="col-md-3  col-lg-3 required <?php if (array_key_exists('tipodoc', $params['validationErrors'])) echo "has-error"; ?>">
                                <select class="form-control" id="tipodoc" name="tipodoc">
                                    <option value="0">SELECCIONAR</option>
                                    <?php foreach ($params['tipodoc'] as $tipodoc) { ?>
                                        <option value="<?php echo $tipodoc['id']; ?>" <?php if ($persona['tipodoc'] == $tipodoc['id']) echo "selected"; ?>><?php echo $tipodoc['descripcion']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-5 col-lg-5 required <?php if (array_key_exists('documentonro', $params['validationErrors'])) echo "has-error"; ?>">
                                <input type="number" class="form-control" id="documentonro" name="documentonro" value="<?php echo $persona['documentonro']; ?>" autocomplete="off" >
                            </div>
                        </div>



                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('razonsocial', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="razonsocial" class="col-md-4 control-label">Razón Social</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="razonsocial" name="razonsocial" placeholder="" maxlength="50" value="<?php echo $persona['razonsocial']; ?>" <?php /* solo-valider="" dacoines-post required */ ?> autocomplete="off" />
                            </div>
                        </div>

                    </fieldset>                
                </div>
            </div>
        </div>
    </div>

    <div class="panel-group" id="accordion-domicilio" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-domicilio" href="#collapse-domicilio" aria-expanded="true" aria-controls="collapseOne">
                        Domicilio
                    </a>
                </h4>
            </div>
            <div id="collapse-domicilio" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">

                    <fieldset>
                        <input type="hidden" class="form-control" id="iddomicilio" name="iddomicilio" value="<?php if ($persona['domicilio']['iddomicilio'] != null) echo $persona['domicilio']['iddomicilio']; ?>" >

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('calle', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="calle" class="col-md-4 control-label">Calle</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="calle" name="calle" placeholder=""  maxlength="45" value="<?php echo $persona['domicilio']['calle']; ?>" autocomplete="off" >
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('numero', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="numero" class="col-md-4 control-label">Número</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="numero" name="numero" placeholder="" maxlength="5" value="<?php echo $persona['domicilio']['nro']; ?>" autocomplete="off" >
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('piso', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="piso" class="col-md-4 control-label">Piso</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="piso" name="piso" placeholder="" maxlength="5" value="<?php echo $persona['domicilio']['piso']; ?>" autocomplete="off" >
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('depto', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="depto" class="col-md-4 control-label">Depto</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="depto" name="depto" placeholder="" maxlength="5" value="<?php echo $persona['domicilio']['depto']; ?>" autocomplete="off" >
                            </div>
                        </div>


                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('provincia', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="provincia" class="col-md-4 control-label">Provincia</label>
                            <div class="col-md-8">
                                <select class="form-control" id="provincia" name="provincia" >
                                    <option value="0">SELECCIONAR</option>
                                    <?php foreach ($params['provincias'] as $prov) { ?>
                                        <option value="<?php echo $prov['idprovincia']; ?>" <?php if ($persona['domicilio']['provincia'] == $prov['idprovincia']) echo "selected"; ?>>
                                            <?php echo $prov['descripcion']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6  <?php if (array_key_exists('municipio', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="municipio" class="col-md-4 control-label">Municipio</label>
                            <div class="col-md-8">
                                <select class="form-control" id="municipio" name="municipio" >
                                    <option value="0">SELECCIONAR</option>
                                    <?php foreach ($params['municipiosD'] as $municipio) { ?>
                                        <option value="<?php echo $municipio['idmunicipio']; ?>" <?php if ($persona['domicilio']['municipio'] == $municipio['idmunicipio']) echo "selected"; ?>>
                                            <?php echo $municipio['descripcion']; ?>
                                        </option>
                                    <?php } ?></select>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6  <?php if (array_key_exists('localidad', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="localidad" class="col-md-4 control-label">Localidad</label>
                            <div class="col-md-8">
                                <select class="form-control" id="localidad" name="localidad" >
                                    <option value="0">SELECCIONAR</option>
                                    <?php foreach ($params['localidadesD'] as $localidad) { ?>
                                        <option value="<?php echo $localidad['idlocalidad']; ?>" <?php if ($persona['domicilio']['idlocalidad'] == $localidad['idlocalidad']) echo "selected"; ?>><?php echo $localidad['descripcion']; ?></option>
                                    <?php } ?></select>
                            </div>
                        </div>    
                    </fieldset>

                </div>
            </div>
        </div>
    </div>

    <div class="panel-group" id="accordion-contacto" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-contacto"
                       href="#collapse-datos-contacto" aria-expanded="true" aria-controls="collapseOne">
                        Contacto
                    </a>
                </h4>
            </div>
            <div id="collapse-datos-contacto" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">

                    <fieldset>
                        <div class="form-group col-md-6 col-lg-6  <?php if (array_key_exists('telefono1', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="telefono1" class="col-md-4 control-label">Telefono 1</label>
                            <div class="col-md-8">
                                <input type="tel" class="form-control" id="telefono1" name="telefono1" placeholder="" value="<?php echo $persona['telefono1']; ?>" autocomplete="off" >
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('telefono2', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="telefono2" class="col-md-4 control-label">Telefono 2</label>
                            <div class="col-md-8">
                                <input type="tel" class="form-control" id="telefono2" name="telefono2" placeholder="" value="<?php echo $persona['telefono2']; ?>" autocomplete="off" >
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('telefono3', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="telefono3" class="col-md-4 control-label">Telefono 3</label>
                            <div class="col-md-8">
                                <input type="tel" class="form-control" id="telefono3" name="telefono3" placeholder="" value="<?php echo $persona['telefono3']; ?>" autocomplete="off" >
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6  <?php if (array_key_exists('email', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="email" class="col-md-4 control-label">Email</label>
                            <div class="col-md-8">
                                <input type="email" class="form-control" id="email" name="email" maxlength="50" placeholder="" value="<?php echo $persona['email']; ?>" autocomplete="off" />
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-lg-6 <?php if (array_key_exists('web', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="web" class="col-md-4 control-label">Web</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="web" name="web" value="<?php echo $persona['web']; ?>" autocomplete="off" >
                            </div>
                        </div> 

                    </fieldset>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-group" id="accordion-otros-datos" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-otros-datos" href="#collapse-otros-datos" aria-expanded="true" aria-controls="collapseOne">
                        Otros Datos
                    </a>
                </h4>
            </div>
            <div id="collapse-otros-datos" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">

                    <fieldset>

                        <div class="form-group col-md-6 col-lg-6  <?php if (array_key_exists('observaciones', $params['validationErrors'])) echo "has-error"; ?>">
                            <label for="observaciones" class="col-md-4 control-label">Observaciones</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="observaciones" name="observaciones" maxlength="200" value="<?php echo $persona['observaciones']; ?>" autocomplete="off" >
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

<script type="text/javascript">
    $().ready(function () {

        // Seteo y carga de domicilio

        $("#provincia").change(function () { //OK
            $.ajax({url: "/ajaxmunicipios/" + $(this).val(), dataType: 'json', success: function (response) {
                    $('#municipio').empty();
                    $('#localidad').empty();
                    $('#municipio').append($('<option>').text('SELECCIONAR').attr('value', 0));
                    $('#localidad').append($('<option>').text('SELECCIONAR').attr('value', 0));
                    $encontre = false;
                    $.each(response, function (i, value) {
                        $('#municipio').append($('<option>').text(value.descripcion).attr('value', value.idmunicipio));
                        if (sessionStorage.getItem("crearentidad-municipio") == value.idmunicipio) {
                            $encontre = true;
                            $('#municipio').val(sessionStorage.getItem("crearentidad-municipio"));
                        }
                    });
                    //Si no se encontreo variable seleccionada o bien no hay ninguna dejo seleccionado el SELECCIONAR
                    if (!$encontre)
                        $('#municipio').val(0);
                    $("#municipio").trigger("change");
                }});
        });

        $("#municipio").change(function () { //OK
            $.ajax({url: "/ajaxlocalidades/" + $(this).val(), dataType: 'json',
                success: function (response) {
                    $('#localidad').empty();
                    $('#localidad').append($('<option>').text('SELECCIONAR').attr('value', 0));
                    $encontre = false;
                    $.each(response, function (i, value) {
                        $('#localidad').append($('<option>').text(value.descripcion).attr('value', value.idlocalidad));
                        if (sessionStorage.getItem("crearentidad-localidad") == value.idlocalidad) {
                            $encontre = true;
                            $('#localidad').val(sessionStorage.getItem("crearentidad-localidad"));
                        }
                    }); //each
                    //Si no se encontreo variable seleccionada o bien no hay ninguna dejo seleccionado el SELECCIONAR
                    if (!$encontre) {
                        $('#localidad').val(0);
                    }
                    $("#localidad").trigger("change");
                }});
        });

    });

</script>