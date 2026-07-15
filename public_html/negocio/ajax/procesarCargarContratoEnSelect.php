<?php

require_once '../../data/Contrato.php';

$serviceContrato = new Contrato();
$idArea = htmlspecialchars($_GET['idArea']);
$contratos = $serviceContrato->getContratosPorArea($idArea);
