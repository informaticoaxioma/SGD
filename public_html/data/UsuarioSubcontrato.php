<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsuarioSubcontrato
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class UsuarioSubcontrato {

    private $idUsuario;
    private $idSubcontrato;

    public function __construct() {
        
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getIdSubcontrato() {
        return $this->idSubcontrato;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setIdSubcontrato($idSubcontrato) {
        $this->idSubcontrato = $idSubcontrato;
    }

    
    /**
     * 
     * @param int $idUsuario id del usuario
     * @return array Retorna una array con los resultados de la query
     */
    public function getSubcontratosPorUsuario($idUsuario) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM usuario_subcontrato WHERE id_usuario='$idUsuario'";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $usuarioSubContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando usuarios
                $usuarioSubcontrato = new UsuarioSubcontrato();
                $usuarioSubcontrato->setIdUsuario($r['id_usuario']);
                $usuarioSubcontrato->setIdUsuario($r['id_subcontrato']);

                array_push($usuarioSubContratos, $usuarioSubcontrato); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarioSubContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    
    /**
     * 
     * @param Objeto $usuarioSubContrato Objeto con los atributos a almacenar en la BD
     * @return int Retorna 1 si la operacion es correcta,-1 si no lo es
     */
    public function ingresarUsuarioSubcontrato($usuarioSubContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "INSERT INTO usuario_subcontrato(id_usuario,id_subcontrato) "
                    . "VALUES('".$usuarioSubContrato->getIdUsuario()."','".$usuarioSubContrato->getIdSubContrato()."')";

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1;//asignando valor a la flag

            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando resultado
            
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

    /**
     *
     * @param int $idUsuario id del usuario
     * @return array Retorna una array con los resultados de la query
     */
    public function getSubcontratosDelUsuario($idUsuario){
        try {
            $serviceCnx = new Conexion(); //servicio de la conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT id_subcontrato FROM usuario_subcontrato WHERE id_usuario='$idUsuario'";
            $rs = mysqli_query($cnx, $sql); //Asignando valor a la flag
            $usuarioSubContratos = array(); //array para guardar datos

            while ($r = mysqli_fetch_array($rs)) {
                $usuarioSubcontrato = $r['id_subcontrato'];
                array_push($usuarioSubContratos, $usuarioSubcontrato); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarioSubContratos; //retornando resultado
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de excepcion
        }
    }

}
