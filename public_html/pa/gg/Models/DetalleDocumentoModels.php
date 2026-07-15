<?php

require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/Model.php';

class DetalleDocumentoModels extends Model
{

  public function set($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "REPLACE INTO corresponsable (id_corresponsable_doc, id_documento, id_usuario_corresponsable) VALUES
    ($id_corresponsable_doc, $id_documento,$id_usuario_corresponsable)";
    error_log($this->query);    
    $val = $this->set_query_id();

    return $val;
  }
  public function insertarDetalleDocumento($user_data = array())
  {
    foreach ($user_data as $key => $value) {
      $$key = $value;
    }

    $this->query = "INSERT INTO detalle_documento (id_documento, num_documento, num_providencia, num_proceso, id_remitente, id_destinatario, materia, antecedente, incluye, comentario, id_tipo_doc, id_responsable) VALUES ($id_documento,'$num_documento','$num_providencia','$num_proceso',$id_remitente,$id_destinatario,'$materia','$antecedente','$incluye','$comentario',$id_tipo_doc,$id_responsable)";
    error_log($this->query);    
    $val = $this->set_query();
    
    return $val;
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
