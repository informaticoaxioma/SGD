<?php
error_log("YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY");
require_once '../../data/Contrato.php';
require_once '../../data/TipoDocumento.php';
require_once '../../data/Documento.php';
require_once '../../data/DetalleDocumento.php';
require_once '../../data/Adjunto.php';
require_once '../../data/Accion.php';
require_once '../../data/Seguimiento.php';
require_once '../../data/Usuario.php';
require_once '../../data/SubContrato.php';
require_once '../../data/Accion.php';
require_once '../../data/Actividad.php';
require_once '../../data/EstadoDocumento.php';
require_once '../../data/Bitacora.php';
require_once '../../data/Entidad.php';
require_once '../../data/Cargo.php';
require_once '../../data/Entidad.php';
require_once '../../data/Seguimiento.php';
require_once '../../data/Funciones.php';
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Controllers/UsuariosController.php';
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Controllers/CorresponsableController.php';

$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];

//sessiones anteriores por si se cerro de forma inesperada
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);
unset($_SESSION['documentosRelacionadosCargaM']);
unset($_SESSION['documentosRelacionadosEntrada']);

if (!isset($_SESSION['documentosRelacionadosDetalle'])) {
    $_SESSION['documentosRelacionadosDetalle'] = array();
}


//servicios
$serviceContrato = new Contrato();
$serviceTipoDocumento = new TipoDocumento();
$serviceSubContrato = new SubContrato();
$serviceUsuario = new Usuario();
$serviceDocumento = new Documento();
$serviceAccion = new Accion();
$serviceActividad = new Actividad();
$serviceAdjunto = new Adjunto();
$serviceEstadoDoc = new EstadoDocumento();
$serviceBitacora = new Bitacora();
$serviceDetalleDoc = new DetalleDocumento();
$serviceEntidades = new Entidad();
$serviceCargo = new Cargo();
$serviceEntidad = new Entidad();
$serviceSeguimiento = new Seguimiento();
$serviceFunciones = new Funciones();

//GG CONTROLLER

$usuarioController = new UsuariosController();
$controllerCorresponsable = new CorresponsableController();





//variables
if ($_GET) {

    $idDocumento = htmlspecialchars($_GET['idDocumento']) != "" ? htmlspecialchars($_GET['idDocumento']) : "";

    if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 9) {//USUARIO SUPER ADMIN
        //------------------------------------------//
        $tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();
        $idContrato = $usuarioSession->getidContrato();
        $contrato = $serviceContrato->getContratoPorId($idContrato);
        $subContratos = $serviceSubContrato->getSubContratoPorContrato($idContrato);
        $documento = $serviceDocumento->getDocumentoPorID($idDocumento);

        $responsables = $usuarioSession->getIdPerfil() != 1 ? $serviceUsuario->getUsuariosPorSubContratoActivo($documento[0]['id_subcontrato']) : $serviceUsuario->getUsuarios();        
        $corresponsables = $usuarioSession->getIdPerfil() != 1 ? $serviceUsuario->getUsuariosPorSubContratoActivo($documento[0]['id_subcontrato']) : $serviceUsuario->getUsuarios();        
        $selectedCorresponsables= $controllerCorresponsable->getCorresponsablesPorIdDocumento($idDocumento);

        $acciones = $serviceAccion->getAccionesPorDocumento($idDocumento);
        $adjuntos = $serviceAdjunto->getAdjuntosPorDocumento($idDocumento);
        $bitacoras = $serviceBitacora->getBitacoraPorDocumento($idDocumento);
        $estadosDocs = $serviceEstadoDoc->getEstadosDocs();
        $seguimientos = $serviceSeguimiento->getSeguimientosPorDocumento($idDocumento);

        if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 9) {//SUPER ADMIN
            $remitentes = $serviceEntidades->getEntidadesPorTipo(1, $idContrato);
            $destinatarios = $serviceEntidades->getEntidadesPorTipo(2, $idContrato);
        } else {//COORDINADOR DEL AREA
            $remitentes = $serviceEntidades->getEntidadesPorTipo(1, $usuarioSession->getIdContrato());
            $destinatarios = $serviceEntidades->getEntidadesPorTipo(2, $usuarioSession->getIdContrato());
        }

        //---------------------------------------//
    } else {//USUARIO NORMAL (NO EDITA, SOLO VISUALIZA)
        //------------------------------------------------//
        $idContrato = $usuarioSession->getidContrato();
        $contrato = $serviceContrato->getContratoPorId($idContrato);
        $documento = $serviceDocumento->getDocumentoPorID($idDocumento);

        $subContrato = $serviceSubContrato->getSubContratoPorId($documento[0]['id_subcontrato']);
        $tipoDocumento = $serviceTipoDocumento->getTipoDocumentoPorId($documento[0]['id_tipo_doc']);

        $remitente = $serviceEntidades->getEntidadPorId($documento[0]['id_remitente']);
        $cargoRemitente = $serviceCargo->getCargoPorId($remitente->getIdCargo());
        $destinatario = $serviceEntidades->getEntidadPorId($documento[0]['id_destinatario']);
        $cargoDestinatario = $serviceCargo->getCargoPorId($destinatario->getIdCargo());
        $responsable = $serviceUsuario->getUsuarioPorId($documento[0]['id_responsable']);
        $acciones = $serviceAccion->getAccionesPorDocumento($idDocumento);
        $adjuntos = $serviceAdjunto->getAdjuntosPorDocumento($idDocumento);
        $bitacoras = $serviceBitacora->getBitacoraPorDocumento($idDocumento);
        $seguimientos = $serviceSeguimiento->getSeguimientosPorDocumento($idDocumento);
    }
}



