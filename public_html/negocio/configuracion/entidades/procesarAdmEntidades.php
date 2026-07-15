<?php

require_once '../../../data/Cargo.php';
require_once '../../../data/TipoEntidad.php';
require_once '../../../data/Entidad.php';
require_once '../../../data/Usuario.php';
require_once '../../../data/Contrato.php';

$sessionUsuario = new Usuario();
session_start();
$sessionUsuario = $_SESSION['usuario'];
//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);



$serviceCargo = new Cargo();
$serviceTipoEntidad = new TipoEntidad();
$serviceEntidad = new Entidad();
$serviceContrato = new Contrato();

$tipoEntidades = $serviceTipoEntidad->getTiposEntidades();

if($sessionUsuario->getIdPerfil() == 1){
    $cargos = $serviceCargo->getCargos();
}else{
    $cargos = $serviceCargo->getCargosUsuario($sessionUsuario->getIdContrato());
}


//SI EL USUARIO ES SUPER ADMIN PUEDE VER TODAS LAS ENTIDADES, SI NO SOLO PUEDE VER LAS ENTIDADES ASOCIADAS A SU CONTRATO
$entidades = $sessionUsuario->getIdPerfil() == 1 ? $serviceEntidad->getEntidades() : $serviceEntidad->getEntidadesPorContrato($sessionUsuario->getIdContrato());

$contratos = $serviceContrato->getContratos();

if ($_POST) {


    $flag = htmlspecialchars($_POST['flag']);

    switch ($flag) {

        case 1://INGRESAR ENTIDAD

            $idEntidad = $serviceEntidad->getMaxIdEntidad() + 1;
            $nombreEntidad = ucwords(htmlspecialchars($_POST['nombreEntidad']));
            $apellidoEntidad = ucwords(htmlspecialchars($_POST['apellidoEntidad']));
            $idCargo = htmlspecialchars($_POST['idCargo']);
            $idTipoEntidad = htmlspecialchars($_POST['idTipoEntidad']);


            $entidad = new Entidad();
            $entidad->setIdEntidad($idEntidad);
            $entidad->setNombreEntidad($nombreEntidad);
            $entidad->setApellidoEntidad($apellidoEntidad);
            $entidad->setIdTipoEntidad($idTipoEntidad);
            $entidad->setIdCargo($idCargo);
            //El contrato de la entidad se asignara de forma automatica si quien lo ingresa no es administrador
            if ($sessionUsuario->getIdPerfil() != 1) {

                $entidad->setIdContrato($sessionUsuario->getIdContrato());
            } else {//SUPER ADMIN
                $idContrato = htmlspecialchars($_POST['idContrato']);
                $entidad->setIdContrato($idContrato);
            }

            echo $serviceEntidad->ingresarEntidad($entidad);
            break;

        case 2://ACTUALIZAR ENTIDAD
            $idEntidad = ucwords(htmlspecialchars($_POST['idEntidad']));
            $nombreEntidad = ucwords(htmlspecialchars($_POST['nombreEntidad']));
            $apellidoEntidad = ucwords(htmlspecialchars($_POST['apellidoEntidad']));
            $idCargo = htmlspecialchars($_POST['idCargo']);
            $idTipoEntidad = htmlspecialchars($_POST['idTipoEntidad']);

            $entidad = new Entidad();
            $entidad->setIdEntidad($idEntidad);
            $entidad->setNombreEntidad($nombreEntidad);
            $entidad->setApellidoEntidad($apellidoEntidad);
            $entidad->setIdTipoEntidad($idTipoEntidad);
            $entidad->setIdCargo($idCargo);


            echo $serviceEntidad->actualizarEntidad($entidad);
            break;


        case 3://ELIMINAR ENTIDAD

            $idEntidad = htmlspecialchars($_POST['idEntidad']);

            echo $serviceEntidad->eliminarEntidad($idEntidad);

            break;
    }
}
