<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubContrato
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class SubContrato {

    private $idSubcontrato;
    private $nombreSubContrato;
    private $idContrato;

    function __construct() {
        
    }

    function getIdSubcontrato() {
        return $this->idSubcontrato;
    }

    function getNombreSubContrato() {
        return $this->nombreSubContrato;
    }

    function getIdContrato() {
        return $this->idContrato;
    }

    function setIdSubcontrato($idSubcontrato) {
        $this->idSubcontrato = $idSubcontrato;
    }

    function setNombreSubContrato($nombreSubContrato) {
        $this->nombreSubContrato = $nombreSubContrato;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    /**
     * Método que agrega un registro de subcontrato  a la base de datos
     * @param Objeto $subContrato Objeto con los atributos a ingresar a la BD
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function agregarSubContrato($subContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "INSERT INTO subcontrato(nombre_subcontrato,id_contrato) "//query
                    . "VALUES('" . $subContrato->getNombreSubContrato() . "','" . $subContrato->getIdContrato() . "')";

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //Asignando valor a la flag
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene todos los subcontratos asociados a un contrato
     * @param int $idContrato id del contrato
     * @return array Retorna un array con los resultados
     */
    public function getSubContratoPorContrato($idContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM subcontrato WHERE id_contrato='$idContrato'";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $subContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando usuarios
                $subContrato = new SubContrato();
                $subContrato->setIdSubcontrato($r['id_subcontrato']);
                $subContrato->setNombreSubContrato($r['nombre_subcontrato']);
                $subContrato->setIdContrato($r['id_contrato']);

                array_push($subContratos, $subContrato); //agregando objetos al array
            }


            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $subContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene todos los subcontratos asociados a un usuario
     * @param int $idUsuario id del usuario a consultar
     * @return array Retorna un array con los resultados
     */
    public function getSubContratoPorUsuario($idUsuario) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM subcontrato sc JOIN usuario_subcontrato us USING(id_subcontrato)"
                    . "WHERE us.id_usuario='$idUsuario';";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $subContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando usuarios
                $subContrato = new SubContrato();
                $subContrato->setIdSubcontrato($r['id_subcontrato']);
                $subContrato->setNombreSubContrato($r['nombre_subcontrato']);
                $subContrato->setIdContrato($r['id_contrato']);

                array_push($subContratos, $subContrato); //agregando objetos al array
            }


            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $subContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * Método que obtiene un subcontrato por ID
     * @param int $idSubContrato id del subcontrato
     * @return \SubContrato Retorna un objeto con los resultados
     */
    public function getSubContratoPorId($idSubContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM subcontrato WHERE id_subcontrato='$idSubContrato'";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $subContrato = null;

            if (mysqli_num_rows($rs)) :
                while ($r = mysqli_fetch_array($rs)) {
                    //instanceando y seteando usuarios
                    $subContrato = new SubContrato();
                    $subContrato->setIdSubcontrato($r['id_subcontrato']);
                    $subContrato->setNombreSubContrato($r['nombre_subcontrato']);
                    $subContrato->setIdContrato($r['id_contrato']);
                }
            endif;

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $subContrato; //retornando resultado
            
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @return array Retorna un array con todos los subcontratos
     */
    public function getSubContratos() {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM subcontrato";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $subContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando usuarios
                $subContrato = new SubContrato();
                $subContrato->setIdSubcontrato($r['id_subcontrato']);
                $subContrato->setNombreSubContrato($r['nombre_subcontrato']);
                $subContrato->setIdContrato($r['id_contrato']);

                array_push($subContratos, $subContrato); //agregando objetos al array
            }
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $subContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idSubcontrato id del subcontrato a actualizar
     * @param int $idContrato id del contrato
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarSubcontrato($subcontrato) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sqlMod = "UPDATE subcontrato SET id_contrato='" . $subcontrato->getIdContrato() . "' WHERE id_subcontrato ='" . $subcontrato->getIdSubcontrato() . "'";

            $exito = mysqli_query($cnx, $sqlMod) == true ? 1 : -1; //Asignando valor a la flag
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     * 
     * @param int $idSubcontrato id del subcontrato a eliminar
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarSubcontrato($idSubContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "DELETE FROM subcontrato WHERE id_subcontrato ='$idSubContrato'"; //query

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //Asignando valor a la flag
            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    public function getSubContratosDocumentos($mes,$ano) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT COUNT(d.id_documento) AS cantidad,s.nombre_subcontrato AS nombre_subcontrato,c.nombre_contrato AS nombre_contrato FROM subcontrato s LEFT JOIN contrato c ON s.id_contrato = c.id_contrato LEFT JOIN documento d ON s.id_subcontrato =d.id_subcontrato AND MONTH(d.fecha_documento) = $mes AND YEAR(d.fecha_documento) = $ano WHERE s.id_subcontrato != 1 GROUP BY s.id_subcontrato;";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $subContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando usuarios
                $datos = array("cantidad"=>$r["cantidad"],"nombre_subcontrato"=>$r["nombre_subcontrato"],"nombre_contrato"=>$r["nombre_contrato"]);

                array_push($subContratos, $datos); //agregando objetos al array
            }
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $subContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }
    public function obtenerGraficoDocumentos() {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT COUNT(d.id_documento) AS cantidad,s.nombre_subcontrato AS nombre_subcontrato,c.nombre_contrato AS nombre_contrato FROM subcontrato s LEFT JOIN contrato c ON s.id_contrato = c.id_contrato LEFT JOIN documento d ON s.id_subcontrato =d.id_subcontrato 
                AND d.fecha_documento >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)  
                WHERE s.id_subcontrato != 1
                GROUP BY s.id_subcontrato;";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $subContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando usuarios
                $datos = array("cantidad"=>$r["cantidad"],"nombre_subcontrato"=>$r["nombre_subcontrato"],"nombre_contrato"=>$r["nombre_contrato"]);
                array_push($subContratos, $datos); //agregando objetos al array
            }
            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $subContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    public function asignaUsuarioSubcontrato($usuario, $listasubcontratos){
        $serviceCnx = new Conexion(); //servicio de la conexion
        $cnx = $serviceCnx->conectar(); //link de la conexion
        $frst = "delete from usuario_subcontrato where id_usuario = ".$usuario->getIdUsuario();
        $rd = mysqli_query($cnx, $frst);
        foreach($listasubcontratos as $sc){
            $qry = "insert into usuario_subcontrato set id_usuario = ".$usuario->getIdUsuario().", id_subcontrato = $sc";
            error_log($qry);
            $rs = mysqli_query($cnx, $qry);
        }
        //liberando recursos

        mysqli_close($cnx);
    }

}
