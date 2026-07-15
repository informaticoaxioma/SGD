<?php
require_once '../../negocio/inicio/procesarActualizarDocumento.php';
//var_dump($seguimientos);
//echo "<pre>" . print_r($seguimientos, true) . " </pre>"; IMPRIMIR SEGUUIMIENTOS

?>
<!--VISTA SUPER ADMIN O RESIDENTE -->
<!--Navegadora lateral -->
<ul id="menu" class="mfb-component--br mfb-zoomin" data-mfb-toggle="hover">
    <li class="mfb-component__wrap">
        <a href="#divDetalleDocumento" data-mfb-label="Detalle Documento" class="mfb-component__button--main">
            <i class="mfb-component__main-icon--resting ion-plus-round"></i>
            <i class="mfb-component__main-icon--active ion-ios-home"></i>
        </a>
        <ul class="mfb-component__list">
            <li>
                <a href="#divArchivos" data-mfb-label="Archivos" data-mfb-label="label with long long title" class="mfb-component__button--child">
                    <i class="mfb-component__child-icon ion-document-text"></i>
                </a>
            </li>
            <li>
                <a href="#divRelacionarDocumento" data-mfb-label="Relacionar Documentos" class="mfb-component__button--child">
                    <i class="mfb-component__child-icon ion-shuffle"></i>
                </a>
            </li>

            <li>
                <a href="#divSeguimiento" data-mfb-label="Seguimiento" class="mfb-component__button--child">
                    <i class="mfb-component__child-icon ion-search"></i>
                </a>
            </li>

            <li>
                <a href="#divResponsable" data-mfb-label="Responsable" class="mfb-component__button--child">
                    <i class="mfb-component__child-icon ion-person"></i>
                </a>
            </li>
            <li>
                <a href="#divBitacoraResponsable" data-mfb-label="Bitacora Responsables" class="mfb-component__button--child">
                    <i class="mfb-component__child-icon ion-ios-list-outline"></i>
                </a>
            </li>
        </ul>
    </li>
