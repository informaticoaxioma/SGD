<?php
@ini_set('display_errors', '0');
@ini_set('memory_limit', '512M');
@set_time_limit(120);
ob_start();
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
session_write_close();
$userId = (int)$usuarioSession->getIdUsuario();
$userProfile = (int)$usuarioSession->getIdPerfil();
$userContractId = (int)$usuarioSession->getIdContrato();
if ($userContractId !== 41) {
    echo json_encode(['error' => 'Acceso restringido: El módulo Chat con IA solo está habilitado para usuarios pertenecientes al contrato autorizado.']);
    exit;
}
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
        'enviado', 'enviados', 'dirigido', 'dirigidos', 'asignado', 'asignados', 'cargo',
        'cuanto', 'cuantos', 'cuanta', 'cuantas', 'cantidad', 'cantidades', 'cifra', 'cifras',
        'total', 'totales', 'historico', 'historicos', 'historica', 'historicas', 'registro', 'registros',
        'estadistica', 'estadisticas', 'conteo', 'contar', 'hay', 'existen'
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

    $fechaCierre = !empty($row['fecha_cierre']) && $row['fecha_cierre'] !== '0000-00-00' ? $row['fecha_cierre'] : null;
    $diasEnCerrar = null;
    if ($fechaCierre !== null) {
        $fIni = (!empty($row['fecha_documento']) && $row['fecha_documento'] !== '0000-00-00')
            ? $row['fecha_documento']
            : ((!empty($row['fecha_recepcion']) && $row['fecha_recepcion'] !== '0000-00-00') ? $row['fecha_recepcion'] : null);
        if ($fIni !== null) {
            $tsIni = strtotime(substr(trim($fIni), 0, 10));
            $tsFin = strtotime(substr(trim($fechaCierre), 0, 10));
            if ($tsIni !== false && $tsFin !== false) {
                $diff = (int)round(($tsFin - $tsIni) / 86400);
                if ($diff >= 0 && $diff <= 3650) {
                    $diasEnCerrar = $diff;
                }
            }
        }
    }

    return [
        'id_documento' => (int)$row['id_documento'],
        'nombre_documento' => $row['nombre_documento'],
        'fecha_documento' => $row['fecha_documento'],
        'fecha_recepcion' => $row['fecha_recepcion'],
        'fecha_plazo' => $row['fecha_plazo'],
        'fecha_cierre' => $fechaCierre,
        'dias_en_cerrar' => $diasEnCerrar,
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
 * Construye los JOINs y cláusula WHERE común para consultar o contar documentos
 * del contrato del usuario logueado aplicando todos los filtros de variables.
 */
function construirBaseWhereDocumentos($userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = []) {
    $fromJoins = " FROM documento d
            INNER JOIN detalle_documento dd ON d.id_documento = dd.id_documento
            LEFT JOIN subcontrato sc ON d.id_subcontrato = sc.id_subcontrato
            LEFT JOIN contrato c ON sc.id_contrato = c.id_contrato
            LEFT JOIN usuario u ON dd.id_responsable = u.id_usuario
            LEFT JOIN contrato cu ON u.id_contrato = cu.id_contrato
            LEFT JOIN tipo_documento td ON dd.id_tipo_doc = td.id_tipo_doc
            LEFT JOIN entidad rem ON dd.id_remitente = rem.id_entidad
            LEFT JOIN cargo crem ON rem.id_cargo = crem.id_cargo
            LEFT JOIN entidad dest ON dd.id_destinatario = dest.id_entidad
            LEFT JOIN cargo cdest ON dest.id_cargo = cdest.id_cargo";

    $where = " WHERE 1=1";
    $params = [];
    $types = "";

    // Restricción estricta al contrato del usuario logueado
    if ($userContractId === 1) {
        $where .= " AND ((sc.id_contrato = ? AND (u.id_contrato = ? OR u.id_contrato IS NULL)) OR u.id_contrato = ?)";
        $params[] = $userContractId;
        $params[] = $userContractId;
        $params[] = $userContractId;
        $types .= "iii";
    } else {
        $where .= " AND ((d.id_subcontrato <> 1 AND sc.id_contrato = ?) OR u.id_contrato = ?)";
        $params[] = $userContractId;
        $params[] = $userContractId;
        $types .= "ii";
    }

    // 1. Filtro por ID exacto si fue enviado
    if (!empty($customVars['id_responsable'])) {
        $where .= " AND dd.id_responsable = ?";
        $params[] = (int)$customVars['id_responsable'];
        $types .= "i";
    }
    if (!empty($customVars['id_remitente'])) {
        $where .= " AND dd.id_remitente = ?";
        $params[] = (int)$customVars['id_remitente'];
        $types .= "i";
    }
    if (!empty($customVars['id_destinatario'])) {
        $where .= " AND dd.id_destinatario = ?";
        $params[] = (int)$customVars['id_destinatario'];
        $types .= "i";
    }

    // 2. Filtro si pide solo documentos donde el usuario logueado es responsable
    if (!empty($filters['solo_mis_documentos'])) {
        $where .= " AND dd.id_responsable = ?";
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
            $where .= " AND dd.id_responsable IN ($placeholders)";
            foreach ($matchedUserIds as $uid) {
                $params[] = $uid;
                $types .= "i";
            }
        } elseif (!empty($words)) {
            foreach ($words as $w) {
                $where .= " AND (" . sqlLikeUtf8('u.nombre') . " OR " . sqlLikeUtf8('u.apellido_p') . " OR " . sqlLikeUtf8('u.apellido_m') . " OR " . sqlLikeUtf8('u.nombre_usuario') . ")";
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
            $where .= " AND (" . sqlLikeUtf8('rem.nombre_entidad') . " OR " . sqlLikeUtf8('rem.apellido_entidad') . " OR " . sqlLikeUtf8('crem.cargo') . ")";
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
            $where .= " AND (" . sqlLikeUtf8('dest.nombre_entidad') . " OR " . sqlLikeUtf8('dest.apellido_entidad') . " OR " . sqlLikeUtf8('cdest.cargo') . ")";
            $likeW = "%" . $w . "%";
            $params[] = $likeW;
            $params[] = $likeW;
            $params[] = $likeW;
            $types .= "sss";
        }
    }

    // 6. Otras variables del documento: estado, flujo, tipo, materia, subcontrato, nombre, número y fechas
    if (!empty($filters['estado_doc'])) {
        $where .= " AND d.id_estado_doc = ?";
        $params[] = (int)$filters['estado_doc'];
        $types .= "i";
    }

    if (!empty($filters['flujo_doc'])) {
        $where .= " AND d.id_flujo = ?";
        $params[] = (int)$filters['flujo_doc'];
        $types .= "i";
    }

    if (!empty($filters['tipo_documento'])) {
        $where .= " AND " . sqlLikeUtf8('td.tipo_documento');
        $params[] = "%" . trim($filters['tipo_documento']) . "%";
        $types .= "s";
    }

    if (!empty($filters['subcontrato'])) {
        $where .= " AND " . sqlLikeUtf8('sc.nombre_subcontrato');
        $params[] = "%" . trim($filters['subcontrato']) . "%";
        $types .= "s";
    }

    if (!empty($filters['materia'])) {
        $where .= " AND (" . sqlLikeUtf8('dd.materia') . " OR " . sqlLikeUtf8('dd.antecedente') . " OR " . sqlLikeUtf8('dd.comentario') . ")";
        $likeM = "%" . trim($filters['materia']) . "%";
        $params[] = $likeM;
        $params[] = $likeM;
        $params[] = $likeM;
        $types .= "sss";
    }

    if (!empty($filters['doc_nombre'])) {
        $where .= " AND (" . sqlLikeUtf8('d.nombre_documento') . " OR " . sqlLikeUtf8('dd.materia') . ")";
        $params[] = "%" . $filters['doc_nombre'] . "%";
        $params[] = "%" . $filters['doc_nombre'] . "%";
        $types .= "ss";
    }

    if (!empty($filters['num_proceso'])) {
        $numProcClean = trim((string)$filters['num_proceso']);
        $where .= " AND " . sqlLikeUtf8('dd.num_proceso');
        $params[] = "%" . $numProcClean . "%";
        $types .= "s";
    }

    if (!empty($filters['num_providencia'])) {
        $numProvClean = trim((string)$filters['num_providencia']);
        $where .= " AND " . sqlLikeUtf8('dd.num_providencia');
        $params[] = "%" . $numProvClean . "%";
        $types .= "s";
    }

    if (!empty($filters['doc_numero'])) {
        $docNumRaw = trim((string)$filters['doc_numero']);
        $variantesNum = [$docNumRaw];

        // Si el código tiene prefijos con "/" o "-" (ej. "SCINC/IF/1140/2026"), agregar también el sufijo corto (ej. "1140/2026")
        $segmentosSlash = array_values(array_filter(array_map('trim', explode('/', $docNumRaw)), function ($s) { return $s !== ''; }));
        if (count($segmentosSlash) >= 3) {
            $sufijoDos = $segmentosSlash[count($segmentosSlash) - 2] . '/' . $segmentosSlash[count($segmentosSlash) - 1];
            if (!in_array($sufijoDos, $variantesNum, true)) {
                $variantesNum[] = $sufijoDos;
            }
        }

        // Si el código incluye prefijos con ":", "N°", "Nº", "Nro" o espacios (ej. "ORD.IF : N°1348/2026"),
        // extraer también el código numérico/barra final (ej. "1348/2026")
        if (preg_match('/([0-9]+\/[0-9]{2,4})\b/u', $docNumRaw, $mShortSlash)) {
            $shortSlash = trim($mShortSlash[1]);
            if (!in_array($shortSlash, $variantesNum, true)) {
                $variantesNum[] = $shortSlash;
            }
        }

        $orNumParts = [];
        foreach ($variantesNum as $vNum) {
            $orNumParts[] = "(" . sqlLikeUtf8('dd.num_documento') . " OR " . sqlLikeUtf8('dd.num_providencia') . " OR " . sqlLikeUtf8('dd.num_proceso') . " OR " . sqlLikeUtf8('d.nombre_documento') . ")";
            $likeV = "%" . $vNum . "%";
            $params[] = $likeV;
            $params[] = $likeV;
            $params[] = $likeV;
            $params[] = $likeV;
            $types .= "ssss";
        }

        // Comparación adicional normalizada sin espacios, dos puntos ni símbolo de grado solo cuando el código consultado los contiene (ej. "ORD.IF : N°1348/2026")
        if (preg_match('/[\s:°º]/u', $docNumRaw)) {
            $docNumCompacto = preg_replace('/[\s:°º]+/u', '', $docNumRaw);
            if ($docNumCompacto !== '' && mb_strlen($docNumCompacto) >= 3) {
                $colNormSql = "REPLACE(REPLACE(REPLACE(REPLACE(CONVERT(dd.num_documento USING utf8) COLLATE utf8_general_ci, ' ', ''), ':', ''), '°', ''), 'º', '')";
                $orNumParts[] = "({$colNormSql} LIKE CONVERT(? USING utf8) COLLATE utf8_general_ci)";
                $params[] = "%" . $docNumCompacto . "%";
                $types .= "s";
            }
        }

        $where .= " AND (" . implode(" OR ", $orNumParts) . ")";
    }

    // Expresión de fecha efectiva del documento (o fecha_cierre cuando se consulta por documentos que pasaron a cerrado en un mes/año)
    $fechaDocumentoBaseSql = "COALESCE(NULLIF(d.fecha_documento, '0000-00-00'), NULLIF(d.fecha_recepcion, '0000-00-00'))";
    $fechaCierreBaseSql = "COALESCE(NULLIF((SELECT MAX(b.fecha_emision) FROM bitacora b WHERE b.id_documento = d.id_documento), '0000-00-00'), NULLIF(d.fecha_documento, '0000-00-00'), NULLIF(d.fecha_recepcion, '0000-00-00'))";
    $fechaEfectivaSql = !empty($filters['filtrar_por_fecha_cierre']) ? $fechaCierreBaseSql : $fechaDocumentoBaseSql;

    if (!empty($filters['anio']) && (int)$filters['anio'] >= 1990 && (int)$filters['anio'] <= 2100) {
        $where .= " AND YEAR($fechaEfectivaSql) = ?";
        $params[] = (int)$filters['anio'];
        $types .= "i";
    }

    if (!empty($filters['mes']) && (int)$filters['mes'] >= 1 && (int)$filters['mes'] <= 12) {
        $where .= " AND MONTH($fechaEfectivaSql) = ?";
        $params[] = (int)$filters['mes'];
        $types .= "i";
    }

    if (empty($filters['anio']) && !empty($filters['fecha_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$filters['fecha_desde']))) {
        $where .= " AND $fechaEfectivaSql >= ?";
        $params[] = trim((string)$filters['fecha_desde']);
        $types .= "s";
    }

    if (empty($filters['anio']) && !empty($filters['fecha_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string)$filters['fecha_hasta']))) {
        $where .= " AND $fechaEfectivaSql <= ?";
        $params[] = trim((string)$filters['fecha_hasta']);
        $types .= "s";
    }

    // 7. Búsqueda por palabras clave generales sobre todas las variables del documento
    $hasSpecificVarFilter = !empty($customVars['id_responsable']) || !empty($customVars['id_remitente']) || !empty($customVars['id_destinatario']) ||
                            !empty($responsableFiltro) || !empty($filters['remitente']) || !empty($filters['destinatario']) ||
                            !empty($filters['estado_doc']) || !empty($filters['flujo_doc']) ||
                            !empty($filters['tipo_documento']) || !empty($filters['subcontrato']) || !empty($filters['materia']) ||
                            !empty($filters['doc_nombre']) || !empty($filters['doc_numero']) ||
                            !empty($filters['num_proceso']) || !empty($filters['num_providencia']) ||
                            !empty($filters['anio']) || !empty($filters['mes']) ||
                            !empty($filters['fecha_desde']) || !empty($filters['fecha_hasta']) ||
                            !empty($filters['filtrar_por_fecha_cierre']) ||
                            !empty($filters['orden_antiguos']) || !empty($filters['orden_recientes']) ||
                            !empty($filters['es_consulta_cantidad']);

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
            $where .= " AND (" . implode(" OR ", $orConds) . ")";
        }
    }

    return [
        'from_joins' => $fromJoins,
        'where' => $where,
        'params' => $params,
        'types' => $types
    ];
}

/**
 * Cuenta y clasifica el registro histórico completo de documentos (cifras y cantidades)
 * a través de TODOS los atributos del documento: tipo de documento, materia, remitente,
 * destinatario, responsable, fechas (año, mes, fecha exacta), número/código de proceso,
 * estado, flujo y subcontrato.
 */
function contarDocumentosHistoricosPorVariables($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = []) {
    $base = construirBaseWhereDocumentos($userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);
    $fromWhere = $base['from_joins'] . $base['where'];
    $params = $base['params'];
    $types = $base['types'];

    $sql = "SELECT d.id_documento,
                   d.fecha_documento,
                   d.fecha_recepcion,
                   d.fecha_plazo,
                   CASE WHEN d.id_estado_doc = 2 THEN
                       COALESCE(
                           NULLIF((SELECT MAX(b.fecha_emision) FROM bitacora b WHERE b.id_documento = d.id_documento), '0000-00-00'),
                           NULLIF(d.fecha_documento, '0000-00-00'),
                           NULLIF(d.fecha_recepcion, '0000-00-00')
                       )
                   ELSE NULL END AS fecha_cierre,
                   d.id_estado_doc,
                   d.id_flujo,
                   dd.num_documento,
                   dd.num_providencia,
                   dd.num_proceso,
                   dd.materia,
                   td.tipo_documento,
                   u.nombre AS resp_nombre, u.apellido_p AS resp_apellido_p, u.apellido_m AS resp_apellido_m,
                   rem.nombre_entidad AS rem_nombre, rem.apellido_entidad AS rem_apellido, crem.cargo AS cargo_remitente,
                   dest.nombre_entidad AS dest_nombre, dest.apellido_entidad AS dest_apellido, cdest.cargo AS cargo_destinatario,
                   sc.nombre_subcontrato"
           . $fromWhere;

    $rawRows = ejecutarSelect($cnx, $sql, $params, $types);
    if (isset($rawRows['error'])) {
        return ['error' => $rawRows['error']];
    }

    $nombresMeses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    $seenIds = [];
    $totalHistorico = 0;
    $totalPendientes = 0;
    $totalCerrados = 0;
    $totalEntrada = 0;
    $totalSalida = 0;
    $primeraFecha = null;
    $ultimaFecha = null;

    $mapAnio = [];
    $mapMes = [];
    $mapFecha = [];
    $mapTipo = [];
    $mapResp = [];
    $mapRemitente = [];
    $mapDestinatario = [];
    $mapProceso = [];
    $mapMateria = [];
    $mapSub = [];

    // Si se filtró por un año específico (ej. 2026) y no por un único mes, pre-inicializamos los 12 meses de ese año
    $anioFiltro = (!empty($filters['anio']) && (int)$filters['anio'] >= 1990) ? (int)$filters['anio'] : null;
    if ($anioFiltro !== null && empty($filters['mes'])) {
        for ($m = 1; $m <= 12; $m++) {
            $ymKey = sprintf('%04d-%02d', $anioFiltro, $m);
            $mapMes[$ymKey] = [
                'anio' => $anioFiltro,
                'mes_numero' => $m,
                'mes_nombre' => $nombresMeses[$m],
                'periodo' => $nombresMeses[$m] . ' ' . $anioFiltro,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0,
                'por_tipo_documento' => []
            ];
        }
    }

    foreach ($rawRows as $r) {
        $idDoc = (int)$r['id_documento'];
        if (isset($seenIds[$idDoc])) {
            continue;
        }
        $seenIds[$idDoc] = true;
        $totalHistorico++;

        $estado = (int)$r['id_estado_doc'];
        $flujo = (int)$r['id_flujo'];
        $esPendiente = ($estado === 1);
        $esCerrado = ($estado === 2);
        $esEntrada = ($flujo === 1);
        $esSalida = ($flujo === 2);

        if ($esPendiente) $totalPendientes++;
        if ($esCerrado) $totalCerrados++;
        if ($esEntrada) $totalEntrada++;
        if ($esSalida) $totalSalida++;

        // Fecha efectiva del documento (o fecha_cierre cuando se filtra por cambio de estado a cerrado)
        $fechaDoc = (!empty($filters['filtrar_por_fecha_cierre']) && !empty($r['fecha_cierre']) && $r['fecha_cierre'] !== '0000-00-00')
            ? $r['fecha_cierre']
            : ((!empty($r['fecha_documento']) && $r['fecha_documento'] !== '0000-00-00')
                ? $r['fecha_documento']
                : ((!empty($r['fecha_recepcion']) && $r['fecha_recepcion'] !== '0000-00-00') ? $r['fecha_recepcion'] : null));

        $anioDoc = 'Sin fecha';
        $mesNum = 0;
        $ymKey = '0000-00';
        $fechaExactaKey = 'Sin fecha';
        if ($fechaDoc !== null && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fechaDoc, $mFecha)) {
            $anioInt = (int)$mFecha[1];
            $mesInt = (int)$mFecha[2];
            if ($anioInt >= 1990 && $mesInt >= 1 && $mesInt <= 12) {
                $anioDoc = $anioInt;
                $mesNum = $mesInt;
                $ymKey = sprintf('%04d-%02d', $anioInt, $mesInt);
                $fechaExactaKey = substr($fechaDoc, 0, 10);
                if ($primeraFecha === null || $fechaDoc < $primeraFecha) {
                    $primeraFecha = $fechaDoc;
                }
                if ($ultimaFecha === null || $fechaDoc > $ultimaFecha) {
                    $ultimaFecha = $fechaDoc;
                }
            }
        }

        $tipoNombre = !empty($r['tipo_documento']) ? trim($r['tipo_documento']) : 'Sin tipo asignado';
        $nombreResp = trim(preg_replace('/\s+/u', ' ', ($r['resp_nombre'] ?? '') . ' ' . ($r['resp_apellido_p'] ?? '') . ' ' . ($r['resp_apellido_m'] ?? '')));
        if ($nombreResp === '') {
            $nombreResp = 'Sin responsable asignado';
        }

        $nombreRem = trim(preg_replace('/\s+/u', ' ', ($r['rem_nombre'] ?? '') . ' ' . ($r['rem_apellido'] ?? '')));
        $cargoRem = trim((string)($r['cargo_remitente'] ?? ''));
        $etiquetaRem = $nombreRem !== '' ? ($cargoRem !== '' ? "{$nombreRem} ({$cargoRem})" : $nombreRem) : ($cargoRem !== '' ? $cargoRem : 'Sin remitente');

        $nombreDest = trim(preg_replace('/\s+/u', ' ', ($r['dest_nombre'] ?? '') . ' ' . ($r['dest_apellido'] ?? '')));
        $cargoDest = trim((string)($r['cargo_destinatario'] ?? ''));
        $etiquetaDest = $nombreDest !== '' ? ($cargoDest !== '' ? "{$nombreDest} ({$cargoDest})" : $nombreDest) : ($cargoDest !== '' ? $cargoDest : 'Sin destinatario');

        $numProcesoVal = !empty($r['num_proceso']) ? trim(preg_replace('/\s+/u', ' ', (string)$r['num_proceso'])) : 'Sin código de proceso';
        if ($numProcesoVal === '') {
            $numProcesoVal = 'Sin código de proceso';
        }

        $materiaVal = !empty($r['materia']) ? trim(preg_replace('/\s+/u', ' ', (string)$r['materia'])) : 'Sin materia';
        if ($materiaVal === '') {
            $materiaVal = 'Sin materia';
        }

        $nombreSub = !empty($r['nombre_subcontrato']) ? trim($r['nombre_subcontrato']) : 'Sin subcontrato';

        // 1. Clasificación por Año
        if (!isset($mapAnio[$anioDoc])) {
            $mapAnio[$anioDoc] = [
                'anio' => $anioDoc,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0,
                'por_tipo_documento' => []
            ];
        }
        $mapAnio[$anioDoc]['cantidad_total']++;
        if ($esPendiente) $mapAnio[$anioDoc]['pendientes']++;
        if ($esCerrado) $mapAnio[$anioDoc]['cerrados']++;
        if ($esEntrada) $mapAnio[$anioDoc]['entrada']++;
        if ($esSalida) $mapAnio[$anioDoc]['salida']++;
        $mapAnio[$anioDoc]['por_tipo_documento'][$tipoNombre] = ($mapAnio[$anioDoc]['por_tipo_documento'][$tipoNombre] ?? 0) + 1;

        // 2. Clasificación por Mes (Año-Mes)
        if (!isset($mapMes[$ymKey])) {
            $mesNombre = $nombresMeses[$mesNum] ?? 'Sin mes';
            $mapMes[$ymKey] = [
                'anio' => $anioDoc,
                'mes_numero' => $mesNum,
                'mes_nombre' => $mesNombre,
                'periodo' => ($mesNum >= 1 && $mesNum <= 12) ? ($mesNombre . ' ' . $anioDoc) : 'Sin fecha',
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0,
                'por_tipo_documento' => []
            ];
        }
        $mapMes[$ymKey]['cantidad_total']++;
        if ($esPendiente) $mapMes[$ymKey]['pendientes']++;
        if ($esCerrado) $mapMes[$ymKey]['cerrados']++;
        if ($esEntrada) $mapMes[$ymKey]['entrada']++;
        if ($esSalida) $mapMes[$ymKey]['salida']++;
        $mapMes[$ymKey]['por_tipo_documento'][$tipoNombre] = ($mapMes[$ymKey]['por_tipo_documento'][$tipoNombre] ?? 0) + 1;

        // 3. Clasificación por Fecha exacta
        if (!isset($mapFecha[$fechaExactaKey])) {
            $mapFecha[$fechaExactaKey] = [
                'fecha' => $fechaExactaKey,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0
            ];
        }
        $mapFecha[$fechaExactaKey]['cantidad_total']++;
        if ($esPendiente) $mapFecha[$fechaExactaKey]['pendientes']++;
        if ($esCerrado) $mapFecha[$fechaExactaKey]['cerrados']++;

        // 4. Clasificación por Tipo de Documento
        if (!isset($mapTipo[$tipoNombre])) {
            $mapTipo[$tipoNombre] = [
                'tipo_documento' => $tipoNombre,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0
            ];
        }
        $mapTipo[$tipoNombre]['cantidad_total']++;
        if ($esPendiente) $mapTipo[$tipoNombre]['pendientes']++;
        if ($esCerrado) $mapTipo[$tipoNombre]['cerrados']++;
        if ($esEntrada) $mapTipo[$tipoNombre]['entrada']++;
        if ($esSalida) $mapTipo[$tipoNombre]['salida']++;

        // 5. Clasificación por Responsable
        if (!isset($mapResp[$nombreResp])) {
            $mapResp[$nombreResp] = [
                'responsable' => $nombreResp,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0
            ];
        }
        $mapResp[$nombreResp]['cantidad_total']++;
        if ($esPendiente) $mapResp[$nombreResp]['pendientes']++;
        if ($esCerrado) $mapResp[$nombreResp]['cerrados']++;
        if ($esEntrada) $mapResp[$nombreResp]['entrada']++;
        if ($esSalida) $mapResp[$nombreResp]['salida']++;

        // 6. Clasificación por Remitente
        if (!isset($mapRemitente[$etiquetaRem])) {
            $mapRemitente[$etiquetaRem] = [
                'remitente' => $etiquetaRem,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0
            ];
        }
        $mapRemitente[$etiquetaRem]['cantidad_total']++;
        if ($esPendiente) $mapRemitente[$etiquetaRem]['pendientes']++;
        if ($esCerrado) $mapRemitente[$etiquetaRem]['cerrados']++;
        if ($esEntrada) $mapRemitente[$etiquetaRem]['entrada']++;
        if ($esSalida) $mapRemitente[$etiquetaRem]['salida']++;

        // 7. Clasificación por Destinatario
        if (!isset($mapDestinatario[$etiquetaDest])) {
            $mapDestinatario[$etiquetaDest] = [
                'destinatario' => $etiquetaDest,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0
            ];
        }
        $mapDestinatario[$etiquetaDest]['cantidad_total']++;
        if ($esPendiente) $mapDestinatario[$etiquetaDest]['pendientes']++;
        if ($esCerrado) $mapDestinatario[$etiquetaDest]['cerrados']++;
        if ($esEntrada) $mapDestinatario[$etiquetaDest]['entrada']++;
        if ($esSalida) $mapDestinatario[$etiquetaDest]['salida']++;

        // 8. Clasificación por Número / Código de Proceso
        if (!isset($mapProceso[$numProcesoVal])) {
            $mapProceso[$numProcesoVal] = [
                'num_proceso' => $numProcesoVal,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0,
                'entrada' => 0,
                'salida' => 0
            ];
        }
        $mapProceso[$numProcesoVal]['cantidad_total']++;
        if ($esPendiente) $mapProceso[$numProcesoVal]['pendientes']++;
        if ($esCerrado) $mapProceso[$numProcesoVal]['cerrados']++;
        if ($esEntrada) $mapProceso[$numProcesoVal]['entrada']++;
        if ($esSalida) $mapProceso[$numProcesoVal]['salida']++;

        // 9. Clasificación por Materia
        if (!isset($mapMateria[$materiaVal])) {
            $mapMateria[$materiaVal] = [
                'materia' => $materiaVal,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0
            ];
        }
        $mapMateria[$materiaVal]['cantidad_total']++;
        if ($esPendiente) $mapMateria[$materiaVal]['pendientes']++;
        if ($esCerrado) $mapMateria[$materiaVal]['cerrados']++;

        // 10. Clasificación por Subcontrato
        if (!isset($mapSub[$nombreSub])) {
            $mapSub[$nombreSub] = [
                'subcontrato' => $nombreSub,
                'cantidad_total' => 0,
                'pendientes' => 0,
                'cerrados' => 0
            ];
        }
        $mapSub[$nombreSub]['cantidad_total']++;
        if ($esPendiente) $mapSub[$nombreSub]['pendientes']++;
        if ($esCerrado) $mapSub[$nombreSub]['cerrados']++;
    }

    // Ordenar desglose por año (de más reciente a más antiguo)
    krsort($mapAnio);
    $porAnio = array_values($mapAnio);

    // Ordenar desglose por mes (cronológico ascendente dentro de un año, o del año más reciente por mes)
    if ($anioFiltro !== null) {
        ksort($mapMes);
    } else {
        krsort($mapMes);
    }
    $porMes = array_values($mapMes);

    // Ordenar por cantidad descendente en todas las dimensiones de atributos
    $porTipo = array_values($mapTipo);
    usort($porTipo, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });

    $porResponsable = array_values($mapResp);
    usort($porResponsable, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });

    $porRemitente = array_values($mapRemitente);
    usort($porRemitente, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });
    $porRemitente = array_slice($porRemitente, 0, 30);

    $porDestinatario = array_values($mapDestinatario);
    usort($porDestinatario, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });
    $porDestinatario = array_slice($porDestinatario, 0, 30);

    $porProceso = array_values($mapProceso);
    usort($porProceso, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });
    $porProceso = array_slice($porProceso, 0, 40);

    $porMateria = array_values($mapMateria);
    usort($porMateria, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });
    $porMateria = array_slice($porMateria, 0, 25);

    $porFecha = array_values($mapFecha);
    usort($porFecha, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });
    $porFecha = array_slice($porFecha, 0, 30);

    $porSubcontrato = array_values($mapSub);
    usort($porSubcontrato, function($a, $b) { return $b['cantidad_total'] <=> $a['cantidad_total']; });

    return [
        'total_historico_documentos' => $totalHistorico,
        'clasificacion_principal_solicitada' => $filters['clasificar_por'] ?? 'general',
        'filtros_aplicados_en_conteo' => array_filter([
            'tipo_documento' => $filters['tipo_documento'] ?? null,
            'materia' => $filters['materia'] ?? null,
            'remitente' => $filters['remitente'] ?? null,
            'destinatario' => $filters['destinatario'] ?? null,
            'responsable' => $filters['responsable'] ?? null,
            'num_proceso' => $filters['num_proceso'] ?? null,
            'num_providencia' => $filters['num_providencia'] ?? null,
            'doc_numero' => $filters['doc_numero'] ?? null,
            'anio' => $anioFiltro,
            'mes' => !empty($filters['mes']) ? ($nombresMeses[(int)$filters['mes']] ?? $filters['mes']) : null,
            'fecha_desde' => $filters['fecha_desde'] ?? null,
            'fecha_hasta' => $filters['fecha_hasta'] ?? null
        ]),
        'anio_filtrado' => $anioFiltro,
        'mes_filtrado' => !empty($filters['mes']) ? ($nombresMeses[(int)$filters['mes']] ?? $filters['mes']) : null,
        'desglose_por_estado' => [
            'Pendientes' => $totalPendientes,
            'Cerrados' => $totalCerrados
        ],
        'desglose_por_flujo' => [
            'Entrada' => $totalEntrada,
            'Salida' => $totalSalida
        ],
        'rango_historico_fechas' => [
            'primera_fecha_documento' => $primeraFecha,
            'ultima_fecha_documento' => $ultimaFecha
        ],
        'desglose_por_mes' => $porMes,
        'desglose_por_anio' => $porAnio,
        'desglose_por_fecha_documento' => $porFecha,
        'desglose_por_tipo_documento' => $porTipo,
        'desglose_por_responsable' => $porResponsable,
        'desglose_por_remitente' => $porRemitente,
        'desglose_por_destinatario' => $porDestinatario,
        'desglose_por_num_proceso' => $porProceso,
        'desglose_por_materia' => $porMateria,
        'desglose_por_subcontrato' => $porSubcontrato
    ];
}

/**
 * Verifica si una cadena representa una fecha válida YYYY-MM-DD entre 1990 y 2100.
 */
function esFechaValidaSgd($fecha) {
    if (empty($fecha) || !is_string($fecha)) {
        return false;
    }
    $fecha = trim($fecha);
    if ($fecha === '0000-00-00' || !preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fecha, $m)) {
        return false;
    }
    $y = (int)$m[1];
    $mo = (int)$m[2];
    $d = (int)$m[3];
    return ($y >= 1990 && $y <= 2100 && $mo >= 1 && $mo <= 12 && $d >= 1 && $d <= 31);
}

/**
 * Calcula la diferencia en días calendario entre dos fechas YYYY-MM-DD ($fechaFin - $fechaInicio).
 */
function diasEntreFechasSgd($fechaInicio, $fechaFin) {
    $ts1 = strtotime(substr(trim($fechaInicio), 0, 10));
    $ts2 = strtotime(substr(trim($fechaFin), 0, 10));
    if ($ts1 === false || $ts2 === false) {
        return null;
    }
    return (int)round(($ts2 - $ts1) / 86400);
}

