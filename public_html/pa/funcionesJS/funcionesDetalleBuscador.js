
//-----------------------------------//
//      ELIMINAR SEGUIMIENTO           //
//---------------------------------//

function eliminarSeguimiento(idSeg, element) {
    var data = {
        "idSeg": idSeg,
        "flagDoc": 5
    }

    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¡Está a punto de eliminar un registro del sistema!',
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
        $.ajax({
            data: data,
            url: '../../negocio/inicio/procesarActualizarDocumento.php',
            type: 'post',
            success: function (data) {
                console.log(data);
                if (data == 0) {
                    // Codigo a ejecutar cuando la query fue exitosa y devuelve 1 (notar que tambien devuelve 1 cuando no elimina nada pero la query fue exitosa)
                    swal({
                        title: "Éxito",
                        text: "Registro eliminado",
                        icon: "success",
                        button: "Aceptar",
                    });
                    $(element).closest("tr").remove();
                } else {
                    // Codigo a ejecutar cuando la query falló y devuelve -1
                    swal({
                        title: "Error",
                        text: "Algo falló, el registro no ha sido eliminado",
                        icon: "error",
                        button: "Aceptar",
                    });
                }


                console.log(data);

                // Perform any additional actions after the record is successfully deleted
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                'Cancelado',
                'Los registros están intactos',
                'error'
            );
        }
    });

    
}


$(document).ready(function () {
    //smoth scroll
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
    //-----------------------------------//
    //      ACTUALIZAR DOCUMENTO        //
    //---------------------------------//
    $("#formActualizarDocumentoBuscador").validate({
        rules: {
            idSubContrato: {
                required: true
            },
            numeroDoc: {
                required: true
            },
            numeroProceso: {
                required: true
            },
            remitente: {
                required: true
            },
            destinatario: {
                required: true
            },
            materia: {
                required: true
            },
            incluye: {
                required: true
            },
            idTipoDoc: {
                required: true
            },
            fechaDoc: {
                required: true
            },
            fechaRecepcion: {
                required: true
            },
            fechaPlazo: {
                required: true
            }

        },
        messages: {
            idSubContrato: {
                required: "Por favor, seleccione Sub-Contrato"
            },
            numeroDoc: {
                required: "Por favor, ingrese número de documento"
            },
            numeroProceso: {
                required: "Por favor, ingrese número de proceso"
            },
            remitente: {
                required: "Por favor, ingrese remitente"
            },
            destinatario: {
                required: "Por favor, ingrese destinatario"
            },
            materia: {
                required: "Por favor, ingrese materia"
            },
            incluye: {
                required: "Por favor, ingrese inclusión"
            },
            idTipoDoc: {
                required: "Por favor, seleccione tipo de documento"
            },
            fechaDoc: {
                required: "Por favor, ingrese fecha de documento"
            },
            fechaRecepcion: {
                required: "Por favor, ingrese fecha de recepción"
            },
            fechaPlazo: {
                required: "Por favor, ingrese fecha de plazo"
            }
        },
        submitHandler: function () {

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
            var idCorresponsables = $("#idCorresponsables").val();
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
            formData.append("idCorresponsables", idCorresponsables);
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

            $(".gifCarga").fadeIn("fast");//gif carga

            $.ajax({
                url: '../../negocio/inicio/procesarActualizarDocumento.php', //Url a donde la enviaremos
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
                    }
                    //location.reload();
                }
            });
        }
    });



    $("#btnDocRelacionado").click(function () {
        $("#divSeleccionarDocRel").show("fast");
    });

    $("#btnCerrarBuscarDoc").click(function () {
        $("#numDocumentoBuscar").val("");
        $("#materiaBuscar").val("");
        $("#divSeleccionarDocRel").hide("fast");
    });

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

            $.post("../../negocio/inicio/procesarResponderInicio.php", {
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
                        '¡Error, no se podido agregar la Bitacora',
                        '',
                        'error');
                }

            });
        }
    });
});


