<?php require_once '../../../negocio/configuracion/usuarios/procesarIngresarUsuario.php';
session_start();
$usuarioSession = $_SESSION['usuario'];
$idContratoUser = $usuarioSession->getIdContrato();
//print_r($usuarioSession, false);
?>

<div class="panel panel-default sombraPanel">
    <div class="panel-heading colorAxioma">
        <h2 class="text-center blanco">Ingresar Usuario</h2>
    </div>
    <div class="panel-body paddingBottom">
        <div class="row">
            <form id="formIngresarUsuario" class="form-horizontal">
                <div class="form-group">
                    <label for="nombre" class="col-sm-3 control-label">Nombre: </label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="nombre" placeholder="Ingrese nombre">
                    </div>
                </div>
                <div class="form-group">
                    <label for="apellidoP" class="col-sm-3 control-label">Apellido Paterno: </label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="apellidoP" placeholder="Ingrese Apellido Paterno">
                    </div>
                </div>
                <div class="form-group">
                    <label for="apellidoM" class="col-sm-3 control-label">Apellido Materno: </label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="apellidoM" placeholder="Ingrese Apellido Materno">
                    </div>
                </div>
                <div class="form-group">
                    <label for="correo" class="col-sm-3 control-label">Correo: </label>
                    <div class="col-sm-4">
                        <input type="email" class="form-control" id="correo" placeholder="ejemplo@ejemplo.cl">
                    </div>
                </div>
                <div class="form-group">
                    <label for="nombreUsuario" class="col-sm-3 control-label">Nombre Usuario: </label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="nombreUsuario" placeholder="Ingrese nombre de usuario">
                    </div>
                </div>
                <div class="form-group">
                    <label for="contrasena" class="col-sm-3 control-label">Contrase&ntilde;a: </label>
                    <div class="col-sm-4">
                        <input type="password" class="form-control" id="contrasena" placeholder="**********">
                    </div>
                </div>
                <div class="form-group">
                    <label for="contrasena" class="col-sm-3 control-label">Re-Contrase&ntilde;a: </label>
                    <div class="col-sm-4">
                        <input type="password" class="form-control" id="reContrasena" placeholder="**********">
                    </div>
                </div>

                <div class="form-group">
                    <label for="idArea2" class="col-sm-3 control-label">&Aacute;rea: </label>
                    <div class="col-sm-4">
                        <select class="form-control" disabled>
                            <option value="">Seleccione</option>
                            <option selected value="<?php echo $idAreaUser = $serviceContrato->getContratoPorId($idContratoUser)->getIdArea(); ?>"><?php // Assuming $idAreaUser is already set and contains the desired ID
                                                                                                                                                    $areasRetrieve = $serviceArea->getAreaPorIdArea($idAreaUser);

                                                                                                                                                    // Check if the array is not empty and contains Area objects
                                                                                                                                                    if (!empty($areasRetrieve) && $areasRetrieve[0] instanceof Area) {
                                                                                                                                                        $areaUser = $areasRetrieve[0]->getArea(); // Access the first Area object in the array
                                                                                                                                                        // Now you can use $areaUser in your HTML
                                                                                                                                                        echo "<option selected value=\"" . $idAreaUser . "\">" . $areaUser . "</option>";
                                                                                                                                                    } else {
                                                                                                                                                        // Handle the case where the Area object is not found
                                                                                                                                                        echo "<option selected value=\"\">Area not found</option>";
                                                                                                                                                    } ?></option>



                            ?>
                        </select>

                        <input type="hidden" id="idArea" value="<?php echo $serviceContrato->getContratoPorId($idContratoUser)->getIdContrato(); ?>">
                    </div>
                </div>
                <div class="form-group" id="divIdContrato">
                    <label for="idContrato2" class="col-sm-3 control-label">Contrato: </label>
                    <div class="col-sm-4" id="contratosAjax">
                        <select id="idContrato2" class="form-control" disabled>
                            <option value="">Seleccione</option>
                            <option selected value="<?php echo $contratoUser = $serviceContrato->getContratoPorId($idContratoUser)->getIdContrato(); ?>"><?php echo $contratoUser = $serviceContrato->getContratoPorId($idContratoUser)->getContrato(); ?></option>



                            ?>
                        </select>

                        <input type="hidden" id="idContrato" value="<?php echo $serviceContrato->getContratoPorId($idContratoUser)->getIdContrato(); ?>">

                    </div>
                </div>
                <div class="form-group" id="divSubContratos">
                    <label for="idSubContratos2" class="col-sm-3 control-label">Sub-Contratos: </label>
                    <div class="col-sm-4" id="subContratosAjax2">
                        <select id="idSubcontrato2" class="form-control" multiple>
                            <option value="">Seleccione</option>
                            <?php

                            $subcontratosUser = $serviceSubContrato->getSubContratoPorContrato($idContratoUser);
                            foreach ($subcontratosUser as $s) :
                            ?>
                                <option value="<?php
                                                echo $s->getIdSubcontrato(); ?>"><?php echo $s->getNombreSubContrato(); ?></option>

                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="idPerfil2" class="col-sm-3 control-label">Perfil: </label>
                    <div class="col-sm-4">
                        <select id="idPerfil2" class="form-control">
                            <option value="">Seleccione</option>
                            <?php
                            if (isset($perfilesNoAdmin)) :
                                foreach ($perfilesNoAdmin as $p) :
                            ?>
                                    <option value="<?php echo $p->getIdPerfil(); ?>"><?php echo $p->getPerfil(); ?></option>

                            <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>
                </div>


        </div>

        <div class="row">

            <div class="col-4">
            </div>
            <div class="col-4 text-center">
                <button type="button" id="btnAddUsuario" class="btn btn-success">Agregar Usuario</button>
            </div>
            <div class="col-4">
            </div>

        </div>


        </form>

    </div>
</div>