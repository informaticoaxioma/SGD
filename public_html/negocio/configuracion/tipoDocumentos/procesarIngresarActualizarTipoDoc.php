<?php

require_once '../../../data/TipoDocumento.php';

$serviceTipoDocumento = new TipoDocumento();
$tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();

if ($_POST) {
    $flag = htmlspecialchars($_POST['flag']);

    switch ($flag) {

        case 1://INGRESAR TIPO DOCUMENTO
            $nombreDocumento = ucwords(htmlspecialchars($_POST['nombreDocumento']));

            echo $serviceTipoDocumento->ingresarTipoDoc($nombreDocumento);

            break;

        case 2://ACTUALIZAR TIPO DOCUMENTO
            
            $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']);
            $tipoDocumento = htmlspecialchars($_POST['tipoDoc']);
            //instanceando y seteando objeto
            $tipoDoc= new TipoDocumento();
            $tipoDoc->setIdTipoDocumento($idTipoDoc);
            $tipoDoc->setTipoDocumento($tipoDocumento);
            
            //actualizar tipo doc
            echo $serviceTipoDocumento->actualizarTipoDoc($tipoDoc);

            break;

        case 3://ELIMINAR TIPO DOCUMENTO
            $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']);

            echo $serviceTipoDocumento->eliminarTipoDoc($idTipoDoc);

            break;
    }
}