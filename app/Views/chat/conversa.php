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
                                <a href="<?= URL ?>/chat" class="text-white me-2">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                                <?= $dados['conversa']->contato_nome ?>
                                <span id="apiStatus" class="badge bg-secondary ms-2" title="Verificando status da API...">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </span>
                            </h5>
                            <div>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalEditarConversa">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <a href="<?= URL ?>/chat/index" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Informações do Contato</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarConversa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Nome:</strong> <?= $dados['conversa']->contato_nome ?></p>
                                            <p><strong>Número:</strong> <?= $dados['conversa']->contato_numero ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Mensagens</h6>
                                        </div>
                                        <div class="card-body chat-box" id="chatBox" style="height: 400px; overflow-y: auto;">
                                            <?php if (empty($dados['mensagens'])) : ?>
                                                <div class="text-center text-muted my-5">
                                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                                    <p>Nenhuma mensagem encontrada</p>
                                                    <p>Envie uma mensagem para iniciar a conversa</p>
                                                </div>
                                            <?php else : ?>
                                                <?php foreach ($dados['mensagens'] as $mensagem) : ?>
                                                    <?php 
                                                    // Mensagem enviada pelo usuário atual se remetente_id não for nulo e for igual ao ID do usuário
                                                    $isUsuario = $mensagem->remetente_id == $_SESSION['usuario_id'];
                                                    $align = $isUsuario ? 'justify-content-end' : 'justify-content-start';
                                                    $bgColor = $isUsuario ? 'bg-primary text-white' : 'bg-light';
                                                    ?>
                                                    <div class="d-flex <?= $align ?> mb-3">
                                                        <div class="message <?= $bgColor ?> rounded p-2" style="max-width: 75%;">
                                                            <?php if ($mensagem->tipo == 'text') : ?>
                                                                <p class="mb-0"><?= nl2br(htmlspecialchars($mensagem->conteudo)) ?></p>
                                                            <?php elseif ($mensagem->tipo == 'image') : ?>
                                                                <img src="<?= URL ?>/<?= $mensagem->midia_url ?>" class="img-fluid rounded mb-1" style="max-height: 200px;">
                                                            <?php elseif ($mensagem->tipo == 'video') : ?>
                                                                <video controls class="img-fluid rounded mb-1" style="max-height: 200px;">
                                                                    <source src="<?= URL ?>/<?= $mensagem->midia_url ?>" type="video/mp4">
                                                                    Seu navegador não suporta vídeos HTML5.
                                                                </video>
                                                            <?php elseif ($mensagem->tipo == 'audio') : ?>
                                                                <audio controls class="w-100">
                                                                    <source src="<?= URL ?>/<?= $mensagem->midia_url ?>" type="audio/mpeg">
                                                                    Seu navegador não suporta áudios HTML5.
                                                                </audio>
                                                            <?php elseif ($mensagem->tipo == 'document') : ?>
                                                                <a href="<?= URL ?>/<?= $mensagem->midia_url ?>" target="_blank" class="d-flex align-items-center text-decoration-none">
                                                                    <i class="fas fa-file-alt me-2"></i>
                                                                    <span><?= $mensagem->midia_nome ?? 'Documento' ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <div class="d-flex justify-content-end align-items-center mt-1">
                                                                <small class="<?= $isUsuario ? 'text-white-50' : 'text-muted' ?>">
                                                                    <?= date('H:i', strtotime($mensagem->enviado_em)) ?>
                                                                </small>
                                                                
                                                                <?php if ($isUsuario) : ?>
                                                                    <?php 
                                                                    $statusIcon = '';
                                                                    switch ($mensagem->status) {
                                                                        case 'enviado':
                                                                            $statusIcon = '<i class="fas fa-check text-white-50 ms-1" title="Enviado"></i>';
                                                                            break;
                                                                        case 'entregue':
                                                                            $statusIcon = '<i class="fas fa-check-double text-white-50 ms-1" title="Entregue"></i>';
                                                                            break;
                                                                        case 'lido':
                                                                            $statusIcon = '<i class="fas fa-check-double text-info ms-1" title="Lido"></i>';
                                                                            break;
                                                                        default:
                                                                            $statusIcon = '<i class="fas fa-clock text-white-50 ms-1" title="Pendente"></i>';
                                                                    }
                                                                    echo $statusIcon;
                                                                    ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer">
                                            <form action="<?= URL ?>/chat/enviarMensagem/<?= $dados['conversa']->id ?>" method="POST" enctype="multipart/form-data">
                                                <div class="input-group">
                                                    <textarea class="form-control" name="mensagem" id="mensagem" rows="1" placeholder="Digite sua mensagem..." required></textarea>
                                                    <button class="btn btn-primary" type="submit" id="btnEnviar">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                    <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#collapseAttachment">
                                                        <i class="fas fa-paperclip"></i>
                                                    </button>
                                                </div>
                                                <div class="collapse mt-2" id="collapseAttachment">
                                                    <div class="card card-body">
                                                        <div class="mb-3">
                                                            <label for="midia" class="form-label">Anexar arquivo</label>
                                                            <input class="form-control" type="file" id="midia" name="midia">
                                                            <div id="previewContainer" class="d-none mt-2">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-file me-2"></i>
                                                                    <span id="fileName"></span>
                                                                    <button type="button" id="removeFile" class="btn btn-sm btn-link text-danger">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
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

