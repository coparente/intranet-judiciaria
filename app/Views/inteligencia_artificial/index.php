<?php include 'app/Views/include/nav.php' ?>

<main>
    <div class="content">
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <!-- Menu Lateral -->
                    <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                        <?php include 'app/Views/include/menu_adm.php' ?>
                    <?php endif; ?>
                    <?php include 'app/Views/include/menu.php' ?>
                </div>
                
                <!-- Conteúdo Principal -->
                <div class="col-md-9">
                    <!-- Alertas e Mensagens -->
                    <?= Helper::mensagem('ia') ?>
                    <?= Helper::mensagemSweetAlert('ia') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-robot me-2"></i> Inteligência Artificial - Google AI
                        </h1>
                        <!-- <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard/inicial">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Inteligência Artificial</li>
                            </ol>
                        </nav> -->
                        <div class="text-end">
                            <a href="<?= URL ?>/dashboard/inicial" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card principal -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-pdf me-2"></i> Análise de Documentos PDF
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text mb-4">
                                Utilize a inteligência artificial para analisar documentos PDF e obter descrições detalhadas do conteúdo.
                            </p>
                            
                            <form action="<?= URL ?>/inteligenciaartificial/index" method="POST" enctype="multipart/form-data" onsubmit="return mostrarCarregando()">
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">O que você deseja extrair do documento?</label>
                                    <textarea class="form-control" name="descricao" id="descricao" rows="3" placeholder="Ex: Faça um resumo do documento, extraia os pontos principais, liste as datas mencionadas..." required><?= $dados['descricao'] ?></textarea>
                                    <div class="form-text">Seja específico sobre o que você deseja que a IA analise no documento.</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="pdfFile" class="form-label">Selecione o arquivo PDF</label>
                                    <input type="file" class="form-control" name="pdfFile" id="pdfFile" accept=".pdf" required>
                                    <div class="form-text">Tamanho máximo: 10MB. Apenas arquivos PDF são aceitos.</div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" id="btn_salvar" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i> Analisar Documento
                                        <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (!empty($dados['descricaoTexto'])): ?>
                    <!-- Card com o resultado -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i> Resultado da Análise
                                </h5>
                                <button id="copiarBtn" class="btn btn-sm btn-outline-secondary">
                                    <i class="far fa-copy me-2"></i> Copiar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="border p-3 bg-light rounded" id="descricaoTexto" style="white-space: pre-wrap;">
                                <?= nl2br(htmlspecialchars($dados['descricaoTexto'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Card de informações -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i> Informações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i> 
                                <strong>Dicas para melhores resultados:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Seja específico em sua solicitação para obter resultados mais precisos.</li>
                                    <li>Documentos com texto bem formatado produzem melhores análises.</li>
                                    <li>A análise funciona melhor com documentos em português, inglês ou espanhol.</li>
                                    <li>Documentos muito extensos podem ser processados apenas parcialmente.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    function mostrarCarregando() {
        const btn = document.getElementById('btn_salvar');
        const spinner = document.getElementById('spinner');
        spinner.classList.remove('d-none');
        btn.disabled = true;
        return true;
    }

    // Função para copiar o texto do resultado
    document.addEventListener('DOMContentLoaded', function() {
        const copiarBtn = document.getElementById('copiarBtn');
        if (copiarBtn) {
            copiarBtn.addEventListener('click', function() {
                // Obtém o conteúdo da div
                const descricaoHtml = document.getElementById('descricaoTexto').innerHTML;
                const descricaoText = descricaoHtml.replace(/<br\s*[\/]?>/gi, "\n").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
                
                // Usa a API de clipboard moderna
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(descricaoText)
                        .then(() => {
                            // Feedback visual temporário
                            copiarBtn.innerHTML = '<i class="fas fa-check me-2"></i> Copiado!';
                            copiarBtn.classList.replace('btn-outline-secondary', 'btn-success');
                            
                            setTimeout(() => {
                                copiarBtn.innerHTML = '<i class="far fa-copy me-2"></i> Copiar';
                                copiarBtn.classList.replace('btn-success', 'btn-outline-secondary');
                            }, 2000);
                        })
                        .catch(err => {
                            console.error('Erro ao copiar texto: ', err);
                            alert('Não foi possível copiar o texto automaticamente.');
                        });
                } else {
                    // Fallback para método antigo
                    const tempTextArea = document.createElement('textarea');
                    tempTextArea.value = descricaoText;
                    document.body.appendChild(tempTextArea);
                    tempTextArea.select();
                    
                    try {
                        document.execCommand('copy');
                        copiarBtn.innerHTML = '<i class="fas fa-check me-2"></i> Copiado!';
                        setTimeout(() => {
                            copiarBtn.innerHTML = '<i class="far fa-copy me-2"></i> Copiar';
                        }, 2000);
                    } catch (err) {
                        console.error('Erro ao copiar texto: ', err);
                        alert('Não foi possível copiar o texto automaticamente.');
                    }
                    
                    document.body.removeChild(tempTextArea);
                }
            });
        }
    });
</script>

<?php include 'app/Views/include/footer.php' ?> 