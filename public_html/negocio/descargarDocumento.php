<?php

require_once '../data/Documento.php';
$serviceDocumento = new Documento();

if ($_POST) {

    $idDoc = htmlspecialchars($_POST['idDocumento']);
    $doc = $serviceDocumento->getDocumentoPorID($idDoc);
    $dbMime = $doc[0]['mime_documento'];
    $extension = $dbMime;
    if (strpos($dbMime, '/') !== false) {
        $parts = explode('/', $dbMime);
        $extension = end($parts);
    }
    $extension = strtolower($extension);

    $mime = $dbMime;
    if (strpos($dbMime, '/') === false) {
        switch ($extension) {
            case 'pdf':
                $mime = 'application/pdf';
                break;
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            default:
                $mime = 'application/octet-stream';
        }
    }

    $nombreDocumento = $doc[0]['nombre_documento'];
    $nombreAux = explode(".", $nombreDocumento);
    $nombreFormat = str_replace(".", "_", $nombreAux[0]);
    $nombreFormat = str_replace(" ", "_", $nombreFormat);
    $nombreFormat = str_replace(",", "_", $nombreFormat);

    $nombrefinal = $nombreFormat . "." . $extension;
    $contenido = $doc[0]['documento'];

    header("Content-type: $mime");
    header('Content-Disposition: attachment; filename="' . $nombrefinal . '"');

    echo $contenido;
}

