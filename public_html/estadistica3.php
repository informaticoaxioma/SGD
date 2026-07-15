<?php
$servername = "localhost"; // Cambia esto al nombre de tu servidor MySQL
$username = "root"; // Cambia esto al nombre de usuario de tu base de datos
$password = "Csnis%Min."; // Cambia esto a tu contraseña
$dbname = "gestor_documental"; // Cambia esto al nombre de tu base de datos

// Crear una conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener la fecha actual y la fecha hace 12 meses
$currentDate = date("Y-m-d");
$lastYearDate = date("Y-m-d", strtotime("-12 months"));

$sql = 'SELECT
    UPPER(c.nombre_contrato) AS contrato,
    ed.estado_doc AS estado_documento,
    COUNT(d.id_documento) AS cantidad_documentos
FROM documento d
JOIN estado_documento ed ON d.id_estado_doc = ed.id_estado_doc
JOIN subcontrato sc ON d.id_subcontrato = sc.id_subcontrato
JOIN contrato c ON sc.id_contrato = c.id_contrato
JOIN area a ON c.id_area = a.id_area
WHERE a.id_area = 3
    AND d.id_estado_doc <> 3
    AND d.fecha_documento BETWEEN "'.$lastYearDate.'" AND "'.$currentDate.'"
GROUP BY contrato, ed.estado_doc
ORDER BY contrato';

$result = $conn->query($sql);

// Crear arrays para almacenar los datos del gráfico
$labels = array();
$dataSets = array();

if ($result->num_rows > 0) {
    $contractsColors = array(); // Almacenar colores para cada contrato
    while ($row = $result->fetch_assoc()) {
        $contract = $row["contrato"];
        if (!array_key_exists($contract, $contractsColors)) {
            $contractsColors[$contract] = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); // Generar un color hexadecimal aleatorio
        }
        $labels[] = $contract . " - " . $row["estado_documento"];
        $dataSets[] = array(
            "label" => $contract,
            "data" => array($row["cantidad_documentos"]),
            "backgroundColor" => $contractsColors[$contract]
        );
    }
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gráfico de Documentos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h2>Gráfico de Documentos</h2>

<!-- Agrega un elemento canvas para el gráfico -->
<canvas id="myChart"></canvas>

<script>
    var ctx = document.getElementById("myChart").getContext("2d");

    var labels = <?php echo json_encode($labels); ?>;
    var dataSets = <?php echo json_encode($dataSets); ?>;

    var myChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: dataSets
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: "top"
                }
            }
        }
    });
</script>

</body>
</html>
