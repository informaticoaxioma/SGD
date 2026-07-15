<?php
$servername = "localhost";
$username = "root";
$password = "Csnis%Min.";
$dbname = "gestor_documental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT c.id_contrato,s.id_subcontrato AS id,c.nombre_contrato,s.nombre_subcontrato, count(*) AS total ,
        (
            select count(*)
            from documento doc
            WHERE id_subcontrato=id
                AND
                doc.fecha_recepcion
                BETWEEN
                DATE_SUB(curdate(),INTERVAL 1 MONTH)
                AND
                curdate()) AS ultimo_mes,
                (
            select count(*)
            from documento doc
            WHERE id_subcontrato=id
                AND
                doc.fecha_recepcion
                BETWEEN
                DATE_SUB(curdate(),INTERVAL 1 WEEK)
                AND
                curdate()) AS ultima_semana
        FROM documento d
        LEFT JOIN subcontrato s
        ON d.id_subcontrato=s.id_subcontrato
        LEFT JOIN contrato c
        ON c.id_contrato=s.id_contrato AND DATE_FORMAT(fecha_termino,'%Y%m%d')>=DATE_FORMAT(DATE_SUB(curdate(),INTERVAL 1 MONTH),'%Y%m%d') 
        WHERE c.id_area=3 AND (fecha_inicio IS NULL OR DATE_FORMAT(fecha_inicio,'%Y%m%d')<=DATE_FORMAT(d.fecha_recepcion,'%Y%m%d'))
        GROUP BY c.id_contrato, s.id_subcontrato ASC;";

$result = $conn->query($sql);

$tabla= '';
$sw =1;
if ($result->num_rows > 0) {
 // output data of each row
 while($row = $result->fetch_assoc()) {

   ($row["ultima_semana"] == 0) ? $alerta = 'class="table-danger"' : $alerta = 'class="table-primary"';

  $tabla.= '<tr '.$alerta.'>
    <td >'.($sw).'</td>
    <td >'.$row["id_contrato"].'</td>
    <td >'.$row["nombre_contrato"].'</td>
    <td >'.$row["nombre_subcontrato"].'</td>
    <td >'.$row["total"].'</td>
    <td >'.$row["ultimo_mes"].'</td>
    <td >'.$row["ultima_semana"].'</td>
    </tr>';
    $sw++;
 }
} else {
 echo "0 resultado";
}
$conn->close();

$html='

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentos SGD</title>
  </head>

<style>

table.minimalistBlack {
  border: 3px solid #000000;
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
table.minimalistBlack td, table.minimalistBlack th {
  border: 1px solid #000000;
  padding: 5px 4px;
}
table.minimalistBlack tbody td {
  font-size: 13px;
}
table.minimalistBlack thead {
  background: #CFCFCF;
  background: -moz-linear-gradient(top, #dbdbdb 0%, #d3d3d3 66%, #CFCFCF 100%);
  background: -webkit-linear-gradient(top, #dbdbdb 0%, #d3d3d3 66%, #CFCFCF 100%);
  background: linear-gradient(to bottom, #dbdbdb 0%, #d3d3d3 66%, #CFCFCF 100%);
  border-bottom: 3px solid #000000;
}
table.minimalistBlack thead th {
  font-size: 15px;
  font-weight: bold;
  color: #000000;
  text-align: left;
}
table.minimalistBlack tfoot {
  font-size: 14px;
  font-weight: bold;
  color: #000000;
  border-top: 3px solid #000000;
}
table.minimalistBlack tfoot td {
  font-size: 14px;
}

</style>
  <body>
    <div class="table-wrapper">
      <h1>Documentos por Contrato - CONCESIONES</h1>
      <table class="minimalistBlack">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">ID Contrato</th>
            <th scope="col">Contrato</th>
            <th scope="col">Sub Contrato</th>
            <th scope="col">Total</th>
            <th scope="col">Último Mes</th>
            <th scope="col">Última Semana</th>
          </tr>
        </thead>
        <tbody>'.$tabla.'
        </tbody>
      </table>
    </div>

  </body>
</html>';



//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Create a new PHPMailer instance
require '/var/www/sgd.axioma.cl/public_html/correo/src/Exception.php';
require '/var/www/sgd.axioma.cl/public_html/correo/src/PHPMailer.php';
require '/var/www/sgd.axioma.cl/public_html/correo/src/SMTP.php';
$mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->isSMTP();
$mail->CharSet = 'UTF-8';
$mail->isHTML(true);
//Enable SMTP debugging
// SMTP::DEBUG_OFF = off (for production use)
// SMTP::DEBUG_CLIENT = client messages
// SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
//Set the hostname of the mail server
$mail->Host = 'mail.zboxapp.com';
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 587;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = 'sisaxioma@axioma.cl';
//Password to use for SMTP authentication
$mail->Password = '2%9PLZV;e903[h8&dg';
//Set who the message is to be sent from
$mail->setFrom('sgd@axioma.cl', 'Sistema Documental');
//Set an alternative reply-to address
//$mail->addReplyTo('correo@dominio.tld', 'Magic');
//Set who the message is to be sent to
$mail->addAddress('mario.gaete@axioma.cl', 'Mario Gaete');
//$mail->addAddress('armando.jara@axioma.cl', 'Armando Jara');
//$mail->addCC('gurrutia@axioma.cl', 'German Urrutia');
//$mail->addBCC('mario.gaete@axioma.cl', 'Mario Gaete');
//Set the subject line
$mail->Subject = 'Estado de Contratos SGD';
$mail->Body = $html; // Mensaje a enviar
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
echo 'Message sent!';
}



