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
                            <i class="fas fa-chart-bar me-2"></i> Estatísticas CIRI
                        </h1>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-filter me-2"></i> Filtros
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/estatisticasCIRI/index" method="GET" class="row g-3">
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

                    <!-- Resumo -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total de Processos</h5>
                                    <p class="display-4"><?= $dados['total_processos'] ?></p>
                                    <p class="text-muted">No período selecionado</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Tipos de Ato</h5>
                                    <p class="display-4"><?= count($dados['estatisticas_tipo_ato']) ?></p>
                                    <p class="text-muted">Diferentes tipos utilizados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Período</h5>
                                    <p class="h5 mt-3">
                                        <?= date('d/m/Y', strtotime($dados['filtros']['data_inicio'])) ?>
                                        <br>até<br>
                                        <?= date('d/m/Y', strtotime($dados['filtros']['data_fim'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas por Tipo de Ato -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i> Processos por Tipo de Ato
                            </h5>
                            <div>
                                <a href="<?= URL ?>/estatisticasCIRI/porTipoAto?data_inicio=<?= $dados['filtros']['data_inicio'] ?>&data_fim=<?= $dados['filtros']['data_fim'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-chart-pie me-1"></i> Detalhes
                                </a>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tipo de Ato</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Percentual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($dados['estatisticas_tipo_ato'])): ?>
                                            <?php foreach ($dados['estatisticas_tipo_ato'] as $estatistica): ?>
                                                <tr>
                                                    <td><?= $estatistica->nome_tipo_ato ?: 'Não definido' ?></td>
                                                    <td class="text-center"><?= $estatistica->total ?></td>
                                                    <td class="text-center">
                                                        <?php 
                                                        $percentual = ($dados['total_processos'] > 0) ? 
                                                            round(($estatistica->total / $dados['total_processos']) * 100, 2) : 0;
                                                        echo $percentual . '%';
                                                        ?>
                                                        <div class="progress" style="height: 5px;">
                                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentual ?>%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhum dado encontrado para o período selecionado.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas por Status -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks me-2"></i> Processos por Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Percentual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($dados['estatisticas_status'])): ?>
                                            <?php foreach ($dados['estatisticas_status'] as $estatistica): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $status = $estatistica->status_processo ?: 'Não definido';
                                                        $badgeClass = 'badge bg-secondary';
                                                        
                                                        switch ($status) {
                                                            case 'pendente':
                                                                $badgeClass = 'badge bg-warning text-dark';
                                                                $status = 'Pendente';
                                                                break;
                                                            case 'em_andamento':
                                                                $badgeClass = 'badge bg-info text-dark';
                                                                $status = 'Em Andamento';
                                                                break;
                                                            case 'concluido':
                                                                $badgeClass = 'badge bg-success';
                                                                $status = 'Concluído';
                                                                break;
                                                            case 'cancelado':
                                                                $badgeClass = 'badge bg-danger';
                                                                $status = 'Cancelado';
                                                                break;
                                                        }
                                                        
                                                        echo "<span class='$badgeClass'>$status</span>";
                                                        ?>
                                                    </td>
                                                    <td class="text-center"><?= $estatistica->total ?></td>
                                                    <td class="text-center">
                                                        <?php 
                                                        $percentual = ($dados['total_processos'] > 0) ? 
                                                            round(($estatistica->total / $dados['total_processos']) * 100, 2) : 0;
                                                        echo $percentual . '%';
                                                        ?>
                                                        <div class="progress" style="height: 5px;">
                                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentual ?>%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhum dado encontrado para o período selecionado.</td>
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

<?php include 'app/Views/include/footer.php' ?> 