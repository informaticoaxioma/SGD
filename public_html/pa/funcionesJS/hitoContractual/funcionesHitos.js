$(document).ready(function () {

    $(":input").focus(function () {
        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");
    });


    $('#color').colorselector(); //Inicializando el plugin de selector de colores en el modal de ingreso de hito

    $("#formIngresarHito").validate({//validando el formulario
        rules: {
            descripcionHito: {
                required: true
            },
            idResponsable: {
                required: true
            },
            destino: {
                required: true,
                maxlength: 100
            },
            normativa: {
                required: true,
                maxlength: 100
            },
            idFrecuenciaHito: {
                required: true
            }

        },
        messages: {
            descripcionHito: {
                required: "Por favor, ingrese Descripción"
            },
            idResponsable: {
                required: "Por favor, seleccione Responsable"

            },
            destino: {
                required: "Por favor, ingrese Destinatario",
                maxlength: "Error, límite de caracteres excedido"
            },
            normativa: {
                required: "Por favor, ingrese Normativa",
                maxlength: "Error, límite de caracteres excedido"
            },
            idFrecuenciaHito: {
                required: "Por favor, seleccione Frecuencia"
            }
        },
        submitHandler: function () {//enviando el formulario

            var descripcionHito = $("#descripcionHito").val();
            var fechaEntrega = $("#fechaEntrega").val();
            var idResponsable = $("#idResponsable").val();
            var destino = $("#destino").val();
            var normativa = $("#normativa").val();
            var comentario = $("#comentario").val();
            var idFrecuenciaHito = $("#idFrecuenciaHito").val();
            var idColor = $("#color").val();
            var idDocRelacionado = $("#idDocRelacionado").val();

            bloquearPantalla();
            $(".aviso").show("fast"); //MOSTRANDO GIF DE CARGA

            $.post("../negocio/hitoContractual/procesarIngresarHito.php", {"descripcionHito": descripcionHito, "idResponsable": idResponsable,
                "destino": destino, "normativa": normativa, "fechaEntrega": fechaEntrega, "flag": 1, "idDocRelacionado": idDocRelacionado,
                "idFrecuenciaHito": idFrecuenciaHito, "idColor": idColor, "comentario": comentario},
                    function (r) {


                        if (r == 1) {//Realizando acciones segun respuesta de la funcion

                            $(".mensajeError").hide("fast");
                            $(".mensajeExito").show("fast");
                            $("#formIngresarHito").each(function () {//reseteando el formulario
                                this.reset();
                            });
                            $("#btnGuardarHito").hide("fast"); //escondiendo el boton de accion

                            $("#divSeleccionarDocRel").hide("fast");
                            $("#tablaDocsRelacionados").empty();
                            $(".mensajeExitoDocRel").hide("fast");

                            desbloquearPantalla();
                        } else {//mostrando mensajes de error

                            desbloquearPantalla();
                            $(".mensajeExito").hide("fast");
                            $(".mensajeError").show("fast");
                        }

                    });
        }

    });


    $("#ModalAdd").on('hidden.bs.modal', function () {//limpiando el modal al perder el focus
        $.get("../negocio/eliminarSessionDocumentos.php");
        $(".help-block").hide("fast");
        $(".form-group").removeClass("has-error");
        $(".mensajeError").hide("fast");
        $(".mensajeExito").hide("fast");
        $("#btnGuardarHito").show("fast");

        $("#formIngresarHito").each(function () {
            this.reset();
        });

        $.get("hitoContractual/hitosContractuales.php", function (r) {

            $.getScript("funcionesJS/hitoContractual/funcionCalendario.js");
            $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
            //Actualizando la tabla de hitos del mes
            $(".ajaxCalendario").html(r);
        });
    });


//    $("#ModalEdit").on('hidden.bs.modal', function () {//limpiando el modal al perder el focus
//        $.get("../negocio/eliminarSessionDocumentos.php");
//
//        $.get("hitoContractual/hitosContractuales.php", function (r) {
//
//            $.getScript("funcionesJS/hitoContractual/funcionCalendario.js");
//            $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
//            //Actualizando la tabla de hitos del mes
//            $(".ajaxCalendario").html(r);
//        });
//    });




    $("#btnDocRelacionado").click(function () {//MOSTRANDO DIV CON LA SELECCION DE DOCUMENTOS

        $("#divSeleccionarDocRel").show("fast");
    });
    //-----------------------------------//
    //  BUSQUEDA DOC RELACIONADO   //
    //---------------------------------//

    $("#btnBuscarDoc").click(function () {

        var idContrato = $("#idContratoBuscar").val();
        var numDoc = $("#numDocumentoBuscar").val();
        var idNombreNumero = $("#idNombreNumero").val();
        var materia = $("#materiaBuscar").val();

        $.get("../negocio/ajax/buscarDocumentos.php", {"idContrato": idContrato, "numDoc": numDoc, "idNombreNumero": idNombreNumero, "materia": materia},
                function (r) {
                    $(".resultadoAjax").html(r);
                });
    });


    $("#btnCerrarBuscarDoc").click(function () {//BOTON PARA CERRAR EL DIV DE DOCS RELACIONADOS

        $("#materiaBuscar").val("");
        $("#numDocumentoBuscar").val("");
        $("#divSeleccionarDocRel").hide("fast");
        $(".mensajeExitoDocRel").hide("fast");
        $(".resultadoAjax").empty();
    });

    $("#btnSalirBuscar").click(function () {//BOTON PARA SALIR DE BUSCAR DOCS

        $("#materiaBuscar").val("");
        $("#numDocumentoBuscar").val("");
        $("#divSeleccionarDoc").hide("fast");
        $(".mensajeExitoDocRel").hide("fast");
    });


    //-----------------------------------//
    //        ACTUALIZANDO HITO         //
    //---------------------------------//

    $("#formActualizarHito").validate({
        rules: {
            descripcionHitoMod: {
                required: true
            },
            destinoHitoMod: {
                required: true
            },
            normativaHitoMod: {
                required: true
            }
        },
        messages: {
            descripcionHitoMod: {
                required: "Por favor, ingrese Descripción"
            },
            destinoHitoMod: {
                required: "Por favor, ingrese Destino"
            },
            normativaHitoMod: {
                required: "Por favor, ingrese Normativa"
            }
        },
        submitHandler: function () {

            //INICIALIZANDO VARIABLES
            var fechaEntrega = $("#fechaEntregaMod").val();
            var descripcionHito = $("#descripcionHitoMod").val();
            var idResponsable = $("#idResponsableHito").val();
            var destino = $("#destinoHitoMod").val();
            var normativa = $("#normativaHitoMod").val();
            var comentario = $("#comentarioHitoMod").val();
            var idEstadoHitoMod = $("#idEstadoHitoMod").is(":checked") ? '2' : '1';
            var idHito = $("#idHito").val();

            //ENVIANDO FORMULARIO POR AJAX
            $.post("../negocio/hitoContractual/procesarIngresarHito.php", {"descripcionHito": descripcionHito, "idResponsable": idResponsable, "destino": destino,
                "normativa": normativa, "comentario": comentario, "idEstadoHitoMod": idEstadoHitoMod, "fechaEntrega": fechaEntrega,
                "idHito": idHito, "flag": 2}, function (r) {

                if (r != '-1') {//ANALIZANDO LA RESPUESTA, 1 CORRECTO SINO INCORRECTO
                    //MOSTRANDO  MENSAJES
                    $.get("../negocio/hitoContractual/actualizarHitoAjax.php", {"idHito": r}, function (r) {

                        $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
                        $.getScript("funcionesJS/cargarCalendario.js");
                        $("#divAjaxHitoEdit").html(r);

                    });

                    //MENSAJES
                    swal(
                            '¡Hito actualizado exitosamente!',
                            '',
                            'success'
                            );

                } else {
                    //MENSAJES
                    swal(
                            '¡Error, no se ha podido Hito actualizado exitosamente!',
                            '',
                            'error'
                            );


                }

            });
        }

    });



    //-------------------------------------------//
    //              LISTADO DE HITOS            //
    //-----------------------------------------//

    //listar hitos por filtro
    $("#formFiltroHitoListado").submit(function (e) {
        e.preventDefault();
        var idResponsable = $("#idResposableListado").val();
        var idEstadoHito = $("#idEstadoHitoListado").val();
        var idFrecuenciaHito = $("#idFrecuenciaHitoListado").val();

        $.get("hitoContractual/listarHitosAjax.php", {"idResponsable": idResponsable, "idEstadoHito": idEstadoHito, "idFrecuenciaHito": idFrecuenciaHito}, function (r) {
            $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
            $(".divAjax").html(r);

        });
    });

    $(".aPagination").click(function (e) {
        e.preventDefault();
        var page = $(".active a").html();
        page = parseInt(page) - 1;
        
        var idResponsable = $("#idResposableListado").val();
        var idEstadoHito = $("#idEstadoHitoListado").val();
        var idFrecuenciaHito = $("#idFrecuenciaHitoListado").val();

        $.get("hitoContractual/listarHitosAjax.php", {"page": page, "idResponsable": idResponsable, "idEstadoHito": idEstadoHito, "idFrecuenciaHito": idFrecuenciaHito}, function (r) {
            $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
            $(".divAjax").html(r);

        });
    });

    $(".aNext").click(function (e) {
        e.preventDefault();
        var page = $(this).html();
        var idResponsable = $("#idResposableListado").val();
        var idEstadoHito = $("#idEstadoHitoListado").val();
        var idFrecuenciaHito = $("#idFrecuenciaHitoListado").val();

        $.get("hitoContractual/listarHitosAjax.php", {"page": page, "idResponsable": idResponsable, "idEstadoHito": idEstadoHito, "idFrecuenciaHito": idFrecuenciaHito}, function (r) {
            $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
            $(".divAjax").html(r);

        });
    });

    $(".siguiente").click(function (e) {
        e.preventDefault();
        var page = $(".active a").html();
        page = parseInt(page) + 1;

        var idResponsable = $("#idResposableListado").val();
        var idEstadoHito = $("#idEstadoHitoListado").val();
        var idFrecuenciaHito = $("#idFrecuenciaHitoListado").val();

        $.get("hitoContractual/listarHitosAjax.php", {"page": page, "idResponsable": idResponsable, "idEstadoHito": idEstadoHito, "idFrecuenciaHito": idFrecuenciaHito}, function (r) {
            $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
            $(".divAjax").html(r);

        });
    });
});


