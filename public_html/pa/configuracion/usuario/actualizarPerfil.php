<?php require_once '../../../negocio/configuracion/usuarios/procesarActualizarPerfil.php'; ?>

<?php
  

  
  

?>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco"><i class="fa fa-user"></i> Mi Perfil</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <div class="row">                    
                    <div class="col-xs-12 col-sm-12 col-md-6 ">
                        <form id="formActualizarPerfil" name="formActualizarPerfil" class="form-horizontal text-left">
                            <div class="form-group">
                                <label for="nombre" class="col-sm-4 control-label">Nombre:</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $usuarioSession->getNombre(); ?>" disabled="true">
                                    <input type="hidden" id="idUsuario" name="idUsuario" value="<?php echo $usuarioSession->getIdUsuario(); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="apellidoP" class="col-sm-4 control-label">Apellido Paterno:</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="apellidoP" name="apellidoP" value="<?php echo $usuarioSession->getApellidoP(); ?>" disabled="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="apellidoM" class="col-sm-4 control-label">Apellido Materno:</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="apellidoM" name="apellidoM" value="<?php echo $usuarioSession->getApellidoM(); ?>" disabled="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="correo" class="col-sm-4 control-label">Correo:</label>
                                <div class="col-sm-6">
                                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $usuarioSession->getCorreo(); ?>" disabled="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"></label>
                                <div class="col-xs-12 col-sm-12 col-md-2">
                                    <input type="button" class="btn btn-primary" id="btnEditar" value="Editar">
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-2">
                                    <input type="submit" class="btn btn-success" id="btnActualizarPerfil" value="Actualizar" disabled="true">
                                </div>
                                <input type="hidden" id="flagEditar" name="flagEditar" value="1">
                            </div>
                        </form>

                        <div class="row">
                            <br/>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="alert alert-success noDisplay mensajeExito">
                                    <label><i class="fa fa-fw fa-user"></i> Perfil actualizado exitosamente</label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <br/>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="alert alert-danger noDisplay  mensajeError">
                                    <label><i class="fa fa-fw fa-warning"></i> Error, no se ha podido actualizar el perfil</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 ">
                        <form id="formCambiarContrasena" class="form-horizontal text-left">                            
                            <div class="form-group">
                                <label for="contrasenaN" class="col-sm-4 control-label">Contrase&ntilde;a Nueva</label>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control" id="contrasenaN" name="contrasenaN" placeholder="***************">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reContrasena" class="col-sm-4 control-label">Confimar Contrase&ntilde;a:</label>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control" id="reContrasena" name="reContrasena" placeholder="***************">
                                </div>
                            </div>    

                            <div id="divBtnCambiarContrasena" class="form-group">
                                <div class="col-xs-12 col-sm-12 col-md-2">
                                    <input type="submit" class="btn btn-success"  value="Cambiar Contrase&ntilde;a">
                                </div>
                            </div>

                        </form>

                        <div class="row">
                            <br/>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="alert alert-success noDisplay mensajeExitoMod">
                                    <label><i class="fa fa-fw fa-user"></i> Contrase&ntilde;a actualizada exitosamente</label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <br/>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="alert alert-danger noDisplay  mensajeErrorMod">
                                    <label><i class="fa fa-fw fa-warning"></i> Error, no se ha podido actualizar la contrase&ntilde;a</label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>