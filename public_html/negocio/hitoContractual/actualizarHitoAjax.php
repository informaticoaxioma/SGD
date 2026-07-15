<?php
require_once '../../negocio/hitoContractual/procesarIngresarHito.php';
$documentosHito = $serviceHitoDocumento->getDocumentosPorHito($hito->getIdHito());

if (count($documentosHito) > 0):
    ?>
    <div class="row">
        <label  class="col-sm-2 control-label">Documento(s): </label>
        <div class="col-sm-9">
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <ul class="listaDocumentosRel">
                <?php
                if (isset($documentosHito)):
                    $li = 0;
                    foreach ($documentosHito as $dh) :

                        $documento = $serviceDocumento->getDocumentoPorID($dh->getIdDocumento());
                        ?>
                        <li id="<?php echo "li" . $li; ?>">
                            <form action="../negocio/hitoContractual/procesarDescargarDocumento.php" method="POST" class="form-horizontal">
                                <div class="form-group">
                                    <label class ="col-sm-3 control-label rojo izquierda">
                                        <i class="fa fa-file-pdf-o negro"></i>&nbsp;
                                        <?php
                                        echo $documento[0]['nombre_documento'] != "" ? $documento[0]['nombre_documento'] . " (" . $documento[0]['tamano_documento'] . ")" : "No posee archivo digital";
                                        ?>
                                    </label>     
                                    <div class="col-sm-2">
                                        <input type="hidden" id="idDocumento" name="idDocumento" value="<?php echo $dh->getIdDocumento(); ?>">
                                        <?php if ($documento[0]['nombre_documento'] != "") { ?>
                                            <input type = "submit" class = "btn btn-primary btn-sm" id = "btnDescargarArchivoHito" name = "btnDescargarArchivoHito" value = "Descargar">
                                        <?php }
                                        ?>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="button" class="btn btn-danger btn-sm" onclick="eliminarDocAdjuntosRel(<?php echo $dh->getIdDocumento(); ?>, '<?php echo "li" . $li; ?>', 3)" value="Eliminar">
                                    </div>
                                </div>
                            </form>
                        </li>
                        <?php
                    endforeach;
                endif;
                ?>
            </ul>
        </div>
    </div>

<?php endif; ?>

<form id="formActualizarHito" name="formActualizarHito" class="form-horizontal">

    <div class="form-group">
        <label for="fechaEntrega" class="col-sm-2 control-label">Fecha Entrega:</label>
        <div class="col-sm-2">
            <input type="text" id="fechaEntregaMod" name="fechaEntregaMod" class="form-control"  value="<?php echo $serviceFunciones->formatoFecha($hito->getFechaEntrega()); ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="descripcionHito" class="col-sm-2 control-label">Descripción hito: </label>
        <div class="col-sm-9">
            <textarea class="form-control noResize" cols="4" rows="3" id="descripcionHitoMod" name="descripcionHitoMod"><?php echo $hito->getDescripcionHito(); ?></textarea>
        </div>
    </div>                 
    <div class="form-group">
        <label for="origen" class="col-sm-2 control-label">Responsable: </label>
        <div class="col-sm-4">
            <select id="idResponsableHito" name="idResponsableHito" class="form-control">
                <option value="">Seleccione</option>
                <?php
                if (isset($responsables)) :
                    foreach ($responsables as $r) :
                        if ($r->getIdPerfil() != 1)://no listar al administrador
                            ?>
                            <option value="<?php echo $r->getIdUsuario(); ?>" <?php echo $r->getIdUsuario() == $hito->getIdResponsableHito() ? 'SELECTED' : ''; ?> ><?php echo $r->getNombre() . " " . $r->getApellidoP(); ?></option>
                            <?php
                        endif;
                    endforeach;
                endif;
                ?>
            </select>             
        </div>
    </div>
    <div class="form-group">
        <label for="destino" class="col-sm-2 control-label">Destino: </label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="destinoHitoMod" name="destinoHitoMod" value="<?php echo $hito->getDestinoInfo(); ?>">
        </div>
    </div>  
    <div class="form-group">
        <label for="normativa" class="col-sm-2 control-label">Normativa: </label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="normativaHitoMod" name="normativaHitoMod" value="<?php echo $hito->getNormativa(); ?>">
        </div>
    </div> 

    <div class = "form-group">
        <label  class="col-sm-2 control-label">Relacionar Documento: </label>
        <div class ="col-sm-2">           
            <input type="button" class="btn btn-primary btn-sm" onclick="cargarFormDocRel()" value="Relacionar">
        </div>
    </div>
    <div id="divSeleccionarDoc" class="form-group noDisplay">

        <input type="hidden" id="idContratoBuscar" name="idContratoBuscar" value="<?php echo $sessionUsuario->getIdContrato(); ?>">
        <label for="numDocumentoBuscar" class="col-sm-1 control-label">N&uacute;mero:</label>
        <div class="col-sm-2">
            <input type="text" class="form-control" id="numDocumentoBuscarMod" name="numDocumentoBuscarMod" placeholder="Ingrese N°">
        </div>
        <div class="col-sm-3">
            <select id="idNombreNumero" name="idNombreNumero" class="form-control">
                <option value="1">N° Documento</option>
                <option value="2">N° Providencia</option>
                <option value="3">N° Proceso</option>
            </select>
        </div>
        <label for="materiaBuscar" class="col-sm-1 control-label">Materia:</label>
        <div class="col-sm-3">
            <input type="text" class="form-control" id="materiaBuscarMod" name="materiaBuscarMod" placeholder="Ingrese Materia">
        </div>                        
        <div class="col-sm-1">
            <input type="button" class="btn btn-success" onclick="buscarDocumentos()" value="Buscar">
            <input type="button" class="btn btn-danger" onclick="salirFormDocRel()" value="Cerrar">
        </div>
    </div>

    <div class="resultadoAjax form-group">

    </div>                    

    <div class="form-group">
        <div id="tablaDocsRelacionadosMod" class="col-md-offset-2 col-md-8">

        </div>                                
    </div> 
    <div class="form-group">
        <label for="comentario" class="col-sm-2 control-label">Comentario: </label>
        <div class="col-sm-9">
            <textarea type="text" class="form-control noResize text-justify" rows="7" id="comentarioHitoMod" name="comentarioHitoMod"><?php echo $hito->getComentario(); ?></textarea>
        </div>
    </div> 
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="idEstadoHitoMod" name="idEstadoHitoMod" <?php echo $hito->getIdEstadoHito() == 2 ? 'checked' : ''; ?>> Cumplido
                </label>
            </div>
        </div>
    </div>    

    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-5">
            <input type="hidden" id="idHito" name="idHito" value=""><!--recibe el valor al activar el evento dle calendario -->
            <button type="button" class="btn btn-danger" data-dismiss="modal">Salir</button>
            <button id="btnActualizarHito" class="btn btn-success">Actualizar Hito</button>
        </div>
    </div>
</form>
<div class="row">
    <br/>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="alert alert-success noDisplay mensajeExito">
            <label><i class="fa fa-fw fa-book"></i> Hito Contractual agregado exitosamente</label>
        </div>
    </div>
</div>
<div class="row">
    <br/>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="alert alert-danger noDisplay mensajeError">
            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el Hito Contractual</label>
        </div>
    </div>
</div>