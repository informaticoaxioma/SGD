<?php
// Clase abstracta de conexion mysql
abstract class Model
{
  // Atributos
  private static $db_host = '10.50.0.11';
  private static $db_user = 'DEV2';
  private static $db_pass = 'D3V.aXi2024';
  private static $db_name = 'gestor_documental';
  private static $db_charset = 'utf8';
  private $conn;
  protected $query;
  protected $rows = array();
  // Metodos
  abstract protected function set();
  abstract protected function get();
  abstract protected function del();

  //metodo privado para conectarse a la base de dato
  private function db_open()
  {
    $this->conn = new mysqli(
      self::$db_host,
      self::$db_user,
      self::$db_pass,
      self::$db_name
    );

    $this->conn->set_charset(self::$db_charset);
  }
  //metodo privado para desconectarse de la base de dato
  private function db_close()
  {
    $this->conn->close();
  }

  //Ejecuta query simple insert, delete, update
  protected function set_query()
  {
    $this->db_open();
    $val = false;
    if ($this->conn->query($this->query)) {
      $val = true;
    }
    $this->db_close();
    return $val;
  }
  //
  protected function set_query_id()
  {
    $this->db_open();
    $this->conn->query($this->query);
    $id = mysqli_insert_id($this->conn);
    $this->db_close();
    return $id;
  }

  // Traer resultados de una consulta de tipo select en un array
  protected function get_query()
  {

    $this->db_open();
    $result = $this->conn->query($this->query);
    while ($this->rows[] = $result->fetch_assoc());
    $result->close();
    $this->db_close();
    return array_pop($this->rows);
  }


  protected function get_single_query() {
    $this->get_query();
    return !empty($this->rows) ? end($this->rows) : null;
}

protected function get_all_query() {
  $this->db_open();
  $result = $this->conn->query($this->query);
  $rows = [];
  while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
  }
  $result->close();
  $this->db_close();
  return $rows;
}

}