/**
 * Inicializa un acumulador de métricas de tiempo entre fechas para un grupo (general, responsable, tipo, mes, año, flujo, subcontrato).
 */
function crearAcumuladorTiempos($etiqueta) {
    return [
        'grupo' => $etiqueta,
        'total_documentos' => 0,
        'cerrados' => 0,
        'pendientes' => 0,
        // De pendiente a cerrado (todos los cerrados con fecha válida)
        'suma_dias_cierre' => 0,
        'conteo_dias_cierre' => 0,
        'min_dias_cierre' => null,
        'max_dias_cierre' => null,
        // De pendiente a cerrado (solo aquellos con fecha explícita en bitácora/seguimiento o días > 0)
        'suma_dias_cierre_efectivo' => 0,
        'conteo_dias_cierre_efectivo' => 0,
        // Desde fecha_documento hasta fecha_cierre
        'suma_dias_doc_a_cierre' => 0,
        'conteo_dias_doc_a_cierre' => 0,
        // Desde fecha_recepcion hasta fecha_cierre
        'suma_dias_rec_a_cierre' => 0,
        'conteo_dias_rec_a_cierre' => 0,
        // Desde fecha_documento hasta fecha_recepcion
        'suma_dias_doc_a_rec' => 0,
        'conteo_dias_doc_a_rec' => 0,
        // Plazo otorgado (inicio a fecha_plazo)
        'suma_dias_plazo_otorgado' => 0,
        'conteo_dias_plazo_otorgado' => 0,
        'cerrados_en_plazo' => 0,
        'cerrados_fuera_de_plazo' => 0,
        // Documentos actualmente pendientes (tiempo de espera hasta hoy)
        'suma_dias_espera_pendientes' => 0,
        'conteo_dias_espera_pendientes' => 0,
        'max_dias_espera_pendiente' => null,
        'pendientes_en_plazo' => 0,
        'pendientes_vencidos' => 0
    ];
}

/**
 * Registra las métricas de un documento dentro de un acumulador de grupo.
 */
function registrarDocEnAcumuladorTiempos(&$acc, $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido) {
    $acc['total_documentos']++;
    if ($esCerrado) {
        $acc['cerrados']++;
    }
    if ($esPendiente) {
        $acc['pendientes']++;
    }
    if ($diasCierre !== null) {
        $acc['suma_dias_cierre'] += $diasCierre;
        $acc['conteo_dias_cierre']++;
        if ($acc['min_dias_cierre'] === null || $diasCierre < $acc['min_dias_cierre']) {
            $acc['min_dias_cierre'] = $diasCierre;
        }
        if ($acc['max_dias_cierre'] === null || $diasCierre > $acc['max_dias_cierre']) {
            $acc['max_dias_cierre'] = $diasCierre;
        }
        if ($tieneCierreEfectivo) {
            $acc['suma_dias_cierre_efectivo'] += $diasCierre;
            $acc['conteo_dias_cierre_efectivo']++;
        }
    }
    if ($diasDocACierre !== null) {
        $acc['suma_dias_doc_a_cierre'] += $diasDocACierre;
        $acc['conteo_dias_doc_a_cierre']++;
    }
    if ($diasRecACierre !== null) {
        $acc['suma_dias_rec_a_cierre'] += $diasRecACierre;
        $acc['conteo_dias_rec_a_cierre']++;
    }
    if ($diasDocARec !== null) {
        $acc['suma_dias_doc_a_rec'] += $diasDocARec;
        $acc['conteo_dias_doc_a_rec']++;
    }
    if ($diasPlazoOtorgado !== null) {
        $acc['suma_dias_plazo_otorgado'] += $diasPlazoOtorgado;
        $acc['conteo_dias_plazo_otorgado']++;
    }
    if ($cumplePlazoCerrado === true) {
        $acc['cerrados_en_plazo']++;
    } elseif ($cumplePlazoCerrado === false) {
        $acc['cerrados_fuera_de_plazo']++;
    }
    if ($diasEsperaPendiente !== null) {
        $acc['suma_dias_espera_pendientes'] += $diasEsperaPendiente;
        $acc['conteo_dias_espera_pendientes']++;
        if ($acc['max_dias_espera_pendiente'] === null || $diasEsperaPendiente > $acc['max_dias_espera_pendiente']) {
            $acc['max_dias_espera_pendiente'] = $diasEsperaPendiente;
        }
    }
    if ($pendienteVencido === true) {
        $acc['pendientes_vencidos']++;
    } elseif ($pendienteVencido === false) {
        $acc['pendientes_en_plazo']++;
    }
}

/**
 * Resume un acumulador calculando los promedios en días (redondeados a 1 decimal).
 */
function finalizarAcumuladorTiempos($acc) {
    $promCierreGeneral = $acc['conteo_dias_cierre'] > 0
        ? round($acc['suma_dias_cierre'] / $acc['conteo_dias_cierre'], 1)
        : null;
    $promCierreEfectivo = $acc['conteo_dias_cierre_efectivo'] > 0
        ? round($acc['suma_dias_cierre_efectivo'] / $acc['conteo_dias_cierre_efectivo'], 1)
        : $promCierreGeneral;
    $promDocACierre = $acc['conteo_dias_doc_a_cierre'] > 0
        ? round($acc['suma_dias_doc_a_cierre'] / $acc['conteo_dias_doc_a_cierre'], 1)
        : null;
    $promRecACierre = $acc['conteo_dias_rec_a_cierre'] > 0
        ? round($acc['suma_dias_rec_a_cierre'] / $acc['conteo_dias_rec_a_cierre'], 1)
        : null;
    $promDocARec = $acc['conteo_dias_doc_a_rec'] > 0
        ? round($acc['suma_dias_doc_a_rec'] / $acc['conteo_dias_doc_a_rec'], 1)
        : null;
    $promPlazo = $acc['conteo_dias_plazo_otorgado'] > 0
        ? round($acc['suma_dias_plazo_otorgado'] / $acc['conteo_dias_plazo_otorgado'], 1)
        : null;
    $promEsperaPend = $acc['conteo_dias_espera_pendientes'] > 0
        ? round($acc['suma_dias_espera_pendientes'] / $acc['conteo_dias_espera_pendientes'], 1)
        : null;

    $totalConPlazoCerrados = $acc['cerrados_en_plazo'] + $acc['cerrados_fuera_de_plazo'];
    $porcentajeEnPlazo = $totalConPlazoCerrados > 0
        ? round(($acc['cerrados_en_plazo'] / $totalConPlazoCerrados) * 100, 1)
        : null;

    return [
        'grupo' => $acc['grupo'],
        'total_documentos' => $acc['total_documentos'],
        'cerrados' => $acc['cerrados'],
        'pendientes' => $acc['pendientes'],
        'promedio_dias_pendiente_a_cerrado' => $promCierreEfectivo !== null ? $promCierreEfectivo : $promCierreGeneral,
        'promedio_dias_cierre_todos_los_cerrados' => $promCierreGeneral,
        'promedio_dias_cierre_con_seguimiento_o_bitacora' => $promCierreEfectivo,
        'documentos_cerrados_con_fechas_medibles' => $acc['conteo_dias_cierre'],
        'documentos_cerrados_con_bitacora_o_seguimiento' => $acc['conteo_dias_cierre_efectivo'],
        'minimo_dias_cierre' => $acc['min_dias_cierre'],
        'maximo_dias_cierre' => $acc['max_dias_cierre'],
        'promedio_dias_fecha_documento_a_cierre' => $promDocACierre,
        'promedio_dias_fecha_recepcion_a_cierre' => $promRecACierre,
        'promedio_dias_emision_a_recepcion' => $promDocARec,
        'promedio_dias_plazo_otorgado' => $promPlazo,
        'cerrados_en_plazo' => $acc['cerrados_en_plazo'],
        'cerrados_fuera_de_plazo' => $acc['cerrados_fuera_de_plazo'],
        'porcentaje_cumplimiento_plazo' => $porcentajeEnPlazo,
        'promedio_dias_espera_pendientes_actuales' => $promEsperaPend,
        'maximo_dias_espera_pendiente_actual' => $acc['max_dias_espera_pendiente'],
        'pendientes_en_plazo' => $acc['pendientes_en_plazo'],
        'pendientes_vencidos' => $acc['pendientes_vencidos']
    ];
}

/**
 * Mide los tiempos en días entre las fechas de los documentos del contrato:
 * - Tiempo en pasar de Pendiente a Cerrado (usando fecha de cierre de bitácora, seguimiento o recepción/documento)
 * - Tiempo desde Fecha de Documento hasta Fecha de Recepción
 * - Tiempo desde Fecha de Documento / Recepción hasta Fecha de Plazo y cumplimiento de plazos
 * - Tiempo de espera acumulado en documentos actualmente Pendientes
 * Clasifica todas las métricas de forma general y por responsable/usuario, tipo de documento, año, mes, flujo, subcontrato y remitente.
 */
function calcularMetricasTiemposDocumentos($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = []) {
    // Para medir tiempos entre pendiente y cerrado no restringimos estado_doc en el WHERE base
    $filtersParaTiempos = $filters;
    unset($filtersParaTiempos['estado_doc']);

    $base = construirBaseWhereDocumentos($userContractId, $userId, $filtersParaTiempos, $keywords, $customVars, $usuariosContrato);
    $fromWhere = $base['from_joins'] . $base['where'];

    $sql = "SELECT d.id_documento,
                   d.fecha_documento,
                   d.fecha_recepcion,
                   d.fecha_plazo,
                   d.id_estado_doc,
                   d.id_flujo,
                   td.tipo_documento,
                   u.nombre AS resp_nombre, u.apellido_p AS resp_apellido_p, u.apellido_m AS resp_apellido_m,
                   sc.nombre_subcontrato,
                   rem.nombre_entidad AS rem_nombre, rem.apellido_entidad AS rem_apellido"
           . $fromWhere;

    $rawRows = ejecutarSelect($cnx, $sql, $base['params'], $base['types']);
    if (isset($rawRows['error'])) {
        return ['error' => $rawRows['error']];
    }

    // Obtener fechas de cierre registradas en bitácora para el contrato
    $mapBitacora = [];
    $sqlBit = "SELECT b.id_documento,
                      MAX(NULLIF(b.fecha_emision, '0000-00-00')) AS max_fecha_bitacora
               FROM bitacora b
               INNER JOIN documento d ON b.id_documento = d.id_documento
               INNER JOIN detalle_documento dd ON d.id_documento = dd.id_documento
               LEFT JOIN subcontrato sc ON d.id_subcontrato = sc.id_subcontrato
               LEFT JOIN usuario u ON dd.id_responsable = u.id_usuario
               WHERE sc.id_contrato = ? OR u.id_contrato = ?
               GROUP BY b.id_documento";
    $rowsBit = ejecutarSelect($cnx, $sqlBit, [$userContractId, $userContractId], "ii");
    if (is_array($rowsBit) && !isset($rowsBit['error'])) {
        foreach ($rowsBit as $rb) {
            if (!empty($rb['max_fecha_bitacora']) && esFechaValidaSgd($rb['max_fecha_bitacora'])) {
                $mapBitacora[(int)$rb['id_documento']] = $rb['max_fecha_bitacora'];
            }
        }
    }

    // Obtener fechas de seguimiento (documentos de respuesta vinculados por numero_seguimiento)
    $mapSeguimiento = [];
    $sqlSeg = "SELECT s1.id_documento,
                      MAX(COALESCE(NULLIF(d2.fecha_documento, '0000-00-00'), NULLIF(d2.fecha_recepcion, '0000-00-00'))) AS max_fecha_seg
               FROM seguimiento s1
               INNER JOIN seguimiento s2 ON s1.numero_seguimiento = s2.numero_seguimiento AND s1.numero_seguimiento <> 0 AND s2.id_documento <> s1.id_documento
               INNER JOIN documento d2 ON s2.id_documento = d2.id_documento
               INNER JOIN documento d1 ON s1.id_documento = d1.id_documento
               LEFT JOIN subcontrato sc ON d1.id_subcontrato = sc.id_subcontrato
               WHERE sc.id_contrato = ?
               GROUP BY s1.id_documento";
    $rowsSeg = ejecutarSelect($cnx, $sqlSeg, [$userContractId], "i");
    if (is_array($rowsSeg) && !isset($rowsSeg['error'])) {
        foreach ($rowsSeg as $rsg) {
            if (!empty($rsg['max_fecha_seg']) && esFechaValidaSgd($rsg['max_fecha_seg'])) {
                $mapSeguimiento[(int)$rsg['id_documento']] = $rsg['max_fecha_seg'];
            }
        }
    }

    $nombresMeses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    $hoy = date('Y-m-d');
    $seenIds = [];

    $accGeneral = crearAcumuladorTiempos('Total General del Contrato');
    $accPorResp = [];
    $accPorTipo = [];
    $accPorAnio = [];
    $accPorMes = [];
    $accPorFlujo = [];
    $accPorSub = [];
    $accPorRem = [];

    foreach ($rawRows as $r) {
        $idDoc = (int)$r['id_documento'];
        if (isset($seenIds[$idDoc])) {
            continue;
        }
        $seenIds[$idDoc] = true;

        $estado = (int)$r['id_estado_doc'];
        $flujo = (int)$r['id_flujo'];
        $esPendiente = ($estado === 1);
        $esCerrado = ($estado === 2);

        $fDoc = esFechaValidaSgd($r['fecha_documento'] ?? '') ? substr(trim($r['fecha_documento']), 0, 10) : null;
        $fRec = esFechaValidaSgd($r['fecha_recepcion'] ?? '') ? substr(trim($r['fecha_recepcion']), 0, 10) : null;
        $fPlazo = esFechaValidaSgd($r['fecha_plazo'] ?? '') ? substr(trim($r['fecha_plazo']), 0, 10) : null;
        $fBit = $mapBitacora[$idDoc] ?? null;
        $fSeg = $mapSeguimiento[$idDoc] ?? null;

        $fInicio = $fDoc ?? $fRec;
        $fInicioRec = $fRec ?? $fDoc;

        // 1. Días entre Fecha de Documento y Fecha de Recepción
        $diasDocARec = null;
        if ($fDoc !== null && $fRec !== null) {
            $diffDR = diasEntreFechasSgd($fDoc, $fRec);
            if ($diffDR !== null && $diffDR >= 0 && $diffDR <= 3650) {
                $diasDocARec = $diffDR;
            }
        }

        // 2. Días de plazo otorgado
        $diasPlazoOtorgado = null;
        if ($fInicio !== null && $fPlazo !== null) {
            $diffPlazo = diasEntreFechasSgd($fInicio, $fPlazo);
            if ($diffPlazo !== null && $diffPlazo >= 0 && $diffPlazo <= 3650) {
                $diasPlazoOtorgado = $diffPlazo;
            }
        }

        // 3. Tiempo de paso de Pendiente a Cerrado (para documentos cerrados)
        $diasCierre = null;
        $tieneCierreEfectivo = false;
        $diasDocACierre = null;
        $diasRecACierre = null;
        $cumplePlazoCerrado = null;

        if ($esCerrado) {
            // Determinar fecha de cierre efectiva
            $fCierre = null;
            if ($fBit !== null) {
                $fCierre = $fBit;
                $tieneCierreEfectivo = true;
            } elseif ($fSeg !== null && ($fInicio === null || $fSeg >= $fInicio)) {
                $fCierre = $fSeg;
                $tieneCierreEfectivo = true;
            } elseif ($fRec !== null && $fDoc !== null && $fRec >= $fDoc) {
                $fCierre = $fRec;
                if ($fRec > $fDoc) {
                    $tieneCierreEfectivo = true;
                }
            } else {
                $fCierre = $fDoc ?? $fRec;
            }

            if ($fCierre !== null && $fInicio !== null) {
                $dCierre = diasEntreFechasSgd($fInicio, $fCierre);
                if ($dCierre !== null && $dCierre < 0 && $fInicioRec !== null) {
                    $dCierre = diasEntreFechasSgd($fInicioRec, $fCierre);
                }
                if ($dCierre !== null && $dCierre >= 0 && $dCierre <= 3650) {
                    $diasCierre = $dCierre;
                    if ($dCierre > 0) {
                        $tieneCierreEfectivo = true;
                    }
                }
            }

            if ($fDoc !== null && $fCierre !== null) {
                $dDC = diasEntreFechasSgd($fDoc, $fCierre);
                if ($dDC !== null && $dDC >= 0 && $dDC <= 3650) {
                    $diasDocACierre = $dDC;
                }
            }
            if ($fRec !== null && $fCierre !== null) {
                $dRC = diasEntreFechasSgd($fRec, $fCierre);
                if ($dRC !== null && $dRC >= 0 && $dRC <= 3650) {
                    $diasRecACierre = $dRC;
                }
            }

            if ($fCierre !== null && $fPlazo !== null) {
                $cumplePlazoCerrado = ($fCierre <= $fPlazo);
            }
        }

        // 4. Tiempo de espera acumulado (para documentos actualmente pendientes)
        $diasEsperaPendiente = null;
        $pendienteVencido = null;
        if ($esPendiente && $fInicio !== null) {
            $dEsp = diasEntreFechasSgd($fInicio, $hoy);
            if ($dEsp !== null && $dEsp >= 0 && $dEsp <= 7300) {
                $diasEsperaPendiente = $dEsp;
            }
            if ($fPlazo !== null) {
                $pendienteVencido = ($hoy > $fPlazo);
            }
        }

        // Etiquetas de agrupación
        $nombreResp = trim(preg_replace('/\s+/u', ' ', ($r['resp_nombre'] ?? '') . ' ' . ($r['resp_apellido_p'] ?? '') . ' ' . ($r['resp_apellido_m'] ?? '')));
        if ($nombreResp === '') {
            $nombreResp = 'Sin responsable asignado';
        }
        $tipoNombre = !empty($r['tipo_documento']) ? trim($r['tipo_documento']) : 'Sin tipo asignado';
        $flujoNombre = ($flujo === 1) ? 'Entrada' : (($flujo === 2) ? 'Salida' : 'Otro');
        $subNombre = !empty($r['nombre_subcontrato']) ? trim($r['nombre_subcontrato']) : 'Sin subcontrato';
        $remNombre = trim(preg_replace('/\s+/u', ' ', ($r['rem_nombre'] ?? '') . ' ' . ($r['rem_apellido'] ?? '')));
        if ($remNombre === '') {
            $remNombre = 'Sin remitente';
        }

        $anioKey = 'Sin fecha';
        $ymKey = '0000-00';
        $mesLabel = 'Sin fecha';
        if ($fInicio !== null && preg_match('/^(\d{4})-(\d{2})/', $fInicio, $mYM)) {
            $anioInt = (int)$mYM[1];
            $mesInt = (int)$mYM[2];
            $anioKey = (string)$anioInt;
            $ymKey = sprintf('%04d-%02d', $anioInt, $mesInt);
            $mesLabel = ($nombresMeses[$mesInt] ?? 'Mes ' . $mesInt) . ' ' . $anioInt;
        }

        // Registrar en acumulador general y por dimensiones
        registrarDocEnAcumuladorTiempos($accGeneral, $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorResp[$nombreResp])) $accPorResp[$nombreResp] = crearAcumuladorTiempos($nombreResp);
        registrarDocEnAcumuladorTiempos($accPorResp[$nombreResp], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorTipo[$tipoNombre])) $accPorTipo[$tipoNombre] = crearAcumuladorTiempos($tipoNombre);
        registrarDocEnAcumuladorTiempos($accPorTipo[$tipoNombre], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorFlujo[$flujoNombre])) $accPorFlujo[$flujoNombre] = crearAcumuladorTiempos($flujoNombre);
        registrarDocEnAcumuladorTiempos($accPorFlujo[$flujoNombre], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorSub[$subNombre])) $accPorSub[$subNombre] = crearAcumuladorTiempos($subNombre);
        registrarDocEnAcumuladorTiempos($accPorSub[$subNombre], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorAnio[$anioKey])) $accPorAnio[$anioKey] = crearAcumuladorTiempos($anioKey);
        registrarDocEnAcumuladorTiempos($accPorAnio[$anioKey], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorMes[$ymKey])) $accPorMes[$ymKey] = crearAcumuladorTiempos($mesLabel);
        registrarDocEnAcumuladorTiempos($accPorMes[$ymKey], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);

        if (!isset($accPorRem[$remNombre])) $accPorRem[$remNombre] = crearAcumuladorTiempos($remNombre);
        registrarDocEnAcumuladorTiempos($accPorRem[$remNombre], $esPendiente, $esCerrado, $diasCierre, $tieneCierreEfectivo, $diasDocACierre, $diasRecACierre, $diasDocARec, $diasPlazoOtorgado, $cumplePlazoCerrado, $diasEsperaPendiente, $pendienteVencido);
    }

    // Finalizar y ordenar desgloses
    $listaResp = array_map('finalizarAcumuladorTiempos', array_values($accPorResp));
    usort($listaResp, function ($a, $b) { return $b['total_documentos'] <=> $a['total_documentos']; });

    $listaTipo = array_map('finalizarAcumuladorTiempos', array_values($accPorTipo));
    usort($listaTipo, function ($a, $b) { return $b['total_documentos'] <=> $a['total_documentos']; });

    $listaFlujo = array_map('finalizarAcumuladorTiempos', array_values($accPorFlujo));
    $listaSub = array_map('finalizarAcumuladorTiempos', array_values($accPorSub));
    usort($listaSub, function ($a, $b) { return $b['total_documentos'] <=> $a['total_documentos']; });

    krsort($accPorAnio);
    $listaAnio = array_map('finalizarAcumuladorTiempos', array_values($accPorAnio));

    krsort($accPorMes);
    $listaMes = array_slice(array_map('finalizarAcumuladorTiempos', array_values($accPorMes)), 0, 24);

    $listaRem = array_map('finalizarAcumuladorTiempos', array_values($accPorRem));
    usort($listaRem, function ($a, $b) { return $b['total_documentos'] <=> $a['total_documentos']; });
    $listaRem = array_slice($listaRem, 0, 15);

    return [
        'resumen_general_tiempos' => finalizarAcumuladorTiempos($accGeneral),
        'clasificacion_principal_solicitada' => $filters['clasificar_por'] ?? 'general',
        'tiempos_por_responsable' => $listaResp,
        'tiempos_por_tipo_documento' => $listaTipo,
        'tiempos_por_flujo' => $listaFlujo,
        'tiempos_por_subcontrato' => $listaSub,
        'tiempos_por_anio' => $listaAnio,
        'tiempos_por_mes' => $listaMes,
        'tiempos_por_remitente' => $listaRem
    ];
}

/**
 * Función para acceder y buscar documentos del contrato del usuario logueado
 * filtrando por sus variables: responsable, remitente, destinatario, tipo, materia,
 * número, estado, flujo, subcontrato, fechas y palabras clave.
 */
function buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = []) {
    $base = construirBaseWhereDocumentos($userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);

    $sql = "SELECT d.id_documento, d.nombre_documento, d.fecha_documento, d.fecha_recepcion, d.fecha_plazo,
                   CASE WHEN d.id_estado_doc = 2 THEN COALESCE(NULLIF((SELECT MAX(b.fecha_emision) FROM bitacora b WHERE b.id_documento = d.id_documento), '0000-00-00'), NULLIF(d.fecha_documento, '0000-00-00'), NULLIF(d.fecha_recepcion, '0000-00-00')) ELSE NULL END AS fecha_cierre,
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
                   cu.nombre_contrato AS contrato_usr"
            . $base['from_joins']
            . $base['where'];

    $ordenAntiguos = !empty($filters['orden_antiguos']);
    $esExportExcel = !empty($filters['generar_excel']);
    $maxPermitido = $esExportExcel ? 2000 : 50;
    $defaultLimite = $esExportExcel ? 1000 : 20;
    $limiteSolicitado = (!empty($filters['limite']) && (int)$filters['limite'] >= 1) ? min((int)$filters['limite'], $maxPermitido) : $defaultLimite;
    $sqlLimit = $esExportExcel ? max(2500, $limiteSolicitado * 2) : max(80, $limiteSolicitado * 4);

    // Expresión de fecha de documento válida (priorizando siempre d.fecha_documento y usando d.fecha_recepcion solo si fecha_documento es inválida)
    $fechaDocValidaExpr = "CASE
        WHEN d.fecha_documento IS NOT NULL AND d.fecha_documento <> '0000-00-00' AND d.fecha_documento >= '1990-01-01' THEN d.fecha_documento
        WHEN d.fecha_recepcion IS NOT NULL AND d.fecha_recepcion <> '0000-00-00' AND d.fecha_recepcion >= '1990-01-01' THEN d.fecha_recepcion
        ELSE NULL
    END";
    $tieneFechaDocExpr = "CASE
        WHEN d.fecha_documento IS NOT NULL AND d.fecha_documento <> '0000-00-00' AND d.fecha_documento >= '1990-01-01' THEN 0
        WHEN d.fecha_recepcion IS NOT NULL AND d.fecha_recepcion <> '0000-00-00' AND d.fecha_recepcion >= '1990-01-01' THEN 1
        ELSE 2
    END";

    // Sin GROUP BY en SQL para evitar incompatibilidades con ONLY_FULL_GROUP_BY; deduplicamos por id_documento en PHP.
    if ($ordenAntiguos) {
        // Ordenar por fecha de documento más antigua primero (ASC)
        $sql .= " ORDER BY {$tieneFechaDocExpr} ASC,
                           {$fechaDocValidaExpr} ASC,
                           d.fecha_documento ASC,
                           d.fecha_recepcion ASC,
                           d.id_documento ASC
                  LIMIT {$sqlLimit}";
    } else {
        // Ordenar por fecha de documento más reciente primero (DESC)
        $sql .= " ORDER BY {$tieneFechaDocExpr} ASC,
                           {$fechaDocValidaExpr} DESC,
                           d.fecha_documento DESC,
                           d.fecha_recepcion DESC,
                           d.id_documento DESC
                  LIMIT {$sqlLimit}";
    }

    $rawRows = ejecutarSelect($cnx, $sql, $base['params'], $base['types']);
    if (isset($rawRows['error'])) {
        return $rawRows;
    }

    // Si se buscó por un número de documento específico (ej. "ORD.IF : N°1348/2026", "SCINC/IF/1140/2026" o "1140/2026")
    // y no hubo coincidencias por restricción de tipo_documento, materia, estado o año, reintentamos flexibilizando esos filtros
    if (empty($rawRows) && !empty($filters['doc_numero']) && (!empty($filters['tipo_documento']) || !empty($filters['materia']) || !empty($filters['anio']) || !empty($filters['mes']) || !empty($filters['estado_doc']))) {
        $filtersFlex = $filters;
        $filtersFlex['tipo_documento'] = null;
        $filtersFlex['materia'] = null;
        $filtersFlex['anio'] = null;
        $filtersFlex['mes'] = null;
        $filtersFlex['estado_doc'] = null;
        return buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filtersFlex, [], $customVars, $usuariosContrato);
    }

    $docs = [];
    $seenIds = [];
    foreach ($rawRows as $r) {
        $idDoc = (int)$r['id_documento'];
        if (!isset($seenIds[$idDoc])) {
            $seenIds[$idDoc] = true;
            $docs[] = formatearFilaDocumento($r);
        }
    }

    // Si se buscó por doc_numero, priorizar coincidencias exactas o normalizadas más específicas primero
    if (!empty($filters['doc_numero']) && count($docs) > 1) {
        $needleNorm = mb_strtolower(trim((string)$filters['doc_numero']), 'UTF-8');
        $needleCompact = preg_replace('/[\s:°º]+/u', '', $needleNorm);
        usort($docs, function ($a, $b) use ($needleNorm, $needleCompact) {
            $calcScore = function ($d) use ($needleNorm, $needleCompact) {
                $num = mb_strtolower(trim((string)($d['num_documento'] ?? '')), 'UTF-8');
                $numCompact = preg_replace('/[\s:°º]+/u', '', $num);
                if ($num === $needleNorm) return 5;
                if ($needleCompact !== '' && $numCompact === $needleCompact) return 4;
                if ($needleCompact !== '' && strpos($numCompact, $needleCompact) !== false) return 3;
                if (substr($num, -strlen($needleNorm)) === $needleNorm) return 2;
                if (strpos($num, $needleNorm) !== false) return 1;
                return 0;
            };
            return $calcScore($b) <=> $calcScore($a);
        });
    }

    return array_slice($docs, 0, $limiteSolicitado);
}

/**
 * Consulta los documentos relacionados (Seguimiento y tabla documento_relacionado) de un documento
 * específico del contrato (por ejemplo, "SCINC/IF/1140/2026" o "1140/2026"), determinando:
 * 1. Si tiene documentos relacionados.
 * 2. Cuántos documentos relacionados tiene (tanto registros totales en seguimiento como documentos únicos).
 * 3. Si tiene documentos relacionados duplicados (mismo id_documento insertado varias veces en seguimiento
 *    o distintos id_documento con el mismo num_documento) y cuáles son.
 */
