<?php require_once '../../../negocio/configuracion/perfiles/procesarAdministrarPerfiles.php';?>

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>Nombre Perfil</th>
            <th>Actualizar</th>
            <th>Eliminar</th>                                    
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($perfiles)):
            $cont = 0;
            foreach ($perfiles as $p) :
                ?>
                <tr id="<?php echo $cont; ?>">
                    <td><input id="<?php echo "perfil" . $cont; ?>" name="<?php echo "perfil" . $cont; ?>" class="form-control" type="text" value="<?php echo $p->getPerfil(); ?>"></td>
                    <td><input type="button" id="btnActualizarPerfil" name="btnActualizarPerfil" class="btn btn-success" value="Actualizar Perfil" onclick="actualizarPerfil(<?php echo $p->getIdPerfil(); ?>, '<?php echo "perfil" . $cont; ?>')"></td>
                    <td><input type="button" id="btnEliminarPerfil" name="btnEliminarPerfil" class="btn btn-danger" value="Eliminar Perfil" onclick="eliminarPerfil(<?php echo $p->getIdPerfil(); ?>,<?php echo $cont; ?>)"></td>
                </tr>
                <?php
                $cont++;
            endforeach;
        endif;
        ?>
    </tbody>
</table>
