<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bitacora
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Bitacora {

    private $idBitacora;
    private $emisor;
    private $fechaEmision;
    private $asunto;
    private $detalleRespuesta;
    private $idDocumento;

    public function __construct() {
        
    }
    
    function getIdBitacora() {
        return $this->idBitacora;
    }

    function getEmisor() {
        return $this->emisor;
    }

    function getFechaEmision() {
        return $this->fechaEmision;
    }

    function getAsunto() {
        return $this->asunto;
    }

    function getDetalleRespuesta() {
        return $this->detalleRespuesta;
    }

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function setIdBitacora($idBitacora) {
        $this->idBitacora = $idBitacora;
    }

    function setEmisor($emisor) {
        $this->emisor = $emisor;
    }

    function setFechaEmision($fechaEmision) {
        $this->fechaEmision = $fechaEmision;
    }

    function setAsunto($asunto) {
        $this->asunto = $asunto;
    }

    function setDetalleRespuesta($detalleRespuesta) {
        $this->detalleRespuesta = $detalleRespuesta;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

        
    /**
     * 
     * @param int $idDocumento id del documento
     * @return array Retorna un array con las bitacoras relacionadas al documento
     */
    public function getBitacoraPorDocumento($idDocumento) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //conexion con al base de datos
            $sql = "SELECT * FROM bitacora  WHERE id_documento='$idDocumento'"; //query
            $bitacoras = array(); //array para almacenar resultados
            $rs = mysqli_query($cnx, $sql); //resultSet de la query

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                $bitacora = new Bitacora();
                $bitacora->setIdBitacora($r['id_bitacora']);
                $bitacora->setEmisor($r['emisor']);
                $bitacora->setFechaEmision($r['fecha_emision']);
                $bitacora->setAsunto($r['asunto']);
                $bitacora->setDetalleRespuesta($r['detalle_respuesta']);
                $bitacora->setIdDocumento($r['id_documento']);

                array_push($bitacoras, $bitacora); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $bitacoras; //retornando resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    public function ingresarBitacora($bitacora) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //conexion con al base de datos
            $sql = "INSERT INTO bitacora(emisor,fecha_emision,asunto,detalle_respuesta,id_documento) "
                    . "VALUES('" . $bitacora->getEmisor() . "','" . $bitacora->getFechaEmision() . "',"
                    . "'" . $bitacora->getAsunto() . "','" . $bitacora->getDetalleRespuesta() . "','" . $bitacora->getIdDocumento() . "')"; //query


            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //resultSet de la query
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando resultados
            
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
