<?php require_once '../../../negocio/configuracion/cargos/procesarAdministrarCargos.php'; ?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Ingresar Cargo</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <?php if ($usuarioSession->getIdPerfil() == 1) : ?>
                    <form id="formIngresarCargo" class="form-horizontal"> 
                        <div class="form-group">
                            <label for="nombreCargo" class="col-sm-4 control-label">Nombre Cargo: </label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="nombreCargo" name="nombreCargo" placeholder="Ingrese Cargo">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="idArea" class="col-sm-4 control-label">&Aacute;rea: </label>
                            <div class="col-sm-4">
                                <select id="idArea" name="idArea" class="form-control">
                                    <option value="">Seleccione</option>
                                    <?php
                                    if (isset($areas)):
                                        foreach ($areas as $a) :
                                            ?>
                                            <option value="<?php echo $a->getIdArea(); ?>"><?php echo $a->getArea(); ?> </option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="divContratos">

                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-success" id="btnIngresarCargo" name="btnIngresarCargo" value="Ingresar Cargo">
                        </div>
                    </form>
                <?php else: ?>'
                    <form id="formIngresarCargo" class="form-inline"> 
                        <div class="form-group">
                            <label for="nombreCargo">Nombre Cargo: </label>
                            <input type="text" class="form-control" id="nombreCargo" name="nombreCargo" placeholder="Ingrese Cargo">
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-success" id="btnIngresarCargo" name="btnIngresarCargo" value="Ingresar Cargo">
                        </div>
                    </form>
                <?php endif; ?>


                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-success noDisplay mensajeExito">
                            <label><i class="fa fa-fw fa-users"></i> Cargo agregado exitosamente</label>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-danger noDisplay  mensajeError">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el Cargo</label>
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
                <h2 class="text-center blanco">Actualizar / Eliminar Cargo</h2>
            </div>
            <div class="panel-body paddingBottom">                
                <div class="row">
                    <div class="table-responsive col-xs-12 col-sm-12 col-md-8 col-md-offset-2 paddingTop divAjax">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre Cargo</th>
                                    <th>Actualizar</th>
                                    <th>Eliminar</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($cargos)):
                                    $cont = 0;
                                    foreach ($cargos as $c) :
                                        ?>
                                        <tr id="<?php echo $cont; ?>">
                                            <td><input id="<?php echo "cargo" . $cont; ?>" name="<?php echo "cargo" . $cont; ?>" class="form-control" type="text" value="<?php echo $c->getCargo(); ?>"></td>
                                            <td><input type="button" id="btnActualizarCargo" name="btnActualizarCargo" class="btn btn-success" value="Actualizar Cargo" onclick="actualizarCargo(<?php echo $c->getIdCargo(); ?>, '<?php echo "cargo" . $cont; ?>')"></td>
                                            <td><input type="button" id="btnEliminarCargo" name="btnEliminarCargo" class="btn btn-danger" value="Eliminar Cargo" onclick="eliminarCargo(<?php echo $c->getIdCargo(); ?>,<?php echo $cont; ?>)"></td>

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