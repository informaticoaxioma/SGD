<?php require_once '../../../negocio/configuracion/contratos/procesarAgregarActualizarEliminarContrato.php'; ?>
<!--CONTRATOS -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Ingresar Contrato</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <form id="formIngresarContrato" class="form-inline"> 
                    <div class="form-group">
                        <label for="nombreContrato">Nombre Contrato: </label>
                        <input type="text" class="form-control" id="nombreContrato" name="nombreContrato" placeholder="Ingrese Contrato">
                    </div>
                    <div class="form-group">
                        <label for="fechaTermino">Fecha T&eacute;rmino: </label>
                        <input type="text" class="form-control" id="fechaTermino" name="fechaTermino" placeholder="DD-MM-AA">
                    </div>
                    <div class="form-group">
                        <label for="idArea">&Aacute;rea: </label>
                        <select id="idArea" name="idArea" class="form-control ">
                            <option value="">Seleccione &Aacute;rea</option>
                            <?php
                            if (isset($areas)):
                                foreach ($areas as $a) :
                                    ?>
                                    <option value="<?php echo $a->getIdArea(); ?>"><?php echo $a->getArea(); ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>

                        </select> 
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-success" id="btnIngresarContrato" name="btnIngresarContrato" value="Ingresar Contrato">
                    </div>
                </form>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-success noDisplay mensajeExito">
                            <label><i class="fa fa-fw fa-briefcase"></i> Contrato Agregado exitosamente</label>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-danger noDisplay  mensajeError">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el contrato</label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>    
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Actualizar / Eliminar Contrato</h2>
            </div>
            <div class="panel-body paddingBottom">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-4 col-md-offset-4">
                        <select id="idAreaBuscar" name="idAreaBuscar" class="form-control ">
                            <option value="">Seleccione &Aacute;rea</option>
                            <?php
                            if (isset($areas)):
                                foreach ($areas as $a) :
                                    ?>
                                    <option value="<?php echo $a->getIdArea(); ?>"><?php echo $a->getArea(); ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select> 
                    </div>
                </div>              
                <div class="row">
                    <div id="divContratoAjax" class="table-responsive col-xs-12 col-sm-12 col-md-12 paddingTop">

                    </div>  
                </div>
            </div>
        </div>
    </div>    
</div>
<!-- FIN CONTRATOS -->


<!-- SUB CONTRATOS -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Ingresar Sub-Contrato</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <form id="formIngresarSubContrato" class="form-inline text-center"> 
                    <div class="form-group">
                        <label for="nombreSubContrato">Sub-Contrato: </label>
                        <input type="text" class="form-control" id="nombreSubContrato" name="nombreSubContrato" placeholder="Ingrese Contrato">
                    </div>
                    <div class="form-group">
                        <label for="idAreaSubC">&Aacute;rea: </label>
                        <select id="idAreaSubC" name="idAreaSubC" class="form-control ">
                            <option value="">Seleccione &Aacute;rea</option>
                            <?php
                            if (isset($areas)):
                                foreach ($areas as $a) :
                                    ?>
                                    <option value="<?php echo $a->getIdArea(); ?>"><?php echo $a->getArea(); ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>

                        </select> 
                    </div>
                    <div class="form-group divAjaxContrato">
                        <label for="idContrato">Contrato: </label>
                        <select id="idContrato" name="idContrato" class="form-control">
                            <option value="">Seleccione</option>
                        </select> 
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-success" id="btnIngresarContrato" name="btnIngresarContrato" value="Ingresar Contrato">
                    </div>
                </form>

                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-success noDisplay mensajeExitoSubC">
                            <label><i class="fa fa-fw fa-briefcase"></i> Sub-Contrato Agregado exitosamente</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-danger noDisplay  mensajeErrorSubC">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el Sub-Contrato</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Actualizar / Eliminar Sub-Contrato</h2>
            </div>
            <div class="panel-body paddingBottom">
                <div class="row">
                    <div class="table-responsive col-md-8 col-md-offset-2">                       
                        <table class="table table-condensed table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre Sub-Contrato</th>
                                    <th>Contrato</th>
                                    <th>Actualizar</th>
                                    <th>Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($subContratos)) :
                                    $cont = 0;
                                    foreach ($subContratos as $s) :
                                        ?>
                                        <tr id="<?php echo "fmsc" . $cont; ?>">
                                            <td><?php echo $s->getNombreSubContrato(); ?></td>
                                            <td>
                                                <select id="<?php echo "idContratoModSub" . $cont; ?>" name="<?php echo "idContratoModSub" . $cont; ?>" class="form-control">
                                                    <?php
                                                    if (isset($contratos)):
                                                        foreach ($contratos as $c) :
                                                            ?>
                                                            <option value="<?php echo $c->getIdContrato(); ?>"  <?php echo $c->getIdContrato() == $s->getIdContrato() ? 'Selected' : ''; ?>   ><?php echo $c->getContrato(); ?></option>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="button" id="btnActualizar" name="btnActualizar" class="btn btn-success" value="Actualizar" onclick="actualizarSubContrato('<?php echo $s->getIdSubcontrato(); ?>', '<?php echo "idContratoModSub" . $cont; ?>')">
                                            </td>
                                            <td>
                                                <input type="button" id="btnEliminar" name="btnEliminar" class="btn btn-danger" value="Eliminar" onclick="eliminarSubContrato('<?php echo $s->getIdSubcontrato(); ?>', '<?php echo "fmsc" . $cont; ?>');" >
                                            </td>
                                        </tr>

                                        <?php
                                        $cont++;
                                    endforeach;
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>                
                </div>

            </div>
        </div>
    </div>
</div>   

<!-- FIN SUBCONTRATOS -->