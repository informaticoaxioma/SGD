<?php require_once '../../../negocio/configuracion/tipoDocumentos/procesarIngresarActualizarTipoDoc.php'; ?>
<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Ingresar Tipo Documento</h2>
    </div>
    <div class="panel-body paddingBottom text-center">
        <form id="formIngresarTipoDoc" class="form-inline"> 
            <div class="form-group">
                <label for="nombreDocumento">Nombre Tipo Documento: </label>
                <input type="text" class="form-control" id="nombreDocumento" name="nombreDocumento" placeholder="Ingrese Tipo Documento">
            </div>
            <div class="form-group">               
                <input type="submit" class="btn btn-success" id="btnIngresarTipoDoc" name="btnIngresarTipoDoc" value="Ingresar Tipo Documento">
            </div>         
        </form>
        <br/>

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-8">
                <div class="progress noDisplay gifCarga">
                    <div class="progress-bar progress-bar-danger progress-bar-striped colorAxioma" role="progressbar" aria-valuenow="12" aria-valuemin="0" aria-valuemax="12" style="width: 100%">
                        <span class="sr-only">100% Complete (danger)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mensajeExito noDisplay ">
            <div class="col-xs-12 col-sm-12 col-md-offset-3 col-md-6">
                <div class="alert alert-success">                    
                    <i class="fa fa-book"></i> <label>Tipo Documento Agregado Exitosamente</label>
                </div>
            </div>
        </div>
        <div class="row mensajeError noDisplay">
            <div class="col-xs-12 col-sm-12 col-md-offset-3 col-md-6">
                <div class="alert alert-danger ">                    
                    <i class="fa fa-warning"></i> <label>Error, no se ha podido agregar el Tipo de Documento</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Actualizar / Eliminar Tipo Documento</h2>
    </div>
    <div class="panel-body paddingBottom">       
        <div class="table-responsive text-center divAjax">
            <table class="table table-hover tablaCustom mediumWidth centrar">
                <thead>
                    <tr>
                        <th>Tipo Documento</th>
                        <th>Actualizar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($tipoDocumentos)):
                        $c = 0;
                        foreach ($tipoDocumentos as $td):
                            ?>
                            <tr id="<?php echo $c; ?>">
                                <td><input id="<?php echo "nombreTipoDoc" . $c; ?>" name="<?php echo "nombreTipoDoc" . $c; ?>" value="<?php echo $td->getTipoDocumento(); ?>" class="form-control"></td>
                                <td><input type="button" onclick="actualizarTipoDoc(<?php echo $td->getIdTipoDocumento(); ?>,'<?php echo "nombreTipoDoc" . $c; ?>')" class="btn btn-success" value="Actualizar"></td>
                                <td><input type="button" onclick="eliminarTipoDoc(<?php echo $td->getIdTipoDocumento(); ?>,<?php echo $c; ?>)" class="btn btn-danger" value="Eliminar"></td>
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