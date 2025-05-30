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
                    <?= Helper::mensagem('modulo') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-edit me-2"></i> Editar Módulo
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/modulos/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <!-- Formulário de Edição -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-th-list me-2"></i> Informações do Módulo
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/modulos/editar/<?= $dados['modulo']->id ?>" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nome" class="form-label">Nome do Módulo</label>
                                            <input type="text" class="form-control" id="nome" name="nome"
                                                value="<?= $dados['modulo']->nome ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="rota" class="form-label">Rota</label>
                                            <div class="input-group">
                                                <span class="input-group-text">/</span>
                                                <input type="text" class="form-control" id="rota" name="rota"
                                                    value="<?= $dados['modulo']->rota ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="icone" class="form-label">Ícone (FontAwesome)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-icons"></i></span>
                                                <input type="text" class="form-control" id="icone" name="icone"
                                                    value="<?= $dados['modulo']->icone ?>" required>
                                            </div>
                                            <div class="form-text">
                                                <small>Ex: fa-users, fa-cog, fa-chart-bar</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pai_id" class="form-label">Módulo Pai</label>
                                            <select class="form-control" id="pai_id" name="pai_id">
                                                <option value="">Selecione se for submódulo</option>
                                                <?php foreach ($dados['modulos_pai'] as $modulo): ?>
                                                    <?php if ($modulo->id != $dados['modulo']->id): ?>
                                                        <option value="<?= $modulo->id ?>"
                                                            <?= $dados['modulo']->pai_id == $modulo->id ? 'selected' : '' ?>>
                                                            <?= $modulo->nome ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="descricao" class="form-label">Descrição</label>
                                            <textarea class="form-control" id="descricao" name="descricao"
                                                rows="3"><?= $dados['modulo']->descricao ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="ativo" <?= $dados['modulo']->status == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                                <option value="inativo" <?= $dados['modulo']->status == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Botões -->
                                <div class="col-12 justify-content-end text-right">
                                    <a href="<?= URL ?>/modulos/listar" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times me-2"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-2"></i> Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Card de Ajuda -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i> Informações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Dica:</strong> Os módulos são componentes do sistema que podem ser acessados pelos usuários.
                                <ul class="mb-0 mt-2">
                                    <li>O <strong>Nome</strong> é exibido no menu e nas páginas do sistema.</li>
                                    <li>A <strong>Rota</strong> define o URL de acesso ao módulo (ex: "usuarios" para acessar /usuarios).</li>
                                    <li>O <strong>Ícone</strong> deve ser um código do FontAwesome (ex: "fa-users").</li>
                                    <li>Um módulo pode ser submódulo de outro, selecionando um <strong>Módulo Pai</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?>