<?php
require_once '../../data/Usuario.php';
require_once '../../data/Contrato.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    die("Acceso denegado");
}
$usuarioSession = $_SESSION['usuario'];
$idContratoUsuario = (int)$usuarioSession->getIdContrato();
$nombreUsuarioCompleto = trim($usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP() . " " . $usuarioSession->getApellidoM());

$nombreContratoUsuario = "Contrato #" . $idContratoUsuario;
try {
    $serviceContrato = new Contrato();
    $objContrato = $serviceContrato->getContratoPorId($idContratoUsuario);
    if (is_object($objContrato) && method_exists($objContrato, 'getContrato') && $objContrato->getContrato() != "") {
        $nombreContratoUsuario = $objContrato->getContrato();
    }
} catch (Exception $e) {
    // Mantener nombre por defecto si falla la consulta
}
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco"><i class="fa fa-comments"></i>&nbsp;Chat con IA</h2>
            </div>

            <div class="panel-body paddingBottom text-left">
                <!-- Barra de información del usuario y su contrato asignado -->
                <div class="row" style="margin-bottom: 12px;">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="fondoInfo" style="padding: 10px 15px; border-radius: 4px; border: 1px solid #ccc;">
                            <div class="row">
                                <div class="col-xs-12 col-sm-8 col-md-8" style="line-height: 30px;">
                                    <span>
                                        <i class="fa fa-user color-principal"></i>&nbsp;<strong>Usuario:</strong>
                                        <?php echo htmlspecialchars($nombreUsuarioCompleto); ?>
                                    </span>
                                    &nbsp;&nbsp;|&nbsp;&nbsp;
                                    <span>
                                        <i class="fa fa-briefcase color-principal"></i>&nbsp;<strong>Asesor&iacute;a / Contrato:</strong>
                                        <?php echo htmlspecialchars($nombreContratoUsuario); ?>
                                    </span>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-4 text-right">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalConfigIA">
                                        <i class="fa fa-gear"></i>&nbsp;Configurar API Key
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm" onclick="limpiarChat()">
                                        <i class="fa fa-refresh"></i>&nbsp;Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de consultas rápidas del sistema -->
                <div class="row" style="margin-bottom: 12px;">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label class="control-label" style="margin-right: 8px;"><i class="fa fa-bolt color-principal"></i>&nbsp;Consultas r&aacute;pidas:</label>
                        <button type="button" class="btn btn-default btn-sm sgd-chip-btn" onclick="sendSuggested('Mostrar los últimos documentos registrados de mi contrato')">
                            <i class="fa fa-file-pdf-o color-principal"></i>&nbsp;&Uacute;ltimos documentos
                        </button>
                        <button type="button" class="btn btn-default btn-sm sgd-chip-btn" onclick="sendSuggested('Mostrar mis documentos asignados como responsable')">
                            <i class="fa fa-user-check color-principal"></i>&nbsp;Mis documentos como responsable
                        </button>
                        <button type="button" class="btn btn-default btn-sm sgd-chip-btn" onclick="sendSuggested('Mostrar los documentos pendientes de mi contrato')">
                            <i class="fa fa-clock-o color-principal"></i>&nbsp;Documentos pendientes
                        </button>
                        <button type="button" class="btn btn-default btn-sm sgd-chip-btn" onclick="sendSuggested('Listar los usuarios de mi contrato')">
                            <i class="fa fa-users color-principal"></i>&nbsp;Usuarios de mi contrato
                        </button>
                        <button type="button" class="btn btn-default btn-sm sgd-chip-btn" onclick="sendSuggested('Mostrar información de mi contrato y subcontratos')">
                            <i class="fa fa-briefcase color-principal"></i>&nbsp;Mi contrato y subcontratos
                        </button>
                    </div>
                </div>

                <!-- Contenedor del Historial de Conversación -->
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div id="chatHistory" class="sgd-chat-history">
                            <!-- Mensaje inicial de bienvenida -->
                            <div class="sgd-message sgd-message-ai" id="welcomeMessage">
                                <div class="sgd-message-avatar colorAxioma">
                                    <i class="fa fa-comments blanco"></i>
                                </div>
                                <div class="sgd-message-body">
                                    <div class="sgd-message-header">
                                        <strong>Asistente SGD Axioma</strong>
                                        <span class="sgd-message-time"><?php echo date("H:i"); ?></span>
                                    </div>
                                    <div class="sgd-message-content">
                                        Hola, <strong><?php echo htmlspecialchars($usuarioSession->getNombre()); ?></strong>. Bienvenido al módulo de consulta con Inteligencia Artificial del SGD.
                                        <br><br>
                                        Todas las consultas están validadas y asociadas exclusivamente a tu cuenta y a tu contrato asignado (<strong><?php echo htmlspecialchars($nombreContratoUsuario); ?></strong>). Puedes escribir directamente tu consulta para buscar documentos por:
                                        <ul>
                                            <li><strong>Responsable:</strong> <em>"Dame el último documento que tiene como responsable el Jefe Asesoría"</em></li>
                                            <li><strong>Remitente:</strong> <em>"Documentos enviados por el remitente..."</em></li>
                                            <li><strong>Destinatario:</strong> <em>"Documentos dirigidos al destinatario..."</em></li>
                                            <li><strong>Recientes / Fechas / Materia:</strong> <em>"¿Cuáles son los últimos documentos registrados del 2026?"</em></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de progreso estilo SGD -->
                <div id="chatLoadingBar" class="progress noDisplay" style="margin-top: 10px; margin-bottom: 5px;">
                    <div class="progress-bar progress-bar-striped active colorAxioma maxWidth" role="progressbar"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                        Procesando consulta...
                    </div>
                </div>

                <!-- Formulario de envío de consulta -->
                <div class="row" style="margin-top: 12px;">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="input-group">
                            <input type="text" id="chatInput" class="form-control" placeholder="Consulte por responsable, remitente, destinatario, materia, N° de documento o fecha..." onkeypress="handleKeyPress(event)">
                            <span class="input-group-btn">
                                <button id="btnBuscarChat" class="btn btn-success" type="button" onclick="sendMessage()">
                                    <i class="fa fa-search-plus"></i>&nbsp;Consultar
                                </button>
                            </span>
                        </div>
                        <div id="apiKeyWarning" class="alert alert-warning text-center small noDisplay" style="margin-top: 10px; margin-bottom: 0; padding: 8px;">
                            <i class="fa fa-warning"></i>&nbsp;No has configurado tu API Key de Gemini. Haz clic en <strong>Configurar API Key</strong> arriba a la derecha si el servidor no posee una configurada.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIGURACIÓN API KEY (Diseño estándar del sistema SGD) -->
