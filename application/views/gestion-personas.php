
<style>
    /* The Modal (background) */

    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        padding-top: 100px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */

    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    /* The Close Button */

    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
    input {
        text-transform: uppercase;
    }

    .small,
    small { 
        font-size: 82% !important;
    }
</style>


<form class="form-horizontal" action="#" id="form-busqueda"
      method="POST" accept-charset="utf-8">
    <fieldset>
        <h3>Búsqueda de Personas</h3>
        <!--<p>Complete al menos un campo para realizar la búsqueda</p>-->
    

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="tipo" class="col-xs-4 control-label">Tipo</label>
            <div class="col-xs-8">
                <select class="form-control" id="tipo" name="tipo">
                    <option value="0">SELECCIONAR</option>
                    <?php foreach ($params['viewDataTipo'] as $tipo) { ?>
                        <option value="<?php echo $tipo['idtipopersona']; ?>"><?php echo $tipo['descripcion']; ?></option>
                    <?php } ?>  
                </select>
            </div>
        </div>

        <div class="form-group col-xs-12 col-sm-6 col-lg-4">
            <label for="razonsocialapellido" class="col-xs-4 control-label">Razon Social</label>
            <div class="col-xs-8">
                <input type="text" class="form-control" id="razonsocialapellido" name="razonsocialapellido" placeholder="" value="">
            </div>
        </div>

        <div class="form-group">
            <button id="btnBuscar" type="submit" class="btn btn-primary">Buscar</button>
            <button id="btnReset" type="button" class="btn btn-default">Limpiar</button>
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
                        <th>Tipo</th>
                        <th>Tipo y Nro Doc.</th>
                        <th>Razon Social</th>
                        <th>Telefono 1</th>
                        <th>Telefono 2</th>
                        <th>Domicilio</th>
                        <th>Mail</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</fieldset>

<fieldset>
    <div class="form-group">
        <div class="col-xs-12">
            <a href="/crear-persona" class="btn btn-success pull-right">Crear Persona</a>
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
// Verifica si existe una busqueda previa y si la hay la carga en el formulario.
        if (sessionStorage.getItem('busqueda-entidades') !== null) {
            let oldSearch = JSON.parse(sessionStorage.getItem('busqueda-entidades'));
            $("#tipo").val(oldSearch.tipo);
            $("#razonsocialapellido").val(oldSearch.razonsocialapellido);
        }
        // DATATABLE------------------------------------------------------------------------//
        // Variable global donde se aloja el objeto datatable.
        var table;
        var firstLoad = true;
        
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
                            url: "/persona/personasConFiltro",
                            method: "post",
                            data: function (data) {
                                data = {};
                                data.idtipopersona = $("#tipo").val();
                                data.razonsocialapellido = $("#razonsocialapellido").val();
                                sessionStorage.setItem("busqueda-entidades", JSON.stringify(data));
                                return {'filtros': data};
                            },
                            complete: function () {
                                $('#loadingModal').hide();
                                $("#btnBuscar").attr("disabled", false);
                            }
                        },
                        columns: [
                            {data: 'tipopersona'},
                            {data: 'tipodocynro'},
                            {data: 'denominacion'},
                            {data: 'telefono1'},
                            {data: 'telefono2'},
                            {data: 'domicilio'},
                            {data: 'email'},
                            {data: 'acciones',
                                width: "5%",
                                render: function (data, type, row, meta) {
                                    return '<a type="button" class="btn btn-default btn-xs" href="crear-persona/' + row["idpersona"] + '" title="Editar..."><i class="glyphicon glyphicon-pencil" ></i></a>';
                                }
                            }
                        ]
                    });
   
        }
// Form functions ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $("#form-busqueda").submit(function (e) {
            e.preventDefault();
        });
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
        // Limpia el formulario de busqueda
        function limpiarForm() {
            $("#tipo").val(0);
            $("#razonsocialapellido").val("");
            sessionStorage.removeItem('busqueda-entidades');
        }

        $("#btnReset").click(function () {
            limpiarForm();
        });


    });
</script>
