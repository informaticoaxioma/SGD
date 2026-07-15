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
    
    private function querySimple($sql)
    {
        $result = $this->conn->query($sql);
        $data = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getDocumentosEntrada($id)
    {
        $sql = "SELECT COUNT(d.id_documento) AS total
                FROM documento d
                INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
                INNER JOIN usuario u ON u.id_usuario = de.id_responsable
                WHERE u.id_contrato = $id AND d.id_flujo = 1";

        return $this->querySimple($sql);
    }

    public function getDocumentosSalida($id)
    {
        $sql = "SELECT COUNT(d.id_documento) AS total
                FROM documento d
                INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
                INNER JOIN usuario u ON u.id_usuario = de.id_responsable
                WHERE u.id_contrato = $id AND d.id_flujo = 2";

        return $this->querySimple($sql);
    }

    public function getDocumentosContrato($id)
    {
        $sql = "SELECT COUNT(d.id_documento) AS total 
                FROM detalle_documento d 
                INNER JOIN usuario u ON u.id_usuario = d.id_responsable
                WHERE u.id_contrato = $id";

        return $this->querySimple($sql);
    }

    public function getDocumentosPendientes($id)
    {
        $sql = "SELECT COUNT(d.id_estado_doc) AS total
                FROM documento d
                INNER JOIN detalle_documento de ON de.id_documento = d.id_documento
                INNER JOIN usuario u ON u.id_usuario = de.id_responsable
                WHERE u.id_contrato = $id AND d.id_estado_doc <> 2";

        return $this->querySimple($sql);
    }

    public function getUsuario($id)
    {
        $sql = "SELECT * FROM usuario WHERE id_usuario = $id";
        return $this->querySimple($sql);
    }

    // ? FUNCIÓN PRINCIPAL CORREGIDA
    public function getDocumentosContratos()
    {
        $contratos = [];

        $sql = "SELECT id_contrato, nombre_contrato, id_estado FROM contrato";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $id = $row['id_contrato'];

                // ? USAR $this (no model)
                $total = $this->getDocumentosContrato($id);
                $pendientes = $this->getDocumentosPendientes($id);
                $entrada = $this->getDocumentosEntrada($id);
                $salida = $this->getDocumentosSalida($id);

                $totalDocs = $total[0]['total'] ?? 0;
                $pendDocs = $pendientes[0]['total'] ?? 0;
                $entradaDocs = $entrada[0]['total'] ?? 0;
                $salidaDocs = $salida[0]['total'] ?? 0;

                $procesados = $entradaDocs + $salidaDocs;
                $porcentaje = ($totalDocs > 0) ? round((($totalDocs - $pendDocs) / $totalDocs) * 100, 2) : 0;

                $contratos[] = [
                    'contrato'   => $row['nombre_contrato'],
                    'id_estado'   => $row['id_estado'],
                    'total'      => $totalDocs,
                    'entrada'    => $entradaDocs,
                    'salida'     => $salidaDocs,
                    'pendientes' => $pendDocs,
                    'porcentaje' => $porcentaje
                ];
            }
        }

        return $contratos;
    }
}

$idBusqueda = $_GET['idUserRev'] ?? 0;

$controller = new Consultas();

$usuario = $controller->getUsuario($idBusqueda);

if (!empty($usuario)) {

    $idContrato = $usuario[0]['id_contrato'];

    $totalContrato = $controller->getDocumentosContrato($idContrato);
    $totalPendientes = $controller->getDocumentosPendientes($idContrato);

} else {
    error_log("Usuario no encontrado");
}



$contratos = $controller->getDocumentosContratos();
?>

