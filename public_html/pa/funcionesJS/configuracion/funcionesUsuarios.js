$(document).ready(function () {

    $(":input").focus(function () {
        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");
    });

    $("#idArea").change(function () {

        var idArea = $(this).val();

        if (idArea === "") {
            $("#divIdContrato").hide("fast");
        } else {
            $.get("../negocio/cargarContrato.php", {"idArea": idArea}, function (r) {
                $.getScript("funcionesJS/configuracion/funcionesUsuarios.js");
                $("#contratosAjax").html(r);
                $("#divIdContrato").show("fast");

            });
        }
    });

    $("#idContrato").change(function () {

        var idContrato = $(this).val();

        if (idContrato === "") {
            $("#divSubContratos").hide("fast");
        } else {
            $.get("../negocio/ajax/cargarSubContratos.php", {"idContrato": idContrato}, function (r) {

                $("#subContratosAjax").html(r);
                $("#divSubContratos").show("fast");

            });
        }
    });

    //validacion de formulario de ingreso de usuarios
    $("#formIngresarUsuario").validate({
        rules: {
            nombre: {
                required: true,
                espacioBlanco: true,
                letterswithbasicpunc: true,
                maxlength: 30
            },
            apellidoP: {
                required: true,
                espacioBlanco: true,
                letterswithbasicpunc: true,
                maxlength: 30
            },
            apellidoM: {
                letterswithbasicpunc: true,
                maxlength: 30
            },
            nombreUsuario: {
                required: true,
                espacioBlanco: true,
                maxlength: 30,
                remote: {
                    url: "../negocio/verificarNombreUsuario.php",
                    type: 'GET',
                    data: {
                        nombreUsuario: function () {
                            return $('#nombreUsuario').val();
                        }
                    }
                }
            },
            contrasena: {
                required: true,
                maxlength: 32
            },
            reContrasena:{
                equalTo:"#contrasena"
            },
            idArea: {
                required: true
            },
            idContrato: {
                required: true
            },
            idSubContratos: {
                required: true
            },
            idPerfil: {
                required: true
            }
        },
        messages: {
            nombre: {
                required: "Por favor, ingrese nombre",
                espacioBlanco: "Error, no rellene con espacios",
                letterswithbasicpunc: "Por favor, ingrese letras solamente",
                maxlength: "Error, límite de caractéres excedidos"
            },
            apellidoP: {
                required: "Por favor, ingrese Apellido Paterno",
                espacioBlanco: "Error, no rellene con espacios",
                letterswithbasicpunc: "Por favor, ingrese letras solamente",
                maxlength: "Error, límite de caractéres excedidos"
            },
            apellidoM: {
                espacioBlanco: "Error, no rellene con espacios",
                letterswithbasicpunc: "Por favor, ingrese letras solamente",
                maxlength: "Error, límite de caractéres excedidos"
            },
            nombreUsuario: {
                required: "Por favor, ingrese nombre de usuario",
                espacioBlanco: "Error, no rellene con espacios",
                maxlength: "Error, límite de caractéres excedidos",
                remote: jQuery.validator.format("Nombre de usuario no disponible")
            },
            contrasena: {
                required: "Por favor, ingrese contraseña",
                maxlength: "Error, límite de caractéres excedidos"
            },
            reContrasena:{
                equalTo:"Error, las contraseñas no coinciden"
            },
            idArea: {
                required: "Por favor, seleccione Área"
            },
            idSubContratos: {
                required: "Por favor, seleccione Sub-Contrato(s) "
            },
            idContrato: {
                required: "Por favor, seleccione Contrato"
            },
            idPerfil: {
                required: "Por favor, seleccione Perfil"
            }
        },
        submitHandler: function () {

            var nombre = $("#nombre").val();
            var apellidoP = $("#apellidoP").val();
            var apellidoM = $("#apellidoM").val();
            var correo = $("#correo").val();
            var nombreUsuario = $("#nombreUsuario").val();
            var contrasena = $("#contrasena").val();
            var idArea = $("#idArea").val();
            var idPerfil = $("#idPerfil").val();
            var idContrato = $("#idContrato").val();
            var idUsuarioSubContratos = $("#idSubContratos").val();

            //imagen de carga
            $(".gifCarga").fadeIn("fast");

            $.post("../negocio/configuracion/usuarios/procesarIngresarUsuario.php",
                    {"nombre": nombre, "apellidoP": apellidoP, "apellidoM": apellidoM, "correo": correo, "nombreUsuario": nombreUsuario,
                        "contrasena": contrasena, "idArea": idArea, "idPerfil": idPerfil, "idContrato": idContrato, "idUsuarioSubContratos": idUsuarioSubContratos},
                    function (r) {

                        $(".gifCarga").fadeOut("fast");//escondiendo gif de carga

                        if (r == 1) {
                            //mostrando mensajes
                            $(".mensajeError").hide("fast");
                            $(".mensajeExito").show("fast");
                            //limpiando el formulario
                            $("#formIngresarUsuario").each(function () {
                                this.reset();
                            });
                            $("#divIdContrato").hide("fast");
                            $("#divSubContratos").hide("fast");

                        } else {
                            $(".mensajeError").show("fast");
                            $(".mensajeExito").hide("fast");
                        }

                    });
        }

    });

    //-----------------------------------------//
    //              EDITAR PERFIL             //
    //---------------------------------------//
    $("#btnEditar").click(function () {//desbloquea los campos disabled

        var flagEditar = $("#flagEditar").val();

        if (flagEditar == 1) {

            $(":text").removeAttr("disabled");
            $(":input[type=email]").removeAttr("disabled");
            $("#btnActualizarPerfil").removeAttr("disabled");
            $("#flagEditar").val("-1");

        } else {

            $(":text").prop('disabled', true);
            $(":input[type=email]").prop('disabled', true);
            $("#btnActualizarPerfil").prop('disabled', true);
            $("#flagEditar").val("1");
        }
    });

    //------ACTUALIZAR PERFIL------//
    $("#formActualizarPerfil").validate({
        rules: {
            nombre: {
                required: true,
                espacioBlanco: true,
                maxlength: 30,
                letterswithbasicpunc: true
            },
            apellidoP: {
                required: true,
                espacioBlanco: true,
                maxlength: 30,
                letterswithbasicpunc: true
            },
            apellidoM: {
                required: true,
                espacioBlanco: true,
                maxlength: 30,
                letterswithbasicpunc: true
            },
            correo: {
                required: false,
                espacioBlanco: true,
                maxlength: 30,
                email: true
            }


        },
        messages: {
            nombre: {
                required: "Por favor, ingrese nombre",
                espacioBlanco: "Error, no rellene con espacios",
                maxlength: "Error, límite de caracteres excedido",
                letterswithbasicpunc: "Por favor, ingrese letras solamente"
            },
            apellidoP: {
                required: "Por favor, ingrese Apellido Paterno",
                espacioBlanco: "Error, no rellene con espacios",
                maxlength: "Error, límite de caracteres excedido",
                letterswithbasicpunc: "Por favor, ingrese letras solamente"
            },
            apellidoM: {
                required: "Por favor, ingrese Apellido Materno",
                espacioBlanco: "Error, no rellene con espacios",
                maxlength: "Error, límite de caracteres excedido",
                letterswithbasicpunc: "Por favor, ingrese letras solamente"
            },
            correo: {
                required: "Por favor, ingrese Correo",
                espacioBlanco: "Error, no rellene con espacios",
                maxlength: "Error, límite de caracteres excedido",
                email: "Error, formato de correo no válido"
            }
        },
        submitHandler: function (r) {
            var idUsuario = $("#idUsuario").val();
            var nombre = $("#nombre").val();
            var apellidoP = $("#apellidoP").val();
            var apellidoM = $("#apellidoM").val();
            var correo = $("#correo").val();

            //enviado post
            $.post("../negocio/configuracion/usuarios/procesarActualizarPerfil.php", {"nombre": nombre, "apellidoP": apellidoP, "apellidoM": apellidoM, "correo": correo, "idUsuario": idUsuario, "flag": 1},
                    function (r) {

                        if (r == "1") {

                            $(".mensajeError").hide("fast");
                            $(".mensajeExito").show("fast");

                            //deshabilitando campos
                            $(":text").prop('disabled', true);
                            $(":input[type=email]").prop('disabled', true);
                            $("#btnActualizarPerfil").prop('disabled', true);
                            $("#flagEditar").val("1");

                        } else {

                            $(".mensajeExito").hide("fast");
                            $(".mensajeError").show("fast");

                        }


                    });

        }
    });


    //---------------------------------//
    //      CAMBIAR CONTRASEÑA        //
    //-------------------------------//
    $("#formCambiarContrasena").validate({
        rules: {
            contrasenaN: {
                required: true
            },
            reContrasena: {
                required: true,
                equalTo: "#contrasenaN"
            }

        },
        messages: {
            contrasenaN: {
                required: "Por favor, ingrese contrasena nueva"
            },
            reContrasena: {
                required: "Por favor, confirme contraseña",
                equalTo: "Error, las contraseñas no coinciden"
            }

        },
        submitHandler: function () {

            var idUsuario = $("#idUsuario").val();
            var contrasena = $("#contrasenaN").val();

            $.post("../negocio/configuracion/usuarios/procesarActualizarPerfil.php", {"idUsuario": idUsuario, "contrasena": contrasena, "flag": 2},
                    function (r) {

                        if (r == "1") {

                            $(".mensajeErrorMod").hide("fast");
                            $(".mensajeExitoMod").show("fast");

                            $("#formCambiarContrasena").each(function () {

                                this.reset();//reseteando formulario

                            });

                        } else {

                            $(".mensajeExitoMod").hide("fast");
                            $(".mensajeErrorMod").show("fast");

                        }


                    });
        }

    });



});



