<?php
// Teste do sistema de roteamento - verdadeira causa dos erros 302
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste do Sistema de Roteamento</h1>";

// Simular diferentes cenários de URL
$urls_para_testar = [
    'consultaprocessos/index',
    'consulta_processos/index',  // Note o underscore vs sem underscore
    'ConsultaProcessos/index',
    'ciri/listar',
    'CIRI/listar',
    'projudi/index'
];

echo "<h2>1. Teste de Mapeamento de URLs</h2>";

foreach ($urls_para_testar as $url) {
    echo "<h3>Testando URL: $url</h3>";
    
    // Simular o que a classe Rota faz
    $url_parts = explode('/', $url);
    $controlador = ucwords($url_parts[0] ?? '');
    $metodo = $url_parts[1] ?? 'index';
    
    echo "Controller esperado: $controlador<br>";
    echo "Método esperado: $metodo<br>";
    
    // Verificar se o arquivo do controller existe
    $controller_file = './app/Controllers/' . $controlador . '.php';
    echo "Arquivo controller: $controller_file<br>";
    echo "Existe: " . (file_exists($controller_file) ? 'SIM' : 'NÃO') . "<br>";
    
    if (file_exists($controller_file)) {
        // Tentar incluir e verificar se a classe existe
        try {
            require_once $controller_file;
            if (class_exists($controlador)) {
                echo "✅ Classe $controlador existe<br>";
                
                // Verificar se o método existe
                if (method_exists($controlador, $metodo)) {
                    echo "✅ Método $metodo existe<br>";
                } else {
                    echo "❌ Método $metodo NÃO existe<br>";
                }
            } else {
                echo "❌ Classe $controlador NÃO existe<br>";
            }
        } catch (Exception $e) {
            echo "❌ Erro ao incluir controller: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Controller não encontrado - SERIA REDIRECIONADO PARA ERRO<br>";
    }
    
    echo "<br>";
}

echo "<h2>2. Teste da Lógica de Roteamento Exata</h2>";

// Simular exatamente o que a classe Rota faz
function testar_rota($url_string) {
    echo "<h3>Simulando rota: $url_string</h3>";
    
    // Simular $_GET['url']
    $_GET['url'] = $url_string;
    
    // Código similar ao da classe Rota
    $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
    if (isset($url)) {
        $url = trim(rtrim($url, '/'));
        $url = explode('/', $url);
    } else {
        $url = [];
    }
    
    echo "URL processada: " . print_r($url, true) . "<br>";
    
    if (empty($url)) {
        $controlador = CONTROLLER; // Padrão: Login
    } else {
        $controlador = ucwords($url[0]);
        echo "Controller determinado: $controlador<br>";
        
        if (file_exists('./app/Controllers/' . $controlador . '.php')) {
            echo "✅ Controller encontrado<br>";
            
            // Verificar método
            if (isset($url[1])) {
                $metodo = $url[1];
                echo "Método determinado: $metodo<br>";
                
                // Simular verificação de método
                require_once './app/Controllers/' . $controlador . '.php';
                if (method_exists($controlador, $metodo)) {
                    echo "✅ Método encontrado<br>";
                } else {
                    echo "❌ Método NÃO encontrado - REDIRECIONARIA PARA ERRO<br>";
                }
            } else {
                echo "Método padrão: " . METODO . "<br>";
            }
        } else {
            echo "❌ Controller NÃO encontrado - REDIRECIONARIA PARA ERRO<br>";
        }
    }
    
    echo "<br>";
}

// Testar as URLs problemáticas
testar_rota('consultaprocessos/index');
testar_rota('ciri/listar');
testar_rota('projudi/index');

echo "<h2>3. Verificar Constantes de Roteamento</h2>";
try {
    require_once 'app/configuracao.php';
    echo "CONTROLLER padrão: " . (defined('CONTROLLER') ? CONTROLLER : 'NÃO DEFINIDO') . "<br>";
    echo "METODO padrão: " . (defined('METODO') ? METODO : 'NÃO DEFINIDO') . "<br>";
} catch (Exception $e) {
    echo "Erro ao carregar configuração: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Teste com Nomes Reais dos Controllers</h2>";

echo "Arquivo ConsultaProcessos.php: " . (file_exists('./app/Controllers/ConsultaProcessos.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
echo "Arquivo Consultaprocessos.php: " . (file_exists('./app/Controllers/Consultaprocessos.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
echo "Arquivo consultaprocessos.php: " . (file_exists('./app/Controllers/consultaprocessos.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";

echo "<br><strong>Teste concluído!</strong>";
?> 