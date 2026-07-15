$(document).ready(function () {

    $('a[href^="#"]').on('click', function (e) {
        e.preventDefault();

        var target = this.hash;
        var $target = $(target);

        $('html, body').stop().animate({
            'scrollTop': $target.offset().top
        }, 900, 'swing', function () {
            window.location.hash = target;
        });
    });

    //ESCONDIENDO MENSAJES
    $(":input").focus(function () {
        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");

    });

    $("#btnEsconderTablaDoc").click(function () {
        $(".resultadoAjax").empty();
        $("#divTablaDocInicio").hide("fast");
    });
    //------------------------------------------//
    //    CARGANDO DOCUMENTOS SEGUN FILTRO     //
    //----------------------------------------//
    $(".selectorAdmin").click(function (e) {
        e.preventDefault();
        $("html, body").animate({ scrollTop: 350 }, '5000');//SCROLL A LA TABLA
    });


    //-----------------------------------------------//

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

            $.post("../negocio/inicio/procesarResponderInicio.php", {
                "idDocumento": idDocumento, "emisor": emisor, "fechaRespuesta": fechaRespuesta,
                "asunto": asunto, "detalle": detalle, "cerrarDoc": cerrarDoc
            }, function (r) {

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
                    $.get("../../negocio/buscador/bitacoraAjax.php", { "idDocumento": idDocumento }, function (r) {
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



    //CERRANDO DIV DEL DETALLE
    $("#btnCerrarDetalle").click(function () {

        $("#contenedorAjax").empty();
        $("#headerInicio").show("fast");
    });

    //----------------------------------------//
    //              PAGINACION               //
    //--------------------------------------//
    $(".aPagination").click(function (e) {
        e.preventDefault();

        var page = $(".active a").html();
        page = parseInt(page) - 1;
        var idSubContrato = $("#idSubcontrato").val();
        var idFlujo = $("#idFlujo").val();
        var idTipoUsuario = $("#idPerfilUsuario").val();

        switch (idTipoUsuario) {

            case "1":
                $.get("inicio/cargarDocsPorFiltroAdmin.php", { "page": page, "idFlujo": idFlujo }, function (r) {
                    $.getScript("funcionesJS/inicio/funcionesInicio.js");
                    $(".resultadoAjax").html(r);
                });


                break;
            case "3":
                var flag = $("#flagEncargado").val();

                if (flag == "1") {

                    $.get("inicio/cargarDocPorFiltroEncargado.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $("#resultadosDocsEncargado").html(r);
                    });

                } else if (flag == "2") {
                    $.get("inicio/cargarDocPorFiltroContrato.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $("#resultadosDocsEncargado").html(r);
                    });
                } else {
                    $.get("inicio/cargarDocsPorFiltro.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $(".resultadoAjax").html(r);
                    });

                }

                break;

            default:
                $.get("inicio/cargarDocsPorFiltro.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                    $.getScript("funcionesJS/inicio/funcionesInicio.js");

                    $(".resultadoAjax").html(r);
                });
                break;
        }

    });

    $(".siguiente").click(function (e) {
        e.preventDefault();

        var page = $(".active a").html();
        page = parseInt(page) + 1;
        var idSubContrato = $("#idSubcontrato").val();
        var idFlujo = $("#idFlujo").val();
        var idTipoUsuario = $("#idPerfilUsuario").val();

        switch (idTipoUsuario) {

            case "1":
                $.get("inicio/cargarDocsPorFiltroAdmin.php", { "page": page, "idFlujo": idFlujo }, function (r) {
                    $.getScript("funcionesJS/inicio/funcionesInicio.js");
                    $(".resultadoAjax").html(r);
                });


                break;

            case "3":
                var flag = $("#flagEncargado").val();

                if (flag == "1") {

                    $.get("inicio/cargarDocPorFiltroEncargado.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $("#resultadosDocsEncargado").html(r);
                    });
                } else if (flag == "2") {
                    $.get("inicio/cargarDocPorFiltroContrato.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $("#resultadosDocsEncargado").html(r);
                    });

                } else {
                    $.get("inicio/cargarDocsPorFiltro.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $(".resultadoAjax").html(r);
                    });

                }

                break;

            default:
                $.get("inicio/cargarDocsPorFiltro.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                    $.getScript("funcionesJS/inicio/funcionesInicio.js");

                    $(".resultadoAjax").html(r);
                });
                break;
        }

    });

    $(".aNext").click(function (e) {
        e.preventDefault();

        var page = $(this).html();
        var idSubContrato = $("#idSubcontrato").val();
        var idFlujo = $("#idFlujo").val();
        var idTipoUsuario = $("#idPerfilUsuario").val();

        switch (idTipoUsuario) {
            case "3":
                var flag = $("#flagEncargado").val();

                if (flag == "1") {

                    $.get("inicio/cargarDocPorFiltroEncargado.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $("#resultadosDocsEncargado").html(r);
                    });
                } else if (flag == "2") {
                    $.get("inicio/cargarDocPorFiltroContrato.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $("#resultadosDocsEncargado").html(r);
                    });

                } else {
                    $.get("inicio/cargarDocPorFiltro.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                        $.getScript("funcionesJS/inicio/funcionesInicio.js");

                        $(".resultadoAjax").html(r);
                    });

                }

                break;

            default:
                $.get("inicio/cargarDocPorFiltroEncargado.php", { "page": page, "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {
                    $.getScript("funcionesJS/inicio/funcionesInicio.js");

                    $(".resultadoAjax").html(r);
                });
                break;
        }
    });



});

