<?php

require_once '../../../data/Cargo.php';
require_once '../../../data/Usuario.php';
require_once '../../../data/Area.php';
$usuarioSession = new Usuario();
session_start();
$usuarioSession = $_SESSION['usuario'];
//destruyendo variables de sesion sin uso
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosHitos']);

$serviceCargo = new Cargo();
$serviceArea = new Area();
$areas = $serviceArea->getAreas();

$cargos = $usuarioSession->getIdPerfil() == 1 ? $serviceCargo->getCargos() : $serviceCargo->getCargosPorContrato($usuarioSession->getIdContrato());

if ($_POST) {

    $flag = htmlspecialchars($_POST['flag']);


    switch ($flag) {

        case 1://INGRESAR CARGO

            $idCargo = $serviceCargo->getMaxCargoId() + 1;
            $nombreCargo = ucwords(htmlspecialchars($_POST['nombreCargo']));
            
            if (isset($_POST['idContrato'])) {
                $idContrato = htmlspecialchars($_POST['idContrato']);
            }


            if ($usuarioSession->getIdPerfil() == 1) {//INGRESO DE CARGO VIA ADMIN
                echo $serviceCargo->ingresarCargos($idCargo, $nombreCargo, $idContrato) == 1 ? $idCargo : -1;
            } else {
                echo $serviceCargo->ingresarCargos($idCargo, $nombreCargo, $usuarioSession->getIdContrato()) == 1 ? $idCargo : -1;
            }



            break;

        case 2://ACTUALIZAR O MODIFICAR CARGO

            $idCargo = htmlspecialchars($_POST['idCargo']);
            $nombreCargo = ucwords(htmlspecialchars($_POST['nombreCargo']));

            $cargo = new Cargo();
            $cargo->setIdCargo($idCargo);
            $cargo->setCargo($nombreCargo);

            echo $serviceCargo->actualizarCargo($cargo);

            break;

        case 3://eliminar Cargo

            $idCargo = htmlspecialchars($_POST['idCargo']);

            echo $serviceCargo->eliminarCargo($idCargo);

            break;
    }
}