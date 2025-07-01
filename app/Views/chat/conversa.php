<?php include 'app/Views/include/nav.php' ?>

<style>
/* === ESTILOS WHATSAPP-LIKE === */
:root {
    --whatsapp-green: #00a884;
    --whatsapp-green-light: #d9fdd3;
    --whatsapp-gray: #f0f0f0;
    --whatsapp-gray-dark: #e9edef;
    --whatsapp-bg: #efeae2;
    --whatsapp-teal: #00a884;
    --whatsapp-blue: #53bdeb;
    --shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
}

.chat-container {
    height: calc(100vh - 200px);
    min-height: 600px;
    display: flex;
    flex-direction: column;
    background: var(--whatsapp-bg);
    border-radius: 0;
    overflow: hidden;
    box-shadow: var(--shadow);
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    border: 1px solid #d1d7db;
}

/* Background pattern como WhatsApp */
.chat-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="260" height="260" viewBox="0 0 260 260"><g fill="%23f0f0f0" fill-opacity="0.4" fill-rule="evenodd"><g fill-rule="nonzero"><path d="M24.37 16c0 13.255-10.745 24-24 24S-23.63 29.255-23.63 16s10.745-24 24-24 24 10.745 24 24"/></g></g></svg>');
    opacity: 0.06;
    pointer-events: none;
    z-index: 1;
}

/* Header estilo WhatsApp */
.chat-header {
    background: #f0f0f0;
    background: linear-gradient(to bottom, #f0f0f0, #e9edef);
    padding: 10px 16px;
    border-bottom: 1px solid #d1d7db;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 10;
    min-height: 60px;
}

.contact-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.contact-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--whatsapp-teal), var(--whatsapp-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
    font-size: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    position: relative;
    flex-shrink: 0;
}

.contact-details h5 {
    margin: 0;
    color: #111b21;
    font-weight: 500;
    font-size: 16px;
    line-height: 1.2;
}

.contact-details small {
    color: #667781;
    font-size: 13px;
    margin-top: 2px;
    display: block;
}

/* Status da API estilo WhatsApp */
.api-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #667781;
    padding: 4px 8px;
    border-radius: 12px;
    background: rgba(255,255,255,0.7);
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--whatsapp-teal);
}

.status-indicator.offline {
    background: #f15c6d;
}

.status-indicator.warning {
    background: #ffab00;
}

/* Área de mensagens estilo WhatsApp */
.chat-messages {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    background: var(--whatsapp-bg);
    position: relative;
    z-index: 2;
    scroll-behavior: smooth;
}

/* Scrollbar estilo WhatsApp */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

/* Mensagens estilo WhatsApp */
.message-wrapper {
    margin-bottom: 2px;
    display: flex;
    align-items: flex-end;
    animation: messageSlideIn 0.3s ease-out;
}

@keyframes messageSlideIn {
    from { 
        opacity: 0;
        transform: translateY(10px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.message-wrapper.sent {
    justify-content: flex-end;
}

.message-wrapper.received {
    justify-content: flex-start;
}

/* Bolhas de mensagem estilo WhatsApp */
.message-bubble {
    max-width: 65%;
    padding: 6px 7px 8px 9px;
    border-radius: 7.5px;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
    margin: 0 8px 2px;
    font-size: 14px;
    line-height: 1.4;
}

.message-bubble.sent {
    background: #d9fdd3;
    color: #111b21;
    border-bottom-right-radius: 2px;
    margin-right: 0;
}

.message-bubble.received {
    background: #ffffff;
    color: #111b21;
    border-bottom-left-radius: 2px;
    margin-left: 0;
}

/* Cauda das mensagens estilo WhatsApp */
.message-bubble.sent::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: -6px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 0 13px 8px;
    border-color: transparent transparent #d9fdd3 transparent;
}

.message-bubble.received::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: -6px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 8px 13px 0;
    border-color: transparent #ffffff transparent transparent;
}

.message-content {
    margin: 0;
    word-break: break-word;
    white-space: pre-wrap;
}

/* Mídia nas mensagens */
.message-media {
    border-radius: 7.5px;
    overflow: hidden;
    margin-bottom: 4px;
    max-width: 100%;
    position: relative;
}

.message-media img, 
.message-media video {
    width: 100%;
    height: auto;
    display: block;
    max-width: 300px;
    border-radius: 7.5px;
}

.message-media audio {
    width: 100%;
    max-width: 250px;
    height: 32px;
}

/* Documentos estilo WhatsApp */
.document-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: rgba(0,0,0,0.04);
    border-radius: 7.5px;
    border: 1px solid rgba(0,0,0,0.1);
    max-width: 280px;
}

