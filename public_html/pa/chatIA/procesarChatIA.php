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
$userId = (int)$usuarioSession->getIdUsuario();
$userProfile = (int)$usuarioSession->getIdPerfil();
$userContractId = (int)$usuarioSession->getIdContrato();
$userFullName = trim($usuarioSession->getNombre() . ' ' . $usuarioSession->getApellidoP() . ' ' . $usuarioSession->getApellidoM());
$userLoginName = $usuarioSession->getNombreUsuario();

// Obtener datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
$query = isset($input['query']) ? trim($input['query']) : '';
$clientApiKey = isset($input['api_key']) ? trim($input['api_key']) : '';
$customVars = isset($input['variables']) && is_array($input['variables']) ? $input['variables'] : [];

if (empty($query)) {
    echo json_encode(['error' => 'La consulta no puede estar vacía.']);
    exit;
}

// API Key a utilizar (Servidor o Cliente)
$apiKey = (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') ? GEMINI_API_KEY : $clientApiKey;
if (empty($apiKey)) {
    echo json_encode(['error' => 'Falta la API Key de Gemini. Por favor configúrela en el botón "Configurar API Key".']);
    exit;
}

// Conectar a la base de datos
$serviceConexion = new Conexion();
$cnx = $serviceConexion->conectar();
if (is_string($cnx) || !$cnx) {
    echo json_encode(['error' => 'Error de conexión con la base de datos: ' . (is_string($cnx) ? $cnx : 'Conexión nula')]);
    exit;
}
mysqli_set_charset($cnx, 'utf8');

/**
 * Ejecuta consultas preparadas capturando cualquier excepción SQL (PHP 8.1+ mysqli_sql_exception)
 * para evitar errores HTTP 500.
 */
function ejecutarSelect($cnx, $sql, $params = [], $types = "") {
    try {
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
    } catch (Throwable $e) {
        error_log("Error SQL en procesarChatIA.php: " . $e->getMessage() . " | SQL: " . $sql);
        return ['error' => $e->getMessage()];
    }
}

/**
 * Genera una expresión LIKE segura en UTF-8 que evita el error
 * "Illegal mix of collations (latin1_bin,NONE) and (binary,COERCIBLE)"
 * y permite búsqueda insensible a mayúsculas y tildes.
 */
function sqlLikeUtf8($column) {
    return "CONVERT($column USING utf8) COLLATE utf8_general_ci LIKE CONVERT(? USING utf8) COLLATE utf8_general_ci";
}

/**
 * Normaliza un texto en PHP quitando tildes, pasando a minúsculas y colapsando espacios.
 */
function normalizarTexto($str) {
    $str = mb_strtolower(trim((string)$str), 'UTF-8');
    $str = strtr($str, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'Ü' => 'u'
    ]);
    $str = preg_replace('/\s+/u', ' ', $str);
    return trim($str);
}

/**
 * Extrae palabras significativas para buscar responsables, remitentes o destinatarios,
 * ignorando conectores y artículos como "el", "la", "de", "del", "al", etc.
 */
function extraerPalabrasSignificativas($texto) {
    $conectores = [
        'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'de', 'del', 'al', 'a', 'en', 'con', 'por', 'para',
        'y', 'o', 'u', 'que', 'como', 'tiene', 'tienen', 'es', 'son', 'fue', 'sea', 'responsable', 'responsables',
        'remitente', 'remitentes', 'destinatario', 'destinatarios', 'documento', 'documentos', 'ultimo', 'ultimos',
        'ultima', 'ultimas', 'reciente', 'recientes', 'dame', 'mostrar', 'muestra', 'ver', 'buscar', 'busca',
        'enviado', 'enviados', 'dirigido', 'dirigidos', 'asignado', 'asignados', 'cargo'
    ];
    $limpio = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', (string)$texto);
    $partes = preg_split('/\s+/u', trim($limpio), -1, PREG_SPLIT_NO_EMPTY);
    $palabras = [];
    foreach ($partes as $p) {
        $pNorm = normalizarTexto($p);
        if (mb_strlen($pNorm) >= 2 && !in_array($pNorm, $conectores, true)) {
            $palabras[] = $p;
        }
    }
    return $palabras;
}

/**
 * Formatea una fila de documento proveniente de la base de datos limpiando espacios dobles.
 */
