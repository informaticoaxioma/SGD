<?php

require_once '../../data/Documento.php';
require_once '../../data/Usuario.php';
require_once '../../data/SubContrato.php';
require_once '../../data/EstadoDocumento.php';
require_once '../../data/Zebra_Pagination.php';

//ASIGNANDO OBJETO USUARIO DE SESSION A VARIABLE
$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];

//INSTANCEANDO SERVICIOS
$serviceDocumento = new Documento();
$serviceSubContrato = new SubContrato();
$serviceEstadoDoc = new EstadoDocumento();
$servicePaginacion = new Zebra_Pagination();
$serviceUsuario = new Usuario();


if ($_GET) {

    $idSubContrato = htmlspecialchars($_GET['idSubContrato']);
    $idFlujo = htmlspecialchars($_GET['idFlujo']);

    if (isset($_GET['page'])) {
        $pagina = ltrim($_GET['page'], "0") + 1; //ELIMINANDO EL CERO A LA IZQUIERDA
        $servicePaginacion->set_page($_GET['page']); //SETEANDO LA PAGINA
        //CONFIGURANDO PAGINACION
    }


    $totalRegistros = $serviceDocumento->contarDocumentosPorSubContratoYFlujo($idSubContrato, $idFlujo);
    $registrosPorHoja = 10;
//Seteando el service de paginacion
    $servicePaginacion->records($totalRegistros);
    $servicePaginacion->records_per_page($registrosPorHoja);


//paginacion dinamica
    $inicio = $servicePaginacion->get_page() - 1; //la pagina que estamos -1
    $inicio*=$registrosPorHoja; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja


    $documentos = $serviceDocumento->getDocumentosPorSubContratoYFlujoPaginados($idSubContrato, $idFlujo, $inicio, $registrosPorHoja);
    $subContrato = $serviceSubContrato->getSubContratoPorId($idSubContrato);
}
