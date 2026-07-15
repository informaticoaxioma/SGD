<?php

require_once '../../data/Documento.php';
$serviceDocumento = new Documento();

$idDoc = htmlspecialchars($_POST['idDocumento']);
$doc = $serviceDocumento->getDocumentoPorID($idDoc);
$mime = $doc[0]['mime_documento'];
$nombreDocumento = $doc[0]['nombre_documento'];
$contenido = $doc[0]['documento'];

header("Content-type: $mime");
header("Content-Disposition: attachment; filename=" . $nombreDocumento);

echo $contenido;
