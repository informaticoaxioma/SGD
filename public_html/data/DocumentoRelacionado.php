<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DocumentoRelacionado
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class DocumentoRelacionado {

    private $idDocumento;
    private $idDocumentoRelacionado;

    public function __construct() {
        
    }

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function getIdDocumentoRelacionado() {
        return $this->idDocumentoRelacionado;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

    function setIdDocumentoRelacionado($idDocumentoRelacionado) {
        $this->idDocumentoRelacionado = $idDocumentoRelacionado;
    }

    /**
     * Metodo que ingresa un registro a la tabla documento_relacionado
     * @param int $idDocumento id del documento
     * @param int $idDocumentoRelacionado id del documento relacionado
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarDocumentoRelacionado($idDocumento, $idDocumentoRelacionado) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "INSERT INTO documento_relacionado(id_documento,id_documento_rel) "
                    . "VALUES('$idDocumento','$idDocumentoRelacionado')"; //query

            $exito = mysqli_query($cnx, $sql) == TRUE ? 1 : -1; //asignando flag segun resultado
            //LIBERANDO RECURSOS
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //servicio de excepcion
        }
    }

    /**
     * Metodo que lista todos los documentos relacionados a otro documento
     * @param int $idDocumento Id del documento al cual estan relacionados los registros
     * @return array Retorna un array con los resultados obtenidos
     */
    public function listarDocumentosRelacionadosY($idDocumento) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM documento_relacionado WHERE id_documento='$idDocumento'"; //query
            $rs = mysqli_query($cnx, $sql); //result set
            $documentosRelacionados = array(); //array para guardar resultados

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                //instanceando y seteando objeto
                $docRelacionado = $r['id_documento_rel'];

                array_push($documentosRelacionados, $docRelacionado); //ingresando objetos al array
            }

            //LIBERANDO RECURSOS
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documentosRelacionados; //retornando resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    public function listarDocumentosRelacionadosX($idDocumento) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM documento_relacionado WHERE id_documento_rel='$idDocumento'"; //query
            $rs = mysqli_query($cnx, $sql); //result set
            $documentosRelacionados = array(); //array para guardar resultados

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                //instanceando y seteando objeto
                $docRelacionado = $r['id_documento'];


                array_push($documentosRelacionados, $docRelacionado); //ingresando objetos al array
            }

            //LIBERANDO RECURSOS
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documentosRelacionados; //retornando resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que elimina un registro de la tabla documento_relacionado
     * @param int $idDocRel id de documento a eliminar
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarDocRelacionadoPorID($idDocRel) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "DELETE FROM documento_relacionado WHERE id_documento_rel='$idDocRel'";
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
