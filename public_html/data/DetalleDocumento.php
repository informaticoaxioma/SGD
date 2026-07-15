<?php

/**
 * Description of DetalleDocumento
 *
 * @author vfernandez
 */
require_once 'Conexion.php';
require_once 'DocumentoDetalle.php';
require_once 'Documento.php';

class DetalleDocumento
{

    private $idDocumento;
    private $numDocumento;
    private $numProvidencia;
    private $numProceso;
    private $idRemitente;
    private $idDestinatario;
    private $materia;
    private $antecedente;
    private $incluye;
    private $comentario;
    private $idTipoDoc;
    private $idResponsable;

    public function __construct()
    {
    }

    function getIdRemitente()
    {
        return $this->idRemitente;
    }

    function getIdDestinatario()
    {
        return $this->idDestinatario;
    }

    function setIdRemitente($idRemitente)
    {
        $this->idRemitente = $idRemitente;
    }

    function setIdDestinatario($idDestinatario)
    {
        $this->idDestinatario = $idDestinatario;
    }

    function getIdDocumento()
    {
        return $this->idDocumento;
    }

    function getNumDocumento()
    {
        return $this->numDocumento;
    }

    function getNumProvidencia()
    {
        return $this->numProvidencia;
    }

    function getMateria()
    {
        return $this->materia;
    }

    function getIncluye()
    {
        return $this->incluye;
    }

    function getComentario()
    {
        return $this->comentario;
    }

    function getIdTipoDoc()
    {
        return $this->idTipoDoc;
    }

    function getIdResponsable()
    {
        return $this->idResponsable;
    }

    function setIdDocumento($idDocumento)
    {
        $this->idDocumento = $idDocumento;
    }

    function setNumDocumento($numDocumento)
    {
        $this->numDocumento = $numDocumento;
    }

    function setNumProvidencia($numProvidencia)
    {
        $this->numProvidencia = $numProvidencia;
    }

    function setMateria($materia)
    {
        $this->materia = $materia;
    }

    function setIncluye($incluye)
    {
        $this->incluye = $incluye;
    }

    function setComentario($comentario)
    {
        $this->comentario = $comentario;
    }

    function setIdTipoDoc($idTipoDoc)
    {
        $this->idTipoDoc = $idTipoDoc;
    }

    function setIdResponsable($idResponsable)
    {
        $this->idResponsable = $idResponsable;
    }

    function getNumProceso()
    {
        return $this->numProceso;
    }

    function setNumProceso($numProceso)
    {
        $this->numProceso = $numProceso;
    }

    function getAntecedente()
    {
        return $this->antecedente;
    }

    function setAntecedente($antecedente)
    {
        $this->antecedente = $antecedente;
    }

    /**
     * 
     * @param int $idDocumento id de documento
     * @return Objeto Retorna un objeto del tipo DetalleDocumento con los resultados de la query
     */
    public function getDetallePorIdDocumento($idDocumento)
    {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "SELECT * FROM detalle_documento WHERE id_documento='$idDocumento'"; //query
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            while ($r = mysqli_fetch_array($rs)) {

                $detalle = new DetalleDocumento();
                $detalle->setIdDocumento($r['id_documento']);
                $detalle->setNumDocumento($r['num_documento']);
                $detalle->setNumProvidencia($r['num_providencia']);
                $detalle->setNumProceso($r['num_proceso']);
                $detalle->setIdRemitente($r['id_remitente']);
                $detalle->setIdDestinatario($r['id_destinatario']);
                $detalle->setMateria($r['materia']);
                $detalle->setAntecedente($r['antecedente']);
                $detalle->setIncluye($r['incluye']);
                $detalle->setComentario($r['comentario']);
                $detalle->setIdTipoDoc($r['id_tipo_doc']);
                $detalle->setIdResponsable($r['id_responsable']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);


            return $detalle; //retornando Objeto con el resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Objeto $detalleDoc Objeto con los atributos a ingresar a la tabla
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarDetalleDocumento($detalleDoc)
    {
        try {
            $serviceCnx = new Conexion(); // Clase servicio
            $cnx = $serviceCnx->conectar(); // link de conexion

            // Prepare your SQL query using prepared statements
            $sql = "INSERT INTO detalle_documento (id_documento, num_documento, num_providencia, num_proceso, id_remitente, id_destinatario, materia, antecedente, incluye, comentario, id_tipo_doc, id_responsable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($cnx, $sql);

            // Bind parameters to the prepared statement as strings ('s') or integers ('i') accordingly
            mysqli_stmt_bind_param(
                $stmt,
                'issssissssii',
                $detalleDoc->getIdDocumento(),
                $detalleDoc->getNumDocumento(),
                $detalleDoc->getNumProvidencia(),
                $detalleDoc->getNumProceso(),
                $detalleDoc->getIdRemitente(),
                $detalleDoc->getIdDestinatario(),
                $detalleDoc->getMateria(),
                $detalleDoc->getAntecedente(),
                $detalleDoc->getIncluye(),
                $detalleDoc->getComentario(),
                $detalleDoc->getIdTipoDoc(),
                $detalleDoc->getIdResponsable()
            );                        

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt); // Close the statement
                mysqli_close($cnx); // Close the connection
                return 1; // Return 1 on success
            } else {
                mysqli_stmt_close($stmt); // Close the statement
                mysqli_close($cnx); // Close the connection
                return 0; // Return 0 on failure
            }
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            return 0; // Return 0 on exception
        }
    }