function relacionarDocumento(idDocRelacionado, idFila) {

    $.get("../negocio/ingresoDocumento/procesarRelacionarDocumento.php", {"idDocRelacionado": idDocRelacionado}, function (r) {
        swal(
                '¡Documento relacionado exitosamente!',
                '',
                'success'
                );

        $("#" + idFila + "").hide("normal");
        $("#tablaDocsRelacionados").html(r);
    });

}

function relacionarDocumentoModHitos(idDocRelacionado, idFila) {


    $.get("../negocio/ingresoDocumento/procesarRelacionarDocumento.php", {"idDocRelacionado": idDocRelacionado}, function (r) {
        $("#" + idFila + "").hide("fast");
        swal(
                '¡Documento relacionado exitosamente!',
                '',
                'success'
                );

        $("#tablaDocsRelacionadosMod").html(r);
    });
}
//---------------------------------------//
//FUNCIONES PARA EL MODAL ACTUALIZAR HITO
//-------------------------------------//
function cargarFormDocRel() {//CARGA EL FORMULARIO PARA RELACIONAR DOC EN EL MODULO ACTUALIZAR HITO


    $("#divSeleccionarDoc").show("normal");
}

function salirFormDocRel() {//METODO PAR ASLAIR DEL FORMULARIO DOC REL EN EL MODULO ACTUALZIAR DOCUMENTO
    $(".resultadoAjax").empty();
    $("#numDocumentoBuscarMod").val("");
    $("#materiaBuscarMod").val("");
    $(".mensajeExitoDocRel").hide("fast");
    $("#divSeleccionarDoc").hide("fast");
}

