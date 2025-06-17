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
                                <i class="fas fa-user-slash me-2"></i> Conversas Não Atribuídas
                                <span class="badge bg-warning text-dark ms-2"><?= $dados['total_registros'] ?></span>
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/index" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar ao Chat
                                </a>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="card-body border-bottom">
                            <form method="GET" action="<?= URL ?>/chat/conversasNaoAtribuidas" class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <label for="filtro_contato" class="form-label">
                                        <i class="fas fa-user me-1"></i> Filtrar por Contato
                                    </label>
                                    <input type="text" class="form-control" id="filtro_contato" name="filtro_contato" 
                                           placeholder="Nome do contato..." 
                                           value="<?= htmlspecialchars($dados['filtro_contato']) ?>"
                                           autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="filtro_numero" class="form-label">
                                        <i class="fas fa-phone me-1"></i> Filtrar por Número
                                    </label>
                                    <input type="text" class="form-control" id="filtro_numero" name="filtro_numero" 
                                           placeholder="Número de telefone..." 
                                           value="<?= htmlspecialchars($dados['filtro_numero']) ?>"
                                           autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-12 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                    <div class="ms-2">
                                        <a href="<?= URL ?>/chat/conversasNaoAtribuidas" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> Limpar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <!-- Informações de paginação -->
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                <div class="text-muted mb-2 mb-md-0">
                                    <?php if ($dados['total_registros'] > 0): ?>
                                        Mostrando <?= $dados['registro_inicio'] ?> a <?= $dados['registro_fim'] ?> 
                                        de <?= $dados['total_registros'] ?> conversa<?= $dados['total_registros'] != 1 ? 's' : '' ?> não atribuída<?= $dados['total_registros'] != 1 ? 's' : '' ?>
                                        <?php if (!empty($dados['filtro_contato']) || !empty($dados['filtro_numero'])): ?>
                                            <span class="badge bg-info ms-2" title="Filtros aplicados">
                                                <i class="fas fa-filter me-1"></i> Filtrado
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if (!empty($dados['filtro_contato']) || !empty($dados['filtro_numero'])): ?>
                                            <span class="text-warning">
                                                <i class="fas fa-search me-1"></i>
                                                Nenhuma conversa não atribuída encontrada com os filtros aplicados
                                            </span>
                                        <?php else: ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Todas as conversas estão atribuídas
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($dados['filtro_contato']) || !empty($dados['filtro_numero'])): ?>
                                    <div class="text-start text-md-end">
                                        <small class="text-muted d-block mb-1">
                                            Filtros ativos:
                                        </small>
                                        <div>
                                            <?php if (!empty($dados['filtro_contato'])): ?>
                                                <span class="badge bg-light text-dark me-1 mb-1">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?= htmlspecialchars($dados['filtro_contato']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($dados['filtro_numero'])): ?>
                                                <span class="badge bg-light text-dark me-1 mb-1">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <?= htmlspecialchars($dados['filtro_numero']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Contato</th>
                                            <th>Número</th>
                                            <th>Última Mensagem</th>
                                            <th>Total Msgs</th>
                                            <th>Não Lidas</th>
                                            <th>Última Atividade</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['conversas'])): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <?php if (!empty($dados['filtro_contato']) || !empty($dados['filtro_numero'])): ?>
                                                        <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                                        <p class="text-muted mb-0">Nenhuma conversa não atribuída encontrada com os filtros aplicados</p>
                                                        <a href="<?= URL ?>/chat/conversasNaoAtribuidas" class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="fas fa-times me-1"></i> Limpar Filtros
                                                        </a>
                                                    <?php else: ?>
                                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                                        <p class="text-muted mb-0">Todas as conversas estão atribuídas a usuários</p>
                                                        <a href="<?= URL ?>/chat/index" class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="fas fa-comments me-1"></i> Ver Conversas
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dados['conversas'] as $conversa): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($conversa->contato_nome) ?></strong>
                                                        <?php if ($conversa->nao_lidas > 0): ?>
                                                            <span class="badge bg-warning ms-1"><?= $conversa->nao_lidas ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <code class="text-muted"><?= htmlspecialchars($conversa->contato_numero) ?></code>
                                                    </td>
                                                    <td>
                                                        <?php if ($conversa->ultima_mensagem): ?>
                                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($conversa->ultima_mensagem) ?>">
                                                                <?= htmlspecialchars($conversa->ultima_mensagem) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Sem mensagens</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?= $conversa->total_mensagens ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($conversa->nao_lidas > 0): ?>
                                                            <span class="badge bg-warning"><?= $conversa->nao_lidas ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">0</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($conversa->ultima_atividade): ?>
                                                            <small class="text-muted">
                                                                <?= date('d/m/Y H:i', strtotime($conversa->ultima_atividade)) ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <small class="text-muted">-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" 
                                                           class="btn btn-info btn-sm me-1" 
                                                           title="Visualizar conversa">
                                                            <i class="fas fa-eye me-1"></i> Ver
                                                        </a>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm" 
                                                                data-toggle="modal" 
                                                                data-target="#modalAtribuir"
                                                                data-conversa-id="<?= $conversa->id ?>"
                                                                data-contato-nome="<?= htmlspecialchars($conversa->contato_nome) ?>"
                                                                data-contato-numero="<?= htmlspecialchars($conversa->contato_numero) ?>"
                                                                title="Atribuir a um usuário">
                                                            <i class="fas fa-user-plus me-1"></i> Atribuir
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginação -->
                            <?php if ($dados['total_paginas'] > 1): ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted">
                                        Página <?= $dados['pagina_atual'] ?> de <?= $dados['total_paginas'] ?>
                                    </div>
                                    <nav aria-label="Paginação">
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php if ($dados['pagina_atual'] > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasNaoAtribuidas?pagina=<?= $dados['pagina_atual'] - 1 ?><?= $dados['query_string'] ?>">
                                                        <i class="fas fa-angle-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = max(1, $dados['pagina_atual'] - 2); $i <= min($dados['total_paginas'], $dados['pagina_atual'] + 2); $i++): ?>
                                                <li class="page-item <?= $i == $dados['pagina_atual'] ? 'active' : '' ?>">
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasNaoAtribuidas?pagina=<?= $i ?><?= $dados['query_string'] ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasNaoAtribuidas?pagina=<?= $dados['pagina_atual'] + 1 ?><?= $dados['query_string'] ?>">
                                                        <i class="fas fa-angle-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal para Atribuir Conversa -->
<div class="modal fade" id="modalAtribuir" tabindex="-1" aria-labelledby="modalAtribuirLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= URL ?>/chat/atribuirConversa">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAtribuirLabel">
                        <i class="fas fa-user-plus me-2"></i> Atribuir Conversa
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="conversa_id" name="conversa_id">
                    
                    <div class="alert alert-info" id="contato_info">
                        <!-- Preenchido via JavaScript -->
                    </div>

                    <div class="form-group">
                        <label for="usuario_id">
                            <i class="fas fa-user me-1"></i> Atribuir para o usuário:
                        </label>
                        <select class="form-control" id="usuario_id" name="usuario_id" required>
                            <option value="">Selecione um usuário...</option>
                            <?php foreach ($dados['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario->id ?>">
                                    <?= htmlspecialchars($usuario->nome) ?> 
                                    <span class="text-muted">(<?= ucfirst($usuario->perfil) ?>)</span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            A conversa será transferida para o usuário selecionado e aparecerá na lista de conversas dele.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Atribuir Conversa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Configurar modal de atribuição
    $('#modalAtribuir').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var conversaId = button.data('conversa-id');
        var contatoNome = button.data('contato-nome');
        var contatoNumero = button.data('contato-numero');
        
        var modal = $(this);
        modal.find('#conversa_id').val(conversaId);
        modal.find('#contato_info').html(
            '<div class="d-flex align-items-center">' +
            '<i class="fas fa-user fa-2x text-primary me-3"></i>' +
            '<div>' +
            '<h6 class="mb-1">' + contatoNome + '</h6>' +
            '<small class="text-muted"><i class="fas fa-phone me-1"></i>' + contatoNumero + '</small>' +
            '</div>' +
            '</div>'
        );
    });

    // Limpar modal ao fechar
    $('#modalAtribuir').on('hidden.bs.modal', function () {
        $(this).find('#conversa_id').val('');
        $(this).find('#usuario_id').val('');
        $(this).find('#contato_info').html('');
    });
});
</script>

<?php include 'app/Views/include/footer.php' ?> 