<?php

require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/Model.php';

class DocumentoModels extends Model
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

  public function ingresarDocumentoConFile($doc, $fileContent = null, $fileMimeType = null) {
    try {

        $serviceCnx = new Conexion(); //Clase servicio
        $cnx = $serviceCnx->conectar(); //link de conexion

        // Preparing the SQL query with placeholders for prepared statement
        $sql = "INSERT INTO documento(
            id_documento, nombre_documento, documento, mime_documento, 
            tamano_documento, fecha_documento, fecha_recepcion, 
            fecha_plazo, id_estado_doc, id_flujo, id_subcontrato
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare the statement to avoid SQL injection
        if ($stmt = mysqli_prepare($cnx, $sql)) {
            // Bind the parameters to the statement
            mysqli_stmt_bind_param($stmt, "isssssssiii",
                $doc->getIdDocumento(), 
                $doc->getNombreDocumento(), 
                $fileContent,
                $fileMimeType,
                $doc->getTamanoDocumento(), 
                $doc->getFechaDocumento(), 
                $doc->getFechaRecepcion(), 
                $doc->getFechaPlazo(), 
                $doc->getIdEstadoDoc(), 
                $doc->getIdFlujo(), 
                $doc->getIdSubContrato()
            );

            // Execute the prepared statement
            $exito = mysqli_stmt_execute($stmt) ? 1 : -1;

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            // Handle error in preparation of statement
            $exito = -1;
        }
        //liberando recursos

        mysqli_close($cnx);


        return $exito; //retornando la flag
    } catch (Exception $ex) {

        echo $ex->getMessage(); //mensaje de excepcion
    }
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
