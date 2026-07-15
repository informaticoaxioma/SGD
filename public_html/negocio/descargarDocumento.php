<?php

require_once '../data/Documento.php';
$serviceDocumento = new Documento();

if ($_POST) {

    $idDoc = htmlspecialchars($_POST['idDocumento']);
    $doc = $serviceDocumento->getDocumentoPorID($idDoc);
    $mime = $doc[0]['mime_documento'];
    $nombreDocumento = $doc[0]['nombre_documento'];
    $nombreAux = explode(".", $nombreDocumento);
    $nombreFormat = "";
    for ($i = 0; $i < (count($nombreAux) - 1); $i++) {
        $nombreFormat.=$nombreAux[$i];
    }

    $nombreFormat = str_replace(".", "_", $nombreAux[0]);
    $nombreFormat = str_replace(" ", "_", $nombreFormat);
    $nombreFormat = str_replace(",", "_", $nombreFormat);

    // If mime is a full type like application/pdf, use the extension part for filename
    $extension = $mime;
    if (strpos($mime, '/') !== false) {
        $parts = explode('/', $mime);
        $extension = end($parts);
    }

    $nombrefinal = $nombreFormat . "." . $extension;
    $contenido = $doc[0]['documento'];

    header("Content-type: $mime");
    header('Content-Disposition: attachment; filename="' . $nombrefinal . '"');

    echo $contenido;
}

