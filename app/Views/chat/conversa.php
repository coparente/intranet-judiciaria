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
                                    
                                    <!-- NOVO: Status do Ticket -->
                                    <?php if (isset($dados['conversa']->status_atendimento)): ?>
                                        <div class="ticket-status mt-1">
                                            <?php
                                            $statusClass = [
                                                'aberto' => 'badge-danger',
                                                'em_andamento' => 'badge-warning',
                                                'aguardando_cliente' => 'badge-info',
                                                'resolvido' => 'badge-success',
                                                'fechado' => 'badge-secondary'
                                            ];
                                            $statusNomes = [
                                                'aberto' => 'Aberto',
                                                'em_andamento' => 'Em Andamento',
                                                'aguardando_cliente' => 'Aguardando Cliente',
                                                'resolvido' => 'Resolvido',
                                                'fechado' => 'Fechado'
                                            ];
                                            $status = $dados['conversa']->status_atendimento ?? 'aberto';
                                            ?>
                                            <span class="badge <?= $statusClass[$status] ?? 'badge-secondary' ?>">
                                                <i class="fas fa-ticket-alt me-1"></i>
                                                <?= $statusNomes[$status] ?? 'Desconhecido' ?>
                                            </span>
                                            
                                            <?php if (isset($dados['conversa']->ticket_aberto_em)): ?>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Aberto em: <?= date('d/m/Y H:i', strtotime($dados['conversa']->ticket_aberto_em)) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="api-status">
                                    <div class="status-indicator" id="statusIndicator"></div>
                                    <span id="apiStatusText">Verificando...</span>
                                </div>
                                
                                <!-- NOVO: Botões de Controle de Ticket -->
                                <?php if (!isset($dados['bloqueado']) || !$dados['bloqueado']): ?>
                                    <div class="ticket-controls">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                <i class="fas fa-ticket-alt me-1"></i> Ticket
                                            </button>
                                            <div class="dropdown-menu">
                                                <?php if ($dados['conversa']->status_atendimento !== 'fechado'): ?>
                                                    <a class="dropdown-item" href="#" onclick="alterarStatusTicket('em_andamento')">
                                                        <i class="fas fa-play text-warning me-2"></i> Em Andamento
                                                    </a>
                                                    <a class="dropdown-item" href="#" onclick="alterarStatusTicket('aguardando_cliente')">
                                                        <i class="fas fa-clock text-info me-2"></i> Aguardando Cliente
                                                    </a>
                                                    <!-- <a class="dropdown-item" href="#" onclick="alterarStatusTicket('resolvido')">
                                                        <i class="fas fa-check text-success me-2"></i> Resolvido
                                                    </a> -->
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" onclick="encerrarTicket()">
                                                        <i class="fas fa-times-circle me-2"></i> Encerrar Ticket
                                                    </a>
                                                <?php else: ?>
                                                    <a class="dropdown-item text-success" href="#" onclick="reabrirTicket()">
                                                        <i class="fas fa-redo me-2"></i> Reabrir Ticket
                                                    </a>
                                                <?php endif; ?>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="<?= URL ?>/chat/historicoTicket/<?= $dados['conversa']->id ?>">
                                                    <i class="fas fa-history me-2"></i> Ver Histórico
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

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

                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalDiagnosticoAudio">
                                            <i class="fas fa-stethoscope me-2"></i> Diagnóstico de Áudio
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" id="testeAudioBtn">
                                            <i class="fas fa-microphone me-2"></i> Testar Áudio Gravado
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
                                        <div class="message-bubble <?= $bubbleClass ?> <?= $mensagem->tipo == 'audio' ? 'has-audio' : '' ?>">
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
                                                    <?php if (!empty($mensagem->midia_url)): ?>
                                                        <img src="<?= URL ?>/media/<?= $mensagem->midia_url ?>" 
                                                             alt="Imagem" 
                                                             class="img-thumbnail"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                        <div class="image-error" style="display:none; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center;">
                                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                                            <p class="mb-0 mt-2">Erro ao carregar imagem</p>
                                                            <small class="text-muted">Caminho: <?= htmlspecialchars($mensagem->midia_url) ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="image-error" style="padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center;">
                                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                                            <p class="mb-0 mt-2">Imagem não disponível</p>
                                                            <small class="text-muted">Caminho de mídia não encontrado</small>
                                                        </div>
                                                    <?php endif; ?>
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
                                                    <audio controls class="audio-player">
                                                        <source src="<?= URL ?>/media/<?= $mensagem->midia_url ?>" type="audio/mpeg">
                                                        Seu navegador não suporta áudios HTML5.
                                                    </audio>
                                                    <div class="message-content">
                                                        <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                    </div>
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
                                                <?php if (!empty($mensagem->conteudo) && $mensagem->conteudo !== $mensagem->midia_url): ?>
                                                    <div class="message-content">
                                                        <?= nl2br(htmlspecialchars($mensagem->conteudo)) ?>
                                                    </div>
                                                <?php endif; ?>
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
                            <?php if (isset($dados['bloqueado']) && $dados['bloqueado']): ?>
                                <!-- Bloqueado devido a conflito de agentes -->
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-lock me-2"></i>
                                    <strong>Conversa Bloqueada</strong><br>
                                    <small>Esta conversa está sendo atendida por outro agente. Use o botão "Assumir Conversa" acima para poder enviar mensagens.</small>
                                </div>
                            <?php else: ?>
                                <form action="<?= URL ?>/chat/enviarMensagem/<?= $dados['conversa']->id ?>" method="POST" enctype="multipart/form-data" id="messageForm">
                                    <div class="input-group-modern">
                                        <button type="button" class="btn-quick-message" id="quickMessageBtn" title="Mensagens Rápidas" data-toggle="modal" data-target="#modalMensagensRapidas">
                                            <i class="fas fa-bolt"></i>
                                        </button>
                                        <button type="button" class="btn-attachment" onclick="document.getElementById('fileInput').click()" title="Anexar arquivo" id="attachBtn">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        <button type="button" class="btn-emoji" id="emojiBtn" title="Adicionar emoji">
                                            <i class="fas fa-smile"></i>
                                        </button>
                                        <button type="button" class="btn-voice" id="voiceBtn" title="Gravar áudio">
                                            <i class="fas fa-microphone"></i>
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
                                        accept="image/*,video/*,audio/mpeg,audio/aac,audio/ogg,audio/amr,.pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx">
                                    <input type="file" id="audioInput" name="audio_gravado" style="display: none;" accept="audio/mpeg,audio/aac,audio/ogg,audio/amr">
                                </form>

                                <!-- Controles de Gravação de Áudio -->
                                <div class="voice-recording d-none" id="voiceRecording">
                                    <div class="recording-controls">
                                        <div class="recording-indicator">
                                            <div class="recording-dot"></div>
                                            <span class="recording-text">Gravando...</span>
                                            <span class="recording-time" id="recordingTime">00:00</span>
                                        </div>
                                        <div class="recording-actions">
                                            <button type="button" class="btn-recording btn-cancel" id="cancelRecording" title="Cancelar gravação">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn-recording btn-stop" id="stopRecording" title="Parar e enviar">
                                                <i class="fas fa-stop"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
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

