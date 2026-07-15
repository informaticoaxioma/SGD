<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Color
 *
 * @author vfernandez
 */

require_once 'Conexion.php';

class Color {
    
    private $idColor;
    private $color;
    
    public function __construct() {
        
    }
    
    function getIdColor() {
        return $this->idColor;
    }

    function getColor() {
        return $this->color;
    }

    function setIdColor($idColor) {
        $this->idColor = $idColor;
    }

    function setColor($color) {
        $this->color = $color;
    }

    
    /**
     * 
     * @return array Retorna todos los colores almacenados en la base de datos
     */
  public function getColores() {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar();
            $sql =  "SELECT * FROM color";
            $rs = mysqli_query($cnx, $sql);
            $colores = array();
            
            while ($r = mysqli_fetch_array($rs)) {
                
               $color = new Color();
               $color->setIdColor($r['id_color']);
               $color->setColor($r['codigo_color']);
                
               array_push($colores, $color);
                
            }
            
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);
            
            return $colores;                    
                    
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    
    /**
     * 
     * @param int $idColor id del color a buscar
     * @return Objeto Retorna un Objeto tipo Color con todos los atributos presentes en la db
     */
    public function getColorPorId($idColor) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar();
            $sql =  "SELECT * FROM color WHERE id_color='$idColor'";
            $rs = mysqli_query($cnx, $sql);
            $colores = array();
            
            while ($r = mysqli_fetch_array($rs)) {
                
               $color = new Color();
               $color->setIdColor($r['id_color']);
               $color->setColor($r['codigo_color']);               
                              
            }
            
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);
            
            return $color;                    
                    
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    
}
