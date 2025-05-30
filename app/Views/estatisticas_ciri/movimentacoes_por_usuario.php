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
                    <?= Helper::mensagem('estatisticasCIRI') ?>
                    <?= Helper::mensagemSweetAlert('estatisticasCIRI') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-exchange-alt me-2"></i> Movimentações por Usuário
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/estatisticasCIRI/porUsuario?data_inicio=<?= $dados['filtros']['data_inicio'] ?>&data_fim=<?= $dados['filtros']['data_fim'] ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-filter me-2"></i> Filtros
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/estatisticasCIRI/movimentacoesPorUsuario" method="GET" class="row g-3">
                                <div class="col-md-5">
                                    <label for="data_inicio" class="form-label">Data Início:</label>
                                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= $dados['filtros']['data_inicio'] ?>">
                                </div>
                                <div class="col-md-5">
                                    <label for="data_fim" class="form-label">Data Fim:</label>
                                    <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= $dados['filtros']['data_fim'] ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Gráfico -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i> Distribuição de Movimentações por Usuário
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoMovimentacoes" width="400" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Tabela Detalhada -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i> Detalhamento de Movimentações por Usuário
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuário</th>
                                            <th class="text-center">Total de Movimentações</th>
                                            <th class="text-center">Percentual</th>
                                            <th>Gráfico</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($dados['estatisticas'])): ?>
                                            <?php foreach ($dados['estatisticas'] as $estatistica): ?>
                                                <tr>
                                                    <td><?= $estatistica->nome_usuario ?: 'Não identificado' ?></td>
                                                    <td class="text-center"><?= $estatistica->total ?></td>
                                                    <td class="text-center">
                                                        <?php 
                                                        $totalMovimentacoes = isset($dados['total_movimentacoes']) ? $dados['total_movimentacoes'] : 0;
                                                        $percentual = ($totalMovimentacoes > 0) ? 
                                                            round(($estatistica->total / $totalMovimentacoes) * 100, 2) : 0;
                                                        echo $percentual . '%';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-info" role="progressbar" 
                                                                style="width: <?= $percentual ?>%;" 
                                                                aria-valuenow="<?= $percentual ?>" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                                <?= $percentual ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Nenhum dado encontrado para o período selecionado.</td>
                                            </tr>
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

<!-- Scripts para o gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados para o gráfico
    const ctx = document.getElementById('graficoMovimentacoes').getContext('2d');
    
    // Extrair dados da tabela para o gráfico
    const labels = [];
    const data = [];
    const backgroundColors = [
        '#36b9cc', '#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', 
        '#5a5c69', '#6f42c1', '#fd7e14', '#20c997', '#6610f2'
    ];
    
    <?php if (!empty($dados['estatisticas'])): ?>
        <?php foreach ($dados['estatisticas'] as $index => $estatistica): ?>
            labels.push('<?= addslashes($estatistica->nome_usuario ?: "Não identificado") ?>');
            data.push(<?= $estatistica->total ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    // Criar o gráfico
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total de Movimentações',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include 'app/Views/include/footer.php' ?> 