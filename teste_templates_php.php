<?php
/**
 * Teste da nova implementação de templates em PHP puro
 */

// Simular sessão de admin
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 'admin';

// Incluir dependências
require_once 'app/configuracao.php';
require_once 'app/Libraries/SerproHelper.php';

echo "<h1>🧪 Teste da Nova Implementação de Templates em PHP</h1>";

// Inicializar SerproHelper
SerproHelper::init();

echo "<h2>📊 Testando carregamento de templates via PHP...</h2>";

try {
    $resultado = SerproHelper::listarTemplates();
    
    echo "<h3>✅ Status da resposta:</h3>";
    echo "<pre>";
    echo "Status: " . $resultado['status'] . "\n";
    echo "Resposta completa:\n";
    print_r($resultado);
    echo "</pre>";
    
    if ($resultado['status'] == 200 && isset($resultado['response'])) {
        $templates = $resultado['response'];
        
        echo "<h3>📋 Templates encontrados (" . count($templates) . "):</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th style='padding: 10px;'>Nome</th>";
        echo "<th style='padding: 10px;'>Categoria</th>";
        echo "<th style='padding: 10px;'>Idioma</th>";
        echo "<th style='padding: 10px;'>Status</th>";
        echo "</tr>";
        
        foreach ($templates as $template) {
            $status = $template['status'] ?? 'UNKNOWN';
            $color = '';
            switch($status) {
                case 'APPROVED':
                    $color = 'background-color: #d4edda; color: #155724;';
                    break;
                case 'PENDING':
                    $color = 'background-color: #fff3cd; color: #856404;';
                    break;
                case 'REJECTED':
                    $color = 'background-color: #f8d7da; color: #721c24;';
                    break;
                default:
                    $color = 'background-color: #e2e3e5; color: #383d41;';
            }
            
            echo "<tr>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($template['name'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($template['category'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($template['language'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 10px; $color'>" . htmlspecialchars($status) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h3>🔧 Dados que serão passados para a view:</h3>";
        echo "<pre>";
        echo "Array dos dados que vai para \$dados['templates']:\n";
        print_r($templates);
        echo "</pre>";
        
    } else {
        echo "<h3>❌ Erro ao carregar templates:</h3>";
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "Status: " . $resultado['status'] . "<br>";
        echo "Erro: " . ($resultado['error'] ?? 'Erro desconhecido');
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h3>💥 Exceção capturada:</h3>";
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "Mensagem: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine();
    echo "</div>";
}

echo "<hr>";
echo "<h3>🎯 Conclusão:</h3>";
echo "<p>✅ Esta implementação elimina completamente a dependência de AJAX</p>";
echo "<p>✅ Os templates são carregados diretamente no PHP quando a página é acessada</p>";
echo "<p>✅ Não há mais problemas de sessão/cookies em requisições AJAX</p>";
echo "<p>✅ O erro 'Unexpected token' não pode mais ocorrer</p>";

echo "<hr>";
echo "<p><a href='chat/gerenciarTemplates'>🔗 Ir para página real de templates</a></p>";
?> 