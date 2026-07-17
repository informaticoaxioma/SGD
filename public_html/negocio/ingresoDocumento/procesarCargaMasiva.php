<?php

ini_set('memory_limit', '100M');
ob_end_clean();

require_once '../../data/Contrato.php';
require_once '../../data/SubContrato.php';
require_once '../../data/TipoDocumento.php';
require_once '../../data/Usuario.php';
require_once '../../data/EstadoDocumento.php';
require_once '../../data/Documento.php';
require_once '../../data/DetalleDocumento.php';
require_once '../../data/Accion.php';
require_once '../../data/Log.php';
require_once '../../data/Funciones.php';
require_once '../../data/Cargo.php';
require_once '../../data/Entidad.php';
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Controllers/DocumentoController.php';
require '../../vendor/autoload.php';

// Replace PHPExcel with PhpSpreadsheet namespace
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


//SERVICIOS
$usuario = new Usuario();
session_start();
$usuario = $_SESSION['usuario'];

//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);



$serviceContrato = new Contrato();
$serviceSubContrato = new SubContrato();
$serviceTipoDocumento = new TipoDocumento();
$serviceEstadoDoc = new EstadoDocumento();
$serviceDocumento = new Documento();
$serviceDetalleDocumento = new DetalleDocumento();
$serviceUsuario = new Usuario();
$serviceAccion = new Accion();
$serviceLog = new Log();
$serviceFunciones = new Funciones();
$serviceCargo = new Cargo();
$serviceEntidad = new Entidad();


//Variables
$contrato = $serviceContrato->getContratoPorId($usuario->getIdContrato());
$subContratos = $serviceSubContrato->getSubContratoPorContrato($usuario->getIdContrato());
$tipoDocumentos = $serviceTipoDocumento->getTipoDocumentos();
$estadoDocs = $serviceEstadoDoc->getEstadosDocs();
$responsables = $serviceUsuario->getUsuariosPorContrato($usuario->getIdContrato());

