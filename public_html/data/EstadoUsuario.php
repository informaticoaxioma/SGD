<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EstadoUsuario
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class EstadoUsuario {

    private $idEstadoUsuario;
    private $estadoUsuario;

    public function __construct() {
        
    }

    function getIdEstadoUsuario() {
        return $this->idEstadoUsuario;
    }

    function getEstadoUsuario() {
        return $this->estadoUsuario;
    }

    function setIdEstadoUsuario($idEstadoUsuario) {
        $this->idEstadoUsuario = $idEstadoUsuario;
    }

    function setEstadoUsuario($estadoUsuario) {
        $this->estadoUsuario = $estadoUsuario;
    }

    /**
     * 
     * @return array Retorna un array con los estados presentes en la base de datos
     */
    public function getEstados() {
        try {

            $serviceCnx = new Conexion();
            $cnx = $serviceCnx->conectar();
            $sql = "SELECT * FROM estado_usuario";
            $rs = mysqli_query($cnx, $sql);
            $estados = array();

            while ($r = mysqli_fetch_array($rs)) {
                $estado = new EstadoUsuario();
                $estado->setIdEstadoUsuario($r['id_estado_usuario']);
                $estado->setEstadoUsuario($r['estado_usuario']);

                array_push($estados, $estado);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $estados;
            
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
