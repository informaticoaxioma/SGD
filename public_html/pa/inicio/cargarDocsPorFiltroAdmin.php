<?php require_once '../../negocio/inicio/procesarCargarDocPorFiltroAdmin.php'; ?>
<input type="hidden" id="idFlujo" name="idFlujo" value="<?php echo $idFlujo; ?>">
<table id="tablaDocInicio" class="table table-striped table-hover text-left tablaCustom">
    <thead>
        <tr>          
            <th>N° Providencia</th>
            <th>N° Documento</th>
            <th>N° Proceso</th>
            <th>Fecha Documento</th>
            <th>Materia</th>           
            <th>Flujo</th>          
            <th>Responsable</th> 
            <th>SubContrato</th>
            <th>Dias de Vigencia</th>          
            <th>Detalle</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($documentos)) :
            $cont = 0;
            foreach ($documentos as $d):
                ?>
                <tr id="<?php echo "filaDoc" . $cont; ?>">                                                                             
                    <td><?php echo strtoupper($d['num_providencia']) == "" ? 'No Registra' : strtoupper($d['num_providencia']); ?></td>
                    <td><?php echo strtoupper($d['num_documento']); ?></td>
                    <td><?php echo strtoupper($d['num_proceso']) == "" ? 'No Registra' : strtoupper($d['num_proceso']); ?></td>
                    <td><?php echo $serviceDocumento->formatoFecha($d['fecha_documento']); ?></td>
                    <td class="text-justify"><?php echo $d['materia']; ?></td>                  
                    <td>
                        <?php
                        if ($d['id_flujo'] == 1) {
                            echo "Entrada";
                        } else {
                            echo "Salida";
                        }
                        ?>

                    </td>

                    <?php
                    if ($usuarioSession->getIdPerfil() == 1):
                        $responsable = $serviceUsuario->getUsuarioPorId($d['id_responsable']);
                        ?>
                        <td>
                            <?php echo $responsable != "" ? $responsable->getNombre() . " " . $responsable->getApellidoP() : "Sin Responsable"; ?>
                        </td>
                        <?php
                    endif;
                    $diasVigencia = $serviceDocumento->calculaDiasDeVigencia($d['id_documento'], $d['fecha_plazo']);
                    $color = $diasVigencia < 7 ? 'rojo' : '';
                    ?>
                    <td>
                        <?php
                        $subContrato = $serviceSubContrato->getSubContratoPorId($d['id_subcontrato']);
                        echo $subContrato->getNombreSubContrato();
                        ?>
                    </td>
                    <td class="text-center <?php echo $color; ?> ">
                        <?php
                        echo $diasVigencia;
                        ?>
                    </td>                    
                    <td><input type="button" class="btn btn-success btn-sm" value="Detalle" id="btnDetalle" onclick="cargarDetalle(<?php echo $d['id_documento']; ?>)"></td>                                                                 
                    <td><input type="button" class="btn btn-danger btn-sm" value="Eliminar" id="btnEliminarDoc" onclick="eliminarDocumento(<?php echo $d['id_documento']; ?>,'<?php echo "filaDoc" . $cont; ?>')"></td>                                                                 
                </tr>
                <?php
                $cont++;
            endforeach;
        endif;
        ?>
    </tbody>
</table> 
<?php $servicePaginacion->render(); ?>