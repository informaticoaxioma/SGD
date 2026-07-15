<?php

class Autoload {

  public function __construct() {

    spl_autoload_register(function($class_name){
      $models_path = '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/' . $class_name . '.php';
      error_log($class_name);
      $controllers_path = '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/Controllers/' . $class_name . '.php';
      error_log($class_name);
      error_log($models_path);
      error_log($controllers_path);

      if ( file_exists($models_path))  require_once($models_path);
      if ( file_exists($controllers_path))  require_once($controllers_path);

    });
  }

  public function __destruct() {
    $this;
  }
}
 ?>
