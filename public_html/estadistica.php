
<?php


//comenzamos declarando el nombre de la clse
class conexion {
    //creamos los atributos de la clase
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;

    //declaramos el contructor
    function __construct(){
        //obtenemos los datos del archivo config mediente el metodo datosConexion

        $this->conexion = new mysqli("localhost","root","Csnis%Min.","gestor_documental","3306");
        if($this->conexion->connect_errno){
            echo "algo va mal con la conexion";
            die();
        }

    }

    //convertiremos los datos obtenidos en utf8
    private function convertirUTF8($array){
        array_walk_recursive($array,function(&$item,$key){
            if(!mb_detect_encoding($item,'utf-8',true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    //esta funcion la invocaremos cuando necesitemos utilizar un select
    public function obtenerDatos($sqlstr){
        $results = $this->conexion->query($sqlstr);
        $resultArray = array();
        foreach ($results as $key) {
            $resultArray[] = $key;
        }
        return $this->convertirUTF8($resultArray);

    }

    //esta funcion la invocaremos cuando necesitemos utilizar insert,delete,update
    public function nonQuery($sqlstr){
        $results = $this->conexion->query($sqlstr);
        return $this->conexion->affected_rows;
    }


    //UNICAMENTE INSERT YA QUE NOS DEVOLVERA EL ULTIMO ID INSERTADO
    public function nonQueryId($sqlstr){
        $results = $this->conexion->query($sqlstr);
         $filas = $this->conexion->affected_rows;
         if($filas >= 1){
            return $this->conexion->insert_id;
         }else{
             return 0;
         }
    }

    //encriptar CONTRASEÑAS

    protected function encriptar($string){
        return md5($string);
    }

}
function mes($m){
  $dato = array('','ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE');
  return $dato[$m];
}

if (!empty($_POST['contratos'])) {

  $id_selectC=$_POST['contratos'];

$fil = implode(",", $_POST['contratos']);
$filtraContrato= "AND id_contrato IN (".$fil.")";


}else {
  $filtraContrato='';
  $id_selectC=array();
}
$_conexion5 = new conexion;
$query5= "SELECT * FROM contrato WHERE id_estado = 1 ORDER BY nombre_contrato ASC ";
$contratos5 = $_conexion5->obtenerDatos($query5);

$_conexion1 = new conexion;
$query1= "SELECT * FROM contrato WHERE id_estado = 1 ".$filtraContrato." ORDER BY nombre_contrato ASC ";

$contratos = $_conexion1->obtenerDatos($query1);

$combreContratos="";
for ($n=0; $n < count($contratos); $n++) {
  $combreContratos.= " data.addColumn('number', '".$contratos[$n]['nombre_contrato']."'); ";
}

$_conexion2 = new conexion;
$query2= "SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -11 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -11 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -1 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -10 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -10 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -2 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -9 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -9 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -3 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -8 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -8 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -4 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -7 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -7 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -5 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -6 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -6 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -6 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -5 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -5 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -7 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -4 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -4 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -8 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -3 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -3 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -9 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -2 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -2 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -10 MONTH)) AS year
UNION
SELECT MONTHNAME(DATE_ADD(CURDATE( ),INTERVAL -1 MONTH)) AS nombre, MONTH(DATE_ADD(CURDATE( ),INTERVAL -1 MONTH)) AS mes, YEAR(DATE_ADD(CURDATE( ),INTERVAL -11 MONTH)) AS year
UNION
SELECT MONTHNAME(CURDATE()) AS nombre, MONTH(CURDATE()) AS mes, YEAR(CURDATE()) AS year";
$meses = $_conexion2->obtenerDatos($query2);

$data="";

$tab=array();
for ($m=0; $m <count($meses) ; $m++) {

$data.= "['".mes($meses[$m]['mes'])."',";
for ($c=0; $c < count($contratos); $c++) {

    $_conexion3 = new conexion;
    $query3= "SELECT count(d.id_documento) AS total FROM documento d INNER JOIN subcontrato s ON s.id_subcontrato=d.id_subcontrato
    WHERE YEAR(d.fecha_recepcion)=".$meses[$m]['year']." AND MONTH(d.fecha_recepcion)=".$meses[$m]['mes']." AND s.id_contrato=".$contratos[$c]['id_contrato'];
   // echo $query3."<br>";
    $total = $_conexion3->obtenerDatos($query3);
    $data.= $total[0]['total'].",";

    $tab[]=array(
      'id_contrato' => $contratos[$c]['id_contrato'],
      'nombre_contrato' => $contratos[$c]['nombre_contrato'],
      'year' => $meses[$m]['year'],
      'mes' => $meses[$m]['mes'],
      'dato' => $total[0]['total']
    );


  }
$sw++;

$data.= "],";
}


$sw = '';

$cCs='<th>Meses / Contratos</th>';
for ($cc=0; $cc < count($contratos); $cc++) {
  $cCs.= '<th>'.$contratos[$cc]['nombre_contrato'].'</th>';
}

$tabla = '<h4>Detalle de registros de ingreso de documentos en SGD</h4><br>
<table class="table table-bordered table-sm table-striped">'.$cCs;
for ($t=0; $t <count($tab) ; $t++) {

  if ($t <> 0 && $tab[$t]['mes'] <> $sw) {
    $tabla.='</tr>';
  }
    if ($t == 0 || $tab[$t]['mes'] <> $sw) {
      $tabla.='<tr><th>'.mes($tab[$t]['mes']).' '.$tab[$t]['year'].'</th>';
    }

  $tabla.='<td>'.$tab[$t]['dato'].'</td>';

  $sw = $tab[$t]['mes'];

}
$tabla.='</tr>';
$tabla.='</table>';
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });
    </script>
    <script type="text/javascript">

    google.charts.load('current', {'packages':['line']});
    google.charts.setOnLoadCallback(drawChart);


   function drawChart() {

     var data = new google.visualization.DataTable();
     data.addColumn('string', 'Últimos 12 meses');

     <?php echo $combreContratos; ?>

     data.addRows([ <?php echo $data; ?> ]);


     var options = {
       chart: {
         title: 'Registros de ingreso de documentos en SGD',
         subtitle: 'Últimos 12 meses'
       },
       height: 600
     };

     var chart = new google.charts.Line(document.getElementById('grafico'));
      chart.draw(data, google.charts.Line.convertOptions(options));

   }

    </script>
    <title>Gestor Documental</title>
  </head>
  <body>
    <h1>Gestor Documental</h1>
    <div class="container-fluid">
      <form class="row " method="post">

        <div class="mb-3">
          <label for="" class="form-label">Seleccionar Contratos</label>
          <select class="js-example-basic-multiple form-select" name="contratos[]" multiple="multiple">
            <?php
            for ($n=0; $n < count($contratos5); $n++) {
              (in_array($contratos5[$n]['id_contrato'],$id_selectC)) ? $select ='selected' : $select ='';

              echo '<option '.$select.' value="'.$contratos5[$n]['id_contrato'].'">'.$contratos5[$n]['nombre_contrato'].'</option>';
            }
             ?>
          </select>
        </div>
        <div class="mb-3">
          <button type="submit" class="btn btn-primary mb-3">Filtrar</button>
        </div>
      </form>
      <div id="grafico" style="width: 100%; "></div>
      <br>
      <?php echo $tabla; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  </body>
</html>
