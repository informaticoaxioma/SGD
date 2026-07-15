<?php

require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/Model.php';

class UsuariosModels extends Model
{

  public function set($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "REPLACE INTO usuario (id_usuario, nombre, apellido_p, apellido_m, correo, nombre_usuario, contrasena, id_perfil, id_contrato, id_estado_usuario) VALUES
    ($id_usuario,'$nombre', '$apellido_p', '$apellido_m', '$correo', '$nombre_usuario', MD5('$contrasena'), $id_perfil, $id_contrato, $id_estado_usuario )";
    // este te deja ver la query pero se te mezcla con los datos que devuelves al ajax: echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query_id();

    return $val;
  }

  public function setUserSubcontrato($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "REPLACE INTO usuario_subcontrato (id_usuario, id_subcontrato) VALUES
    ($id_usuario, $id_subcontrato )";

    error_log($this->query);
    // este te deja ver la query pero se te mezcla con los datos que devuelves al ajax: echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query_id();

    error_log("Val es: ".$val);

    return $val;
  }
  public function updateUserNoPass($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuarios set
    rut='$rut', primer_nombre='$primer_nombre', segundo_nombre='$segundo_nombre', apellido_paterno='$apellido_paterno', apellido_materno='$apellido_materno',
    telefono='$telefono', celular='$celular', direccion='$direccion', id_estado_usuario=$id_estado_usuario ,nombre_institucion='$nombre_institucion' ,direccion_institucion='$direccion_institucion',telefonos_institucion='$telefonos_institucion', profesion='$profesion' WHERE id_usuario=$id_usuario ";
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }
  public function updatePass($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuarios set pass=MD5('$pass') WHERE id_usuario=$id_usuario ";
    $val = $this->set_query();

    return $val;
  }

  //Mios para Encargado Documental:


  public function updateUserWithoutPassword($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuario set
    nombre = $nombre, apellido_p = $apellido_p, apellido_m = $apellido_m, correo=$correo, id_perfil = $id_perfil, id_estado_usuario = $id_estado_usuario
     WHERE id_usuario=$id_usuario ";
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }

  public function updateUserWithPassword($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuario set
    nombre = $nombre, apellido_p = $apellido_p, apellido_m = $apellido_m, correo=$correo, contrasena=md5('$contrasena'), id_perfil = $id_perfil, id_estado_usuario = $id_estado_usuario
     WHERE id_usuario=$id_usuario ";
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }


  public function passTemporal($email)
  {
    $passAleatoria = trim(substr(md5(rand()), 0, 10));
    $this->query = "INSERT INTO recupera (id_recupera, fecha, email, new_pass)
    VALUES (0,NOW(),'$email',MD5('$passAleatoria'))";
    $val = $this->set_query();

    return $passAleatoria;
  }

  public function getTipoUsuario($id = '')
  {
    $this->query = "SELECT * FROM tipo_usuario";
    $this->get_query();
    $data = '';
    for ($i = 0; $i < count($this->rows); $i++) {
      ($id == $this->rows[$i]['id_tipo_usuario']) ? $selected = "selected" : $selected = "";
      $data .= '<option ' . $selected . ' value="' . $this->rows[$i]['id_tipo_usuario'] . '">' . $this->rows[$i]['nombre'] . '</option>';
    }
    return $data;
  }

  public function updatePerfil($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $query = "UPDATE usuarios SET primer_nombre='$pnombre', segundo_nombre='$snombre', a_paterno='$apaterno', a_materno='$amaterno'";

    if (isset($user_data['PassNueva']) && isset($user_data['PassNueva2'])) {
      if ($user_data['PassNueva'] !== $user_data['PassNueva2']) {
        // Passwords do not match
        return false;
      }
      //error_log("LLEGASTE A LA PASSWORD");
      //error_log($user_data['PassNueva']);
      //error_log($user_data['PassNueva2']);
      $Passfinal = md5($user_data['PassNueva']);
      $query .= ", pass='$Passfinal'";
    }

    $query .= " WHERE id_usuario=$id_usuario";
    error_log($query);
    $this->query = $query;
    $val = $this->set_query();

    return $val;
  }

  public function updateUsuario($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $query = "UPDATE usuarios SET primer_nombre='$pnombre', segundo_nombre='$snombre', a_paterno='$apaterno', a_materno='$amaterno', id_tipo_usuario='$id_tipo_usuario'";

    if (isset($user_data['PassNueva']) && isset($user_data['PassNueva2'])) {
      if ($user_data['PassNueva'] !== $user_data['PassNueva2']) {
        // Passwords do not match
        return false;
      }
      //error_log("LLEGASTE A LA PASSWORD");
      //error_log($user_data['PassNueva']);
      //error_log($user_data['PassNueva2']);
      $Passfinal = md5($user_data['PassNueva']);
      $query .= ", pass='$Passfinal'";
    }

    $query .= " WHERE id_usuario=$id_usuario";
    error_log($query);
    $this->query = $query;
    $val = $this->set_query();

    return $val;
  }

