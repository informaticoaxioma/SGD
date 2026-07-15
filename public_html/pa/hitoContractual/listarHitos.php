<?php require_once '../../negocio/hitoContractual/procesarListarHitos.php'; ?>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Listado de Hitos</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <!--FILTROS -->
                <div class="row">
                    <div class="col-xs-12">
                        <form id="formFiltroHitoListado" name="formFiltroHitoListado" class="form-inline">
                            <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 8) :
                                ?>
                                <div class="form-group">
                                    <label for="idResposableListado">Responsable: </label>
                                    <select id="idResposableListado" name="idResposableListado" class="form-control">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($responsables)):
                                            foreach ($responsables as $r):
                                                if ($r->getIdPerfil() != 1):
                                                    ?>
                                                    <option value="<?php echo $r->getIdUsuario(); ?>"><?php echo $r->getNombre() . " " . $r->getApellidoP(); ?></option>
                                                    <?php
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="idEstadoHitoListado">Estado Hito:</label>
                                <select id="idEstadoHitoListado" name="idEstadoHitoListado" class="form-control">
                                    <option value="">Seleccione</option>
                                    <?php
                                    if (isset($estadosHito)):
                                        foreach ($estadosHito as $eh):
                                            ?>
                                            <option value="<?php echo $eh->getIdEstadoHito(); ?>"><?php echo $eh->getEstadoHito(); ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="idFrecuenciaHitoListado">Frecuencia: </label>
                                <select id="idFrecuenciaHitoListado" name="idFrecuenciaHitoListado" class="form-control">
                                    <option value="">Seleccione</option>
                                    <?php
                                    if (isset($frecuenciaHitos)):
                                        foreach ($frecuenciaHitos as $fh):
                                            ?>
                                            <option value="<?php echo $fh->getIdFrecuenciaHito(); ?>"><?php echo $fh->getFrecuenciaHito(); ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </form>                        
                    </div>
                </div>
                <hr>
                <br>
                <!-- FIN FILTROS -->
                <div class="table table-responsive divAjax">
                    <table id="tablaHitosAjax" class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Fecha entrega</th>
                                <th class="text-center">Descripci&oacute;n hito</th>
                                <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 8) : ?>
                                    <th class="text-center">Responsable</th>
                                <?php endif; ?>
                                <th class="text-center">Destinatario</th>
                                <th class="text-center">Normativa</th>
                                <th class="text-center">Comentario</th>
                                <th class="text-center">Estado hito</th>
                                <th class="text-center">Frecuencia</th>
                                <th class="text-center">Actualizar</th>
                            </tr>
                        </thead>
                        <tbody class="text-justify">
                            <?php
                            if (isset($hitos)) :
                                $c = 0;
                                foreach ($hitos as $h) :
                                    $estadoHito = $serviceEstadoHito->getEstadoPorId($h->getIdEstadoHito());
                                    $frecuenciaHito = $serviceFrecuenciaHito->getFrecuenciaPorId($h->getIdFrecuenciaHito());
                                    $responsable = $serviceUsuario->getUsuarioPorId($h->getIdResponsableHito());
                                   
                                    $colorAlerta = "alert alert-danger";
                                    $colorExito = "alert alert-success";
                                    ?>
                                    <tr id="<?php echo $c; ?>">
                                        <td><?php echo $serviceDocumento->formatoFecha($h->getFechaEntrega()); ?></td>
                                        <td><?php echo $h->getDescripcionHito(); ?></td>
                                        <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 8) : ?>
                                            <td><?php echo $responsable->getNombre() . " " . $responsable->getApellidoP(); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo $h->getDestinoInfo(); ?></td>
                                        <td><?php echo $h->getNormativa(); ?></td>
                                        <td><textarea class="form-control noResize" disabled><?php echo $h->getComentario(); ?></textarea></td>
                                        <td class="<?php echo $h->getIdEstadoHito() == 1 ? $colorAlerta : $colorExito; ?>"><?php echo $estadoHito->getEstadoHito(); ?></td>
                                        <td><?php echo $frecuenciaHito->getFrecuenciaHito(); ?></td>
                                        <td>

                                            <button type="button" id="btnActualizarHito" name="btnActualizarHito" class="btn btn-success" onclick="actualizarHito(<?php echo $h->getIdHito(); ?>)"><i class="fa fa-edit"></i>&nbsp;Actualizar</button>
                                        </td>    
                                    </tr>

                                    <?php
                                    $c++;
                                endforeach;
                            endif;
                            ?>
                        </tbody>                    
                    </table>
                    <?php $paginacion->render(); ?>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="ModalEditListado" name="ModalEditListado" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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