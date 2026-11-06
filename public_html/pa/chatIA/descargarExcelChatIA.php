<?php
require_once '../../data/Usuario.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    die("Acceso denegado. Debe iniciar sesión en el sistema.");
}

$usuarioSession = $_SESSION['usuario'];
$userContractId = is_object($usuarioSession) && method_exists($usuarioSession, 'getIdContrato')
    ? (int)$usuarioSession->getIdContrato()
    : 0;
session_write_close();

if ($userContractId !== 41) {
    http_response_code(403);
    die("Acceso restringido: Este módulo solo está habilitado para usuarios del contrato autorizado.");
}

$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
if ($token === '' || !preg_match('/^[a-f0-9]{16,64}$/i', $token)) {
    http_response_code(404);
    die("La planilla solicitada ya no se encuentra disponible o ha expirado. Por favor, solicítela nuevamente en el chat.");
}

$exportFilePath = rtrim(sys_get_temp_dir(), '/\\') . '/sgd_chat_excel_' . $token . '.json';
$export = null;
if (is_file($exportFilePath)) {
    $decoded = json_decode((string)@file_get_contents($exportFilePath), true);
    if (is_array($decoded)) {
        $export = $decoded;
    }
}

if (!$export || ($userContractId > 0 && !empty($export['contract_id']) && (int)$export['contract_id'] !== $userContractId)) {
    http_response_code(404);
    die("La planilla solicitada ya no se encuentra disponible o ha expirado. Por favor, solicítela nuevamente en el chat.");
}
$headers = isset($export['headers']) && is_array($export['headers']) ? array_values($export['headers']) : [];
$rows = isset($export['rows']) && is_array($export['rows']) ? array_values($export['rows']) : [];
$sheetTitle = isset($export['sheet_title']) ? (string)$export['sheet_title'] : 'Datos Solicitados';
$filename = isset($export['filename']) ? (string)$export['filename'] : ('Planilla_SGD_' . date('Ymd_His') . '.xlsx');

// Limpiar nombre de hoja para Excel (máx 31 caracteres, sin caracteres inválidos)
$sheetTitle = preg_replace('/[\\\\\/\?\*\:\[\]]/', ' ', $sheetTitle);
$sheetTitle = trim(mb_substr($sheetTitle, 0, 31, 'UTF-8'));
if ($sheetTitle === '') {
    $sheetTitle = 'Datos';
}

// Limpiar nombre de archivo
$filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
if (!preg_match('/\.xlsx$/i', $filename)) {
    $filename .= '.xlsx';
}

/**
 * Convierte un índice de columna base 0 (0, 1, 2...) a letra de columna Excel (A, B, C... AA, AB...)
 */
function colIndexToLetter($index) {
    $index = (int)$index;
    $letter = '';
    while ($index >= 0) {
        $letter = chr(($index % 26) + 65) . $letter;
        $index = (int)floor($index / 26) - 1;
    }
    return $letter;
}

/**
 * Escapa texto de forma segura para XML (eliminando caracteres de control inválidos en XML 1.0)
 */
function xmlEscapeExcel($str) {
    $str = (string)$str;
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);
    return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

