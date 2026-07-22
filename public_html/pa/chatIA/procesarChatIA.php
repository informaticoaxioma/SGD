<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../../data/config_ai.php';
require_once '../../data/Conexion.php';
require_once '../../data/Usuario.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'Sesión expirada o no iniciada. Por favor, vuelva a iniciar sesión.']);
    exit;
}

$usuarioSession = $_SESSION['usuario'];

// Obtener datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
$query = isset($input['query']) ? trim($input['query']) : '';
$clientApiKey = isset($input['api_key']) ? trim($input['api_key']) : '';

if (empty($query)) {
    echo json_encode(['error' => 'La consulta no puede estar vacía.']);
    exit;
}

// API Key a utilizar (Servidor o Cliente)
$apiKey = (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') ? GEMINI_API_KEY : $clientApiKey;
if (empty($apiKey)) {
    echo json_encode(['error' => 'Falta la API Key de Gemini. Por favor configúrela en los ajustes del chat.']);
    exit;
}

// Conectar a la base de datos
$serviceConexion = new Conexion();
$cnx = $serviceConexion->conectar();
if (is_string($cnx)) {
    echo json_encode(['error' => 'Error de conexión con la base de datos: ' . $cnx]);
    exit;
}
mysqli_set_charset($cnx, 'utf8');

// Helper para llamar a Gemini
function llamarGemini($apiKey, $prompt, $jsonMode = false) {
    $model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-1.5-flash';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];
    
    if ($jsonMode) {
        $data['generationConfig'] = [
            'responseMimeType' => 'application/json'
        ];
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => $error];
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['error' => 'API returned HTTP code ' . $httpCode . '. Response: ' . $response];
    }
    
    $resObj = json_decode($response, true);
    if (isset($resObj['candidates'][0]['content']['parts'][0]['text'])) {
        return ['text' => $resObj['candidates'][0]['content']['parts'][0]['text']];
    } else {
        return ['error' => 'Respuesta no válida de Gemini. API Response: ' . $response];
    }
}

// Helper para ejecutar consultas seguras
function ejecutarSelect($cnx, $sql, $params = [], $types = "") {
    $stmt = mysqli_prepare($cnx, $sql);
    if (!$stmt) {
        return ['error' => mysqli_error($cnx)];
    }
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// PASO 1: Clasificación de intención y extracción de entidades con Gemini
$stage1Prompt = "Analiza la consulta en lenguaje natural de un usuario del Sistema de Gestión Documental (SGD) de Axioma y extrae los criterios de búsqueda estructurados.
La base de datos contiene estas tablas principales:
1. `contrato`: información de contratos (nombre_contrato, fecha_termino, id_area)
2. `usuario`: información de usuarios (nombre, apellido_p, apellido_m, correo, nombre_usuario, id_contrato, id_perfil)
3. `documento` y `detalle_documento`: información de documentos cargados en el sistema (nombre_documento, fecha_documento, fecha_recepcion, num_documento, num_providencia, num_proceso, materia, comentario, id_subcontrato)

Debes retornar EXCLUSIVAMENTE un objeto JSON estructurado con las siguientes claves:
- 'intent': Debe ser uno de: 'contract', 'user', 'document', 'general' (la intención principal de búsqueda).
- 'keywords': Un array de palabras clave (strings) extraídas de la consulta para usar en búsquedas LIKE generales. Si no hay términos de texto significativos, mantén el array vacío.
- 'filters': Un objeto con filtros clave extraídos de la consulta:
  - 'usuario_nombre': string o null (si se busca un nombre/apellido de persona específico)
  - 'usuario_correo': string o null (si se busca un email de usuario específico)
  - 'contrato_nombre': string o null (si se busca un nombre de contrato específico)
  - 'doc_nombre': string o null (si se busca un título o nombre de documento específico)
  - 'doc_numero': string o null (si se menciona algún número de documento, providencia o proceso)
  - 'fecha_desde': string o null (fecha en formato YYYY-MM-DD si se pide filtrar desde una fecha)
  - 'fecha_hasta': string o null (fecha en formato YYYY-MM-DD si se pide filtrar hasta una fecha)

Consulta del usuario: \"" . $query . "\"";

$stage1Res = llamarGemini($apiKey, $stage1Prompt, true);

if (isset($stage1Res['error'])) {
    echo json_encode(['error' => 'Error al analizar la consulta con la IA (Paso 1): ' . $stage1Res['error']]);
    mysqli_close($cnx);
    exit;
}

$searchParams = json_decode($stage1Res['text'], true);
if (!$searchParams) {
    // Si falla el parseo, usamos parámetros por defecto
    $searchParams = [
        'intent' => 'general',
        'keywords' => explode(' ', $query),
        'filters' => []
    ];
}

$intent = $searchParams['intent'] ?? 'general';
$keywords = $searchParams['keywords'] ?? [];
$filters = $searchParams['filters'] ?? [];

$dbResults = [];

// PASO 2: Realizar las consultas a la base de datos según la intención detectada
if ($intent === 'contract' || $intent === 'general') {
    $sql = "SELECT c.id_contrato, c.nombre_contrato, c.fecha_termino, a.nombre_area 
            FROM contrato c 
            LEFT JOIN area a ON c.id_area = a.id_area 
            WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['contrato_nombre'])) {
        $sql .= " AND c.nombre_contrato LIKE ?";
        $params[] = "%" . $filters['contrato_nombre'] . "%";
        $types .= "s";
    } elseif (!empty($keywords)) {
        $sql .= " AND (";
        $orConds = [];
        foreach ($keywords as $kw) {
            if (trim($kw) !== "") {
                $orConds[] = "c.nombre_contrato LIKE ?";
                $params[] = "%" . $kw . "%";
                $types .= "s";
            }
        }
        if (!empty($orConds)) {
            $sql .= implode(" OR ", $orConds);
        } else {
            $sql .= "1=1";
        }
        $sql .= ")";
    }
    
    $sql .= " LIMIT 15";
    $res = ejecutarSelect($cnx, $sql, $params, $types);
    if (!isset($res['error'])) {
        $dbResults['contratos'] = $res;
    }
}

