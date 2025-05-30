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
                    <?= Helper::mensagem('estatisticas') ?>
                    <?= Helper::mensagemSweetAlert('estatisticas') ?>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-chart-pie me-2"></i> Estatísticas do Sistema
                        </h1>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Nome do Usuário</label>
                                            <input type="text" name="nome_usuario" class="form-control" 
                                                   value="<?= isset($dados['filtros']['nome_usuario']) ? $dados['filtros']['nome_usuario'] : '' ?>" 
                                                   placeholder="Digite o nome do usuário">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Data Início</label>
                                            <input type="date" name="data_inicio" class="form-control" 
                                                   value="<?= isset($dados['filtros']['data_inicio']) ? $dados['filtros']['data_inicio'] : '' ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Data Fim</label>
                                            <input type="date" name="data_fim" class="form-control" 
                                                   value="<?= isset($dados['filtros']['data_fim']) ? $dados['filtros']['data_fim'] : '' ?>">
                                        </div>
                                        <div class="col-md-3 align-items-end mt-4">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-filter me-2"></i>Filtrar
                                            </button>
                                            <a href="<?= URL ?>/estatisticas/atividades" class="btn btn-secondary me-2">
                                                <i class="fas fa-undo me-2"></i>Limpar
                                            </a>
                                            <?php
                                            // Constrói a query string apenas com os parâmetros de filtro necessários
                                            $queryParams = array_filter([
                                                'nome_usuario' => isset($dados['filtros']['nome_usuario']) ? $dados['filtros']['nome_usuario'] : '',
                                                'data_inicio' => isset($dados['filtros']['data_inicio']) ? $dados['filtros']['data_inicio'] : '',
                                                'data_fim' => isset($dados['filtros']['data_fim']) ? $dados['filtros']['data_fim'] : ''
                                            ]);
                                            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                                            ?>
                                            <a href="<?= URL ?>/estatisticas/gerarPdf<?= $queryString ?>" 
                                               class="btn btn-danger" target="_blank">
                                                <i class="fas fa-file-pdf me-2"></i>PDF
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cards de Estatísticas Gerais -->
                    <div class="row g-4 mb-4 mt-3">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Total de Atividades</h5>
                                    <h2 class="mb-0"><?= $dados['estatisticas_gerais']['total_atividades'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-success">Usuários Ativos Hoje</h5>
                                    <h2 class="mb-0"><?= $dados['estatisticas_gerais']['usuarios_ativos_hoje'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-info">Média por Sessão</h5>
                                    <h2 class="mb-0"><?= $dados['estatisticas_gerais']['media_tempo_sessao'] ?> min</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-warning">Tempo Total</h5>
                                    <h2 class="mb-0"><?= number_format($dados['tempo_total_sistema'], 2) ?> min</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Tempo de Sessão por Usuário -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h5 class="mb-0">Tempo de Sessão por Usuário</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="cor-fundo-azul-escuro text-white">
                                                <tr>
                                                    <th>Usuário</th>
                                                    <th>Perfil</th>
                                                    <th>Dias Ativos</th>
                                                    <th>Primeira Atividade</th>
                                                    <th>Última Atividade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dados['tempo_sessao'] as $sessao): ?>
                                                    <tr>
                                                        <td><?= $sessao->nome ?></td>
                                                        <td><span class="badge bg-<?= $sessao->perfil == 'admin' ? 'danger' : 'primary' ?>"><?= $sessao->perfil ?></span></td>
                                                        <td><?= $sessao->dias_ativos ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($sessao->primeira_atividade)) ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($sessao->ultima_atividade)) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($dados['estatisticas_usuario']): ?>
                    <!-- Estatísticas Detalhadas do Usuário -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h5 class="mb-0">Estatísticas Detalhadas do Usuário</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="card-subtitle mb-2 text-muted">Total de Dias Ativos</h6>
                                                    <h3 class="mb-0 text-primary"><?= $dados['estatisticas_usuario']->total_dias ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="card-subtitle mb-2 text-muted">Média de Tempo por Dia</h6>
                                                    <h3 class="mb-0 text-success"><?= number_format($dados['estatisticas_usuario']->media_minutos_dia, 2) ?> min</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="card-subtitle mb-2 text-muted">Tempo Total de Uso</h6>
                                                    <h3 class="mb-0 text-info"><?= number_format($dados['estatisticas_usuario']->total_minutos, 2) ?> min</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once APPROOT . '/Views/include/footer.php'; ?> 