function bloquearPantalla() {
    $.blockUI(
        {
            message: '<img src="media/30.gif" /><h2> Procesando, por favor espere.</h2>',
            css: {
                border: 'none',
                padding: '15px',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .5,
                color: '#fff'

            }
        }
    );

    $("#sideNav a").addClass("bloqueo-link");
}

function desbloquearPantalla() {
    $.unblockUI();
    $("#sideNav a").removeClass("bloqueo-link");
}



function cargarModalRespuesta(idDocumento) {

    $.get("../negocio/ajax/modalResponderInicio.php", { "idDocumento": idDocumento }, function (r) {

        $.getScript("funcionesJS/inicio/funcionesInicio.js");
        $.getScript("funcionesJS/inicio/funcionModal.js");
        $(".divAjax").html(r);
    });

}

function cargarModalRespuestaDetalle(idDocumento) {

    $.get("../negocio/ajax/modalResponderInicio.php", { "idDocumento": idDocumento }, function (r) {

        $.getScript("funcionesJS/inicio/funcionModalBitacora.js");
        $(".divAjax").html(r);
    });

}


function cargarDetalle(idDocumento) {

    $.get("../negocio/eliminarSessionDocumentos.php");

    $("#headerInicio").hide();
    $.get("inicio/cargarDetalleDocumento.php", { "idDocumento": idDocumento }, function (r) {

        //        $.getScript("funcionesJS/inicio/funcionesInicio.js");
        $.getScript("funcionesJS/cargarCalendario.js");
        $.getScript("funcionesJS/ingresarDocumentos/funcionesIngresarDocumentos.js");
        $.getScript("funcionesJS/funcionesComunes.js");

        $("#contenedorAjax").html(r);
        $("#contenedorAjax").show("fast");

        $("#formActualizarDocumento :input").prop("disabled", true);
        $("#btnEditarDocumento").prop("disabled", false);

    });
}


//**************************************************//
//          SELECTORES SEGUN TIPO USUARIO          //
//************************************************//
function selectorFlujo(idSubContrato, idFlujo) {
    $("#idFlujo").val(idFlujo);
    $("#flagEncargado").val("");

    $.get("../pa/inicio/cargarDocsPorFiltro.php", { "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {

        $.getScript("funcionesJS/inicio/funcionesInicio.js");//IMPORTANDO LAS FUNCIONES JS

        $(".resultadoAjax").html(r);//CARGANDO EL RESULTADO EN EL DIV

        //$("html, body").animate({scrollTop: 350}, '5000');//SCROLL A LA TABLA

    });
}

function selectorFlujoContratoDoc(idSubContrato, idFlujo) {
    $("#idFlujo").val(idFlujo);

    $.get("../pa/inicio/cargarDocPorFiltroContrato.php", { "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {

        $.getScript("funcionesJS/inicio/funcionesInicio.js");//IMPORTANDO LAS FUNCIONES JS

        $("#resultadosDocsEncargado").html(r);//CARGANDO EL RESULTADO EN EL DIV

        $("#flagEncargado").val(2);

        //$("html, body").animate({scrollTop: 350}, '5000');//SCROLL A LA TABLA

    });
}


function selectorFlujoEncargadoDoc(idSubContrato, idFlujo) {
    $("#idFlujo").val(idFlujo);
    $.get("../pa/inicio/cargarDocPorFiltroEncargado.php", { "idSubContrato": idSubContrato, "idFlujo": idFlujo }, function (r) {

        $.getScript("funcionesJS/inicio/funcionesInicio.js");//IMPORTANDO LAS FUNCIONES JS

        $("#resultadosDocsEncargado").html(r);//CARGANDO EL RESULTADO EN EL DIV

        $("#flagEncargado").val(1);

        //$("html, body").animate({scrollTop: 1000}, '5000');//SCROLL A LA TABLA

    });
}

function selectorFlujoAdmin(idFlujo) {
    $("#idFlujo").val(idFlujo);

    $.get("../pa/inicio/cargarDocsPorFiltroAdmin.php", { "idFlujo": idFlujo }, function (r) {

        $.getScript("funcionesJS/inicio/funcionesInicio.js");//IMPORTANDO LAS FUNCIONES JS

        $(".resultadoAjax").html(r);//CARGANDO EL RESULTADO EN EL DIV

        //$("html, body").animate({scrollTop: 350}, '5000');//SCROLL A LA TABLA

    });
}
//****************************************//
//    FIN SELECTORES SEGUN TIPO USUARIO  //
//****************************************//

