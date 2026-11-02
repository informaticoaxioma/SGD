<?php

require_once '../../data/Documento.php';
require_once '../../data/Usuario.php';
require_once '../../data/Bitacora.php';

$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];
$serviceDocumento = new Documento();

if ($_GET) {
    $idDocumento = htmlspecialchars($_GET['idDocumento']);
    $documento = $serviceDocumento->getDocumentoPorID($idDocumento);
}

$fechaActual = $serviceDocumento->obtenerFechaActual();




if ($_POST) {

    $idDocumento = htmlspecialchars($_POST['idDocumento']);
    $emisor = htmlspecialchars($_POST['emisor']);
    $fechaEmision = $serviceDocumento->formatoFechaGuardarDB(htmlspecialchars($_POST['fechaRespuesta']));
    $asunto = htmlspecialchars($_POST['asunto']);
    $detalle = htmlspecialchars($_POST['detalle']);

    $estadoDocumento = isset($_POST['cerrarDoc']) ? htmlspecialchars($_POST['cerrarDoc']) : "";

    $serviceBitacora = new Bitacora(); //instanceando servicio
    $bitacora = new Bitacora(); //instanceando bitacora

    $bitacora->setEmisor($emisor); //seteando la bitacoraa
    $bitacora->setFechaEmision($fechaEmision);
    $bitacora->setAsunto($asunto);
    $bitacora->setDetalleRespuesta($detalle);
    $bitacora->setIdDocumento($idDocumento);

    $idPerfil = method_exists($usuarioSession, 'getIdPerfil') ? (int)$usuarioSession->getIdPerfil() : (isset($_SESSION['id_perfil']) ? (int)$_SESSION['id_perfil'] : 0);
    $idContrato = method_exists($usuarioSession, 'getIdContrato') ? (int)$usuarioSession->getIdContrato() : (isset($_SESSION['id_contrato']) ? (int)$_SESSION['id_contrato'] : 0);
    $ocultarCierre = ($idPerfil === 7 && $idContrato === 77);

    if ($estadoDocumento == 2 && !$ocultarCierre) {//cambiar el estado del documento a cerrado
        $documento = new Documento();
        $documento->setIdDocumento($idDocumento);
        $documento->setIdEstadoDoc($estadoDocumento);
        //actualizando estado
        $documento->actualizarDocumento($documento, 1);
    }

    echo $serviceBitacora->ingresarBitacora($bitacora); //ingresando bitacora
}