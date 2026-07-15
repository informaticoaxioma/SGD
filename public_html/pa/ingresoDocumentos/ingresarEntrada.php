<?php require_once '../../negocio/ingresoDocumento/procesarIngresarEntrada.php'; ?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Ingresar Entrada</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <form id="formIngresarEntrada" class="form-horizontal text-left">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="contrato" class="col-sm-4 control-label">Asesor&iacute;a / Contrato:</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" value="<?php echo $contrato->getContrato(); ?>" disabled="true">
                                </div>
                            </div>
                            <div  class="form-group">
                                <label for="idSubContrato" class="col-sm-4 control-label">Sub-Contrato:</label>
                                <div  class="col-sm-8">
                                    <select id="idSubContrato" name="idSubContrato" class="form-control">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($subContratos)):
                                            foreach ($subContratos as $sc) :
                                                ?>
                                                <option value="<?php echo $sc->getIdSubcontrato(); ?>"><?php echo $sc->getNombreSubContrato(); ?></option>
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
                                    <input type="text" class="form-control" id="numeroDoc" name="numeroDoc" placeholder="Ingrese N° Documento">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="numeroProv" class="col-sm-4 control-label">N° Providencia:</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" id="numeroProv" name="numeroProv" placeholder="Ingrese N° Providencia">
                                </div>
                                <label  class="col-sm-3 " style="color:#761c19!important;" data-toggle="tooltip" data-placement="top" title="Ultimo N° Providencia Ingresado"><?php echo $correlativo; ?></label>
                            </div>
                            <div class="form-group">
                                <label for="numeroProceso" class="col-sm-4 control-label">N° Proceso:</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" id="numeroProceso" name="numeroProceso" placeholder="Ingrese N° Proceso" value="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="remitente" class="col-sm-4 control-label">Remitente:</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="remitente" name="remitente">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($remitentes)) :
                                            foreach ($remitentes as $r) :
                                                ?>
                                                <option value="<?php echo $r['id_entidad'] ?>"><?php echo $r['nombre_entidad'] . " " . $r['apellido_entidad'] . " (" . $r['cargo'] . ")"; ?></option>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAgregarEntidad" onclick="cargarModalAgregarEntidad(1)"><i class="fa fa-plus-circle"></i>&nbsp;Remitente</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="destinatario" class="col-sm-4 control-label">Destinatario:</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="destinatario" name="destinatario">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($destinatarios)) :
                                            foreach ($destinatarios as $d) :
                                                ?>
                                                <option value="<?php echo $d['id_entidad'] ?>"><?php echo $d['nombre_entidad'] . " " . $d['apellido_entidad'] . " (" . $d['cargo'] . ")"; ?></option>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" id="btnAgregarDestinatario" name="btnAgregarDestinatario" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAgregarEntidad" onclick="cargarModalAgregarEntidad(2)"><i class="fa fa-plus-circle"></i>&nbsp;Destinatario</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="materia" class="col-sm-4 control-label">Materia:</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control noResize sanitize-input" id="materia" name="materia" placeholder="Ingrese Materia..." rows="3"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="antecedente" class="col-sm-4 control-label">Antecedente:</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control noResize sanitize-input" id="antecedente" name="antecedente" placeholder="Ingrese antecedente..." rows="4"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="incluye" class="col-sm-4 control-label">Incluye:</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control noResize sanitize-input" id="incluye" name="incluye" placeholder="Ingrese Datos..." rows="3"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comentarios" class="col-sm-4 control-label">Comentarios:</label>
                                <div class="col-sm-8">
                                    <textarea id="comentarios" name="comentarios" class="form-control noResize sanitize-input" placeholder="Ingrese comentario..." rows="4"></textarea>
                                </div>
                            </div>

                            <div id="divDocumento" class="form-group">
                                <label for="documento" class="col-sm-4 control-label">Documento:</label>
                                <div class="col-sm-8">
                                    <input type="file" id="documento" name="documento" accept=".pdf">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="adjunto" class="col-sm-4 control-label">Adjunto:</label>
                                <div class="col-sm-8">
                                    <input type="file" id="adjunto" name="adjunto" onchange="detalleAdjuntos()" multiple="multiple" accept=".pdf">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"></label>
                                <div id="listadoAdjunto" class="col-sm-8">
                                    <img id="imgCarga" alt="gif-carga" src="media/715.gif" class="noDisplay">

                                </div>
                            </div>
                        </div>

                        <!--COLUMNA DERECHA -->
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="idTipoDoc" class="col-sm-4 control-label">Tipo Documento:</label>
                                <div class="col-sm-6">
                                    <select id="idTipoDoc" name="idTipoDoc" class="form-control">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if (isset($tipoDocumentos)):
                                            foreach ($tipoDocumentos as $td) :
                                                ?>
                                                <option value="<?php echo $td->getIdTipoDocumento(); ?>"><?php echo $td->getTipoDocumento(); ?></option>

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
                                    <input type="text" id="fechaDoc" name="fechaDoc" class="form-control" placeholder="DD-MM-AA">
                                </div>
                            </div>
                            <div id="divFechaRec"class="form-group">
                                <label for="fechaRecepcion" class="col-sm-4 control-label">Fecha Recepci&oacute;n:</label>
                                <div class="col-sm-4">
                                    <input type="text" id="fechaRecepcion" name="fechaRecepcion" class="form-control" placeholder="DD-MM-AA">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fechaPlazo" class="col-sm-4 control-label">Fecha Plazo:</label>
                                <div class="col-sm-4">
                                    <input type="text" id="fechaPlazo" name="fechaPlazo" class="form-control" placeholder="DD-MM-AA">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="idResponsable" class="col-sm-4 control-label">Responsable:</label>
                                <div class="col-sm-6 responsables">
                                    <select class="form-control" id="idResponsable" name="idResponsable">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="idCorresponsable" class="col-sm-4 control-label">Co-Responsable (CTRL + Click para Seleccion Múltiple):</label>                                
                                <div class="col-sm-6 corrresponsablesMultiple">
                                    <select class="form-control" id="idCorresponsable" name="idCorresponsable" multiple>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>



                           <!-- 
                            <div class="form-group">
                                <label for="idResponsable" class="col-sm-4 control-label">Email Conocimiento:</label>
                                <div class="col-sm-6 responsablesMultiple">
                                    <select class="form-control" id="idResponsableMultiple" name="idResponsableMultiple" multiple>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>
