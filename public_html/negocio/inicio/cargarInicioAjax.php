<?php

require_once '../../data/Usuario.php';
require_once '../../data/Documento.php';
require_once '../../data/EstadoDocumento.php';
require_once '../../data/SubContrato.php';
require_once '../../data/Zebra_Pagination.php';

//Seteando valores de la sesion aun objeto
$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];

//cargando modulo de inicio
$serviceDocumento = new Documento();
$serviceUsuario = new Usuario();
$serviceEstadoDoc = new EstadoDocumento();
$serviceSubcontrato = new SubContrato();
$servicePaginacion = new Zebra_Pagination();
$pagina = "";


if ($_GET) {//SETEANDO VARIABLES A UTILIZAR EN LA PAGINACION

    $pagina = ltrim($_GET['page'], "0") + 1; //ELIMINANDO EL CERO A LA IZQUIERDA
    $servicePaginacion->set_page($_GET['page']); //SETEANDO LA PAGINA
}

switch ($usuarioSession->getIdPerfil()) {

    case 1://ADMINISTRADOR   
        //CONFIGURANDO PAGINACION
        $totalRegistros = $serviceDocumento->contarTotalDocumentos();
        $registrosPorHoja = 10;
        //Seteando el service de paginacion
        $servicePaginacion->records($totalRegistros);
        $servicePaginacion->records_per_page($registrosPorHoja);


        //paginacion dinamica
        $inicio = $servicePaginacion->get_page() - 1; //la pagina que estamos -1
        $inicio*=$registrosPorHoja; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja



        $documentos = $serviceDocumento->getDocumentosPaginados($inicio, $registrosPorHoja);
        $docPendientesEntrada = $serviceDocumento->contarDocumentosPorEstado(1, 0, 1, 1, 0, $usuarioSession->getIdContrato());
        $docPendientesSalida = $serviceDocumento->contarDocumentosPorEstado(1, 0, 1, 2, 0, $usuarioSession->getIdContrato());
        break;

    case 3://COORDINADOR O RESIDENTE        
        //CONFIGURANDO PAGINACION
        $totalRegistros = $serviceDocumento->contarDocumentosPorContrato($usuarioSession->getIdContrato());
        $registrosPorHoja = 10;
        //Seteando el service de paginacion
        $servicePaginacion->records($totalRegistros);
        $servicePaginacion->records_per_page($registrosPorHoja);


        //paginacion dinamica
        $inicio = $servicePaginacion->get_page() - 1; //la pagina que estamos -1
        $inicio*=$registrosPorHoja; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja

        $documentos = $serviceDocumento->getDocumentosPorContratoPaginados($usuarioSession->getIdContrato(), $inicio, $registrosPorHoja);

        break;


    case 7://USUARIO NORMAL
        //CONFIGURANDO PAGINACION
        $totalRegistros = $serviceDocumento->contarDocumentosPorUsuario($usuarioSession->getIdUsuario());
        $registrosPorHoja = 10;
        //Seteando el service de paginacion
        $servicePaginacion->records($totalRegistros);
        $servicePaginacion->records_per_page($registrosPorHoja);


        //paginacion dinamica
        $inicio = $servicePaginacion->get_page() - 1; //la pagina que estamos -1
        $inicio*=$registrosPorHoja; //el resultado lo multiplico por la cantidad de registros, y me da la cantidad de registros a mostrar por hoja


        $subContratos = $serviceSubcontrato->getSubContratoPorContrato($usuarioSession->getIdContrato());
        $documentos = $serviceDocumento->getDocumentosPorUsuarioPaginados($usuarioSession->getIdUsuario(), $inicio, $registrosPorHoja);
        break;
}

