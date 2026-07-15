<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Actividad
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Actividad {

    private $idActividad;
    private $actividad;

    public function __construct() {
        
    }
    
    function getIdActividad() {
        return $this->idActividad;
    }

    function getActividad() {
        return $this->actividad;
    }

    function setIdActividad($idActividad) {
        $this->idActividad = $idActividad;
    }

    function setActividad($actividad) {
        $this->actividad = $actividad;
    }

    /**
     * 
     * @param int $idActividad id de la actividad a buscar
     * @return \Actividad Retorna un objeto con lso atributos de la entidad actividad
     */
    public function getActividadPorId($idActividad) {
        try {
            $serviceCnx = new Conexion();
            $cnx = $serviceCnx->conectar();
            $sql = "SELECT * FROM actividad WHERE id_actividad = '$idActividad'";
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $actividad = new Actividad();
                $actividad->setIdActividad($r['id_actividad']);
                $actividad->setActividad($r['actividad']);
            }

            //liberar recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $actividad;
            
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
