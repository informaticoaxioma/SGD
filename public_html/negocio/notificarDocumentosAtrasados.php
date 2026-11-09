<?php
/**
 * Script / Endpoint de notificación de documentos en atraso (`fecha_plazo <= CURDATE()` y estado `Pendiente`).
 * Envía correos diferenciados al Responsable principal y a los Corresponsables asignados.
 *
 * Puede ejecutarse:
 * 1. Automáticamente en segundo plano al iniciar sesión / cargar `pa/inicio.php` (vía AJAX).
 * 2. Mediante tarea programada (Cron CLI): `php /var/www/sgd.axioma.cl/public_html/negocio/notificarDocumentosAtrasados.php`
 * 3. Puntualmente para un documento o contrato vía POST/GET (`idDocumento`, `idContrato`, `forzar`).
 */
@ini_set('display_errors', '0');
@set_time_limit(180);

require_once __DIR__ . '/../data/Mail.php';
require_once __DIR__ . '/../data/Usuario.php';

$esCli = (php_sapi_name() === 'cli');
$idContrato = null;
$idDocumento = null;
$forzarEnvio = false;

if ($esCli) {
    // Ejecución por Cron / CLI: por defecto revisa todos los contratos activos
    if (!empty($argv[1]) && is_numeric($argv[1])) {
        $idContrato = (int)$argv[1];
    }
    if (!empty($argv[2]) && is_numeric($argv[2])) {
        $idDocumento = (int)$argv[2];
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    session_start();
    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada']);
        exit;
    }
    $usuarioSession = $_SESSION['usuario'];
    $idContratoSesion = is_object($usuarioSession) && method_exists($usuarioSession, 'getIdContrato')
        ? (int)$usuarioSession->getIdContrato()
        : 0;
    session_write_close();

    $idDocumento = isset($_REQUEST['idDocumento']) ? (int)$_REQUEST['idDocumento'] : null;
    $idContrato = isset($_REQUEST['idContrato']) ? (int)$_REQUEST['idContrato'] : ($idContratoSesion > 0 ? $idContratoSesion : null);
    $forzarEnvio = !empty($_REQUEST['forzar']);

    // Si es el chequeo automático diario al abrir Inicio (sin idDocumento puntual ni forzar),
    // verificar marca diaria por contrato para no repetir la consulta en cada recarga de página
    if (!$idDocumento && !$forzarEnvio) {
        $hoy = date('Y-m-d');
        $keyContrato = $idContrato !== null ? (int)$idContrato : 0;
        $stampFile = rtrim(sys_get_temp_dir(), '/\\') . "/sgd_check_atraso_{$keyContrato}_{$hoy}.stamp";
        if (is_file($stampFile)) {
            echo json_encode([
                'ok' => true,
                'ya_verificado_hoy' => true,
                'fecha' => $hoy,
                'id_contrato' => $keyContrato
            ]);
            exit;
        }
        @file_put_contents($stampFile, date('Y-m-d H:i:s'));
    }
}

$serviceMail = new Mail();
$resultado = $serviceMail->notificarDocumentosAtrasados($idContrato, $idDocumento, $forzarEnvio);

if ($esCli) {
    echo "[" . date('Y-m-d H:i:s') . "] Notificación de documentos atrasados completada: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo json_encode([
        'ok' => true,
        'resumen' => $resultado
    ], JSON_UNESCAPED_UNICODE);
}

