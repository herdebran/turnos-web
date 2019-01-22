<form class="form-horizontal" action="#" id="form-busqueda"
      method="POST" accept-charset="utf-8">
    <fieldset>
        <h3>ABMs Varios</h3>
        <p>Desde esta pantalla se podran agregar, quitar o eliminar datos utiles usados en el sistema</p>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label><input type="radio" id="especialidades" name="opttipoabm" checked>Especialidades</label>
        </div>
        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label><input type="radio" id="estudios" name="opttipoabm">Estudios</label>
        </div>
        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label><input type="radio" id="obrassociales" name="opttipoabm">Obras Sociales</label>
        </div>

        
        <fieldset>
            <h3>Resultados</h3>
            <div class="panel panel-default">
                <div class="panel-body">
                    <table class="table table-striped table-hover table-condensed" id="table-resultados" width="100%">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Descripcion</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </fieldset>        
        
        
        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="nueva" class="col-xs-4 control-label">Nueva</label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="nueva" name="nueva" placeholder="" maxlength="45" value="" autocomplete="off">
                <button id="btnAgregar" type="button" class="btn btn-primary">Agregar</button>
            </div>
        </div>

        
    </fieldset>
</form>


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
                                data.id = $("#id").val();
                                data.descripcion = $("#descripcion").val();
                                data.activo = $("#activo").val();
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
                                            <a type="button" class="btn btn-default btn-xs" href="resetear-pass/' + row["idpersona"] + '" title="Resetear Clave"><i class="glyphicon glyphicon-erase" ></i></a>';
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