function formatearFilaDocumento($row) {
    $responsable = trim(preg_replace('/\s+/u', ' ', ($row['resp_nombre'] ?? '') . ' ' . ($row['resp_apellido_p'] ?? '') . ' ' . ($row['resp_apellido_m'] ?? '')));
    $remitente = trim(preg_replace('/\s+/u', ' ', ($row['rem_nombre'] ?? '') . ' ' . ($row['rem_apellido'] ?? '')));
    $destinatario = trim(preg_replace('/\s+/u', ' ', ($row['dest_nombre'] ?? '') . ' ' . ($row['dest_apellido'] ?? '')));
    $nombreContrato = !empty($row['contrato_sub']) ? $row['contrato_sub'] : ($row['contrato_usr'] ?? '');

    return [
        'id_documento' => (int)$row['id_documento'],
        'nombre_documento' => $row['nombre_documento'],
        'fecha_documento' => $row['fecha_documento'],
        'fecha_recepcion' => $row['fecha_recepcion'],
        'fecha_plazo' => $row['fecha_plazo'],
        'estado_documento' => $row['estado_documento'],
        'flujo_documento' => $row['flujo_documento'],
        'num_documento' => $row['num_documento'],
        'num_providencia' => $row['num_providencia'],
        'num_proceso' => $row['num_proceso'],
        'materia' => $row['materia'],
        'antecedente' => $row['antecedente'],
        'incluye' => $row['incluye'],
        'comentario' => $row['comentario'],
        'tipo_documento' => $row['tipo_documento'],
        'id_responsable' => (int)$row['id_responsable'],
        'responsable' => $responsable,
        'correo_responsable' => $row['correo_responsable'] ?? '',
        'id_remitente' => (int)$row['id_remitente'],
        'remitente' => $remitente,
        'cargo_remitente' => $row['cargo_remitente'] ?? '',
        'id_destinatario' => (int)$row['id_destinatario'],
        'destinatario' => $destinatario,
        'cargo_destinatario' => $row['cargo_destinatario'] ?? '',
        'nombre_subcontrato' => $row['nombre_subcontrato'] ?? '',
        'nombre_contrato' => $nombreContrato
    ];
}

/**
 * Función para acceder y buscar documentos del contrato del usuario logueado
 * filtrando por sus variables: responsable, remitente, destinatario, tipo, materia,
 * número, estado, flujo, subcontrato, fechas y palabras clave.
 */
function buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = []) {
    $sql = "SELECT d.id_documento, d.nombre_documento, d.fecha_documento, d.fecha_recepcion, d.fecha_plazo,
                   CASE WHEN d.id_estado_doc = 1 THEN 'Pendiente' WHEN d.id_estado_doc = 2 THEN 'Cerrado' ELSE 'Otro' END AS estado_documento,
                   CASE WHEN d.id_flujo = 1 THEN 'Entrada' WHEN d.id_flujo = 2 THEN 'Salida' ELSE 'Otro' END AS flujo_documento,
                   dd.num_documento, dd.num_providencia, dd.num_proceso, dd.materia, dd.antecedente, dd.incluye, dd.comentario,
                   td.tipo_documento,
                   dd.id_responsable,
                   u.nombre AS resp_nombre, u.apellido_p AS resp_apellido_p, u.apellido_m AS resp_apellido_m,
                   u.correo AS correo_responsable,
                   dd.id_remitente,
                   rem.nombre_entidad AS rem_nombre, rem.apellido_entidad AS rem_apellido,
                   crem.cargo AS cargo_remitente,
                   dd.id_destinatario,
                   dest.nombre_entidad AS dest_nombre, dest.apellido_entidad AS dest_apellido,
                   cdest.cargo AS cargo_destinatario,
                   sc.nombre_subcontrato,
                   c.nombre_contrato AS contrato_sub,
                   cu.nombre_contrato AS contrato_usr
            FROM documento d
            INNER JOIN detalle_documento dd ON d.id_documento = dd.id_documento
            LEFT JOIN subcontrato sc ON d.id_subcontrato = sc.id_subcontrato
            LEFT JOIN contrato c ON sc.id_contrato = c.id_contrato
            LEFT JOIN usuario u ON dd.id_responsable = u.id_usuario
            LEFT JOIN contrato cu ON u.id_contrato = cu.id_contrato
            LEFT JOIN tipo_documento td ON dd.id_tipo_doc = td.id_tipo_doc
            LEFT JOIN entidad rem ON dd.id_remitente = rem.id_entidad
            LEFT JOIN cargo crem ON rem.id_cargo = crem.id_cargo
            LEFT JOIN entidad dest ON dd.id_destinatario = dest.id_entidad
            LEFT JOIN cargo cdest ON dest.id_cargo = cdest.id_cargo
            WHERE 1=1";
    $params = [];
    $types = "";

    // Restricción estricta al contrato del usuario logueado
    if ($userContractId === 1) {
        $sql .= " AND ((sc.id_contrato = ? AND (u.id_contrato = ? OR u.id_contrato IS NULL)) OR u.id_contrato = ?)";
        $params[] = $userContractId;
        $params[] = $userContractId;
        $params[] = $userContractId;
        $types .= "iii";
    } else {
        $sql .= " AND ((d.id_subcontrato <> 1 AND sc.id_contrato = ?) OR u.id_contrato = ?)";
        $params[] = $userContractId;
        $params[] = $userContractId;
        $types .= "ii";
    }

    // 1. Filtro por ID exacto si fue enviado
    if (!empty($customVars['id_responsable'])) {
        $sql .= " AND dd.id_responsable = ?";
        $params[] = (int)$customVars['id_responsable'];
        $types .= "i";
    }
    if (!empty($customVars['id_remitente'])) {
        $sql .= " AND dd.id_remitente = ?";
        $params[] = (int)$customVars['id_remitente'];
        $types .= "i";
    }
    if (!empty($customVars['id_destinatario'])) {
        $sql .= " AND dd.id_destinatario = ?";
        $params[] = (int)$customVars['id_destinatario'];
        $types .= "i";
    }

    // 2. Filtro si pide solo documentos donde el usuario logueado es responsable
    if (!empty($filters['solo_mis_documentos'])) {
        $sql .= " AND dd.id_responsable = ?";
        $params[] = $userId;
        $types .= "i";
    }

    // 3. Variable: RESPONSABLE (por texto/nombre extraído por IA)
    $responsableFiltro = !empty($filters['responsable']) ? $filters['responsable'] : (!empty($filters['usuario_nombre']) ? $filters['usuario_nombre'] : null);
    if (empty($customVars['id_responsable']) && !empty($responsableFiltro)) {
        $words = extraerPalabrasSignificativas($responsableFiltro);

        // Primero intentamos resolver contra la lista de usuarios/responsables del contrato del usuario (insensible a tildes y espacios dobles)
        $matchedUserIds = [];
        if (!empty($words) && !empty($usuariosContrato)) {
            foreach ($usuariosContrato as $uc) {
                $fullNorm = normalizarTexto(($uc['nombre'] ?? '') . ' ' . ($uc['apellido_p'] ?? '') . ' ' . ($uc['apellido_m'] ?? '') . ' ' . ($uc['nombre_usuario'] ?? ''));
                $allMatched = true;
                foreach ($words as $w) {
                    if (mb_strpos($fullNorm, normalizarTexto($w)) === false) {
                        $allMatched = false;
                        break;
                    }
                }
                if ($allMatched) {
                    $matchedUserIds[] = (int)$uc['id_usuario'];
                }
            }
        }

        if (!empty($matchedUserIds)) {
            $placeholders = implode(',', array_fill(0, count($matchedUserIds), '?'));
            $sql .= " AND dd.id_responsable IN ($placeholders)";
            foreach ($matchedUserIds as $uid) {
                $params[] = $uid;
                $types .= "i";
            }
        } elseif (!empty($words)) {
            foreach ($words as $w) {
                $sql .= " AND (" . sqlLikeUtf8('u.nombre') . " OR " . sqlLikeUtf8('u.apellido_p') . " OR " . sqlLikeUtf8('u.apellido_m') . " OR " . sqlLikeUtf8('u.nombre_usuario') . ")";
                $likeW = "%" . $w . "%";
                $params[] = $likeW;
                $params[] = $likeW;
                $params[] = $likeW;
                $params[] = $likeW;
                $types .= "ssss";
            }
        }
    }

    // 4. Variable: REMITENTE (por nombre, apellido o cargo)
    if (empty($customVars['id_remitente']) && !empty($filters['remitente'])) {
        $words = extraerPalabrasSignificativas($filters['remitente']);
        foreach ($words as $w) {
            $sql .= " AND (" . sqlLikeUtf8('rem.nombre_entidad') . " OR " . sqlLikeUtf8('rem.apellido_entidad') . " OR " . sqlLikeUtf8('crem.cargo') . ")";
            $likeW = "%" . $w . "%";
            $params[] = $likeW;
            $params[] = $likeW;
            $params[] = $likeW;
            $types .= "sss";
        }
    }

    // 5. Variable: DESTINATARIO (por nombre, apellido o cargo)
    if (empty($customVars['id_destinatario']) && !empty($filters['destinatario'])) {
        $words = extraerPalabrasSignificativas($filters['destinatario']);
        foreach ($words as $w) {
            $sql .= " AND (" . sqlLikeUtf8('dest.nombre_entidad') . " OR " . sqlLikeUtf8('dest.apellido_entidad') . " OR " . sqlLikeUtf8('cdest.cargo') . ")";
            $likeW = "%" . $w . "%";
            $params[] = $likeW;
            $params[] = $likeW;
            $params[] = $likeW;
            $types .= "sss";
        }
    }

    // 6. Otras variables del documento: estado, flujo, tipo, materia, subcontrato, nombre, número y fechas
    if (!empty($filters['estado_doc'])) {
        $sql .= " AND d.id_estado_doc = ?";
        $params[] = (int)$filters['estado_doc'];
        $types .= "i";
    }

    if (!empty($filters['flujo_doc'])) {
        $sql .= " AND d.id_flujo = ?";
        $params[] = (int)$filters['flujo_doc'];
        $types .= "i";
    }

    if (!empty($filters['tipo_documento'])) {
        $sql .= " AND " . sqlLikeUtf8('td.tipo_documento');
        $params[] = "%" . trim($filters['tipo_documento']) . "%";
        $types .= "s";
    }

    if (!empty($filters['subcontrato'])) {
        $sql .= " AND " . sqlLikeUtf8('sc.nombre_subcontrato');
        $params[] = "%" . trim($filters['subcontrato']) . "%";
        $types .= "s";
    }

    if (!empty($filters['materia'])) {
        $sql .= " AND (" . sqlLikeUtf8('dd.materia') . " OR " . sqlLikeUtf8('dd.antecedente') . " OR " . sqlLikeUtf8('dd.comentario') . ")";
        $likeM = "%" . trim($filters['materia']) . "%";
        $params[] = $likeM;
        $params[] = $likeM;
        $params[] = $likeM;
        $types .= "sss";
    }

    if (!empty($filters['doc_nombre'])) {
        $sql .= " AND (" . sqlLikeUtf8('d.nombre_documento') . " OR " . sqlLikeUtf8('dd.materia') . ")";
        $params[] = "%" . $filters['doc_nombre'] . "%";
        $params[] = "%" . $filters['doc_nombre'] . "%";
        $types .= "ss";
    }

    if (!empty($filters['doc_numero'])) {
        $sql .= " AND (" . sqlLikeUtf8('dd.num_documento') . " OR " . sqlLikeUtf8('dd.num_providencia') . " OR " . sqlLikeUtf8('dd.num_proceso') . ")";
        $params[] = "%" . $filters['doc_numero'] . "%";
        $params[] = "%" . $filters['doc_numero'] . "%";
        $params[] = "%" . $filters['doc_numero'] . "%";
        $types .= "sss";
    }

    if (!empty($filters['fecha_desde'])) {
        $sql .= " AND (d.fecha_documento >= ? OR d.fecha_recepcion >= ?)";
        $params[] = $filters['fecha_desde'];
        $params[] = $filters['fecha_desde'];
        $types .= "ss";
    }

    if (!empty($filters['fecha_hasta'])) {
        $sql .= " AND (d.fecha_documento <= ? OR d.fecha_recepcion <= ?)";
        $params[] = $filters['fecha_hasta'];
        $params[] = $filters['fecha_hasta'];
        $types .= "ss";
    }

    // 7. Búsqueda por palabras clave generales sobre todas las variables del documento
    $hasSpecificVarFilter = !empty($customVars['id_responsable']) || !empty($customVars['id_remitente']) || !empty($customVars['id_destinatario']) ||
                            !empty($responsableFiltro) || !empty($filters['remitente']) || !empty($filters['destinatario']) ||
                            !empty($filters['tipo_documento']) || !empty($filters['subcontrato']) || !empty($filters['materia']) ||
                            !empty($filters['doc_nombre']) || !empty($filters['doc_numero']) ||
                            !empty($filters['fecha_desde']) || !empty($filters['fecha_hasta']);

    if (!$hasSpecificVarFilter && !empty($keywords)) {
        $orConds = [];
        foreach ($keywords as $kw) {
            if (trim($kw) !== "") {
                $colsKw = [
                    'd.nombre_documento', 'dd.num_documento', 'dd.num_providencia', 'dd.num_proceso',
                    'dd.materia', 'dd.antecedente', 'dd.comentario', 'td.tipo_documento',
                    'u.nombre', 'u.apellido_p', 'u.apellido_m',
                    'rem.nombre_entidad', 'rem.apellido_entidad', 'crem.cargo',
                    'dest.nombre_entidad', 'dest.apellido_entidad', 'cdest.cargo'
                ];
                $likeParts = [];
                $likeKw = "%" . trim($kw) . "%";
                foreach ($colsKw as $colName) {
                    $likeParts[] = sqlLikeUtf8($colName);
                    $params[] = $likeKw;
                    $types .= "s";
                }
                $orConds[] = "(" . implode(" OR ", $likeParts) . ")";
            }
        }
        if (!empty($orConds)) {
            $sql .= " AND (" . implode(" OR ", $orConds) . ")";
        }
    }

    // Ordenar siempre por los documentos más recientes primero (2026 -> 2025 -> ...)
    // Sin GROUP BY en SQL para evitar incompatibilidades con ONLY_FULL_GROUP_BY; deduplicamos por id_documento en PHP.
    $sql .= " ORDER BY GREATEST(COALESCE(NULLIF(d.fecha_documento, '0000-00-00'), '1000-01-01'), COALESCE(NULLIF(d.fecha_recepcion, '0000-00-00'), '1000-01-01')) DESC,
                       d.fecha_documento DESC,
                       d.fecha_recepcion DESC,
                       d.id_documento DESC
              LIMIT 50";

    $rawRows = ejecutarSelect($cnx, $sql, $params, $types);
    if (isset($rawRows['error'])) {
        return $rawRows;
    }

    $docs = [];
    $seenIds = [];
    foreach ($rawRows as $r) {
        $idDoc = (int)$r['id_documento'];
        if (!isset($seenIds[$idDoc])) {
            $seenIds[$idDoc] = true;
            $docs[] = formatearFilaDocumento($r);
            if (count($docs) >= 20) {
                break;
            }
        }
    }
    return $docs;
}