function eliminarUsuario(idUsuario, idFila) {//metodo para eliminar un usuario

    swal({//seteando el plugin sweetAlert
        title: '¿Está seguro que desea eliminar al usuario?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'No, cancelar',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
    }).then(function () {//funcion a realizar
        $.post("../negocio/configuracion/usuarios/procesarActualizarEliminarUsuarios.php", {"idUsuario": idUsuario, "flag": 2},
                function (r) {
                    if (r == 1) {//si la respuesta es 1 se borra la fila de a capa de presentacion y se despliega un mensaje

                        $("#" + idFila + "").hide("fast");
                        swal(
                                '¡Usuario Eliminado Exitosamente!',
                                '',
                                'success'
                                );
                    } else {//sino se despliega un mensaje de error
                        swal(
                                'Error',
                                'No se ha podido eliminar el usuario, es posible que tenga documentos o una entrada en el log asociados',
                                'error'
                                );
                    }

                });
    }, function (dismiss) {//si se presiona la opcion cancelar se despliega el siguiente mensaje

        if (dismiss === 'cancel') {
            swal(
                    'Cancelado',
                    'El usuario no fue eliminado',
                    'error'
                    );
        }
    });

}

function actualizarUsuario(idUsuario, nombreUsuario, apellidoPUsuario, apellidoMUsuario, idCargoUsuario, idPerfilUsuario, idEstadoUsuario) {

    var nombre = $("#" + nombreUsuario + "").val();
    var apellidoP = $("#" + apellidoPUsuario + "").val();
    var apellidoM = $("#" + apellidoMUsuario + "").val();
    var idCargo = $("#" + idCargoUsuario + "").val();
    var idPerfil = $("#" + idPerfilUsuario + "").val();
    var idEstado = $("#" + idEstadoUsuario + "").val();

    $.post("../negocio/configuracion/usuarios/procesarActualizarEliminarUsuarios.php",
            {"idUsuario": idUsuario, "nombre": nombre, "apellidoP": apellidoP, "apellidoM": apellidoM, "idCargo": idCargo, "idPerfil": idPerfil, "idEstado": idEstado, "flag": 1},
            function (r) {
                if (r == 1) {

                    swal(
                            '¡Usuario actualizado exitosamente',
                            '',
                            'success'
                            );

                } else {
                    swal(
                            '!Error, no ha podido actualizar el usuario',
                            '',
                            'error');
                }

            });

}

function subContatoUser(idUsuario, idContrato, nombre, apellido){
    $.post("../negocio/cargarSubContratos.php",{"idContrato":idContrato, "idUsuario":idUsuario},function(r){
        $("#subcontrausuer").html(r);
        $("#SubConUserId").val(idUsuario);
        $("#nombreusuario").html(nombre+" "+apellido);
        $("#subcon").modal('show');
    });

}

function guardaSubContra(idSub, idUser){
    $.post("../negocio/configuracion/usuarios/procesarActualizarEliminarUsuarios.php",{"flag": 4,"idUsuario":idUser, "IdsSubContrato":idSub},function(r){
        alert(r);
    });
}


function filtroContratos(){
    var contrato = $("#cbxContrato option:selected").val();
    $.post("../negocio/configuracion/usuarios/procesarActualizarEliminarUsuarios.php",{"flag": 3,"contrato":contrato},function(r){
        $("#tablaContratos").html(r);
    });
}

