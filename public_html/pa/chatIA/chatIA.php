<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    die("Acceso denegado");
}
$usuarioSession = $_SESSION['usuario'];
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
        <div class="panel panel-default sombraPanel chat-panel">
            <div class="panel-heading chat-header">
                <div class="chat-header-title">
                    <i class="fa fa-comments-o chat-icon-main"></i>
                    <div>
                        <h3 class="panel-title blanco font-weight-bold">Asistente Virtual con IA</h3>
                        <small class="blanco-opaco">Consulta información sobre contratos, usuarios y documentos en tiempo real</small>
                    </div>
                </div>
                <div class="chat-header-actions">
                    <button class="btn btn-link btn-settings-toggle text-white" onclick="toggleSettingsModal()" title="Configuración de IA">
                        <i class="fa fa-cog text-white font-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="panel-body chat-body-container">
                <!-- Modal de Configuración de API Key -->
                <div id="aiSettingsModal" class="ai-settings-modal hidden">
                    <div class="ai-settings-content">
                        <h4 class="font-weight-bold text-burdeo"><i class="fa fa-key"></i> Configuración de API Key de Gemini</h4>
                        <p class="text-muted small">
                            Para interactuar con la IA de forma directa y privada, puedes guardar tu API Key de Gemini. Esta se almacenará localmente en tu navegador de forma segura.
                        </p>
                        <div class="form-group">
                            <label for="gemini_api_key_input">API Key de Gemini:</label>
                            <input type="password" id="gemini_api_key_input" class="form-control" placeholder="AIzaSy...">
                        </div>
                        <div class="text-right">
                            <button class="btn btn-default btn-sm" onclick="toggleSettingsModal()">Cancelar</button>
                            <button class="btn btn-success btn-sm" onclick="saveApiKey()">Guardar Clave</button>
                        </div>
                    </div>
                </div>

                <!-- Historial de Conversación -->
                <div id="chatHistory" class="chat-history">
                    <!-- Mensaje de bienvenida de la IA -->
                    <div class="message message-ai">
                        <div class="message-avatar">
                            <i class="fa fa-android"></i>
                        </div>
                        <div class="message-content-wrapper">
                            <div class="message-content">
                                ¡Hola, <strong><?php echo htmlspecialchars($usuarioSession->getNombre()); ?></strong>! Soy tu asistente de Inteligencia Artificial para el Sistema de Gestión Documental (SGD) de Axioma.
                                <br><br>
                                Puedo ayudarte a consultar de forma ágil información clave del sistema. Por ejemplo, puedes preguntarme:
                                <ul>
                                    <li><em>"¿Qué contratos hay en el sistema?"</em></li>
                                    <li><em>"Busca al usuario con nombre de usuario 'admin' o con correo..."</em></li>
                                    <li><em>"Encuentra documentos relacionados con 'licitación' o con materia 'adicional'"</em></li>
                                    <li><em>"Busca documentos del mes de junio del 2024"</em></li>
                                </ul>
                            </div>
                            <div class="message-time"><?php echo date("H:i"); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Sugerencias rápidas (Chips) -->
                <div class="chat-chips-container">
                    <span class="chip" onclick="sendSuggested('Listar contratos registrados')">📋 Contratos vigentes</span>
                    <span class="chip" onclick="sendSuggested('Busca usuarios administradores')">👥 Usuarios admin</span>
                    <span class="chip" onclick="sendSuggested('Buscar documentos del último mes')">📄 Documentos recientes</span>
                    <span class="chip" onclick="sendSuggested('Buscar documentos con materia adicional')">🔍 Materia 'Adicional'</span>
                </div>
            </div>

            <!-- Caja de envío de mensajes -->
            <div class="panel-footer chat-footer">
                <div class="input-group">
                    <input type="text" id="chatInput" class="form-control chat-input" placeholder="Escribe tu consulta aquí..." onkeypress="handleKeyPress(event)">
                    <span class="input-group-btn">
                        <button class="btn btn-burdeo" type="button" onclick="sendMessage()">
                            <span class="hidden-xs">Preguntar</span> <i class="fa fa-paper-plane"></i>
                        </button>
                    </span>
                </div>
                <div id="apiKeyWarning" class="text-warning text-center small hidden" style="margin-top: 5px;">
                    <i class="fa fa-exclamation-triangle"></i> No has configurado tu API Key de Gemini. Haz clic en el engranaje <i class="fa fa-cog"></i> arriba a la derecha para configurarla si el servidor no tiene una configurada.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* --- ESTILOS MODERNOS PREMIUM PARA CHAT DE IA --- */
