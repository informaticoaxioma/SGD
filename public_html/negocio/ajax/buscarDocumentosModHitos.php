<?php
require_once '../../data/Documento.php';
require_once '../../data/Usuario.php';
session_start();
$idContrato = htmlspecialchars($_GET['idContrato']);
$numDocumento = htmlspecialchars($_GET['numDoc']);
$idNombreNumero = htmlspecialchars($_GET['idNombreNumero']);
$materia = htmlspecialchars($_GET['materia']);
$serviceDocumento = new Documento();
$usuario = new Usuario();
$usuario = $_SESSION['usuario'];
$documentos = $serviceDocumento->getDocumentosDetallePorContrato($idContrato, $numDocumento, $idNombreNumero, $materia, $usuario->getIdPerfil());

if ($idContrato != ""):
    ?>

    <table id="tablaDocRelacionadosHitos" name="tablaDocRelacionadosHitos" class="table table-hover table-striped">
        <thead>
            <tr>
                <th></th>
                <th>N&uacute;mero</th>
                <th>Fecha</th>
                <th>Materia</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($documentos)) :
                $c = 1;
                foreach ($documentos as $d) :
                    ?>
                    <tr id="<?php echo "filaMod".$c; ?>">
                        <td><?php echo $c; ?></td>
                        <?php
                        switch ($idNombreNumero) {
                            case 1:

                                echo " <td>" . $d['num_documento'] . "</td>";
                                break;

                            case 2:

                                echo " <td>" . $d['num_providencia'] . "</td>";
                                break;

                            case 3:

                                echo " <td>" . $d['num_proceso'] . "</td>";
                                break;
                        }
                        ?>

                        <td><?php echo $serviceDocumento->formatoFecha($d['fecha_documento']); ?></td>
                        <td><?php echo $d['materia']; ?></td>
                        <td><input type="button" class="btn btn-success bn-sm" value="Agregar" onclick="relacionarDocumentoModHitos(<?php echo $d['id_documento']; ?>,'<?php echo "filaMod".$c; ?>')"></td>
                    </tr>

                    <?php
                    $c++;
                endforeach;

            endif;
            ?>
        </tbody>

    </table>

<?php else : ?>
    <div class="row">
        <br/>
        <div class="col-xs-12 col-sm-12 col-md-12 mensajeError">
            <div class="alert alert-danger">
                <label><i class="fa fa-fw fa-warning"></i> Por favor, seleccione un Contrato</label>
            </div>
        </div>

    </div>

<?php
endif;
?>