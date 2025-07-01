<?php include 'app/Views/include/nav.php' ?>

<style>
.file-preview {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-left: 4px solid #007bff;
}

.file-preview .file-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}

.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    background-color: #007bff;
    transition: width 0.3s ease;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.uploading {
    animation: pulse 1.5s infinite;
}
</style>

<main>
    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <!-- Botão Voltar -->
                <div class="mb-3">
                    <a href="<?= URL ?>/chat/index" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i> Voltar para Conversas
                    </a>
                </div>

                <!-- Container do Chat -->
                <div class="chat-container fade-in">
                    <!-- Header do Chat -->
                    <div class="chat-header">
                        <div class="contact-info">
                            <div class="contact-avatar">
                                <?= strtoupper(substr($dados['conversa']->contato_nome, 0, 2)) ?>
                            </div>
                            <div class="contact-details">
                                <h5><?= htmlspecialchars($dados['conversa']->contato_nome) ?></h5>
                                <small><?= htmlspecialchars($dados['conversa']->contato_numero) ?></small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="api-status">
                                <div class="status-indicator" id="statusIndicator"></div>
                                <span id="apiStatusText">Verificando...</span>
                            </div>
                            <form method="POST" action="<?= URL ?>/chat/conversa/<?= $dados['conversa']->id ?>" style="display: inline;">
                                <input type="hidden" name="acao" value="verificar_status">
                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Verificar status das mensagens">
                                    <i class="fas fa-sync-alt"></i> Verificar Status
                                </button>
                            </form>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalEditarConversa">
                                        <i class="fas fa-edit me-2"></i> Editar Contato
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" onclick="confirmarExclusao()">
                                        <i class="fas fa-trash me-2"></i> Excluir Conversa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Área de Mensagens -->
                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($dados['mensagens'])): ?>
                            <div class="empty-chat">
                                <i class="fas fa-comments"></i>
                                <h5>Nenhuma mensagem ainda</h5>
                                <p>Envie uma mensagem para iniciar a conversa com <?= htmlspecialchars($dados['conversa']->contato_nome) ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($dados['mensagens'] as $mensagem): ?>
                                <?php 
                                $isUsuario = $mensagem->remetente_id == $_SESSION['usuario_id'];
                                $messageClass = $isUsuario ? 'sent' : 'received';
                                $bubbleClass = $isUsuario ? 'sent' : 'received';
                                ?>
                                <div class="message-wrapper <?= $messageClass ?>" data-message-id="<?= $mensagem->id ?>">
                                    <div class="message-bubble <?= $bubbleClass ?>">
                                        <?php if ($mensagem->tipo == 'text'): ?>
                                            <div class="message-content">
                                                <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                            </div>
                                        <?php elseif ($mensagem->tipo == 'button'): ?>
                                            <div class="message-content">
                                                <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                            </div>
                                        <?php elseif ($mensagem->tipo == 'image'): ?>
                                            <div class="message-media">
                                                <img src="<?= URL ?>/<?= $mensagem->midia_url ?>" alt="Imagem" target="_blank" height="100" width="100" class="img-fluid">
                                            </div>
                                            <?php if (!empty($mensagem->conteudo)): ?>
                                                <div class="message-content">
                                                    <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($mensagem->tipo == 'video'): ?>
                                            <div class="message-media">
                                                <video controls class="w-100">
                                                    <source src="<?= URL ?>/<?= $mensagem->midia_url ?>" target="_blank" type="video/mp4">
                                                    Seu navegador não suporta vídeos HTML5.
                                                </video>
                                            </div>
                                            <?php if (!empty($mensagem->conteudo)): ?>
                                                <div class="message-content">
                                                    <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($mensagem->tipo == 'audio'): ?>
                                            <div class="message-media">
                                                <audio controls class="w-100">
                                                    <source src="<?= URL ?>/<?= $mensagem->midia_url ?>" target="_blank" type="audio/mpeg">
                                                    Seu navegador não suporta áudios HTML5.
                                                </audio>
                                            </div>
                                        <?php elseif ($mensagem->tipo == 'document'): ?>
                                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background: rgba(255,255,255,0.1);">
                                                <i class="fas fa-file-alt fa-2x"></i>
                                                <div class="flex-fill">
                                                    <a href="<?= URL ?>/<?= $mensagem->midia_url ?>" target="_blank" class="text-decoration-none">
                                                        <strong><?= htmlspecialchars($mensagem->midia_nome ?? 'Documento') ?></strong>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="message-time">
                                            <span><?= date('H:i', strtotime($mensagem->enviado_em)) ?></span>
                                            <?php if ($isUsuario): ?>
                                                <span class="message-status">
                                                    <?php if ($mensagem->status == 'enviado'): ?>
                                                        <i class="fas fa-check" title="Enviado"></i> <span class="status-badge enviado">Enviado</span>
                                                    <?php elseif ($mensagem->status == 'entregue'): ?>
                                                        <i class="fas fa-check-double" title="Entregue"></i> <span class="status-badge entregue">Entregue</span>
                                                    <?php elseif ($mensagem->status == 'lido'): ?>
                                                        <i class="fas fa-check-double text-lido" title="Lido"></i> <span class="status-badge lido">Lido</span>
                                                    <?php elseif ($mensagem->status == 'falhou'): ?>
                                                        <i class="fas fa-exclamation-triangle text-danger" title="Falhou"></i> <span class="status-badge falhou">Falhou</span>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock" title="Pendente"></i> <span class="status-badge pendente">Pendente</span>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Indicador de digitação (será controlado via JS) -->
                        <div class="typing-indicator d-none" id="typingIndicator">
                            <div class="message-wrapper received">
                                <div class="message-bubble received">
                                    <div class="loading-indicator">
                                        Digitando
                                        <div class="loading-dots">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview de arquivo (quando selecionado) -->
                    <div class="file-preview d-none" id="filePreview">
                        <div class="d-flex align-items-center gap-3">
                            <div class="file-icon" id="fileIcon">
                                <i class="fas fa-file fa-2x text-muted"></i>
                            </div>
                            <div class="file-preview-info flex-grow-1">
                                <div class="fw-bold" id="fileName"></div>
                                <small class="text-muted" id="fileSize"></small>
                                <div class="progress mt-1 d-none" id="uploadProgress" style="height: 3px;">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFilePreview()" id="removeFileBtn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Área de Input -->
                    <div class="chat-input-area">
                        <form action="<?= URL ?>/chat/enviarMensagem/<?= $dados['conversa']->id ?>" method="POST" enctype="multipart/form-data" id="messageForm">
                            <div class="input-group-modern">
                                <button type="button" class="btn-attachment" onclick="document.getElementById('fileInput').click()" title="Anexar arquivo" id="attachBtn">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <textarea 
                                    class="message-input" 
                                    name="mensagem" 
                                    id="messageInput" 
                                    placeholder="Digite sua mensagem ou anexe um arquivo..." 
                                    rows="1"></textarea>
                                <button type="submit" class="btn-send" id="sendButton" disabled title="Enviar mensagem">
                                    <i class="fas fa-paper-plane" id="sendIcon"></i>
                                </button>
                            </div>
                            <input type="file" id="fileInput" name="midia" style="display: none;" 
                                   accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx">
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal Editar Conversa -->
<div class="modal fade" id="modalEditarConversa" tabindex="-1" aria-labelledby="modalEditarConversaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/atualizarConversa/<?= $dados['conversa']->id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarConversaLabel">
                        <i class="fas fa-edit me-2"></i> Editar Contato
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome do Contato</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($dados['conversa']->contato_nome) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($dados['conversa']->contato_numero) ?>" readonly>
                        <small class="form-text text-muted">O número não pode ser alterado</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const statusIndicator = document.getElementById('statusIndicator');
    const apiStatusText = document.getElementById('apiStatusText');
    
    let lastMessageId = <?= !empty($dados['mensagens']) ? end($dados['mensagens'])->id : 0 ?>;
    let isApiOnline = null; // null = verificando, true = online, false = offline
    
    // Scroll para o final das mensagens
    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    // Scroll inicial
    scrollToBottom();
    
    // Auto-resize do textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        
        // Habilitar/desabilitar botão de envio
        updateSendButton();
    });
    
    // Enter para enviar (Shift+Enter para nova linha)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendButton.disabled) {
                document.getElementById('messageForm').submit();
            }
        }
    });
    
    // Preview de arquivo
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validar arquivo
            if (!validateFile(file)) {
                this.value = '';
                return;
            }
            
            // Atualizar preview
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            updateFileIcon(file.type);
            filePreview.classList.remove('d-none');
            
            // Atualizar botão de envio
            updateSendButton();
        } else {
            filePreview.classList.add('d-none');
            updateSendButton();
        }
    });
    
    // Função para atualizar o botão de envio
    function updateSendButton() {
        const hasText = messageInput.value.trim();
        const hasFile = fileInput.files.length > 0;
        
        // Habilitar botão se há texto ou arquivo, mesmo se a API ainda está sendo verificada
        // Apenas desabilitar se explicitamente sabemos que a API está offline
        sendButton.disabled = (!hasText && !hasFile) || (isApiOnline === false);
    }
    
    // Validar arquivo
    function validateFile(file) {
        const allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'video/mp4', 'video/3gpp',
            'audio/aac', 'audio/amr', 'audio/mpeg', 'audio/mp4', 'audio/ogg',
            'application/pdf', 'application/msword', 'text/plain',
            'application/vnd.ms-powerpoint', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Tipo de arquivo não permitido: ' + file.type);
            return false;
        }
        
        // Verificar tamanho
        let maxSize = 5 * 1024 * 1024; // 5MB padrão
        if (file.type.startsWith('video/') || file.type.startsWith('audio/')) {
            maxSize = 16 * 1024 * 1024; // 16MB
        } else if (file.type.startsWith('application/')) {
            maxSize = 95 * 1024 * 1024; // 95MB
        }
        
        if (file.size > maxSize) {
            const maxMB = Math.round(maxSize / (1024 * 1024));
            alert(`Arquivo muito grande. Limite: ${maxMB}MB`);
            return false;
        }
        
        return true;
    }
    
    // Atualizar ícone do arquivo
    function updateFileIcon(fileType) {
        const iconElement = document.querySelector('#fileIcon i');
        
        if (fileType.startsWith('image/')) {
            iconElement.className = 'fas fa-image fa-2x text-primary';
        } else if (fileType.startsWith('video/')) {
            iconElement.className = 'fas fa-video fa-2x text-danger';
        } else if (fileType.startsWith('audio/')) {
            iconElement.className = 'fas fa-music fa-2x text-warning';
        } else if (fileType === 'application/pdf') {
            iconElement.className = 'fas fa-file-pdf fa-2x text-danger';
        } else if (fileType.includes('word') || fileType.includes('document')) {
            iconElement.className = 'fas fa-file-word fa-2x text-primary';
        } else if (fileType.includes('excel') || fileType.includes('sheet')) {
            iconElement.className = 'fas fa-file-excel fa-2x text-success';
        } else if (fileType.includes('powerpoint') || fileType.includes('presentation')) {
            iconElement.className = 'fas fa-file-powerpoint fa-2x text-warning';
        } else {
            iconElement.className = 'fas fa-file fa-2x text-muted';
        }
    }
    
    // Remover preview de arquivo
    window.removeFilePreview = function() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        updateSendButton();
    };
    
    // Formatar tamanho do arquivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Evento de submit do formulário - apenas feedback visual
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        const sendIcon = document.getElementById('sendIcon');
        const sendButton = document.getElementById('sendButton');
        
        // Mostrar feedback visual
        sendButton.disabled = true;
        sendIcon.className = 'fas fa-spinner fa-spin';
        
        // Se há arquivo, mostrar progresso
        if (fileInput.files.length > 0) {
            const progressBar = document.getElementById('uploadProgress');
            progressBar.classList.remove('d-none');
        }
    });
    
    // Verificar status da API
    function checkApiStatus() {
        const timestamp = new Date().getTime();
        
        // Durante a verificação, mostrar status de carregamento
        statusIndicator.className = 'status-indicator warning';
        apiStatusText.textContent = 'Verificando...';
        
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
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("Resposta não é JSON válido!");
            }
            return response.json();
        })
        .then(data => {
            isApiOnline = data.online;
            
            if (data.online) {
                statusIndicator.className = 'status-indicator';
                apiStatusText.textContent = 'Online';
            } else {
                statusIndicator.className = 'status-indicator offline';
                apiStatusText.textContent = 'Offline';
            }
            updateSendButton();
        })
        .catch(error => {
            console.error('Erro ao verificar status da API:', error);
            statusIndicator.className = 'status-indicator warning';
            apiStatusText.textContent = 'Erro na verificação';
            
            // Em caso de erro na verificação, permitir tentativa de envio
            isApiOnline = null;
            updateSendButton();
        });
    }
    
    // Carregar novas mensagens
    function loadNewMessages() {
        fetch(`<?= URL ?>/chat/carregarNovasMensagens/<?= $dados['conversa']->id ?>/${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.mensagens && data.mensagens.length > 0) {
                    data.mensagens.forEach(message => {
                        addMessageToChat(message);
                        lastMessageId = message.id;
                    });
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar novas mensagens:', error);
            });
    }
    
    // Adicionar mensagem ao chat
    function addMessageToChat(message) {
        const isUser = message.remetente_id == <?= $_SESSION['usuario_id'] ?>;
        const messageClass = isUser ? 'sent' : 'received';
        const bubbleClass = isUser ? 'sent' : 'received';
        
        let content = '';
        let status = '';
        
        if (message.tipo === 'text') {
            content = `<div class="message-content">${message.conteudo}</div>`;
        } else if (message.tipo === 'image') {
            content = `
                <div class="message-media">
                    <img src="<?= URL ?>/${message.midia_url}" alt="Imagem" class="img-fluid">
                </div>
                ${message.conteudo ? `<div class="message-content">${message.conteudo}</div>` : ''}
            `;
        } else if (message.tipo === 'document') {
            content = `
                <div class="d-flex align-items-center gap-2 p-2 rounded" style="background: rgba(255,255,255,0.1);">
                    <i class="fas fa-file-alt fa-2x"></i>
                    <div class="flex-fill">
                        <a href="<?= URL ?>/${message.midia_url}" target="_blank" class="text-decoration-none">
                            <strong>${message.midia_nome || 'Documento'}</strong>
                        </a>
                    </div>
                </div>
            `;
        }
        
        if (isUser) {
            switch (message.status) {
                case 'enviado':
                    status = '<i class="fas fa-check" title="Enviado"></i> <span class="status-badge enviado">Enviado</span>';
                    break;
                case 'entregue':
                    status = '<i class="fas fa-check-double" title="Entregue"></i> <span class="status-badge entregue">Entregue</span>';
                    break;
                case 'lido':
                    status = '<i class="fas fa-check-double text-lido" title="Lido"></i> <span class="status-badge lido">Lido</span>';
                    break;
                case 'falhou':
                    status = '<i class="fas fa-exclamation-triangle text-danger" title="Falhou"></i> <span class="status-badge falhou">Falhou</span>';
                    break;
                default:
                    status = '<i class="fas fa-clock" title="Pendente"></i> <span class="status-badge pendente">Pendente</span>';
            }
        }
        
        const time = new Date(message.enviado_em).toLocaleTimeString('pt-BR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        const messageHTML = `
            <div class="message-wrapper ${messageClass}" data-message-id="${message.id}">
                <div class="message-bubble ${bubbleClass}">
                    ${content}
                    <div class="message-time">
                        <span>${time}</span>
                        ${isUser ? `<span class="message-status">${status}</span>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        chatMessages.insertAdjacentHTML('beforeend', messageHTML);
    }
    
    // Confirmar exclusão da conversa
    window.confirmarExclusao = function() {
        if (confirm('Tem certeza que deseja excluir esta conversa? Esta ação não pode ser desfeita.')) {
            window.location.href = `<?= URL ?>/chat/excluirConversa/<?= $dados['conversa']->id ?>`;
        }
    };
    
    // Verificar status das mensagens (automático)
    function verificarStatusMensagens() {
        fetch(`<?= URL ?>/chat/atualizarStatusMensagens/<?= $dados['conversa']->id ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.mensagens_atualizadas.length > 0) {
                    // Atualizar badges de status na tela
                    data.mensagens_atualizadas.forEach(mensagem => {
                        atualizarStatusMensagemNaTela(mensagem.id, mensagem.status_novo);
                    });
                    
                    console.log(`${data.mensagens_atualizadas.length} mensagens tiveram status atualizado automaticamente`);
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status das mensagens:', error);
            });
    }
    
    // Atualizar status de mensagem específica na tela
    function atualizarStatusMensagemNaTela(mensagemId, novoStatus) {
        const messageWrapper = document.querySelector(`[data-message-id="${mensagemId}"]`);
        if (messageWrapper) {
            const statusElement = messageWrapper.querySelector('.message-status');
            if (statusElement) {
                let novoHTML = '';
                
                switch (novoStatus) {
                    case 'enviado':
                        novoHTML = '<i class="fas fa-check" title="Enviado"></i> <span class="status-badge enviado">Enviado</span>';
                        break;
                    case 'entregue':
                        novoHTML = '<i class="fas fa-check-double" title="Entregue"></i> <span class="status-badge entregue">Entregue</span>';
                        break;
                    case 'lido':
                        novoHTML = '<i class="fas fa-check-double text-lido" title="Lido"></i> <span class="status-badge lido">Lido</span>';
                        break;
                    case 'falhou':
                        novoHTML = '<i class="fas fa-exclamation-triangle text-danger" title="Falhou"></i> <span class="status-badge falhou">Falhou</span>';
                        break;
                    default:
                        novoHTML = '<i class="fas fa-clock" title="Pendente"></i> <span class="status-badge pendente">Pendente</span>';
                }
                
                statusElement.innerHTML = novoHTML;
                
                // Adicionar animação de atualização
                statusElement.classList.add('status-updated');
                
                // Remover animação após completar
                setTimeout(() => {
                    statusElement.classList.remove('status-updated');
                }, 600);
            }
        }
    }
    
    // Verificações periódicas
    checkApiStatus();
    updateSendButton(); // Verificação inicial
    setInterval(checkApiStatus, 30000); // A cada 30 segundos
    setInterval(loadNewMessages, 5000); // A cada 5 segundos
    setInterval(verificarStatusMensagens, 15000); // A cada 15 segundos - verificar status das mensagens
});
</script>

<?php include 'app/Views/include/footer.php' ?>