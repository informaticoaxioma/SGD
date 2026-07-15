<?php require_once '../../../negocio/configuracion/entidades/procesarAdmEntidades.php'; ?>
<table class="table table-hover centrar">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Cargo</th>
            <th>Tipo Entidad</th>
            <th>Actualizar</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($entidades)):
            $cont = 0;
            foreach ($entidades as $e):
                ?>
                <tr id="<?php echo "fila" . $cont; ?>">
                    <td><input type="text" id="nombreEntidadMod" name="nombreEntidadMod" class="form-control" value="<?php echo $e->getNombreEntidad(); ?>"></td>
                    <td><input type="text" id="apellidoEntidadMod" name="apellidoEntidadMod" class="form-control" value="<?php echo $e->getApellidoEntidad(); ?>"></td>
                    <td>
                        <select id="idCargoMod" name="idCargoMod" class="form-control">
                            <?php
                            if (isset($cargos)):
                                foreach ($cargos as $c) :
                                    ?>
                                    <option value="<?php echo $c->getIdCargo(); ?>" <?php echo $c->getIdCargo() == $e->getIdCargo() ? 'Selected' : '' ?>><?php echo $c->getCargo(); ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                    <td>
                        <select id="idTipoEntidadMod" name="idTipoEntidadMod" class="form-control">
                            <?php
                            if (isset($tipoEntidades)) :
                                foreach ($tipoEntidades as $te) :
                                    ?>
                                    <option value="<?php echo $te->getIdTipoEntidad(); ?>" <?php echo $te->getIdTipoEntidad() == $e->getIdTipoEntidad() ? 'Selected' : ''; ?>><?php echo $te->getTipoEntidad(); ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                    <td><input type="button" id="btnModEntidad" name="btnModEntidad" class="btn btn-success" value="Actualizar"></td>
                    <td><input type="button" id="btnEliminarEntidad" name="btnEliminarEntidad"  class="btn btn-danger" value="Eliminar" onclick="eliminarEntidad('<?php echo $e->getIdEntidad(); ?>', '<?php echo "fila" . $cont; ?>')"></td>
                </tr>
                <?php
                $cont++;
            endforeach;
        endif;
        ?>
    </tbody>

</table>
