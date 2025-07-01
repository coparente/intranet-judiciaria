<?php
/**
 * Teste funcional da URL do MinIO
 * Verifica se a URL gerada realmente funciona para download
 */

require_once 'app/configuracao.php';
require_once 'app/Libraries/MinioHelper.php';

echo "<h2>🧪 TESTE FUNCIONAL - URL MinIO</h2>\n";
echo "<hr>\n";

$caminhoTeste = 'document/2025/boleto_renner.pdf';

echo "<h3>1. 📋 Gerando URL Fresca</h3>\n";
$url = MinioHelper::gerarUrlVisualizacao($caminhoTeste, 3600);

if ($url) {
    echo "✅ <strong>URL gerada:</strong><br>\n";
    echo "<code style='word-break: break-all;'>{$url}</code><br><br>\n";
    
    echo "<h3>2. 🔗 Links de Teste</h3>\n";
    echo "• <a href='{$url}' target='_blank'>🔗 Abrir URL Direta (Nova Aba)</a><br>\n";
    echo "• <a href='chat/visualizarMidiaMinIO/" . urlencode($caminhoTeste) . "' target='_blank'>🔗 Via Sistema (Chat Controller)</a><br><br>\n";
    
    echo "<h3>3. 🧪 Teste de Headers HTTP</h3>\n";
    
    // Testar a URL com cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true); // Apenas headers
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "• <strong>Status HTTP:</strong> ";
    if ($httpCode == 200) {
        echo "<span style='color: green;'>✅ {$httpCode} - OK</span><br>\n";
    } else {
        echo "<span style='color: red;'>❌ {$httpCode} - Erro</span><br>\n";
    }
    
    if ($error) {
        echo "• <strong>Erro cURL:</strong> <span style='color: red;'>{$error}</span><br>\n";
    }
    
    echo "<br><strong>Headers recebidos:</strong><br>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($headers);
    echo "</pre>\n";
    
    echo "<h3>4. 📊 Informações da URL</h3>\n";
    $urlParts = parse_url($url);
    parse_str($urlParts['query'], $queryParams);
    
    echo "• <strong>Host:</strong> {$urlParts['host']}<br>\n";
    echo "• <strong>Caminho:</strong> {$urlParts['path']}<br>\n";
    echo "• <strong>Data de expiração:</strong> ";
    
    if (isset($queryParams['X-Amz-Date']) && isset($queryParams['X-Amz-Expires'])) {
        $dataAmz = DateTime::createFromFormat('Ymd\THis\Z', $queryParams['X-Amz-Date']);
        $expiraEm = $dataAmz->getTimestamp() + (int)$queryParams['X-Amz-Expires'];
        $agora = time();
        
        if ($expiraEm > $agora) {
            $tempoRestante = $expiraEm - $agora;
            echo "<span style='color: green;'>✅ Válida por mais " . gmdate('H:i:s', $tempoRestante) . "</span><br>\n";
        } else {
            echo "<span style='color: red;'>❌ URL expirada</span><br>\n";
        }
        
        echo "• <strong>Expira em:</strong> " . date('Y-m-d H:i:s', $expiraEm) . "<br>\n";
    }
    
} else {
    echo "❌ <strong>Falha ao gerar URL</strong><br>\n";
}

echo "<hr>\n";
echo "<h3>💡 Instruções</h3>\n";
echo "1. Clique nos links acima para testar o download<br>\n";
echo "2. Se o link direto não funcionar, use o link via sistema<br>\n";
echo "3. URLs expiram em 1 hora - gere uma nova se necessário<br>\n";
echo "4. Se ainda houver problemas, verifique as permissões do bucket<br>\n";
?> 