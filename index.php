<?php
session_start();
include 'vendor/autoload.php';
include 'app/configuracao.php';
include 'app/autoload.php';
$db = new Database;

include 'app/Views/include/head.php';
$rotas = new Rota();
include 'app/Views/include/linkjs.php';
