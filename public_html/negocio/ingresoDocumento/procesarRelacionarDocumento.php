<?php
require_once '../../data/DocumentoRelacionado.php';
require_once '../../data/DetalleDocumento.php';
session_start();
$serviceDetalleDocumento = new DetalleDocumento();

if ($_GET) {

    $idDocumentoRel = htmlspecialchars($_GET['idDocRelacionado']);

    if (isset($_SESSION['documentosRelacionadosEntrada'])) {
        array_push($_SESSION['documentosRelacionadosEntrada'], $idDocumentoRel);
    }

    if (isset($_SESSION['documentosRelacionadosSalida'])) {

        array_push($_SESSION['documentosRelacionadosSalida'], $idDocumentoRel);
    }


    if (isset($_SESSION['documentosRelacionadosHitos'])) {

        array_push($_SESSION['documentosRelacionadosHitos'], $idDocumentoRel);
    }

    if (isset($_SESSION['documentosRelacionadosCargaM'])) {

        array_push($_SESSION['documentosRelacionadosCargaM'], $idDocumentoRel);
    }

    if (isset($_SESSION['documentosRelacionadosDetalle'])) {

        array_push($_SESSION['documentosRelacionadosDetalle'], $idDocumentoRel);
    }
}

$documentosRelacionados = array();

if (isset($_SESSION['documentosRelacionadosEntrada'])) {


    for ($i = 0; $i < count($_SESSION['documentosRelacionadosEntrada']); $i++) {
        $idDocumento = $_SESSION['documentosRelacionadosEntrada'][$i];

        $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
        array_push($documentosRelacionados, $documento);
    }
}

if (isset($_SESSION['documentosRelacionadosSalida'])) {

    for ($i = 0; $i < count($_SESSION['documentosRelacionadosSalida']); $i++) {
        $idDocumento = $_SESSION['documentosRelacionadosSalida'][$i];
        $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
        array_push($documentosRelacionados, $documento);
    }
}


if (isset($_SESSION['documentosRelacionadosHitos'])) {

    for ($i = 0; $i < count($_SESSION['documentosRelacionadosHitos']); $i++) {
        $idDocumento = $_SESSION['documentosRelacionadosHitos'][$i];
        $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
        array_push($documentosRelacionados, $documento);
    }
}


if (isset($_SESSION['documentosRelacionadosCargaM'])) {

    for ($i = 0; $i < count($_SESSION['documentosRelacionadosCargaM']); $i++) {
        $idDocumento = $_SESSION['documentosRelacionadosCargaM'][$i];
        $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
        array_push($documentosRelacionados, $documento);
    }
}



if (isset($_SESSION['documentosRelacionadosDetalle'])) {

    for ($i = 0; $i < count($_SESSION['documentosRelacionadosDetalle']); $i++) {
        $idDocumento = $_SESSION['documentosRelacionadosDetalle'][$i];
        $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
        array_push($documentosRelacionados, $documento);
    }
}

foreach ($documentosRelacionados as $d) : 
  echo $d->getIdDocumento(); 
endforeach;
?>

    
