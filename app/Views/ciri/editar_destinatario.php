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

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-edit me-2"></i> Editar Destinatário
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/visualizar/<?= $dados['destinatario']->processo_id ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i> Formulário de Edição
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/ciri/editarDestinatario/<?= $dados['destinatario']->id ?>" method="POST">
                                <div class="form-group mb-3">
                                    <label for="nome">Nome:</label>
                                    <input type="text" name="nome" id="nome" class="form-control" value="<?= $dados['destinatario']->nome ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="email">E-mail:</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?= $dados['destinatario']->email ?>">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="telefone">Telefone:</label>
                                    <input type="text" name="telefone" id="telefone" class="form-control" value="<?= $dados['destinatario']->telefone ?>">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?> 