<?php

/**
 * Description of Usuario
 *
 * @author vfernandez
 */
require_once 'Conexion.php';
require_once 'ConexionCloud.php';

class Usuario {

    //ATRIBUTOS
    private $idUsuario;
    private $nombre;
    private $apellidoP;
    private $apellidoM;
    private $correo;
    private $nombreUsuario;
    private $contrasena;
    private $idPerfil;
    private $idContrato;
    private $idEstadoUsuario;

    public function __construct() {
        
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getApellidoP() {
        return $this->apellidoP;
    }

    function getApellidoM() {
        return $this->apellidoM;
    }

    function getCorreo() {
        return $this->correo;
    }

    function getNombreUsuario() {
        return $this->nombreUsuario;
    }

    function getContrasena() {
        return $this->contrasena;
    }

    function getIdPerfil() {
        return $this->idPerfil;
    }

    function getIdContrato() {
        return $this->idContrato;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setApellidoP($apellidoP) {
        $this->apellidoP = $apellidoP;
    }

    function setApellidoM($apellidoM) {
        $this->apellidoM = $apellidoM;
    }

    function setCorreo($correo) {
        $this->correo = $correo;
    }

    function setNombreUsuario($nombreUsuario) {
        $this->nombreUsuario = $nombreUsuario;
    }

    function setContrasena($contrasena) {
        $this->contrasena = $contrasena;
    }

    function setIdPerfil($idPerfil) {
        $this->idPerfil = $idPerfil;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    function getIdEstadoUsuario() {
        return $this->idEstadoUsuario;
    }

    function setIdEstadoUsuario($idEstadoUsuario) {
        $this->idEstadoUsuario = $idEstadoUsuario;
    }

    //-----------------------------//
    //          METODOS           //
    //---------------------------//

    /**
     * Método que retorna todos los usuarios de ingresados en la base de datos
     * @return Array Metodo que devuele un array con todos los usuario de la base de datos
     */
    public function getUsuarios() {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM usuario"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $usuarios = array(); //array para los usuarios

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);

                array_push($usuarios, $usuario); //llenando el array con los usuarios de la db                
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarios; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Método que obtiene un registro de usuario a traves de su correo
     * @param string $correo correo para consulta
     * @return \Usuario Retorna un objeto con los resultados
     */
    public function getUsuarioPorCorreo($correo) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM usuario WHERE correo='$correo'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
          
            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);
            
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuario; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Método que obtiene a todos los usuarios asociados a un subcontrato
     * @param int $idSubcontrato id del subcontrato
     * @return array Retorna un array con los resultados obtenidos
     */
    public function getUsuariosPorSubContrato($idSubcontrato) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = " SELECT * FROM usuario u JOIN usuario_subcontrato us USING(id_usuario) "
                    . "WHERE us.id_subcontrato = '$idSubcontrato'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $usuarios = array(); //array para los usuarios

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);

                $usuarios[] = $usuario; //llenando el array con los usuarios de la db
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarios; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function getUsuariosPorSubContratoActivo($idSubcontrato) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = " SELECT * FROM usuario u JOIN usuario_subcontrato us USING(id_usuario) "
                    . "WHERE us.id_subcontrato = '$idSubcontrato' AND u.id_estado_usuario=1 "; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $usuarios = array(); //array para los usuarios

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);