</ul>
<?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 9) : ?>
    <div id="divDetalleDocumento"></div>
    <br />
    <br />
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="panel panel-default sombraPanel">
                <div class="panel-heading colorAxioma">
                    <h3 class="text-center blanco">Detalle Documento</h3>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-success" onclick="volverInicio()"><i class="fa fa-reply"></i>&nbsp;Volver a Inicio</button>
                    </div>
                </div>
                <div class="panel-body text-center">

                    <form id="formActualizarDocumento" name="formActualizarDocumento" class="form-horizontal text-left">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="contrato" class="col-sm-4 control-label">Asesor&iacute;a / Contrato:</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" value="<?php echo $contrato->getContrato(); ?>" disabled="true">
                                        <input type="hidden" id="idDocumento" name="idDocumento" value="<?php echo $idDocumento; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="idSubContrato" class="col-sm-4 control-label">Sub-Contrato:</label>
                                    <div class="col-sm-8">
                                        <select id="idSubContrato" name="idSubContrato" class="form-control">
                                            <?php
                                            if (isset($subContratos)) :
                                                foreach ($subContratos as $sc) :
                                            ?>
                                                    <option value="<?php echo $sc->getIdSubcontrato(); ?>" <?php echo $sc->getIdSubContrato() == $documento[0]['id_subcontrato'] ? 'Selected' : ''; ?>><?php echo $sc->getNombreSubContrato(); ?></option>

                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="numeroDoc" class="col-sm-4 control-label">N° Documento:</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="numeroDoc" name="numeroDoc" value="<?php echo $documento[0]['num_documento']; ?>">
                                    </div>
                                </div>
                                <?php if ($documento[0]['id_flujo'] == 1) : ?>
                                    <div class="form-group">
                                        <label for="numeroProv" class="col-sm-4 control-label">N° Providencia:</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="numeroProv" name="numeroProv" value="<?php echo $documento[0]['num_providencia']; ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="form-group">
                                    <label for="numeroProceso" class="col-sm-4 control-label">N° Proceso:</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="numeroProceso" name="numeroProceso" value="<?php echo $documento[0]['num_proceso']; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="remitente" class="col-sm-4 control-label">Remitente:</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="remitente" name="remitente">
                                            <?php
                                            if (isset($remitentes)) :
                                                foreach ($remitentes as $r) :
                                            ?>
                                                    <option value="<?php echo $r['id_entidad']; ?>" <?php echo $r['id_entidad'] == $documento[0]['id_remitente'] ? 'Selected' : ''; ?>><?php echo $r['nombre_entidad'] . " " . $r['apellido_entidad'] . " (" . $r['cargo'] . ")"; ?></option>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="destinatario" class="col-sm-4 control-label">Destinatario:</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="destinatario" name="destinatario">
                                            <?php
                                            if (isset($destinatarios)) :
                                                foreach ($destinatarios as $d) :
                                            ?>
                                                    <option value="<?php echo $d['id_entidad']; ?>" <?php echo $d['id_entidad'] == $documento[0]['id_destinatario'] ? 'Selected' : ''; ?>><?php echo $d['nombre_entidad'] . " " . $d['apellido_entidad'] . " (" . $d['cargo'] . ")"; ?></option>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="materia" class="col-sm-4 control-label">Materia:</label>
                                    <div class="col-sm-8">
                                        <textarea id="materia" name="materia" class="form-control noResize" rows="3"><?php echo $documento[0]['materia']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comentarios" class="col-sm-4 control-label">Antecedente:</label>
                                    <div class="col-sm-8">
                                        <textarea id="antecedente" name="antecedente" class="form-control noResize" rows="2"><?php echo $documento[0]['antecedente']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="incluye" class="col-sm-4 control-label">Incluye:</label>
                                    <div class="col-sm-8">
                                        <textarea id="incluye" name="incluye" class="form-control noResize" rows="3"><?php echo $documento[0]['incluye']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comentarios" class="col-sm-4 control-label">Comentarios:</label>
                                    <div class="col-sm-8">
                                        <textarea id="comentarios" name="comentarios" class="form-control noResize" rows="2"><?php echo $documento[0]['comentario']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comentarios" class="col-sm-4 control-label">Archivo:</label>
                                    <div class="col-sm-8">
                                        <input type="file" name="archivoDoc" id="archivoDoc" accept=".pdf">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comentarios" class="col-sm-4 control-label">Adjunto(s):</label>
                                    <div class="col-sm-8">
                                        <input type="file" name="adjunto" id="adjunto" onchange="detalleAdjuntos()" style="display: none;" multiple="multiple" accept=".pdf">
                                        <button id="subir" onclick="buscarArchivos()">Elegir Archivos</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label"></label>
                                    <div id="listadoAdjunto" class="col-sm-8">

                                    </div>
                                </div>

                            </div>

                            <!--COLUMNA DERECHA -->
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="idTipoDoc" class="col-sm-4 control-label">Tipo Documento:</label>
                                    <div class="col-sm-6">
                                        <select id="idTipoDoc" name="idTipoDoc" class="form-control">
                                            <?php
                                            if (isset($tipoDocumentos)) :
                                                foreach ($tipoDocumentos as $td) :
                                            ?>
                                                    <option value="<?php echo $td->getIdTipoDocumento(); ?>" <?php echo $td->getIdTipoDocumento() == $documento[0]['id_tipo_doc'] ? 'Selected' : ''; ?>><?php echo $td->getTipoDocumento(); ?></option>

                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="divFechaDoc" class="form-group ">
                                    <label for="fechaDoc" class="col-sm-4 control-label">Fecha Documento:</label>
                                    <div class="col-sm-4">
                                        <input type="text" id="fechaDoc" name="fechaDoc" class="form-control" value="<?php echo $serviceDocumento->formatoFecha($documento[0]['fecha_documento']); ?>">
                                    </div>
                                </div>
                                <div id="divFechaRec" class="form-group">
                                    <label for="fechaRecepcion" class="col-sm-4 control-label">Fecha Recepci&oacute;n:</label>
                                    <div class="col-sm-4">
                                        <input type="text" id="fechaRecepcion" name="fechaRecepcion" class="form-control" value="<?php echo $serviceDocumento->formatoFecha($documento[0]['fecha_recepcion']); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="fechaPlazo" class="col-sm-4 control-label">Fecha Plazo:</label>
                                    <div class="col-sm-4">
                                        <input type="text" id="fechaPlazo" name="fechaPlazo" class="form-control" value="<?php echo $serviceDocumento->formatoFecha($documento[0]['fecha_plazo']); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="idResponsable" class="col-sm-4 control-label">Responsable:</label>
                                    <div class="col-sm-6 responsables">
                                        <select class="form-control" id="idResponsable" name="idResponsable">
                                            <?php
                                            if (isset($responsables)) :
                                                foreach ($responsables as $r) :
                                            ?>
                                                    <option value="<?php echo $r->getIdUsuario(); ?>" <?php echo $r->getIdUsuario() == $documento[0]['id_responsable'] ? 'Selected' : ''; ?>><?php echo $r->getNombre() . " " . $r->getApellidoP(); ?></option>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <?php if ($documento[0]['id_flujo'] == 1) : //DOCUMENTO DE ENTRADA 
                                ?>
                                    <!--ACCIONES -->
                                    <div class="form-group">
                                        <label for="fechaPlazo" class="col-sm-4 control-label">Acciones:</label>
                                        <div class="col-sm-6">
                                            <table class="text-left table ">
                                                <tr>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="conocimiento" type="checkbox" value="1" <?php
                                                                                                                    foreach ($acciones as $a) : if ($a->getIdActividad() == 1) : echo 'checked';
                                                                                                                            break;
                                                                                                                        endif;
                                                                                                                    endforeach;
                                                                                                                    ?>>
                                                                Conocimiento
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="coordinar" type="checkbox" value="2" <?php
                                                                                                                foreach ($acciones as $a) : if ($a->getIdActividad() == 2) : echo 'checked';
                                                                                                                        break;
                                                                                                                    endif;
                                                                                                                endforeach;
                                                                                                                ?>>
                                                                Coordinar
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="conversar" type="checkbox" value="3" <?php
                                                                                                                foreach ($acciones as $a) : if ($a->getIdActividad() == 3) : echo 'checked';
                                                                                                                        break;
                                                                                                                    endif;
                                                                                                                endforeach;
                                                                                                                ?>>
                                                                Conversar
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="archivo" type="checkbox" value="4" <?php
                                                                                                                foreach ($acciones as $a) : if ($a->getIdActividad() == 4) : echo 'checked';
                                                                                                                        break;
                                                                                                                    endif;
                                                                                                                endforeach;
                                                                                                                ?>>
                                                                Archivo
                                                            </label>
                                                        </div>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="responder" type="checkbox" value="5" <?php
                                                                                                                foreach ($acciones as $a) : if ($a->getIdActividad() == 5) : echo 'checked';
                                                                                                                        break;
                                                                                                                    endif;
                                                                                                                endforeach;
                                                                                                                ?>>
                                                                Responder
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="revisar" type="checkbox" value="6" <?php
                                                                                                                foreach ($acciones as $a) : if ($a->getIdActividad() == 6) : echo 'checked';
                                                                                                                        break;
                                                                                                                    endif;
                                                                                                                endforeach;
                                                                                                                ?>>
                                                                Revisar
                                                            </label>
                                                        </div>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="urgente" type="checkbox" value="7" <?php
                                                                                                                foreach ($acciones as $a) : if ($a->getIdActividad() == 7) : echo 'checked';
                                                                                                                        break;
                                                                                                                    endif;
                                                                                                                endforeach;
                                                                                                                ?>>
                                                                Urgente
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <!--FIN ACCIONES -->
                                <?php endif; ?>

                                <?php if ($documento[0]['id_flujo'] == 2) : ?>

                                    <div class="form-group">
                                        <label for="idEstado" class="col-sm-4 control-label">Estado:</label>
                                        <div class="col-sm-6">
                                            <select class="form-control" id="idEstado" name="idEstado">
                                                <?php
                                                if (isset($estadosDocs)) :
                                                    foreach ($estadosDocs as $ed) :
                                                ?>
                                                        <option value="<?php echo $ed->getIdEstadoDoc(); ?>" <?php echo $ed->getIdEstadoDoc() == $documento[0]['id_estado_doc'] ? 'Selected' : ''; ?>><?php echo $ed->getEstadoDoc(); ?></option>
                                                <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <input type="hidden" id="flag" name="flag" value="<?php echo $documento[0]['id_flujo']; ?>">
                                <input type="hidden" id="idDoc" name="idDoc" value="<?php echo $documento[0]['id_documento']; ?>">

                            </div><!--Fin de la columna -->
                        </div><!--Fin de la fila -->

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-2 col-md-offset-2">
                                <div class="col-sm-2">
                                    <input type="hidden" id="flagEditar" value="-1">
                                    <button type="button" onclick="habilitarActualizarDoc()" id="btnEditarDocumento" name="btnEditarDocumento" class="btn btn-primary"><i class="fa fa-edit"></i>&nbsp;Editar Documento</button>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-2">
                                <div class="col-sm-2">
                                    <button type="submit" id="btnActualizarDocumento" name="btnActualizarDocumento" onclick="actualizarDocumento()" class="btn btn-success"><i class="fa fa-refresh"></i>&nbsp;Actualizar Documento</button>
                                </div>
                            </div>
                        </div>

                    </form>
                    <div class="progress noDisplay gifCarga">
                        <div class="progress-bar progress-bar-striped active colorAxioma maxWidth" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--FIN VISTA SUPER ADMIN O RESIDENTE -->
