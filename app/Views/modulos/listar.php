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
                    <!-- Cabeçalho da Página -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-th-list me-2"></i> Módulos do Sistema
                        </h1>
                        <div class="d-flex gap-2">
                            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCadastroModulo">
                                    <i class="fas fa-plus me-2"></i> Novo Módulo
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Alertas e Mensagens -->
                    <?= Helper::mensagem('modulo') ?>

                    <!-- Tabela de Módulos -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="cor-fundo-azul-escuro text-white">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Rota</th>
                                            <th>Ícone</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th width="150">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($dados['modulos'])): ?>
                                            <?php foreach ($dados['modulos'] as $modulo): ?>
                                                <tr>
                                                    <td>
                                                    ID: <?= $modulo['id'] ?> -
                                                        <i class="<?= $modulo['icone'] ?> me-2"></i>
                                                         <?= $modulo['nome'] ?>
                                                    </td>
                                                    <td><code><?= $modulo['rota'] ?></code></td>
                                                    <td><i class="<?= $modulo['icone'] ?>"></i></td>
                                                    <td>
                                                        <span class="badge bg-primary">Principal</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $modulo['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                                            <?= ucfirst($modulo['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= URL ?>/modulos/editar/<?= $modulo['id'] ?>"
                                                                class="btn btn-sm btn-primary"
                                                                data-toggle="tooltip"
                                                                title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="confirmarExclusao(<?= $modulo['id'] ?>)"
                                                                data-toggle="tooltip"
                                                                title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php if (!empty($modulo['submodulos'])): ?>
                                                    <?php foreach ($modulo['submodulos'] as $submodulo): ?>
                                                        <tr>
                                                            <td class="ps-4">
                                                                ID: <?= $submodulo['id'] ?> -
                                                                <i class="<?= $submodulo['icone'] ?> me-2"></i>
                                                                <?= $submodulo['nome'] ?>
                                                            </td>
                                                            <td><code><?= $submodulo['rota'] ?></code></td>
                                                            <td><i class="<?= $submodulo['icone'] ?>"></i></td>
                                                            <td>
                                                                <span class="badge bg-info">Submódulo</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?= $submodulo['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                                                    <?= ucfirst($submodulo['status']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="<?= URL ?>/modulos/editar/<?= $submodulo['id'] ?>"
                                                                        class="btn btn-sm btn-primary"
                                                                        data-toggle="tooltip"
                                                                        title="Editar">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger"
                                                                        onclick="confirmarExclusao(<?= $submodulo['id'] ?>)"
                                                                        data-toggle="tooltip"
                                                                        title="Excluir">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center"><i class="fas fa-info-circle me-2"></i> Nenhum módulo cadastrado</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- fim col-md-9 -->
            </div>
        </section>
    </div>
</main>



<!-- Modal de Cadastro -->
<div class="modal fade" id="modalCadastroModulo" tabindex="-1" aria-labelledby="modalCadastroModuloLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroModuloLabel">
                    <i class="fas fa-plus-circle me-2"></i> Novo Módulo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/modulos/cadastrar" method="POST" id="formCadastroModulo">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Módulo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="rota" class="form-label">Rota</label>
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            <input type="text" class="form-control" id="rota" name="rota" required
                                placeholder="modulo/acao">
                        </div>
                        <small class="text-muted">Exemplo: usuarios/listar</small>
                    </div>

                    <div class="mb-3">
                        <label for="icone" class="form-label">Ícone (FontAwesome)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-icons"></i></span>
                            <input type="text" class="form-control" id="icone" name="icone" required
                                placeholder="fas fa-users">
                        </div>
                        <small class="text-muted">Exemplo: fas fa-users</small>
                    </div>

                    <div class="mb-3">
                        <label for="pai_id" class="form-label">Módulo Pai</label>
                        <select class="form-control" id="pai_id" name="pai_id">
                            <option value="">Selecione se for submódulo</option>
                            <?php foreach ($dados['modulos'] as $modulo): ?>
                                <option value="<?= $modulo['id'] ?>"><?= $modulo['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"
                            placeholder="Breve descrição do módulo"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-2 "></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1" aria-labelledby="modalExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExclusaoLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este módulo?</p>
                <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <form action="" method="POST" id="formExclusao" class="d-inline">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-2"></i>Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'app/Views/include/footer.php' ?>
<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa os tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Manipulação do formulário
        const form = document.getElementById('formCadastroModulo');
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Aqui você pode adicionar validações adicionais se necessário

            this.submit();
        });
    });

    function confirmarExclusao(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
        const form = document.getElementById('formExclusao');
        form.action = `<?= URL ?>/modulos/excluir/${id}`;
        modal.show();
    }
</script>