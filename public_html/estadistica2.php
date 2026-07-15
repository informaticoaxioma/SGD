
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


}
function mes($m){
  $dato = array('','ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE');
  return $dato[$m];
}

$array= array();
/*
$array[] = array(
'mes' => date("m"),
'year' => date("Y")
);*/
for ($i = 0; $i <= 24; $i++) {
  //  $months[] = date("M Y", strtotime( date( 'Y-m-01' )." -$i months"));
    $array[] = array(
    'mes' => date("m", strtotime( date( 'Y-m-01' )." -$i months")),
    'year' => date("Y", strtotime( date( 'Y-m-01' )." -$i months"))
  );
}
/*
for($i=0;$i<=11;$i++){

    $array[] = array(
    'mes' => date("m",mktime(0,0,0,date("m")-$i,date("d"),date("Y"))),
    'year' => date("Y",mktime(0,0,0,date("m")-$i,date("d"),date("Y")))
  );
}*/
//echo "<pre>".print_r($array,true)."</pre>";
$array = array_reverse($array);



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



$data="";

$tab=array();


//$data.= "['".mes($meses[$m]['mes'])."',";
//echo "<pre>".print_r($array,true)."</pre>";
//echo "<pre>".print_r($contratos,true)."</pre>";
$mesesGrafico = '';
$tabla='<table style=" font-size: 12px;" class="table table-sm table-bordered">
  <thead>
    <tr><th scope="col">CONTRATOS</th>';

  for ($m=0; $m < count($array) ; $m++) {
    ($m+1 == count($array)) ? $color1 = 'table-success' : $color1 = '';
    $tabla.='<th class="'.$color1.'" scope="col">'.mes(intval($array[$m]['mes'])).' '.$array[$m]['year'].'</th>';
    $mesesGrafico.= "'".mes(intval($array[$m]['mes']))." ".$array[$m]['year']."',";

  }

$tabla.='
    </tr>
  </thead><tbody>';
  $datosGrafico='';

for ($a=0; $a <count($contratos) ; $a++) {


    $tabla.='<tr><th scope="row">'.strtoupper($contratos[$a]['nombre_contrato']).'</th>';

$datosGrafico.="{
  data: [";

      for ($c=0; $c < count($array); $c++) {


              $_conexion3 = new conexion;
              $query3= "SELECT count(d.id_documento) AS total FROM documento d INNER JOIN subcontrato s ON s.id_subcontrato=d.id_subcontrato
              WHERE YEAR(d.fecha_recepcion)=".$array[$c]['year']." AND MONTH(d.fecha_recepcion)=".$array[$c]['mes']." AND s.id_contrato=".$contratos[$a]['id_contrato'];
              //  echo $query3."<br>";
              $total = $_conexion3->obtenerDatos($query3);
              //  var_dump($total);
              //    echo "<br>";
              ($total[0]['total'] == 0 && $c == (count($array) -1 )) ? $color2 = 'table-danger' : $color2 = '';

              $tabla.='<td class="'.$color2.'" >'.$total[0]['total'].'</td>';
              $datosGrafico.=$total[0]['total'].",";

            }

      $datosGrafico.="],
      label: '".strtoupper($contratos[$a]['nombre_contrato'])."',
        borderWidth: 1,
        fill: false
      },";

    $tabla.='</tr>';

}
$tabla.='</tbody>
  </table>';




?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>





    <title>Gestor Documental</title>
  </head>
  <body><br>

    <div class="row">
      <div class="d-flex justify-content-center">
        <h2 >Sistema Estadístico Gestor Documental</h2>
      </div>
    </div>
    <div class="container-fluid">
<form class="row g-3" method="POST">
  <div class="col-auto">
    <label for="staticEmail2" class="visually-hidden">Seleccionar Contratos</label>
<select class="js-example-basic-multiple form-select form-control" name="contratos[]" multiple="multiple">
            <?php
            for ($n=0; $n < count($contratos5); $n++) {
              (in_array($contratos5[$n]['id_contrato'],$id_selectC)) ? $select ='selected' : $select ='';

              echo '<option '.$select.' value="'.$contratos5[$n]['id_contrato'].'">'.strtoupper($contratos5[$n]['nombre_contrato']).'</option>';
            }
             ?>
          </select>

  </div>
  <div class="col-auto">
    <label for="inputPassword2" class="visually-hidden">Password</label>
<button type="submit" class="form-control btn btn-primary mb-3">Filtrar</button>
  </div>
  <div class="col-auto">
	<button type="button" onclick="limpiar()" class=" form-control btn btn-success mb-3">Limpiar</button>
 </div>
</form>

      <div>
        <div class="mb-12">
          <canvas id="myChart"></canvas>

        </div>
        <div class="row mb-12">
          <h3>Detalles</h3>
          <div class="overflow-auto"><?php echo $tabla; ?></div>

        </div>
</div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function limpiar(){
window.location.href = "estadistica2.php";
}
</script>
<script>
const ctx = document.getElementById('myChart');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: [<?php echo $mesesGrafico; ?>],
    datasets: [
    <?php echo $datosGrafico; ?>
  ]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>


  </body>
</html>
