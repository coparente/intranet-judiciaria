<?php
// Teste de diagnóstico para produção
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Diagnóstico - Intranet Judiciária</h1>";

// Teste 1: Configurações básicas
echo "<h2>1. Configurações Básicas</h2>";
echo "Ambiente: " . (strpos($_SERVER['HTTP_HOST'], 'sistemas.coparente.top') !== false ? 'Produção' : 'Local') . "<br>";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Teste 2: Estrutura de arquivos
echo "<h2>2. Estrutura de Arquivos</h2>";
echo "app/Controllers/CIRI.php: " . (file_exists('app/Controllers/CIRI.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
echo "app/Controllers/ConsultaProcessos.php: " . (file_exists('app/Controllers/ConsultaProcessos.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
echo "public/assets/dist/css/skin-blue-light.css: " . (file_exists('public/assets/dist/css/skin-blue-light.css') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
echo "public/assets/dist/css/AdminTJGO.css: " . (file_exists('public/assets/dist/css/AdminTJGO.css') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";

// Teste 3: Permissões de arquivo
echo "<h2>3. Permissões de Arquivo</h2>";
if (file_exists('app/Controllers/CIRI.php')) {
    echo "CIRI.php - Permissões: " . substr(sprintf('%o', fileperms('app/Controllers/CIRI.php')), -4) . "<br>";
}
if (file_exists('public/assets/dist/css/AdminTJGO.css')) {
    echo "AdminTJGO.css - Permissões: " . substr(sprintf('%o', fileperms('public/assets/dist/css/AdminTJGO.css')), -4) . "<br>";
}

// Teste 4: Teste de conexão com banco
echo "<h2>4. Teste de Conexão com Banco</h2>";
try {
    require_once 'app/configuracao.php';
    require_once 'app/autoload.php';
    
    $db = new Database();
    $conexao = $db->conectar();
    echo "Conexão com banco: SUCESSO<br>";
    echo "Banco: " . BANCO . "<br>";
    echo "Host: " . HOST . "<br>";
} catch (Exception $e) {
    echo "Erro na conexão: " . $e->getMessage() . "<br>";
}

// Teste 5: Teste de roteamento
echo "<h2>5. Teste de Roteamento</h2>";
echo "URL atual: " . ($_GET['url'] ?? 'vazia') . "<br>";

// Teste 6: Verificar se as classes existem
echo "<h2>6. Classes Disponíveis</h2>";
if (class_exists('CIRI')) {
    echo "Classe CIRI: EXISTE<br>";
} else {
    echo "Classe CIRI: NÃO EXISTE<br>";
}

if (class_exists('ConsultaProcessos')) {
    echo "Classe ConsultaProcessos: EXISTE<br>";
} else {
    echo "Classe ConsultaProcessos: NÃO EXISTE<br>";
}

// Teste 7: Verificar conteúdo do diretório Controllers
echo "<h2>7. Conteúdo do Diretório Controllers</h2>";
if (is_dir('app/Controllers')) {
    $files = scandir('app/Controllers');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo $file . "<br>";
        }
    }
} else {
    echo "Diretório não encontrado<br>";
}

// Teste 8: Teste de URL rewriting
echo "<h2>8. Teste de URL Rewriting</h2>";
echo "Mod_rewrite ativo: " . (in_array('mod_rewrite', apache_get_modules()) ? 'SIM' : 'NÃO') . "<br>";

?> 