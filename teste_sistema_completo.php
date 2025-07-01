<?php
/**
 * Teste do Sistema Completo de Download
 * Mostra como usar corretamente o sistema implementado
 */

require_once 'app/configuracao.php';

echo "<h2>🎯 SISTEMA DE DOWNLOAD MinIO - GUIA COMPLETO</h2>\n";
echo "<hr>\n";

$caminhoTeste = 'document/2025/boleto_renner.pdf';
$urlBase = URL; // Da configuração

echo "<h3>1. ❌ NÃO USE URLs Assinadas Diretas</h3>\n";
echo "<p style='color: red;'>As URLs do tipo <code>https://minioapidj.helpersti.online/chatserpro/...</code> não funcionam mais.</p>\n";
echo "<p>Elas geram erro <strong>AuthorizationQueryParametersError</strong>.</p>\n";

echo "<br><h3>2. ✅ USE o Sistema Implementado</h3>\n";
echo "<p style='color: green;'>Use sempre as URLs do seu sistema:</p>\n";

$urlVisualizacao = $urlBase . '/chat/visualizarMidiaMinIO/' . urlencode($caminhoTeste);
$urlDownloadDireto = $urlBase . '/download_direto.php?arquivo=' . urlencode($caminhoTeste);

echo "<h4>📋 URLs Corretas:</h4>\n";
echo "<div style='background: #f0f0f0; padding: 15px; border-left: 4px solid green;'>\n";
echo "<strong>Via Sistema Principal (Recomendado):</strong><br>\n";
echo "<code>{$urlVisualizacao}</code><br>\n";
echo "<a href='{$urlVisualizacao}' target='_blank' style='color: green;'>🔗 Testar Download Via Sistema</a><br><br>\n";

echo "<strong>Via Script Direto (Alternativo):</strong><br>\n";
echo "<code>{$urlDownloadDireto}</code><br>\n";
echo "<a href='{$urlDownloadDireto}' target='_blank' style='color: blue;'>🔗 Testar Download Direto</a>\n";
echo "</div>\n";

echo "<br><h3>3. 🔄 Como Funciona o Sistema</h3>\n";
echo "<ol>\n";
echo "<li><strong>Usuário clica no link</strong> → Sistema verifica autenticação</li>\n";
echo "<li><strong>Sistema verifica permissões</strong> → Usuário tem acesso à mídia?</li>\n";
echo "<li><strong>Sistema baixa do MinIO</strong> → Via método acessoDirecto()</li>\n";
echo "<li><strong>Sistema serve ao usuário</strong> → Com headers corretos</li>\n";
echo "</ol>\n";

echo "<br><h3>4. 🛠️ Como Integrar no Seu Chat</h3>\n";

echo "<h4>No PHP (ex: ao exibir mensagens):</h4>\n";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars('<?php
// Se a mensagem tem mídia no MinIO
if ($mensagem->tipo != "text" && !empty($mensagem->conteudo)) {
    $caminhoMinio = $mensagem->conteudo; // Ex: "document/2025/arquivo.pdf"
    $urlDownload = URL . "/chat/visualizarMidiaMinIO/" . urlencode($caminhoMinio);
    
    echo "<a href=\'" . $urlDownload . "\' target=\'_blank\'>";
    echo "📎 Baixar " . $mensagem->midia_nome;
    echo "</a>";
}
?>');
echo "</pre>\n";

echo "<h4>No JavaScript (via AJAX):</h4>\n";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars('// Gerar URL temporária via AJAX
function baixarMidia(caminhoMinio) {
    fetch("/chat/gerarUrlMidia", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({caminho_minio: caminhoMinio})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.url, "_blank");
        } else {
            alert("Erro: " + data.error);
        }
    });
}');
echo "</pre>\n";

echo "<br><h3>5. 🧪 Teste Imediato</h3>\n";
echo "<p>Clique nos links acima para testar o download imediatamente.</p>\n";
echo "<p><strong>Importante:</strong> Você precisa estar logado no sistema para que funcione.</p>\n";

echo "<br><h3>6. 🔧 Solução de Problemas</h3>\n";
echo "<ul>\n";
echo "<li><strong>403 Forbidden:</strong> Usuário não logado ou sem permissão</li>\n";
echo "<li><strong>404 Not Found:</strong> Arquivo não existe no MinIO</li>\n";
echo "<li><strong>500 Internal Error:</strong> Problema de configuração MinIO</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<h3>📝 Resumo</h3>\n";
echo "<div style='background: #e7f5e7; padding: 15px; border: 1px solid green;'>\n";
echo "<strong>✅ SEMPRE USE:</strong> <code>URL_DO_SEU_SISTEMA/chat/visualizarMidiaMinIO/CAMINHO_ARQUIVO</code><br>\n";
echo "<strong>❌ NUNCA USE:</strong> URLs diretas do MinIO (minioapidj.helpersti.online)<br><br>\n";
echo "<strong>🎯 O sistema agora baixa DIRETAMENTE do MinIO via PHP</strong><br>\n";
echo "Isso elimina completamente os problemas de assinatura de URL!\n";
echo "</div>\n";
?> 