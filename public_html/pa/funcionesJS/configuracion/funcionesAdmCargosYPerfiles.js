$(document).ready(function () {

    $(":input").focus(function () {

        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");
        $(".mensajeErrorMod").hide("fast");
        $(".mensajeExitoMod").hide("fast");

    });

    $("#idArea").change(function () {
        if ($(this).val() != "") {
            $.get("../negocio/ajax/cargarContratoEnSelect.php", {"idArea": $(this).val()}, function (r) {

                $("#divContratos").html(r);
                $("#divContratos").show("fast");
            });
        } else {
            $("#divContratos").hide("fast");
        }

    });

//----------------------------------//
//          INGRESAR CARGO         //
//--------------------------------//

    $("#formIngresarCargo").validate({//VALIDANDO EL FORMULARIO
        rules: {
            nombreCargo: {
                required: true,
                letterswithbasicpunc: true,
                espacioBlanco: true
            },
            idContrato: {
                required: true
            }

        },
        messages: {
            nombreCargo: {
                required: "Por favor, ingrese cargo",
                letterswithbasicpunc: "Por favor, ingrese letras solamente",
                espacioBlanco: "Error, no rellene con espacios"
            },
            idContrato: {
                required: "Por favor, seleccione un contrato"
            }
        },
        submitHandler: function () {//REALIZANDO EL SUBMIT DEL FORMULARIO MEDIANTE AJAX

            var nombreCargo = $("#nombreCargo").val();
            var idContrato = $("#idContrato").val();

            $.post("../negocio/configuracion/cargos/procesarAdministrarCargos.php", {"nombreCargo": nombreCargo, "idContrato": idContrato, "flag": 1}, function (r) {//PETICION AJAX

                if (r != -1) {//SE LA OPERACION ES CORRECTA SE DESPLEGA EL MENSAJE DE EXITO

                    $(".mensajeExito").show("fast");
                    $("#nombreCargo").val("");

                    $.get("configuracion/cargos/tablaCargosAjax.php", function (r) {//CARGANDO LA TABLA INFERIOR CON LOS CAGOS PARA MODIFICAR O ACTUALIZAR

                        $.getScript("funcionesJS/borrarMensajesAjax.js");
                        $(".divAjax").html(r);//CARGANDO LA RESPUESTA

                    });

                } else {

                    $(".mensajeExito").hide();
                    $(".mensajeError").show();
                }

            });
        }

    });


    //----------------------------------//
    //        ADMINISTRAR PEFILES      //
    //--------------------------------//

    $("#formIngresarPerfil").validate({
        rules: {
            nombrePerfil: {
                required: true,
                letterswithbasicpunc: true,
                espacioBlanco: true,
                maxlength: 100
            }
        },
        messages: {
            required: "Por favor, ingrese perfil",
            letterswithbasicpunc: "Por favor, ingrese letras solamente",
            espacioBlanco: "Error, no rellene con espacios",
            maxlength: "Error, límite de caracteres excedido"

        },
        submitHandler: function () {

            var perfil = $("#nombrePerfil").val();

            $.post("../negocio/configuracion/perfiles/procesarAdministrarPerfiles.php", {"perfil": perfil, "flag": 1},
                    function (r) {

                        if (r == 1) {

                            $(".mensajeExito").show("fast");
                            $("#nombrePerfil").val("");
                            //cargando tabla con nuevos perfiles
                            $.get("configuracion/perfiles/tablaPerfilesAjax.php", function (r) {

                                $.get("funcionesJS/borrarMensajesAjax.js");
                                $(".divAjax").html(r);

                            });
                        }

                    });


        }


    });


});

function actualizarCargo(idCargo, nombreCargo) {//metodo que actualiza el cargo segun id

    var cargo = $("#" + nombreCargo + "").val();

    $.post("../negocio/configuracion/cargos/procesarAdministrarCargos.php", {"idCargo": idCargo, "nombreCargo": cargo, "flag": 2},
            function (r) {

                if (r == 1) {//si la operacion es correcta

                    swal(
                            '¡Cargo Actualizado Exitosamente!',
                            '',
                            'success'
                            );


                } else {//si no lo es
                    swal(
                            '¡Error, no se ha podido actualizar el Cargo',
                            '',
                            'Error');
                }


            });

}

function eliminarCargo(idCargo, fila) {

    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Esta seguro que desea eliminar el Cargo?',
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
        $.post("../negocio/configuracion/cargos/procesarAdministrarCargos.php", {"idCargo": idCargo, "flag": 3}, function (r) {
           
            if (r == '1') {
                swal(
                        '¡Cargo Eliminado Exitosamente!',
                        '',
                        'success'
                        );
                $("#" + fila + "").hide();

            } else {
                swal(
                        'Error',
                        'No se ha podido eliminar el Cargo ya que hay entidades asociadas a el',
                        'error'
                        );
            }
        });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'El Cargo no fue eliminado',
                    'error'
                    );
        }
    });

}

//----------------------------//
//    ADMINISTRAR PERFILES   //
//--------------------------//
function actualizarPerfil(idPerfil, nombrePerfil) {//metodo que actualiza el cargo segun id

    var perfil = $("#" + nombrePerfil + "").val();

    $.post("../negocio/configuracion/perfiles/procesarAdministrarPerfiles.php", {"idPerfil": idPerfil, "nombrePerfil": perfil, "flag": 2},
            function (r) {

                if (r == 1) {//si la operacion es correcta
                    swal(
                            '¡Perfil Actualizado Exitosamente!',
                            '',
                            'success'
                            );



                } else {//si no lo es

                    swal(
                            '¡Error, no ha podido eliminar el Perfil',
                            '',
                            'error'

                            );
                }


            });

}

function eliminarPerfil(idPerfil, fila) {

    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Está seguro que desea eliminar el Perfil?',
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
        $.post("../negocio/configuracion/perfiles/procesarAdministrarPerfiles.php", {"idPerfil": idPerfil, "flag": 3}, function (r) {

            if (r == '1') {
                swal(
                        '¡Entidad Eliminada Exitosamente!',
                        '',
                        'success'
                        );
                $("#" + fila + "").hide("fast");//escondiendo la fila de la tabla

            } else {
                swal(
                        'Error',
                        'No se ha podido eliminado la entidad',
                        'error'
                        );
            }
        });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'La entidad no fue eliminada',
                    'error'
                    );
        }
    });

}