<?php else : ?>
    <div id="divDetalleDocumento"></div>
    <br />
    <br />
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="panel panel-default sombraPanel ">
                <div class="panel-heading colorAxioma">
                    <h3 class="text-center blanco">Detalle Documento</h3>
                </div>
                <div class="panel-body text-left">
                    <div class="container-fluid">
                        <form class="form-horizontal">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Asesor&iacute;a:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $contrato->getContrato(); ?>" disabled="disabled">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Sub-Contrato:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $subContrato->getNombreSubContrato(); ?>" disabled="disabled">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Tipo Documento:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $tipoDocumento->getTipoDocumento(); ?>" disabled="disabled">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">N° Documento:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $documento[0]['num_documento']; ?>" disabled="disabled">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">N° Providencia:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $documento[0]['num_providencia']; ?>" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">N° Proceso:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $documento[0]['num_proceso']; ?>" disabled="disabled">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Fecha Documento:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $serviceDocumento->formatoFecha($documento[0]['fecha_documento']); ?>" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Fecha Recepci&oacute;n:</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $serviceDocumento->formatoFecha($documento[0]['fecha_recepcion']); ?>" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Fecha Plazo: </label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $serviceDocumento->formatoFecha($documento[0]['fecha_plazo']); ?>" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Remitente:</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control noResize" disabled="disabled"><?php echo $remitente->getNombreEntidad() . " " . $remitente->getApellidoEntidad() . " (" . $cargoRemitente->getCargo() . ")"; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Destinatario:</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control noResize" disabled="disabled"><?php echo $destinatario->getNombreEntidad() . " " . $destinatario->getApellidoEntidad() . " (" . $cargoDestinatario->getCargo() . ")"; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Responsable:</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control noResize" rows="1" disabled="disabled"><?php echo $responsable->getNombre() . " " . $responsable->getApellidoP(); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Materia:</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control noResize" disabled="disabled"><?php echo $documento[0]['materia']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Incluye: </label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" value="<?php echo $documento[0]['incluye']; ?>" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Antecedente:</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control noResize" disabled="disabled"><?php echo $documento[0]['antecedente']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Comentarios:</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control noResize" disabled="disabled"><?php echo $documento[0]['comentario']; ?></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