<!-- NOVO: Modal Encerrar Ticket -->
<div class="modal fade" id="modalEncerrarTicket" tabindex="-1" aria-labelledby="modalEncerrarTicketLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/alterarStatusTicket" method="POST">
                <input type="hidden" name="conversa_id" value="<?= $dados['conversa']->id ?>">
                <input type="hidden" name="status" value="fechado">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEncerrarTicketLabel">
                        <i class="fas fa-times-circle me-2"></i> Encerrar Ticket
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção!</strong> O ticket será encerrado e não será possível enviar novas mensagens até que seja reaberto.
                    </div>
                    <div class="form-group">
                        <label for="observacao_encerramento" class="form-label">Observação do encerramento</label>
                        <textarea class="form-control" id="observacao_encerramento" name="observacao" rows="3" placeholder="Descreva o motivo do encerramento (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle me-1"></i> Encerrar Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- NOVO: Modal Reabrir Ticket -->
<div class="modal fade" id="modalReabrirTicket" tabindex="-1" aria-labelledby="modalReabrirTicketLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/reabrirTicket" method="POST">
                <input type="hidden" name="conversa_id" value="<?= $dados['conversa']->id ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReabrirTicketLabel">
                        <i class="fas fa-redo me-2"></i> Reabrir Ticket
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informação:</strong> O ticket será reaberto e voltará ao status "Aberto".
                    </div>
                    <div class="form-group">
                        <label for="observacao_reabertura" class="form-label">Motivo da reabertura</label>
                        <textarea class="form-control" id="observacao_reabertura" name="observacao" rows="3" placeholder="Descreva o motivo da reabertura (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-redo me-1"></i> Reabrir Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Diagnóstico de Áudio -->
