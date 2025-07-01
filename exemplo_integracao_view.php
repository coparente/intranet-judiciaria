<?php
/**
 * EXEMPLO DE INTEGRAÇÃO - Como exibir links de download nas views
 */

// No arquivo: app/Views/chat/conversa.php ou similar
// Ao exibir as mensagens da conversa:

foreach ($dados['mensagens'] as $mensagem) {
    echo "<div class='mensagem'>";
    
    if ($mensagem->tipo == 'text') {
        // Mensagem de texto normal
        echo "<p>" . htmlspecialchars($mensagem->conteudo) . "</p>";
        
    } else {
        // Mensagem com mídia (image, document, audio, video)
        echo "<div class='midia-container'>";
        
        // Nome do arquivo para exibir
        $nomeArquivo = $mensagem->midia_nome ?: basename($mensagem->conteudo);
        
        // URL correta para download
        $urlDownload = URL . '/chat/visualizarMidiaMinIO/' . urlencode($mensagem->conteudo);
        
        // Ícone baseado no tipo
        $icone = '';
        switch ($mensagem->tipo) {
            case 'image':
                $icone = '🖼️';
                break;
            case 'document':
                $icone = '📄';
                break;
            case 'audio':
                $icone = '🎵';
                break;
            case 'video':
                $icone = '🎬';
                break;
            default:
                $icone = '📎';
        }
        
        // Link de download
        echo "<a href='{$urlDownload}' target='_blank' class='btn btn-primary'>";
        echo "{$icone} {$nomeArquivo}";
        echo "</a>";
        
        echo "</div>";
    }
    
    echo "</div>";
}

// ===============================================
// EXEMPLO 2: Via JavaScript/AJAX
// ===============================================
?>

<script>
function baixarMidia(caminhoMinio, nomeArquivo) {
    // Método 1: Redirecionamento direto (mais simples)
    const urlDownload = '<?= URL ?>/chat/visualizarMidiaMinIO/' + encodeURIComponent(caminhoMinio);
    window.open(urlDownload, '_blank');
}

function baixarMidiaAjax(caminhoMinio) {
    // Método 2: Via AJAX para URLs temporárias
    fetch('<?= URL ?>/chat/gerarUrlMidia', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            caminho_minio: caminhoMinio
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.url, '_blank');
        } else {
            alert('Erro ao gerar link: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro na requisição');
    });
}
</script>

<?php
// ===============================================
// EXEMPLO 3: Para admins - Listagem de arquivos
// ===============================================

// Em uma página administrativa:
if ($_SESSION['usuario_perfil'] === 'admin') {
    echo "<h3>Arquivos no MinIO</h3>";
    
    // Buscar arquivos via AJAX
    ?>
    <div id="lista-arquivos"></div>
    
    <script>
    function carregarArquivos() {
        fetch('<?= URL ?>/chat/listarArquivosMinIO?tipo=document&ano=2025')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<ul>';
                data.arquivos.forEach(arquivo => {
                    const urlDownload = '<?= URL ?>/chat/visualizarMidiaMinIO/' + encodeURIComponent(arquivo.caminho);
                    html += `<li><a href="${urlDownload}" target="_blank">${arquivo.caminho}</a> (${arquivo.tamanho} bytes)</li>`;
                });
                html += '</ul>';
                document.getElementById('lista-arquivos').innerHTML = html;
            }
        });
    }
    
    // Carregar ao abrir a página
    carregarArquivos();
    </script>
    <?php
}

// ===============================================
// RESUMO DAS URLs DISPONÍVEIS:
// ===============================================

/*
1. DOWNLOAD DIRETO:
   URL/chat/visualizarMidiaMinIO/CAMINHO_ARQUIVO

2. URL TEMPORÁRIA VIA AJAX:
   POST URL/chat/gerarUrlMidia
   Body: {"caminho_minio": "document/2025/arquivo.pdf"}

3. URL FRESCA (GET):
   URL/chat/gerarUrlFresca/CAMINHO_ARQUIVO

4. TESTE DIRETO:
   URL/download_direto.php?arquivo=CAMINHO_ARQUIVO

IMPORTANTE: 
- CAMINHO_ARQUIVO deve estar URL-encoded
- Usuário deve estar logado
- Sistema verifica permissões automaticamente
*/
?> 