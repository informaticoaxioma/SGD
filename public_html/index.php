<!DOCTYPE html>
<?php require_once 'negocio/login/procesarLogin.php'; ?>


<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">  
        <link rel="SHORTCUT ICON" href="pa/media/LogoAxioma.jpg" /> 
        <link rel="stylesheet" href="pa/css/bootstrap.min.css">
        <link rel="stylesheet" href="pa/css/cssLogin.css">        
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">  
        <link href="pa/media/LogoAxioma.jpg" rel="SHORTCUT ICON">
        <title>Login</title>
         <style>
        /* ===== Paleta Burdeo / Fondo Blanco ===== */
        :root {
            --burdeo-900: #4a1e24;
            --burdeo-800: #5c252c;
            --burdeo-700: #722f37;
            --burdeo-600: #8a3a44;
            --burdeo-500: #a5464f;
            --burdeo-100: #f4e8ea;
            --burdeo-50:  #faf3f4;
            --blanco:     #ffffff;
            --gris-fondo: #f7f7f8;
            --texto:      #2b2b2b;
            --texto-mut:  #6b6b6b;
            --borde:      #e6dcde;
            --error-bg:   #fdecee;
            --error-bd:   #e4b4bb;
            --error-tx:   #8a1f2b;
            --sombra:     0 10px 40px rgba(74, 30, 36, 0.12);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--texto);
            background-color: var(--gris-fondo);
        }

      body {
          margin: 0;
          min-height: 100vh;
          background-image: url('LoginSGD.jpeg');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
          background-attachment: fixed;
      }

        /* ===== Header ===== */
        #head { width: 100%; }


        /* ===== Contenedor central ===== */
        .container {
            flex: 1;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        #divLogin {
            width: 100%;
            max-width: 420px;
            background: var(--blanco);
            border: 1px solid var(--borde);
            border-radius: 16px;
            box-shadow: var(--sombra);
            padding: 40px 36px 36px;
        }

        /* Marca / icono superior de la tarjeta */
        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
        }

        .login-brand .icono {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            background: var(--burdeo-700);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .login-brand .icono svg {
            width: 30px;
            height: 30px;
            stroke: var(--blanco);
        }

        .login-brand h2 {
            font-size: 2.15rem;
            font-weight: 600;
            color: var(--burdeo-800);
        }

        .login-brand p {
            font-size: 0.85rem;
            color: var(--texto-mut);
            margin-top: 4px;
        }

        /* ===== Formulario ===== */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--texto);
            margin-bottom: 7px;
        }

        .input-wrap {
            font-size: 1.2rem;
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            stroke: var(--texto-mut);
            pointer-events: none;
        }

        /* Mantiene .form-control y .bordeAxioma del código original */
        .form-control {
            width: 100%;
            padding: 13px 14px 13px 42px;
            font-size: 1.4rem;
            color: var(--texto);
            background: var(--gris-fondo);
            border: 1.5px solid var(--borde);
            border-radius: 10px;
            outline: none;
            transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
        }

        .form-control::placeholder { 
        font-size: 1.4rem;
        color: #a99ea1; }

        .form-control:focus,
        .bordeAxioma:focus {
            border-color: var(--burdeo-600);
            background: var(--blanco);
            box-shadow: 0 0 0 4px var(--burdeo-100);
        }

        /* ===== Link recuperar contraseña ===== */
        #linkRecuperarContrasena {
            display: inline-block;
            font-size: 1rem;
            color: var(--burdeo-700);
            text-decoration: none;
            font-weight: 500;
            transition: color .15s ease;
        }

        #linkRecuperarContrasena:hover {
            color: var(--burdeo-500);
            text-decoration: underline;
        }

        .row-recuperar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        /* ===== Botón ===== */
        /* Mantiene .btn .btnApp .btn-lg del código original */
        #btnIngresar,
        .btnApp {
            width: 100%;
            padding: 14px 16px;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: var(--blanco);
            background: var(--burdeo-700);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background .18s ease, transform .08s ease, box-shadow .18s ease;
        }

        #btnIngresar:hover,
        .btnApp:hover {
            background: var(--burdeo-800);
            box-shadow: 0 6px 18px rgba(114, 47, 55, 0.32);
        }

        #btnIngresar:active,
        .btnApp:active { transform: translateY(1px); }

        /* ===== Alerta de error (compatible con Bootstrap) ===== */
        .alert {
            margin-top: 20px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.88rem;
            text-align: center;
        }

        .alert-danger {
            background: var(--error-bg);
            border: 1px solid var(--error-bd);
            color: var(--error-tx);
        }

        /* ===== Footer ===== */
        .footer {
            background: var(--burdeo-900);
            padding: 18px 24px;
            text-align: center;
        }

        .footer h2 {
            color: var(--burdeo-100);
            font-weight: 500;
            letter-spacing: 0.08em;
        }

        @media (max-width: 480px) {
            #divLogin { padding: 32px 22px 28px; }
            #headTitulo h1 { font-size: 1rem; }
        }
    </style>        
    </head>
    <body id="bodyLogin">

    <div class="container">
        <div id="divLogin">
            <div class="login-brand">
                <div class="icono">
                    <img src="logoAxioma.jpg" alt="Logo Axioma" class="logo">
                </div>
                <h1>Sistema de Gestión Documental</h1>
                <h2>SGD</h2>
            </div>

            <form id="formLogin" action="index.php" method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" name="nombreUsuario" id="nombreUsuario" placeholder="Ingrese nombre usuario" class="form-control bordeAxioma">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" name="contrasena" id="contrasena" placeholder="************" class="form-control bordeAxioma">
                    </div>
                </div>

                <div class="row-recuperar">
                    <a id="linkRecuperarContrasena" href="recuperarContrasena.php">¿Olvidaste Tu contraseña?</a>
                </div>

                <div class="form-group">
                    <input type="submit" id="btnIngresar" name="btnIngresar" value="Ingresar" class="btn btnApp btn-lg">
                </div>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
        <!--SCRIPTS -->
        <script src="pa/js/jquery.js"></script>
        <script src="pa/js/bootstrap.min.js"></script>       
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
        

    </body>
</html>
