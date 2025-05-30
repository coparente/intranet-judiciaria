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
                <!-- Cabeçalho da Página -->


                <!-- Conteúdo Principal -->
                <div class="col-md-9">
                    <div class="box box-info">
                        <div class="box-header with-border" id="tituloMenu">
                            <h3 class="box-title"><i class="fas fa-bell me-2"></i> Notificações</h3>
                        </div>

                        <div class="card-body">
                            <?php Helper::mensagem('notificacoes'); ?>
                            <?php Helper::mensagemSweetAlert('notificacoes'); ?>

                            <?php if (empty($dados['notificacoes'])) : ?>
                                <div class="alert alert-info"> <i class="fas fa-info-circle me-2"></i> Nenhuma notificação pendente</div>
                            <?php else : ?>
                                <div class="list-group">
                                    <?php foreach ($dados['notificacoes'] as $notificacao) : ?>
                                        <div class="list-group-item border-0 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="mb-1"><?= $notificacao->mensagem ?></p>
                                                    <span class="text-muted">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        Prazo: <?= date('d/m/Y', strtotime($notificacao->data_prazo)) ?>
                                                    </span>
                                                </div>
                                                <a href="<?= URL ?>/notificacoes/marcarLida/<?= $notificacao->id ?>"
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-check me-1"></i>
                                                    Marcar como lida
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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

<?php require APPROOT . '/Views/include/footer.php'; ?>