function detalleAdjuntos() {
    
    var archivos = document.getElementById("adjunto");//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
    var archivo = archivos.files; //Obtenemos los archivos seleccionados en el input
    //Creamos una instancia del Objeto FormDara.
    var archivos = new FormData();
    /* Como son multiples archivos creamos un ciclo for que recorra el arreglo de los archivos seleccionados en el input
     Este y añadimos cada elemento al formulario FormData en forma de arreglo, utilizando la variable i (autoincremental) como 
     indice para cada archivo, si no hacemos esto, los valores del arreglo se sobre escriben*/
    for (i = 0; i < archivo.length; i++) {

        archivos.append('archivo' + i, archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
    }

    /*Ejecutamos la función ajax de jQuery*/
    $.ajax({
        url: '../../negocio/ajax/mostrarAdjuntos.php', //Url a donde la enviaremos
        type: 'POST', //Metodo que usaremos
        contentType: false, //Debe estar en false para que pase el objeto sin procesar
        data: archivos, //Le pasamos el objeto que creamos con los archivos
        processData: false, //Debe estar en false para que JQuery no procese los datos a enviar
        cache: false,
        success: function (r) {

            $("#listadoAdjunto").html(r);
            
        }
    });

    

}

function buscarArchivos() {
    event.preventDefault();
    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Está seguro que desea reemplazar los archivos? Estos seran eliminados permanentemente',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, modificar',
        cancelButtonText: 'No, cancelar',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
    }).then(function () {
        var fileInput = document.getElementById("adjunto");
        fileInput.click();
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                'Cancelado',
                'Los archivos no han sido modificados',
                'error'
            );
        }
    });



}

function insertarAdjuntos(idAdjunto, idDocumento) {
    var archivos = document.getElementById(idAdjunto);//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
    var archivo = archivos.files; //Obtenemos los archivos seleccionados en el imput
    //Creamos una instancia del Objeto FormDara.
    var archivos = new FormData();
    /* Como son multiples archivos creamos un ciclo for que recorra el arreglo de los archivos seleccionados en el input
     Este y añadimos cada elemento al formulario FormData en forma de arreglo, utilizando la variable i (autoincremental) como 
     indice para cada archivo, si no hacemos esto, los valores del arreglo se sobre escriben*/

    for (i = 0; i < archivo.length; i++) {

        archivos.append('archivo' + i, archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
    }

    archivos.append("idDocumento", idDocumento);

    /*Ejecutamos la función ajax de jQuery*/
    $.ajax({
        url: '../../negocio/procesarIngresarAdjuntos.php', //Url a donde la enviaremos
        type: 'POST', //Metodo que usaremos
        contentType: false, //Debe estar en false para que pase el objeto sin procesar
        data: archivos, //Le pasamos el objeto que creamos con los archivos
        processData: false, //Debe estar en false para que JQuery no procese los datos a enviar
        cache: false,
        success: function () {
            $("#listadoAdjunto").empty();
        }
    });

}


//REVISAR ESTE METODO
function cargarIdDocModalArchivos(idDoc, numDoc, numProvidencia, numProceso) {

    $("#idDocModal").val(idDoc);
    $("#numDocModal").text("N° Documento: " + numDoc);
    $("#numProvidenciaModal").text("N° Providencia: " + numProvidencia);
    $("#numProcesoModal").text("N° Proceso: " + numProceso);


}

function eliminarRegistro(idDocumento, idFila) {

    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Está seguro que desea eliminar el registro??',
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
        $.post("../negocio/ingresoDocumento/procesarCargarArchivosRestantes.php", { "idDocumento": idDocumento, "flag": 2 },
            function (r) {

                if (r == '1') {
                    $("#" + idFila + "").hide();
                    swal(
                        '¡Registro Eliminado Exitosamente!',
                        '',
                        'success'
                    );


                } else {
                    swal(
                        'Error',
                        'No se ha podido eliminar el Registro',
                        'error'
                    );
                }
            });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                'Cancelado',
                'El registro no fue eliminado',
                'error'
            );
        }
    });

}


function cargarModalAgregarEntidad(tipoEntidad) {

    switch (tipoEntidad) {

        case 1://ASIGNANDO VALORES SI LA ENTIDAD ES REMITENTE
            $("#idTipoEntidad").val("1");
            $("#tituloTipoEntidad").text("Agregar Remitente");

            break;

        case 2://ASIGNANDO VALORES SI LA ENTIDAD ES DESTINATARIO
            $("#idTipoEntidad").val("2");
            $("#tituloTipoEntidad").text("Agregar Destinatario");

            break;
    }
}