<!-- Modal Editar Conversa -->
<div class="modal fade" id="modalEditarConversa" tabindex="-1" aria-labelledby="modalEditarConversaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/atualizarConversa/<?= $dados['conversa']->id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarConversaLabel">Editar Conversa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Contato</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= $dados['conversa']->contato_nome ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chatBox');
    let lastMessageId = <?= !empty($dados['mensagens']) ? end($dados['mensagens'])->id : 0 ?>;
    
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    
    // Preview de arquivo anexado
    const mediaInput = document.getElementById('midia');
    const previewContainer = document.getElementById('previewContainer');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');
    
    if (mediaInput) {
        mediaInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                previewContainer.classList.remove('d-none');
            } else {
                previewContainer.classList.add('d-none');
            }
        });
    }
    
    if (removeFile) {
        removeFile.addEventListener('click', function() {
            mediaInput.value = '';
            previewContainer.classList.add('d-none');
        });
    }
    
    // Função para carregar novas mensagens
    function carregarNovasMensagens() {
        fetch(`<?= URL ?>/chat/carregarNovasMensagens/<?= $dados['conversa']->id ?>/${lastMessageId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta da rede');
                }
                return response.json();
            })
            .then(data => {
                if (data.mensagens && data.mensagens.length > 0) {
                    data.mensagens.forEach(mensagem => {
                        const isUsuario = mensagem.remetente_id == <?= $_SESSION['usuario_id'] ?>;
                        const align = isUsuario ? 'justify-content-end' : 'justify-content-start';
                        const bgColor = isUsuario ? 'bg-primary text-white' : 'bg-light';
                        
                        let conteudo = '';
                        let status = '';
                        
                        if (isUsuario) {
                            switch (mensagem.status) {
                                case 'enviado':
                                    status = '<i class="fas fa-check text-white-50 ms-1" title="Enviado"></i>';
                                    break;
                                case 'entregue':
                                    status = '<i class="fas fa-check-double text-white-50 ms-1" title="Entregue"></i>';
                                    break;
                                case 'lido':
                                    status = '<i class="fas fa-check-double text-info ms-1" title="Lido"></i>';
                                    break;
                                default:
                                    status = '<i class="fas fa-clock text-white-50 ms-1" title="Pendente"></i>';
                            }
                        }
                        
                        // Formata a data/hora
                        const data = new Date(mensagem.enviado_em);
                        const hora = data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                        
                        if (mensagem.tipo === 'text') {
                            conteudo = `<p class="mb-0">${mensagem.conteudo}</p>`;
                        } else if (mensagem.tipo === 'image') {
                            conteudo = `
                                <a href="<?= URL ?>/${mensagem.midia_url}" target="_blank">
                                    <img src="<?= URL ?>/${mensagem.midia_url}" class="img-fluid rounded" style="max-height: 200px;" alt="Imagem">
                                </a>
                            `;
                        } else if (mensagem.tipo === 'document') {
                            conteudo = `
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file me-2"></i>
                                    <a href="<?= URL ?>/${mensagem.midia_url}" target="_blank" class="text-truncate">
                                        ${mensagem.midia_nome || 'Documento'}
                                    </a>
                                </div>
                            `;
                        }
                        
                        const mensagemHTML = `
                            <div class="d-flex ${align} mb-3">
                                <div class="message ${bgColor} rounded p-2" style="max-width: 75%;">
                                    ${conteudo}
                                    <div class="d-flex justify-content-end align-items-center mt-1">
                                        <small class="${isUsuario ? 'text-white-50' : 'text-muted'}">${hora}</small>
                                        ${status}
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        chatBox.innerHTML += mensagemHTML;
                        lastMessageId = mensagem.id;
                    });
                    
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            })
            .catch(error => {
                console.error('Erro ao carregar novas mensagens:', error);
            });
    }
    
    // Verificar status da API
    function verificarStatusAPI() {
        const apiStatus = document.getElementById('apiStatus');
        if (!apiStatus) return; // Verifica se o elemento existe
        
        // Adiciona um timestamp para evitar cache
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
                // Verifica se o tipo de conteúdo é JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new TypeError("Resposta não é JSON válido!");
                }
                return response.json();
            })
            .then(data => {
                if (data.online) {
                    apiStatus.className = 'badge bg-success ms-2';
                    apiStatus.innerHTML = '<i class="fas fa-check-circle"></i> API Online';
                    apiStatus.title = 'API está online e funcionando';
                    
                    // Habilitar botão de envio
                    const btnEnviar = document.getElementById('btnEnviar');
                    if (btnEnviar) {
                        btnEnviar.disabled = false;
                    }
                } else {
                    apiStatus.className = 'badge bg-danger ms-2';
                    apiStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> API Offline';
                    
                    // Adiciona detalhes do erro ao título
                    const errorMsg = data.error ? data.error : 'API está offline. Mensagens não serão enviadas.';
                    apiStatus.title = errorMsg;
                    console.error('Erro na API:', errorMsg);
                    
                    // Desabilitar botão de envio
                    const btnEnviar = document.getElementById('btnEnviar');
                    if (btnEnviar) {
                        btnEnviar.disabled = true;
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status da API:', error);
                apiStatus.className = 'badge bg-warning ms-2';
                apiStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro';
                apiStatus.title = 'Erro ao verificar status da API: ' + error.message;
            });
    }
    
    // Verificar status da API ao carregar a página
    verificarStatusAPI();
    
    // Atualizar a cada 5 segundos
    setInterval(carregarNovasMensagens, 5000);
    
    // Verificar status da API a cada 30 segundos
    setInterval(verificarStatusAPI, 30000);
});
</script>

<?php include 'app/Views/include/footer.php' ?>