endif;
?>

<div id="divArchivos"></div>
<br />
<br />

<!-- DESCARGA DE ARCHIVOS -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h3 class="text-center blanco">Archivos</h3>
            </div>
            <div class="panel-body text-center">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="row">
                            <label class="col-md-4">Nombre Archivo: </label>
                            <label class="col-md-8 fondoInfo breakWords"><?php echo $documento[0]['nombre_documento'] . " (" . $documento[0]['tamano_documento'] . ")"; ?></label>
                        </div>
                        <div class="row">
                            <br />
                            <form id="formDescargarArchivo" method="POST" action="../negocio/inicio/procesarActualizarDocumento.php">
                                <input id="idDocumento" name="idDocumento" type="hidden" value="<?php echo $documento[0]['id_documento']; ?>">
                                <input type="hidden" name="flagDoc" id="flagDoc" value="1">
                                <button type="submit" id="btnDescargarArchivo" name="btnDescargarArchivo" class="btn btn-success col-md-4 col-md-offset-4" onclick=" document.getElementById('formDescargarArchivo').submit()"><i class="fa fa-download"></i>&nbsp;Descargar Archivo</button>
                            </form>

                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="row">
                            <label class="col-xs-12 col-md-4">Archivo Adjunto: </label>
                            <ul class="col-xs-10 col-md-8 text-left">
                                <?php
                                if (isset($adjuntos)) :
                                    $fila = 0;
                                    foreach ($adjuntos as $a) :
                                ?>
                                        <div id="<?php echo "fila" . $fila; ?>" class="row">
                                            <li class="breakWords">
                                                <form id="formDescargarAdjunto" method="POST" action="../negocio/inicio/procesarActualizarDocumento.php">
                                                    <div class="col-md-7 fondoInfo">
                                                        <label class=""><?php echo $a->getNombreAdjunto() . " (" . $a->getTamanoAdjunto() . ")"; ?></label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input id="idAdjunto" name="idAdjunto" type="hidden" value="<?php echo $a->getIdAdjunto(); ?>">
                                                        <input type="hidden" name="flagDoc" value="2">
                                                        <input type="submit" id="btnDescargarAdjunto" name="btnDescargarAdjunto" class="btn btn-success btn-sm" value="Descargar">
                                                        <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 8) : ?>
                                                            <input type="button" id="btnEliminarAdjunto" name="btnEliminarAdjunto" class="btn btn-danger btn-sm" onclick="eliminarDocAdjuntosRel(<?php echo $a->getIdAdjunto(); ?>, '<?php echo "fila" . $fila; ?>', 1)" value="Eliminar">
                                                        <?php endif; ?>
                                                    </div>
                                                </form>
                                            </li>
                                        </div>
                                        <br />
                                <?php
                                        $fila++;
                                    endforeach;
                                endif;
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN DESCARGA DE ARCHIVOS -->
<div id="divRelacionarDocumento"></div>
<br />
<br />
<!--RELACIONAR DOCUMENTOS -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h3 class="text-center blanco">Relacionar Documentos</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-4">
                        <form class="form-horizontal">
                            <div class="form-group">
                                <label for="docRelacionado" class="col-sm-8 control-label">Relacionar Documento:</label>
                                <div class="col-sm-4">
                                    <input type="button" id="docRelacionado" name="docRelacionado" class="btn btn-success" value="Relacionar" data-toggle="modal" data-target="#modalDocRel">
                                </div>
                            </div>
                        </form>

                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div id="tablaDocsRelacionados">

                        </div>
                        <input type="button" id="btnRelacionarDocDetalle" name="btnRelacionarDocDetalle" class="btn btn-success btn-sm noDisplay" onclick="relacionarDocumentoDetalle(<?php echo $documento[0]['id_documento'] ?>, 1)" value="Relacionar">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--FIN RELACIONAR DOCUMENTOS -->

