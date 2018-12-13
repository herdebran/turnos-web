<form class="form-horizontal" action="#" id="form-busqueda"
      method="POST" accept-charset="utf-8">
    <fieldset>
        <h3>Búsqueda de Usuarios</h3>
        <p>Complete al menos un campo para realizar la búsqueda</p>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="apellido" class="col-xs-4 control-label">Apellido</label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="" maxlength="45" value="" autofocus="on"
                       autocomplete="off">
            </div>
        </div>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="nombre" class="col-xs-4 control-label">Nombre</label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="" maxlength="45" value="" autocomplete="off">
            </div>
        </div>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="tipdoc" class="col-xs-4 control-label">Tipo Doc.</label>
            <div class="col-xs-8">
                <select class="form-control" id="tipdoc" name="tipdoc">
                    <option value="0">SELECCIONAR</option>
                    <?php foreach ($params['viewDataTipoDocumento'] as $tipdoc) { ?>
                        <option value="<?php echo $tipdoc['id']; ?>"><?php echo $tipdoc['descripcion']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="nrodoc" class="col-xs-4 control-label">Nro.Doc.</label>
            <div class="col-xs-8">
                <input type="number" min="1" max="99999999" class="form-control" id="nrodoc" name="nrodoc" placeholder="" value="">
            </div>
        </div>


        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="rol" class="col-xs-4 control-label">Rol</label>
            <div class="col-xs-8">
                <select class="form-control" id="rol" name="rol">
                    <option value="0">SELECCIONAR</option>
                    <?php foreach ($params["roles"] as $key => $value): ?>
                        <option value="<?php echo $value["idrol"]; ?>"><?php echo $value["nombre"]; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="rol" class="col-xs-4 control-label">Permiso</label>
            <div class="col-xs-8">
                <select class="form-control" id="permiso" name="permiso">
                    <option value="0">SELECCIONAR</option>
                    <?php foreach ($params["permisos"] as $key => $value): ?>
                        <option value="<?php echo $value["idpermiso"]; ?>"><?php echo $value["nombre"]; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-xs-12">
                <button id="btnBuscar" type="button" class="btn btn-primary">Buscar</button>
                <a href="/crear-persona" type="button" class="btn btn-success">Crear Persona</a>
                <a href="/permisos/gestionroles" type="button" class="btn btn-success">Gestion Roles</a>
            </div>
        </div>
    </fieldset>
</form>
<fieldset>
    <h3>Resultados</h3>
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-striped table-hover table-condensed" id="table-resultados" width="100%">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre y Apellido</th>
                        <th>Habilitado</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</fieldset>

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

        /**
         * Verifica si existe una busqueda previa y si la hay la carga en el formulario.
         */
        if (sessionStorage.getItem('busqueda') !== null) {
            let oldSearch = JSON.parse(sessionStorage.getItem('busqueda'));
            $("#nombre").val(oldSearch.nombre);
            $("#apellido").val(oldSearch.apellido);
            $("#tipdoc").val(oldSearch.tipdoc);
            $("#nrodoc").val(oldSearch.nrodoc);
            $("#rol").val(oldSearch.rol);
            $("#permiso").val(oldSearch.permiso);
        }

        // DATATABLE ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        /**
         * 
         * @type Boolean
         * Determina si hay que "crear" el objeto datatables.
         * Caso contrario, hay que recargar el llamado ajax.
         */
        var firstLoad = true;

        /**
         * 
         * @type DataTables
         * Variable global donde se aloja el objeto datatable.
         */
        var table;

        /**
         * 
         * @returns void
         * Inicializa el objeto Datatable y lo asigna en "table"
         */
        function cargarDataTable() {
            table = $('#table-resultados')
                    .removeAttr('width')
                    .DataTable({
                        language: {
                            url: "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                        },
                        processing: false,
                        serverSide: false,
                        scrollX: true,
                        ajax: {
                            url: "/permisos/personasConFiltro",
                            method: "post",
                            data: function (data) {
                                data = {};
                                data.nombre = $("#nombre").val();
                                data.apellido = $("#apellido").val();
                                data.tipdoc = $("#tipdoc").val();
                                data.nrodoc = $("#nrodoc").val();
                                data.rol = $("#rol").val();
                                data.permiso = $("#permiso").val();
                                sessionStorage.setItem("busqueda", JSON.stringify(data));
                                return {'filtros': data};
                            },
                            complete: function () {
                                $('#loadingModal').hide();
                                $("#btnBuscar").attr("disabled", false);
                            }
                        },
                        columns: [
                            {data: 'tipodocynro',
                                width: "10%"
                            },
                            {data: 'apeynom'},
                            {data: 'estado'},
                            {data: 'rol'},
                            {data: 'acciones',
                                width: "5%",
                                render: function (data, type, row, meta) {
                                    return '<a type="button" class="btn btn-default btn-xs" href="permisos/detalle/' + row["idpersona"] + '" title="Detalle de usuario"><i class="glyphicon glyphicon-cog" ></i></a>\n\
                                            <button type="button" class="btn btn-default btn-xs resetpass" data-persona="' + row["idpersona"] + '" title="Resetear Clave"><i class="glyphicon glyphicon-erase" ></i></button>';
                                }
                            }
                        ]
                    });
        }

        $("#table-resultados tbody").on("click", "button", function (event) {
            var btn = $(event.target);
            if (btn.is("span")) {
                btn = $(btn.parent());
            }
            var ok = confirm("¿Está seguro que desea resetear la contraseña del usuario? id:" + btn.data("persona"));
            if (ok) {
                var idpersona = btn.data("persona");
                $(location).attr('href', '/permisos/resetpass/' + idpersona);
            }
        });
        // Form functions ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        /**
         * 
         * @event
         * Evita que el formulario dispare la redireccion en caso de enviarse.
         */
        $("#form-busqueda").submit(function (e) {
            e.preventDefault();
        });

        /**
         * 
         * @event
         * Evita que el formulario dispare la redireccion en caso de enviarse.
         */
        $("#btnBuscar").click(function () {
            $('#loadingModal').show();
            $(this).attr("disabled", true);
            if (firstLoad) {
                cargarDataTable();
                firstLoad = false;
            } else {
                table.ajax.reload();
            }
        });
        /**
         * 
         * @returns void
         * Limpia el formulario de busqueda
         */
        function limpiarForm() {
            $("#nombre").val("");
            $("#apellido").val("");
            $("#tipdoc").val(0);
            $("#nrodoc").val("");
            $("#rol").val(0);
            $("#permiso").val(0);
            sessionStorage.removeItem('busqueda');
        }
        /**
         * 
         * @returns void
         * Llama a la funcion "limpiarForm()"
         */
        $("#btnReset").click(function () {
            limpiarForm();
        });
    });
</script>
