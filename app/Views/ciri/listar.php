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
                            <i class="fas fa-gavel me-2"></i> Central de Intimação Remota do Interior
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/cadastrar" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-2"></i> Novo Processo
                            </a>
                            <a href="<?= URL ?>/ciri/listar" 
                               class="btn btn-warning btn-sm"
                               data-toggle="modal"
                               data-target="#processosDuplicadosModal">
                                <i class="fas fa-copy me-2"></i> Processos Duplicados
                            </a>
                            <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-cog me-2"></i> Configurações
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="<?= URL ?>/ciri/gerenciarTiposAto">
                                            <i class="fas fa-list me-2"></i> Tipos de Ato
                                        </a>
                                        <a class="dropdown-item" href="<?= URL ?>/ciri/gerenciarTiposIntimacao">
                                            <i class="fas fa-bell me-2"></i> Tipos de Intimação
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#delegarModal">
                                <i class="fas fa-user-plus me-2"></i> Delegar Processos
                            </button> -->
                        </div>
                    </div>

                    <!-- Filtros de Busca -->
                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-filter me-2"></i> Filtros
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= URL ?>/ciri/listar">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="numero_processo">Número do Processo:</label>
                                        <input type="text" name="numero_processo" id="numero_processo" class="form-control" value="<?= $dados['filtros']['numero_processo'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="comarca">Comarca/Serventia:</label>
                                        <input type="text" name="comarca" id="comarca" class="form-control" value="<?= $dados['filtros']['comarca'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="status">Status:</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">Todos</option>
                                            <option value="pendente" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                            <option value="em_andamento" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                            <option value="concluido" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                            <option value="cancelado" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                            <option value="PROCESSO FINALIZADO" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'PROCESSO FINALIZADO' ? 'selected' : '' ?>>PROCESSO FINALIZADO</option>
                                            <option value="RETORNAR PARA ANÁLISE" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'RETORNAR PARA ANÁLISE' ? 'selected' : '' ?>>RETORNAR PARA ANÁLISE</option>
                                            <option value="AGUARDANDO RESPOSTA DE WHATSAPP" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'AGUARDANDO RESPOSTA DE WHATSAPP' ? 'selected' : '' ?>>AGUARDANDO RESPOSTA DE WHATSAPP</option>
                                            <option value="AGUARDANDO RESPOSTA DE E-MAIL" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'AGUARDANDO RESPOSTA DE E-MAIL' ? 'selected' : '' ?>>AGUARDANDO RESPOSTA DE E-MAIL</option>
                                            <option value="AGUARDANDO PROVIDÊNCIA" <?= isset($dados['filtros']['status']) && $dados['filtros']['status'] == 'AGUARDANDO PROVIDÊNCIA' ? 'selected' : '' ?>>AGUARDANDO PROVIDÊNCIA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="usuario_id">Responsável:</label>
                                        <select name="usuario_id" id="usuario_id" class="form-control select2">
                                            <option value="">Todos</option>
                                            <option value="null" <?= isset($dados['filtros']['usuario_id']) && $dados['filtros']['usuario_id'] === 'null' ? 'selected' : '' ?>>Não atribuído</option>
                                            <?php foreach ($dados['usuarios'] ?? [] as $usuario): ?>
                                                <option value="<?= $usuario->id ?>" <?= isset($dados['filtros']['usuario_id']) && $dados['filtros']['usuario_id'] == $usuario->id ? 'selected' : '' ?>>
                                                    <?= $usuario->nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="destinatario_ciri_id">Destinatário:</label>
                                        <select name="destinatario_ciri_id" id="destinatario_ciri_id" class="form-control select2">
                                            <option value="">Todos</option>
                                            <?php foreach ($dados['destinatarios'] ?? [] as $destinatario): ?>
                                                <option value="<?= $destinatario->id ?>" <?= isset($dados['filtros']['destinatario_ciri_id']) && $dados['filtros']['destinatario_ciri_id'] == $destinatario->id ? 'selected' : '' ?>>
                                                    <?= $destinatario->nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tipo_ato">Tipo de Ato:</label>
                                        <select name="tipo_ato" id="tipo_ato" class="form-control">
                                            <option value="">Todos</option>
                                            <?php foreach ($dados['tipos_ato'] ?? [] as $tipo): ?>
                                                <option value="<?= $tipo->id ?>" <?= isset($dados['filtros']['tipo_ato']) && $dados['filtros']['tipo_ato'] == $tipo->id ? 'selected' : '' ?>>
                                                    <?= $tipo->nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                     
                                    <div class="col-md-4 mb-3">
                                        <label for="data_inicio">Data Início:</label>
                                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= $dados['filtros']['data_inicio'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="data_fim">Data Fim:</label>
                                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= $dados['filtros']['data_fim'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Buscar
                                    </button>
                                    <a href="<?= URL ?>/ciri/listar" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i> Limpar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Processos -->
                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-list me-2"></i> Processos
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                <button type="button"
                                    id="btnDelegarLote"
                                    class="btn btn-primary btn-sm mb-3"
                                    data-toggle="modal"
                                    data-target="#delegarModal"
                                    disabled>
                                    <i class="fas fa-users me-2"></i> Delegar Selecionados
                                </button>
                            <?php endif; ?>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="cor-fundo-azul-escuro text-white">
                                        <tr>
                                            <th><input type="checkbox" id="selecionarTodos"> Todos</th>
                                            <th>Número do Processo</th>
                                            <th>Comarca/Serventia</th>
                                            <th>Destinatário</th>
                                            <th>Tipo de Ato</th>
                                            <th>Status</th>
                                            <th>Data Cadastro</th>
                                            <th>Responsável</th>
                                            <th width="150">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['processos'])): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Nenhum processo encontrado</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dados['processos'] as $processo): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($processo->status_processo == 'pendente' && ($processo->usuario_id == NULL)): ?>
                                                            <input type="checkbox" class="processo-checkbox" value="<?= $processo->id ?>">
                                                        <?php else: ?>
                                                            <i class="fas fa-lock text-muted" title="Processo não disponível para delegação"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $processo->numero_processo ?></td>
                                                    <td><?= $processo->comarca_serventia ?? 'Não definido' ?></td>
                                                    <td>
                                                        <?php if (!empty($processo->destinatarios)): ?>
                                                            <div class="destinatarios-lista">
                                                                <?php foreach ($processo->destinatarios as $dest): ?>
                                                                    <div class="destinatario-item">
                                                                        <i class="fas fa-user me-1"></i> <?= $dest->nome ?>
                                                                        <?php if ($dest->telefone): ?>
                                                                            <br><small class="text-muted">
                                                                                <i class="fas fa-phone me-1"></i> <?= $dest->telefone ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">Não definido</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $processo->tipo_ato_nome ?? 'Não definido' ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClasses = [
                                                            'pendente' => 'badge bg-warning',
                                                            'em_andamento' => 'badge bg-primary',
                                                            'concluido' => 'badge bg-success',
                                                            'cancelado' => 'badge bg-danger',
                                                            'PROCESSO FINALIZADO' => 'badge bg-success',
                                                            'RETORNAR PARA ANÁLISE' => 'badge bg-warning',
                                                            'AGUARDANDO RESPOSTA DE WHATSAPP' => 'badge bg-info',
                                                            'AGUARDANDO RESPOSTA DE E-MAIL' => 'badge bg-info',
                                                            'AGUARDANDO PROVIDÊNCIA' => 'badge bg-info'
                                                        ];
                                                        $statusTexto = [
                                                            'pendente' => 'Pendente',
                                                            'em_andamento' => 'Em Andamento',
                                                            'concluido' => 'Concluído',
                                                            'cancelado' => 'Cancelado',
                                                            'PROCESSO FINALIZADO' => 'Processo Finalizado',
                                                            'RETORNAR PARA ANÁLISE' => 'Retornar para Análise',
                                                            'AGUARDANDO RESPOSTA DE WHATSAPP' => 'Aguardando Resposta de Whatsapp',
                                                            'AGUARDANDO RESPOSTA DE E-MAIL' => 'Aguardando Resposta de E-mail',
                                                            'AGUARDANDO PROVIDÊNCIA' => 'Aguardando Província'
                                                        ];
                                                        $classe = $statusClasses[$processo->status_processo] ?? 'badge bg-secondary';
                                                        $texto = $statusTexto[$processo->status_processo] ?? 'Desconhecido';
                                                        ?>
                                                        <span class="<?= $classe ?>"><?= $texto ?></span>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($processo->criado_em)) ?></td>
                                                    <td><?= $processo->usuario_nome ?? 'Não atribuído' ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= URL ?>/ciri/visualizar/<?= $processo->id ?>" class="btn btn-sm btn-info" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                                <a href="<?= URL ?>/ciri/editar/<?= $processo->id ?>" class="btn btn-sm btn-primary" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php if ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista'): ?>
                                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteProcessoModal<?= $processo->id ?>" title="Excluir">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
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
                            <?php if (isset($dados['paginacao']) && $dados['paginacao']['total_paginas'] > 1): ?>
                                <div class="card-footer bg-white">
                                    <nav class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            Mostrando <?= count($dados['processos']) ?> de <?= $dados['paginacao']['total_registros'] ?> registros
                                        </div>
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php
                                                $paginaAtual = $dados['paginacao']['pagina_atual'];
                                                $totalPaginas = $dados['paginacao']['total_paginas'];
                                                
                                                // Calcular intervalo de páginas a serem mostradas
                                                $inicio = max(1, min($paginaAtual - 2, $totalPaginas - 4));
                                                $fim = min($totalPaginas, max(5, $paginaAtual + 2));
                                                
                                                // Ajustar início se estiver próximo do final
                                                if ($fim - $inicio < 4) {
                                                    $inicio = max(1, $fim - 4);
                                                }
                                            ?>

                                            <!-- Botão Anterior -->
                                            <?php if ($paginaAtual > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/ciri/listar/<?= ($paginaAtual - 1) ?>?<?= http_build_query($dados['filtros']) ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Primeira página e reticências -->
                                            <?php if ($inicio > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/ciri/listar/1?<?= http_build_query($dados['filtros']) ?>">1</a>
                                                </li>
                                                <?php if ($inicio > 2): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <!-- Páginas numeradas -->
                                            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                                                <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                                                    <a class="page-link" href="<?= URL ?>/ciri/listar/<?= $i ?>?<?= http_build_query($dados['filtros']) ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <!-- Última página e reticências -->
                                            <?php if ($fim < $totalPaginas): ?>
                                                <?php if ($fim < $totalPaginas - 1): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/ciri/listar/<?= $totalPaginas ?>?<?= http_build_query($dados['filtros']) ?>"><?= $totalPaginas ?></a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Botão Próximo -->
                                            <?php if ($paginaAtual < $totalPaginas): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/ciri/listar/<?= ($paginaAtual + 1) ?>?<?= http_build_query($dados['filtros']) ?>">
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
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal para Delegação de Processos -->
<div class="modal fade" id="delegarModal" tabindex="-1" role="dialog" aria-labelledby="delegarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delegarModalLabel">Delegar Processos Selecionados</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= URL ?>/ciri/delegarProcessos" method="POST" id="formDelegarLote">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="usuario_id">Selecione o Usuário:</label>
                        <select name="usuario_id" id="usuario_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($dados['usuarios'] ?? [] as $usuario): ?>
                                <option value="<?= $usuario->id ?>"><?= $usuario->nome ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Processos Selecionados:</label>
                        <div id="processosSelecionados" class="alert alert-info">
                            Selecione processos na lista para delegação.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Delegar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para manipulação dos checkboxes e delegação em lote -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.processo-checkbox');
        const btnDelegarLote = document.getElementById('btnDelegarLote');
        const selecionarTodos = document.getElementById('selecionarTodos');
        const formDelegarLote = document.getElementById('formDelegarLote');
        const processosSelecionados = document.getElementById('processosSelecionados');
        
        // Função para atualizar o botão de delegação
        function atualizarBotao() {
            const selecionados = document.querySelectorAll('.processo-checkbox:checked');
            btnDelegarLote.disabled = selecionados.length === 0;
            
            // Atualizar a lista de processos selecionados no modal
            if (selecionados.length === 0) {
                processosSelecionados.innerHTML = 'Selecione processos na lista para delegação.';
            } else {
                processosSelecionados.innerHTML = `<p>${selecionados.length} processo(s) selecionado(s) para delegação.</p>`;
            }
        }
        
        // Evento para selecionar/desselecionar todos
        selecionarTodos.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            atualizarBotao();
        });
        
        // Evento para cada checkbox individual
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', atualizarBotao);
        });
        
        // Evento para o formulário de delegação
        formDelegarLote.addEventListener('submit', function(e) {
            const selecionados = document.querySelectorAll('.processo-checkbox:checked');
            
            if (selecionados.length === 0) {
                e.preventDefault();
                alert('Selecione pelo menos um processo para delegação.');
                return false;
            }
            
            // Adicionar os IDs dos processos selecionados como campos ocultos
            selecionados.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'processos[]';
                input.value = checkbox.value;
                formDelegarLote.appendChild(input);
            });
        });
        
        // Inicializar o estado do botão
        atualizarBotao();
    });
