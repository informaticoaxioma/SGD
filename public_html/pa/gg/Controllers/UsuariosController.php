<?php
require_once '/var/www/sgd.axioma.cl/public_html/pa/gg/Models/UsuariosModels.php'; 



class UsuariosController {
	private $model;
	public function __construct() {
		$this->model = new UsuariosModels();
	}
	public function set( $data = array() ) {
		return $this->model->set($data);
	}

	public function setUserSubcontrato( $data = array() ) {
		return $this->model->setUserSubcontrato($data);
	}

	public function getFiltro( $data = array() ) {
		return $this->model->getFiltro($data);
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

	public function eliminarSubcontratosUsuario( $id = '' ) {
		return $this->model->eliminarSubcontratosUsuario($id);
	}
	public function get( $id = '' ) {
		return $this->model->get($id);
	}

	
	public function updateSubcontratoUsuario($idSubcontrato ='',$idUsuario = '') {
		return $this->model->updateSubcontratoUsuario($idSubcontrato,$idUsuario);
	}

	public function getPerfilUsuario( $id = '' ) {
		return $this->model->getPerfilUsuario($id);
	}
	public function getIDPerfilUsuario( $id = '' ) {
		return $this->model->getIDPerfilUsuario($id);
	}
	public function getAllPerfiles() {
		return $this->model->getAllPerfiles();
	}

	public function getAllEstados() {
		return $this->model->getAllEstados();
	}

	public function getContratoUsuario( $id = '' ) {
		return $this->model->getContratoUsuario($id);
	}

	public function getEstadoUsuario( $id = '' ) {
		return $this->model->getEstadoUsuario($id);
	}

	public function getPorContratoUser($idContratoUser = '') {
		return $this->model->getPorContratoUser($idContratoUser);
	}

	public function getSubcontratosPorIDContrato($idContratoUser = '') {
		return $this->model->getSubcontratosPorIDContrato($idContratoUser);
	}
	public function getTipoUsuario( $id = '' ) {
		return $this->model->getTipoUsuario($id);
	}

	public function getAnexos( $id = '' ) {
		return $this->model->getAnexos($id);
	}
	public function verificaEmail( $correo = '' ) {
		return $this->model->verificaEmail($correo);
	}	

	public function verificaUsername( $nombre_usuario = '' ) {
		return $this->model->verificaUsername($nombre_usuario);
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
