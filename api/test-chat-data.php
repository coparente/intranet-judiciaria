<?php
/**
 * Script de teste para verificar dados do chat no banco
 * Acesse: http://localhost/chat-api/test-chat-data.php
 */

require_once 'src/config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔍 Teste de Dados do Chat</h1>";
    
    // 1. Verificar usuários
    echo "<h2>1. Usuários</h2>";
    $stmt = $pdo->query("SELECT id, nome, email, ativo FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<div style='color: green;'>✅ " . count($usuarios) . " usuários encontrados</div>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th></tr>";
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>" . ($user['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: red;'>❌ Nenhum usuário encontrado</div>";
    }
    
    // 2. Verificar mensagens
    echo "<h2>2. Mensagens</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mensagens");
    $totalMensagens = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($totalMensagens > 0) {
        echo "<div style='color: green;'>✅ {$totalMensagens} mensagens encontradas</div>";
        
        // Últimas 5 mensagens
        $stmt = $pdo->query("SELECT id, numero, mensagem, direcao, status, data_hora FROM mensagens ORDER BY data_hora DESC LIMIT 5");
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Últimas 5 mensagens:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th></tr>";
        foreach ($mensagens as $msg) {
            echo "<tr>";
            echo "<td>{$msg['id']}</td>";
            echo "<td>{$msg['numero']}</td>";
            echo "<td>" . substr($msg['mensagem'], 0, 50) . "...</td>";
            echo "<td>{$msg['direcao']}</td>";
            echo "<td>{$msg['status']}</td>";
            echo "<td>{$msg['data_hora']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: red;'>❌ Nenhuma mensagem encontrada</div>";
    }
    
    // 3. Verificar contatos (números únicos)
    echo "<h2>3. Contatos (Números Únicos)</h2>";
    $stmt = $pdo->query("SELECT DISTINCT numero FROM mensagens ORDER BY numero");
    $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($contatos) > 0) {
        echo "<div style='color: green;'>✅ " . count($contatos) . " contatos únicos encontrados</div>";
        echo "<ul>";
        foreach ($contatos as $contato) {
            echo "<li>{$contato['numero']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: red;'>❌ Nenhum contato encontrado</div>";
    }
    
    // 4. Estatísticas
    echo "<h2>4. Estatísticas</h2>";
    
    // Mensagens por direção
    $stmt = $pdo->query("SELECT direcao, COUNT(*) as total FROM mensagens GROUP BY direcao");
    $direcoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Mensagens por direção:</h3>";
    foreach ($direcoes as $dir) {
        echo "<div>{$dir['direcao']}: {$dir['total']}</div>";
    }
    
    // Mensagens por status
    $stmt = $pdo->query("SELECT status, COUNT(*) as total FROM mensagens GROUP BY status");
    $status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Mensagens por status:</h3>";
    foreach ($status as $stat) {
        echo "<div>{$stat['status']}: {$stat['total']}</div>";
    }
    
    // 5. Criar dados de teste se não houver
    if ($totalMensagens == 0) {
        echo "<h2>5. Criando Dados de Teste</h2>";
        
        // Inserir algumas mensagens de teste
        $mensagensTeste = [
            ['5511999999999', 'Olá! Como você está?', 'enviada', 'entregue'],
            ['5511999999999', 'Oi! Tudo bem, obrigado!', 'recebida', 'lida'],
            ['5511888888888', 'Bom dia! Preciso de ajuda.', 'enviada', 'enviada'],
            ['5511777777777', 'Teste de mensagem', 'recebida', 'entregue']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO mensagens (numero, mensagem, direcao, status, data_hora, usuario_id) VALUES (?, ?, ?, ?, NOW(), 1)");
        
        foreach ($mensagensTeste as $msg) {
            $stmt->execute($msg);
        }
        
        echo "<div style='color: green;'>✅ Dados de teste criados com sucesso!</div>";
        echo "<div><a href='test-chat-data.php'>Recarregar para ver os dados</a></div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>❌ Erro de conexão com o banco: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Erro: " . $e->getMessage() . "</div>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { font-size: 12px; }
    th, td { padding: 5px; text-align: left; }
    th { background-color: #f0f0f0; }
</style> 