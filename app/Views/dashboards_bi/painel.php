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
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-desktop me-2"></i> Painel de Dashboards BI
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/dashboardsbi/index" class="btn btn-secondary btn-sm">
                                <i class="fas fa-list me-2"></i> Listar Dashboards
                            </a>
                            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                <a href="<?= URL ?>/dashboardsbi/cadastrar" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-2"></i> Novo Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Filtro por categoria -->
                    <?php if (!empty($dados['categorias'])): ?>
                        <div class="mb-4">
                            <div class="btn-group">
                                <a href="<?= URL ?>/dashboardsbi/painel" class="btn btn-outline-primary <?= empty($dados['categoria_atual']) ? 'active' : '' ?>">
                                    Todos
                                </a>
                                <?php foreach ($dados['categorias'] as $categoria): ?>
                                    <a href="<?= URL ?>/dashboardsbi/painel?categoria=<?= urlencode($categoria->categoria) ?>" 
                                       class="btn btn-outline-primary <?= $dados['categoria_atual'] == $categoria->categoria ? 'active' : '' ?>">
                                        <?= $categoria->categoria ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($dados['dashboards'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Nenhum dashboard encontrado.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($dados['dashboards'] as $dashboard): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas <?= $dashboard->icone ?> me-2"></i>
                                                <?= $dashboard->titulo ?>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><?= $dashboard->descricao ?></p>
                                            <a href="<?= URL ?>/dashboardsbi/visualizar/<?= $dashboard->id ?>" class="btn btn-primary">Visualizar</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?>
