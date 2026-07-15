<?php require_once '../../../negocio/configuracion/perfiles/procesarAdministrarPerfiles.php';?>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Ingresar Perfil</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <form id="formIngresarPerfil" class="form-inline"> 
                    
                    <div class="form-group">
                        <label for="nombrePerfil">Nombre Perfil: </label>
                        <input type="text" class="form-control" id="nombrePerfil" name="nombrePerfil" placeholder="Ingrese Perfil">
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-success" id="btnIngresarPerfil" name="btnIngresarPerfil" value="Ingresar Perfil">
                    </div>
                </form>
                
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-success noDisplay mensajeExito">
                            <label><i class="fa fa-fw fa-users"></i> Perfil agregado exitosamente</label>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
                        <div class="alert alert-danger noDisplay  mensajeError">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el Perfil</label>
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
                <h2 class="text-center blanco">Actualizar / Eliminar Perfil</h2>
            </div>
            <div class="panel-body paddingBottom">
                <div class="row">
                    <div class="table-responsive col-xs-12 col-sm-12 col-md-8 col-md-offset-2 paddingTop divAjax">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre Perfil</th>
                                    <th>Actualizar</th>
                                    <th>Eliminar</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($perfiles)):
                                    $cont = 0;
                                    foreach ($perfiles as $p) :
                                        ?>
                                        <tr id="<?php echo $cont; ?>">
                                            <td><input id="<?php echo "perfil" . $cont; ?>" name="<?php echo "perfil" . $cont; ?>" class="form-control" type="text" value="<?php echo $p->getPerfil(); ?>"></td>
                                            <td><input type="button" id="btnActualizarPerfil" name="btnActualizarPerfil" class="btn btn-success" value="Actualizar Perfil" onclick="actualizarPerfil(<?php echo $p->getIdPerfil(); ?>, '<?php echo "perfil" . $cont; ?>')"></td>
                                            <td><input type="button" id="btnEliminarPerfil" name="btnEliminarPerfil" class="btn btn-danger" value="Eliminar Perfil" onclick="eliminarPerfil(<?php echo $p->getIdPerfil(); ?>,<?php echo $cont; ?>)"></td>
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