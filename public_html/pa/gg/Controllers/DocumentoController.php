<?php
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/DocumentoModels.php'; 



class DocumentoController {
	private $model;
	public function __construct() {
		$this->model = new DocumentoModels();
	}
	public function set( $data = array() ) {
		return $this->model->set($data);
	}
	public function insertarDetalleDocumento( $data = array() ) {
		return $this->model->insertarDetalleDocumento($data);
	}    
    
    public function ingresarDocumentoConFile($doc, $fileContent = null, $fileMimeType = null) {
		return $this->model->ingresarDocumentoConFile($doc, $fileContent, $fileMimeType);
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
