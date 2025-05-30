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
                    <?= Helper::mensagem('processos') ?>
                    <?= Helper::mensagemSweetAlert('processos') ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-gavel me-2"></i> Processos de Custas
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/processos/cadastrar" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle me-2"></i> Novo Processo
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <!-- Filtros -->
                                    <form method="GET" action="<?= URL ?>/processos/listar">
                                        <div class="row">
                                            <div class="form-group col-3">
                                                <select class="form-control" name="status" onchange="this.form.submit()">
                                                    <option value="">Todos os Status</option>
                                                    <option value="analise" <?= isset($_GET['status']) && $_GET['status'] == 'analise' ? 'selected' : '' ?>>Em Análise</option>
                                                    <option value="intimacao" <?= isset($_GET['status']) && $_GET['status'] == 'intimacao' ? 'selected' : '' ?>>Em Intimação</option>
                                                    <option value="diligencia" <?= isset($_GET['status']) && $_GET['status'] == 'diligencia' ? 'selected' : '' ?>>Em Diligência</option>
                                                    <option value="aguardando" <?= isset($_GET['status']) && $_GET['status'] == 'aguardando' ? 'selected' : '' ?>>Aguardando Pagamento</option>
                                                    <option value="concluido" <?= isset($_GET['status']) && $_GET['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                                    <option value="arquivado" <?= isset($_GET['status']) && $_GET['status'] == 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4">
                                                <input type="text" 
                                                    class="form-control" 
                                                    name="comarca_serventia" 
                                                    placeholder="Comarca..." 
                                                    value="<?= isset($_GET['comarca_serventia']) ? htmlspecialchars($_GET['comarca_serventia']) : '' ?>">
                                            </div>
                                            <div class="form-group col-5">
                                                <div class="input-group">
                                                    <input type="text"
                                                        class="form-control"
                                                        name="numero_processo"
                                                        placeholder="Número do processo..."
                                                        value="<?= isset($_GET['numero_processo']) ? htmlspecialchars($_GET['numero_processo']) : '' ?>">
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
                                        <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                            <button type="button"
                                                id="btnDelegarLote"
                                                class="btn btn-primary btn-sm mb-3"
                                                data-toggle="modal"
                                                data-target="#modalDelegarLote"
                                                disabled>
                                                <i class="fas fa-users me-2"></i> Delegar Selecionados
                                            </button>
                                        <?php endif; ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover" class="display" id="processos" width="100%" cellspacing="0">
                                                <thead class="cor-fundo-azul-escuro text-white">
                                                    <tr>
                                                        <th><input type="checkbox" id="selecionarTodos"> Todos</th>
                                                        <th>Processo</th>
                                                        <th>Comarca</th>
                                                        <th width="120">Status</th>
                                                        <th width="150">Data Cadastro</th>
                                                        <th width="150">Responsável</th>
                                                        <th width="180" class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($dados['processos'])): ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center py-4">
                                                                <div class="text-muted">
                                                                    <i class="fas fa-info-circle me-2"></i> Nenhum processo encontrado
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($dados['processos'] as $processo): ?>
                                                            <?php 
                                                            $tooltipInfo = [];
                                                            
                                                            // Decodifica o JSON das guias se existir
                                                            $guias = json_decode($processo->guias);
                                                            
                                                            if (!empty($guias)) {
                                                                $tooltipInfo[] = "<strong>Guias de Pagamento:</strong>";
                                                                foreach ($guias as $guia) {
                                                                    $statusClass = '';
                                                                    switch ($guia->status) {
                                                                        case 'pago':
                                                                            $statusClass = 'text-success';
                                                                            break;
                                                                        case 'pendente':
                                                                            $statusClass = 'text-warning';
                                                                            break;
                                                                        default:
                                                                            $statusClass = 'text-danger';
                                                                            break;
                                                                    }
                                                                    
                                                                    $tooltipInfo[] = sprintf(
                                                                        "Guia %s: R$ %s - <span class='%s'>%s</span>",
                                                                        $guia->numero_guia,
                                                                        number_format($guia->valor, 2, ',', '.'),
                                                                        $statusClass,
                                                                        ucfirst($guia->status)
                                                                    );
                                                                    
                                                                    if (!empty($guia->observacao)) {
                                                                        $tooltipInfo[] = "<small>Obs: " . htmlspecialchars($guia->observacao) . "</small>";
                                                                    }
                                                                }
                                                            }
                                                            
                                                            if (!empty($processo->observacoes)) {
                                                                $tooltipInfo[] = "<hr><strong>Observações do Processo:</strong>";
                                                                $tooltipInfo[] = htmlspecialchars($processo->observacoes);
                                                            }
                                                            
                                                            $tooltipText = !empty($tooltipInfo) ? implode("<br>", $tooltipInfo) : "Sem informações adicionais";
                                                            ?>
                                                            
                                                            <tr data-toggle="tooltip"
                                                                data-html="true"
                                                                title="<?= $tooltipText ?>">
                                                                <td>
                                                                    <input type="checkbox" name="processos[]" value="<?= $processo->id ?>" class="processo-checkbox">
                                                                </td>
                                                                <td><?= $processo->numero_processo ?></td>
                                                                <td><?= $processo->comarca_serventia ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?= $processo->status == 'concluido' ? 'success' : ($processo->status == 'analise' ? 'primary text-white' : ($processo->status == 'intimacao' ? 'warning' : ($processo->status == 'diligencia' ? 'info' : ($processo->status == 'aguardando' ? 'secondary' : ($processo->status == 'arquivado' ? 'dark text-white' : 'light'))))) ?>">
                                                                        <?= ucfirst($processo->status) ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= date('d/m/Y H:i', strtotime($processo->data_cadastro)) ?></td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if ($processo->responsavel_nome): ?>
                                                                            <?= $processo->responsavel_nome ?>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">Não atribuído</span>
                                                                        <?php endif; ?>
                                                                        <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-primary ms-2"
                                                                                data-toggle="modal"
                                                                                data-target="#modalResponsavel<?= $processo->id ?>">
                                                                                <i class="fas fa-user-edit"></i>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                                <td width="180" class="text-center">
                                                                    <div class="btn-group">
                                                                        <a href="<?= URL ?>/processos/visualizar/<?= $processo->id ?>"
                                                                            class="btn btn-sm btn-info"
                                                                            data-toggle="tooltip"
                                                                            title="Visualizar">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                                            <a href="<?= URL ?>/processos/editar/<?= $processo->id ?>"
                                                                                class="btn btn-sm btn-primary"
                                                                                data-toggle="tooltip"
                                                                                title="Editar">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                        <?php endif; ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-warning"
                                                                            data-toggle="modal"
                                                                            data-target="#modalMovimentacao<?= $processo->id ?>"
                                                                            title="Movimentar">
                                                                            <i class="fas fa-exchange-alt"></i>
                                                                        </button>
                                                                        <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin' && $processo->status !== 'arquivado'): ?>
                                                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModalProcesso<?= $processo->id ?>" title="Excluir">
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
                                        <?php if ($dados['total_paginas'] > 1): ?>
                                            <div class="card-footer bg-white">
                                                <nav class="d-flex justify-content-between align-items-center">
                                                    <div class="text-muted small">
                                                        Mostrando <?= count($dados['processos']) ?> de <?= $dados['total_processos'] ?> registros
                                                    </div>
                                                    <ul class="pagination pagination-sm mb-0">
                                                        <?php if ($dados['pagina_atual'] > 1): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="<?= URL ?>/processos/listar/<?= ($dados['pagina_atual'] - 1) ?>?<?= http_build_query($dados['filtros']) ?>">
                                                                    <i class="fas fa-chevron-left"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php for ($i = 1; $i <= $dados['total_paginas']; $i++): ?>
                                                            <li class="page-item <?= $i == $dados['pagina_atual'] ? 'active' : '' ?>">
                                                                <a class="page-link" href="<?= URL ?>/processos/listar/<?= $i ?>?<?= http_build_query($dados['filtros']) ?>">
                                                                    <?= $i ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>

                                                        <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="<?= URL ?>/processos/listar/<?= ($dados['pagina_atual'] + 1) ?>?<?= http_build_query($dados['filtros']) ?>">
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

