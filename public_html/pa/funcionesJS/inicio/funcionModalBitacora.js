$(document).ready(function () {

    $("#formEnviarRespuesta").validate({//FORMULARIO DE RESPUESTA
        rules: {
            asunto: {
                required: true
            }

        },
        messages: {
            asunto: {
                required: "Por favor, ingrese Asunto"
            }
        },
        submitHandler: function () { 
          
            var idDocumento = $("#idDocumento").val();
            var emisor = $("#emisor").val();
            var fechaRespuesta = $("#fechaRespuesta").val();
            var asunto = $("#asunto").val();
            var detalle = $("#detalle").val();
            var cerrarDoc = $("#cerrarDoc").is(":checked") ? $("#cerrarDoc").val() : "1";//if ternario si cerrar doc esta chekeado le asigno el avalor 2 sino un 1 por los valores de la base de datos

            $.post("../negocio/inicio/procesarResponderInicio.php", {"idDocumento": idDocumento, "emisor": emisor, "fechaRespuesta": fechaRespuesta,
                "asunto": asunto, "detalle": detalle, "cerrarDoc": cerrarDoc}, function (r) {

                if (r == 1) {
                    swal(
                            '¡Bitacora agregada exitosamente',
                            '',
                            'success'
                            );

                    $("#formEnviarRespuesta").each(function () {
                        this.reset();//Reseteando el formulario
                    });
                    //Escondiendo botones y mostrando mensajes para no volver a "cerrar" el documento
                    if (cerrarDoc == 2) {
                        $("#" + $("#idBtn").val() + "").hide();
                        $("#" + $("#idPMensajeDoc").val() + "").fadeIn();
                        $("#" + $("#idPEstadoDoc").val() + "").text("Cerrado");
                    }

                    //CARGANDO BITACORA CON AJAX
                    $.get("../negocio/buscador/bitacoraAjax.php", {"idDocumento": idDocumento}, function (r) {
                        $("#divBitacoraAjax").html(r);

                    });

                } else {
                    swal(
                            '¡Error, no se ha podido agregar la bitacora',
                            '',
                            'error'
                            );
                }


            });
        }
    });

});

