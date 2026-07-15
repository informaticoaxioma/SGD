<?php

require_once '../../data/Contrato.php';
require_once '../../data/SubContrato.php';
require_once '../../data/Area.php';
require_once '../../data/TipoDocumento.php';
require_once '../../data/Documento.php';
require_once '../../data/DetalleDocumento.php';
require_once '../../data/Adjunto.php';
require_once '../../data/Accion.php';
require_once '../../data/EstadoDocumento.php';
require_once '../../data/Usuario.php';
require_once '../../data/Log.php';
require_once '../../data/Entidad.php';
require_once '../../data/Cargo.php';
require_once '../../data/Seguimiento.php';
require_once '../../data/Mail.php';

$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];
//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosHitos']);
unset($_SESSION['documentosRelacionadosCargaM']);

if (!isset($_SESSION['documentosRelacionadosSalida'])) {
    $_SESSION['documentosRelacionadosSalida'] = array();
}

//servicios
$serviceContrato = new Contrato();
$serviceTipoDocumento = new TipoDocumento();
$serviceSubContrato = new SubContrato();
$serviceEstadoDoc = new EstadoDocumento();
$serviceLog = new Log();
$serviceEntidades = new Entidad();
$serviceCargo = new Cargo();
$serviceSeguimiento = new Seguimiento();

//variables
$tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();
$idContrato = $usuarioSession->getidContrato();
$contrato = $serviceContrato->getContratoPorId($idContrato);
$estadosDocs = $serviceEstadoDoc->getEstadosDocs();

if ($usuarioSession->getIdPerfil() == 1) { //SUPER ADMIN
    $remitentes = $serviceEntidades->getEntidadesPorTipo(1, 0);
    $destinatarios = $serviceEntidades->getEntidadesPorTipo(2, 0);
    $subContratos = $serviceSubContrato->getSubContratos();
} else { //USUARIO NORMAL POR CONTRATO
    $remitentes = $serviceEntidades->getEntidadesPorTipo(1, $usuarioSession->getIdContrato());
    $destinatarios = $serviceEntidades->getEntidadesPorTipo(2, $usuarioSession->getIdContrato());
    $subContratos = $serviceSubContrato->getSubContratoPorContrato($idContrato);
}

$cargos = $usuarioSession->getIdPerfil() == 1 ? $serviceCargo->getCargos() : $serviceCargo->getCargosPorContrato($usuarioSession->getIdContrato());

