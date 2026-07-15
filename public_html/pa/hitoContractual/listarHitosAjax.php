<?php require_once '../../negocio/hitoContractual/procesarListarHitos.php'; ?>

<table id="tablaHitosAjax" class="table table-hover">
    <thead>
        <tr>
            <th class="text-center">Fecha entrega</th>
            <th class="text-center">Descripci&oacute;n hito</th>
            <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 8) : ?>
                <th class="text-center">Responsable</th>
            <?php endif; ?>
            <th class="text-center">Destinatario</th>
            <th class="text-center">Normativa</th>
            <th class="text-center">Comentario</th>
            <th class="text-center">Estado hito</th>
            <th class="text-center">Frecuencia</th>
            <th class="text-center">Actualizar</th>            
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($hitos)) :
            $c = 0;
            foreach ($hitos as $h) :
                $estadoHito = $serviceEstadoHito->getEstadoPorId($h->getIdEstadoHito());
                $frecuenciaHito = $serviceFrecuenciaHito->getFrecuenciaPorId($h->getIdFrecuenciaHito());
                $responsable = $serviceUsuario->getUsuarioPorId($h->getIdResponsableHito());
                $colorAlerta = "alert alert-danger";
                $colorExito = "alert alert-success";
                ?>
                <tr id="<?php echo $c; ?>">
                    <td><?php echo $serviceDocumento->formatoFecha($h->getFechaEntrega()); ?></td>
                    <td><?php echo $h->getDescripcionHito(); ?></td>

                    <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 8) : ?>
                        <td><?php echo $responsable->getNombre() . " " . $responsable->getApellidoP(); ?></td>
                    <?php endif; ?>

                    <td><?php echo $h->getDestinoInfo(); ?></td>

                    <td><?php echo $h->getNormativa(); ?></td>
                    <td><textarea class="form-control noResize" disabled><?php echo $h->getComentario(); ?></textarea></td>
                    <td class="<?php echo $h->getIdEstadoHito() == 1 ? $colorAlerta : $colorExito; ?>"><?php echo $estadoHito->getEstadoHito(); ?></td>
                    <td><?php echo $frecuenciaHito->getFrecuenciaHito(); ?></td>
                    <td>
                        <input type="hidden" id="idHito" name="idHito" value="<?php echo $h->getIdHito(); ?>">
                        <button type="button" id="btnActualizarHito" name="btnActualizarHito" class="btn btn-success" onclick="actualizarHito(<?php echo $h->getIdHito(); ?>)"><i class="fa fa-edit"></i>&nbsp;Actualizar</button>
                    </td>                    
                </tr>
                <?php
                $c++;
            endforeach;
        endif;
        ?>
    </tbody>                    
</table>
<?php $paginacion->render(); ?>
