<?php

require_once '../../../data/Usuario.php';
require_once '../../../data/Perfil.php';
require_once '../../../data/Cargo.php';
require_once '../../../data/EstadoUsuario.php';
require_once '../../../data/Contrato.php';
require_once '../../../data/SubContrato.php';

//servicios
$serviceUsuario = new Usuario();
$servicePerfil = new Perfil();
$serviceCargo = new Cargo();
$serviceEstadoUsuario = new EstadoUsuario();
$serviceContrato= new Contrato();
$serviceSubContrato= new SubContrato();
//cargando datos necesarios
$usuarios = $serviceUsuario->getUsuarios();
$cargos = $serviceCargo->getCargos();
$perfiles = $servicePerfil->getPerfiles();
$estados = $serviceEstadoUsuario->getEstados();
$contratos  = $serviceContrato->getContratos();
$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];
if ($_POST) {
    $flag = htmlspecialchars($_POST['flag']);
    $idUsuario = isset($_POST['idUsuario'])?htmlspecialchars($_POST['idUsuario']):null;
    $idsSubContrato = (isset($_POST['IdsSubContrato']) and count($_POST['IdsSubContrato']) >0)?$_POST['IdsSubContrato']:null;
    switch ($flag) {

        case 1://Actualizar Usuarios
            
            $idUsuario = htmlspecialchars($_POST['idUsuario']);
            $nombre = htmlspecialchars($_POST['nombre']);
            $apellidoP = htmlspecialchars($_POST['apellidoP']);
            $apellidoM = htmlspecialchars($_POST['apellidoM']);            
            $idPerfil = htmlspecialchars($_POST['idPerfil']);
            $idEstado = htmlspecialchars($_POST['idEstado']);

            $usuario = new Usuario();
            $usuario->setIdUsuario($idUsuario);
            $usuario->setNombre($nombre);
            $usuario->setApellidoP($apellidoP);
            $usuario->setApellidoM($apellidoM);           
            $usuario->setIdPerfil($idPerfil);
            $usuario->setIdEstadoUsuario($idEstado);

            echo $serviceUsuario->actualizarUsuario($usuario,1);


            break;

        case 2://Eliminar Usuarios

            echo $serviceUsuario->eliminarUsuarioPorId($idUsuario);

            break;
        case 3:
            $idContrato = $_POST["contrato"];
            $usuarios = $serviceUsuario->getUsuariosContrato($idContrato);
            $cargos = $serviceCargo->getCargos();
            $perfiles = $servicePerfil->getPerfiles();
            $estados = $serviceEstadoUsuario->getEstados();
            $contratos  = $serviceContrato->getContratos();
            $cont = 0;
                ?>
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
                        <th>Eliminar</th>                    
                    </tr>
                </thead>
                <tbody> 
                
                <?php
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
                ?>
                            </tbody>

                        </table>
                    </div>
                </div>
                </div>
                <?php
             break;

        case 4:
            if($idUsuario !== null and $idsSubContrato !== null){
                $usr = $serviceUsuario->getUsuarioPorId($idUsuario);
                try {
                    $serviceSubContrato->asignaUsuarioSubcontrato($usr, $idsSubContrato);
                    ?>
                    Subcontratos actualizados
                    <?php
                }catch(Exception $ex){
                    error_log($ex);
                    ?>
                    Error, no se han podido actualizar los Subcontratos
                    <?php
                }
            }

            break;
    }
}
