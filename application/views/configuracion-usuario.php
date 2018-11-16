<a href="/" class="btn btn-primary">Volver</a>
<div class="row">
    <div class="col-md-12">
        <h4><?php echo $params["persona"]["nombrecompleto"]; ?></h4>
        <small>USUARIO</small> <strong><?php echo $params["persona"]["usuario"]; ?></strong><br />
        <small>ROL</small> <strong><?php echo $params["persona"]["rolname"]; ?></strong><br/>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <br><small><strong>PERMISOS ASIGNADOS</strong></small><br>
        <table class="table">
            <thead>
                <tr>
                    <th>Permiso</th>
                    <th>Asignado</th>
                </tr>
            </thead>
            <tbody> 
        <?php foreach ($params["permisos"] as $permiso): ?>
                <tr><td><?php echo $permiso[1]; ?></td><td>SI</td></tr>
        <?php endforeach; ?>
                 </tbody>
        </table>
    </div>
    <div class="col-md-6">
        <br><small><strong>CONFIGURACIONES</strong></small><br>
        <table class="table">
            <thead>
                <tr>
                    <th>Parametro</th>
                    <th>Valor</th>
                    <th>Cambiar</th>
                </tr>
            </thead>
            <tbody>   
                <?php foreach ($params["configuraciones"] as $key => $conf): ?>
                    <tr class="<?php  
                    if($conf[1] == 'N'){ echo("warning");}
                    else {echo("success");}
                    //$conf[1] == 'N' ? "default" : 'warning'; 
                    
                    ?>" id="fila-<?php echo $key; ?>">
                        <td><?php echo $conf[0]; ?></td>
                        <td><?php echo $conf[1]; ?></td>
                        <td>
                            <?php  
                            if ($ses->tienePermiso('', 'Gestión de Configuracion - Cambiar')) {
                            ?>
                            <button id="switchButton-<?php echo $key; ?>" <?php echo ($conf[1] == 'N' || $conf[1] == 'Y') ? "" : 'disabled'; ?>
                                    class="btn btn-default switchConf" id="fila-<?php echo $key; ?>">
                                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                            </button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
<script>

    $().ready(function () {
        $(".switchConf").click(function (event) {
            var elem = $(event.target);
            if (elem.is("span")) {
                elem = $(elem.parent());
            }
            var cambio = false;
            var numfila = elem.attr('id').split('-')[1];
            var fila = $("#fila-" + numfila);
            var valor = $(fila.find('td')[1]);
            var parametro = $(fila.find('td')[0]);
            if (valor.text() === 'N' || valor.text() === 'Y') {
                cambio = true;
            }
            if (cambio) {
                var params = {
                    parametro: parametro.text()
                }
                if (valor.text() === "Y") {
                    params.valor = "N";
                } else {
                    params.valor = "Y";
                }
                $.ajax({
                    url: "/configuracion/ajaxswitchconf",
                    data: {params: params},
                    dataType: 'JSON',
                    type: 'POST',
                    success: function (data, textStatus, jqXHR) {
                        if (data.ok) {
                            if (valor.text() === "Y") {
                                fila.removeClass("success");
                                valor.text("N");
                                fila.addClass("warning");
                            } else {
                                fila.removeClass("warning");
                                valor.text("Y");
                                fila.addClass("success");
                            }
                            addGlobalMessage('alert-success', data.message)
                        } else {
                            addGlobalMessage('alert-danger', data.message)
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert("Ah ocurrido un error grave en el sistema, por favor comuniquese con el administrador.");
                        addMessage('alert-success', "Ah ocurrido un error grave en el sistema, por favor comuniquese con el administrador. Texto: "+textStatus)
                    }
                });
            } else {
                alert("Estas intentando cmabiar un valor prohibido. Esto es peligroso, no lo hagas mas.");
                elem.attr('disabled', true)
            }
        });
    });

</script>



