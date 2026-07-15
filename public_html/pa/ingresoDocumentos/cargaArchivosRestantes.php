<?php require_once '../../negocio/ingresoDocumento/procesarCargarArchivosRestantes.php'; ?>
<div class="divAjax">
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
</div>


<!--MODAL CARGA DE ARCHIVO -->

<div class="modal fade " id="modalCargarArchivo" tabindex="-1" role="dialog" aria-labelledby="modalCargarArchivo">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header colorAxioma">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times blanco"></i></button>
                <h4 class="modal-title blanco text-center">Cargar Archivo</h4>
            </div>
            <div class="modal-body">
                <form id="formCargarArchivosRestantes" name="formCargarArchivosRestantes" class="form-horizontal">
                    <br/>
                    <input type="hidden" id="idDocModal" name="idDocModal">
                    <div class="form-group text-center">                       
                        <div class="col-sm-4">
                            <label id="numDocModal" name ="numDocModal"></label>
                        </div>
                        <div class="col-sm-4">
                            <label id="numProvidenciaModal" name="numProvidenciaModal" ></label>
                        </div>
                        <div class="col-sm-4">
                            <label id="numProcesoModal" name="numProcesoModal" ></label>
                        </div>                           
                    </div>
                    <div id="divArchivoModal" class="form-group">
                        <label  class="col-sm-2 control-label">Archivo: </label>
                        <div class="col-sm-10">
                            <input type="file" id="archivo" name="archivo" accept=".pdf" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label  class="col-sm-2 control-label">Adjunto: </label>
                        <div class="col-sm-10">
                            <input type="file" id="adjunto" name="adjunto" onchange="detalleAdjuntos()" multiple="multiple" accept=".pdf">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div id="listadoAdjunto" class="col-sm-8">

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="docRelacionado" class="col-sm-2 control-label">Doc. Relacionado:</label>
                        <div class="col-sm-2">
                            <input type="button" id="btnDocRelacionado" name="btnDocRelacionado" class="btn btn-primary btn-sm" value="Editar">                                    
                        </div>
                    </div>
                    <div id="divSeleccionarDocRel" class="noDisplay">
                        <input type="hidden" id="idContratoBuscar" name="idContratoBuscar" value="<?php echo $usuarioSession->getIdContrato(); ?>">
                        <div class="form-group">
                            <label for="numDocumentoBuscar" class="col-sm-1 control-label">N&uacute;mero:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="numDocumentoBuscar" name="numDocumentoBuscar" placeholder="Ingrese N°">
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
                                <input type="text" class="form-control" id="materiaBuscar" name="materiaBuscar" placeholder="Ingrese Materia">
                            </div>                        
                            <div class="col-sm-1">
                                <input type="button" class="btn btn-success" id="btnBuscarDoc" name="btnBuscarDoc" value="Buscar">
                                <input type="button" class="btn btn-danger" id="btnCerrarBuscarDoc" name="btnCerrarBuscarDoc" value="Cerrar">
                            </div>
                        </div>

                        <div class="resultadoAjax">

                        </div> 
                    </div>
                    <div class="row">
                        <div id="tablaDocsRelacionados" class="col-md-offset-2 col-md-8">

                        </div>                                
                    </div>
                    <div class="form-group">
                        <label  class="col-sm-2 control-label"></label>
                        <div class="col-sm-2">
                            <input type="submit" class="btn btn-success" id="btnSubirArchivos" name="btnSubirArchivos" value="Cargar Archivos">
                        </div>
                        <div class="col-sm-2">
                            <input type="button" class="btn btn-danger" value="Salir"  data-dismiss="modal" aria-label="Close">
                        </div>
                    </div>
                </form>
                <!--MENSAJES -->
                <div class="progress noDisplay gifCarga">
                    <div class="progress-bar progress-bar-striped active colorAxioma maxWidth" role="progressbar"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="alert alert-success noDisplay mensajeExito">
                            <label><i class="fa fa-fw fa-book"></i> Documento agregado exitosamente</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="alert alert-danger noDisplay mensajeError">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el Documento</label>
                        </div>
                    </div>

                </div>

                <!--FIN MENSAJES -->
            </div>
        </div>
    </div>
</div>
