<?php

require_once '../../../data/Area.php';
require_once '../../../data/Contrato.php';
require_once '../../../data/SubContrato.php';
require_once '../../../data/Funciones.php';

$serviceArea = new Area();
$serviceContrato = new Contrato();
$serviceSubContrato = new SubContrato();
$serviceFunciones = new Funciones();

$areas = $serviceArea->getAreas();
$subContratos = $serviceSubContrato->getSubContratos();
$contratos = $serviceContrato->getContratos();


if ($_POST) {

    $flag = htmlspecialchars($_POST['flag']);

    switch ($flag) {//ACCION SEGUN EL VALOR DEL LA FLAG
        case 1://AGREGAR CONTRATO

            $contrato = new Contrato();

            $idArea = htmlspecialchars($_POST['idArea']);
            $nombreContrato = ucwords(htmlspecialchars($_POST['nombreContrato']));
            $fechaTermino = $serviceFunciones->formatoFechaGuardarDB($_POST['fechaTermino']);

            $contrato->setContrato($nombreContrato);
            $contrato->setFechaTermino($fechaTermino);
            $contrato->setIdArea($idArea);

            echo $serviceContrato->ingresarContrato($contrato);

            break;

        case 2: //ACTUALIZAR CONTRATO

            $contratoActualizar = new Contrato();

            $idContrato = htmlspecialchars($_POST['idContrato']);
            $nombreContrato = ucwords(htmlspecialchars($_POST['nombreContrato']));
            $idArea = htmlspecialchars($_POST['idArea']);
            $fechaTermino = htmlspecialchars($_POST['fechaTermino']);


            $contratoActualizar->setContrato($nombreContrato);
            $contratoActualizar->setFechaTermino($serviceFunciones->formatoFechaGuardarDB($fechaTermino));
            $contratoActualizar->setIdArea($idArea);
            $contratoActualizar->setIdContrato($idContrato);

            echo $serviceContrato->actualizarContrato($contratoActualizar);

            break;


        case 3://ELIMINAR CONTRATO

            $idContrato = htmlspecialchars($_POST['idContrato']);
            echo $serviceContrato->eliminarContrato($idContrato);

            break;

        case 4://INGRESAR SUBCONTRATO

            $idContrato = htmlspecialchars($_POST['idContrato']);
            $nombreSubContrato = ucwords(htmlspecialchars($_POST['nombreSubContrato']));


            $subContrato = new SubContrato();
            $subContrato->setNombreSubContrato($nombreSubContrato);
            $subContrato->setIdContrato($idContrato);

            echo $serviceSubContrato->agregarSubContrato($subContrato);
            break;

        case 5://ACTUALIZAR SUBCONTRATO

            $idSubContrato = htmlspecialchars($_POST['idSubContrato']);
            $idContrato = htmlspecialchars($_POST['idContrato']);

            $subContrato = new SubContrato();
            $subContrato->setIdContrato($idContrato);
            $subContrato->setIdSubcontrato($idSubContrato);


            echo $serviceSubContrato->actualizarSubcontrato($subContrato);

            break;

        case 6://ELIMINAR SUBCONTRATO
            $idSubContrato = htmlspecialchars($_POST['idSubContrato']);

            echo $serviceSubContrato->eliminarSubcontrato($idSubContrato);

            break;
    }
}

