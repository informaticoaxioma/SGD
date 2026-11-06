<?php

require_once '../data/Documento.php';
require_once '../data/Adjunto.php';
$serviceDocumento = new Documento();

if (isset($_GET['idAdjunto']) || isset($_POST['idAdjunto'])) {
    $idAdjunto = htmlspecialchars(isset($_POST['idAdjunto']) ? $_POST['idAdjunto'] : $_GET['idAdjunto']);
    $serviceAdjunto = new Adjunto();
    $adjunto = $serviceAdjunto->getAdjuntoPorId($idAdjunto);
    if ($adjunto && $adjunto->getIdAdjunto()) {
        $dbMime = (string)$adjunto->getMimeAdjunto();
        $extension = $dbMime;
        if (strpos($dbMime, '/') !== false) {
            $parts = explode('/', $dbMime);
            $extension = end($parts);
        }
        $extension = strtolower(trim($extension));

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

        $nombreAdjunto = $adjunto->getNombreAdjunto() ?: ('adjunto_' . (int)$idAdjunto);
        $contenidoAdj = $adjunto->getArchivoAdjunto();

        header("Content-type: $mime");
        header('Content-Disposition: attachment; filename="' . $nombreAdjunto . '"');
        echo $contenidoAdj;
        exit;
    }
}

if ($_POST || isset($_GET['idDocumento'])) {

    $idDoc = htmlspecialchars(isset($_POST['idDocumento']) ? $_POST['idDocumento'] : $_GET['idDocumento']);
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
    $nombrefinal = $nombreDocumento;
    $contenido = $doc[0]['documento'];

    header("Content-type: $mime");
    header('Content-Disposition: attachment; filename="' . $nombrefinal . '"');

    echo $contenido;
}