                array_push($usuarios, $usuario); //llenando el array con los usuarios de la db                
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarios; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    /**
     * Método que obtiene el id maximo de la tabla usuario
     * @return int Retorna el id máximo de la tabla usuario
     */
    public function getMaxIdUsuario() {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT MAX(id_usuario) as max FROM usuario"; //query
            $rs = mysqli_query($cnx, $sql); //resultset

            $idUsuario = "";
            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $idUsuario = $r['max'];
            }

//liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $idUsuario; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param int $idContrato id del contrato a consultar
     * @return array Retorna un array con los usuario obtenidos segun el parametro ingresado
     */
    public function getUsuariosPorContrato($idContrato) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM usuario WHERE id_estado_usuario=1 AND id_Contrato='$idContrato'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset
            $usuarios = array(); //array para los usuarios

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);

                array_push($usuarios, $usuario); //llenando el array con los usuarios por contrato               
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarios; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param int $idUsuario id del usuario
     * @return Usuario Retorna un obj tipo usuario de acuerdo al parametro ingresado
     */
    public function getUsuarioPorId($idUsuario) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM usuario WHERE id_usuario='$idUsuario'"; //query
            $rs = mysqli_query($cnx, $sql); //resultset

            $usuario = null;
            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuario; //retornando el array    
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function verificarLogin($nombreUsuario, $contrasena) {
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $tb = mysqli_query($cnx, 'show tables');
            //var_dump (implode(",",mysqli_fetch_all($tb)));
            //var_dump($cnx);
            $sql = "SELECT * FROM usuario WHERE nombre_usuario='".$nombreUsuario."' AND contrasena='". $contrasena."' AND id_estado_usuario=1"; //query
            //var_dump($sql);

            $rs = mysqli_query($cnx, $sql); //resultset

            if(!$rs){
                var_dump(mysqli_error($cnx));
            }

            //var_dump($rs);
            $usuario = "";
            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuario; //retornando el objeto         
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param int $idUsuario id del usuario
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function eliminarUsuarioPorId($idUsuario) {
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sqlTablaDependiente = "DELETE FROM usuario_subcontrato WHERE id_usuario='$idUsuario'"; //query para eliminar la tabla Usuario_subcontrato

            mysqli_query($cnx, $sqlTablaDependiente); //si se pudo eliminar los registros de la tabla dependiente entonces se borra al usuario
            $sql = "DELETE FROM usuario WHERE id_usuario='$idUsuario'"; //query
            $exito = mysqli_query($cnx, $sql) == true ? 1 : -1; //resultado de la query, asignando valor a la bandera         
            //liberando recursos          
            mysqli_close($cnx);

            return $exito; //retornando la flag  
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param Objeto $usuario Objeto con todos los atributos del usuario
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function actualizarUsuario($usuario, $flag) {
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            switch ($flag) {
                case 1://ACTUALIZAR USUARIO MODULO ADMIN
                    $sql = "UPDATE usuario SET nombre='" . $usuario->getNombre() . "',apellido_p='" . $usuario->getApellidoP() . "', apellido_m='" . $usuario->getApellidoM() . "',"
                            . "id_perfil='" . $usuario->getIdPerfil() . "',id_estado_usuario='" . $usuario->getIdEstadoUsuario() . "' WHERE id_usuario='" . $usuario->getIdUsuario() . "'  "; //query

                    break;

                case 2://ADMINISTRAR PERFIL
                    $sql = "UPDATE usuario SET nombre='" . $usuario->getNombre() . "',apellido_p='" . $usuario->getApellidoP() . "', apellido_m='" . $usuario->getApellidoM() . "',"
                            . "correo='" . $usuario->getCorreo() . "' WHERE id_usuario='" . $usuario->getIdUsuario() . "'  "; //query
                    break;

                case 3://ACTUALIZAR CONTRASEÑA
                    $sql = "UPDATE usuario SET contrasena='" . $usuario->getContrasena() . "' WHERE id_usuario='" . $usuario->getIdUsuario() . "'";
                    break;
            }

            $rs = mysqli_query($cnx, $sql); //resultset

            $exito = -1; //variable que indica si la accion se ejecuto correctamente

            if ($rs) { //si el resulset es true entonces se devuelve un 1, sino se mantiene el -1 anterior               
                $exito = 1;
            }

            //liberando recursos

            mysqli_close($cnx);

            return $exito; //retornando la flag  
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param Objetivo $usuario Objeto con los atributos a insertar en la BD
     * @return int Retorna un 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarUsuario($usuario) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); // Link de conexion
            
            // Preparing the base SQL query
            $sql = "INSERT INTO usuario (id_usuario, nombre, apellido_p, apellido_m, correo, nombre_usuario, contrasena, id_perfil, id_contrato, id_estado_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Preparing the statement
            $stmt = $cnx->prepare($sql);
    
            // Collecting data from $usuario object
            $idUsuario = $usuario->getIdUsuario();
            $nombre = $usuario->getNombre();
            $apellidoP = $usuario->getApellidoP();
            $apellidoM = $usuario->getApellidoM();
            $correo = $usuario->getCorreo(); // This is optional
            $nombreUsuario = $usuario->getNombreUsuario();
            $contrasena = $usuario->getContrasena();
            $idPerfil = $usuario->getIdPerfil();
            $idContrato = $usuario->getIdContrato();
            $idEstadoUsuario = $usuario->getIdEstadoUsuario();
    
            // Checking if correo is set
            $correo = ($correo !== null) ? $correo : "";
    
            // Binding parameters
            $stmt->bind_param("ssssssssss", $idUsuario, $nombre, $apellidoP, $apellidoM, $correo, $nombreUsuario, $contrasena, $idPerfil, $idContrato, $idEstadoUsuario);
    
            // Executing the statement and setting the success flag
            $exito = $stmt->execute() ? 1 : -1;
    
            // Closing the statement and connection
            $stmt->close();
            $cnx->close();
    
            return $exito; // Returning the flag
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    

    /**
     * 
     * @param String $nombre Nombre del usuario
     * @param String $nombreUsuario Nombre de usuario del sistema
     * @param String $area area a la que pertenece el usuario
     * @return int Retorna 1 si la operacion es correcta, -1 si no lo es
     */
    public function ingresarUsuarioCloud($nombre, $nombreUsuario, $area) {
        try {

            $serviceConexion = new ConexionCloud();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "INSERT INTO oc_users(uid,displayname,password) "
                    . "VALUES('$nombreUsuario','$nombre','1|$2y$10" . '$kW4zHFoHR15mOdXLxaBDZ' . ".ecjyCbO.biTOvab3dku6ZLQg./v51Ii')";

            $sql2 = "INSERT INTO oc_group_user(gid,uid) VALUES('$area','$nombreUsuario')";
            $exito = -1;

            if (mysqli_query($cnx, $sql)) { //si el resulset es true entonces se devuelve un 1, sino se mantiene el -1 anterior               
                $exito = mysqli_query($cnx, $sql2) == true ? 1 : -1;
            }

            //liberando recursos           
            mysqli_close($cnx);

            return $exito; //retornando la flag  
        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    public function verificarCamposUnicos($nombreUsuario, $correo, $flag) {
        try {

            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            switch ($flag) {

                case 1://VERIFICAR NOMBRE DE USUARIO
                    $sql = "SELECT * FROM usuario WHERE nombre_usuario ='$nombreUsuario'";

                    break;

                case 2://VERIFICAR 
                    $sql = "SELECT * FROM usuario WHERE correo ='$correo'";

                    break;
            }

            $rs = mysqli_query($cnx, $sql);

            $filasAfectadas = mysqli_num_rows($rs);

            //liberando recursos  
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $filasAfectadas; //retornando la flag  
        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    public function getUsuariosContrato($idContrato) {
        try {
            $serviceConexion = new Conexion();
            $cnx = $serviceConexion->conectar(); //link de conexion
            $sql = "SELECT * FROM usuario"; //query
            if($idContrato != "todos"){
                $sql.=" WHERE id_contrato = $idContrato;";
            }
            
            $rs = mysqli_query($cnx, $sql); //resultset
            $usuarios = array(); //array para los usuarios

            while ($r = mysqli_fetch_array($rs)) {
                //seteando el objeto                
                $usuario = new Usuario();
                $usuario->setIdUsuario($r['id_usuario']);
                $usuario->setNombre($r['nombre']);
                $usuario->setApellidoP($r['apellido_p']);
                $usuario->setApellidoM($r['apellido_m']);
                $usuario->setCorreo($r['correo']);
                $usuario->setNombreUsuario($r['nombre_usuario']);
                $usuario->setContrasena($r['contrasena']);
                $usuario->setIdPerfil($r['id_perfil']);
                $usuario->setIdContrato($r['id_contrato']);
                $usuario->setIdEstadoUsuario($r['id_estado_usuario']);

                array_push($usuarios, $usuario); //llenando el array con los usuarios de la db                
            }

            //liberando recursos
            mysqli_free_result($rs);
            mysqli_close($cnx);

            return $usuarios; //retornando el array            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
