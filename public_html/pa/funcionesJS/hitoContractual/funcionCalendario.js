$(document).ready(function () {

    $('#calendar').fullCalendar({
        locale: 'es',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay'
        },
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        selectable: true,
        selectHelper: true,
        events: "../negocio/hitoContractual/cargarEventosCalendario.php",
        loading: function (bool) {
            if (bool)
                $('#loading').show();
            else
                $('#loading').hide();
        },
        select: function (start, end) {

            $('#ModalAdd #fechaEntrega').val(moment(start).format('DD-MM-YYYY'));
            $('#ModalAdd').modal('show');
        },
        eventRender: function (event, element) {
            element.bind('dblclick', function () {

                $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");

                $('#ModalEdit').modal('show');
            });
        },
        eventClick: function (event, element) {//ACCION A REALIZAR AL HACER CLICK EN EL EVENTO

            $.get("../negocio/hitoContractual/actualizarHitoAjax.php", {"idHito": event.id}, function (r) {

                $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");
                $.getScript("funcionesJS/cargarCalendario.js");
                $.getScript("funcionesJS/funcionesComunes.js");
                $("#divAjaxHitoEdit").html(r);
                $("#idHito").val(event.id);//ASIGNANDO IDHITO AL INPUT

            });

            $('#ModalEdit').modal('show');
        }
    });

});
