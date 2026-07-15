<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Conexion
 *
 * @author vfernandez
 */
class Conexion {
    private $servidor = "10.50.0.11";
    private $usuario = "DEV2";
    private $contrasena = "D3V.aXi2024";
    private $db = "gestor_documental";
    public  $conexion_id;
    public  $error;

    public function __construct() {
        
    }
   
    /**
     * Retorna el link de la conexion a la base de datos
     *
     * @return mysqli| string
     */
    public function conectar() {
        try {
            //metodo para establecer la conexion con la base de datos
            $this->conexion_id = mysqli_connect($this->servidor, $this->usuario, $this->contrasena, $this->db, '3306');

            if (!$this->conexion_id) {//si es false se retorna un mensaje de error
                return $this->error = "Error, no se ha podido conectar a la base de datos (".mysqli_connect_error().")";
            }

            return $this->conexion_id; //retornando link de la conexion
            
        } catch (Exception $e) {
            error_log($e);
            echo $e->getMessage();

        }
    }

    /**
     * @tutorial package Metodo que cierra la conexion establecida con la base de datos
     * @return int Retorna un 1 si el metodo es exitoso
     */
    public function cerrarConexion() {
        mysqli_close($this->conexion_id);
        return 1;
    }

}
