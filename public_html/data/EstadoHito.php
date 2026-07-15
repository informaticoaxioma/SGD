<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EstadoHito
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class EstadoHito {

    private $idEstadoHito;
    private $estadoHito;

    public function __construct() {
        
    }

    function getIdEstadoHito() {
        return $this->idEstadoHito;
    }

    function getEstadoHito() {
        return $this->estadoHito;
    }

    function setIdEstadoHito($idEstadoHito) {
        $this->idEstadoHito = $idEstadoHito;
    }

    function setEstadoHito($estadoHito) {
        $this->estadoHito = $estadoHito;
    }

    /**
     * Metodo que obtiene todos los estados de un hito
     * @return array Retorna un array con todos los estados
     */
    public function getEstados() {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar();
            $sql = "SELECT * FROM estado_hito";
            $rs = mysqli_query($cnx, $sql);
            $estadosHito = array();

            while ($r = mysqli_fetch_array($rs)) {
                $estado = new EstadoHito();
                $estado->setIdEstadoHito($r['id_estado_hito']);
                $estado->setEstadoHito($r['estado_hito']);

                array_push($estadosHito, $estado);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $estadosHito;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene un estado de acuerdo a su ID
     * @param int $idEstadoHito id del estado
     * @return Objeto Retorna un objeto con los atributos
     */
    public function getEstadoPorId($idEstadoHito) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar();
            $sql = "SELECT * FROM estado_hito WHERE id_estado_hito='$idEstadoHito'";
            $rs = mysqli_query($cnx, $sql);            

            while ($r = mysqli_fetch_array($rs)) {
                $estado = new EstadoHito();
                $estado->setIdEstadoHito($r['id_estado_hito']);
                $estado->setEstadoHito($r['estado_hito']);

               
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $estado;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
