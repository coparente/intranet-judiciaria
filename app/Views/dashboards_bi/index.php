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
                    <?= Helper::mensagem('dashboards_bi') ?>
                    <?= Helper::mensagemSweetAlert('dashboards_bi') ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-chart-bar me-2"></i> Dashboards BI
                        </h1>
                        <div class="text-end">
                            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                <a href="<?= URL ?>/dashboardsbi/painel" class="btn btn-success btn-sm">
                                    <i class="fas fa-desktop me-2"></i> Ver Painel
                                </a>
                                <a href="<?= URL ?>/dashboardsbi/cadastrar" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-2"></i> Novo Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <!-- Filtros -->
                                    <form method="get" action="<?= URL ?>/dashboardsbi/index">
                                        <div class="row">
                                            <div class="form-group col-4">
                                                <select class="form-control" name="categoria" onchange="this.form.submit()">
                                                    <option value="">Todas as Categorias</option>
                                                    <?php foreach ($dados['categorias'] as $categoria): ?>
                                                        <option value="<?= $categoria->categoria ?>" <?= isset($dados['categoria_atual']) && $dados['categoria_atual'] == $categoria->categoria ? 'selected' : '' ?>>
                                                            <?= $categoria->categoria ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-3">
                                                <select class="form-control" name="status" onchange="this.form.submit()">
                                                    <option value="">Todos os Status</option>
                                                    <option value="ativo" <?= isset($dados['status']) && $dados['status'] == 'ativo' ? 'selected' : '' ?>>Ativos</option>
                                                    <option value="inativo" <?= isset($dados['status']) && $dados['status'] == 'inativo' ? 'selected' : '' ?>>Inativos</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-5">
                                                <div class="input-group">
                                                    <input type="text"
                                                        class="form-control"
                                                        name="filtro"
                                                        placeholder="Buscar..."
                                                        value="<?= isset($dados['filtro']) ? htmlspecialchars($dados['filtro']) : '' ?>">
                                                    <button class="btn btn-primary btn-sm" type="submit">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- fim box-header -->
                                <fieldset aria-labelledby="tituloMenu">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="dashboards" width="100%" cellspacing="0">
                                                <thead class="cor-fundo-azul-escuro text-white">
                                                    <tr>
                                                        <th width="50">ID</th>
                                                        <th>Título</th>
                                                        <th>Categoria</th>
                                                        <th width="100">Status</th>
                                                        <th width="180">Criado em</th>
                                                        <th width="250">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($dados['dashboards'])): ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4">
                                                                <div class="text-muted">
                                                                    <i class="fas fa-info-circle me-2"></i> Nenhum dashboard encontrado
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($dados['dashboards'] as $dashboard): ?>
                                                            <tr>
                                                                <td><?= $dashboard->id ?></td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div>
                                                                            <i class="fas <?= $dashboard->icone ?> me-2"></i>
                                                                            <?= $dashboard->titulo ?>
                                                                            <?php if (isset($dashboard->descricao) && $dashboard->descricao): ?>
                                                                                <div class="small text-muted"><?= $dashboard->descricao ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td><?= $dashboard->categoria ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?= $dashboard->status == 'ativo' ? 'success' : 'secondary' ?>">
                                                                        <?= ucfirst($dashboard->status) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if (isset($dashboard->criado_em)): ?>
                                                                        <span class="text-muted">
                                                                            <?= date('d/m/Y H:i', strtotime($dashboard->criado_em)) ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group">
                                                                        <a href="<?= URL ?>/dashboardsbi/visualizar/<?= $dashboard->id ?>"
                                                                            class="btn btn-sm btn-info"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Visualizar">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        <a href="<?= URL ?>/dashboardsbi/editar/<?= $dashboard->id ?>"
                                                                            class="btn btn-sm btn-primary"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Editar">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-danger"
                                                                                data-toggle="modal"
                                                                                data-target="#deleteModal<?= $dashboard->id ?>"
                                                                                title="Excluir">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- Modal de exclusão -->
                                                            <div class="modal fade" id="deleteModal<?= $dashboard->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $dashboard->id ?>" aria-hidden="true">
                                                                <div class="modal-dialog" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="deleteModalLabel<?= $dashboard->id ?>">Confirmar Exclusão</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            Tem certeza que deseja excluir o dashboard <strong><?= $dashboard->titulo ?></strong>?
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                            <a href="<?= URL ?>/dashboardsbi/excluir/<?= $dashboard->id ?>" class="btn btn-danger">Excluir</a>
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
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?>
