<?php require_once '../../negocio/inicio/cargarInicioAjax.php'; ?>
<table id="tablaDocInicio" class="table table-striped table-hover text-left tablaCustom">
    <thead>
        <tr>            
            <th>N° Providencia</th>
            <th>N° Documento</th>
            <th>N° Proceso</th>
            <th>Fecha Documento</th>
            <th>Materia</th>
            <th>Estado</th>
            <th>Flujo</th>
            <?php if ($usuarioSession->getIdPerfil() == 1): ?>
                <th>Responsable</th>        
            <?php endif; ?> 
            <th>SubContrato</th>
            <th>Dias de Vigencia</th>
            <?php if ($usuarioSession->getIdPerfil() != 1): ?>
                <th>Responder</th>
            <?php endif; ?>
            <th>Detalle</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($documentos)) :
            foreach ($documentos as $d):
                $subContrato = $serviceSubcontrato->getSubContratoPorId($d['id_subcontrato']);
                ?>
                <tr>                                                                                
                    <td><?php echo strtoupper($d['num_providencia']) == "" ? 'No Registra' : strtoupper($d['num_providencia']); ?></td>
                    <td><?php echo strtoupper($d['num_documento']); ?></td>
                    <td><?php echo strtoupper($d['num_proceso']) == "" ? 'No Registra' : strtoupper($d['num_proceso']); ?></td>
                    <td><?php echo $serviceDocumento->formatoFecha($d['fecha_documento']); ?></td>
                    <td class="text-justify"><?php echo $d['materia']; ?></td>
                    <td>
                        <?php
                        $estadoDoc = $serviceEstadoDoc->getEstadosPorID($d['id_estado_doc']);
                        echo $estadoDoc->getEstadoDoc();
                        ?>
                    </td>
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
                    <td><?php echo $subContrato->getNombreSubContrato(); ?></td>
                    <td class="<?php echo $color; ?> text-center">
                        <?php
                        echo $diasVigencia;
                        ?>
                    </td>
                    <?php if ($usuarioSession->getIdPerfil() != 1): ?>
                        <td><input type="button" class="btn btn-primary btn-sm" value="Responder" id="btnResponder" data-toggle="modal"  data-target="#modalRespuesta" onclick="CargarModalRespuesta(<?php echo $d['id_documento']; ?>)"></td>                                                                    
                    <?php endif; ?>
                    <td><input type="button" class="btn btn-success btn-sm" value="Detalle" id="btnDetalle" onclick="cargarDetalle(<?php echo $d['id_documento']; ?>)"></td>                                                                    
                </tr>
                <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table> 
<?php $servicePaginacion->render(); ?>