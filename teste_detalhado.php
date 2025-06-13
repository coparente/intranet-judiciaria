<?php
// Teste detalhado para rastrear onde para a execução
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h1>Teste Detalhado - Rastreamento de Execução</h1>";

// Simular uma sessão ativa
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 'admin';

try {
    require_once 'app/configuracao.php';
    require_once 'app/autoload.php';
    
    echo "<h2>Teste 1: Verificar método view() da classe Controllers</h2>";
    
    // Testar se a classe Controllers base tem problemas
    try {
        class TestController extends Controllers {
            public function testar() {
                echo "Dentro do método testar<br>";
                $dados = ['teste' => 'valor'];
                echo "Antes da chamada view()<br>";
                $this->view('teste/inexistente', $dados);
                echo "Depois da chamada view()<br>";
            }
        }
        
        $test = new TestController();
        echo "Instanciado TestController<br>";
        $test->testar();
        echo "Método testar executado completamente<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro no TestController: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>Teste 2: Verificar classe Controllers base</h2>";
    
    // Verificar se Controllers existe e funciona
    if (class_exists('Controllers')) {
        echo "✅ Classe Controllers existe<br>";
        
        // Verificar se o método view existe
        if (method_exists('Controllers', 'view')) {
            echo "✅ Método view() existe na classe Controllers<br>";
        } else {
            echo "❌ Método view() NÃO existe na classe Controllers<br>";
        }
        
        if (method_exists('Controllers', 'model')) {
            echo "✅ Método model() existe na classe Controllers<br>";
        } else {
            echo "❌ Método model() NÃO existe na classe Controllers<br>";
        }
    } else {
        echo "❌ Classe Controllers NÃO existe<br>";
    }
    
    echo "<hr>";
    echo "<h2>Teste 3: Simular método index do ConsultaProcessos step by step</h2>";
    
    try {
        echo "1. Criando instância...<br>";
        $consulta = new ConsultaProcessos();
        echo "2. Instância criada com sucesso<br>";
        
        echo "3. Testando Middleware::verificarPermissao(5)...<br>";
        $permissao = Middleware::verificarPermissao(5);
        echo "4. Middleware executado: " . ($permissao ? 'TRUE' : 'FALSE') . "<br>";
        
        echo "5. Preparando dados para view...<br>";
        $dados = [
            'tituloPagina' => 'Consulta de Processos',
            'numeroProcesso' => '',
            'resultado' => null,
            'erro' => null
        ];
        echo "6. Dados preparados<br>";
        
        echo "7. Testando chamada view()...<br>";
        $consulta->view('consulta_processos/index', $dados);
        echo "8. View executada com sucesso<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro no passo a passo: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>Teste 4: Verificar se view chama include/require</h2>";
    
    echo "Arquivo consulta_processos/index.php existe: " . (file_exists('app/Views/consulta_processos/index.php') ? 'SIM' : 'NÃO') . "<br>";
    
    if (file_exists('app/Views/consulta_processos/index.php')) {
        echo "Tamanho do arquivo: " . filesize('app/Views/consulta_processos/index.php') . " bytes<br>";
        echo "Permissões: " . substr(sprintf('%o', fileperms('app/Views/consulta_processos/index.php')), -4) . "<br>";
        
        // Tentar ler as primeiras linhas para verificar se há erros de sintaxe
        $conteudo = file_get_contents('app/Views/consulta_processos/index.php', false, null, 0, 200);
        echo "Primeiras linhas:<br><pre>" . htmlspecialchars($conteudo) . "</pre>";
    }
    
    echo "<hr>";
    echo "<h2>Teste 5: Comparar com Projudi que funciona</h2>";
    
    echo "Arquivo projudi/index.php existe: " . (file_exists('app/Views/projudi/index.php') ? 'SIM' : 'NÃO') . "<br>";
    
    if (file_exists('app/Views/projudi/index.php')) {
        echo "Tamanho do arquivo: " . filesize('app/Views/projudi/index.php') . " bytes<br>";
        echo "Permissões: " . substr(sprintf('%o', fileperms('app/Views/projudi/index.php')), -4) . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>Teste 6: Verificar logs de erro</h2>";
    
    // Verificar se há logs de erro
    if (function_exists('error_get_last')) {
        $ultimoErro = error_get_last();
        if ($ultimoErro) {
            echo "Último erro: " . print_r($ultimoErro, true) . "<br>";
        } else {
            echo "Nenhum erro registrado<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<br><strong>Teste concluído!</strong>";
?> 