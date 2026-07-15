<?php

require_once '../../data/DocumentoRelacionado.php';
require_once '../../data/DetalleDocumento.php';
session_start();
$serviceDetalleDocumento = new DetalleDocumento();

if ($_GET) {

    $idDocumentoRel = htmlspecialchars($_GET['idDocRelacionado']);

    if (isset($_SESSION['documentosRelacionadosEntrada'])) {
        array_push($_SESSION['documentosRelacionadosEntrada'], $idDocumentoRel);
    } else {

        if (isset($_SESSION['documentosRelacionadosSalida'])) {

            array_push($_SESSION['documentosRelacionadosSalida'], $idDocumentoRel);
            
        } else {

            array_push($_SESSION['documentosRelacionadosHitos'], $idDocumentoRel);
        }
    }
}



$documentosRelacionados = array();

if (isset($_SESSION['documentosRelacionadosEntrada'])) {


    for ($i = 0; $i < count($_SESSION['documentosRelacionadosEntrada']); $i++) {
        $idDocumento = $_SESSION['documentosRelacionadosEntrada'][$i];

        $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
        array_push($documentosRelacionados, $documento);
    }
    
} else {

    if (isset($_SESSION['documentosRelacionadosSalida'])) {

        for ($i = 0; $i < count($_SESSION['documentosRelacionadosSalida']); $i++) {
            $idDocumento = $_SESSION['documentosRelacionadosSalida'][$i];
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }
    } else {

        for ($i = 0; $i < count($_SESSION['documentosRelacionadosHitos']); $i++) {
            $idDocumento = $_SESSION['documentosRelacionadosHitos'][$i];
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }
    }
}

$c = 0;
?>

<table class="table table-condensed table-hover tablaDocRel">
    <thead>
        <tr>
            <th>Documento</th>
            <th>Providencia</th>
            <th>Proceso</th>            
            <th>Materia</th>
        </tr>        
    </thead>
    <tbody>
        <?php foreach ($documentosRelacionados as $d) : ?>
            <tr id="<?php echo "fila" . $c; ?> ">             

                <td><?php echo $d->getNumDocumento(); ?></td>
                <td><?php echo $d->getNumProvidencia(); ?></td>
                <td><?php echo $d->getNumProceso(); ?></td>
                <td><?php echo $d->getMateria(); ?></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarDocRelacionadoSession(<?php echo $d->getIdDocumento(); ?>, '<?php echo "fila" . $c; ?>')"><i class="fa fa-trash"></i></button></td>
            </tr>
            <?php
            $c++;
        endforeach;
        ?>
    </tbody>
</table>