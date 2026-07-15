<?php
require_once '../data/SubContrato.php';
require_once '../data/UsuarioSubcontrato.php';

$idContrato = htmlspecialchars($_POST['idContrato']);
$idUsuario =  htmlspecialchars($_POST['idUsuario']);

$serviceSubContrato = new SubContrato();
$serviceUsuarioSubContrato = new UsuarioSubcontrato();
$subContratos = $serviceSubContrato->getSubContratoPorContrato($idContrato);
$usuarioSubContrato = $serviceUsuarioSubContrato->getSubcontratosDelUsuario($idUsuario);

if(!empty($subContratos)) {
    echo '<select name="cmbSubContra" id="cmbSubContra" multiple class="form-control" >';
    foreach ($subContratos as $c) {
        $selected = '';
        foreach ($usuarioSubContrato as $us){
            if(!($c->getIdSubContrato() != $us)){
                $selected = ' selected ';
            }
        }
        echo "<option value=" . $c->getIdSubContrato() . $selected . ">" . $c->getNombreSubContrato() . "</option>";
    }

}else{
    echo "<select id='cmbSubContra' name='cmbSubContra' class='form-control' disabled>";
    echo "<option value=''>No hay subcontratos asociados al contrato</option>";
}
echo "</select>";