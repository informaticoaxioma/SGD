<?php

require_once '../data/Usuario.php';
require_once '../data/Documento.php';
require_once '../data/EstadoDocumento.php';
require_once '../data/SubContrato.php';
require_once '../data/Zebra_Pagination.php';

//Seteando valores de la sesion aun objeto
$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];

//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);
unset($_SESSION['documentosRelacionadosCargaM']);


//cargando modulo de inicio
$serviceDocumento = new Documento();
$serviceUsuario = new Usuario();
$serviceEstadoDoc = new EstadoDocumento();
$serviceSubcontrato = new SubContrato();
$servicePaginacion = new Zebra_Pagination();


$subContratos = $serviceSubcontrato->getSubContratoPorUsuario($usuarioSession->getIdUsuario());

if ($usuarioSession->getIdPerfil() == 1) {
    $docPendientesEntrada = $serviceDocumento->contarDocumentosPorEstado(1, "", 1, 1, 2, "");
    $docPendientesSalida = $serviceDocumento->contarDocumentosPorEstado(1, "", 1, 2, 2, "");
}