<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accion
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Accion {

    private $idAccion;
    private $idDocumento;
    private $idActividad;

    public function __construct() {
        
    }

    function getIdAccion() {
        return $this->idAccion;
    }

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function getIdActividad() {
        return $this->idActividad;
    }

    function setIdAccion($idAccion) {
        $this->idAccion = $idAccion;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

    function setIdActividad($idActividad) {
        $this->idActividad = $idActividad;
    }

    /**
     * 
     * @param int $idDocumento id del documento
     * @return array Retorna una array con todas las acciones asociadas al documento
     */
    public function getAccionesPorDocumento($idDocumento) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "SELECT * FROM accion WHERE id_documento ='$idDocumento'"; //query
            $acciones = array(); //array que almacenara los resultados
            $rs = mysqli_query($cnx, $sql); //resultset de la query

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                //instanceando y seteando objetos
                $accion = new Accion();
                $accion->setIdAccion($r['id_accion']);
                $accion->setIdDocumento($r['id_documento']);
                $accion->setIdActividad($r['id_actividad']);

                //agregando objetos al array
                array_push($acciones, $accion);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $acciones; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Objeto $accion Objeto con los atributos a guardan en la Base de datos
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarAccion($idDocumento, $accion) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "INSERT INTO accion(id_documento,id_actividad) "
                    . "VALUES('$idDocumento','$accion');"; //query          


            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //asignando valor a la flag
            //liberando recursos

            mysqli_close($cnx);

            return $exito; //Retornando resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    
    /**
     * 
     * @param int $idDocumento id del documento relacionados con las acciones
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function eliminarAcciones($idDocumento) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "DELETE FROM accion WHERE id_documento='$idDocumento'"; //query          


            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //asignando valor a la flag
            //liberando recursos

            mysqli_close($cnx);

            return $exito; //Retornando resultados
            
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