  public function updateDatosUsuario($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuario set
    nombre = '$nombre', apellido_p = '$apellido_p', apellido_m = '$apellido_m', correo='$correo', id_estado_usuario = '$id_estado_usuario'
     WHERE id_usuario=$id_usuario ";
    error_log($this->query );
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }

  public function updateContraseñaUsuario($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuario set
    contrasena=md5('$contrasena')
     WHERE id_usuario=$id_usuario ";
     error_log($this->query );
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }

  public function updatePerfilUsuario($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "UPDATE usuario set
    id_perfil = $id_perfil
     WHERE id_usuario=$id_usuario ";
     error_log($this->query );
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }

  public function updateSubcontratoUsuario($idSubcontrato ='',$idUsuario = '')
  {
    
    $this->query = "UPDATE usuario_subcontrato set
    id_subcontrato= $idSubcontrato
     WHERE id_usuario=$idUsuario ";
     error_log($this->query );
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }


  public function eliminarSubcontratosUsuario($idUsuario = '')
{
    if (!empty($idUsuario)) {
        $this->query = "DELETE FROM usuario_subcontrato WHERE id_usuario = $idUsuario";
        error_log($this->query);
        $result = $this->set_query();

        if ($result === false) {
            // Log error or handle it
            error_log("Error in executing DELETE query: " . $this->db_error);
            return 0;
        }

        return 1; // Return true if deletion was successful
    } else {
        // If $idUsuario is empty, log an error and return false
        error_log('Invalid user ID provided for deletion.');
        return 0;
    }
}
  public function getNombreUsuario($id = '')
  {

    $this->query = "SELECT nombre, apellido_p FROM usuarios WHERE id_usuario = $id ";
    //  echo $this->query."<br>";
    $this->get_query();
    $num_rows = count($this->rows);
    //echo $num_rows;
    $data = array();

    foreach ($this->rows as $key => $value) {
      // code...
      array_push($data, $value);
    }
    //return $this->rows;
    return $data;
  }



  public function get($user_id = '')
  {

    $this->query = ($user_id != '')
      ? "SELECT * FROM usuarios WHERE id_usuario = $user_id "
      : "SELECT * FROM usuarios";

    $this->get_query();
    // var_dump($this->rows);
    $num_rows = count($this->rows);
    //echo $num_rows;
    $data = array();

    foreach ($this->rows as $key => $value) {
      // code...
      array_push($data, $value);
    }
    //return $this->rows;
    return $data;
  }

  public function getPerfilUsuario($id_perfil = '')
  {

    $this->query = "SELECT perfil FROM perfil WHERE id_perfil = $id_perfil";
    error_log($this->query);
    $row = $this->get_single_query();
    return $row ? $row['perfil'] : null;
  }

  public function getIDPerfilUsuario($id_usuario = '')
  {

    $this->query = "SELECT id_perfil FROM usuario WHERE id_usuario = $id_usuario";
    error_log($this->query);
    $row = $this->get_single_query();
    return $row ? $row['id_perfil'] : null;
  }

  public function getAllPerfiles()
  {
    $this->query = "SELECT id_perfil, perfil FROM perfil";
    return $this->get_all_query();
  }

  public function getAllEstados()
  {
    $this->query = "SELECT id_estado_usuario, estado_usuario FROM estado_usuario";
    return $this->get_all_query();
  }

  public function getContratoUsuario($id_contrato = '')
  {
    $this->query = "SELECT nombre_contrato FROM contrato WHERE id_contrato = $id_contrato ";
    error_log($this->query);
    $row = $this->get_single_query();
    return $row ? $row['nombre_contrato'] : null;
  }
  public function getEstadoUsuario($estado_id = '')
  {

    $this->query = "SELECT estado_usuario FROM estado_usuario WHERE id_estado_usuario = $estado_id";
    error_log($this->query);
    $row = $this->get_single_query();
    return $row ? $row['estado_usuario'] : null;
  }

  public function getAllEstadoUsuario()
  {
    $this->query = "SELECT id_estado_usuario, estado_usuario FROM estado_usuario";
    return $this->get_all_query();
  }

  public function getPorContratoUser($idContratoUser = '')
  {

    $this->query = ($idContratoUser != '')
      ? "SELECT * FROM usuario WHERE id_contrato = $idContratoUser"
      : "SELECT * FROM usuario";

    $this->get_query();
    // var_dump($this->rows);
    $num_rows = count($this->rows);
    //echo $num_rows;
    $data = array();

    foreach ($this->rows as $key => $value) {
      // code...
      array_push($data, $value);
    }
    //return $this->rows;
    return $data;
  }

  public function getSubcontratosPorIDContrato($idContratoUser = '')
  {    
    $this->query = "SELECT id_subcontrato, nombre_subcontrato FROM subcontrato WHERE id_contrato = $idContratoUser";    
    $this->get_query();
    // var_dump($this->rows);
    $num_rows = count($this->rows);
    //echo $num_rows;
    $data = array();

    foreach ($this->rows as $key => $value) {
      // code...
      array_push($data, $value);
    }
    //return $this->rows;
    return $data;
  }

