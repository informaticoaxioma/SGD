<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Perfil
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Perfil {

    private $idPerfil;
    private $perfil;

    public function __construct() {
        
    }

    function getIdPerfil() {
        return $this->idPerfil;
    }

    function getPerfil() {
        return $this->perfil;
    }

    function setIdPerfil($idPerfil) {
        $this->idPerfil = $idPerfil;
    }

    function setPerfil($perfil) {
        $this->perfil = $perfil;
    }

    /**
     * 
     * @return array Retorna un Array con todos los perfiles almacenados en la base de datos
     */
    public function getPerfiles() {
        try {
            $serviceCnx = new Conexion(); //Servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM perfil"; //query
            $perfiles = array(); //array para almacenar los objetos
            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo resultados
            while ($r = mysqli_fetch_array($rs)) {
                //Instanceando y seteando objetos
                $perfil = new Perfil();
                $perfil->setIdPerfil($r['id_perfil']);
                $perfil->setPerfil($r['perfil']);

                array_push($perfiles, $perfil); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $perfiles; //retornando array con objetos
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }        
    
    public function getPerfilesNoAdmin() {
        try {
            $serviceCnx = new Conexion(); //Servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM perfil where id_perfil != 1"; //query
            $perfiles = array(); //array para almacenar los objetos
            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo resultados
            while ($r = mysqli_fetch_array($rs)) {
                //Instanceando y seteando objetos
                $perfil = new Perfil();
                $perfil->setIdPerfil($r['id_perfil']);
                $perfil->setPerfil($r['perfil']);

                array_push($perfiles, $perfil); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $perfiles; //retornando array con objetos
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idPerfil id del perfil a buscar
     * @return Objeto Retorna un objeto del tipo perfil 
     */
    public function getPerfilPorId($idPerfil) {
        
        try {
            
            $serviceCnx = new Conexion(); //Servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM perfil WHERE id_perfil='$idPerfil'"; //query

            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo resultados
            while ($r = mysqli_fetch_array($rs)) {
                //Instanceando y seteando objetos
                $perfil = new Perfil();
                $perfil->setIdPerfil($r['id_perfil']);
                $perfil->setPerfil($r['perfil']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $perfil; //retornando el perfil
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

   /**
    * 
    * @param String $perfil Nombre del perfil a ingresar
    * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
    */
    public function insertarPerfil($perfil) {
        try {
            
            $serviceCnx = new Conexion(); //Servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "INSERT INTO perfil(perfil) VALUES('$perfil')"; //query

            $rs = mysqli_query($cnx, $sql); //resultado de la query


            $exito = -1; //flag

            if ($rs) {//si el result set es true retorno 1
                $exito = 1;
            }

            mysqli_close($cnx);

            return $exito; //retornando flag 
            
        } catch (Exception $ex) {
            
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idPerfil id del perfil a actualizar
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarPerfil($idPerfil) {
        try {
            
            $serviceCnx = new Conexion(); //Servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "DELETE FROM perfil WHERE id_perfil='$idPerfil'"; //query

            $rs = mysqli_query($cnx, $sql); //resultado de la query


            $exito = -1; //flag

            if ($rs) {//si el result set es true retorno 1
                $exito = 1;
            }

            mysqli_close($cnx);

            return $exito; //retornando flag 
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Objeto $perfil Objeto con los atributos a actualizar
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarPerfil($perfil) {
        try {
            $serviceCnx = new Conexion(); //Servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            
            $sql = "UPDATE perfil SET perfil='" . $perfil->getPerfil() . "' WHERE id_perfil='".$perfil->getIdPerfil()."'"; //query

            $rs = mysqli_query($cnx, $sql); //resultado de la query


            $exito = -1; //flag

            if ($rs) {//si el result set es true retorno 1
                $exito = 1;
            }

            mysqli_close($cnx);

            return $exito; //retornando flag 
            
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
