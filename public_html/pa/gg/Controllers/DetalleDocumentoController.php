<?php
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/DetalleDocumentoModels.php'; 



class DetalleDocumentoController {
	private $model;
	public function __construct() {
		$this->model = new DetalleDocumentoModels();
	}
	public function set( $data = array() ) {
		return $this->model->set($data);
	}
	public function insertarDetalleDocumento( $data = array() ) {
		return $this->model->insertarDetalleDocumento($data);
	}

	public function get( $id = '' ) {
		return $this->model->get($id);
	}	
	public function del( $id = '' ) {
		return $this->model->del($id);
	}
	public function __destruct() {
    $this;
	}
}
