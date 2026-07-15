<?php

require_once '../../../data/Area.php';
require_once '../../../data/Contrato.php';
require_once '../../../data/Funciones.php';

$serviceArea = new Area();
$serviceContrato = new Contrato();
$serviceFunciones = new Funciones();

$areas = $serviceArea->getAreas();

if ($_GET) {
    $idAreaBuscar = htmlspecialchars($_GET['idArea']);
    $contratos = $serviceContrato->getContratosPorArea($idAreaBuscar);
}