<div id="divSeguimiento"></div>
<br />
<br />

<!-- DOCUMENTOS RELACIONADOS -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h3 class="text-center blanco">Seguimiento</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive divDocRelacionados">
                    <table class="table table-condensed table-hover">
                        <thead>
                            <tr class="text-center">
                                <th>N°Documento</th>
                                <th>N°Providencia</th>
                                <th>N°Proceso</th>
                                <th>Fecha Recepción</th>
                                <th>Materia</th>
                                <th>Comentario</th>
                                <th>Detalle</th>
                                <th>Descargar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $filaTabla = 0;
                            if (isset($seguimientos)) :

                                foreach ($seguimientos as $s) :
                                    $documentoRel = $serviceDetalleDoc->getDocumentoYDetallePorId($s->getIdDocumento());
                            ?>
                                    <tr id="<?php echo "filaTabla" . $filaTabla; ?>">
                                        <td><?php echo $documentoRel->getDetalle()->getNumDocumento(); ?></td>
                                        <td><?php echo $documentoRel->getDetalle()->getNumProvidencia(); ?></td>
                                        <td><?php echo $documentoRel->getDetalle()->getNumProceso(); ?></td>
                                        <td><?php echo $serviceFunciones->formatoFecha($documentoRel->getDocumento()->getFechaRecepcion()); ?></td>
                                        <td><?php echo $documentoRel->getDetalle()->getMateria(); ?></td>
                                        <td><?php echo $documentoRel->getDetalle()->getComentario(); ?></td>
                                        <td>
                                            <a href="buscador/detalleDocumentoBuscador.php?idDocumento=<?php echo $documentoRel->getDetalle()->getIdDocumento(); ?>" target="_blank">
                                                <label class="labelA">Ver Detalle</label>
                                            </a>
                                        </td>
                                        <td>
                                            <form id="formDescargarDocRel" method="POST" action="../negocio/inicio/procesarActualizarDocumento.php">
                                                <input type="hidden" name="flagDoc" value="3">
                                                <input name="idDocRelacionado" type="hidden" value="<?php echo $documentoRel->getDetalle()->getIdDocumento(); ?>">
                                                <input type="submit" class="btn btn-success btn-sm" value="Descargar">
                                            </form>
                                        </td>
                                        <td>
                                            <form id="formEliminarSeguimiento" method="POST">
                                                <input type="hidden" name="flagDoc" value="5">
                                                <div class="form-group col-md-9 text-right">
                                                    <button type="button" id="botonElimina" onclick="eliminarSeguimiento(<?php echo $s->getIdSeguimiento(); ?>, this )" class="btn btn-danger ">Eliminar</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                            <?php
                                    $filaTabla++;
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

