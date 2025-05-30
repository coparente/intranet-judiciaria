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
                            <i class="fas fa-plus-circle me-2"></i> Novo Tipo de Ato
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/gerenciarTiposAto" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-list-alt me-2"></i> Dados do Tipo de Ato
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/ciri/adicionarTipoAto" method="POST">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="nome">Nome: <span class="text-danger">*</span></label>
                                        <input type="text" name="nome" id="nome" class="form-control <?= isset($dados['nome_erro']) && !empty($dados['nome_erro']) ? 'is-invalid' : '' ?>" value="<?= $dados['nome'] ?? '' ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['nome_erro'] ?? '' ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="descricao">Descrição:</label>
                                        <textarea name="descricao" id="descricao" class="form-control" rows="4"><?= $dados['descricao'] ?? '' ?></textarea>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i> Salvar
                                    </button>
                                    <a href="<?= URL ?>/ciri/gerenciarTiposAto" class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i> Cancelar
                                    </a>
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