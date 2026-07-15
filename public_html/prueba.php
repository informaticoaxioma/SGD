<?php 
echo "ACCESO TXT OK.";

require_once 'data/Mail.php';

$serviceMail = new Mail();
$mensaje = $serviceMail->generarBodyMailGenerico("Link resporte estadisticas gestor documental <a href='http://sgd.axioma.cl/gestorDocumental/estadisticas.php'>link</a>");
//$serviceMail->enviarMailResumenMes($mensaje, "gurrutia@axioma.cl");
$serviceMail->enviarMailResumenMes($mensaje, "mario.gaete@axioma.cl");
//$serviceMail->enviarMailResumenMes($mensaje, "patricio.echeverria@axioma.cl");

 ?>