.document-icon {
    width: 48px;
    height: 48px;
    background: var(--whatsapp-teal);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-name {
    font-weight: 500;
    color: var(--whatsapp-teal);
    text-decoration: none;
    display: block;
    font-size: 14px;
    margin-bottom: 2px;
    word-break: break-all;
}

.document-name:hover {
    text-decoration: underline;
}

.document-size {
    font-size: 12px;
    color: #667781;
}

/* Horário das mensagens estilo WhatsApp */
.message-time {
    font-size: 11px;
    color: #667781;
    margin-top: 4px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    line-height: 1;
}

.message-bubble.sent .message-time {
    color: #53857c;
}

/* Status das mensagens estilo WhatsApp */
.message-status {
    display: inline-flex;
    align-items: center;
    margin-left: 4px;
}

.message-status i {
    font-size: 12px;
}

.message-status .fa-clock { color: #667781; }
.message-status .fa-check { color: #53857c; }
.message-status .fa-check-double { color: #53857c; }
.message-status .fa-check-double.text-lido { color: #4fc3f7; }

/* Área de input estilo WhatsApp */
.chat-input-area {
    background: #f0f0f0;
    padding: 8px 16px;
    border-top: 1px solid #d1d7db;
    position: relative;
    z-index: 10;
}

.input-group-modern {
    background: white;
    border-radius: 20px;
    padding: 6px 8px;
    display: flex;
    align-items: flex-end;
    gap: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    border: 1px solid #d1d7db;
    min-height: 42px;
}

.input-group-modern:focus-within {
    border-color: var(--whatsapp-teal);
}

.btn-attachment {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #54656f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.btn-attachment:hover {
    background: #f5f5f5;
    color: var(--whatsapp-teal);
}

.message-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 4px;
    font-size: 15px;
    resize: none;
    outline: none;
    max-height: 100px;
    min-height: 20px;
    font-family: inherit;
    line-height: 1.4;
    color: #111b21;
}

.message-input::placeholder {
    color: #8696a0;
}

.btn-send {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: var(--whatsapp-teal);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
    flex-shrink: 0;
}

.btn-send:hover:not(:disabled) {
    background: #00926c;
    transform: scale(1.05);
}

.btn-send:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

/* Preview de arquivo estilo WhatsApp */
.file-preview {
    background: white;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    border: 1px solid #d1d7db;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from { 
        opacity: 0;
        transform: translateY(-10px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.file-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--whatsapp-teal);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.file-preview-info {
    flex: 1;
    min-width: 0;
}

.file-preview-info .fw-bold {
    font-size: 14px;
    color: #111b21;
    margin-bottom: 2px;
    word-break: break-all;
}

.file-preview-info small {
    color: #667781;
    font-size: 12px;
}

/* Chat vazio estilo WhatsApp */
.empty-chat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #667781;
    text-align: center;
    animation: fadeIn 0.5s ease-out;
}

.empty-chat i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.4;
    color: #d1d7db;
}

.empty-chat h5 {
    color: #111b21;
    margin-bottom: 8px;
    font-weight: 400;
}

.empty-chat p {
    color: #667781;
    font-size: 14px;
    max-width: 300px;
    line-height: 1.4;
}

/* Indicador de digitação estilo WhatsApp */
.typing-indicator {
    margin-bottom: 8px;
}

.loading-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #667781;
    font-size: 13px;
    font-style: italic;
}

.loading-dots {
    display: inline-flex;
    gap: 2px;
}

.loading-dots span {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #667781;
    animation: loadingPulse 1.4s infinite ease-in-out;
}

.loading-dots span:nth-child(1) { animation-delay: -0.32s; }
.loading-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes loadingPulse {
    0%, 80%, 100% { 
        transform: scale(0.6); 
        opacity: 0.4; 
    }
    40% { 
        transform: scale(1); 
        opacity: 1; 
    }
}

/* Dropdown melhorado */
.dropdown-menu {
    border-radius: 8px;
    border: none;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    background: white;
    padding: 4px;
}

.dropdown-item {
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 14px;
    transition: all 0.2s ease;
    margin: 1px 0;
}

.dropdown-item:hover {
    background: #f5f5f5;
    color: #111b21;
}

/* Responsivo melhorado */
@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 120px);
        border-radius: 0;
        margin: 0;
        max-width: none;
        border-left: none;
        border-right: none;
    }
    
    .message-bubble {
        max-width: 80%;
        font-size: 14px;
    }
    
    .chat-header {
        padding: 8px 12px;
        min-height: 56px;
    }
    
    .contact-avatar {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    
    .chat-messages {
        padding: 8px;
    }
    
    .input-group-modern {
        padding: 4px 6px;
        min-height: 38px;
    }
    
    .btn-send, .btn-attachment {
        width: 28px;
        height: 28px;
        font-size: 13px;
    }
    
    .message-input {
        font-size: 14px;
        padding: 6px 4px;
    }
}

/* Animações suaves */
.fade-in {
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(20px);
    }
    to { 
        opacity: 1; 
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<main>
    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <!-- Botão Voltar -->
                <div class="mb-3">
                    <a href="<?= URL ?>/chat/index" class="btn btn-secondary btn-sm">
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
                                                <audio controls class="">
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