if ($_POST) {

    $serviceDocumento = new Documento();
    $serviceDetalleDoc = new DetalleDocumento();
    //ATRIBUTOS DOCUMENTO
    $idSubContrato = htmlspecialchars($_POST['idSubContrato']);
    $fechaDoc = htmlspecialchars($_POST['fechaDoc']);
    $fechaRecepcion = htmlspecialchars($_POST['fechaRecepcion']);
    $fechaPlazo = htmlspecialchars($_POST['fechaPlazo']) != '' ? htmlspecialchars($_POST['fechaPlazo']) : '';
    $idDocRelacionado = htmlspecialchars($_POST['idDocRelacionado']);
    $idEstadoDoc = htmlspecialchars($_POST['idEstadoDoc']);
    $idFlujo = 2;
    $idDocumento = $serviceDocumento->getMaxIdDocumento() + 1;

    //calculando fecha de plazo
    $fechaActual = $serviceDocumento->obtenerFechaSinHora();

    if ($fechaPlazo == "") {
        $fechaPlazo = date('Y-m-d', strtotime("$fechaActual + 7 day"));
    }


    //DOCUMENTO
    $nombreDocumento = $_FILES['documento']['name'];
    $nombreDocumento = str_replace(" ", "_", $nombreDocumento);

    $mime = end(explode(".", $_FILES['documento']['name'])); //realiza un explode desde el fin desde derecha a izquierda
    $tamano = $serviceDocumento->convertirTamanoArchivo($_FILES['documento']['size']);
    //-----------------------------------//
    //LEYENDO EL ARCHIVO Y TRANSFORMANDOLO A BINARIO
    $fp = fopen($_FILES['documento']['tmp_name'], "rb");
    $doc = fread($fp, $_FILES['documento']['size']);
    $doc = addslashes($doc);
    fclose($fp);


    //instanceando objeto documento
    $documento = new Documento();
    $documento->setIdDocumento($idDocumento);
    $documento->setNombreDocumento($nombreDocumento);
    $documento->setDocumento($doc);
    $documento->setMimeDocumento($mime);
    $documento->setTamanoDocumento($tamano);
    $documento->setFechaDocumento($serviceDocumento->formatoFechaGuardarDB($fechaDoc));
    $documento->setFechaRecepcion($serviceDocumento->formatoFechaGuardarDB($fechaRecepcion));
    $documento->setFechaPlazo($serviceDocumento->formatoFechaGuardarDB($fechaPlazo));
    $documento->setIdEstadoDoc($idEstadoDoc);
    $documento->setIdFlujo($idFlujo);
    $documento->setIdSubContrato($idSubContrato);
    $documento->setIdDocRelacionado($idDocRelacionado);
    $documento->setFechaPlazo($serviceDocumento->formatoFechaGuardarDB($fechaPlazo));

    //Insertando documento
    if ($serviceDocumento->ingresarDocumento($documento) == "1") {

        //DETALLE DOCUMENTO
        $remitente = htmlspecialchars($_POST['remitente']);
        $destinatario = htmlspecialchars($_POST['destinatario']);

        $materia = htmlspecialchars($_POST['materia']);
        error_log("Valor de la materia ya pasada al backend: ".$materia);
        $antecedente = htmlspecialchars($_POST['antecedente']);
        $incluye = htmlspecialchars($_POST['incluye']);
        $comentarios = htmlspecialchars($_POST['comentarios']);
        $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']);
        $numeroProceso = htmlspecialchars($_POST['numeroProceso']);
        $numeroDoc = htmlspecialchars($_POST['numeroDoc']);
        $idResponsable = htmlspecialchars($_POST['idResponsable']);

        //instanceando y seteando objeto
        $detalle = new DetalleDocumento();
        $detalle->setIdDocumento($idDocumento);
        $detalle->setNumDocumento(strtoupper($numeroDoc));
        $detalle->setNumProceso(strtoupper($numeroProceso));
        $detalle->setNumProvidencia("No registra");

        $detalle->setIdRemitente($remitente);
        $detalle->setIdDestinatario($destinatario);

        $detalle->setMateria($materia);
        $detalle->setAntecedente($antecedente);
        $detalle->setIncluye($incluye);
        $detalle->setComentario($comentarios);
        $detalle->setIdTipoDoc($idTipoDoc);
        $detalle->setIdResponsable($idResponsable);

        //seteando un Json para mostrar mensaje de exito y enviar el id documento para ingresar los adjuntos
        $exito = $serviceDetalleDoc->ingresarDetalleDocumento($detalle);

        error_log("variable exito despues de ingresarDetalleDocumento: ".$exito);

        //RELACIONANDO DOCUMENTOS
        $documentosRelacionados = array();
        $documentosRelacionados = $_SESSION['documentosRelacionadosSalida'];

        $flagSeg = 0;

        if (count($documentosRelacionados) > 0) {

            for ($i = 0; $i < count($documentosRelacionados); $i++) {

                $numSeguimientoDocRel = $serviceSeguimiento->getNumeroSeguimientoPorIdDocumento($documentosRelacionados[$i]);

                if ($numSeguimientoDocRel != 0) { //si los archivos relacionados ya tienen un seguimiento entonces al documento nuevo lo ingreso a ese seguimiento
                    $serviceSeguimiento->ingresarSeguimiento($idDocumento, $numSeguimientoDocRel);
                } else { //si los documentos relacionados no tienen seguimiento los ingreso al seguimiento nuevo
                    $serviceSeguimiento->ingresarSeguimiento($idDocumento, $numSeguimiento);
                }
            }
        } else {
            $numeroSeguimiento = $serviceSeguimiento->crearNumeroSeguimiento(); //
            $serviceSeguimiento->ingresarSeguimiento($idDocumento, $numeroSeguimiento);
        }

        unset($_SESSION['documentosRelacionadosSalida']);
        unset($documentosRelacionados);


        //envio mail
        $serviceUsuario = new Usuario();
        try {
            $responsable = $serviceUsuario->getUsuarioPorId($idResponsable);
            $correo = $responsable->getCorreo();
            $titulo = "Asignación de documento de salida";
            $asunto = "Se ha asignado un documento de salida. <br/>"
                . "<strong>Número proceso:</strong> $numeroProceso <br/>"
                . "<strong>Número Documento:</strong> $numeroDoc <br/>"
                . "<strong>Materia: </strong>$materia <br/>"
                . "<strong>Incluye:</strong> $incluye <br/>"
                . "<strong>Comentarios: </strong>$comentarios <br/>"
                . "<strong>Fecha recepción: </strong> $fechaRecepcion <br/>"
                . "<strong>Fecha Plazo: </strong>$fechaPlazo";

            $serviceMail = new Mail();
            $mensaje = $serviceMail->generarBodyMailGenerico($asunto);
            $serviceMail->enviarMail($titulo, $mensaje, $correo);
            // fin mail.
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            $exitoOperacion = -1; // Mail sending failed
        } catch (Throwable $t) {
            error_log("Error: " . $t->getMessage());
            $exitoOperacion = -1; // Other failures
        }

        //ingreso log
        $log = new Log();
        $log->setNombreUsuario($usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP());
        $log->setAccion("Salida: Subio el documento " . $documento->getNombreDocumento());
        $log->setFechaAccion($serviceDocumento->obtenerFechaConHora());
        $log->setIdUsuario($usuarioSession->getIdUsuario());

        $serviceLog->ingresarLog($log); //INGRESANDO REGISTRO AL LOG

        $respuesta = array("exito" => $exito, "idDocumento" => $idDocumento);

        echo json_encode($respuesta); //agregando el detalle a la tabla
    } else {

        echo -1;
    }
}