// Validar en BD la información actualizada del usuario que tiene el login y su contrato asignado
$userDbInfo = ejecutarSelect(
    $cnx,
    "SELECT u.id_usuario, u.nombre, u.apellido_p, u.apellido_m, u.nombre_usuario, u.id_perfil, u.id_contrato,
            c.nombre_contrato, p.perfil AS nombre_perfil
     FROM usuario u
     LEFT JOIN contrato c ON u.id_contrato = c.id_contrato
     LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
     WHERE u.id_usuario = ? AND u.id_estado_usuario = 1
     LIMIT 1",
    [$userId],
    "i"
);

if (empty($userDbInfo) || isset($userDbInfo['error'])) {
    mysqli_close($cnx);
    echo json_encode(['error' => 'No se pudo validar la cuenta del usuario activo en el sistema.']);
    exit;
}

$currentUser = $userDbInfo[0];
$userContractId = (int)$currentUser['id_contrato'];
$userProfile = (int)$currentUser['id_perfil'];
$userContractName = $currentUser['nombre_contrato'] ?? ('Contrato #' . $userContractId);
$userFullName = trim(preg_replace('/\s+/u', ' ', ($currentUser['nombre'] ?? '') . ' ' . ($currentUser['apellido_p'] ?? '') . ' ' . ($currentUser['apellido_m'] ?? '')));
$userLoginName = $currentUser['nombre_usuario'];