:root {
    --burdeo-900: #4a1e24;
    --burdeo-800: #5c252c;
    --burdeo-700: #722f37;
    --burdeo-600: #8a3a44;
    --burdeo-500: #a5464f;
    --burdeo-100: #f4e8ea;
    --burdeo-50:  #faf3f4;
    --blanco:     #ffffff;
    --gris-fondo: #f9f9fb;
    --texto:      #2b2b2b;
    --texto-mut:  #6b6b6b;
    --borde:      #e6dcde;
    --sombra:     0 10px 30px rgba(74, 30, 36, 0.08);
}

.sombraPanel {
    box-shadow: var(--sombra) !important;
}

.chat-panel {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--borde);
    background-color: var(--blanco);
    margin-bottom: 30px;
}

/* Header del chat */
.chat-header {
    background: linear-gradient(135deg, var(--burdeo-800) 0%, var(--burdeo-700) 100%) !important;
    padding: 15px 20px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: none !important;
}

.chat-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
}

.chat-icon-main {
    font-size: 2.2rem;
    color: var(--blanco);
}

.font-weight-bold {
    font-weight: 700;
}

.blanco-opaco {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.85rem;
}

.font-lg {
    font-size: 1.35rem;
}

/* Contenedor del Historial */
.chat-body-container {
    position: relative;
    padding: 20px !important;
    background-color: var(--gris-fondo);
    height: 480px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.chat-history {
    overflow-y: auto;
    flex-grow: 1;
    padding-right: 10px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 15px;
}

/* Burbujas de Mensaje */
.message {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    max-width: 85%;
    animation: slideIn 0.3s ease forwards;
}

.message-ai {
    align-self: flex-start;
}

.message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: var(--burdeo-700);
    color: var(--blanco);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.message-user .message-avatar {
    background-color: #6b6b6b;
}

.message-content-wrapper {
    display: flex;
    flex-direction: column;
}

.message-content {
    padding: 12px 16px;
    border-radius: 14px;
    font-size: 0.95rem;
    line-height: 1.5;
    text-align: left;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.message-ai .message-content {
    background-color: var(--blanco);
    color: var(--texto);
    border: 1px solid var(--borde);
    border-top-left-radius: 2px;
}

.message-user .message-content {
    background-color: var(--burdeo-700);
    color: var(--blanco);
    border-top-right-radius: 2px;
}

.message-time {
    font-size: 0.75rem;
    color: var(--texto-mut);
    margin-top: 4px;
    padding: 0 4px;
}

.message-user .message-time {
    text-align: right;
}

/* Enlaces de descarga dentro de los mensajes */
.chat-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background-color: #e3f2fd;
    color: #0d47a1;
    padding: 4px 10px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    margin: 5px 0;
    font-size: 0.85rem;
    transition: background-color 0.2s;
    border: 1px solid #bbdefb;
}

.chat-link:hover {
    background-color: #bbdefb;
    text-decoration: none;
    color: #0d47a1;
}

/* Chips de Sugerencias */
.chat-chips-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid var(--borde);
}

