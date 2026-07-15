<?php

require_once '../../data/Documento.php';
require_once '../../data/DetalleDocumento.php';
require_once '../../data/Usuario.php';
require_once '../../data/Accion.php';
require_once '../../data/Log.php';
require_once '../../data/DocumentoRelacionado.php';

$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];
//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);

if (!isset($_SESSION['documentosRelacionadosCargaM'])) {
    $_SESSION['documentosRelacionadosCargaM'] = array();
}

$serviceDocumento = new Documento();
$serviceDetalleDocumento = new DetalleDocumento();
$serviceAccion = new Accion();
$serviceLog = new Log();
$serviceDocumentosRelacionados = new DocumentoRelacionado();

$documentosSinArchivos = $serviceDocumento->getDocumentosSinArchivos($usuarioSession->getIdContrato(), $usuarioSession->getIdPerfil());

if ($_POST) {

    $flag = htmlspecialchars($_POST['flag']);
    $idDocumento = htmlspecialchars($_POST['idDocumento']);

    switch ($flag) {

        case 1://CARGANDO DOCUMENTO
            //DOCUMENTO
            $nombreDocumento = $_FILES['archivo']['name'];
            $nombreDocumento = str_replace(" ", "_", $nombreDocumento);
            
            $mime = end(explode(".", $_FILES['archivo']['name'])); //realiza un explode desde el fin desde derecha a izquierda
            $tamano = $serviceDocumento->convertirTamanoArchivo($_FILES['archivo']['size']);
            //-----------------------------------//
            //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
            $fp = fopen($_FILES['archivo']['tmp_name'], "rb");
            $archivo = fread($fp, $_FILES['archivo']['size']);
            $archivo = addslashes($archivo);
            fclose($fp);

            $documento = new Documento();

            $documento->setNombreDocumento($nombreDocumento);
            $documento->setMimeDocumento($mime);
            $documento->setTamanoDocumento($tamano);
            $documento->setDocumento($archivo);
            $documento->setIdDocumento($idDocumento);

            //ingreso log
            $log = new Log();
            $log->setNombreUsuario($usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP());
            $log->setAccion("Carga Archivo Restante: Subio el documento " . $documento->getNombreDocumento());
            $log->setFechaAccion($serviceDocumento->obtenerFechaConHora());
            $log->setIdUsuario($usuarioSession->getIdUsuario());

            $serviceLog->ingresarLog($log); //INGRESANDO REGISTRO AL LOG


            echo $serviceDocumento->actualizarDocumento($documento, 4); //flag 4 para actualizar solo los datos relacionados con el archivo           
            //RELACIONANDO DOCUMENTOS
            $documentosRelacionados = array();
            $documentosRelacionados = $_SESSION['documentosRelacionadosCargaM'];

            for ($i = 0; $i < count($documentosRelacionados); $i++) {
                $serviceDocumentosRelacionados->ingresarDocumentoRelacionado($idDocumento, $documentosRelacionados[$i]);
            }

            unset($_SESSION['documentosRelacionadosCargaM']);
            unset($documentosRelacionados);


            break;

        case 2://ELIMINAR REGISTRO
            //eliminando dependencias
            if ($serviceDetalleDocumento->eliminarDetalleDocumento($idDocumento) == 1) {

                if ($serviceAccion->eliminarAcciones($idDocumento) == 1) {
                    //ingreso log
                    $log = new Log();
                    $log->setNombreUsuario($usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP());
                    $log->setAccion("Elimino Registro con la id= " . $idDocumento);
                    $log->setFechaAccion($serviceDocumento->obtenerFechaConHora());
                    $log->setIdUsuario($usuarioSession->getIdUsuario());

                    $serviceLog->ingresarLog($log); //INGRESANDO REGISTRO AL LOG

                    echo $serviceDocumento->eliminarDocumento($idDocumento);
                }
            }



            break;
    }
}