<div class="ajaxCalendario">
    <?php require_once '../../negocio/hitoContractual/procesarIngresarHito.php'; ?>

    <div class="panel panel-default sombraPanel">
        <div class="panel-heading colorAxioma">
            <h2 class="text-center blanco">Hitos contractuales</h2>
        </div>
        <div class="panel-body paddingBottom text-center"> 
            <div class="row">           
                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                    <!-- CALENDARIO -->
                    <div id="calendar" class="col-centered paddingTop">

                    </div>
                </div> 
            </div>
        </div>
    </div>

    <?php if ($sessionUsuario->getIdPerfil() != 7) : ?>

        <!-- MODALES -->
        <div class="modal fade" id="ModalAdd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-lg">
                    <form id="formIngresarHito" class="form-horizontal" method="POST">

                        <div class="modal-header colorAxioma">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times blanco"></i></button>
                            <h2 class="modal-title text-center blanco" id="myModalLabel">Agregar Hito</h2>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="fechaEntrega" class="col-sm-2 control-label">Fecha Entrega:</label>
                                <div class="col-sm-2">
                                    <input type="text" name="fechaEntrega" class="form-control" id="fechaEntrega" disabled>
                                </div>
                            </div>  

                            <div class="form-group">
                                <label for="color" class="col-sm-2 control-label">Color: </label>
                                <div class="col-sm-5 ">
                                    <select id="color" name="color">                                
                                        <?php
                                        if (isset($colores)):
                                            foreach ($colores as $c) :
                                                ?>
                                                <option value="<?php echo $c->getIdColor(); ?>" data-color="<?php echo $c->getColor(); ?>"></option>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="descripcionHito" class="col-sm-2 control-label">Descripción hito: </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control noResize" cols="4" rows="3" id="descripcionHito" name="descripcionHito"></textarea>
                                </div>
                            </div>    

                            <div class="form-group">
                                <label for="origen" class="col-sm-2 control-label">Responsable: </label>
                                <div class="col-sm-4">
                                    <select id="idResponsable" name="idResponsable" class="form-control">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($responsables)) :
                                            foreach ($responsables as $r) :
                                                if ($r->getIdPerfil() != 1)://no listar al administrador
                                                    ?>
                                                    <option value="<?php echo $r->getIdUsuario(); ?>"><?php echo $r->getNombre() . " " . $r->getApellidoP(); ?></option>
                                                    <?php
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>                               
                                </div>
                            </div>    
                            <div class="form-group">
                                <label for="destino" class="col-sm-2 control-label">Destinatario: </label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="destino" name="destino">
                                </div>
                            </div>  
                            <div class="form-group">
                                <label for="normativa" class="col-sm-2 control-label">Normativa: </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="normativa" name="normativa">
                                </div>
                            </div>  

                            <div class="form-group">
                                <label for="idFrecuenciaHito" class="col-sm-2 control-label">Frecuencia Hito: </label>
                                <div class="col-sm-4">
                                    <select id="idFrecuenciaHito" name="idFrecuenciaHito" class="form-control">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($frecuencias)) :
                                            foreach ($frecuencias as $f):
                                                ?>
                                                <option value="<?php echo $f->getIdFrecuenciaHito(); ?>"><?php echo $f->getFrecuenciaHito(); ?></option>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="docRelacionado" class="col-sm-2 control-label">Doc. Relacionado:</label>
                                <div class="col-sm-2">
                                    <input type="button" id="btnDocRelacionado" name="btnDocRelacionado" class="btn btn-success btn-sm" value="Editar">                                    
                                </div>
                            </div>
                            <div id="divSeleccionarDocRel" class="noDisplay">
                                <input type="hidden" id="idContratoBuscar" name="idContratoBuscar" value="<?php echo $sessionUsuario->getIdContrato(); ?>">
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
                                <label for="comentario" class="col-sm-2 control-label">Comentario: </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control noResize" rows="7" id="comentario" name="comentario"></textarea>
                                </div>
                            </div>  

                            <div class="form-group">
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-sm-9">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Salir</button>
                                    <button type="submit" id="btnGuardarHito" class="btn btn-success">Guardar Hito</button>
                                </div>
                            </div>
                            <div class="progress noDisplay gifCarga">
                                <div class="progress-bar progress-bar-striped active colorAxioma maxWidth" role="progressbar"
                                     aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                </div>

                            </div>
                            <h4 class="text-center noDisplay aviso ">Por favor, espere mientras se insertan los hitos</h4>
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
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<div class="modal fade" id="ModalEdit" name="ModalEdit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">           

            <div class="modal-header colorAxioma">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times blanco"></i></button>
                <h2 class="modal-title text-center blanco" >Hito</h2>
            </div>
            <div class="modal-body">                    
                <div id="divAjaxHitoEdit">


                </div>
            </div>

        </div>
    </div>
</div>


