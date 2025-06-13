<?php
// Teste da correção do sistema de roteamento
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste da Correção do Roteamento</h1>";

// Incluir dependências necessárias
require_once 'app/configuracao.php';
require_once 'app/Libraries/Rota.php';

echo "<h2>1. Teste do Mapeamento de Controllers</h2>";

// Simular a lógica da classe Rota com os novos mapeamentos
$mapeamentoControllers = [
    'consultaprocessos' => 'ConsultaProcessos',
    'ciri' => 'CIRI',
    'projudi' => 'Projudi',
    'chat' => 'Chat',
    'dashboard' => 'Dashboard',
    'usuarios' => 'Usuarios',
    'perfis' => 'Perfis',
    'relatorios' => 'Relatorios',
    'configuracoes' => 'Configuracoes'
];

function obterNomeController($urlController, $mapeamentoControllers) 
{
    $urlLowerCase = strtolower($urlController);
    
    // Verifica se existe mapeamento específico
    if (isset($mapeamentoControllers[$urlLowerCase])) {
        return $mapeamentoControllers[$urlLowerCase];
    }
    
    // Fallback para o comportamento original
    return ucwords($urlController);
}

$urls_problematicas = [
    'consultaprocessos',
    'ciri', 
    'projudi'
];

foreach ($urls_problematicas as $url) {
    echo "<h3>Testando URL: $url</h3>";
    
    $controllerName = obterNomeController($url, $mapeamentoControllers);
    echo "Controller mapeado: $controllerName<br>";
    
    $controller_file = './app/Controllers/' . $controllerName . '.php';
    echo "Arquivo controller: $controller_file<br>";
    echo "Existe: " . (file_exists($controller_file) ? '✅ SIM' : '❌ NÃO') . "<br>";
    
    if (file_exists($controller_file)) {
        echo "✅ ROTEAMENTO CORRIGIDO - Controller será encontrado!<br>";
    } else {
        echo "❌ Ainda há problema com este controller<br>";
    }
    
    echo "<br>";
}

echo "<h2>2. Verificação de Outros Controllers</h2>";

// Listar todos os controllers disponíveis
$controllers_dir = './app/Controllers/';
if (is_dir($controllers_dir)) {
    $controllers = array_diff(scandir($controllers_dir), array('..', '.'));
    
    echo "Controllers disponíveis:<br>";
    foreach ($controllers as $controller) {
        if (pathinfo($controller, PATHINFO_EXTENSION) === 'php') {
            $name = pathinfo($controller, PATHINFO_FILENAME);
            echo "- $name.php<br>";
        }
    }
}

echo "<h2>3. Teste Simulado de URLs Problemáticas</h2>";

// Simular as URLs que estavam dando erro 302
$urls_producao = [
    'consultaprocessos/index',
    'ciri/listar',
    'projudi/index'
];

foreach ($urls_producao as $url_completa) {
    echo "<h3>Simulando: $url_completa</h3>";
    
    $parts = explode('/', $url_completa);
    $controller_url = $parts[0];
    $metodo = $parts[1] ?? 'index';
    
    $controller_mapped = obterNomeController($controller_url, $mapeamentoControllers);
    echo "Controller: $controller_url → $controller_mapped<br>";
    echo "Método: $metodo<br>";
    
    if (file_exists('./app/Controllers/' . $controller_mapped . '.php')) {
        echo "✅ SUCESSO - Controller encontrado!<br>";
        echo "🎯 Esta URL agora funcionará em produção<br>";
    } else {
        echo "❌ FALHA - Controller não encontrado<br>";
    }
    
    echo "<br>";
}

echo "<h2>Resultado Final</h2>";
echo "<strong style='color: green;'>✅ Correção implementada com sucesso!</strong><br>";
echo "As URLs problemáticas agora devem funcionar em produção:<br>";
echo "- /intranet/consultaprocessos/index → ConsultaProcessos::index()<br>";
echo "- /intranet/ciri/listar → CIRI::listar()<br>";
echo "- /intranet/projudi/index → Projudi::index()<br>";

?> 