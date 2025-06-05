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
                                <i class="fas fa-cog me-2"></i> Configurações do Chat
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/index" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= URL ?>/chat/configuracoes">
                                <div class="row">
                                    <!-- Configurações Gerais -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-settings me-2"></i>Configurações Gerais</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="template_padrao" class="form-label">Template Padrão:</label>
                                                    <input type="text" class="form-control" id="template_padrao" 
                                                        name="template_padrao" 
                                                        value="<?= $dados['configuracoes']['template_padrao'] ?? 'simple_greeting' ?>"
                                                        placeholder="Nome do template aprovado na Meta">
                                                    <small class="form-text text-muted">
                                                        Nome do template que será usado para a primeira mensagem (deve estar aprovado na Meta)
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="webhook_url" class="form-label">URL do Webhook:</label>
                                                    <input type="url" class="form-control" id="webhook_url" 
                                                        name="webhook_url" 
                                                        value="<?= $dados['configuracoes']['webhook_url'] ?? URL . '/chat/webhook' ?>"
                                                        placeholder="https://seudominio.com/chat/webhook">
                                                    <small class="form-text text-muted">
                                                        URL onde o SERPRO enviará as notificações de mensagens recebidas
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                            id="auto_resposta" name="auto_resposta" value="1"
                                                            <?= isset($dados['configuracoes']['auto_resposta']) && $dados['configuracoes']['auto_resposta'] == '1' ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="auto_resposta">
                                                            Habilitar Auto-Resposta
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Enviar resposta automática quando receber mensagens fora do horário de atendimento
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="horario_atendimento" class="form-label">Horário de Atendimento:</label>
                                                    <input type="text" class="form-control" id="horario_atendimento" 
                                                        name="horario_atendimento" 
                                                        value="<?= $dados['configuracoes']['horario_atendimento'] ?? '08:00-18:00' ?>"
                                                        placeholder="08:00-18:00">
                                                    <small class="form-text text-muted">
                                                        Formato: HH:MM-HH:MM (ex: 08:00-18:00)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informações da API -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações da API</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Client ID:</label>
                                                    <input type="text" class="form-control" 
                                                        value="<?= defined('SERPRO_CLIENT_ID') ? SERPRO_CLIENT_ID : 'Não configurado' ?>" 
                                                        readonly>
                                                    <small class="form-text text-muted">
                                                        Configurado no arquivo de constantes
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">WABA ID:</label>
                                                    <input type="text" class="form-control" 
                                                        value="<?= defined('SERPRO_WABA_ID') ? SERPRO_WABA_ID : 'Não configurado' ?>" 
                                                        readonly>
                                                    <small class="form-text text-muted">
                                                        WhatsApp Business Account ID
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Base URL:</label>
                                                    <input type="text" class="form-control" 
                                                        value="<?= defined('SERPRO_BASE_URL') ? SERPRO_BASE_URL : 'Não configurado' ?>" 
                                                        readonly>
                                                    <small class="form-text text-muted">
                                                        URL base da API SERPRO WhatsApp
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Status da API:</label>
                                                    <div class="d-flex align-items-center">
                                                        <span id="apiStatus" class="badge bg-secondary me-2">
                                                            <i class="fas fa-circle-notch fa-spin"></i> Verificando...
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="verificarStatusAPI()">
                                                            <i class="fas fa-sync me-1"></i> Verificar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ações Rápidas -->
                                        <div class="card mt-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Ações Rápidas</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= URL ?>/chat/gerenciarTemplates" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-file-alt me-1"></i> Gerenciar Templates
                                                    </a>
                                                    <a href="<?= URL ?>/chat/gerenciarWebhooks" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-webhook me-1"></i> Gerenciar Webhooks
                                                    </a>
                                                    <a href="<?= URL ?>/chat/qrCode" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-qrcode me-1"></i> Gerenciar QR Codes
                                                    </a>
                                                    <div class="mb-2">
                                                        <a href="<?= URL ?>/chat/metricas" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-chart-bar me-1"></i> Ver Métricas
                                                        </a>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="testarAPI()">
                                                        <i class="fas fa-flask me-1"></i> Testar API
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <hr>
                                        <div class="text-end">
                                            <a href="<?= URL ?>/chat/index" class="btn btn-secondary me-2">
                                                <i class="fas fa-times me-1"></i> Cancelar
                                            </a>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-1"></i> Salvar Configurações
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Seção de Logs (se necessário) -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Logs Recentes</h6>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="carregarLogs()">
                                                <i class="fas fa-sync me-1"></i> Atualizar
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div id="logsContainer">
                                                <p class="text-muted">Clique em "Atualizar" para carregar os logs recentes</p>
                                            </div>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar status da API ao carregar
    verificarStatusAPI();
    
    // Máscara para horário de atendimento
    const horarioInput = document.getElementById('horario_atendimento');
    if (horarioInput) {
        horarioInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d:]/g, '');
            if (value.length >= 5 && value.indexOf('-') === -1) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }
            e.target.value = value;
        });
    }
});

