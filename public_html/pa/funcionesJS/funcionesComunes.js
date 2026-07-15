
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

        $.post("../negocio/comun/eliminarDocAdjuntoRel.php", {"idDoc": idDoc, "flag": flag}, function (r) {

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

            $.post("../negocio/comun/relacionarDocumentoDetalle.php", {"idDocumento": idDocumento}, function (r) {
                if (r == 1) {

                    $("#tablaDocsRelacionados").empty();
                    $("#btnRelacionarDocDetalle").hide("fast");
                    swal('Documento(s) relacionado(s) exitosamente', '', 'success');
                    $.get("../negocio/comun/cargarDocRelacionadosDetalle.php", {"idDocumento": idDocumento}, function (r) {
                        $(".divDocRelacionados").html(r);
                    });
                }
            });
            break;

        case 2:

            $.post("../../negocio/comun/relacionarDocumentoDetalle.php", {"idDocumento": idDocumento}, function (r) {

                if (r == 1) {

                    $("#tablaDocsRelacionados").empty();
                    $("#btnRelacionarDocDetalle").hide("fast");
                    swal('Documento(s) relacionado(s) exitosamente', '', 'success');
                    $.get("../../negocio/comun/cargarDocRelacionadosDetalle.php", {"idDocumento": idDocumento}, function (r) {
                        $(".divDocRelacionados").html(r);
                    });
                }
            });

            break;
    }

}
