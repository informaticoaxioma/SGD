$(document).ready(function () {

    $(":input").focus(function () {
        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");
    });

    $("#formIngresarEntidad").validate({//INGRESANDO LA ENTIDAD
        rules: {//validando 
            nombreEntidad: {
                required: true,
                maxlength: 20,
                letterswithbasicpunc: true,
                espacioBlanco: true
            },
            apellidoEntidad: {
                required: true,
                maxlength: 30,
                letterswithbasicpunc: true,
                espacioBlanco: true
            },
            idCargo: {
                required: true
            },
            idTipoEntidad: {
                required: true
            },
            idContrato:{
                required: true
            }
        },
        messages: {
            nombreEntidad: {
                required: "Por favor, ingrese Nombre",
                maxlength: "Error, límite de caracteres excedido(20)",
                letterswithbasicpunc: "Error, ingrese letras solamente"
            },
            apellidoEntidad: {
                required: "Por favor, ingrese Apellido",
                maxlength: "Error, límite de caracteres excedido(30)",
                letterswithbasicpunc: "Error, ingrese letras solamente"
            },
            idCargo: {
                required: "Por favor, seleccione Cargo"
            },
            idTipoEntidad: {
                required: "Por favor, seleccione Tipo Entidad"
            },
            idContrato:{
                required: "Por favor, seleccione Contrato"
            }

        },
        submitHandler: function () {
            
            var nombreEntidad = $.trim($("#nombreEntidad").val());
            var apellidoEntidad = $.trim($("#apellidoEntidad").val());
            var idCargo = $("#idCargo").val();
            var idTipoEntidad = $("#idTipoEntidad").val();
            var idContrato =$("#idContrato").val();
            
             //enviado datos por ajax
            //flag para insertar(1), actualizar(2) o eliminar(3)
            $.post("../negocio/configuracion/entidades/procesarAdmEntidades.php", {"nombreEntidad": nombreEntidad, "apellidoEntidad": apellidoEntidad,
                "idCargo": idCargo, "idTipoEntidad": idTipoEntidad,"idContrato":idContrato ,"flag": 1}, function (r) {

                if (r != '-1') {

                    $(".mensajeError").hide("fast");
                    $(".mensajeExito").show("fast");

                    $("#formIngresarEntidad").each(function () {
                        this.reset();
                    });

                    //ajax
                    $.get("configuracion/entidades/tablaActualizarEntidadAjax.php", function (r) {

                        $.getScript("funcionesJS/configuracion/funcionesAdmEntidades.js");
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


function actualizarEntidad(idEntidad, nombre, apellido, idC, idTipoE) {


    var nombreEntidad = $("#" + nombre + "").val();
    var apellidoEntidad = $("#" + apellido + "").val();
    var idCargo = $("#" + idC + "").val();
    var idTipoEntidad = $("#" + idTipoE + "").val();
    //ejecutando el metodo
    $.post("../negocio/configuracion/entidades/procesarAdmEntidades.php", {"idEntidad": idEntidad, "nombreEntidad": nombreEntidad, "apellidoEntidad": apellidoEntidad, "idCargo": idCargo, "idTipoEntidad": idTipoEntidad, "flag": 2},
            function (r) {

                if (r == '1') {
                    swal(
                            '¡Entidad Actualizada Exitosamente!',
                            '',
                            'success'
                            );

                } else {
                    swal(
                            'Error',
                            'No se ha podido Actualizar la entidad',
                            'error'
                            );
                }
            });
}

function eliminarEntidad(idEntidad, fila) {

    swal({//SETEANDO PLUGIN SWEET ALERT
        title: '¿Esta seguro que desea eliminar la Entidad?',
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
        $.post("../negocio/configuracion/entidades/procesarAdmEntidades.php", {"idEntidad": idEntidad, "flag": 3}, function (r) {

            if (r == '1') {
                swal(
                        '¡Entidad Eliminada Exitosamente!',
                        '',
                        'success'
                        );
                $("#" + fila + "").hide();

            } else {
                swal(
                        'Error',
                        'No se ha podido eliminado la Entidad',
                        'error'
                        );
            }
        });
    }, function (dismiss) {//CANCELAR ACCION

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'La Entidad no fue eliminada',
                    'error'
                    );
        }
    });
}