function eliminarDocRelacionadoSession(idDocRel, flag) {

    var flagDoc = $("#" + flag + "").val();
    $.get("../../negocio/ingresoDocumento/procesarGestionarRelacionDocumentos.php", { "idDocRelacionado": idDocRel, "flagDoc": flagDoc }, function (r) {

        $("#tablaDocsRelacionados").html(r);
        $("#flagCargaArchivosSession").val(flagDoc);
    });
}



function relacionarDocAuxiliar() {
    var idContrato = $("#idContratoBuscar").val();
    var numDoc = $("#numDocumentoBuscar").val();
    var idNombreNumero = $("#idNombreNumero").val();
    var materia = $("#materiaBuscar").val();
    $.get("../../negocio/ajax/buscarDocumentos.php", { "idContrato": idContrato, "numDoc": numDoc, "idNombreNumero": idNombreNumero, "materia": materia },
        function (r) {
            $(".resultadoAjax").html(r);
        });
}

function relacionarDocumento(idDocRelacionado, idFila) {

    $.get("../../negocio/ingresoDocumento/procesarRelacionarDocumento.php", { "idDocRelacionado": idDocRelacionado }, function (r) {
        $("#" + idFila + "").hide("fast");
        //metodo auxiliar, ya q en un modal el codigo de arriba no funka en inicio/detalledocumento
        $("table").find("#" + idFila + "").hide("fast");
        relacionarDocumentoDetalle(r, 2);
    });
}


function eliminarDocAdjuntosRel(idDoc, idFila, flag) {

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

        $.post("../../negocio/comun/eliminarDocAdjuntoRel.php", { "idDoc": idDoc, "flag": flag }, function (r) {

            if (r == '1') {
                swal(
                    'Documento eliminado exitosamente',
                    '',
                    'success'
                );
                $("#" + idFila + "").hide("fast"); //escodiendo la fila


            } else {

                swal(
                    'Error, no se ha podido eliminar el documento',
                    '',
                    'error'
                );
            }

        });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                'Cancelado',
                'El Documento no fue eliminado',
                'error'
            );
        }
    });
}

function relacionarDocumentoDetalle(idDocumento, flag) {

    switch (flag) {
        case 1:

            $.post("../negocio/comun/relacionarDocumentoDetalle.php", { "idDocumento": idDocumento }, function (r) {

                if (r == 1) {

                    swal('Documento(s) relacionado(s) exitosamente', '', 'success');

                }
            });
            break;

        case 2:

            $.post("../../negocio/comun/relacionarDocumentoDetalle.php", { "idDocumento": idDocumento }, function (r) {

                if (r == 1) {


                    swal('Documento(s) relacionado(s) exitosamente', '', 'success');
                    $.get("../../negocio/comun/cargarDocRelacionadosDetalle.php", { "idDocumento": idDocumento }, function (r) {
                        $(".divDocRelacionados").html(r);
                    });
                }
            });

            break;
    }

}

function habilitarActualizarDoc() {

    var flagEdit = $("#flagEditar").val();

    if (flagEdit == -1) {
        //editable
        $("#formActualizarDocumentoBuscador :input").prop("disabled", false);
        $("#flagEditar").val("1");

    } else {
        //no editable
        $("#flagEditar").val("-1");
        $("#formActualizarDocumentoBuscador :input").prop("disabled", true);
        $("#subir").prop("disabled", true);
        $("#btnEditarDocumento").prop("disabled", false);
    }
}


function cargarModalRespuesta(idDocumento, idBtn, idPEstado, idPMensajeDoc) {

    $("#idBtn").val(idBtn);
    $("#idPEstadoDoc").val(idPEstado);
    $("#idPMensajeDoc").val(idPMensajeDoc);

    $.get("../../negocio/ajax/modalResponderInicio.php", { "idDocumento": idDocumento }, function (r) {

        $.getScript("../funcionesJS/funcionesDetalleBuscador.js");
        $(".divAjax").html(r);
    });

}