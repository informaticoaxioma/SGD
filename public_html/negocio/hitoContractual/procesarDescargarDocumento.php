<?php

require_once '../../data/Documento.php';
$serviceDocumento = new Documento();

$idDoc = htmlspecialchars($_POST['idDocumento']);
$doc = $serviceDocumento->getDocumentoPorID($idDoc);
$dbMime = $doc[0]['mime_documento'];
$mime = $dbMime;
if (strpos($dbMime, '/') === false) {
    $extension = strtolower($dbMime);
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
$contenido = $doc[0]['documento'];

header("Content-type: $mime");
header("Content-Disposition: attachment; filename=" . $nombreDocumento);

echo $contenido;