if ($userContractId <= 0) {
    mysqli_close($cnx);
    echo json_encode(['error' => 'El usuario en sesión no tiene un contrato asignado para realizar consultas.']);
    exit;
}

// Obtener la lista de usuarios (responsables) pertenecientes al contrato del usuario logueado
$usuariosContratoRaw = ejecutarSelect(
    $cnx,
    "SELECT u.id_usuario, u.nombre, u.apellido_p, u.apellido_m, u.correo, u.nombre_usuario,
            c.nombre_contrato, p.perfil AS nombre_perfil
     FROM usuario u
     LEFT JOIN contrato c ON u.id_contrato = c.id_contrato
     LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
     WHERE u.id_contrato = ? AND u.id_estado_usuario = 1
     ORDER BY u.nombre ASC, u.apellido_p ASC",
    [$userContractId],
    "i"
);
$usuariosContrato = !isset($usuariosContratoRaw['error']) ? $usuariosContratoRaw : [];
$nombresResponsablesContrato = [];
foreach ($usuariosContrato as $uc) {
    $nombresResponsablesContrato[] = trim(preg_replace('/\s+/u', ' ', ($uc['nombre'] ?? '') . ' ' . ($uc['apellido_p'] ?? '') . ' ' . ($uc['apellido_m'] ?? '')));
}

