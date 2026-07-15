<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConexionCloud
 *
 * @author vfernandez
 */
class ConexionCloud {

    /*private $servidor = "10.100.0.20";
    private $usuario = "remttaxi";
    private $contrasena = "R.61_axiW2";
    private $db = "clouddb";*/
    private $servidor = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $db = "gestor_documental";
    public  $conexion_id;
    public  $error;

    public function __construct() {
        
    }

    /**
     * 
     * @return mysql_link Retorna el link de la conexion a la base de datos
     */
    public function conectar() {
        try {
//metodo para establecer la conexion con la base de datos
            $this->conexion_id = mysqli_connect($this->servidor, $this->usuario, $this->contrasena, $this->db);

            if (!$this->conexion_id) {//si es false se retorna un mensaje de error
                return $this->error = "Error, no se ha podido conectar a la base de datos";
            }

            return $this->conexion_id; //retornando link de la conexion
        } catch (Exception $e) {
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
