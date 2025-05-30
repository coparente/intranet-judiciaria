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
                                <i class="fas fa-comments me-2"></i> Chat
                                <span id="apiStatus" class="badge bg-secondary ms-2" title="Verificando status da API...">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </span>
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/novaConversa" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i> Nova Conversa
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Contato</th>
                                            <th>Número</th>
                                            <th>Última Mensagem</th>
                                            <th>Atualizado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['conversas'])) : ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Nenhuma conversa encontrada</td>
                                            </tr>
                                        <?php else : ?>
                                            <?php foreach ($dados['conversas'] as $conversa) : ?>
                                                <tr>
                                                    <td>
                                                        <?= $conversa->contato_nome ?>
                                                        <?php if (isset($conversa->lido) && $conversa->lido > 0) : ?>
                                                            <span class="badge bg-danger"><?= $conversa->lido ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $conversa->contato_numero ?></td>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_mensagem)) : ?>
                                                            <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                                <?= htmlspecialchars($conversa->ultima_mensagem) ?>
                                                            </span>
                                                        <?php else : ?>
                                                            <span class="text-muted">Sem mensagens</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_atualizacao)) : ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->ultima_atualizacao)) ?>
                                                        <?php else : ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->criado_em)) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-comments me-1"></i> Abrir
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalExcluirConversa<?= $conversa->id ?>">
                                                            <i class="fas fa-trash me-1"></i> Excluir
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Modal Excluir Conversa -->
                                                <div class="modal fade" id="modalExcluirConversa<?= $conversa->id ?>" tabindex="-1" aria-labelledby="modalExcluirConversaLabel<?= $conversa->id ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalExcluirConversaLabel<?= $conversa->id ?>">Confirmar Exclusão</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Tem certeza que deseja excluir a conversa com <strong><?= $conversa->contato_nome ?></strong>?</p>
                                                                <p class="text-danger"><small>Esta ação não pode ser desfeita e todas as mensagens serão excluídas.</small></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                <a href="<?= URL ?>/chat/excluirConversa/<?= $conversa->id ?>" class="btn btn-danger">Excluir</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
                    } else {
                        apiStatus.className = 'badge bg-danger ms-2';
                        apiStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> API Offline';

                        // Adiciona detalhes do erro ao título
                        const errorMsg = data.error ? data.error : 'API está offline. Mensagens não serão enviadas.';
                        apiStatus.title = errorMsg;
                        console.error('Erro na API:', errorMsg);
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

        // Verificar status da API a cada 30 segundos
        setInterval(verificarStatusAPI, 30000);
    });
</script>

<?php include 'app/Views/include/footer.php' ?>