if (isset($_POST['flagDoc'])) {

    $flagDoc = htmlspecialchars($_POST['flagDoc']);

    switch ($flagDoc) {
        case 1://DESCARGAR DOCUMENTO PRINCIPAL

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
            header("Content-Disposition: attachment; filename=" . $nombrefinal);

            echo $contenido;

            break;

        case 2://DESCARGAR ADJUNTOS

            $idAdjunto = htmlspecialchars($_POST['idAdjunto']);
            $adjunto = $serviceAdjunto->getAdjuntoPorId($idAdjunto);

            $dbMime = $adjunto->getMimeAdjunto();
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

            $nombreDocumento = $adjunto->getNombreAdjunto();
            $contenido = $adjunto->getArchivoAdjunto();

            $nombreAux = explode(".", $nombreDocumento);
            $nombreFormat = str_replace(".", "_", $nombreAux[0]);
            $nombreFormat = str_replace(" ", "_", $nombreFormat);
            $nombreFormat = str_replace(",", "_", $nombreFormat);
            $nombrefinal = $nombreFormat . "." . $extension;

            header("Content-type: $mime");
            header("Content-Disposition: attachment; filename=" . $nombrefinal);

            echo $contenido;
            break;

        case 3://documentos relacionados

            $idDocRel = htmlspecialchars($_POST['idDocRelacionado']);
            $doc = $serviceDocumento->getDocumentoPorID($idDocRel);

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
            $contenido = $doc[0]['documento'];

            $nombreAux = explode(".", $nombreDocumento);
            $nombreFormat = str_replace(".", "_", $nombreAux[0]);
            $nombreFormat = str_replace(" ", "_", $nombreFormat);
            $nombreFormat = str_replace(",", "_", $nombreFormat);
            $nombrefinal = $nombreFormat . "." . $extension;

            header("Content-type: $mime");
            header("Content-Disposition: attachment; filename=" . $nombrefinal);

            echo $contenido;
            break;

        case 4:
            $idDocumento = htmlspecialchars($_POST['idDocumento']);

            echo $serviceDocumento->eliminarDocumento($idDocumento);
            break;
        //ELIMINAR SEGUIMIENTO
        case 5:
            error_log(count($_POST));                        
            $idSeguimiento = $_POST['idSeg'];
            
            $eliminaSeguimiento =  $serviceSeguimiento->eliminarSeguimiento($idSeguimiento);
            echo $eliminaSeguimiento;
            break;
    }
}


