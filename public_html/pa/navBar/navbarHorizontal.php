<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar colorAxioma"></span>
            <span class="icon-bar colorAxioma"></span>
            <span class="icon-bar colorAxioma"></span>
        </button>
        <a class="navbar-brand" href="inicio.php"><img id="logoHeader" src="media/logoAxiomaOficial.png" alt="logo"/></a>
    </div>
    <!-- Top Menu Items -->
    <ul class="nav navbar-right top-nav">
        <!--        <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu message-dropdown">
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong>John Smith</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong>John Smith</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong>John Smith</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-footer">
                            <a href="#">Read All New Messages</a>
                        </li>
                    </ul>
                </li>-->
        <!--    
        <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu alert-dropdown">
                        <li>
                            <a href="#">Alert Name <span class="label label-default">Alert Badge</span></a>
                        </li>               
                    </ul>
         </li>
        -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $usuarioSession->getNombre() . " " . $usuarioSession->getApellidoP(); ?> <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="#ActualizarPerfil" onclick="cargarPerfil()"><i class="fa fa-fw fa-user"></i> Perfil</a>
                </li>              

                <li class="divider"></li>
                <li>
                    <a href="../negocio/cerrarSession.php"><i class="fa fa-fw fa-power-off"></i>Salir</a>
                </li>
            </ul>
        </li>
    </ul>

    <!-- MENU LATERAL   -->
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul id="sideNav" class="nav navbar-nav side-nav">
            <li>
                <a href="inicio.php" disable="true"><i class="fa fa-fw fa-home"></i> Inicio</a>
            </li>
            <!--Modulo Ingreso documentos -->
            <li>
                <a href="javascript:;" data-toggle="collapse" data-target="#modIngresoDoc"><i class="fa fa-fw fa-folder-open"></i> Ingreso Documentos<i class="fa fa-fw fa-caret-down"></i></a>
                <ul id="modIngresoDoc" class="collapse">
                    <li>
                        <a href="#ingresarEntrada" onclick="cargarIngresarEntrada()"><i class="fa fa-fw fa-minus"></i> Ingresar Entrada</a>
                    </li>
                    <li>
                        <a href="#ingresarSalida" onclick="cargarIngresarSalida()"><i class="fa fa-fw fa-minus"></i> Ingresar Salida</a>
                    </li>
                    <?php if ($usuarioSession->getIdPerfil() == 9  || $usuarioSession->getIdPerfil() == 3 ): ?>
                    <li>
                        <a href="#cargaMasiva" onclick="cargarCargaMasivaRegistro()"><i class="fa fa-fw fa-minus"></i> Carga masiva de &nbsp;&nbsp;&nbsp;registros</a> 
                    </li>
                    <li>
                        <a href="#cargaMasiva" onclick="cargarCargaArchivosRestantes()"><i class="fa fa-fw fa-minus"></i> Carga archivos &nbsp;&nbsp;&nbsp;restantes</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <li>
                <a href=#buscador onclick="cargarBuscador()"><i class="fa fa-fw fa-search"></i> Buscador</a>
            </li>      
            <li>
                <a href="#chatIA" onclick="cargarChatIA()"><i class="fa fa-fw fa-comments"></i> Chat con IA</a>
            </li>

<!--            <li>
                <a href="javascript:;" data-toggle="collapse" data-target="#hitosContractuales"><i class="fa fa-fw fa-briefcase"></i> Hitos Contractuales<i class="fa fa-fw fa-caret-down"></i></a>
                <ul id="hitosContractuales" class="collapse">
                    <li>
                        <a href="#admHitos" onclick="cargarHitosContractuales()"><i class="fa fa-fw fa-minus"></i> Adm.Hitos Contractuales</a>
                        <a href="#listarHitos" onclick="cargarListarHitosContractuales()"><i class="fa fa-fw fa-minus"></i> Listar Hitos Contractuales</a>
                    </li>

                </ul>
            </li>
-->
            <?php if ($usuarioSession->getIdPerfil() == 1 || $usuarioSession->getIdPerfil() == 3 || $usuarioSession->getIdPerfil() == 9) : ?>
                <li>
                    <a href="javascript:;" data-toggle="collapse" data-target="#modConfiguracion"><i class="fa fa-fw fa-gear"></i> Configuraci&oacute;n<i class="fa fa-fw fa-caret-down"></i></a>
                    <ul id="modConfiguracion" class="collapse">

                        <?php if ($usuarioSession->getIdPerfil() == 1): ?>
                            <li>
                                <a href="#CrearUsuario" onclick="cargarIngresarUsuario()"><i class="fa fa-fw fa-minus"></i> Crear Usuario</a>
                            </li>
                        <?php endif; ?>    
                        <?php if ($usuarioSession->getIdPerfil() == 9): ?>
                            <li>
                                <a href="#CrearUsuarioEncargado" onclick="cargarIngresarUsuarioEncargado()"><i class="fa fa-fw fa-minus"></i> Crear Usuarios</a>
                            </li>
                            <li>
                                <a href="#ActualizarUsuarios" onclick="cargarActualizarUsuariosEncargado()"><i class="fa fa-fw fa-minus"></i> Actualizar Usuarios</a>
                            </li>                            
                        <?php endif; ?>  
                        <?php if ($usuarioSession->getIdPerfil() == 1): ?>
                            <li>
                                <a href="#CrearUsuarioEncargado" onclick="cargarIngresarUsuarioEncargado()"><i class="fa fa-fw fa-minus"></i> Crear Usuarios</a>
                            </li>
                            <li>
                                <a href="#ActualizarUsuarios" onclick="cargarActualizarUsuariosEncargado()"><i class="fa fa-fw fa-minus"></i> Actualizar Usuarios</a>
                            </li>
                            <li>
                                <a href="#ActualizarEliminarUsuario" onclick="cargarActualizarEliminarUsuario()" ><i class="fa fa-fw fa-minus"></i> Actualizar / Eliminar &nbsp;&nbsp;&nbsp;Usuario</a>
                            </li>
                            <li>
                                <a href="#MantenerContratos" onclick="cargarMantenerContratos()" ><i class="fa fa-fw fa-minus"></i> Administrar Contratos</a>
                            </li>                            
                            <li>
                                <a href="#MantenerPerfiles" onclick="cargarMantenerPerfiles()" ><i class="fa fa-fw fa-minus"></i> Administrar Perfiles</a>
                            </li>
                            <li>
                                <a href="#MantenerTipoDoc" onclick="cargarMantenerTipoDoc()" ><i class="fa fa-fw fa-minus"></i> Administrar Tipo &nbsp;&nbsp;&nbsp;Documento</a>
                            </li>
                        <?php endif; ?>
                            
                        <li>
                            <a href="#MantenerEntidades" onclick="cargarMantenerEntidades()" ><i class="fa fa-fw fa-minus"></i> Administrar Entidades</a>
                        </li>
                        <li>
                            <a href="#MantenerCargos" onclick="cargarMantenerCargos()" ><i class="fa fa-fw fa-minus"></i> Administrar Cargos</a>
                        </li>

                    </ul>
                </li>   

            <?php endif; ?>
            <li>
                <a href="#Manuales" onclick="cargaTutoriales()"><i class="fa fa-fw fa-play-circle-o"></i>Video Tutorial</a>
            </li>
             <li>
                <a href="#Bases" onclick="cargaBases(<?php echo $usuarioSession->getIdContrato(); ?>)"><i class="fa fa-fw fa-file"></i>Bases de Licitación</a>
            </li>

        </ul>
    </div>
    <!-- /.navbar-collapse -->
</nav>
