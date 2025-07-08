<?php
/**
 * Script de diagnóstico para problemas com imagens do chat
 */

session_start();
require_once 'app/configuracao.php';
require_once 'app/Libraries/Database.php';
require_once 'app/Libraries/MinioHelper.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    die("❌ Usuário não logado. Faça login primeiro.");
}

echo "<h1>🔍 Diagnóstico de Imagens do Chat</h1>";

try {
    $db = new Database();
    
    // 1. Verificar mensagens de imagem no banco
    echo "<h2>1. Mensagens de Imagem no Banco</h2>";
    $sql = "SELECT m.id, m.conversa_id, m.tipo, m.conteudo, m.midia_url, m.midia_nome, 
                   m.enviado_em, c.contato_nome, c.usuario_id 
            FROM mensagens_chat m 
            LEFT JOIN conversas c ON m.conversa_id = c.id 
            WHERE m.tipo = 'image' 
            ORDER BY m.enviado_em DESC 
            LIMIT 10";
    
    $db->query($sql);
    $mensagens = $db->resultados();
    
    if (empty($mensagens)) {
        echo "<p>❌ Nenhuma mensagem de imagem encontrada no banco.</p>";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Conversa</th><th>Contato</th><th>Usuário</th><th>Conteúdo</th><th>Mídia URL</th><th>Mídia Nome</th><th>Enviado em</th><th>Teste MinIO</th></tr>";
        
        foreach ($mensagens as $msg) {
            echo "<tr>";
            echo "<td>{$msg->id}</td>";
            echo "<td>{$msg->conversa_id}</td>";
            echo "<td>{$msg->contato_nome}</td>";
            echo "<td>{$msg->usuario_id}</td>";
            echo "<td>" . htmlspecialchars(substr($msg->conteudo ?? '', 0, 50)) . "</td>";
            echo "<td>" . htmlspecialchars($msg->midia_url ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($msg->midia_nome ?? 'NULL') . "</td>";
            echo "<td>{$msg->enviado_em}</td>";
            
            // Testar MinIO
            if ($msg->midia_url) {
                $resultado = MinioHelper::acessoDirecto($msg->midia_url);
                if ($resultado['sucesso']) {
                    echo "<td>✅ OK (" . number_format($resultado['tamanho'] / 1024, 2) . " KB)</td>";
                } else {
                    echo "<td>❌ Erro: " . $resultado['erro'] . "</td>";
                }
            } else {
                echo "<td>⚠️ Sem URL</td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Verificar permissões do usuário atual
    echo "<h2>2. Verificar Permissões do Usuário Atual</h2>";
    echo "<p><strong>Usuário ID:</strong> {$_SESSION['usuario_id']}</p>";
    echo "<p><strong>Perfil:</strong> " . ($_SESSION['usuario_perfil'] ?? 'N/A') . "</p>";
    
    if (!empty($mensagens)) {
        echo "<h3>Teste de Permissões:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Mídia URL</th><th>Tem Permissão?</th><th>Detalhes</th></tr>";
        
        foreach ($mensagens as $msg) {
            if ($msg->midia_url) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($msg->midia_url) . "</td>";
                
                // Testar permissão usando a mesma lógica do sistema
                $sql_permissao = "SELECT c.* FROM conversas c 
                                 INNER JOIN mensagens_chat m ON c.id = m.conversa_id 
                                 WHERE c.usuario_id = :usuario_id 
                                 AND (m.midia_url = :caminho_minio1 OR m.conteudo = :caminho_minio2)
                                 LIMIT 1";
                
                $db->query($sql_permissao);
                $db->bind(':usuario_id', $_SESSION['usuario_id']);
                $db->bind(':caminho_minio1', $msg->midia_url);
                $db->bind(':caminho_minio2', $msg->midia_url);
                
                $tem_permissao = $db->resultado() !== false;
                
                if ($tem_permissao) {
                    echo "<td>✅ SIM</td>";
                    echo "<td>Usuário {$_SESSION['usuario_id']} tem acesso</td>";
                } else {
                    echo "<td>❌ NÃO</td>";
                    echo "<td>Usuário {$_SESSION['usuario_id']} não tem acesso (conversa pertence ao usuário {$msg->usuario_id})</td>";
                }
                
                echo "</tr>";
            }
        }
        echo "</table>";
    }
    
    // 3. Testar MinIO
    echo "<h2>3. Teste de Conexão MinIO</h2>";
    $teste_minio = MinioHelper::testarConexao();
    
    if ($teste_minio['sucesso']) {
        echo "<p>✅ <strong>MinIO conectado com sucesso!</strong></p>";
        echo "<p><strong>Bucket:</strong> {$teste_minio['bucket']}</p>";
        echo "<p><strong>Endpoint:</strong> {$teste_minio['endpoint']}</p>";
    } else {
        echo "<p>❌ <strong>Erro ao conectar com MinIO:</strong> {$teste_minio['erro']}</p>";
    }
    
    // 4. Testar URLs de acesso
    echo "<h2>4. Teste de URLs de Acesso</h2>";
    
    if (!empty($mensagens)) {
        $primeira_msg = $mensagens[0];
        if ($primeira_msg->midia_url) {
            echo "<p><strong>Testando primeira imagem:</strong> {$primeira_msg->midia_url}</p>";
            
            // URL via media.php
            $url_media = URL . "/media/{$primeira_msg->midia_url}";
            echo "<p><strong>URL via media.php:</strong> <a href='{$url_media}' target='_blank'>{$url_media}</a></p>";
            
            // URL via controller
            $url_controller = URL . "/chat/visualizarMidiaMinIO/" . urlencode($primeira_msg->midia_url);
            echo "<p><strong>URL via controller:</strong> <a href='{$url_controller}' target='_blank'>{$url_controller}</a></p>";
        }
    }
    
    // 5. Estatísticas gerais
    echo "<h2>5. Estatísticas Gerais</h2>";
    
    $sql_stats = "SELECT 
                    tipo,
                    COUNT(*) as total,
                    COUNT(CASE WHEN midia_url IS NOT NULL THEN 1 END) as com_midia_url,
                    COUNT(CASE WHEN midia_url IS NULL THEN 1 END) as sem_midia_url
                  FROM mensagens_chat 
                  WHERE tipo IN ('image', 'video', 'audio', 'document')
                  GROUP BY tipo";
    
    $db->query($sql_stats);
    $stats = $db->resultados();
    
    if (!empty($stats)) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Tipo</th><th>Total</th><th>Com mídia_url</th><th>Sem mídia_url</th></tr>";
        
        foreach ($stats as $stat) {
            echo "<tr>";
            echo "<td>{$stat->tipo}</td>";
            echo "<td>{$stat->total}</td>";
            echo "<td>{$stat->com_midia_url}</td>";
            echo "<td>{$stat->sem_midia_url}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
}

echo "<h2>6. Próximos Passos</h2>";
echo "<ul>";
echo "<li>Se as imagens estão no banco mas não aparecem: problema de permissões ou URLs</li>";
echo "<li>Se MinIO não conecta: verificar configurações em app/configuracao.php</li>";
echo "<li>Se usuário não tem permissão: verificar se está vendo conversa de outro usuário</li>";
echo "<li>Se URLs não funcionam: verificar .htaccess e media.php</li>";
echo "</ul>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2, h3 { color: #333; }
    table { width: 100%; margin: 10px 0; }
    th { background-color: #f0f0f0; }
    td, th { text-align: left; padding: 8px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style> 