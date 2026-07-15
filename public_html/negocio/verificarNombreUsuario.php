<?php

require_once '../data/Usuario.php';

$serviceUsuario = new Usuario();

$nombreUsuario = htmlspecialchars($_GET['nombreUsuario']);


if ($serviceUsuario->verificarCamposUnicos($nombreUsuario, "", 1) == 0) {

    echo 'true';
} else {

    echo 'false';
}

       