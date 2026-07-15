<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FrecuenciaHito
 *
 * @author vfernandez
 */

require_once 'Conexion.php';

class FrecuenciaHito {
    
    private $idFrecuenciaHito;
    private $frecuenciaHito;
    
    public function __construct() {
        
    }
    
    function getIdFrecuenciaHito() {
        return $this->idFrecuenciaHito;
    }

    function getFrecuenciaHito() {
        return $this->frecuenciaHito;
    }

    function setIdFrecuenciaHito($idFrecuenciaHito) {
        $this->idFrecuenciaHito = $idFrecuenciaHito;
    }

    function setFrecuenciaHito($frecuenciaHito) {
        $this->frecuenciaHito = $frecuenciaHito;
    }

/**
 * Metodo que obtiene todas las frecuencias que pueda tener un hito
 * @return array Retorna todas las fracuencias almacenadas en la base de datos
 */
    public function getFrecuencias() {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar();
            $sql =  "SELECT * FROM frecuencia_hito";
            $rs = mysqli_query($cnx, $sql);
            $frecuencias = array();
            
            while ($r = mysqli_fetch_array($rs)) {
                
               $frecuencia = new FrecuenciaHito();
               $frecuencia->setIdFrecuenciaHito($r['id_frecuencia_hito']);
               $frecuencia->setFrecuenciaHito($r['frecuencia_hito']);
                
               array_push($frecuencias, $frecuencia);
                
            }
            
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);
            
            return $frecuencias;                    
                    
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
 /**
  * Metodo que obtiene una frecuencia segun su ID
  * @param int $idFrecuencia id de la frecuencia
  * @return Obj Retorna un objeto con los resultados
  */
    public function getFrecuenciaPorId($idFrecuencia) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar();
            $sql =  "SELECT * FROM frecuencia_hito WHERE id_frecuencia_hito='$idFrecuencia'";
            $rs = mysqli_query($cnx, $sql);
                    
            while ($r = mysqli_fetch_array($rs)) {
                
               $frecuencia = new FrecuenciaHito();
               $frecuencia->setIdFrecuenciaHito($r['id_frecuencia_hito']);
               $frecuencia->setFrecuenciaHito($r['frecuencia_hito']);                
            }
            
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);
            
            return $frecuencia;                    
                    
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    
    
    
    
    
}
