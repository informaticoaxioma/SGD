<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tipoEntidad
 *
 * @author vfernandez
 */
class tipoEntidad {

    private $idTipoEntidad;
    private $tipoEntidad;

    public function __construct() {
        
    }

    function getIdTipoEntidad() {
        return $this->idTipoEntidad;
    }

    function getTipoEntidad() {
        return $this->tipoEntidad;
    }

    function setIdTipoEntidad($idTipoEntidad) {
        $this->idTipoEntidad = $idTipoEntidad;
    }

    function setTipoEntidad($tipoEntidad) {
        $this->tipoEntidad = $tipoEntidad;
    }

    
    /**
     * 
     * @return array Retorna un array con los tipos de entidades
     */
    public function getTiposEntidades() {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "SELECT * FROM tipo_entidad"; //query
            $rs = mysqli_query($cnx, $sql);
            $tipos = array();
            
            while ($r = mysqli_fetch_array($rs)) {
                $tipoEntidad = new tipoEntidad(); //instanceando y seteando el obj
                $tipoEntidad->setIdTipoEntidad($r['id_tipo_entidad']);
                $tipoEntidad->setTipoEntidad($r['tipo_entidad']);
                
                array_push($tipos, $tipoEntidad);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $tipos; //retornando resultado
            
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

}
