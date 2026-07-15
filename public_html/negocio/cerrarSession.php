<?php

session_start();
unset($_SESSION['usuario']);
unset($sessionUsuario);
session_destroy();
header("location:../index.php");
