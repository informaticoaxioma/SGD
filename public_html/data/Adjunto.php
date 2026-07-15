<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adjunto
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Adjunto {

    private $idAdjunto;
    private $nombreAdjunto;
    private $mimeAdjunto;
    private $tamanoAdjunto;
    private $archivoAdjunto;
    private $idDocumento;

    public function __construct() {
        
    }

    function getIdAdjunto() {
        return $this->idAdjunto;
    }

    function getNombreAdjunto() {
        return $this->nombreAdjunto;
    }

    function getMimeAdjunto() {
        return $this->mimeAdjunto;
    }

    function getArchivoAdjunto() {
        return $this->archivoAdjunto;
    }

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function setIdAdjunto($idAdjunto) {
        $this->idAdjunto = $idAdjunto;
    }

    function setNombreAdjunto($nombreAdjunto) {
        $this->nombreAdjunto = $nombreAdjunto;
    }

    function setMimeAdjunto($mimeAdjunto) {
        $this->mimeAdjunto = $mimeAdjunto;
    }

    function setArchivoAdjunto($archivoAdjunto) {
        $this->archivoAdjunto = $archivoAdjunto;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

    function getTamanoAdjunto() {
        return $this->tamanoAdjunto;
    }

    function setTamanoAdjunto($tamanoAdjunto) {
        $this->tamanoAdjunto = $tamanoAdjunto;
    }

    /**
     * 
     * @param int $idDocumento id del documento
     * @return array Retorna todos los archivos adjuntos asociados a un documento
     */
    public function getAdjuntosPorDocumento($idDocumento) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "SELECT * FROM adjunto WHERE id_documento ='$idDocumento'"; //query
            $adjuntos = array(); //array que almacenara los resultados
            $rs = mysqli_query($cnx, $sql); //resultset de la query

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                //instanceando y seteando objetos
                $adjunto = new Adjunto();
                $adjunto->setIdAdjunto($r['id_adjunto']);
                $adjunto->setNombreAdjunto($r['nombre_adjunto']);
                $adjunto->setMimeAdjunto($r['mime_adjunto']);
                $adjunto->setTamanoAdjunto($r['tamano_adjunto']);
                $adjunto->setArchivoAdjunto($r['archivo_adjunto']);
                $adjunto->setIdDocumento($r['id_documento']);

                //agregando objetos al array
                array_push($adjuntos, $adjunto);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $adjuntos; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idAdjunto id del adjunto a buscar
     * @return \Adjunto Retorna un objeto adjunto con todos sus atributos
     */
    public function getAdjuntoPorId($idAdjunto) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "SELECT * FROM adjunto WHERE id_adjunto ='$idAdjunto'"; //query

            $rs = mysqli_query($cnx, $sql); //resultset de la query

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                //instanceando y seteando objetos
                $adjunto = new Adjunto();
                $adjunto->setIdAdjunto($r['id_adjunto']);
                $adjunto->setNombreAdjunto($r['nombre_adjunto']);
                $adjunto->setMimeAdjunto($r['mime_adjunto']);
                $adjunto->setTamanoAdjunto($r['tamano_adjunto']);
                $adjunto->setArchivoAdjunto($r['archivo_adjunto']);
                $adjunto->setIdDocumento($r['id_documento']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $adjunto; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Objeto $adjunto Objeto de tipo adjunto con todos los atributos a almacenar en la base de datos
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarAdjunto($adjunto) {

        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "INSERT INTO adjunto(nombre_adjunto,mime_adjunto,tamano_adjunto,archivo_adjunto,id_documento) "
                    . "VALUES('" . $adjunto->getNombreAdjunto() . "','" . $adjunto->getMimeAdjunto() . "','" . $adjunto->getTamanoAdjunto() . "','" . $adjunto->getArchivoAdjunto() . "','" . $adjunto->getIdDocumento() . "')"; //query            
            $rs = mysqli_query($cnx, $sql); //resultset de la query


            $exito = $rs == true ? 1 : -1; //seteando la flag segun el resultset
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idDocumento id del documento 
     * @return int Retorna la cantidad total de adjuntos asociados al documento
     */
    public function contarAdjuntoPorDocumento($idDocumento) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "SELECT COUNT(*) as total FROM adjunto WHERE id_documento='$idDocumento'"; //query
            $total = 0;
            $rs = mysqli_query($cnx, $sql); //resultset de la query

            while ($r = mysqli_fetch_array($rs)) {//obteniendo resultados
                $total = $r['total'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $total; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idDocumento id del documento 
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarAdjuntosPorDocumento($idDocumento) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "DELETE FROM adjunto WHERE id_documento='$idDocumento'";
            $rs = mysqli_query($cnx, $sql); //resultset de la query


            $exito = $rs == true ? 1 : -1; //seteando la flag segun el resultset
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //Retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que elimina un registro de la tabla adjunto
     * @param int $idAdjunto id del adjunto
     * @return Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarAdjuntoPorID($idAdjunto) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion   
            $cnx = $serviceCnx->conectar(); //conexion a la base de datos
            $sql = "DELETE FROM adjunto WHERE id_adjunto='$idAdjunto'";
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
