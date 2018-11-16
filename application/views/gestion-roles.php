<form class="form-horizontal" action="#" id="form-busqueda"
      method="POST" accept-charset="utf-8">
    <fieldset>
        <h3>Edición de Roles</h3>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="tipdoc" class="col-xs-4 control-label">Rol</label>
            <div class="col-xs-8">
                <select class="form-control" id="changerol" name="changerol">
                    <option value="0">SELECCIONAR</option>
                    <?php foreach ($params['roles'] as $rol) { ?>
                        <option <?php echo $params['rolselected'] == $rol['idrol'] ? "selected" : ""; ?> value="<?php echo $rol['idrol']; ?>"><?php echo $rol['nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

            <div class="form-group">
                <div class="col-xs-12">
                    <a href="/gestion-permisos" id="btnBuscar" type="button" class="btn btn-primary">Volver</a>
                </div>
            </div>
    </fieldset>
</form>

<table class="table table-condensed" id="tablapermisos">
    <thead>
        <tr>
            <th>Permiso</th>
            <th>Asignado</th>
            <th>Cambiar</th>
        </tr>
    </thead>
    <tbody> 
        <?php foreach ($params["permisos"] as $permiso): ?>

            <?php
            $estado = false;
            foreach ($params["permisosactuales"] as $permi) {
                if (($permiso["idpermiso"] == $permi["idpermiso"]) && ($params["rolselected"] == $permi["idrol"])) {
                    $estado = true;
                }
            }
            ?>
            <?php if ($estado): ?>
                <tr class='success'>
                    <td><?php echo $permiso["nombre"] ?></td>
                    <td>SI</td>
                    <td>
                        <button class="btn btn-default btn-xs switchPermiso" 
                                data-estado="true" 
                                data-idpermiso="<?php echo $permiso["idpermiso"] ?>" >
                            <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>                
            <?php else: ?>
                <tr class='warning'>
                    <td><?php echo $permiso["nombre"] ?></td>
                    <td>NO</td>
                    <td>
                        <button class='btn btn-default btn-xs switchPermiso' 
                                data-estado='false' 
                                data-idpermiso="<?php echo $permiso["idpermiso"] ?>">
                            <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>
            <?php endif; ?>

        <?php endforeach; ?>
    </tbody>
</table>


<div class="modal" id="loadingModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cargando... Por favor espere.</h4>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>
            </div>
        </div>
    </div>
</div>

</body>

<script>
    $().ready(function () {
        if ($("#changerol").val() == 0) {
            $(".switchPermiso").each(function (index) {
                $(this).attr("disabled", true);
            })
        }
        $('#tablapermisos').removeAttr('width').DataTable({

            language: {
                url: "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            paging: false
        });

        $("#changerol").change(function () {
            $(location).attr('href', '/permisos/gestionroles/' + $(this).val());
        });

        $(".switchPermiso").click(function (event) {
            var ok = confirm("seguro que desea modificar este permiso ?");
            if (ok) {
                var btn = $(event.target);
                if (btn.is("span")) {
                    btn = $(btn.parent());
                }
                if (!btn.data("estado")) {
                    var estado = 1;
                } else {
                    var estado = 0;
                }
                var idpermiso = btn.data("idpermiso");
                $(location).attr('href', '/permisos/setpermisorol/' + $("#changerol").val() + '/' + idpermiso + '/' + estado);
            }
        });
    });

</script>
