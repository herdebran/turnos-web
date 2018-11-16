<form class="form-horizontal" action="#" method="POST" accept-charset="utf-8">
    <fieldset>
        <h3>Búsqueda de Usuarios</h3>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="apellido" class="col-xs-4 control-label">Nombre y Apellido: </label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="" maxlength="45" value="<?php echo $params["persona"]["apeynom"]; ?>"
                       autofocus="on" autocomplete="off" readonly>
            </div>
        </div>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="nrodoc" class="col-xs-4 control-label">Doc: </label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="nrodoc" name="nrodoc" value="<?php echo $params["persona"]["tipodocynro"]; ?>" readonly>
            </div>
        </div>


        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="user" class="col-xs-4 control-label">Usuario: </label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="user" name="user" value="<?php echo $params["usuario"]["usuario"]; ?>" readonly>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <a href="/gestion-permisos" id="btnBuscar" type="button" class="btn btn-primary">Volver</a>
            </div>
        </div>
        <input hidden="" type="text" id="idpersona" disabled="" value="<?php echo $params["persona"]["idpersona"]; ?>">
    </fieldset>
</form>
<div class="row">

    <div class="col-md-6">
        <br><small><strong>ROLES</strong></small><br>
        <hr style="margin-top: 0px">
        <?php foreach ($params["roles"] as $key => $rol): ?>
            <?php
            $asignado = false;
            foreach ($params["personaRoles"] as $rasignado) {
                if ($rol["idrol"] == $rasignado["idrol"]) {
                    $asignado = true;
                }
            }
            ?>
            <div class="col-md-4">
                <input class="switchRol" data-idrol="<?php echo $rol["idrol"]; ?>" type="checkbox" <?php echo $asignado ? "checked" : ""; ?>> <small><strong><?php echo $rol["nombre"]; ?></strong></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="col-md-6">
        <br><small><strong>Habilitar / Deshabilitar usuario</strong></small><br>
        <hr style="margin-top: 0px">
        <div class="col-md-12">
            <table class="table">
                <tbody>
                    <tr class="<?php echo $params["usuario"]["estado"] ? 'success' : 'warning'; ?>">
                        <td>HABILITADO</td>
                        <td><?php echo $params["usuario"]["estado"] ? 'SI' : 'NO'; ?></td>
                        <td>
                            <button id='estadousuario' class='btn btn-default btn-xs' 
                                    data-estado='<?php echo $params["usuario"]["estado"] ? 'true' : 'false'; ?>'>
                                <span class='glyphicon glyphicon-refresh' aria-hidden='true'></span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="row">

    <div class="col-md-12">

        <br><small><strong>Permisos</strong></small><br>
        <hr style="margin-top: 0px">
        <table class="table table-condensed" id="tablapermisos">
            <thead>
                <tr>
                    <th>Permiso</th>
                    <th>Rol</th>
                    <th>Asignado</th>
                    <th>Cambiar</th>
                </tr>
            </thead>
            <tbody> 
                <?php foreach ($params["permisos"] as $permiso): ?>
                    <?php
                    $asignado = false;
                    $asignadoxrol = false;
                    $nombrerol = '';
                    foreach ($params["permisosAsignados"] as $pasignado) {
                        if ($permiso["idpermiso"] == $pasignado["idpermiso"]) {
                            $asignado = true;
                            if ($pasignado["rol"] != '') {
                                $asignadoxrol = true;
                                $nombrerol .= " " . $pasignado["rol"];
                            }
                        }
                    }

                    if ($asignado) {
                        echo("<tr class='success'>");
                        echo "<td>" . $permiso["nombre"] . "</td>";
                        if ($asignadoxrol)
                            echo "<td>$nombrerol</td>";
                        else
                            echo "<td>-----</td>";
                        echo "<td>SI</td>";
                        echo "<td>";
                        echo "<button id='switchButton-$key' class='btn btn-default switchPermiso btn-xs' id='fila-$key' data-idpermiso='" . $permiso['idpermiso'] . "' data-estado='true'";
                        if ($asignadoxrol)
                            echo(" disabled class='btn btn-default btn-xs'");
                        else
                            echo(" class='btn btn-default switchPermiso btn-xs'");
                        echo "><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span></button></td>";
                        echo "</tr>";
                    }
                    else {
                        echo("<tr class='warning'>");
                        echo "<td>" . $permiso["nombre"] . "</td>";
                        echo "<td>-----</td>";
                        echo "<td>NO</td>";
                        echo "<td>";
                        echo "<button id='switchButton-$key' class='btn btn-default btn-xs switchPermiso' data-estado='false' data-idpermiso='" . $permiso['idpermiso'] . "' id='fila-$key'";
                        echo "><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span></button></td>";
                        echo "</tr>";
                    }
                    ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>



    <script>

        $().ready(function () {
            $('#tablapermisos').removeAttr('width').DataTable({

                language: {
                    url: "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                },
                paging: false,
                searching: false
            });

            $(".switchRol").click(function () {
                var ok = confirm("desea continuar?");
                if (ok) {
                    var idpersona = $("#idpersona").val();
                    var idrol = $(this).data("idrol");
                    var estado = $(this).prop("checked");
                    if (estado) {
                        estado = 1;
                    } else {
                        estado = 0;
                    }
                    $(location).attr('href', '/permisos/setpersonarol/' + idpersona + '/' + idrol + '/' + estado);
                } else {
                    if ($(this).val() === 'on') {
                        $(this).val(false);
                    } else {
                        $(this).val(true);
                    }
                    $(this).prop("checked", !$(this).prop("checked"))
                }
            });

            $(".switchPermiso").click(function (event) {
                // obtengo el elemento "boton"
                var btn = $(event.target);
                if (btn.is("span")) {
                    btn = $(btn.parent());
                }
                var ok = confirm("desea continuar?");
                if (ok) {
                    var idpersona = $("#idpersona").val();
                    var idrol = btn.data("idpermiso");
                    var estado = btn.data("estado");
                    if (!estado) {
                        estado = 1;
                    } else {
                        estado = 0;
                    }
                    $(location).attr('href', '/permisos/setpersonapermiso/' + idpersona + '/' + idrol + '/' + estado);
                }
            })

            $("#estadousuario").click(function () {
                var ok = confirm("desea continuar?");
                if (ok) {
                    var idpersona = $("#idpersona").val();
                    var estado = $(this).data("estado");
                    if (estado) {
                        estado = 0;
                    } else {
                        estado = 1;
                    }
                    $(location).attr('href', '/permisos/setpersonaestado/' + idpersona + '/' + estado);
                }
            });
        });

    </script>