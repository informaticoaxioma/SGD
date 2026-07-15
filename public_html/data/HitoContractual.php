<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HitoContractual
 *
 * @author vfernandez
 */
require_once 'Conexion.php';

class HitoContractual {

    private $idHito;
    private $fechaEntrega;
    private $descripcionHito;
    private $origenInfo;
    private $destinoInfo;
    private $normativa;
    private $comentario;
    private $comentarioHito;
    private $idContrato;
    private $idEstadoHito;
    private $idFrecuenciaHito;
    private $idResponsableHito;
    private $idUsuarioHito;
    private $idColor;

    public function __construct() {
        
    }

    function getFechaEntrega() {
        return $this->fechaEntrega;
    }

    function setFechaEntrega($fechaEntrega) {
        $this->fechaEntrega = $fechaEntrega;
    }

    function getIdHito() {
        return $this->idHito;
    }

    function getDescripcionHito() {
        return $this->descripcionHito;
    }

    function getOrigenInfo() {
        return $this->origenInfo;
    }

    function getDestinoInfo() {
        return $this->destinoInfo;
    }

    function getNormativa() {
        return $this->normativa;
    }

    function getComentarioHito() {
        return $this->comentarioHito;
    }

    function getIdContrato() {
        return $this->idContrato;
    }

    function getIdEstadoHito() {
        return $this->idEstadoHito;
    }

    function getIdFrecuenciaHito() {
        return $this->idFrecuenciaHito;
    }

    function getIdResponsableHito() {
        return $this->idResponsableHito;
    }

    function getIdColor() {
        return $this->idColor;
    }

    function setIdHito($idHito) {
        $this->idHito = $idHito;
    }

    function setDescripcionHito($descripcionHito) {
        $this->descripcionHito = $descripcionHito;
    }

    function setOrigenInfo($origenInfo) {
        $this->origenInfo = $origenInfo;
    }

    function setDestinoInfo($destinoInfo) {
        $this->destinoInfo = $destinoInfo;
    }

    function setNormativa($normativa) {
        $this->normativa = $normativa;
    }

