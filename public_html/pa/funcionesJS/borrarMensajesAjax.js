$(document).ready(function () {
    //CODIGO PARA ESCONDER LOS MENSAJES QUE SE MUESTRAN EN AQUELLOS ELEMENTOS QUE HAN SIDO CARGADOS MEDIANTE AJAX
    $(":input").focus(function () {

        $(".mensajeExito").hide("fast");
        $(".mensajeError").hide("fast");
        $(".mensajeErrorMod").hide("fast");
        $(".mensajeExitoMod").hide("fast");

    });

});

