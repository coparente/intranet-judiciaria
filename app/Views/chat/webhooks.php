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
                                <i class="fas fa-link me-2"></i> Gerenciar Webhooks
                            </h5>
                            <div>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalNovoWebhook">
                                    <i class="fas fa-plus me-1"></i> Novo Webhook
                                </button>
                                <a href="<?= URL ?>/chat/configuracoes" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (isset($dados['webhookError'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= $dados['webhookError'] ?>
                                </div>
                            <?php endif; ?>

                            <div id="webhooksList" class="table-responsive">
                                <?php if (!empty($dados['webhooks'])): ?>
                                    <table class="table table-hover" id="webhooksTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>URL</th>
                                                <th>Token JWT</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="webhooksTableBody">
                                            <?php foreach ($dados['webhooks'] as $webhook): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($webhook['id'] ?? 'N/A') ?></td>
                                                    <td class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($webhook['uri'] ?? '') ?>">
                                                        <?= htmlspecialchars($webhook['uri'] ?? '') ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($webhook['jwtToken'])): ?>
                                                            <span class="badge bg-info">Configurado</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Não configurado</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($webhook['ativo']) && $webhook['ativo']): ?>
                                                            <span class="badge bg-success">Ativo</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Inativo</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm" 
                                                                onclick="editarWebhook('<?= htmlspecialchars($webhook['id'] ?? '') ?>', '<?= htmlspecialchars($webhook['uri'] ?? '') ?>', '<?= htmlspecialchars($webhook['jwtToken'] ?? '') ?>')">
                                                            <i class="fas fa-edit me-1"></i> Editar
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="confirmarExclusaoWebhook('<?= htmlspecialchars($webhook['id'] ?? '') ?>', '<?= htmlspecialchars($webhook['uri'] ?? '') ?>')">
                                                            <i class="fas fa-trash me-1"></i> Excluir
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div id="noWebhooks" class="text-center py-4">
                                        <i class="fas fa-link fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Nenhum webhook encontrado</p>
                                        <p class="text-muted">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNovoWebhook">
                                                <i class="fas fa-plus me-1"></i> Cadastrar Primeiro Webhook
                                            </button>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h6><i class="fas fa-info-circle me-2"></i>Informações sobre Webhooks</h6>
                                <p class="mb-2"><strong>URL Atual do Sistema:</strong> <code><?= URL ?>/chat/webhook</code></p>
                                <p class="mb-2"><strong>Token de Verificação:</strong> <code><?= defined('WEBHOOK_VERIFY_TOKEN') ? WEBHOOK_VERIFY_TOKEN : 'Não configurado' ?></code></p>
                                <p class="mb-0"><small>Configure seu webhook na Meta Business API para receber notificações de mensagens.</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal Novo Webhook -->
<div class="modal fade" id="modalNovoWebhook" tabindex="-1" aria-labelledby="modalNovoWebhookLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoWebhookLabel">Cadastrar Novo Webhook</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoWebhook">
                    <div class="mb-3">
                        <label for="webhookUri" class="form-label">URL do Webhook: <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="webhookUri" name="uri" required 
                            placeholder="https://exemplo.com/webhook">
                        <small class="form-text text-muted">URL completa onde as notificações serão enviadas</small>
                    </div>
                    <div class="mb-3">
                        <label for="webhookJwtToken" class="form-label">Token JWT (opcional)</label>
                        <input type="text" class="form-control" id="webhookJwtToken" name="jwt_token" 
                            placeholder="Token JWT para autenticação">
                        <small class="form-text text-muted">Token de segurança para validar as requisições</small>
                    </div>
                </form>
                <div id="loadingCadastro" class="text-center d-none">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <span class="ms-2">Cadastrando webhook...</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnCadastrarWebhook">
                    <i class="fas fa-save me-1"></i> Cadastrar Webhook
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Webhook -->
<div class="modal fade" id="modalEditarWebhook" tabindex="-1" aria-labelledby="modalEditarWebhookLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarWebhookLabel">Editar Webhook</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarWebhook">
                    <input type="hidden" id="editWebhookId" name="webhook_id">
                    <div class="mb-3">
                        <label for="editWebhookUri" class="form-label">URL do Webhook: <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="editWebhookUri" name="uri" required>
                    </div>
                    <div class="mb-3">
                        <label for="editWebhookJwtToken" class="form-label">Token JWT (opcional)</label>
                        <input type="text" class="form-control" id="editWebhookJwtToken" name="jwt_token">
                    </div>
                </form>
                <div id="loadingEdicao" class="text-center d-none">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <span class="ms-2">Atualizando webhook...</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAtualizarWebhook">
                    <i class="fas fa-save me-1"></i> Atualizar Webhook
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Webhook -->
<div class="modal fade" id="modalExcluirWebhook" tabindex="-1" aria-labelledby="modalExcluirWebhookLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirWebhookLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este webhook?</p>
                <p><strong>URL:</strong> <span id="webhookUrlToDelete"></span></p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                <div id="loadingExclusao" class="text-center d-none">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <span class="ms-2">Excluindo webhook...</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoWebhook">
                    <i class="fas fa-trash me-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let webhookToDelete = '';

    // Event listeners
    document.getElementById('btnCadastrarWebhook').addEventListener('click', cadastrarWebhook);
    document.getElementById('btnAtualizarWebhook').addEventListener('click', atualizarWebhook);
    document.getElementById('btnConfirmarExclusaoWebhook').addEventListener('click', () => excluirWebhook(webhookToDelete));

    /**
     * Cadastra um novo webhook
     */
    function cadastrarWebhook() {
        const form = document.getElementById('formNovoWebhook');
        const btnCadastrar = document.getElementById('btnCadastrarWebhook');
        const loading = document.getElementById('loadingCadastro');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Mostrar loading
        btnCadastrar.disabled = true;
        loading.classList.remove('d-none');

        const formData = new FormData(form);
        formData.append('acao', 'cadastrar');

        fetch('<?= URL ?>/chat/gerenciarWebhooks', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Resposta não é JSON válido. Verifique se você está logado.');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 200 || data.status === 201) {
                alert('Webhook cadastrado com sucesso!');
                // Fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoWebhook'));
                modal.hide();
                form.reset();
                // Recarregar página para mostrar novo webhook
                window.location.reload();
            } else {
                alert('Erro ao cadastrar webhook: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao cadastrar webhook: ' + error.message);
        })
        .finally(() => {
            btnCadastrar.disabled = false;
            loading.classList.add('d-none');
        });
    }

    /**
     * Atualiza um webhook existente
     */
    function atualizarWebhook() {
        const form = document.getElementById('formEditarWebhook');
        const btnAtualizar = document.getElementById('btnAtualizarWebhook');
        const loading = document.getElementById('loadingEdicao');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Mostrar loading
        btnAtualizar.disabled = true;
        loading.classList.remove('d-none');

        const formData = new FormData(form);
        formData.append('acao', 'atualizar');

        fetch('<?= URL ?>/chat/gerenciarWebhooks', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Resposta não é JSON válido. Verifique se você está logado.');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 200) {
                alert('Webhook atualizado com sucesso!');
                // Fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarWebhook'));
                modal.hide();
                // Recarregar página para mostrar alterações
                window.location.reload();
            } else {
                alert('Erro ao atualizar webhook: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar webhook: ' + error.message);
        })
        .finally(() => {
            btnAtualizar.disabled = false;
            loading.classList.add('d-none');
        });
    }

    /**
     * Exclui um webhook
     */
    function excluirWebhook(webhookId) {
        const btnExcluir = document.getElementById('btnConfirmarExclusaoWebhook');
        const loading = document.getElementById('loadingExclusao');

        // Mostrar loading
        btnExcluir.disabled = true;
        loading.classList.remove('d-none');

        const formData = new FormData();
        formData.append('acao', 'excluir');
        formData.append('webhook_id', webhookId);

        fetch('<?= URL ?>/chat/gerenciarWebhooks', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Resposta não é JSON válido. Verifique se você está logado.');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 200) {
                alert('Webhook excluído com sucesso!');
                // Fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalExcluirWebhook'));
                modal.hide();
                // Recarregar página para mostrar alterações
                window.location.reload();
            } else {
                alert('Erro ao excluir webhook: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir webhook: ' + error.message);
        })
        .finally(() => {
            btnExcluir.disabled = false;
            loading.classList.add('d-none');
        });
    }

    /**
     * Funções globais para os botões
     */
    window.editarWebhook = function(id, uri, jwtToken) {
        document.getElementById('editWebhookId').value = id;
        document.getElementById('editWebhookUri').value = uri;
        document.getElementById('editWebhookJwtToken').value = jwtToken;
        new bootstrap.Modal(document.getElementById('modalEditarWebhook')).show();
    };

    window.confirmarExclusaoWebhook = function(webhookId, uri) {
        webhookToDelete = webhookId;
        document.getElementById('webhookUrlToDelete').textContent = uri;
        new bootstrap.Modal(document.getElementById('modalExcluirWebhook')).show();
    };
});
</script>

<?php include 'app/Views/include/footer.php' ?> 