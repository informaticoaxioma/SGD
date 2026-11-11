<?php
// Configuración del módulo de Inteligencia Artificial para el SGD
// Para configurar la API Key en el servidor sin subirla a GitHub (evitando GitHub Push Protection),
// créala en public_html/data/config_ai.local.php o mediante la variable de entorno GEMINI_API_KEY.

if (file_exists(__DIR__ . '/config_ai.local.php')) {
    require_once __DIR__ . '/config_ai.local.php';
}

if (!defined('GEMINI_API_KEY')) {
    $envKey = getenv('GEMINI_API_KEY');
    define('GEMINI_API_KEY', $envKey !== false ? $envKey : '');
}

if (!defined('GEMINI_MODEL')) {
    define('GEMINI_MODEL', 'gemini-flash-latest');
}

if (!defined('CONTRATOS_AUTORIZADOS_CHAT_IA')) {
    // IDs de contratos con acceso al módulo Chat con IA:
    // 41 = LOS VILOS-LA SERENA
    // 79 = CHILLAN COLLIPULLI III
    define('CONTRATOS_AUTORIZADOS_CHAT_IA', [41, 79]);
}

if (!function_exists('contratoTieneAccesoChatIA')) {
    /**
     * Determina si un contrato tiene acceso al módulo Chat con IA (IDs autorizados: 41 y 79).
     */
    function contratoTieneAccesoChatIA($idContrato, $nombreContrato = '') {
        return in_array((int)$idContrato, CONTRATOS_AUTORIZADOS_CHAT_IA, true);
    }
}
