<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Area
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Area {

    private $idArea;
    private $area;

    public function __construct() {
        
    }

    function getIdArea() {
        return $this->idArea;
    }

    function getArea() {
        return $this->area;
    }

    function setIdArea($idArea) {
        $this->idArea = $idArea;
    }

    function setArea($area) {
        $this->area = $area;
    }

    /**
     * 
     * @return array Retorna todas las areas presentes en la BD
     */
    public function getAreas() {
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM area"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $areas = array(); //array para las areas

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $area = new Area();
                $area->setIdArea($r['id_area']);
                $area->setArea($r['nombre_area']);
                array_push($areas, $area);    //llenando el array con las areas de la DB
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $areas; //retornando el array 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getAreaPorIdArea($idArea) {
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM area WHERE id_area=$idArea"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $areas = array(); //array para las areas

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $area = new Area();
                $area->setIdArea($r['id_area']);
                $area->setArea($r['nombre_area']);
                array_push($areas, $area);    //llenando el array con las areas de la DB
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $areas; //retornando el array 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
