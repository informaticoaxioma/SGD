<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mail
 *
 * @author vfernandez
 */
class Mail
{

    /**
     * 
     * @param String $titulo titulo del correo
     * @param String $mensaje Cuerpo o mensaje del correo
     * @param String $correo Direccion de destino del correo
     */
    public function enviarMail($titulo, $mensaje, $correo)
    {

        if (!isset($mail)) {
            require_once('class.phpmailer.php');
            $mail = new PHPMailer;
        }

        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "mail.axioma.cl"; // SMTP a utilizar. Por ej. smtp.elserver.com
        $mail->Username = "sisaxioma@axioma.cl"; // Correo completo a utilizar
        $mail->Password = "2%9PLZV;e903[h8&dg"; // Contraseña
        $mail->Port = 587; // Puerto a utilizar
        $mail->From = "sisaxioma@axioma.cl"; // Desde donde enviamos (Para mostrar)
        $mail->FromName = "Sistema de gestión documental";
        $mail->AddAddress($correo); // Esta es la dirección a donde enviamos

        $mail->IsHTML(true); // El correo se envía como HTML
        $mail->Subject = $titulo; // Este es el titulo del email.
        $body = $mensaje;
        $mail->CharSet = 'UTF-8';
        $mail->Body = $body; // Mensaje a enviar
        $mail->AltBody = "";
        $mail->ContentType = "text/html";
        try {


            $mail->Send(); // Envía el correo.
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * 
     * @param string $nombreUsuario nombre del usuario
     * @param String $contrasena Contraseña del usuario
     * @return string Devuelve el cuerpo del mensaje en formato html
     */
    public function generarBodyMail($nombreUsuario, $contrasenaMail, $contrasena)
    {
        try {

            $tabla = "<h1><strong>Sistema de gestión documental</strong></h1>";
            $tabla .= "<hr>";
            $tabla .= "<table>"
                . "<tr>"
                . "<td>"
                . "Se informa que se ha creado una cuenta de usuario en el sistema Gestor Documental de Axioma Ingenieros Consultores SA. con las siguientes credenciales:"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "Nombre Usuario: <strong>" . $nombreUsuario . "</strong>"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "Contraseña: <strong>" . $contrasena . "</strong>"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "Link de ingreso:"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "<a href='http://sgd.axioma.cl' target='_blank'>http://sgd.axioma.cl</a>"
                . "</td>"
                . "</tr>"
                . "<tr>"
                . "<td>"
                . "En caso de requerir información adicional sobre el funcionamiento del sistema favor contactar al encargado de documentación de su contrato, si desconoce esta información, favor aclarar con su residente. "
                . "</td>"
                . "</tr>"


                . "</table>";
            $tabla .= "<hr>";
            $tabla .= "<div style='width:50%'> <img alt='logoAxioma' src='http://consultoravial.cl/aplicaciones/boletasGarantia/media/logoAxiomaOficial.png'/>  </div>";

            return $tabla;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function generarBodyMailGenerico($asunto)
    {
        try {

            $tabla = "<h1><strong>Sistema de gestión documental</strong></h1>";
            $tabla .= "<hr>";
            $tabla .= "<table>"
                . "<tr>"
                . "<td>"
                . $asunto
                . "</td>"
                . "</tr>"
                . "</table>";
            $tabla .= "<hr>";
            //    $tabla.="<div style='width:50%'> <img alt='logoAxioma' src='http://consultoravial.cl/aplicaciones/boletasGarantia/media/logoAxiomaOficial.png'/></div>";

            return $tabla;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function enviarMailResumenMes($mensaje, $correo)
    {

        if (!isset($mail)) {
            require_once('class.phpmailer.php');
            $mail = new PHPMailer;
        }

        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "mail.axioma.cl"; // SMTP a utilizar. Por ej. smtp.elserver.com
        $mail->Username = "sisaxioma@axioma.cl"; // Correo completo a utilizar
        $mail->Password = "2%9PLZV;e903[h8&dg"; // Contraseña
        $mail->Port = 587; // Puerto a utilizar
        $mail->From = "sisaxioma@axioma.cl"; // Desde donde enviamos (Para mostrar)
        $mail->FromName = "Sistema de gestión documental";
        $mail->AddAddress($correo); // Esta es la dirección a donde enviamos

        $mail->IsHTML(true); // El correo se envía como HTML
        $mail->Subject = "Resumen mensual gestorDocumental."; // Este es el titulo del email.
        $body = $mensaje;
        $mail->CharSet = 'UTF-8';
        $mail->Body = $body; // Mensaje a enviar
        $mail->AltBody = "";
        $mail->ContentType = "text/html";

        $mail->addAttachment("Resumen_mensual.pdf");
        $mail->Send(); // Envía el correo.
    }
}
