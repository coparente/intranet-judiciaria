<?php
// Teste específico para verificar o problema com nav.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 'admin';

echo "<h1>Teste do Nav.php - Raiz do Problema</h1>";

try {
    require_once 'app/configuracao.php';
    require_once 'app/autoload.php';
    
    echo "<h2>1. Verificar se nav.php existe</h2>";
    $nav_path = 'app/Views/include/nav.php';
    echo "Caminho: $nav_path<br>";
    echo "Existe: " . (file_exists($nav_path) ? 'SIM' : 'NÃO') . "<br>";
    
    if (file_exists($nav_path)) {
        echo "Permissões: " . substr(sprintf('%o', fileperms($nav_path)), -4) . "<br>";
        echo "Tamanho: " . filesize($nav_path) . " bytes<br>";
        echo "Legível: " . (is_readable($nav_path) ? 'SIM' : 'NÃO') . "<br>";
    }
    
    echo "<h2>2. Testar include do nav.php diretamente</h2>";
    
    if (file_exists($nav_path)) {
        echo "Tentando incluir nav.php...<br>";
        try {
            ob_start();
            include $nav_path;
            $output = ob_get_contents();
            ob_end_clean();
            echo "✅ Nav.php incluído com sucesso!<br>";
            echo "Tamanho do output: " . strlen($output) . " bytes<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao incluir nav.php: " . $e->getMessage() . "<br>";
        } catch (Error $e) {
            echo "❌ Erro fatal no nav.php: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2>3. Verificar dependências do nav.php</h2>";
    
    // Ler o conteúdo do nav.php para ver o que ele inclui
    if (file_exists($nav_path)) {
        $conteudo_nav = file_get_contents($nav_path, false, null, 0, 500);
        echo "Primeiros 500 caracteres do nav.php:<br>";
        echo "<pre>" . htmlspecialchars($conteudo_nav) . "</pre>";
    }
    
    echo "<h2>4. Simular exatamente o que acontece na view</h2>";
    
    // Simular o que acontece quando Controllers carrega uma view
    echo "Simulando carregamento de consulta_processos/index...<br>";
    
    // Preparar dados como Controllers faria
    $dados = [
        'tituloPagina' => 'Consulta de Processos',
        'numeroProcesso' => '',
        'resultado' => null,
        'erro' => null
    ];
    
    // Criar instância de Controllers para ter acesso ao moduloModel
    $controller = new ConsultaProcessos();
    
    echo "Controller criado...<br>";
    
    // Tentar executar exatamente como Controllers faz
    $arquivo = './app/Views/consulta_processos/index.php';
    echo "Arquivo: $arquivo<br>";
    
    if(file_exists($arquivo)) {
        echo "Arquivo existe, tentando require_once...<br>";
        try {
            ob_start();
            require_once $arquivo;
            $output = ob_get_contents();
            ob_end_clean();
            echo "✅ View carregada com sucesso!<br>";
            echo "Tamanho do output: " . strlen($output) . " bytes<br>";
        } catch (Exception $e) {
            echo "❌ Erro durante require_once: " . $e->getMessage() . "<br>";
            echo "Stack trace: " . $e->getTraceAsString() . "<br>";
        } catch (Error $e) {
            echo "❌ Erro fatal durante require_once: " . $e->getMessage() . "<br>";
            echo "Arquivo: " . $e->getFile() . "<br>";
            echo "Linha: " . $e->getLine() . "<br>";
        }
    } else {
        echo "❌ Arquivo não existe<br>";
    }
    
    echo "<h2>5. Verificar outros includes</h2>";
    
    $includes_para_testar = [
        'app/Views/include/head.php',
        'app/Views/include/linkjs.php',
        'app/Views/include/footer.php'
    ];
    
    foreach ($includes_para_testar as $include) {
        echo "Testando $include: " . (file_exists($include) ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
} catch (Error $e) {
    echo "❌ Erro fatal geral: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
}

echo "<br><strong>Teste concluído!</strong>";
?> 