<!--FIN DOCUMENTOS RELACIONADOS-->
<div id="divResponsable"></div>
<br />
<br />
<!-- RESPONSABLE -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h3 class="text-center blanco">Responsable</h3>
            </div>
            <div class="panel-body text-center">
                <div class="row text-left">
                    <?php
                    $responsable = $serviceUsuario->getUsuarioPorId($documento[0]['id_responsable']);
                    ?>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-md-offset-2">
                        <div class="row">
                            <label class="col-xs-12 col-md-4">Responsable: </label>
                            <label class="col-md-8 fondoInfo <?php echo $responsable == "" ? "rojo" : ''; ?>">
                                <?php
                                if ($responsable != "") :
                                    echo $responsable->getNombre() . " " . $responsable->getApellidoP();
                                else :
                                    echo "Documento no posee responsable";
                                endif;
                                ?>
                            </label>
                        </div>
                        <div class="row">
                            <label class="col-xs-12 col-md-4">Fecha plazo:</label>
                            <label class="col-md-8 fondoInfo">
                                <?php
                                if ($documento[0]['fecha_plazo'] == "0000-00-00") :
                                    echo "No posee fecha de plazo";
                                else :
                                    echo $serviceDocumento->formatoFecha($documento[0]['fecha_plazo']);
                                endif;
                                ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <label class="col-xs-12 col-sm-12 col-md-3">Acciones:</label>
                        <ul class="col-xs-12 col-sm-12 col-md-5">
                            <?php
                            if (isset($acciones)) :
                                if (count($acciones) != 0) :

                                    foreach ($acciones as $a) :
                                        $actividad = $serviceActividad->getActividadPorId($a->getIdActividad());
                            ?>
                                        <li class="fondoInfo"><?php echo $actividad->getActividad(); ?></li>
                            <?php
                                    endforeach;

                                else :
                                    echo "<li class='rojo'>No registra acciones</li>";
                                endif;
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN RESPONSABLE -->
<div id="divBitacoraResponsable"></div>
<br />
<br />
<!--BITACORA DE RESPONSABLE -->
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h3 class="text-center blanco">Bitacora Responsables</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive col-xs-12 col-sm-12 col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <h3 class="pull-left">Estado del Documento:</h3>
                        </div>
                        <div class="col-md-3">
                            <?php
                            switch ($documento[0]['id_estado_doc']):
                                case 1:
                            ?>

                                    <h3 class="pull-left">Abierto</h3>
                                <?php
                                    break;

                                case 2:
                                ?>
                                    <h3 class="rojo pull-left">Cerrado</h3>
                                <?php
                                    break;

                                case 3:
                                ?>
                                    <h3 class="rojo pull-left">Vencido</h3>
                            <?php
                                    break;
                            endswitch;
                            ?>
                        </div>
                    </div>
                    <div id="divBitacoraAjax">
                        <table class="table table-bordered tablaCustom maxWidth">
                            <thead>
                                <tr>
                                    <th>De Usuario</th>
                                    <th>Asunto</th>
                                    <th>Fecha</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($bitacoras)) :
                                    foreach ($bitacoras as $b) :
                                ?>
                                        <tr>
                                            <td><?php echo $b->getEmisor(); ?></td>
                                            <td><?php echo $b->getAsunto(); ?></td>
                                            <td><?php echo $serviceDocumento->formatoFecha($b->getFechaEmision()); ?></td>
                                            <td><?php echo $b->getDetalleRespuesta(); ?></td>
                                        </tr>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <?php if ($documento[0]['id_estado_doc'] != 2) : ?>
                            <input type="button" class="btn btn-success btn-sm pull-right" value="Responder" id="btnResponder" data-toggle="modal" data-target="#modalRespuesta" onclick="cargarModalRespuesta(<?php echo $documento[0]['id_documento']; ?>)">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN BITACORA DE RESPONSABLE -->