.chip {
    background-color: var(--blanco);
    border: 1px solid var(--borde);
    border-radius: 16px;
    padding: 6px 14px;
    font-size: 0.85rem;
    color: var(--burdeo-800);
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.chip:hover {
    background-color: var(--burdeo-100);
    border-color: var(--burdeo-500);
    transform: translateY(-1px);
}

/* Footer del chat */
.chat-footer {
    background-color: var(--blanco) !important;
    border-top: 1px solid var(--borde) !important;
    padding: 15px 20px !important;
}

.chat-input {
    border-radius: 20px 0 0 20px !important;
    border: 1.5px solid var(--borde);
    padding: 10px 18px !important;
    height: 44px !important;
    font-size: 1rem !important;
    outline: none !important;
    box-shadow: none !important;
    transition: border-color 0.2s;
}

.chat-input:focus {
    border-color: var(--burdeo-600) !important;
}

.btn-burdeo {
    background-color: var(--burdeo-700) !important;
    border-color: var(--burdeo-700) !important;
    color: var(--blanco) !important;
    border-radius: 0 20px 20px 0 !important;
    height: 44px;
    padding: 0 22px !important;
    font-weight: 600;
    font-size: 1rem;
    transition: background-color 0.2s;
}

.btn-burdeo:hover {
    background-color: var(--burdeo-800) !important;
}

/* Animaciones */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Modal de Configuración */
.ai-settings-modal {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(74, 30, 36, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.25s ease;
}

.ai-settings-content {
    background-color: var(--blanco);
    border-radius: 10px;
    padding: 24px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid var(--borde);
    text-align: left;
}

.text-burdeo {
    color: var(--burdeo-700);
}

.hidden {
    display: none !important;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Indicador de carga / escritura */
.typing-dots {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
}

.typing-dot {
    width: 8px;
    height: 8px;
    background-color: var(--texto-mut);
    border-radius: 50%;
    animation: bounceDot 1.4s infinite ease-in-out both;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounceDot {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1.0); }
}
</style>

<script>
// Cargar la API Key almacenada al iniciar
$(document).ready(function() {
    var storedKey = localStorage.getItem('gemini_api_key_sgd');
    if (storedKey) {
        $('#gemini_api_key_input').val(storedKey);
    } else {
        // Mostrar alerta discreta de que no hay API key guardada
        $('#apiKeyWarning').removeClass('hidden');
    }
});

// Manejo del Enter
function handleKeyPress(e) {
    if (e.which === 13) {
        sendMessage();
    }
}

// Alternar el modal de configuración
function toggleSettingsModal() {
    $('#aiSettingsModal').toggleClass('hidden');
}

// Guardar la API Key en localStorage
function saveApiKey() {
    var key = $('#gemini_api_key_input').val().trim();
    if (key === '') {
        localStorage.removeItem('gemini_api_key_sgd');
        $('#apiKeyWarning').removeClass('hidden');
        swal('Info', 'Se ha eliminado la clave API local.', 'info');
    } else {
        localStorage.setItem('gemini_api_key_sgd', key);
        $('#apiKeyWarning').addClass('hidden');
        swal('Éxito', 'Clave API guardada correctamente de forma local.', 'success');
    }
    toggleSettingsModal();
}

// Enviar sugerencia rápida
function sendSuggested(text) {
    $('#chatInput').val(text);
    sendMessage();
}

// Convertir Markdown básico a HTML
function formatMarkdown(text) {
    if (!text) return "";
    
    // Escapar HTML para evitar XSS
    var html = text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
        
    // Negritas **texto**
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Cursivas *texto* o _texto_
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Listas con viñetas en líneas separadas
    var lines = html.split('\n');
    var inList = false;
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();
        // Coincidir con viñeta "* " o "- "
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
    
    // Reemplazar saltos de línea por <br> (evitando romper etiquetas de lista)
    html = html.replace(/\n/g, '<br>');
    html = html.replace(/<\/ul><br>/g, '</ul>');
    html = html.replace(/<ul><br>/g, '<ul>');
    html = html.replace(/<li><br>/g, '<li>');
    html = html.replace(/<\/li><br>/g, '</li>');
    
    // Enlaces personalizados: [Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)
    // El backend escapa a &lt; y &gt; por lo que el regex debe capturar el formato escapado o no
    var linkRegex = /\[(.*?)\]\((negocio\/descargarDocumento\.php\?idDocumento=(\d+))\)/gi;
    html = html.replace(linkRegex, function(match, label, url, id) {
        return '<a href="../' + url + '" target="_blank" class="chat-link"><i class="fa fa-download"></i> ' + label + '</a>';
    });
    
    return html;
}

// Enviar Mensaje
function sendMessage() {
    var input = $('#chatInput').val().trim();
    if (input === '') return;

    // Obtener la clave de localStorage
    var userKey = localStorage.getItem('gemini_api_key_sgd') || '';

    // Limpiar input
    $('#chatInput').val('');

    // Obtener hora actual
    var time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Agregar mensaje del usuario a la vista
    var userMessageHtml = 
        '<div class="message message-user">' +
        '    <div class="message-avatar"><i class="fa fa-user"></i></div>' +
        '    <div class="message-content-wrapper">' +
        '        <div class="message-content">' + input.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") + '</div>' +
        '        <div class="message-time">' + time + '</div>' +
        '    </div>' +
        '</div>';
    
    $('#chatHistory').append(userMessageHtml);
    scrollChatBottom();

    // Agregar indicador de carga/escritura de la IA
    var typingId = 'typing_' + Date.now();
    var typingHtml = 
        '<div class="message message-ai" id="' + typingId + '">' +
        '    <div class="message-avatar"><i class="fa fa-android"></i></div>' +
        '    <div class="message-content-wrapper">' +
        '        <div class="message-content">' +
        '            <div class="typing-dots">' +
        '                <div class="typing-dot"></div>' +
        '                <div class="typing-dot"></div>' +
        '                <div class="typing-dot"></div>' +
        '            </div>' +
        '        </div>' +
        '    </div>' +
        '</div>';
    
    $('#chatHistory').append(typingHtml);
    scrollChatBottom();

    // Petición AJAX
    $.ajax({
        url: 'chatIA/procesarChatIA.php',
        type: 'POST',
        data: JSON.stringify({
            query: input,
            api_key: userKey
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            // Remover indicador de carga
            $('#' + typingId).remove();

            var aiTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            if (response.error) {
                // Mostrar error del backend
                var errorMessage = 
                    '<div class="message message-ai">' +
                    '    <div class="message-avatar" style="background-color: #d9534f;"><i class="fa fa-exclamation"></i></div>' +
                    '    <div class="message-content-wrapper">' +
                    '        <div class="message-content text-danger">' +
                    '            <strong>Error del sistema:</strong> ' + response.error + 
                    '        </div>' +
                    '        <div class="message-time">' + aiTime + '</div>' +
                    '    </div>' +
                    '</div>';
                $('#chatHistory').append(errorMessage);
            } else {
                // Formatear Markdown de la respuesta
                var formattedText = formatMarkdown(response.answer);
                var aiMessageHtml = 
                    '<div class="message message-ai">' +
                    '    <div class="message-avatar"><i class="fa fa-android"></i></div>' +
                    '    <div class="message-content-wrapper">' +
                    '        <div class="message-content">' + formattedText + '</div>' +
                    '        <div class="message-time">' + aiTime + '</div>' +
                    '    </div>' +
                    '</div>';
                $('#chatHistory').append(aiMessageHtml);
            }
            scrollChatBottom();
        },
        error: function(xhr, status, error) {
            // Remover indicador de carga
            $('#' + typingId).remove();

            var aiTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            var errMessageHtml = 
                '<div class="message message-ai">' +
                '    <div class="message-avatar" style="background-color: #d9534f;"><i class="fa fa-exclamation"></i></div>' +
                '    <div class="message-content-wrapper">' +
                '        <div class="message-content text-danger">' +
                '            <strong>Error de conexión:</strong> No se pudo contactar con el controlador backend.' +
                '        </div>' +
                '        <div class="message-time">' + aiTime + '</div>' +
                '    </div>' +
                '</div>';
            $('#chatHistory').append(errMessageHtml);
            scrollChatBottom();
        }
    });
}

// Scroll automático al fondo
function scrollChatBottom() {
    var history = document.getElementById('chatHistory');
    if (history) {
        history.scrollTop = history.scrollHeight;
    }
}
</script>
