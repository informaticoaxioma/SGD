<?php require_once '../../../negocio/configuracion/entidades/procesarAdmEntidades.php'; ?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Ingresar Entidad</h2>
            </div>
            <div class="panel-body paddingBottom">
                <form id="formIngresarEntidad" method="POST" class="form-horizontal">
                    <div class="form-group">
                        <label for="nombreEntidad" class="control-label col-sm-3">Nombre: </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="nombreEntidad" name="nombreEntidad" placeholder="Ingrese Nombre">
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="apellidoEntidad" class="control-label col-sm-3">Apellido: </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="apellidoEntidad" name="apellidoEntidad" placeholder="Ingrese Apellido">
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="idCargo" class="control-label col-sm-3">Cargo: </label>
                        <div class="col-sm-6">
                            <select id="idCargo" name="idCargo" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($cargos)) :
                                    foreach ($cargos as $c) :
                                        ?>
                                        <option value="<?php echo $c->getIdCargo(); ?>"><?php echo $c->getCargo(); ?></option>
                                        <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="idTipoEntidad" class="control-label col-sm-3">Tipo Entidad: </label>
                        <div class="col-sm-4">
                            <select id="idTipoEntidad" name="idTipoEntidad" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($tipoEntidades)) :
                                    foreach ($tipoEntidades as $te) :
                                        ?>
                                        <option value="<?php echo $te->getIdTipoEntidad(); ?>"><?php echo $te->getTipoEntidad(); ?></option>
                                        <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php if ($sessionUsuario->getIdPerfil() == 1) : ?>
                        <div class="form-group">
                            <label for="idContrato" class="control-label col-sm-3">Contrato: </label>
                            <div class="col-sm-4">
                                <select id="idContrato" name="idContrato" class="form-control">
                                    <option value="">Seleccione</option>
                                    <?php
                                    if (isset($contratos)) :
                                        foreach ($contratos as $c) :
                                            ?>
                                            <option value="<?php echo $c->getIdContrato(); ?>"><?php echo $c->getContrato(); ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="control-label col-sm-3"></label>
                        <div class="col-sm-4">
                            <input type="submit" id="btnIngresarEntidad" name="btnIngresarEntidad" class="btn btn-success btn-lg" value="Ingresar">
                        </div>

                    </div>
                </form>
                <!-- Mensajes -->
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="alert alert-success noDisplay mensajeExito">
                            <label><i class="fa fa-fw fa-user"></i> Entidad agregada exitosamente</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="alert alert-danger  noDisplay mensajeError">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar la entidad</label>
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
                <h2 class="text-center blanco">Actualizar Entidad</h2>
            </div>
            <div class="panel-body paddingBottom">
                <div class="table-responsive centrar divAjax">
                    <table class="table table-hover centrar">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cargo</th>
                                <th>Tipo Entidad</th>
                                <th>Actualizar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($entidades)):
                                $cont = 0;
                                foreach ($entidades as $e):
                                    ?>
                                    <tr id="<?php echo "fila" . $cont; ?>">
                                        <td><input type="text" id="<?php echo "nombreEntidadMod" . $cont; ?>" name="<?php echo "nombreEntidadMod" . $cont; ?>" class="form-control" value="<?php echo $e->getNombreEntidad(); ?>"></td>
                                        <td><input type="text" id="<?php echo "apellidoEntidadMod" . $cont; ?>" name="<?php echo "apellidoEntidadMod" . $cont; ?>" class="form-control" value="<?php echo $e->getApellidoEntidad(); ?>"></td>
                                        <td>
                                            <select id="<?php echo "idCargoMod" . $cont; ?>" name="<?php echo "idCargoMod" . $cont; ?>" class="form-control">
                                                <?php
                                                if (isset($cargos)):
                                                    foreach ($cargos as $c) :
                                                        ?>
                                                        <option value="<?php echo $c->getIdCargo(); ?>" <?php echo $c->getIdCargo() == $e->getIdCargo() ? 'Selected' : '' ?>><?php echo $c->getCargo(); ?></option>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="<?php echo "idTipoEntidadMod" . $cont; ?>" name="<?php echo "idTipoEntidadMod" . $cont; ?>" class="form-control">
                                                <?php
                                                if (isset($tipoEntidades)) :
                                                    foreach ($tipoEntidades as $te) :
                                                        ?>
                                                        <option value="<?php echo $te->getIdTipoEntidad(); ?>" <?php echo $te->getIdTipoEntidad() == $e->getIdTipoEntidad() ? 'Selected' : ''; ?>><?php echo $te->getTipoEntidad(); ?></option>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </select>
                                        </td>
                                        <td><input type="button" id="btnModEntidad" name="btnModEntidad" class="btn btn-success" onclick="actualizarEntidad('<?php echo $e->getIdEntidad(); ?>', '<?php echo "nombreEntidadMod" . $cont; ?>', '<?php echo "apellidoEntidadMod" . $cont; ?>', '<?php echo "idCargoMod" . $cont; ?>', '<?php echo "idTipoEntidadMod" . $cont; ?>')" value="Actualizar"></td>
                                        <td><input type="button" id="btnEliminarEntidad" name="btnEliminarEntidad"  class="btn btn-danger" value="Eliminar" onclick="eliminarEntidad('<?php echo $e->getIdEntidad(); ?>', '<?php echo "fila" . $cont; ?>')"></td>
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