<style>
.dataTables_filter {
    display:none;
}
.table-burdeo {
    border: 1px solid #800020;
}
.table-burdeo thead {
    background-color: #800020;
    color: white;
}
.badge-rojo { background:#dc3545;color:white;padding:5px 10px;border-radius:5px;}
.badge-amarillo { background:#ffc107;color:black;padding:5px 10px;border-radius:5px;}
.badge-verde { background:#28a745;color:white;padding:5px 10px;border-radius:5px;}
</style>

<div id="headerInicio" class="row">
    <div class="col-lg-12">
        <div class="panel panel-default sombraPanel">
            <div class="panel-heading colorAxioma">
                <h2 class="text-center blanco">Documentos</h2>
            </div>
            <div class="panel-body paddingBottom text-center">  
              <div class="row">
                  <div class="col-md-12">
                      <h4 class="text-left">Resumen de Documentos</h4>
                      <div class="table-responsive">
                        <div class="row" style="display:flex; align-items:end; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                        
                            <div class="col-md-4" style="padding-left:0;">
                                <label>Buscar Contrato:</label>
                                <input 
                                    type="text" 
                                    id="buscadorContrato" 
                                    class="form-control" 
                                    placeholder="Buscar contrato..."
                                >
                            </div>
                        
                            <div class="col-md-3">
                                <label>Filtrar por Estado:</label>
                        
                                <select id="filtroEstado" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="0">Estado Inactivo</option>
                                    <option value="1">Estado Activo</option>
                                </select>
                            </div>
                        
                        </div>                       
                        <br>
                        <table id="tablaContratos" class="table table-bordered table-burdeo">
                            <thead>
                                <tr>
                                    <th>Contrato</th>
                                    <th>Total</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Pendientes</th>
                                    <th>Estado</th>
                                    <th>Riesgo</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($contratos) && count($contratos) > 0){
                                foreach($contratos as $c){
                        
                                    $porcentaje = $c['porcentaje'];
                        
                                    if ($porcentaje < 50) {
                                        $clase = "badge-rojo";
                                    } elseif ($porcentaje < 80) {
                                        $clase = "badge-amarillo";
                                    } else {
                                        $clase = "badge-verde";
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $c['contrato']; ?></td>
                                        <td><?php echo $c['total']; ?></td>
                                        <td><?php echo $c['entrada']; ?></td>
                                        <td><?php echo $c['salida']; ?></td>
                                        <td><?php echo $c['pendientes']; ?></td>
                                        <td><?php echo $c['id_estado']; ?></td>                                                                                
                                        <td data-order="<?php echo $porcentaje; ?>">
                                            <span class="<?php echo $clase; ?>">
                                                <?php echo $porcentaje; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                      </div>
                  </div>
              </div>
                
                               
                <div class="row">
                    <div class="col-md-3 col-md-offset-3">
                        <div class="panel colorAxioma">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-2">
                                        <i class="fa fa-reply fa-2x blanco"></i>
                                    </div>
                                    <div class="col-xs-10 text-right blanco">
                                        <div class="huge">
                                            <?php
                                            echo $docPendientesEntrada;
                                            ?>
                                        </div>
                                        <div>Documentos Pendientes <br/>entrada</div>
                                    </div>
                                </div>
                            </div>
                            <a href="#" onclick="selectorFlujoAdmin(1)">
                                <div class="panel-footer negro">
                                    <span class="pull-left">Ver detalles</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="panel colorAxioma">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-2">
                                        <i class="fa fa-share fa-2x blanco"></i>
                                    </div>
                                    <div class="col-xs-10 text-right blanco">
                                        <div class="huge">
                                            <?php
                                            echo $docPendientesSalida;
                                            ?>
                                        </div>
                                        <div>Documentos Pendientes <br/>salida</div>
                                    </div>
                                </div>
                            </div>
                            <a href="#" onclick="selectorFlujoAdmin(2);">
                                <div class="panel-footer negro">
                                    <span class="pull-left">Ver detalles</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>                                       
                </div> 

                <div class="row">
                    <div class="col-md-12  resultadoAjax">
                        <div id="divTablaDocInicio" class="table-responsive">                                        

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {

    // ?? GUARDAR EN VARIABLE
    var table = $('#tablaContratos').DataTable({

        "ordering": true,

        "order": [],
        

        "language": {
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "paginate": {
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });

    // ?? FILTRO ESTADO
    $('#filtroEstado').on('change', function () {

        var valor = $(this).val();

        if(valor == '') {
            table.column(5).search('').draw();
        } else {
            table.column(5).search('^' + valor + '$', true, false).draw();
        }

    });

    // ?? BUSCADOR CONTRATO
    $('#buscadorContrato').on('keyup', function () {
        table.search(this.value).draw();
    });

});
</script>