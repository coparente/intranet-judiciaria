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
                    <?= Helper::mensagem('relatorios') ?>
                    <?= Helper::mensagemSweetAlert('relatorios') ?>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-chart-line me-2"></i> <?= $dados['tituloPagina'] ?>
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/dashboard/inicial" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filtros</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Usuário</label>
                                    <select name="usuario_id" class="form-control">
                                        <option value="">Todos os usuários</option>
                                        <?php foreach ($dados['usuarios'] as $usuario): ?>
                                            <option value="<?= $usuario->id ?>" <?= $dados['filtros']['usuario_id'] == $usuario->id ? 'selected' : '' ?>>
                                                <?= $usuario->nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data Início</label>
                                    <input type="date" name="data_inicio" class="form-control" value="<?= $dados['filtros']['data_inicio'] ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data Fim</label>
                                    <input type="date" name="data_fim" class="form-control" value="<?= $dados['filtros']['data_fim'] ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div>
                                        <button type="submit" class="btn btn-primary w-100 mb-2">
                                            <i class="fas fa-search me-2"></i> Buscar
                                        </button>
                                        <a href="<?= URL ?>/relatorios/produtividade" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-undo me-2"></i> Limpar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Botão de Exportar PDF -->
                    <div class="text-end mb-3">
                        <?php
                        $queryParams = array_filter([
                            'usuario_id' => $dados['filtros']['usuario_id'],
                            'data_inicio' => $dados['filtros']['data_inicio'],
                            'data_fim' => $dados['filtros']['data_fim']
                        ]);
                        $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                        ?>
                        <a href="<?= URL ?>/relatorios/gerarPdfProdutividade<?= $queryString ?>" 
                           class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Exportar PDF
                        </a>
                    </div>

                    <?php if (isset($dados['produtividade'])): ?>
                        <!-- Relatório de Usuário Específico -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i> Exibindo produtividade do usuário <strong><?= $dados['produtividade'][0]->nome_usuario ?? 'Não identificado' ?></strong> no período de <strong><?= date('d/m/Y', strtotime($dados['filtros']['data_inicio'])) ?></strong> a <strong><?= date('d/m/Y', strtotime($dados['filtros']['data_fim'])) ?></strong>
                        </div>
                        
                        <!-- Resumo do Usuário -->
                        <?php if (isset($dados['resumo_usuario'])): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-user-chart me-2"></i> Resumo do Usuário</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-primary bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Total de Processos</h6>
                                            <h3 class="text-primary text-center mb-0"><?= $dados['resumo_usuario']->total_processos ?? 0 ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-success bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Processos Concluídos</h6>
                                            <h3 class="text-success text-center mb-0"><?= $dados['resumo_usuario']->total_concluidos ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-warning bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Média de Dias p/ Conclusão</h6>
                                            <h3 class="text-warning text-center mb-0"><?= number_format($dados['resumo_usuario']->media_dias_conclusao, 1) ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-info bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Total de Movimentações</h6>
                                            <h3 class="text-info text-center mb-0"><?= $dados['resumo_usuario']->total_movimentacoes ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Tabela Detalhada por Data -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Detalhamento por Data</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Data</th>
                                                <th>Total Processos</th>
                                                <th>Concluídos</th>
                                                <th>Em Análise</th>
                                                <th>Em Intimação</th>
                                                <th>Em Diligência</th>
                                                <th>Movimentações</th>
                                                <th>Notificações</th>
                                                <th>Média Dias Conclusão</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['produtividade'] as $prod): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($prod->data)) ?></td>
                                                    <td><?= $prod->total_processos ?></td>
                                                    <td><?= $prod->concluidos ?></td>
                                                    <td><?= $prod->em_analise ?></td>
                                                    <td><?= $prod->em_intimacao ?></td>
                                                    <td><?= $prod->em_diligencia ?></td>
                                                    <td><?= $prod->total_movimentacoes ?></td>
                                                    <td><?= $prod->total_notificacoes ?></td>
                                                    <td><?= number_format($prod->media_dias_conclusao, 1) ?> dias</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    
                    <?php elseif (isset($dados['produtividade_geral'])): ?>
                        <!-- Relatório Geral de Todos os Usuários -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i> Exibindo produtividade de <strong>todos os usuários</strong> no período de <strong><?= date('d/m/Y', strtotime($dados['filtros']['data_inicio'])) ?></strong> a <strong><?= date('d/m/Y', strtotime($dados['filtros']['data_fim'])) ?></strong>
                        </div>
                        
                        <!-- Resumo Geral -->
                        <?php if (isset($dados['resumo_geral']) && $dados['resumo_geral']): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Resumo Geral</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-primary bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Total de Processos</h6>
                                            <h3 class="text-primary text-center mb-0"><?= $dados['resumo_geral']->total_processos ?? 0 ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-success bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Processos Concluídos</h6>
                                            <h3 class="text-success text-center mb-0"><?= $dados['resumo_geral']->total_concluidos ?? 0 ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-warning bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Média Dias p/ Conclusão</h6>
                                            <h3 class="text-warning text-center mb-0"><?= number_format($dados['resumo_geral']->media_dias_conclusao ?? 0, 1) ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="border rounded p-3 bg-info bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                            <h6 class="text-muted text-center">Total de Movimentações</h6>
                                            <h3 class="text-info text-center mb-0"><?= $dados['resumo_geral']->total_movimentacoes ?? 0 ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Tabela Comparativa de Usuários -->
                        <?php if (isset($dados['produtividade_geral']) && !empty($dados['produtividade_geral'])): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Comparativo por Usuário</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Usuário</th>
                                                <th>Perfil</th>
                                                <th>Total Processos</th>
                                                <th>Concluídos</th>
                                                <th>Em Análise</th>
                                                <th>Em Intimação</th>
                                                <th>Movimentações</th>
                                                <th>Média Dias Conclusão</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['produtividade_geral'] as $prod): ?>
                                            <tr>
                                                <td><?= $prod->nome_usuario ?? 'N/A' ?></td>
                                                <td>
                                                    <span class="badge <?= $prod->perfil_usuario == 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                        <?= ucfirst($prod->perfil_usuario ?? 'N/A') ?>
                                                    </span>
                                                </td>
                                                <td><?= $prod->total_processos ?? 0 ?></td>
                                                <td><?= $prod->total_concluidos ?? 0 ?></td>
                                                <td><?= $prod->total_analise ?? 0 ?></td>
                                                <td><?= $prod->total_intimacao ?? 0 ?></td>
                                                <td><?= $prod->total_movimentacoes ?? 0 ?></td>
                                                <td><?= number_format($prod->media_dias_conclusao ?? 0, 1) ?> dias</td>
                                                <td>
                                                    <a href="<?= URL ?>/relatorios/produtividade?usuario_id=<?= $prod->usuario_id ?>&data_inicio=<?= $dados['filtros']['data_inicio'] ?>&data_fim=<?= $dados['filtros']['data_fim'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
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
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Selecione os filtros e clique em "Buscar" para visualizar o relatório de produtividade.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?> 