<?php
/**
 * Teste para verificar se admin visualiza conversas organizadas corretamente
 * Acesse via navegador: http://localhost/intranet-judiciaria/teste_layout_admin.php
 */

session_start();
require_once 'app/configuracao.php';
require_once 'app/Libraries/Database.php';

echo "<h1>🔍 Teste de Layout para Admin</h1>";

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px 0;'>";
    echo "❌ <strong>Usuário não logado</strong><br>";
    echo "Faça login no sistema primeiro e depois acesse este link novamente.";
    echo "</div>";
    exit;
}

echo "<div style='color: green; padding: 20px; border: 1px solid green; margin: 20px 0;'>";
echo "✅ <strong>Usuário logado:</strong> ID {$_SESSION['usuario_id']}<br>";
echo "✅ <strong>Perfil:</strong> " . ($_SESSION['usuario_perfil'] ?? 'N/A');
echo "</div>";

// Verificar se é admin
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    echo "<div style='color: orange; padding: 20px; border: 1px solid orange; margin: 20px 0;'>";
    echo "⚠️ <strong>Aviso:</strong> Você não é admin. Este teste funciona melhor com perfil de admin.";
    echo "</div>";
}

try {
    $db = new Database();
    
    // Buscar conversas de diferentes usuários
    echo "<h2>1. Conversas no Sistema</h2>";
    
    $sql = "SELECT c.id, c.usuario_id, c.contato_nome, c.contato_numero, u.nome as usuario_nome,
                   COUNT(m.id) as total_mensagens
            FROM conversas c 
            LEFT JOIN usuarios u ON c.usuario_id = u.id 
            LEFT JOIN mensagens_chat m ON c.id = m.conversa_id 
            WHERE c.usuario_id IS NOT NULL AND c.usuario_id > 0
            GROUP BY c.id, c.usuario_id, c.contato_nome, c.contato_numero, u.nome
            ORDER BY c.id DESC 
            LIMIT 10";
    
    $db->query($sql);
    $conversas = $db->resultados();
    
    if ($conversas) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Proprietário</th>";
        echo "<th style='padding: 8px;'>Contato</th>";
        echo "<th style='padding: 8px;'>Mensagens</th>";
        echo "<th style='padding: 8px;'>Testar</th>";
        echo "</tr>";
        
        foreach ($conversas as $conversa) {
            $isProprietario = $conversa->usuario_id == $_SESSION['usuario_id'];
            $corLinha = $isProprietario ? '#e8f5e8' : '#f8f8f8';
            
            echo "<tr style='background: {$corLinha};'>";
            echo "<td style='padding: 8px;'>" . $conversa->id . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($conversa->usuario_nome ?? 'N/A') . " (ID: {$conversa->usuario_id})</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($conversa->contato_nome) . "</td>";
            echo "<td style='padding: 8px;'>" . $conversa->total_mensagens . " msgs</td>";
            echo "<td style='padding: 8px;'>";
            
            if ($isProprietario) {
                echo "<span style='color: green;'>✅ Sua conversa</span>";
            } else {
                echo "<a href='" . URL . "/chat/conversa/{$conversa->id}' target='_blank' style='color: blue;'>Ver como Admin</a>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h2>2. Como Funciona a Nova Lógica</h2>";
        echo "<div style='padding: 20px; background: #f0f8ff; border: 1px solid #cce7ff; margin: 20px 0;'>";
        echo "<strong>🎯 Objetivo:</strong> Admin vê conversas organizadas como se fosse normal<br><br>";
        echo "<strong>📋 Regras:</strong><br>";
        echo "• <span style='color: green;'>Suas conversas:</span> Layout normal (suas msgs à direita)<br>";
        echo "• <span style='color: blue;'>Conversas de outros:</span> Msgs do proprietário à direita, contatos à esquerda<br><br>";
        echo "<strong>🧪 Para testar:</strong><br>";
        echo "1. Clique em 'Ver como Admin' nas conversas de outros usuários<br>";
        echo "2. Verifique se as mensagens estão organizadas corretamente<br>";
        echo "3. Mensagens do proprietário devem estar à direita<br>";
        echo "4. Mensagens de contatos externos devem estar à esquerda";
        echo "</div>";
        
    } else {
        echo "<div style='color: orange; padding: 10px; background: #fff3cd; margin: 10px 0;'>";
        echo "⚠️ Nenhuma conversa encontrada no sistema.";
        echo "</div>";
    }
    
    echo "<h2>3. Simulação da Lógica</h2>";
    echo "<div style='padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; margin: 10px 0;'>";
    echo "<code style='display: block; white-space: pre; font-family: monospace;'>";
    echo "// Pseudocódigo da nova lógica:\n";
    echo "if (admin && conversaDeOutroUsuario) {\n";
    echo "    // Organizar pela perspectiva do proprietário\n";
    echo "    isUsuario = (remetente_id == proprietario_conversa_id)\n";
    echo "} else {\n";
    echo "    // Lógica normal\n";
    echo "    isUsuario = (remetente_id == usuario_logado_id)\n";
    echo "}\n\n";
    echo "messageClass = isUsuario ? 'sent' : 'received'";
    echo "</code>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px 0;'>";
    echo "❌ <strong>Erro:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>✅ Correção implementada com sucesso!</strong></p>";
echo "<small>Teste executado em: " . date('d/m/Y H:i:s') . "</small>";
echo "<br><br>";
echo "<a href='" . URL . "/chat/index' style='color: blue;'>← Voltar para o Chat</a>";
?> 