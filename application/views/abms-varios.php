<fieldset>
    <h3><?php echo $params['pageTitle'];?></h3>
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

<form class="form-horizontal" action="#" id="form-busqueda"
      method="POST" accept-charset="utf-8">
    <fieldset>
        
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
        cargarDataTable();
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
                            url: "/abmsvarios/listarEspecialidades",
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
                            }
                        },
                        columns: [
                            {data: 'id'},
                            {data: 'descripcion'},
                            {data: 'activo'},
                            {data: 'acciones',
                                width: "5%",
                                render: function (data, type, row, meta) {
                                    /**return '<a type="button" class="btn btn-default btn-xs" href="permisos/detalle/' + row["id"] + '" title="desactivar"><i class="glyphicon glyphicon-remove" ></i></a>';**/
                                    return '<button type="button" class="btn btn-default btn-xs desactivar" data-desactivar="' + row["id"] + '" title="Desactivar"><i class="glyphicon glyphicon-remove" ></i></button>';
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
            var ok = confirm("¿Está seguro?");
            if (ok) {
                var id = btn.data("desactivar");
                /**$(location).attr('href', '/permisos/resetpass/' + idpersona); */
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
            $("#nueva").val("");
        }
        /**
         * 
         * @returns void
         * Llama a la funcion "limpiarForm()"
         */
        $("#btnReset").click(function () {
            limpiarForm();
        });
        
        $("#btnAgregar").click(function () {
            var ok = confirm("¿Está seguro?");
            if (ok) {
                var nueva = $("#nueva").val();
            
            
            var row = '<?php echo $params['tipo'];?>'
            if (row=='esp')    
                $(location).attr('href', '/abmsvarios/altanuevaespecialidad/' + nueva );
            }
        });
    });
</script>