<div class="modal fade" id="modalDiagnosticoAudio" tabindex="-1" aria-labelledby="modalDiagnosticoAudioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDiagnosticoAudioLabel">
                    <i class="fas fa-microphone me-2"></i> Diagnóstico de Áudio
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Teste de Compatibilidade -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Teste de Compatibilidade</h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-sm" onclick="testarCompatibilidade()">
                            <i class="fas fa-play me-1"></i> Executar Teste
                        </button>
                        <div id="resultadoCompatibilidade" class="mt-2"></div>
                    </div>
                </div>

                <!-- Teste de Gravação -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-record-vinyl me-2"></i>Teste de Gravação</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-sm" id="btnIniciarTeste">
                                    <i class="fas fa-microphone me-1"></i> Iniciar Gravação Teste
                                </button>
                                <button type="button" class="btn btn-danger btn-sm d-none" id="btnPararTeste">
                                    <i class="fas fa-stop me-1"></i> Parar Gravação
                                </button>
                            </div>
                            <div class="col-md-6">
                                <span id="tempoGravacaoTeste" class="badge badge-info">00:00</span>
                            </div>
                        </div>
                        <div id="resultadoGravacao" class="mt-2"></div>
                        <div id="playerTeste" class="mt-2 d-none">
                            <audio controls style="width: 100%;"></audio>
                        </div>
                    </div>
                </div>

                <!-- Teste de Envio -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Teste de Envio</h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-warning btn-sm" id="btnTestarEnvio" disabled>
                            <i class="fas fa-upload me-1"></i> Testar Envio API
                        </button>
                        <div id="resultadoEnvio" class="mt-2"></div>
                    </div>
                </div>

                <!-- Log de Debug -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-terminal me-2"></i>Log de Debug</h6>
                    </div>
                    <div class="card-body">
                        <textarea id="logDebug" class="form-control" rows="8" readonly style="font-family: monospace; font-size: 12px;"></textarea>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="limparLog()">
                            <i class="fas fa-trash me-1"></i> Limpar Log
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- NOVO: Modal Mensagens Rápidas -->
<div class="modal fade" id="modalMensagensRapidas" tabindex="-1" aria-labelledby="modalMensagensRapidasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMensagensRapidasLabel">
                    <i class="fas fa-bolt me-2"></i> Mensagens Rápidas
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Instruções:</strong> Clique na mensagem desejada para inserir no campo de texto.
                </div>
                
                <!-- Loading -->
                <div class="text-center py-4" id="loadingMensagens">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Carregando mensagens...</p>
                </div>
                
                <!-- Lista de mensagens (carregada via AJAX) -->
                <div class="list-group" id="listaMensagensRapidas" style="display: none;">
                    <!-- Mensagens serão inseridas aqui via JavaScript -->
                </div>
                
                <!-- Mensagem de erro -->
                <div class="alert alert-warning" id="erroMensagens" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Aviso:</strong> Não foi possível carregar as mensagens rápidas.
                </div>
                
                <!-- Mensagem quando não há mensagens -->
                <div class="text-center py-4" id="semMensagens" style="display: none;">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Nenhuma mensagem rápida configurada</h6>
                    <p class="text-muted">Entre em contato com o administrador para configurar mensagens rápidas.</p>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Dica:</strong> Após inserir a mensagem, você pode editá-la antes de enviar.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Fechar
                </button>
                <?php if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])): ?>
                    <a href="<?= URL ?>/chat/gerenciarMensagensRapidas" class="btn btn-primary">
                        <i class="fas fa-cog me-1"></i> Gerenciar Mensagens
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Painel Seletor de Emojis -->
<div class="emoji-picker d-none" id="emojiPicker">
    <div class="emoji-header">
        <div class="emoji-categories">
            <button class="emoji-category active" data-category="pessoas" title="Pessoas">😀</button>
            <button class="emoji-category" data-category="gestos" title="Gestos">👋</button>
        </div>
    </div>
    <div class="emoji-content">
        <div class="emoji-grid" id="emojiGrid">
            <!-- Emojis serão carregados aqui via JavaScript -->
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

        // Elementos de gravação de áudio
        const voiceBtn = document.getElementById('voiceBtn');
        const audioInput = document.getElementById('audioInput');
        const voiceRecording = document.getElementById('voiceRecording');
        const recordingTime = document.getElementById('recordingTime');
        const cancelRecording = document.getElementById('cancelRecording');
        const stopRecording = document.getElementById('stopRecording');

        // Elementos do seletor de emojis
        const emojiBtn = document.getElementById('emojiBtn');
        const emojiPicker = document.getElementById('emojiPicker');
        const emojiGrid = document.getElementById('emojiGrid');
        const emojiCategories = document.querySelectorAll('.emoji-category');

        // Variáveis de gravação
        let mediaRecorder = null;
        let audioChunks = [];
        let recordingTimer = null;
        let recordingStartTime = 0;
        let isRecording = false;

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

                // Limpar audioInput se fileInput for usado
                audioInput.value = '';

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
            const hasAudio = audioInput.files.length > 0;
            sendButton.disabled = (!hasText && !hasFile && !hasAudio) || (isApiOnline === false);
        }

        function validateFile(file) {
            const allowedTypes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'video/mp4', 'video/3gpp', 'video/quicktime',
                'audio/aac', 'audio/amr', 'audio/mpeg', 'audio/ogg',
                'audio/ogg;codecs=opus', 'audio/ogg;codecs=vorbis', // OGG com codecs (padrão das mensagens recebidas)
                'application/pdf', 'application/msword', 'text/plain',
                'application/vnd.ms-powerpoint', 'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            // Verificação especial para arquivos de áudio gravados (priorizar OGG)
            const isAudioByExtension = /\.(ogg|m4a|mp3|aac|amr|mp4)$/i.test(file.name);
            const isAudioByType = file.type.indexOf('audio/') === 0;

            if (!allowedTypes.includes(file.type) && !isAudioByType && !isAudioByExtension) {
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
            audioInput.value = '';
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
                    headers: {
                        'Accept': 'application/json'
                    }
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

        // ========== FUNÇÕES DE GRAVAÇÃO DE ÁUDIO ==========

        // Inicializar gravação de áudio
        if (voiceBtn) {
            voiceBtn.addEventListener('click', function() {
                if (!isRecording) {
                    startRecording();
                }
            });
        }

        // Cancelar gravação
        if (cancelRecording) {
            cancelRecording.addEventListener('click', function() {
                stopRecordingProcess(false);
            });
        }

        // Parar e enviar gravação
        if (stopRecording) {
            stopRecording.addEventListener('click', function() {
                stopRecordingProcess(true);
            });
        }

        function startRecording() {
            // Verificar se o navegador suporta MediaRecorder
            if (!window.MediaRecorder) {
                alert('🎤 Seu navegador não suporta gravação de áudio. Use Chrome, Firefox ou Edge mais recentes.');
                return;
            }

            // Função para obter stream de áudio (compatível com HTTP e HTTPS)
            function getUserMedia(constraints) {
                // Verificar se estamos em um contexto inseguro (HTTP + IP)
                const isHTTP = location.protocol === 'http:';
                const isIP = /^\d+\.\d+\.\d+\.\d+$/.test(location.hostname);
                const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                if (isHTTP && isIP && !isLocalhost) {
                    return Promise.reject(new Error('ERRO_IP_HTTP: Acesso via IP em HTTP não é permitido pelos navegadores. Use HTTPS ou localhost.'));
                }

                // Tentar método moderno (HTTPS)
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    return navigator.mediaDevices.getUserMedia(constraints);
                }

                // Fallback para HTTP (desenvolvimento)
                const getUserMediaLegacy = navigator.getUserMedia ||
                    navigator.webkitGetUserMedia ||
                    navigator.mozGetUserMedia ||
                    navigator.msGetUserMedia;

                if (!getUserMediaLegacy) {
                    return Promise.reject(new Error('getUserMedia não suportado'));
                }

                // Converter callback para Promise
                return new Promise((resolve, reject) => {
                    getUserMediaLegacy.call(navigator, constraints, resolve, reject);
                });
            }

            // === CORREÇÃO: Configurações de áudio mais específicas ===
            const audioConstraints = {
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    sampleRate: 44100,
                    channelCount: 1
                }
            };

            // Solicitar permissão do microfone
            getUserMedia(audioConstraints)
                .then(function(stream) {

                    // === CORREÇÃO: Nova estratégia de seleção de formato ===
                    const options = {};

                    // Lista ordenada APENAS com formatos suportados pela API SERPRO
                    const formatosPreferidos = [
                        // Formatos aceitos pela API SERPRO (ordem de preferência)
                        'audio/mpeg', // MP3 - universalmente suportado
                        'audio/aac', // AAC - padrão móvel
                        'audio/ogg;codecs=opus', // OGG com codec opus
                        'audio/ogg', // OGG padrão
                        'audio/amr' // AMR - formato móvel
                    ];

                    let formatoEscolhido = null;

                    for (const formato of formatosPreferidos) {
                        const suportado = MediaRecorder.isTypeSupported(formato);

                        if (suportado && !formatoEscolhido) {
                            formatoEscolhido = formato;
                            break;
                        }
                    }

                    if (formatoEscolhido) {
                        options.mimeType = formatoEscolhido;
                    } else {
                        alert('⚠️ Aviso: Seu navegador não suporta formatos de áudio compatíveis com a API SERPRO (MP3, AAC, OGG, AMR). O áudio gravado pode falhar no envio.');
                        // Deixar sem mimeType para usar padrão do navegador
                    }

                    // === CORREÇÃO: Configurações de qualidade otimizadas para API SERPRO ===
                    if (formatoEscolhido && formatoEscolhido.includes('opus')) {
                        options.audioBitsPerSecond = 64000; // 64kbps para Opus
                    } else {
                        options.audioBitsPerSecond = 128000; // 128kbps para outros formatos
                    }

                    try {
                        mediaRecorder = new MediaRecorder(stream, options);
                        audioChunks = [];

                        // === CORREÇÃO: Melhorar coleta de dados ===
                        mediaRecorder.ondataavailable = function(event) {
                            if (event.data.size > 0) {
                                audioChunks.push(event.data);
                            }
                        };

                        mediaRecorder.onstop = function() {
                            stream.getTracks().forEach(track => track.stop());
                        };

                        mediaRecorder.onerror = function(event) {
                            alert('❌ Erro durante a gravação: ' + event.error.name);
                            hideRecordingUI();
                        };

                        // === CORREÇÃO: Interval otimizado para coleta de dados ===
                        const intervaloColeta = formatoEscolhido?.includes('webm') ? 500 : 1000;
                        mediaRecorder.start(intervaloColeta);

                        isRecording = true;
                        recordingStartTime = Date.now();

                        // Mostrar interface de gravação
                        showRecordingUI();

                        // Iniciar timer
                        startRecordingTimer();

                    } catch (error) {
                        alert('❌ Erro ao inicializar gravação: ' + error.message);

                        // Parar stream em caso de erro
                        stream.getTracks().forEach(track => track.stop());
                    }

                })
                .catch(function(error) {

                    let errorMessage = 'Erro ao acessar o microfone.';
                    if (error.name === 'NotAllowedError') {
                        errorMessage = 'Permissão para usar o microfone foi negada. Verifique as configurações do navegador.';
                    } else if (error.name === 'NotFoundError') {
                        errorMessage = 'Nenhum microfone encontrado.';
                    } else if (error.name === 'NotSupportedError') {
                        errorMessage = 'Gravação de áudio não suportada neste navegador.';
                    } else if (error.message.includes('getUserMedia')) {
                        errorMessage = 'getUserMedia não disponível. Tente acessar via HTTPS ou localhost.';
                    }

                    alert('🎤 ' + errorMessage);
                });
        }

        function stopRecordingProcess(shouldSend) {
            if (!isRecording || !mediaRecorder) {
                return;
            }

            isRecording = false;
            clearInterval(recordingTimer);

            if (shouldSend) {
                mediaRecorder.onstop = function() {
                    if (audioChunks.length === 0) {
                        alert('❌ Erro: Nenhum dado de áudio foi gravado. Tente gravar novamente.');
                        hideRecordingUI();
                        return;
                    }

                    // === CORREÇÃO: Usar tipo do MediaRecorder real, não forçar OGG ===
                    let mimeTypeReal = mediaRecorder.mimeType || 'audio/ogg';

                    // Criar blob com o tipo real
                    const audioBlob = new Blob(audioChunks, {
                        type: mimeTypeReal
                    });

                    if (audioBlob.size === 0) {
                        alert('❌ Erro: Áudio gravado está vazio. Tente gravar novamente.');
                        hideRecordingUI();
                        return;
                    }

                    // === CORREÇÃO: Determinar extensão e nome corretos ===
                    let extensao = '.ogg';
                    let tipoFinal = 'audio/ogg';

                    if (mimeTypeReal.includes('mpeg')) {
                        extensao = '.mp3';
                        tipoFinal = 'audio/mpeg';
                    } else if (mimeTypeReal.includes('aac')) {
                        extensao = '.aac';
                        tipoFinal = 'audio/aac';
                    } else if (mimeTypeReal.includes('amr')) {
                        extensao = '.amr';
                        tipoFinal = 'audio/amr';
                    } else {
                        // Manter OGG como padrão (formato mais compatível com API SERPRO)
                        tipoFinal = 'audio/ogg';
                    }

                    const timestamp = Date.now();
                    const nomeArquivo = `audio_gravado_${timestamp}${extensao}`;

                    // Criar File com tipo apropriado
                    const audioFile = new File([audioBlob], nomeArquivo, {
                        type: tipoFinal,
                        lastModified: timestamp
                    });

                    // Verificação final antes do envio
                    if (audioFile.size < 100) {
                        alert('❌ Erro: Gravação muito curta ou corrompida. Tente gravar por mais tempo.');
                        hideRecordingUI();
                        return;
                    }

                    // Enviar arquivo
                    uploadAudioFile(audioFile);
                    hideRecordingUI();
                };
            } else {
                hideRecordingUI();
            }

            mediaRecorder.stop();
        }

        function showRecordingUI() {
            voiceRecording.classList.remove('d-none');
            voiceBtn.classList.add('recording');
            messageInput.disabled = true;
            sendButton.disabled = true;
            document.getElementById('attachBtn').disabled = true;
        }

        function hideRecordingUI() {
            voiceRecording.classList.add('d-none');
            voiceBtn.classList.remove('recording');
            messageInput.disabled = false;
            document.getElementById('attachBtn').disabled = false;
            updateSendButton();
        }

        function startRecordingTimer() {
            recordingTimer = setInterval(function() {
                const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                recordingTime.textContent = `${minutes}:${seconds}`;

                // Limite de 10 minutos
                if (elapsed >= 600) {
                    alert('⏰ Gravação limitada a 10 minutos. Áudio será enviado automaticamente.');
                    stopRecordingProcess(true);
                }
            }, 1000);
        }

        function uploadAudioFile(audioFile) {
            // === CORREÇÃO: Sistema melhorado de feedback e validação ===

            // === VERIFICAÇÕES INICIAIS ===
            if (!audioFile || audioFile.size === 0) {
                alert('❌ Erro: Arquivo de áudio inválido. Tente gravar novamente.');
                return;
            }

            // Verificar tamanho mínimo mais rigoroso
            if (audioFile.size < 2048) { // 2KB mínimo
                alert('❌ Erro: Gravação muito curta. Grave por pelo menos 3 segundos.');
                return;
            }

            // Verificar tamanho máximo
            if (audioFile.size > 16 * 1024 * 1024) { // 16MB máximo
                alert('❌ Erro: Arquivo muito grande. Máximo: 16MB.');
                return;
            }

            // === CRIAR DATATRANSFER E ADICIONAR AO INPUT ===
            try {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(audioFile);

                // Limpar input anterior
                audioInput.value = '';
                fileInput.value = '';

                // Atribuir ao input de áudio
                audioInput.files = dataTransfer.files;

                // Verificar se foi adicionado corretamente
                if (audioInput.files.length === 0) {
                    alert('❌ Erro: Falha ao preparar arquivo para envio. Tente novamente.');
                    return;
                }

            } catch (error) {
                alert('❌ Erro: Falha ao processar arquivo. Tente novamente.');
                return;
            }

            // === ATUALIZAR PREVIEW ===
            try {
                fileName.textContent = audioFile.name;
                updateFileIcon(audioFile.type);
                filePreview.classList.remove('d-none');
                updateSendButton();

                // Simular evento change para disparar validações
                const event = new Event('change', {
                    bubbles: true
                });
                audioInput.dispatchEvent(event);

            } catch (error) {
                // Continue mesmo com erro no preview
            }

            // === SISTEMA DE ENVIO AUTOMÁTICO COM FEEDBACK ===
            let countdown = 3; // 3 segundos para o usuário cancelar se quiser
            let countdownInterval;

            // Função para atualizar display do countdown
            function atualizarCountdown() {
                if (fileSize) {
                    const tamanhoFormatado = formatFileSize(audioFile.size);
                    const formato = audioFile.type.replace('audio/', '').toUpperCase();

                    if (countdown > 0) {
                        fileSize.innerHTML = `
                        <div class="text-success">
                            <i class="fas fa-microphone me-1"></i>
                            <strong>Áudio ${formato} (${tamanhoFormatado})</strong><br>
                            <small>Enviando automaticamente em ${countdown}s... 
                            <span class="text-muted">(clique X para cancelar)</span></small>
                        </div>
                    `;
                    } else {
                        fileSize.innerHTML = `
                        <div class="text-primary">
                            <i class="fas fa-paper-plane me-1"></i>
                            <strong>Enviando áudio...</strong><br>
                            <small>Aguarde o processamento</small>
                        </div>
                    `;
                    }
                }
            }

            // Iniciar countdown
            atualizarCountdown();

            countdownInterval = setInterval(() => {
                countdown--;
                atualizarCountdown();

                if (countdown <= 0) {
                    clearInterval(countdownInterval);

                    // Verificar se o arquivo ainda está lá (usuário não cancelou)
                    if (audioInput.files.length > 0) {

                        // Desabilitar botão de cancelar durante envio
                        const removeBtn = document.getElementById('removeFileBtn');
                        if (removeBtn) {
                            removeBtn.disabled = true;
                            removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        }

                        // Enviar formulário
                        try {
                            document.getElementById('messageForm').submit();
                        } catch (error) {
                            alert('❌ Erro: Falha ao enviar. Tente novamente.');

                            // Restaurar botão
                            if (removeBtn) {
                                removeBtn.disabled = false;
                                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                            }
                        }
                    }
                }
            }, 1000);

            // === SUBSTITUIR FUNÇÃO DE REMOÇÃO TEMPORARIAMENTE ===
            const originalRemoveFunction = window.removeFilePreview;

            window.removeFilePreview = function() {
                // Parar countdown
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }

                // Chamar função original
                originalRemoveFunction();

                // Restaurar função original
                window.removeFilePreview = originalRemoveFunction;
            };
        }

        // === SISTEMA DE EMOJIS SIMPLES ===

        // Apenas pessoas e gestos como solicitado
        const emojis = {
            pessoas: [
                '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '☺️', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪',
                '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧',
                '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢',
                '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖'
            ],
            gestos: [
                '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '👊', '✊', '🤛', '🤜',
                '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄'
            ]
        };

        let currentEmojiCategory = 'pessoas';
        let isEmojiPickerOpen = false;

        // Função para abrir/fechar o seletor de emojis
        function toggleEmojiPicker() {
            if (isEmojiPickerOpen) {
                emojiPicker.classList.add('d-none');
                emojiBtn.classList.remove('active');
                isEmojiPickerOpen = false;
            } else {
                emojiPicker.classList.remove('d-none');
                emojiBtn.classList.add('active');
                isEmojiPickerOpen = true;
                loadEmojiCategory(currentEmojiCategory);
            }
        }

        // Função para carregar emojis da categoria
        function loadEmojiCategory(category) {
            // Atualizar categoria ativa
            document.querySelectorAll('.emoji-category').forEach(cat => {
                cat.classList.remove('active');
                if (cat.dataset.category === category) {
                    cat.classList.add('active');
                }
            });

            // Limpar grid atual
            emojiGrid.innerHTML = '';

            // Carregar emojis da categoria
            const categoryEmojis = emojis[category] || emojis.pessoas;
            categoryEmojis.forEach(emoji => {
                const emojiButton = document.createElement('button');
                emojiButton.className = 'emoji-item';
                emojiButton.textContent = emoji;
                emojiButton.title = emoji;
                emojiButton.onclick = () => insertEmoji(emoji);
                emojiGrid.appendChild(emojiButton);
            });

            currentEmojiCategory = category;
        }

        // Função para inserir emoji no campo de texto
        function insertEmoji(emoji) {
            const cursorPos = messageInput.selectionStart;
            const textBefore = messageInput.value.substring(0, cursorPos);
            const textAfter = messageInput.value.substring(messageInput.selectionEnd);

            // Inserir emoji na posição do cursor
            messageInput.value = textBefore + emoji + textAfter;

            // Mover cursor para depois do emoji
            const newCursorPos = cursorPos + emoji.length;
            messageInput.setSelectionRange(newCursorPos, newCursorPos);

            // Focar no campo de entrada
            messageInput.focus();

            // Atualizar botão de envio
            updateSendButton();
        }

        // Event listeners para emojis
        if (emojiBtn) {
            emojiBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleEmojiPicker();
            });
        }

        // Event listeners para categorias de emoji
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('emoji-category')) {
                loadEmojiCategory(e.target.dataset.category);
            }
        });

        // Fechar emoji picker ao clicar fora
        document.addEventListener('click', function(e) {
            if (isEmojiPickerOpen &&
                emojiPicker && !emojiPicker.contains(e.target) &&
                emojiBtn && !emojiBtn.contains(e.target)) {
                toggleEmojiPicker();
            }
        });

        // === BOTÃO DE TESTE DE ÁUDIO ===
        const testeAudioBtn = document.getElementById('testeAudioBtn');
        if (testeAudioBtn) {
            testeAudioBtn.addEventListener('click', function(e) {
                e.preventDefault();
                testarEnvioAudio();
            });
        }

        // Função para testar envio de áudio
        function testarEnvioAudio() {
            if (!audioInput.files.length) {
                alert('ℹ️ Nenhum áudio gravado encontrado. Grave um áudio primeiro usando o botão de microfone.');
                return;
            }

            const audioFile = audioInput.files[0];

            // Criar FormData para enviar
            const formData = new FormData();
            formData.append('audio_gravado', audioFile);
            formData.append('destinatario', '<?= $dados['conversa']->contato_numero ?>');
            formData.append('caption', 'Teste de áudio gravado - ' + new Date().toLocaleTimeString());

            // Mostrar loading
            const originalText = testeAudioBtn.innerHTML;
            testeAudioBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Testando...';
            testeAudioBtn.disabled = true;

            fetch('<?= URL ?>/chat/enviarMensagem/<?= $dados['conversa']->id ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {

                    let message = '';
                    if (data.success) {
                        message = '✅ SUCESSO!\n\nÁudio enviado com sucesso!';
                    } else {
                        message = '❌ FALHA!\n\nErro: ' + (data.error || 'Erro desconhecido');
                    }

                    alert(message);

                    // Restaurar botão
                    testeAudioBtn.innerHTML = originalText;
                    testeAudioBtn.disabled = false;
                })
                .catch(error => {
                    alert('❌ Erro ao testar áudio: ' + error.message);

                    // Restaurar botão
                    testeAudioBtn.innerHTML = originalText;
                    testeAudioBtn.disabled = false;
                });
        }

        // === FIM TESTE DE ÁUDIO ===

        // === SISTEMA DE DIAGNÓSTICO DE ÁUDIO ===

        let mediaRecorderTeste = null;
        let audioChunksTeste = [];
        let streamTeste = null;
        let recordingTimerTeste = null;
        let recordingStartTimeTeste = 0;
        let isRecordingTeste = false;
        let audioFileTeste = null;

        // Função para adicionar ao log de debug
        window.adicionarLog = function(texto) {
            const logDebug = document.getElementById('logDebug');
            if (logDebug) {
                const timestamp = new Date().toLocaleTimeString();
                logDebug.value += `[${timestamp}] ${texto}\n`;
                logDebug.scrollTop = logDebug.scrollHeight;
            }
        };

        // Função para limpar log
        window.limparLog = function() {
            const logDebug = document.getElementById('logDebug');
            if (logDebug) {
                logDebug.value = '';
            }
        };

        // Teste de compatibilidade
        window.testarCompatibilidade = function() {
            const resultDiv = document.getElementById('resultadoCompatibilidade');
            resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Testando...';

            let resultados = [];

            // 1. Verificar suporte básico
            const temMediaRecorder = !!window.MediaRecorder;
            const temGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

            resultados.push({
                teste: 'MediaRecorder disponível',
                resultado: temMediaRecorder,
                detalhes: temMediaRecorder ? 'Suportado' : 'Não suportado'
            });

            resultados.push({
                teste: 'getUserMedia disponível',
                resultado: temGetUserMedia,
                detalhes: temGetUserMedia ? 'Suportado' : 'Não suportado'
            });

            // 2. Testar formatos suportados
            if (temMediaRecorder) {
                const formatos = [
                    'audio/mpeg',
                    'audio/aac',
                    'audio/ogg;codecs=opus',
                    'audio/ogg',
                    'audio/amr'
                ];

                formatos.forEach(formato => {
                    const suportado = MediaRecorder.isTypeSupported(formato);
                    resultados.push({
                        teste: `Formato ${formato}`,
                        resultado: suportado,
                        detalhes: suportado ? 'Suportado' : 'Não suportado'
                    });
                });
            }

            // 3. Verificar contexto de segurança
            const isHTTPS = location.protocol === 'https:';
            const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            const contextoSeguro = isHTTPS || isLocalhost;

            resultados.push({
                teste: 'Contexto seguro (HTTPS/localhost)',
                resultado: contextoSeguro,
                detalhes: `${location.protocol}//${location.hostname}`
            });

            // Montar resultado visual
            let html = '<div class="mt-2">';

            resultados.forEach(item => {
                const icone = item.resultado ?
                    '<i class="fas fa-check-circle text-success"></i>' :
                    '<i class="fas fa-times-circle text-danger"></i>';

                html += `
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span>${item.teste}:</span>
                    <span>${icone} ${item.detalhes}</span>
                </div>
            `;
            });

            html += '</div>';

            // Resumo geral
            const todosSucessos = resultados.every(r => r.resultado);
            const classeResumo = todosSucessos ? 'alert-success' : 'alert-warning';
            const textoResumo = todosSucessos ?
                'Todos os testes passaram! Gravação de áudio deve funcionar.' :
                'Alguns testes falharam. Pode haver limitações na gravação.';

            html += `<div class="alert ${classeResumo} mt-2">${textoResumo}</div>`;

            resultDiv.innerHTML = html;
        };

        // Botões de teste de gravação
        document.addEventListener('DOMContentLoaded', function() {
            const btnIniciar = document.getElementById('btnIniciarTeste');
            const btnParar = document.getElementById('btnPararTeste');
            const btnTestarEnvio = document.getElementById('btnTestarEnvio');

            if (btnIniciar) {
                btnIniciar.addEventListener('click', window.iniciarGravacaoTeste);
            }

            if (btnParar) {
                btnParar.addEventListener('click', window.pararGravacaoTeste);
            }

            if (btnTestarEnvio) {
                btnTestarEnvio.addEventListener('click', window.testarEnvioAPI);
            }
        });

        // Iniciar gravação de teste
        window.iniciarGravacaoTeste = function() {
            const btnIniciar = document.getElementById('btnIniciarTeste');
            const btnParar = document.getElementById('btnPararTeste');
            const resultDiv = document.getElementById('resultadoGravacao');

            // Mesmo código de configuração da gravação principal, mas adaptado
            const audioConstraints = {
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    sampleRate: 44100,
                    channelCount: 1
                }
            };

            navigator.mediaDevices.getUserMedia(audioConstraints)
                .then(function(stream) {
                    streamTeste = stream;

                    // Detectar melhor formato disponível
                    const formatosPreferidos = [
                        'audio/mpeg',
                        'audio/aac',
                        'audio/ogg;codecs=opus',
                        'audio/ogg',
                        'audio/amr'
                    ];

                    let formatoEscolhido = null;
                    for (const formato of formatosPreferidos) {
                        if (MediaRecorder.isTypeSupported(formato)) {
                            formatoEscolhido = formato;
                            break;
                        }
                    }

                    const options = {};
                    if (formatoEscolhido) {
                        options.mimeType = formatoEscolhido;
                    }

                    try {
                        mediaRecorderTeste = new MediaRecorder(stream, options);
                        audioChunksTeste = [];

                        mediaRecorderTeste.ondataavailable = function(event) {
                            if (event.data.size > 0) {
                                audioChunksTeste.push(event.data);
                            }
                        };

                        mediaRecorderTeste.onstop = function() {
                            const totalSize = audioChunksTeste.reduce((total, chunk) => total + chunk.size, 0);

                            // Criar arquivo de teste
                            const audioBlob = new Blob(audioChunksTeste, {
                                type: mediaRecorderTeste.mimeType
                            });

                            // Determinar extensão
                            let extensao = '.ogg';
                            if (mediaRecorderTeste.mimeType.includes('mp3') || mediaRecorderTeste.mimeType.includes('mpeg')) extensao = '.mp3';
                            else if (mediaRecorderTeste.mimeType.includes('aac')) extensao = '.aac';
                            else if (mediaRecorderTeste.mimeType.includes('amr')) extensao = '.amr';

                            const nomeArquivo = `teste_audio_${Date.now()}${extensao}`;
                            audioFileTeste = new File([audioBlob], nomeArquivo, {
                                type: mediaRecorderTeste.mimeType
                            });

                            // Mostrar player
                            mostrarPlayerTeste(audioBlob);

                            // Habilitar teste de envio
                            document.getElementById('btnTestarEnvio').disabled = false;

                            // Parar stream
                            streamTeste.getTracks().forEach(track => track.stop());

                            resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>Gravação concluída!</strong><br>
                                Arquivo: ${audioFileTeste.name}<br>
                                Tamanho: ${formatFileSize(audioFileTeste.size)}<br>
                                Tipo: ${audioFileTeste.type}
                            </div>
                        `;
                        };

                        mediaRecorderTeste.onerror = function(event) {
                            resultDiv.innerHTML = `<div class="alert alert-danger">Erro: ${event.error.name}</div>`;
                        };

                        // Iniciar gravação
                        mediaRecorderTeste.start(1000);
                        isRecordingTeste = true;
                        recordingStartTimeTeste = Date.now();

                        // Atualizar interface
                        btnIniciar.classList.add('d-none');
                        btnParar.classList.remove('d-none');

                        // Iniciar timer
                        recordingTimerTeste = setInterval(function() {
                            const elapsed = Math.floor((Date.now() - recordingStartTimeTeste) / 1000);
                            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                            const seconds = (elapsed % 60).toString().padStart(2, '0');
                            document.getElementById('tempoGravacaoTeste').textContent = `${minutes}:${seconds}`;
                        }, 1000);

                        resultDiv.innerHTML = '<div class="alert alert-info">Gravando... Fale algo no microfone.</div>';

                    } catch (error) {
                        resultDiv.innerHTML = `<div class="alert alert-danger">Erro: ${error.message}</div>`;
                        streamTeste.getTracks().forEach(track => track.stop());
                    }
                })
                .catch(function(error) {

                    let mensagem = 'Erro ao acessar microfone: ';
                    if (error.name === 'NotAllowedError') {
                        mensagem += 'Permissão negada';
                    } else if (error.name === 'NotFoundError') {
                        mensagem += 'Microfone não encontrado';
                    } else {
                        mensagem += error.message;
                    }

                    resultDiv.innerHTML = `<div class="alert alert-danger">${mensagem}</div>`;
                });
        };

        // Parar gravação de teste
        window.pararGravacaoTeste = function() {
            if (isRecordingTeste && mediaRecorderTeste) {
                mediaRecorderTeste.stop();
                isRecordingTeste = false;
                clearInterval(recordingTimerTeste);

                // Restaurar interface
                document.getElementById('btnIniciarTeste').classList.remove('d-none');
                document.getElementById('btnPararTeste').classList.add('d-none');
                document.getElementById('tempoGravacaoTeste').textContent = '00:00';
            }
        };

        // Mostrar player de teste
        window.mostrarPlayerTeste = function(audioBlob) {
            const playerDiv = document.getElementById('playerTeste');
            const audioElement = playerDiv.querySelector('audio');

            if (audioElement) {
                const audioUrl = URL.createObjectURL(audioBlob);
                audioElement.src = audioUrl;
                playerDiv.classList.remove('d-none');
            }
        };

        // Testar envio via API
        window.testarEnvioAPI = function() {
            if (!audioFileTeste) {
                alert('Grave um áudio de teste primeiro!');
                return;
            }

            const btnTeste = document.getElementById('btnTestarEnvio');
            const resultDiv = document.getElementById('resultadoEnvio');

            btnTeste.disabled = true;
            btnTeste.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testando...';
            resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Enviando para API...';

            // Criar FormData
            const formData = new FormData();
            formData.append('audio_gravado', audioFileTeste);
            formData.append('destinatario', '<?= $dados['conversa']->contato_numero ?>');
            formData.append('caption', `Teste de áudio - ${new Date().toLocaleTimeString()}`);

            // Enviar para API normal
            fetch('<?= URL ?>/chat/enviarMensagem/<?= $dados['conversa']->id ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {

                    if (data.success) {
                        resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <strong>✅ SUCESSO!</strong><br>
                        <small>Áudio enviado com sucesso!</small>
                    </div>
                `;
                    } else {
                        resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>❌ FALHA!</strong><br>
                        <small>Erro: ${data.error || 'Erro desconhecido'}</small>
                    </div>
                `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="alert alert-danger">Erro na requisição: ${error.message}</div>`;
                })
                .finally(() => {
                    btnTeste.disabled = false;
                    btnTeste.innerHTML = '<i class="fas fa-upload me-1"></i> Testar Envio API';
                });
        };

        // === FIM SISTEMA DE DIAGNÓSTICO ===

        // =========== NOVO: FUNÇÕES DE GERENCIAMENTO DE TICKETS ===========

        // Função para alterar status do ticket
        window.alterarStatusTicket = function(novoStatus) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= URL ?>/chat/alterarStatusTicket';
            
            const inputs = [
                { name: 'conversa_id', value: '<?= $dados['conversa']->id ?>' },
                { name: 'status', value: novoStatus }
            ];
            
            inputs.forEach(input => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = input.name;
                hiddenInput.value = input.value;
                form.appendChild(hiddenInput);
            });
            
            document.body.appendChild(form);
            form.submit();
        };

        // Função para encerrar ticket (com modal)
        window.encerrarTicket = function() {
            $('#modalEncerrarTicket').modal('show');
        };

        // Função para reabrir ticket (com modal)
        window.reabrirTicket = function() {
            $('#modalReabrirTicket').modal('show');
        };

        // Bloquear envio de mensagens se ticket estiver fechado
        function verificarStatusTicket() {
            const statusAtual = '<?= $dados['conversa']->status_atendimento ?? 'aberto' ?>';
            
            if (statusAtual === 'fechado') {
                // Desabilitar controles de envio
                const messageInput = document.getElementById('messageInput');
                const sendButton = document.getElementById('sendButton');
                const attachBtn = document.getElementById('attachBtn');
                const voiceBtn = document.getElementById('voiceBtn');
                
                if (messageInput) {
                    messageInput.disabled = true;
                    messageInput.placeholder = 'Ticket fechado - não é possível enviar mensagens';
                }
                
                if (sendButton) sendButton.disabled = true;
                if (attachBtn) attachBtn.disabled = true;
                if (voiceBtn) voiceBtn.disabled = true;
                
                // Mostrar aviso
                const chatInputArea = document.querySelector('.chat-input-area');
                if (chatInputArea && !chatInputArea.querySelector('.ticket-closed-warning')) {
                    const warning = document.createElement('div');
                    warning.className = 'alert alert-warning ticket-closed-warning';
                    warning.innerHTML = `
                        <i class="fas fa-lock me-2"></i>
                        <strong>Ticket Fechado</strong><br>
                        <small>Este ticket foi encerrado. Para enviar mensagens, é necessário reabrir o ticket.</small>
                    `;
                    chatInputArea.insertBefore(warning, chatInputArea.firstChild);
                }
            }
        }

        // Executar verificação na inicialização
        setTimeout(verificarStatusTicket, 100);

        // === FIM DAS FUNÇÕES DE TICKET ===

        // ========== SISTEMA DE MENSAGENS RÁPIDAS ==========
        
        // Inicializar sistema de mensagens rápidas
        function initQuickMessages() {
            // Carregar mensagens quando o modal for aberto
            $('#modalMensagensRapidas').on('show.bs.modal', function() {
                carregarMensagensRapidas();
            });
        }
        
        // Carregar mensagens rápidas do banco de dados
        function carregarMensagensRapidas() {
            const loadingDiv = document.getElementById('loadingMensagens');
            const listaMensagens = document.getElementById('listaMensagensRapidas');
            const erroDiv = document.getElementById('erroMensagens');
            const semMensagensDiv = document.getElementById('semMensagens');
            
            // Mostrar loading
            loadingDiv.style.display = 'block';
            listaMensagens.style.display = 'none';
            erroDiv.style.display = 'none';
            semMensagensDiv.style.display = 'none';
            
            console.log('MENSAGENS_RAPIDAS: Iniciando carregamento...');
            
            // Buscar mensagens via AJAX
            fetch('<?= URL ?>/chat/apiMensagensRapidas', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('MENSAGENS_RAPIDAS: Response status:', response.status);
                console.log('MENSAGENS_RAPIDAS: Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('MENSAGENS_RAPIDAS: Data received:', data);
                loadingDiv.style.display = 'none';
                
                if (data.success && data.mensagens && data.mensagens.length > 0) {
                    console.log('MENSAGENS_RAPIDAS: Carregando', data.mensagens.length, 'mensagens');
                    
                    // Limpar lista anterior
                    listaMensagens.innerHTML = '';
                    
                    // Adicionar mensagens
                    data.mensagens.forEach(mensagem => {
                        const item = criarItemMensagemRapida(mensagem);
                        listaMensagens.appendChild(item);
                    });
                    
                    listaMensagens.style.display = 'block';
                    
                    // Reconfigurar event listeners
                    configurarEventListenersMensagens();
                    
                } else if (data.success && data.mensagens && data.mensagens.length === 0) {
                    console.log('MENSAGENS_RAPIDAS: Nenhuma mensagem encontrada');
                    // Nenhuma mensagem encontrada
                    semMensagensDiv.style.display = 'block';
                } else {
                    console.error('MENSAGENS_RAPIDAS: Erro na resposta:', data);
                    // Erro na resposta - mostrar erro mais específico
                    erroDiv.style.display = 'block';
                    if (data.error) {
                        erroDiv.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Erro:</strong> ${data.error}
                            </div>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('MENSAGENS_RAPIDAS: Erro na requisição:', error);
                loadingDiv.style.display = 'none';
                erroDiv.style.display = 'block';
                
                // Mostrar erro mais detalhado
                erroDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Erro de conexão:</strong> ${error.message}<br>
                        <small>Verifique se o servidor está funcionando corretamente.</small>
                    </div>
                `;
            });
        }
        
        // Criar item de mensagem rápida
        function criarItemMensagemRapida(mensagem) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'list-group-item list-group-item-action quick-message-item';
            button.setAttribute('data-message', mensagem.conteudo);
            button.setAttribute('data-id', mensagem.id);
            
            // Criar conteúdo truncado para preview
            const conteudoTruncado = mensagem.conteudo.length > 150 
                ? mensagem.conteudo.substring(0, 150) + '...'
                : mensagem.conteudo;
            
            button.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        <i class="${mensagem.icone} me-2 text-primary"></i>
                        ${mensagem.titulo}
                    </h6>
                    <small class="text-muted">
                        <i class="fas fa-mouse-pointer me-1"></i>
                        Clique para usar
                    </small>
                </div>
                <p class="mb-1">${conteudoTruncado}</p>
                <small class="text-muted">
                    ${mensagem.conteudo.length} caracteres
                </small>
            `;
            
            return button;
        }
        
        // Configurar event listeners para mensagens
        function configurarEventListenersMensagens() {
            const quickMessageItems = document.querySelectorAll('.quick-message-item');
            const messageInput = document.getElementById('messageInput');
            
            quickMessageItems.forEach(item => {
                item.addEventListener('click', function() {
                    const message = this.getAttribute('data-message');
                    
                    if (message && messageInput) {
                        // Inserir a mensagem no input
                        messageInput.value = message;
                        
                        // Ajustar altura do textarea
                        messageInput.style.height = 'auto';
                        messageInput.style.height = Math.min(messageInput.scrollHeight, 100) + 'px';
                        
                        // Atualizar botão de envio
                        updateSendButton();
                        
                        // Fechar modal
                        $('#modalMensagensRapidas').modal('hide');
                        
                        // Focar no input para permitir edição
                        setTimeout(() => {
                            messageInput.focus();
                            // Posicionar cursor no final
                            messageInput.setSelectionRange(message.length, message.length);
                        }, 300);
                        
                        // Feedback visual
                        mostrarNotificacaoSucesso('Mensagem inserida com sucesso!');
                    }
                });
            });
        }
        
        // Mostrar notificação de sucesso
        function mostrarNotificacaoSucesso(mensagem) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.innerHTML = `<i class="fas fa-check me-2"></i>${mensagem}`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 2000);
        }
        
        // Inicializar quando o DOM estiver carregado
        setTimeout(initQuickMessages, 100);

        // === FIM DO SISTEMA DE MENSAGENS RÁPIDAS ===
    });