-->
                            <!--ACCIONES -->
                            <div class="form-group">
                                <label for="fechaPlazo" class="col-sm-4 control-label">Acciones:</label>
                                <div class="col-sm-6">
                                    <table  class="text-left table " >
                                        <tr>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="conocimiento" type="checkbox" value="1">
                                                        Conocimiento
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="coordinar" type="checkbox" value="2">
                                                        Coordinar
                                                    </label>
                                                </div>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="conversar" type="checkbox" value="3">
                                                        Conversar
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="archivo" type="checkbox" value="4">
                                                        Archivo
                                                    </label>
                                                </div>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="responder" type="checkbox" value="5">
                                                        Responder
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="revisar" type="checkbox" value="6">
                                                        Revisar
                                                    </label>
                                                </div>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="urgente" type="checkbox" value="7">
                                                        Urgente
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <p id="mensajeValAccion" class="rojo noDisplay">Por favor, seleccione una acción</p>
                                </div>
                            </div>  <!--FIN ACCIONES -->

                            <div class="form-group">
                                <label for="docRelacionado" class="col-sm-4 control-label">Doc. Relacionado:</label>
                                <div class="col-sm-2">
                                    <input type="button" id="docRelacionado" name="docRelacionado" class="btn btn-primary btn-sm" value="Editar" data-toggle="modal" data-target="#modalDocRel">
                                </div>
                            </div>
                            <div class="row">
                                <div id="tablaDocsRelacionados" class="col-md-11">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group ">
                                <label class="col-sm-6 control-label"></label>
                                <div class="col-sm-2">
                                    <input type="submit" id="btnIngresarEntrada" name="btnIngresarEntrada" class="btn btn-success btn-lg" value="Ingresar Entrada">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="progress noDisplay gifCarga">
                    <div class="progress-bar progress-bar-striped active colorAxioma maxWidth" role="progressbar"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="alert alert-success noDisplay mensajeExito">
                            <label><i class="fa fa-fw fa-book"></i> Documento agregado exitosamente</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="alert alert-danger  noDisplay mensajeError">
                            <label><i class="fa fa-fw fa-warning"></i> Error, no se pudo agregar el Documento</label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!--MODAL DOCUMENTO RELACIONADO -->
