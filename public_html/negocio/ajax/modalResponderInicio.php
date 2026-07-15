<?php require_once '../../negocio/inicio/procesarResponderInicio.php'; ?>

<div class="row">
    <div class="col-md-10">       
        <form id="formEnviarRespuesta" name="formEnviarRespuesta" class="form-horizontal text-left">
            <div class="form-group">
                <label  class="col-sm-3 control-label">Respuesta a documento: </label>
                <div class="col-sm-8">
                    <textarea id="respuesta" name="respuesta" class="form-control noResize" rows="2" disabled="true" ><?php echo $documento[0]['materia']; ?></textarea>
                    <input type="hidden" id="idDocumento" name="idDocumento" value="<?php echo $documento[0]['id_documento']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="emisor" class="col-sm-3 control-label">Emite Respuesta: </label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="emisor" id="emisor" value="<?php echo $usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP() . " " . $usuarioSession->getApellidoM(); ?>" disabled="true">
                </div>
            </div>
            <div class="form-group">
                <label for="fechaRespuesta" class="col-sm-3 control-label">Fecha: </label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="fechaRespuesta" name="fechaRespuesta" value="<?php echo $fechaActual; ?>" disabled="true">
                </div>
            </div>
            <div class="form-group">
                <label for="asunto" class="col-sm-3 control-label">Asunto: </label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="asunto" name="asunto" value="<?php echo "RE: " . $documento[0]['materia']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="detalle" class="col-sm-3 control-label">Detalle: </label>
                <div class="col-sm-8">
                    <textarea id="detalle" name="detalle" class="form-control noResize" rows="5" placeholder="Ingrese detalle..."></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-8">
                    <div class="checkbox">
                        <label>
                            <input id="cerrarDoc" name="cerrarDoc" type="checkbox" value="2" <?php echo $documento[0]['id_estado_doc'] == 2 ? 'Checked' : ''; ?>>&nbsp;Marcar para cerrar registro de documento.<!--el valor  2 es el valor de cerrado en la base de datos -->
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="btnResponder" class="col-sm-3 control-label"></label>
                <div class="col-sm-2">
                    <input type="submit" id="btnResponder" name="btnResponder" class="btn btn-success" value="Responder">
                </div>
                <div class="col-sm-2">
                    <input type="button" class="btn btn-danger" data-dismiss="modal" value="Salir">
                </div>
            </div>
        </form>
        <div class="row">
            <br/>
            <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-2">
                <div class="alert alert-success noDisplay mensajeExito">
                    <label><i class="fa fa-fw fa-book"></i> Respuesta enviada exitosamente</label>
                </div>
            </div>
        </div>
        <div class="row">
            <br/>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="alert alert-danger noDisplay mensajeError">
                    <label><i class="fa fa-fw fa-warning"></i> Error, no se ha podido enviar la respuesta</label>
                </div>
            </div>
        </div>
    </div>

</div>