    public function ingresarCoresponsable($idResponsable = '')
    {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion
            $sql = "INSERT INTO detalle_documento(id_documento,num_documento,num_providencia,num_proceso,id_remitente,id_destinatario,materia,antecedente,incluye,comentario,id_tipo_doc,id_responsable) "
                . "VALUES('" . $detalleDoc->getIdDocumento() . "','" . $detalleDoc->getNumDocumento() . "','" . $detalleDoc->getNumProvidencia() . "','" . $detalleDoc->getNumProceso() . "','" . $detalleDoc->getIdRemitente() . "',"
                . "'" . $detalleDoc->getIdDestinatario() . "','" . $detalleDoc->getMateria() . "','" . $detalleDoc->getAntecedente() . "','" . $detalleDoc->getIncluye() . "','" . $detalleDoc->getComentario() . "',"
                . "'" . $detalleDoc->getIdTipoDoc() . "','" . $detalleDoc->getIdResponsable() . "')"; //query
            error_log($sql);
            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = $rs == true ? 1 : -1; //asignando bandera para indicar resultado de la operacion
            //liberando recursos

            mysqli_close($cnx);


            return $exito; //retornando flag con el resultado de la operacion
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param Objeto $detalleDoc Objeto con los atributos a actualizar en la base de datos
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function actualizarDetalleDocumento($detalleDoc)
    {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "UPDATE detalle_documento SET num_documento='" . $detalleDoc->getNumDocumento() . "',num_providencia='" . $detalleDoc->getNumProvidencia() . "',"
                . "num_proceso='" . $detalleDoc->getNumProceso() . "',id_remitente='" . $detalleDoc->getIdRemitente() . "',id_destinatario='" . $detalleDoc->getIdDestinatario() . "',"
                . "materia='" . $detalleDoc->getMateria() . "',incluye='" . $detalleDoc->getIncluye() . "',comentario='" . $detalleDoc->getComentario() . "',id_responsable='" . $detalleDoc->getIdResponsable() . "',"
                . " id_tipo_doc='" . $detalleDoc->getIdTipoDoc() . "',antecedente='" . $detalleDoc->getAntecedente() . "' WHERE id_documento='" . $detalleDoc->getIdDocumento() . "'"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = $rs == true ? 1 : -1; //asignando bandera para indicar resultado de la operacion
            //liberando recursos

            mysqli_close($cnx);

            return $exito; //retornando flag con el resultado de la operacion
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idDocumento id del documento del cual depende el detalle
     * @return int Retorna 1 si la operación es correcta, -1 si no lo es
     */
    public function eliminarDetalleDocumento($idDocumento)
    {
        try {
            $serviceCnx = new Conexion(); //Clase servicio
            $cnx = $serviceCnx->conectar(); //link de conexion

            $sql = "DELETE FROM detalle_documento WHERE id_documento = '$idDocumento'"; //query

            $rs = mysqli_query($cnx, $sql); //result set de la query ejecutada

            $exito = $rs == true ? 1 : -1; //asignando bandera para indicar resultado de la operacion
            //liberando recursos

            mysqli_close($cnx);


            return $exito; //retornando flag con el resultado de la operacion
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    public function getDocumentoYDetallePorId($idDocumento)
    {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT dd.id_documento,dd.num_documento,dd.num_proceso,dd.num_providencia,dd.id_remitente,"
                . "dd.id_destinatario,dd.materia,dd.antecedente,dd.id_responsable,dd.comentario,d.fecha_recepcion,"
                . "d.id_estado_doc,d.id_flujo FROM detalle_documento dd JOIN documento d USING(id_documento) "
                . "JOIN seguimiento s USING(id_documento) "
                . "WHERE id_documento='$idDocumento'";
            //query
            $rs = mysqli_query($cnx, $sql); //result set            

            while ($r = mysqli_fetch_array($rs)) { //obteniendo resultados
                //instanceando y seteando objeto
                $detalle = new DetalleDocumento();
                $detalle->setIdDocumento($r['id_documento']);
                $detalle->setNumDocumento($r['num_documento']);
                $detalle->setNumProceso($r['num_proceso']);
                $detalle->setNumProvidencia($r['num_providencia']);
                $detalle->setIdRemitente($r['id_remitente']);
                $detalle->setIdDestinatario($r['id_destinatario']);
                $detalle->setMateria($r['materia']);
                $detalle->setAntecedente($r['antecedente']);
                $detalle->setComentario($r['comentario']);
                $detalle->setIdResponsable($r['id_responsable']);

                $documento = new Documento();
                $documento->setFechaRecepcion($r['fecha_recepcion']);
                $documento->setIdEstadoDoc($r['id_estado_doc']);
                $documento->setIdFlujo($r['id_flujo']);

                $documentoDetalle = new DocumentoDetalle($documento, $detalle);
            }

            //LIBERANDO RECURSOS
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $documentoDetalle; //retornando resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }
    public function detalleNProvidencia($idContrato)
    {
        try {
            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT t.num_providencia AS num_providencia FROM detalle_documento t INNER JOIN documento d ON t.id_documento = d.id_documento
                WHERE d.id_subcontrato = (SELECT id_subcontrato FROM subcontrato WHERE id_contrato=$idContrato) AND d.id_flujo ORDER BY t.id_documento DESC LIMIT 1;";
            //query
            error_log($sql);
            $rs = mysqli_query($cnx, $sql); //result set            
            $resultado = "";

            while ($r = mysqli_fetch_assoc($rs)) { //obteniendo resultados
                //instanceando y seteando objeto
                $resultado = $r["num_providencia"];
            }
            /*
            if(is_numeric($resultado)) {
                $resultado = intval($resultado)+1;
            }
            */
            //LIBERANDO RECURSOS
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $resultado; //retornando resultados
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }
}
