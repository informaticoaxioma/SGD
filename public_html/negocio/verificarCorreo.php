<?php

require_once '../data/Usuario.php';

$serviceUsuario = new Usuario();

$correo = htmlspecialchars($_GET['correo']);

if ($serviceUsuario->verificarCamposUnicos("", $correo, 2) == 0) {

    echo 'true';
} else {

    echo 'false';
}
