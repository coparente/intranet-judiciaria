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
                    
                    <!-- Botão Voltar -->
                    <div class="mb-3">
                        <a href="<?= URL ?>/chat/index" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Voltar para Conversas
                        </a>
                    </div>

                    <!-- Container do Chat estilo WhatsApp -->
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
                                        <i class="fas fa-sync-alt"></i>
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
                                                    <img src="<?= URL ?>/media/<?= $mensagem->midia_url ?>" alt="Imagem" class="img-thumbnail">
                                                </div>
                                                <?php if (!empty($mensagem->conteudo) && $mensagem->conteudo !== $mensagem->midia_url): ?>
                                                    <div class="message-content">
                                                        <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($mensagem->tipo == 'video'): ?>
                                                <div class="message-media">
                                                    <video controls>
                                                        <source src="<?= URL ?>/media/<?= $mensagem->midia_url ?>" type="video/mp4">
                                                        Seu navegador não suporta vídeos HTML5.
                                                    </video>
                                                </div>
                                                <?php if (!empty($mensagem->conteudo) && $mensagem->conteudo !== $mensagem->midia_url): ?>
                                                    <div class="message-content">
                                                        <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($mensagem->tipo == 'audio'): ?>
                                                <div class="message-media">
                                                    <audio controls class="w-100">
                                                        <source src="<?= URL ?>/media/<?= $mensagem->midia_url ?>" type="audio/mpeg">
                                                        Seu navegador não suporta áudios HTML5.
                                                    </audio>
                                                </div>
                                            <?php elseif ($mensagem->tipo == 'document'): ?>
                                                <div class="document-preview">
                                                    <div class="document-icon">
                                                        <i class="fas fa-file-alt"></i>
                                                    </div>
                                                    <div class="document-info">
                                                        <a href="<?= URL ?>/media/<?= $mensagem->midia_url ?>" target="_blank" class="document-name">
                                                            <?= htmlspecialchars($mensagem->midia_nome ?? 'Documento') ?>
                                                        </a>
                                                        <div class="document-size">Clique para baixar</div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="message-time">
                                                <span><?= date('d/m/Y H:i', strtotime($mensagem->enviado_em)) ?></span>
                                                <?php if ($isUsuario): ?>
                                                    <span class="message-status">
                                                        <?php if ($mensagem->status == 'enviado'): ?>
                                                            <i class="fas fa-check" title="Enviado"></i>
                                                        <?php elseif ($mensagem->status == 'entregue'): ?>
                                                            <i class="fas fa-check-double" title="Entregue"></i>
                                                        <?php elseif ($mensagem->status == 'lido'): ?>
                                                            <i class="fas fa-check-double text-lido" title="Lido"></i>
                                                        <?php elseif ($mensagem->status == 'falhou'): ?>
                                                            <i class="fas fa-exclamation-triangle text-danger" title="Falhou"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-clock" title="Pendente"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Indicador de digitação -->
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

                        <!-- Preview de arquivo -->
                        <div class="file-preview d-none" id="filePreview">
                            <div class="file-icon" id="fileIcon">
                                <i class="fas fa-file fa-lg"></i>
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
                                        placeholder="Digite uma mensagem" 
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
// JavaScript melhorado para experiência WhatsApp-like
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
    let isApiOnline = null;
    
    // Scroll suave para o final
    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    // Scroll inicial
    setTimeout(scrollToBottom, 100);
    
    // Auto-resize do textarea mais suave
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        updateSendButton();
    });
    
    // Enter para enviar
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendButton.disabled) {
                document.getElementById('messageForm').submit();
            }
        }
    });
    
    // Preview de arquivo melhorado
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            if (!validateFile(file)) {
                this.value = '';
                return;
            }
            
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            updateFileIcon(file.type);
            filePreview.classList.remove('d-none');
            updateSendButton();
        } else {
            filePreview.classList.add('d-none');
            updateSendButton();
        }
    });
    
    function updateSendButton() {
        const hasText = messageInput.value.trim();
        const hasFile = fileInput.files.length > 0;
        sendButton.disabled = (!hasText && !hasFile) || (isApiOnline === false);
    }
    
    function validateFile(file) {
        const allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/3gpp', 'video/quicktime',
            'audio/aac', 'audio/amr', 'audio/mpeg', 'audio/mp4', 'audio/ogg',
            'application/pdf', 'application/msword', 'text/plain',
            'application/vnd.ms-powerpoint', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Tipo de arquivo não permitido');
            return false;
        }
        
        let maxSize = 5 * 1024 * 1024; // 5MB padrão
        if (file.type.startsWith('video/') || file.type.startsWith('audio/')) {
            maxSize = 16 * 1024 * 1024; // 16MB
        } else if (file.type.startsWith('application/')) {
            maxSize = 95 * 1024 * 1024; // 95MB
        }
        
        if (file.size > maxSize) {
            const maxMB = Math.round(maxSize / (1024 * 1024));
            alert(`❌ Arquivo muito grande. Limite: ${maxMB}MB`);
            return false;
        }
        
        return true;
    }
    
    function updateFileIcon(fileType) {
        const iconElement = document.querySelector('#fileIcon i');
        const fileIcon = document.getElementById('fileIcon');
        
        if (fileType.startsWith('image/')) {
            iconElement.className = 'fas fa-image';
            fileIcon.style.background = '#4285f4';
        } else if (fileType.startsWith('video/')) {
            iconElement.className = 'fas fa-video';
            fileIcon.style.background = '#ea4335';
        } else if (fileType.startsWith('audio/')) {
            iconElement.className = 'fas fa-music';
            fileIcon.style.background = '#fbbc05';
        } else if (fileType === 'application/pdf') {
            iconElement.className = 'fas fa-file-pdf';
            fileIcon.style.background = '#d93025';
        } else {
            iconElement.className = 'fas fa-file-alt';
            fileIcon.style.background = 'var(--whatsapp-teal)';
        }
    }
    
    window.removeFilePreview = function() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        updateSendButton();
    };
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Feedback visual no envio
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        const sendIcon = document.getElementById('sendIcon');
        sendButton.disabled = true;
        sendIcon.className = 'fas fa-spinner fa-spin';
        
        if (fileInput.files.length > 0) {
            const progressBar = document.getElementById('uploadProgress');
            progressBar.classList.remove('d-none');
        }
    });
    
    // Verificar status da API
    function checkApiStatus() {
        fetch(`<?= URL ?>/chat/verificarStatusAPI?_=${Date.now()}`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            isApiOnline = data.online;
            statusIndicator.className = data.online ? 'status-indicator' : 'status-indicator offline';
            apiStatusText.textContent = data.online ? 'online' : 'offline';
            updateSendButton();
        })
        .catch(error => {
            statusIndicator.className = 'status-indicator warning';
            apiStatusText.textContent = 'erro';
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
            .catch(error => console.error('Erro ao carregar mensagens:', error));
    }
    
    function addMessageToChat(message) {
        const isUser = message.remetente_id == <?= $_SESSION['usuario_id'] ?>;
        const messageClass = isUser ? 'sent' : 'received';
        const bubbleClass = isUser ? 'sent' : 'received';
        
        let content = '';
        if (message.tipo === 'text') {
            content = `<div class="message-content">${message.conteudo}</div>`;
        } else if (message.tipo === 'document') {
            content = `
                <div class="document-preview">
                    <div class="document-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="document-info">
                        <a href="<?= URL ?>/media/${message.midia_url}" target="_blank" class="document-name">
                            ${message.midia_nome || 'Documento'}
                        </a>
                        <div class="document-size">Clique para baixar</div>
                    </div>
                </div>
            `;
        }
        
        const messageHTML = `
            <div class="message-wrapper ${messageClass}" data-message-id="${message.id}">
                <div class="message-bubble ${bubbleClass}">
                    ${content}
                    <div class="message-time">
                        <span>${new Date(message.enviado_em).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})}</span>
                        ${isUser ? '<span class="message-status"><i class="fas fa-clock"></i></span>' : ''}
                    </div>
                </div>
            </div>
        `;
        
        chatMessages.insertAdjacentHTML('beforeend', messageHTML);
    }
    
    window.confirmarExclusao = function() {
        if (confirm('Tem certeza que deseja excluir esta conversa? Esta ação não pode ser desfeita.')) {
            window.location.href = `<?= URL ?>/chat/excluirConversa/<?= $dados['conversa']->id ?>`;
        }
    };
    
    // Inicializar
    checkApiStatus();
    updateSendButton();
    
    // Timers
    setInterval(checkApiStatus, 30000);
    setInterval(loadNewMessages, 5000);
});
</script>

<?php include 'app/Views/include/footer.php' ?>