    function setComentarioHito($comentarioHito) {
        $this->comentarioHito = $comentarioHito;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    function setIdEstadoHito($idEstadoHito) {
        $this->idEstadoHito = $idEstadoHito;
    }

    function setIdFrecuenciaHito($idFrecuenciaHito) {
        $this->idFrecuenciaHito = $idFrecuenciaHito;
    }

    function setIdResponsableHito($idResponsableHito) {
        $this->idResponsableHito = $idResponsableHito;
    }

    function setIdColor($idColor) {
        $this->idColor = $idColor;
    }

    function getComentario() {
        return $this->comentario;
    }

    function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    function getIdUsuarioHito() {
        return $this->idUsuarioHito;
    }

    function setIdUsuarioHito($idUsuarioHito) {
        $this->idUsuarioHito = $idUsuarioHito;
    }

    /**
     * Metodo que obtiene todos los hitos almacenados en la base de datos
     * @return array Retorna un array con todos los hitos almacenados en la BD
     */
    public function getHitosContractuales() {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual  ORDER BY fecha_entrega";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene los hitos contractuales segun responsable y mes
     * @param int $idResponsable id del usuario Responsable
     * @param Date $primerDia primer dia del mes
     * @param Date $ultimoDia ultimo dia del mes
     * @return array Retorna los hitos obtenidos por la consulta a la db
     */
    public function getHitosContractualesPorResponsableYMes($idResponsable, $primerDia, $ultimoDia) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual WHERE id_responsable_hito = '$idResponsable' "
                    . "AND fecha_entrega BETWEEN '$primerDia' AND '$ultimoDia' ORDER BY fecha_entrega";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene los hitos segun responsable
     * @param int $idResponsable id del usuario responsable
     * @return array Retorna un array con los resultados
     */
    public function getHitosContractualesPorResponsable($idResponsable) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual WHERE id_responsable_hito = '$idResponsable' ";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que retorna los hitos por responsable paginados
     * @param int $idResponsable id del responsable
     * @param int $inicio numero de inicio de los registros
     * @param int $registrosPorHoja cantidad de registros desplegados por hoja
     * @return array Retorna un array con los resultados obtenidos mediante la consulta
     */
    public function getHitosContractualesPorResponsablePaginados($idResponsable, $inicio, $registrosPorHoja) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual WHERE id_responsable_hito = '$idResponsable' LIMIT $inicio,$registrosPorHoja";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene los hitos contractuales por contrato paginados
     * @param int $idContrato id del contrato a consultar
     * @param int $inicio numero de inicio de los registros
     * @param int $registrosPorHoja cantidad de registros desplegados por hoja
     * @return array Retorna un array con resultados obtenidos a traves de la consulta
     */
    public function getHitosContractualesPorContratoPaginados($idContrato, $inicio, $registrosPorHoja) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual WHERE id_contrato = '$idContrato' LIMIT $inicio,$registrosPorHoja";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene todos los hitos por contrato
     * @param int $idContrato id del contrato
     * @return array Retorna un array con los resultados
     */
    public function getHitosContractualesPorContrato($idContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual WHERE id_contrato = '$idContrato' ";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene el total de hitos ingresados por usuario, 
     * se utiliza para generar la paginacion
     * @param Objeto $usuario Objeto con lso atributos del usuario
     * @return int Retorna el total de hitos por usuario
     */
    public function contarHitosPorUsuario($usuario) {//METODO SUPERADMIN
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $max = "";

            switch ($usuario->getIdPerfil()) {

                case 1://SUPER ADMINISTRADOR
                    $sql = "SELECT COUNT(*) as max FROM hito_contractual";
                    break;

                case 3://RESIDENTE
                    $sql = "SELECT COUNT(*) as max FROM hito_contractual WHERE id_contrato ='" . $usuario->getIdContrato() . "'";
                    break;

                case 8://ENCARGADO HITOS
                    $sql = "SELECT COUNT(*) as max FROM hito_contractual WHERE id_contrato ='" . $usuario->getIdContrato() . "'";
                    break;

                default ://USUARIO
                    $sql = "SELECT COUNT(*) as max FROM hito_contractual WHERE id_responsable_hito ='" . $usuario->getIdUsuario() . "'";
                    break;
            }

            $rs = mysqli_query($cnx, $sql); //RESULT SET


            while ($r = mysqli_fetch_array($rs)) {

                $max = $r['max'];
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $max; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene todos los hitos. Se utiliza LIMIT  para poder paginar los resultados
     * @param int $inicio inicio de fila
     * @param int $resultados total de resultados
     * @return array Retorna un array con  los resultados
     */
    public function getTodosHitos($inicio, $resultados) {//METODO SUPERADMIN
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual ORDER BY fecha_entrega LIMIT $inicio,$resultados";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                //agregando el bojeto al array
                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene un hito segun su id
     * @param int $idHito id del hito a buscar
     * @return Obj Retorna un objeto con los resultados
     */
    public function getHitoPorID($idHito) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual WHERE id_hito = '$idHito' ";
            $hitos = array();
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);
            }


            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hito; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que ingresa un registro de hito en la base de datos
     * @param Objeto $hito Objeto con lsoa tributos a insertar en la base de datos
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarHito($hito) {

        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "INSERT INTO hito_contractual(id_hito,fecha_entrega,descripcion_hito,origen_info,destino_info,normativa,comentario,id_contrato,id_estado_hito,id_frecuencia_hito,id_responsable_hito,id_usuario_hito,id_color) "
                    . "VALUES('" . $hito->getIdHito() . "','" . $hito->getFechaEntrega() . "','" . $hito->getDescripcionHito() . "','" . $hito->getOrigenInfo() . "',"
                    . "'" . $hito->getDestinoInfo() . "','" . $hito->getNormativa() . "','" . $hito->getComentario() . "','" . $hito->getIdContrato() . "','" . $hito->getIdEstadoHito() . "',"
                    . "'" . $hito->getIdFrecuenciaHito() . "','" . $hito->getIdResponsableHito() . "','" . $hito->getIdUsuarioHito() . "','" . $hito->getIdColor() . "')";

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1;

            mysqli_close($cnx);

            return $exito; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que obtiene el primer y ultimo dia del mes
     * @param date $fecha fecha a consultar
     * @return array Retorna un array con el primer y ultimo dia del mes
     */
    public function obtenerPrimeryUltimoDiaFecha($fecha) {
        $serviceCnx = new Conexion;
        $cnx = $serviceCnx->conectar();

        $sql = "SELECT LAST_DAY('$fecha'- INTERVAL 1 MONTH) + INTERVAL 1 DAY AS primerDia, LAST_DAY('$fecha') ultimoDia";
        $dias = array();
        $rs = mysqli_query($cnx, $sql);

        while ($d = mysqli_fetch_array($rs)) {
            $dias['primerDia'] = $d['primerDia'];
            $dias['ultimoDia'] = $d['ultimoDia'];
        }

        mysqli_free_result($rs);
        mysqli_close($cnx);

        return $dias;
    }

    /**
     * Metodo que obtiene el ultimo dia del mes siguiente al ingresado por parametro
     * @param Fecha $fechaHito Fecha ingresado por parametro
     * @return int Retorna el ultimo dia del mes siguiente a la fecha ingresada por parametro
     */
    public function verificarUltimoDiaMesSiguiente($fechaHito) {

        $serviceHito = new HitoContractual();
        $fechaFormateada = strtotime($fechaHito);
        $fecha = date('Y', $fechaFormateada) . "-" . date('m', $fechaFormateada) . "-01";


        //$nuevaFecha = date('Y-m-d', strtotime('+1 months', strtotime($hitoContractual->getFechaEntrega())));
        //AUMENTO EN UN MES LA FECHA FORMATEDA
        $fecha = date('Y-m-d', strtotime('+1 months', strtotime($fecha)));
        //obtengo el ultimo dia del mes auxiliar para despues compararlo con el "dia original" de la fecha ingresada
        $diasMes = $serviceHito->obtenerPrimeryUltimoDiaFecha($fecha);
        $ultimoDiaMes = $diasMes['ultimoDia'];
        $ultimoDiaMes = strtotime($diasMes['ultimoDia']);
        $ultimoDiaMes = date('d', $ultimoDiaMes); //OBTENIENDO EL ULTIMO 

        return $ultimoDiaMes;
    }

    /**
     * Metodo que suma dias o mes  a una fecha ingresada por parametro
     * @param Date $fecha Fecha ingresada por parametro
     * @param int $dias Dias a sumar
     * @param int $meses Meses a sumar
     * @param int $flag flag para seleccionar sumar dias o mes
     * @return Date Retorna la fecha formateda
     */
    public function sumarMesDiaFecha($fecha, $dias, $meses, $flag) {
        try {

            $serviceCnx = new Conexion();
            $cnx = $serviceCnx->conectar();
            $fechaResultado = "";

            switch ($flag) {

                case 1://DIAS
                    $sql = "SELECT DATE_ADD('$fecha', INTERVAL +'$dias' DAY) AS resultado";
                    $rs = mysqli_query($cnx, $sql); //resultSet
                    break;

                case 2://MESES
                    $sql = "SELECT DATE_ADD('$fecha', INTERVAL +'$meses' MONTH) AS resultado";
                    $rs = mysqli_query($cnx, $sql); //resultSet
                    break;
            }

            //obteniendo resultados
            while ($r = mysqli_fetch_array($rs)) {

                $fechaResultado = $r['resultado'];
            }

            //LIBERANDO RECURSOS
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $fechaResultado; //Retornando resultado
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * Método que permite obtener el día (numero) de la fecha ingresada
     * @param date $fecha Fecha a consultar su día
     * @return int Retorna el numero del día consultado
     */
    function obtenerDia($fecha) {
        $dia = strtotime($fecha);
        $dia = date('d', $dia);

        return $dia;
    }

    /**
     * Método que permite obtener el mes (numero) de la fecha ingresada
     * @param date $fecha Fecha a consultar su mes
     * @return int Retorna el numero del mes consultado
     */
    function obtenerMes($fecha) {
        $mes = strtotime($fecha);
        $mes = date('m', $mes);

        return $mes;
    }

    function armarFecha($fecha, $diaOriginal) {
        $aux = strtotime($fecha);
        $fechaFormateada = date("Y", $aux) . "-" . date("m", $aux) . "-" . $diaOriginal;

        return $fechaFormateada;
    }

    /**
     * Metodo que ingresa hitos durante toda la duracion del contrato
     * @param Obj $hitoContractual Objeto con los atributos a ingresar
     * @param date $fechaTerminoContrato Fecha de termino del contrato
     */
    public function ingresarHitoPorTodoElContrato($hitoContractual, $fechaTerminoContrato) {
        try {
            $serviceHito = new HitoContractual();

            $marcador = 0;
            $flagFebreroTrimestre = 0;
            $hitoContractual->setIdHito("");
            while ($fechaTerminoContrato > $hitoContractual->getFechaEntrega()):

                switch ($hitoContractual->getIdFrecuenciaHito()) {

                    case 1://SEMANAL

                        $nuevaFecha = date('Y-m-d', strtotime('+7 days', strtotime($hitoContractual->getFechaEntrega())));
                        $hitoContractual->setFechaEntrega($nuevaFecha);


                        $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD


                        break;


                    case 2://MENSUAL     
                        //
                        //SE GENERA UNA NUEVA FECHA A PARTIR DE LA FECHA QUE INGRESA POR PARAMETRO---SE AGREGA UN MES
                        $fechaFormateada = strtotime($hitoContractual->getFechaEntrega());

                        if ($marcador == 0) {

                            $diaOriginal = date('d', $fechaFormateada); //dia original de la fecha ingresada
                            $marcador++;
                        }

                        //CODIGO !!!     
                        $nuevaFecha = $serviceHito->sumarMesDiaFecha($hitoContractual->getFechaEntrega(), 0, 1, 2); //sumo mes a la fecha ingresada
                        $diaFecha = $serviceHito->obtenerDia($nuevaFecha); //obtengo el dia de la fecha calculada
                        $mesNuevaFecha = $serviceHito->obtenerMes($nuevaFecha);
                        $ultimoDiaFecha = $serviceHito->obtenerPrimeryUltimoDiaFecha($nuevaFecha); //obtengo la ultima fecha del mes de la fecha calculada arriba

                        $ultimoDiaFecha = $ultimoDiaFecha['ultimoDia']; //ultimo fecha (y-m-d)

                        $ultimoDiaFecha = $serviceHito->obtenerDia($ultimoDiaFecha); //obtengo el dia

                        if ($ultimoDiaFecha == 31 && $diaOriginal == 31 && $diaFecha == 30) {//SUMANDO 1 DIA SI ES Q EL PROXIMO MES TIENE 31 DIAS
                            $nuevaFecha = $serviceHito->sumarMesDiaFecha($nuevaFecha, 1, 0, 1);
                        } else if ($mesNuevaFecha == 03 && $diaOriginal > $diaFecha) {//cálculo despues de febrero....malvado febrero
                            $nuevaFecha = $serviceHito->armarFecha($nuevaFecha, $diaOriginal);
                        }


                        $hitoContractual->setFechaEntrega($nuevaFecha);

                        $diaSemanaNuevaFecha = date("w", strtotime($nuevaFecha));

                        switch ($diaSemanaNuevaFecha) {

                            case 6://SABADO
                                $fechaActualizada = date('Y-m-d', strtotime('-1 days', strtotime($nuevaFecha)));
                                $hitoContractual->setFechaEntrega($fechaActualizada);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                $nuevaFecha = date('Y-m-d', strtotime('+1 days', strtotime($hitoContractual->getFechaEntrega())));
                                $hitoContractual->setFechaEntrega($nuevaFecha);

                                break;

                            case 0://DOMINGO
                                $fechaActualizada = date('Y-m-d', strtotime('-2 days', strtotime($nuevaFecha)));
                                $hitoContractual->setFechaEntrega($fechaActualizada);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                $nuevaFecha = date('Y-m-d', strtotime('+2 days', strtotime($hitoContractual->getFechaEntrega())));
                                $hitoContractual->setFechaEntrega($nuevaFecha);
                                break;

                            default://DIAS DE LA SEMANA
                                $hitoContractual->setFechaEntrega($nuevaFecha);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                break;
                        }

                        break;




                    case 3://TRIMESTRAL
                        //
                        //SE GENERA UNA NUEVA FECHA A PARTIR DE LA FECHA QUE INGRESA POR PARAMETRO---SE AGREGA UN MES
                        $fechaFormateada = strtotime($hitoContractual->getFechaEntrega());

                        if ($marcador == 0) {
                            $diaOriginal = date('d', $fechaFormateada); //dia original de la fecha ingresada
                            $marcador++;
                        }

                        //CODIGO !!!     
                        $nuevaFecha = $serviceHito->sumarMesDiaFecha($hitoContractual->getFechaEntrega(), 0, 3, 2); //sumo mes a la fecha ingresada
                        $diaFecha = $serviceHito->obtenerDia($nuevaFecha); //obtengo el dia de la fecha calculada
                        $mesNuevaFecha = $serviceHito->obtenerMes($nuevaFecha);
                        $ultimoDiaFecha = $serviceHito->obtenerPrimeryUltimoDiaFecha($nuevaFecha); //obtengo la ultima fecha del mes de la fecha calculada arriba

                        $ultimoDiaFecha = $ultimoDiaFecha['ultimoDia']; //ultimo fecha (y-m-d)

                        $ultimoDiaFecha = $serviceHito->obtenerDia($ultimoDiaFecha); //obtengo el dia

                        if ($ultimoDiaFecha == 31 && $diaOriginal == 31 && $diaFecha == 30) {//SUMANDO 1 DIA SI ES Q EL PROXIMO MES TIENE 31 DIAS
                            $nuevaFecha = $serviceHito->sumarMesDiaFecha($nuevaFecha, 1, 0, 1);
                        } else if ($flagFebreroTrimestre == 1 && $diaOriginal > $diaFecha) {//cálculo despues de febrero....malvado febrero
                            $nuevaFecha = $serviceHito->armarFecha($nuevaFecha, $diaOriginal);
                            $flagFebreroTrimestre = 0;
                        }


                        $hitoContractual->setFechaEntrega($nuevaFecha);

                        $diaSemanaNuevaFecha = date("w", strtotime($nuevaFecha));


                        switch ($diaSemanaNuevaFecha) {

                            case 6://SABADO

                                $fechaActualizada = date('Y-m-d', strtotime('-1 days', strtotime($nuevaFecha)));
                                $hitoContractual->setFechaEntrega($fechaActualizada);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                $nuevaFecha = date('Y-m-d', strtotime('+1 days', strtotime($hitoContractual->getFechaEntrega())));
                                $hitoContractual->setFechaEntrega($nuevaFecha);



                                break;

                            case 0://DOMINGO
                                $fechaActualizada = date('Y-m-d', strtotime('-2 days', strtotime($nuevaFecha)));
                                $hitoContractual->setFechaEntrega($fechaActualizada);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                $nuevaFecha = date('Y-m-d', strtotime('+2 days', strtotime($hitoContractual->getFechaEntrega())));
                                $hitoContractual->setFechaEntrega($nuevaFecha);
                                break;

                            default://DIAS DE LA SEMANA
                                //OBTENER MESDE LA FECHA CALCULADA, DEJAR FLAG SI EL MES ES FEBRERO...MALVADO FEBRERO
                                $mesfecha = $serviceHito->obtenerMes($nuevaFecha);
                                $flagFebreroTrimestre = $mesfecha == 02 ? 1 : 0; //ASIGNANDO FLAG EN CASO DE QUE EL MES DE LA FECHA SEA FEBRERO

                                $hitoContractual->setFechaEntrega($nuevaFecha);
                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                break;
                        }



                        break;



                    case 4://ANUAL

                        $nuevaFecha = date('Y-m-d', strtotime('+1 years', strtotime($hitoContractual->getFechaEntrega())));

//                        $hitoContractual->setFechaEntrega($nuevaFecha);

                        $diaSemanaNuevaFecha = date("w", strtotime($nuevaFecha));

                        if ($diaSemanaNuevaFecha == 6) {

                            $fechaActualizada = date('Y-m-d', strtotime('-1 days', strtotime($nuevaFecha)));
                            $hitoContractual->setFechaEntrega($fechaActualizada);

                            $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                            $nuevaFecha = date('Y-m-d', strtotime('+1 days', strtotime($hitoContractual->getFechaEntrega())));
                            $hitoContractual->setFechaEntrega($nuevaFecha);
                        } else {

                            if ($diaSemanaNuevaFecha == 0) {

                                $fechaActualizada = date('Y-m-d', strtotime('-2 days', strtotime($nuevaFecha)));
                                $hitoContractual->setFechaEntrega($fechaActualizada);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD

                                $nuevaFecha = date('Y-m-d', strtotime('+2 days', strtotime($hitoContractual->getFechaEntrega())));
                                $hitoContractual->setFechaEntrega($nuevaFecha);
                            } else {

                                $hitoContractual->setFechaEntrega($nuevaFecha);

                                $fechaTerminoContrato > $nuevaFecha ? $serviceHito->ingresarHito($hitoContractual) : ''; //verifico si la fecha de termino es superior a la fecha calculada, si los es inserto el hito sino no xD
                            }
                        }

                        break;
                }

            endwhile;
        } catch (Exception $ex) {

            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que actualiza el hito seleccionado
     * @param Objeto $hito Objeto con los atributos a insertar en la base de datos
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarHito($hito) {

        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "UPDATE hito_contractual SET fecha_entrega='" . $hito->getFechaEntrega() . "', descripcion_hito='" . $hito->getDescripcionHito() . "',"
                    . "id_responsable_hito='" . $hito->getIdResponsableHito() . "',id_usuario_hito ='" . $hito->getIdUsuarioHito() . "' ,destino_info='" . $hito->getDestinoInfo() . "',"
                    . "normativa='" . $hito->getNormativa() . "',id_estado_hito='" . $hito->getIdEstadoHito() . "',"
                    . "comentario='" . $hito->getComentario() . "'  WHERE id_hito = '" . $hito->getIdHito() . "'";

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1;

            mysqli_close($cnx);

            return $exito; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Metodo que elimina un registro de hito segun su id
     * @param int $idHito id del hito
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarHito($idHito) {

        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "DELETE FROM hito_contractual  WHERE id_hito = '$idHito'";

            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1;

            mysqli_close($cnx);

            return $exito; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Método que obtiene el id máximo de la tabla 
     * @return int Retorna el id máximo de la tabla hito
     */
    public function getMaxIdHito() {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT MAX(id_hito) AS max FROM hito_contractual";
            $max = "";
            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $max = $r['max'];
            }


            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $max; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function obtenerHitosPorFiltro($idResponsable, $idEstadoHito, $idFrecuencia, $idPerfilUsuario, $idContrato, $inicio, $limite) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual ";
            $hitos = array();

            if ($idPerfilUsuario == 2 || $idPerfilUsuario == 3) {//RESIDENTE Y ENCAGADO DE HITOS
                $sql.= " WHERE id_contrato ='$idContrato'";
            } else {

                if ($idPerfilUsuario == 1) {
                    $sql.= " WHERE id_contrato!=''";
                } else {
                    $sql.= " WHERE id_responsable_hito='$idResponsable'";
                }
            }


            //FILTROS
            if ($idResponsable != "") {
                $sql.= " AND id_responsable_hito='$idResponsable'";
            }

            if ($idFrecuencia != "") {
                $sql.= " AND id_frecuencia_hito='$idFrecuencia'";
            }

            if ($idEstadoHito != "") {
                $sql.= " AND id_estado_hito='$idEstadoHito'";
            }

            $sql.=" ORDER BY fecha_entrega LIMIT $inicio,$limite ";

            $rs = mysqli_query($cnx, $sql);

            while ($r = mysqli_fetch_array($rs)) {

                $hito = new HitoContractual(); //instanceando y seteando objeto
                $hito->setIdHito($r['id_hito']);
                $hito->setFechaEntrega($r['fecha_entrega']);
                $hito->setDescripcionHito($r['descripcion_hito']);
                $hito->setOrigenInfo($r['origen_info']);
                $hito->setDestinoInfo($r['destino_info']);
                $hito->setNormativa($r['normativa']);
                $hito->setComentario($r['comentario']);
                $hito->setIdContrato($r['id_contrato']);
                $hito->setIdEstadoHito($r['id_estado_hito']);
                $hito->setIdFrecuenciaHito($r['id_frecuencia_hito']);
                $hito->setIdResponsableHito($r['id_responsable_hito']);
                $hito->setIdUsuarioHito($r['id_usuario_hito']);
                $hito->setIdColor($r['id_color']);

                array_push($hitos, $hito);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $hitos; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function contarHitosPorFiltro($idResponsable, $idEstadoHito, $idFrecuencia, $idPerfilUsuario, $idContrato) {
        try {
            $serviceCnx = new Conexion(); //servicio
            $cnx = $serviceCnx->conectar(); //link de la conexion
            $sql = "SELECT * FROM hito_contractual";

            if ($idPerfilUsuario == 2 || $idPerfilUsuario == 3) {//RESIDENTE Y ENCAGADO DE HITOS
                $sql.= " WHERE id_contrato ='$idContrato'";
            } else {

                if ($idPerfilUsuario == 1) {
                    $sql.= " WHERE id_contrato!=''";
                } else {
                    $sql.= " WHERE id_responsable_hito='$idResponsable'";
                }
            }

            //FILTROS
            if ($idResponsable != "") {
                $sql.= " AND id_responsable_hito='$idResponsable'";
            }

            if ($idFrecuencia != "") {
                $sql.= " AND id_frecuencia_hito='$idFrecuencia'";
            }

            if ($idEstadoHito != "") {
                $sql.= " AND id_estado_hito='$idEstadoHito'";
            }


            $rs = mysqli_query($cnx, $sql);
            $total = mysqli_num_rows($rs);

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $total; //retornando resultados
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
