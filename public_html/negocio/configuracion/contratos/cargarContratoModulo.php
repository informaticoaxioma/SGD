<?php

require_once '../../../data/Contrato.php';

$idArea = htmlspecialchars($_GET['idArea']);

$serviceContrato = new Contrato();
$contratos = $serviceContrato->getContratosPorArea($idArea);



if (!empty($contratos)):
    echo '<label for="idContrato">Contrato: </label>';
    echo "<select id='idContrato' name='idContrato' class='form-control'>";
    echo "<option value=''>Seleccione</option>";

    foreach ($contratos as $c) {
        echo "<option value=" . $c->getIdContrato() . ">" . $c->getContrato() . "</option>";
    }

    echo "</select>";


else:

    echo "<select id='idContrato' name='idContrato' class='form-control' disabled>";


    echo "<option value=''>No hay contratos asociados</option>";


    echo "</select>";

endif;

unset($contratos);
