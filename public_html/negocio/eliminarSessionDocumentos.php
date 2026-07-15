<?php

session_start();


//sessiones anteriores por si se cerro de forma inesperada
unset($_SESSION['documentosRelacionadosSalida']);
unset($_SESSION['documentosRelacionadosEntrada']);
unset($_SESSION['documentosRelacionadosHitos']);
unset($_SESSION['documentosRelacionadosCargaM']);
unset($_SESSION['documentosRelacionadosDetalle']);


