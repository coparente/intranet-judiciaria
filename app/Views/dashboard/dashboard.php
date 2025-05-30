<?php include 'app/Views/include/nav.php' ?>

<main>
    <div class="content">

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <!-- Menu Lateral -->
                    <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                        <?php include 'app/Views/include/menu_adm.php' ?>
                    <?php endif; ?>
                    <?php include 'app/Views/include/menu.php' ?>
                </div>
                <div class="col-md-9">
                    <!-- Cabeçalho da Página -->
                    <?= Helper::mensagem('dashboard') ?>
                    <?= Helper::mensagemSweetAlert('dashboard') ?>
                    <!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h3 class="h3">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </h3>

                    </div> -->

                    <!-- Cards de Estatísticas -->
                    <div class="row g-3 mb-4">

                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>
                                        <?= isset($dados['total_usuarios']) ? $dados['total_usuarios'] : '0' ?>
                                    </h3>
                                    <p>Total de Usuários</p>
                                </div>
                                <div class="icon"> <i class="fas fa-users "></i> </div>
                                <a href="<?= URL ?>/usuarios/listar" class="small-box-footer"> Mais informações <i
                                        class="fa fa-arrow-circle-right"></i> </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>
                                        <?= isset($dados['usuarios_ativos']) ? $dados['usuarios_ativos'] : '0' ?>
                                    </h3>
                                    <p>Usuários Ativos</p>
                                </div>
                                <div class="icon"> <i class="fas fa-user-check"></i> </div>
                                <a href="processosConcluidos" class="small-box-footer"> Mais informações <i
                                        class="fa fa-arrow-circle-right"></i> </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>
                                        <?= isset($dados['acessos_hoje']) ? $dados['acessos_hoje'] : '0' ?>
                                    </h3>
                                    <p>Acessos Hoje</p>
                                </div>
                                <div class="icon"> <i class="fas fa-clock"></i> </div>
                                <a href="#" class="small-box-footer"> Mais informações <i
                                        class="fa fa-arrow-circle-right"></i> </a>
                            </div>
                        </div>

                        <!-- small box -->
                        <div class="col-lg-3 col-xs-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>
                                        
                                        <?= '0' ?>
                                    </h3>
                                    <p>###</p>
                                </div>
                                <div class="icon"> <i class="fas fa-bell"></i> </div>
                                <a href="#intimacoes" class="small-box-footer"> Mais informações <i
                                        class="fa fa-arrow-circle-right"></i> </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Pesquisa
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i> Pesquisa de Processos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/dashboard/pesquisar" method="POST" class="row g-3" id="formPesquisa" onsubmit="return validarPesquisa()">
                                <div class="col-md-6 mb-2">
                                    <div class="form-floating">
                                        <label for="numero_processo">Número do Processo</label>
                                        <input type="text" class="form-control" id="numero_processo" name="numero_processo" placeholder="Digite o número do processo">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-floating">
                                        <label for="numero_guia">Número da Guia</label>
                                        <input type="text" class="form-control" id="numero_guia" name="numero_guia" placeholder="Digite o número da guia">
                                    </div>
                                </div>
                                <br>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Preencha pelo menos um dos campos para realizar a pesquisa
                                    </div>
                                </div>
                                <div class="col-12 justify-content-end text-right">
                                    <button type="reset" class="btn btn-default btn-sm" onclick="limparCampos()"><i class="fas fa-eraser"></i>
                                        Limpar</button>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i>
                                        Pesquisar</button>
                                </div>
                            </form>
                        </div>
                    </div> -->
                    <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                    <!-- Últimas Atividades -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="fas fa-history me-2"></i> Últimas Atividades
                                </h4>
                                <a href="#" class="btn btn-sm btn-link text-decoration-none">Ver Todas</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if (isset($dados['atividades']) && !empty($dados['atividades'])): ?>
                                    <?php foreach ($dados['atividades'] as $atividade): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1 text-muted"><?= $atividade->perfil ?></h5>
                                                <span class="text-muted"><?= Helper::dataBr($atividade->ultimo_acesso) ?></span>
                                            </div>
                                            <p class="mb-1 text-muted"><?= $atividade->nome ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-info-circle me-2"></i>Nenhuma atividade recente
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
<?php include 'app/Views/include/footer.php' ?>