<?php

require_once '../../data/HitoContractual.php';
require_once '../../data/Usuario.php';
require_once '../../data/Funciones.php';
require_once '../../data/EstadoHito.php';
require_once '../../data/FrecuenciaHito.php';
require_once '../../data/Color.php';
require_once '../../data/Contrato.php';
require_once '../../data/Documento.php';
require_once '../../data/HitoDocumento.php';

$sessionUsuario = new Usuario();
session_start();
$sessionUsuario = $_SESSION['usuario'];

unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosCargaM']);

if (!isset($_SESSION['documentosRelacionadosHitos'])):
    $_SESSION['documentosRelacionadosHitos'] = array();
endif;

//SERVICIOS
$serviceHito = new HitoContractual();
$serviceFunciones = new Funciones();
$serviceEstadoHito = new EstadoHito();
$serviceFrecuencia = new FrecuenciaHito();
$serviceColor = new Color();
$serviceContrato = new Contrato();
$serviceDocumento = new Documento();
$serviceUsuario = new Usuario();
$serviceHitoDocumento = new HitoDocumento();

$contrato = $serviceContrato->getContratoPorId($sessionUsuario->getIdContrato());

$frecuencias = $serviceFrecuencia->getFrecuencias();
$estadosHito = $serviceEstadoHito->getEstados();
$colores = $serviceColor->getColores();
$responsables = $serviceUsuario->getUsuariosPorContrato($sessionUsuario->getIdContrato());

$dias = $serviceHito->obtenerPrimeryUltimoDiaFecha($serviceFunciones->obtenerFechaSinHora());
$hitos = $serviceHito->getHitosContractualesPorResponsableYMes($sessionUsuario->getIdUsuario(), $dias['primerDia'], $dias['ultimoDia']);

$contrato->getFechaTermino();
$idDocRelacionadoMod = "";

if ($_GET) {

    $idHito = htmlspecialchars($_GET['idHito']);

    $hito = $serviceHito->getHitoPorID($idHito);
}


if ($_POST):

    $flag = htmlspecialchars($_POST['flag']);

    switch ($flag):

        case 1://AGREGAR HITO

            $descripcionHito = trim(htmlspecialchars($_POST['descripcionHito']));
            $fechaEntrega = $serviceFunciones->formatoFechaGuardarDB($_POST['fechaEntrega']);
            $idResponsable = trim(htmlspecialchars($_POST['idResponsable']));
            $destino = trim(htmlspecialchars($_POST['destino']));
            $normativa = trim(htmlspecialchars($_POST['normativa']));
            $comentario = trim(htmlspecialchars($_POST['comentario']));
            $idEstadoHito = 1;
            $idFrecuenciaHito = trim(htmlspecialchars($_POST['idFrecuenciaHito']));
            $idColor = trim(htmlspecialchars($_POST['idColor']));
            $idHito = $serviceHito->getMaxIdHito() + 1;

            $hitoContractual = new HitoContractual(); //instanceando y seteando el objeto


            $hitoContractual->setIdHito($idHito);
            $hitoContractual->setDescripcionHito($descripcionHito);
            $hitoContractual->setFechaEntrega($fechaEntrega);
            $hitoContractual->setIdResponsableHito($idResponsable);
            $hitoContractual->setIdUsuarioHito($sessionUsuario->getIdUsuario());
            $hitoContractual->setDestinoInfo($destino);
            $hitoContractual->setNormativa($normativa);
            $hitoContractual->setComentario($comentario);
            $hitoContractual->setIdEstadoHito(1);
            $hitoContractual->setIdFrecuenciaHito($idFrecuenciaHito);
            $hitoContractual->setIdContrato($sessionUsuario->getIdContrato());
            $hitoContractual->setIdColor($idColor);

            echo $serviceHito->ingresarHito($hitoContractual); //ingresando el objeto
            //INGRESANDO HITOS REPETITIVOS

            $documentosRelacionados = $_SESSION['documentosRelacionadosHitos'];


            if ($hitoContractual->getIdFrecuenciaHito() != 5) {

                $serviceHito->ingresarHitoPorTodoElContrato($hitoContractual, $contrato->getFechaTermino());
            }


            for ($i = 0; $i < count($documentosRelacionados); $i++) {
                $serviceHitoDocumento->ingresarDocumentoHito($idHito, $documentosRelacionados[$i]);
            }


            unset($_SESSION['documentosRelacionadosHitos']);
            break;


        case 2://ACTUALIZAR HITO
            //INICIALIZANDO VARIABLES
            $fechaEntrega = $serviceFunciones->formatoFechaGuardarDB(htmlspecialchars($_POST['fechaEntrega']));
            $descripcionHito = htmlspecialchars($_POST['descripcionHito']);
            $idResponsable = htmlspecialchars($_POST['idResponsable']);
            $destino = htmlspecialchars($_POST['destino']);
            $normativa = htmlspecialchars($_POST['normativa']);
            $comentario = htmlspecialchars($_POST['comentario']);
            $idEstadoHitoMod = htmlspecialchars($_POST['idEstadoHitoMod']);
            $idHito = htmlspecialchars($_POST['idHito']);

            //INSTANCEANDO Y SETEANDO EL HITO
            $hito = new HitoContractual();
            $hito->setFechaEntrega($fechaEntrega);
            $hito->setDescripcionHito($descripcionHito);
            $hito->setIdResponsableHito($idResponsable);
            $hito->setDestinoInfo($destino);
            $hito->setNormativa($normativa);
            $hito->setComentario($comentario);
            $hito->setIdEstadoHito($idEstadoHitoMod);
            $hito->setIdHito($idHito);
            $hito->setIdUsuarioHito($sessionUsuario->getIdUsuario());

            if (count($_SESSION['documentosRelacionadosHitos']) > 0) {//AGREGANDO DOCUMENTOS RELACIONADOS
                $documentosRelacionados = $_SESSION['documentosRelacionadosHitos'];

                for ($i = 0; $i < count($documentosRelacionados); $i++) {
                    $serviceHitoDocumento->ingresarDocumentoHito($idHito, $documentosRelacionados[$i]);
                }
            }

            unset($_SESSION['documentosRelacionadosHitos']);
            unset($documentosRelacionados);
            echo $serviceHito->actualizarHito($hito) == 1 ? $idHito : -1; //DEVUELVO EL ID DEL HITO PARA REFRESCAR EL MODAL CON AJAX

            break;


    endswitch;

    
    
endif;

