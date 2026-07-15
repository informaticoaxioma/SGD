<?php require_once '../../../negocio/configuracion/usuarios/procesarActualizarEliminarUsuarios.php'; ?>

        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Filtro Contrato</h2>
            </div>
            <div class="panel-body paddingBottom">
                <div class="col-md-1">
                    <label>Contrato: </label>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="cbxContrato">
                    <option value="todos">Todos</option>
                <?php foreach ($contratos as $c) : ?>
                    <option value="<?php echo $c->getIdContrato(); ?>"><?php echo $c->getContrato(); ?></option>
                <?php endforeach ?>
                </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success" onclick="filtroContratos();">filtrar</button>
                </div>        
            </div>
        </div>

<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Actualizar / Eliminar Usuario</h2>
    </div>
    <div class="panel-body paddingBottom" id="tablaContratos">

        <div class="row mensajeExito noDisplay">
            <div class="col-xs-12 col-sm-12 col-md-offset-3 col-md-6">
                <div class="alert alert-success">                  
                    <i class="fa fa-user"></i> <label>Usuario Actualizado Exitosamente</label>
                </div>
            </div>
        </div>
        <div class="row mensajeError noDisplay">
            <div class="col-xs-12 col-sm-12 col-md-offset-3 col-md-6">
                <div class="alert alert-danger ">                  
                    <i class="fa fa-warning"></i> <label>Error, no se ha podido Actualizar al Usuario</label>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed table-hover"> 
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nombre</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>                       
                        <th>Perfil</th>
                        <th>Contrato</th>
                        <th>Estado</th>
                        <th>Actualizar</th>
                        <th>Subcontratos</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>           
                    <?php
                    if (isset($usuarios)):
                        $cont = 0;
                        foreach ($usuarios as $u) :
                           $contrato = $serviceContrato->getContratoPorId($u->getIdContrato());
                            ?>
                                <tr id="<?php echo "f" . $cont; ?>">
                                <td><?php echo $u->getNombreUsuario(); ?></td>
                                <td><input id="<?php echo "nombre" . $cont; ?>" name="<?php echo "nombre" . $cont; ?>" type="text" class="form-control" value="<?php echo $u->getNombre(); ?>"></td>
                                <td><input id="<?php echo "apellidoP" . $cont; ?>" name="<?php echo "apellidoP" . $cont; ?>" type="text" class="form-control" value="<?php echo $u->getApellidoP(); ?>"></td>
                                <td><input id="<?php echo "apellidoM" . $cont; ?>" name="<?php echo "apellidoM" . $cont; ?>" type="text" class="form-control" value="<?php echo $u->getApellidoM(); ?>"></td>                               
                                <td>
                                    <select id="<?php echo "idPerfil" . $cont; ?>" name="<?php echo "idPerfil" . $cont; ?>" class="form-control">
                                        <?php
                                        if (isset($perfiles)) :
                                            foreach ($perfiles as $p) :
                                                ?>
                                                <option value="<?php echo $p->getIdPerfil(); ?>" <?php echo $p->getIdPerfil() == $u->getIdPerfil() ? 'Selected' : ''; ?> ><?php echo $p->getPerfil(); ?></option>

                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </td>
                                <td><input class="form-control" disabled="disable" value="<?php echo $contrato->getContrato(); ?>"></td>
                                <td>
                                    <select id="<?php echo "idEstado" . $cont; ?>" name="<?php echo "idEstado" . $cont; ?>" class="form-control">
                                        <?php
                                        if (isset($estados)) :
                                            foreach ($estados as $e) :
                                                ?>
                                                <option value="<?php echo $e->getIdEstadoUsuario(); ?>" <?php echo $e->getIdEstadoUsuario() == $u->getIdEstadoUsuario() ? 'Selected' : ''; ?> ><?php echo $e->getEstadoUsuario(); ?></option>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </td>
                                <td><input type="button" id="btnActualizarUsuario" name="btnActualizarUsuario" class="btn btn-success" value="Actualizar" onclick="actualizarUsuario(<?php echo $u->getIdUsuario(); ?>, '<?php echo "nombre" . $cont; ?>', '<?php echo "apellidoP" . $cont; ?>', '<?php echo "apellidoM" . $cont; ?>', '<?php echo "idCargo" . $cont; ?>', '<?php echo "idPerfil" . $cont; ?>', '<?php echo "idEstado" . $cont; ?>');"></td>
                                <td><input type="button" id="btnActualizarSubcontratos" name="btnActualizarSubcontratos" class="btn btn-primary" value="Subcontratos" onclick="subContatoUser(<?= $u->getIdUsuario() ?>,<?= $contrato->getIdContrato()?>, '<?= $u->getNombre() ?>', '<?= $u->getApellidoP() ?>');"></td>
                                <td><input type="button" id="btnEliminarUsuario" name="btnEliminarUsuario" class="btn btn-danger" onclick="eliminarUsuario(<?php echo $u->getIdUsuario(); ?>, '<?php echo "f" . $cont; ?>')" value="Eliminar"></td>

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
<div class="modal fade" id="subcon" tabindex="-1" aria-labelledby="subconModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div id = "subconModalLabel" class="modal-title">Subcontratos para <span id="nombreusuario"></span></div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="SubConUserId" id="SubConUserId">
                <div id="subcontrausuer">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onClick="guardaSubContra($('#cmbSubContra').val(),$('#SubConUserId').val())">Guardar</button>
            </div>
        </div>
    </div>
</div>

