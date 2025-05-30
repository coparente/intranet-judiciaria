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
                    <?= Helper::mensagem('ciri') ?>
                    <?= Helper::mensagemSweetAlert('ciri') ?>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-tasks me-2"></i> Meus Processos CIRI
                            </h5>
                            <div>
                                <a href="<?= URL ?>/ciri/sortearProcesso" class="btn btn-success btn-sm">
                                    <i class="fas fa-random me-1"></i> Pegar Processo
                                </a>
                                <?php if ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista'): ?>
                                    <a href="<?= URL ?>/ciri/cadastrar" class="btn btn-light btn-sm ms-2">
                                        <i class="fas fa-plus me-1"></i> Novo Processo
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filtros de Busca -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-filter me-2"></i> Filtros
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="<?= URL ?>/ciri/meusProcessos">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="numero_processo" class="form-label">Número do Processo:</label>
                                                <input type="text" name="numero_processo" id="numero_processo" class="form-control" value="<?= $dados['filtros']['numero_processo'] ?? '' ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="comarca" class="form-label">Comarca/Serventia:</label>
                                                <input type="text" name="comarca" id="comarca" class="form-control" value="<?= $dados['filtros']['comarca'] ?? '' ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="status" class="form-label">Status:</label>
                                                <select name="status" id="status" class="form-control">
                                                    <option value="">Todos</option>
                                                    <option value="pendente" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                                    <option value="em_andamento" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                                    <option value="concluido" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                                    <option value="cancelado" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="destinatario_ciri_id">Destinatário:</label>
                                                <select name="destinatario_ciri_id" id="destinatario_ciri_id" class="form-control select2">
                                                    <option value="">Todos</option>
                                                    <?php foreach ($dados['destinatarios'] ?? [] as $destinatario): ?>
                                                        <option value="<?= $destinatario->id ?>" <?= isset($dados['filtros']['destinatario_ciri_id']) && $dados['filtros']['destinatario_ciri_id'] == $destinatario->id ? 'selected' : '' ?>>
                                                            <?= $destinatario->nome ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="data_inicio" class="form-label">Data Início:</label>
                                                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= $dados['filtros']['data_inicio'] ?? '' ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="data_fim" class="form-label">Data Fim:</label>
                                                <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= $dados['filtros']['data_fim'] ?? '' ?>">
                                            </div>
                                        </div>
                                        <div class="md-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-1"></i> Buscar
                                            </button>
                                            <a href="<?= URL ?>/ciri/meusProcessos" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i> Limpar
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Lista de Processos -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i> Meus Processos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($dados['processos'])): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i> Você não possui processos atribuídos.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Processo</th>
                                                        <th>Comarca/Serventia</th>
                                                        <th>Destinatário</th>
                                                        <th>Tipo de Ato</th>
                                                        <th>Status</th>
                                                        <th>obs</th>
                                                        <th>Data</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dados['processos'] as $processo): ?>
                                                        <tr>
                                                            <td><?= $processo->numero_processo ?></td>
                                                            <td><?= $processo->comarca_serventia ?></td>
                                                            <td>
                                                                <?php if (!empty($processo->destinatarios)): ?>
                                                                    <div class="destinatarios-lista">
                                                                        <?php foreach ($processo->destinatarios as $dest): ?>
                                                                            <div class="destinatario-item">
                                                                                <i class="fas fa-user me-1"></i> <?= $dest->nome ?>
                                                                                <?php if ($dest->telefone): ?>
                                                                                    <br><small class="text-muted">
                                                                                        <i class="fas fa-phone me-1"></i> <?= $dest->telefone ?>
                                                                                    </small>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Não definido</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= $processo->tipo_ato_nome ?? 'Não definido' ?></td>
                                                            <td>
                                                                <?php
                                                                $statusClass = '';
                                                                switch ($processo->status_processo) {
                                                                    case 'pendente':
                                                                        $statusClass = 'badge bg-warning';
                                                                        break;
                                                                    case 'AGUARDANDO RESPOSTA DE E-MAIL':
                                                                        $statusClass = 'badge bg-warning';
                                                                        break;
                                                                    case 'AGUARDANDO RESPOSTA DE WHATSAPP':
                                                                        $statusClass = 'badge bg-warning';
                                                                        break;
                                                                    case 'em_andamento':
                                                                        $statusClass = 'badge bg-primary';
                                                                        break;
                                                                    case 'concluido':
                                                                        $statusClass = 'badge bg-success';
                                                                        break;
                                                                    case 'PROCESSO FINALIZADO':
                                                                        $statusClass = 'badge bg-success';
                                                                        break;
                                                                    case 'cancelado':
                                                                        $statusClass = 'badge bg-danger';
                                                                        break;
                                                                    default:
                                                                        $statusClass = 'badge bg-secondary';
                                                                }
                                                                ?>
                                                                <span class="<?= $statusClass ?>">
                                                                    <?= ucfirst(str_replace('_', ' ', $processo->status_processo)) ?>
                                                                </span>
                                                            </td>
                                                            <td><?= $processo->observacao_atividade ?></td>
                                                            <td><?= date('d/m/Y H:i', strtotime($processo->criado_em)) ?></td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="<?= URL ?>/ciri/visualizarMeuProcesso/<?= $processo->id ?>" class="btn btn-sm btn-info" title="Visualizar">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    <a href="<?= URL ?>/ciri/editarMeusProcessos/<?= $processo->id ?>" class="btn btn-sm btn-primary" title="Editar">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Paginação -->
                                <?php if (isset($dados['paginacao']['total_paginas']) && $dados['paginacao']['total_paginas'] > 1): ?>
                                    <div class="card-footer bg-white">
                                        <nav class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted small">
                                                Mostrando <?= count($dados['processos']) ?> de <?= $dados['paginacao']['total_registros'] ?> registros
                                            </div>
                                            <ul class="pagination pagination-sm mb-0">
                                                <?php if ($dados['paginacao']['pagina_atual'] > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="<?= URL ?>/ciri/meusProcessos/<?= ($dados['paginacao']['pagina_atual'] - 1) ?>?numero_processo=<?= urlencode($dados['filtros']['numero_processo'] ?? '') ?>&comarca=<?= urlencode($dados['filtros']['comarca'] ?? '') ?>&status=<?= urlencode($dados['filtros']['status'] ?? '') ?>&data_inicio=<?= urlencode($dados['filtros']['data_inicio'] ?? '') ?>&data_fim=<?= urlencode($dados['filtros']['data_fim'] ?? '') ?>">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php for ($i = 1; $i <= $dados['paginacao']['total_paginas']; $i++): ?>
                                                    <li class="page-item <?= $i == $dados['paginacao']['pagina_atual'] ? 'active' : '' ?>">
                                                        <a class="page-link" href="<?= URL ?>/ciri/meusProcessos/<?= $i ?>?numero_processo=<?= urlencode($dados['filtros']['numero_processo'] ?? '') ?>&comarca=<?= urlencode($dados['filtros']['comarca'] ?? '') ?>&status=<?= urlencode($dados['filtros']['status'] ?? '') ?>&data_inicio=<?= urlencode($dados['filtros']['data_inicio'] ?? '') ?>&data_fim=<?= urlencode($dados['filtros']['data_fim'] ?? '') ?>">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>

                                                <?php if ($dados['paginacao']['pagina_atual'] < $dados['paginacao']['total_paginas']): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="<?= URL ?>/ciri/meusProcessos/<?= ($dados['paginacao']['pagina_atual'] + 1) ?>?numero_processo=<?= urlencode($dados['filtros']['numero_processo'] ?? '') ?>&comarca=<?= urlencode($dados['filtros']['comarca'] ?? '') ?>&status=<?= urlencode($dados['filtros']['status'] ?? '') ?>&data_inicio=<?= urlencode($dados['filtros']['data_inicio'] ?? '') ?>&data_fim=<?= urlencode($dados['filtros']['data_fim'] ?? '') ?>">
                                                            <i class="fas fa-chevron-right"></i>
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
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?>