</script>

<style>
    /* Estilos para o botão de mensagens rápidas */
    .btn-quick-message {
        background: linear-gradient(135deg, #25d366, #128c7e);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.3s ease;
        margin-right: 8px;
        cursor: pointer;
    }

    .btn-quick-message:hover {
        background: linear-gradient(135deg, #128c7e, #075e54);
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
        color: white;
    }

    .btn-quick-message:active {
        transform: scale(0.95);
    }

    .btn-quick-message i {
        font-size: 16px;
    }

    /* Estilos para os itens de mensagem rápida no modal */
    .quick-message-item {
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .quick-message-item:hover {
        background-color: #f8f9fa;
        border-color: #25d366;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .quick-message-item:active {
        transform: translateY(0);
    }

    /* Notificação toast */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #25d366, #128c7e);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .toast-notification.show {
        opacity: 1;
        transform: translateX(0);
    }

    /* Melhorias nos estilos do modal */
    #modalMensagensRapidas .modal-header {
        background: linear-gradient(135deg, #25d366, #128c7e);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    #modalMensagensRapidas .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    #modalMensagensRapidas .modal-header .close:hover {
        opacity: 1;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .btn-quick-message {
            width: 35px;
            height: 35px;
            margin-right: 5px;
        }
        
        .btn-quick-message i {
            font-size: 14px;
        }
        
        .toast-notification {
            right: 10px;
            left: 10px;
            width: auto;
        }
    }
</style>

<?php include 'app/Views/include/footer.php' ?>