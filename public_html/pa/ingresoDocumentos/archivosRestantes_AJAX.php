<?php require_once '../../negocio/ingresoDocumento/procesarCargarArchivosRestantes.php'; ?>
<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Carga de archivos restantes</h2>
    </div>
    <div class="panel-body paddingBottom">
        <div class="table-responsive">
            <table class="table tablaCustom centrar">
                <thead>
                    <tr>
                        <th>N° Providencia</th>
                        <th>N° Documento</th>
                        <th>Materia</th>
                        <th>N° Fecha Recepci&oacute;n</th>
                        <th>Cargar Archivo</th>
                        <th>Eliminar Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($documentosSinArchivos)):
                        $c = 0;
                        foreach ($documentosSinArchivos as $d) :
                            ?>
                            <tr id="<?php echo $c; ?>">
                                <td><?php echo $d['num_providencia']; ?></td>
                                <td><?php echo $d['num_documento']; ?></td>
                                <td><?php echo $d['materia']; ?></td>
                                <td><?php echo $serviceDocumento->formatoFecha($d['fecha_recepcion']); ?></td>
                                <td><input onclick="cargarIdDocModalArchivos(<?php echo $d['id_documento']; ?>, '<?php echo $d['num_documento']; ?>', '<?php echo $d['num_providencia']; ?>', '<?php echo $d['num_proceso']; ?>')" type="button" id="btnCargarArchivos" name="btnCargarArchivos" class="btn btn-success" value="Cargar Archivo" data-toggle="modal" data-target="#modalCargarArchivo"></td>
                                <td><input onclick="eliminarRegistro(<?php echo $d['id_documento']; ?>,<?php echo $c; ?>)" type="button" id="btnEliminarArchivos" name="btnEliminarArchivos" class="btn btn-danger" value="Eliminar Registro"></td>
                            </tr>
                            <?php
                            $c++;
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
