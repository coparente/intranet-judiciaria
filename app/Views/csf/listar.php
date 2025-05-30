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
                    <?= Helper::mensagem('csf') ?>
                    <?= Helper::mensagemSweetAlert('csf') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-home me-2"></i> Visitas Técnicas
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/csf/cadastrar" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-2"></i> Cadastrar Visita
                            </a>
                        </div>
                    </div>

                    <!-- Filtros de Busca -->
                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-search me-2"></i> Filtros de Busca
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= URL ?>/csf/listar" class="row g-3">
                                <div class="col-md-4 mt-3">
                                    <label for="processo" class="form-label">Processo:</label>
                                    <input type="text" name="processo" id="processo" class="form-control" value="<?= isset($_GET['processo']) ? $_GET['processo'] : '' ?>">
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label for="comarca" class="form-label">Comarca:</label>
                                    <input type="text" name="comarca" id="comarca" class="form-control" value="<?= isset($_GET['comarca']) ? $_GET['comarca'] : '' ?>">
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label for="proad" class="form-label">PROAD:</label>
                                    <input type="text" name="proad" id="proad" class="form-control" value="<?= isset($_GET['proad']) ? $_GET['proad'] : '' ?>">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label for="autor" class="form-label">Autor:</label>
                                    <input type="text" name="autor" id="autor" class="form-control" value="<?= isset($_GET['autor']) ? $_GET['autor'] : '' ?>">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label for="reu" class="form-label">Réu:</label>
                                    <input type="text" name="reu" id="reu" class="form-control" value="<?= isset($_GET['reu']) ? $_GET['reu'] : '' ?>">
                                </div>
                                <div class="col-12 text-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Buscar
                                    </button>
                                    <a href="<?= URL ?>/csf/listar" class="btn btn-secondary">
                                        <i class="fas fa-eraser me-2"></i> Limpar Filtros
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Listagem de Visitas -->
                    <form method="POST" action="<?= URL ?>/csf/excluirSelecionados">
                        <div class="card mb-4">
                            <div class="card-header cor-fundo-azul-escuro">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="fas fa-list me-2"></i> Visitas Cadastradas
                                    </h5>
                                    <!-- <div>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir os itens selecionados?')">
                                            <i class="fas fa-trash me-2"></i> Excluir Selecionados
                                        </button>
                                    </div> -->
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selecionar_todos"></th>
                                                <th>Processo</th>
                                                <th>Comarca</th>
                                                <th>Autor</th>
                                                <th>Réu</th>
                                                <th>PROAD</th>
                                                <th>Participantes</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($dados['visitas'])): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">Nenhuma visita encontrada</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($dados['visitas'] as $visita): ?>
                                                    <tr>
                                                        <td><input type="checkbox" name="selected_users[]" value="<?= $visita->id ?>" class="checkbox_item"></td>
                                                        <td><?= $visita->processo ?></td>
                                                        <td><?= $visita->comarca ?></td>
                                                        <td><?= $visita->autor ?></td>
                                                        <td><?= $visita->reu ?></td>
                                                        <td><?= $visita->proad ?></td>
                                                        <td><?= count($visita->participantes) ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="<?= URL ?>/csf/visualizar/<?= $visita->id ?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="Visualizar">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="<?= URL ?>/csf/editar/<?= $visita->id ?>" class="btn btn-primary btn-sm" data-toggle="tooltip" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger"
                                                                    data-toggle="modal"
                                                                    data-target="#deleteModal<?= $visita->id ?>"
                                                                    title="Excluir">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Modal de exclusão -->
                                                    <div class="modal fade" id="deleteModal<?= $visita->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $visita->id ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?= $visita->id ?>">Confirmar Exclusão</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Tem certeza que deseja excluir a visita <strong><?= $visita->processo ?></strong> ?
                                                                    <br> 
                                                                    <div class="alert alert-warning">
                                                                        <i class="fas fa-exclamation-circle"></i> Esta ação não poderá ser desfeita.
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                    <a href="<?= URL ?>/csf/excluir/<?= $visita->id ?>" class="btn btn-danger">Excluir</a>
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
                        </div>
                    </form>

                    <!-- Paginação -->
                    <?php if (isset($dados['paginacao']['total_paginas']) && $dados['paginacao']['total_paginas'] > 1): ?>
                        <div class="card-footer bg-white">
                            <nav class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Mostrando <?= count($dados['visitas']) ?> de <?= $dados['paginacao']['total_registros'] ?> registros
                                </div>
                                <ul class="pagination pagination-sm mb-0">
                                    <?php if ($dados['paginacao']['pagina_atual'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= URL ?>/csf/listar/<?= ($dados['paginacao']['pagina_atual'] - 1) ?>?processo=<?= urlencode($_GET['processo'] ?? '') ?>&comarca=<?= urlencode($_GET['comarca'] ?? '') ?>&autor=<?= urlencode($_GET['autor'] ?? '') ?>&reu=<?= urlencode($_GET['reu'] ?? '') ?>&proad=<?= urlencode($_GET['proad'] ?? '') ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $dados['paginacao']['total_paginas']; $i++): ?>
                                        <li class="page-item <?= $i == $dados['paginacao']['pagina_atual'] ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= URL ?>/csf/listar/<?= $i ?>?processo=<?= urlencode($_GET['processo'] ?? '') ?>&comarca=<?= urlencode($_GET['comarca'] ?? '') ?>&autor=<?= urlencode($_GET['autor'] ?? '') ?>&reu=<?= urlencode($_GET['reu'] ?? '') ?>&proad=<?= urlencode($_GET['proad'] ?? '') ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($dados['paginacao']['pagina_atual'] < $dados['paginacao']['total_paginas']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= URL ?>/csf/listar/<?= ($dados['paginacao']['pagina_atual'] + 1) ?>?processo=<?= urlencode($_GET['processo'] ?? '') ?>&comarca=<?= urlencode($_GET['comarca'] ?? '') ?>&autor=<?= urlencode($_GET['autor'] ?? '') ?>&reu=<?= urlencode($_GET['reu'] ?? '') ?>&proad=<?= urlencode($_GET['proad'] ?? '') ?>">
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
        </section>
    </div>
</main>

<script>
    // Função para selecionar/desselecionar todos os checkboxes
    document.getElementById('selecionar_todos').addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('checkbox_item');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });
</script>

<?php include 'app/Views/include/footer.php' ?>