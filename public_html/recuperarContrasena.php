<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="SHORTCUT ICON" href="pa/media/LogoAxioma.jpg" />
        <link rel="stylesheet" href="pa/css/bootstrap.min.css">
        <link rel="stylesheet" href="pa/css/cssLogin.css">
        <link href="pa/media/LogoAxioma.jpg" rel="SHORTCUT ICON">
        <title>Recuperar Contraseña</title>
        <style>
            :root {
                --burdeo: #761c19;
                --burdeo-oscuro: #5a1512;
                --burdeo-claro: #9a2f2b;
                --burdeo-suave: #f5e9e8;
                --texto: #2b2b2b;
                --texto-tenue: #6b6b6b;
                --borde: #e6dad9;
                --blanco: #ffffff;
                --sombra: 0 10px 40px rgba(118, 28, 25, 0.12);
            }

            #body-recuperar-pass {
                min-height: 100vh;
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                color: var(--texto);
                padding: 24px;
            }

            #body-recuperar-pass .container {
                width: 100%;
                max-width: 460px;
            }

            #divRecuperarContrasena {
                animation: aparecer 0.5s ease;
            }

            @keyframes aparecer {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }

            #divRecuperarContrasena .panel {
                background: var(--blanco);
                border: 1px solid var(--borde);
                border-radius: 16px;
                box-shadow: var(--sombra);
                overflow: hidden;
            }

            #divRecuperarContrasena .panel-heading.color-principal {
                background: linear-gradient(135deg, var(--burdeo) 0%, var(--burdeo-oscuro) 100%);
                padding: 28px 24px;
                border: none;
            }

            #divRecuperarContrasena .panel-heading .blanco {
                color: var(--blanco);
                margin: 0;
                font-weight: 600;
                letter-spacing: 0.3px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
            }

            #divRecuperarContrasena .panel-body {
                padding: 28px 24px 24px;
            }

            #divRecuperarContrasena .alert-info {
                background: var(--burdeo-suave);
                border: 1px solid var(--borde);
                border-left: 4px solid var(--burdeo);
                color: var(--texto);
                border-radius: 10px;
                padding: 14px 16px;
                margin-bottom: 20px;
                font-size: 14px;
                line-height: 1.5;
            }

            #divRecuperarContrasena .alert-info b {
                color: var(--burdeo);
            }

            #divRecuperarContrasena .form-control {
                height: 50px;
                border: 1.5px solid var(--borde);
                border-radius: 10px 0 0 10px;
                padding: 0 16px;
                font-size: 15px;
                color: var(--texto);
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
                box-shadow: none;
            }

            #divRecuperarContrasena .form-control:focus {
                border-color: var(--burdeo);
                box-shadow: 0 0 0 3px rgba(118, 28, 25, 0.12);
                outline: none;
            }

            #divRecuperarContrasena .input-group-btn .btn.color-principal {
                background: linear-gradient(135deg, var(--burdeo) 0%, var(--burdeo-oscuro) 100%);
                color: var(--blanco);
                border: none;
                border-radius: 0 10px 10px 0;
                height: 50px;
                padding: 0 24px;
                font-weight: 600;
                font-size: 15px;
                transition: filter 0.2s ease, transform 0.1s ease;
            }

            #divRecuperarContrasena .input-group-btn .btn.color-principal:hover {
                filter: brightness(1.1);
            }

            #divRecuperarContrasena .input-group-btn .btn.color-principal:active {
                transform: translateY(1px);
            }

            #divRecuperarContrasena .alert-danger,
            #divRecuperarContrasena .alert-success {
                border-radius: 10px;
                padding: 12px 16px;
                margin-top: 16px;
                font-size: 14px;
                border: 1px solid transparent;
            }

            #divRecuperarContrasena .alert-danger {
                background: #fdeceb;
                border-color: #f5c6c4;
                color: #a02622;
            }

            #divRecuperarContrasena .alert-success {
                background: #eaf6ec;
                border-color: #c3e6cb;
                color: #2b7a3b;
            }

            #divRecuperarContrasena .alert-danger label,
            #divRecuperarContrasena .alert-success label {
                margin: 0;
                font-weight: 500;
            }

            #divRecuperarContrasena .no-display {
                display: none;
            }

            #divRecuperarContrasena .btn-success {
                background: transparent;
                color: var(--burdeo);
                border: 1.5px solid var(--burdeo);
                border-radius: 10px;
                padding: 8px 18px;
                font-weight: 600;
                font-size: 14px;
                transition: background 0.2s ease, color 0.2s ease;
            }

            #divRecuperarContrasena .btn-success:hover {
                background: var(--burdeo);
                color: var(--blanco);
            }

            #divRecuperarContrasena .row:last-of-type {
                margin-top: 20px;
            }

            #divRecuperarContrasena a.blanco {
                text-decoration: none;
            }

            @media (max-width: 480px) {
                #divRecuperarContrasena .input-group,
                #divRecuperarContrasena .input-group-lg {
                    display: block;
                }

                #divRecuperarContrasena .form-control {
                    width: 100%;
                    border-radius: 10px;
                    margin-bottom: 12px;
                }

                #divRecuperarContrasena .input-group-btn {
                    display: block;
                    width: 100%;
                }

                #divRecuperarContrasena .input-group-btn .btn.color-principal {
                    width: 100%;
                    border-radius: 10px;
                }
            }
        </style>
    </head>
    <body id="body-recuperar-pass">
        <div class="container">
            <div id="divRecuperarContrasena">
                <div class="panel">
                    <div class="panel-heading color-principal">
                        <h3 class="text-center blanco"> Recuperar Contrase&ntilde;a </h3>
                    </div>

                    <div class="panel-body">
                        <form id="formRecuperarContrasena" name="formRecuperarContrasena" method="post" role="form">
                            <div class="alert alert-info">
                                Ingresa tu <b>Email institucional</b> para recuperar la contrase&ntilde;a
                            </div>
                            <div class="form-group m-b-0">
                                <div class="input-group input-group-lg">
                                    <input type="email" id="correo" name="correo" class="form-control" placeholder="Ingrese email">
                                    <span class="input-group-btn"> <button type="submit" class="btn color-principal blanco waves-effect waves-light">Recuperar</button> </span>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-danger mensajeError no-display">
                                    <label><i class="glyphicon glyphicon-exclamation-sign"></i>&nbsp;El Correo ingresado no es válido</label>
                                </div>
                                <div class="alert alert-success mensajeExito no-display">
                                    <label><i class="glyphicon glyphicon-ok"></i>&nbsp;La nueva contraseña ha sido enviada al correo ingresado</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 ">
                                <a href="index.php" class="blanco"><button class="btn btn-sm btn-success">Volver al inicio</button></a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!--SCRIPTS -->
        <script src="pa/js/jquery.js"></script>
        <script src="pa/js/bootstrap.min.js"></script>       
        <script src="pa/funcionesJS/funcionesRecuperarContrasena.js"></script>       
        <script src="pa/js/jquery.validate.min.js"></script>       
        <script src="pa/js/validacionesCustom.js"></script>       
    </body>
</html>
