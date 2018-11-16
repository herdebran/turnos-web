<div class="row">
    <div class="col-md-12">
        <select class="form-control" id="selectInformes">
            <option value="" <?php echo $params["funcion"] == "" ? "selected='selected'" : ""; ?>>Seleccione un Informe</option>
            <?php if ($ses->tienePermiso('', 'Informes - Consulta Manual')) { ?>
                <option value="consultaManual" <?php echo $params["funcion"] == "consultaManual" ? "selected='selected'" : ""; ?>>Consulta Manual</option>
            <?php } ?>
            <?php foreach ($params["menuReportes"] as $key => $value): ?>
                <option  value="<?php echo $value["funcion"]; ?>" <?php echo $params["funcion"] == $value["funcion"] ? "selected='selected'" : ""; ?>><?php echo $value["nombre"]; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-12">

        <?php if (!isset($params["encabezados"])): ?>
            <h3> Sección de Informes dinámicos. Elija un informe</h3>
            <?php if ($ses->tienePermiso('', 'Informes - Consulta Manual')): ?>
                <form action="/reportedinamico/consultaManual" method="POST" id="consultaManual">
                    <div class="form-group">
                        <label for="query">Ingrese su consulta SQL:</label>
                        <textarea class="form-control" name="query" id="query" style="width:100%;height:150px;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-info">consultar</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <h3> Resultados del informe: <?php echo $params["nombre"]; ?></h3>

            <table class="table" id="tablareportes" style="width:100%">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <?php foreach ($params["encabezados"] as $value): ?>
                            <th><?php echo $value; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        var table = $('#tablareportes').DataTable({
            language: {
                url: "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            dom: 'Bfrtip',
            buttons: [
                'excel'
            ],
            ajax: {
                url: "",
                type: "POST",
                data: {datatable: true}
            }
        });

        $("#selectInformes").change(function () {
            window.location.replace("/reportedinamico/ejecutar/" + $(this).val())
        });
    });

</script>