<?php

require_once '../../data/HitoContractual.php';
require_once '../../data/Usuario.php';
require_once '../../data/Color.php';

$sessionUsuario = new Usuario();
session_start();
$sessionUsuario = $_SESSION['usuario'];

unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);

$serviceHito = new HitoContractual();
$serviceColor = new Color();
$serviceUsuario = new Usuario();



switch ($sessionUsuario->getIdPerfil()) {

    case 1://ADMINISTRADOR
        $hitos = $serviceHito->getHitosContractuales();

        break;

    case 3: //COORDINADOR DE AREA O RESIDENTE
        $hitos = $serviceHito->getHitosContractualesPorContrato($sessionUsuario->getIdContrato());
        break;

    case 8: //ENCARGADO DE HITOS
        $hitos = $serviceHito->getHitosContractualesPorContrato($sessionUsuario->getIdContrato());
        break;

    default://USUARIO
        $hitos = $serviceHito->getHitosContractualesPorResponsable($sessionUsuario->getIdUsuario());
        break;
}



$hitosJson = array();

foreach ($hitos as $h) {
    
    $colorObj = $serviceColor->getColorPorId($h->getIdColor());
    $color = $colorObj->getColor();

    $aux = array(
        'id' => $h->getIdHito(),
        'title' => $h->getDescripcionHito(),
        'start' => $h->getFechaEntrega(),
        'color' => $color
    );

    array_push($hitosJson, $aux);
}

unset($aux);

echo json_encode($hitosJson);
