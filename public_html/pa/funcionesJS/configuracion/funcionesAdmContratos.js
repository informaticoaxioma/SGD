$(document).ready(function () {


    $(":input").focus(function () {//ESCONDIENDO MENSAJES DE LAS OPERACIONES

        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");
        $(".mensajeExitoMod").hide("fast");
        $(".mensajeExitoSubC").hide("fast");
    });



    $("#formIngresarContrato").validate({//ingresar Contrato
        rules: {
            nombreContrato: {
                required: true,
                espacioBlanco: true
            },
            fechaTermino: {
                required: true,
                formatoFecha: true
            },
            idArea: {
                required: true
            }
        },
        messages: {
            nombreContrato: {
                required: "Por favor, ingrese contrato",
                espacioBlanco: "Error, no rellene con espacios"
            },
            fechaTermino: {
                required: "Por favor, ingrese fecha"
            },
            idArea: {
                required: "Por favor, seleccione un área"
            }
        },
        submitHandler: function () {

            var idArea = $("#idArea").val();
            var nombreContrato = $.trim($("#nombreContrato").val());
            var fechaTermino = $("#fechaTermino").val();

            $.post("../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php",
                    {"nombreContrato": nombreContrato, "idArea": idArea, "fechaTermino": fechaTermino, "flag": 1}, function (r) {

                if (r == 1) {
                    $(".mensajeError").hide("fast");
                    $(".mensajeExito").show("fast");

                    $("#formIngresarContrato").each(function () {
                        this.reset();
                    });

                } else {

                    $(".mensajeExito").hide("fast");
                    $(".mensajeError").show("fast");

                }

            });


        }

    });

    //-----------------------------------//
    //       INGRESAR SUB-CONTRATO      //
    //---------------------------------//
    $("#idAreaSubC").change(function () {
        var idArea = $(this).val();

        $.get("../negocio/configuracion/contratos/cargarContratoModulo.php", {"idArea": idArea}, function (r) {
            $(".divAjaxContrato").html(r);
        });
    });


    //INGRESANDO SUBCONTRATO
    $("#formIngresarSubContrato").validate({
        rules: {
            nombreSubContrato: {
                required: true
            },
            idAreaSubC: {
                required: true
            },
            idContrato: {
                required: true
            }
        },
        messages: {
            nombreSubContrato: {
                required: "Por favor, ingrese nombre"
            },
            idAreaSubC: {
                required: "Por favor, seleccione Área"
            },
            idContrato: {
                required: "Por favor, seleccione Contrato"
            }
        },
        submitHandler: function () {

            var nombreSubContrato = $("#nombreSubContrato").val();
            var idContrato = $("#idContrato").val();


            $.post("../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php",
                    {"nombreSubContrato": nombreSubContrato, "idContrato": idContrato, "flag": 4}, function (r) {

                if (r == 1) {

                    $(".mensajeErrorSubC").hide("fast");
                    $(".mensajeExitoSubC").show("fast");

                    $("#formIngresarSubContrato").each(function () {
                        this.reset();
                    });

                } else {

                    $(".mensajeErrorSubC").show("fast");
                    $(".mensajeExitoSubC").hide("fast");
                }
            });
        }

    });


    //----------------------------------//
    // ACTUALIZAR / ELIMINAR CONTRATO   //
    //--------------------------------//
    $("#idAreaBuscar").change(function () {

        var idArea = $(this).val();

        if (idArea != "") {

            $.get("configuracion/contratos/cargarContratosAreasAjax.php", {"idArea": idArea}, function (r) {

                $.getScript("funcionesJS/borrarMensajesAjax.js");
                $("#divContratoAjax").html(r);
                $("#divContratoAjax").show("fast");
            });

        } else {
            $("#divContratoAjax").hide("fast");
        }

    });


});

//----------------------------------------------------//
//METODOS DE ACTUALIZACION Y ELIMINACION DE CONTRATOS//
//--------------------------------------------------//

function eliminarContrato(idContrato, numeroFila) {

    swal({
        title: '¿Esta seguro que desea eliminar el Contrato?',
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
        $.post("../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php",
                {"idContrato": idContrato, "numeroFila": numeroFila, "flag": 3}, function (r) {

            if (r == '1') {
                swal(
                        '¡Contrato Eliminado Exitosamente!',
                        '',
                        'success'
                        );
                $("#" + numeroFila + "").hide("fast");//ELIMINANDO LA FILA DE LA TABLA

            } else {
                swal(
                        'Error',
                        'No se ha podido eliminar el Contrato',
                        'error'
                        );
            }
        });

    }, function (dismiss) {

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'El Contrato no fue eliminado',
                    'error'
                    );
        }
    });
}


function actualizarContrato(idContrato, nombre, area, fechaTerm) {

    var nombreContrato = $("#" + nombre + "").val();
    var idArea = $("#" + area + "").val();
    var fechaTermino = $("#" + fechaTerm + "").val();


    $.post("../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php",
            {"idContrato": idContrato, "nombreContrato": nombreContrato, "idArea": idArea, "fechaTermino": fechaTermino, "flag": 2}, function (r) {

        if (r == 1) {
            swal(
                    '¡Contrato Actualizado Exitosamente!',
                    '',
                    'success'
                    );

        } else {
            swal(
                    'Error',
                    'No se ha podido Actualizar el Contrato',
                    'error'
                    );
        }

    });
}


//--------------------------------------------------------//
//METODOS DE ACTUALIZACION Y ELIMINACION DE SUB-CONTRATOS//
//------------------------------------------------------//

function actualizarSubContrato(idSubContrato, idC) {//id del subcontrato y id del contrato 

    var idContrato = $("#" + idC + "").val();//asignando valor a la variable

    $.post("../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php",
            {"idSubContrato": idSubContrato, "idContrato": idContrato, "flag": 5}, function (r) {
        if (r == 1) {
            swal(
                    '¡Sub-Contrato Actualizado Exitosamente!',
                    '',
                    'success'
                    );

        } else {
            swal(
                    'Error',
                    'No se ha podido Actualizar el Sub-Contrato',
                    'error'
                    );
        }

    });
}

function eliminarSubContrato(idSubContrato, numeroFila) {

    swal({//Seteando plugin sweetAlert
        title: '¿Está seguro que desea eliminar el Sub-Contrato?',
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
        $.post("../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php",
                {"idSubContrato": idSubContrato, "numeroFila": numeroFila, "flag": 6}, function (r) {
          
            if (r == '1') {
                swal(
                        '¡Sub-Contrato Eliminado Exitosamente!',
                        '',
                        'success'
                        );
                $("#" + numeroFila + "").hide("fast");//ELIMINANDO LA FILA DE LA TABLA

            } else {
                swal(
                        'Error',
                        'No se ha podido eliminar el Sub-Contrato. Primero elimine los usuarios o los documentos asociados',
                        'error'
                        );
            }
        });

    }, function (dismiss) {

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'El Sub-Contrato no fue eliminado',
                    'error'
                    );
        }
    });
}
