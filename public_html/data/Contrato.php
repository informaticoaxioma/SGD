<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contrato
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Contrato {

    private $idContrato;
    private $fechaTermino;    
    private $contrato;
    private $idArea;

    public function __construct() {
        
    }

    function getFechaTermino() {
        return $this->fechaTermino;
    }

    function setFechaTermino($fechaTermino) {
        $this->fechaTermino = $fechaTermino;
    }

        
    function getIdContrato() {
        return $this->idContrato;
    }

    function getContrato() {
        return $this->contrato;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    function setContrato($contrato) {
        $this->contrato = $contrato;
    }

    function getIdArea() {
        return $this->idArea;
    }

    function setIdArea($idArea) {
        $this->idArea = $idArea;
    }

    /**
     * 
     * @param int $idArea id del area
     * @return Array Retorna un array con todos los contratos asociados a un area
     */
    public function getContratosPorArea($idArea) {

        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM contrato WHERE id_area='$idArea'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $contratos = array(); //array para las areas

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $contrato = new Contrato();
                $contrato->setIdArea($r['id_area']);
                $contrato->setIdContrato($r['id_contrato']);
                $contrato->setContrato($r['nombre_contrato']);
                $contrato->setFechaTermino($r['fecha_termino']);

                array_push($contratos, $contrato);    //llenando el array con las areas de la DB
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $contratos; //retornando el array 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    
    
    
     public function getContratos() {

        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM contrato"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $contratos = array(); //array para las areas

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $contrato = new Contrato();
                $contrato->setIdArea($r['id_area']);
                $contrato->setIdContrato($r['id_contrato']);
                $contrato->setContrato($r['nombre_contrato']);
                $contrato->setFechaTermino($r['fecha_termino']);

                array_push($contratos, $contrato);    //llenando el array con las areas de la DB
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $contratos; //retornando el array 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    public function getContratoPorId($idContrato) {
            error_log('YEAS!');
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM contrato WHERE id_contrato='$idContrato'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset          

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $contrato = new Contrato();
                $contrato->setIdArea($r['id_area']);
                $contrato->setIdContrato($r['id_contrato']);
                $contrato->setContrato($r['nombre_contrato']);
                $contrato->setFechaTermino($r['fecha_termino']);                
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $contrato; //retornando el array 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getContratoPorIdArea($idArea) {

        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM contrato WHERE id_area='$idArea'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset          

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $contrato = new Contrato();
                $contrato->setIdArea($r['id_area']);
                $contrato->setIdContrato($r['id_contrato']);
                $contrato->setContrato($r['nombre_contrato']);
                $contrato->setFechaTermino($r['fecha_termino']);                
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $contrato; //retornando el array 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param Objeto $contrato Objeto con los datos necesarios para ingresar un contrato
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarContrato($contrato) {

        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion

            $sql = "INSERT INTO contrato(nombre_contrato,fecha_termino,id_area) "
                    . "VALUES('" . $contrato->getContrato() . "','".$contrato->getFechaTermino()."','" . $contrato->getIdArea() . "')"; //query
            $rs = mysqli_query($cnx, $sql); //resultset

            $exito = -1; //flag 

            if ($rs) {//si el rs es true se asiga 1 si es false se mantiene el -1
                $exito = 1;
            }

            mysqli_close($cnx);

            return $exito; //retornando la flag
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param int $idContrato id del contrato a eliminar
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarContrato($idContrato) {

        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion

            $sql = "DELETE FROM contrato WHERE id_contrato='$idContrato'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset

            $exito = -1; //flag 

            if ($rs) {//si el rs es true se asiga 1 si es false se mantiene el -1
                $exito = 1;
            }

            mysqli_close($cnx);

            return $exito; //retornando la flag
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    
    /**
     * 
     * @param int $idContrato id del contrato
     * @param int $nombreContrato nombre modificado del contrato
     * @param int $idArea id del area modificada
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarContrato($contrato) {

        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion

            $sql = "UPDATE contrato SET nombre_contrato='".$contrato->getContrato()."',fecha_termino='".$contrato->getFechaTermino()."',"
                    . "id_area='".$contrato->getIdArea()."' WHERE id_contrato='".$contrato->getIdContrato()."'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset

            $exito = -1; //flag 

            if ($rs) {//si el rs es true se asiga 1 si es false se mantiene el -1
                $exito = 1;
            }

            mysqli_close($cnx);

            return $exito; //retornando la flag
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