function buscarDoc(numDoc, materiaDoc) {//METODO QUE PERMITE BUSCAR DOCUMENTOS DE ACUERDO A CIERTOS CRITERIOS

    var idContrato = $("#idContratoBuscar").val();
    var numDocumento = document.getElementById(numDoc).value;
    var materia = document.getElementById(materiaDoc).value;
    $.get("../negocio/hitoContractual/buscarDocRelacionar.php", {"idContrato": idContrato, "numDoc": numDocumento, "materia": materia},
            function (r) {
                $(".resultadoAjax").html(r); //CARANDO RESULTADOS
            });
}

function actualizarHito(idHito) {

    $.get("../negocio/hitoContractual/actualizarHitoAjax.php", {"idHito": idHito}, function (r) {
        $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
        $.getScript("funcionesJS/cargarCalendario.js");
        $("#divAjaxHitoEdit").html(r);
        $("#idHito").val(idHito);//ASIGNANDO IDHITO AL INPUT

    });

    $('#ModalEditListado').modal('show');
}

function eliminarDocRelacionadoSession(idDocRel, flag) {

    var flagDoc = $("#" + flag + "").val();
    $.get("../negocio/ingresoDocumento/procesarGestionarRelacionDocumentos.php", {"idDocRelacionado": idDocRel, "flagDoc": flagDoc}, function (r) {

        $("#tablaDocsRelacionados").html(r);
        $("#tablaDocsRelacionadosMod").html(r);//carga para modal con conflicto
        $("#flagCargaArchivosSession").val(flagDoc);
    });
}

function buscarDocumentos() {

    var idContrato = $("#idContratoBuscar").val();
    var numDoc = $("#numDocumentoBuscarMod").val();
    var idNombreNumero = $("#idNombreNumero").val();
    var materia = $("#materiaBuscarMod").val();

    $.get("../negocio/ajax/buscarDocumentosModHitos.php", {"idContrato": idContrato, "numDoc": numDoc, "idNombreNumero": idNombreNumero, "materia": materia},
            function (r) {
                $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
                $(".resultadoAjax").html(r);
            });
}


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