  public function getAnexos($user_id = '')
  {

    $this->query = ($user_id != '')
      ? "SELECT * FROM usuarios WHERE id_usuario = $user_id AND id_usuario <> 1"
      : "SELECT * FROM usuarios WHERE id_usuario <> 1 ORDER BY primer_nombre ASC";

    $this->get_query();
    // var_dump($this->rows);
    $num_rows = count($this->rows);
    //echo $num_rows;
    $data = array();

    foreach ($this->rows as $key => $value) {
      // code...
      array_push($data, $value);
    }
    //return $this->rows;
    return $data;
  }
  public function getFiltro($data = array())
  {
    $sw = 0;
    $sw2 = 0;
    $filtro = '';
    foreach ($data as $key => $value) {
      $$key = $value;
      if (!empty($$key)) {
        ($sw2 == 0) ? $filtro .= " WHERE " : $filtro .= "";
        $sw2++;
        $filtro .= array_keys($data)[$sw] . "='" . $$key . "' AND ";
      } else {
        $filtro .= '';
      }
      $sw++;
    }
    $limpio = substr($filtro, 0, -5);
    $this->query = "SELECT * FROM usuarios $limpio";
    //echo $this->query;
    $this->get_query();
    $num_rows = count($this->rows);
    $data = array();
    foreach ($this->rows as $key => $value) {
      array_push($data, $value);
    }
    return $data;
  }


  private function verificaRecuperaPass($user, $pass)
  {

    $this->query = "SELECT  u.id_usuario, r.id_recupera, r.fecha, r.email, r.new_pass
    FROM recupera r
    INNER JOIN usuarios u
    ON u.email=r.email
    WHERE r.email='$user' AND r.new_pass=MD5('$pass') AND DATE(r.fecha)=DATE(NOW())
    ORDER BY r.fecha DESC limit 1 ";
    error_log($this->query);
    $this->get_query();
    $data = array();
    foreach ($this->rows as $key => $value) {
      array_push($data, $value);
    }
    return $data;
  }
  private function updatePassRecupera($id_usuario, $pass)
  {

    $this->query = "UPDATE usuarios set pass=MD5('$pass') WHERE id_usuario=$id_usuario ";
    error_log($this->query);
    $val = $this->set_query();

    return $val;
  }
  private function logAcceso($user)
  {

    $this->query = "INSERT INTO logs (id_log, fecha, user, id_user, accion) VALUES (0,NOW(),'$user',1,1)";
    $val = $this->set_query();
  }
  private function eliminaRecupera($email)
  {
    $this->query = "DELETE from recupera WHERE email = '$email'";
    $this->set_query();
  }
  public function validate_user($user, $pass)
  {

    $verificaRecupera = self::verificaRecuperaPass($user, $pass);
    $salidaCount = count($verificaRecupera);
    if ($salidaCount >= 1) {
      //  var_dump($verificaRecupera);
      //exit;

      $actualizaRecupera = self::updatePassRecupera($verificaRecupera[0]['id_usuario'], $pass);

      if ($actualizaRecupera == true) {
        $eliminarecuperaPass = self::eliminaRecupera($user);

        $this->query = "SELECT u.id_usuario AS id_usuario, u.primer_nombre AS primer_nombre, u.segundo_nombre AS segundo_nombre, u.a_paterno AS a_paterno, u.a_materno AS a_materno, u.id_tipo_usuario , u.email
        FROM usuarios u  WHERE u.email = '$user' AND u.pass = MD5('$pass')";

        $this->get_query();
        $data = array();
        foreach ($this->rows as $key => $value) {
          array_push($data, $value);
        }
        return $data;
      }
    } else {

      $registraAccesoLog = self::logAcceso($user);

      $this->query = "SELECT u.id_usuario, u.primer_nombre, u.segundo_nombre, u.a_paterno, u.a_materno,u.id_tipo_usuario, u.email
      FROM usuarios u  WHERE u.email = '$user' AND u.pass = MD5('$pass')";
      $this->get_query();
      $data = array();

      foreach ($this->rows as $key => $value) {
        // code...
        array_push($data, $value);
      }
      //return $this->rows;
      return $data;
    }
  }
  public function verificaEmail($correo = '')
  {
    $this->query = "SELECT count(correo) AS total FROM usuario WHERE correo='$correo' ";
    $this->get_query();
    $salida = $this->rows[0]['total'];
    return $salida;
  }

  public function verificaUsername($nombre_usuario = '')
  {
    $this->query = "SELECT count(nombre_usuario) AS total FROM usuario WHERE nombre_usuario='$nombre_usuario' ";
    $this->get_query();
    $salida = $this->rows[0]['total'];
    return $salida;
  }

  public function del($id = '')
  {
    $this->query = "DELETE from usuarios WHERE id_usuario = $id";

    error_log($this->query);
    //echo $this->query;
    $this->set_query();
  }

  public function __destruct()
  {
    $this;
  }
}