function habilitarActualizarDoc() {

    var flagEdit = $("#flagEditar").val();

    if (flagEdit == -1) {

        $("#formActualizarDocumento :input").prop("disabled", false);
        $("#flagEditar").val("1");

    } else {

        $("#flagEditar").val("-1");
        $("#formActualizarDocumento :input").prop("disabled", true);
        $("#btnEditarDocumento").prop("disabled", false);
    }
}

function volverInicio() {
    $("#contenedorAjax").fadeOut("fast");
    $("#headerInicio").show();
}


function eliminarDocumento(idDocumento, idFila) {
    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Esta seguro que desea eliminar el Documento?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'No, cancelar!',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
    }).then(function () {

        //ejecutando el metodo
        $.post("../negocio/inicio/procesarActualizarDocumento.php", { "idDocumento": idDocumento, "flagDoc": 4 }, function (r) {

            if (r == '1') {
                swal(
                    '¡Documento Eliminado Exitosamente!',
                    '',
                    'success'
                );
                $("#" + idFila + "").hide();

            } else {
                swal(
                    'Error',
                    'No se ha podido eliminar el Documento',
                    'error'
                );
            }
        });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                'Cancelado',
                'El documento no fue eliminado',
                'error'
            );
        }
    });

}


function actualizarDocumento() {

    var idSubContrato = $("#idSubContrato").val();
    var idDocumento = $("#idDocumento").val();
    var numeroDoc = $("#numeroDoc").val();
    var numeroProceso = $("#numeroProceso").val();
    var numeroProvidencia = $("#numeroProv").val();
    var remitente = $("#remitente").val();
    var destinatario = $("#destinatario").val();
    var materia = $("#materia").val();
    var antecedente = $("#antecedente").val();
    var incluye = $("#incluye").val();
    var comentarios = $("#comentarios").val();
    var idTipoDoc = $("#idTipoDoc").val();
    var fechaDoc = $("#fechaDoc").val();
    var fechaRecepcion = $("#fechaRecepcion").val();
    var fechaPlazo = $("#fechaPlazo").val();
    var idResponsable = $("#idResponsable").val();
    var idEstado = $("#idEstado").val();

    //ACCIONES

    var conocimiento = $("#conocimiento").is(":checked") ? $("#conocimiento").val() : "";
    var coordinar = $("#coordinar").is(":checked") ? $("#coordinar").val() : "";
    var conversar = $("#conversar").is(":checked") ? $("#conversar").val() : "";
    var archivo = $("#archivo").is(":checked") ? $("#archivo").val() : "";
    var responder = $("#responder").is(":checked") ? $("#responder").val() : "";
    var revisar = $("#revisar").is(":checked") ? $("#revisar").val() : "";
    var urgente = $("#urgente").is(":checked") ? $("#urgente").val() : "";
    //

    var flag = $("#flag").val();

    $(".gifCarga").fadeIn("fast");//gif carga


    var archivoDoc = $("#archivoDoc")[0].files[0];

    var formData = new FormData();

    formData.append("idSubContrato", idSubContrato);
    formData.append("idDocumento", idDocumento);
    formData.append("numeroDoc", numeroDoc);
    formData.append("numeroProceso", numeroProceso);
    formData.append("numeroProvidencia", numeroProvidencia);
    formData.append("remitente", remitente);
    formData.append("destinatario", destinatario);
    formData.append("materia", materia);
    formData.append("antecedente", antecedente);
    formData.append("incluye", incluye);
    formData.append("comentarios", comentarios);
    formData.append("idTipoDoc", idTipoDoc);
    formData.append("fechaDoc", fechaDoc);
    formData.append("fechaRecepcion", fechaRecepcion);
    formData.append("fechaPlazo", fechaPlazo);
    formData.append("idResponsable", idResponsable);
    formData.append("idEstado", idEstado);
    formData.append("archivoDoc", archivoDoc);
    formData.append("flag", flag);
    //acciones
    formData.append("conocimiento", conocimiento);
    formData.append("coordinar", coordinar);
    formData.append("conversar", conversar);
    formData.append("archivo", archivo);
    formData.append("responder", responder);
    formData.append("revisar", revisar);
    formData.append("urgente", urgente);

    bloquearPantalla();

    $.ajax({
        url: '../negocio/inicio/procesarActualizarDocumento.php', //Url a donde la enviaremos
        type: 'POST', //Metodo que usaremos              
        data: formData, //Le pasamos el objeto que creamos con los archivos
        contentType: false,
        processData: false,
        cache: false,
        success: function (r) {
            $(".gifCarga").fadeOut("fast");

            var res = $.parseJSON(r);

            if (res['exito'] == 1) {
                insertarAdjuntos('adjunto', res['idDocumento']);//insertando adjunto, paso por parametro el nombre del input que tiene los adjuntos, junto com el id del documento
                swal({
                    title: '¡Documento actualizado exitosamente',
                    text: '',
                    type: 'success',
                    allowOutsideClick: false
                  }).then(function () {                    
                    document.location.reload();
                  });


            } else {
                swal({
                    title: '¡Error, no se ha podido actualizar el documento',
                    text: '',
                    type: 'error',
                    allowOutsideClick: false
                  });
                desbloquearPantalla();
            }
        }
    });


}