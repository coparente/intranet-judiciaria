<?php
/**
 * Teste de acesso direto ao MinIO
 * Verifica se o método acessoDirecto funciona corretamente
 */

require_once 'app/configuracao.php';
require_once 'app/Libraries/MinioHelper.php';

echo "<h2>🎯 TESTE ACESSO DIRETO - MinIO</h2>\n";
echo "<hr>\n";

$caminhoTeste = 'document/2025/boleto_renner.pdf';

echo "<h3>1. 🔍 Debug da Geração de URL</h3>\n";
$debug = MinioHelper::debugGeracaoUrl($caminhoTeste, 3600);

echo "<strong>Informações:</strong><br>\n";
echo "• Caminho: {$debug['caminho']}<br>\n";
echo "• Bucket: {$debug['bucket']}<br>\n";
echo "• Expiração: {$debug['expiracao']}s<br><br>\n";

echo "<strong>Tentativas de URL:</strong><br>\n";
foreach ($debug['tentativas'] as $metodo => $resultado) {
    if ($resultado['sucesso']) {
        echo "✅ <strong>{$metodo}:</strong> Sucesso<br>\n";
        if (isset($resultado['tem_assinatura'])) {
            echo "&nbsp;&nbsp;- Assinatura: " . ($resultado['tem_assinatura'] ? '✓' : '❌') . "<br>\n";
        }
        if (isset($resultado['url'])) {
            echo "&nbsp;&nbsp;- URL: <a href='{$resultado['url']}' target='_blank'>Testar</a><br>\n";
        }
    } else {
        echo "❌ <strong>{$metodo}:</strong> {$resultado['erro']}<br>\n";
    }
}

echo "<br><h3>2. 🎯 Teste de Acesso Direto</h3>\n";

try {
    $resultado = MinioHelper::acessoDirecto($caminhoTeste);
    
    if ($resultado['sucesso']) {
        echo "✅ <strong>Acesso direto:</strong> SUCESSO<br>\n";
        echo "• <strong>Tamanho:</strong> " . number_format($resultado['tamanho']) . " bytes<br>\n";
        echo "• <strong>Tipo:</strong> {$resultado['content_type']}<br>\n";
        echo "• <strong>Metadados:</strong> " . json_encode($resultado['metadados']) . "<br>\n";
        
        // Oferecer download
        echo "<br><strong>Download via PHP:</strong><br>\n";
        echo "<a href='download_direto.php?arquivo=" . urlencode($caminhoTeste) . "' target='_blank'>🔗 Baixar arquivo via acesso direto</a><br>\n";
        
        // Salvar temporariamente para teste
        $nomeTemp = 'temp_' . basename($caminhoTeste);
        file_put_contents($nomeTemp, $resultado['dados']);
        echo "<a href='{$nomeTemp}' target='_blank'>🔗 Arquivo salvo temporariamente: {$nomeTemp}</a><br>\n";
        
    } else {
        echo "❌ <strong>Acesso direto:</strong> FALHOU<br>\n";
        echo "• <strong>Erro:</strong> {$resultado['erro']}<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Exceção:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<br><h3>3. 🔗 Teste via Controller</h3>\n";
echo "• <a href='chat/visualizarMidiaMinIO/" . urlencode($caminhoTeste) . "' target='_blank'>🔗 Via Controller (visualizarMidiaMinIO)</a><br>\n";
echo "• <a href='chat/gerarUrlFresca/" . urlencode($caminhoTeste) . "' target='_blank'>🔗 Via URL Fresca (gerarUrlFresca)</a><br>\n";

echo "<hr>\n";
echo "<h3>📋 Resumo</h3>\n";
echo "O método de <strong>acesso direto</strong> baixa o arquivo diretamente do MinIO via PHP e serve para o usuário.<br>\n";
echo "Isso evita problemas de assinatura de URL e garante que o arquivo seja sempre acessível.<br>\n";
echo "Use o link 'Via Controller' para testar o sistema completo.<br>\n";
?> 