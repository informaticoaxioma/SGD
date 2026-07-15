<?php require_once '../../negocio/hitoContractual/procesarIngresarHito.php'; ?>

<table class="table table-hover table-condensed tablaCustom">
    <thead>                                 
    <th>Fecha Entrega</th>
    <th>Descripci&oacute;n</th>
    <th>Normativa</th>
</thead>                            
<tbody>
    <?php
    if (isset($hitos)) :
        foreach ($hitos as $h) :
            ?>
            <tr>
                <td><?php echo $serviceDocumento->formatoFecha($h->getFechaEntrega()); ?></td>
                <td><?php echo $h->getDescripcionHito(); ?></td>
                <td><?php echo $h->getNormativa(); ?></td>
            </tr>
            <?php
        endforeach;
    endif;
    ?>
</tbody>
</table>
