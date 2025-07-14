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

                    <!-- Cabeçalho -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3">
                            <i class="fas fa-tickets-alt me-2"></i>Gerenciar Tickets
                            <?php if (isset($dados['mostrar_todos']) && $dados['mostrar_todos']): ?>
                                <span class="badge badge-info ms-2">Todos os Tickets</span>
                            <?php endif; ?>
                        </h1>
                        <div class="d-flex gap-2">
                            <a href="<?= URL ?>/chat/dashboardTickets" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-chart-bar me-1"></i> Dashboard
                            </a>
                            <a href="<?= URL ?>/chat/relatorioTickets" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-file-alt me-1"></i> Relatórios
                            </a>
                        </div>
                    </div>

                    <!-- Estatísticas Rápidas -->
                    <?php if (isset($dados['estatisticas'])): ?>
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="display-4 text-primary">
                                            <?= $dados['estatisticas']->total_tickets ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="display-4 text-danger">
                                            <?= $dados['estatisticas']->abertos ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Abertos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="display-4 text-warning">
                                            <?= $dados['estatisticas']->em_andamento ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Em Andamento</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="display-4 text-info">
                                            <?= $dados['estatisticas']->aguardando_cliente ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Aguardando</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="display-4 text-success">
                                            <?= $dados['estatisticas']->resolvidos ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Resolvidos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="display-4 text-secondary">
                                            <?= $dados['estatisticas']->fechados ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Fechados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Filtros -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-filter me-2"></i>Filtros
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= URL ?>/chat/gerenciarTickets">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Status do Ticket</label>
                                        <select name="status" class="form-control">
                                            <option value="todos" <?= ($dados['filtro_status'] ?? 'todos') == 'todos' ? 'selected' : '' ?>>Todos</option>
                                            <option value="aberto" <?= ($dados['filtro_status'] ?? '') == 'aberto' ? 'selected' : '' ?>>Aberto</option>
                                            <option value="em_andamento" <?= ($dados['filtro_status'] ?? '') == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                            <option value="aguardando_cliente" <?= ($dados['filtro_status'] ?? '') == 'aguardando_cliente' ? 'selected' : '' ?>>Aguardando Cliente</option>
                                            <option value="resolvido" <?= ($dados['filtro_status'] ?? '') == 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
                                            <option value="fechado" <?= ($dados['filtro_status'] ?? '') == 'fechado' ? 'selected' : '' ?>>Fechado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-1"></i> Filtrar
                                            </button>
                                            <a href="<?= URL ?>/chat/gerenciarTickets" class="btn btn-outline-secondary">
                                                <i class="fas fa-undo me-1"></i> Limpar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tickets Vencidos -->
                    <?php if (!empty($dados['tickets_vencidos'])): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Tickets Vencidos (>24h)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Contato</th>
                                                <th>Responsável</th>
                                                <th>Status</th>
                                                <th>Horas em Aberto</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['tickets_vencidos'] as $ticket): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($ticket->contato_nome) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($ticket->contato_numero) ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($ticket->responsavel_nome ?? 'Não atribuído') ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $ticket->status_atendimento == 'aberto' ? 'danger' : 'warning' ?>">
                                                            <?= ucfirst(str_replace('_', ' ', $ticket->status_atendimento)) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-danger">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= $ticket->horas_em_aberto ?>h
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= URL ?>/chat/conversa/<?= $ticket->id ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i> Ver
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Lista de Tickets -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Lista de Tickets
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['conversas'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Contato</th>
                                                <?php if (isset($dados['mostrar_todos']) && $dados['mostrar_todos']): ?>
                                                    <th>Responsável</th>
                                                <?php endif; ?>
                                                <th>Status</th>
                                                <th>Última Mensagem</th>
                                                <th>Tempo em Aberto</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['conversas'] as $conversa): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm me-3">
                                                                <?= strtoupper(substr($conversa->contato_nome, 0, 2)) ?>
                                                            </div>
                                                            <div>
                                                                <strong><?= htmlspecialchars($conversa->contato_nome) ?></strong><br>
                                                                <small class="text-muted"><?= htmlspecialchars($conversa->contato_numero) ?></small>
                                                                <?php if (isset($conversa->nao_lidas) && $conversa->nao_lidas > 0): ?>
                                                                    <span class="badge badge-danger"><?= $conversa->nao_lidas ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <?php if (isset($dados['mostrar_todos']) && $dados['mostrar_todos']): ?>
                                                        <td>
                                                            <?php if (isset($conversa->responsavel_nome) && $conversa->responsavel_nome): ?>
                                                                <span class="badge badge-primary">
                                                                    <i class="fas fa-user me-1"></i>
                                                                    <?= htmlspecialchars($conversa->responsavel_nome) ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">
                                                                    <i class="fas fa-user-slash me-1"></i>
                                                                    Não atribuído
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
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
                                                        $status = $conversa->status_atendimento ?? 'aberto';
                                                        ?>
                                                        <span class="badge <?= $statusClass[$status] ?? 'badge-secondary' ?>">
                                                            <?= $statusNomes[$status] ?? 'Desconhecido' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_mensagem)): ?>
                                                            <div class="small">
                                                                <?= htmlspecialchars(substr($conversa->ultima_mensagem, 0, 50)) ?>...
                                                            </div>
                                                            <small class="text-muted">
                                                                <?= date('d/m/Y H:i', strtotime($conversa->ultima_atividade)) ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Nenhuma mensagem</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($conversa->horas_em_aberto)): ?>
                                                            <span class="<?= $conversa->horas_em_aberto > 24 ? 'text-danger' : 'text-muted' ?>">
                                                                <?= number_format($conversa->horas_em_aberto, 1) ?>h
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye me-1"></i> Ver
                                                            </a>
                                                            <a href="<?= URL ?>/chat/historicoTicket/<?= $conversa->id ?>" class="btn btn-sm btn-outline-info">
                                                                <i class="fas fa-history me-1"></i> Histórico
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum ticket encontrado</h5>
                                    <p class="text-muted">Não há tickets com os filtros selecionados.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--whatsapp-teal);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.display-4 {
    font-size: 2rem;
    font-weight: bold;
}

.ticket-status {
    font-size: 0.9rem;
}

.badge {
    font-size: 0.8rem;
    padding: 0.4em 0.8em;
}
</style>

<?php include 'app/Views/include/footer.php' ?> 