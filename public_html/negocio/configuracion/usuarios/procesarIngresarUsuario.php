<?php

require_once '../../../data/Area.php';
require_once '../../../data/Usuario.php';
require_once '../../../data/Contrato.php';
require_once '../../../data/Perfil.php';
require_once '../../../data/Cargo.php';
require_once '../../../data/Mail.php';
require_once '../../../data/UsuarioSubcontrato.php';
require_once '../../../data/SubContrato.php';

//servicios
$serviceArea = new Area();
$servicePerfil = new Perfil();
$serviceCargo = new Cargo();
$serviceUsuario = new Usuario(); //servicio usuarios
$serviceContrato = new Contrato();
$serviceSubContrato = new SubContrato();

//cargando datos necesarios
$areas = $serviceArea->getAreas();
$cargos = $serviceCargo->getCargos();
$perfiles = $servicePerfil->getPerfiles();
$perfilesNoAdmin = $servicePerfil->getPerfilesNoAdmin();

if ($_POST) {

    $usuario = new Usuario();
    //atributos del objeto

    $nombre = ucwords(htmlspecialchars($_POST['nombre']));
    $apellidoP = ucwords(htmlspecialchars($_POST['apellidoP']));
    $apellidoM = ucwords(htmlspecialchars($_POST['apellidoM']));
    $correo = htmlspecialchars($_POST['correo']);
    $nombreUsuario = htmlspecialchars($_POST['nombreUsuario']);
    $contrasena = md5(htmlspecialchars($_POST['contrasena']));

    $idPerfil = htmlspecialchars($_POST['idPerfil']);
    $idContrato = htmlspecialchars($_POST['idContrato']);
    $idArea = htmlspecialchars($_POST['idArea']);
    $idUsuario = $serviceUsuario->getMaxIdUsuario() + 1;

    //seteando el objeto
    $usuario->setIdUsuario($idUsuario);
    $usuario->setNombre($nombre);
    $usuario->setApellidoP($apellidoP);
    $usuario->setApellidoM($apellidoM);
    $usuario->setCorreo($correo);
    $usuario->setNombreUsuario($nombreUsuario);
    $usuario->setContrasena($contrasena);
    $usuario->setIdContrato($idContrato);
    $usuario->setIdPerfil($idPerfil);
    $usuario->setIdEstadoUsuario(1);

    //agregando al usuario
    if ($serviceUsuario->ingresarUsuario($usuario) == 1) { //si es correcto se crea cuenta en cloud
        //CREANDO USUARIO EN CUENTA CLOUD
        $area = "";

        switch ($idArea) {
            case 1:
                $area = "Administracion";
                break;

            case 2:
                $area = "Asesoria";
                break;

            case 3:
                $area = "Concesiones";
                break;

            case 4:
                $area = "Estudio";
                break;
        }

        //ingresando subcontratos
        $serviceUsuarioSubcontrato = new UsuarioSubcontrato();
        //obteniendo subcontratos

        $idUsuarioSubcontratos = $_POST['idUsuarioSubContratos']; //obteniendo el array        
        if (count($idUsuarioSubcontratos) > 0) {
            for ($i = 0, $iMax = count($idUsuarioSubcontratos); $i < $iMax; $i++) {
                $usuarioSubContrato = new UsuarioSubcontrato();
                $usuarioSubContrato->setIdUsuario($idUsuario);
                $usuarioSubContrato->setIdSubcontrato($idUsuarioSubcontratos[$i]);

                $serviceUsuarioSubcontrato->ingresarUsuarioSubcontrato($usuarioSubContrato);
            }
        }
        //
        //$serviceUsuario->ingresarUsuarioCloud($nombre, $nombreUsuario, $area);
        //enviando mail
        // Only send email if correo is set and not empty
        if (!empty($correo)) {
            //enviando mail
            $contrasenaMail = "aXi%.2016P";
            $titulo = "Cuenta usuario Sistema de Gestión Documental";
            $serviceMail = new Mail();
            $mensaje = $serviceMail->generarBodyMail($nombreUsuario, $contrasenaMail, htmlspecialchars($_POST['contrasena']));
            $serviceMail->enviarMail($titulo, $mensaje, $correo);
        }
        echo 1;
    } else {

        echo -1;
    }
}
