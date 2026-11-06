<?php
require_once '../../data/config_ai.php';
require_once '../../data/Usuario.php';
require_once '../../data/Contrato.php';

$serverHasApiKey = (defined('GEMINI_API_KEY') && trim(GEMINI_API_KEY) !== '');

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
                                    <?php if (!$serverHasApiKey): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalConfigIA">
                                        <i class="fa fa-gear"></i>&nbsp;Configurar API Key
                                    </button>
                                    <?php endif; ?>
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
.sgd-message-content ul,
.sgd-message-content ol {
    padding-left: 20px;
    margin-top: 5px;
    margin-bottom: 6px;
}
.sgd-message-content li {
    margin-bottom: 3px;
}
.sgd-md-h1,
.sgd-md-h2,
.sgd-md-h3,
.sgd-md-h4 {
    color: #761c19;
    font-weight: 700;
    margin-top: 10px;
    margin-bottom: 6px;
    line-height: 1.3;
}
.sgd-md-h1 {
    font-size: 16px;
    border-bottom: 2px solid #761c19;
    padding-bottom: 4px;
}
.sgd-md-h2 {
    font-size: 15px;
    border-bottom: 1px solid rgba(118, 28, 25, 0.35);
    padding-bottom: 3px;
}
.sgd-md-h3 {
    font-size: 14px;
    border-bottom: 1px solid rgba(118, 28, 25, 0.25);
    padding-bottom: 3px;
}
.sgd-md-h4 {
    font-size: 13px;
    color: #333333;
    margin-top: 8px;
    margin-bottom: 4px;
}
.sgd-message-content > .sgd-md-h1:first-child,
.sgd-message-content > .sgd-md-h2:first-child,
.sgd-message-content > .sgd-md-h3:first-child,
.sgd-message-content > .sgd-md-h4:first-child {
    margin-top: 0;
}
.sgd-md-code {
    background-color: rgba(118, 28, 25, 0.1);
    color: #761c19;
    padding: 1px 5px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
}
.sgd-md-hr {
    border: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.15);
    margin: 8px 0;
}
.sgd-md-blockquote {
    border-left: 3px solid #761c19;
    background-color: rgba(255, 255, 255, 0.55);
    padding: 6px 10px;
    margin: 6px 0;
    font-size: 12.5px;
}
.sgd-md-table {
    background-color: #ffffff;
    margin-top: 6px;
    margin-bottom: 8px;
    font-size: 12px;
}
.sgd-md-table th {
    background-color: #761c19;
    color: #ffffff;
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
var serverHasApiKey = <?php echo $serverHasApiKey ? 'true' : 'false'; ?>;

$(document).ready(function() {
    var storedKey = localStorage.getItem('gemini_api_key_sgd');
    if (storedKey) {
        $('#gemini_api_key_input').val(storedKey);
    } else if (!serverHasApiKey) {
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

function formatInlineMarkdown(str) {
    if (!str) return "";
    // Código en línea `texto`
    str = str.replace(/`([^`]+)`/g, '<code class="sgd-md-code">$1</code>');
    // Negritas **texto**
    str = str.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Cursivas *texto*
    str = str.replace(/(^|[^\*])\*([^\*\n]+)\*/g, '$1<em>$2</em>');
    // Botón de descarga de Planilla Excel generada por la IA
    var excelRegex = /\[(.*?)\]\((chatIA\/descargarExcelChatIA\.php\?token=([a-f0-9]+))\)/gi;
    str = str.replace(excelRegex, function(match, label, url) {
        return '<span class="sgd-doc-actions">' +
               '<a href="' + url + '" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i>&nbsp;' + label + '</a>' +
               '</span>';
    });
    // Botones de Descarga y Detalle con el estilo estándar del SGD
    var linkRegex = /\[(.*?)\]\((negocio\/descargarDocumento\.php\?idDocumento=(\d+))\)/gi;
    str = str.replace(linkRegex, function(match, label, url, id) {
        return '<span class="sgd-doc-actions">' +
               '<a href="../' + url + '" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-download"></i>&nbsp;Descargar</a>' +
               '<a href="buscador/detalleDocumentoBuscador.php?idDocumento=' + id + '" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i>&nbsp;Detalle</a>' +
               '</span>';
    });

    var adjLinkRegex = /\[(.*?)\]\((negocio\/descargarDocumento\.php\?idAdjunto=(\d+))\)/gi;
    str = str.replace(adjLinkRegex, function(match, label, url) {
        return '<span class="sgd-doc-actions">' +
               '<a href="../' + url + '" target="_blank" class="btn btn-info btn-sm"><i class="fa fa-paperclip"></i>&nbsp;' + (label || 'Descargar Adjunto') + '</a>' +
               '</span>';
    });
    return str;
}

function formatMarkdown(text) {
    if (!text) return "";

    var escaped = text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    var lines = escaped.split(/\r?\n/);
    var out = [];
    var inUl = false;
    var inOl = false;
    var inTable = false;
    var tableHeaderDone = false;

    function closeLists() {
        if (inUl) {
            out.push('</ul>');
            inUl = false;
        }
        if (inOl) {
            out.push('</ol>');
            inOl = false;
        }
    }

    function closeTable() {
        if (inTable) {
            out.push('</tbody></table>');
            inTable = false;
            tableHeaderDone = false;
        }
    }

    for (var i = 0; i < lines.length; i++) {
        var rawLine = lines[i];
        var line = rawLine.trim();

        // Línea vacía
        if (line === '') {
            closeLists();
            closeTable();
            if (out.length > 0) {
                var lastItem = out[out.length - 1];
                if (lastItem !== '<br>' && !/(<\/h[1-6]>|<\/ul>|<\/ol>|<\/table>|<hr[^>]*>|<\/blockquote>)$/.test(lastItem)) {
                    out.push('<br>');
                }
            }
            continue;
        }

        // Separador horizontal (---, ***, ___)
        if (/^(\-{3,}|\*{3,}|_{3,})$/.test(line)) {
            closeLists();
            closeTable();
            out.push('<hr class="sgd-md-hr">');
            continue;
        }

        // Encabezados Markdown (#, ##, ###, ####, #####, ######)
        var headingMatch = line.match(/^(#{1,6})\s+(.+?)\s*#*$/);
        if (headingMatch) {
            closeLists();
            closeTable();
            var level = headingMatch[1].length;
            var headingContent = formatInlineMarkdown(headingMatch[2]);
            var hClass = level === 1 ? 'sgd-md-h1' : (level === 2 ? 'sgd-md-h2' : (level === 3 ? 'sgd-md-h3' : 'sgd-md-h4'));
            var tag = level <= 3 ? 'h4' : 'h5';
            out.push('<' + tag + ' class="' + hClass + '">' + headingContent + '</' + tag + '>');
            continue;
        }

        // Tablas Markdown (| col1 | col2 |)
        if (/^\|.*\|$/.test(line)) {
            closeLists();
            // Ignorar fila separadora |---|---|
            if (/^\|[\s\-:\|]+\|$/.test(line)) {
                continue;
            }
            var cells = line.replace(/^\||\|$/g, '').split('|');
            if (!inTable) {
                out.push('<table class="table table-bordered table-condensed table-striped sgd-md-table">');
                inTable = true;
                tableHeaderDone = false;
            }
            if (!tableHeaderDone) {
                var ths = '';
                for (var c = 0; c < cells.length; c++) {
                    ths += '<th>' + formatInlineMarkdown(cells[c].trim()) + '</th>';
                }
                out.push('<thead><tr>' + ths + '</tr></thead><tbody>');
                tableHeaderDone = true;
            } else {
                var tds = '';
                for (var c2 = 0; c2 < cells.length; c2++) {
                    tds += '<td>' + formatInlineMarkdown(cells[c2].trim()) + '</td>';
                }
                out.push('<tr>' + tds + '</tr>');
            }
            continue;
        } else {
            closeTable();
        }

        // Blockquote (> texto)
        var quoteMatch = line.match(/^&gt;\s*(.+)$/);
        if (quoteMatch) {
            closeLists();
            out.push('<div class="sgd-md-blockquote">' + formatInlineMarkdown(quoteMatch[1]) + '</div>');
            continue;
        }

        // Lista con viñetas (* , - , • )
        var ulMatch = line.match(/^(\*|\-|•)\s+(.+)$/);
        if (ulMatch) {
            if (inOl) {
                out.push('</ol>');
                inOl = false;
            }
            if (!inUl) {
                out.push('<ul>');
                inUl = true;
            }
            out.push('<li>' + formatInlineMarkdown(ulMatch[2]) + '</li>');
            continue;
        }

        // Lista numerada (1. , 2. )
        var olMatch = line.match(/^\d+\.\s+(.+)$/);
        if (olMatch) {
            if (inUl) {
                out.push('</ul>');
                inUl = false;
            }
            if (!inOl) {
                out.push('<ol>');
                inOl = true;
            }
            out.push('<li>' + formatInlineMarkdown(olMatch[1]) + '</li>');
            continue;
        }

        // Párrafo / línea normal
        closeLists();
        var formattedLine = formatInlineMarkdown(line);
        if (out.length > 0) {
            var prev = out[out.length - 1];
            if (prev !== '<br>' && !/(<\/h[1-6]>|<\/ul>|<\/ol>|<\/table>|<hr[^>]*>|<\/div>)$/.test(prev)) {
                out.push('<br>');
            }
        }
        out.push(formattedLine);
    }

    closeLists();
    closeTable();

    return out.join('');
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