function consultarDocumentosRelacionados($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = []) {
    $filtersBase = $filters;
    $filtersBase['limite'] = 10;
    // Al buscar un documento puntual por código/número, no filtrar por estado ni por año de fecha_documento
    if (!empty($filtersBase['doc_numero'])) {
        $filtersBase['estado_doc'] = null;
        $filtersBase['anio'] = null;
        $filtersBase['mes'] = null;
    }

    $docsEncontrados = buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filtersBase, $keywords, $customVars, $usuariosContrato);
    if (isset($docsEncontrados['error'])) {
        return ['error' => $docsEncontrados['error']];
    }

    if (empty($docsEncontrados)) {
        return [
            'documento_encontrado' => false,
            'criterio_buscado' => $filters['doc_numero'] ?? ($filters['doc_nombre'] ?? ($filters['materia'] ?? '')),
            'analisis_por_documento' => []
        ];
    }

    $selectCamposDoc = "d.id_documento, d.nombre_documento, d.fecha_documento, d.fecha_recepcion, d.fecha_plazo,
                   CASE WHEN d.id_estado_doc = 2 THEN COALESCE(NULLIF((SELECT MAX(b.fecha_emision) FROM bitacora b WHERE b.id_documento = d.id_documento), '0000-00-00'), NULLIF(d.fecha_documento, '0000-00-00'), NULLIF(d.fecha_recepcion, '0000-00-00')) ELSE NULL END AS fecha_cierre,
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
                   cu.nombre_contrato AS contrato_usr";

    $joinsDoc = "INNER JOIN documento d ON s.id_documento = d.id_documento
                 INNER JOIN detalle_documento dd ON d.id_documento = dd.id_documento
                 LEFT JOIN subcontrato sc ON d.id_subcontrato = sc.id_subcontrato
                 LEFT JOIN contrato c ON sc.id_contrato = c.id_contrato
                 LEFT JOIN usuario u ON dd.id_responsable = u.id_usuario
                 LEFT JOIN contrato cu ON u.id_contrato = cu.id_contrato
                 LEFT JOIN tipo_documento td ON dd.id_tipo_doc = td.id_tipo_doc
                 LEFT JOIN entidad rem ON dd.id_remitente = rem.id_entidad
                 LEFT JOIN cargo crem ON rem.id_cargo = crem.id_cargo
                 LEFT JOIN entidad dest ON dd.id_destinatario = dest.id_entidad
                 LEFT JOIN cargo cdest ON dest.id_cargo = cdest.id_cargo";

    $analisisResultados = [];

    foreach (array_slice($docsEncontrados, 0, 5) as $docBase) {
        $idDocBase = (int)$docBase['id_documento'];
        $numDocBaseNorm = mb_strtolower(preg_replace('/\s+/u', '', trim((string)($docBase['num_documento'] ?? ''))), 'UTF-8');

        // 1. Obtener los números de seguimiento asociados al documento base en la tabla `seguimiento`
        $rowsNumSeg = ejecutarSelect(
            $cnx,
            "SELECT id_seguimiento, id_documento, numero_seguimiento
             FROM seguimiento
             WHERE id_documento = ? AND numero_seguimiento <> 0
             ORDER BY id_seguimiento ASC",
            [$idDocBase],
            "i"
        );

        $numerosSeguimiento = [];
        if (is_array($rowsNumSeg) && !isset($rowsNumSeg['error'])) {
            foreach ($rowsNumSeg as $rns) {
                $numSeg = (int)$rns['numero_seguimiento'];
                if ($numSeg > 0 && !in_array($numSeg, $numerosSeguimiento, true)) {
                    $numerosSeguimiento[] = $numSeg;
                }
            }
        }

        // 2. Obtener todas las filas de `seguimiento` que comparten esos `numero_seguimiento`
        $filasSeguimiento = [];
        $seenIdSeguimiento = [];
        if (!empty($numerosSeguimiento)) {
            $phSeg = implode(',', array_fill(0, count($numerosSeguimiento), '?'));
            $typesSeg = str_repeat('i', count($numerosSeguimiento));
            $sqlSeg = "SELECT s.id_seguimiento, s.numero_seguimiento, {$selectCamposDoc}
                       FROM seguimiento s
                       {$joinsDoc}
                       WHERE s.numero_seguimiento IN ({$phSeg}) AND s.numero_seguimiento <> 0
                       ORDER BY d.fecha_recepcion ASC, s.id_seguimiento ASC";
            $resSeg = ejecutarSelect($cnx, $sqlSeg, $numerosSeguimiento, $typesSeg);
            if (is_array($resSeg) && !isset($resSeg['error'])) {
                foreach ($resSeg as $rs) {
                    $idSeg = (int)$rs['id_seguimiento'];
                    // Deduplicar por id_seguimiento por si detalle_documento tiene más de una fila de corresponsable
                    if (!isset($seenIdSeguimiento[$idSeg])) {
                        $seenIdSeguimiento[$idSeg] = true;
                        $docFmt = formatearFilaDocumento($rs);
                        $docFmt['id_seguimiento'] = $idSeg;
                        $docFmt['numero_seguimiento'] = (int)$rs['numero_seguimiento'];
                        $docFmt['origen_relacion'] = 'seguimiento';
                        $filasSeguimiento[] = $docFmt;
                    }
                }
            }
        }

        // 3. Consultar también la tabla `documento_relacionado` por si existen relaciones directas registradas ahí
        $sqlDocRel = "SELECT dr.id_documento AS id_padre_rel, dr.id_documento_rel AS id_hijo_rel,
                             CASE WHEN dr.id_documento = ? THEN dr.id_documento_rel ELSE dr.id_documento END AS id_doc_vinculado
                      FROM documento_relacionado dr
                      WHERE dr.id_documento = ? OR dr.id_documento_rel = ?";
        $resDocRelIds = ejecutarSelect($cnx, $sqlDocRel, [$idDocBase, $idDocBase, $idDocBase], "iii");
        if (is_array($resDocRelIds) && !isset($resDocRelIds['error']) && !empty($resDocRelIds)) {
            foreach ($resDocRelIds as $drRow) {
                $idVinc = (int)$drRow['id_doc_vinculado'];
                if ($idVinc <= 0) {
                    continue;
                }
                $sqlDetalleVinc = "SELECT 0 AS id_seguimiento, 0 AS numero_seguimiento, {$selectCamposDoc}
                                   FROM (SELECT ? AS id_documento) s
                                   {$joinsDoc}
                                   LIMIT 1";
                $resVinc = ejecutarSelect($cnx, $sqlDetalleVinc, [$idVinc], "i");
                if (is_array($resVinc) && !isset($resVinc['error']) && !empty($resVinc)) {
                    $docFmt = formatearFilaDocumento($resVinc[0]);
                    $docFmt['id_seguimiento'] = null;
                    $docFmt['numero_seguimiento'] = null;
                    $docFmt['origen_relacion'] = 'documento_relacionado';
                    $filasSeguimiento[] = $docFmt;
                }
            }
        }

        // 4. Separar las filas del propio documento consultado vs los otros documentos relacionados
        $aparicionesPropioDoc = [];
        $filasOtrosDocumentos = [];
        foreach ($filasSeguimiento as $fila) {
            if ((int)$fila['id_documento'] === $idDocBase) {
                $aparicionesPropioDoc[] = $fila;
            } else {
                $filasOtrosDocumentos[] = $fila;
            }
        }

        // Agrupar otros documentos relacionados por id_documento y por num_documento normalizado para detectar duplicados
        $conteoPorIdDoc = [];
        $conteoPorNumDoc = [];
        foreach ($filasOtrosDocumentos as $f) {
            $idD = (int)$f['id_documento'];
            $numDNorm = mb_strtolower(preg_replace('/\s+/u', '', trim((string)($f['num_documento'] ?? ''))), 'UTF-8');
            $conteoPorIdDoc[$idD][] = $f;
            if ($numDNorm !== '' && $numDNorm !== 'noregistra') {
                $conteoPorNumDoc[$numDNorm][] = $f;
            }
        }

        $duplicadosDetectados = [];
        $idsYaReportadosEnDuplicados = [];

        // Caso A: Mismo id_documento relacionado 2 o más veces en seguimiento
        foreach ($conteoPorIdDoc as $idD => $ocurrencias) {
            if (count($ocurrencias) > 1) {
                $primero = $ocurrencias[0];
                $idsSeg = array_values(array_filter(array_map(function ($x) { return $x['id_seguimiento']; }, $ocurrencias)));
                $duplicadosDetectados[] = [
                    'tipo_duplicado' => 'Mismo documento vinculado múltiples veces en el seguimiento',
                    'id_documento' => $idD,
                    'ids_documento_involucrados' => [$idD],
                    'ids_seguimiento' => $idsSeg,
                    'tipo_documento' => $primero['tipo_documento'],
                    'num_documento' => $primero['num_documento'],
                    'num_providencia' => $primero['num_providencia'],
                    'materia' => $primero['materia'],
                    'fecha_documento' => $primero['fecha_documento'],
                    'fecha_recepcion' => $primero['fecha_recepcion'],
                    'estado_documento' => $primero['estado_documento'],
                    'responsable' => $primero['responsable'],
                    'veces_repetido' => count($ocurrencias)
                ];
                $idsYaReportadosEnDuplicados[$idD] = true;
            }
        }

        // Caso B: Distintos id_documento pero con el mismo num_documento
        foreach ($conteoPorNumDoc as $numNorm => $ocurrencias) {
            $idsDistintos = array_values(array_unique(array_map(function ($x) { return (int)$x['id_documento']; }, $ocurrencias)));
            if (count($idsDistintos) > 1) {
                $primero = $ocurrencias[0];
                $idsSeg = array_values(array_filter(array_map(function ($x) { return $x['id_seguimiento']; }, $ocurrencias)));
                $duplicadosDetectados[] = [
                    'tipo_duplicado' => 'Múltiples registros de documento con el mismo N° de Documento en el seguimiento',
                    'id_documento' => (int)$primero['id_documento'],
                    'ids_documento_involucrados' => $idsDistintos,
                    'ids_seguimiento' => $idsSeg,
                    'tipo_documento' => $primero['tipo_documento'],
                    'num_documento' => $primero['num_documento'],
                    'num_providencia' => $primero['num_providencia'],
                    'materia' => $primero['materia'],
                    'fecha_documento' => $primero['fecha_documento'],
                    'fecha_recepcion' => $primero['fecha_recepcion'],
                    'estado_documento' => $primero['estado_documento'],
                    'responsable' => $primero['responsable'],
                    'veces_repetido' => count($ocurrencias)
                ];
                foreach ($idsDistintos as $idD) {
                    $idsYaReportadosEnDuplicados[$idD] = true;
                }
            }
        }

        // Caso C: El propio documento consultado está insertado 2 o más veces en su tabla de seguimiento
        if (count($aparicionesPropioDoc) > 1) {
            $idsSegPropio = array_values(array_filter(array_map(function ($x) { return $x['id_seguimiento']; }, $aparicionesPropioDoc)));
            $duplicadosDetectados[] = [
                'tipo_duplicado' => 'El propio documento consultado aparece repetido múltiples veces en su tabla de seguimiento',
                'id_documento' => $idDocBase,
                'ids_documento_involucrados' => [$idDocBase],
                'ids_seguimiento' => $idsSegPropio,
                'tipo_documento' => $docBase['tipo_documento'],
                'num_documento' => $docBase['num_documento'],
                'num_providencia' => $docBase['num_providencia'],
                'materia' => $docBase['materia'],
                'fecha_documento' => $docBase['fecha_documento'],
                'fecha_recepcion' => $docBase['fecha_recepcion'],
                'estado_documento' => $docBase['estado_documento'],
                'responsable' => $docBase['responsable'],
                'veces_repetido' => count($aparicionesPropioDoc)
            ];
        }

        // Construir lista única de otros documentos relacionados indicando cuántas veces aparece cada uno
        $documentosRelacionadosUnicos = [];
        foreach ($conteoPorIdDoc as $idD => $ocurrencias) {
            $docItem = $ocurrencias[0];
            $docItem['veces_en_seguimiento'] = count($ocurrencias);
            $docItem['ids_seguimiento'] = array_values(array_filter(array_map(function ($x) { return $x['id_seguimiento']; }, $ocurrencias)));
            $docItem['es_duplicado'] = isset($idsYaReportadosEnDuplicados[$idD]);
            $documentosRelacionadosUnicos[] = $docItem;
        }

        $totalFilasOtros = count($filasOtrosDocumentos);
        $totalUnicosOtros = count($documentosRelacionadosUnicos);
        $totalFilasTablaSeguimiento = count($filasSeguimiento);

        $analisisResultados[] = [
            'documento_consultado' => $docBase,
            'numeros_seguimiento' => $numerosSeguimiento,
            'tiene_documentos_relacionados' => ($totalFilasOtros > 0),
            'cantidad_documentos_relacionados_unicos' => $totalUnicosOtros,
            'cantidad_registros_documentos_relacionados' => $totalFilasOtros,
            'total_filas_tabla_seguimiento_incluyendo_documento_base' => $totalFilasTablaSeguimiento,
            'apariciones_documento_base_en_seguimiento' => count($aparicionesPropioDoc),
            'tiene_documentos_relacionados_duplicados' => !empty($duplicadosDetectados),
            'cantidad_grupos_duplicados' => count($duplicadosDetectados),
            'duplicados_detectados' => $duplicadosDetectados,
            'documentos_relacionados' => $documentosRelacionadosUnicos
        ];
    }

    return [
        'documento_encontrado' => true,
        'total_documentos_base_coincidentes' => count($analisisResultados),
        'analisis_por_documento' => $analisisResultados
    ];
}

/**
 * Evalúa los documentos del contrato que cumplen con los filtros (ej. mes, año, responsable, proceso, o todo el contrato)
 * diferenciando estrictamente dos modos independientes para que NUNCA se mezclen sus resultados:
 *
 * - Modo `'contrato'` (por defecto):
 *   Evalúa ÚNICAMENTE los documentos asociados al contrato en las tablas `documento` y `detalle_documento` que están
 *   repetidos por tener el mismo N° de Documento (`num_documento`) o el mismo N° de Providencia (`num_providencia`)
 *   en 2 o más registros distintos (`id_documento`). NO incluye repeticiones de la tabla `seguimiento`.
 *
 * - Modo `'seguimiento'`:
 *   Evalúa ÚNICAMENTE la tabla `seguimiento` de los documentos del contrato y retorna solo aquellos documentos que
 *   tienen documentos de seguimiento / documentos relacionados repetidos dentro de un mismo hilo `numero_seguimiento`.
 *   NO incluye documentos repetidos del contrato que no tengan duplicidad en `seguimiento`.
 */
function consultarDocumentosRepetidos($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = [], $modoRepeticion = 'contrato') {
    $modoRepeticion = ($modoRepeticion === 'seguimiento') ? 'seguimiento' : 'contrato';
    $base = construirBaseWhereDocumentos($userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);

    $sql = "SELECT d.id_documento, d.nombre_documento, d.fecha_documento, d.fecha_recepcion, d.fecha_plazo,
                   CASE WHEN d.id_estado_doc = 2 THEN COALESCE(NULLIF((SELECT MAX(b.fecha_emision) FROM bitacora b WHERE b.id_documento = d.id_documento), '0000-00-00'), NULLIF(d.fecha_documento, '0000-00-00'), NULLIF(d.fecha_recepcion, '0000-00-00')) ELSE NULL END AS fecha_cierre,
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
                   cu.nombre_contrato AS contrato_usr"
            . $base['from_joins']
            . $base['where']
            . " ORDER BY d.fecha_documento DESC, d.id_documento DESC";

    $rawRows = ejecutarSelect($cnx, $sql, $base['params'], $base['types']);
    if (isset($rawRows['error'])) {
        return ['error' => $rawRows['error']];
    }

    $docsPeriodo = [];
    $seenIds = [];
    foreach ($rawRows as $r) {
        $idDoc = (int)$r['id_documento'];
        if (!isset($seenIds[$idDoc])) {
            $seenIds[$idDoc] = true;
            $docsPeriodo[$idDoc] = formatearFilaDocumento($r);
        }
    }

    $descripcionModo = ($modoRepeticion === 'seguimiento')
        ? 'Documentos del contrato que presentan documentos de seguimiento (documentos relacionados) repetidos'
        : 'Documentos asociados al contrato repetidos por N° de Documento o N° de Providencia';

    if (empty($docsPeriodo)) {
        return [
            'modo_evaluacion' => $modoRepeticion,
            'descripcion_evaluacion' => $descripcionModo,
            'total_documentos_evaluados_en_periodo' => 0,
            'total_documentos_repetidos_encontrados' => 0,
            'documentos_repetidos' => []
        ];
    }

    // Valores genéricos que NO deben considerarse como un número de documento/providencia válido para duplicidad
    $valoresIgnorados = ['', '0', '-', '--', 's/n', 'sn', 's/i', 'si', 'noregistra', 'sinnumero', 'na', 'n/a', 'null'];

    $normalizarCodigo = function ($val) use ($valoresIgnorados) {
        $norm = mb_strtolower(preg_replace('/\s+/u', '', trim((string)$val)), 'UTF-8');
        return in_array($norm, $valoresIgnorados, true) ? '' : $norm;
    };

    $porNumDoc = [];
    $porNumProv = [];
    $duplicadosSeguimientoPorDoc = [];
    $numSeguimientoPorDoc = [];

    if ($modoRepeticion === 'contrato') {
        // MODO A ('contrato'): Evaluar EXCLUSIVAMENTE documentos del contrato con num_documento o num_providencia repetido
        foreach ($docsPeriodo as $idDoc => $doc) {
            $numNorm = $normalizarCodigo($doc['num_documento'] ?? '');
            if ($numNorm !== '') {
                $porNumDoc[$numNorm][$idDoc] = $doc['num_documento'];
            }

            $provNorm = $normalizarCodigo($doc['num_providencia'] ?? '');
            if ($provNorm !== '') {
                $porNumProv[$provNorm][$idDoc] = $doc['num_providencia'];
            }
        }

        // Verificar también contra todo el contrato si algún documento del periodo tiene su mismo num_documento en otro registro del contrato
        $baseContrato = construirBaseWhereDocumentos($userContractId, $userId, [], [], [], $usuariosContrato);
        $sqlGlobalNum = "SELECT d.id_documento, dd.num_documento"
                        . $baseContrato['from_joins']
                        . $baseContrato['where'];
        $rowsGlobal = ejecutarSelect($cnx, $sqlGlobalNum, $baseContrato['params'], $baseContrato['types']);
        if (is_array($rowsGlobal) && !isset($rowsGlobal['error'])) {
            foreach ($rowsGlobal as $rg) {
                $idG = (int)$rg['id_documento'];
                $numGNorm = $normalizarCodigo($rg['num_documento'] ?? '');
                if ($numGNorm !== '' && isset($porNumDoc[$numGNorm])) {
                    $porNumDoc[$numGNorm][$idG] = $rg['num_documento'];
                }
            }
        }
    } else {
        // MODO B ('seguimiento'): Evaluar EXCLUSIVAMENTE repeticiones dentro de la tabla `seguimiento`
        $idsPeriodo = array_keys($docsPeriodo);
        $chunksIds = array_chunk($idsPeriodo, 300);

        foreach ($chunksIds as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $typesChunk = str_repeat('i', count($chunk));
            $sqlSegBase = "SELECT id_seguimiento, id_documento, numero_seguimiento
                           FROM seguimiento
                           WHERE id_documento IN ({$ph}) AND numero_seguimiento <> 0";
            $rowsSegBase = ejecutarSelect($cnx, $sqlSegBase, $chunk, $typesChunk);
            if (!is_array($rowsSegBase) || isset($rowsSegBase['error']) || empty($rowsSegBase)) {
                continue;
            }

            $numSegPorDocChunk = [];
            $todosNumSeg = [];
            foreach ($rowsSegBase as $rsb) {
                $idD = (int)$rsb['id_documento'];
                $nSeg = (int)$rsb['numero_seguimiento'];
                if ($nSeg > 0) {
                    $numSegPorDocChunk[$idD][$nSeg] = true;
                    $numSeguimientoPorDoc[$idD][$nSeg] = $nSeg;
                    $todosNumSeg[$nSeg] = true;
                }
            }

            if (empty($todosNumSeg)) {
                continue;
            }

            $listaNumSeg = array_keys($todosNumSeg);
            $phNumSeg = implode(',', array_fill(0, count($listaNumSeg), '?'));
            $typesNumSeg = str_repeat('i', count($listaNumSeg));

            $sqlAllSeg = "SELECT s.id_seguimiento, s.id_documento, s.numero_seguimiento,
                                 dd.num_documento, td.tipo_documento
                          FROM seguimiento s
                          INNER JOIN documento d ON s.id_documento = d.id_documento
                          INNER JOIN detalle_documento dd ON d.id_documento = dd.id_documento
                          LEFT JOIN tipo_documento td ON dd.id_tipo_doc = td.id_tipo_doc
                          WHERE s.numero_seguimiento IN ({$phNumSeg}) AND s.numero_seguimiento <> 0";
            $rowsAllSeg = ejecutarSelect($cnx, $sqlAllSeg, $listaNumSeg, $typesNumSeg);
            if (!is_array($rowsAllSeg) || isset($rowsAllSeg['error'])) {
                continue;
            }

            // Deduplicar por id_seguimiento y agrupar por numero_seguimiento -> id_documento y por numero_seguimiento -> num_documento normalizado
            $seenIdSeg = [];
            $segAgrupadoPorIdDoc = [];
            $segAgrupadoPorNumDoc = [];
            $infoDocSeg = [];
            foreach ($rowsAllSeg as $ras) {
                $idS = (int)$ras['id_seguimiento'];
                if (isset($seenIdSeg[$idS])) continue;
                $seenIdSeg[$idS] = true;
                $nSeg = (int)$ras['numero_seguimiento'];
                $idDocS = (int)$ras['id_documento'];
                $numDocSNorm = $normalizarCodigo($ras['num_documento'] ?? '');

                $segAgrupadoPorIdDoc[$nSeg][$idDocS][] = $idS;
                if ($numDocSNorm !== '') {
                    $segAgrupadoPorNumDoc[$nSeg][$numDocSNorm][$idDocS] = trim(($ras['tipo_documento'] ?? 'Doc.') . ' ' . ($ras['num_documento'] ?? ('ID ' . $idDocS)));
                }
                $infoDocSeg[$idDocS] = trim(($ras['tipo_documento'] ?? 'Doc.') . ' ' . ($ras['num_documento'] ?? ('ID ' . $idDocS)));
            }

            // Para cada documento del contrato, revisar si en alguno de sus numero_seguimiento hay documentos de seguimiento repetidos
            foreach ($numSegPorDocChunk as $idDocP => $mapNSeg) {
                foreach (array_keys($mapNSeg) as $nSeg) {
                    // Caso 1: Mismo id_documento insertado 2 o más veces en el mismo numero_seguimiento
                    if (!empty($segAgrupadoPorIdDoc[$nSeg])) {
                        foreach ($segAgrupadoPorIdDoc[$nSeg] as $idDocEnSeg => $listaIdSeguimiento) {
                            $cantVeces = count($listaIdSeguimiento);
                            if ($cantVeces > 1) {
                                $etiquetaDocRep = $infoDocSeg[$idDocEnSeg] ?? ('ID ' . $idDocEnSeg);
                                if ($idDocEnSeg === $idDocP) {
                                    $duplicadosSeguimientoPorDoc[$idDocP][] = [
                                        'veces' => $cantVeces,
                                        'id_doc_repetido' => $idDocEnSeg,
                                        'motivo' => "El propio documento ({$etiquetaDocRep}) aparece repetido {$cantVeces} veces en el Seguimiento N° {$nSeg}",
                                        'doc_duplicado' => $etiquetaDocRep
                                    ];
                                } else {
                                    $duplicadosSeguimientoPorDoc[$idDocP][] = [
                                        'veces' => $cantVeces,
                                        'id_doc_repetido' => $idDocEnSeg,
                                        'motivo' => "Documento de seguimiento repetido {$cantVeces} veces en Seguimiento N° {$nSeg}: {$etiquetaDocRep} (ID: {$idDocEnSeg})",
                                        'doc_duplicado' => $etiquetaDocRep
                                    ];
                                }
                            }
                        }
                    }

                    // Caso 2: Distintos id_documento con el mismo num_documento dentro del mismo numero_seguimiento
                    if (!empty($segAgrupadoPorNumDoc[$nSeg])) {
                        foreach ($segAgrupadoPorNumDoc[$nSeg] as $numDocNormSeg => $mapIdsConMismoNum) {
                            if (count($mapIdsConMismoNum) > 1) {
                                $idsDupSeg = array_keys($mapIdsConMismoNum);
                                $etiquetaEjemplo = reset($mapIdsConMismoNum);
                                $vecesNumSeg = count($idsDupSeg);
                                $duplicadosSeguimientoPorDoc[$idDocP][] = [
                                    'veces' => $vecesNumSeg,
                                    'id_doc_repetido' => (int)$idsDupSeg[0],
                                    'motivo' => "Documento de seguimiento con mismo N° ({$etiquetaEjemplo}) duplicado {$vecesNumSeg} veces en Seguimiento N° {$nSeg} (IDs: " . implode(', ', $idsDupSeg) . ")",
                                    'doc_duplicado' => $etiquetaEjemplo
                                ];
                            }
                        }
                    }
                }
            }
        }
    }

    // Construir la lista final filtrada ÚNICAMENTE según el modo solicitado ('contrato' vs 'seguimiento')
    $documentosRepetidos = [];
    foreach ($docsPeriodo as $idDoc => $doc) {
        $motivos = [];
        $maxVeces = 1;
        $idsAsociados = [$idDoc];
        $docsRelDup = [];

        if ($modoRepeticion === 'contrato') {
            $numNorm = $normalizarCodigo($doc['num_documento'] ?? '');
            if ($numNorm !== '' && isset($porNumDoc[$numNorm]) && count($porNumDoc[$numNorm]) > 1) {
                $idsGrupo = array_keys($porNumDoc[$numNorm]);
                $veces = count($idsGrupo);
                $maxVeces = max($maxVeces, $veces);
                $idsAsociados = array_values(array_unique(array_merge($idsAsociados, $idsGrupo)));
                $motivos[] = "N° de Documento ({$doc['num_documento']}) repetido {$veces} veces en el contrato (IDs: " . implode(', ', $idsGrupo) . ")";
            }

            $provNorm = $normalizarCodigo($doc['num_providencia'] ?? '');
            if ($provNorm !== '' && isset($porNumProv[$provNorm]) && count($porNumProv[$provNorm]) > 1) {
                $idsGrupoP = array_keys($porNumProv[$provNorm]);
                $vecesP = count($idsGrupoP);
                $maxVeces = max($maxVeces, $vecesP);
                $idsAsociados = array_values(array_unique(array_merge($idsAsociados, $idsGrupoP)));
                $motivos[] = "N° de Providencia ({$doc['num_providencia']}) repetido {$vecesP} veces en el contrato (IDs: " . implode(', ', $idsGrupoP) . ")";
            }
        } else {
            if (!empty($duplicadosSeguimientoPorDoc[$idDoc])) {
                foreach ($duplicadosSeguimientoPorDoc[$idDoc] as $dSeg) {
                    $maxVeces = max($maxVeces, (int)$dSeg['veces']);
                    $motivos[] = $dSeg['motivo'];
                    if (!empty($dSeg['id_doc_repetido'])) {
                        $idsAsociados[] = (int)$dSeg['id_doc_repetido'];
                    }
                    $docsRelDup[] = $dSeg['doc_duplicado'] . " (" . $dSeg['veces'] . " veces)";
                }
                $idsAsociados = array_values(array_unique($idsAsociados));
            }
        }

        if (!empty($motivos)) {
            $doc['es_repetido'] = true;
            $doc['modo_repeticion'] = $modoRepeticion;
            $doc['veces_repetido'] = $maxVeces;
            $doc['motivo_repeticion'] = implode(' | ', array_values(array_unique($motivos)));
            $doc['ids_asociados'] = implode(', ', $idsAsociados);
            $doc['numeros_seguimiento'] = !empty($numSeguimientoPorDoc[$idDoc]) ? implode(', ', array_values($numSeguimientoPorDoc[$idDoc])) : '';
            $doc['documentos_relacionados_duplicados'] = implode(', ', array_values(array_unique($docsRelDup)));
            $documentosRepetidos[] = $doc;
        }
    }

    return [
        'modo_evaluacion' => $modoRepeticion,
        'descripcion_evaluacion' => $descripcionModo,
        'total_documentos_evaluados_en_periodo' => count($docsPeriodo),
        'total_documentos_repetidos_encontrados' => count($documentosRepetidos),
        'filtros_aplicados' => [
            'anio' => $filters['anio'] ?? null,
            'mes' => $filters['mes'] ?? null,
            'tipo_documento' => $filters['tipo_documento'] ?? null,
            'responsable' => $filters['responsable'] ?? null,
            'num_proceso' => $filters['num_proceso'] ?? null
        ],
        'documentos_repetidos' => $documentosRepetidos
    ];
}

/**
 * Determina el MIME type real de un archivo asociado a un documento (principal o adjunto)
 * inspeccionando sus primeros bytes (magic bytes), la extensión de su nombre y el campo mime de la BD.
 */
function normalizarMimeArchivo($dbMime, $nombreArchivo = '', $binario = '') {
    $head8 = substr((string)$binario, 0, 8);
    if (strncmp($head8, '%PDF-', 5) === 0) {
        return 'application/pdf';
    }
    if (strncmp($head8, "\xFF\xD8\xFF", 3) === 0) {
        return 'image/jpeg';
    }
    if (strncmp($head8, "\x89PNG\r\n\x1a\n", 8) === 0) {
        return 'image/png';
    }
    if (strncmp($head8, 'RIFF', 4) === 0 && substr((string)$binario, 8, 4) === 'WEBP') {
        return 'image/webp';
    }

    $ext = strtolower(pathinfo((string)$nombreArchivo, PATHINFO_EXTENSION));
    $mimeClean = strtolower(trim((string)$dbMime));
    if (strpos($mimeClean, '/') !== false) {
        $parts = explode('/', $mimeClean);
        $extFromMime = trim((string)end($parts));
        if ($ext === '' && $extFromMime !== '') {
            $ext = $extFromMime;
        }
    } elseif ($ext === '' && $mimeClean !== '') {
        $ext = $mimeClean;
    }

    switch ($ext) {
        case 'pdf':
            return 'application/pdf';
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'png':
            return 'image/png';
        case 'webp':
            return 'image/webp';
        case 'docx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        case 'xlsx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        case 'txt':
            return 'text/plain';
        case 'csv':
            return 'text/csv';
        case 'html':
        case 'htm':
            return 'text/html';
        default:
            return (strpos($mimeClean, '/') !== false) ? $mimeClean : 'application/octet-stream';
    }
}

/**
 * Formatea un tamaño en bytes a formato legible (KB / MB).
 */
function formatearTamanoArchivo($bytes) {
    $b = (int)$bytes;
    if ($b <= 0) return '0 B';
    if ($b < 1024) return $b . ' B';
    if ($b < 1048576) return round($b / 1024, 1) . ' KB';
    return round($b / 1048576, 2) . ' MB';
}

/**
 * Garantiza que una cadena sea UTF-8 válida y libre de bytes de control no imprimibles,
 * evitando que json_encode() o preg_replace('/.../u') fallen con datos binarios o latin1.
 */
function limpiarTextoUtf8Seguro($str) {
    $s = (string)$str;
    if ($s === '') {
        return '';
    }
    if (!mb_check_encoding($s, 'UTF-8')) {
        $s = @mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
    }
    if (function_exists('iconv')) {
        $ic = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
        if (is_string($ic)) {
            $s = $ic;
        }
    }
    $sClean = @preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $s);
    return is_string($sClean) ? trim($sClean) : '';
}

/**
 * Decodifica una cadena literal PDF `(...)` procesando escapes octales (\ddd),
 * saltos/tabulaciones, UTF-16BE (BOM \xFE\xFF) y convirtiendo de Windows-1252/ISO-8859-1 a UTF-8 limpio.
 */
function decodificarLiteralPdf($strP) {
    if ($strP === '') {
        return '';
    }
    // Continuación de línea en literales PDF: barra invertida seguida de salto de línea
    $strP = preg_replace('/\\\\(?:\r\n|\r|\n)/', '', $strP);

    $out = '';
    $len = strlen($strP);
    $i = 0;
    while ($i < $len) {
        $ch = $strP[$i];
        if ($ch === '\\' && ($i + 1) < $len) {
            $next = $strP[$i + 1];
            if ($next >= '0' && $next <= '7') {
                $oct = $next;
                $i += 2;
                if ($i < $len && $strP[$i] >= '0' && $strP[$i] <= '7') {
                    $oct .= $strP[$i];
                    $i++;
                    if ($i < $len && $strP[$i] >= '0' && $strP[$i] <= '7') {
                        $oct .= $strP[$i];
                        $i++;
                    }
                }
                $out .= chr(octdec($oct) & 0xFF);
                continue;
            }
            switch ($next) {
                case 'n': $out .= "\n"; break;
                case 'r': $out .= "\r"; break;
                case 't': $out .= "\t"; break;
                case 'b': $out .= ''; break;
                case 'f': $out .= ' '; break;
                case '(': $out .= '('; break;
                case ')': $out .= ')'; break;
                case '\\': $out .= '\\'; break;
                default: $out .= $next; break;
            }
            $i += 2;
        } else {
            $out .= $ch;
            $i++;
        }
    }

    // Si tiene BOM UTF-16BE (\xFE\xFF), convertir desde UTF-16BE a UTF-8
    if (strlen($out) >= 2 && ord($out[0]) === 0xFE && ord($out[1]) === 0xFF) {
        $u16 = substr($out, 2);
        if (function_exists('mb_convert_encoding')) {
            $out = @mb_convert_encoding($u16, 'UTF-8', 'UTF-16BE');
        }
    } elseif (function_exists('mb_check_encoding') && !mb_check_encoding($out, 'UTF-8')) {
        if (function_exists('mb_convert_encoding')) {
            $out = @mb_convert_encoding($out, 'UTF-8', 'Windows-1252');
        }
    }

    // Eliminar caracteres de control salvo saltos de línea y tabuladores
    $out = @preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string)$out);
    return is_string($out) ? $out : '';
}

/**
 * Evalúa si una línea extraída de un PDF corresponde a texto humano legible en español/inglés
 * y descarta basura binaria de fuentes TrueType (`FontFile2`), tablas de glifos o metadatos de fuentes (`Calibri`, `Arial`).
 */
function esLineaLegibleHumana($linea) {
    $l = trim((string)$linea);
    if ($l === '') {
        return false;
    }
    // Descartar líneas que solo nombran tipografías embebidas o URLs aisladas de plantilla
    if (preg_match('/^(?:(?:Calibri|Arial|Times\s*New\s*Roman|Helvetica|Verdana|Tahoma|Cambria|Symbol)(?:\s*[-,]\s*(?:Bold|Italic|Regular|MT))?\s*)+$/iu', $l)) {
        return false;
    }
    if (preg_match('/^https?:\/\/\S+$/i', $l)) {
        return false;
    }

    $lenChars = mb_strlen($l, 'UTF-8');
    if ($lenChars < 2) {
        return false;
    }

    // Contar letras, dígitos y caracteres sospechosos (símbolos matemáticos/binarios de tablas TrueType)
    $letras = preg_match_all('/[\p{L}]/u', $l);
    $digitos = preg_match_all('/[\p{N}]/u', $l);
    $alfanum = $letras + $digitos;
    if ($alfanum < 2) {
        return false;
    }

    // Contar caracteres permitidos habituales en documentos administrativos en español
    $normales = preg_match_all('/[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.,;:\/\-_º°ª\(\)\[\]"%\$\'&@#\+\?¡!]/u', $l);
    $ratioNormal = $normales / max(1, $lenChars);
    $ratioAlfanum = $alfanum / max(1, $lenChars);

    // Si menos del 85% son caracteres normales de texto administrativo o menos del 45% son alfanuméricos, es basura binaria
    if ($ratioNormal < 0.85 || $ratioAlfanum < 0.45) {
        return false;
    }

    // Rechazar secuencias típicas de bytecode de fuentes TrueType (`++`, `99//`, `@_`, `¸ÿ`, etc.)
    if (preg_match('/(?:\+{3,}|\/{3,}|\?{3,}|[\x{0080}-\x{00BF}\x{00D7}\x{00F7}\x{00FE}\x{00FF}]{2,})/u', $l)) {
        return false;
    }

    return true;
}

