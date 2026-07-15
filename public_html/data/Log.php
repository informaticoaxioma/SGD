<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Log
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Log {

    private $idLog;
    private $nombreUsuario;
    private $accion;
    private $fechaAccion;
    private $idUsuario;

    public function __construct() {
        
    }

    function getIdLog() {
        return $this->idLog;
    }

    function getNombreUsuario() {
        return $this->nombreUsuario;
    }

    function getAccion() {
        return $this->accion;
    }

    function getFechaAccion() {
        return $this->fechaAccion;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function setIdLog($idLog) {
        $this->idLog = $idLog;
    }

    function setNombreUsuario($nombreUsuario) {
        $this->nombreUsuario = $nombreUsuario;
    }

    function setAccion($accion) {
        $this->accion = $accion;
    }

    function setFechaAccion($fechaAccion) {
        $this->fechaAccion = $fechaAccion;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    /**
     * 
     * @param int $idUsuario id del usuario a buscar log
     * @return array Retorna un array con los resultados de acuerdo al valor ingresado por parametro
     */
    public function getLogPorUsuario($idUsuario) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM log WHERE id_usuario='$idUsuario'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset de la query
            $logs = array(); //array para almacenar los datos

            while ($r = mysqli_fetch_array($rs)) {//obteniendo los resultados
                //instaceando y seteando el objeto
                $log = new Log();
                $log->setIdLog($r['id_log']);
                $log->setNombreUsuario($r['nombre_usuario']);
                $log->setAccion($r['accion']);
                $log->setFechaAccion($r['fecha_accion']);
                $log->setIdUsuario($r['id_usuario']);

                array_push($logs, $log); //agregando objeto al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $logs; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Objeto $log Objeto con los parametros a ingresar en la DB
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarLog($log) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "INSERT INTO log(nombre_usuario,accion,fecha_accion,id_usuario) "
                    . "VALUES('" . $log->getNombreUsuario() . "','" . $log->getAccion() . "','" . $log->getFechaAccion() . "','" . $log->getIdUsuario() . "')"; //query


            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1;//asignando valor a la flag

            mysqli_close($cnx);


            return $exito; //retornando resultado
            
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
