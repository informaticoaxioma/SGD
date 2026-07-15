<?php require_once '../../../negocio/configuracion/tipoDocumentos/procesarIngresarActualizarTipoDoc.php'; ?>
<table class="table table-hover tablaCustom mediumWidth centrar">
    <thead>
        <tr>
            <th>Tipo Documento</th>
            <th>Actualizar</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($tipoDocumentos)):
            $c = 0;
            foreach ($tipoDocumentos as $td):
                ?>
                <tr id="<?php echo $c; ?>">
                    <td><input id="<?php echo "nombreTipoDoc" . $c; ?>" name="<?php echo "nombreTipoDoc" . $c; ?>" value="<?php echo $td->getTipoDocumento(); ?>" class="form-control"></td>
                    <td><input type="button" onclick="actualizarTipoDoc(<?php echo $td->getIdTipoDocumento(); ?>, '<?php echo "nombreTipoDoc" . $c; ?>')" class="btn btn-success" value="Actualizar"></td>
                    <td><input type="button" onclick="eliminarTipoDoc(<?php echo $td->getIdTipoDocumento(); ?>,<?php echo $c; ?>)" class="btn btn-danger" value="Eliminar"></td>
                </tr>
                <?php
                $c++;
            endforeach;
        endif;
        ?>
    </tbody>
</table>