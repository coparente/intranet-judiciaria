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
                            <?php if (isset($dados['qrCodeError'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= $dados['qrCodeError'] ?>
                                </div>
                            <?php endif; ?>

                            <div id="qrCodesList">
                                <?php if (!empty($dados['qrCodes'])): ?>
                                    <div class="row" id="qrCodesGrid">
                                        <?php foreach ($dados['qrCodes'] as $qr): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <div class="qr-code-container mb-3" style="height: 150px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6; border-radius: 5px;">
                                                            <?php if (isset($qr['qr_image']) && !empty($qr['qr_image'])): ?>
                                                                <img src="data:image/png;base64,<?= htmlspecialchars($qr['qr_image']) ?>" class="img-fluid" style="max-height: 140px;" alt="QR Code">
                                                            <?php else: ?>
                                                                <i class="fas fa-qrcode fa-3x text-muted"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <h6 class="card-title text-truncate" title="<?= htmlspecialchars($qr['mensagem'] ?? '') ?>">
                                                            <?= htmlspecialchars($qr['mensagem'] ?? 'Sem mensagem') ?>
                                                        </h6>
                                                        <p class="card-text">
                                                            <small class="text-muted">Código: <?= htmlspecialchars($qr['codigo'] ?? 'N/A') ?></small>
                                                        </p>
                                                        <div class="btn-group w-100" role="group">
                                                            <button type="button" class="btn btn-primary btn-sm" 
                                                                onclick="visualizarQRCode('<?= htmlspecialchars($qr['id'] ?? '') ?>', '<?= htmlspecialchars($qr['mensagem'] ?? '') ?>', '<?= htmlspecialchars($qr['codigo'] ?? '') ?>', '<?= htmlspecialchars($qr['qr_image'] ?? '') ?>', '<?= htmlspecialchars($qr['link_whatsapp'] ?? '') ?>')">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="confirmarExclusaoQR('<?= htmlspecialchars($qr['id'] ?? '') ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div id="noQRCodes" class="text-center py-4">
                                        <i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Nenhum QR code encontrado</p>
                                        <p class="text-muted">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNovoQR">
                                                <i class="fas fa-plus me-1"></i> Gerar Primeiro QR Code
                                            </button>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h6><i class="fas fa-info-circle me-2"></i>Sobre QR Codes</h6>
                                <p class="mb-2">Os QR codes permitem que usuários iniciem conversas no WhatsApp escaneando o código.</p>
                                <p class="mb-0"><small>Cada QR code pode ter uma mensagem pré-preenchida e um código identificador personalizado.</small></p>
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
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoQRLabel">Gerar Novo QR Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNovoQR">
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
                </form>
                <div id="qrPreview" class="text-center mt-3 d-none">
                    <h6>Preview do QR Code:</h6>
                    <div id="qrImageContainer" class="mb-2"></div>
                    <p class="text-muted"><small>O QR code será atualizado após a geração</small></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGerarQR">
                    <i class="fas fa-qrcode me-1"></i> Gerar QR Code
                </button>
            </div>
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
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="btnBaixarQR">
                        <i class="fas fa-download me-1"></i> Baixar QR Code
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnCopiarLink">
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
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirQRLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este QR code?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoQR">
                    <i class="fas fa-trash me-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let qrToDelete = '';
    let currentQRData = {};

    // Event listeners
    document.getElementById('btnGerarQR').addEventListener('click', gerarQRCode);
    document.getElementById('btnConfirmarExclusaoQR').addEventListener('click', () => excluirQRCode(qrToDelete));
    document.getElementById('btnBaixarQR').addEventListener('click', baixarQRCode);
    document.getElementById('btnCopiarLink').addEventListener('click', copiarLinkWhatsApp);

    function gerarQRCode() {
        const form = document.getElementById('formNovoQR');
        const formData = new FormData(form);
        formData.append('acao', 'gerar');

        const btnGerar = document.getElementById('btnGerarQR');
        const textOriginal = btnGerar.innerHTML;
        btnGerar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Gerando...';
        btnGerar.disabled = true;

        fetch('<?= URL ?>/chat/qrCode', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Verificar se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("Resposta não é JSON válido! Possível problema de sessão.");
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 200 || data.status === 201) {
                alert('QR Code gerado com sucesso!');
                $('#modalNovoQR').modal('hide');
                form.reset();
                // Recarregar a página para mostrar o novo QR code
                window.location.reload();
            } else {
                alert('Erro ao gerar QR code: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao gerar QR code: ' + error.message);
        })
        .finally(() => {
            btnGerar.innerHTML = textOriginal;
            btnGerar.disabled = false;
        });
    }

    function excluirQRCode(qrId) {
        const formData = new FormData();
        formData.append('acao', 'excluir');
        formData.append('qr_id', qrId);

        fetch('<?= URL ?>/chat/qrCode', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Verificar se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("Resposta não é JSON válido! Possível problema de sessão.");
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 200) {
                alert('QR Code excluído com sucesso!');
                $('#modalExcluirQR').modal('hide');
                // Recarregar a página para remover o QR code excluído
                window.location.reload();
            } else {
                alert('Erro ao excluir QR code: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir QR code: ' + error.message);
        });
    }

    function baixarQRCode() {
        if (currentQRData.qr_image) {
            const link = document.createElement('a');
            link.href = 'data:image/png;base64,' + currentQRData.qr_image;
            link.download = `qrcode_${currentQRData.codigo || 'whatsapp'}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    function copiarLinkWhatsApp() {
        if (currentQRData.link_whatsapp) {
            navigator.clipboard.writeText(currentQRData.link_whatsapp).then(() => {
                alert('Link copiado para a área de transferência!');
            }).catch(err => {
                console.error('Erro ao copiar link:', err);
                // Fallback para navegadores mais antigos
                const textArea = document.createElement('textarea');
                textArea.value = currentQRData.link_whatsapp;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Link copiado para a área de transferência!');
            });
        }
    }

    // Funções globais para os botões
    window.visualizarQRCode = function(id, mensagem, codigo, qrImage, linkWhatsapp) {
        currentQRData = {
            id: id,
            mensagem: mensagem,
            codigo: codigo,
            qr_image: qrImage,
            link_whatsapp: linkWhatsapp
        };

        document.getElementById('qrCodeMensagem').textContent = mensagem;
        document.getElementById('qrCodeCodigo').textContent = codigo || 'N/A';
        
        const qrImageContainer = document.getElementById('qrCodeImage');
        if (qrImage) {
            qrImageContainer.innerHTML = `<img src="data:image/png;base64,${qrImage}" class="img-fluid" style="max-width: 300px;">`;
        } else {
            qrImageContainer.innerHTML = '<i class="fas fa-qrcode fa-5x text-muted"></i><p>QR Code não disponível</p>';
        }

        $('#modalVisualizarQR').modal('show');
    };

    window.confirmarExclusaoQR = function(qrId) {
        qrToDelete = qrId;
        $('#modalExcluirQR').modal('show');
    };
});
</script>

<?php include 'app/Views/include/footer.php' ?> 