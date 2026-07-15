$(document).ready(function () {

    $(":input").focus(function () {

        $(".mensajeError").fadeOut("fast");
        $(".mensajeExito").fadeOut("fast");

    });

    $("#formRecuperarContrasena").validate({
        rules: {
            correo: {
                required: true,
                email: true
            }
        },
        messages: {
            correo: {
                required: "Por favor, ingrese Correo",
                email: "Error, formato incorrecto"
            }
        },
        submitHandler: function () {

            var correo = $("#correo").val();

            $.post("negocio/procesarRecuperarContrasena.php", {"correo": correo}, function (r) {
                if (r != -1) {

                    $("#correo").val("");
                    $(".mensajeError").fadeOut("fast");
                    $(".mensajeExito").show("fast");

                } else {

                    $(".mensajeExito").fadeOut("fast");
                    $(".mensajeError").show("fast");
                }
            });


        }



    });




});