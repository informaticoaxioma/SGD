<?php

/**
 * Description of Documento
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Documento {

    private $idDocumento;
    private $nombreDocumento;
    private $documento;
    private $mimeDocumento;
    private $tamanoDocumento;
    private $fechaDocumento;
    private $fechaDocumento2;
    private $fechaRecepcion;
    private $fechaPlazo;
    private $idEstadoDoc;
    private $idFlujo;
    private $idDocRelacionado;
    private $idSubContrato;
    private $idContrato;

    public function __construct() {

    }

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function getNombreDocumento() {
        return $this->nombreDocumento;
    }

    function getDocumento() {
        return $this->documento;
    }

    function getMimeDocumento() {
        return $this->mimeDocumento;
    }

    function getFechaDocumento() {
        return $this->fechaDocumento;
    }

    function getFechaDocumento2() {
        return $this->fechaDocumento2;
    }

    function getFechaRecepcion() {
        return $this->fechaRecepcion;
    }

    function getFechaPlazo() {
        return $this->fechaPlazo;
    }

    function getIdEstadoDoc() {
        return $this->idEstadoDoc;
    }

    function getIdFlujo() {
        return $this->idFlujo;
    }

    function getIdDocRelacionado() {
        return $this->idDocRelacionado;
    }

    function getIdContrato(){
        return $this->idContrato;
    }

    function setIdContrato($idContrato){
        $this->idContrato = $idContrato;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

    function setNombreDocumento($nombreDocumento) {
        $this->nombreDocumento = $nombreDocumento;
    }

    function setDocumento($documento) {
        $this->documento = $documento;
    }

    function setMimeDocumento($mimeDocumento) {
        $this->mimeDocumento = $mimeDocumento;
    }

    function setFechaDocumento($fechaDocumento) {
        $this->fechaDocumento = $fechaDocumento;
    }

    function setFechaDocumento2($fechaDocumento) {
        $this->fechaDocumento2 = $fechaDocumento;
    }

    function setFechaRecepcion($fechaRecepcion) {
        $this->fechaRecepcion = $fechaRecepcion;
    }

    function setFechaPlazo($fechaPlazo) {
        $this->fechaPlazo = $fechaPlazo;
    }

    function setIdEstadoDoc($idEstadoDoc) {
        $this->idEstadoDoc = $idEstadoDoc;
    }

    function setIdFlujo($idFlujo) {
        $this->idFlujo = $idFlujo;
    }

    function setIdDocRelacionado($idDocRelacionado) {
        $this->idDocRelacionado = $idDocRelacionado;
    }

    function getIdSubContrato() {
        return $this->idSubContrato;
    }

    function setIdSubContrato($idSubContrato) {
        $this->idSubContrato = $idSubContrato;
    }

    function getTamanoDocumento() {
        return $this->tamanoDocumento;
    }

    function setTamanoDocumento($tamanoDocumento) {
        $this->tamanoDocumento = $tamanoDocumento;
    }

    /**
     * Metodo que obtiene todos los documentos presentes en la base de datos
     * @return array Retorna un array con todos los documentos almacenados en la base de datos
     */
    public function getDocumentos() {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM documento d JOIN detalle_documento dd USING(id_documento) ORDER BY d.fecha_documento DESC"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {



                array_push($documentos, $r); //agregando objetos al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que retorna, de manera paginada, los registros presentes en la base de datos
     * @param int $inicio numero de inicio de los registros
     * @param int $registrosPorHoja Cantidad de registros por hoja
     * @return array Retorna un Array con lso resultados obtenidos por medio de la consulta
     */
    public function getDocumentosPaginados($inicio, $registrosPorHoja) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT id_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                    . "dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable"
                    . " FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . "ORDER BY dd.num_providencia  LIMIT $inicio,$registrosPorHoja"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene todos los documentos con un flujo determinado, los entrega de forma paginada
     * @param int $idFlujo id del flujo
     * @param int $inicio numero de la fila de inicio de la consulta
     * @param int $registrosPorHoja cantidad de registros a mostrar por hoja
     * @return array Retorna un array con los resultados
     */
    public function getDocumentosPaginadosPorFlujo($idFlujo, $inicio, $registrosPorHoja) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT id_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                    . "dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable"
                    . " FROM documento d JOIN detalle_documento dd USING(id_documento)  WHERE d.id_flujo='$idFlujo' "
                    . "ORDER BY dd.num_providencia  LIMIT $inicio,$registrosPorHoja"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que cuenta el total de documentos segun flujo
     * @param int $idFlujo id del flujo
     * @return int retorna la cantidad obtenida
     */
    public function contarTotalDocumentosPorFlujo($idFlujo) {//CONTADOR PARA EL ADMIN
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT COUNT(id_documento) AS total FROM documento"
                    . " WHERE id_flujo='$idFlujo'"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $total = 0;

            while ($r = mysqli_fetch_array($rs)) {

                $total = $r['total'];
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $total; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que obtiene el numero total de documentos almacenados en la base de datos
     * @return int Retorna el total de documentos almacenados en la base de datos
     */
    public function contarTotalDocumentos() {//CONTADOR PARA EL ADMIN
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT COUNT(id_documento) AS total FROM documento"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $total = 0;

            while ($r = mysqli_fetch_array($rs)) {

                $total = $r['total'];
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $total; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que obtiene el documento y su detalle segun su id
     * @param int $idDocumento id del ducumento a buscar
     * @return Objeto Retorna un objeto documento
     */
    public function getDocumentoPorID($idDocumento) {
        try {
            $serviceCnx = new Conexion(); //Servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT * FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . "WHERE id_documento='$idDocumento'"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documento = array();

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documento, $r);
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documento; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que retorna el total de documentos por contrato de manera paginada
     * @param int $idContrato id del contrato
     * @return array Retorna un arreglo con los documentos obtenidos mediante la consulta
     */
    public function getDocumentosPorContratoPaginados($idContrato, $inicio, $registrosPorHoja) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato) "
                    . "WHERE sc.id_contrato='$idContrato' LIMIT $inicio,$registrosPorHoja"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {


                array_push($documentos, $r); //agregando objetos al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene los documentos asociados por usuario, y los retorna de manera paginada
     * @param int $idUsuario id del usuario
     * @param int $inicio inicio de los registros
     * @param int $registrosPorHoja Cantidad de registros por hoja
     * @return array Retorna un arreglo con los documentos obtenidos de acuerdo a la consulta realizada
     */
    public function getDocumentosPorUsuarioPaginados($idUsuario, $inicio, $registrosPorHoja) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM documento d  JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE  dd.id_responsable='$idUsuario' ORDER BY d.fecha_documento DESC LIMIT $inicio,$registrosPorHoja"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando resultados al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene el numero total de documentos asociados a un usuario
     * @param int $idUsuario id del usuario
     * @return int Retorna el total de documentos asociados a un usuario
     */
    public function contarDocumentosPorUsuario($idUsuario) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT COUNT(id_documento) AS total FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE dd.id_responsable='$idUsuario'"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $total = 0;

            while ($r = mysqli_fetch_array($rs)) {

                $total = $r['total'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $total; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene el numero total de documentos por contrato
     * @param int $idContrato id del contrato
     * @return int Retorna numero total de documentos por contrato
     */
    public function contarDocumentosPorContrato($idContrato) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT COUNT(id_documento) AS total FROM documento d JOIN subcontrato sc USING(id_subcontrato) "
                    . "WHERE sc.id_contrato='$idContrato'"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $total = 0;

            while ($r = mysqli_fetch_array($rs)) {

                $total = $r['total'];
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $total; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que obtiene el documento y su detalle segun el contrato
     * @param int $idContrato id de contrato
     * @param int $numeroDoc numero del documento
     * @param int $idNombreNumero idNombreDocumento
     * @param varchar $materia Materia del documento
     * @param int $idTipoUsuario id del tipo de usuario
     * @return array Retorna un array con los resultados de la tabla documento y detalle
     */
    public function getDocumentosDetallePorContrato($idContrato, $numeroDoc, $idNombreNumero, $materia, $idTipoUsuario) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM documento d JOIN detalle_documento dd USING(id_documento)"; //QUERY SUPER ADMIN

            if ($idTipoUsuario != 1) {//FILTRO PARA OTROS USUARIOS
                $sql.= " JOIN subcontrato sc USING(id_subcontrato) "
                        . "WHERE sc.id_contrato='$idContrato'";
            } else {
                $sql.= "WHERE id_subcontrato!=''";
            }

            if ($numeroDoc != "") {

                switch ($idNombreNumero) {
                    case 1://NUMERO DOCUMENTO
                        $sql .= " AND dd.num_documento LIKE '%$numeroDoc%'"; //query

                        break;

                    case 2://NUMERO PROVIDENCIA
                        $sql .= " AND dd.num_providencia LIKE '%$numeroDoc%'"; //query

                        break;

                    case 3://NUMERO PROCESO
                        $sql .= " AND dd.num_proceso LIKE '%$numeroDoc%'"; //query
                        break;
                }
            }


            if ($materia != "") {

                $sql.= " AND dd.materia ='$materia'"; //query
            }

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando objetos al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene documentos por subcontrato, flujo y usuario
     * @param int $idSubContrato id del subcontrato
     * @param int $idUsuario id del usuario
     * @param int $idFlujo id del flujo
     * @return array Retorna un array con los resultados
     */
    public function getDocumentosPorSubContratoFlujoYUsuario($idSubContrato, $idUsuario, $idFlujo) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE id_subcontrato='$idSubContrato' AND dd.id_responsable='$idUsuario'"
                    . " AND d.id_flujo='$idFlujo' AND d.id_estado_doc=1 ORDER BY d.fecha_documento DESC"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando resultados al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene los documentos asociados a un subcontrato y un usuario, entregandolos de manera paginada
     * @param int $idSubContrato id del subcontrato
     * @param int $idUsuario id del usuario
     * @param int $idFlujo id del tipo de flujo (entrada o salida)
     * @param int $inicio inicio de los registros
     * @param int $limite limite de los registros a mostrar
     * @return array Retorna un array con los resultados
     */
    public function getDocumentosPorSubContratoFlujoYUsuarioPaginados($idSubContrato, $idUsuario, $idFlujo, $inicio, $limite) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE id_subcontrato='$idSubContrato' AND dd.id_responsable='$idUsuario'"
                    . " AND d.id_flujo='$idFlujo' AND d.id_estado_doc=1 ORDER BY d.fecha_documento DESC LIMIT $inicio,$limite"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando resultados al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene los documentos asociados a un subcontrato y un usuario, entregandolos de manera paginada
     * @param int $idSubContrato id del subcontrato
     * @param int $idUsuario id del usuario
     * @param int $idFlujo id del tipo de flujo (entrada o salida)
     * @param int $inicio inicio de los registros
     * @param int $limite limite de los registros a mostrar
     * @return array Retorna un array con los resultados
     */
    public function getDocumentosPorSubContratoYFlujoPaginados($idSubContrato, $idFlujo, $inicio, $limite) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,id_subcontrato,d.fecha_plazo,"
                    . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                    . "dd.id_remitente,dd.id_destinatario FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE id_subcontrato='$idSubContrato' "
                    . " AND d.id_flujo='$idFlujo' AND d.id_estado_doc=1 "
                    . " ORDER BY d.fecha_documento DESC LIMIT $inicio,$limite"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documentos = array(); //array para guardar los documentos

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r); //agregando resultados al array
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene el total de documentos por subcontrato, flujo y usuario
     * @param int $idSubContrato id del subcontrato
     * @param int $idUsuario id del usuario
     * @param int $idFlujo id del flujo
     * @return int Retornal el total de documentos
     */
    public function contarDocumentosPorSubContratoFlujoYUsuario($idSubContrato, $idUsuario, $idFlujo) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT COUNT(*) AS total FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE id_subcontrato='$idSubContrato' AND dd.id_responsable='$idUsuario'"
                    . " AND d.id_flujo='$idFlujo' AND d.id_estado_doc=1 ORDER BY d.fecha_documento DESC"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $total = 0;

            while ($r = mysqli_fetch_array($rs)) {

                $total = $r['total'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $total; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que cuenta todos los documentos activos de entrada o salida por subcontrato,
     * @param int $idSubContrato id de subcontrato
     * @param int $idFlujo id del flujo (Entrada o salida)
     * @return int Retorna la cantidad de documentos obtenidos
     */
    public function contarDocumentosPorSubContratoYFlujo($idSubContrato, $idFlujo) {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "SELECT COUNT(*) AS total FROM documento d JOIN detalle_documento dd USING(id_documento) "
                    . " WHERE id_subcontrato='$idSubContrato'"
                    . " AND d.id_flujo='$idFlujo' AND d.id_estado_doc=1 ORDER BY d.fecha_documento DESC"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $total = 0;

            while ($r = mysqli_fetch_array($rs)) {

                $total = $r['total'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $total; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que obtiene el id maximo de la tabla documento
     * @return int Retorna el maximo valor de las id de la tabla documento
     */
    public function getMaxIdDocumento() {

        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT MAX(id_documento) as max FROM documento"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $max = "";

            while ($r = mysqli_fetch_array($rs)) {

                $max = $r['max'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $max; //retornando la id mayor de la tabla doc
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que convierte un archivo en binario y lo inserta en la Base de datos
     * @param Objeto $doc Objeto de tipo Documento
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarDocumento($doc) {
        try {

            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "INSERT INTO documento(id_documento,nombre_documento,documento,mime_documento,tamano_documento,fecha_documento,fecha_recepcion,fecha_plazo,id_estado_doc,id_flujo,id_subcontrato) "
                    . "VALUES('" . $doc->getIdDocumento() . "','" . $doc->getNombreDocumento() . "','" . $doc->getDocumento() . "','" . $doc->getMimeDocumento() . "','" . $doc->getTamanoDocumento() . "','" . $doc->getFechaDocumento() . "',"
                    . "'" . $doc->getFechaRecepcion() . "','" . $doc->getFechaPlazo() . "'," . $doc->getIdEstadoDoc() . "," . $doc->getIdFlujo() . "," . $doc->getIdSubContrato() . ")"; //query
	//error_log($sql);

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = $rs == true ? 1 : -1; //asignando bandera para indicar resultado de la operacion
            //liberando recursos

            mysqli_close($cnx);


            return $exito; //retornando la flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que da formato europeo a la fecha (ingresar a la BD)
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
     * Metodo que convierte la fecha proveniente de la BD al formato de lectura nuestro (DD-MM-AA)
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

    /**
     * Metodo que obtiene la fecha sin hora con el formato AA/MM/DD
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
     * Método que obtiene la fecha de la base de datos
     * @return Date Retorna la fecha con hora de la base de datos
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
     * Método que obtiene la fecha actual de la base de datos
     * @return date Retorna la fecha actual de la base de datos
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
     * Metodo para actualizar el documento
     * @param Objeto $documento Objeto con los atributos a actualizar
     * @param int $flag Flag para determinar que query se utilizara
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarDocumento($documento, $flag) {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "";

            switch ($flag) {//flag para determinar la query a utilizar
                case 1://cerrar seguimiento

                    $sql = "UPDATE documento SET id_estado_doc ='" . $documento->getIdEstadoDoc() . "' "
                            . "WHERE id_documento='" . $documento->getIdDocumento() . "'";
                    break;

                case 2://actualizar documento entrada

                    $sql = "UPDATE documento SET id_subcontrato='" . $documento->getIdSubContrato() . "',fecha_documento='" . $documento->getFechaDocumento() . "',"
                            . "fecha_recepcion='" . $documento->getFechaRecepcion() . "',fecha_plazo='" . $documento->getFechaPlazo() . "' "
                            . "WHERE id_documento='" . $documento->getIdDocumento() . "'";
                    break;

                case 3://actualizar documento Salida
                    $sql = "UPDATE documento SET id_subcontrato='" . $documento->getIdSubContrato() . "',fecha_documento='" . $documento->getFechaDocumento() . "',"
                            . "fecha_recepcion='" . $documento->getFechaRecepcion() . "',fecha_plazo='" . $documento->getFechaPlazo() . "',id_estado_doc='" . $documento->getIdEstadoDoc() . "'"
                            . " WHERE id_documento='" . $documento->getIdDocumento() . "'";

                    break;

                case 4:
                    $sql = "UPDATE documento SET nombre_documento='" . $documento->getNombreDocumento() . "',documento='" . $documento->getDocumento() . "',"
                            . "mime_documento='" . $documento->getMimeDocumento() . "',tamano_documento='" . $documento->getTamanoDocumento() . "' "
                            . " WHERE id_documento='" . $documento->getIdDocumento() . "'";
                    break;
            }

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //asignando valor a la flag
//liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que retorna el total de documentos segun su estado
     * @param int $idEstadoDoc ID del estado del documento
     * @param int $idSubContrato ID Del subcontrato
     * @param int $flagBusqueda Flag para la busqueda: 1 para el admin- 2 para los otros usuarios
     * @param int $idFlujo ID del flujo (Entrada o salida)
     * @param int $idResponsable id del responsable del documento
     * @param int $idContrato ID  del contrato
     * @return int Retorna el total de documentos segun los parametros para su busqueda
     */
    public function contarDocumentosPorEstado($idEstadoDoc, $idSubContrato, $flagBusqueda, $idFlujo, $idResponsable, $idContrato) {
        try {
            $serviceConexion = new Conexion(); //servicio para la conexion
            $cnx = $serviceConexion->conectar(); //link de conexion a la db

            switch ($flagBusqueda) {

                case 1://ADMINISTRADOR
                    $sql = "SELECT COUNT(id_documento) AS total FROM documento WHERE id_estado_doc='$idEstadoDoc'"
                            . " AND id_flujo='$idFlujo' ";
			//echo $sql;
                    break;

                case 2: //USUARIO
                    $sql = "SELECT COUNT(dd.id_documento) AS total FROM detalle_documento dd JOIN documento d USING(id_documento) WHERE d.id_estado_doc='$idEstadoDoc'"
                            . " AND d.id_flujo='$idFlujo' AND d.id_subcontrato='$idSubContrato' AND dd.id_responsable ='$idResponsable'";
			//echo $sql;
                    break;

//                case 3://RESIDENTE
//
//                    $sql = "SELECT COUNT(dd.id_documento) AS total FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato) "
//                            . " WHERE d.id_estado_doc='$idEstadoDoc' AND d.id_flujo='$idFlujo' AND sc.id_contrato='$idContrato'";
//                    break;
            }


            $rs = mysqli_query($cnx, $sql); //result set

            $total = 0; //variable que almacenara los resultados

            while ($r = mysqli_fetch_array($rs)) {//obteniendo los resultados
                $total = $r['total']; //asignando el resultado a la variable
            }


            return $total; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que convierte el tamaño del archivo de bytes al valor de tamaño correspondiente
     * @param int $tamanoArchivo Recibe el tamaño de un archivo en bytes y le otorga un formato de lectura de acuerdo a su tamaño
     */
    function convertirTamanoArchivo($tamanoArchivo) {
        error_log("-Metodo convertirTamanoArchivo-");
        error_log($tamanoArchivo);

        $tamanoFormateado = ""; //variable que almacenará el valor formateado

        if ($tamanoArchivo >= 1 && $tamanoArchivo < 1024) {//formato byte
            $tamanoFormateado = $tamanoArchivo . " B";
        } else {

            if ($tamanoArchivo >= 1024 && $tamanoArchivo < 1048576) {//formato para kilobyte
                $tamanoFormateado = $tamanoArchivo / 1024;
                $tamanoFormateado = number_format($tamanoFormateado, 2, ",", ".") . " KB";
            } else {

                if ($tamanoArchivo >= 1048576 && $tamanoArchivo < 1073741824) {//formato para megabyte
                    $tamanoFormateado = ($tamanoArchivo / 1024) / 1024;
                    $tamanoFormateado = number_format($tamanoFormateado, 2, ",", ".") . " MB";
                }
            }
        }

        return $tamanoFormateado; //retorna el valor formateado
        error_log("-Metodo convertirTamanoArchivo FIN-");
    }

    /**
     * Metodo que obtiene todos los documentos que no tienen archivos digitales asociados
     * @param int $idContrato id del contrato
     * @param int $idTipoUsuario id del tipo de usuario
     * @return array Retorna un array con los documentos obtenidos segun la query
     */
    public function getDocumentosSinArchivos($idContrato, $idTipoUsuario) {
        try {
            $serviceCnx = new Conexion(); //Servicio
            $cnx = $serviceCnx->conectar(); //link de conexion


            if ($idTipoUsuario == 1):
                $sql = "SELECT * FROM documento d JOIN detalle_documento dd USING(id_documento) WHERE d.documento =''";
            else:
                $sql = "SELECT * FROM detalle_documento dd  JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato)"
                        . " WHERE sc.id_contrato= '$idContrato' AND d.documento =''"; //query
            endif;


            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada
            $documento = array();

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documento, $r);
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documento; //retornando el array con los resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que elimina un documento segun su ID
     * @param int $idDocumento id del Documento a eliminar
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function eliminarDocumento($idDocumento) {
        try {

            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "DELETE FROM documento WHERE id_documento = '$idDocumento'"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = $rs == true ? 1 : -1; //asignando bandera para indicar resultado de la operacion
//liberando recursos

            mysqli_close($cnx);


            return $exito; //retornando la flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Metodo que calcula los dias de vigencia del documento
     * @param int $idDocumento id del documento
     * @param int $fechaPlazo fecha de plazo del documento
     * @return int Retorna los dias de vigencia del documento
     */
    public function calculaDiasDeVigencia($idDocumento, $fechaPlazo) {
        try {
            $serviceConexion = new Conexion(); //servicio para la conexion
            $cnx = $serviceConexion->conectar(); //link de conexion a la db
            $sql = "SELECT DATEDIFF('$fechaPlazo',now()) AS dias from documento"
                    . " WHERE id_documento='$idDocumento'";

            $rs = mysqli_query($cnx, $sql); //result set

            $dias = 0; //variable que almacenara los resultados

            while ($r = mysqli_fetch_array($rs)) {//obteniendo los resultados
                $dias = $r['dias']; //asignando el resultado a la variable
            }


            return $dias; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    //---------------------------------------------//
    //           METODOS PARA BUSCADOR            //
    //-------------------------------------------//
    /**
     * Metodo que realiza una busqueda de acuerdo a los filtros ingresados
     * @param Obj $documento con los atributos para la busqueda
     * @param Obj $detalleDoc con los atributos para la busqueda
     * @param int $idContrato id del contrato
     * @param int $inicio de la busqueda de registro, se utiliza en la sentencia LIMIT
     * @param int $resultados el total de resultados obtenidos por la query, se utiliza para la paginacion
     * @param int $idPerfil id del tipo  de usuario que realiza la consultar
     * @return array Retorna un array con los documentos obtenidos mediante la busqueda
     */
    public function buscarDocumentosPorFiltro($documento, $detalleDoc, $idContrato, $inicio, $resultados, $idPerfil) {//Los filtros utilizados esta presentes en los objetos a excepcion del id del flujo (entrada o salida)
        try {

            $serviceCnx = new Conexion();
            $cnx = $serviceCnx->conectar();

            if ($idPerfil == 1) {

                if ($documento == NULL || $detalleDoc == NULL) {

                    $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                            . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                            . "dd.id_remitente,dd.id_destinatario "
                            . " FROM documento d JOIN detalle_documento dd USING(id_documento) ";
                } else {

                    $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                            . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                            . "dd.id_remitente,dd.id_destinatario "
                            . " FROM documento d JOIN detalle_documento dd USING(id_documento) WHERE dd.num_documento !=''  ";
                }
                //QUERY INCOMPLETA QUE SE RELLENARA A MEDIDA QUE SE INGRESEN LOS DATOS DE LOS FILTROS
            } else {
                $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,id_subcontrato,d.fecha_plazo,"
                        . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                        . "dd.id_remitente,dd.id_destinatario "
                        . " FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato)"
                        . " WHERE sc.id_contrato='$idContrato' "; //QUERY INCOMPLETA QUE SE RELLENARA A MEDIDA QUE SE INGRESEN LOS DATOS DE LOS FILTROS
            }

            //COMIENZO A RELLENAR LA QUERY CON LOS DATOS DE LOS FILTROS
            $documentos = array();

            if ($detalleDoc->getNumDocumento() != "") {//NUMERO DOCUMENTO
                $sql.="AND dd.num_documento LIKE '%" . $detalleDoc->getNumDocumento() . "%' ";
            }

            if ($detalleDoc->getNumProvidencia() != "") {//NUMERO PROVIDENCIA
                $sql.="AND dd.num_providencia LIKE '%" . $detalleDoc->getNumProvidencia() . "%' ";
            }

            if ($detalleDoc->getNumProceso() != "") {//NUMERO DE PROCESO
                $sql.="AND dd.num_proceso LIKE '%" . $detalleDoc->getNumProceso() . "%' ";
            }

            if ($documento->getFechaDocumento() != "") {//FECHA DOCUMENTO
                $sql .= "AND d.fecha_documento = '" . $documento->getFechaDocumento() . "' ";
            }

            if ($documento->getFechaRecepcion() != "") {//FECHA RECEPCION
                $sql.="AND d.fecha_recepcion ='" . $documento->getFechaRecepcion() . "' ";
            }

            if ($documento->getFechaPlazo() != "") {//FECHA PLAZO
                $sql.="AND d.fecha_plazo = '" . $documento->getFechaPlazo() . "' ";
            }

            if ($detalleDoc->getIdTipoDoc() != "") {//ID DEL TIPO DE DOCUMENTO
                $sql.="AND dd.id_tipo_doc='" . $detalleDoc->getIdTipoDoc() . "' ";
            }

            if ($detalleDoc->getIdRemitente() != "") {// ID DEL REMITENTE
                $sql.="AND dd.id_remitente ='" . $detalleDoc->getIdRemitente() . "' ";
            }

            if ($detalleDoc->getIdDestinatario() != "") {// ID DEL DESTINATARIO
                $sql.="AND dd.id_destinatario = '" . $detalleDoc->getIdDestinatario() . "' ";
            }

            if ($detalleDoc->getIdResponsable() != "") {
                $sql.="AND dd.id_responsable ='" . $detalleDoc->getIdResponsable() . "' ";
            }

            if ($detalleDoc->getMateria() != "") {
                $sql.="AND dd.materia LIKE '%" . $detalleDoc->getMateria() . "%'";
            }

            if ($documento->getIdFlujo() != "") {
                $sql.="AND d.id_flujo='" . $documento->getIdFlujo() . "'";
            }

            $sql.=" LIMIT $inicio,$resultados";

            //EJECUTANDO LA QUERY
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos;
        } catch (Exception $ex) {

            echo $ex->getTraceAsString();
        }
    }

    /**
     * Metodo que cuenta el numero de registros de la buqueda por filtro, se utiliza para la paginacion de resultados
     * @param int $documento id del documento
     * @param OBJ $detalleDoc objeto con los atributos de la busqueda
     * @param int $idContrato id del contrato
     * @param int $idPerfil id del perfil del usuario que realiza la busqueda
     * @return int Retorna el total de registros obtenidos con la query
     */
    public function contarRegistrosPorBusqueda($documento, $detalleDoc, $idContrato, $idPerfil) {

        $serviceCnx = new Conexion();
        $cnx = $serviceCnx->conectar();

        if ($idPerfil == 1) {//PERFIL ADMINISTRADOR

            if ($documento == NULL || $detalleDoc == NULL) {

                $sql = "SELECT COUNT(id_documento) as total FROM detalle_documento dd JOIN documento d USING(id_documento) ";
            } else {
//echo "admin1";
                $sql = "SELECT
                            COUNT(id_documento) as total
                        FROM subcontrato s
                        LEFT JOIN contrato c ON s.id_contrato = c.id_contrato
                        LEFT JOIN documento d ON s.id_subcontrato =d.id_subcontrato
                        JOIN detalle_documento dd USING(id_documento)
                        WHERE dd.num_documento !=''  ";
            }
        } else {

            $sql = "SELECT COUNT(id_documento) as total FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato)"
                    . " WHERE sc.id_contrato='$idContrato' "; //QUERY INCOMPLETA QUE SE RELLENARA A MEDIDA QUE SE INGRESEN LOS DATOS DE LOS FILTROS
        }//COMIENZO A RELLENAR LA QUERY CON LOS DATOS DE LOS FILTROS


        if ($detalleDoc->getNumDocumento() != "") {//NUMERO DOCUMENTO
            $sql.="AND dd.num_documento='" . $detalleDoc->getNumDocumento() . "' ";
        }

        if ($detalleDoc->getNumProvidencia() != "") {//NUMERO PROVIDENCIA
            $sql.="AND dd.num_providencia='" . $detalleDoc->getNumProvidencia() . "' ";
        }

        if ($detalleDoc->getNumProceso() != "") {//NUMERO DE PROCESO
            $sql.="AND dd.num_proceso='" . $detalleDoc->getNumProceso() . "' ";
        }

        if ($documento->getFechaDocumento() != "") {//FECHA DOCUMENTO
          if($documento->getFechaDocumento2() != ""){
            $sql .= "AND d.fecha_documento BETWEEN '" . $documento->getFechaDocumento() . "' AND '" . $documento->getFechaDocumento2() . "' ";
          }else{
            $sql .= "AND d.fecha_documento = '" . $documento->getFechaDocumento() . "' ";
          }
        }

        if ($documento->getFechaRecepcion() != "") {//FECHA RECEPCION
            $sql.="AND d.fecha_recepcion ='" . $documento->getFechaRecepcion() . "' ";
        }

        if ($documento->getFechaPlazo() != "") {//FECHA PLAZO
            $sql.="AND d.fecha_plazo = '" . $documento->getFechaPlazo() . "' ";
        }

        if ($detalleDoc->getIdTipoDoc() != "") {//ID DEL TIPO DE DOCUMENTO
            $sql.="AND dd.id_tipo_doc='" . $detalleDoc->getIdTipoDoc() . "' ";
        }

        if ($detalleDoc->getIdRemitente() != "") {// ID DEL REMITENTE
            $sql.="AND dd.id_remitente ='" . $detalleDoc->getIdRemitente() . "' ";
        }

        if ($detalleDoc->getIdDestinatario() != "") {// ID DEL DESTINATARIO
            $sql.="AND dd.id_destinatario = '" . $detalleDoc->getIdDestinatario() . "' ";
        }

        if ($detalleDoc->getIdResponsable() != "") {
            $sql.="AND dd.id_responsable ='" . $detalleDoc->getIdResponsable() . "' ";
        }

        if ($documento->getIdFlujo() != "") {
            $sql.="AND d.id_flujo='" . $documento->getIdFlujo() . "'";
        }

        if ($detalleDoc->getMateria() != "") {
            $sql.="AND dd.materia='" . $detalleDoc->getMateria() . "'";
        }

        if($documento->getIdEstadoDoc() !=""){
            $sql.="AND d.id_estado_doc='" .$documento->getIdEstadoDoc() . "'";
        }

        if($documento->getIdContrato() !=""){
            $sql.="AND c.id_contrato = '" . $documento->getIdContrato() ."'";
        }

//        echo "1)  " . $sql;

        $total = 0;
        $rs = mysqli_query($cnx, $sql);

        while ($r = mysqli_fetch_array($rs)) {
            $total = $r['total'];
        }

        return $total;
    }

    public function resultadoBuscador($documento, $detalleDoc, $idContrato, $idPerfil,$idSubContrato) {//Los filtros utilizados esta presentes en los objetos a excepcion del id del flujo (entrada o salida)
        try {

            $serviceCnx = new Conexion();
            $cnx = $serviceCnx->conectar();

            if ($idPerfil == 1) {


                if ($documento == NULL || $detalleDoc == NULL) {

                    $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,d.id_subcontrato,d.fecha_plazo,"
                            . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                            . "dd.id_remitente,dd.id_destinatario "
                            . " FROM documento d JOIN detalle_documento dd USING(id_documento) ";
                } else {
//echo "admin2";

                $sql = "SELECT "
                            ."id_documento, "
                            ."d.nombre_documento, "
                            ."d.fecha_documento, "
                            ."d.id_estado_doc, "
                            ."d.id_flujo, "
                            ."d.id_subcontrato, "
                            ."d.fecha_plazo, "
                            ."d.fecha_recepcion, "
                            ."dd.id_tipo_doc, "
                            ."dd.num_documento, "
                            ."dd.num_providencia, "
                            ."dd.num_proceso, "
                            ."dd.materia, "
                            ."dd.id_responsable, "
                            ."dd.id_remitente, "
                            ."dd.id_destinatario "
                        ."FROM subcontrato s "
                        ."LEFT JOIN contrato c ON s.id_contrato = c.id_contrato "
                        ."LEFT JOIN documento d ON s.id_subcontrato =d.id_subcontrato "
                        ."JOIN detalle_documento dd USING(id_documento) "
                        ." WHERE dd.num_documento !=''  ";
                }
                //QUERY INCOMPLETA QUE SE RELLENARA A MEDIDA QUE SE INGRESEN LOS DATOS DE LOS FILTROS
            } else {
                $sql = "SELECT id_documento,d.nombre_documento,d.fecha_documento,d.id_estado_doc,d.id_flujo,id_subcontrato,d.fecha_plazo,"
                        . "d.fecha_recepcion,dd.id_tipo_doc,dd.num_documento,dd.num_providencia,dd.num_proceso,dd.materia,dd.id_responsable,"
                        . "dd.id_remitente,dd.id_destinatario "
                        . " FROM detalle_documento dd JOIN documento d USING(id_documento) JOIN subcontrato sc USING(id_subcontrato)"
                        . " WHERE sc.id_contrato='$idContrato' "; //QUERY INCOMPLETA QUE SE RELLENARA A MEDIDA QUE SE INGRESEN LOS DATOS DE LOS FILTROS
            }

            //COMIENZO A RELLENAR LA QUERY CON LOS DATOS DE LOS FILTROS
            $documentos = array();

            if ($detalleDoc->getNumDocumento() != "") {//NUMERO DOCUMENTO
                $sql.="AND dd.num_documento LIKE '%" . $detalleDoc->getNumDocumento() . "%' ";
            }

            if ($detalleDoc->getNumProvidencia() != "") {//NUMERO PROVIDENCIA
                $sql.="AND dd.num_providencia LIKE '%" . $detalleDoc->getNumProvidencia() . "%' ";
            }

            if ($detalleDoc->getNumProceso() != "") {//NUMERO DE PROCESO
                $sql.="AND dd.num_proceso LIKE '%" . $detalleDoc->getNumProceso() . "%' ";
            }

            if ($documento->getFechaDocumento() != "") {//FECHA DOCUMENTO
                if($documento->getFechaDocumento2() != ""){
                  $sql .= "AND d.fecha_documento BETWEEN '" . $documento->getFechaDocumento() . "' AND '" . $documento->getFechaDocumento2() . "' ";
                }else{
                  $sql .= "AND d.fecha_documento = '" . $documento->getFechaDocumento() . "' ";
                }

            }

            if ($documento->getFechaRecepcion() != "") {//FECHA RECEPCION
                $sql.="AND d.fecha_recepcion ='" . $documento->getFechaRecepcion() . "' ";
            }

            if ($documento->getFechaPlazo() != "") {//FECHA PLAZO
                $sql.="AND d.fecha_plazo = '" . $documento->getFechaPlazo() . "' ";
            }

            if ($detalleDoc->getIdTipoDoc() != "") {//ID DEL TIPO DE DOCUMENTO
                $sql.="AND dd.id_tipo_doc='" . $detalleDoc->getIdTipoDoc() . "' ";
            }

            if ($detalleDoc->getIdRemitente() != "") {// ID DEL REMITENTE
                $sql.="AND dd.id_remitente ='" . $detalleDoc->getIdRemitente() . "' ";
            }

            if ($detalleDoc->getIdDestinatario() != "") {// ID DEL DESTINATARIO
                $sql.="AND dd.id_destinatario = '" . $detalleDoc->getIdDestinatario() . "' ";
            }

            if ($detalleDoc->getIdResponsable() != "") {
                $sql.="AND dd.id_responsable ='" . $detalleDoc->getIdResponsable() . "' ";
            }

            if ($detalleDoc->getMateria() != "") {
                $sql.="AND dd.materia LIKE '%" . $detalleDoc->getMateria() . "%'";
            }

            if ($documento->getIdFlujo() != "") {
                $sql.="AND d.id_flujo='" . $documento->getIdFlujo() . "'";
            }

            if($documento->getIdEstadoDoc() !=""){
                $sql.="AND d.id_estado_doc='" .$documento->getIdEstadoDoc() . "'";
            }

            if($documento->getIdContrato() !=""){
                $sql.="AND c.id_contrato = '" . $documento->getIdContrato() ."'";
            }

            if($idSubContrato !=""){
                $sql.="AND d.id_subcontrato = '" . $idSubContrato ."'";
            }
        //    error_log("----------->".$idSubContrato);
            // echo $sql;
//echo "<br>2)  ".$sql;
            //EJECUTANDO LA QUERY
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                array_push($documentos, $r);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $documentos;
        } catch (Exception $ex) {

            echo $ex->getTraceAsString();
        }
    }

}
