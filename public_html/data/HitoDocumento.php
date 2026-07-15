<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HitoDocumento
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class HitoDocumento {

    private $idHito;
    private $idDocumento;

    public function __construct() {
        
    }

    function getIdHito() {
        return $this->idHito;
    }

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function setIdHito($idHito) {
        $this->idHito = $idHito;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

    public function ingresarDocumentoHito($idHito,$idDocumento) {
        try {
            $serviceCnx = new Conexion();//SERVICIO DE LA CONEXION
            $cnx = $serviceCnx->conectar();//LINK DE LA CONEXION
            $sql = "INSERT INTO hito_documento(id_hito,id_documento) VALUES('$idHito','$idDocumento')";//QUERY

            $exito = mysqli_query($cnx, $sql) == TRUE ? 1 : -1;//ASIGNANDO FLAG SEGUN RESULTADO DE LA OPERACION

            return $exito;//RETORNANDO RESULTADOS
            
        } catch (Exception $exc) {
            echo $exc->getMessage();//RETORNANDO MENSAJE DE EXCECION
        }
    }

    public function getDocumentosPorHito($idHito) {
        try {
            $serviceCnx = new Conexion();//SERVICIO DE LA CONEXION
            $cnx = $serviceCnx->conectar();//LINK DE LA CONEXION
            $sql = "SELECT * FROM hito_documento WHERE id_hito='$idHito'";//QUERY
            $rs = mysqli_query($cnx, $sql);//RESULTSET
            $documentosHito = array();//ARRAY PARA ALMACENAR RESULTADOS

            while ($r = mysqli_fetch_array($rs)) {//OBTENIENDO RESULTADOS
                //INTANCEANDO Y SETEANDO OBJETOS
                $documentoHito = new HitoDocumento();
                $documentoHito->setIdDocumento($r['id_documento']);
                $documentoHito->setIdHito($r['id_hito']);

                array_push($documentosHito, $documentoHito);//INSERTANDO OBJETOS
            }

            //LIBERANDO RECURSOS
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documentosHito;//RETORNANDO RESULTADOS
            
        } catch (Exception $exc) {
            echo $exc->getMessage();//MENSAJE DE EXCEPCION
        }
    }
    
    
    /**
     * Método que elimina un registro de la tabla hito_documento
     * @param int $idDocHito id del docHito a eliminar
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
      public function eliminarDocHitoPorID($idDocHito) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "DELETE FROM hito_documento WHERE id_documento='$idDocHito'";
            $rs = mysqli_query($cnx, $sql); //resultset de la query

            $exito = $rs == true ? 1 : -1; //seteando la flag segun el resultset
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
