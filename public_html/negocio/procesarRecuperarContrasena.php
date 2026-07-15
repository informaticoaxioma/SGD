<?php

require_once '../data/Usuario.php';
require_once '../data/Mail.php';

$serviceUsuario = new Usuario();
$serviceMail = new Mail();

if ($_POST) {

    $correo = htmlspecialchars($_POST['correo']);

    if ($serviceUsuario->verificarCamposUnicos("", $correo, 2) != 0) {
        //generando contrasena al azar
        $caracteres = array(3, "d", "x", 9, "k", 2, "a", "w", "g", 7);
        $contrasenaNueva = "";

        for ($i = 0; $i < 8; $i++) {
            $azar = mt_rand(0, 9);
            $contrasenaNueva.=$caracteres[$azar];
        }

        $usuario = $serviceUsuario->getUsuarioPorCorreo($correo);

        $contrasenaFinal = md5($contrasenaNueva); //encriptando contraseña

        $usuario->setContrasena($contrasenaFinal); //actualizand contraseña

        if ($serviceUsuario->actualizarUsuario($usuario, 3) == 1) {

            $asunto = "<h1>Actualización de contraseña exitosa.</h1>"
                    . "<p><strong>Su nueva contraseña es: </strong>" . $contrasenaNueva . "</p>";
            $mensaje = $serviceMail->generarBodyMailGenerico($asunto);
            $titulo = "Recuperación de contraseña SGD";

            $serviceMail->enviarMail($titulo, $mensaje, $correo);

            echo 1;
        }
    } else {
        echo -1;
    }
}