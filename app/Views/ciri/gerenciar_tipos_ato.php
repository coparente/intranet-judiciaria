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
                            <i class="fas fa-list me-2"></i> Gerenciar Tipos de Ato
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/adicionarTipoAto" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-2"></i> Novo Tipo
                            </a>
                            <a href="<?= URL ?>/ciri/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-list-alt me-2"></i> Tipos de Ato Cadastrados
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['tipos_ato'])): ?>
                                <div class="alert alert-info m-3">
                                    <i class="fas fa-info-circle me-2"></i> Nenhum tipo de ato cadastrado.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Descrição</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['tipos_ato'] as $tipo): ?>
                                                <tr>
                                                    <td><?= $tipo->nome ?></td>
                                                    <td><?= $tipo->descricao ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= URL ?>/ciri/editarTipoAto/<?= $tipo->id ?>" class="btn btn-sm btn-primary" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($_SESSION['usuario_perfil'] == 'admin'): ?>
                                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteTipoAtoModal<?= $tipo->id ?>" title="Excluir">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modais de confirmação de exclusão -->
<?php if (!empty($dados['tipos_ato'])): ?>
    <?php foreach ($dados['tipos_ato'] as $tipo): ?>
        <div class="modal fade" id="deleteTipoAtoModal<?= $tipo->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteTipoAtoModalLabel<?= $tipo->id ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="deleteTipoAtoModalLabel<?= $tipo->id ?>">
                            <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir o tipo de ato:</p>
                        <p><strong>Nome:</strong> <?= $tipo->nome ?></p>
                        <p><strong>Descrição:</strong> <?= $tipo->descricao ?></p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i> Esta ação excluirá permanentemente este tipo de ato e não poderá ser desfeita.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <a href="<?= URL ?>/ciri/excluirTipoAto/<?= $tipo->id ?>" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Excluir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'app/Views/include/footer.php' ?> 