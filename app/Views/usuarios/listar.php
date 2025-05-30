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
                    <?= Helper::mensagem('usuario') ?>
                    <?= Helper::mensagemSweetAlert('usuario') ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-users me-2"></i> Usuários
                        </h1>
                        <div class="text-end">
                            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista'): ?>
                                <a href="<?= URL ?>/usuarios/gerarPDF" class="btn btn-danger btn-sm" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i> Gerar PDF
                                </a>
                                <a href="<?= URL ?>/usuarios/cadastrar" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus me-2"></i> Novo Usuário
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <!-- Filtros -->
                                    <form method="get" action="<?= URL ?>/usuarios/listar">
                                        <div class="row">
                                            <div class="form-group col-3">
                                                <select class="form-control" name="status" onchange="this.form.submit()">
                                                    <option value="">Todos os Status</option>
                                                    <option value="ativo" <?= isset($dados['status']) && $dados['status'] == 'ativo' ? 'selected' : '' ?>>Ativos</option>
                                                    <option value="inativo" <?= isset($dados['status']) && $dados['status'] == 'inativo' ? 'selected' : '' ?>>Inativos</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4">
                                                <select class="form-control" name="perfil" onchange="this.form.submit()">
                                                    <option value="">Todos os Perfis</option>
                                                    <option value="admin" <?= isset($dados['perfil']) && $dados['perfil'] == 'admin' ? 'selected' : '' ?>>Administradores</option>
                                                    <option value="analista" <?= isset($dados['perfil']) && $dados['perfil'] == 'analista' ? 'selected' : '' ?>>Analistas</option>
                                                    <option value="usuario" <?= isset($dados['perfil']) && $dados['perfil'] == 'usuario' ? 'selected' : '' ?>>Usuários</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-5">
                                                <div class="input-group">
                                                    <input type="text"
                                                        class="form-control"
                                                        name="filtro"
                                                        placeholder="Buscar..."
                                                        value="<?= isset($dados['filtro']) ? htmlspecialchars($dados['filtro']) : '' ?>">
                                                    <button class="btn btn-primary btn-sm" type="submit">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- fim box-header -->
                                <fieldset aria-labelledby="tituloMenu">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="usuarios" width="100%" cellspacing="0">
                                                <thead class="cor-fundo-azul-escuro text-white">
                                                    <tr>
                                                        <th width="50">ID</th>
                                                        <th>Nome</th>
                                                        <th>Email</th>
                                                        <th width="120">Perfil</th>
                                                        <th width="100">Status</th>
                                                        <th width="180">Último Acesso</th>
                                                        <th width="250">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($dados['usuarios'])): ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center py-4">
                                                                <div class="text-muted">
                                                                    <i class="fas fa-info-circle me-2"></i> Nenhum usuário encontrado
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($dados['usuarios'] as $usuario): ?>
                                                            <tr>
                                                                <td><?= $usuario->id ?></td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div>
                                                                            <?= $usuario->nome ?>
                                                                            <?php if (isset($usuario->biografia) && $usuario->biografia): ?>
                                                                                <div class="small text-muted"><?= $usuario->biografia ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td><?= $usuario->email ?></td>
                                                                <td><?= ucfirst($usuario->perfil) ?></td>
                                                                <!-- <td>
                                                                    <span class="badge bg-<?= $usuario->perfil == 'admin' ? 'danger' : ($usuario->perfil == 'analista' ? 'warning' : 'info') ?>">
                                                                        <?= ucfirst($usuario->perfil) ?>
                                                                    </span>
                                                                </td> -->
                                                                <td>
                                                                    <span class="badge bg-<?= $usuario->status == 'ativo' ? 'success' : 'secondary' ?>">
                                                                        <?= ucfirst($usuario->status) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if (isset($usuario->ultimo_acesso) && $usuario->ultimo_acesso): ?>
                                                                        <span class="text-muted" data-bs-toggle="tooltip" title="<?= Helper::dataBr($usuario->ultimo_acesso) ?>">
                                                                            <?= date('d/m/Y H:i', strtotime($usuario->ultimo_acesso)) ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Nunca acessou</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group">
                                                                        <a href="<?= URL ?>/usuarios/editar/usuario/<?= $usuario->id ?>"
                                                                            class="btn btn-sm btn-primary"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Editar">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        <a href="<?= URL ?>/usuarios/permissoes/usuario/<?= $usuario->id ?>"
                                                                            class="btn btn-sm btn-success"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Permissões">
                                                                            <i class="fas fa-shield-alt"></i>
                                                                            Permissões
                                                                        </a>
                                                                        <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-danger"
                                                                                data-toggle="modal"
                                                                                data-target="#deleteModal<?= $usuario->id ?>"
                                                                                title="Excluir">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- Paginação -->
                                        <?php if (isset($dados['total_paginas']) && $dados['total_paginas'] > 1): ?>
                                            <div class="card-footer bg-white">
                                                <nav class="d-flex justify-content-between align-items-center">
                                                    <div class="text-muted small">
                                                        Mostrando <?= count($dados['usuarios']) ?> de <?= $dados['total_usuarios'] ?> registros
                                                    </div>
                                                    <ul class="pagination pagination-sm mb-0">
                                                        <?php if ($dados['pagina_atual'] > 1): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="<?= URL ?>/usuarios/listar/<?= ($dados['pagina_atual'] - 1) ?>?filtro=<?= urlencode($dados['filtro'] ?? '') ?>&status=<?= urlencode($dados['status'] ?? '') ?>&perfil=<?= urlencode($dados['perfil'] ?? '') ?>">
                                                                    <i class="fas fa-chevron-left"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php for ($i = 1; $i <= $dados['total_paginas']; $i++): ?>
                                                            <li class="page-item <?= $i == $dados['pagina_atual'] ? 'active' : '' ?>">
                                                                <a class="page-link" href="<?= URL ?>/usuarios/listar/<?= $i ?>?filtro=<?= urlencode($dados['filtro'] ?? '') ?>&status=<?= urlencode($dados['status'] ?? '') ?>&perfil=<?= urlencode($dados['perfil'] ?? '') ?>">
                                                                    <?= $i ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>

                                                        <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="<?= URL ?>/usuarios/listar/<?= ($dados['pagina_atual'] + 1) ?>?filtro=<?= urlencode($dados['filtro'] ?? '') ?>&status=<?= urlencode($dados['status'] ?? '') ?>&perfil=<?= urlencode($dados['perfil'] ?? '') ?>">
                                                                    <i class="fas fa-chevron-right"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </nav>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </fieldset>
                            </div><!-- fim box -->
                        </div>
                    </div> <!-- fim row -->
                </div><!-- fim col-md-9 -->
            </div>
        </section>
    </div>
</main>
<?php include 'app/Views/include/footer.php' ?>

<!-- MODAL DE EXCLUSÃO -->
<?php if (!empty($dados['usuarios'])): ?>
    <?php foreach ($dados['usuarios'] as $usuario): ?>
        <div class="modal fade" id="deleteModal<?= $usuario->id ?>" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                            title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja excluir o usuário <strong><?= $usuario->nome ?></strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <form action="<?= URL ?>/usuarios/deletar/usuario/<?= $usuario->id ?>" method="post" class="d-inline">
                            <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- FIM DO MODAL DE EXCLUSÃO -->