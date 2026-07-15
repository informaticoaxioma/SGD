<?php
require '../../data/DocumentoRelacionado.php';
require '../../data/DetalleDocumento.php';

session_start();
$serviceDetalleDocumento = new DetalleDocumento();
$documentosRelacionados = array();
$cont = 0;

//ELIMINAR ELEMENTO DEL ARRAY DE SESION
if ($_GET) {

    $idDocumentoRel = htmlspecialchars($_GET['idDocRelacionado']);
    $aux = array();

    if (isset($_SESSION['documentosRelacionadosEntrada'])) {


        $aux = $_SESSION['documentosRelacionadosEntrada'];


        for ($x = 0; $x < count($aux); $x++) {

            if ($aux[$x] == $idDocumentoRel) {
                unset($aux[$x]);
            }
        }


        $_SESSION['documentosRelacionadosEntrada'] = array_values($aux);

        foreach ($_SESSION['documentosRelacionadosEntrada'] as $d) {
            $idDocumento = $d;
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }

        $cont = count($_SESSION['documentosRelacionadosEntrada']);
    }


    if (isset($_SESSION['documentosRelacionadosSalida'])) {



        $aux = $_SESSION['documentosRelacionadosSalida'];


        for ($x = 0; $x < count($aux); $x++) {

            if ($aux[$x] == $idDocumentoRel) {
                unset($aux[$x]);
            }
        }


        $_SESSION['documentosRelacionadosSalida'] = array_values($aux);

        foreach ($_SESSION['documentosRelacionadosSalida'] as $d) {
            $idDocumento = $d;
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }

        $cont = count($_SESSION['documentosRelacionadosSalida']);
    }


    if (isset($_SESSION['documentosRelacionadosHitos'])) {


        $aux = $_SESSION['documentosRelacionadosHitos'];

        array_slice($aux, $idDocumentoRel);

        for ($x = 0; $x < count($aux); $x++) {

            if ($aux[$x] == $idDocumentoRel) {
                unset($aux[$x]);
            }
        }


        $_SESSION['documentosRelacionadosHitos'] = array_values($aux);

        foreach ($_SESSION['documentosRelacionadosHitos'] as $d) {
            $idDocumento = $d;
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }

        $cont = count($_SESSION['documentosRelacionadosHitos']);
    }


    if (isset($_SESSION['documentosRelacionadosCargaM'])) {


        $aux = $_SESSION['documentosRelacionadosCargaM'];

        array_slice($aux, $idDocumentoRel);

        for ($x = 0; $x < count($aux); $x++) {

            if ($aux[$x] == $idDocumentoRel) {
                unset($aux[$x]);
            }
        }


        $_SESSION['documentosRelacionadosCargaM'] = array_values($aux);

        foreach ($_SESSION['documentosRelacionadosCargaM'] as $d) {
            $idDocumento = $d;
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }

        $cont = count($_SESSION['documentosRelacionadosCargaM']);
    }



    if (isset($_SESSION['documentosRelacionadosDetalle'])) {


        $aux = $_SESSION['documentosRelacionadosDetalle'];

        array_slice($aux, $idDocumentoRel);

        for ($x = 0; $x < count($aux); $x++) {

            if ($aux[$x] == $idDocumentoRel) {
                unset($aux[$x]);
            }
        }


        $_SESSION['documentosRelacionadosDetalle'] = array_values($aux);

        foreach ($_SESSION['documentosRelacionadosDetalle'] as $d) {
            $idDocumento = $d;
            $documento = $serviceDetalleDocumento->getDetallePorIdDocumento($idDocumento);
            array_push($documentosRelacionados, $documento);
        }

        $cont = count($_SESSION['documentosRelacionadosDetalle']);
    }
}


if ($cont != 0):
    $c = 0;
    ?>

    <table class="table table-condensed table-hover">

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
                <tr id="<?php echo "fila" . $c; ?>">             

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
<?php endif; ?>