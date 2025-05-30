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
                            <i class="fas fa-bell me-2"></i> Gerenciar Tipos de Intimação
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/adicionarTipoIntimacao" class="btn btn-success btn-sm">
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
                                <i class="fas fa-list-alt me-2"></i> Tipos de Intimação Cadastrados
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['tipos_intimacao'])): ?>
                                <div class="alert alert-info m-3">
                                    <i class="fas fa-info-circle me-2"></i> Nenhum tipo de intimação cadastrado.
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
                                            <?php foreach ($dados['tipos_intimacao'] as $tipo): ?>
                                                <tr>
                                                    <td><?= $tipo->nome ?></td>
                                                    <td><?= $tipo->descricao ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= URL ?>/ciri/editarTipoIntimacao/<?= $tipo->id ?>" class="btn btn-sm btn-primary" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($_SESSION['usuario_perfil'] == 'admin'): ?>
                                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteTipoIntimacaoModal<?= $tipo->id ?>" title="Excluir">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Modal de exclusão -->
                                                        <div class="modal fade" id="deleteTipoIntimacaoModal<?= $tipo->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteTipoIntimacaoModalLabel<?= $tipo->id ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteTipoIntimacaoModalLabel<?= $tipo->id ?>">Confirmar Exclusão</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Tem certeza que deseja excluir o tipo de intimação <strong><?= $tipo->nome ?></strong>?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                        <a href="<?= URL ?>/ciri/excluirTipoIntimacao/<?= $tipo->id ?>" class="btn btn-danger">Excluir</a>
                                                                    </div>
                                                                </div>
                                                            </div>
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

<?php include 'app/Views/include/footer.php' ?> 