if ($_POST) {
    $id = $_POST["id"];
    switch ($id) {
        case '1':
            $idSubContrato = htmlspecialchars($_POST['idSubContrato']);
            $idTipoDocumento = htmlspecialchars($_POST['idTipoDoc']);
            $idFlujo = htmlspecialchars($_POST['idFlujo']);
            $cantidadFilas = intval($_POST['filas']) + 2;
            $filaInicioDatos = 2;
            $idEstadoDoc = htmlspecialchars($_POST['idEstadoDoc']);
            $idResponsable = htmlspecialchars($_POST['idResponsable']);
            $idTipoDoc = htmlspecialchars($_POST['idTipoDoc']);

            $archivoExcel = htmlspecialchars($_FILES['archivoExcel']['tmp_name']);
            $nombreArchivoExcel = htmlspecialchars($_FILES['archivoExcel']['name']);

            // Load the spreadsheet using PhpSpreadsheet
            $spreadsheet = IOFactory::load($archivoExcel);

            // Accessing the active sheet
            $sheet = $spreadsheet->getActiveSheet();
            $totalFilas = $sheet->getHighestRow();

            error_log("------------- totalFilas : " . $totalFilas);

            // Build an array of uploaded files to search by filename
            $fileMap = [];
            if (isset($_FILES['cargaDocumentos']) && isset($_FILES['cargaDocumentos']['name'])) {
                foreach ($_FILES['cargaDocumentos']['name'] as $index => $name) {
                    $parts = explode(".", $name);
                    $fileExtension = end($parts);
                    $fileMap[] = [
                        'tmp_name' => $_FILES['cargaDocumentos']['tmp_name'][$index],
                        'size' => $_FILES['cargaDocumentos']['size'][$index],
                        'name' => $name,
                        'type' => $fileExtension,
                        'name_lc' => strtolower($name)
                    ];
                }
            }



            $matchMap = array();
            for ($i = 2; $i <= intval($totalFilas); $i++) {
                error_log("------------------------------------------- CONTADOR :" . $i);
                //CLASSES
                $documento = new Documento();
                $detalle = new DetalleDocumento();
                $idDocumento = $serviceDocumento->getMaxIdDocumento() + 1;
                $controllerDocumento = new DocumentoController();
                // Extracting fields from the current row
                $numeroProvidencia = $sheet->getCell('A' . $i)->getValue();
                // Match uploaded files by the 'Num Documento' (column D) contained in the filename
                $numDocumentoFila = trim((string)$sheet->getCell('D' . $i)->getValue());

                $fileContent = null;
                $fileSize = 0;
                $fileName = "";
                $fileType = "";
                $matched = false;

                if ($numDocumentoFila !== "" && !empty($fileMap)) {
                    $numDocumentoLc = strtolower($numDocumentoFila);
                    foreach ($fileMap as $fi) {
                        if (strpos($fi['name_lc'], $numDocumentoLc) !== false) {
                            $fileContent = file_get_contents($fi['tmp_name']);
                            $fileSize = $fi['size'];
                            $fileName = $fi['name'];
                            $fileType = $fi['type'];
                            $documento->setNombreDocumento($fileName);
                            $matched = true;
                            break;
                        }
                    }
                }

                if (!$matched) {
                    error_log("No file found matching Num Documento '" . $numDocumentoFila . "' for row " . $i);
                    $documento->setNombreDocumento("");
                }
                // record what was matched (null if none)
                $matchMap[$numDocumentoFila] = $matched ? $fileName : null;
                

                //SETEAR OBJETOS: DOCUMENTO y DETALLE
                $documento->setIdDocumento($idDocumento);

                // Reading data from the sheet
                $detalle->setNumProvidencia($sheet->getCell('A' . $i)->getValue());
                $fechaDocumento = $sheet->getCell('B' . $i)->getValue();
                $fechaDocumento = $serviceFunciones->formatoFechaGuardarDB($fechaDocumento);
                $fechaRecepcion = $sheet->getCell('C' . $i)->getValue();
                $fechaRecepcion = $serviceFunciones->formatoFechaGuardarDB($fechaRecepcion);
                //fecha de plazo
                $fechaActual = $serviceDocumento->obtenerFechaSinHora();
                $fechaPlazo = date('Y-m-d', strtotime("$fechaActual + 7 day"));
                //seteando fechas en el objeto
                $documento->setFechaDocumento($fechaDocumento);
                $documento->setFechaRecepcion($fechaRecepcion);
                $documento->setFechaPlazo($fechaActual);
                //$documento->setFechaPlazo($fechaPlazo);
                $documento->setIdEstadoDoc($idEstadoDoc);
                $documento->setIdFlujo($idFlujo);
                $documento->setIdSubContrato($idSubContrato);

                //detalle del documento
                $detalle->setNumDocumento($sheet->getCell('D' . $i)->getValue());
                if ($sheet->getCell('E' . $i)->getValue() != "") {
                    $detalle->setNumProceso($sheet->getCell('E' . $i)->getValue());
                } else {
                    $detalle->setNumProceso("");
                }

                $idContrato = $usuario->getIdContrato();
                $remitente = trim((string)$sheet->getCell('F' . $i)->getValue());
                $cargoR = trim((string)$sheet->getCell('G' . $i)->getValue());
                $destinatario = trim((string)$sheet->getCell('H' . $i)->getValue());
                $cargoD = trim((string)$sheet->getCell('I' . $i)->getValue());

                // Remitente
                if (is_numeric($remitente) && intval($remitente) > 0) {
                    $entidadR = intval($remitente);
                } else {
                    $nombreR = explode(" ", $remitente);
                    if (count($nombreR) > 1) {
                        $nomR = $nombreR[0];
                        $apeR = "";
                        for ($f = 1; $f < count($nombreR); $f++) {
                            $apeR .= $nombreR[$f] . " ";
                        }
                    } else {
                        $nomR = $nombreR[0];
                        $apeR = " ";
                    }
                    $apeR = mb_convert_encoding($apeR, 'UTF-8', 'auto');

                    if (is_numeric($cargoR) && intval($cargoR) > 0) {
                        $resultadoR = array("estado" => "encontrado", "idCargo" => intval($cargoR));
                    } else {
                        $resultadoR = $serviceCargo->ConsultarCargoCargaMasiva($cargoR, $idContrato);
                    }

                    $entidad = new Entidad();
                    $entidad->setNombreEntidad($nomR);
                    $entidad->setApellidoEntidad($apeR);
                    $entidad->setIdTipoEntidad(1);
                    $entidad->setIdCargo($resultadoR["idCargo"]);
                    $entidad->setIdContrato($idContrato);
                    $entidadR = $serviceEntidad->consultarEntidadCargaMasiva($resultadoR["estado"], $entidad);
                }

                // Destinatario
                if (is_numeric($destinatario) && intval($destinatario) > 0) {
                    $entidadD = intval($destinatario);
                } else {
                    $nombreD = explode(" ", $destinatario);
                    if (count($nombreD) > 1) {
                        $nomD = $nombreD[0];
                        $apeD = "";
                        for ($j = 1; $j < count($nombreD); $j++) {
                            $apeD .= $nombreD[$j] . " ";
                        }
                    } else {
                        $nomD = $nombreD[0];
                        $apeD = " ";
                    }
                    $apeD = mb_convert_encoding($apeD, 'UTF-8', 'auto');

                    if (is_numeric($cargoD) && intval($cargoD) > 0) {
                        $resultadoD = array("estado" => "encontrado", "idCargo" => intval($cargoD));
                    } else {
                        $resultadoD = $serviceCargo->ConsultarCargoCargaMasiva($cargoD, $idContrato);
                    }

                    $entidad = new Entidad();
                    $entidad->setNombreEntidad($nomD);
                    $entidad->setApellidoEntidad($apeD);
                    $entidad->setIdTipoEntidad(2);
                    $entidad->setIdCargo($resultadoD["idCargo"]);
                    $entidad->setIdContrato($idContrato);
                    $entidadD = $serviceEntidad->consultarEntidadCargaMasiva($resultadoD["estado"], $entidad);
                }

                $detalle->setIdRemitente($entidadR);
                $detalle->setIdDestinatario($entidadD);

                // Make sure you use $sheet for all the cell value accesses
                $detalle->setMateria($sheet->getCell('J' . $i)->getValue());
                $detalle->setAntecedente($sheet->getCell('M' . $i)->getValue());

                if ($spreadsheet->getActiveSheet()->getCell('K' . $i)->getValue() != "") {
                    $detalle->setIncluye($spreadsheet->getActiveSheet()->getCell('K' . $i)->getValue());
                } else {
                    $detalle->setIncluye("No Aplica");
                }

                if ($spreadsheet->getActiveSheet()->getCell('L' . $i)->getValue() != "") {
                    $detalle->setComentario($spreadsheet->getActiveSheet()->getCell('L' . $i)->getValue());
                } else {
                    $detalle->setComentario(" ");
                }

                $detalle->setIdResponsable($idResponsable);
                $detalle->setIdTipoDoc($idTipoDoc);
                $detalle->setIdDocumento($idDocumento);;
                //insertar documento       
                if (($insertarConFile = $controllerDocumento->ingresarDocumentoConFile($documento, $fileContent, $fileType)) == 1) {

                    $serviceDetalleDocumento->ingresarDetalleDocumento($detalle);
                }

                $serviceAccion->ingresarAccion($idDocumento, 4);
            }


            //ingreso log
            $log = new Log();
            $log->setNombreUsuario($usuario->getNombre() . " " . $usuario->getApellidoP());
            $log->setAccion("Realizó carga masiva de registros: nombre archivo utilizado: " . $nombreArchivoExcel);
            $log->setFechaAccion($serviceDocumento->obtenerFechaConHora());
            $log->setIdUsuario($usuario->getIdUsuario());

            $serviceLog->ingresarLog($log); //INGRESANDO REGISTRO AL LOG

            // Emit mapping debug info before the final response so frontend can log matches.
            // Prefix with a unique marker to let JS detect it.
            echo "###MATCHES###" . json_encode($matchMap) . "\n1";
            break;

        case '2':
            $archivoExcel = htmlspecialchars($_FILES['archivoExcel']['tmp_name']);
            $nombreArchivoExcel = htmlspecialchars($_FILES['archivoExcel']['name']);
            $filaInicioDatos = 2;
            //INSTANCENADO OBJETO PHPEXCEL (LIBRERIA)
            $spreadsheet  = IOFactory::load($archivoExcel);

            $sheet = $spreadsheet->getActiveSheet(); //asignando la hoja activa de excel
            $totalFilas = $sheet->getHighestRow();
            $error = "correcto";
            for ($i = $filaInicioDatos; $i <= $totalFilas; $i++) { //ciclo para ingresar datos del documento

                $nProv = $sheet->getCell('A' . $i)->getValue();
                $fDoc = $sheet->getCell('B' . $i)->getValue();
                $fRecep = $sheet->getCell('C' . $i)->getValue();
                $nDoc = $sheet->getCell('D' . $i)->getValue();
                $nProceso = $sheet->getCell('E' . $i)->getValue();

                $remitente = $sheet->getCell('F' . $i)->getValue();
                $cargoR = $sheet->getCell('G' . $i)->getValue();
                $destinatario = $sheet->getCell('H' . $i)->getValue();
                $cargoD = $sheet->getCell('I' . $i)->getValue();

                $materia = $sheet->getCell('J' . $i)->getValue();
                $incluye = $sheet->getCell('K' . $i)->getValue() != "" ? $sheet->getCell('K' . $i)->getValue() : "No Aplica";
                $comentario = $sheet->getCell('L' . $i)->getValue() != "" ? $sheet->getCell('L' . $i)->getValue() : " ";
                $accion = $spreadsheet->getActiveSheet()->getCell('N' . $i)->getValue();
                $antecedente = $spreadsheet->getActiveSheet()->getCell('M' . $i)->getValue();




                if ($fDoc == "") {
                    $error .= "B" . $i . "(No puede quedar Vacio)-";
                } else {
                    $fechaDoc = explode("-", $fDoc);
                    if (count($fechaDoc) != 3) {
                        $error .= "B" . $i . "(Formato Fecha Invalido)-";
                    } else {

                        if (!checkdate(intval($fechaDoc[1]), intval($fechaDoc[0]), intval($fechaDoc[2]))) {
                            $error .= "B" . $i . "(Fecha Invalida)-";
                        }
                    }
                }
                if ($fRecep == "") {
                    $error .= "C" . $i . "(No puede quedar Vacio)-";
                } else {

                    $fechaRe = explode("-", $fRecep);
                    if (count($fechaRe) != 3) {
                        $error .= "C" . $i . "(Formato Fecha Invalido)-";
                    } else {

                        if (!checkdate(intval($fechaRe[1]), intval($fechaRe[0]), intval($fechaRe[2]))) {
                            $error .= "C" . $i . "(Fecha Invalida)-";
                        }
                    }
                }

                if ($nDoc == "") {
                    $error .= "D" . $i . "(No puede quedar Vacio)-";
                }
                if ($remitente == "") {
                    $error .= "F" . $i . "(No puede quedar Vacio)-";
                }
                if ($cargoR == "") {
                    $error .= "G" . $i . "(No puede quedar Vacio)-";
                }
                if ($destinatario == "") {
                    $error .= "H" . $i . "(No puede quedar Vacio)-";
                }
                if ($cargoD == "") {
                    $error .= "I" . $i . "(No puede quedar Vacio)-";
                }
                if ($materia == "") {
                    $error .= "J" . $i . "(No puede quedar Vacio)-";
                }
            }

            echo $error;

            break;
    }
}