/**
 * Verifica status da API
 */
function verificarStatusAPI() {
    const apiStatus = document.getElementById('apiStatus');
    if (!apiStatus) return;
    
    apiStatus.className = 'badge bg-secondary me-2';
    apiStatus.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Verificando...';
    
    const timestamp = new Date().getTime();
    
    fetch(`<?= URL ?>/chat/verificarStatusAPI?_=${timestamp}`, {
        method: 'POST',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erro na resposta da rede: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new TypeError("Resposta não é JSON válido!");
        }
        return response.json();
    })
    .then(data => {
        if (data.online) {
            apiStatus.className = 'badge bg-success me-2';
            apiStatus.innerHTML = '<i class="fas fa-check-circle"></i> Online';
        } else {
            apiStatus.className = 'badge bg-danger me-2';
            apiStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> Offline';
            console.error('Erro na API:', data.error);
        }
    })
    .catch(error => {
        console.error('Erro ao verificar status da API:', error);
        apiStatus.className = 'badge bg-warning me-2';
        apiStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro';
    });
}

/**
 * Carrega logs recentes (implementar conforme necessário)
 */
function carregarLogs() {
    const container = document.getElementById('logsContainer');
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando logs...</div>';
    
    // Simular carregamento de logs
    setTimeout(() => {
        container.innerHTML = `
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Token renovado automaticamente</strong><br>
                        <small class="text-muted">${new Date().toLocaleString('pt-BR')}</small>
                    </div>
                    <span class="badge bg-success">Sucesso</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Verificação de status da API</strong><br>
                        <small class="text-muted">${new Date(Date.now() - 60000).toLocaleString('pt-BR')}</small>
                    </div>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Webhook configurado</strong><br>
                        <small class="text-muted">${new Date(Date.now() - 120000).toLocaleString('pt-BR')}</small>
                    </div>
                    <span class="badge bg-info">Info</span>
                </div>
            </div>
        `;
    }, 1000);
}

/**
 * Testa a conectividade com a API SERPRO
 */
function testarAPI() {
    const container = document.getElementById('logsContainer');
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testando API SERPRO...</div>';
    
    fetch('<?= URL ?>/chat/testarAPI', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new TypeError("Resposta não é JSON válido!");
        }
        return response.json();
    })
    .then(data => {
        console.log('Resultado do teste da API:', data);
        
        let statusClass = data.status === 200 ? 'text-success' : 'text-danger';
        let statusIcon = data.status === 200 ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        container.innerHTML = `
            <div class="alert alert-${data.status === 200 ? 'success' : 'danger'}">
                <h6><i class="fas ${statusIcon}"></i> Resultado do Teste da API</h6>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong> ${data.status}<br>
                        <strong>Token obtido:</strong> ${data.token_obtido ? 'Sim' : 'Não'}<br>
                        ${data.token_length ? `<strong>Tamanho do token:</strong> ${data.token_length}<br>` : ''}
                    </div>
                    <div class="col-md-6">
                        <strong>Configurações:</strong><br>
                        <small>
                            Base URL: ${data.configuracoes ? data.configuracoes.base_url : 'N/A'}<br>
                            Client ID: ${data.configuracoes ? data.configuracoes.client_id : 'N/A'}<br>
                            WABA ID: ${data.configuracoes ? data.configuracoes.waba_id : 'N/A'}
                        </small>
                    </div>
                </div>
                ${data.error ? `<div class="mt-2"><strong>Erro:</strong> ${data.error}</div>` : ''}
                ${data.api_templates ? `<div class="mt-2"><strong>Templates encontrados:</strong> ${JSON.stringify(data.api_templates, null, 2)}</div>` : ''}
            </div>
        `;
    })
    .catch(error => {
        console.error('Erro ao testar API:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle"></i> Erro no Teste da API</h6>
                <p><strong>Erro:</strong> ${error.message}</p>
                <p><small>Verifique o console do navegador para mais detalhes.</small></p>
            </div>
        `;
    });
}

/**
 * Validação do formulário
 */
document.querySelector('form').addEventListener('submit', function(e) {
    const horarioAtendimento = document.getElementById('horario_atendimento').value;
    
    // Validar formato do horário
    if (horarioAtendimento && !/^\d{2}:\d{2}-\d{2}:\d{2}$/.test(horarioAtendimento)) {
        e.preventDefault();
        alert('Formato do horário de atendimento inválido. Use o formato HH:MM-HH:MM (ex: 08:00-18:00)');
        return false;
    }
    
    // Validar URL do webhook
    const webhookUrl = document.getElementById('webhook_url').value;
    if (webhookUrl && !webhookUrl.startsWith('http')) {
        e.preventDefault();
        alert('URL do webhook deve começar com http:// ou https://');
        return false;
    }
});
</script>

<?php include 'app/Views/include/footer.php' ?> 