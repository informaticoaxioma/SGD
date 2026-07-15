<?php

require_once '../../data/Usuario.php';
require_once '../../data/Documento.php';
require_once '../../data/HitoContractual.php';
require_once '../../data/Zebra_Pagination.php';
require_once '../../data/EstadoHito.php';
require_once '../../data/FrecuenciaHito.php';
require_once '../../data/Usuario.php';

$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];

unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosCargaM']);

if (!isset($_SESSION['documentosRelacionadosHitos'])):
    $_SESSION['documentosRelacionadosHitos'] = array();
endif;


//INSTANCEANDO SERVICIOS
$serviceUsuario = new Usuario();
$serviceHito = new HitoContractual();
$serviceDocumento = new Documento();
$serviceEstadoHito = new EstadoHito();
$serviceFrecuenciaHito = new FrecuenciaHito();
$paginacion = new Zebra_Pagination();
$pagina = "";
//SI EL USUARIO ES ADMINISTRADOR, SE LISTAN TODOS LOS USUARIOS
$idContrato = $usuarioSession->getIdContrato();
$responsables = $usuarioSession->getIdPerfil() != 1 ? $serviceUsuario->getUsuariosPorContrato($idContrato) : $serviceUsuario->getUsuarios();
$estadosHito = $serviceEstadoHito->getEstados();
$frecuenciaHitos = $serviceFrecuenciaHito->getFrecuencias();



if ($_GET) {

    $idResponsable = isset($_GET['idResponsable']) != FALSE ? htmlspecialchars($_GET['idResponsable']) : $usuarioSession->getIdUsuario();
    $idEstadoHito = isset($_GET['idEstadoHito']) != FALSE ? htmlspecialchars($_GET['idEstadoHito']) : '';
    $idFrecuenciaHito = isset($_GET['idFrecuenciaHito']) != FALSE ? htmlspecialchars($_GET['idFrecuenciaHito']) : '';

    $totalRegistros = $serviceHito->contarHitosPorFiltro($idResponsable, $idEstadoHito, $idFrecuenciaHito, $usuarioSession->getIdPerfil(), $usuarioSession->getIdContrato()); //OBTENIENDO EL TOTAL DE REGISTROS SEGUN TIPO DE USUARIO
//**************PAGINACION**************//

    $registroPorHoja = 10; //TOTAL DE REGISTROS A MOSTRAR POR PAGINA
    $paginacion->records($totalRegistros); //SETEANDO EL TOTAL DE REGISTROS
    $paginacion->records_per_page($registroPorHoja); //SETEANDO EL TOTAL DE REGISTROS POR HOJA
//paginacion dinamica
    $inicio = $paginacion->get_page() - 1; //la pagina que estamos -1
    $inicio*=$registroPorHoja; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja

    $page = isset($_GET['page']) != FALSE ? $_GET['page'] : '';

    $pagina = $pagina < 10 ? substr($page, 1, 1) : $page; //ELIMINANDO EL CERO A LA IZQUIERDA

    $paginacion->set_page($page); //SETEANDO LA PAGINA

    $hitos = $serviceHito->obtenerHitosPorFiltro($idResponsable, $idEstadoHito, $idFrecuenciaHito, $usuarioSession->getIdPerfil(), $usuarioSession->getIdContrato(), $inicio, $registroPorHoja);
} else {

    $totalRegistros = $serviceHito->contarHitosPorUsuario($usuarioSession); //OBTENIENDO EL TOTAL DE REGISTROS SEGUN TIPO DE USUARIO
//**************PAGINACION**************//

    $registroPorHoja = 10; //TOTAL DE REGISTROS A MOSTRAR POR PAGINA
    $paginacion->records($totalRegistros); //SETEANDO EL TOTAL DE REGISTROS
    $paginacion->records_per_page($registroPorHoja); //SETEANDO EL TOTAL DE REGISTROS POR HOJA
//paginacion dinamica
    $inicio = $paginacion->get_page() - 1; //la pagina que estamos -1
    $inicio*=$registroPorHoja; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja

    switch ($usuarioSession->getIdPerfil()) {

        case 1://SUPER ADMINISTRADOR
            $hitos = $serviceHito->getTodosHitos($inicio, $registroPorHoja);

            break;

        case 3://COORDINARDOR O RESIDENTE
            $hitos = $serviceHito->getHitosContractualesPorContratoPaginados($usuarioSession->getIdContrato(), $inicio, $registroPorHoja);

            break;

        case 8://ENCARGADO DE HITOS
            $hitos = $serviceHito->getHitosContractualesPorContratoPaginados($usuarioSession->getIdContrato(), $inicio, $registroPorHoja);

            break;

        default://USUARIO NORMAL
            $hitos = $serviceHito->getHitosContractualesPorResponsablePaginados($usuarioSession->getIdUsuario(), $inicio, $registroPorHoja);
            break;
    }
}

