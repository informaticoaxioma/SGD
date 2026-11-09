<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mail
 *
 * @author vfernandez
 */
class Mail
{

    /**
     * 
     * @param String $titulo titulo del correo
     * @param String $mensaje Cuerpo o mensaje del correo
     * @param String $correo Direccion de destino del correo
     */
    public function enviarMail($titulo, $mensaje, $correo)
    {

        if (!isset($mail)) {
            require_once('class.phpmailer.php');
            $mail = new PHPMailer;
        }

        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "mail.axioma.cl"; // SMTP a utilizar. Por ej. smtp.elserver.com
        $mail->Username = "sisaxioma@axioma.cl"; // Correo completo a utilizar
        $mail->Password = "2%9PLZV;e903[h8&dg"; // Contraseña
        $mail->Port = 587; // Puerto a utilizar
        $mail->From = "sisaxioma@axioma.cl"; // Desde donde enviamos (Para mostrar)
        $mail->FromName = "Sistema de gestión documental";
        $mail->AddAddress($correo); // Esta es la dirección a donde enviamos

        $mail->IsHTML(true); // El correo se envía como HTML
        $mail->Subject = $titulo; // Este es el titulo del email.
        $body = $mensaje;
        $mail->CharSet = 'UTF-8';
        $mail->Body = $body; // Mensaje a enviar
        $mail->AltBody = "";
        $mail->ContentType = "text/html";
        try {


            $mail->Send(); // Envía el correo.
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * 
     * @param string $nombreUsuario nombre del usuario
     * @param String $contrasena Contraseña del usuario
     * @return string Devuelve el cuerpo del mensaje en formato html
     */
    public function generarBodyMail($nombreUsuario, $contrasenaMail, $contrasena)
    {
        try {

            $tabla = "<h1><strong>Sistema de gestión documental</strong></h1>";
            $tabla .= "<hr>";
            $tabla .= "<table>"
                . "<tr>"
                . "<td>"
                . "Se informa que se ha creado una cuenta de usuario en el sistema Gestor Documental de Axioma Ingenieros Consultores SA. con las siguientes credenciales:"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "Nombre Usuario: <strong>" . $nombreUsuario . "</strong>"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "Contraseña: <strong>" . $contrasena . "</strong>"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "Link de ingreso:"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "<a href='http://sgd.axioma.cl' target='_blank'>http://sgd.axioma.cl</a>"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "En caso de requerir información adicional sobre el funcionamiento del sistema favor contactar al encargado de documentación de su contrato, si desconoce esta información, favor aclarar con su residente. "
                . "</td>"
                . "</tr>"


                . "</table>";
            $tabla .= "<hr>";
            $tabla .= "<div style='width:50%'> <img alt='logoAxioma' src='http://consultoravial.cl/aplicaciones/boletasGarantia/media/logoAxiomaOficial.png'/>  </div>";

            return $tabla;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function generarBodyMailGenerico($asunto)
    {
        try {

            $tabla = "<h1><strong>Sistema de gestión documental</strong></h1>";
            $tabla .= "<hr>";
            $tabla .= "<table>"
                . "<tr>"
                . "<td>"
                . $asunto
                . "</td>"
                . "</tr>"
                . "</table>";
            $tabla .= "<hr>";
            //    $tabla.="<div style='width:50%'> <img alt='logoAxioma' src='http://consultoravial.cl/aplicaciones/boletasGarantia/media/logoAxiomaOficial.png'/></div>";

            return $tabla;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function enviarMailResumenMes($mensaje, $correo)
    {

        if (!isset($mail)) {
            require_once('class.phpmailer.php');
            $mail = new PHPMailer;
        }

        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "mail.axioma.cl"; // SMTP a utilizar. Por ej. smtp.elserver.com
        $mail->Username = "sisaxioma@axioma.cl"; // Correo completo a utilizar
        $mail->Password = "2%9PLZV;e903[h8&dg"; // Contraseña
        $mail->Port = 587; // Puerto a utilizar
        $mail->From = "sisaxioma@axioma.cl"; // Desde donde enviamos (Para mostrar)
        $mail->FromName = "Sistema de gestión documental";
        $mail->AddAddress($correo); // Esta es la dirección a donde enviamos

        $mail->IsHTML(true); // El correo se envía como HTML
        $mail->Subject = "Resumen mensual gestorDocumental."; // Este es el titulo del email.
        $body = $mensaje;
        $mail->CharSet = 'UTF-8';
        $mail->Body = $body; // Mensaje a enviar
        $mail->AltBody = "";
        $mail->ContentType = "text/html";

        $mail->addAttachment("Resumen_mensual.pdf");
        $mail->Send(); // Envía el correo.
    }

    /**
     * Genera el cuerpo HTML del correo de alerta de atraso dirigido específicamente al RESPONSABLE principal del documento.
     *
     * @param array $datosDoc Datos del documento atrasado
     * @return string HTML del correo para el Responsable
     */
    public function generarBodyMailAtrasoResponsable($datosDoc)
    {
        $nombreDestinatario = htmlspecialchars($datosDoc['nombre_destinatario'] ?? 'Responsable', ENT_QUOTES, 'UTF-8');
        $tipoDoc            = htmlspecialchars($datosDoc['tipo_documento'] ?? 'Documento', ENT_QUOTES, 'UTF-8');
        $numDoc             = htmlspecialchars($datosDoc['num_documento'] ?? 'S/N', ENT_QUOTES, 'UTF-8');
        $numProv            = htmlspecialchars($datosDoc['num_providencia'] ?? 'S/I', ENT_QUOTES, 'UTF-8');
        $numProceso         = htmlspecialchars($datosDoc['num_proceso'] ?? 'S/I', ENT_QUOTES, 'UTF-8');
        $materia            = htmlspecialchars($datosDoc['materia'] ?? 'Sin materia', ENT_QUOTES, 'UTF-8');
        $incluye            = htmlspecialchars($datosDoc['incluye'] ?? 'S/I', ENT_QUOTES, 'UTF-8');
        $comentario         = htmlspecialchars($datosDoc['comentario'] ?? 'Sin comentarios', ENT_QUOTES, 'UTF-8');
        $fechaRecepcion     = htmlspecialchars($datosDoc['fecha_recepcion_fmt'] ?? ($datosDoc['fecha_recepcion'] ?? 'S/I'), ENT_QUOTES, 'UTF-8');
        $fechaPlazo         = htmlspecialchars($datosDoc['fecha_plazo_fmt'] ?? ($datosDoc['fecha_plazo'] ?? 'S/I'), ENT_QUOTES, 'UTF-8');
        $nombreContrato     = htmlspecialchars($datosDoc['nombre_contrato'] ?? '', ENT_QUOTES, 'UTF-8');
        $nombreSubcontrato  = htmlspecialchars($datosDoc['nombre_subcontrato'] ?? '', ENT_QUOTES, 'UTF-8');
        $listaCorresp       = htmlspecialchars($datosDoc['nombres_corresponsables'] ?? '', ENT_QUOTES, 'UTF-8');
        $diasAtraso         = isset($datosDoc['dias_atraso']) ? (int)$datosDoc['dias_atraso'] : 0;

        $textoAtraso = ($diasAtraso <= 0)
            ? "Plazo cumplido hoy ({$fechaPlazo})"
            : ($diasAtraso === 1 ? "1 día de atraso (venció el {$fechaPlazo})" : "{$diasAtraso} días de atraso (venció el {$fechaPlazo})");

        $asuntoHtml = "<div style='background-color:#fdf2f2;border-left:5px solid #d9534f;padding:12px 15px;margin-bottom:15px;font-family:Arial,sans-serif;'>"
            . "<h3 style='margin:0 0 8px 0;color:#b52b27;'>⚠️ Alerta de Documento con Fecha de Plazo Cumplida (En Atraso) — Rol: RESPONSABLE</h3>"
            . "<p style='margin:0;color:#333;'>Estimado(a) <strong>{$nombreDestinatario}</strong>,<br/>"
            . "Se le informa que el siguiente documento asignado a usted como <strong>Responsable principal</strong> ya cumplió su <strong>Fecha de Plazo</strong> y aún se encuentra en estado <strong>Pendiente</strong>. "
            . "Por favor, gestione su respuesta o cierre en el Sistema de Gestión Documental a la brevedad.</p>"
            . "</div>"
            . "<table style='border-collapse:collapse;width:100%;max-width:680px;font-family:Arial,sans-serif;font-size:14px;'>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;width:35%;'><strong>Rol Asignado:</strong></td>"
            . "<td style='padding:6px 10px;border:1px solid #ddd;'><span style='background:#d9534f;color:#fff;padding:2px 8px;border-radius:3px;font-weight:bold;'>Responsable Principal</span></td></tr>"
            . ($nombreContrato !== '' ? "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Contrato / Subcontrato:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$nombreContrato}" . ($nombreSubcontrato !== '' ? " — {$nombreSubcontrato}" : "") . "</td></tr>" : "")
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Tipo y N° Documento:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'><strong>{$tipoDoc} N° {$numDoc}</strong></td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Número Providencia:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$numProv}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Número Proceso:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$numProceso}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Materia:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$materia}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Incluye:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$incluye}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Comentarios:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$comentario}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Fecha Recepción:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$fechaRecepcion}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Fecha Plazo (Vencimiento):</strong></td><td style='padding:6px 10px;border:1px solid #ddd;color:#d9534f;font-weight:bold;'>{$fechaPlazo}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Estado de Atraso:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;color:#d9534f;font-weight:bold;'>{$textoAtraso}</td></tr>"
            . ($listaCorresp !== '' ? "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Corresponsable(s) en copia de gestión:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$listaCorresp}</td></tr>" : "")
            . "</table>"
            . "<p style='margin-top:14px;font-family:Arial,sans-serif;font-size:13px;'>Ingrese al sistema para gestionar este documento: <a href='http://sgd.axioma.cl' target='_blank'>http://sgd.axioma.cl</a></p>";

        return $this->generarBodyMailGenerico($asuntoHtml);
    }

    /**
     * Genera el cuerpo HTML del correo de alerta de atraso dirigido específicamente a un CORRESPONSABLE del documento.
     *
     * @param array $datosDoc Datos del documento atrasado
     * @return string HTML del correo para el Corresponsable
     */
    public function generarBodyMailAtrasoCorresponsable($datosDoc)
    {
        $nombreDestinatario = htmlspecialchars($datosDoc['nombre_destinatario'] ?? 'Corresponsable', ENT_QUOTES, 'UTF-8');
        $nombreResponsable  = htmlspecialchars($datosDoc['nombre_responsable_principal'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8');
        $tipoDoc            = htmlspecialchars($datosDoc['tipo_documento'] ?? 'Documento', ENT_QUOTES, 'UTF-8');
        $numDoc             = htmlspecialchars($datosDoc['num_documento'] ?? 'S/N', ENT_QUOTES, 'UTF-8');
        $numProv            = htmlspecialchars($datosDoc['num_providencia'] ?? 'S/I', ENT_QUOTES, 'UTF-8');
        $numProceso         = htmlspecialchars($datosDoc['num_proceso'] ?? 'S/I', ENT_QUOTES, 'UTF-8');
        $materia            = htmlspecialchars($datosDoc['materia'] ?? 'Sin materia', ENT_QUOTES, 'UTF-8');
        $incluye            = htmlspecialchars($datosDoc['incluye'] ?? 'S/I', ENT_QUOTES, 'UTF-8');
        $comentario         = htmlspecialchars($datosDoc['comentario'] ?? 'Sin comentarios', ENT_QUOTES, 'UTF-8');
        $fechaRecepcion     = htmlspecialchars($datosDoc['fecha_recepcion_fmt'] ?? ($datosDoc['fecha_recepcion'] ?? 'S/I'), ENT_QUOTES, 'UTF-8');
        $fechaPlazo         = htmlspecialchars($datosDoc['fecha_plazo_fmt'] ?? ($datosDoc['fecha_plazo'] ?? 'S/I'), ENT_QUOTES, 'UTF-8');
        $nombreContrato     = htmlspecialchars($datosDoc['nombre_contrato'] ?? '', ENT_QUOTES, 'UTF-8');
        $nombreSubcontrato  = htmlspecialchars($datosDoc['nombre_subcontrato'] ?? '', ENT_QUOTES, 'UTF-8');
        $diasAtraso         = isset($datosDoc['dias_atraso']) ? (int)$datosDoc['dias_atraso'] : 0;

        $textoAtraso = ($diasAtraso <= 0)
            ? "Plazo cumplido hoy ({$fechaPlazo})"
            : ($diasAtraso === 1 ? "1 día de atraso (venció el {$fechaPlazo})" : "{$diasAtraso} días de atraso (venció el {$fechaPlazo})");

        $asuntoHtml = "<div style='background-color:#fcf8e3;border-left:5px solid #f0ad4e;padding:12px 15px;margin-bottom:15px;font-family:Arial,sans-serif;'>"
            . "<h3 style='margin:0 0 8px 0;color:#8a6d3b;'>🔔 Aviso de Documento en Atraso — Rol: CORRESPONSABLE</h3>"
            . "<p style='margin:0;color:#333;'>Estimado(a) <strong>{$nombreDestinatario}</strong>,<br/>"
            . "Se le informa que el siguiente documento, en el cual usted figura asignado en calidad de <strong>Corresponsable</strong>, ya cumplió su <strong>Fecha de Plazo</strong> y continúa en estado <strong>Pendiente</strong>. "
            . "Se solicita coordinar o apoyar al Responsable principal (<strong>{$nombreResponsable}</strong>) para su pronta resolución.</p>"
            . "</div>"
            . "<table style='border-collapse:collapse;width:100%;max-width:680px;font-family:Arial,sans-serif;font-size:14px;'>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;width:35%;'><strong>Rol Asignado:</strong></td>"
            . "<td style='padding:6px 10px;border:1px solid #ddd;'><span style='background:#f0ad4e;color:#fff;padding:2px 8px;border-radius:3px;font-weight:bold;'>Corresponsable</span></td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Responsable Principal:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'><strong>{$nombreResponsable}</strong></td></tr>"
            . ($nombreContrato !== '' ? "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Contrato / Subcontrato:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$nombreContrato}" . ($nombreSubcontrato !== '' ? " — {$nombreSubcontrato}" : "") . "</td></tr>" : "")
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Tipo y N° Documento:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'><strong>{$tipoDoc} N° {$numDoc}</strong></td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Número Providencia:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$numProv}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Número Proceso:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$numProceso}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Materia:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$materia}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Incluye:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$incluye}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Comentarios:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$comentario}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Fecha Recepción:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;'>{$fechaRecepcion}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Fecha Plazo (Vencimiento):</strong></td><td style='padding:6px 10px;border:1px solid #ddd;color:#d9534f;font-weight:bold;'>{$fechaPlazo}</td></tr>"
            . "<tr><td style='padding:6px 10px;border:1px solid #ddd;background:#f9f9f9;'><strong>Estado de Atraso:</strong></td><td style='padding:6px 10px;border:1px solid #ddd;color:#d9534f;font-weight:bold;'>{$textoAtraso}</td></tr>"
            . "</table>"
            . "<p style='margin-top:14px;font-family:Arial,sans-serif;font-size:13px;'>Ingrese al sistema para revisar el seguimiento de este documento: <a href='http://sgd.axioma.cl' target='_blank'>http://sgd.axioma.cl</a></p>";

        return $this->generarBodyMailGenerico($asuntoHtml);
    }

    /**
     * Evalúa y envía correos diferenciados al Responsable y a los Corresponsables
     * para los documentos pendientes (`id_estado_doc = 1`) que ya cumplieron o superaron su `fecha_plazo`.
     *
     * @param int|null $idContrato   Opcional: limitar a los documentos de un contrato específico
     * @param int|null $idDocumento  Opcional: evaluar un único documento específico (ej. al crear/actualizar)
     * @param bool     $forzarEnvio  Si es true, envía el correo aunque ya se haya notificado hoy
     * @return array Resumen de notificaciones enviadas
     */
    public function notificarDocumentosAtrasados($idContrato = null, $idDocumento = null, $forzarEnvio = false)
    {
        require_once __DIR__ . '/Conexion.php';
        $resumen = [
            'documentos_atrasados_detectados' => 0,
            'correos_responsable_enviados'    => 0,
            'correos_corresponsable_enviados' => 0,
            'omitidos_ya_notificados'         => 0,
            'errores'                         => []
        ];

        $serviceConexion = new Conexion();
        $cnx = $serviceConexion->conectar();
        if (!$cnx || is_string($cnx)) {
            $resumen['errores'][] = 'No se pudo conectar a la base de datos.';
            return $resumen;
        }

        // Crear tabla de control de notificaciones de atraso si no existe (evita correos duplicados por el mismo documento/fecha_plazo)
        $sqlCreateLog = "CREATE TABLE IF NOT EXISTS notificacion_atraso_documento (
            id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
            id_documento INT NOT NULL,
            id_usuario INT NOT NULL,
            rol VARCHAR(25) NOT NULL,
            correo VARCHAR(180) NOT NULL,
            fecha_plazo DATE NOT NULL,
            fecha_envio DATE NOT NULL,
            fecha_hora_envio DATETIME NOT NULL,
            UNIQUE KEY uq_doc_user_rol_plazo (id_documento, id_usuario, rol, fecha_plazo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        @mysqli_query($cnx, $sqlCreateLog);

        // Archivo local de respaldo en caso de que el usuario MySQL no tenga permiso CREATE TABLE
        $archivoControlLocal = rtrim(sys_get_temp_dir(), '/\\') . '/sgd_notificaciones_atraso_log.json';
        $registroLocal = [];
        if (is_file($archivoControlLocal)) {
            $dec = json_decode((string)@file_get_contents($archivoControlLocal), true);
            if (is_array($dec)) {
                $registroLocal = $dec;
            }
        }

        $condiciones = [
            "d.id_estado_doc = 1",
            "d.fecha_plazo IS NOT NULL",
            "d.fecha_plazo NOT IN ('0000-00-00', '1970-01-01', '')",
            "d.fecha_plazo <= CURDATE()"
        ];

        if ($idDocumento !== null && (int)$idDocumento > 0) {
            $condiciones[] = "d.id_documento = " . (int)$idDocumento;
        }
        if ($idContrato !== null && (int)$idContrato > 0) {
            $condiciones[] = "s.id_contrato = " . (int)$idContrato;
        }

        $whereSql = implode(' AND ', $condiciones);

        $sqlDocs = "SELECT d.id_documento,
                           d.fecha_documento,
                           d.fecha_recepcion,
                           d.fecha_plazo,
                           DATEDIFF(CURDATE(), d.fecha_plazo) AS dias_atraso,
                           DATE_FORMAT(d.fecha_recepcion, '%d-%m-%Y') AS fecha_recepcion_fmt,
                           DATE_FORMAT(d.fecha_plazo, '%d-%m-%Y') AS fecha_plazo_fmt,
                           dd.num_documento,
                           dd.num_providencia,
                           dd.num_proceso,
                           dd.materia,
                           dd.incluye,
                           dd.comentario,
                           dd.id_responsable,
                           COALESCE(td.tipo_documento, 'Documento') AS tipo_documento,
                           COALESCE(s.nombre_subcontrato, '') AS nombre_subcontrato,
                           COALESCE(c.nombre_contrato, '') AS nombre_contrato,
                           u.nombre AS resp_nombre,
                           u.apellido_p AS resp_apellido_p,
                           u.apellido_m AS resp_apellido_m,
                           u.correo AS resp_correo,
                           u.id_estado_usuario AS resp_estado
                    FROM documento d
                    INNER JOIN detalle_documento dd ON d.id_documento = dd.id_documento
                    LEFT JOIN tipo_documento td ON dd.id_tipo_doc = td.id_tipo_doc
                    LEFT JOIN subcontrato s ON d.id_subcontrato = s.id_subcontrato
                    LEFT JOIN contrato c ON s.id_contrato = c.id_contrato
                    LEFT JOIN usuario u ON dd.id_responsable = u.id_usuario
                    WHERE {$whereSql}
                    ORDER BY d.fecha_plazo ASC, d.id_documento ASC";

        $rsDocs = @mysqli_query($cnx, $sqlDocs);
        if (!$rsDocs) {
            mysqli_close($cnx);
            return $resumen;
        }

        while ($doc = mysqli_fetch_assoc($rsDocs)) {
            $resumen['documentos_atrasados_detectados']++;
            $idDocActual = (int)$doc['id_documento'];
            $fechaPlazoRaw = (string)$doc['fecha_plazo'];
            $idResponsable = (int)($doc['id_responsable'] ?? 0);
            $nombreResponsable = trim(($doc['resp_nombre'] ?? '') . ' ' . ($doc['resp_apellido_p'] ?? '') . ' ' . ($doc['resp_apellido_m'] ?? ''));
            $correoResponsable = trim((string)($doc['resp_correo'] ?? ''));

            // Obtener todos los corresponsables asignados a este documento
            $corresponsablesDoc = [];
            $nombresCorresponsablesArr = [];
            $sqlCorr = "SELECT cd.id_usuario_corresponsable AS id_usuario,
                               u.nombre,
                               u.apellido_p,
                               u.apellido_m,
                               u.correo,
                               u.id_estado_usuario
                        FROM corresponsable cd
                        INNER JOIN usuario u ON cd.id_usuario_corresponsable = u.id_usuario
                        WHERE cd.id_documento = {$idDocActual}";
            $rsCorr = @mysqli_query($cnx, $sqlCorr);
            if ($rsCorr) {
                while ($rCorr = mysqli_fetch_assoc($rsCorr)) {
                    $nomC = trim(($rCorr['nombre'] ?? '') . ' ' . ($rCorr['apellido_p'] ?? '') . ' ' . ($rCorr['apellido_m'] ?? ''));
                    if ($nomC !== '') {
                        $nombresCorresponsablesArr[] = $nomC;
                    }
                    $corresponsablesDoc[] = [
                        'id_usuario' => (int)$rCorr['id_usuario'],
                        'nombre'     => $nomC,
                        'correo'     => trim((string)($rCorr['correo'] ?? '')),
                        'estado'     => (int)($rCorr['id_estado_usuario'] ?? 1)
                    ];
                }
                mysqli_free_result($rsCorr);
            }

            $datosBaseCorreo = [
                'id_documento'                 => $idDocActual,
                'tipo_documento'               => $doc['tipo_documento'],
                'num_documento'                => $doc['num_documento'],
                'num_providencia'              => $doc['num_providencia'],
                'num_proceso'                  => $doc['num_proceso'],
                'materia'                      => $doc['materia'],
                'incluye'                      => $doc['incluye'],
                'comentario'                   => $doc['comentario'],
                'fecha_recepcion'              => $doc['fecha_recepcion'],
                'fecha_recepcion_fmt'          => $doc['fecha_recepcion_fmt'],
                'fecha_plazo'                  => $fechaPlazoRaw,
                'fecha_plazo_fmt'              => $doc['fecha_plazo_fmt'],
                'dias_atraso'                  => (int)$doc['dias_atraso'],
                'nombre_contrato'              => $doc['nombre_contrato'],
                'nombre_subcontrato'           => $doc['nombre_subcontrato'],
                'nombre_responsable_principal' => $nombreResponsable !== '' ? $nombreResponsable : 'Sin responsable asignado',
                'nombres_corresponsables'      => implode(', ', $nombresCorresponsablesArr)
            ];

            // 1. Notificar al RESPONSABLE principal
            if ($idResponsable > 0 && $correoResponsable !== '') {
                $claveUnicaResp = "{$idDocActual}_{$idResponsable}_RESPONSABLE_{$fechaPlazoRaw}";
                $yaNotificadoResp = !$forzarEnvio && $this->fueNotificadoAtraso($cnx, $idDocActual, $idResponsable, 'RESPONSABLE', $fechaPlazoRaw, $registroLocal, $claveUnicaResp);

                if ($yaNotificadoResp) {
                    $resumen['omitidos_ya_notificados']++;
                } else {
                    $datosResp = $datosBaseCorreo;
                    $datosResp['nombre_destinatario'] = $nombreResponsable;
                    $tituloResp = "Alerta de documento en atraso - Responsable (Doc. N° " . ($doc['num_documento'] ?: $idDocActual) . ")";
                    $bodyResp = $this->generarBodyMailAtrasoResponsable($datosResp);

                    try {
                        $this->enviarMail($tituloResp, $bodyResp, $correoResponsable);
                        $this->registrarNotificacionAtraso($cnx, $idDocActual, $idResponsable, 'RESPONSABLE', $correoResponsable, $fechaPlazoRaw, $registroLocal, $claveUnicaResp);
                        $resumen['correos_responsable_enviados']++;
                    } catch (Throwable $e) {
                        $resumen['errores'][] = "Error enviando correo a responsable ({$correoResponsable}) en doc {$idDocActual}: " . $e->getMessage();
                    }
                }
            }

            // 2. Notificar a cada CORRESPONSABLE con correo diferenciado
            foreach ($corresponsablesDoc as $corr) {
                $idUsuCorr = (int)$corr['id_usuario'];
                $correoCorr = $corr['correo'];
                if ($idUsuCorr <= 0 || $correoCorr === '') {
                    continue;
                }
                $claveUnicaCorr = "{$idDocActual}_{$idUsuCorr}_CORRESPONSABLE_{$fechaPlazoRaw}";
                $yaNotificadoCorr = !$forzarEnvio && $this->fueNotificadoAtraso($cnx, $idDocActual, $idUsuCorr, 'CORRESPONSABLE', $fechaPlazoRaw, $registroLocal, $claveUnicaCorr);

                if ($yaNotificadoCorr) {
                    $resumen['omitidos_ya_notificados']++;
                } else {
                    $datosCorr = $datosBaseCorreo;
                    $datosCorr['nombre_destinatario'] = $corr['nombre'];
                    $tituloCorr = "Aviso de documento en atraso - Corresponsable (Doc. N° " . ($doc['num_documento'] ?: $idDocActual) . ")";
                    $bodyCorr = $this->generarBodyMailAtrasoCorresponsable($datosCorr);

                    try {
                        $this->enviarMail($tituloCorr, $bodyCorr, $correoCorr);
                        $this->registrarNotificacionAtraso($cnx, $idDocActual, $idUsuCorr, 'CORRESPONSABLE', $correoCorr, $fechaPlazoRaw, $registroLocal, $claveUnicaCorr);
                        $resumen['correos_corresponsable_enviados']++;
                    } catch (Throwable $e) {
                        $resumen['errores'][] = "Error enviando correo a corresponsable ({$correoCorr}) en doc {$idDocActual}: " . $e->getMessage();
                    }
                }
            }
        }

        mysqli_free_result($rsDocs);
        @file_put_contents($archivoControlLocal, json_encode($registroLocal, JSON_UNESCAPED_UNICODE));
        mysqli_close($cnx);

        return $resumen;
    }

    /**
     * Evalúa y notifica si un documento puntual (por ID) ya cumplió su fecha de atraso.
     */
    public function notificarAtrasoDocumentoPorId($idDocumento, $forzarEnvio = false)
    {
        return $this->notificarDocumentosAtrasados(null, (int)$idDocumento, $forzarEnvio);
    }

    /**
     * Verifica si ya se envió notificación de atraso para la combinación (documento, usuario, rol, fecha_plazo).
     */
    private function fueNotificadoAtraso($cnx, $idDocumento, $idUsuario, $rol, $fechaPlazo, &$registroLocal, $claveUnica)
    {
        if (!empty($registroLocal[$claveUnica])) {
            return true;
        }
        $idDoc = (int)$idDocumento;
        $idUsu = (int)$idUsuario;
        $rolEsc = mysqli_real_escape_string($cnx, $rol);
        $plazoEsc = mysqli_real_escape_string($cnx, $fechaPlazo);
        $sqlCheck = "SELECT id_notificacion FROM notificacion_atraso_documento
                     WHERE id_documento = {$idDoc}
                       AND id_usuario = {$idUsu}
                       AND rol = '{$rolEsc}'
                       AND fecha_plazo = '{$plazoEsc}'
                     LIMIT 1";
        $rs = @mysqli_query($cnx, $sqlCheck);
        if ($rs) {
            $existe = (mysqli_num_rows($rs) > 0);
            mysqli_free_result($rs);
            if ($existe) {
                $registroLocal[$claveUnica] = date('Y-m-d H:i:s');
                return true;
            }
        }
        return false;
    }

    /**
     * Registra el envío de la notificación de atraso en la tabla `notificacion_atraso_documento` y en el registro local.
     */
    private function registrarNotificacionAtraso($cnx, $idDocumento, $idUsuario, $rol, $correo, $fechaPlazo, &$registroLocal, $claveUnica)
    {
        $registroLocal[$claveUnica] = date('Y-m-d H:i:s');
        $idDoc = (int)$idDocumento;
        $idUsu = (int)$idUsuario;
        $rolEsc = mysqli_real_escape_string($cnx, $rol);
        $correoEsc = mysqli_real_escape_string($cnx, $correo);
        $plazoEsc = mysqli_real_escape_string($cnx, $fechaPlazo);
        $sqlIns = "INSERT IGNORE INTO notificacion_atraso_documento
                   (id_documento, id_usuario, rol, correo, fecha_plazo, fecha_envio, fecha_hora_envio)
                   VALUES ({$idDoc}, {$idUsu}, '{$rolEsc}', '{$correoEsc}', '{$plazoEsc}', CURDATE(), NOW())";
        @mysqli_query($cnx, $sqlIns);
    }
}