<!--MODAL DOCUMENTO RELACIONADO -->
<div class="modal fade" id="modalDocRel" tabindex="-1" role="dialog" aria-labelledby="modalDocRel">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header colorAxioma">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title blanco text-center">Relacionar Documento</h4>
            </div>
            <div class="modal-body">
                <form id="formBuscarDocumentos" class="form-horizontal text-left">
                    <input type="hidden" id="idContratoBuscar" name="idContratoBuscar" value="<?php echo $usuarioSession->getIdContrato(); ?>">
                    <div class="form-group">
                        <label for="numDocumentoBuscar" class="col-sm-1 control-label">N&uacute;mero:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="numDocumentoBuscar" name="numDocumentoBuscar" placeholder="Ingrese N°">
                        </div>
                        <div class="col-sm-3">
                            <select id="idNombreNumero" name="idNombreNumero" class="form-control">
                                <option value="1">N° Documento</option>
                                <option value="2">N° Providencia</option>
                                <option value="3">N° Proceso</option>
                            </select>
                        </div>
                        <label for="materiaBuscar" class="col-sm-1 control-label">Materia:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="materiaBuscar" name="materiaBuscar" placeholder="Ingrese Materia">
                        </div>
                        <div class="col-sm-1">
                            <input type="button" class="btn btn-success" id="btnBuscarDoc" name="btnBuscarDoc" value="Buscar">
                            <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">Salir</button>
                        </div>
                    </div>
                </form>
                <div class="resultadoAjax table-responsive">

                </div>
            </div>
        </div>
    </div>
</div>