<div class="modal fade" id="modalConfigIA" tabindex="-1" role="dialog" aria-labelledby="modalConfigIA">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header colorAxioma">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times blanco"></i></button>
                <h4 class="modal-title blanco text-center"><i class="fa fa-key"></i>&nbsp;Configuraci&oacute;n de API Key de Gemini</h4>
            </div>
            <div class="modal-body text-left">
                <form class="form-horizontal" onsubmit="event.preventDefault(); saveApiKey();">
                    <p class="text-muted">
                        Ingrese su clave API de Gemini para realizar consultas en el asistente. La clave se almacenar&aacute; localmente en su navegador.
                    </p>
                    <div class="form-group">
                        <label for="gemini_api_key_input" class="col-sm-3 control-label">API Key:</label>
                        <div class="col-sm-8">
                            <input type="password" id="gemini_api_key_input" class="form-control" placeholder="AIzaSy...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"></label>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-success" onclick="saveApiKey()">
                                <i class="fa fa-save"></i>&nbsp;Guardar
                            </button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                Salir
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.sgd-chip-btn {
    margin-bottom: 4px;
    margin-right: 4px;
}
.sgd-chip-btn:hover {
    background-color: #e3e3e3;
    border-color: #761c19;
}
.sgd-chat-history {
    height: 430px;
    overflow-y: auto;
    border: 1px solid #800020;
    background-color: #ffffff;
    padding: 15px;
    border-radius: 4px;
}
.sgd-message {
    display: flex;
    align-items: flex-start;
    margin-bottom: 14px;
}
.sgd-message-user {
    flex-direction: row-reverse;
}
.sgd-message-avatar {
    width: 34px;
    height: 34px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 10px;
}
.sgd-message-user .sgd-message-avatar {
    background-color: #333333;
    margin-right: 0;
    margin-left: 10px;
}
.sgd-message-body {
    max-width: 85%;
    border-radius: 4px;
    padding: 10px 14px;
    border: 1px solid #ccc;
}
.sgd-message-ai .sgd-message-body {
    background-color: #e3e3e3;
    border-left: 4px solid #761c19;
    color: #000000;
}
.sgd-message-user .sgd-message-body {
    background-color: #761c19;
    border-color: #6b001a;
    color: #ffffff;
}
.sgd-message-header {
    font-size: 12px;
    margin-bottom: 5px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    padding-bottom: 3px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}
