<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EstadoDocumento
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class EstadoDocumento {

    private $idEstadoDoc;
    private $estadoDoc;

    public function __construct() {
        
    }

    function getIdEstadoDoc() {
        return $this->idEstadoDoc;
    }

    function getEstadoDoc() {
        return $this->estadoDoc;
    }

    function setIdEstadoDoc($idEstadoDoc) {
        $this->idEstadoDoc = $idEstadoDoc;
    }

    function setEstadoDoc($estadoDoc) {
        $this->estadoDoc = $estadoDoc;
    }

    /**
     * 
     * @return array Retorna un array con todos los estados disponibles en la base de datos
     */
    public function getEstadosDocs() {
        try {
            $serviceCnx = new Conexion(); //servicio par establecer conexion
            $cnx = $serviceCnx->conectar(); //conexion con la base de datos
            $sql = "SELECT * FROM estado_documento"; //query
            $estadosDocs = array(); //array para almacenar los resultados
            $rs = mysqli_query($cnx, $sql); //resultSet
            //obteniendo resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto
                $estadoDoc = new EstadoDocumento();
                $estadoDoc->setIdEstadoDoc($r['id_estado_doc']);
                $estadoDoc->setEstadoDoc($r['estado_doc']);

                //agregando el objeto al array
                array_push($estadosDocs, $estadoDoc);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $estadosDocs; //retornando resultados 
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idEstadoDoc id del estado a buscar
     * @return Objeto Retorna un objeto con el resultado
     */
    public function getEstadosPorID($idEstadoDoc) {
        try {
            $serviceCnx = new Conexion(); //servicio par establecer conexion
            $cnx = $serviceCnx->conectar(); //conexion con la base de datos
            $sql = "SELECT * FROM estado_documento WHERE id_estado_doc='$idEstadoDoc'"; //query

            $rs = mysqli_query($cnx, $sql); //resultSet
            //obteniendo resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto
                $estadoDoc = new EstadoDocumento();
                $estadoDoc->setIdEstadoDoc($r['id_estado_doc']);
                $estadoDoc->setEstadoDoc($r['estado_doc']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $estadoDoc; //retornando resultados 
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
