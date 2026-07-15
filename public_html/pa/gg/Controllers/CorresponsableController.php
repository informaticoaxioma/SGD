<?php
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/CorresponsableModels.php'; 



class CorresponsableController {
	private $model;
	public function __construct() {
		$this->model = new CorresponsableModels();
	}
	public function set( $data = array() ) {
		return $this->model->set($data);
	}

	public function setUserSubcontrato( $data = array() ) {
		return $this->model->setUserSubcontrato($data);
	}
	
	public function updateUserNoPass( $data = array() ) {
		return $this->model->updateUserNoPass($data);
	}
	public function updateUserWithPassword( $data = array() ) {
		return $this->model->updateUserWithPassword($data);
	}
	public function updateUserWithoutPassword( $data = array() ) {
		return $this->model->updateUserWithoutPassword($data);
	}
	public function updateDatosUsuario( $data = array() ) {
		return $this->model->updateDatosUsuario($data);
	}
	public function updateContraseñaUsuario( $data = array() ) {
		return $this->model->updateContraseñaUsuario($data);
	}
	public function updatePerfilUsuario( $data = array() ) {
		return $this->model->updatePerfilUsuario($data);
	}
	public function updatePass( $data = array() ) {
		return $this->model->updatePass($data);
	}
	public function updatePerfil( $data = array() ) {
		return $this->model->updatePerfil($data);
	}

	public function updateUsuario( $data = array() ) {
		return $this->model->updateUsuario($data);
	}

	public function eliminarCorresponsablesPorIdDocumento( $idDocumento = '' ) {
		return $this->model->eliminarCorresponsablesPorIdDocumento($idDocumento);
	}


	public function eliminarSubcontratosUsuario( $id = '' ) {
		return $this->model->eliminarSubcontratosUsuario($id);
	}

	public function getCorresponsablesPorIdDocumento( $id = '' ) {
		return $this->model->getCorresponsablesPorIdDocumento($id);
	}

	public function get( $id = '' ) {
		return $this->model->get($id);
	}

	public function updateSubcontratoUsuario($idSubcontrato ='',$idUsuario = '') {
		return $this->model->updateSubcontratoUsuario($idSubcontrato,$idUsuario);
	}

	public function getTipoUsuario( $id = '' ) {
		return $this->model->getTipoUsuario($id);
	}

	public function getNombreUsuario( $id = '' ) {
		return $this->model->getNombreUsuario($id);
	}
	public function del( $id = '' ) {
		return $this->model->del($id);
	}
	public function __destruct() {
    $this;
	}
}