// Helper para llamar a Gemini
function llamarGemini($apiKey, $prompt, $jsonMode = false) {
    $model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-flash-latest';
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

$fechaActual = date('Y-m-d');
$anioActual = date('Y');
$listaRespTexto = implode(', ', $nombresResponsablesContrato);

// PASO 1: Clasificación de intención y extracción de variables/entidades con Gemini
$stage1Prompt = "Analiza la consulta en lenguaje natural de un usuario del Sistema de Gestión Documental (SGD) de Axioma y extrae los criterios y variables de búsqueda estructurados.
Fecha actual del sistema: {$fechaActual} (Año actual: {$anioActual}).
El usuario autenticado pertenece exclusivamente al contrato: \"{$userContractName}\" (ID Contrato: {$userContractId}).
Dentro de este contrato existen los siguientes usuarios/responsables registrados: [{$listaRespTexto}].

La base de datos contiene estas tablas principales (restringidas al contrato del usuario logueado):
1. `contrato` y `subcontrato`: información del contrato y subcontratos del usuario (nombre_contrato, fecha_termino, nombre_subcontrato)
2. `usuario`: información de usuarios/responsables del contrato (nombre, apellido_p, apellido_m, correo, nombre_usuario)
3. `documento`, `detalle_documento`, `entidad` y `cargo`: información de documentos cargados en el contrato y sus variables:
   - `responsable`: usuario/cargo interno del contrato responsable del documento (por ejemplo: 'Jefe Asesoria', 'Encargada De Gestion', 'Jefe Area Proyectos', 'Asesor Legal', etc.)
   - `remitente`: persona/entidad o cargo que envía o emite el documento
   - `destinatario`: persona/entidad o cargo a quien va dirigido el documento
   - `tipo_documento`: tipo (Oficio, Carta, Informe, Providencia, Memorándum, Resolución, etc.)
   - `materia`, `antecedente`, `comentario`, `num_documento`, `num_providencia`, `num_proceso`, `fecha_documento`, `fecha_recepcion`, `fecha_plazo`, `id_estado_doc`, `id_flujo`

IMPORTANTE: Si el usuario menciona cargos o nombres como 'Jefe Asesoria', 'Jefe de Asesoría', 'Encargada de Gestión', 'Jefe Área Construcción', 'Asesor Legal', etc. (incluso con errores ortográficos o sin tildes), corrígelo e identifícalo como `responsable` (o `remitente`/`destinatario` según el contexto de la frase) DENTRO de su propio contrato, y NUNCA como un contrato distinto (`contrato_nombre` debe ser null).

Debes retornar EXCLUSIVAMENTE un objeto JSON estructurado con las siguientes claves:
- 'intent': Debe ser uno de: 'contract', 'user', 'document', 'general' (si se consulta por documentos según su responsable, remitente o destinatario, el intent DEBE ser 'document').
- 'keywords': Un array de palabras clave específicas (strings) para búsquedas generales. Si los términos ya fueron ubicados en 'filters' (como responsable, remitente, destinatario, materia, etc.) o si es una consulta general de 'últimos documentos', DEJA EL ARRAY VACÍO []. NO incluyas palabras genéricas.
- 'filters': Un objeto con las variables extraídas de la consulta (corrigiendo errores de tipeo si aplica):
  - 'responsable': string o null (nombre o cargo del responsable del documento dentro del contrato, ej. 'Jefe Asesoria', sin artículos como 'el' o 'la')
  - 'remitente': string o null (nombre, apellido o cargo de la entidad remitente si se buscan documentos enviados/remitidos por alguien)
  - 'destinatario': string o null (nombre, apellido o cargo de la entidad destinataria si se buscan documentos dirigidos/enviados a alguien)
  - 'tipo_documento': string o null (si se menciona un tipo de documento como Oficio, Carta, Informe, etc.)
  - 'materia': string o null (si se busca por una materia o asunto específico)
  - 'subcontrato': string o null (si se menciona un subcontrato específico)
  - 'usuario_nombre': string o null (si la intención es 'user' y se busca información de un usuario)
  - 'usuario_correo': string o null (si se busca un email de usuario específico)
  - 'doc_nombre': string o null (si se busca un nombre de archivo específico)
  - 'doc_numero': string o null (si se menciona algún número de documento, providencia o proceso)
  - 'estado_doc': int o null (1 si pide documentos 'pendientes', 2 si pide documentos 'cerrados', null en otro caso)
  - 'flujo_doc': int o null (1 si pide documentos de 'entrada', 2 si pide documentos de 'salida', null en otro caso)
  - 'solo_mis_documentos': boolean (true SOLO si el usuario pide explícitamente 'mis documentos' en primera persona, false cuando pregunta por otro responsable del contrato como 'Jefe Asesoria')
  - 'orden_recientes': boolean (true si pide el último documento, los últimos documentos, más recientes o nuevos)
  - 'fecha_desde': string o null (fecha YYYY-MM-DD SOLO si el usuario especifica un año, mes o fecha concreta, ej. '2026-01-01'. Si solo pide 'el último documento' o 'los últimos documentos' sin mencionar fecha/año, pon null).
  - 'fecha_hasta': string o null (fecha YYYY-MM-DD SOLO si el usuario especifica un límite de fecha concreto; de lo contrario null).

Consulta del usuario: \"" . $query . "\"";

$stage1Res = llamarGemini($apiKey, $stage1Prompt, true);

if (isset($stage1Res['error'])) {
    echo json_encode(['error' => 'Error al analizar la consulta con la IA (Paso 1): ' . $stage1Res['error']]);
    mysqli_close($cnx);
    exit;
}

$searchParams = json_decode($stage1Res['text'], true);
if (!$searchParams) {
    $searchParams = [
        'intent' => 'general',
        'keywords' => [],
        'filters' => []
    ];
}

$intent = $searchParams['intent'] ?? 'general';
$keywords = $searchParams['keywords'] ?? [];
$filters = $searchParams['filters'] ?? [];

// Si se detectaron filtros de variables de documentos (responsable, remitente, destinatario), asegurar intent document
if (!empty($customVars['id_responsable']) || !empty($customVars['id_remitente']) || !empty($customVars['id_destinatario']) ||
    !empty($filters['responsable']) || !empty($filters['remitente']) || !empty($filters['destinatario'])) {
    $intent = 'document';
}

// Filtrar palabras clave genéricas (Stopwords)
$stopwords = [
    'usuario', 'usuarios', 'contrato', 'contratos', 'subcontrato', 'subcontratos',
    'documento', 'documentos', 'listar', 'lista', 'mostrar', 'muestra', 'muestrame', 'muéstrame',
    'todos', 'todas', 'ver', 'dame', 'entregar', 'especifico', 'específicos', 'especifica', 'especificas',
    'buscar', 'busca', 'consultar', 'consulta', 'con', 'del', 'los', 'las', 'unos', 'unas', 'para',
    'sobre', 'materia', 'ultimo', 'ultimos', 'último', 'últimos', 'ultima', 'ultimas', 'última', 'últimas',
    'reciente', 'recientes', 'nuevo', 'nuevos', 'nueva', 'nuevas', 'actual', 'actuales', 'registrado',
    'registrados', 'cargado', 'cargados', 'sistema', 'sgd', 'axioma', 'pendiente', 'pendientes',
    'cerrado', 'cerrados', 'entrada', 'salida', 'fecha', 'fechas', 'mes', 'año', 'anio', 'cuales', 'cuáles', 'son', 'hay',
    'responsable', 'responsables', 'remitente', 'remitentes', 'destinatario', 'destinatarios', 'enviado', 'enviados', 'dirigido', 'dirigidos',
    'tiene', 'tienen', 'como'
];
$filteredKeywords = [];
if (is_array($keywords)) {
    foreach ($keywords as $kw) {
        $kwClean = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower(trim((string)$kw)));
        if (!in_array($kwClean, $stopwords) && mb_strlen($kwClean) > 2) {
            $filteredKeywords[] = trim((string)$kw);
        }
    }
}
$keywords = $filteredKeywords;