if ($intent === 'user' || $intent === 'general') {
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido_p, u.apellido_m, u.correo, u.nombre_usuario, c.nombre_contrato, p.perfil AS nombre_perfil
            FROM usuario u
            LEFT JOIN contrato c ON u.id_contrato = c.id_contrato
            LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
            WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['usuario_nombre'])) {
        $sql .= " AND (u.nombre LIKE ? OR u.apellido_p LIKE ? OR u.apellido_m LIKE ?)";
        $params[] = "%" . $filters['usuario_nombre'] . "%";
        $params[] = "%" . $filters['usuario_nombre'] . "%";
        $params[] = "%" . $filters['usuario_nombre'] . "%";
        $types .= "sss";
    }
    if (!empty($filters['usuario_correo'])) {
        $sql .= " AND u.correo LIKE ?";
        $params[] = "%" . $filters['usuario_correo'] . "%";
        $types .= "s";
    }
    
    if (empty($filters['usuario_nombre']) && empty($filters['usuario_correo']) && !empty($keywords)) {
        $sql .= " AND (";
        $orConds = [];
        foreach ($keywords as $kw) {
            if (trim($kw) !== "") {
                $orConds[] = "u.nombre LIKE ? OR u.apellido_p LIKE ? OR u.correo LIKE ? OR u.nombre_usuario LIKE ?";
                $params[] = "%" . $kw . "%";
                $params[] = "%" . $kw . "%";
                $params[] = "%" . $kw . "%";
                $params[] = "%" . $kw . "%";
                $types .= "ssss";
            }
        }
        if (!empty($orConds)) {
            $sql .= implode(" OR ", $orConds);
        } else {
            $sql .= "1=1";
        }
        $sql .= ")";
    }
    
    $sql .= " LIMIT 15";
    $res = ejecutarSelect($cnx, $sql, $params, $types);
    if (!isset($res['error'])) {
        $dbResults['usuarios'] = $res;
    }
}

