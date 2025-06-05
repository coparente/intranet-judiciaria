<?php include 'app/Views/include/nav.php' ?>

<style>

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
                                        <?php elseif ($mensagem->tipo == 'image'): ?>
                                            <div class="message-media">
                                                <img src="<?= URL ?>/<?= $mensagem->midia_url ?>" alt="Imagem" class="img-fluid">
                                            </div>
                                            <?php if (!empty($mensagem->conteudo)): ?>
                                                <div class="message-content">
                                                    <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($mensagem->tipo == 'video'): ?>
                                            <div class="message-media">
                                                <video controls class="w-100">
                                                    <source src="<?= URL ?>/<?= $mensagem->midia_url ?>" type="video/mp4">
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
                                                    <source src="<?= URL ?>/<?= $mensagem->midia_url ?>" type="audio/mpeg">
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
                                                        <i class="fas fa-check" title="Enviado"></i>
                                                    <?php elseif ($mensagem->status == 'entregue'): ?>
                                                        <i class="fas fa-check-double" title="Entregue"></i>
                                                    <?php elseif ($mensagem->status == 'lido'): ?>
                                                        <i class="fas fa-check-double text-info" title="Lido"></i>
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
                            <i class="fas fa-file fa-2x"></i>
                            <div class="file-preview-info">
                                <div class="fw-bold" id="fileName"></div>
                                <small id="fileSize"></small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light" onclick="removeFilePreview()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Área de Input -->
                    <div class="chat-input-area">
                        <form action="<?= URL ?>/chat/enviarMensagem/<?= $dados['conversa']->id ?>" method="POST" enctype="multipart/form-data" id="messageForm">
                            <div class="input-group-modern">
                                <button type="button" class="btn-attachment" onclick="document.getElementById('fileInput').click()" title="Anexar arquivo">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <textarea 
                                    class="message-input" 
                                    name="mensagem" 
                                    id="messageInput" 
                                    placeholder="Digite sua mensagem..." 
                                    rows="1"
                                    required></textarea>
                                <button type="submit" class="btn-send" id="sendButton" disabled title="Enviar mensagem">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <input type="file" id="fileInput" name="midia" style="display: none;" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt">
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
    let isApiOnline = false;
    
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
        sendButton.disabled = !this.value.trim() || !isApiOnline;
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
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.remove('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });
    
    // Remover preview de arquivo
    window.removeFilePreview = function() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
    };
    
    // Formatar tamanho do arquivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Verificar status da API
    function checkApiStatus() {
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
                sendButton.disabled = !messageInput.value.trim();
            } else {
                statusIndicator.className = 'status-indicator offline';
                apiStatusText.textContent = 'Offline';
                sendButton.disabled = true;
            }
        })
        .catch(error => {
            console.error('Erro ao verificar status da API:', error);
            statusIndicator.className = 'status-indicator warning';
            apiStatusText.textContent = 'Erro';
            isApiOnline = false;
            sendButton.disabled = true;
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
                    status = '<i class="fas fa-check" title="Enviado"></i>';
                    break;
                case 'entregue':
                    status = '<i class="fas fa-check-double" title="Entregue"></i>';
                    break;
                case 'lido':
                    status = '<i class="fas fa-check-double text-info" title="Lido"></i>';
                    break;
                default:
                    status = '<i class="fas fa-clock" title="Pendente"></i>';
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
    
    // Verificações periódicas
    checkApiStatus();
    setInterval(checkApiStatus, 30000); // A cada 30 segundos
    setInterval(loadNewMessages, 5000); // A cada 5 segundos
});
</script>

<?php include 'app/Views/include/footer.php' ?>