// Si el usuario consulta por "último documento" / "últimos documentos" / "recientes" sin haber escrito un año o mes explícito,
// limpiamos fecha_desde / fecha_hasta para asegurar que el ORDER BY devuelva los de 2026+.
$queryLower = mb_strtolower($query);
$mentionsExplicitYearOrMonth = preg_match('/\b(20\d{2}|enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\b/u', $queryLower);
if (!$mentionsExplicitYearOrMonth && preg_match('/(últim|ultim|recient|nuev)/u', $queryLower)) {
    $filters['fecha_desde'] = null;
    $filters['fecha_hasta'] = null;
}

$dbResults = [
    'contexto_usuario_logueado' => [
        'id_usuario' => $userId,
        'nombre_completo' => $userFullName,
        'nombre_usuario' => $userLoginName,
        'id_contrato_asignado' => $userContractId,
        'nombre_contrato_asignado' => $userContractName,
        'perfil' => $currentUser['nombre_perfil'] ?? $userProfile
    ],
    'responsables_del_contrato' => $nombresResponsablesContrato
];

// PASO 2: Realizar las consultas a la base de datos restringiendo SIEMPRE al contrato del usuario que tiene el login

if ($intent === 'contract' || $intent === 'general') {
    $sql = "SELECT c.id_contrato, c.nombre_contrato, c.fecha_termino, a.nombre_area
            FROM contrato c
            LEFT JOIN area a ON c.id_area = a.id_area
            WHERE c.id_contrato = ?";
    $res = ejecutarSelect($cnx, $sql, [$userContractId], "i");
    if (!isset($res['error'])) {
        $dbResults['contratos'] = $res;
    }

    $sqlSub = "SELECT sc.id_subcontrato, sc.nombre_subcontrato
               FROM subcontrato sc
               WHERE sc.id_contrato = ?";
    $resSub = ejecutarSelect($cnx, $sqlSub, [$userContractId], "i");
    if (!isset($resSub['error'])) {
        $dbResults['subcontratos'] = $resSub;
    }
}

