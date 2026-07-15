$(document).ready(function () {
//limpiando mensajes despues de ingreso de datos
    $(":input").focus(function () {

        $(".mensajeError").hide("fast");
        $(".mensajeExito").hide("fast");
    });

    //-----------------------------------//
    //      INGRESAR TIPO DOCUMENTO     //
    //---------------------------------//
    $("#formIngresarTipoDoc").validate({
        rules: {
            nombreDocumento: {
                required: true
            }
        },
        messages: {
            nombreDocumento: {
                required: "Por favor, ingrese Tipo Documento"
            }
        },
        submitHandler: function () {
            var nombreDocumento = $("#nombreDocumento").val();

            $(".gifCarga").show("fast");

            $.post("../negocio/configuracion/tipoDocumentos/procesarIngresarActualizarTipoDoc.php", {"nombreDocumento": nombreDocumento, "flag": 1},
                    function (r) {

                        $(".gifCarga").hide("fast");

                        if (r == '1') {

                            $(".mensajeError").hide("fast");
                            $(".mensajeExito").show("fast");
                            $("#formIngresarTipoDoc").each(function () {
                                this.reset();
                            });

                            $(".divAjax").empty();

                            $.get("configuracion/tipoDocumentos/tablaTipoDocAjax.php", function (r) {

                                $(".divAjax").html(r);

                            });

                        } else {
                            $(".mensajeExito").hide("fast");
                            $(".mensajeError").show("fast");
                        }


                    });
        }
    });

});

//------------------------------------//
//      ACTUALIZAR / ELIMINAR        //
//----------------------------------//

function actualizarTipoDoc(idTipoDoc, tipoD) {

    var tipoDoc = $("#" + tipoD + "").val();

    $.post("../negocio/configuracion/tipoDocumentos/procesarIngresarActualizarTipoDoc.php", {"idTipoDoc": idTipoDoc, "tipoDoc": tipoDoc, "flag": 2},
            function (r) {

                if (r == '1') {

                    swal(
                            '!Tipo de Documento actualizado exitosamente',
                            '',
                            'success');

                } else {

                    swal(
                            '!Error, no se ha podido actualizar el Tipo de Documento',
                            '',
                            'error');
                }
            });

}


function eliminarTipoDoc(idTipoDoc, fila) {

    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Está seguro que desea eliminar el Tipo de Documento?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'No, cancelar',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
    }).then(function () {

        //ejecutando el metodo
        $.post("../negocio/configuracion/tipoDocumentos/procesarIngresarActualizarTipoDoc.php", {"idTipoDoc": idTipoDoc, "flag": 3},
                function (r) {

                    if (r == '1') {
                        swal(
                                '¡Tipo de Documento eliminado exitosamente!',
                                '',
                                'success'
                                );
                        $("#" + fila + "").hide();

                    } else {
                        swal(
                                'Error',
                                'No se ha podido eliminar el Tipo de Documento',
                                'error'
                                );
                    }
                });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'El Tipo de Documento no fue eliminado',
                    'error'
                    );
        }
    });

}