<style>
    /*.mce-widget{*/
    /*display: none;*/
    /*}*/
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
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
</style>
<?php echo $saludo; ?>
<?php if ($editor): ?>
    <hr/>
    <h3>Novedades
        <button class="btn btn-info pull-right" id="nueva">Nueva Novedad</button>
    </h3>
<?php endif; ?>
<br>

<?php if (empty($novedades)) : ?>
    <h3>No hay novedades</h3>
<?php else: ?>
    <?php foreach ($novedades as $novedad) : ?>
        <div class="row">
            <div class="col-md-12">
                <div class="thumbnail" id="novedad_<?php echo $novedad['idnovedad']; ?>">
                    <div class="caption">
                        <h3 style="margin-top: 0px" class="botonera">
                            <span id="titulo_<?php echo $novedad['idnovedad']; ?>"><?php echo $novedad['titulo']; ?></span>
                            <?php if ($editor): ?>

                                <p class="pull-right">
                                    <button class="btn btn-warning btn-xs edit" role="button"
                                            data-novedad="<?php echo $novedad['idnovedad']; ?>">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                    <button class="btn btn-danger btn-xs delete"  role="button"
                                            data-novedad="<?php echo $novedad['idnovedad']; ?>">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </button>
                                </p>
                            <?php endif; ?>

                        </h3>
                        <?php echo $novedad['contenido']; ?>
                    </div>
                    <hr>
                    <ul class="list-inline" id="lista-destinatarios">
                        <?php foreach ($novedad['destinatarios'] as $destinatario) : ?>
                            <li><?php echo $destinatario['nombre']; ?> | </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php if ($editor): ?>
    <div class="modal" id="novedadModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">Formulario de Novedades</h4>
                </div>
                <div class="modal-body">
                    <form id="novedad-form" action="novedades/save" method="post">
                        <div class="form-group">
                            <label for="titulo" class="control-label">Titulo:</label>
                            <input type="text" class="form-control" id="titulo" name="titulo"/>
                            <br>
                        </div>
                        <div class="form-group">
                            <label for="contenido" class="control-label">Contenido:</label>
                            <textarea class="form-control" rows="5" id="contenido" name="contenido"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="roles" class="control-label">Destinatarios:</label>
                            <?php foreach ($roles as $rol) : ?>
                                <div class="checkbox">
                                    <label><input type="checkbox"
                                                  name="destinatarios[]"
                                                  class="destinatarios"
                                                  id="check_<?php echo $rol['nombre']; ?>"
                                                  value="<?php echo $rol['idrol']; ?>"><?php echo $rol['nombre']; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" value="0" class="form-control" id="idnovedad" name="idnovedad"/>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="cancelNovedad">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarNovedad">Enviar</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function clearForm() {
        $('#titulo').val("");
        $('#titulo').val(0);
        tinyMCE.activeEditor.setContent('');
        $(".destinatarios").each(function () {
            $(this).prop('checked', false);
        })
    }
    function closeNovedadModal() {
        var ok = confirm("¿Seguro que desea cancelar?");
        if (ok) {
            clearForm();
            $('#novedadModal').hide();
        }
    }
    // When the user clicks the button, open the modal
    $("#nueva").click(function () {
        $('#novedadModal').show();
    });

    $(".edit").click(function (event) {
        let target = $(event.target);
        if (target.is("span")) {
            target = $(target.parent());
        }
        const idnovedad = target.data("novedad");

        var novedad = $("#novedad_" + idnovedad);
        let destinatarios = [];
        novedad.find('#lista-destinatarios')
                .find('li')
                .each(function () {
                    destinatarios.push($(this).text().split(" ")[0]);
                });
        $(".destinatarios").each(function () {
            const elem = $(this).attr("id").split("_")[1]
            const existe = $.inArray(elem, destinatarios);
            if (existe != -1) {
                $(this).prop('checked', true);
            }
        })
        titulo = novedad.find('#titulo_' + idnovedad).text();
        //contenido = $(novedad.find('p')[2]).html();
        contenido = $(novedad.html());
        contenido.find(".botonera").remove();
        contenido.find(".list-inline").remove();

        $('#novedadModal').show();
        $('#titulo').val(titulo);
        $('#idnovedad').val(idnovedad);
        console.log(contenido.html());
        tinyMCE.activeEditor.setContent(contenido.html());

    });
    // When the user clicks on <span> (x), close the modal
    $(".close").click(function () {
        closeNovedadModal();
    });
    // When the user clicks on <button> (Cancelar), close the modal
    $("#cancelNovedad").click(function () {
        var ok = confirm("¿seguro que desea cancelar?");
        if (ok) {
            clearForm();
            $('#novedadModal').hide();
        }
    });
    // When the user clicks anywhere outside of the modal, close it
    $(window).click(function (event) {
        if (event.target == $('#novedadModal')[0]) {
            var ok = confirm("¿seguro que desea cancelar?");
            if (ok) {
                clearForm();
                $('#novedadModal').hide();
            }
        }
    });
    // When the user clicks on <button> (Enviar)
    $("#guardarNovedad").click(function () {
        tinyMCE.triggerSave();
        let titulo = $("#titulo").val();
        let contenido = $("#contenido").val();
        var destinatarios = $("#novedad-form input:checkbox:checked").map(function () {
            return $(this).val();
        }).get(); // <----
        if (titulo && contenido && destinatarios.length > 0) {
            $("#novedad-form").submit();
        } else {
            let msj = "Hay errores en el formulario:\n";
            if (titulo == 0) {
                msj += "El titulo no puede estar vacio.\n";
            }

            if (contenido == 0) {
                msj += "El contenido no puede ser vacio.\n";
            }

            if (destinatarios.length == 0) {
                msj += "Debe seleccional al menos 1 destinatario.\n";
            }
            alert(msj);
            return false;
        }
    });


//    $(".editar").click(function (event) {
//        let target = $(event.target);
//        if (target.is("span")) {
//            target = $(target.parent());
//        }
//        const idnovedad = target.data("novedad");
//        $.ajax({
//            type: "GET",
//            dataType: 'json',
//            url: "novedades/novedad/" + idnovedad,
//            success: function (data) {
//                alert(data.message)
//            },
//            error: function (data) {
//                alert(data.message)
//            }
//        });
//
//    });

    $().ready(function () {
        tinymce.init({
            selector: 'textarea',
            height: 200,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste code'
            ],
            toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            content_css: [
                '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                '//www.tinymce.com/css/codepen.min.css']
        });

        $('.delete').click(function (event) {
            if (!confirm("Seguro que desea eliminar esta novedad?")) {
                return false;
            }

            let target = $(event.target);
            if (target.is("span")) {
                target = $(target.parent());
            }
            const idnovedad = target.data("novedad");
            $.ajax({
                type: "POST",
                dataType: 'json',
                data: {idnovedad: idnovedad},
                url: "novedades/delete/",
                success: function (data) {
                    alert(data.message)
                    target.parent().parent().parent().parent().parent().parent().remove();
                },
                error: function (data) {
                    alert(data.message)
                }
            });
        });
    });
</script>
