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
                            <i class="fas fa-chart-bar me-2"></i>Dashboard de Tickets
                        </h1>
                        <div class="d-flex gap-2">
                            <a href="<?= URL ?>/chat/gerenciarTickets" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-tickets-alt me-1"></i> Gerenciar Tickets
                            </a>
                            <a href="<?= URL ?>/chat/relatorioTickets" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-file-alt me-1"></i> Relatórios
                            </a>
                        </div>
                    </div>

                    <!-- Estatísticas Gerais do Sistema -->
                    <?php if (isset($dados['dashboard_geral']['estatisticas'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-globe me-2"></i>Estatísticas Gerais do Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-primary">
                                                <?= $dados['dashboard_geral']['estatisticas']->total_tickets ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Total de Tickets</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-danger">
                                                <?= $dados['dashboard_geral']['estatisticas']->abertos ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Abertos</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-warning">
                                                <?= $dados['dashboard_geral']['estatisticas']->em_andamento ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Em Andamento</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-info">
                                                <?= $dados['dashboard_geral']['estatisticas']->aguardando_cliente ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Aguardando</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-success">
                                                <?= $dados['dashboard_geral']['estatisticas']->fechados ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Fechados</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-danger">
                                                <?= $dados['dashboard_geral']['estatisticas']->tickets_vencidos ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Vencidos (>24h)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (isset($dados['dashboard_geral']['estatisticas']->tempo_medio_resolucao_horas)): ?>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="alert alert-info">
                                                <i class="fas fa-clock me-2"></i>
                                                <strong>Tempo Médio de Resolução:</strong> 
                                                <?= number_format($dados['dashboard_geral']['estatisticas']->tempo_medio_resolucao_horas, 1) ?> horas
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Estatísticas do Usuário -->
                    <?php if (isset($dados['dashboard_usuario']['estatisticas'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Meus Tickets
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-primary">
                                                <?= $dados['dashboard_usuario']['estatisticas']->total_tickets ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Total</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-danger">
                                                <?= $dados['dashboard_usuario']['estatisticas']->abertos ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Abertos</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-warning">
                                                <?= $dados['dashboard_usuario']['estatisticas']->em_andamento ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Em Andamento</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-info">
                                                <?= $dados['dashboard_usuario']['estatisticas']->aguardando_cliente ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Aguardando</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-success">
                                                <?= $dados['dashboard_usuario']['estatisticas']->fechados ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Fechados</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stat-card">
                                            <div class="stat-number text-danger">
                                                <?= $dados['dashboard_usuario']['estatisticas']->tickets_vencidos ?? 0 ?>
                                            </div>
                                            <div class="stat-label">Vencidos</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Tickets Vencidos do Sistema -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Tickets Vencidos (Sistema)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dados['dashboard_geral']['tickets_vencidos'])): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Contato</th>
                                                        <th>Responsável</th>
                                                        <th>Horas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($dados['dashboard_geral']['tickets_vencidos'], 0, 5) as $ticket): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?= URL ?>/chat/conversa/<?= $ticket->id ?>" class="text-decoration-none">
                                                                    <?= htmlspecialchars(substr($ticket->contato_nome, 0, 20)) ?>...
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <small><?= htmlspecialchars($ticket->responsavel_nome ?? 'N/A') ?></small>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-danger"><?= $ticket->horas_em_aberto ?>h</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if (count($dados['dashboard_geral']['tickets_vencidos']) > 5): ?>
                                            <div class="text-center mt-2">
                                                <a href="<?= URL ?>/chat/gerenciarTickets?status=aberto" class="btn btn-sm btn-outline-danger">
                                                    Ver todos (<?= count($dados['dashboard_geral']['tickets_vencidos']) ?>)
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <p class="text-muted mb-0">Nenhum ticket vencido!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Meus Tickets Vencidos -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>Meus Tickets Vencidos
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dados['dashboard_usuario']['tickets_vencidos'])): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Contato</th>
                                                        <th>Status</th>
                                                        <th>Horas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($dados['dashboard_usuario']['tickets_vencidos'], 0, 5) as $ticket): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?= URL ?>/chat/conversa/<?= $ticket->id ?>" class="text-decoration-none">
                                                                    <?= htmlspecialchars(substr($ticket->contato_nome, 0, 20)) ?>...
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?= $ticket->status_atendimento == 'aberto' ? 'danger' : 'warning' ?> badge-sm">
                                                                    <?= ucfirst(str_replace('_', ' ', $ticket->status_atendimento)) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-danger"><?= $ticket->horas_em_aberto ?>h</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if (count($dados['dashboard_usuario']['tickets_vencidos']) > 5): ?>
                                            <div class="text-center mt-2">
                                                <a href="<?= URL ?>/chat/gerenciarTickets?status=aberto" class="btn btn-sm btn-outline-warning">
                                                    Ver todos (<?= count($dados['dashboard_usuario']['tickets_vencidos']) ?>)
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-smile fa-2x text-success mb-2"></i>
                                            <p class="text-muted mb-0">Você não tem tickets vencidos!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribuição por Status -->
                    <div class="row mt-4">
                        <!-- Distribuição Sistema -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>Distribuição por Status (Sistema)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dados['dashboard_geral']['relatorio_status'])): ?>
                                        <div class="status-chart">
                                            <?php foreach ($dados['dashboard_geral']['relatorio_status'] as $item): ?>
                                                <?php
                                                $statusClass = [
                                                    'aberto' => 'danger',
                                                    'em_andamento' => 'warning',
                                                    'aguardando_cliente' => 'info',
                                                    'resolvido' => 'success',
                                                    'fechado' => 'secondary'
                                                ];
                                                $statusNomes = [
                                                    'aberto' => 'Aberto',
                                                    'em_andamento' => 'Em Andamento',
                                                    'aguardando_cliente' => 'Aguardando Cliente',
                                                    'resolvido' => 'Resolvido',
                                                    'fechado' => 'Fechado'
                                                ];
                                                ?>
                                                <div class="status-item mb-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge badge-<?= $statusClass[$item->status] ?? 'secondary' ?>">
                                                            <?= $statusNomes[$item->status] ?? 'Desconhecido' ?>
                                                        </span>
                                                        <span class="fw-bold"><?= $item->quantidade ?> tickets</span>
                                                    </div>
                                                    <small class="text-muted">
                                                        Tempo médio: <?= number_format($item->tempo_medio_horas ?? 0, 1) ?>h
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center">Nenhum dado disponível</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Distribuição Usuário -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>Meus Tickets por Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dados['dashboard_usuario']['relatorio_status'])): ?>
                                        <div class="status-chart">
                                            <?php foreach ($dados['dashboard_usuario']['relatorio_status'] as $item): ?>
                                                <?php
                                                $statusClass = [
                                                    'aberto' => 'danger',
                                                    'em_andamento' => 'warning',
                                                    'aguardando_cliente' => 'info',
                                                    'resolvido' => 'success',
                                                    'fechado' => 'secondary'
                                                ];
                                                $statusNomes = [
                                                    'aberto' => 'Aberto',
                                                    'em_andamento' => 'Em Andamento',
                                                    'aguardando_cliente' => 'Aguardando Cliente',
                                                    'resolvido' => 'Resolvido',
                                                    'fechado' => 'Fechado'
                                                ];
                                                ?>
                                                <div class="status-item mb-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge badge-<?= $statusClass[$item->status] ?? 'secondary' ?>">
                                                            <?= $statusNomes[$item->status] ?? 'Desconhecido' ?>
                                                        </span>
                                                        <span class="fw-bold"><?= $item->quantidade ?> tickets</span>
                                                    </div>
                                                    <small class="text-muted">
                                                        Tempo médio: <?= number_format($item->tempo_medio_horas ?? 0, 1) ?>h
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center">Você ainda não possui tickets</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
.stat-card {
    text-align: center;
    padding: 15px 10px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 5px;
}

.status-chart {
    max-height: 300px;
    overflow-y: auto;
}

.status-item {
    padding: 10px;
    border-radius: 6px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.3em 0.6em;
}

.fw-bold {
    font-weight: bold;
}

.text-decoration-none {
    text-decoration: none !important;
}

.card-header.bg-danger,
.card-header.bg-warning {
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
</style>

<?php include 'app/Views/include/footer.php' ?> 