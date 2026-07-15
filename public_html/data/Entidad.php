<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Entidad
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Entidad {

    private $idEntidad;
    private $nombreEntidad;
    private $apellidoEntidad;
    private $idCargo;
    private $idTipoEntidad;
    private $idContrato;

    function getIdEntidad() {
        return $this->idEntidad;
    }

    function getNombreEntidad() {
        return $this->nombreEntidad;
    }

    function getApellidoEntidad() {
        return $this->apellidoEntidad;
    }

    function setIdEntidad($idEntidad) {
        $this->idEntidad = $idEntidad;
    }

    function setNombreEntidad($nombreEntidad) {
        $this->nombreEntidad = $nombreEntidad;
    }

    function setApellidoEntidad($apellidoEntidad) {
        $this->apellidoEntidad = $apellidoEntidad;
    }

    function getIdCargo() {
        return $this->idCargo;
    }

    function getIdTipoEntidad() {
        return $this->idTipoEntidad;
    }

    function setIdCargo($idCargo) {
        $this->idCargo = $idCargo;
    }

    function setIdTipoEntidad($idTipoEntidad) {
        $this->idTipoEntidad = $idTipoEntidad;
    }

    function getIdContrato() {
        return $this->idContrato;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    /**
     * 
     * @param Obj $entidad Objeto con los atributos a insertar en la bd
     * @return Int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarEntidad($entidad) {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "INSERT INTO entidad(id_entidad,nombre_entidad,apellido_entidad,id_tipo_entidad,id_cargo,id_contrato) " //query
                    . "VALUES('" . $entidad->getIdEntidad() . "','" . $entidad->getNombreEntidad() . "','" . $entidad->getApellidoEntidad() . "','" . $entidad->getIdTipoEntidad() . "',"
                    . "'" . $entidad->getIdCargo() . "','" . $entidad->getIdContrato() . "')";

            $exito = mysqli_query($cnx, $sql) == TRUE ? $entidad->getIdEntidad() : -1; //ejecutando la query y asignando valor dependiendo del resultado de la operacion


            return $exito; //retornando resultado (retorno el id de la entidad para poder agregarlo al select en el ingreso de documento
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @return array Retorna un array con las entidades
     */
    public function getEntidades() {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "SELECT * FROM entidad";
            $rs = mysqli_query($cnx, $sql);
            $entidades = array();
            while ($r = mysqli_fetch_array($rs)) {
                $entidad = new Entidad(); //instanceando y seteando el obj
                $entidad->setIdEntidad($r['id_entidad']);
                $entidad->setNombreEntidad($r['nombre_entidad']);
                $entidad->setApellidoEntidad($r['apellido_entidad']);
                $entidad->setIdTipoEntidad($r['id_tipo_entidad']);
                $entidad->setIdCargo($r['id_cargo']);
                $entidad->setIdContrato($r['id_contrato']);

                array_push($entidades, $entidad); //insertado objeto al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $entidades; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    public function getEntidadesPorContrato($idContrato) {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "SELECT * FROM entidad WHERE id_contrato = '$idContrato'";
            $rs = mysqli_query($cnx, $sql);
            $entidades = array();
            while ($r = mysqli_fetch_array($rs)) {
                $entidad = new Entidad(); //instanceando y seteando el obj
                $entidad->setIdEntidad($r['id_entidad']);
                $entidad->setNombreEntidad($r['nombre_entidad']);
                $entidad->setApellidoEntidad($r['apellido_entidad']);
                $entidad->setIdTipoEntidad($r['id_tipo_entidad']);
                $entidad->setIdCargo($r['id_cargo']);
                $entidad->setIdContrato($r['id_contrato']);

                array_push($entidades, $entidad); //insertado objeto al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $entidades; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param int $idEntidad id de la entidad a buscar
     * @return Entidad Retorna un objeto con los atributos de la entidad buscada
     */
    public function getEntidadPorId($idEntidad) {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "SELECT * FROM entidad WHERE id_entidad='$idEntidad'";
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {
                $entidad = new Entidad(); //instanceando y seteando el obj
                $entidad->setIdEntidad($r['id_entidad']);
                $entidad->setNombreEntidad($r['nombre_entidad']);
                $entidad->setApellidoEntidad($r['apellido_entidad']);
                $entidad->setIdTipoEntidad($r['id_tipo_entidad']);
                $entidad->setIdCargo($r['id_cargo']);
                $entidad->setIdContrato($r['id_contrato']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $entidad; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param int $idTipoEntidad
     * @return array Retorna un array con las entidades encontradas
     */
    public function getEntidadesPorTipo($idTipoEntidad, $idContrato) {//Destinatario o remitente
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            if ($idContrato == 0) {//SUPER ADMIN
                $sql = "SELECT * FROM entidad e JOIN cargo c USING(id_cargo) WHERE id_tipo_entidad='$idTipoEntidad' ORDER BY e.nombre_entidad ASC";
            } else {
                $sql = "SELECT * FROM entidad e JOIN cargo c USING(id_cargo) WHERE id_tipo_entidad='$idTipoEntidad' AND e.id_contrato='$idContrato' ORDER BY e.nombre_entidad ASC";
            }

            $rs = mysqli_query($cnx, $sql);
            $entidades = array();
            while ($r = mysqli_fetch_array($rs)) {
                array_push($entidades, $r); //ingresadando los resultados a un array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $entidades; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param Objeto $entidad Objeto con los atributos a actualizar
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarEntidad($entidad) {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "UPDATE entidad SET nombre_entidad = '" . $entidad->getNombreEntidad() . "',apellido_entidad='" . $entidad->getApellidoEntidad() . "',"
                    . "id_tipo_entidad='" . $entidad->getIdTipoEntidad() . "',id_cargo='" . $entidad->getIdCargo() . "' WHERE id_entidad = '" . $entidad->getIdEntidad() . "'";


            $exito = mysqli_query($cnx, $sql) == TRUE ? 1 : -1;
            //liberando recursos 
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param int $idEntidad id de la entidad a eliminar
     * @return int Retorna  1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarEntidad($idEntidad) {
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "DELETE FROM entidad WHERE id_entidad = '$idEntidad'";
            $exito = mysqli_query($cnx, $sql) == TRUE ? 1 : -1;
            //liberando recursos 
            mysqli_close($cnx);

            return $exito; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @return Retorna el id mayor de la tabla
     */
    public function getMaxIdEntidad() {//Destinatario o remitente
        try {
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd
            $sql = "SELECT MAX(id_entidad) as max FROM entidad";
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {
                $max = $r['max'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $max; //retornando resultado
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }
    public function consultarEntidadCargaMasiva($estado,$entidad) {
        try {
            $idEntidad;
            $serviceCnx = new Conexion(); //Servicio de conexion
            $cnx = $serviceCnx->conectar(); //link de la conexion a la bd

            if($estado == "nuevo"){

                $sql = "INSERT INTO entidad(nombre_entidad,apellido_entidad,id_tipo_entidad,id_cargo,id_contrato) " 
                . "VALUES('" . $entidad->getNombreEntidad() . "','" . $entidad->getApellidoEntidad() . "'," . $entidad->getIdTipoEntidad() . ","
                 . $entidad->getIdCargo() . "," . $entidad->getIdContrato() . ");";
                 mysqli_query($cnx, $sql);
                 $idEntidad = mysqli_insert_id($cnx);
                  
            }else{
                $existe = "no";
                $sql = "SELECT id_entidad FROM entidad WHERE nombre_entidad='".$entidad->getNombreEntidad()."' AND apellido_entidad = '".$entidad->getApellidoEntidad()."' AND id_tipo_entidad = ".$entidad->getIdTipoEntidad()." AND id_cargo = ".$entidad->getIdCargo()." AND id_contrato = ".$entidad->getIdContrato().";";
                $rs = mysqli_query($cnx, $sql); //resultado de la query

                while ($r = mysqli_fetch_assoc($rs)) {
                    $existe = "si";
                    $idEntidad = $r["id_entidad"];
                }
                if($existe == "no"){
                    $sql = "INSERT INTO entidad(nombre_entidad,apellido_entidad,id_tipo_entidad,id_cargo,id_contrato) " 
                    . "VALUES('" . $entidad->getNombreEntidad() . "','" . $entidad->getApellidoEntidad() . "'," . $entidad->getIdTipoEntidad() . ","
                     . $entidad->getIdCargo() . "," . $entidad->getIdContrato() . ");";
                     mysqli_query($cnx, $sql);
                     $idEntidad = mysqli_insert_id($cnx);
                }
                
            }
            
            return $idEntidad; //retornando resultado (retorno el id de la entidad para poder agregarlo al select en el ingreso de documento
        } catch (Exception $ex) {
            echo $ex->getTraceAsString(); //mensaje de la excepcion
        }
    }

}
