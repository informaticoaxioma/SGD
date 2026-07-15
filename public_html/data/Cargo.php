<?php

/**
 * Description of Cargo
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class Cargo {

    private $idCargo;
    private $cargo;
    private $idContrato;

    public function __construct() {
        
    }

    function getIdCargo() {
        return $this->idCargo;
    }

    function getCargo() {
        return $this->cargo;
    }

    function getIdContrato() {
        return $this->idContrato;
    }

    function setIdCargo($idCargo) {
        $this->idCargo = $idCargo;
    }

    function setCargo($cargo) {
        $this->cargo = $cargo;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    /**
     * 
     * @return array Retorna un array con todos los cargos almacenados en la base de datos
     */
    public function getCargos() {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "SELECT * FROM cargo ORDER BY cargo"; //query para obtener los datos
            $cargos = array(); //array para almacenar los objetos
            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo los resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto(s)
                $cargo = new Cargo();
                $cargo->setIdCargo($r['id_cargo']);
                $cargo->setCargo($r['cargo']);
                $cargo->setIdContrato($r['id_contrato']);

                array_push($cargos, $cargo); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $cargos; //retornando el array con objetos
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    public function getCargosPorContrato($idContrato) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "SELECT * FROM cargo WHERE id_contrato='$idContrato' ORDER BY cargo"; //query para obtener los datos
            $cargos = array(); //array para almacenar los objetos
            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo los resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto(s)
                $cargo = new Cargo();
                $cargo->setIdCargo($r['id_cargo']);
                $cargo->setCargo($r['cargo']);
                $cargo->setIdContrato($r['id_contrato']);

                array_push($cargos, $cargo); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $cargos; //retornando el array con objetos
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param int $idCargo id del cago a buscar u obtener
     * @return Objeto Retorna un objeto Cargo obtenido mediante la consulta a la base de datos
     */
    public function getCargoPorId($idCargo) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "SELECT * FROM cargo WHERE id_cargo='$idCargo'"; //query para obtener los datos

            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo los resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto(s)
                $cargo = new Cargo();
                $cargo->setIdCargo($r['id_cargo']);
                $cargo->setCargo($r['cargo']);
                $cargo->setIdContrato($r['id_contrato']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $cargo; //retornando  objeto
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    
    /**
     * Método que obtiene el id mayor de la tabla cargo
     * @return int id solicitado
     */
    public function getMaxCargoId() {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "SELECT MAX(id_cargo) AS max FROM cargo"; //query para obtener los datos

            $rs = mysqli_query($cnx, $sql); //resultado de la query
            $max = -1;
            //obteniendo los resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto(s)
                $max=$r["max"];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $max; //retornando  objeto
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param String $nombreCargo Nombre del cargo a ingresar
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarCargos($idCargo,$nombreCargo, $idContrato) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "INSERT INTO cargo(id_cargo,cargo,id_contrato) VALUES('$idCargo','$nombreCargo','$idContrato')"; //query para insertar datos

            $rs = mysqli_query($cnx, $sql); //resultado de la query

            $exito = -1;

            if ($rs) {
                $exito = 1;
            }

            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param int $idCargo id del cargo a eliminar
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarCargo($idCargo) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "DELETE FROM cargo WHERE id_cargo='$idCargo'"; //query para eliminar datos

            $rs = mysqli_query($cnx, $sql); //resultado de la query

            $exito = -1;

            if ($rs) {
                $exito = 1;
            }

            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    /**
     * 
     * @param Objeto $cargo Objeto Con los datos a actualizar
     * @return int Retorna 1 se la operacion es correcta, -1 si no lo es
     */
    public function actualizarCargo($cargo) {

        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "UPDATE cargo SET cargo='" . $cargo->getCargo() . "' "
                    . " WHERE id_cargo='" . $cargo->getIdCargo() . "'"; //query para actualizar datos

            $rs = mysqli_query($cnx, $sql); //resultado de la query

            $exito = -1;

            if ($rs) {
                $exito = 1;
            }

            //liberando recursos
            mysqli_close($cnx);

            return $exito; //retornando flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

    public function getCargosUsuario($idContrato) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "SELECT * FROM cargo WHERE id_contrato = $idContrato ORDER BY cargo "; //query para obtener los datos
            $cargos = array(); //array para almacenar los objetos
            $rs = mysqli_query($cnx, $sql); //resultado de la query
            //obteniendo los resultados
            while ($r = mysqli_fetch_array($rs)) {
                //instanceando y seteando objeto(s)
                $cargo = new Cargo();
                $cargo->setIdCargo($r['id_cargo']);
                $cargo->setCargo($r['cargo']);
                $cargo->setIdContrato($r['id_contrato']);

                array_push($cargos, $cargo); //agregando objetos al array
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $cargos; //retornando el array con objetos
        } catch (Exception $ex) {
            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }
    public function ConsultarCargoCargaMasiva($nombreCargo, $idContrato) {
        try {

            $serviceCnx = new Conexion(); //servicio para la conexion
            $cnx = $serviceCnx->conectar(); //obteniendo el link
            $sql = "SELECT id_cargo FROM cargo WHERE cargo = '$nombreCargo' AND id_contrato = $idContrato;";
            $rs = mysqli_query($cnx, $sql); 
            $estado = "encontrado";
            $idCargo = "error";

            while ($r = mysqli_fetch_array($rs)) {
                $idCargo=$r["id_cargo"];
            }
            if($idCargo == "error"){
                $sql = "INSERT INTO cargo(cargo,id_contrato) VALUES('$nombreCargo',$idContrato);"; //query para insertar datos
                $rs = mysqli_query($cnx, $sql);
                $idCargo = mysqli_insert_id($cnx);
                $estado = "nuevo";
            }
            
            $datos = array("estado"=>$estado,"idCargo"=>$idCargo);
            //liberando recursos
            mysqli_close($cnx);
            
            return $datos; //retornando flag
        } catch (Exception $ex) {

            echo $ex->getMessage(); //mensaje de la excepcion
        }
    }

}
