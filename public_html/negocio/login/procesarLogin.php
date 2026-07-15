<?php

require_once 'data/Usuario.php';
require_once 'data/Log.php';
require_once 'data/Documento.php';


if ($_POST) {

    //var_dump($_POST);

    $nombreUsuario = htmlspecialchars($_POST['nombreUsuario']);
    $contrasena = md5(htmlspecialchars($_POST['contrasena']));
    //echo $contrasena;
    $serviceUsuario = new Usuario();
    $serviceLog = new Log();
    $serviceDocumento = new Documento();


    $usuario = $serviceUsuario->verificarLogin($nombreUsuario, $contrasena);

    if ($usuario != NULL) {

        //GRABANDO ACCION EN EL LOG
        $log = new Log();
        $log->setAccion("Usuario inicio sesión");
        $log->setNombreUsuario($usuario->getNombre() . " " . $usuario->getApellidoP());
        $log->setIdUsuario($usuario->getIdUsuario());
        $log->setFechaAccion($serviceDocumento->obtenerFechaConHora());

        $serviceLog->ingresarLog($log);

	//session_set_cookie_params(3600);
        session_start();
        $_SESSION['usuario'] = $usuario;
        header("location:pa/inicio.php");
    } else {

        $error = "Error, usuario o contraseña incorrectas";
    }
}
