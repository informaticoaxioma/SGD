<?php

require_once '../../data/Cargo.php';
$serviceCargo = new Cargo();

if ($_GET) {
    
    $idCargo = htmlspecialchars($_GET['idCargo']);
    $cargo = $serviceCargo->getCargoPorId($idCargo);

    echo $cargo->getCargo();
}