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
                    <?= Helper::mensagem('chat') ?>
                    <?= Helper::mensagemSweetAlert('chat') ?>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-qrcode me-2"></i> Gerenciar QR Codes
                            </h5>
                            <div>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalNovoQR">
                                    <i class="fas fa-plus me-1"></i> Gerar QR Code
                                </button>
                                <a href="<?= URL ?>/chat/configuracoes" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Seletor de Modo de Visualização -->
                            <div class="alert alert-info mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><i class="fas fa-eye me-2"></i>Modo de Visualização</h6>
                                        <small>Escolha como visualizar os QR codes:</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="btn-group w-100" role="group">
                                            <a href="<?= URL ?>/chat/qrCode?modo=combinado" 
                                               class="btn btn-sm <?= ($dados['modo'] ?? 'combinado') === 'combinado' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                                <i class="fas fa-magic"></i> Combinado
                                            </a>
                                            <a href="<?= URL ?>/chat/qrCode?modo=imagem" 
                                               class="btn btn-sm <?= ($dados['modo'] ?? '') === 'imagem' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                                <i class="fas fa-image"></i> Só Imagem
                                            </a>
                                            <a href="<?= URL ?>/chat/qrCode?modo=dados" 
                                               class="btn btn-sm <?= ($dados['modo'] ?? '') === 'dados' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                                <i class="fas fa-code"></i> Só Dados
                                            </a>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <?php 
                                            $modoAtual = $dados['modo'] ?? 'combinado';
                                            switch ($modoAtual) {
                                                case 'combinado':
                                                    echo '<i class="fas fa-check text-success"></i> Dados + Imagens (recomendado)';
                                                    break;
                                                case 'imagem':
                                                    echo '<i class="fas fa-image text-info"></i> Apenas URLs das imagens';
                                                    break;
                                                case 'dados':
                                                    echo '<i class="fas fa-code text-warning"></i> Códigos e links (sem imagens)';
                                                    break;
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($dados['qrCodeError'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= $dados['qrCodeError'] ?>
                                </div>
                            <?php endif; ?>

                            <!-- DEBUG: Mostrar dados dos QR codes -->
                            <?php if (isset($_GET['debug'])): ?>
                                <div class="alert alert-info">
                                    <h6>Debug - Dados dos QR Codes:</h6>
                                    <pre><?= htmlspecialchars(print_r($dados['qrCodes'] ?? 'Não definido', true)) ?></pre>
                                    <p><strong>Tipo:</strong> <?= gettype($dados['qrCodes'] ?? null) ?></p>
                                    <p><strong>Erro:</strong> <?= $dados['qrCodeError'] ?? 'Nenhum' ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- QR Codes carregados via PHP -->
                            <div id="qrCodesList">
                                <?php if (!empty($dados['qrCodes']) && is_array($dados['qrCodes'])): ?>
                                    <div class="row" id="qrCodesGrid">
                                        <?php foreach ($dados['qrCodes'] as $index => $qr): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <div class="qr-code-container mb-3" style="height: 150px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6; border-radius: 5px;">
                                                            <?php if (isset($qr['qrImageUrl']) && !empty($qr['qrImageUrl'])): ?>
                                                                <img src="<?= htmlspecialchars($qr['qrImageUrl']) ?>" class="img-fluid" style="max-height: 140px;" alt="QR Code" crossorigin="anonymous">
                                                            <?php else: ?>
                                                                <i class="fas fa-qrcode fa-3x text-muted"></i>
                                                                <p class="text-muted"><small>Imagem não disponível</small></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <h6 class="card-title text-truncate" title="<?= htmlspecialchars($qr['mensagemPrePreenchida'] ?? 'QR Code #' . ($index + 1)) ?>">
                                                            <?= htmlspecialchars($qr['mensagemPrePreenchida'] ?? 'QR Code #' . ($index + 1)) ?>
                                                        </h6>
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                <?php if (!empty($qr['codigo'])): ?>
                                                                    <strong>ID:</strong> <?= htmlspecialchars($qr['codigo']) ?><br>
                                                                <?php endif; ?>
                                                                
                                                                <?php if (!empty($qr['deepLinkUrl'])): ?>
                                                                    <strong>Link:</strong>
                                                                    <a href="<?= htmlspecialchars($qr['deepLinkUrl']) ?>" target="_blank" class="text-decoration-none">
                                                                        <?= htmlspecialchars(substr($qr['deepLinkUrl'], 0, 30)) ?>...
                                                                    </a><br>
                                                                <?php endif; ?>
                                                                
                                                                <?php if (!empty($qr['qrImageUrl'])): ?>
                                                                    <strong>Imagem:</strong> <span class="text-success">Disponível</span>
                                                                <?php else: ?>
                                                                    <strong>Imagem:</strong> <span class="text-warning">Não disponível</span>
                                                                <?php endif; ?>
                                                                
                                                                <br><small class="text-info">
                                                                    <?php 
                                                                    $modoAtual = $dados['modo'] ?? 'combinado';
                                                                    switch ($modoAtual) {
                                                                        case 'combinado':
                                                                            echo 'Modo: Completo';
                                                                            break;
                                                                        case 'imagem':
                                                                            echo 'Modo: Só Imagem';
                                                                            break;
                                                                        case 'dados':
                                                                            echo 'Modo: Só Dados';
                                                                            break;
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </small>
                                                        </p>
                                                        <div class="btn-group w-100" role="group">
                                                            <?php if (isset($qr['qrImageUrl']) && !empty($qr['qrImageUrl'])): ?>
                                                                <button type="button" class="btn btn-primary btn-sm" 
                                                                    onclick="visualizarQRCodeCompleto('<?= htmlspecialchars(json_encode($qr), ENT_QUOTES) ?>')">
                                                                    <i class="fas fa-eye"></i> Ver
                                                                </button>
                                                                <button type="button" class="btn btn-success btn-sm" 
                                                                    onclick="baixarQRCodeDireto('<?= htmlspecialchars($qr['qrImageUrl']) ?>', '<?= htmlspecialchars($qr['codigo'] ?? 'qr_' . ($index + 1)) ?>')">
                                                                    <i class="fas fa-download"></i> Baixar
                                                                </button>
                                                                <button type="button" class="btn btn-info btn-sm" 
                                                                    onclick="abrirQRCodeNovaAba('<?= htmlspecialchars($qr['qrImageUrl']) ?>')">
                                                                    <i class="fas fa-external-link-alt"></i> Abrir
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                                    <i class="fas fa-times"></i> Imagem indisponível
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                        <div class="btn-group w-100 mt-1" role="group">
                                            <?php if (!empty($qr['deepLinkUrl'])): ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="copiarLinkWhatsApp('<?= htmlspecialchars($qr['deepLinkUrl']) ?>')">
                                                    <i class="fas fa-copy"></i> Copiar Link WA
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="window.open('<?= htmlspecialchars($qr['deepLinkUrl']) ?>', '_blank')">
                                                    <i class="fas fa-external-link-alt"></i> Testar Link
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($qr['codigo'])): ?>
                                                <!-- QR com código - pode excluir -->
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="confirmarExclusaoQR('<?= htmlspecialchars($qr['codigo']) ?>')">
                                                    <i class="fas fa-trash"></i> Excluir
                                                </button>
                                            <?php else: ?>
                                                <!-- QR sem código - não pode excluir -->
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="alert('❌ Este QR code não pode ser excluído porque a API não retornou o código identificador.\\n\\n✅ Solução: Gere um novo QR code que terá código para exclusão.')">
                                                    <i class="fas fa-exclamation-triangle"></i> Sem ID
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <p class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Total de QR codes: <strong><?= count($dados['qrCodes']) ?></strong>
                                        </p>
                                    </div>
                                    
                                <?php else: ?>
                                    <div id="noQRCodes" class="text-center py-4">
                                        <i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Nenhum QR code encontrado</h5>
                                        <p class="text-muted">Crie QR codes para facilitar o contato via WhatsApp</p>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNovoQR">
                                            <i class="fas fa-plus me-1"></i> Gerar Primeiro QR Code
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h6><i class="fas fa-info-circle me-2"></i>Sobre QR Codes</h6>
                                <p class="mb-2">Os QR codes permitem que usuários iniciem conversas no WhatsApp escaneando o código.</p>
                                <p class="mb-2"><small>Cada QR code pode ter uma mensagem pré-preenchida e um código identificador personalizado.</small></p>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6 class="mb-2"><i class="fas fa-link me-1"></i> Links WhatsApp</h6>
                                        <ul class="list-unstyled small">
                                            <li><i class="fas fa-check text-success me-1"></i> Links podem expirar após um tempo</li>
                                            <li><i class="fas fa-info text-info me-1"></i> Se aparecer "Link no longer valid", gere um novo QR</li>
                                            <li><i class="fas fa-mobile text-primary me-1"></i> Links funcionam melhor em dispositivos móveis</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-2"><i class="fas fa-eye me-1"></i> Modos de Visualização</h6>
                                        <ul class="list-unstyled small">
                                            <li><i class="fas fa-magic text-success me-1"></i> <strong>Combinado:</strong> Dados + Imagens (recomendado)</li>
                                            <li><i class="fas fa-image text-info me-1"></i> <strong>Só Imagem:</strong> Para ver/baixar QR codes</li>
                                            <li><i class="fas fa-code text-warning me-1"></i> <strong>Só Dados:</strong> Para excluir e obter links</li>
                                            <li><i class="fas fa-lightbulb text-primary me-1"></i> Use o toggle acima para alternar</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal Gerar QR Code -->
<div class="modal fade" id="modalNovoQR" tabindex="-1" aria-labelledby="modalNovoQRLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/qrCode" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovoQRLabel">Gerar Novo QR Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="acao" value="gerar">
                    
                    <div class="mb-3">
                        <label for="qrMensagem" class="form-label">Mensagem Pré-preenchida: <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="qrMensagem" name="mensagem" rows="3" required 
                            placeholder="Digite a mensagem que aparecerá automaticamente quando o usuário escanear o QR code">Olá! Entrei em contato através do QR code.</textarea>
                        <small class="form-text text-muted">Esta mensagem aparecerá automaticamente no WhatsApp do usuário</small>
                    </div>
                    <div class="mb-3">
                        <label for="qrCodigo" class="form-label">Código Identificador (opcional)</label>
                        <input type="text" class="form-control" id="qrCodigo" name="codigo" 
                            placeholder="Ex: atendimento-balcao-1">
                        <small class="form-text text-muted">Código para identificar a origem do contato (apenas letras, números e hífens)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-qrcode me-1"></i> Gerar QR Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizar QR Code -->
<div class="modal fade" id="modalVisualizarQR" tabindex="-1" aria-labelledby="modalVisualizarQRLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizarQRLabel">QR Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeImage" class="mb-3">
                    <!-- QR Code será inserido aqui -->
                </div>
                <div id="qrCodeInfo">
                    <p><strong>Mensagem:</strong> <span id="qrCodeMensagem"></span></p>
                    <p><strong>Código:</strong> <span id="qrCodeCodigo"></span></p>
                    <p><strong>Link WhatsApp:</strong> <a href="#" id="qrCodeLink" target="_blank">Ver link</a></p>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="btnBaixarQRModal">
                        <i class="fas fa-download me-1"></i> Baixar QR Code
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnAbrirLinkModal">
                        <i class="fas fa-external-link-alt me-1"></i> Abrir Imagem em Nova Aba
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnCopiarLinkModal">
                        <i class="fas fa-copy me-1"></i> Copiar Link WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir QR Code -->
<div class="modal fade" id="modalExcluirQR" tabindex="-1" aria-labelledby="modalExcluirQRLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/qrCode" method="POST" id="formExcluirQR">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExcluirQRLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="qr_id" id="qrIdToDelete">
                    
                    <p>Tem certeza que deseja excluir este QR code?</p>
                    <p><strong>ID:</strong> <span id="qrIdDisplay"></span></p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Excluir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentQRData = {};

    // Função para visualizar QR Code completo
    window.visualizarQRCodeCompleto = function(qrData) {
        try {
            const qr = JSON.parse(qrData);
            
            currentQRData = {
                codigo: qr.codigo || '',
                mensagem: qr.mensagemPrePreenchida || 'Sem mensagem',
                qrImageUrl: qr.qrImageUrl || '',
                deepLinkUrl: qr.deepLinkUrl || ''
            };
            
            // Preencher modal
            document.getElementById('qrCodeMensagem').textContent = currentQRData.mensagem;
            document.getElementById('qrCodeCodigo').textContent = currentQRData.codigo || 'N/A';
            
            const linkElement = document.getElementById('qrCodeLink');
            if (currentQRData.deepLinkUrl) {
                linkElement.href = currentQRData.deepLinkUrl;
                linkElement.textContent = currentQRData.deepLinkUrl;
                linkElement.style.display = 'inline';
            } else {
                linkElement.style.display = 'none';
            }
            
            // Mostrar imagem
            if (currentQRData.qrImageUrl) {
                document.getElementById('qrCodeImage').innerHTML = `<img src="${currentQRData.qrImageUrl}" class="img-fluid" style="max-width: 300px;" crossorigin="anonymous">`;
            } else {
                document.getElementById('qrCodeImage').innerHTML = '<i class="fas fa-qrcode fa-5x text-muted"></i><p>Imagem não disponível</p>';
            }
            
            // Abrir modal
            $('#modalVisualizarQR').modal('show');
        } catch (error) {
            console.error('Erro ao processar QR Code:', error);
            alert('Erro ao exibir QR Code: ' + error.message);
        }
    };

    // Função para baixar QR Code diretamente
    window.baixarQRCodeDireto = function(qrImageUrl, codigo) {
        if (!qrImageUrl) {
            alert('URL da imagem não disponível');
            return;
        }
        
        // Criar formulário para enviar via backend (contorna CORS)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= URL ?>/chat/baixarQRCode';
        form.target = '_blank';
        
        const urlInput = document.createElement('input');
        urlInput.type = 'hidden';
        urlInput.name = 'qr_url';
        urlInput.value = qrImageUrl;
        
        const nomeInput = document.createElement('input');
        nomeInput.type = 'hidden';
        nomeInput.name = 'nome_arquivo';
        nomeInput.value = `qrcode_${codigo}.png`;
        
        form.appendChild(urlInput);
        form.appendChild(nomeInput);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    // Função para abrir QR Code em nova aba
    window.abrirQRCodeNovaAba = function(qrImageUrl) {
        if (!qrImageUrl) {
            alert('URL da imagem não disponível');
            return;
        }
        
        const novaAba = window.open(qrImageUrl, '_blank');
        if (!novaAba) {
            alert('Por favor, permita popups para este site. URL: ' + qrImageUrl);
        }
    };

    // Função para copiar link WhatsApp
    window.copiarLinkWhatsApp = function(link) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(link).then(() => {
                alert('Link WhatsApp copiado para a área de transferência!');
            }).catch(() => {
                // Fallback
                const textArea = document.createElement('textarea');
                textArea.value = link;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Link WhatsApp copiado para a área de transferência!');
            });
        } else {
            // Fallback para navegadores sem clipboard API
            const textArea = document.createElement('textarea');
            textArea.value = link;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Link WhatsApp copiado para a área de transferência!');
        }
    };

    // Função para confirmar exclusão
    window.confirmarExclusaoQR = function(qrId) {
        document.getElementById('qrIdToDelete').value = qrId;
        document.getElementById('qrIdDisplay').textContent = qrId;
        $('#modalExcluirQR').modal('show');
    };

    // Event listeners para os botões do modal
    document.getElementById('btnBaixarQRModal').addEventListener('click', function() {
        if (currentQRData.qrImageUrl) {
            baixarQRCodeDireto(currentQRData.qrImageUrl, currentQRData.codigo);
        }
    });

    document.getElementById('btnAbrirLinkModal').addEventListener('click', function() {
        if (currentQRData.qrImageUrl) {
            abrirQRCodeNovaAba(currentQRData.qrImageUrl);
        }
    });

    document.getElementById('btnCopiarLinkModal').addEventListener('click', function() {
        if (currentQRData.deepLinkUrl) {
            copiarLinkWhatsApp(currentQRData.deepLinkUrl);
        } else {
            alert('Link do WhatsApp não disponível');
        }
    });
});
</script>

<?php include 'app/Views/include/footer.php' ?> 