if ($intent === 'document' || $intent === 'general') {
    $sql = "SELECT d.id_documento, d.nombre_documento, d.fecha_documento, d.fecha_recepcion, d.fecha_plazo,
                   dd.num_documento, dd.num_providencia, dd.num_proceso, dd.materia, dd.comentario,
                   sc.nombre_subcontrato, c.nombre_contrato
            FROM documento d
            JOIN detalle_documento dd USING (id_documento)
            LEFT JOIN subcontrato sc USING (id_subcontrato)
            LEFT JOIN contrato c ON sc.id_contrato = c.id_contrato
            WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['doc_nombre'])) {
        $sql .= " AND d.nombre_documento LIKE ?";
        $params[] = "%" . $filters['doc_nombre'] . "%";
        $types .= "s";
    }
    if (!empty($filters['doc_numero'])) {
        $sql .= " AND (dd.num_documento LIKE ? OR dd.num_providencia LIKE ? OR dd.num_proceso LIKE ?)";
        $params[] = "%" . $filters['doc_numero'] . "%";
        $params[] = "%" . $filters['doc_numero'] . "%";
        $params[] = "%" . $filters['doc_numero'] . "%";
        $types .= "sss";
    }
    if (!empty($filters['contrato_nombre'])) {
        $sql .= " AND c.nombre_contrato LIKE ?";
        $params[] = "%" . $filters['contrato_nombre'] . "%";
        $types .= "s";
    }
    if (!empty($filters['fecha_desde'])) {
        $sql .= " AND d.fecha_documento >= ?";
        $params[] = $filters['fecha_desde'];
        $types .= "s";
    }
    if (!empty($filters['fecha_hasta'])) {
        $sql .= " AND d.fecha_documento <= ?";
        $params[] = $filters['fecha_hasta'];
        $types .= "s";
    }
    
    if (empty($filters['doc_nombre']) && empty($filters['doc_numero']) && empty($filters['contrato_nombre']) && empty($filters['fecha_desde']) && empty($filters['fecha_hasta']) && !empty($keywords)) {
        $sql .= " AND (";
        $orConds = [];
        foreach ($keywords as $kw) {
            if (trim($kw) !== "") {
                $orConds[] = "d.nombre_documento LIKE ? OR dd.num_documento LIKE ? OR dd.materia LIKE ? OR dd.comentario LIKE ?";
                $params[] = "%" . $kw . "%";
                $params[] = "%" . $kw . "%";
                $params[] = "%" . $kw . "%";
                $params[] = "%" . $kw . "%";
                $types .= "ssss";
            }
        }
        if (!empty($orConds)) {
            $sql .= implode(" OR ", $orConds);
        } else {
            $sql .= "1=1";
        }
        $sql .= ")";
    }
    
    // Si no hay ningún parámetro de búsqueda y es una intención de documentos, ordenar por fecha descendente
    if (empty($params)) {
        $sql .= " ORDER BY d.fecha_documento DESC";
    }
    
    $sql .= " LIMIT 15";
    $res = ejecutarSelect($cnx, $sql, $params, $types);
    if (!isset($res['error'])) {
        $dbResults['documentos'] = $res;
    }
}

// Cerrar conexión
mysqli_close($cnx);

// PASO 3: Construcción de la respuesta final con Gemini (RAG)
$stage2Prompt = "Eres un asistente de Inteligencia Artificial para el Sistema de Gestión Documental (SGD) de Axioma.
Tu objetivo es responder de forma profesional, clara y amable a la consulta del usuario basándote en los datos reales que se han recuperado de la base de datos del sistema.

Consulta del usuario: \"" . $query . "\"

Datos reales del sistema:
" . json_encode($dbResults, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "

Instrucciones para elaborar tu respuesta:
1. Responde en español neutro de forma profesional y atenta.
2. Si la base de datos contiene documentos y los estás mencionando en tu respuesta, incluye un enlace de descarga directa en formato Markdown de la siguiente manera exacta: `[Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)` donde ID es el id_documento correspondiente. Ejemplo: `[Descargar Documento: Factura 2024](negocio/descargarDocumento.php?idDocumento=105)`.
3. Si los datos están completamente vacíos o no corresponden a lo solicitado, indícalo de manera educada. Sugiérele términos alternativos o aclara qué tipo de información sí puedes buscar (contratos, usuarios o documentos). No inventes nombres, correos, contratos ni documentos que no estén listados arriba.
4. Usa formatos legibles (listas con viñetas, negritas) para destacar información clave.
5. NO utilices jerga técnica de base de datos ni hagas mención a la estructura de la consulta SQL ejecutada, ni que se han hecho 'dos llamadas' o 'búsquedas previas'. Preséntate directamente como el asistente de consulta del SGD de Axioma.";

$stage2Res = llamarGemini($apiKey, $stage2Prompt);

if (isset($stage2Res['error'])) {
    echo json_encode(['error' => 'Error al generar la respuesta con la IA (Paso 2): ' . $stage2Res['error']]);
    exit;
}

echo json_encode([
    'success' => true,
    'answer' => $stage2Res['text'],
    'intent' => $intent,
    'filters_detected' => $searchParams
]);
