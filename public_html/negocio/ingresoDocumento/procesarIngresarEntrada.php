<?php

require_once '../../data/Contrato.php';
require_once '../../data/TipoDocumento.php';
require_once '../../data/Documento.php';
require_once '../../data/DetalleDocumento.php';
require_once '../../data/Adjunto.php';
require_once '../../data/Accion.php';
require_once '../../data/Usuario.php';
require_once '../../data/SubContrato.php';
require_once '../../data/Log.php';
require_once '../../data/Entidad.php';
require_once '../../data/Cargo.php';
require_once '../../data/Seguimiento.php';
require_once '../../data/Mail.php';
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Controllers/DetalleDocumentoController.php';
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Controllers/CorresponsableController.php';


$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];

//sessiones anteriores por si se cerro de forma inesperada
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);
unset($_SESSION['documentosRelacionadosCargaM']);

if (!isset($_SESSION['documentosRelacionadosEntrada'])) {
    $_SESSION['documentosRelacionadosEntrada'] = array();
}
$serviceDetalleDoc = new DetalleDocumento();
$idContrato = $usuarioSession->getIdContrato();
$correlativo = $serviceDetalleDoc->detalleNProvidencia($idContrato);

//servicios
$serviceContrato = new Contrato();
$serviceTipoDocumento = new TipoDocumento();
$serviceSubContrato = new SubContrato();
$serviceUsuario = new Usuario();
$serviceLog = new Log();
$serviceEntidades = new Entidad();
$serviceCargo = new Cargo();
$serviceSeguimiento = new Seguimiento();


//variables
$tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();
$idContrato = $usuarioSession->getidContrato();
$contrato = $serviceContrato->getContratoPorId($idContrato);