/**
 * Extrae texto legible en PHP desde un archivo binario (PDF digital, DOCX, XLSX, TXT, CSV, HTML)
 * reconstruyendo correctamente los bloques `BT ... ET` y los arreglos de kerning `[...] TJ` de PDFs generados por Word/Acrobat
 * sin mezclar streams binarios de fuentes (`FontFile2`) ni agotar el JIT stack de PCRE.
 */
function extraerTextoDeArchivoBinario($binario, $mimeReal, $nombreArchivo = '') {
    $len = strlen((string)$binario);
    if ($len === 0) {
        return '';
    }

    $ext = strtolower(pathinfo((string)$nombreArchivo, PATHINFO_EXTENSION));

    // 1. Archivos de texto plano, CSV, HTML o XML
    if (in_array($mimeReal, ['text/plain', 'text/csv', 'text/html', 'text/xml', 'application/xml'], true) || in_array($ext, ['txt', 'csv', 'html', 'htm', 'xml'], true)) {
        $txt = substr((string)$binario, 0, 200000);
        if (in_array($mimeReal, ['text/html', 'text/xml', 'application/xml'], true) || in_array($ext, ['html', 'htm', 'xml'], true)) {
            $txt = strip_tags($txt);
        }
        $txt = limpiarTextoUtf8Seguro($txt);
        $txt = preg_replace('/\s+/u', ' ', $txt);
        return mb_substr(trim((string)$txt), 0, 12000, 'UTF-8');
    }

    // 2. Archivos Word (.docx) o Excel (.xlsx) basados en contenedor ZIP/XML
    if (($mimeReal === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || $ext === 'docx' ||
         $mimeReal === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $ext === 'xlsx') && class_exists('ZipArchive')) {
        $tmpFile = @tempnam(sys_get_temp_dir(), 'sgd_doc_');
        if ($tmpFile !== false) {
            @file_put_contents($tmpFile, $binario);
            $zip = new ZipArchive();
            $extraido = '';
            if (@$zip->open($tmpFile) === true) {
                if ($ext === 'docx' || strpos($mimeReal, 'wordprocessingml') !== false) {
                    $xmlDoc = $zip->getFromName('word/document.xml');
                    if ($xmlDoc !== false) {
                        $xmlDoc = str_replace(['</w:p>', '<w:br/>', '<w:cr/>', '</w:tr>'], "\n", $xmlDoc);
                        $xmlDoc = str_replace('</w:tc>', ' | ', $xmlDoc);
                        $extraido = html_entity_decode(strip_tags($xmlDoc), ENT_QUOTES | ENT_XML1, 'UTF-8');
                    }
                } else {
                    // XLSX: leer sharedStrings y hojas
                    $shared = [];
                    $xmlShared = $zip->getFromName('xl/sharedStrings.xml');
                    if ($xmlShared !== false && preg_match_all('/<si>(.*?)<\/si>/s', $xmlShared, $mSi)) {
                        foreach ($mSi[1] as $siBlock) {
                            $shared[] = html_entity_decode(strip_tags($siBlock), ENT_QUOTES | ENT_XML1, 'UTF-8');
                        }
                    }
                    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
                    if ($sheetXml !== false && preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $sheetXml, $mRows)) {
                        $lineasXls = [];
                        foreach (array_slice($mRows[1], 0, 200) as $rowBlock) {
                            $celdasFila = [];
                            if (preg_match_all('/<c([^>]*)>(.*?)<\/c>/s', $rowBlock, $mCells, PREG_SET_ORDER)) {
                                foreach ($mCells as $cellMatch) {
                                    $attrs = $cellMatch[1];
                                    $inner = $cellMatch[2];
                                    if (strpos($attrs, 't="s"') !== false && preg_match('/<v>(\d+)<\/v>/', $inner, $mV)) {
                                        $idxS = (int)$mV[1];
                                        $celdasFila[] = $shared[$idxS] ?? '';
                                    } else {
                                        $celdasFila[] = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_XML1, 'UTF-8'));
                                    }
                                }
                            }
                            $lineaLimpia = implode(' | ', array_filter($celdasFila, function ($v) { return $v !== ''; }));
                            if ($lineaLimpia !== '') {
                                $lineasXls[] = $lineaLimpia;
                            }
                        }
                        $extraido = implode("\n", $lineasXls);
                    }
                }
                $zip->close();
            }
            @unlink($tmpFile);
            $extraido = limpiarTextoUtf8Seguro($extraido);
            $extraido = trim((string)preg_replace('/[ \t]+/u', ' ', $extraido));
            return mb_substr($extraido, 0, 12000, 'UTF-8');
        }
    }

    // 3. Archivos PDF: reconstrucción de texto desde bloques `BT ... ET` y operadores `TJ` / `Tj`
    //    excluyendo streams de fuentes TrueType (`FontFile2` / `/Length1`), imágenes y metadatos.
    if ($mimeReal === 'application/pdf' || strncmp(substr((string)$binario, 0, 5), '%PDF-', 5) === 0) {
        $maxScanLen = min($len, 5 * 1024 * 1024);
        $offset = 0;
        $streamsProcesados = 0;
        $streamsContenido = [];
        $cmapHexToUtf8 = [];

        while ($offset < $maxScanLen && $streamsProcesados < 90) {
            $posStream = strpos($binario, 'stream', $offset);
            if ($posStream === false || $posStream >= $maxScanLen) {
                break;
            }
            // Evitar coincidencia con 'endstream'
            if ($posStream >= 3 && substr($binario, $posStream - 3, 3) === 'end') {
                $offset = $posStream + 6;
                continue;
            }

            // Inspeccionar el diccionario del objeto PDF justo antes de `stream` (últimos 450 bytes)
            $dictStart = max(0, $posStream - 450);
            $dictHeader = substr($binario, $dictStart, $posStream - $dictStart);

            $startData = $posStream + 6;
            if ($startData < $len && $binario[$startData] === "\r") {
                $startData++;
            }
            if ($startData < $len && $binario[$startData] === "\n") {
                $startData++;
            }

            $posEnd = strpos($binario, 'endstream', $startData);
            if ($posEnd === false) {
                break;
            }
            $offset = $posEnd + 9;
            $streamLen = $posEnd - $startData;

            if ($streamLen <= 0 || $streamLen > 358400) {
                continue;
            }

            // DESCARTAR streams de fuentes embebidas (`/Length1`, `/FontFile`, `/Subtype /TrueType`, `/CIDFontType0C`),
            // imágenes (`/Subtype /Image`), tablas `/XRef`, `/ObjStm` o `/Metadata` XMP
            if (preg_match('/\/(?:Subtype\s*\/(?:Image|XML|Type1C|CIDFontType0C|TrueType|OpenType)|FontFile[123]?|Length1|Type\s*\/(?:XRef|ObjStm|Metadata|FontDescriptor))\b/i', $dictHeader)) {
                continue;
            }

            $rawStream = trim(substr($binario, $startData, $streamLen), "\r\n");
            $streamsProcesados++;

            $decoded = @gzuncompress($rawStream);
            if ($decoded === false) {
                $decoded = @gzinflate($rawStream);
            }
            if ($decoded === false || !is_string($decoded) || $decoded === '') {
                if (strpos($rawStream, 'BT') !== false || strpos($rawStream, 'beginbfchar') !== false || strpos($rawStream, 'beginbfrange') !== false) {
                    $decoded = $rawStream;
                } else {
                    continue;
                }
            }

            if (strlen($decoded) > 524288) {
                continue;
            }

            // Si es un stream CMap `/ToUnicode` (`beginbfchar` / `beginbfrange`), registrar tabla de traducción hexadecimal -> UTF-8
            if (strpos($decoded, 'beginbfchar') !== false || strpos($decoded, 'beginbfrange') !== false) {
                if (@preg_match_all('/beginbfchar\s*(.*?)\s*endbfchar/s', $decoded, $mBfc)) {
                    foreach ($mBfc[1] as $blockBfc) {
                        if (@preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $blockBfc, $mPairs, PREG_SET_ORDER)) {
                            foreach ($mPairs as $pair) {
                                $srcHex = strtoupper($pair[1]);
                                $dstHex = $pair[2];
                                if (strlen($dstHex) % 4 === 0 && function_exists('mb_convert_encoding')) {
                                    $cmapHexToUtf8[$srcHex] = @mb_convert_encoding(@ pack('H*', $dstHex), 'UTF-8', 'UTF-16BE');
                                }
                            }
                        }
                    }
                }
                if (@preg_match_all('/beginbfrange\s*(.*?)\s*endbfrange/s', $decoded, $mBfr)) {
                    foreach ($mBfr[1] as $blockBfr) {
                        if (@preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $blockBfr, $mRanges, PREG_SET_ORDER)) {
                            foreach ($mRanges as $rng) {
                                $startCode = hexdec($rng[1]);
                                $endCode = hexdec($rng[2]);
                                $targetCode = hexdec($rng[3]);
                                $padLen = strlen($rng[1]);
                                if ($endCode >= $startCode && ($endCode - $startCode) <= 256) {
                                    for ($c = $startCode; $c <= $endCode; $c++) {
                                        $kHex = strtoupper(str_pad(dechex($c), $padLen, '0', STR_PAD_LEFT));
                                        $uCode = $targetCode + ($c - $startCode);
                                        $uHex = str_pad(dechex($uCode), 4, '0', STR_PAD_LEFT);
                                        if (function_exists('mb_convert_encoding')) {
                                            $cmapHexToUtf8[$kHex] = @mb_convert_encoding(@pack('H*', $uHex), 'UTF-8', 'UTF-16BE');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                continue;
            }

            // Solo conservar streams que contengan bloques de texto PDF `BT ... ET` y operadores `TJ` o `Tj`
            if (strpos($decoded, 'BT') !== false && strpos($decoded, 'ET') !== false && (strpos($decoded, 'TJ') !== false || strpos($decoded, 'Tj') !== false)) {
                $streamsContenido[] = $decoded;
            }
        }

        // Procesar los streams de contenido de página extrayendo texto dentro de `BT ... ET`
        $lineasLimpias = [];
        $caracteresAcumulados = 0;

        foreach ($streamsContenido as $contentStream) {
            if (!@preg_match_all('/\bBT\b(.*?)\bET\b/s', $contentStream, $mBtBlocks)) {
                continue;
            }
            foreach ($mBtBlocks[1] as $btBlock) {
                $lineaActual = '';
                // Tokenizar operaciones dentro de BT..ET: arreglos `[...] TJ`, literales `(...) Tj`, hex `<...> Tj`, y saltos de línea `Td`/`TD`/`Tm`/`T*`
                if (@preg_match_all('/\[([^\[\]]*)\]\s*TJ|\(([^()\\\\]*(?:\\\\.[^()\\\\]*)*)\)\s*(?:Tj|\'|")|<([0-9A-Fa-f\s]+)>\s*Tj|(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+T[dD]\b|\bT\*\b|\bTm\b/s', $btBlock, $mOps, PREG_SET_ORDER)) {
                    foreach ($mOps as $op) {
                        $tokenFull = $op[0];
                        // Caso 1: Arreglo de kerning `[...] TJ` (muy usado por Microsoft Word al exportar a PDF con Calibri/Arial)
                        if ( isset($op[1]) && $op[1] !== '' ) {
                            $arrBody = $op[1];
                            if (@preg_match_all('/\(([^()\\\\]*(?:\\\\.[^()\\\\]*)*)\)|<([0-9A-Fa-f\s]+)>|(-?\d+(?:\.\d+)?)/s', $arrBody, $mArrItems, PREG_SET_ORDER)) {
                                foreach ($mArrItems as $item) {
                                    if (isset($item[1]) && $item[0][0] === '(') {
                                        // Concatenar directamente (incluyendo literales de 1 solo carácter como `(S)`, `(d)`, `(a)`, `(6)`)
                                        $lineaActual .= decodificarLiteralPdf($item[1]);
                                    } elseif (isset($item[2]) && $item[2] !== '') {
                                        $hexStr = preg_replace('/\s+/', '', strtoupper($item[2]));
                                        if (!empty($cmapHexToUtf8)) {
                                            $step = isset($cmapHexToUtf8[substr($hexStr, 0, 4)]) ? 4 : 2;
                                            for ($h = 0; $h < strlen($hexStr); $h += $step) {
                                                $codeH = substr($hexStr, $h, $step);
                                                if (isset($cmapHexToUtf8[$codeH])) {
                                                    $lineaActual .= $cmapHexToUtf8[$codeH];
                                                }
                                            }
                                        }
                                    } elseif (isset($item[3]) && $item[3] !== '') {
                                        // En PDF `TJ`, un desplazamiento negativo grande (<= -100 milésimas de em) representa un espacio entre palabras;
                                        // valores pequeños como `1`, `-1`, `-3`, `5` son micro-ajustes de kerning dentro de la misma palabra.
                                        $kernVal = (float)$item[3];
                                        if ($kernVal <= -100.0) {
                                            if ($lineaActual !== '' && substr($lineaActual, -1) !== ' ') {
                                                $lineaActual .= ' ';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // Caso 2: Literal simple `(...) Tj`
                        elseif (isset($op[2]) && $tokenFull[0] === '(') {
                            $lineaActual .= decodificarLiteralPdf($op[2]);
                        }
                        // Caso 3: Hex simple `<...> Tj`
                        elseif (isset($op[3]) && $op[3] !== '') {
                            $hexStr = preg_replace('/\s+/', '', strtoupper($op[3]));
                            if (!empty($cmapHexToUtf8)) {
                                $step = isset($cmapHexToUtf8[substr($hexStr, 0, 4)]) ? 4 : 2;
                                for ($h = 0; $h < strlen($hexStr); $h += $step) {
                                    $codeH = substr($hexStr, $h, $step);
                                    if (isset($cmapHexToUtf8[$codeH])) {
                                        $lineaActual .= $cmapHexToUtf8[$codeH];
                                    }
                                }
                            }
                        }
                        // Caso 4: Operadores de cambio de posición vertical `Td`, `TD`, `T*`, `Tm`
                        else {
                            $esSaltoLinea = ($tokenFull === 'T*' || $tokenFull === 'Tm');
                            if (!$esSaltoLinea && isset($op[5]) && $op[5] !== '') {
                                $dy = abs((float)$op[5]);
                                if ($dy > 1.5) {
                                    $esSaltoLinea = true;
                                } elseif (isset($op[4]) && (float)$op[4] > 10.0 && $lineaActual !== '' && substr($lineaActual, -1) !== ' ') {
                                    $lineaActual .= ' ';
                                }
                            }
                            if ($esSaltoLinea && trim($lineaActual) !== '') {
                                $cand = limpiarTextoUtf8Seguro(preg_replace('/[ \t]+/u', ' ', $lineaActual));
                                if (esLineaLegibleHumana($cand)) {
                                    $lineasLimpias[] = $cand;
                                    $caracteresAcumulados += strlen($cand);
                                }
                                $lineaActual = '';
                            }
                        }
                    }
                }
                if (trim($lineaActual) !== '') {
                    $cand = limpiarTextoUtf8Seguro(preg_replace('/[ \t]+/u', ' ', $lineaActual));
                    if (esLineaLegibleHumana($cand)) {
                        $lineasLimpias[] = $cand;
                        $caracteresAcumulados += strlen($cand);
                    }
                }
                if ($caracteresAcumulados >= 14000) {
                    break 2;
                }
            }
        }

        if (!empty($lineasLimpias)) {
            // Eliminar líneas consecutivas idénticas (p. ej. encabezados/pies repetidos)
            $lineasUnicas = [];
            $prevL = null;
            foreach ($lineasLimpias as $lin) {
                if ($lin !== $prevL) {
                    $lineasUnicas[] = $lin;
                    $prevL = $lin;
                }
            }
            $textoPdf = implode("\n", $lineasUnicas);
            return mb_substr(trim($textoPdf), 0, 12000, 'UTF-8');
        }
    }

    return '';
}

/**
 * Accede al documento solicitado y a TODA su información y archivos asociados en la base de datos:
 * 1. Metadatos completos de `documento` y `detalle_documento` (materia, antecedente, incluye, comentario, fechas, flujo, estado, remitente, destinatario, responsable, código de proceso).
 * 2. El archivo principal cargado en la tabla `documento` (`d.nombre_documento`, `d.mime_documento`, `d.documento`).
 * 3. Todos los archivos adjuntos asociados en la tabla `adjunto` (`a.nombre_adjunto`, `a.mime_adjunto`, `a.archivo_adjunto`).
 * 4. El historial de respuestas en `bitacora`, las actividades en `accion`/`actividad` y los documentos relacionados en `seguimiento`.
 */
function consultarArchivosYContenidoDocumento($cnx, $userContractId, $userId, $filters = [], $keywords = [], $customVars = [], $usuariosContrato = [], &$inlinePartsOut = []) {
    $inlinePartsOut = [];
    $filtersBase = $filters;
    $filtersBase['limite'] = 5;

    // Si busca un documento puntual por código/número o providencia, limpiar filtros accesorios que puedan ocultar el documento
    if (!empty($filtersBase['doc_numero']) || !empty($filtersBase['num_providencia'])) {
        $filtersBase['estado_doc'] = null;
        $filtersBase['anio'] = null;
        $filtersBase['mes'] = null;
        $filtersBase['fecha_desde'] = null;
        $filtersBase['fecha_hasta'] = null;
        $filtersBase['tipo_documento'] = null;
        $filtersBase['materia'] = null;
        $keywords = [];
    }

    $docsEncontrados = buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filtersBase, $keywords, $customVars, $usuariosContrato);
    if (isset($docsEncontrados['error'])) {
        return ['error' => $docsEncontrados['error']];
    }

    if (empty($docsEncontrados)) {
        return [
            'documento_encontrado' => false,
            'criterio_buscado' => $filters['doc_numero'] ?? ($filters['num_providencia'] ?? ($filters['doc_nombre'] ?? ($filters['materia'] ?? ''))),
            'documentos_analizados' => []
        ];
    }

    $mimesSoportadosGeminiInline = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp'
    ];
    // Límite seguro para envío inline base64 a Gemini sin provocar timeout HTTP del servidor web
    $maxBytesInlineTotal = 4 * 1024 * 1024; // 4 MB máximo para inline_data multimodal
    $maxBytesLecturaLocal = 10 * 1024 * 1024; // 10 MB máximo para lectura de BLOB en memoria PHP
    $bytesInlineAcumulados = 0;

    // Cuando el usuario consulta por un número de documento específico (ej. SCINC/IF/1001/2026),
    // analizamos el documento principal coincidente (1) para responder rápido y con precisión sin mezclar otros documentos
    $maxDocsAnalizar = (!empty($filters['doc_numero']) || !empty($filters['num_providencia'])) ? 1 : 2;
    $documentosAnalizados = [];

    foreach (array_slice($docsEncontrados, 0, $maxDocsAnalizar) as $idxDoc => $docMeta) {
        $idDoc = (int)$docMeta['id_documento'];
        $etiquetaDoc = trim(($docMeta['tipo_documento'] ?? 'Documento') . ' ' . ($docMeta['num_documento'] ?? ('ID ' . $idDoc)));

        // 1. Consultar primero metadatos y tamaño real en bytes (`OCTET_LENGTH(documento)`) sin traer el LONGBLOB si es gigante
        $rowsMetaBin = ejecutarSelect(
            $cnx,
            "SELECT nombre_documento, mime_documento, tamano_documento, OCTET_LENGTH(documento) AS bytes_reales
             FROM documento
             WHERE id_documento = ?
             LIMIT 1",
            [$idDoc],
            "i"
        );

        $infoArchivoPrincipal = [
            'tiene_archivo_cargado' => false,
            'nombre_archivo' => limpiarTextoUtf8Seguro($docMeta['nombre_documento'] ?? ''),
            'mime_type' => '',
            'tamano_bytes' => 0,
            'tamano_legible' => '0 B',
            'enviado_a_ia_multimodal' => false,
            'texto_extraido_local' => '',
            'url_descarga' => "negocio/descargarDocumento.php?idDocumento={$idDoc}"
        ];

        if (is_array($rowsMetaBin) && !isset($rowsMetaBin['error']) && !empty($rowsMetaBin[0])) {
            $rMeta = $rowsMetaBin[0];
            $bytesReales = (int)($rMeta['bytes_reales'] ?? 0);
            $nomArchivo = limpiarTextoUtf8Seguro($rMeta['nombre_documento'] ?? '');
            $dbMimeDoc = trim((string)($rMeta['mime_documento'] ?? ''));
            $tamReportado = $bytesReales > 0 ? $bytesReales : (int)($rMeta['tamano_documento'] ?? 0);

            $infoArchivoPrincipal['nombre_archivo'] = $nomArchivo;
            $infoArchivoPrincipal['mime_type'] = normalizarMimeArchivo($dbMimeDoc, $nomArchivo, '');
            $infoArchivoPrincipal['tamano_bytes'] = $tamReportado;
            $infoArchivoPrincipal['tamano_legible'] = formatearTamanoArchivo($tamReportado);

            if ($bytesReales > 0) {
                $infoArchivoPrincipal['tiene_archivo_cargado'] = true;

                if ($bytesReales <= $maxBytesLecturaLocal) {
                    $rowsBinDoc = ejecutarSelect(
                        $cnx,
                        "SELECT documento FROM documento WHERE id_documento = ? LIMIT 1",
                        [$idDoc],
                        "i"
                    );
                    if (is_array($rowsBinDoc) && !isset($rowsBinDoc['error']) && !empty($rowsBinDoc[0]['documento'])) {
                        $binContent = (string)$rowsBinDoc[0]['documento'];
                        $lenBin = strlen($binContent);
                        $mimeReal = normalizarMimeArchivo($dbMimeDoc, $nomArchivo, $binContent);
                        $infoArchivoPrincipal['mime_type'] = $mimeReal;
                        $infoArchivoPrincipal['texto_extraido_local'] = extraerTextoDeArchivoBinario($binContent, $mimeReal, $nomArchivo);

                        // Si el archivo es soportado por Gemini inline y cabe en el presupuesto de 4 MB,
                        // lo adjuntamos como inline_data para lectura multimodal directa además del texto extraído limpio
                        $debeEnviarInline = in_array($mimeReal, $mimesSoportadosGeminiInline, true)
                            && (($bytesInlineAcumulados + $lenBin) <= $maxBytesInlineTotal);

                        if ($debeEnviarInline) {
                            $inlinePartsOut[] = [
                                'text' => "=== ARCHIVO PRINCIPAL DEL DOCUMENTO ({$etiquetaDoc} | ID: {$idDoc} | Nombre de archivo: {$nomArchivo}) ==="
                            ];
                            $inlinePartsOut[] = [
                                'inline_data' => [
                                    'mime_type' => $mimeReal,
                                    'data' => base64_encode($binContent)
                                ]
                            ];
                            $bytesInlineAcumulados += $lenBin;
                            $infoArchivoPrincipal['enviado_a_ia_multimodal'] = true;
                        }
                        unset($binContent, $rowsBinDoc);
                    }
                }
            }
        }

        // 2. Obtener los archivos adjuntos asociados al documento en la tabla `adjunto` (verificando primero OCTET_LENGTH)
        $rowsAdjMeta = ejecutarSelect(
            $cnx,
            "SELECT id_adjunto, nombre_adjunto, mime_adjunto, tamano_adjunto, OCTET_LENGTH(archivo_adjunto) AS bytes_reales
             FROM adjunto
             WHERE id_documento = ?
             ORDER BY id_adjunto ASC",
            [$idDoc],
            "i"
        );

        $listaAdjuntos = [];
        if (is_array($rowsAdjMeta) && !isset($rowsAdjMeta['error'])) {
            foreach ($rowsAdjMeta as $rAdj) {
                $idAdj = (int)$rAdj['id_adjunto'];
                $nomAdj = limpiarTextoUtf8Seguro($rAdj['nombre_adjunto'] ?? ('adjunto_' . $idAdj));
                $bytesAdj = (int)($rAdj['bytes_reales'] ?? 0);
                $dbMimeAdj = trim((string)($rAdj['mime_adjunto'] ?? ''));
                $mimeAdj = normalizarMimeArchivo($dbMimeAdj, $nomAdj, '');
                $tamAdjBytes = $bytesAdj > 0 ? $bytesAdj : (int)($rAdj['tamano_adjunto'] ?? 0);

                $itemAdj = [
                    'id_adjunto' => $idAdj,
                    'nombre_adjunto' => $nomAdj,
                    'mime_type' => $mimeAdj,
                    'tamano_bytes' => $tamAdjBytes,
                    'tamano_legible' => formatearTamanoArchivo($tamAdjBytes),
                    'tiene_contenido' => ($bytesAdj > 0),
                    'enviado_a_ia_multimodal' => false,
                    'texto_extraido_local' => '',
                    'url_descarga' => "negocio/descargarDocumento.php?idAdjunto={$idAdj}"
                ];

                if ($bytesAdj > 0 && $bytesAdj <= (6 * 1024 * 1024)) {
                    $rowsAdjBin = ejecutarSelect(
                        $cnx,
                        "SELECT archivo_adjunto FROM adjunto WHERE id_adjunto = ? LIMIT 1",
                        [$idAdj],
                        "i"
                    );
                    if (is_array($rowsAdjBin) && !isset($rowsAdjBin['error']) && !empty($rowsAdjBin[0]['archivo_adjunto'])) {
                        $binAdj = (string)$rowsAdjBin[0]['archivo_adjunto'];
                        $lenAdj = strlen($binAdj);
                        $mimeAdj = normalizarMimeArchivo($dbMimeAdj, $nomAdj, $binAdj);
                        $itemAdj['mime_type'] = $mimeAdj;
                        $itemAdj['texto_extraido_local'] = extraerTextoDeArchivoBinario($binAdj, $mimeAdj, $nomAdj);

                        $textoAdjLen = mb_strlen($itemAdj['texto_extraido_local'], 'UTF-8');
                        if (in_array($mimeAdj, $mimesSoportadosGeminiInline, true) && ($bytesInlineAcumulados + $lenAdj) <= $maxBytesInlineTotal && ($textoAdjLen < 600 || $lenAdj <= (1024 * 1024))) {
                            $inlinePartsOut[] = [
                                'text' => "=== ARCHIVO ADJUNTO ASOCIADO AL DOCUMENTO ({$etiquetaDoc} | Adjunto ID: {$idAdj} | Nombre: {$nomAdj}) ==="
                            ];
                            $inlinePartsOut[] = [
                                'inline_data' => [
                                    'mime_type' => $mimeAdj,
                                    'data' => base64_encode($binAdj)
                                ]
                            ];
                            $bytesInlineAcumulados += $lenAdj;
                            $itemAdj['enviado_a_ia_multimodal'] = true;
                        }
                        unset($binAdj, $rowsAdjBin);
                    }
                }

                $listaAdjuntos[] = $itemAdj;
            }
        }

        // 3. Obtener el historial de bitácora / respuestas asociadas al documento en `bitacora`
        $rowsBit = ejecutarSelect(
            $cnx,
            "SELECT id_bitacora, emisor, fecha_emision, asunto, detalle_respuesta
             FROM bitacora
             WHERE id_documento = ?
             ORDER BY fecha_emision ASC, id_bitacora ASC",
            [$idDoc],
            "i"
        );
        $listaBitacora = [];
        if (is_array($rowsBit) && !isset($rowsBit['error'])) {
            foreach ($rowsBit as $rb) {
                $listaBitacora[] = [
                    'emisor' => limpiarTextoUtf8Seguro($rb['emisor'] ?? ''),
                    'fecha_emision' => limpiarTextoUtf8Seguro($rb['fecha_emision'] ?? ''),
                    'asunto' => limpiarTextoUtf8Seguro($rb['asunto'] ?? ''),
                    'detalle_respuesta' => limpiarTextoUtf8Seguro($rb['detalle_respuesta'] ?? '')
                ];
            }
        }

        // 4. Obtener las acciones / actividades requeridas asociadas al documento en `accion` + `actividad`
        $rowsAcc = ejecutarSelect(
            $cnx,
            "SELECT ac.id_actividad, act.actividad
             FROM accion ac
             LEFT JOIN actividad act ON ac.id_actividad = act.id_actividad
             WHERE ac.id_documento = ?",
            [$idDoc],
            "i"
        );
        $listaAcciones = [];
        if (is_array($rowsAcc) && !isset($rowsAcc['error'])) {
            foreach ($rowsAcc as $rac) {
                $nomAct = limpiarTextoUtf8Seguro($rac['actividad'] ?? '');
                if ($nomAct !== '' && !in_array($nomAct, $listaAcciones, true)) {
                    $listaAcciones[] = $nomAct;
                }
            }
        }

        // 5. Obtener documentos relacionados vinculados en `seguimiento` para enriquecer el contexto del documento
        $rowsSeg = ejecutarSelect(
            $cnx,
            "SELECT DISTINCT d2.id_documento, td2.tipo_documento, dd2.num_documento, dd2.materia, d2.fecha_documento,
                    CASE WHEN d2.id_estado_doc = 1 THEN 'Pendiente' WHEN d2.id_estado_doc = 2 THEN 'Cerrado' ELSE 'Otro' END AS estado_documento
             FROM seguimiento s1
             INNER JOIN seguimiento s2 ON s1.numero_seguimiento = s2.numero_seguimiento
             INNER JOIN documento d2 ON s2.id_documento = d2.id_documento
             INNER JOIN detalle_documento dd2 ON d2.id_documento = dd2.id_documento
             LEFT JOIN tipo_documento td2 ON dd2.id_tipo_doc = td2.id_tipo_doc
             WHERE s1.id_documento = ? AND s1.numero_seguimiento <> 0 AND s2.id_documento <> ?
             ORDER BY d2.fecha_documento DESC
             LIMIT 15",
            [$idDoc, $idDoc],
            "ii"
        );
        $listaRelacionadosSeg = [];
        if (is_array($rowsSeg) && !isset($rowsSeg['error'])) {
            foreach ($rowsSeg as $rSeg) {
                $listaRelacionadosSeg[] = [
                    'id_documento' => (int)$rSeg['id_documento'],
                    'tipo_documento' => limpiarTextoUtf8Seguro($rSeg['tipo_documento'] ?? ''),
                    'num_documento' => limpiarTextoUtf8Seguro($rSeg['num_documento'] ?? ''),
                    'materia' => limpiarTextoUtf8Seguro($rSeg['materia'] ?? ''),
                    'fecha_documento' => limpiarTextoUtf8Seguro($rSeg['fecha_documento'] ?? ''),
                    'estado_documento' => limpiarTextoUtf8Seguro($rSeg['estado_documento'] ?? '')
                ];
            }
        }

        // Asegurar que todos los campos de metadatos estén limpios en UTF-8
        foreach ($docMeta as $kMeta => $vMeta) {
            if (is_string($vMeta)) {
                $docMeta[$kMeta] = limpiarTextoUtf8Seguro($vMeta);
            }
        }

        $documentosAnalizados[] = [
            'metadatos_documento' => $docMeta,
            'archivo_principal' => $infoArchivoPrincipal,
            'cantidad_archivos_adjuntos' => count($listaAdjuntos),
            'archivos_adjuntos' => $listaAdjuntos,
            'bitacora_respuestas' => $listaBitacora,
            'acciones_requeridas' => $listaAcciones,
            'documentos_relacionados_seguimiento' => $listaRelacionadosSeg
        ];
    }

    return [
        'documento_encontrado' => true,
        'criterio_buscado' => limpiarTextoUtf8Seguro($filters['doc_numero'] ?? ($filters['num_providencia'] ?? ($filters['doc_nombre'] ?? ''))),
        'total_documentos_coincidentes' => count($docsEncontrados),
        'documentos_analizados' => $documentosAnalizados
    ];
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

if ($userContractId !== 41) {
    mysqli_close($cnx);
    echo json_encode(['error' => 'Acceso restringido: El módulo Chat con IA solo está habilitado para usuarios pertenecientes al contrato autorizado.']);
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

// Helper para limpiar y decodificar respuestas JSON de Gemini aunque incluyan bloques ```json
function extraerJsonDeTexto($texto) {
    $limpio = trim((string)$texto);
    $limpio = preg_replace('/^```(?:json)?\s*/i', '', $limpio);
    $limpio = preg_replace('/\s*```$/', '', $limpio);
    $decoded = json_decode($limpio, true);
    if (is_array($decoded)) {
        return isset($decoded[0]) && is_array($decoded[0]) ? $decoded[0] : $decoded;
    }
    if (preg_match('/\{.*\}/s', $limpio, $m)) {
        $decoded2 = json_decode($m[0], true);
        if (is_array($decoded2)) {
            return $decoded2;
        }
    }
    return null;
}

// Helper para llamar a Gemini con resolución IPv4 forzada, soporte multimodal (`inline_data`) y fallback rápido sin agotar el timeout HTTP del servidor
function llamarGemini($apiKey, $prompt, $jsonMode = false, $timeoutSec = 20, $inlineParts = []) {
    $primaryModel = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-flash-latest';
    $modelsToTry = array_values(array_unique([$primaryModel, 'gemini-2.0-flash', 'gemini-1.5-flash']));
    $lastError = 'Error desconocido al conectar con Gemini';

    $parts = [['text' => limpiarTextoUtf8Seguro($prompt)]];
    if (!empty($inlineParts) && is_array($inlineParts)) {
        foreach ($inlineParts as $ip) {
            $parts[] = $ip;
        }
    }

    $jsonFlags = JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0);

    foreach ($modelsToTry as $idx => $model) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

        $data = [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2
            ]
        ];

        if ($jsonMode) {
            $data['generationConfig']['responseMimeType'] = 'application/json';
        }

        $payloadJson = json_encode($data, $jsonFlags);
        if ($payloadJson === false) {
            if (!empty($inlineParts)) {
                return llamarGemini($apiKey, $prompt, $jsonMode, 18, []);
            }
            return ['error' => 'Error al codificar payload JSON para Gemini'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, $idx === 0 ? $timeoutSec : min(15, $timeoutSec));
        if (defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $lastError = curl_error($ch);
            curl_close($ch);
            error_log("Advertencia Gemini ({$model}): {$lastError}.");
            // Si la petición incluía archivos pesados en inline_data y demoró/falló por red, reintentar de inmediato solo con el texto extraído y metadatos
            if (!empty($inlineParts)) {
                error_log("Reintentando Gemini sin inline_data (usando texto extraído local + metadatos BD)...");
                return llamarGemini($apiKey, $prompt, $jsonMode, 16, []);
            }
            continue;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $lastError = 'API returned HTTP code ' . $httpCode . '. Response: ' . $response;
            if (!empty($inlineParts)) {
                error_log("Advertencia HTTP {$httpCode} en Gemini con inline_data. Reintentando con texto extraído en PHP + metadatos BD...");
                return llamarGemini($apiKey, $prompt, $jsonMode, 16, []);
            }
            if ($httpCode === 429 || $httpCode >= 500 || $httpCode === 404) {
                error_log("Advertencia HTTP {$httpCode} en Gemini ({$model}). Probando siguiente modelo...");
                continue;
            }
            return ['error' => $lastError];
        }

        $resObj = json_decode($response, true);
        if (!empty($resObj['candidates'][0]['content']['parts']) && is_array($resObj['candidates'][0]['content']['parts'])) {
            $textParts = [];
            foreach ($resObj['candidates'][0]['content']['parts'] as $candPart) {
                if (!empty($candPart['thought'])) {
                    continue;
                }
                if (isset($candPart['text']) && is_string($candPart['text'])) {
                    $textParts[] = $candPart['text'];
                }
            }
            $joinedText = trim(implode("\n", $textParts));
            if ($joinedText !== '') {
                $usage = $resObj['usageMetadata'] ?? [];
                return [
                    'text' => $joinedText,
                    'model_used' => $model,
                    'usage' => [
                        'prompt_tokens'     => (int)($usage['promptTokenCount'] ?? 0),
                        'completion_tokens' => (int)($usage['candidatesTokenCount'] ?? 0),
                        'total_tokens'      => (int)($usage['totalTokenCount'] ?? 0)
                    ]
                ];
            }
        }
        $lastError = 'Respuesta no válida de Gemini. API Response: ' . $response;
    }

    return ['error' => $lastError];
}

/**
 * Registra en la tabla `log_consumo_ia` los tokens, créditos y costo estimado de la consulta.
 * Envuelto en try/catch para evitar que una excepción SQL interrumpa la respuesta JSON.
 */
function registrarConsumoIA($cnx, $idContrato, $idUsuario, $modelo, $intent, $tokensEntrada, $tokensSalida, $tokensTotales, $query) {
    try {
        if (!$cnx) {
            return;
        }
        $creditos = round($tokensTotales / 1000, 4);
        $costoUsd = round((($tokensEntrada / 1000000) * 0.10) + (($tokensSalida / 1000000) * 0.40), 6);

        $sql = "INSERT INTO log_consumo_ia 
                (id_contrato, id_usuario, fecha_hora, modelo_usado, intent_detectado, tokens_entrada, tokens_salida, tokens_totales, creditos_consumidos, costo_estimado_usd, consulta_usuario)
                VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = @mysqli_prepare($cnx, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "iissiiidds",
                $idContrato,
                $idUsuario,
                $modelo,
                $intent,
                $tokensEntrada,
                $tokensSalida,
                $tokensTotales,
                $creditos,
                $costoUsd,
                $query
            );
            @mysqli_stmt_execute($stmt);
            @mysqli_stmt_close($stmt);
        }
    } catch (Throwable $e) {
        error_log("Aviso: No se pudo registrar consumo IA en log_consumo_ia: " . $e->getMessage());
    }
}

/**
 * Formatea una respuesta clara en Markdown directamente en PHP si la API externa de IA
 * llegara a presentar intermitencia o timeout en el Paso 2.
 */
function generarRespuestaFallbackLocal($dbResults, $esConsultaTiempos, $esConsultaCantidad, $userContractName, $filters = [], $esConsultaRelacionados = false, $esConsultaRepetidos = false, $esConsultaContenidoArchivo = false) {
    if ($esConsultaContenidoArchivo && isset($dbResults['analisis_contenido_documento'])) {
        $ac = $dbResults['analisis_contenido_documento'];
        if (empty($ac['documento_encontrado']) || empty($ac['documentos_analizados'])) {
            $crit = $ac['criterio_buscado'] ?: ($filters['doc_numero'] ?? 'indicado');
            return "No se encontró ningún documento con el número o código **{$crit}** dentro del contrato **{$userContractName}**.";
        }
        $md = "### Resumen e Información del Documento — Contrato **{$userContractName}**\n\n";
        foreach ($ac['documentos_analizados'] as $item) {
            $d = $item['metadatos_documento'];
            $ap = $item['archivo_principal'];
            $md .= "#### **{$d['tipo_documento']} N° {$d['num_documento']}** (ID: {$d['id_documento']}" . (!empty($d['num_providencia']) ? " | Providencia: {$d['num_providencia']}" : "") . ")\n\n";
            $md .= "##### 1. Lo que informa el documento (Resumen Ejecutivo)\n";
            $md .= "- **Materia / Asunto Principal:** " . (!empty($d['materia']) ? $d['materia'] : 'Sin materia registrada') . "\n";
            if (!empty($d['antecedente'])) {
                $md .= "- **Antecedente:** {$d['antecedente']}\n";
            }
            if (!empty($d['incluye'])) {
                $md .= "- **Incluye:** {$d['incluye']}\n";
            }
            if (!empty($d['comentario'])) {
                $md .= "- **Observación / Comentario:** {$d['comentario']}\n";
            }

            if (!empty($ap['texto_extraido_local'])) {
                $lineasExt = array_values(array_filter(array_map('trim', explode("\n", (string)$ap['texto_extraido_local'])), 'esLineaLegibleHumana'));
                if (!empty($lineasExt)) {
                    $extracto = mb_substr(implode("\n", $lineasExt), 0, 2500, 'UTF-8');
                    $md .= "\n**Contenido extraído del archivo del documento:**\n> " . str_replace("\n", "\n> ", $extracto) . "\n";
                }
            }

            $md .= "\n##### 2. Ficha Técnica y Participantes\n";
            $md .= "- **Fecha del Documento:** {$d['fecha_documento']} | **Fecha de Recepción:** {$d['fecha_recepcion']}" . (!empty($d['fecha_plazo']) && $d['fecha_plazo'] !== '0000-00-00' ? " | **Fecha Plazo:** {$d['fecha_plazo']}" : "") . "\n";
            $md .= "- **Estado Actual:** **{$d['estado_documento']}** | **Flujo:** {$d['flujo_documento']}" . (!empty($d['fecha_cierre']) ? " | **Fecha Cierre:** {$d['fecha_cierre']}" : "") . "\n";
            $md .= "- **Remitente (Quién informa/envía):** " . (!empty($d['remitente']) ? $d['remitente'] : 'Sin especificar') . (!empty($d['cargo_remitente']) ? " (*{$d['cargo_remitente']}*)" : "") . "\n";
            $md .= "- **Destinatario (A quién se dirige):** " . (!empty($d['destinatario']) ? $d['destinatario'] : 'Sin especificar') . (!empty($d['cargo_destinatario']) ? " (*{$d['cargo_destinatario']}*)" : "") . "\n";
            $md .= "- **Responsable Asignado:** " . (!empty($d['responsable']) ? $d['responsable'] : 'Sin asignar') . (!empty($d['num_proceso']) ? " | **Código de Proceso:** `{$d['num_proceso']}`" : "") . (!empty($d['nombre_subcontrato']) ? " | **Subcontrato:** {$d['nombre_subcontrato']}" : "") . "\n";
            $md .= "- **Archivo Principal:** " . ($ap['tiene_archivo_cargado'] ? "`{$ap['nombre_archivo']}` ({$ap['mime_type']}, {$ap['tamano_legible']}) — [Descargar Documento]({$ap['url_descarga']})" : "Registro documental sin archivo binario adjunto — [Ver Ficha / Descargar]({$ap['url_descarga']})") . "\n";

            if (!empty($item['acciones_requeridas'])) {
                $md .= "- **Acciones / Actividades Requeridas:** " . implode(', ', $item['acciones_requeridas']) . "\n";
            }

            if (!empty($item['archivos_adjuntos'])) {
                $md .= "\n##### 3. Archivos Adjuntos Asociados (" . count($item['archivos_adjuntos']) . ")\n";
                foreach ($item['archivos_adjuntos'] as $adj) {
                    $md .= "- `{$adj['nombre_adjunto']}` ({$adj['mime_type']}, {$adj['tamano_legible']}) — [Descargar Adjunto]({$adj['url_descarga']})\n";
                    if (!empty($adj['texto_extraido_local'])) {
                        $extAdj = mb_substr($adj['texto_extraido_local'], 0, 600, 'UTF-8');
                        $md .= "  > " . str_replace("\n", " ", $extAdj) . "\n";
                    }
                }
            }
            if (!empty($item['bitacora_respuestas'])) {
                $md .= "\n##### 4. Historial de Bitácora / Respuestas\n";
                foreach ($item['bitacora_respuestas'] as $bit) {
                    $md .= "- **{$bit['fecha_emision']} ({$bit['emisor']}):** {$bit['asunto']} — {$bit['detalle_respuesta']}\n";
                }
            }
            if (!empty($item['documentos_relacionados_seguimiento'])) {
                $md .= "\n##### 5. Documentos Relacionados en Seguimiento (" . count($item['documentos_relacionados_seguimiento']) . ")\n";
                foreach ($item['documentos_relacionados_seguimiento'] as $idxSeg => $relS) {
                    if ($idxSeg < 7) {
                        $md .= "- **{$relS['tipo_documento']} N° {$relS['num_documento']}** ({$relS['fecha_documento']}, {$relS['estado_documento']}): {$relS['materia']} — [Descargar Documento](negocio/descargarDocumento.php?idDocumento={$relS['id_documento']})\n";
                    } else {
                        if ($idxSeg === 7) {
                            $md .= "\n**Resto de documentos relacionados (listado en texto):**\n";
                        }
                        $md .= "- {$relS['tipo_documento']} N° {$relS['num_documento']} ({$relS['fecha_documento']}, {$relS['estado_documento']}) — Materia: {$relS['materia']}\n";
                    }
                }
            }
            $md .= "\n";
        }
        return $md;
    }
    if ($esConsultaRepetidos && isset($dbResults['analisis_documentos_repetidos'])) {
        $rep = $dbResults['analisis_documentos_repetidos'];
        $modoEv = $rep['modo_evaluacion'] ?? 'contrato';
        $totEval = (int)($rep['total_documentos_evaluados_en_periodo'] ?? 0);
        $totRep = (int)($rep['total_documentos_repetidos_encontrados'] ?? 0);

        if ($modoEv === 'seguimiento') {
            $md = "### Análisis de Documentos de Seguimiento Repetidos — Contrato **{$userContractName}**\n\n";
            $md .= "- **Total de documentos del contrato evaluados:** **{$totEval}**\n";
            $md .= "- **Documentos con registros de seguimiento repetidos:** **{$totRep}**\n\n";
        } else {
            $md = "### Análisis de Documentos Repetidos Asociados al Contrato — Contrato **{$userContractName}**\n\n";
            $md .= "- **Total de documentos del contrato evaluados:** **{$totEval}**\n";
            $md .= "- **Total de documentos repetidos en el contrato (por N° Documento / Providencia):** **{$totRep}**\n\n";
        }

        if (empty($rep['documentos_repetidos'])) {
            $md .= ($modoEv === 'seguimiento')
                ? "No se detectaron documentos que tengan documentos de seguimiento repetidos en el contrato."
                : "No se detectaron documentos repetidos asociados al contrato que cumplan con los criterios consultados.";
            return $md;
        }

        $listaRep = $rep['documentos_repetidos'];
        foreach (array_slice($listaRep, 0, 7) as $idx => $d) {
            $n = $idx + 1;
            $md .= "{$n}. **{$d['tipo_documento']} N° {$d['num_documento']}** (ID: {$d['id_documento']} | Providencia: {$d['num_providencia']})\n";
            if ($modoEv === 'seguimiento' && !empty($d['documentos_relacionados_duplicados'])) {
                $md .= "   - **Documentos de seguimiento repetidos:** **{$d['documentos_relacionados_duplicados']}**\n";
            }
            $md .= "   - **Detalle de repetición:** **{$d['motivo_repeticion']}**\n";
            $md .= "   - **Materia:** {$d['materia']}\n";
            $md .= "   - **Fecha Documento:** {$d['fecha_documento']} | **Estado:** {$d['estado_documento']} | **Responsable:** {$d['responsable']}\n";
            $md .= "   - [Descargar Documento](negocio/descargarDocumento.php?idDocumento={$d['id_documento']})\n\n";
        }

        if (count($listaRep) > 7) {
            $md .= "#### Resto de los documentos repetidos encontrados (listado en texto)\n\n";
            foreach (array_slice($listaRep, 7) as $idxRest => $d) {
                $n = $idxRest + 8;
                $extraSeg = ($modoEv === 'seguimiento' && !empty($d['documentos_relacionados_duplicados']))
                    ? " | Seguimiento repetido: {$d['documentos_relacionados_duplicados']}"
                    : "";
                $md .= "{$n}. **{$d['tipo_documento']} N° {$d['num_documento']}** (Providencia: {$d['num_providencia']}) — Materia: {$d['materia']} | Fecha: {$d['fecha_documento']} | Estado: {$d['estado_documento']} | Responsable: {$d['responsable']} | {$d['motivo_repeticion']}{$extraSeg}\n";
            }
            $md .= "\n";
        }
        return $md;
    }

    if ($esConsultaRelacionados && isset($dbResults['analisis_documentos_relacionados'])) {
        $rel = $dbResults['analisis_documentos_relacionados'];
        if (empty($rel['documento_encontrado']) || empty($rel['analisis_por_documento'])) {
            $crit = $rel['criterio_buscado'] ?: ($filters['doc_numero'] ?? 'indicado');
            return "No se encontró ningún documento con el número o código **{$crit}** dentro del contrato **{$userContractName}**.";
        }
        $md = "### Análisis de Documentos Relacionados — Contrato **{$userContractName}**\n\n";
        foreach ($rel['analisis_por_documento'] as $item) {
            $doc = $item['documento_consultado'];
            $tieneRel = !empty($item['tiene_documentos_relacionados']);
            $cantUnicos = (int)($item['cantidad_documentos_relacionados_unicos'] ?? 0);
            $cantRegistros = (int)($item['cantidad_registros_documentos_relacionados'] ?? 0);
            $tieneDup = !empty($item['tiene_documentos_relacionados_duplicados']);

            $md .= "#### Documento consultado: **{$doc['tipo_documento']} N° {$doc['num_documento']}** (ID: {$doc['id_documento']})\n";
            $md .= "- **Materia:** {$doc['materia']}\n";
            $md .= "- **Estado:** {$doc['estado_documento']} | **Fecha Documento:** {$doc['fecha_documento']} | **Responsable:** {$doc['responsable']}\n";
            $md .= "- **¿Tiene documentos relacionados?:** **" . ($tieneRel ? "Sí" : "No") . "**\n";
            $md .= "- **Cantidad de documentos relacionados:** **{$cantUnicos} documento(s) único(s)** ({$cantRegistros} registro(s) vinculado(s) en seguimiento)\n";
            $md .= "- **¿Tiene documentos relacionados duplicados?:** **" . ($tieneDup ? "Sí (" . count($item['duplicados_detectados']) . " duplicado(s) detectado(s))" : "No tiene duplicados") . "**\n";

            if ($tieneDup && !empty($item['duplicados_detectados'])) {
                $md .= "\n**Detalle de duplicados detectados:**\n";
                foreach ($item['duplicados_detectados'] as $dup) {
                    $md .= "- **{$dup['tipo_documento']} N° {$dup['num_documento']}** — Materia: *{$dup['materia']}* | Repetido **{$dup['veces_repetido']} veces** ({$dup['tipo_duplicado']})\n";
                }
            }

            if (!empty($item['documentos_relacionados'])) {
                $listaRel = $item['documentos_relacionados'];
                $md .= "\n**Listado de documentos relacionados:**\n";
                foreach (array_slice($listaRel, 0, 7) as $idx => $dr) {
                    $n = $idx + 1;
                    $badgeDup = !empty($dr['es_duplicado']) ? " *(Duplicado: aparece {$dr['veces_en_seguimiento']} veces)*" : "";
                    $md .= "{$n}. **{$dr['tipo_documento']} N° {$dr['num_documento']}**{$badgeDup} — Materia: {$dr['materia']} | Fecha: {$dr['fecha_documento']} | Estado: {$dr['estado_documento']} | [Descargar Documento](negocio/descargarDocumento.php?idDocumento={$dr['id_documento']})\n";
                }
                if (count($listaRel) > 7) {
                    $md .= "\n**Resto de los documentos relacionados (listado en texto):**\n";
                    foreach (array_slice($listaRel, 7) as $idxRest => $dr) {
                        $n = $idxRest + 8;
                        $badgeDup = !empty($dr['es_duplicado']) ? " *(Duplicado: aparece {$dr['veces_en_seguimiento']} veces)*" : "";
                        $md .= "{$n}. **{$dr['tipo_documento']} N° {$dr['num_documento']}**{$badgeDup} — Materia: {$dr['materia']} | Fecha: {$dr['fecha_documento']} | Estado: {$dr['estado_documento']}\n";
                    }
                }
            }
            $md .= "\n";
        }
        return $md;
    }

    if ($esConsultaTiempos && isset($dbResults['metricas_tiempos_fechas_documentos'])) {
        $m = $dbResults['metricas_tiempos_fechas_documentos'];
        $g = $m['resumen_general_tiempos'] ?? [];
        $promCierre = $g['promedio_dias_pendiente_a_cerrado'] ?? 'S/I';
        $promDocCierre = $g['promedio_dias_fecha_documento_a_cierre'] ?? 'S/I';
        $promRecCierre = $g['promedio_dias_fecha_recepcion_a_cierre'] ?? 'S/I';
        $promDocRec = $g['promedio_dias_emision_a_recepcion'] ?? 'S/I';
        $promPlazo = $g['promedio_dias_plazo_otorgado'] ?? 'S/I';
        $promEspera = $g['promedio_dias_espera_pendientes_actuales'] ?? 'S/I';

        $md = "### Métricas de Tiempos entre Fechas de Documentos — Contrato **{$userContractName}**\n\n";
        $md .= "- **Total de documentos analizados:** " . ($g['total_documentos'] ?? 0) . " (" . ($g['cerrados'] ?? 0) . " cerrados y " . ($g['pendientes'] ?? 0) . " pendientes)\n";
        $md .= "- **Tiempo promedio en pasar de Pendiente a Cerrado:** **" . ($promCierre !== null ? "{$promCierre} días" : "Sin registros medibles") . "** (mínimo: " . ($g['minimo_dias_cierre'] ?? 0) . " días, máximo: " . ($g['maximo_dias_cierre'] ?? 0) . " días)\n";
        if ($promDocCierre !== null) {
            $md .= "- **Promedio desde Fecha del Documento hasta Cierre:** **{$promDocCierre} días**\n";
        }
        if ($promRecCierre !== null) {
            $md .= "- **Promedio desde Fecha de Recepción hasta Cierre:** **{$promRecCierre} días**\n";
        }
        if ($promDocRec !== null) {
            $md .= "- **Promedio desde Fecha de Emisión a Recepción:** **{$promDocRec} días**\n";
        }
        if ($promPlazo !== null) {
            $md .= "- **Plazo promedio asignado (`fecha_plazo`):** **{$promPlazo} días** (Cumplimiento en cerrados: " . ($g['porcentaje_cumplimiento_plazo'] ?? 0) . "%)\n";
        }
        if ($promEspera !== null) {
            $md .= "- **Tiempo promedio de espera en documentos actualmente Pendientes:** **{$promEspera} días**\n";
        }

        if (!empty($m['tiempos_por_responsable'])) {
            $md .= "\n#### Desglose de Tiempos por Responsable / Usuario\n";
            foreach (array_slice($m['tiempos_por_responsable'], 0, 15) as $r) {
                $pC = $r['promedio_dias_pendiente_a_cerrado'] !== null ? ($r['promedio_dias_pendiente_a_cerrado'] . " días") : "S/I";
                $pE = $r['promedio_dias_espera_pendientes_actuales'] !== null ? ($r['promedio_dias_espera_pendientes_actuales'] . " días") : "S/I";
                $md .= "- **{$r['grupo']}**: {$r['total_documentos']} docs ({$r['cerrados']} cerrados, {$r['pendientes']} pendientes) | Promedio cierre: **{$pC}** | Espera pendientes: **{$pE}**\n";
            }
        }
        return $md;
    }

    if ($esConsultaCantidad && isset($dbResults['estadisticas_historicas_documentos'])) {
        $e = $dbResults['estadisticas_historicas_documentos'];
        $md = "### Estadísticas y Clasificación de Documentos — Contrato **{$userContractName}**\n\n";
        if (!empty($e['filtros_aplicados_en_conteo'])) {
            $partesFiltro = [];
            foreach ($e['filtros_aplicados_en_conteo'] as $k => $v) {
                if ($v !== null && $v !== '') {
                    $partesFiltro[] = "`{$k}`: **{$v}**";
                }
            }
            if (!empty($partesFiltro)) {
                $md .= "- **Filtros aplicados:** " . implode(' | ', $partesFiltro) . "\n";
            }
        }
        $md .= "- **Total de documentos que cumplen los criterios:** **" . ($e['total_historico_documentos'] ?? 0) . "**\n";
        $md .= "- **Estado:** Pendientes: **" . ($e['desglose_por_estado']['Pendientes'] ?? 0) . "** | Cerrados: **" . ($e['desglose_por_estado']['Cerrados'] ?? 0) . "**\n";
        $md .= "- **Flujo:** Entrada: **" . ($e['desglose_por_flujo']['Entrada'] ?? 0) . "** | Salida: **" . ($e['desglose_por_flujo']['Salida'] ?? 0) . "**\n";

        $seccionesDesglose = [
            'desglose_por_tipo_documento' => ['titulo' => 'Clasificación por Tipo de Documento', 'campo' => 'tipo_documento'],
            'desglose_por_num_proceso'    => ['titulo' => 'Clasificación por Número/Código de Proceso', 'campo' => 'num_proceso'],
            'desglose_por_responsable'    => ['titulo' => 'Clasificación por Responsable', 'campo' => 'responsable'],
            'desglose_por_remitente'      => ['titulo' => 'Clasificación por Remitente', 'campo' => 'remitente'],
            'desglose_por_destinatario'   => ['titulo' => 'Clasificación por Destinatario', 'campo' => 'destinatario'],
            'desglose_por_materia'        => ['titulo' => 'Clasificación por Materia / Asunto (Top)', 'campo' => 'materia'],
            'desglose_por_anio'           => ['titulo' => 'Clasificación por Año', 'campo' => 'anio'],
        ];
        foreach ($seccionesDesglose as $keyDesglose => $meta) {
            if (!empty($e[$keyDesglose])) {
                $md .= "\n#### {$meta['titulo']}\n";
                foreach (array_slice($e[$keyDesglose], 0, 10) as $row) {
                    $label = $row[$meta['campo']] ?? 'Sin especificar';
                    $md .= "- **{$label}**: **{$row['total']}** docs (Pendientes: {$row['pendientes']}, Cerrados: {$row['cerrados']}, Entrada: {$row['entrada']}, Salida: {$row['salida']})\n";
                }
            }
        }
        return $md;
    }

    if (!empty($dbResults['documentos'])) {
        $listaDocs = $dbResults['documentos'];
        $md = "### Documentos encontrados en el contrato **{$userContractName}**\n\n";
        foreach (array_slice($listaDocs, 0, 7) as $idx => $d) {
            $num = $idx + 1;
            $md .= "{$num}. **{$d['tipo_documento']} N° {$d['num_documento']}** (Providencia: {$d['num_providencia']})\n";
            $md .= "   - **Materia:** {$d['materia']}\n";
            $md .= "   - **Fecha Documento:** {$d['fecha_documento']} | **Fecha Recepción:** {$d['fecha_recepcion']}\n";
            $md .= "   - **Estado:** {$d['estado_documento']} | **Responsable:** {$d['responsable']}\n";
            $md .= "   - [Descargar Documento](negocio/descargarDocumento.php?idDocumento={$d['id_documento']})\n\n";
        }
        if (count($listaDocs) > 7) {
            $md .= "#### Resto de los documentos encontrados (listado en texto)\n\n";
            foreach (array_slice($listaDocs, 7) as $idxRest => $d) {
                $num = $idxRest + 8;
                $md .= "{$num}. **{$d['tipo_documento']} N° {$d['num_documento']}** (Providencia: {$d['num_providencia']}) — Materia: {$d['materia']} | Fecha: {$d['fecha_documento']} | Estado: {$d['estado_documento']} | Responsable: {$d['responsable']}\n";
            }
            $md .= "\n";
        }
        return $md;
    }

    return "No se encontraron registros que coincidan con los criterios consultados en el contrato **{$userContractName}**.";
}

/**
 * Garantiza que en cualquier listado de documentos entregado en la respuesta:
 * 1. Solo los primeros 7 documentos tengan los botones de Descargar y Detalle (`[Descargar Documento](negocio/descargarDocumento.php?idDocumento=...)`).
 * 2. Si la consulta arrojó más de 7 documentos, del 8° documento en adelante se listen únicamente como texto simple sin botones.
 */
function aplicarReglaMaximoSieteDocumentosConDetalle($textoRespuesta, $dbResults, $esConsultaRelacionados = false, $esConsultaRepetidos = false, $esConsultaContenidoArchivo = false, $esConsultaTiempos = false, $esConsultaCantidad = false) {
    if ($esConsultaTiempos || $esConsultaCantidad) {
        return $textoRespuesta;
    }

    // 1. Limitar enlaces `[...](negocio/descargarDocumento.php?idDocumento=...)` a un máximo de 7 ocurrencias
    $contadorLinks = 0;
    $textoRespuesta = preg_replace_callback(
        '/(?:\s*[—\-|•]*\s*)?\[([^\]]*)\]\((negocio\/descargarDocumento\.php\?idDocumento=\d+)\)/i',
        function ($m) use (&$contadorLinks) {
            $contadorLinks++;
            if ($contadorLinks <= 7) {
                return $m[0];
            }
            return '';
        },
        (string)$textoRespuesta
    );

    // 2. Determinar si hay documentos adicionales (del 8° en adelante) que deban listarse como texto si no fueron incluidos por el modelo
    $listaCompleta = [];
    $modoRep = 'contrato';
    if ($esConsultaRepetidos && !empty($dbResults['analisis_documentos_repetidos']['documentos_repetidos'])) {
        $listaCompleta = $dbResults['analisis_documentos_repetidos']['documentos_repetidos'];
        $modoRep = $dbResults['analisis_documentos_repetidos']['modo_evaluacion'] ?? 'contrato';
    } elseif (!$esConsultaRelacionados && !$esConsultaContenidoArchivo && !empty($dbResults['documentos']) && is_array($dbResults['documentos'])) {
        $listaCompleta = $dbResults['documentos'];
    }

    if (count($listaCompleta) > 7) {
        $restantes = array_slice($listaCompleta, 7, 43); // Mostrar hasta 50 en total en el chat
        $lineasFaltantes = [];
        foreach ($restantes as $idxR => $docR) {
            $numOrden = $idxR + 8;
            $numDocStr = trim((string)($docR['num_documento'] ?? ''));
            // Verificar si este documento ya fue listado en el texto por Gemini
            if ($numDocStr !== '' && mb_stripos($textoRespuesta, $numDocStr, 0, 'UTF-8') !== false) {
                continue;
            }
            $tipo = $docR['tipo_documento'] ?? 'Documento';
            $prov = !empty($docR['num_providencia']) ? " (Providencia: {$docR['num_providencia']})" : "";
            $mat  = $docR['materia'] ?? 'Sin materia';
            $fec  = $docR['fecha_documento'] ?? ($docR['fecha_recepcion'] ?? 'S/F');
            $est  = $docR['estado_documento'] ?? '';
            $resp = $docR['responsable'] ?? 'Sin asignar';

            if ($esConsultaRepetidos) {
                $motivo = !empty($docR['motivo_repeticion']) ? " | {$docR['motivo_repeticion']}" : "";
                $segDup = ($modoRep === 'seguimiento' && !empty($docR['documentos_relacionados_duplicados']))
                    ? " | Seguimiento repetido: {$docR['documentos_relacionados_duplicados']}"
                    : "";
                $lineasFaltantes[] = "{$numOrden}. **{$tipo} N° {$numDocStr}**{$prov} — Materia: {$mat} | Fecha: {$fec} | Estado: {$est} | Responsable: {$resp}{$motivo}{$segDup}";
            } else {
                $lineasFaltantes[] = "{$numOrden}. **{$tipo} N° {$numDocStr}**{$prov} — Materia: {$mat} | Fecha: {$fec} | Estado: {$est} | Responsable: {$resp}";
            }
        }

        if (!empty($lineasFaltantes)) {
            $textoRespuesta = rtrim($textoRespuesta)
                . "\n\n#### Resto de los documentos encontrados (listado en texto)\n\n"
                . implode("\n", $lineasFaltantes);
        }
    }

    return $textoRespuesta;
}

/**
 * Extrae las columnas específicas que el usuario pidió incluir en la planilla Excel (si especificó campos concretos).
 * Si el usuario no restringió columnas individuales, retorna un array vacío para usar solo las columnas propias de la consulta realizada.
 */
function extraerColumnasExcelSolicitadas($query, $columnasIA = []) {
    $mapaSinonimos = [
        'id_documento'       => ['id_documento', 'id documento', 'id del documento'],
        'num_documento'      => ['num_documento', 'numero de documento', 'nro de documento', 'n° de documento', 'n° documento', 'numero documento', 'codigo de documento', 'folio'],
        'tipo_documento'     => ['tipo_documento', 'tipo de documento', 'tipo documento', 'tipo'],
        'num_providencia'    => ['num_providencia', 'numero de providencia', 'nro de providencia', 'n° providencia', 'providencia'],
        'num_proceso'        => ['num_proceso', 'numero de proceso', 'codigo de proceso', 'n° de proceso', 'n° proceso', 'proceso'],
        'materia'            => ['materia', 'asunto', 'materias', 'descripcion'],
        'fecha_documento'    => ['fecha_documento', 'fecha de documento', 'fecha del documento', 'fecha documento', 'fecha de emision', 'fecha emision'],
        'fecha_recepcion'    => ['fecha_recepcion', 'fecha de recepcion', 'fecha recepcion'],
        'fecha_plazo'        => ['fecha_plazo', 'fecha de plazo', 'fecha plazo', 'plazo'],
        'fecha_cierre'       => ['fecha_cierre', 'fecha de cierre', 'fecha cierre'],
        'dias_en_cerrar'     => ['dias_en_cerrar', 'dias en cerrar', 'dias de cierre', 'demora', 'tiempo de cierre'],
        'estado_documento'   => ['estado_documento', 'estado del documento', 'estado documento', 'estado'],
        'flujo_documento'    => ['flujo_documento', 'flujo del documento', 'flujo'],
        'responsable'        => ['responsable', 'responsables', 'usuario asignado', 'encargado'],
        'correo_responsable' => ['correo_responsable', 'correo del responsable', 'email del responsable', 'correo responsable'],
        'remitente'          => ['remitente', 'remitentes', 'enviado por', 'emisor'],
        'cargo_remitente'    => ['cargo_remitente', 'cargo del remitente', 'cargo remitente'],
        'destinatario'       => ['destinatario', 'destinatarios', 'dirigido a', 'receptor'],
        'cargo_destinatario' => ['cargo_destinatario', 'cargo del destinatario', 'cargo destinatario'],
        'nombre_subcontrato' => ['nombre_subcontrato', 'subcontrato'],
        'antecedente'        => ['antecedente', 'antecedentes'],
        'incluye'            => ['incluye'],
        'comentario'         => ['comentario', 'comentarios', 'observaciones', 'observacion'],
        'nombre_documento'   => ['nombre_documento', 'nombre de archivo', 'archivo']
    ];

    $seleccionadas = [];

    // 1. Verificar columnas extraídas por Paso 1 de la IA
    if (is_array($columnasIA) && !empty($columnasIA)) {
        foreach ($columnasIA as $colItem) {
            $colNorm = normalizarTexto((string)$colItem);
            if ($colNorm === '') continue;
            if (isset($mapaSinonimos[$colNorm])) {
                $seleccionadas[$colNorm] = true;
                continue;
            }
            foreach ($mapaSinonimos as $keyCanonical => $variantes) {
                foreach ($variantes as $v) {
                    if ($colNorm === normalizarTexto($v)) {
                        $seleccionadas[$keyCanonical] = true;
                        break 2;
                    }
                }
            }
        }
    }

    // 2. Si el usuario menciona explícitamente en la frase qué columnas/campos incluir (ej. "solo con...", "con las columnas...", "que contenga...", "mostrando...")
    $qNorm = normalizarTexto($query);
    if (preg_match('/\b(columnas?|campos?|solo\s+con|[uú]nicamente\s+con|solamente\s+con|que\s+contenga|que\s+incluya|incluyendo\s+solo|mostrando\s+solo)\b/iu', $query)) {
        foreach ($mapaSinonimos as $keyCanonical => $variantes) {
            foreach ($variantes as $v) {
                $vNorm = normalizarTexto($v);
                if ($vNorm !== '' && mb_strlen($vNorm) >= 5 && preg_match('/\b' . preg_quote($vNorm, '/') . '\b/u', $qNorm)) {
                    $seleccionadas[$keyCanonical] = true;
                    break;
                }
            }
        }
    }

    return array_keys($seleccionadas);
}

/**
 * Construye los encabezados y filas de la planilla Excel conteniendo ÚNICAMENTE la información
 * que el usuario solicitó en su consulta.
 */
function construirDatosExcelSolicitados($dbResults, $intent, $filters, $esConsultaRelacionados, $esConsultaTiempos, $esConsultaCantidad, $query, $userContractName) {
    $etiquetasColumnasDoc = [
        'id_documento'       => 'ID Documento',
        'num_documento'      => 'N° Documento',
        'tipo_documento'     => 'Tipo Documento',
        'num_providencia'    => 'N° Providencia',
        'num_proceso'        => 'N° / Código Proceso',
        'materia'            => 'Materia',
        'fecha_documento'    => 'Fecha Documento',
        'fecha_recepcion'    => 'Fecha Recepción',
        'fecha_plazo'        => 'Fecha Plazo',
        'fecha_cierre'       => 'Fecha Cierre',
        'dias_en_cerrar'     => 'Días en Cerrar',
        'estado_documento'   => 'Estado',
        'flujo_documento'    => 'Flujo',
        'responsable'        => 'Responsable',
        'correo_responsable' => 'Correo Responsable',
        'remitente'          => 'Remitente',
        'cargo_remitente'    => 'Cargo Remitente',
        'destinatario'       => 'Destinatario',
        'cargo_destinatario' => 'Cargo Destinatario',
        'nombre_subcontrato' => 'Subcontrato',
        'antecedente'        => 'Antecedente',
        'incluye'            => 'Incluye',
        'comentario'         => 'Comentario',
        'nombre_documento'   => 'Archivo'
    ];

    $columnasPersonalizadas = extraerColumnasExcelSolicitadas($query, $filters['columnas_excel'] ?? []);

    // CASO 0: Consulta de Documentos Repetidos / Duplicados (diferenciando entre 'contrato' y 'seguimiento')
    if (isset($dbResults['analisis_documentos_repetidos'])) {
        $modoEv = $dbResults['analisis_documentos_repetidos']['modo_evaluacion'] ?? 'contrato';
        $docsRep = $dbResults['analisis_documentos_repetidos']['documentos_repetidos'] ?? [];
        $colsUsar = !empty($columnasPersonalizadas)
            ? $columnasPersonalizadas
            : [
                'id_documento',
                'num_documento',
                'tipo_documento',
                'num_providencia',
                'num_proceso',
                'materia',
                'fecha_documento',
                'fecha_recepcion',
                'estado_documento',
                'flujo_documento',
                'responsable',
                'remitente',
                'destinatario'
            ];

        $headers = [];
        foreach ($colsUsar as $ck) {
            $headers[] = $etiquetasColumnasDoc[$ck] ?? $ck;
        }
        if ($modoEv === 'seguimiento') {
            $headers[] = 'N° Seguimiento';
            $headers[] = 'Documentos de Seguimiento Repetidos';
            $headers[] = 'Veces Repetido en Seguimiento';
            $headers[] = 'Detalle Repetición en Seguimiento';
        } else {
            $headers[] = 'Veces Repetido en Contrato';
            $headers[] = 'Motivo de Repetición (N° Doc / Providencia)';
            $headers[] = 'IDs Documentos Repetidos';
        }

        $rows = [];
        foreach ($docsRep as $d) {
            $fila = [];
            foreach ($colsUsar as $ck) {
                $fila[] = $d[$ck] ?? '';
            }
            if ($modoEv === 'seguimiento') {
                $fila[] = $d['numeros_seguimiento'] ?? '';
                $fila[] = $d['documentos_relacionados_duplicados'] ?? '';
                $fila[] = (int)($d['veces_repetido'] ?? 2);
                $fila[] = $d['motivo_repeticion'] ?? '';
            } else {
                $fila[] = (int)($d['veces_repetido'] ?? 2);
                $fila[] = $d['motivo_repeticion'] ?? '';
                $fila[] = $d['ids_asociados'] ?? '';
            }
            $rows[] = $fila;
        }

        return [
            'sheet_title' => ($modoEv === 'seguimiento') ? 'Seguimiento Repetidos' : 'Docs Repetidos Contrato',
            'filename'    => ($modoEv === 'seguimiento')
                ? ('Documentos_Seguimiento_Repetidos_SGD_' . date('Ymd_His') . '.xlsx')
                : ('Documentos_Repetidos_Contrato_SGD_' . date('Ymd_His') . '.xlsx'),
            'headers'     => $headers,
            'rows'        => $rows
        ];
    }

    // CASO 1: Consulta de Documentos Relacionados / Seguimiento / Duplicados
    if ($esConsultaRelacionados && !empty($dbResults['analisis_documentos_relacionados']['analisis_por_documento'])) {
        $analisis = $dbResults['analisis_documentos_relacionados']['analisis_por_documento'];
        $pideSoloDuplicados = (bool)preg_match('/\bduplicad[oa]s?\b/iu', $query);

        if ($pideSoloDuplicados) {
            $headers = ['Documento Consultado', 'Tipo Doc. Relacionado', 'N° Doc. Relacionado', 'Materia', 'Fecha Documento', 'Estado', 'Responsable', 'Veces Repetido', 'Motivo Duplicado'];
            $rows = [];
            foreach ($analisis as $item) {
                $baseDoc = $item['documento_consultado']['num_documento'] ?? '';
                foreach ($item['duplicados_detectados'] ?? [] as $dup) {
                    $rows[] = [
                        $baseDoc,
                        $dup['tipo_documento'] ?? '',
                        $dup['num_documento'] ?? '',
                        $dup['materia'] ?? '',
                        $dup['fecha_documento'] ?? '',
                        $dup['estado_documento'] ?? '',
                        $dup['responsable'] ?? '',
                        (int)($dup['veces_repetido'] ?? 0),
                        $dup['tipo_duplicado'] ?? ''
                    ];
                }
            }
            return [
                'sheet_title' => 'Relacionados Duplicados',
                'filename'    => 'Documentos_Relacionados_Duplicados_' . date('Ymd_His') . '.xlsx',
                'headers'     => $headers,
                'rows'        => $rows
            ];
        }

        $colsUsar = !empty($columnasPersonalizadas)
            ? $columnasPersonalizadas
            : ['tipo_documento', 'num_documento', 'num_providencia', 'num_proceso', 'materia', 'fecha_documento', 'estado_documento', 'flujo_documento', 'responsable', 'remitente', 'destinatario'];

        $headers = ['Documento Consultado'];
        foreach ($colsUsar as $ck) {
            $headers[] = $etiquetasColumnasDoc[$ck] ?? $ck;
        }
        $headers[] = 'Veces en Seguimiento';
        $headers[] = '¿Es Duplicado?';

        $rows = [];
        foreach ($analisis as $item) {
            $baseLabel = ($item['documento_consultado']['tipo_documento'] ?? '') . ' ' . ($item['documento_consultado']['num_documento'] ?? '');
            foreach ($item['documentos_relacionados'] ?? [] as $dr) {
                $fila = [trim($baseLabel)];
                foreach ($colsUsar as $ck) {
                    $fila[] = $dr[$ck] ?? '';
                }
                $fila[] = (int)($dr['veces_en_seguimiento'] ?? 1);
                $fila[] = !empty($dr['es_duplicado']) ? 'Sí' : 'No';
                $rows[] = $fila;
            }
        }
        return [
            'sheet_title' => 'Documentos Relacionados',
            'filename'    => 'Documentos_Relacionados_' . date('Ymd_His') . '.xlsx',
            'headers'     => $headers,
            'rows'        => $rows
        ];
    }

    // CASO 2: Consulta de Tiempos / Demora entre Fechas
    if ($esConsultaTiempos && isset($dbResults['metricas_tiempos_fechas_documentos'])) {
        $m = $dbResults['metricas_tiempos_fechas_documentos'];
        $clasif = $filters['clasificar_por'] ?? null;
        $mapaKeyTiempos = [
            'responsable'    => ['key' => 'tiempos_por_responsable',    'label' => 'Responsable / Usuario', 'sheet' => 'Tiempos por Responsable'],
            'tipo_documento' => ['key' => 'tiempos_por_tipo_documento', 'label' => 'Tipo de Documento',     'sheet' => 'Tiempos por Tipo'],
            'flujo'          => ['key' => 'tiempos_por_flujo',          'label' => 'Flujo',                 'sheet' => 'Tiempos por Flujo'],
            'subcontrato'    => ['key' => 'tiempos_por_subcontrato',    'label' => 'Subcontrato',           'sheet' => 'Tiempos por Subcontrato'],
            'anio'           => ['key' => 'tiempos_por_anio',           'label' => 'Año',                   'sheet' => 'Tiempos por Año'],
            'mes'            => ['key' => 'tiempos_por_mes',            'label' => 'Mes',                   'sheet' => 'Tiempos por Mes'],
            'remitente'      => ['key' => 'tiempos_por_remitente',      'label' => 'Remitente',             'sheet' => 'Tiempos por Remitente'],
        ];

        if ($clasif && isset($mapaKeyTiempos[$clasif]) && !empty($m[$mapaKeyTiempos[$clasif]['key']])) {
            $metaT = $mapaKeyTiempos[$clasif];
            $headers = [
                $metaT['label'],
                'Total Documentos',
                'Cerrados',
                'Pendientes',
                'Promedio Días (Pendiente a Cerrado)',
                'Mínimo Días Cierre',
                'Máximo Días Cierre',
                'Promedio Días (Fecha Doc. a Cierre)',
                'Promedio Días (Recepción a Cierre)',
                'Promedio Días Espera (Pendientes)',
                '% Cumplimiento Plazo'
            ];
            $rows = [];
            foreach ($m[$metaT['key']] as $r) {
                $rows[] = [
                    $r['grupo'] ?? '',
                    (int)($r['total_documentos'] ?? 0),
                    (int)($r['cerrados'] ?? 0),
                    (int)($r['pendientes'] ?? 0),
                    $r['promedio_dias_pendiente_a_cerrado'] ?? 'S/I',
                    $r['minimo_dias_cierre'] ?? 'S/I',
                    $r['maximo_dias_cierre'] ?? 'S/I',
                    $r['promedio_dias_fecha_documento_a_cierre'] ?? 'S/I',
                    $r['promedio_dias_fecha_recepcion_a_cierre'] ?? 'S/I',
                    $r['promedio_dias_espera_pendientes_actuales'] ?? 'S/I',
                    $r['porcentaje_cumplimiento_plazo'] !== null ? ($r['porcentaje_cumplimiento_plazo'] . '%') : 'S/I'
                ];
            }
            return [
                'sheet_title' => $metaT['sheet'],
                'filename'    => 'Metricas_Tiempos_' . ucfirst($clasif) . '_' . date('Ymd_His') . '.xlsx',
                'headers'     => $headers,
                'rows'        => $rows
            ];
        }

        // Si no pidió clasificar por un grupo específico, entregar el resumen general de tiempos solicitados
        $g = $m['resumen_general_tiempos'] ?? [];
        $headers = ['Métrica de Tiempo / Plazo', 'Valor'];
        $rows = [
            ['Total de documentos analizados', (int)($g['total_documentos'] ?? 0)],
            ['Documentos Cerrados', (int)($g['cerrados'] ?? 0)],
            ['Documentos Pendientes', (int)($g['pendientes'] ?? 0)],
            ['Promedio días de Pendiente a Cerrado', ($g['promedio_dias_pendiente_a_cerrado'] !== null ? $g['promedio_dias_pendiente_a_cerrado'] . ' días' : 'S/I')],
            ['Mínimo días en cerrar', ($g['minimo_dias_cierre'] ?? 'S/I')],
            ['Máximo días en cerrar', ($g['maximo_dias_cierre'] ?? 'S/I')],
            ['Promedio días desde Fecha Documento a Cierre', ($g['promedio_dias_fecha_documento_a_cierre'] !== null ? $g['promedio_dias_fecha_documento_a_cierre'] . ' días' : 'S/I')],
            ['Promedio días desde Fecha Recepción a Cierre', ($g['promedio_dias_fecha_recepcion_a_cierre'] !== null ? $g['promedio_dias_fecha_recepcion_a_cierre'] . ' días' : 'S/I')],
            ['Promedio días entre Emisión y Recepción', ($g['promedio_dias_emision_a_recepcion'] !== null ? $g['promedio_dias_emision_a_recepcion'] . ' días' : 'S/I')],
            ['Plazo promedio asignado (fecha_plazo)', ($g['promedio_dias_plazo_otorgado'] !== null ? $g['promedio_dias_plazo_otorgado'] . ' días' : 'S/I')],
            ['Porcentaje cumplimiento de plazo en cerrados', ($g['porcentaje_cumplimiento_plazo'] !== null ? $g['porcentaje_cumplimiento_plazo'] . '%' : 'S/I')],
            ['Promedio días espera actual en Pendientes', ($g['promedio_dias_espera_pendientes_actuales'] !== null ? $g['promedio_dias_espera_pendientes_actuales'] . ' días' : 'S/I')]
        ];
        return [
            'sheet_title' => 'Métricas de Tiempos',
            'filename'    => 'Metricas_Tiempos_Documentos_' . date('Ymd_His') . '.xlsx',
            'headers'     => $headers,
            'rows'        => $rows
        ];
    }

    // CASO 3: Consulta de Cantidades / Conteos / Clasificaciones por Atributos
    if ($esConsultaCantidad && isset($dbResults['estadisticas_historicas_documentos'])) {
        $e = $dbResults['estadisticas_historicas_documentos'];
        $clasif = $filters['clasificar_por'] ?? null;

        // Si el usuario filtró por un atributo específico (ej. num_proceso) y no especificó otra clasificación, o especificó clasificar_por:
        $mapaDesgloses = [
            'num_proceso'    => ['key' => 'desglose_por_num_proceso',     'campo' => 'num_proceso',     'label' => 'N° / Código de Proceso', 'sheet' => 'Por Código Proceso'],
            'tipo_documento' => ['key' => 'desglose_por_tipo_documento',  'campo' => 'tipo_documento',  'label' => 'Tipo de Documento',      'sheet' => 'Por Tipo Documento'],
            'responsable'    => ['key' => 'desglose_por_responsable',     'campo' => 'responsable',     'label' => 'Responsable',            'sheet' => 'Por Responsable'],
            'remitente'      => ['key' => 'desglose_por_remitente',       'campo' => 'remitente',       'label' => 'Remitente',              'sheet' => 'Por Remitente'],
            'destinatario'   => ['key' => 'desglose_por_destinatario',    'campo' => 'destinatario',    'label' => 'Destinatario',           'sheet' => 'Por Destinatario'],
            'materia'        => ['key' => 'desglose_por_materia',         'campo' => 'materia',         'label' => 'Materia / Asunto',       'sheet' => 'Por Materia'],
            'fecha'          => ['key' => 'desglose_por_fecha_documento', 'campo' => 'fecha_documento', 'label' => 'Fecha Documento',        'sheet' => 'Por Fecha'],
            'mes'            => ['key' => 'desglose_por_mes',             'campo' => 'periodo',         'label' => 'Mes / Período',          'sheet' => 'Por Mes'],
            'anio'           => ['key' => 'desglose_por_anio',            'campo' => 'anio',            'label' => 'Año',                    'sheet' => 'Por Año'],
            'subcontrato'    => ['key' => 'desglose_por_subcontrato',     'campo' => 'subcontrato',     'label' => 'Subcontrato',            'sheet' => 'Por Subcontrato']
        ];

        if ($clasif === 'estado') {
            return [
                'sheet_title' => 'Por Estado',
                'filename'    => 'Conteo_Por_Estado_' . date('Ymd_His') . '.xlsx',
                'headers'     => ['Estado del Documento', 'Cantidad de Documentos'],
                'rows'        => [
                    ['Pendientes', (int)($e['desglose_por_estado']['Pendientes'] ?? 0)],
                    ['Cerrados',   (int)($e['desglose_por_estado']['Cerrados'] ?? 0)],
                    ['Total',      (int)($e['total_historico_documentos'] ?? 0)]
                ]
            ];
        }

        if ($clasif === 'flujo') {
            return [
                'sheet_title' => 'Por Flujo',
                'filename'    => 'Conteo_Por_Flujo_' . date('Ymd_His') . '.xlsx',
                'headers'     => ['Flujo del Documento', 'Cantidad de Documentos'],
                'rows'        => [
                    ['Entrada', (int)($e['desglose_por_flujo']['Entrada'] ?? 0)],
                    ['Salida',  (int)($e['desglose_por_flujo']['Salida'] ?? 0)],
                    ['Total',   (int)($e['total_historico_documentos'] ?? 0)]
                ]
            ];
        }

        // Si no indicó clasificar_por pero filtró por num_proceso puntual, mostrar el desglose de ese código de proceso
        if (!$clasif && !empty($filters['num_proceso'])) {
            $clasif = 'num_proceso';
        }

        if ($clasif && isset($mapaDesgloses[$clasif]) && isset($e[$mapaDesgloses[$clasif]['key']])) {
            $metaD = $mapaDesgloses[$clasif];
            // Verificar si el usuario pidió solo pendientes o solo cerrados en su conteo
            $soloPend = ($filters['estado_doc'] ?? null) === 1;
            $soloCerr = ($filters['estado_doc'] ?? null) === 2;

            if ($soloPend) {
                $headers = [$metaD['label'], 'Documentos Pendientes', 'Entrada', 'Salida'];
            } elseif ($soloCerr) {
                $headers = [$metaD['label'], 'Documentos Cerrados', 'Entrada', 'Salida'];
            } else {
                $headers = [$metaD['label'], 'Total Documentos', 'Pendientes', 'Cerrados', 'Entrada', 'Salida'];
            }

            $rows = [];
            foreach ($e[$metaD['key']] as $item) {
                $valGrupo = $item[$metaD['campo']] ?? '';
                if ($soloPend) {
                    $rows[] = [$valGrupo, (int)($item['pendientes'] ?? 0), (int)($item['entrada'] ?? 0), (int)($item['salida'] ?? 0)];
                } elseif ($soloCerr) {
                    $rows[] = [$valGrupo, (int)($item['cerrados'] ?? 0), (int)($item['entrada'] ?? 0), (int)($item['salida'] ?? 0)];
                } else {
                    $rows[] = [
                        $valGrupo,
                        (int)($item['total'] ?? 0),
                        (int)($item['pendientes'] ?? 0),
                        (int)($item['cerrados'] ?? 0),
                        (int)($item['entrada'] ?? 0),
                        (int)($item['salida'] ?? 0)
                    ];
                }
            }
            return [
                'sheet_title' => $metaD['sheet'],
                'filename'    => 'Clasificacion_' . ucfirst($clasif) . '_' . date('Ymd_His') . '.xlsx',
                'headers'     => $headers,
                'rows'        => $rows
            ];
        }

        // Conteo general solicitado
        $headers = ['Indicador Solicitado', 'Cantidad'];
        $rows = [
            ['Total Documentos que cumplen el criterio', (int)($e['total_historico_documentos'] ?? 0)],
            ['Pendientes', (int)($e['desglose_por_estado']['Pendientes'] ?? 0)],
            ['Cerrados',   (int)($e['desglose_por_estado']['Cerrados'] ?? 0)],
            ['Flujo Entrada', (int)($e['desglose_por_flujo']['Entrada'] ?? 0)],
            ['Flujo Salida',  (int)($e['desglose_por_flujo']['Salida'] ?? 0)]
        ];
        return [
            'sheet_title' => 'Resumen Conteo',
            'filename'    => 'Resumen_Conteo_Documentos_' . date('Ymd_His') . '.xlsx',
            'headers'     => $headers,
            'rows'        => $rows
        ];
    }

    // CASO 4: Consulta de Usuarios del Contrato ($intent === 'user')
    if ($intent === 'user' && isset($dbResults['usuarios'])) {
        $headers = ['Nombre Completo', 'Nombre Usuario', 'Correo Electrónico', 'Perfil / Rol', 'Contrato'];
        $rows = [];
        foreach ($dbResults['usuarios'] as $u) {
            $rows[] = [
                $u['nombre_completo'] ?? '',
                $u['nombre_usuario'] ?? '',
                $u['correo'] ?? '',
                $u['nombre_perfil'] ?? '',
                $u['nombre_contrato'] ?? $userContractName
            ];
        }
        return [
            'sheet_title' => 'Usuarios del Contrato',
            'filename'    => 'Usuarios_Contrato_' . date('Ymd_His') . '.xlsx',
            'headers'     => $headers,
            'rows'        => $rows
        ];
    }

    // CASO 5: Consulta de Contrato / Subcontratos ($intent === 'contract')
    if ($intent === 'contract' && isset($dbResults['subcontratos'])) {
        $headers = ['ID Subcontrato', 'Nombre Subcontrato', 'Contrato Principal'];
        $rows = [];
        foreach ($dbResults['subcontratos'] as $sc) {
            $rows[] = [
                (int)($sc['id_subcontrato'] ?? 0),
                $sc['nombre_subcontrato'] ?? '',
                $userContractName
            ];
        }
        return [
            'sheet_title' => 'Subcontratos',
            'filename'    => 'Subcontratos_' . date('Ymd_His') . '.xlsx',
            'headers'     => $headers,
            'rows'        => $rows
        ];
    }

    // CASO 6: Listado de Documentos solicitados por el usuario ($dbResults['documentos'])
    $docs = $dbResults['documentos'] ?? [];
    if (!empty($columnasPersonalizadas)) {
        $colsUsar = $columnasPersonalizadas;
    } else {
        $colsUsar = [
            'num_documento',
            'tipo_documento',
            'num_providencia',
            'num_proceso',
            'materia',
            'fecha_documento',
            'fecha_recepcion',
            'fecha_plazo',
            'fecha_cierre',
            'estado_documento',
            'flujo_documento',
            'responsable',
            'remitente',
            'destinatario'
        ];
    }

    $headers = [];
    foreach ($colsUsar as $ck) {
        $headers[] = $etiquetasColumnasDoc[$ck] ?? $ck;
    }

    $rows = [];
    foreach ($docs as $d) {
        $fila = [];
        foreach ($colsUsar as $ck) {
            $fila[] = $d[$ck] ?? '';
        }
        $rows[] = $fila;
    }

    return [
        'sheet_title' => 'Documentos Solicitados',
        'filename'    => 'Documentos_SGD_' . date('Ymd_His') . '.xlsx',
        'headers'     => $headers,
        'rows'        => $rows
    ];
}

$fechaActual = date('Y-m-d');
$anioActual = date('Y');
$listaRespTexto = implode(', ', $nombresResponsablesContrato);
$queryLower = mb_strtolower($query, 'UTF-8');

// Detectar de forma determinista en PHP si el usuario solicita generar/exportar/descargar una planilla Excel
$generarExcelPre = (bool)preg_match(
    '/\b(excel|planillas?|xlsx|xls|hojas?\s+de\s+c[aá]lculo|exporta(?:r|me|lo|los|las)?)\b/iu',
    $query
);

// Detectar de forma determinista en PHP si el usuario consulta por documentos repetidos o duplicados (incluyendo errores comunes de tipeo como "respetidos" o "repeditos")
$mencionaRepeticionPre = (bool)preg_match(
    '/\b(re(?:s)?petid[oa]s?|repedit[oa]s?|duplicad[oa]s?|est[aá]n?\s+re(?:s)?petid[oa]s?|est[aá]n?\s+duplicad[oa]s?|se\s+repiten?|m[aá]s\s+de\s+una\s+vez)\b/iu',
    $query
);

// Detectar si el usuario menciona seguimiento o documentos relacionados en su consulta
$mencionaSeguimientoORelacionadosPre = (bool)preg_match(
    '/\b(seguimientos?|documentos?\s+de\s+seguimiento|documentos?\s+relacionad[oa]s?|en\s+el\s+seguimiento)\b/iu',
    $query
);

// Determinar el modo de repetición cuando se consulta por documentos repetidos/duplicados a nivel de contrato o período:
// - 'seguimiento': cuando pide documentos de seguimiento repetidos en los documentos del contrato (ej. "Podrías decirme si hay documentos de seguimiento respetidos en los documentos de mi contrato")
// - 'contrato': cuando pide los documentos repetidos asociados directamente al contrato (ej. "Podrías decirme los documentos repetidos del contrato")
$modoRepeticionPre = ($mencionaRepeticionPre && $mencionaSeguimientoORelacionadosPre) ? 'seguimiento' : 'contrato';

// Detectar de forma determinista en PHP si el usuario consulta por documentos relacionados o seguimiento de un documento específico
$esConsultaRelacionadosPre = (bool)preg_match(
    '/\b(documentos?\s+relacionad[oa]s?|relacionad[oa]s?\s+(?:con|a|de|al|tiene|duplicad[oa]s?)|tiene\s+(?:documentos?\s+)?relacionad[oa]s?|duplicad[oa]s?\s+(?:en\s+)?(?:el\s+)?(?:seguimiento|relacionad[oa]s?)|seguimiento\s+(?:de|del|para)\s+(?:la\s+|el\s+)?(?:carta|oficio|informe|documento|memor[aá]ndum|resoluci[oó]n))\b/iu',
    $query
);

// Detectar de forma determinista en PHP si el usuario consulta por documentos repetidos o duplicados en general (ya sea del contrato o de seguimiento del contrato)
$esConsultaRepetidosPre = $mencionaRepeticionPre;

// Detectar de forma determinista en PHP si el usuario pide un resumen, síntesis, lectura, lo que informa, explicación o información de un documento y sus archivos asociados
$esConsultaContenidoArchivoPre = !$esConsultaRelacionadosPre && !$esConsultaRepetidosPre && (bool)preg_match(
    '/\b(resumen(?:to)?|resumir|res[uú]me(?:me|lo|la)?|s[ií]ntesis|sintetiza(?:r)?|de\s+qu[eé]\s+trata|qu[eé]\s+trata|qu[eé]\s+dice|qu[eé]\s+informa|lo\s+que\s+informa|informa\s+(?:el|la)\s+(?:documento|carta|oficio|informe)|qu[eé]\s+contiene|contenido\s+(?:de|del)|detalle\s+(?:de|del)\s+(?:el\s+|la\s+)?(?:documento|carta|oficio|informe)|explica(?:r|me)?\s+(?:el|la)|analiza(?:r)?\s+(?:el|la)|lee(?:r)?\s+(?:el|la)|archivos?\s+asociad[oa]s?|adjuntos?\s+(?:de|del|asociad[oa]s?)|anexos?\s+(?:de|del|asociad[oa]s?)|informaci[oó]n\s+(?:sobre|de|del|acerca\s+de)\s+(?:el\s+|la\s+)?(?:documento|carta|oficio|informe|archivo|ord))\b/iu',
    $query
);

// Extraer de forma determinista en PHP número/código de proceso (ej. "4.2.0.0", "código de proceso 4.2.0.0", "número de proceso X")
$numProcesoExplicitoDetectado = null;
if (preg_match('/\b(?:n[uú]mero|nro\.?|n[°ºo]|c[oó]digo)?\s*(?:de\s+|del\s+)?proceso\s*(?:n[°ºo]|nro\.?|es|=|:)?\s*([A-Z0-9_.\-\/]{2,30})\b/iu', $query, $mProcWord)) {
    $candProc = trim($mProcWord[1], ".,;:?¿!¡\"'()[]");
    if (!in_array(mb_strtolower($candProc, 'UTF-8'), ['por', 'del', 'de', 'el', 'la', 'los', 'las', 'que', 'en', 'con', 'para'], true)) {
        $numProcesoExplicitoDetectado = $candProc;
    }
} elseif (preg_match('/\b(\d+(?:\.\d+){2,6})\b/u', $query, $mProcDots)) {
    $numProcesoExplicitoDetectado = trim($mProcDots[1]);
}

// Extraer de forma determinista en PHP códigos/números de documento como "ORD.IF : N°1348/2026", "SCINC/IF/1001/2026", "SCINC/IF/1140/2026" o "1140/2026"
$docNumeroExplicitoDetectado = null;
if (preg_match('/\b([A-Z]{2,15}(?:[.\-\/][A-Z0-9]{1,15})*\s*(?::\s*)?(?:N[°ºo]\.?|NRO\.?)?\s*[0-9]+\/[0-9]{2,4})\b/iu', $query, $mCompositeCode)) {
    $candComp = trim($mCompositeCode[1]);
    // Evitar incluir sustantivos genéricos iniciales como "documento 1140/2026", "numero 1001/2026" o "carta 1140/2026"
    $candComp = preg_replace('/^(?:(?:el|la|del|de|con|al)\s+)*(?:(?:documento|carta|oficio|informe|providencia|memor[aá]ndum|resoluci[oó]n|n[uú]mero|nro\.?)\s+)+/iu', '', $candComp);
    $docNumeroExplicitoDetectado = trim($candComp);
} elseif (preg_match('/\b([A-Z0-9_.\-]{1,30}(?:\/[A-Z0-9_.\-]{1,30})+)\b/iu', $query, $mCodeSlash)) {
    // Evitar confundir una fecha completa DD/MM/YYYY con un número de documento
    if (!preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}$/', $mCodeSlash[1])) {
        $docNumeroExplicitoDetectado = trim($mCodeSlash[1]);
    }
} elseif ($numProcesoExplicitoDetectado === null && preg_match('/\b(?:carta|oficio|informe|documento|providencia|memor[aá]ndum|resoluci[oó]n|n[°ºo]|nro\.?|n[uú]mero)\s+(?:con\s+)?(?:n[°ºo]\s*|nro\.?\s*|n[uú]mero\s*)?([A-Z0-9_.\-\/]{2,40})\b/iu', $query, $mCodeWord)) {
    $candidato = trim($mCodeWord[1], ".,;:?¿!¡\"'()[]");
    if (preg_match('/\d/', $candidato)) {
        $docNumeroExplicitoDetectado = $candidato;
    }
}

// Detectar fechas explícitas escritas en la consulta (YYYY-MM-DD o DD-MM-YYYY / DD/MM/YYYY)
$fechasExplicitasEnQuery = [];
if (preg_match_all('/\b(\d{4})-(\d{2})-(\d{2})\b/', $query, $mIsoDates, PREG_SET_ORDER)) {
    foreach ($mIsoDates as $md) {
        $fechasExplicitasEnQuery[] = "{$md[1]}-{$md[2]}-{$md[3]}";
    }
}
if (preg_match_all('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](19\d{2}|20\d{2})\b/', $query, $mLatamDates, PREG_SET_ORDER)) {
    foreach ($mLatamDates as $md) {
        $dDia = (int)$md[1];
        $dMes = (int)$md[2];
        $dAnio = (int)$md[3];
        if ($dMes >= 1 && $dMes <= 12 && $dDia >= 1 && $dDia <= 31) {
            $fechasExplicitasEnQuery[] = sprintf('%04d-%02d-%02d', $dAnio, $dMes, $dDia);
        }
    }
}

// Detectar de forma determinista en PHP si el usuario consulta qué/cuántos documentos cambiaron de pendiente a cerrado (o se cerraron) en un mes/año/fecha
$filtrarPorFechaCierrePre = (bool)preg_match(
    '/\b(cambi(?:aron|ó|o|an|ar)\s+(?:de\s+)?(?:estado\s+)?(?:de\s+)?pendientes?\s+a\s+cerrad[oa]s?|pasar(?:on)?\s+(?:de\s+)?(?:estado\s+)?(?:de\s+)?pendientes?\s+a\s+cerrad[oa]s?|pas[oó]\s+(?:de\s+)?(?:estado\s+)?pendiente\s+a\s+cerrado|se\s+cerraron|fueron\s+cerrad[oa]s?)\b/iu',
    $query
);

// Detectar de forma determinista en PHP si es una consulta sobre tiempos/duración/promedios entre fechas
$esConsultaTiemposPre = (bool)preg_match(
    '/\b(cu[aá]nto\s+(?:tiempo\s+)?(?:tarda|tardan|demora|demoran|toma|toman|lleva|llevan)|tiempo\s+promedio|promedio\s+de\s+(?:d[ií]as|tiempo|cierre|respuesta|espera)|d[ií]as\s+en\s+promedio|tiempo\s+(?:en\s+)?pasar\s+de\s+pendiente\s+a\s+cerrado|tiempo\s+de\s+(?:cierre|respuesta|gesti[oó]n|espera|resoluci[oó]n|tramitaci[oó]n)|entre\s+fechas|diferencia\s+(?:de\s+)?(?:fechas|d[ií]as)|m[eé]tricas?\s+de\s+(?:tiempo|fechas|cierre|plazo)|cumplimiento\s+de\s+plazos?)\b/iu',
    $query
);

// PASO 1: Clasificación de intención y extracción de variables/entidades con Gemini
// (Con timeout acotado de 12s y fallback automático a NLU local en PHP para que Paso 1 NUNCA bloquee por timeout)
$stage1Prompt = "Analiza la consulta en lenguaje natural de un usuario del Sistema de Gestión Documental (SGD) de Axioma y extrae los criterios y variables de búsqueda o clasificación estructurados.
Fecha actual del sistema: {$fechaActual} (Año actual: {$anioActual}).
El usuario autenticado pertenece exclusivamente al contrato: \"{$userContractName}\" (ID Contrato: {$userContractId}).
Dentro de este contrato existen los siguientes usuarios/responsables registrados: [{$listaRespTexto}].

La base de datos contiene estas tablas principales (restringidas al contrato del usuario logueado):
1. `contrato` y `subcontrato`: información del contrato y subcontratos del usuario (nombre_contrato, fecha_termino, nombre_subcontrato)
2. `usuario`: información de usuarios/responsables del contrato (nombre, apellido_p, apellido_m, correo, nombre_usuario)
3. `documento`, `detalle_documento`, `seguimiento`, `documento_relacionado`, `bitacora`, `entidad` y `cargo`: información de documentos cargados en el contrato, sus documentos relacionados en seguimiento y sus variables:
   - `responsable`: usuario/cargo interno del contrato responsable del documento (por ejemplo: 'Jefe Asesoria', 'Encargada De Gestion', 'Jefe Area Proyectos', 'Asesor Legal', etc.)
   - `remitente`: persona/entidad o cargo que envía o emite el documento
   - `destinatario`: persona/entidad o cargo a quien va dirigido el documento
   - `tipo_documento`: tipo (Oficio, Carta, Informe, Providencia, Memorándum, Resolución, etc.)
   - `materia`, `antecedente`, `comentario`, `num_documento`, `num_providencia`, `num_proceso` (código/número de proceso ej. '4.2.0.0'), `fecha_documento`, `fecha_recepcion`, `fecha_plazo`, `id_estado_doc`, `id_flujo`

IMPORTANTE: Si el usuario menciona cargos o nombres como 'Jefe Asesoria', 'Jefe de Asesoría', 'Encargada de Gestión', 'Jefe Área Construcción', 'Asesor Legal', etc. (incluso con errores ortográficos o sin tildes), corrígelo e identifícalo como `responsable` (o `remitente`/`destinatario` según el contexto de la frase) DENTRO de su propio contrato, y NUNCA como un contrato distinto (`contrato_nombre` debe ser null).

Debes retornar EXCLUSIVAMENTE un objeto JSON estructurado con las siguientes claves:
- 'intent': Debe ser uno de: 'contract', 'user', 'document', 'general' (si se consulta, lista, cuenta, verifica documentos relacionados/duplicados o mide tiempos entre fechas de documentos, el intent DEBE ser 'document').
- 'keywords': Un array de palabras clave específicas (strings) para búsquedas generales. Si los términos ya fueron ubicados en 'filters' (como num_proceso, doc_numero, año, mes, estado, responsable, remitente, destinatario, tipo_documento, materia, etc.) o si es una consulta de documentos relacionados, conteo/clasificación, métricas de tiempos o de 'últimos documentos' / 'documentos más antiguos', DEJA EL ARRAY VACÍO [].
- 'filters': Un objeto con las variables extraídas de la consulta (corrigiendo errores de tipeo si aplica):
  - 'responsable': string o null (nombre o cargo del responsable del documento dentro del contrato, ej. 'Jefe Asesoria', sin artículos como 'el' o 'la')
  - 'remitente': string o null (nombre, apellido o cargo de la entidad remitente si se buscan documentos enviados/remitidos por alguien)
  - 'destinatario': string o null (nombre, apellido o cargo de la entidad destinataria si se buscan documentos dirigidos/enviados a alguien)
  - 'tipo_documento': string o null (si filtra por un tipo de documento específico como Oficio, Carta, Informe; si pide clasificar 'por tipo de documento', pon null aquí y usa 'clasificar_por': 'tipo_documento')
  - 'materia': string o null (si se busca o cuenta por una materia o asunto específico)
  - 'subcontrato': string o null (si se menciona un subcontrato específico)
  - 'usuario_nombre': string o null (si la intención es 'user' y se busca información de un usuario)
  - 'usuario_correo': string o null (si se busca un email de usuario específico)
  - 'doc_nombre': string o null (si se busca un nombre de archivo específico)
  - 'num_proceso': string o null (si se busca o cuenta por número/código de proceso específico, ej. '4.2.0.0')
  - 'num_providencia': string o null (si se busca o cuenta por un número de providencia específico)
  - 'doc_numero': string o null (si se menciona algún código o número de documento general, ej. 'ORD.IF : N°1348/2026', 'SCINC/IF/1140/2026' o '1140/2026', que NO sea un código de proceso)
  - 'es_consulta_relacionados': boolean (true SOLO si el usuario pregunta sobre un documento ESPECÍFICO indicando su código/número ej. 'SCINC/IF/1140/2026' o '1140/2026' para saber si ese documento puntual tiene documentos relacionados, cuántos tiene o si tiene documentos relacionados duplicados; false si pregunta en general por todos los documentos del contrato o de un período sin indicar un código de documento específico)
  - 'es_consulta_repetidos': boolean (true si el usuario pide evaluar, buscar, contar, listar o exportar documentos repetidos o duplicados en el contrato, mes, año o proceso, ya sean los documentos propios del contrato o los documentos de seguimiento repetidos en los documentos del contrato; false si consulta por un documento específico en 'es_consulta_relacionados')
  - 'modo_repeticion': string o null ('seguimiento' si el usuario pregunta si hay documentos de seguimiento repetidos/duplicados en los documentos del contrato; 'contrato' si pregunta por los documentos repetidos asociados al contrato sin referirse a documentos de seguimiento; null si no es consulta de repetidos)
  - 'es_consulta_contenido_archivo': boolean (true si el usuario pide un resumen, síntesis, lectura, explicación, de qué trata, qué dice o información de los archivos/adjuntos asociados a un documento específico como 'ORD.IF : N°1348/2026'; false en otro caso)
  - 'estado_doc': int o null (1 si pide documentos 'pendiente' o 'pendientes', 2 si pide documentos 'cerrado' o 'cerrados', null si pregunta por el paso de pendiente a cerrado o en general)
  - 'flujo_doc': int o null (1 si pide documentos de 'entrada', 2 si pide documentos de 'salida', null en otro caso)
  - 'solo_mis_documentos': boolean (true SOLO si el usuario pide explícitamente 'mis documentos' en primera persona, false cuando pregunta por otro responsable del contrato como 'Jefe Asesoria')
  - 'es_consulta_tiempos': boolean (true si el usuario pregunta cuánto tarda o demora en promedio un documento en pasar de pendiente a cerrado, tiempos de respuesta/cierre/espera, diferencia de días entre fechas o cumplimiento de plazos; false en otro caso)
  - 'es_consulta_cantidad': boolean (true SOLO si el usuario pregunta por cifras, cantidades, totales, conteos o clasificaciones estadísticas por atributos como proceso, materia, tipo, remitente, destinatario, responsable o fechas; false si pregunta por los documentos relacionados de un documento específico, si pide listar documentos o medir tiempos promedio)
  - 'clasificar_por': string o null (dimensión principal por la que el usuario pide clasificar o agrupar el conteo o las métricas de tiempo: 'responsable', 'remitente', 'destinatario', 'tipo_documento', 'materia', 'num_proceso', 'fecha', 'mes', 'anio', 'estado', 'flujo', 'subcontrato', o null si es general)
  - 'anio': int o null (año de 4 dígitos SOLO si el usuario filtra por un año de forma independiente y NO cuando el año es parte del código del documento como 'SCINC/IF/1140/2026' o '1140/2026')
  - 'mes': int o null (número de mes del 1 al 12 SOLO si el usuario menciona explícitamente un mes específico, ej. enero = 1; null si no menciona ningún mes)
  - 'orden_antiguos': boolean (true si pide el documento más antiguo, los documentos más antiguos, más viejos, primeros en el tiempo o de fecha de documento más antigua; false en caso contrario)
  - 'orden_recientes': boolean (true si pide el último documento, los últimos documentos, más recientes o nuevos; false si pide los más antiguos)
  - 'limite': int o null (cantidad de documentos a listar si el usuario pide un número específico, ej. 'Lista los 10 documentos más antiguos' -> 10; null si no especifica un número)
  - 'fecha_desde': string o null (fecha YYYY-MM-DD SOLO si el usuario escribe explícitamente una fecha de inicio o fecha exacta en su consulta; NUNCA inventes fechas del mes actual, pon null)
  - 'fecha_hasta': string o null (fecha YYYY-MM-DD SOLO si el usuario escribe explícitamente una fecha límite o fecha exacta en su consulta; pon null en cualquier otro caso)
  - 'generar_excel': boolean (true si el usuario pide generar, crear, exportar o descargar una planilla Excel, archivo Excel, hoja de cálculo, .xlsx o .xls; false en otro caso)
  - 'columnas_excel': array de strings o [] (SOLO si el usuario especifica explícitamente qué columnas o campos puntuales desea dentro de la planilla Excel, ej. ['num_documento', 'materia', 'responsable']; si no restringe columnas individuales, deja el array vacío [])

Consulta del usuario: \"" . $query . "\"";

// Si en PHP ya detectamos de forma determinista el código exacto del documento (ej. "SCINC/IF/1001/2026" o "ORD.IF : N°1348/2026")
// y sabemos que es una consulta de resumen/información del documento o de sus documentos relacionados,
// omitimos la llamada de red del Paso 1 para evitar doble latencia y timeouts HTTP en el servidor.
$usarFastPathLocalPaso1 = ($docNumeroExplicitoDetectado !== null && ($esConsultaContenidoArchivoPre || $esConsultaRelacionadosPre));

$stage1Res = [];
$searchParams = null;
if ($usarFastPathLocalPaso1) {
    $searchParams = [
        'intent' => 'document',
        'keywords' => [],
        'filters' => [
            'doc_numero' => $docNumeroExplicitoDetectado,
            'es_consulta_contenido_archivo' => $esConsultaContenidoArchivoPre,
            'es_consulta_relacionados' => $esConsultaRelacionadosPre
        ]
    ];
} else {
    $stage1Res = llamarGemini($apiKey, $stage1Prompt, true, 10);
    if (!isset($stage1Res['error']) && isset($stage1Res['text'])) {
        $searchParams = extraerJsonDeTexto($stage1Res['text']);
    } else {
        error_log("Paso 1 IA usó extractor NLU local en PHP por demora/error de red: " . ($stage1Res['error'] ?? 'sin respuesta'));
    }
}

if (!$searchParams) {
    $searchParams = [
        'intent' => 'document',
        'keywords' => [],
        'filters' => []
    ];
}

$intent = $searchParams['intent'] ?? 'general';
$keywords = $searchParams['keywords'] ?? [];
$filters = $searchParams['filters'] ?? [];

// Detectar si el usuario solicitó generar una planilla Excel
$generarExcel = !empty($filters['generar_excel']) || $generarExcelPre;
$filters['generar_excel'] = $generarExcel;

// Si detectamos en PHP un número/código de proceso explícito (como "4.2.0.0"), aseguramos que quede en $filters['num_proceso']
if ($numProcesoExplicitoDetectado !== null) {
    $filters['num_proceso'] = $numProcesoExplicitoDetectado;
    if (!empty($filters['doc_numero']) && trim((string)$filters['doc_numero']) === $numProcesoExplicitoDetectado) {
        $filters['doc_numero'] = null;
    }
}

// Si detectamos en PHP un código/número de documento explícito (como "SCINC/IF/1140/2026" o "1140/2026"),
// aseguramos que quede en $filters['doc_numero'] completo
if ($docNumeroExplicitoDetectado !== null) {
    if (empty($filters['doc_numero']) || mb_strlen($docNumeroExplicitoDetectado) > mb_strlen(trim((string)$filters['doc_numero']))) {
        $filters['doc_numero'] = $docNumeroExplicitoDetectado;
    }
}

// Si el usuario escribió una fecha explícita en su consulta y Paso 1 no la colocó en fecha_desde/fecha_hasta, asignarla
if (!empty($fechasExplicitasEnQuery)) {
    if (count($fechasExplicitasEnQuery) === 1 && empty($filters['fecha_desde']) && empty($filters['fecha_hasta'])) {
        $filters['fecha_desde'] = $fechasExplicitasEnQuery[0];
        $filters['fecha_hasta'] = $fechasExplicitasEnQuery[0];
    } elseif (count($fechasExplicitasEnQuery) >= 2 && empty($filters['fecha_desde']) && empty($filters['fecha_hasta'])) {
        $filters['fecha_desde'] = $fechasExplicitasEnQuery[0];
        $filters['fecha_hasta'] = $fechasExplicitasEnQuery[1];
    }
}

// Detectar si el usuario especificó un documento puntual (por número, providencia o nombre de archivo)
$tieneDocumentoEspecifico = !empty($filters['doc_numero']) || !empty($filters['num_providencia']) || !empty($filters['doc_nombre']);

// Detectar si es consulta sobre documentos relacionados / seguimiento / duplicados de un DOCUMENTO ESPECÍFICO
// (Si no indicó un documento puntual y pregunta por repetidos/duplicados del contrato o en el seguimiento de los documentos del contrato, va a consultarDocumentosRepetidos)
$esConsultaRelacionados = (!empty($filters['es_consulta_relacionados']) || $esConsultaRelacionadosPre) && ($tieneDocumentoEspecifico || !$esConsultaRepetidosPre);
if ($esConsultaRepetidosPre && !$tieneDocumentoEspecifico) {
    $esConsultaRelacionados = false;
}
$filters['es_consulta_relacionados'] = $esConsultaRelacionados;

// Detectar si es consulta general de evaluación de documentos repetidos o duplicados (modo 'contrato' o modo 'seguimiento')
$esConsultaRepetidos = !$esConsultaRelacionados && (!empty($filters['es_consulta_repetidos']) || $esConsultaRepetidosPre);
$filters['es_consulta_repetidos'] = $esConsultaRepetidos;

// Determinar modo de repetición ('contrato' vs 'seguimiento') para no mezclar nunca los resultados de ambos tipos de consulta
$modoRepeticion = ($modoRepeticionPre === 'seguimiento' || ($filters['modo_repeticion'] ?? '') === 'seguimiento')
    ? 'seguimiento'
    : 'contrato';
$filters['modo_repeticion'] = $esConsultaRepetidos ? $modoRepeticion : null;

// Detectar si es consulta para acceder, leer o resumir los archivos asociados a un documento específico (ej. "Podrías darme un resumento del documento ORD.IF : N°1348/2026")
$esConsultaContenidoArchivo = !$esConsultaRelacionados && !$esConsultaRepetidos && (
    !empty($filters['es_consulta_contenido_archivo']) ||
    $esConsultaContenidoArchivoPre ||
    (!empty($filters['doc_numero']) && !$esConsultaTiemposPre && !$generarExcelPre)
);
$filters['es_consulta_contenido_archivo'] = $esConsultaContenidoArchivo;

// Si Paso 1 no extrajo responsable pero el usuario escribió el nombre de algún usuario/responsable del contrato en su consulta, lo detectamos en PHP
if (empty($filters['responsable']) && !empty($usuariosContrato)) {
    $qNorm = normalizarTexto($query);
    foreach ($usuariosContrato as $uc) {
        $nomCompleto = trim(preg_replace('/\s+/u', ' ', ($uc['nombre'] ?? '') . ' ' . ($uc['apellido_p'] ?? '') . ' ' . ($uc['apellido_m'] ?? '')));
        $nomNorm = normalizarTexto($nomCompleto);
        if ($nomNorm !== '' && mb_strlen($nomNorm) >= 4 && mb_strpos($qNorm, $nomNorm) !== false) {
            $filters['responsable'] = $nomCompleto;
            break;
        }
    }
}

// Detectar si es consulta de métricas de tiempo / duración entre fechas de documentos
$pideQueOCualesDocumentos = (bool)preg_match('/\b(qu[eé]\s+documentos?|cu[aá]les?\s+(?:son\s+los\s+)?documentos?|lista(?:r)?\s+(?:los\s+)?documentos?|mostrar\s+(?:los\s+)?documentos?|mu[eé]strame\s+(?:los\s+)?documentos?)\b/iu', $query);
$esConsultaTiempos = (!$esConsultaRelacionados) && (!$esConsultaRepetidos) && (!$esConsultaContenidoArchivo) && (!$pideQueOCualesDocumentos) && (!empty($filters['es_consulta_tiempos']) || $esConsultaTiemposPre);
$filters['es_consulta_tiempos'] = $esConsultaTiempos;

// Detectar si consulta por documentos que cambiaron de estado pendiente a cerrado (o se cerraron) en un período específico
$filtrarPorFechaCierre = !$esConsultaRelacionados && !$esConsultaRepetidos && !$esConsultaContenidoArchivo && !$esConsultaTiempos && (!empty($filters['filtrar_por_fecha_cierre']) || $filtrarPorFechaCierrePre);
$filters['filtrar_por_fecha_cierre'] = $filtrarPorFechaCierre;

// Detectar si el usuario pide ordenar por fecha de documento más antigua (más antiguos / más viejos / primeros)
$ordenAntiguos = !empty($filters['orden_antiguos']) ||
    (bool)preg_match('/\b(antigu[oa]s?|viej[oa]s?|primer(?:o|os|a|as)?\s+(?:\d+\s+)?documentos?|menor\s+fecha|fechas?\s+(?:de\s+documentos?\s+)?m[aá]s\s+antigu[oa]s?)\b/iu', $query);
$filters['orden_antiguos'] = $ordenAntiguos;
if ($ordenAntiguos) {
    $filters['orden_recientes'] = false;
}

// Detectar límite explícito de documentos a listar (ej. "Lista los 10 documentos más antiguos")
if (preg_match('/\b(?:lista(?:r)?|muestra|mu[eé]strame|mostrar|dame|ver|buscar|busca|los|las|primeros|primeras|[uú]ltimos|[uú]ltimas|top)\s+(?:los\s+|las\s+)?(\d{1,2})\s+(?:documentos?|registros?|archivos?|pendientes?|cerrados?)\b/iu', $query, $mLim)) {
    $filters['limite'] = (int)$mLim[1];
}

// Detectar si la consulta pregunta por cifras, cantidades, totales, conteos o clasificaciones estadísticas
// (Si es una consulta sobre documentos relacionados, documentos repetidos/duplicados o resumen/contenido de un documento, NO usar el contador general)
$esConsultaCantidad = !$esConsultaRelacionados && !$esConsultaRepetidos && !$esConsultaContenidoArchivo && !$esConsultaTiempos && (
    !empty($filters['es_consulta_cantidad']) ||
    (bool)preg_match('/\b(cu[aá]nt[oa]s?|cantidad(?:es)?|cifras?|total(?:es)?|conteo|contar|estad[ií]sticas?|clasific\w*|desglos\w*|agrup\w*|n[uú]mero\s+total|n[uú]mero\s+de\s+documentos|registro\s+hist[oó]rico|por\s+mes(?:es)?|por\s+a[ñn]os?|por\s+tipos?)\b/iu', $query)
);

// Si el usuario pide explícitamente listar/mostrar o exportar a Excel los documentos (ej. "Genera una planilla excel con los documentos pendientes") y no pregunta cuántos hay, no es consulta de cantidad
$pideListarDocumentos = (
    (bool)preg_match('/\b(lista(?:r)?|muestra|mu[eé]strame|mostrar|dame\s+los|ver\s+los|cu[aá]les\s+son\s+los)\b/iu', $query) ||
    ($generarExcel && (bool)preg_match('/\b(documentos?|cartas?|oficios?|informes?|memor[aá]ndums?|resoluciones?|providencias?)\b/iu', $query))
) && !preg_match('/\b(cu[aá]nt[oa]s?|cantidad(?:es)?|cifras?|conteo|estad[ií]sticas?|clasific\w*|desglos\w*|por\s+mes(?:es)?|por\s+a[ñn]os?|por\s+responsables?|por\s+procesos?|por\s+tipos?|por\s+remitentes?|por\s+destinatarios?|por\s+materias?|promedio|tarda|demora)\b/iu', $query);
if ($pideListarDocumentos || $esConsultaRelacionados || $esConsultaRepetidos || $esConsultaContenidoArchivo) {
    $esConsultaCantidad = false;
}
$filters['es_consulta_cantidad'] = $esConsultaCantidad;

// Detectar dimensión de clasificación solicitada a través de todos los atributos del documento
if (empty($filters['clasificar_por'])) {
    if (preg_match('/\b(por\s+(?:responsables?|usuarios?|encargados?|personas?)|seg[uú]n\s+(?:su\s+)?(?:responsable|usuario))\b/iu', $query)) {
        $filters['clasificar_por'] = 'responsable';
    } elseif (preg_match('/\b(por\s+(?:n[uú]meros?\s+de\s+|c[oó]digos?\s+de\s+)?procesos?|seg[uú]n\s+(?:su\s+)?(?:n[uú]mero\s+de\s+|c[oó]digo\s+de\s+)?proceso)\b/iu', $query)) {
        $filters['clasificar_por'] = 'num_proceso';
    } elseif (preg_match('/\b(por\s+(?:materias?|asuntos?)|seg[uú]n\s+(?:su\s+)?(?:materia|asunto))\b/iu', $query)) {
        $filters['clasificar_por'] = 'materia';
    } elseif (preg_match('/\b(por\s+remitentes?|seg[uú]n\s+(?:su\s+)?remitente)\b/iu', $query)) {
        $filters['clasificar_por'] = 'remitente';
    } elseif (preg_match('/\b(por\s+destinatarios?|seg[uú]n\s+(?:su\s+)?destinatario)\b/iu', $query)) {
        $filters['clasificar_por'] = 'destinatario';
    } elseif (preg_match('/\b(por\s+fechas?(?:\s+de\s+documento)?|por\s+d[ií]as?|seg[uú]n\s+(?:su\s+)?fecha)\b/iu', $query)) {
        $filters['clasificar_por'] = 'fecha';
    } elseif (preg_match('/\b(por\s+mes(?:es)?|mensual(?:es|mente)?|cada\s+mes|mes\s+a\s+mes)\b/iu', $query)) {
        $filters['clasificar_por'] = 'mes';
    } elseif (preg_match('/\b(por\s+a[ñn]os?|anual(?:es|mente)?|cada\s+a[ñn]o|a[ñn]o\s+a\s+a[ñn]o)\b/iu', $query)) {
        $filters['clasificar_por'] = 'anio';
    } elseif (preg_match('/\b(por\s+tipos?(?:\s+de\s+documentos?)?|seg[uú]n\s+(?:su\s+)?tipo)\b/iu', $query)) {
        $filters['clasificar_por'] = 'tipo_documento';
    } elseif (preg_match('/\b(por\s+estados?|seg[uú]n\s+(?:su\s+)?estado)\b/iu', $query)) {
        $filters['clasificar_por'] = 'estado';
    } elseif (preg_match('/\b(por\s+flujos?|seg[uú]n\s+(?:su\s+)?flujo)\b/iu', $query)) {
        $filters['clasificar_por'] = 'flujo';
    } elseif (preg_match('/\b(por\s+subcontratos?)\b/iu', $query)) {
        $filters['clasificar_por'] = 'subcontrato';
    }
}

// Detectar estado del documento explícito ('pendiente'/'pendientes' = 1, 'cerrado'/'cerrados' = 2),
// Si consulta qué/cuántos documentos cambiaron de pendiente a cerrado ($filtrarPorFechaCierre), su estado actual es Cerrado (2).
$mencionaAmbosEstados = (bool)(preg_match('/\bpendientes?\b/iu', $query) && preg_match('/\bcerrad[oa]s?\b/iu', $query));
if ($filtrarPorFechaCierre) {
    $filters['estado_doc'] = 2;
} elseif ($mencionaAmbosEstados || $esConsultaTiempos || $esConsultaRelacionados || $esConsultaContenidoArchivo) {
    $filters['estado_doc'] = null;
} elseif (empty($filters['estado_doc']) && ($filters['clasificar_por'] ?? null) !== 'estado') {
    if (preg_match('/\bpendientes?\b/iu', $query)) {
        $filters['estado_doc'] = 1;
    } elseif (preg_match('/\bcerrad[oa]s?\b/iu', $query)) {
        $filters['estado_doc'] = 2;
    }
}

// Si pide clasificar "por mes", aseguramos que 'mes' no filtre un único mes a menos que haya nombrado uno explícitamente
$mapaMesesNombre = [
    'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
    'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
    'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
];
$mesExplicitoDetectado = null;
foreach ($mapaMesesNombre as $nombreM => $numM) {
    if (preg_match('/\b' . $nombreM . '\b/iu', $queryLower)) {
        $mesExplicitoDetectado = $numM;
        break;
    }
}
if ($mesExplicitoDetectado !== null && empty($filters['mes'])) {
    $filters['mes'] = $mesExplicitoDetectado;
} elseif ($mesExplicitoDetectado === null && (($filters['clasificar_por'] ?? null) === 'mes' || $ordenAntiguos || !empty($filters['orden_recientes']) || $esConsultaTiempos || $esConsultaRelacionados || $esConsultaRepetidos || $esConsultaContenidoArchivo)) {
    $filters['mes'] = null;
}

// Detectar año explícito independiente (ej. "en el año 2026", "del 2026", "2025"),
// ignorando años que forman parte de un código con "/" o "-" como "SCINC/IF/1140/2026" o "1140/2026"
$anioExplicitoDetectado = null;
if (preg_match('/(?<![\/\-])\b(19\d{2}|20\d{2})\b(?![\/\-])/u', $queryLower, $mAnio)) {
    $anioExplicitoDetectado = (int)$mAnio[1];
    if (empty($filters['anio'])) {
        $filters['anio'] = $anioExplicitoDetectado;
    }
} elseif (!empty($filters['doc_numero']) || !empty($filters['num_proceso']) || $esConsultaRelacionados || $esConsultaRepetidos || $esConsultaContenidoArchivo || $ordenAntiguos || !empty($filters['orden_recientes']) || $esConsultaTiempos) {
    // Si busca por código de documento (ej. 1140/2026), código de proceso, documentos relacionados, repetidos, contenido, más antiguos, más recientes o métricas de tiempo sin año independiente, no restringir por año
    $filters['anio'] = null;
}

// Si ya tenemos filtro por año o mes entero (y no se escribió una fecha exacta DD-MM-YYYY), limpiamos fecha_desde / fecha_hasta redundantes
if (empty($fechasExplicitasEnQuery) && (!empty($filters['anio']) || !empty($filters['mes']))) {
    $filters['fecha_desde'] = null;
    $filters['fecha_hasta'] = null;
}

// Si se detectaron filtros de variables de documentos, documentos relacionados, repetidos, contenido, consulta de tiempos o consulta de cantidades de documentos, asegurar intent document
if (!empty($customVars['id_responsable']) || !empty($customVars['id_remitente']) || !empty($customVars['id_destinatario']) ||
    !empty($filters['responsable']) || !empty($filters['remitente']) || !empty($filters['destinatario']) ||
    !empty($filters['doc_numero']) || !empty($filters['num_proceso']) || !empty($filters['num_providencia']) ||
    !empty($filters['tipo_documento']) || !empty($filters['materia']) ||
    !empty($filters['estado_doc']) || !empty($filters['flujo_doc']) ||
    !empty($filters['orden_antiguos']) || !empty($filters['orden_recientes']) ||
    !empty($filters['clasificar_por']) || !empty($filters['anio']) || !empty($filters['mes']) ||
    !empty($filters['fecha_desde']) || !empty($filters['fecha_hasta']) ||
    $esConsultaRelacionados || $esConsultaRepetidos || $esConsultaContenidoArchivo || $esConsultaTiempos || $esConsultaCantidad) {
    $intent = 'document';
}

// Filtrar palabras clave genéricas (Stopwords) y años para no contaminar búsquedas por texto
$stopwords = [
    'usuario', 'usuarios', 'contrato', 'contratos', 'subcontrato', 'subcontratos',
    'documento', 'documentos', 'listar', 'lista', 'mostrar', 'muestra', 'muestrame', 'muéstrame',
    'todos', 'todas', 'ver', 'dame', 'darme', 'podrias', 'podrías', 'entregar', 'especifico', 'específicos', 'especifica', 'especificas',
    'buscar', 'busca', 'consultar', 'consulta', 'con', 'del', 'los', 'las', 'unos', 'unas', 'para', 'por',
    'sobre', 'materia', 'materias', 'asunto', 'asuntos', 'proceso', 'procesos', 'codigo', 'código', 'codigos', 'códigos',
    'resumen', 'resumento', 'resumir', 'resume', 'resúmeme', 'resumeme', 'sintesis', 'síntesis', 'contenido', 'trata', 'dice',
    'archivo', 'archivos', 'adjunto', 'adjuntos', 'anexo', 'anexos', 'asociado', 'asociados', 'asociada', 'asociadas',
    'explica', 'explicame', 'explícame', 'explicar', 'analiza', 'analizar', 'leer', 'lee', 'ord',
    'excel', 'planilla', 'planillas', 'xlsx', 'xls', 'exportar', 'exporta', 'exportame', 'expórtame', 'exportalo', 'expórtalo',
    'generar', 'genera', 'generame', 'genérame', 'descargar', 'descarga', 'descargame', 'descárgame', 'hoja', 'calculo', 'cálculo',
    'columna', 'columnas', 'campo', 'campos', 'informacion', 'información', 'anterior', 'eso', 'esos', 'esas', 'estos', 'estas',
    'ultimo', 'ultimos', 'último', 'últimos', 'ultima', 'ultimas', 'última', 'últimas',
    'antiguo', 'antiguos', 'antigua', 'antiguas', 'viejo', 'viejos', 'vieja', 'viejas', 'primer', 'primero', 'primeros', 'primera', 'primeras',
    'mas', 'más', 'estan', 'están', 'esta', 'está', 'que', 'cual', 'cuál',
    'relacionado', 'relacionados', 'relacionada', 'relacionadas', 'duplicado', 'duplicados', 'duplicada', 'duplicadas',
    'repetido', 'repetidos', 'repetida', 'repetidas', 'respetido', 'respetidos', 'respetida', 'respetidas', 'repedito', 'repeditos',
    'repeticion', 'repetición', 'repiten', 'repite', 'decirme', 'decir',
    'seguimiento', 'seguimientos', 'indicame', 'indícame', 'indicar', 'indica', 'carta', 'oficio', 'informe', 'providencia', 'memorandum', 'memorándum', 'resolucion', 'resolución',
    'tarda', 'tardan', 'demora', 'demoran', 'tiempo', 'tiempos', 'promedio', 'promedios', 'dia', 'dias', 'día', 'días',
    'pasar', 'pasa', 'pasan', 'cierre', 'cerrar', 'cerrarse', 'espera', 'plazo', 'plazos', 'entre', 'diferencia', 'metricas', 'métricas',
    'reciente', 'recientes', 'nuevo', 'nuevos', 'nueva', 'nuevas', 'actual', 'actuales', 'registrado',
    'registrados', 'cargado', 'cargados', 'sistema', 'sgd', 'axioma', 'pendiente', 'pendientes',
    'cerrado', 'cerrados', 'entrada', 'salida', 'fecha', 'fechas', 'mes', 'meses', 'mensual', 'año', 'años', 'anio', 'anios', 'anual',
    'cuales', 'cuáles', 'son', 'hay', 'responsable', 'responsables', 'remitente', 'remitentes', 'destinatario', 'destinatarios',
    'enviado', 'enviados', 'dirigido', 'dirigidos', 'tiene', 'tienen', 'como', 'cuanto', 'cuantos', 'cuanta', 'cuantas',
    'cuánto', 'cuántos', 'cuánta', 'cuántas', 'cantidad', 'cantidades', 'cifra', 'cifras', 'total', 'totales',
    'historico', 'historicos', 'histórico', 'históricos', 'historica', 'historicas', 'histórica', 'históricas',
    'registro', 'registros', 'estadistica', 'estadisticas', 'estadística', 'estadísticas', 'conteo', 'contar',
    'numero', 'numeros', 'número', 'números', 'existen', 'existe', 'clasificar', 'clasificados', 'clasificacion', 'clasificación',
    'desglose', 'desglosado', 'agrupar', 'agrupado', 'tipo', 'tipos', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'setiembre', 'octubre', 'noviembre', 'diciembre'
];
$filteredKeywords = [];
if (is_array($keywords)) {
    foreach ($keywords as $kw) {
        $kwClean = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower(trim((string)$kw), 'UTF-8'));
        if (!in_array($kwClean, $stopwords, true) && !preg_match('/^\d+$/', $kwClean) && mb_strlen($kwClean) > 2) {
            $filteredKeywords[] = trim((string)$kw);
        }
    }
}
$keywords = $filteredKeywords;

// Si el usuario consulta por "más antiguos" / "último documento" / "recientes" / documentos relacionados / repetidos / contenido / métricas de tiempo o cifras históricas
// sin haber escrito un año, mes o fecha explícita, limpiamos fecha_desde / fecha_hasta para abarcar todo el registro histórico.
$mentionsExplicitYearOrMonthOrDate = ($mesExplicitoDetectado !== null) || ($anioExplicitoDetectado !== null) || !empty($fechasExplicitasEnQuery);
if (!$mentionsExplicitYearOrMonthOrDate && ($esConsultaRelacionados || $esConsultaRepetidos || $esConsultaContenidoArchivo || $ordenAntiguos || $esConsultaTiempos || preg_match('/(últim|ultim|recient|nuev|antigu|viej|primer)/iu', $queryLower) || $esConsultaCantidad)) {
    $filters['fecha_desde'] = null;
    $filters['fecha_hasta'] = null;
}

// Soporte conversacional para pedir Excel de la consulta inmediatamente anterior
// (ej. "Generame una planilla excel con eso", "Pásalo a excel", "Descárgalo en una planilla")
$tieneCriterioPropio = !empty($filters['responsable']) || !empty($filters['remitente']) || !empty($filters['destinatario']) ||
    !empty($filters['doc_numero']) || !empty($filters['num_proceso']) || !empty($filters['num_providencia']) ||
    !empty($filters['tipo_documento']) || !empty($filters['materia']) || !empty($filters['estado_doc']) ||
    !empty($filters['flujo_doc']) || !empty($filters['clasificar_por']) || !empty($filters['anio']) ||
    !empty($filters['mes']) || !empty($filters['fecha_desde']) || !empty($filters['fecha_hasta']) ||
    !empty($keywords) || $esConsultaRelacionados || $esConsultaRepetidos || $esConsultaContenidoArchivo || $esConsultaTiempos || $esConsultaCantidad ||
    $intent === 'user' || $intent === 'contract';

$ctxTempFile = rtrim(sys_get_temp_dir(), '/\\') . '/sgd_chat_ctx_' . (int)$userId . '_' . (int)$userContractId . '.json';
$lastCtxSaved = null;
if (is_file($ctxTempFile)) {
    $decodedCtx = json_decode((string)@file_get_contents($ctxTempFile), true);
    if (is_array($decodedCtx)) {
        $lastCtxSaved = $decodedCtx;
    }
}

if ($generarExcel && !$tieneCriterioPropio && !empty($lastCtxSaved)) {
    $colsNuevas = $filters['columnas_excel'] ?? [];
    $intent = $lastCtxSaved['intent'] ?? 'document';
    $filters = $lastCtxSaved['filters'] ?? [];
    $keywords = $lastCtxSaved['keywords'] ?? [];
    $filters['generar_excel'] = true;
    if (!empty($colsNuevas)) {
        $filters['columnas_excel'] = $colsNuevas;
    }
    $esConsultaRelacionados = !empty($filters['es_consulta_relacionados']);
    $esConsultaRepetidos = !empty($filters['es_consulta_repetidos']);
    $modoRepeticion = ($filters['modo_repeticion'] ?? 'contrato') === 'seguimiento' ? 'seguimiento' : 'contrato';
    $esConsultaContenidoArchivo = !empty($filters['es_consulta_contenido_archivo']);
    $esConsultaTiempos = !empty($filters['es_consulta_tiempos']);
    $esConsultaCantidad = !empty($filters['es_consulta_cantidad']);
    $ordenAntiguos = !empty($filters['orden_antiguos']);
} else {
    // Guardar contexto actual en archivo temporal por usuario/contrato por si en el siguiente turno pide exportar a Excel
    @file_put_contents($ctxTempFile, json_encode([
        'intent'   => $intent,
        'filters'  => $filters,
        'keywords' => $keywords,
        'query'    => $query
    ], JSON_UNESCAPED_UNICODE));
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

if (($intent === 'user' || $intent === 'general') && !$esConsultaCantidad && !$esConsultaTiempos && !$esConsultaRelacionados && !$esConsultaRepetidos && !$esConsultaContenidoArchivo) {
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

$inlinePartsDocumento = [];

if ($intent === 'document' || $intent === 'general') {
    if ($esConsultaRelacionados) {
        // Cuando pregunta por documentos relacionados, cuántos documentos relacionados tiene o si tiene documentos relacionados duplicados de un documento específico
        $resRel = consultarDocumentosRelacionados($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);
        if (!isset($resRel['error'])) {
            $dbResults['analisis_documentos_relacionados'] = $resRel;
        } else {
            $dbResults['error_consulta_documentos'] = $resRel['error'];
        }
    } elseif ($esConsultaRepetidos) {
        // Cuando pide evaluar, listar, contar o exportar los documentos repetidos / duplicados:
        // - $modoRepeticion = 'contrato': evalúa exclusivamente los documentos asociados al contrato (sin mezclar seguimiento)
        // - $modoRepeticion = 'seguimiento': evalúa exclusivamente si hay documentos de seguimiento repetidos en los documentos del contrato
        $resRep = consultarDocumentosRepetidos($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato, $modoRepeticion);
        if (!isset($resRep['error'])) {
            $dbResults['analisis_documentos_repetidos'] = $resRep;
        } else {
            $dbResults['error_consulta_documentos'] = $resRep['error'];
        }
    } elseif ($esConsultaContenidoArchivo) {
        // Cuando pide resumen, lectura, síntesis o información de los archivos asociados a un documento específico (ej. "Podrías darme un resumento del documento ORD.IF : N°1348/2026")
        $resCont = consultarArchivosYContenidoDocumento($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato, $inlinePartsDocumento);
        if (!isset($resCont['error'])) {
            $dbResults['analisis_contenido_documento'] = $resCont;
        } else {
            $dbResults['error_consulta_documentos'] = $resCont['error'];
        }
    } elseif ($esConsultaTiempos) {
        // Cuando pregunta por tiempos entre fechas (ej. cuánto tarda en pasar de pendiente a cerrado, tiempo promedio general o por responsable/usuario/tipo/mes/año)
        $resTiempos = calcularMetricasTiemposDocumentos($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);
        if (!isset($resTiempos['error'])) {
            $dbResults['metricas_tiempos_fechas_documentos'] = $resTiempos;
        } else {
            $dbResults['error_consulta_documentos'] = $resTiempos['error'];
        }
    } elseif ($esConsultaCantidad) {
        // Cuando pregunta por cifras, cantidades o clasificaciones: contar el registro histórico y NO retornar el detalle de documentos
        $resConteo = contarDocumentosHistoricosPorVariables($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);
        if (!isset($resConteo['error'])) {
            $resConteo['total_usuarios_activos_contrato'] = count($usuariosContrato);
            $dbResults['estadisticas_historicas_documentos'] = $resConteo;
        } else {
            $dbResults['error_consulta_documentos'] = $resConteo['error'];
        }
    } else {
        $resDocs = buscarDocumentosPorVariables($cnx, $userContractId, $userId, $filters, $keywords, $customVars, $usuariosContrato);
        if (!isset($resDocs['error'])) {
            $dbResults['documentos'] = $resDocs;
        } else {
            $dbResults['documentos'] = [];
            $dbResults['error_consulta_documentos'] = $resDocs['error'];
        }
    }
}

// Si el usuario solicitó generar una planilla Excel, construimos la planilla con ÚNICAMENTE la información solicitada
$excelExportInfo = null;
if ($generarExcel) {
    $datosExcel = construirDatosExcelSolicitados(
        $dbResults,
        $intent,
        $filters,
        $esConsultaRelacionados,
        $esConsultaTiempos,
        $esConsultaCantidad,
        $query,
        $userContractName
    );

    if (!empty($datosExcel['headers'])) {
        $tmpDir = rtrim(sys_get_temp_dir(), '/\\');
        // Limpiar archivos temporales de planillas antiguos (> 2 horas)
        foreach ((array)@glob($tmpDir . '/sgd_chat_excel_*.json') as $oldFile) {
            if (@filemtime($oldFile) < (time() - 7200)) {
                @unlink($oldFile);
            }
        }

        $tokenExcel = bin2hex(function_exists('random_bytes') ? random_bytes(12) : openssl_random_pseudo_bytes(12));
        $datosExcel['user_id'] = (int)$userId;
        $datosExcel['contract_id'] = (int)$userContractId;
        $datosExcel['created_at'] = time();

        $exportFilePath = $tmpDir . '/sgd_chat_excel_' . $tokenExcel . '.json';
        @file_put_contents($exportFilePath, json_encode($datosExcel, JSON_UNESCAPED_UNICODE));

        $excelExportInfo = [
            'token'       => $tokenExcel,
            'url'         => 'chatIA/descargarExcelChatIA.php?token=' . $tokenExcel,
            'filename'    => $datosExcel['filename'],
            'sheet_title' => $datosExcel['sheet_title'],
            'rows_count'  => count($datosExcel['rows']),
            'columns'     => $datosExcel['headers']
        ];
    }
}

// Regla de visualización de listas de documentos:
// Si la consulta entrega más de 7 documentos, solo los primeros 7 se detallan con botones de Descargar y Detalle;
// el resto de los documentos (del 8° en adelante) se listan únicamente como texto simple al final de la respuesta.
$dbResultsForPrompt = $dbResults;
if (isset($dbResultsForPrompt['documentos']) && is_array($dbResultsForPrompt['documentos'])) {
    $dbResultsForPrompt['total_documentos_encontrados'] = count($dbResultsForPrompt['documentos']);
    if (count($dbResultsForPrompt['documentos']) > 7) {
        $dbResultsForPrompt['nota_visualizacion_lista'] = 'Se entregan los primeros 7 documentos para mostrar con detalle y botón de descarga; los restantes (' . (count($dbResultsForPrompt['documentos']) - 7) . ' documentos) serán anexados automáticamente como listado de texto.';
        $dbResultsForPrompt['documentos'] = array_slice($dbResultsForPrompt['documentos'], 0, 7);
    }
}
if (isset($dbResultsForPrompt['analisis_documentos_repetidos']['documentos_repetidos']) && is_array($dbResultsForPrompt['analisis_documentos_repetidos']['documentos_repetidos']) && count($dbResultsForPrompt['analisis_documentos_repetidos']['documentos_repetidos']) > 7) {
    $dbResultsForPrompt['analisis_documentos_repetidos']['nota_visualizacion_lista'] = 'Se entregan los primeros 7 documentos repetidos para mostrar con detalle y botón de descarga; los restantes serán anexados automáticamente como listado de texto.';
    $dbResultsForPrompt['analisis_documentos_repetidos']['documentos_repetidos'] = array_slice($dbResultsForPrompt['analisis_documentos_repetidos']['documentos_repetidos'], 0, 7);
}
if ($excelExportInfo !== null) {
    $dbResultsForPrompt['planilla_excel_generada'] = [
        'nombre_archivo' => $excelExportInfo['filename'],
        'hoja' => $excelExportInfo['sheet_title'],
        'total_filas_exportadas' => $excelExportInfo['rows_count'],
        'columnas_incluidas' => $excelExportInfo['columns']
    ];
}

// PASO 3: Construcción de la respuesta final con Gemini (RAG)
$instruccionesOrdenDetalle = $ordenAntiguos
    ? "3. ORDEN POR FECHA DE DOCUMENTO MÁS ANTIGUA: El usuario solicitó los documentos más antiguos. La lista `documentos` ya viene ordenada desde la fecha de documento (`fecha_documento`) más antigua hacia adelante (`documentos[0]` es el más antiguo). Respeta estrictamente ese orden ascendente y muestra todos los documentos entregados en `documentos`."
    : "3. ORDEN POR FECHA DE DOCUMENTO MÁS RECIENTE: La lista `documentos` viene ordenada desde la fecha de documento (`fecha_documento`) más reciente hacia atrás (`documentos[0]` es el más reciente). Si pide 'el último documento' en singular, destaca `documentos[0]`; si pide una lista de documentos, muéstralos respetando este orden.";

if ($esConsultaRelacionados) {
    $instruccionesEspecificas = "3. CONSULTA DE DOCUMENTOS RELACIONADOS, SEGUIMIENTO Y DUPLICADOS: El usuario está consultando sobre los documentos relacionados (Seguimiento) de un documento específico (como una carta u oficio escrito con su código completo ej. `SCINC/IF/1140/2026` o abreviado ej. `1140/2026`). Responde utilizando EXCLUSIVAMENTE los datos en `analisis_documentos_relacionados`:
   - Si `documento_encontrado` es `false`, indícale claramente que no se encontró ningún documento con ese código/número dentro de su contrato.
   - Para cada elemento en `analisis_por_documento`, identifica primero el `documento_consultado` (`tipo_documento`, `num_documento`, `materia`, `fecha_documento`, `estado_documento`, `responsable`).
   - Si el usuario pregunta **si tiene documentos relacionados**: responde claramente **Sí** o **No** según `tiene_documentos_relacionados`, e indícale cuántos tiene y cuáles son.
   - Si el usuario pregunta **cuántos documentos relacionados tiene**: indica tanto la cantidad de **documentos relacionados únicos** (`cantidad_documentos_relacionados_unicos`) como la cantidad total de **registros vinculados en seguimiento** (`cantidad_registros_documentos_relacionados`, que puede ser mayor si alguno está repetido/duplicado), y lista los documentos encontrados en `documentos_relacionados`.
   - Si el usuario pregunta **si tiene documentos relacionados duplicados**: responde claramente **Sí** o **No** según `tiene_documentos_relacionados_duplicados`. Si `tiene_documentos_relacionados_duplicados` es `true`, detalla cada elemento de `duplicados_detectados` indicando su `tipo_documento`, `num_documento`, `materia`, cuántas veces está repetido (`veces_repetido`) y el motivo (`tipo_duplicado`).
   - Siempre que menciones un documento (ya sea el consultado o sus documentos relacionados), incluye su enlace de descarga directa en formato Markdown: `[Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)` donde ID es su `id_documento`.";
} elseif ($esConsultaRepetidos) {
    $instruccionesEspecificas = "3. EVALUACIÓN DIFERENCIADA DE DOCUMENTOS REPETIDOS (`modo_evaluacion`: `'contrato'` vs `'seguimiento'`): El usuario solicitó evaluar documentos repetidos. Responde utilizando EXCLUSIVAMENTE los datos de `analisis_documentos_repetidos` y NUNCA mezcles ambos tipos de evaluación:
   - Revisa primero el campo `modo_evaluacion` dentro de `analisis_documentos_repetidos`:
     - **Caso A (`modo_evaluacion` = `'contrato'`)**: El usuario preguntó por **los documentos repetidos asociados al contrato** (ej. 'Podrías decirme los documentos repetidos del contrato'). Deja claro en el título y explicación que estás informando únicamente los **documentos principales asociados al contrato** que comparten el mismo Número de Documento (`num_documento`) o Número de Providencia (`num_providencia`), sin mezclar registros de seguimiento.
     - **Caso B (`modo_evaluacion` = `'seguimiento'`)**: El usuario preguntó **si hay documentos de seguimiento repetidos en los documentos de su contrato** (ej. 'Podrías decirme si hay documentos de seguimiento repetidos en los documentos de mi contrato'). Deja claro en el título y explicación que estás informando únicamente los **documentos del contrato que presentan documentos de seguimiento / relacionados repetidos** dentro de su historial de seguimiento (`numero_seguimiento`), indicando en cada caso qué documento de seguimiento se repite (`detalle_seguimiento_repetido`), sin mezclar duplicados generales de documentos del contrato.
   - Indica cuántos documentos del contrato fueron evaluados (`total_documentos_evaluados_en_periodo`) y cuántos resultaron con repetición según el modo consultado (`total_documentos_repetidos_encontrados`).
   - Si `total_documentos_repetidos_encontrados` es `0`, responde claramente que no se encontraron repeticiones bajo ese criterio específico.
   - Si existen documentos en `documentos_repetidos`, lista cada uno indicando su `tipo_documento`, `num_documento`, `materia`, `fecha_documento`, `responsable`, `veces_repetido`, su `motivo_repeticion` (y `detalle_seguimiento_repetido` cuando `modo_evaluacion` es `'seguimiento'`), incluyendo el enlace `[Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)`.";
} elseif ($esConsultaContenidoArchivo) {
    $instruccionesEspecificas = "3. ANÁLISIS, LECTURA Y RESUMEN DE LO QUE INFORMA EL DOCUMENTO Y SUS ARCHIVOS ASOCIADOS: El usuario solicita un resumen, explicación o información sobre un documento específico (ej. `SCINC/IF/1001/2026` o `ORD.IF : N°1348/2026`). Responde utilizando los datos de `analisis_contenido_documento` Y, cuando esté presente, el contenido de los archivos binarios adjuntos en la petición (`inline_data` PDF/imágenes) o `texto_extraido_local`:
   - Si `documento_encontrado` es `false`, indícale claramente que no se encontró ningún documento con ese código dentro de su contrato.
   - Si `documento_encontrado` es `true`, entrega siempre una respuesta estructurada y completa con toda la información disponible del documento:
     1. **Resumen Ejecutivo de lo que informa el documento**: sintetiza de forma clara y directa qué informa, comunica o solicita el documento a partir de su `materia`, `antecedente`, `incluye`, `comentario`, `bitacora_respuestas`, `acciones_requeridas` y el contenido de su archivo (`inline_data` y/o `texto_extraido_local`). Incluso si el registro no tiene un archivo PDF binario cargado (`tiene_archivo_cargado` = `false`) o si su archivo es escaneado, NUNCA dejes de entregar el resumen completo basado en la materia, antecedente, comentario, bitácora, participantes y seguimiento registrados en el sistema.
     2. **Ficha Técnica del Documento**: detalla ordenadamente `tipo_documento`, `num_documento`, `num_providencia`, `num_proceso`, `fecha_documento`, `fecha_recepcion`, `fecha_plazo`, `estado_documento`, `flujo_documento`, `remitente` (`cargo_remitente`), `destinatario` (`cargo_destinatario`), `responsable` y `nombre_subcontrato`.
     3. **Archivos Asociados, Bitácora y Seguimiento**:
        - Si `archivo_principal.tiene_archivo_cargado` es `true`, muestra el nombre y tamaño del archivo con su enlace `[Descargar Archivo Principal](negocio/descargarDocumento.php?idDocumento=ID)`; si no tiene archivo binario cargado, incluye igualmente el enlace `[Descargar / Ver Documento](negocio/descargarDocumento.php?idDocumento=ID)`.
        - Si existen archivos en `archivos_adjuntos`, detállalos junto con su enlace `[Descargar Adjunto](negocio/descargarDocumento.php?idAdjunto=ID_ADJUNTO)`.
        - Si existen registros en `bitacora_respuestas` o `documentos_relacionados_seguimiento`, menciónalos brevemente como parte del contexto y gestión del documento.";
} elseif ($esConsultaTiempos) {
    $instruccionesEspecificas = "3. CONSULTA DE TIEMPOS Y MÉTRICAS ENTRE FECHAS DE DOCUMENTOS: El usuario está consultando por tiempos de gestión, demora promedio en pasar de Pendiente a Cerrado, diferencia entre fechas de documentos o cumplimiento de plazos. Responde utilizando EXCLUSIVAMENTE los datos calculados en `metricas_tiempos_fechas_documentos`:
   - Entrega primero las **métricas generales del contrato** (`resumen_general_tiempos`):
     - **Tiempo promedio en pasar de Pendiente a Cerrado** (`promedio_dias_pendiente_a_cerrado` en días), indicando también el mínimo (`minimo_dias_cierre`), máximo (`maximo_dias_cierre`), el promedio medido desde `fecha_documento` a cierre (`promedio_dias_fecha_documento_a_cierre`) y desde `fecha_recepcion` a cierre (`promedio_dias_fecha_recepcion_a_cierre`).
     - **Tiempo promedio entre Fecha del Documento y Fecha de Recepción** (`promedio_dias_emision_a_recepcion`).
     - **Plazo promedio asignado** (`promedio_dias_plazo_otorgado`) y **cumplimiento de plazos** (`cerrados_en_plazo`, `cerrados_fuera_de_plazo`, `porcentaje_cumplimiento_plazo`).
     - **Tiempo promedio de espera actual de los documentos Pendientes** (`promedio_dias_espera_pendientes_actuales`).
   - Además, presenta el **desglose clasificado** según lo que haya pedido el usuario (o incluye un resumen por **Responsable / Usuario** de `tiempos_por_responsable` y por **Tipo de Documento** de `tiempos_por_tipo_documento`; si pregunta por mes, año, flujo, subcontrato o remitente, usa `tiempos_por_mes`, `tiempos_por_anio`, `tiempos_por_flujo`, `tiempos_por_subcontrato` o `tiempos_por_remitente`).
4. PROHIBIDO INVENTAR CIFRAS: Usa exactamente los promedios en días y conteos provistos en `metricas_tiempos_fechas_documentos`.";
} elseif ($esConsultaCantidad) {
    $instruccionesEspecificas = "3. CONSULTA DE CIFRAS, CANTIDADES Y CLASIFICACIÓN DE DOCUMENTOS POR TODOS SUS ATRIBUTOS: El usuario está consultando por cifras, cantidades, totales o clasificación de documentos a través de cualquiera de sus atributos (número/código de proceso como `4.2.0.0`, tipo de documento, materia, remitente, destinatario, responsable, fechas, mes, año, estado, flujo o subcontrato). Responde utilizando EXCLUSIVAMENTE los conteos contenidos en `estadisticas_historicas_documentos`:
   - Indica siempre de forma destacada el `total_historico_documentos` que cumple con los criterios consultados (ver `filtros_aplicados_en_conteo`), junto con cuántos están en estado **Pendiente** y **Cerrado** (`desglose_por_estado`) y cuántos son de **Entrada** y **Salida** (`desglose_por_flujo`).
   - Según el atributo por el que pregunte o pida clasificar el usuario, presenta el desglose correspondiente:
     - **Por Número / Código de Proceso** (ej. '¿cuántos documentos tienen código de proceso 4.2.0.0?' o 'clasificar por proceso'): usa `desglose_por_num_proceso` (y muestra también su distribución por `desglose_por_tipo_documento` y `desglose_por_responsable`).
     - **Por Tipo de Documento**: usa `desglose_por_tipo_documento` (Oficio, Carta, Informe, etc.).
     - **Por Materia / Asunto**: usa `desglose_por_materia`.
     - **Por Remitente**: usa `desglose_por_remitente`.
     - **Por Destinatario**: usa `desglose_por_destinatario`.
     - **Por Responsable / Usuario**: usa `desglose_por_responsable`.
     - **Por Fechas (Fecha exacta, Mes o Año)**: usa `desglose_por_fecha_documento`, `desglose_por_mes` o `desglose_por_anio`.
     - **Por Subcontrato**: usa `desglose_por_subcontrato`.
4. PROHIBIDO RETORNAR DETALLE INDIVIDUAL DE DOCUMENTOS: En consultas de cifras, cantidades y clasificaciones, entrega únicamente los números, totales y desgloses estadísticos solicitados.";
} else {
    $instruccionesEspecificas = "{$instruccionesOrdenDetalle}
4. VARIABLES DE DOCUMENTOS: Cuando muestres uno o más documentos, incluye sus datos clave de forma ordenada:
   - **N° Documento / Providencia** (`num_documento` / `num_providencia`)
   - **Tipo de Documento** (`tipo_documento`)
   - **Materia** (`materia`)
   - **Fecha del Documento** (`fecha_documento`), **Fecha de Recepción** (`fecha_recepcion`) y, si el documento está Cerrado, su **Fecha de Cierre** (`fecha_cierre`) y **Días en cerrar** (`dias_en_cerrar`)
   - **Estado** (`estado_documento`) y **Flujo** (`flujo_documento`)
   - **Responsable** (`responsable`)
   - **Remitente** (`remitente` y `cargo_remitente` si aplica)
   - **Destinatario** (`destinatario` y `cargo_destinatario` si aplica)
5. ENLACES DE DESCARGA Y LÍMITE DE 7 DOCUMENTOS CON DETALLE: Cuando la consulta entregue una lista de documentos, muestra con ficha detallada y enlace `[Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)` únicamente hasta los primeros 7 documentos. Si hay más de 7 documentos (`total_documentos_encontrados > 7`), menciona el total encontrado y detalla esos primeros 7 documentos (el resto de los documentos del 8° en adelante se listarán debajo únicamente como texto simple sin botones).
6. Si la lista `documentos` está vacía para ese filtro, indícale amablemente que no se encontraron documentos con ese criterio dentro de su contrato y menciónale los responsables disponibles en `responsables_del_contrato` si buscaba por responsable.";
}

if ($excelExportInfo !== null) {
    $instruccionesEspecificas .= "\n6. PLANILLA EXCEL GENERADA: Se ha generado una planilla Excel que contiene ÚNICAMENTE la información solicitada por el usuario ({$excelExportInfo['rows_count']} filas y las columnas: " . implode(', ', $excelExportInfo['columns']) . "). Confírmale brevemente al usuario qué información se incluyó en su planilla.";
}

$jsonFlagsPrompt = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0);
$datosJsonPrompt = json_encode($dbResultsForPrompt, $jsonFlagsPrompt);
if ($datosJsonPrompt === false) {
    $datosJsonPrompt = '{}';
}

$stage2Prompt = "Eres el asistente de Inteligencia Artificial del Sistema de Gestión Documental (SGD) de Axioma.
Fecha actual del sistema: {$fechaActual} (Año {$anioActual}).
Usuario en sesión: {$userFullName} (Usuario: {$userLoginName}).
Contrato asignado al usuario: \"{$userContractName}\" (ID: {$userContractId}).

Tu objetivo es responder de forma directa, profesional, clara y amable a la consulta del usuario basándote EXCLUSIVAMENTE en los datos reales recuperados de la base de datos para su contrato asignado.

Consulta del usuario: \"" . $query . "\"

Datos reales del sistema (filtrados por el contrato \"{$userContractName}\" del usuario en sesión):
" . $datosJsonPrompt . "

Instrucciones obligatorias para elaborar tu respuesta:
1. Responde en español neutro de forma directa y clara. Si el usuario cometió algún error menor de ortografía o tilde al escribir el nombre de un responsable, remitente, destinatario o término (por ejemplo, 'Jefe Asesoria' por 'Jefe Asesoría'), entiéndelo con naturalidad y muéstrale directamente la información encontrada.
2. RESPONSABLES Y USUARIOS DEL CONTRATO: Ten en cuenta que todos los usuarios listados en `responsables_del_contrato` (como 'Jefe Asesoria', 'Encargada De Gestion', 'Jefe Area Proyectos', 'Jefe Area Construccion', 'Asesor Legal', etc.) SON responsables que pertenecen dentro del contrato asignado al usuario (\"{$userContractName}\"). NUNCA rechaces ni bloquees consultas sobre ellos por motivos de seguridad o permisos.
{$instruccionesEspecificas}
7. NO utilices jerga técnica de base de datos ni menciones consultas SQL o estructuras JSON.";

$timeoutPaso2 = !empty($inlinePartsDocumento) ? 22 : 20;
$stage2Res = llamarGemini($apiKey, $stage2Prompt, false, $timeoutPaso2, $inlinePartsDocumento);

// Sumar tokens del Paso 1 + Paso 2
$tokensEntrada = (int)($stage1Res['usage']['prompt_tokens'] ?? 0) + (int)($stage2Res['usage']['prompt_tokens'] ?? 0);
$tokensSalida  = (int)($stage1Res['usage']['completion_tokens'] ?? 0) + (int)($stage2Res['usage']['completion_tokens'] ?? 0);
$tokensTotales = (int)($stage1Res['usage']['total_tokens'] ?? 0) + (int)($stage2Res['usage']['total_tokens'] ?? 0);
$modeloUsado   = $stage2Res['model_used'] ?? ($stage1Res['model_used'] ?? 'fallback-local');
// Registrar el consumo por contrato y usuario en BD
registrarConsumoIA(
    $cnx,
    (int)$userContractId,
    (int)$userId,
    $modeloUsado,
    $intent,
    $tokensEntrada,
    $tokensSalida,
    $tokensTotales,
    $query
);
try {
    @mysqli_close($cnx);
} catch (Throwable $e) {
}

$finalAnswer = isset($stage2Res['text'])
    ? $stage2Res['text']
    : generarRespuestaFallbackLocal($dbResults, $esConsultaTiempos, $esConsultaCantidad, $userContractName, $filters, $esConsultaRelacionados, $esConsultaRepetidos, $esConsultaContenidoArchivo);

// Garantizar que si cualquier consulta entrega más de 7 documentos, solo los primeros 7 tengan botones de Descargar y Detalle,
// y el resto de los documentos (del 8° en adelante) se listen simplemente como texto
$finalAnswer = aplicarReglaMaximoSieteDocumentosConDetalle(
    $finalAnswer,
    $dbResults,
    $esConsultaRelacionados,
    $esConsultaRepetidos,
    $esConsultaContenidoArchivo,
    $esConsultaTiempos,
    $esConsultaCantidad
);

if ($excelExportInfo !== null) {
    $colsTexto = implode(', ', $excelExportInfo['columns']);
    $bloqueExcelMd = "\n\n---\n### 📊 Planilla Excel Generada\n"
        . "- **Registros incluidos:** **{$excelExportInfo['rows_count']}**\n"
        . "- **Columnas incluidas:** {$colsTexto}\n"
        . "- [Descargar Planilla Excel ({$excelExportInfo['filename']})]({$excelExportInfo['url']})";
    if (strpos($finalAnswer, 'descargarExcelChatIA.php') === false) {
        $finalAnswer .= $bloqueExcelMd;
    }
}

if (ob_get_length()) {
    @ob_clean();
}

$outFlags = JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0);
$jsonOut = json_encode([
    'success' => true,
    'answer' => limpiarTextoUtf8Seguro($finalAnswer),
    'intent' => $intent,
    'filters_detected' => $searchParams,
    'excel_export' => $excelExportInfo
], $outFlags);

echo $jsonOut !== false ? $jsonOut : json_encode([
    'success' => true,
    'answer' => 'Se procesó la consulta pero ocurrió un problema de codificación al formatear la salida.',
    'intent' => $intent
]);
