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
                        <h2>
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Relatório de Tickets
                        </h2>
                        <div class="d-flex gap-2">
                            <a href="<?= URL ?>/chat/dashboardTickets" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                            <a href="<?= URL ?>/chat/gerenciarTickets" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-1"></i> Gerenciar Tickets
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-filter me-2"></i>
                                Filtros do Relatório
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= URL ?>/chat/relatorioTickets">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Período:</label>
                                        <select name="periodo" class="form-control">
                                            <option value="7" <?= $dados['periodo_selecionado'] == '7' ? 'selected' : '' ?>>Últimos 7 dias</option>
                                            <option value="30" <?= $dados['periodo_selecionado'] == '30' ? 'selected' : '' ?>>Últimos 30 dias</option>
                                            <option value="90" <?= $dados['periodo_selecionado'] == '90' ? 'selected' : '' ?>>Últimos 90 dias</option>
                                            <option value="365" <?= $dados['periodo_selecionado'] == '365' ? 'selected' : '' ?>>Último ano</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Usuário:</label>
                                        <select name="usuario" class="form-control">
                                            <option value="">Todos os usuários</option>
                                            <?php foreach ($dados['usuarios_disponiveis'] as $usuario): ?>
                                                <option value="<?= $usuario->id ?>" <?= $dados['usuario_filtro'] == $usuario->id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($usuario->nome) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Estatísticas Gerais -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="display-4 text-primary">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <h4 class="text-primary"><?= $dados['estatisticas']->total_tickets ?? 0 ?></h4>
                                    <p class="text-muted mb-0">Total de Tickets</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="display-4 text-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h4 class="text-success"><?= $dados['estatisticas']->tickets_resolvidos ?? 0 ?></h4>
                                    <p class="text-muted mb-0">Tickets Resolvidos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="display-4 text-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h4 class="text-warning"><?= $dados['estatisticas']->tickets_pendentes ?? 0 ?></h4>
                                    <p class="text-muted mb-0">Tickets Pendentes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="display-4 text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h4 class="text-danger"><?= count($dados['tickets_vencidos']) ?></h4>
                                    <p class="text-muted mb-0">Tickets Vencidos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribuição por Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Distribuição por Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="statusChart" height="200"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Quantidade</th>
                                                    <th>Percentual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total = $dados['estatisticas']->total_tickets ?? 1;
                                                $statusConfig = [
                                                    'aberto' => ['nome' => 'Aberto', 'cor' => 'danger'],
                                                    'em_andamento' => ['nome' => 'Em Andamento', 'cor' => 'warning'],
                                                    'aguardando_cliente' => ['nome' => 'Aguardando Cliente', 'cor' => 'info'],
                                                    'resolvido' => ['nome' => 'Resolvido', 'cor' => 'success'],
                                                    'fechado' => ['nome' => 'Fechado', 'cor' => 'secondary']
                                                ];
                                                ?>
                                                <?php if (!empty($dados['relatorio_status'])): ?>
                                                    <?php foreach ($dados['relatorio_status'] as $status): ?>
                                                        <?php 
                                                        $config = $statusConfig[$status->status] ?? ['nome' => $status->status, 'cor' => 'secondary'];
                                                        $percentual = $total > 0 ? round(($status->quantidade / $total) * 100, 1) : 0;
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-<?= $config['cor'] ?>">
                                                                    <?= $config['nome'] ?>
                                                                </span>
                                                            </td>
                                                            <td><strong><?= $status->quantidade ?></strong></td>
                                                            <td><?= $percentual ?>%</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            Nenhum ticket encontrado
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tempo Médio de Resolução -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-stopwatch me-2"></i>
                                Métricas de Performance
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h4 class="text-primary">
                                            <?= isset($dados['estatisticas']->tempo_medio_resolucao) && $dados['estatisticas']->tempo_medio_resolucao ? 
                                                round($dados['estatisticas']->tempo_medio_resolucao, 1) . 'h' : 
                                                'N/A' 
                                            ?>
                                        </h4>
                                        <p class="text-muted">Tempo Médio de Resolução</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h4 class="text-success">
                                            <?= isset($dados['estatisticas']->taxa_resolucao) ? 
                                                round($dados['estatisticas']->taxa_resolucao, 1) . '%' : 
                                                'N/A' 
                                            ?>
                                        </h4>
                                        <p class="text-muted">Taxa de Resolução</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h4 class="text-info">
                                            <?= $dados['estatisticas']->tickets_hoje ?? 0 ?>
                                        </h4>
                                        <p class="text-muted">Tickets Hoje</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Vencidos -->
                    <?php if (!empty($dados['tickets_vencidos'])): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Tickets Vencidos (mais de 24h)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Contato</th>
                                                <th>Status</th>
                                                <th>Responsável</th>
                                                <th>Aberto em</th>
                                                <th>Tempo em Aberto</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['tickets_vencidos'] as $ticket): ?>
                                                <?php 
                                                $horasAbertas = round((time() - strtotime($ticket->ticket_aberto_em)) / 3600, 1);
                                                $statusClasses = [
                                                    'aberto' => 'badge-danger',
                                                    'em_andamento' => 'badge-warning',
                                                    'aguardando_cliente' => 'badge-info',
                                                    'resolvido' => 'badge-success',
                                                    'fechado' => 'badge-secondary'
                                                ];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong><?= htmlspecialchars($ticket->contato_nome) ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?= htmlspecialchars($ticket->contato_numero) ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= $statusClasses[$ticket->status_atendimento] ?? 'badge-secondary' ?>">
                                                            <?= ucfirst(str_replace('_', ' ', $ticket->status_atendimento)) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($ticket->responsavel_nome ?? 'Não atribuído') ?>
                                                    </td>
                                                    <td>
                                                        <?= date('d/m/Y H:i', strtotime($ticket->ticket_aberto_em)) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-danger">
                                                            <?= $horasAbertas ?>h
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

                    <!-- Ações Rápidas -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-tools me-2"></i>
                                Ações Rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <a href="#" class="btn btn-outline-primary" onclick="exportarRelatorio('pdf')">
                                            <i class="fas fa-file-pdf me-2"></i> Exportar PDF
                                        </a>
                                        <a href="#" class="btn btn-outline-success" onclick="exportarRelatorio('excel')">
                                            <i class="fas fa-file-excel me-2"></i> Exportar Excel
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <a href="<?= URL ?>/chat/gerenciarTickets" class="btn btn-outline-secondary">
                                            <i class="fas fa-list me-2"></i> Gerenciar Tickets
                                        </a>
                                        <a href="<?= URL ?>/chat/dashboardTickets" class="btn btn-outline-info">
                                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                        </a>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Status
    const statusData = <?= json_encode($dados['relatorio_status']) ?>;
    
    if (statusData && statusData.length > 0) {
        const labels = statusData.map(item => {
            const statusNames = {
                'aberto': 'Aberto',
                'em_andamento': 'Em Andamento',
                'aguardando_cliente': 'Aguardando Cliente',
                'resolvido': 'Resolvido',
                'fechado': 'Fechado'
            };
            return statusNames[item.status] || item.status;
        });
        
        const data = statusData.map(item => item.quantidade);
        const colors = statusData.map(item => {
            const colorMap = {
                'aberto': '#dc3545',
                'em_andamento': '#ffc107',
                'aguardando_cliente': '#17a2b8',
                'resolvido': '#28a745',
                'fechado': '#6c757d'
            };
            return colorMap[item.status] || '#6c757d';
        });

        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    } else {
        // Exibir mensagem quando não há dados
        const ctx = document.getElementById('statusChart').getContext('2d');
        ctx.font = '16px Arial';
        ctx.fillStyle = '#666';
        ctx.textAlign = 'center';
        ctx.fillText('Nenhum dado para exibir', ctx.canvas.width / 2, ctx.canvas.height / 2);
    }
});

function exportarRelatorio(formato) {
    const params = new URLSearchParams(window.location.search);
    params.set('formato', formato);
    
    alert(`Funcionalidade de exportação ${formato.toUpperCase()} será implementada em breve.`);
    
    // Implementar exportação futura
    // window.location.href = `<?= URL ?>/chat/exportarRelatorio?${params.toString()}`;
}
</script>

<style>
.badge {
    font-size: 0.8em;
}

.card {
    border: none;
    box-shadow: 0 0 0 1px rgba(0,0,0,.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.display-4 {
    font-size: 2.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.btn-outline-primary:hover,
.btn-outline-secondary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,.15);
}
</style>

<?php include 'app/Views/include/footer.php' ?> 