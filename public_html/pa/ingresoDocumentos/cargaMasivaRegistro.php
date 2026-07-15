<?php require_once '../../negocio/ingresoDocumento/procesarCargaMasiva.php'; ?>
<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Carga masiva de registros</h2>
    </div>
    <div class="panel-body paddingBottom text-center">
        <form id="formCargaMasiva" name="formCargaMasiva" class="form-horizontal text-left">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="form-group">
                        <label for="contrato" class="col-sm-4 control-label">Contrato:</label>
                        <div class="col-sm-6">
                            <input type="text" id="nombreContrato" name="nombreContrato" class="form-control" value="<?php echo $contrato->getContrato(); ?>" disabled="true">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="idSubContrato" class="col-sm-4 control-label">Sub-Contrato:</label>
                        <div class="col-sm-6">
                            <select id="idSubContrato" name="idSubContrato" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($subContratos)) :
                                    foreach ($subContratos as $sc) :
                                ?>
                                        <option value="<?php echo $sc->getIdSubcontrato(); ?>"><?php echo $sc->getNombreSubContrato(); ?></option>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="idTipoDoc" class="col-sm-4 control-label">Tipo Documento:</label>
                        <div class="col-sm-6">
                            <select id="idTipoDoc" name="idTipoDoc" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($tipoDocumentos)) :
                                    foreach ($tipoDocumentos as $td) :
                                ?>
                                        <option value="<?php echo $td->getIdTipoDocumento(); ?>"><?php echo $td->getTipoDocumento(); ?></option>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="idFlujo" class="col-sm-4 control-label">Flujo:</label>
                        <div class="col-sm-6">
                            <select id="idFlujoT" name="idFlujoT" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="1">Entrada</option>
                                <option value="2">Salida</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="idResponsable" class="col-sm-4 control-label">Responsable:</label>
                        <div class="col-sm-6">
                            <select id="idResponsable" name="idResponsable" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($responsables)) :
                                    foreach ($responsables as $r) :
                                ?>
                                        <option value="<?php echo $r->getIdUsuario(); ?>"><?php echo $r->getNombre() . " " . $r->getApellidoP() . " " . $r->getApellidoM(); ?></option>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>


                </div>
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="form-group">
                        <label for="idEstadoDoc" class="col-sm-4 control-label">Estado Documento:</label>
                        <div class="col-sm-4">
                            <select id="idEstadoDoc" name="idEstadoDoc" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($estadoDocs)) :
                                    foreach ($estadoDocs as $ed) :
                                ?>
                                        <option value="<?php echo $ed->getIdEstadoDoc(); ?>"><?php echo $ed->getEstadoDoc(); ?></option>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="divCargaDocumentos" class="form-group">
                        <label for="cargaDocumentos" class="col-sm-4 control-label">Documentos:</label>
                        <div class="col-sm-4">
                            <input type="file" id="cargaDocumentos" name="cargaDocumentos[]" multiple>
                        </div>
                    </div>
                    <div id="divArchivo" class="form-group">
                        <label for="archivoExcel" class="col-sm-4 control-label">Archivo Excel:</label>
                        <div class="col-sm-4">
                            <input type="file" id="archivoExcel" name="archivoExcel">
                        </div>
                    </div>             
                    <label class="col-sm-4 control-label">Formato Carga:</label>
                    <div class="col-sm-4">
                        <a href="../negocio/FormatoCargaMasivaSGD2024.xlsx"><img src="media/excelNuevo.png" style="width: 50px; height: auto;" alt="iconoExcel"></a>       
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-4">
                            <input type="submit" id="btnCargarArchivos" name="btnCargarArchivos" class="btn btn-success" value="Cargar Registros">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="progress noDisplay gifCarga">
            <div class="progress-bar progress-bar-striped active colorAxioma maxWidth" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
            </div>

        </div>
        <h2 id="tituloEspera" class="noDisplay">Por favor espere, se están cargando los datos ingresados...</h2>
        <div class="row mensajeExito noDisplay">
            <div class="col-xs-12 col-sm-12 col-md-offset-3 col-md-6">
                <div class="alert alert-success">
                    <i class="fa fa-user"></i> <label>Registros agregados Exitosamente</label>
                </div>
            </div>
        </div>
        <div class="row mensajeError noDisplay">
            <div class="col-xs-12 col-sm-12 col-md-offset-3 col-md-6">
                <div class="alert alert-danger ">
                    <i class="fa fa-warning"></i> <label>Error, no se han podido agregar los registros</label>
                </div>
            </div>
        </div>
    </div>
</div>