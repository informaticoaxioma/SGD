<!DOCTYPE html>
<?php require_once '../negocio/inicio/cargarInicio.php'; ?>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Victor Fernandez">
        <link href="media/LogoAxioma.jpg" rel="SHORTCUT ICON">

        <title> SGD | Axioma </title>
        <link href="css/bootstrap.min.css" rel="stylesheet">        
        <link href="css/sb-admin.css" rel="stylesheet">
        <link href="css/plugins/morris.css" rel="stylesheet">       
        <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<!--        <link rel="stylesheet" href="css/fullcalendar.css">       -->
        <link rel="stylesheet" href="js/material-floating-button-master/dist/mfb.css">     

        <link rel="stylesheet" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <link href='http://fonts.googleapis.com/css?family=Raleway:100,200,300,400' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="js/bootstrap-colorselector-master/lib/bootstrap-colorselector-0.2.0/css/bootstrap-colorselector.css">        
        <link rel="stylesheet" href="js/sweetAlert/sweetalert2.min.css">        
        <link href="../plugins/js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
        <link href="../plugins/js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../plugins/js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../plugins/js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../plugins/js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div id="wrapper" class="page-block">      
            <?php require_once 'navBar/navbarHorizontal.php'; ?>
            <div id="page-wrapper" class="text-left">
                <div class="container-fluid text-center">
                    <input type="hidden" id="idPerfilUsuario" name="idPerfilUsuario" value="<?php echo $usuarioSession->getIdPerfil(); ?>">                    
                    <input type="hidden" id="flagEncargado" name="flagEncargado" value="">
                    <input type="hidden" id="idFlujo" name="idFlujo" value="">
                    <!-- Page Heading -->
                    <!--MENSAJES ACTIVIDADES ADMINISTRADOR-->
                    <?php
                    switch ($usuarioSession->getIdPerfil()) {
                        case 1://vista administrador
                            require_once 'inicio/inicioAdministrador.php';

                            break;
                        case 3://VISTA ENCARGADO DE DOCUMENTOS
                            require_once 'inicio/inicioEncargadoDoc.php';
                            break;
                        default:
                            require_once 'inicio/inicioUsuarios.php';
                            break;
                    }
                    ?>  

                    <div class="row">
                        <div id="contenedorAjax" class="col-xs-12 col-sm-12 col-md-12 text-left">


                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- /#page-wrapper -->
        </div>
        <!-- /#wrapper -->


        <!-- MODAL -->
        <div class="modal fade" id="modalRespuesta" tabindex="-1" role="dialog" aria-labelledby="modalRespuesta">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-lg">
                    <div class="modal-header colorAxioma">
                        <button type="button" class="close" id="btnCerrarModal" data-dismiss="modal"  aria-label="Close"><i class="fa fa-times blanco"></i></button>
                        <h4 class="modal-title blanco text-center">Responder Registro</h4>
                    </div>
                    <div class="modal-body  text-left">
                        <input type="hidden" id="idBtn" value="">
                        <input type="hidden" id="idPEstadoDoc" value="">
                        <input type="hidden" id="idPMensajeDoc" value="">

                        <div class="divAjax">

                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- jQuery -->
        <script src="js/jquery.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/validacionesCustom.js"></script>        
        <script src="js/jquery-ui.js"></script>      

        <!-- Bootstrap Core JavaScript -->
        <script src="js/bootstrap.min.js"></script>    

        <!--Funciones JS -->
        <script src="funcionesJS/llamadasAjax.js"></script>
        <script src="funcionesJS/cargarCalendario.js"></script>
        <script src="funcionesJS/inicio/funcionesInicio.js"></script>

        <!-- FullCalendar -->
<!--        <script src='js/fullCalendar/moment.min.js'></script>-->
<!--        <script src='js/fullCalendar/fullcalendar.min.js'></script>-->
<!--        <script src='js/fullCalendar/locale-all.js'></script>-->

        <!--Sweet Alert -->
        <script src='js/sweetAlert/sweetalert2.min.js'></script>
        <script src='js/material-floating-button-master/dist/lib/modernizr.touch.js'></script>
        <script src='js/material-floating-button-master/dist/mfb.min.js'></script>


        <!--BLOCK UI -->
        <script src="js/block-ui.js"></script>

        <script src='js/bootstrap-colorselector-master/lib/bootstrap-colorselector-0.2.0/js/bootstrap-colorselector.js'></script>
          <!-- dataTablesssssss  -->
      <script src="../plugins/js/datatables/jquery.dataTables.min.js"></script>
      <script src="../plugins/js/datatables/dataTables.bootstrap.js"></script>
      <script src="../plugins/js/datatables/dataTables.buttons.min.js"></script>
      <script src="../plugins/js/datatables/buttons.bootstrap.min.js"></script>
      <script src="../plugins/js/datatables/jszip.min.js"></script>
<!--      <script src="../plugins/js/datatables/pdfmake.min.js"></script>-->
<!--      <script src="../plugins/js/datatables/vfs_fonts.js"></script>-->
      <script src="../plugins/js/datatables/buttons.html5.min.js"></script>
      <script src="../plugins/js/datatables/buttons.print.min.js"></script>
      <script src="../plugins/js/datatables/dataTables.fixedHeader.min.js"></script>
      <script src="../plugins/js/datatables/dataTables.keyTable.min.js"></script>
      <script src="../plugins/js/datatables/dataTables.responsive.min.js"></script>
      <script src="../plugins/js/datatables/responsive.bootstrap.min.js"></script>
      <script src="../plugins/js/datatables/dataTables.scroller.min.js"></script>
      <script src="https://cdn.datatables.net/plug-ins/1.10.21/dataRender/datetime.js" charset="utf8"></script>
      <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
      <script src="//cdn.datatables.net/plug-ins/1.10.12/sorting/datetime-moment.js"></script>
    </body>
</html>