if (class_exists('ZipArchive')) {
    $tmpFile = tempnam(sys_get_temp_dir(), 'sgd_xlsx_');
    $zip = new ZipArchive();
    if ($zip->open($tmpFile, ZipArchive::OVERWRITE) === true) {
        $colCount = max(1, count($headers));
        $lastColLetter = colIndexToLetter($colCount - 1);
        $totalSheetRows = count($rows) + (!empty($headers) ? 1 : 0);
        $maxRowNum = max(1, $totalSheetRows);

        // Calcular ancho automático de cada columna según su contenido
        $colWidths = array_fill(0, $colCount, 12);
        foreach ($headers as $cIdx => $hText) {
            $len = mb_strlen((string)$hText, 'UTF-8');
            if ($len + 5 > $colWidths[$cIdx]) {
                $colWidths[$cIdx] = min(55, $len + 5);
            }
        }
        foreach ($rows as $r) {
            $rVals = array_values((array)$r);
            for ($cIdx = 0; $cIdx < $colCount; $cIdx++) {
                $val = isset($rVals[$cIdx]) ? (string)$rVals[$cIdx] : '';
                $len = mb_strlen($val, 'UTF-8');
                if ($len + 4 > $colWidths[$cIdx]) {
                    $colWidths[$cIdx] = min(60, $len + 4);
                }
            }
        }

        $colsXml = '<cols>';
        for ($cIdx = 0; $cIdx < $colCount; $cIdx++) {
            $colNum = $cIdx + 1;
            $width = $colWidths[$cIdx];
            $colsXml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $sheetDataXml = '<sheetData>';
        $currentRowNum = 1;

        // Fila de encabezados (Estilo s="1": fondo institucional #761C19, letra blanca en negrita)
        if (!empty($headers)) {
            $sheetDataXml .= '<row r="' . $currentRowNum . '" ht="22" customHeight="1">';
            foreach ($headers as $cIdx => $hText) {
                $cellRef = colIndexToLetter($cIdx) . $currentRowNum;
                $sheetDataXml .= '<c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . xmlEscapeExcel($hText) . '</t></is></c>';
            }
            $sheetDataXml .= '</row>';
            $currentRowNum++;
        }

        // Filas de datos solicitados (Estilo s="2" para texto y s="3" para números)
        foreach ($rows as $r) {
            $sheetDataXml .= '<row r="' . $currentRowNum . '">';
            $rVals = array_values((array)$r);
            for ($cIdx = 0; $cIdx < $colCount; $cIdx++) {
                $cellRef = colIndexToLetter($cIdx) . $currentRowNum;
                $val = isset($rVals[$cIdx]) ? $rVals[$cIdx] : '';

                if (is_int($val) || is_float($val) || (is_string($val) && preg_match('/^-?\d+(\.\d+)?$/', $val) && !preg_match('/^0\d+$/', $val) && strlen($val) <= 12)) {
                    $sheetDataXml .= '<c r="' . $cellRef . '" s="3" t="n"><v>' . $val . '</v></c>';
                } else {
                    $sheetDataXml .= '<c r="' . $cellRef . '" s="2" t="inlineStr"><is><t>' . xmlEscapeExcel($val) . '</t></is></c>';
                }
            }
            $sheetDataXml .= '</row>';
            $currentRowNum++;
        }
        $sheetDataXml .= '</sheetData>';

        $autoFilterXml = (!empty($headers) && count($rows) > 0)
            ? '<autoFilter ref="A1:' . $lastColLetter . $maxRowNum . '"/>'
            : '';

        $worksheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<dimension ref="A1:' . $lastColLetter . $maxRowNum . '"/>'
            . '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="16"/>'
            . $colsXml
            . $sheetDataXml
            . $autoFilterXml
            . '</worksheet>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="10"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF761C19"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FFD0D0D0"/></left>'
            . '<right style="thin"><color rgb="FFD0D0D0"/></right>'
            . '<top style="thin"><color rgb="FFD0D0D0"/></top>'
            . '<bottom style="thin"><color rgb="FFD0D0D0"/></bottom>'
            . '<diagonal/>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . xmlEscapeExcel($sheetTitle) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $rootRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $rootRelsXml);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheetXml);
        $zip->close();

        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');
        readfile($tmpFile);
        @unlink($tmpFile);
        exit;
    }
}

// Fallback en caso de que algún servidor no tenga habilitado ZipArchive
$xlsFilename = preg_replace('/\.xlsx$/i', '.xls', $filename);
if (ob_get_length()) {
    ob_end_clean();
}
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $xlsFilename . '"');
header('Cache-Control: max-age=0');
echo "\xEF\xBB\xBF";
echo '<table border="1">';
if (!empty($headers)) {
    echo '<thead><tr style="background-color:#761c19;color:#ffffff;font-weight:bold;">';
    foreach ($headers as $h) {
        echo '<th>' . htmlspecialchars((string)$h, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    echo '</tr></thead>';
}
echo '<tbody>';
foreach ($rows as $r) {
    echo '<tr>';
    foreach ((array)$r as $cell) {
        echo '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
exit;

