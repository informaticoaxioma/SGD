<?php

require_once '../../../data/Perfil.php';

$servicePerfil = new Perfil();
$perfiles = $servicePerfil->getPerfiles();

if ($_POST) {

    $flag = htmlspecialchars($_POST['flag']);

    switch ($flag) {

        case 1://INGRESAR PERFIL
            
            $perfil = strtoupper(htmlspecialchars($_POST['perfil']));

            echo $servicePerfil->insertarPerfil($perfil);

            break;

        case 2://ACTUALIZAR O MODIFICAR PERFIL

            $idPerfil = htmlspecialchars($_POST['idPerfil']);
            $nombrePerfil = strtoupper(htmlspecialchars($_POST['nombrePerfil']));

            $perfil = new Perfil();//INSTANCEANDO Y SETEANDO EL OBJETO
            $perfil->setIdPerfil($idPerfil);
            $perfil->setPerfil($nombrePerfil);

            echo $servicePerfil->actualizarPerfil($perfil);//METODO PARA ACTUALIZAR EL PERFIL

            break;

        case 3://ELIMINAR PERFIL
            
            $idPerfil = htmlspecialchars($_POST['idPerfil']);
            
            echo $servicePerfil->eliminarPerfil($idPerfil);

            break;
    }
}