//SI EL USUARIO ES ADMINISTRADOR SE LISTAN TODOS LOS USUARIOS
$responsables = $usuarioSession->getIdPerfil() != 1 ? $serviceUsuario->getUsuariosPorContrato($idContrato) : $serviceUsuario->getUsuarios();


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

    //si la fecha de plazo esta vacía, de manera automática se suman 7 días

    if (htmlspecialchars($_POST['fechaPlazo']) != "") {
        $fechaPlazo = $serviceDocumento->formatoFechaGuardarDB(htmlspecialchars($_POST['fechaPlazo']));
    } else { //sumando 7 dias
        //fecha de plazo
        $fechaActual = $serviceDocumento->obtenerFechaSinHora();
        $fechaPlazo = date('Y-m-d', strtotime("$fechaActual + 7 day"));
    }

    $idEstadoDoc = 1;
    $idFlujo = 1;


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
    $idDocumento = $serviceDocumento->getMaxIdDocumento() + 1;
    $documento = new Documento();
    $documento->setIdDocumento($idDocumento);
    $documento->setNombreDocumento($nombreDocumento);
    $documento->setDocumento($doc);
    $documento->setMimeDocumento($mime);
    $documento->setTamanoDocumento($tamano);
    $documento->setFechaDocumento($serviceDocumento->formatoFechaGuardarDB($fechaDoc));
    $documento->setFechaRecepcion($serviceDocumento->formatoFechaGuardarDB($fechaRecepcion));
    $documento->setFechaPlazo($fechaPlazo);
    $documento->setIdEstadoDoc($idEstadoDoc);
    $documento->setIdFlujo($idFlujo);
    $documento->setIdSubContrato($idSubContrato);


    if ($serviceDocumento->ingresarDocumento($documento) == 1) {

        //DETALLE DOCUMENTO
        $remitente = htmlspecialchars($_POST['remitente']);
        $destinatario = htmlspecialchars($_POST['destinatario']);


        $materia = htmlspecialchars($_POST['materia']);
        $antecedente = htmlspecialchars($_POST['antecedente']);
        $incluye = htmlspecialchars($_POST['incluye']);
        $comentarios = htmlspecialchars($_POST['comentarios']);
        $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']);
        $idResponsable = htmlspecialchars($_POST['idResponsable']);
        $numeroDoc = strtoupper(htmlspecialchars($_POST['numeroDoc']));
        $numeroProv = strtoupper(htmlspecialchars($_POST['numeroProv']));
        $numeroProceso = strtoupper(htmlspecialchars($_POST['numeroProceso']));

        //instanceando y seteando objeto
        $detalle = new DetalleDocumento();
        $detalle->setIdDocumento($idDocumento);
        $detalle->setNumDocumento($numeroDoc);
        $detalle->setNumProvidencia($numeroProv);
        $detalle->setNumProceso($numeroProceso);

        $detalle->setIdRemitente($remitente);
        $detalle->setIdDestinatario($destinatario);

        $detalle->setMateria($materia);
        $detalle->setAntecedente($antecedente);
        $detalle->setIncluye($incluye);
        $detalle->setComentario($comentarios);
        $detalle->setIdResponsable($idResponsable);
        $detalle->setIdTipoDoc($idTipoDoc);

        //INSERTANDO ACCIONES
        $serviceAccion = new Accion();

        htmlspecialchars($_POST['conocimiento']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['conocimiento']) : "";
        htmlspecialchars($_POST['coordinar']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['coordinar']) : "";
        htmlspecialchars($_POST['conversar']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['conversar']) : "";
        htmlspecialchars($_POST['archivo']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['archivo']) : "";
        htmlspecialchars($_POST['responder']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['responder']) : "";
        htmlspecialchars($_POST['revisar']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['revisar']) : "";
        htmlspecialchars($_POST['urgente']) != null ? $serviceAccion->ingresarAccion($idDocumento, $_POST['urgente']) : "";

        //Controllers GG
        $controlerDetalleDocumento = new DetalleDocumentoController();
        $datosDetalleDocumento = array(

            'id_documento' => $idDocumento,
            'num_documento' => addslashes($numeroDoc),
            'num_providencia' => addslashes($numeroProv),
            'num_proceso' => addslashes($numeroProceso),
            'id_remitente' => $remitente,
            'id_destinatario' => $destinatario,
            'materia' => addslashes($materia),
            'antecedente' => addslashes($antecedente),
            'incluye' => addslashes($incluye),
            'comentario' => addslashes($comentarios),
            'id_tipo_doc' => $idTipoDoc,
            'id_responsable' => $idResponsable

        );

        $exito = $controlerDetalleDocumento->insertarDetalleDocumento($datosDetalleDocumento);

        //seteando un Json para mostrar mensaje de exito y enviar el id documento para ingresar los adjuntos - METODO ANTIGUO
        //$exito = $serviceDetalleDoc->ingresarDetalleDocumento($detalle);

        error_log("Valor de exito: " . $exito);

        if ($exito >= 1) {
            $controllerCorresponsable = new CorresponsableController();

            // Assuming $_POST['idCoresponsable'] contains an array of user IDs
            if (isset($_POST['idCorresponsable'])) {
                $idCorresponsable = explode(',', $_POST['idCorresponsable']);
                foreach ($idCorresponsable as $idUsuarioCorresponsable) {
                    $datos = array(
                        'id_corresponsable_doc' => 0,
                        'id_documento' => $idDocumento, // Use the ID of the newly created documento
                        'id_usuario_corresponsable' => $idUsuarioCorresponsable,
                    );

                    $insercionCorresponsable = $controllerCorresponsable->set($datos);
                }
            }

            if ($insercionCorresponsable == 1) {
                $exitoOperacion = 1;
            } else {
                $exitoOperacion = 0;
            }
        } else {
            // Handle the failure of inserting detalle documento
            $exitoOperacion = 0;
        }


        //RELACIONANDO DOCUMENTOS
        $documentosRelacionados = array();
        $documentosRelacionados = $_SESSION['documentosRelacionadosEntrada'];

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



        //Eliminando session con los documentos relacionados
        unset($_SESSION['documentosRelacionadosEntrada']);
        unset($documentosRelacionados);
        $serviceMail = new Mail();
        $titulo = "Asignación de documento de entrada";
        $asunto = "Se ha asignado un documento de entrada. <br/>"
        . "<strong>Número providencia:</strong> $numeroProv <br/>"
        . "<strong>Número proceso:</strong> $numeroProceso <br/>"
        . "<strong>Número Documento:</strong> $numeroDoc <br/>"
        . "<strong>Materia: </strong>$materia <br/>"
        . "<strong>Incluye:</strong> $incluye <br/>"
        . "<strong>Comentarios: </strong>$comentarios <br/>"
        . "<strong>Fecha recepción: </strong> $fechaRecepcion <br/>"
        . "<strong>Fecha Plazo: </strong>$fechaPlazo";
        $mensaje = $serviceMail->generarBodyMailGenerico($asunto);
        try {
            $responsable = $serviceUsuario->getUsuarioPorId($idResponsable);
            $correo = $responsable->getCorreo();


            if ($correo) {
                $serviceMail->enviarMail($titulo, $mensaje, $correo);
            } else {
                // Handle the case where there's no email address
                $exitoOperacion = 0; // Mail not sent due to missing email
            }
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            $exitoOperacion = -1; // Mail sending failed
        } catch (Throwable $t) {
            error_log("Error: " . $t->getMessage());
            $exitoOperacion = -1; // Other failures
        }

        // Send emails to the corresponsables
        if (!empty($_POST['idCorresponsable']) && is_array($_POST['idCorresponsable'])) {
            foreach ($_POST['idCorresponsable'] as $idUsuarioCorresponsable) {
                try {
                    // Get the corresponsable user details using their ID
                    $corresponsable = $serviceUsuario->getUsuarioPorId($idUsuarioCorresponsable);
                    $correoCorresponsable = $corresponsable->getCorreo();

                    // Check if the corresponsable has an email address
                    if ($correoCorresponsable) {
                        // Send email
                        $serviceMail->enviarMail($titulo, $mensaje, $correoCorresponsable);
                    } else {
                        // Handle the case where there's no email address
                        error_log("No email address for corresponsable ID: " . $idUsuarioCorresponsable);
                    }
                } catch (Exception $e) {
                    // Log any exceptions for this corresponsable
                    error_log("Error sending email to corresponsable ID " . $idUsuarioCorresponsable . ": " . $e->getMessage());
                } catch (Throwable $t) {
                    error_log("Error: " . $t->getMessage());
                    
                }
            }
        }

        if ($exitoOperacion == 1) {
            $mensaje = "Documento asignado y correo enviado con éxito.";
        } elseif ($exitoOperacion == 0) {
            $mensaje = "Documento asignado, pero no se especificó un correo.";
        } elseif ($exitoOperacion == -1) {
            $mensaje = "Documento asignado, pero ocurrió un error al enviar el correo.";
        } else {
            $mensaje = "Ocurrió un error al asignar el documento.";
        }

        //ingreso log
        $log = new Log();
        $log->setNombreUsuario($usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP());
        $log->setAccion("Entrada: Subio el documento " . $documento->getNombreDocumento());
        $log->setFechaAccion($serviceDocumento->obtenerFechaConHora());
        $log->setIdUsuario($usuarioSession->getIdUsuario());
        $serviceLog->ingresarLog($log); //INGRESANDO REGISTRO AL LOG

        // Preparing the response
        $respuesta = array(
            "exito" => $exitoOperacion,
            "idDocumento" => $idDocumento,
            "mensaje" => $mensaje
        );

        //debug error_log(print_r($respuesta, true));


        echo json_encode($respuesta); //agregando el detalle a la tabla
    } else {
        echo -1;
    }
}