<!-- MODAIS -->
<!-- Modais de Movimentação -->
<?php foreach ($dados['processos'] as $processo): ?>
    <div class="modal fade" id="modalMovimentacao<?= $processo->id ?>" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt"></i> Atualizar Status do Processo
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/atualizarStatus/<?= $processo->id ?>" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Novo Status*</label>
                            <select name="status" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="analise">Em Análise</option>
                                <option value="diligencia">Em Diligência</option>
                                <option value="intimacao">Em Intimação</option>
                                <option value="aguardando">Aguardando Resposta</option>
                                <option value="concluido">Concluído</option>
                                <option value="arquivado">Arquivado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal para troca de responsável -->
<?php foreach ($dados['processos'] as $processo): ?>
    <div class="modal fade" id="modalResponsavel<?= $processo->id ?>" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit"></i> Alterar Responsável
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/atualizarResponsavel/<?= $processo->id ?>" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Novo Responsável*</label>
                            <?php if (!empty($dados['usuarios'])): ?>
                                <select name="responsavel_id" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($dados['usuarios'] as $usuario): ?>
                                        <option value="<?= $usuario->id ?>"
                                            <?= ($processo->responsavel_id == $usuario->id) ? 'selected' : '' ?>>
                                            <?= $usuario->nome ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <p class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Nenhum usuário com permissão encontrado.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alteração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de delegação em lote -->
