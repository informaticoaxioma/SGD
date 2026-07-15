<?php
require_once '../../data/Bitacora.php';
require_once '../../data/Funciones.php';

$serviceFunciones = new Funciones();
$serviceBitacora = new Bitacora();

if ($_GET) {
    $idDocumento = htmlspecialchars($_GET['idDocumento']);

    $bitacoras = $serviceBitacora->getBitacoraPorDocumento($idDocumento);
}
?>

<table class="table table-striped tablaCustom maxWidth"> 
    <thead>
        <tr>
            <th>De Usuario</th>
            <th>Asunto</th>
            <th>Fecha</th>                                
            <th>Detalle</th>                                
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($bitacoras)):
            foreach ($bitacoras as $b):
                ?>
                <tr>
                    <td><?php echo $b->getEmisor(); ?></td>
                    <td><?php echo $b->getAsunto(); ?></td>
                    <td><?php echo $serviceFunciones->formatoFecha($b->getFechaEmision()); ?></td>                                       
                    <td><?php echo $b->getDetalleRespuesta(); ?></td>                                      

                </tr>
                <?php
            endforeach;
        endif;
        ?>

    </tbody>
</table>