<?php

require_once '../data/Adjunto.php';
require_once '../data/Documento.php';

$serviceAdjunto = new Adjunto();
$serviceDocumento = new Documento();
$adjunto = new Adjunto();
$idDocumento = htmlspecialchars($_POST['idDocumento']);

if (count($_FILES) > 0) {


    if ($serviceAdjunto->contarAdjuntoPorDocumento($idDocumento) == 0) {//si no hay adjuntos----inserto
        foreach ($_FILES as $f):

            $adjunto->setNombreAdjunto($f['name']);
            $adjunto->setMimeAdjunto(end(explode(".", $f['name'])));
            $tamano = $serviceDocumento->convertirTamanoArchivo($f['size']);
            $adjunto->setTamanoAdjunto($tamano);
            //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
            $fp = fopen($f['tmp_name'], "rb");
            $ad = fread($fp, $f['size']);
            $ad = addslashes($ad);
            fclose($fp);
            $adjunto->setArchivoAdjunto($ad);
            $adjunto->setIdDocumento($idDocumento);

            //insertando datos
            $serviceAdjunto->ingresarAdjunto($adjunto);

        endforeach;
    }else { // sino elimino los adjuntos existentes e ingreso los nuevos
        if ($serviceAdjunto->eliminarAdjuntosPorDocumento($idDocumento) == 1) {

            foreach ($_FILES as $f):

                $adjunto->setNombreAdjunto($f['name']);
                $adjunto->setMimeAdjunto(end(explode(".", $f['name'])));
                $tamano = $serviceDocumento->convertirTamanoArchivo($f['size']);
                $adjunto->setTamanoAdjunto($tamano);
                //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
                $fp = fopen($f['tmp_name'], "rb");
                $ad = fread($fp, $f['size']);
                $ad = addslashes($ad);
                fclose($fp);
                $adjunto->setArchivoAdjunto($ad);
                $adjunto->setIdDocumento($idDocumento);

                //insertando datos
                $serviceAdjunto->ingresarAdjunto($adjunto);

            endforeach;
        }
    }
}



