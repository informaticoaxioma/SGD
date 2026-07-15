<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TipoDocumento
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class TipoDocumento {

    private $idTipoDocumento;
    private $tipoDocumento;

    public function __construct() {
        
    }

    function getIdTipoDocumento() {
        return $this->idTipoDocumento;
    }

    function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    function setIdTipoDocumento($idTipoDocumento) {
        $this->idTipoDocumento = $idTipoDocumento;
    }

    function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    /**
     * 
     * @return array Retorna un array con todos los tipso de documentos presentes en la base de datos
     */
    public function getTipoDocumentos() {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT * FROM tipo_documento"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $tiposDocumentos = array(); //array para guardar los tipos de documentos

            while ($r = mysqli_fetch_array($rs)) {
                $tipoDoc = new TipoDocumento(); //instanceando y seteando objetos
                $tipoDoc->setIdTipoDocumento($r['id_tipo_doc']);
                $tipoDoc->setTipoDocumento($r['tipo_documento']);

                array_push($tiposDocumentos, $tipoDoc); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $tiposDocumentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idTipoDoc id del documento a buscar
     * @return Objeto Retorna un Objeto con el id ingresado por parametro
     */
    public function getTipoDocumentoPorId($idTipoDoc) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT * FROM tipo_documento WHERE id_tipo_doc ='$idTipoDoc' ORDER BY tipo_documento"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada


            while ($r = mysqli_fetch_array($rs)) {

                $tipoDoc = new TipoDocumento(); //instanceando y seteando objeto
                $tipoDoc->setIdTipoDocumento($r['id_tipo_doc']);
                $tipoDoc->setTipoDocumento($r['tipo_documento']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $tipoDoc; //retornando el objeto con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param String $tipoDoc Nombre del tipo de documento
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function ingresarTipoDoc($tipoDoc) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "INSERT INTO tipo_documento(tipo_documento) VALUES('$tipoDoc')"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = -1; //flag para el resultado de la query

            if ($rs) {//si rs es true $exito ==1
                $exito = 1;
            }

            //liberando recursos
            mysqli_close($cnx);


            return $exito; //retornando el flag con el resultado de la insercion
            
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idTipoDoc id de tipo de documento a eliminar
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function eliminarTipoDoc($idTipoDoc) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "DELETE FROM tipo_documento WHERE id_tipo_doc = '$idTipoDoc'"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = -1; //flag para el resultado de la query

            if ($rs) {//si rs es true $exito ==1
                $exito = 1;
            }

            //liberando recursos
            mysqli_close($cnx);


            return $exito; //retornando el flag con el resultado de la insercion
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }
    
    
    /**
     * 
     * @param Objeto $tipoDoc Objeto con los atributos a actualizar
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function actualizarTipoDoc($tipoDoc) {
        try {

            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "UPDATE tipo_documento SET tipo_documento='" . $tipoDoc->getTipoDocumento() . "'"
                    . " WHERE id_tipo_doc = '" . $tipoDoc->getIdTipoDocumento() . "'"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = -1; //flag para el resultado de la query

            if ($rs) {//si rs es true $exito ==1
                $exito = 1;
            }

            //liberando recursos
            mysqli_close($cnx);


            return $exito; //retornando el flag con el resultado de la insercion
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
