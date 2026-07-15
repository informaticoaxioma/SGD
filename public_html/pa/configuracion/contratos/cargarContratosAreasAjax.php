<?php require_once '../../../negocio/configuracion/comun/cargarContratosPorArea.php'; ?>
<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>Nombre Contrato</th>
            <th>Fecha Término</th>
            <th>Area</th>
            <th>Actualizar</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($contratos)) :
            $cont = 0;
            foreach ($contratos as $c) :
                ?>
                <tr id="<?php echo $cont; ?>">
                    <td><input name="<?php echo "nombreContratoMod" . $cont; ?>" id="<?php echo "nombreContratoMod" . $cont; ?>" class="form-control" value="<?php echo $c->getContrato(); ?>"></td>
                    <td><input name="<?php echo "fechaTermino" . $cont; ?>" id="<?php echo "fechaTermino" . $cont; ?>" class="form-control" value="<?php echo $serviceFunciones->formatoFecha($c->getFechaTermino()); ?>"></td>
                    <td>
                        <select id="<?php echo "idArea" . $cont; ?>" name="<?php echo "idArea" . $cont; ?>" class="form-control">
                            <?php
                            if (isset($areas)):
                                foreach ($areas as $a):
                                    ?>
                                    <option value="<?php echo $a->getIdArea(); ?>" <?php echo $c->getIdArea() == $a->getIdArea() ? 'selected' : ''; ?>   ><?php echo $a->getArea(); ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                    <td><input type="button" class="btn btn-success" id="btnActualizarContrato" name="btnActualizarContrato" value="Actualizar Contrato" onclick="actualizarContrato(<?php echo $c->getIdContrato(); ?>, '<?php echo "nombreContratoMod" . $cont; ?>', '<?php echo "idArea" . $cont; ?>','<?php echo "fechaTermino" . $cont; ?>')"></td>
                    <td><input type="button" class="btn btn-danger" id="btnEliminarContrato" name="btnEliminarContrato" value="Eliminar Contrato" onclick="eliminarContrato(<?php echo $c->getIdContrato(); ?>,<?php echo $cont; ?>)"></td>
                </tr>
                <?php
                $cont++;
            endforeach;
        endif;
        ?>
    </tbody>


</table>
