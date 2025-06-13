<?php
// Teste específico para controladores que não funcionam
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h1>Teste Específico de Controladores</h1>";

// Simular uma sessão ativa para evitar redirecionamentos
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 'admin';

try {
    require_once 'app/configuracao.php';
    require_once 'app/autoload.php';
    
    echo "<h2>1. Teste do Controller ConsultaProcessos</h2>";
    
    // Testar se pode instanciar o ConsultaProcessos
    try {
        $consultaProcessos = new ConsultaProcessos();
        echo "✅ ConsultaProcessos instanciado com sucesso<br>";
        
        // Testar o método index especificamente
        echo "🔍 Testando método index()...<br>";
        
        // Capturar output para evitar redirecionamentos
        ob_start();
        try {
            $consultaProcessos->index();
            $output = ob_get_contents();
            echo "✅ Método index() executado sem erros<br>";
        } catch (Exception $e) {
            echo "❌ Erro no método index(): " . $e->getMessage() . "<br>";
        } finally {
            ob_end_clean();
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar ConsultaProcessos: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>2. Teste do Controller Projudi (que funciona)</h2>";
    
    try {
        $projudi = new Projudi();
        echo "✅ Projudi instanciado com sucesso<br>";
        
        // Testar o método index
        ob_start();
        try {
            $projudi->index();
            $output = ob_get_contents();
            echo "✅ Método index() do Projudi executado sem erros<br>";
        } catch (Exception $e) {
            echo "❌ Erro no método index() do Projudi: " . $e->getMessage() . "<br>";
        } finally {
            ob_end_clean();
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar Projudi: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>3. Teste do Controller CIRI</h2>";
    
    try {
        $ciri = new CIRI();
        echo "✅ CIRI instanciado com sucesso<br>";
        
        // Testar o método listar
        ob_start();
        try {
            $ciri->listar();
            $output = ob_get_contents();
            echo "✅ Método listar() do CIRI executado sem erros<br>";
        } catch (Exception $e) {
            echo "❌ Erro no método listar() do CIRI: " . $e->getMessage() . "<br>";
        } finally {
            ob_end_clean();
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar CIRI: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>4. Teste do Middleware Especificamente</h2>";
    
    try {
        // Testar verificação de permissão que ambos usam
        $resultado = Middleware::verificarPermissao(5);
        echo "✅ Middleware::verificarPermissao(5) executado: " . ($resultado ? 'TRUE' : 'FALSE') . "<br>";
    } catch (Exception $e) {
        echo "❌ Erro no Middleware: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>5. Teste das Views</h2>";
    
    // Verificar se as views existem
    echo "View consulta_processos/index: " . (file_exists('app/Views/consulta_processos/index.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
    echo "View projudi/index: " . (file_exists('app/Views/projudi/index.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
    echo "View ciri/listar: " . (file_exists('app/Views/ciri/listar.php') ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
    
    echo "<hr>";
    echo "<h2>6. Teste dos Models</h2>";
    
    try {
        $usuarioModel = new UsuarioModel();
        echo "✅ UsuarioModel instanciado com sucesso<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar UsuarioModel: " . $e->getMessage() . "<br>";
    }
    
    try {
        $moduloModel = new ModuloModel();
        echo "✅ ModuloModel instanciado com sucesso<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao instanciar ModuloModel: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>7. Informações de Sessão</h2>";
    echo "SESSION usuario_id: " . ($_SESSION['usuario_id'] ?? 'NÃO DEFINIDO') . "<br>";
    echo "SESSION usuario_perfil: " . ($_SESSION['usuario_perfil'] ?? 'NÃO DEFINIDO') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

?> 