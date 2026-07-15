<?php require_once '../../../negocio/configuracion/cargos/procesarAdministrarCargos.php'; ?>
<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>Nombre Cargo</th>
            <th>Actualizar</th>
            <th>Eliminar</th>                                    
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($cargos)):
            $cont = 0;
            foreach ($cargos as $c) :
                ?>
                <tr id="<?php echo $cont; ?>">
                    <td><input id="<?php echo "cargo" . $cont; ?>" name="<?php echo "cargo" . $cont; ?>" class="form-control" type="text" value="<?php echo $c->getCargo(); ?>"></td>
                    <td><input type="button" id="btnActualizarCargo" name="btnActualizarCargo" class="btn btn-success" value="Actualizar Cargo" onclick="actualizarCargo(<?php echo $c->getIdCargo(); ?>, '<?php echo "cargo" . $cont; ?>')"></td>
                    <td><input type="button" id="btnEliminarCargo" name="btnEliminarCargo" class="btn btn-danger" value="Eliminar Cargo" onclick="eliminarCargo(<?php echo $c->getIdCargo(); ?>,<?php echo $cont; ?>)"></td>

                </tr>
                <?php
                $cont++;
            endforeach;
        endif;
        ?>
    </tbody>
</table>