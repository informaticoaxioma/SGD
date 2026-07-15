<?php

require_once '../data/Usuario.php';

if ($_GET) {


    $flag = htmlspecialchars($_GET['flag']);
    $idContrato = htmlspecialchars($_GET['idContrato']);

    switch ($flag):
        case 1://cargar contrato
            $idArea = htmlspecialchars($_GET['idArea']);

            $serviceContrato = new Contrato();
            $contratos = $serviceContrato->getContratosPorArea($idArea);



            if (!empty($contratos)):

                echo "<select id='idContrato' name='idContrato' class='form-control' onchange='cargarResponsable(this.value)'>";
                echo "<option value=''>Seleccione</option>";

                foreach ($contratos as $c) {
                    echo "<option value=" . $c->getIdContrato() . ">" . $c->getContrato() . "</option>";
                }

                echo "</select>";


            else:

                echo "<select id='idContrato' name='idContrato' class='form-control' disabled>";


                echo "<option value=''>No hay contratos asociados</option>";


                echo "</select>";

            endif;

            unset($contratos);


            break;

        case 2: //cargar usuario por contrato

            $serviceUsuario = new Usuario();
            $usuarios = $serviceUsuario->getUsuariosPorContrato($idContrato);

            echo "<select id='idResponsable' name='idResponsable' class='form-control' >";
            echo "<option value=''>Seleccione</option>";

            foreach ($usuarios as $u):
                echo "<option value='" . $u->getIdUsuario() . "'>" . $u->getNombre() . " " . $u->getApellidoP() . "</option>";
            endforeach;
            
            break;
            
    endswitch;
}

