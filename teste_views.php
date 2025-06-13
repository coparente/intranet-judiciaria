<?php
// Teste específico para views
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Views - Verificação de Caminhos</h1>";

// Teste 1: Verificar caminhos absolutos
echo "<h2>1. Verificação de Caminhos</h2>";
echo "Diretório atual: " . getcwd() . "<br>";
echo "APPROOT definido: " . (defined('APPROOT') ? APPROOT : 'NÃO DEFINIDO') . "<br>";
echo "APP definido: " . (defined('APP') ? APP : 'NÃO DEFINIDO') . "<br>";

// Teste 2: Caminhos que a classe Controllers usa
echo "<h2>2. Caminhos que Controllers usa</h2>";

$views_para_testar = [
    'consulta_processos/index',
    'ciri/listar', 
    'projudi/index'
];

foreach ($views_para_testar as $view) {
    echo "<h3>Testando: $view</h3>";
    
    // Caminho exato que Controllers usa
    $arquivo = ('./app/Views/'.$view.'.php');
    echo "Caminho completo: " . $arquivo . "<br>";
    echo "Arquivo existe: " . (file_exists($arquivo) ? 'SIM' : 'NÃO') . "<br>";
    
    // Caminho absoluto
    $absoluto = realpath($arquivo);
    echo "Caminho absoluto: " . ($absoluto ?: 'NÃO ENCONTRADO') . "<br>";
    
    // Verificar permissões se existe
    if (file_exists($arquivo)) {
        echo "Permissões: " . substr(sprintf('%o', fileperms($arquivo)), -4) . "<br>";
        echo "Tamanho: " . filesize($arquivo) . " bytes<br>";
        echo "Legível: " . (is_readable($arquivo) ? 'SIM' : 'NÃO') . "<br>";
    }
    
    echo "<br>";
}

// Teste 3: Verificar se é problema de case sensitivity
echo "<h2>3. Teste de Case Sensitivity</h2>";

$variantes_consulta = [
    './app/Views/consulta_processos/index.php',
    './app/Views/Consulta_processos/index.php',
    './app/Views/CONSULTA_PROCESSOS/index.php',
    './app/Views/consultaprocessos/index.php'
];

foreach ($variantes_consulta as $variante) {
    echo "$variante: " . (file_exists($variante) ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
}

// Teste 4: Listar conteúdo real dos diretórios
echo "<h2>4. Conteúdo Real dos Diretórios</h2>";

if (is_dir('./app/Views/consulta_processos')) {
    echo "<strong>Conteúdo de ./app/Views/consulta_processos/:</strong><br>";
    $files = scandir('./app/Views/consulta_processos');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file<br>";
        }
    }
} else {
    echo "Diretório ./app/Views/consulta_processos/ NÃO EXISTE<br>";
}

if (is_dir('./app/Views/ciri')) {
    echo "<br><strong>Conteúdo de ./app/Views/ciri/:</strong><br>";
    $files = scandir('./app/Views/ciri');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file<br>";
        }
    }
} else {
    echo "Diretório ./app/Views/ciri/ NÃO EXISTE<br>";
}

// Teste 5: Simular exatamente o que Controllers faz
echo "<h2>5. Simulação Exata da Classe Controllers</h2>";

function testar_view($view) {
    echo "Testando view: $view<br>";
    $arquivo = ('./app/Views/'.$view.'.php');
    echo "Caminho: $arquivo<br>";
    
    if(file_exists($arquivo)) {
        echo "✅ Arquivo encontrado!<br>";
        
        // Tentar ler o arquivo para ver se há problemas
        $conteudo = @file_get_contents($arquivo, false, null, 0, 100);
        if ($conteudo !== false) {
            echo "✅ Arquivo é legível<br>";
            echo "Primeiros caracteres: " . htmlspecialchars(substr($conteudo, 0, 50)) . "...<br>";
        } else {
            echo "❌ Erro ao ler o arquivo<br>";
        }
    } else {
        echo "❌ Arquivo NÃO encontrado - mesma mensagem que Controllers<br>";
    }
    echo "<br>";
}

testar_view('consulta_processos/index');
testar_view('ciri/listar');
testar_view('projudi/index');

?> 