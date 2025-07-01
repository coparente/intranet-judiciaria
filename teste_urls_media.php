<?php
/**
 * 🧪 TESTE DE URLs DE MÍDIA - Verifica todas as formas de acessar arquivos
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/configuracao.php';
require_once 'app/Libraries/Database.php';

// Simular usuário logado (substitua pelo ID real)
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_perfil'] = 'admin';
    echo "<div style='background:#fff3cd; padding:10px; border-radius:5px; color:#856404; margin:20px 0;'>";
    echo "⚠️ Simulando usuário admin logado (ID: 1) para testes";
    echo "</div>";
}

echo "<h1>🧪 TESTE DE URLs DE MÍDIA</h1>";
echo "<strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "<br><br>";

// Arquivo de teste
$arquivoTeste = 'document/2025/document_6863de933940f.pdf';

echo "<h2>📋 ARQUIVO DE TESTE</h2>";
echo "<strong>Caminho:</strong> {$arquivoTeste}<br>";
echo "<strong>URL Original problemática:</strong> https://coparente.top/intranet/document/2025/document_6863de933940f.pdf<br><br>";

// Verificar se arquivo existe no banco
try {
    $db = new Database();
    $sql = "SELECT id, conversa_id, tipo, midia_url, midia_nome 
            FROM mensagens_chat 
            WHERE midia_url = :caminho OR conteudo = :caminho 
            ORDER BY id DESC LIMIT 1";
    
    $db->query($sql);
    $db->bind(':caminho', $arquivoTeste);
    $mensagem = $db->resultado();
    
    if ($mensagem) {
        echo "<h2>✅ ARQUIVO ENCONTRADO NO BANCO</h2>";
        echo "• <strong>ID da mensagem:</strong> {$mensagem->id}<br>";
        echo "• <strong>Conversa ID:</strong> {$mensagem->conversa_id}<br>";
        echo "• <strong>Tipo:</strong> {$mensagem->tipo}<br>";
        echo "• <strong>Nome:</strong> {$mensagem->midia_nome}<br>";
    } else {
        echo "<h2>❌ ARQUIVO NÃO ENCONTRADO NO BANCO</h2>";
        echo "Usando arquivo de exemplo para testes...<br>";
    }
} catch (Exception $e) {
    echo "<h2>⚠️ ERRO AO VERIFICAR BANCO</h2>";
    echo "Erro: " . $e->getMessage() . "<br>";
}

echo "<br><h2>🔗 URLS DE TESTE</h2>";

$baseUrl = 'https://coparente.top/intranet';

$urls = [
    'Endpoint Direto (Método 1)' => $baseUrl . '/media.php/' . $arquivoTeste,
    'Endpoint com Parâmetro (Método 2)' => $baseUrl . '/media.php?file=' . urlencode($arquivoTeste),
    'URL Limpa via Rewrite (Método 3)' => $baseUrl . '/media/' . $arquivoTeste,
    'URL Limpa Direta (Método 4)' => $baseUrl . '/' . $arquivoTeste,
    'Via Controller (Método Original)' => $baseUrl . '/chat/visualizarMidiaMinIO/' . urlencode($arquivoTeste),
    'URL Fresca (Método Original)' => $baseUrl . '/chat/gerarUrlFresca/' . urlencode($arquivoTeste)
];

foreach ($urls as $descricao => $url) {
    echo "<h3>📎 {$descricao}</h3>";
    echo "<strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a><br>";
    
    // Testar URL via cURL (apenas headers para verificar se funciona)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // Apenas headers
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Simular cookies de sessão se necessário
    if (isset($_COOKIE)) {
        $cookies = [];
        foreach ($_COOKIE as $name => $value) {
            $cookies[] = "$name=$value";
        }
        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
        }
    }
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "• <span style='color:red'>❌ Erro cURL: {$error}</span><br>";
    } else {
        switch ($httpCode) {
            case 200:
                echo "• <span style='color:green'>✅ HTTP 200 - Funcionando!</span><br>";
                break;
            case 302:
            case 301:
                echo "• <span style='color:blue'>🔄 HTTP {$httpCode} - Redirecionamento (pode estar funcionando)</span><br>";
                break;
            case 403:
                echo "• <span style='color:orange'>⚠️ HTTP 403 - Acesso negado (verificar autenticação)</span><br>";
                break;
            case 404:
                echo "• <span style='color:red'>❌ HTTP 404 - Não encontrado</span><br>";
                break;
            case 500:
                echo "• <span style='color:red'>💥 HTTP 500 - Erro interno do servidor</span><br>";
                break;
            default:
                echo "• <span style='color:gray'>⚠️ HTTP {$httpCode} - Status não esperado</span><br>";
        }
    }
    echo "<br>";
}

echo "<h2>📝 INSTRUÇÕES DE USO</h2>";
echo "<div style='background:#d4edda; padding:15px; border-radius:5px; color:#155724;'>";
echo "<strong>✅ URLs RECOMENDADAS PARA USO:</strong><br><br>";

echo "<strong>1. URL Mais Limpa (Recomendada):</strong><br>";
echo "<code>{$baseUrl}/media/{$arquivoTeste}</code><br><br>";

echo "<strong>2. URL Direta do Arquivo:</strong><br>";
echo "<code>{$baseUrl}/{$arquivoTeste}</code><br><br>";

echo "<strong>3. Endpoint Específico:</strong><br>";
echo "<code>{$baseUrl}/media.php/{$arquivoTeste}</code><br><br>";

echo "<strong>4. Via Controller (Original):</strong><br>";
echo "<code>{$baseUrl}/chat/visualizarMidiaMinIO/" . urlencode($arquivoTeste) . "</code><br><br>";

echo "<strong>🔐 Requisitos:</strong><br>";
echo "• Usuário deve estar logado no sistema<br>";
echo "• Usuário deve ter acesso à conversa que contém o arquivo<br>";
echo "• Administradores têm acesso a todos os arquivos<br>";
echo "</div>";

echo "<h2>🛠️ PRÓXIMOS PASSOS</h2>";
echo "<div style='background:#d1ecf1; padding:15px; border-radius:5px; color:#0c5460;'>";
echo "<strong>Para usar no sistema:</strong><br>";
echo "1. Faça login no sistema<br>";
echo "2. Teste as URLs acima<br>";
echo "3. Use a URL que funcionar melhor<br>";
echo "4. Integre nas views do sistema conforme necessário<br>";
echo "</div>";

echo "<br><hr>";
echo "<strong>Teste concluído em:</strong> " . date('Y-m-d H:i:s');
?> 