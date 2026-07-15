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

    public function getUsuario($id)
    {
        $sql = "SELECT * FROM usuario WHERE id_usuario=" . $id;
        $result = $this->conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getBases($id_contrato)
    {
        $sql = "SELECT d.id_documento, d.mime_documento,d.nombre_documento, de.num_documento, de.materia, de.antecedente FROM detalle_documento de
                INNER JOIN documento d
                ON de.id_documento=d.id_documento
                INNER JOIN tipo_documento t
                ON t.id_tipo_doc=de.id_tipo_doc
                INNER JOIN subcontrato s
                ON d.id_subcontrato=s.id_subcontrato
                WHERE t.id_tipo_doc=22
                AND s.id_contrato=".$id_contrato.";";
                // hay que cambiar el 6 a 22

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

$controller = new Consultas();


$idContrato = $_GET['idBas'];
$bases = $controller->getBases($idContrato);
//echo "<pre>" . print_r($bases, true) . "</pre>";


?>
<style>
/* Estilo para centrar el ícono horizontal y verticalmente */
.icono-celda {
  text-align: center; /* Centra horizontalmente */
  vertical-align: middle; /* Centra verticalmente */
  height: 50px; /* Ajusta la altura de la celda (puedes ajustarla según tus necesidades) */
}

/* Estilo para el ícono de PDF en rojo */
.icono-pdf-rojo {
  color: red; /* Cambia el color a rojo */
  font-size: 24px; /* Cambia el tamaño del ícono a 24px (puedes ajustarlo según tu preferencia) */
  /* Si estás utilizando Font Awesome 5, puedes cambiar la clase a 'fas fa-file-pdf' */
}
</style>


<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">&nbsp;Bases de Licitación</h2>
            </div>
            <div class="panel-body paddingBottom text-center">
                <table class="table table-striped">
    <thead>
      <tr>
        <th>Nombre Documento</th>
        <th>Número Documento</th>
        <th>Materia</th>
        <th>Antecedente</th>
        <th>PDF</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bases as $documento): ?>
                            <tr>
                                <td>
                                    <?php echo $documento['nombre_documento']; ?>
                                </td>
                                <td>
                                    <?php echo $documento['num_documento']; ?>
                                </td>
                                <td>
                                    <?php echo $documento['materia']; ?>
                                </td>
                                <td>
                                    <?php echo $documento['antecedente']; ?>
                                </td>
                                <td class="icono-celda">
                                    <form action="../negocio/descargarDocumento.php" method="POST">
                                        <input type="hidden" id="idDocumento" name="idDocumento"
                                            value="<?php echo $documento['id_documento']; ?>"><button id="btnDescargarArchivo"
                                            name="btnDescargarArchivo" class="btn btn-success btn-sm"><i class="fa fa-download"></i>&nbsp;
                                            Descargar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>








<div class="container">

  
</div>

