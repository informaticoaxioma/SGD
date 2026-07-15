<?php

require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/Model.php';

class CorresponsableModels extends Model
{

  public function set($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "insert INTO corresponsable (id_corresponsable_doc, id_documento, id_usuario_corresponsable) VALUES
    ($id_corresponsable_doc, $id_documento,$id_usuario_corresponsable)";
    error_log($this->query);
    $val = $this->set_query();

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

    error_log("Val es: " . $val);

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
    error_log($this->query);
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
    error_log($this->query);
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
    error_log($this->query);
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }

  public function updateSubcontratoUsuario($idSubcontrato = '', $idUsuario = '')
  {

    $this->query = "UPDATE usuario_subcontrato set
    id_subcontrato= $idSubcontrato
     WHERE id_usuario=$idUsuario ";
    error_log($this->query);
    //  echo $this->query;
    //$this->set_query_id();
    $val = $this->set_query();

    return $val;
  }

  public function getNombreUsuario($id = '')
  {

    $this->query = "SELECT primer_nombre, segundo_nombre, apellido_paterno, apellido_materno FROM usuarios WHERE id_usuario = $id ";
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

  public function eliminarCorresponsablesPorIdDocumento($idDocumento = '')
  {
    $this->query = "DELETE from corresponsable WHERE id_documento = $idDocumento";
    error_log("QUERY DE ELIMINAR CORRESPONSABLE POR ID:");
    error_log($this->query);
    //echo $this->query;
    $this->set_query();
  }


  public function getCorresponsablesPorIdDocumento($id_documento = '')
  {

    $this->query = "SELECT c.id_usuario_corresponsable as idUsuario, CONCAT(u.nombre, u.apellido_p) as nombre  FROM corresponsable c JOIN usuario u on u.id_usuario=c.id_usuario_corresponsable where id_documento = $id_documento;";
    error_log("----QUERY DE GET RESPONSABLE POR ID DOCUMENTO");
    error_log($this->query);
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