if (isset($_POST['idSubContrato'])) {//actualizando contrato en detalle
    $flag = htmlspecialchars($_POST['flag']);
    //valores en comun (entrada y salida)
    $idSubContrato = htmlspecialchars($_POST['idSubContrato']);
    $fechaDoc = htmlspecialchars($_POST['fechaDoc']);
    $fechaRecepcion = htmlspecialchars($_POST['fechaRecepcion']);
    $fechaPlazo = htmlspecialchars($_POST['fechaPlazo']);
    $idDoc = htmlspecialchars($_POST['idDocumento']);

    //-----------------------------------------------------------------//
    $numeroDoc = htmlspecialchars($_POST['numeroDoc']);
    $numeroProceso = htmlspecialchars($_POST['numeroProceso']);
    $remitente = htmlspecialchars($_POST['remitente']);
    $destinatario = htmlspecialchars($_POST['destinatario']);
    $materia = htmlspecialchars($_POST['materia']);
    $antecedente = htmlspecialchars($_POST['antecedente']);
    $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']);
    $incluye = htmlspecialchars($_POST['incluye']);
    $comentario = htmlspecialchars($_POST['comentarios']);
    $responsable = htmlspecialchars($_POST['idResponsable']);

    switch ($flag) {

        case 1://ACTUALIZAR ENTRADA


            $numeroProvidencia = htmlspecialchars($_POST['numeroProvidencia']);
            $documento = new Documento();
            $documento->setFechaDocumento($serviceDocumento->formatoFechaGuardarDB($fechaDoc));
            $documento->setFechaRecepcion($serviceDocumento->formatoFechaGuardarDB($fechaRecepcion));
            $documento->setFechaPlazo($serviceDocumento->formatoFechaGuardarDB($fechaPlazo));
            $documento->setIdDocumento($idDoc);
            $documento->setIdSubContrato($idSubContrato);


            if ($serviceDocumento->actualizarDocumento($documento, 2) == 1) {//actualizando documento
                //ingresando el detalle
                $detalleDocumento = new DetalleDocumento();
                $detalleDocumento->setNumDocumento($numeroDoc);
                $detalleDocumento->setNumProvidencia($numeroProvidencia);
                $detalleDocumento->setNumProceso($numeroProceso);
                $detalleDocumento->setIdRemitente($remitente);
                $detalleDocumento->setIdDestinatario($destinatario);
                $detalleDocumento->setMateria(ucwords($materia));
                $detalleDocumento->setAntecedente(ucwords($antecedente));
                $detalleDocumento->setIncluye(ucwords($incluye));
                $detalleDocumento->setComentario(ucwords($comentario));
                $detalleDocumento->setIdTipoDoc($idTipoDoc);
                $detalleDocumento->setIdResponsable($responsable);
                $detalleDocumento->setIdDocumento($idDoc);

                $serviceDetalleDoc->actualizarDetalleDocumento($detalleDocumento);
                //Actualizar el corresponsable segun idDocumento
                $controllerCorresponsable = new CorresponsableController();
                $controllerUsuarios = new UsuariosController();
                $eliminaCorresponsable = $controllerCorresponsable->eliminarCorresponsablesPorIdDocumento($idDoc);
                if (isset($_POST['idCorresponsables'])) {
                    $idCorresponsables = explode(',', $_POST['idCorresponsables']);
                    foreach ($idCorresponsables as $idUsuarioCorresponsable) {
                        $datos = array(
                            'id_corresponsable_doc' => 0,
                            'id_documento' => $idDoc, // Use the ID of the newly created documento
                            'id_usuario_corresponsable' => $idUsuarioCorresponsable,
                        );
    
                        $insercionCorresponsable = $controllerCorresponsable->set($datos);
                    }
                }



                //ELIMINANDO ACCIONES ANTIGUAS E INSERTANDO NUEVAS
                if ($serviceAccion->eliminarAcciones($idDoc) == 1) {

                    htmlspecialchars($_POST['conocimiento']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['conocimiento']) : "";
                    htmlspecialchars($_POST['coordinar']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['coordinar']) : "";
                    htmlspecialchars($_POST['conversar']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['conversar']) : "";
                    htmlspecialchars($_POST['archivo']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['archivo']) : "";
                    htmlspecialchars($_POST['responder']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['responder']) : "";
                    htmlspecialchars($_POST['revisar']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['revisar']) : "";
                    htmlspecialchars($_POST['urgente']) != null ? $serviceAccion->ingresarAccion($idDoc, $_POST['urgente']) : "";

//DOCUMENTO

                    if (isset($_FILES["archivoDoc"]['name']) && $_FILES["archivoDoc"]['name'] != "") {

                        $nombreDocumento = $_FILES['archivoDoc']['name'];
                        $mime = end(explode(".", $_FILES['archivoDoc']['name'])); //realiza un explode desde el fin desde derecha a izquierda
                        $tamano = $serviceDocumento->convertirTamanoArchivo($_FILES['archivoDoc']['size']);
                        //-----------------------------------//
                        //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
                        $fp = fopen($_FILES['archivoDoc']['tmp_name'], "rb");
                        $archivo = fread($fp, $_FILES['archivoDoc']['size']);
                        $archivo = addslashes($archivo);
                        fclose($fp);
                    }

                    if (isset($archivo) && $archivo != "") {//Actualizar Documento
                        $documento->setNombreDocumento($nombreDocumento);
                        $documento->setDocumento($archivo);
                        $documento->setMimeDocumento($mime);
                        $documento->setTamanoDocumento($tamano);

                        $serviceDocumento->actualizarDocumento($documento, 4);
                    }


                    $respuesta = array("exito" => 1, "idDocumento" => $idDoc); //codigo que se realiza para ejev¿cuta el metodo subirDjuntos en el lado del cliente

                    echo json_encode($respuesta); //retornando array en json
                }
            }

            break;

        case 2://ACTUALIZAR SALIDA


            $idEstadoDoc = htmlspecialchars($_POST['idEstado']);

            $documento = new Documento();
            $documento->setFechaDocumento($serviceDocumento->formatoFechaGuardarDB($fechaDoc));
            $documento->setFechaRecepcion($serviceDocumento->formatoFechaGuardarDB($fechaRecepcion));
            $documento->setFechaPlazo($serviceDocumento->formatoFechaGuardarDB($fechaPlazo));
            $documento->setIdDocumento($idDoc);
            $documento->setIdSubContrato($idSubContrato);
            $documento->setIdEstadoDoc($idEstadoDoc);
            $documento->setIdEstadoDoc($idEstadoDoc);

            if ($serviceDocumento->actualizarDocumento($documento, 3) == 1):

                $detalleDocumento = new DetalleDocumento();
                $detalleDocumento->setIdDocumento($idDoc);
                $detalleDocumento->setNumDocumento($numeroDoc);
                $detalleDocumento->setNumProceso($numeroProceso);
                $detalleDocumento->setIdRemitente($remitente);
                $detalleDocumento->setIdDestinatario($destinatario);
                $detalleDocumento->setMateria($materia);
                $detalleDocumento->setAntecedente($antecedente);
                $detalleDocumento->setIncluye($incluye);
                $detalleDocumento->setComentario($comentario);
                $detalleDocumento->setIdTipoDoc($idTipoDoc);
                $detalleDocumento->setIdResponsable($responsable);

                //DOCUMENTO

                if (isset($_FILES["archivoDoc"]['name'])) {


                    $nombreDocumento = $_FILES['archivoDoc']['name'];
                    $mime = end(explode(".", $_FILES['archivoDoc']['name'])); //realiza un explode desde el fin desde derecha a izquierda
                    $tamano = $serviceDocumento->convertirTamanoArchivo($_FILES['archivoDoc']['size']);
                    //-----------------------------------//
                    //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
                    $fp = fopen($_FILES['archivoDoc']['tmp_name'], "rb");
                    $archivo = fread($fp, $_FILES['archivoDoc']['size']);
                    $archivo = addslashes($archivo);
                    fclose($fp);

                    if ($_FILES["archivoDoc"]['name'] != "") {

                        $nombreDocumento = $_FILES['archivoDoc']['name'];
                        $mime = end(explode(".", $_FILES['archivoDoc']['name'])); //realiza un explode desde el fin desde derecha a izquierda
                        $tamano = $serviceDocumento->convertirTamanoArchivo($_FILES['archivoDoc']['size']);
                        //-----------------------------------//
                        //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
                        $fp = fopen($_FILES['archivoDoc']['tmp_name'], "rb");
                        $archivo = fread($fp, $_FILES['archivoDoc']['size']);
                        $archivo = addslashes($archivo);
                        fclose($fp);
                    }

                    if ($archivo != "") {//Actualizar Documento
                        $documento->setNombreDocumento($nombreDocumento);
                        $documento->setDocumento($archivo);
                        $documento->setMimeDocumento($mime);
                        $documento->setTamanoDocumento($tamano);

                        $serviceDocumento->actualizarDocumento($documento, 4);
                    }
                }

                $respuesta = array("exito" => $serviceDetalleDoc->actualizarDetalleDocumento($detalleDocumento), "idDocumento" => $idDoc); //codigo que se realiza para ejev¿cuta el metodo subirDjuntos en el lado del cliente

                echo json_encode($respuesta); //retornando array en json


            endif;

            break;
    }
}