</script>

<!-- Modais de confirmação de exclusão -->
<?php if (!empty($dados['processos'])): ?>
    <?php foreach ($dados['processos'] as $processo): ?>
        <div class="modal fade" id="deleteProcessoModal<?= $processo->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteProcessoModalLabel<?= $processo->id ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="deleteProcessoModalLabel<?= $processo->id ?>">
                            <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Atenção!</strong> Você está prestes a excluir o processo:</p>
                        <p><strong>Número:</strong> <?= $processo->numero_processo ?></p>
                        <p><strong>Comarca/Serventia:</strong> <?= $processo->comarca_serventia ?></p>
                        <p><strong>Tipo de Ato:</strong> <?= $processo->tipo_ato_nome ?></p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i> Esta ação excluirá permanentemente o processo e todos os seus registros relacionados e não poderá ser desfeita.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <a href="<?= URL ?>/ciri/excluir/<?= $processo->id ?>" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Excluir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal de Processos Duplicados -->
<div class="modal fade" id="processosDuplicadosModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-copy me-2"></i> Processos Duplicados no Sistema
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Número do Processo</th>
                                <th>Quantidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dados['duplicados'])): ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        Nenhum processo duplicado encontrado
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dados['duplicados'] as $processo): ?>
                                    <tr>
                                        <td><?= $processo->numero_processo ?></td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <?= $processo->quantidade ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= URL ?>/ciri/listar?numero_processo=<?= urlencode($processo->numero_processo) ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-search"></i> Ver Detalhes
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'app/Views/include/footer.php' ?>