<?php require_once '../../negocio/buscador/procesarBuscador.php'; ?>
<table id="tablaResultadoBusqueda" class=" table-hover table-striped"> 
    <thead>
        <tr>
            <th>N° Documento</th>
            <th>N° Providencia</th>
            <th>N° Proceso</th>
            <th>Fecha Documento</th>
            <th>Fecha Recepci&oacute;n</th>
            <th>Fecha Plazo</th>
            <th>Tipo Documento</th>
            <th>Materia</th>
            <th>Remitente</th>
            <th>Destinatario</th>
            <th>Responsable</th>
            <th>Flujo</th>
            <th>Abrir</th>
            <th>Descargar</th>
            <th>Detalle</th>
            <?php if ($sessionUsuario->getIdPerfil() == 1 || $sessionUsuario->getIdPerfil() == 3 || $sessionUsuario->getIdPerfil() == 9) : ?>
                <th>Eliminar</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($documentos)):
            foreach ($documentos as $d) :
                //DATOS DE OTRAS TABLAS
                $tipoDoc = $serviceTipoDocumento->getTipoDocumentoPorId($d['id_tipo_doc']);
                $remitente = $serviceEntidad->getEntidadPorId($d['id_remitente']);
                $destinatario = $serviceEntidad->getEntidadPorId($d['id_destinatario']);
                $responsable = $serviceUsuario->getUsuarioPorId($d['id_responsable']);
                $flujo = $d['id_flujo'] == 1 ? 'Entrada' : 'Salida';
                $c = 0;
                ?>
                <tr>
                    <td><?php echo $d['num_documento']; ?></td>
                    <td><?php echo $d['num_providencia']; ?></td>
                    <td><?php echo $d['num_proceso']; ?></td>
                    <td><?php echo $serviceDocumento->formatoFecha($d['fecha_documento']); ?></td>
                    <td><?php echo $serviceDocumento->formatoFecha($d['fecha_recepcion']); ?></td>
                    <td><?php echo $serviceDocumento->formatoFecha($d['fecha_plazo']); ?></td>
                    <td><?php echo $tipoDoc->getTipoDocumento(); ?></td>
                    <td class="text-justify"><?php echo $d['materia']; ?></td>
                    <td><?php echo $remitente->getNombreEntidad() . " " . $remitente->getApellidoEntidad(); ?></td>
                    <td><?php echo $destinatario->getNombreEntidad() . " " . $destinatario->getApellidoEntidad(); ?></td>
                    <td><?php echo $responsable->getNombre() . " " . $responsable->getApellidoP(); ?></td>
                    <td><?php echo $flujo; ?></td>
                    <td><?php echo $flujo; ?></td>
                    <td>
                        <input type="hidden" id="<?php echo "idDocumento" . $c; ?>" name="<?php echo "idDocumento" . $c; ?>" value="<?php echo $d['id_documento']; ?>">
                        <button id="btnDescargarArchivo" name="btnDescargarArchivo" class="btn btn-success btn-sm" ><i class="fa fa-download"></i>&nbsp;Descargar</button>
                    </td>
                    <td>                      
                        <button id="btnDetalle" name="btnDetalle" class="btn btn-primary btn-sm" ><i class="fa fa-plus-circle"></i>&nbsp;Detalle</button>
                    </td>
                    <td>                      
                        <button id="btnEliminar" name="btnEliminar" class="btn btn-danger btn-sm" ><i class="fa fa-trash"></i>&nbsp;Eliminar</button>
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