<div class="modal fade" id="modalDocRel" tabindex="-1" role="dialog" aria-labelledby="modalDocRel">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header colorAxioma">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times blanco"></i></button>
                <h4 class="modal-title blanco text-center">Relacionar Documento</h4>
            </div>
            <div class="modal-body">

                <form id="formBuscarDocumentos" class="form-horizontal text-left" >
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
                        </div>
                    </div>

                </form>
                <div class="resultadoAjax table-responsive">

                </div>
            </div>
        </div>
    </div>
</div>

<!--MODAL AGREGAR entidad-->

<div class="modal fade" id="modalAgregarEntidad" tabindex="-1" role="dialog" aria-labelledby="modalAgregarEntidad">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header colorAxioma">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times blanco"></i></button>
                <h4 id="tituloTipoEntidad" class="modal-title blanco text-center"></h4>
                <input type="hidden" id="idTipoEntidad" name="idTipoEntidad" value="">
            </div>
            <div class="modal-body">

                <form id="formAgregarEntidad" class="form-horizontal text-left" >
                    <div class="form-group">
                        <label for="nombreEntidad" class="col-sm-3 control-label">Nombre: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nombreEntidad" name="nombreEntidad" placeholder="Ingrese Nombre">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="apellidoEntidad" class="col-sm-3 control-label">Apellido: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="apellidoEntidad" name="apellidoEntidad" placeholder="Ingrese Apellido">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="idCargo" class="col-sm-3 control-label">Cargo: </label>
                        <div class="col-sm-8">
                            <select class="form-control" id="idCargo" name="idCargo">
                                <option value="">Seleccione</option>
                                <?php
                                if (isset($cargos)) :
                                    foreach ($cargos as $c):
                                        ?>
                                        <option value="<?php echo $c->getIdCargo(); ?>"><?php echo $c->getCargo(); ?></option>
                                        <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>

                    <!--AGREGAR CARGO -->
                    <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 9): ?>
                        <div class="form-group">
                            <label for="btnMostrarCargo" class="col-sm-3 control-label">Agregar Cargo: </label>
                            <div class="col-sm-8">
                                <button type="button" id="btnMostrarCargo" name="btnMostrarCargo" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i>&nbsp;Agregar</button>
                            </div>
                        </div>
                        <div id="divIngresarCargo" class="noDisplay">
                            <div  class="form-group">
                                <label for="nombreCargo" class="col-sm-3 control-label">Nombre Cargo: </label>
                                <div class="col-sm-6">
                                    <input class="form-control" id="nombreCargo" name="nombreCargo" placeholder="Ingrese Cargo">
                                    <label id="lblError" class="rojo noDisplay">Por favor, ingrese cargo</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label  class="col-sm-3 control-label"></label>
                                <div class="col-sm-3">
                                    <button type="button" id="btnAgregarCargo" name="btnAgregarCargo" class="btn btn-warning btn-sm"></i>&nbsp;Agregar Cargo</button>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button" id="btnCerrarCargo" class="btn btn-danger btn-sm"></i>&nbsp;Cerrar</button>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <!--FIN AGREGAR CARGO -->
                    <?php endif; ?>
                    <div class="form-group">
                        <label f class="col-sm-3 control-label"></label>
                        <div class="col-sm-2">
                            <input type="submit" id="btnAgregarEntidad" name="btnAgregarEntidad" value="Agregar" class="btn btn-success">
                        </div>
                        <div class="col-sm-2">
                            <input type="button" value="Salir" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        </div>
                    </div>
                </form>
                <div class="row">

                    <div class="col-xs-12 col-sm-12 col-md-12 mensajeExitoModal noDisplay">
                        <div class="alert alert-success">
                            <label><i class="fa fa-user"></i> Entidad agregada exitosamente</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <br/>
                    <div class="col-xs-12 col-sm-12 col-md-12 mensajeErrorModal noDisplay">
                        <div class="alert alert-danger">
                            <label><i class="fa fa-warning"></i> Error, no se ha podido agregar la entidad</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