<div class="modal fade" id="modalDelegarLote" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users"></i> Delegar Processos em Lote
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/delegarLote" method="POST" id="formDelegarLote">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Novo Responsável*</label>
                        <select name="responsavel_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($dados['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario->id ?>"><?= $usuario->nome ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="processosSelecionados"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para manipulação dos checkboxes e delegação em lote -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.processo-checkbox');
        const btnDelegarLote = document.getElementById('btnDelegarLote');
        const selecionarTodos = document.getElementById('selecionarTodos');
        const formDelegarLote = document.getElementById('formDelegarLote');
        const processosSelecionados = document.getElementById('processosSelecionados');

        function atualizarBotao() {
            const selecionados = document.querySelectorAll('.processo-checkbox:checked');
            btnDelegarLote.disabled = selecionados.length === 0;
        }

        selecionarTodos.addEventListener('change', function() {
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            atualizarBotao();
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', atualizarBotao);
        });

        formDelegarLote.addEventListener('submit', function() {
            const selecionados = document.querySelectorAll('.processo-checkbox:checked');
            selecionados.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'processos[]';
                input.value = checkbox.value;
                this.appendChild(input);
            });
        });
    });
</script>

<!-- Modal de confirmação de exclusão do processo -->
<?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
    <?php foreach ($dados['processos'] as $processo): ?>
        <div class="modal fade" id="deleteModalProcesso<?= $processo->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="deleteModalLabel">
                            <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Atenção!</strong> Você está prestes a excluir o processo:</p>
                        <p><strong>Número:</strong> <?= $processo->numero_processo ?></p>
                        <p><strong>Comarca:</strong> <?= $processo->comarca_serventia ?></p>
                        <?php if ($processo->status === 'arquivado'): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-ban"></i> Não é possível excluir processos arquivados.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle"></i> Esta ação excluirá permanentemente o processo e todos os seus registros relacionados (guias, partes, intimações, etc.) e não poderá ser desfeita.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <?php if ($processo->status !== 'arquivado'): ?>
                            <a href="<?= URL ?>/processos/excluirProcesso/<?= $processo->id ?>" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Confirmar Exclusão
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>