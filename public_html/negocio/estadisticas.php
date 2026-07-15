<?php 
require_once '../data/SubContrato.php';
require_once '../data/Funciones.php';
require_once '../data/Mail.php';

$serviceSubContrato = new SubContrato();
$serviceFunciones  = new Funciones();
$serviceMail = new Mail();
$id = $_POST["id"];

switch ($id) {
	case '1':
		$fecha = $serviceFunciones->obtenerFechaActual();
		$meses = array('inicio','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		list($dia,$mes,$ano) = explode("-",$fecha);
		?>
		<div class="col-xs-12 col-sm-12 col-md-12">
			<h1>1. Gráfico Documentos subidos los últimos 90 días.</h1>
		</div>
		<br>
		<div class="col-md-12" id="grafico">
			<canvas id="barra" style="height:50px;width:10%;">
		
			</canvas>
		</div>

		<div class="col-xs-12 col-sm-12 col-md-12">
			<h1>2. Tabla documentos subidos los últimos 12 meses.</h1>
		</div>
		<br>
		<div id="registros" class="table-responsive col-xs-12 col-sm-12 col-md-12 paddingTop">
		<table class="table table-condensed table-hover table-striped">
			<thead>
				<th>Sub Contrato</th>
				<th>Contrato</th>
		<?php 
			for ($i=0; $i < 12; $i++) { 
				if (intval($mes)-1 > 0) {
					$mes = intval($mes)-1;
					?>
					<th><?php echo $meses[$mes]." ".$ano; ?></th>
					<?php
				}else{
					$mes = 12;
					$ano = intval($ano)-1;
					?>
					<th><?php echo $meses[$mes]." ".$ano; ?></th>
					<?php
				}
			}

		 ?>		
			</thead>

			<tbody>
		<?php
		$menuMes = $mes;
		$contratos = $serviceSubContrato->getSubContratosDocumentos(1,1);
		
		foreach ($contratos as $key => $value) {
			list($dia,$mes,$ano) = explode("-",$fecha);
			?>
			<tr>
				<td><?php echo $value["nombre_subcontrato"] ?></td>
				<td><?php echo $value["nombre_contrato"] ?></td>
			<?php
			for ($i=0; $i < 12; $i++) { 
				if (intval($mes)-1 > 0) {
					
					$mes = intval($mes)-1;
					$datos = $serviceSubContrato->getSubContratosDocumentos($mes,$ano);

					foreach ($datos as $c => $valor) {
						if($c == $key){
							?>
								<td><?php echo $valor["cantidad"]; ?></td>
							<?php
						}
					}
				}else{
					$mes = 12;
					$ano = intval($ano)-1;
					$datos = $serviceSubContrato->getSubContratosDocumentos($mes,$ano);

					foreach ($datos as $c => $valor) {
						if($c == $key){
							?>
								<td><?php echo $valor["cantidad"]; ?></td>
							<?php
						}
					}
				}
			}
			?>
			</tr>
			<?php
		}

			?>
			</tbody>
		</table>
		</div>
		<?php
		break;
	case '2':
		$datos = $serviceSubContrato->obtenerGraficoDocumentos();
		echo json_encode($datos);
		break;
	case '3':
        $mensaje = $serviceMail->generarBodyMailGenerico("Reporte Mensual Gestor Documental");
        $serviceMail->enviarMailResumenMes($mensaje, "patricio.echeverria@axioma.cl");
        unlink("Resumen_mensual.pdf");
		break;
	case '4':
		$data = base64_decode($_POST['pdf']);
		file_put_contents( "Resumen_mensual.pdf", $data);
		break;
	
}

 ?>