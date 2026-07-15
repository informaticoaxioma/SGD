<?php
class Consultas
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "Csnis%Min.";
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


  public function updateFechaPlazo($idDocumento, $nuevaFechaPlazo)
    {
        // Evitar SQL injection utilizando sentencias preparadas
        $stmt = $this->conn->prepare("UPDATE documento SET fecha_plazo = ? WHERE id_documento = ?");
        $stmt->bind_param("si", $nuevaFechaPlazo, $idDocumento);

        // Ejecutar la actualización
        $result = $stmt->execute();

        // Cerrar la sentencia preparada
        $stmt->close();

        return $result;
    }

}

$idDocumento = $_POST['id_documento'];
$nuevaFechaPlazo = $_POST['nueva_fecha_plazo'];

// Crea una instancia de la clase Consultas
$consultas = new Consultas();

// Realiza la actualización
$actualizacionExitosa = $consultas->updateFechaPlazo($idDocumento, $nuevaFechaPlazo);

// Verifica si la actualización fue exitosa
if ($actualizacionExitosa) {
    $mensaje = 1;
} else {
    $mensaje = 2;
}
$salida = array(
    'mensaje' => $mensaje
);
echo json_encode($salida);
// No olvides destruir la instancia de Consultas al finalizar
unset($consultas);

?>
