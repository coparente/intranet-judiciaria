<?php
ob_start();
session_start();
include 'vendor/autoload.php';
include 'app/configuracao.php';
include 'app/autoload.php';
$db = new Database;

// Verifica se a URL atual é uma rota que retorna JSON/API
$url = $_GET['url'] ?? '';
$rotasAPI = [
    'chat/webhook',
    'chat/carregarNovasMensagens',
    'chat/downloadMidia',
    'chat/enviarMensagemInterativa',
    'chat/consultarStatus',
    'chat/qrCode',
    'agenda/eventos',
    'agenda/detalhesEvento',
    'agenda/moverEvento',
    'agenda/listarCategorias'
];

// Verifica se é uma rota de API (não deve incluir HTML)
$isAPIRoute = false;
foreach ($rotasAPI as $rota) {
    if ($url === $rota || strpos($url, $rota . '/') === 0) {
        $isAPIRoute = true;
        break;
    }
}

// Só inclui head.php se NÃO for rota de API
if (!$isAPIRoute) {
    include 'app/Views/include/head.php';
}

$rotas = new Rota();

// Só inclui linkjs.php se NÃO for rota de API  
if (!$isAPIRoute) {
    include 'app/Views/include/linkjs.php';
}

ob_end_flush();


// ob_start();
// session_start();
// include 'vendor/autoload.php';
// include 'app/configuracao.php';
// include 'app/autoload.php';
// $db = new Database;

// // Verifica se a URL atual é "webhook"
// $url = $_GET['url'] ?? '';
// if ($url !== 'chat/webhook') {
//     include 'app/Views/include/head.php';
// }

// $rotas = new Rota();

// if ($url !== 'chat/webhook') {
//     include 'app/Views/include/linkjs.php';
// }

// ob_end_flush();