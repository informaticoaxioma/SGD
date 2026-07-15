<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Funciones
 *
 * @author vfernandez
 */
require_once 'Conexion.php';
require_once 'Mail.php';

class  Funciones {

    public function __construct() {
        
    }

    /**
     * 
     * @return Date Retorna la fecha con hora del sistema
     */
    public function obtenerFechaConHora() {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //links de la conexion
            $sqlFechaActual = "SELECT DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s') as fecha"; //query
            $rs = mysqli_query($cnx, $sqlFechaActual); //resultSet

            $fechaFormat = ""; //inicializando variable

            while ($row = mysqli_fetch_array($rs)) {//obteniendo los resultados
                $fechaFormat = $row['fecha'];
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $fechaFormat; //retornando fecha
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @return Date Retorna la fecha actual del la base de datos (sistema)
     */
    public function obtenerFechaActual() {
        try {

            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT date_format(NOW(),'%d-%m-%Y') fecha"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada           
            $fecha = "";

            while ($r = mysqli_fetch_array($rs)) {
                $fecha = $r['fecha'];
            }
//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $fecha; //retornando la flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @return Retorna fecha sin hora con formato para guardar en la DB
     */
    public function obtenerFechaSinHora() {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //links de la conexion
            $sqlFechaActual = "SELECT DATE_FORMAT(NOW(),'%Y-%m-%d') as fecha"; //query
            $rs = mysqli_query($cnx, $sqlFechaActual); //resultSet

            $fechaFormat = ""; //inicializando variable

            while ($row = mysqli_fetch_array($rs)) {//obteniendo los resultados
                $fechaFormat = $row['fecha'];
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $fechaFormat; //retornando fecha
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Date $fecha Recibe una fecha con formato normal (DD-MM-YY)
     * @return Date Retorna fecha formateada para guardar en la BD (YY-MM-DD)
     */
    function formatoFechaGuardarDB($fecha) {
        $anio = substr($fecha, 6, 4);
        $mes = substr($fecha, 3, 2);
        $dia = substr($fecha, 0, 2);

        $fechaFinal = $anio . "-" . $mes . "-" . $dia;
        return $fechaFinal;
    }

    /**
     * 
     * @param Date $fecha fecha con formato YY-MM-DD
     * @return Date Retorna fecha con formato DD-MM-YY
     */
    function formatoFecha($fecha) {
        $anio = substr($fecha, 0, 4);
        $mes = substr($fecha, 5, 2);
        $dia = substr($fecha, 8, 2);

        $fechaFinal = $dia . "-" . $mes . "-" . $anio;

        return $fechaFinal;
    }


}
