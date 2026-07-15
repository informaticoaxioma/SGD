<?php

require_once '../data/Documento.php';
$serviceDocumento = new Documento();

if ($_POST) {

    $idDoc = htmlspecialchars($_POST['idDocumento']);
    $doc = $serviceDocumento->getDocumentoPorID($idDoc);
    
    if (!$doc) {
        die("Documento no encontrado");
    }
    $nombreDocumento = $doc[0]['nombre_documento'];
    $nombreAux = explode(".", $nombreDocumento);
    $nombreFormat = "";
    for ($i = 0; $i < (count($nombreAux) - 1); $i++) {
        $nombreFormat.=$nombreAux[$i];
    }

    $nombreFormat = str_replace(".", "_", $nombreAux[0]);
    $nombreFormat = str_replace(" ", "_", $nombreFormat);
    $nombreFormat = str_replace(",", "_", $nombreFormat);
    $nombrefinal = $nombreFormat . "." . $mime;
    $contenido = $doc[0]['documento'];
    
    $extension = $doc[0]['mime_documento'];
    
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

    
    header("Content-Type: $mime");
    header("Content-Disposition: inline; filename=\"" . $nombrefinal . "\"");
    header("Content-Length: " . strlen($contenido));

    echo $contenido;
    exit;
}

