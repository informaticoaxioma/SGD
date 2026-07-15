<?php
class Consultas
{
    private $servername = "10.50.0.11";
    private $username = "DEV2";
    private $password = "D3V.aXi2024";
    private $database = "gestor_documental";
    private $conn;

    public function __construct()
    {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function __destruct()
    {
        // Close the connection when the object is destroyed
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function getUsuario($id)
    {
        $sql = "SELECT * FROM usuario WHERE id_usuario=" . $id . ";";
        error_log("QUery busqueda id usuario: " . $sql);
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getDocumentosContrato($id)
    {
        $sql = "SELECT COUNT(id_documento) as total FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato) WHERE sc.id_contrato=" .$id;

        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getDocumentosPendientes($id)
    {
        $sql = "SELECT COUNT(id_documento) as total FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato) WHERE sc.id_contrato= " . $id . " AND d.id_estado_doc= 1;";


        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getDocumentosFlujo($id)
    {
        $sql = "SELECT
                CASE
                    WHEN d.id_flujo = 1 THEN 'entrada'
                    WHEN d.id_flujo = 2 THEN 'salida'
                    ELSE 'Otros datos'
                END AS total_datos,
                COUNT(id_documento) as total FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato) WHERE sc.id_contrato=" . $id . "
                GROUP BY d.id_flujo;";

        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getDocumentosPendientesPorUsuario($id)
    {
        $sql = "SELECT
				CONCAT(u.nombre,' ', u.apellido_p,' ', u.apellido_m) AS nombre,
                COUNT(d.id_estado_doc) AS total
                FROM documento d
                INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
                INNER JOIN usuario u ON u.id_usuario = de.id_responsable
                WHERE u.id_contrato = " . $id . " AND d.id_estado_doc <> 2 GRoup by u.id_usuario;";
        error_log("Query de getDocumentosPendientesPorUsuario: " . $sql);
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getDocumentosPorTipo($id)
    { // pendientes x tipo
        $sql = "SELECT 
                	CONCAT( COUNT(dd.id_documento), ' ', td.tipo_documento ) AS nombre,
                    COUNT(dd.id_documento) AS total
                FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato) JOIN tipo_documento td USING(id_tipo_doc)
                WHERE sc.id_contrato = " . $id . " AND d.id_estado_doc= 1 
                GROUP BY dd.id_tipo_doc;";
        error_log("Query getDocumentosPorTipo: " . $sql);
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getDetallePendiente($id)
    {
        $sql = "SELECT UPPER(CONCAT(u.nombre,' ', u.apellido_p,' ', u.apellido_m)) AS usuario, UPPER(t.tipo_documento) AS tipo,UPPER(f.flujo) AS flujo,UPPER(CONCAT(r.nombre_entidad,' ',r.apellido_entidad)) AS remitente, UPPER(CONCAT(e.nombre_entidad,' ',e.apellido_entidad)) AS destinatario, 
        d.nombre_documento, d.mime_documento, d.tamano_documento, d.fecha_documento, d.fecha_recepcion, d.fecha_plazo, d.id_estado_doc, d.id_flujo, d.id_subcontrato, 
        de.id_documento, de.num_documento, de.num_providencia, de.num_proceso, de.id_remitente, de.id_destinatario, UPPER(de.materia) AS materia, de.antecedente, de.incluye, 
        de.comentario, de.id_tipo_doc, de.id_responsable 
            FROM detalle_documento de 
            INNER JOIN usuario u 
            ON u.id_usuario=de.id_responsable 
            INNER JOIN documento d 
            ON d.id_documento=de.id_documento 
            INNER JOIN entidad r
            ON r.id_entidad=de.id_remitente
            INNER JOIN entidad e
            ON e.id_entidad=de.id_destinatario
            INNER JOIN tipo_documento t
            ON t.id_tipo_doc=de.id_tipo_doc
            INNER JOIN flujo f
            ON f.id_flujo=d.id_flujo
            WHERE u.id_contrato=" . $id . " 
            AND d.id_estado_doc <> 2 
            GROUP BY de.id_documento
            ORDER BY d.fecha_recepcion ASC;";

        $result = $this->conn->query($sql);
        error_log("Query getDetallePendiente: " . $sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getDetallePendientePorUsuario($id)
    {
        $sql = "SELECT 
    UPPER(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido_p, ''), ' ', COALESCE(u.apellido_m, ''))) AS usuario,
    UPPER(COALESCE(t.tipo_documento, 'Unknown Type')) AS tipo,
    UPPER(COALESCE(f.flujo, 'Unknown Flow')) AS flujo,
    UPPER(CONCAT(COALESCE(r.nombre_entidad, 'Unknown'), ' ', COALESCE(r.apellido_entidad, ''))) AS remitente,
    UPPER(CONCAT(COALESCE(e.nombre_entidad, 'Unknown'), ' ', COALESCE(e.apellido_entidad, ''))) AS destinatario,
    COALESCE(d.nombre_documento, 'No Document Name') AS nombre_documento,
    COALESCE(d.mime_documento, 'application/octet-stream') AS mime_documento,
    COALESCE(d.tamano_documento, 0) AS tamano_documento,
    COALESCE(d.fecha_documento, '0000-00-00') AS fecha_documento,
    COALESCE(d.fecha_recepcion, '0000-00-00') AS fecha_recepcion,
    COALESCE(d.fecha_plazo, '0000-00-00') AS fecha_plazo,
    d.id_estado_doc, 
    d.id_flujo, 
    d.id_subcontrato,
    de.id_documento,
    de.num_documento,
    de.num_providencia,
    de.num_proceso,
    de.id_remitente,
    de.id_destinatario,
    UPPER(COALESCE(de.materia, 'No Subject')) AS materia,
    COALESCE(de.antecedente, 'No Background') AS antecedente,
    COALESCE(de.incluye, 'No Inclusions') AS incluye,
    COALESCE(de.comentario, 'No Comments') AS comentario,
    de.id_tipo_doc,
    de.id_responsable
FROM detalle_documento de
INNER JOIN usuario u ON u.id_usuario = de.id_responsable
INNER JOIN documento d ON d.id_documento = de.id_documento
INNER JOIN entidad r ON r.id_entidad = de.id_remitente
INNER JOIN entidad e ON e.id_entidad = de.id_destinatario
INNER JOIN tipo_documento t ON t.id_tipo_doc = de.id_tipo_doc
INNER JOIN flujo f ON f.id_flujo = d.id_flujo
WHERE u.id_usuario = " . $id . " AND d.id_estado_doc <> 2
GROUP BY de.id_documento
ORDER BY d.fecha_recepcion ASC;";

        error_log("Query getDetallePendientePorUsuario: " . $sql);
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            // Handle no results
            $data[] = [
                'usuario' => 'N/A',
                'tipo' => 'Unknown Type',
                'flujo' => 'Unknown Flow',
                'remitente' => 'Unknown',
                'destinatario' => 'Unknown',
                'nombre_documento' => 'No Document Name',
                'mime_documento' => 'application/octet-stream',
                'tamano_documento' => 0,
                'fecha_documento' => '0000-00-00',
                'fecha_recepcion' => '0000-00-00',
                'fecha_plazo' => '0000-00-00',
                'materia' => 'No Subject',
                'antecedente' => 'No Background',
                'incluye' => 'No Inclusions',
                'comentario' => 'No Comments'
                // Add more fields as necessary
            ];
        }

        return $data;
    }
    public function getPendientesPorUsuarioPorTipo($id)
    {
        $sql = "SELECT
            CONCAT(count(de.id_documento),' ',t.tipo_documento) AS nombre,
            count(de.id_documento) AS total
            FROM documento d
            INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
            INNER JOIN usuario u ON u.id_usuario = de.id_responsable
            INNER JOIN tipo_documento t ON t.id_tipo_doc=de.id_tipo_doc
            WHERE u.id_usuario = " . $id . " 
            GROUP BY de.id_tipo_doc;";

        error_log("Query getPendientesPorUsuarioPorTipo: " . $sql);
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getPorFechaPorUser($id)
    {
        $sql = "SELECT
            CONCAT(YEAR(d.fecha_recepcion), ' ',
            CASE MONTH(d.fecha_recepcion)
                WHEN 1 THEN 'Enero'
                WHEN 2 THEN 'Febrero'
                WHEN 3 THEN 'Marzo'
                WHEN 4 THEN 'Abril'
                WHEN 5 THEN 'Mayo'
                WHEN 6 THEN 'Junio'
                WHEN 7 THEN 'Julio'
                WHEN 8 THEN 'Agosto'
                WHEN 9 THEN 'Septiembre'
                WHEN 10 THEN 'Octubre'
                WHEN 11 THEN 'Noviembre'
                WHEN 12 THEN 'Diciembre'
                ELSE 'Mes Desconocido'
            END) AS fecha,
            count(de.id_documento) AS total
            FROM documento d
            INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
            INNER JOIN usuario u ON u.id_usuario = de.id_responsable
            INNER JOIN tipo_documento t ON t.id_tipo_doc=de.id_tipo_doc
            WHERE u.id_usuario = " . $id . " 
            GROUP BY YEAR(d.fecha_recepcion), MONTH(d.fecha_recepcion)
            ORDER BY d.fecha_recepcion ASC;";
        //    echo $sql;
        error_log("query getPorFechaPorUser: " . $sql);
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
    public function getPorFechaPorContrato($id)
    {
        $sql = "SELECT
			CONCAT(u.nombre,' ', u.apellido_p,' ', u.apellido_m) AS usuario,
            CONCAT(YEAR(d.fecha_recepcion), ' ',
            CASE MONTH(d.fecha_recepcion)
                WHEN 1 THEN 'Enero'
                WHEN 2 THEN 'Febrero'
                WHEN 3 THEN 'Marzo'
                WHEN 4 THEN 'Abril'
                WHEN 5 THEN 'Mayo'
                WHEN 6 THEN 'Junio'
                WHEN 7 THEN 'Julio'
                WHEN 8 THEN 'Agosto'
                WHEN 9 THEN 'Septiembre'
                WHEN 10 THEN 'Octubre'
                WHEN 11 THEN 'Noviembre'
                WHEN 12 THEN 'Diciembre'
                ELSE 'Mes Desconocido'
            END) AS fecha,
            count(de.id_documento) AS total
            FROM documento d
            INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
            INNER JOIN usuario u ON u.id_usuario = de.id_responsable
            INNER JOIN tipo_documento t ON t.id_tipo_doc=de.id_tipo_doc
            WHERE u.id_contrato = " . $id . "
            GROUP BY YEAR(d.fecha_recepcion), MONTH(d.fecha_recepcion)
            ORDER BY u.id_usuario,d.fecha_recepcion ASC;";
        //    echo $sql;
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }


    public function getUserPorContrato($id)
    {
        $sql = "SELECT
                u.id_usuario,
				CONCAT(u.nombre,' ', u.apellido_p,' ', u.apellido_m) AS nombre
                FROM usuario u 
                WHERE u.id_contrato = " . $id . " AND u.id_estado_usuario =1;";

        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getFull($id_usuario, $year, $mes)
    {
        $sql = "SELECT
            count(de.id_documento) AS total
            FROM documento d
            INNER JOIN detalle_documento de 
            ON de.id_documento = d.id_documento
            WHERE de.id_responsable = " . $id_usuario . "
            AND YEAR(d.fecha_recepcion)=" . $year . "
            AND MONTH(d.fecha_recepcion)=" . $mes . "
            ;";
        //    echo $sql;
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }
}

$idBusqueda = $_GET['idUserRev'];
error_log("USuario de busqueda: " . $idBusqueda);

$controller = new Consultas();
$usuario = $controller->getUsuario($idBusqueda);
error_log("ID del usuario logeado: " . $usuario[0]['id_usuario']);
error_log("Id de contrato del usuario: " . $usuario[0]['id_contrato']);
$totalContrato = $controller->getDocumentosContrato($usuario[0]['id_contrato']);
$totalFlujo = $controller->getDocumentosFlujo($usuario[0]['id_contrato']);
$totalPendientes = $controller->getDocumentosPendientes($usuario[0]['id_contrato']);
$totalPendientesPorUsuario = $controller->getDocumentosPendientesPorUsuario($usuario[0]['id_contrato']);
// Verificar si es un array antes de registrar
if (is_array($totalPendientesPorUsuario)) {
    foreach ($totalPendientesPorUsuario as $pendiente) {
        error_log("Nombre: " . $pendiente['nombre'] . " - Total: " . $pendiente['total']);
    }
} else {
    error_log('El valor de $totalPendientesPorUsuario no es un array');
}
$totalPorTipoDocumento = $controller->getDocumentosPorTipo($usuario[0]['id_contrato']);
$detallePorDocumento = $controller->getDetallePendiente($usuario[0]['id_contrato']);
$detallePorUsuario = $controller->getDetallePendientePorUsuario($idBusqueda);
$pendientePorUsuarioPorTipo = $controller->getPendientesPorUsuarioPorTipo($idBusqueda);
$getPorFechaPorUser = $controller->getPorFechaPorUser($idBusqueda);


$getUserPorContrato = $controller->getUserPorContrato($usuario[0]['id_contrato']);
//echo "<pre>".print_r($getUserPorContrato,true)."</pre>";
?>




<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="vs/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom styles for this template-->
    <link href="vs/css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <br>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Documentos totales del contrato</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalContrato[0]['total']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-pdf fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Documentos de Entrada</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalFlujo[0]['total']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Documentos de salida</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalFlujo[1]['total']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-sign-out-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Documentos Pendientes</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPendientes[0]['total']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 

                    <div class="row" style="">

                  
                        <div class="col-12">
                            <div class="card shadow mb-4">
             
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Consolidado Contrato</h6>
                                 
                                </div>
                          
                                <div class="card-body">
                                    <div id="chartContainer" style="width: 100%; height: 400px;">
                                        <canvas id="consolidadoContrato"></canvas>
                                    </div>

                                </div>
                            </div>
                        </div>


                    </div>Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-8">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Documentos Pendientes Por Responsable</h6>

                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="pendientePorUsuario"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-4">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Documentos Pendientes Por Tipo </h6>

                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class=" pt-4 pb-2">

                                        <canvas id="graficoPendientePorUsuario"></canvas>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-12 mb-4">

                            <!-- Project Card Example -->
                            <div class="card shadow mb-4">

                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Detalles Documentos Pedientes del Contrato</h6>
                                    <div class="dropdown no-arrow">
                                        <div class="mt-4 text-center small">
                                            <span class="mr-2">
                                                <i class="fas fa-circle" style="color: #F90000;"></i>
                                                <= 1 Día
                                                    </span>
                                                    <span class="mr-2">
                                                        <i class="fas fa-circle" style="color: #FF9D46;"></i> >= 2 Días & <= 5 Días
                                                            </span>
                                                            <span class="mr-2">
                                                                <i class="fas fa-circle" style="color: #FFEF00;"></i> >= 6 Días & <= 9 Días
                                                                    </span>
                                                                    <span class="mr-2">
                                                                        <i class="fas fa-circle" style="color: #93DF38;"></i> >= 10 Días
                                                                    </span>
                                        </div>



                                    </div>
                                </div>
                                <div class="card-body">
                                    <style>
                                      .tabla-contenedor{
                                          max-height:500px;
                                          overflow-y:auto;
                                          overflow-x:auto;
                                          border:1px solid #ddd;
                                      }
                                      
                                      .tabla-contenedor table{
                                          width:100%;
                                          border-collapse:collapse;
                                      }
                                      
                                      .tabla-contenedor thead th{
                                          position:sticky;
                                          top:0;
                                          z-index:100;
                                          background:#800020;   /* mismo color de la cabecera */
                                          color:#fff;
                                          border-bottom:2px solid #6b001a;
                                          text-align:center;
                                      }
                                      
                                      .tabla-contenedor tbody td{
                                          vertical-align:middle;
                                      }
                                        }
                                    </style>
                                    <div class="tabla-contenedor">
                                        <table class="table table-sm table-bordered" style="font-size:12px;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th style="width:160px;">Detalles</th>
                                                    <th>Fechas</th>
                                                    <th>Plazo</th>
                                                    <th>Predicción Atraso</th>
                                                    <th>Responsable</th>
                                                    <th>Origen - Destino</th>
                                                    <th>Materia</th>
                                                    <th>Visualizar</th>
                                                </tr>
                                            </thead>
                                            <?php

                                            function colorAlerta($valor)
                                            {
                                                if ($valor <= 1) {
                                                    $salida = '<span class="mr-2"> <i class="fas fa-circle" style="color: #F90000;"></i></span>';
                                                } elseif ($valor >= 2 && $valor <= 5) {
                                                    $salida = '<span class="mr-2"> <i class="fas fa-circle" style="color: #FF9D46;"></i></span>';
                                                } elseif ($valor >= 6 && $valor <= 9) {
                                                    $salida = '<span class="mr-2"> <i class="fas fa-circle" style="color: #FFEF00;"></i></span>';
                                                } elseif ($valor >= 10) {
                                                    $salida = '<span class="mr-2"> <i class="fas fa-circle" style="color: #93DF38;"></i></span>';
                                                }
                                                return $salida;
                                            }
                                            
                                            function prediccionAtraso($usuario, $tipoDocumento, $pendientesUsuario, $diasPlazo)
                                            {
                                                // Si ya venció o vence hoy, riesgo máximo
                                                if ($diasPlazo <= 0) {
                                                    return ["ALTO", 100];
                                                }
                                            
                                                $score = 0;
                                            
                                                // Factor 1: carga del usuario
                                                if ($pendientesUsuario > 20) {
                                                    $score += 40;
                                                } elseif ($pendientesUsuario > 10) {
                                                    $score += 25;
                                                } elseif ($pendientesUsuario > 5) {
                                                    $score += 10;
                                                }
                                            
                                                // Factor 2: tipo de documento
                                                $tipo = strtolower($tipoDocumento);
                                            
                                                if (strpos($tipo, 'oficio') !== false) {
                                                    $score += 20;
                                                } elseif (strpos($tipo, 'informe') !== false) {
                                                    $score += 15;
                                                } elseif (strpos($tipo, 'carta') !== false) {
                                                    $score += 10;
                                                }
                                            
                                                // Factor 3: usuario con alta carga
                                                if ($pendientesUsuario > 15 && !empty($usuario)) {
                                                    $score += 10;
                                                }
                                            
                                                // Factor 4: cercanía al plazo
                                                if ($diasPlazo <= 2) {
                                                    $score += 30;
                                                } elseif ($diasPlazo <= 5) {
                                                    $score += 20;
                                                } elseif ($diasPlazo <= 10) {
                                                    $score += 10;
                                                }
                                            
                                                // Limitar el score a 100
                                                $score = min($score, 100);
                                            
                                                if ($score >= 70) {
                                                    return ["ALTO", $score];
                                                } elseif ($score >= 40) {
                                                    return ["MEDIO", $score];
                                                }
                                            
                                                return ["BAJO", $score];
                                            }
                                            
                                            $pendientesPorUsuario = [];

                                            foreach ($totalPendientesPorUsuario as $item) {
                                            
                                                $nombre = strtoupper(trim($item['nombre']));
                                            
                                                $pendientesPorUsuario[$nombre] = (int)$item['total'];
                                            }


                                            for ($i = 0; $i < count($detallePorDocumento); $i++) {
                                                
                                                $usuarioResp = trim($detallePorDocumento[$i]['usuario']);
                                                $tipoDoc = $detallePorDocumento[$i]['tipo'];
                                                
                                                $pendientesUsuario = isset($pendientesPorUsuario[$usuarioResp])
                                                  ? $pendientesPorUsuario[$usuarioResp]
                                                  : 0;

                                                $fechaBD = $detallePorDocumento[$i]['fecha_documento']; // Supongamos que esta es la fecha de la base de datos
                                                $fechaActual = date("Y-m-d"); // Obtenemos la fecha actual en formato 'Y-m-d'
                                                $diferencia = strtotime($fechaBD) - strtotime($fechaActual);
                                                $fecha1 = floor($diferencia / (60 * 60 * 24));

                                                $fechaBD = $detallePorDocumento[$i]['fecha_recepcion']; // Supongamos que esta es la fecha de la base de datos
                                                $fechaActual = date("Y-m-d"); // Obtenemos la fecha actual en formato 'Y-m-d'
                                                $diferencia = strtotime($fechaBD) - strtotime($fechaActual);
                                                $fecha2 = floor($diferencia / (60 * 60 * 24));

                                                $fechaBD = $detallePorDocumento[$i]['fecha_plazo']; // Supongamos que esta es la fecha de la base de datos
                                                $fechaActual = date("Y-m-d"); // Obtenemos la fecha actual en formato 'Y-m-d'
                                                $diferencia = strtotime($fechaBD) - strtotime($fechaActual);
                                                $fecha3 = floor($diferencia / (60 * 60 * 24));

                                                $prediccion = prediccionAtraso($usuarioResp, $tipoDoc, $pendientesUsuario,$fecha3);


                                                if ($usuario[0]['id_perfil'] == 3 || $usuario[0]['id_perfil'] == 9) {
                                                    // Acciones o código a ejecutar si el perfil es 3 o 9
                                                    $desabilitaUpdate = "";
                                                } else {
                                                    // Acciones o código a ejecutar para otros perfiles
                                                    $desabilitaUpdate = "disabled";
                                                }
                                                $nivel = $prediccion[0];
                                                $score = $prediccion[1];
                                                
                                                if ($nivel == "ALTO") {
                                                    $class = "badge-rojo";
                                                } elseif ($nivel == "MEDIO") {
                                                    $class = "badge-amarillo";
                                                } else {
                                                    $class = "badge-verde";
                                                }

                                                echo '<tr class="">
                                                <td>' . ($i + 1) . '</td>
                                                <td>
                                                <b>Flujo: </b>' . $detallePorDocumento[$i]['flujo'] . '<br>   
                                                <b>Tipo: </b>' . $detallePorDocumento[$i]['tipo'] . '<br>
                                                <b>N° Doc: </b>' . $detallePorDocumento[$i]['num_documento'] . '<br>
                                                <b>Prov: </b>' . $detallePorDocumento[$i]['num_providencia'] . '</td>
                                                <td >

                                                    <b>Documento: </b>' . $detallePorDocumento[$i]['fecha_documento'] . '<br>
                                                   <b>Recepción: </b>' . $detallePorDocumento[$i]['fecha_recepcion'] . '<br>
                                                </td>   
                                                <td style="width:200px;">   
                                                   <input ' . $desabilitaUpdate . ' type="date" name="updatePlazo" id="updatePlazo_' . $detallePorDocumento[$i]['id_documento'] . '" value="' . $detallePorDocumento[$i]['fecha_plazo'] . '">
                                                  
                                                   <button ' . $desabilitaUpdate . ' type="button" class="updateButton" data-id="' . $detallePorDocumento[$i]['id_documento'] . '">
                                                        <i class="fas fa-sync" style="color: blue;"></i>
                                                    </button>
                                                   <br><br>
                                                    <h6>' . colorAlerta($fecha3) . '  ' . $fecha3 . ' Días</h6>
                                                   

                                  
                                                </td>
                                                <td>
                                                    <span class="'.$class.'">
                                                        '.$nivel.' ('.$score.'%)
                                                    </span>
                                                </td>
                                                <td>' . $detallePorDocumento[$i]['usuario'] . '</td>
                                                <td>
                                                <b>Remitente:</b> ' . $detallePorDocumento[$i]['remitente'] . '<br>
                                                <b>Destinatario:</b> ' . $detallePorDocumento[$i]['destinatario'] . '</td>
                                                <td>' . $detallePorDocumento[$i]['materia'] . '</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="http://sgd.axioma.cl/pa/buscador/detalleDocumentoBuscador.php?idDocumento=' . $detallePorDocumento[$i]['id_documento'] . '" target="_blank">
                                                        <i class="fas fa-file-pdf" style="font-size: 35px; color: red;"></i> 
                                                    </a>
                                                    </td>
                                            </tr>';
                                            }
                                            ?>


                                            <!-- Add more rows here -->
                                        </table>
                                    </div>


                                </div>
                            </div>



                        </div>

                        <div class="col-lg-12 mb-12">

                            <!-- Illustrations -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Mis Documentos Pendientes </h6>
                                </div>
                                <div class="card-body">
                                    <div class="tabla-contenedor">
                                        <table class="table table-sm table-bordered" style="font-size:12px;">
                                            <thead>
                                              <tr>
                                                  <th>#</th>
                                                  <th style="width:160px;">Detalles</th>
                                                  <th style="width:200;">Fechas</th>
                                                  <th>Origen - Destino</th>
                                                  <th>Materia</th>
                                                  <th>Responsable</th>
  
                                                  <th>Visualizar</th>
                                              </tr>
                                            </thead>
                                            <?php


                                            for ($i = 0; $i < count($detallePorUsuario); $i++) {

                                                //      echo "<pre>".print_r($detallePorUsuario)."</pre>";
                                                $fechaBD = $detallePorUsuario[$i]['fecha_documento']; // Supongamos que esta es la fecha de la base de datos
                                                $fechaActual = date("Y-m-d"); // Obtenemos la fecha actual en formato 'Y-m-d'
                                                $diferencia = strtotime($fechaBD) - strtotime($fechaActual);
                                                $fecha1 = floor($diferencia / (60 * 60 * 24));

                                                $fechaBD = $detallePorUsuario[$i]['fecha_recepcion']; // Supongamos que esta es la fecha de la base de datos
                                                $fechaActual = date("Y-m-d"); // Obtenemos la fecha actual en formato 'Y-m-d'
                                                $diferencia = strtotime($fechaBD) - strtotime($fechaActual);
                                                $fecha2 = floor($diferencia / (60 * 60 * 24));

                                                $fechaBD = $detallePorUsuario[$i]['fecha_plazo']; // Supongamos que esta es la fecha de la base de datos
                                                $fechaActual = date("Y-m-d"); // Obtenemos la fecha actual en formato 'Y-m-d'
                                                $diferencia = strtotime($fechaBD) - strtotime($fechaActual);
                                                $fecha3 = floor($diferencia / (60 * 60 * 24));





                                                echo '<tr class="">
                                                <td>' . ($i + 1) . '</td>
                                                <td>
                                                <b>Flujo: </b>' . $detallePorUsuario[$i]['flujo'] . '<br>   
                                                <b>Tipo: </b>' . $detallePorUsuario[$i]['tipo'] . '<br>
                                                <b>N° Doc: </b>' . $detallePorUsuario[$i]['num_documento'] . '<br>
                                                <b>Prov: </b>' . $detallePorUsuario[$i]['num_providencia'] . '</td>
                                                <td style="width:260px;">

                                                    <b>Documento: </b>' . $detallePorUsuario[$i]['fecha_documento'] . ' ' . abs($fecha1) . ' Días <br>
                                                   <b>Recepción: </b>' . $detallePorUsuario[$i]['fecha_recepcion'] . ' ' . abs($fecha2) . ' Días <br>
                                                   ' . colorAlerta($fecha3) . ' <b>Plazos: </b>' . $detallePorUsuario[$i]['fecha_plazo'] . ' ' . $fecha3 . ' Días 

                                  
                                                </td>
                                                <td>
                                                <b>Remitente:</b> ' . $detallePorUsuario[$i]['remitente'] . '<br>
                                                <b>Destinatario:</b> ' . $detallePorUsuario[$i]['destinatario'] . '</td>
                                                <td>' . $detallePorUsuario[$i]['materia'] . '</td>
                                                <td>' . $detallePorUsuario[$i]['usuario'] . '</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="http://sgd.axioma.cl/pa/buscador/detalleDocumentoBuscador.php?idDocumento=' . $detallePorUsuario[$i]['id_documento'] . '" target="_blank">
                                                        <i class="fas fa-file-pdf" style="font-size: 35px; color: red;"></i> 
                                                    </a>
                                                    </td>
                                            </tr>';
                                            }
                                            ?>


                                            <!-- Add more rows here -->
                                        </table>
                                    </div>

                                </div>
                            </div>



                        </div>
                        <div class="col-lg-4 mb-4">

                            <!-- Illustrations -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Mis Documentos Por Tipo</h6>
                                </div>
                                <div class="card-body">
                                    <div class=" pt-4 pb-2">
                                        <canvas id="totalPorUsuarioPorTipo"></canvas>

                                    </div>
                                    <div class="text-center mt-3">
                                        <h5>Total de documentos: <span id="totalDocumentos"></span></h5>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-8 mb-8">

                            <!-- Illustrations -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Flujo De Mis Documentos</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="lineChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->

            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->
    <div id="salidaUpdateFecha"></div>
    <!-- Scroll to Top Button-->

    <?php if(!empty($totalPendientesPorUsuario)) {
        echo "<script>var datosPendientesPorUsuario = " . json_encode($totalPendientesPorUsuario, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . "; console.log('datosPendientesPorUsuario:', datosPendientesPorUsuario);</script>";
    } else {
        echo "<script>console.error('datosPendientesPorUsuario no está definido porque está vacío o nulo.');</script>";
    } ?>
    
    <?php echo "<script>var datosPorTipoDocumento = " . json_encode($totalPorTipoDocumento, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . "; console.log('datosPorTipoDocumento:', datosPorTipoDocumento);</script>"; ?>
    
        <?php echo "<script>var idUsuarioLogin = " . json_encode($usuario[0]['id_usuario'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . "; console.log('El id del usuario loggeado es:', idUsuarioLogin);</script>"; ?>
    
    
    <?php echo "<script>var datosPorTipoPorUsuario = " . json_encode($pendientePorUsuarioPorTipo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . "; console.log('datosPorTipoPorUsuario:', datosPorTipoPorUsuario);</script>"; ?>
    <?php echo "<script>var historicoPorUser = " . json_encode($getPorFechaPorUser, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . "; console.log('historicoPorUser:', historicoPorUser);</script>"; ?>



    <script>
        // Gráfico de pastel Documentos por tipo
        var ctxPie = document.getElementById('totalPorUsuarioPorTipo').getContext('2d');
        
        var nombresPie = datosPorTipoPorUsuario.map(function(item) {
            return item.nombre;
        });
        
        var totalesPie = datosPorTipoPorUsuario.map(function(item) {
            return Number(item.total);
        });
        
        var totalDocumentos = totalesPie.reduce(function(acum, valor) {
            return acum + valor;
        }, 0);
        
        document.getElementById('totalDocumentos').innerText = totalDocumentos;
        
        var graficoPastel = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: nombresPie,
                datasets: [{
                    data: totalesPie
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
        // Función para generar una paleta de colores fijos y fuertes
        var ctxBar = document.getElementById('pendientePorUsuario').getContext('2d');
        var nombresBar = datosPendientesPorUsuario.map(function(item) {
            return item.nombre.trim();
        });
        var totalesBar = datosPendientesPorUsuario.map(function(item) {
            return item.total;
        });

        var graficoBarras = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: nombresBar,
                datasets: [{
                    label: 'Documentos Pendientes',
                    data: totalesBar,
                    // No especificamos colores aquí, dejamos que Chart.js los asigne automáticamente
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 0,
                            minRotation: 0,
                            mirror: true,
                            padding: 5,
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                layout: {
                    padding: {
                        left: 20,
                        right: 20,
                        top: 0,
                        bottom: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },

                maintainAspectRatio: false
            }
        });

        // Gráfico de pastel: Documentos por tipo
        var ctxPie = document.getElementById('graficoPendientePorUsuario').getContext('2d');
        var nombresPie = datosPorTipoDocumento.map(function(item) {
            return item.nombre;
        });
        var totalesPie = datosPorTipoDocumento.map(function(item) {
            return item.total;
        });

        var graficoPastel = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: nombresPie,
                datasets: [{
                    data: totalesPie,
                    // No especificamos colores aquí, dejamos que Chart.js los asigne automáticamente
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });







        var ctxBar = document.getElementById('lineChart').getContext('2d');
        var nombresBar = historicoPorUser.map(function(item) {
            return item.fecha.trim();
        });
        var totalesBar = historicoPorUser.map(function(item) {
            return item.total;
        });

        var graficoBarras = new Chart(ctxBar, {
            type: 'line',
            data: {
                labels: nombresBar,
                datasets: [{
                    label: 'Documentos',
                    data: totalesBar,
                    // No especificamos colores aquí, dejamos que Chart.js los asigne automáticamente
                }]
            }
        });
    </script>

    </script>

    <!-- Bootstrap core JavaScript-->
    <script src="vs/vendor/jquery/jquery.min.js"></script>
    <script src="vs/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vs/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="vs/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vs/vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="vs/js/demo/chart-area-demo.js"></script>
    <script src="vs/js/demo/chart-pie-demo.js"></script>

</body>

</html>


<script>
    $(document).ready(function() {
        $(".updateButton").on("click", function() {
            var idDocumento = $(this).data("id");
            var nuevaFechaPlazo = $("#updatePlazo_" + idDocumento).val();

            // Realiza la consulta AJAX
            $.ajax({
                type: "POST",
                url: "../bases/updateFecha.php",
                data: {
                    id_documento: idDocumento,
                    nueva_fecha_plazo: nuevaFechaPlazo
                },
                dataType: "json", // Espera una respuesta JSON
                success: function(response) {
                    // Maneja la respuesta JSON
                    if (response.mensaje === 1) {
                        Swal.fire({
                            title: "Actualizado !",
                            text: "La fecha plazo fue actualizada?",
                            icon: "success"
                        });
                        event.preventDefault();
                        //   $("#salidaUpdateFecha").html("La fecha de plazo fue actualizada con éxito");
                    } else {
                        Swal.fire({
                            title: "ERROR !",
                            text: "Error al actualizar la fecha plazo",
                            icon: "error"
                        });
                        event.preventDefault();
                    }
                }
            });
        });
    });
</script>