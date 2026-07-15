<?php

require_once '../../data/Entidad.php';
require_once '../../data/Documento.php';
require_once '../../data/DetalleDocumento.php';
require_once '../../data/Usuario.php';
require_once '../../data/TipoDocumento.php';
require_once '../../data/Zebra_Pagination.php';
require_once '../../data/HitoContractual.php';
require_once '../../data/Seguimiento.php';

$sessionUsuario = new Usuario();
session_start();
$sessionUsuario = $_SESSION['usuario'];
//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);


$serviceEntidad = new Entidad();
$serviceDocumento = new Documento();
$serviceDetalleDoc = new DetalleDocumento();
$serviceUsuario = new Usuario();
$serviceTipoDocumento = new TipoDocumento();
$serviceHito = new HitoContractual();
$serviceSeguimiento = new Seguimiento();

//CARGANDO SELECTS
$tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();

//inicializando variables
$numDocumento = "";
$numProvidencia = "";
$numProceso = "";
$fechaDocBuscar = "";
$fechaRecepcionBuscar = "";
$fechaPlazoBuscar = "";
$idTipoDoc = "";
$idRemitente = "";
$idDestinatario = "";
$idResponsable = "";
$idFlujo = "";
$materiaBuscar = "";

if ($_POST) {//CARGANDO VALORES PARA POST POR AJAX
    //INSTANCEANDO OBJETOS
    $documento = new Documento();
    $detalleDoc = new DetalleDocumento();
    $paginacion = new Zebra_Pagination();
    error_log("--------->Entre en Post");
    if (isset($_POST['page'])) {
        error_log("--------->Existe Page");
        $pagina = $_POST['page'];
        $paginacion->set_page($pagina);

        $numDocumento = htmlspecialchars($_POST['numDocumento']) != "" ? htmlspecialchars($_POST['numDocumento']) : '';
        $numProvidencia = htmlspecialchars($_POST['numProvidencia']) != "" ? htmlspecialchars($_POST['numProvidencia']) : '';
        $numProceso = htmlspecialchars($_POST['numProceso']) != "" ? htmlspecialchars($_POST['numProceso']) : '';
        $fechaDocBuscar = htmlspecialchars($_POST['fechaDocBuscar']) != "" ? htmlspecialchars($_POST['fechaDocBuscar']) : '';
        $fechaRecepcionBuscar = htmlspecialchars($_POST['fechaRecepcionBuscar']) != "" ? htmlspecialchars($_POST['fechaRecepcionBuscar']) : '';
        $fechaPlazoBuscar = htmlspecialchars($_POST['fechaPlazoBuscar']) != "" ? htmlspecialchars($_POST['fechaPlazoBuscar']) : '';
        $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']) != "" ? htmlspecialchars($_POST['idTipoDoc']) : '';
        $idRemitente = htmlspecialchars($_POST['idRemitente']) != "" ? htmlspecialchars($_POST['idRemitente']) : '';
        $idDestinatario = htmlspecialchars($_POST['idDestinatario']) != "" ? htmlspecialchars($_POST['idDestinatario']) : '';
        $idResponsable = htmlspecialchars($_POST['idResponsable']) != "" ? htmlspecialchars($_POST['idResponsable']) : '';
        $idFlujo = htmlspecialchars($_POST['idFlujo']) != "" ? htmlspecialchars($_POST['idFlujo']) : '';
        $materiaBuscar = isset($_POST['materiaBuscar']) != "" ? htmlspecialchars($_POST['materiaBuscar']) : '';

    } else {
        error_log("--------->NO existe Page");
        $pagina = "";
        if (isset($_POST['page'])) {
            $pagina = ltrim($_POST['page'], "0");
        }

        $paginacion->set_page($pagina);

        $numDocumento = htmlspecialchars($_POST['numDocumento']) != "" ? htmlspecialchars($_POST['numDocumento']) : '';
        $numProvidencia = htmlspecialchars($_POST['numProvidencia']) != "" ? htmlspecialchars($_POST['numProvidencia']) : '';
        $numProceso = htmlspecialchars($_POST['numProceso']) != "" ? htmlspecialchars($_POST['numProceso']) : '';
        $fechaDocBuscar = htmlspecialchars($_POST['fechaDocBuscar']) != "" ? htmlspecialchars($_POST['fechaDocBuscar']) : '';
        $fechaRecepcionBuscar = htmlspecialchars($_POST['fechaRecepcionBuscar']) != "" ? htmlspecialchars($_POST['fechaRecepcionBuscar']) : '';
        $fechaPlazoBuscar = htmlspecialchars($_POST['fechaPlazoBuscar']) != "" ? htmlspecialchars($_POST['fechaPlazoBuscar']) : '';
        $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']) != "" ? htmlspecialchars($_POST['idTipoDoc']) : '';
        $idRemitente = htmlspecialchars($_POST['idRemitente']) != "" ? htmlspecialchars($_POST['idRemitente']) : '';
        $idDestinatario = htmlspecialchars($_POST['idDestinatario']) != "" ? htmlspecialchars($_POST['idDestinatario']) : '';
        $idResponsable = htmlspecialchars($_POST['idResponsable']) != "" ? htmlspecialchars($_POST['idResponsable']) : '';
        $idFlujo = htmlspecialchars($_POST['idFlujo']) != "" ? htmlspecialchars($_POST['idFlujo']) : '';
        $materiaBuscar = htmlspecialchars($_POST['materiaBuscar']) != "" ? htmlspecialchars($_POST['materiaBuscar']) : '';

    }
    //SETEANDO OBJETOS
    //DOCUMENTO
    $documento->setFechaDocumento($fechaDocBuscar != "" ? $serviceDocumento->formatoFechaGuardarDB($fechaDocBuscar) : '');
    $documento->setFechaRecepcion($fechaRecepcionBuscar != "" ? $serviceDocumento->formatoFechaGuardarDB($fechaRecepcionBuscar) : '');
    $documento->setFechaPlazo($fechaPlazoBuscar != "" ? $serviceDocumento->formatoFechaGuardarDB($fechaPlazoBuscar) : '');
    $documento->setIdFlujo($idFlujo);
    
    //DETALLE DOCUMENTO
    $detalleDoc->setNumDocumento($numDocumento);
    $detalleDoc->setNumProvidencia($numProvidencia);
    $detalleDoc->setNumProceso($numProceso);
    $detalleDoc->setIdTipoDoc($idTipoDoc);
    $detalleDoc->setIdRemitente($idRemitente);
    $detalleDoc->setIdDestinatario($idDestinatario);
    $detalleDoc->setIdResponsable($idResponsable);
    $detalleDoc->setMateria($materiaBuscar);

    //SETEANDO PAGINACION

    $totalRegistros = $serviceDocumento->contarRegistrosPorBusqueda($documento, $detalleDoc, $sessionUsuario->getIdContrato(), $sessionUsuario->getIdPerfil());
    $resultados = 10;

    $paginacion->records($totalRegistros);
    $paginacion->records_per_page($resultados);

    //paginacion dinamica
    $inicio = $paginacion->get_page() - 1; //la pagina que estamos -1
    $inicio*=$resultados; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja

    error_log("------------------>Inicio: ".$inicio);
    error_log("------------------>Inicio: ".$pagina);
    $documentos = $serviceDocumento->buscarDocumentosPorFiltro($documento, $detalleDoc, $sessionUsuario->getIdContrato(), $inicio, $resultados, $sessionUsuario->getIdPerfil());

//tabla a cargar
    echo '<h3 class="colorAxioma blanco">Resultados</h3>';
    echo '<p class = "text-left">Total Documentos: ' . $totalRegistros . '</p>';
    echo '<table id="tablaResultadoBusqueda" name="tablaResultadoBusqueda" class="table table-hover table-striped"> ';
    echo '<thead>';
    echo '<tr>';
    echo '<th>N° Documento</th>';
    echo '<th>N° Providencia</th>';
    echo '<th>N° Proceso</th>';
    echo '<th>Fecha Documento</th>';
    echo '<th>Fecha Plazo</th>';
    echo '<th>Tipo Documento</th>';
    echo '<th>Materia</th>';
    echo '<th>Remitente</th>';
    echo '<th>Destinatario</th>';
    echo '<th>Responsable</th>';
    echo '<th>Descargar</th>';
    echo '<th>Detalle</th>';
    if ($sessionUsuario->getIdPerfil() == 1 || $sessionUsuario->getIdPerfil() == 3 || $sessionUsuario->getIdPerfil() == 9) :
        echo "<th>Eliminar</th>";
    endif;
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if (isset($documentos)):
        $c = 0;
        foreach ($documentos as $d) :
            //DATOS DE OTRAS TABLAS
            $tipoDoc = $serviceTipoDocumento->getTipoDocumentoPorId($d['id_tipo_doc']);
            $remitente = $serviceEntidad->getEntidadPorId($d['id_remitente']);
            $destinatario = $serviceEntidad->getEntidadPorId($d['id_destinatario']);
            $responsable = $serviceUsuario->getUsuarioPorId($d['id_responsable']);


            echo' <tr id="' . $c . '">';
            echo "<td>" . $d['num_documento'] . "</td>";
            echo "<td>" . $d['num_providencia'] . "</td>";
            echo "<td>" . $d['num_proceso'] . "</td>";
            echo "<td>" . $serviceDocumento->formatoFecha($d['fecha_documento']) . "</td>";
            echo "<td>" . $serviceDocumento->formatoFecha($d['fecha_plazo']) . "</td>";
            echo "<td>" . $tipoDoc->getTipoDocumento() . "</td>";
            echo "<td class='text-justify'>" . $d['materia'] . "</td>";
            echo "<td>" . $remitente->getNombreEntidad() . " " . $remitente->getApellidoEntidad() . "</td>";
            echo "<td>" . $destinatario->getNombreEntidad() . " " . $destinatario->getApellidoEntidad() . "</td>";

            if ($responsable != "") {
                echo "<td>" . $responsable->getNombre() . " " . $responsable->getApellidoP() . "</td>";
            } else {
                echo "<td class='rojo'> No Posee Responsable</td>";
            }

            echo "<td>";
            echo '<form action="../negocio/descargarDocumento.php" method="POST">';
            echo '<input type = "hidden" id = "idDocumento" name = "idDocumento" value = "' . $d['id_documento'] . '">';

            if ($d['nombre_documento'] != "") {//SI POSEE UN DOCUMENTO SE MUESTRA EL BOTON SINO SE DESPLIEGA UN MENSAJE
                echo '<button id = "btnDescargarArchivo" name = "btnDescargarArchivo" class = "btn btn-success btn-sm" ><i class = "fa fa-download"></i>&nbsp;
            Descargar</button>';
            } else {
                echo '<p class="rojo">No posee documento digital</p>';
            }
            echo '</form>';
            echo '</td>';
            echo '<td>';
            echo '<a href="buscador/detalleDocumentoBuscador.php?idDocumento=' . $d['id_documento'] . '" target="_blank" ><button id="btnDetalle" name="btnDetalle" class="btn btn-primary btn-sm" ><i class="fa fa-plus-circle"></i>&nbsp;Detalle</button></a>';
            echo '</td>';
            if ($sessionUsuario->getIdPerfil() == 1 || $sessionUsuario->getIdPerfil() == 3 || $sessionUsuario->getIdPerfil() == 9) :
                echo "<td>";
                echo ' <button onclick="eliminarDocumento(' . $d['id_documento'] . ',\'' . $c . '\')" id="btnEliminar" name="btnEliminar" class="btn btn-danger btn-sm" ><i class="fa fa-trash"></i>&nbsp;Eliminar</button>';
                echo "</td>";
            endif;
            echo '</tr>';

            $c++;
        endforeach;
    endif;

    echo '</tbody>';
    echo '</table>';
    echo $paginacion->render();
    unset($pagina);
} else {

//---------------------------//
//        SERVICIOS         //
//-------------------------//
    $serviceEntidad = new Entidad();
    $serviceDocumento = new Documento();
    $serviceDetalleDoc = new DetalleDocumento();
    $serviceUsuario = new Usuario();
    $serviceTipoDocumento = new TipoDocumento();

//CARGANDO SELECTS
    $tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();

    if ($sessionUsuario->getIdPerfil() == 1) {//SUPER ADMINISTRADOR
        $remitentes = $serviceEntidad->getEntidadesPorTipo(1, 0);
        $destinatarios = $serviceEntidad->getEntidadesPorTipo(2, 0);
        $responsables = $serviceUsuario->getUsuarios();
    } else {//OTROS USUARIOS
        $remitentes = $serviceEntidad->getEntidadesPorTipo(1, $sessionUsuario->getIdContrato());
        $destinatarios = $serviceEntidad->getEntidadesPorTipo(2, $sessionUsuario->getIdContrato());
        $responsables = $serviceUsuario->getUsuariosPorContrato($sessionUsuario->getIdContrato());
    }
}

