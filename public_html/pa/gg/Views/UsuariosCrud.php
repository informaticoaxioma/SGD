<?php

require_once '../../gg/Controllers/UsuariosController.php';

switch ($_POST['accion']) {

    case 'agegarUsuarioEncargado':

        $verificaUsername = new UsuariosController();
        $usernameVerificado = $verificaUsername->verificaUsername($_POST['nombre_usuario']);
        if ($usernameVerificado == 1) {
            $salidaUsuarioExistente = array(
                'estado' => '0',
                'mensaje' => " El usuario ya se encuentra registrado"
            );
            echo json_encode($salidaUsuarioExistente);
            break;
        } else {

            $controllerUsuarios = new UsuariosController();
            $datos = array(

                'id_usuario' => 0,
                'nombre' => addslashes($_POST['nombre']),
                'apellido_p' => addslashes($_POST['apellido_p']),
                'apellido_m' => addslashes($_POST['apellido_m']),
                'correo' => addslashes($_POST['correo']),
                'nombre_usuario' => addslashes($_POST['nombre_usuario']),
                'contrasena' => $_POST['contrasena'],
                'id_perfil' => addslashes($_POST['id_perfil']),
                'id_contrato' => addslashes($_POST['id_contrato']),
                'id_estado_usuario' => addslashes($_POST['id_estado_usuario'])

            );

            $ejecuta = $controllerUsuarios->set($datos);
            error_log($ejecuta);
            //HAcer un array con los subcontratos:
            $subcontratos = json_decode($_POST['subcontrato'], true);

            if ($ejecuta >= 1) {
                foreach ($subcontratos as $subcontratoId) {
                    $datosSubContrato = array(
                        'id_usuario' => $ejecuta,
                        'id_subcontrato' => addslashes($subcontratoId),
                    );
                    $ejecutaUsuarioSubcontrato = $controllerUsuarios->setUserSubcontrato($datosSubContrato);
                }
                $ok = 1;
                $mensaje = "Usuario creado con exito.";
            } else {
                $ok = 0;
                $mensaje = "No fue posible crear el Usuario.";
            }
            $salida = array(
                'estado' => $ok,
                'mensaje' => $mensaje
            );
            header('Content-Type: application/json');
            echo json_encode($salida);
        }




        break;

    case 'actualizarUsuarioEncargado2':
        $idPerfilUser = $_POST['idPerfilUser'];
        $perfilToUpdate = $_POST['perfil'];

        // Proceed with the update only in the allowed scenarios
        if ($idPerfilUser == 1 || ($idPerfilUser != 1 && $perfilToUpdate != 1)) {
            $controllerUsuarios = new UsuariosController();
            $datos = array(
                'id_usuario' => addslashes($_POST['id_usuario']),
                'nombre' => addslashes($_POST['nombre']),
                'apellido_p' => addslashes($_POST['apellido_p']),
                'apellido_m' => addslashes($_POST['apellido_m']),
                'correo' => addslashes($_POST['correo']),
                'id_perfil' => addslashes($_POST['perfil']),
                'id_estado_usuario' => addslashes($_POST['estado'])
            );

            // Check if password is provided
            if (!empty($_POST['contrasena'])) {
                $datos['contrasena'] = $_POST['contrasena'];
                $resultado = $controllerUsuarios->updateUserWithPassword($datos);
            } else {
                $resultado = $controllerUsuarios->updateUserWithoutPassword($datos);
            }

            // Determine the response
            if ($resultado) {
                $salida = array('estado' => 1, 'mensaje' => "Usuario actualizado con éxito.");
            } else {
                $salida = array('estado' => 0, 'mensaje' => "Error al actualizar el usuario.");
            }
        } else {
            // Not allowed to update to or from an admin profile
            $salida = array('estado' => 0, 'mensaje' => "Actualización no permitida.");
        }

        header('Content-Type: application/json');
        echo json_encode($salida);
        break;
    case 'actualizarUsuarioEncargado':
        $idPerfilUser = $_POST['idPerfilUser'];
        $perfilToUpdate = $_POST['perfil'];
        $controllerUsuarios = new UsuariosController();
        $perfilEnBD = $controllerUsuarios->getPerfilUsuario($_POST['id_usuario']);
        if ($perfilEnBD == 1) {
            $datos = array(
                'id_usuario' => addslashes($_POST['id_usuario']),
                'nombre' => addslashes($_POST['nombre']),
                'apellido_p' => addslashes($_POST['apellido_p']),
                'apellido_m' => addslashes($_POST['apellido_m']),
                'correo' => addslashes($_POST['correo']),
                'id_estado_usuario' => addslashes($_POST['estado'])
            );

            // Check if password is provided
            if (!empty($_POST['contrasena'])) {
                $datos['contrasena'] = $_POST['contrasena'];
                $resultado = $controllerUsuarios->updateDatosUsuario($datos);
                $resultado = $controllerUsuarios->updateContraseñaUsuario($datos);
            } else {
                $resultado = $controllerUsuarios->updateDatosUsuario($datos);
            }
        } else {
            if ($_POST['perfil'] != 1) {
                $datos = array(
                    'id_usuario' => addslashes($_POST['id_usuario']),
                    'nombre' => addslashes($_POST['nombre']),
                    'apellido_p' => addslashes($_POST['apellido_p']),
                    'apellido_m' => addslashes($_POST['apellido_m']),
                    'correo' => addslashes($_POST['correo']),
                    'id_estado_usuario' => addslashes($_POST['estado'])
                );
                $datosPerfil = array(
                    'id_usuario' => addslashes($_POST['id_usuario']),
                    'id_perfil' => addslashes($_POST['perfil'])
                );
                $resultado = $controllerUsuarios->updatePerfilUsuario($datosPerfil);
                if (!empty($_POST['contrasena'])) {
                    $datos['contrasena'] = $_POST['contrasena'];
                    $resultado = $controllerUsuarios->updateDatosUsuario($datos);
                    $resultado = $controllerUsuarios->updateContraseñaUsuario($datos);
                } else {
                    $resultado = $controllerUsuarios->updateDatosUsuario($datos);
                }
            } else {

                $salida = array('estado' => 0, 'mensaje' => "Actualización no permitida.");
            }
        }

        // Determine the response
        if ($resultado) {
            $salida = array('estado' => 1, 'mensaje' => "Usuario actualizado con éxito.");
        } else {
            $salida = array('estado' => 0, 'mensaje' => "Error al actualizar el usuario.");
        }
        header('Content-Type: application/json');
        echo json_encode($salida);
        break;

    case 'actualizarSubcontrato':
        $controllerUsuarios = new UsuariosController();
        $eliminar = $controllerUsuarios->eliminarSubcontratosUsuario($_POST['idUsuario']);
        // Insert selected subcontracts for the user
        foreach ($_POST['idSubcontrato'] as $subcontratoId) {
            $datosSubContrato = array(
                'id_usuario' => $_POST['idUsuario'],
                'id_subcontrato' => $subcontratoId, // Use the current subcontratoId from the loop
            );
            $ejecutaUsuarioSubcontrato = $controllerUsuarios->setUserSubcontrato($datosSubContrato);
            error_log("Resultado insert en foreach es: ".$ejecutaUsuarioSubcontrato);
        }


        // Determine the response
        if ($ejecutaUsuarioSubcontrato == 0) {
            $salida = array('estado' => 1, 'mensaje' => "Usuario actualizado con éxito.");
        } else {
            $salida = array('estado' => 0, 'mensaje' => "Error al actualizar el usuario.");
        }
        header('Content-Type: application/json');
        echo json_encode($salida);
        break;
}
