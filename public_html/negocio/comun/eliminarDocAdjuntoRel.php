<?php

require_once '../../data/DocumentoRelacionado.php';
require_once '../../data/Adjunto.php';
require_once '../../data/HitoDocumento.php';
require_once '../../data/Seguimiento.php';

$serviceAdjunto = new Adjunto();
$serviceDocRelacionado = new DocumentoRelacionado();
$serviceHitoDoc = new HitoDocumento();
$serviceSeguimiento = new Seguimiento();

if ($_POST) {

    $flag = htmlspecialchars($_POST['flag']);

    switch ($flag) {

        case 1://ELIMINAR ADJUNTOS DOCUMENTOS
            $idDocAdjunto = htmlspecialchars($_POST['idDoc']);

            echo $serviceAdjunto->eliminarAdjuntoPorID($idDocAdjunto);

            break;

        case 2://ELIMINAR DOCUMENTOS RELACIONADOS
            $idDocumento = htmlspecialchars($_POST['idDoc']);

            echo $serviceSeguimiento->eliminarSeguimientoPorDocumento($idDocumento);

            break;

        case 3://ELIMINAR DOCUMENTOS RELACIONADOS HITOS
            $idDocHito = htmlspecialchars($_POST['idDoc']);

            echo $serviceHitoDoc->eliminarDocHitoPorID($idDocHito);

            break;
    }
}