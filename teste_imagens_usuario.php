<?php
/**
 * Teste específico para verificar se as imagens carregam para usuário comum
 */

session_start();
require_once 'app/configuracao.php';
require_once 'app/Libraries/Database.php';
require_once 'app/Models/ChatModel.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    die("❌ Usuário não logado. Faça login primeiro.");
}

echo "<h1>🧪 Teste de Imagens para Usuário Comum</h1>";
echo "<p><strong>Usuário ID:</strong> {$_SESSION['usuario_id']}</p>";
echo "<p><strong>Perfil:</strong> " . ($_SESSION['usuario_perfil'] ?? 'N/A') . "</p>";

$chatModel = new ChatModel();
$db = new Database();

try {
    // 1. Buscar imagens das conversas do usuário
    echo "<h2>1. Imagens nas Conversas do Usuário</h2>";
    
    $sql = "SELECT m.id, m.conversa_id, m.midia_url, m.midia_nome, 
                   c.contato_nome, c.usuario_id as conversa_usuario_id
            FROM mensagens_chat m 
            INNER JOIN conversas c ON m.conversa_id = c.id 
            WHERE m.tipo = 'image' 
            AND c.usuario_id = :usuario_id
            AND m.midia_url IS NOT NULL
            ORDER BY m.enviado_em DESC 
            LIMIT 5";
    
    $db->query($sql);
    $db->bind(':usuario_id', $_SESSION['usuario_id']);
    $minhasImagens = $db->resultados();
    
    if (empty($minhasImagens)) {
        echo "<p>❌ Nenhuma imagem encontrada nas suas conversas.</p>";
    } else {
        echo "<p>✅ Encontradas " . count($minhasImagens) . " imagens nas suas conversas:</p>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Conversa</th><th>Contato</th><th>Mídia URL</th><th>Teste Permissão</th><th>Teste URL</th></tr>";
        
        foreach ($minhasImagens as $img) {
            echo "<tr>";
            echo "<td>{$img->id}</td>";
            echo "<td>{$img->conversa_id}</td>";
            echo "<td>{$img->contato_nome}</td>";
            echo "<td>" . htmlspecialchars($img->midia_url) . "</td>";
            
            // Testar permissão usando ChatModel
            $temPermissao = $chatModel->verificarAcessoMidiaMinIO($_SESSION['usuario_id'], $img->midia_url);
            
            if ($temPermissao) {
                echo "<td style='color:green'>✅ TEM ACESSO</td>";
                
                // Testar URL
                $urlImagem = URL . "/media/{$img->midia_url}";
                echo "<td><a href='{$urlImagem}' target='_blank' style='color:blue'>🔗 Testar URL</a></td>";
            } else {
                echo "<td style='color:red'>❌ SEM ACESSO</td>";
                echo "<td>-</td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Testar ChatModel diretamente
    echo "<h2>2. Teste ChatModel</h2>";
    
    if (!empty($minhasImagens)) {
        $primeiraImagem = $minhasImagens[0];
        
        echo "<p><strong>Testando imagem:</strong> {$primeiraImagem->midia_url}</p>";
        
        $resultado = $chatModel->verificarAcessoMidiaMinIO($_SESSION['usuario_id'], $primeiraImagem->midia_url);
        
        if ($resultado) {
            echo "<p style='color:green'>✅ <strong>SUCCESS!</strong> ChatModel retornou TRUE - Usuário tem acesso à imagem</p>";
            
            // Construir URLs de teste
            $urls = [
                'Via media.php' => URL . "/media/{$primeiraImagem->midia_url}",
                'Via controller' => URL . "/chat/visualizarMidiaMinIO/" . urlencode($primeiraImagem->midia_url)
            ];
            
            echo "<h3>URLs para Teste:</h3>";
            echo "<ul>";
            foreach ($urls as $tipo => $url) {
                echo "<li><strong>{$tipo}:</strong> <a href='{$url}' target='_blank'>{$url}</a></li>";
            }
            echo "</ul>";
            
        } else {
            echo "<p style='color:red'>❌ <strong>FALHA!</strong> ChatModel retornou FALSE - Usuário sem acesso</p>";
        }
    }
    
    // 3. Verificar diferenças Admin vs Usuario
    echo "<h2>3. Verificação Admin vs Usuário</h2>";
    
    if ($_SESSION['usuario_perfil'] === 'admin') {
        echo "<p style='color:blue'>ℹ️ Você é ADMIN - tem acesso total a todas as imagens</p>";
    } else {
        echo "<p style='color:orange'>ℹ️ Você é USUÁRIO COMUM - só tem acesso às imagens das suas conversas</p>";
        
        // Buscar uma imagem de outro usuário para testar
        $sql = "SELECT m.id, m.midia_url, c.contato_nome, c.usuario_id 
                FROM mensagens_chat m 
                INNER JOIN conversas c ON m.conversa_id = c.id 
                WHERE m.tipo = 'image' 
                AND c.usuario_id != :usuario_id
                AND c.usuario_id IS NOT NULL
                AND m.midia_url IS NOT NULL
                LIMIT 1";
        
        $db->query($sql);
        $db->bind(':usuario_id', $_SESSION['usuario_id']);
        $imagemOutroUsuario = $db->resultado();
        
        if ($imagemOutroUsuario) {
            echo "<p><strong>Testando imagem de outro usuário:</strong> {$imagemOutroUsuario->midia_url} (Usuário: {$imagemOutroUsuario->usuario_id})</p>";
            
            $temAcessoOutroUsuario = $chatModel->verificarAcessoMidiaMinIO($_SESSION['usuario_id'], $imagemOutroUsuario->midia_url);
            
            if ($temAcessoOutroUsuario) {
                echo "<p style='color:red'>❌ <strong>PROBLEMA!</strong> Usuário comum tem acesso à imagem de outro usuário</p>";
            } else {
                echo "<p style='color:green'>✅ <strong>CORRETO!</strong> Usuário comum NÃO tem acesso à imagem de outro usuário</p>";
            }
        } else {
            echo "<p>ℹ️ Não encontrou imagens de outros usuários para testar</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>4. Status Final</h2>";
echo "<p>Se você vê '✅ SUCCESS!' acima e as URLs funcionam, o problema foi resolvido!</p>";
echo "<p>Se ainda há problemas, verifique:</p>";
echo "<ul>";
echo "<li>Se o usuário é dono da conversa que contém a imagem</li>";
echo "<li>Se a imagem realmente existe no MinIO</li>";
echo "<li>Se não há erros nos logs do Apache/PHP</li>";
echo "</ul>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    th { background-color: #f0f0f0; }
    h1, h2, h3 { color: #333; }
</style> 