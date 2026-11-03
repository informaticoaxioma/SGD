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