.sgd-message-user .sgd-message-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.25);
}
.sgd-message-time {
    font-size: 11px;
    opacity: 0.8;
}
.sgd-message-content {
    font-size: 13px;
    line-height: 1.5;
    word-break: break-word;
}
.sgd-message-content ul {
    padding-left: 20px;
    margin-top: 5px;
    margin-bottom: 5px;
}
.sgd-doc-actions {
    display: inline-block;
    margin: 3px 0;
}
.sgd-doc-actions .btn {
    margin-right: 4px;
}
</style>

<script>
$(document).ready(function() {
    var storedKey = localStorage.getItem('gemini_api_key_sgd');
    if (storedKey) {
        $('#gemini_api_key_input').val(storedKey);
    } else {
        $('#apiKeyWarning').removeClass('noDisplay');
    }
});

function handleKeyPress(e) {
    if (e.which === 13) {
        sendMessage();
    }
}

function saveApiKey() {
    var key = $('#gemini_api_key_input').val().trim();
    if (key === '') {
        localStorage.removeItem('gemini_api_key_sgd');
        $('#apiKeyWarning').removeClass('noDisplay');
        swal('Información', 'Se ha eliminado la clave API local.', 'info');
    } else {
        localStorage.setItem('gemini_api_key_sgd', key);
        $('#apiKeyWarning').addClass('noDisplay');
        swal('¡Éxito!', 'Clave API guardada correctamente.', 'success');
    }
    $('#modalConfigIA').modal('hide');
}

function limpiarChat() {
    var welcome = $('#welcomeMessage').prop('outerHTML');
    $('#chatHistory').html(welcome);
    $('#chatInput').val('').focus();
}

function sendSuggested(text) {
    $('#chatInput').val(text);
    sendMessage();
}

function formatMarkdown(text) {
    if (!text) return "";

    var html = text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    // Negritas **texto**
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    // Cursivas *texto*
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

    // Listas con viñetas
    var lines = html.split('\n');
    var inList = false;
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();
        if (line.indexOf('* ') === 0 || line.indexOf('- ') === 0) {
            var content = line.substring(2);
            if (!inList) {
                lines[i] = '<ul><li>' + content + '</li>';
                inList = true;
            } else {
                lines[i] = '<li>' + content + '</li>';
            }
        } else {
            if (inList) {
                lines[i] = '</ul>' + lines[i];
                inList = false;
            }
        }
    }
    if (inList) {
        lines[lines.length - 1] = lines[lines.length - 1] + '</ul>';
    }
    html = lines.join('\n');

    html = html.replace(/\n/g, '<br>');
    html = html.replace(/<\/ul><br>/g, '</ul>');
    html = html.replace(/<ul><br>/g, '<ul>');
    html = html.replace(/<li><br>/g, '<li>');
    html = html.replace(/<\/li><br>/g, '</li>');

    // Botones de Descarga y Detalle con el estilo estándar del SGD
    var linkRegex = /\[(.*?)\]\((negocio\/descargarDocumento\.php\?idDocumento=(\d+))\)/gi;
    html = html.replace(linkRegex, function(match, label, url, id) {
        return '<span class="sgd-doc-actions">' +
               '<a href="../' + url + '" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-download"></i>&nbsp;Descargar</a>' +
               '<a href="buscador/detalleDocumentoBuscador.php?idDocumento=' + id + '" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i>&nbsp;Detalle</a>' +
               '</span>';
    });

    return html;
}

