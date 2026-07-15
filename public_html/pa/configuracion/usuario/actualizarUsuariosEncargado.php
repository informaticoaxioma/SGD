<?php
require_once '../../../negocio/configuracion/usuarios/procesarIngresarUsuario.php';
require_once '../../gg/Controllers/UsuariosController.php';

session_start();
$usuarioSession = $_SESSION['usuario'];
$idContratoUser = $usuarioSession->getIdContrato();
$idPerfilUser = $usuarioSession->getIdPerfil();

$controllerUsuarios = new UsuariosController();
$controllerUsuarios2 = new UsuariosController();
$usuariosList = $controllerUsuarios->getPorContratoUser($idContratoUser);
$subcontratosList = $controllerUsuarios2->getSubcontratosPorIDContrato($idContratoUser);
?>
<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Actualizar Usuarios del Contrato</h2>
    </div>
    <div class="panel-body paddingBottom">
        <div class="table-responsive">
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nombre</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Correo</th>
                        <th>Perfil</th>
                        <th>Contrato</th>
                        <th>Contraseña</th>
                        <th>Estado</th>
                        <th>Actualizar</th>
                        <th>Subcontratos</th>
                    </tr>
                </thead>
                <tbody>
                    <input type="hidden" id="idPerfilUser" value="<?= $idPerfilUser ?>" />
                    <?php foreach ($usuariosList as $usuario) : ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nombre_usuario']) ?></td>
                            <td>
                                <input type="text" id="nombre_<?= $usuario['id_usuario'] ?>" name="nombre[<?= $usuario['id_usuario'] ?>]" value="<?= htmlspecialchars($usuario['nombre']) ?>" />
                            </td>
                            <td>
                                <input type="text" id="apellido_p_<?= $usuario['id_usuario'] ?>" name="apellido_p[<?= $usuario['id_usuario'] ?>]" value="<?= htmlspecialchars($usuario['apellido_p']) ?>" />
                            </td>
                            <td>
                                <input type="text" id="apellido_m_<?= $usuario['id_usuario'] ?>" name="apellido_m[<?= $usuario['id_usuario'] ?>]" value="<?= htmlspecialchars($usuario['apellido_m']) ?>" />
                            </td>
                            <td>
                                <input type="text" id="correo_<?= $usuario['id_usuario'] ?>" name="correo[<?= $usuario['id_usuario'] ?>]" value="<?= htmlspecialchars($usuario['correo']) ?>" />
                            </td>
                            <td>
                                <select id="perfil_<?= $usuario['id_usuario'] ?>" name="perfil[<?= $usuario['id_usuario'] ?>]" <?= $usuario['id_perfil'] == 1 ? 'disabled' : '' ?>>
                                    <?php
                                    $currentPerfil = $controllerUsuarios->getPerfilUsuario($usuario['id_perfil']);
                                    $allPerfiles = $controllerUsuarios->getAllPerfiles(); // Assuming you have this method

                                    foreach ($allPerfiles as $perfil) {
                                        $selected = ($currentPerfil == $perfil['perfil']) ? 'selected' : '';
                                        echo "<option value='{$perfil['id_perfil']}' {$selected}>{$perfil['perfil']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><?= $controllerUsuarios->getContratoUsuario($usuario['id_contrato']); ?></td>
                            <td>
                                <input type="password" id="contrasena_<?= $usuario['id_usuario'] ?>" name="contrasena" placeholder="Contraseña" />
                            </td>
                            <td>
                                <select id="estado_<?= $usuario['id_usuario'] ?>" name="estado[<?= $usuario['id_usuario'] ?>]">
                                    <?php
                                    $currentEstado = $controllerUsuarios->getEstadoUsuario($usuario['id_estado_usuario']);
                                    $allEstados = $controllerUsuarios->getAllEstados();

                                    foreach ($allEstados as $estado) {
                                        $selected = ($currentEstado == $estado['estado_usuario']) ? 'selected' : '';
                                        echo "<option value='{$estado['id_estado_usuario']}' {$selected}>{$estado['estado_usuario']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>

                                <button type="button" class="btn btn-success" id="actualizar_<?= $usuario['id_usuario'] ?>">Actualizar</button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info" id="subcontratos_<?= $usuario['id_usuario'] ?>">Subcontratos</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<!-- Modal Structure (Bootstrap) -->
<div class="modal fade" id="subcontratosModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Subcontratos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select id="subcontratosSelect" class="form-control" multiple>
                    <?php foreach ($subcontratosList as $subcontrato) : ?>
                        <option value="<?= htmlspecialchars($subcontrato['id_subcontrato']) ?>"><?= htmlspecialchars($subcontrato['nombre_subcontrato']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="guardarSubcontrato">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>