if ($intent === 'user' || $intent === 'general') {
    $usuariosFormateados = [];
    foreach ($usuariosContrato as $uc) {
        $nombreCompletoU = trim(preg_replace('/\s+/u', ' ', ($uc['nombre'] ?? '') . ' ' . ($uc['apellido_p'] ?? '') . ' ' . ($uc['apellido_m'] ?? '')));
        if (!empty($filters['usuario_nombre'])) {
            $wordsU = extraerPalabrasSignificativas($filters['usuario_nombre']);
            $matchU = true;
            foreach ($wordsU as $wu) {
                if (mb_strpos(normalizarTexto($nombreCompletoU . ' ' . ($uc['nombre_usuario'] ?? '')), normalizarTexto($wu)) === false) {
                    $matchU = false;
                    break;
                }
            }
            if (!$matchU) {
                continue;
            }
        }
        if (!empty($filters['usuario_correo'])) {
            if (mb_strpos(normalizarTexto($uc['correo'] ?? ''), normalizarTexto($filters['usuario_correo'])) === false) {
                continue;
            }
        }
        $usuariosFormateados[] = [
            'id_usuario' => (int)$uc['id_usuario'],
            'nombre_completo' => $nombreCompletoU,
            'correo' => $uc['correo'],
            'nombre_usuario' => $uc['nombre_usuario'],
            'nombre_contrato' => $uc['nombre_contrato'],
            'nombre_perfil' => $uc['nombre_perfil']
        ];
    }
    $dbResults['usuarios'] = $usuariosFormateados;
}

if ($intent === 'document' || $intent === 'general') {
    $resDocs = buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);
    if (!isset($resDocs['error'])) {
        $dbResults['documentos'] = $resDocs;
    } else {
        $dbResults['documentos'] = [];
        $dbResults['error_consulta_documentos'] = $resDocs['error'];
    }
}

// Cerrar conexión
mysqli_close($cnx);

// PASO 3: Construcción de la respuesta final con Gemini (RAG)
$stage2Prompt = "Eres el asistente de Inteligencia Artificial del Sistema de Gestión Documental (SGD) de Axioma.
Fecha actual del sistema: {$fechaActual} (Año {$anioActual}).
Usuario en sesión: {$userFullName} (Usuario: {$userLoginName}).
Contrato asignado al usuario: \"{$userContractName}\" (ID: {$userContractId}).

Tu objetivo es responder de forma directa, profesional, clara y amable a la consulta del usuario basándote EXCLUSIVAMENTE en los datos reales recuperados de la base de datos para su contrato asignado.

Consulta del usuario: \"" . $query . "\"

Datos reales del sistema (filtrados por el contrato \"{$userContractName}\" del usuario en sesión):
" . json_encode($dbResults, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "

Instrucciones obligatorias para elaborar tu respuesta:
1. Responde en español neutro de forma directa y clara. Si el usuario cometió algún error menor de ortografía o tilde al escribir el nombre de un responsable, remitente, destinatario o término (por ejemplo, 'Jefe Asesoria' por 'Jefe Asesoría'), entiéndelo con naturalidad y muéstrale directamente la información encontrada.
2. RESPONSABLES Y USUARIOS DEL CONTRATO: Ten en cuenta que todos los usuarios listados en `responsables_del_contrato` (como 'Jefe Asesoria', 'Encargada De Gestion', 'Jefe Area Proyectos', 'Jefe Area Construccion', 'Asesor Legal', etc.) SON responsables que pertenecen dentro del contrato asignado al usuario (\"{$userContractName}\"). NUNCA rechaces ni bloquees consultas sobre ellos por motivos de seguridad o permisos.
3. SI PIDE 'EL ÚLTIMO DOCUMENTO' (en singular): Muestra en primer lugar y de forma destacada el documento más reciente que encabeza la lista `documentos` (el primer elemento `documentos[0]`, que ya viene ordenado del más reciente de 2026 hacia atrás).
4. VARIABLES DE DOCUMENTOS: Cuando muestres uno o más documentos, incluye sus datos clave de forma ordenada:
   - **N° Documento / Providencia** (`num_documento` / `num_providencia`)
   - **Tipo de Documento** (`tipo_documento`)
   - **Materia** (`materia`)
   - **Fecha del Documento** (`fecha_documento`) y **Fecha de Recepción** (`fecha_recepcion`)
   - **Estado** (`estado_documento`) y **Flujo** (`flujo_documento`)
   - **Responsable** (`responsable`)
   - **Remitente** (`remitente` y `cargo_remitente` si aplica)
   - **Destinatario** (`destinatario` y `cargo_destinatario` si aplica)
5. ENLACES DE DESCARGA: Siempre que menciones un documento, incluye su enlace de descarga directa en formato Markdown exactamente así: `[Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)` donde ID es el `id_documento` correspondiente.
6. Si la lista `documentos` está vacía para ese filtro, indícale amablemente que no se encontraron documentos con ese criterio dentro de su contrato y menciónale los responsables disponibles en `responsables_del_contrato` si buscaba por responsable.
7. NO utilices jerga técnica de base de datos ni menciones consultas SQL o estructuras JSON.";

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