function sendMessage(overrideQuery, customVariables) {
    var input = overrideQuery ? overrideQuery.trim() : $('#chatInput').val().trim();
    if (input === '') return;

    var userKey = localStorage.getItem('gemini_api_key_sgd') || '';
    if (!overrideQuery) {
        $('#chatInput').val('');
    }

    var time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    var safeInput = input.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

    var userMessageHtml =
        '<div class="sgd-message sgd-message-user">' +
        '    <div class="sgd-message-avatar"><i class="fa fa-user blanco"></i></div>' +
        '    <div class="sgd-message-body">' +
        '        <div class="sgd-message-header">' +
        '            <strong>Tú</strong>' +
        '            <span class="sgd-message-time">' + time + '</span>' +
        '        </div>' +
        '        <div class="sgd-message-content">' + safeInput + '</div>' +
        '    </div>' +
        '</div>';

    $('#chatHistory').append(userMessageHtml);
    scrollChatBottom();

    $('#chatLoadingBar').removeClass('noDisplay');
    $('#btnBuscarChat').prop('disabled', true);

    $.ajax({
        url: 'chatIA/procesarChatIA.php',
        type: 'POST',
        data: JSON.stringify({
            query: input,
            api_key: userKey,
            variables: customVariables || {}
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            $('#chatLoadingBar').addClass('noDisplay');
            $('#btnBuscarChat').prop('disabled', false);

            var aiTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            if (response.error) {
                var errorMessage =
                    '<div class="sgd-message sgd-message-ai">' +
                    '    <div class="sgd-message-avatar" style="background-color: #d9534f;"><i class="fa fa-warning blanco"></i></div>' +
                    '    <div class="sgd-message-body">' +
                    '        <div class="sgd-message-header">' +
                    '            <strong class="rojo">Aviso del Sistema</strong>' +
                    '            <span class="sgd-message-time">' + aiTime + '</span>' +
                    '        </div>' +
                    '        <div class="sgd-message-content rojo"><strong>Error:</strong> ' + response.error + '</div>' +
                    '    </div>' +
                    '</div>';
                $('#chatHistory').append(errorMessage);
            } else {
                var formattedText = formatMarkdown(response.answer);
                var aiMessageHtml =
                    '<div class="sgd-message sgd-message-ai">' +
                    '    <div class="sgd-message-avatar colorAxioma"><i class="fa fa-comments blanco"></i></div>' +
                    '    <div class="sgd-message-body">' +
                    '        <div class="sgd-message-header">' +
                    '            <strong>Asistente SGD Axioma</strong>' +
                    '            <span class="sgd-message-time">' + aiTime + '</span>' +
                    '        </div>' +
                    '        <div class="sgd-message-content">' + formattedText + '</div>' +
                    '    </div>' +
                    '</div>';
                $('#chatHistory').append(aiMessageHtml);
            }
            scrollChatBottom();
        },
        error: function() {
            $('#chatLoadingBar').addClass('noDisplay');
            $('#btnBuscarChat').prop('disabled', false);

            var aiTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            var errMessageHtml =
                '<div class="sgd-message sgd-message-ai">' +
                '    <div class="sgd-message-avatar" style="background-color: #d9534f;"><i class="fa fa-warning blanco"></i></div>' +
                '    <div class="sgd-message-body">' +
                '        <div class="sgd-message-header">' +
                '            <strong class="rojo">Error de Conexión</strong>' +
                '            <span class="sgd-message-time">' + aiTime + '</span>' +
                '        </div>' +
                '        <div class="sgd-message-content rojo">No se pudo establecer comunicación con el servidor.</div>' +
                '    </div>' +
                '</div>';
            $('#chatHistory').append(errMessageHtml);
            scrollChatBottom();
        }
    });
}

function scrollChatBottom() {
    var history = document.getElementById('chatHistory');
    if (history) {
        history.scrollTop = history.scrollHeight;
    }
}
</script>
