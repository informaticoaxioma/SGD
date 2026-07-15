<?php

require_once '../../data/SubContrato.php';
$serviceSubContrato = new SubContrato();
$idContrato = htmlspecialchars($_GET['idContrato']);
$subContratos = $serviceSubContrato->getSubContratoPorContrato($idContrato);

if (!empty($subContratos)):

    echo "<select id='idSubContratos' name='idSubContratos' class='form-control' multiple>";   

    foreach ($subContratos as $sc) {
        
        echo "<option value=" . $sc->getIdSubContrato() . ">" . $sc->getNombreSubContrato() . "</option>";
    }

    echo "</select>";


else:

    echo "<select id='idSubContratos' name='idSubContratos' class='form-control' disabled>";


    echo "<option value=''>No hay Sub-Contratos asociados</option>";


    echo "</select>";

endif;
