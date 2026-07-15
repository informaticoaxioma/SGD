<?php
require_once '../../data/Usuario.php';
$serviceUsuario = new Usuario();
$idSubContrato = htmlspecialchars($_GET['idSubContrato']);
$responsables = $serviceUsuario->getUsuariosPorSubContratoActivo($idSubContrato);
?>
<select class="form-control" id="idCorresponsable" name="idCorresponsable" multiple>
    <option value="">Seleccione</option>
    <?php
    if (isset($responsables)):
        foreach ($responsables as $r) :
            ?>
            <option value="<?php echo $r->getIdUsuario(); ?>"><?php echo $r->getNombre() . " " . $r->getApellidoP(); ?></option>
            <?php
        endforeach;
    endif;
    ?>
</select>
