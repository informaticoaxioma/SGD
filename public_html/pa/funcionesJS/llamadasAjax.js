//-------------------------------//
//      Modulo Configuracion    //
//-----------------------------//
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


//----------------------------//
//          Usuarios         //
//--------------------------//

//ENCARGADO AGREGAR USUARIOS GG
function cargarIngresarUsuarioEncargado() {

    $.get("configuracion/usuario/ingresarUsuarioEncargado.php", function (r) {
        $.getScript("funcionesJS/configuracion/funcionesUsuariosEncargado.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}


//ENCARGADO ACTUALIZAR USUARIO Y CONTRASEÑAS GG
function cargarActualizarUsuariosEncargado() {

    $.get("configuracion/usuario/actualizarUsuariosEncargado.php", function (r) {
        $.getScript("funcionesJS/configuracion/funcionesActualizarUsuariosEncargado.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

function cargarIngresarUsuario() {

    $.get("configuracion/usuario/ingresarUsuario.php", function (r) {
        $.getScript("funcionesJS/configuracion/funcionesUsuarios.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

function cargarActualizarEliminarUsuario() {

    $.get("configuracion/usuario/actualizarEliminarUsuario.php", function (r) {

        $.getScript("funcionesJS/configuracion/funcionesUsuarios.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();


    });
}

//----------------------------------//
//      Administrar Contratos      //
//--------------------------------//

function cargarMantenerContratos() {

    $.get("configuracion/contratos/administrarContratos.php", function (r) {

        $.getScript("funcionesJS/configuracion/funcionesAdmContratos.js");
        $.getScript("funcionesJS/cargarCalendario.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();


    });
}

//--------------------------------//
//      Administrar Cargos       //
//------------------------------//
function cargarMantenerCargos() {

    $.get("configuracion/cargos/administrarCargos.php", function (r) {
        $.getScript("funcionesJS/configuracion/funcionesAdmCargosYPerfiles.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();


    });
}

//----------------------------//
//   Administrar Perfiles    //
//--------------------------//
function cargarMantenerPerfiles() {

    $.get("configuracion/perfiles/administrarPerfiles.php", function (r) {
        $.getScript("funcionesJS/configuracion/funcionesAdmCargosYPerfiles.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();
    });
}

//----------------------------//
//    ADMINISTRAR PERFIL     //
//--------------------------//
function cargarPerfil() {

    $.post("configuracion/usuario/actualizarPerfil.php", function (r) {

        $.getScript("funcionesJS/configuracion/funcionesUsuarios.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

//-----------------------------//
// ADMINISTRAR TIPO DOCUMENTO //
//---------------------------//
function cargarMantenerTipoDoc() {

    $.post("configuracion/tipoDocumentos/administrarTipoDoc.php", function (r) {

        $.getScript("funcionesJS/configuracion/funcionesAdmTipoDoc.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

//---------------------------//
//  ADMINISTRAR ENTIDADES   //
//-------------------------//
function cargarMantenerEntidades() {

    $.post("configuracion/entidades/admEntidades.php", function (r) {

        $.getScript("funcionesJS/configuracion/funcionesAdmEntidades.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}


//----------------------------//
// MODULO INGRESO DOCUMENTOS //
//--------------------------//

function cargarIngresarEntrada() {

    $.get("../negocio/eliminarSessionDocumentos.php");

    $.get("ingresoDocumentos/ingresarEntrada.php", function (r) {

        $.getScript("funcionesJS/ingresarDocumentos/funcionesIngresarDocumentos.js");
        $.getScript("funcionesJS/cargarCalendario.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

function cargarIngresarSalida() {
    $.get("../negocio/eliminarSessionDocumentos.php");

    $.get("ingresoDocumentos/ingresarSalida.php", function (r) {

        $.getScript("funcionesJS/ingresarDocumentos/funcionesIngresarDocumentos.js");
        $.getScript("funcionesJS/cargarCalendario.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

function cargarCargaMasivaRegistro() {  
    $.get("../negocio/eliminarSessionDocumentos.php");

    $.get("ingresoDocumentos/cargaMasivaRegistro.php", function (r) {

        $.getScript("funcionesJS/ingresarDocumentos/funcionesIngresarDocumentos.js");
        $.getScript("funcionesJS/cargarCalendario.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

function cargarCargaArchivosRestantes() {
    bloquearPantalla();
    $.get("../negocio/eliminarSessionDocumentos.php");

    $.get("ingresoDocumentos/cargaArchivosRestantes.php", function (r) {

        $.getScript("funcionesJS/ingresarDocumentos/funcionesIngresarDocumentos.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

        desbloquearPantalla();
    });


}


//**********************************//
//       HITOS CONTRACTUALES       //
//********************************//
function cargarHitosContractuales() {

    $.get("../negocio/eliminarSessionDocumentos.php");

    $.get("hitoContractual/hitosContractuales.php", function (r) {

        $.getScript("funcionesJS/hitoContractual/funcionCalendario.js");
        $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}


function cargarListarHitosContractuales() {

    $.get("hitoContractual/listarHitos.php", function (r) {

        $.getScript("funcionesJS/hitoContractual/funcionCalendario.js");
        $.getScript("funcionesJS/hitoContractual/funcionesHitos.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

//----------------------------//
//          BUSCADOR         //
//--------------------------//
function cargarBuscador() {

    $.get("../negocio/eliminarSessionDocumentos.php");

    $.get("buscador/buscador.php", function (r) {

        $.getScript("funcionesJS/cargarCalendario.js");
        $.getScript("funcionesJS/buscador/funcionesBuscador.js");

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

function cargaTutoriales() {

    $.get("videoTutoriales/tutoriales.php", function (r) {

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}
function cargaBases(idBas) {

    $.get("bases/bases.php?idBas=" + idBas, function (r) {

        $("#contenedorAjax").html(r);
        $("#headerInicio").empty();

    });
}

