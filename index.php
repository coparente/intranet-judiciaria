<?php
ob_start();
session_start();
include 'vendor/autoload.php';
include 'app/configuracao.php';
include 'app/autoload.php';
$db = new Database;

// Verifica se a URL atual é "webhook"
$url = $_GET['url'] ?? '';
if ($url !== 'chat/webhook') {
    include 'app/Views/include/head.php';
}

$rotas = new Rota();

if ($url !== 'chat/webhook') {
    include 'app/Views/include/linkjs.php';
}

ob_end_flush();
