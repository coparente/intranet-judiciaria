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
                    <?= Helper::mensagem('chat') ?>
                    <?= Helper::mensagemSweetAlert('chat') ?>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-comments me-2"></i> Chat
                                <span id="apiStatus" class="badge bg-secondary ms-2" title="Verificando status da API...">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </span>
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/novaConversa" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus"></i> Nova Conversa
                                </a>
                                <a href="<?= URL ?>/chat/dashboardTickets" class="btn btn-light btn-sm">
                                    <i class="fas fa-ticket-alt"></i> Dashboard Tickets
                                </a>
                                <a href="<?= URL ?>/chat/relatorioConversasAtivas" class="btn btn-light btn-sm">
                                    <i class="fas fa-file-alt"></i> Relatório Conversas Ativas
                                </a>
                            </div>
                        </div>
                        <br>
                        <!-- Abas de Navegação -->
                        <div class="card-body p-0">
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <a class="nav-link <?= $dados['aba_atual'] == 'minhas' ? 'active' : '' ?>" 
                                   href="<?= URL ?>/chat/index?aba=minhas">
                                    <i class="fas fa-user me-2"></i> Minhas Conversas
                                </a>
                                <?php if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])): ?>
                                    <a class="nav-link <?= $dados['aba_atual'] == 'nao_atribuidas' ? 'active' : '' ?>" 
                                       href="<?= URL ?>/chat/index?aba=nao_atribuidas">
                                        <i class="fas fa-user-slash me-2"></i> Não Atribuídas
                                    </a>
                                    <a class="nav-link <?= $dados['aba_atual'] == 'todas' ? 'active' : '' ?>" 
                                       href="<?= URL ?>/chat/index?aba=todas">
                                        <i class="fas fa-users me-2"></i> Todas as Conversas
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="card-body border-bottom">
                            <form method="GET" action="<?= URL ?>/chat/index" class="row g-3">
                                <input type="hidden" name="aba" value="<?= $dados['aba_atual'] ?>">
                                
                                <div class="col-lg-3 col-md-6">
                                    <label for="filtro_contato" class="form-label">
                                        <i class="fas fa-user me-1"></i> Filtrar por Contato
                                    </label>
                                    <input type="text" class="form-control" id="filtro_contato" name="filtro_contato" 
                                           placeholder="Nome do contato..." 
                                           value="<?= htmlspecialchars($dados['filtro_contato']) ?>"
                                           autocomplete="off">
                                </div>
                                
                                <div class="col-lg-3 col-md-6">
                                    <label for="filtro_numero" class="form-label">
                                        <i class="fas fa-phone me-1"></i> Filtrar por Número
                                    </label>
                                    <input type="text" class="form-control" id="filtro_numero" name="filtro_numero" 
                                           placeholder="Número de telefone..." 
                                           value="<?= htmlspecialchars($dados['filtro_numero']) ?>"
                                           autocomplete="off">
                                </div>
                                
                                <div class="col-lg-3 col-md-6">
                                    <label for="filtro_status" class="form-label">
                                        <i class="fas fa-ticket-alt me-1"></i> Status do Ticket
                                    </label>
                                    <select class="form-control" id="filtro_status" name="filtro_status">
                                        <option value="">Todos os Status</option>
                                        <option value="aberto" <?= $dados['filtro_status'] == 'aberto' ? 'selected' : '' ?>>
                                            Aberto
                                        </option>
                                        <option value="em_andamento" <?= $dados['filtro_status'] == 'em_andamento' ? 'selected' : '' ?>>
                                            Em Andamento
                                        </option>
                                        <option value="aguardando_cliente" <?= $dados['filtro_status'] == 'aguardando_cliente' ? 'selected' : '' ?>>
                                            Aguardando Cliente
                                        </option>
                                        <option value="resolvido" <?= $dados['filtro_status'] == 'resolvido' ? 'selected' : '' ?>>
                                            Resolvido
                                        </option>
                                        <option value="fechado" <?= $dados['filtro_status'] == 'fechado' ? 'selected' : '' ?>>
                                            Fechado
                                        </option>
                                    </select>
                                </div>

                                <?php if ($dados['aba_atual'] == 'todas'): ?>
                                <div class="col-lg-3 col-md-6">
                                    <label for="filtro_nome" class="form-label">
                                        <i class="fas fa-user me-1"></i> Filtrar por Nome
                                    </label>
                                    <select class="form-control select2" id="filtro_nome" name="filtro_nome" autocomplete="off">
                                        <option value="">Todos os Nomes</option>
                                        <?php foreach ($dados['usuarios'] as $usuario): ?>
                                            <option value="<?= htmlspecialchars($usuario->id) ?>" <?= $dados['filtro_nome'] == $usuario->id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($usuario->nome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>   
                                </div>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                    <a href="<?= URL ?>/chat/index?aba=<?= $dados['aba_atual'] ?>" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Limpar
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <!-- Informações de paginação -->
                            <?php if (isset($dados['total_registros'])): ?>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                    <div class="text-muted mb-2 mb-md-0">
                                        <?php if ($dados['total_registros'] > 0): ?>
                                            Mostrando <?= $dados['registro_inicio'] ?> a <?= $dados['registro_fim'] ?> 
                                            de <?= $dados['total_registros'] ?> conversa<?= $dados['total_registros'] != 1 ? 's' : '' ?>
                                            
                                            <?php 
                                            $filtrosAtivos = [];
                                            if (!empty($dados['filtro_contato'])) $filtrosAtivos[] = 'contato';
                                            if (!empty($dados['filtro_numero'])) $filtrosAtivos[] = 'número';
                                            if (!empty($dados['filtro_status'])) $filtrosAtivos[] = 'status';
                                            if (!empty($dados['filtro_nome']) && $dados['aba_atual'] == 'todas') $filtrosAtivos[] = 'nome';
                                            ?>
                                            
                                            <?php if (!empty($filtrosAtivos)): ?>
                                                <span class="badge bg-info ms-2" title="Filtros aplicados">
                                                    <i class="fas fa-filter me-1"></i> Filtrado
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php
                                            $abaNomes = [
                                                'minhas' => 'Minhas Conversas',
                                                'nao_atribuidas' => 'Não Atribuídas',
                                                'todas' => 'Todas as Conversas'
                                            ];
                                            ?>
                                            <span class="badge bg-info ms-2 text-white">
                                                <?= $abaNomes[$dados['aba_atual']] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-warning">
                                                <i class="fas fa-search me-1"></i>
                                                Nenhuma conversa encontrada
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($filtrosAtivos)): ?>
                                        <div class="text-start text-md-end">
                                            <small class="text-muted d-block mb-1">
                                                Filtros ativos:
                                            </small>
                                            <div>
                                                <?php if (!empty($dados['filtro_contato'])): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?= htmlspecialchars($dados['filtro_contato']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($dados['filtro_numero'])): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-phone me-1"></i>
                                                        <?= htmlspecialchars($dados['filtro_numero']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($dados['filtro_status'])): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-ticket-alt me-1"></i>
                                                        <?= ucfirst(str_replace('_', ' ', $dados['filtro_status'])) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($dados['filtro_nome']) && $dados['aba_atual'] == 'todas'): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?php 
                                                        $nomeUsuario = '';
                                                        foreach ($dados['usuarios'] as $usuario) {
                                                            if ($usuario->id == $dados['filtro_nome']) {
                                                                $nomeUsuario = $usuario->nome;
                                                                break;
                                                            }
                                                        }
                                                        echo htmlspecialchars($nomeUsuario);
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status do Ticket</th>
                                            <th>Contato</th>
                                            <th>Número</th>
                                            <?php if ($dados['aba_atual'] != 'minhas'): ?>
                                                <th>Responsável</th>
                                            <?php endif; ?>
                                            <th>Última Mensagem</th>
                                            <th>Atualizado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['conversas'])): ?>
                                            <tr>
                                                <td colspan="<?= $dados['aba_atual'] != 'minhas' ? '7' : '6' ?>" class="text-center py-4">
                                                    <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">Nenhuma conversa encontrada</p>
                                                    <a href="<?= URL ?>/chat/novaConversa" class="btn btn-sm btn-outline-primary mt-2">
                                                        <i class="fas fa-plus me-1"></i> Criar Nova Conversa
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dados['conversas'] as $conversa): ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                        $statusClass = [
                                                            'aberto' => 'badge-danger',
                                                            'em_andamento' => 'badge-warning',
                                                            'aguardando_cliente' => 'badge-info',
                                                            'resolvido' => 'badge-success',
                                                            'fechado' => 'badge-secondary'
                                                        ];
                                                        $statusNomes = [
                                                            'aberto' => 'Aberto',
                                                            'em_andamento' => 'Em Andamento',
                                                            'aguardando_cliente' => 'Aguardando Cliente',
                                                            'resolvido' => 'Resolvido',
                                                            'fechado' => 'Fechado'
                                                        ];
                                                        $status = $conversa->status_atendimento ?? 'aberto';
                                                        ?>
                                                        <span class="badge <?= $statusClass[$status] ?? 'badge-secondary' ?>">
                                                            <?= $statusNomes[$status] ?? 'Desconhecido' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($conversa->contato_nome) ?>
                                                        <?php //if (isset($conversa->nao_lidas) && $conversa->nao_lidas > 0): ?>
                                                            <!-- <span class="badge bg-warning ms-1"><?= $conversa->nao_lidas ?></span> -->
                                                        <?php //endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($conversa->contato_numero) ?></td>
                                                    <?php if ($dados['aba_atual'] != 'minhas'): ?>
                                                        <td>
                                                            <?php if (isset($conversa->responsavel_nome)): ?>
                                                                <i class="fas fa-user me-1"></i>
                                                                <?= htmlspecialchars($conversa->responsavel_nome) ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">
                                                                    <i class="fas fa-user-slash me-1"></i>
                                                                    Não atribuído
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_mensagem)): ?>
                                                            <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                                <?= htmlspecialchars($conversa->ultima_mensagem) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Sem mensagens</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_atividade)): ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->ultima_atividade)) ?>
                                                        <?php else: ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->criado_em)) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" class="btn btn-info btn-sm"
                                                            title="Abrir Conversa">
                                                            <i class="fas fa-comments me-1"></i>
                                                        </a>
                                                        
                                                        <?php if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])): ?>
                                                        <?php if ($dados['aba_atual'] == 'nao_atribuidas' || $dados['aba_atual'] == 'todas' && !empty($dados['usuarios'])): ?>
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    data-toggle="modal" 
                                                                    data-target="#modalAtribuirConversa<?= $conversa->id ?>"
                                                                    title="Atribuir Conversa">
                                                                <i class="fas fa-user-plus me-1"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                data-toggle="modal" 
                                                                data-target="#modalExcluirConversa<?= $conversa->id ?>" 
                                                                title="Excluir Conversa">
                                                                <i class="fas fa-trash me-1"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>

                                                <!-- Modal Atribuir Conversa -->
                                                <?php if ($dados['aba_atual'] == 'nao_atribuidas' || $dados['aba_atual'] == 'todas' && !empty($dados['usuarios'])): ?>
                                                    <div class="modal fade" id="modalAtribuirConversa<?= $conversa->id ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="<?= URL ?>/chat/atribuirConversa" method="POST">
                                                                    <input type="hidden" name="conversa_id" value="<?= $conversa->id ?>">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Atribuir Conversa</h5>
                                                                        <button type="button" class="close" data-dismiss="modal">
                                                                            <span>&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>Atribuir conversa com <strong><?= htmlspecialchars($conversa->contato_nome) ?></strong> para:</p>
                                                                        <select class="form-control" name="usuario_id" required>
                                                                            <option value="">Selecione um usuário</option>
                                                                            <?php foreach ($dados['usuarios'] as $usuario): ?>
                                                                                <option value="<?= $usuario->id ?>"><?= htmlspecialchars($usuario->nome) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-success">Atribuir</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Modal Excluir Conversa -->
                                                <div class="modal fade" id="modalExcluirConversa<?= $conversa->id ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Excluir Conversa</h5>
                                                                <button type="button" class="close" data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Tem certeza que deseja excluir a conversa com <strong><?= htmlspecialchars($conversa->contato_nome) ?></strong>?</p>
                                                                <p class="text-danger"><small>Esta ação não pode ser desfeita e todas as mensagens serão excluídas.</small></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                <a href="<?= URL ?>/chat/excluirConversa/<?= $conversa->id ?>" class="btn btn-danger">Excluir</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginação -->
                            <?php if (isset($dados['total_paginas']) && $dados['total_paginas'] > 1): ?>
                                <nav aria-label="Navegação de páginas" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <!-- Primeira página -->
                                        <?php if ($dados['pagina_atual'] > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= URL ?>/chat/index?pagina=1<?= $dados['query_string'] ?>">
                                                    <i class="fas fa-angle-double-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Página anterior -->
                                        <?php if ($dados['pagina_atual'] > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= URL ?>/chat/index?pagina=<?= $dados['pagina_atual'] - 1 ?><?= $dados['query_string'] ?>">
                                                    <i class="fas fa-angle-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Páginas numeradas -->
                                        <?php
                                        $inicio = max(1, $dados['pagina_atual'] - 2);
                                        $fim = min($dados['total_paginas'], $dados['pagina_atual'] + 2);
                                        
                                        for ($i = $inicio; $i <= $fim; $i++): ?>
                                            <li class="page-item <?= $i == $dados['pagina_atual'] ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= URL ?>/chat/index?pagina=<?= $i ?><?= $dados['query_string'] ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Próxima página -->
                                        <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= URL ?>/chat/index?pagina=<?= $dados['pagina_atual'] + 1 ?><?= $dados['query_string'] ?>">
                                                    <i class="fas fa-angle-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Última página -->
                                        <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= URL ?>/chat/index?pagina=<?= $dados['total_paginas'] ?><?= $dados['query_string'] ?>">
                                                    <i class="fas fa-angle-double-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                    
                                    <!-- Informação adicional -->
                                    <div class="text-center text-muted mt-2">
                                        Página <?= $dados['pagina_atual'] ?> de <?= $dados['total_paginas'] ?>
                                    </div>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar status da API
        function verificarStatusAPI() {
            const apiStatus = document.getElementById('apiStatus');
            if (!apiStatus) return;

            const timestamp = new Date().getTime();

            fetch(`<?= URL ?>/chat/verificarStatusAPI?_=${timestamp}`, {
                    method: 'POST',
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache',
                        'Expires': '0',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na resposta da rede: ${response.status}`);
                    }
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new TypeError("Resposta não é JSON válido!");
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.online) {
                        apiStatus.className = 'badge bg-success ms-2';
                        apiStatus.innerHTML = '<i class="fas fa-check-circle"></i> API Online';
                        apiStatus.title = 'API está online e funcionando';
                    } else {
                        apiStatus.className = 'badge bg-danger ms-2';
                        apiStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> API Offline';
                        const errorMsg = data.error ? data.error : 'API está offline. Mensagens não serão enviadas.';
                        apiStatus.title = errorMsg;
                        console.error('Erro na API:', errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar status da API:', error);
                    apiStatus.className = 'badge bg-warning ms-2';
                    apiStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro';
                    apiStatus.title = 'Erro ao verificar status da API: ' + error.message;
                });
        }

        // Melhorias para os filtros
        const filtroContato = document.getElementById('filtro_contato');
        const filtroNumero = document.getElementById('filtro_numero');
        const filtroStatus = document.getElementById('filtro_status');
        const filtroNome = document.getElementById('filtro_nome');
        const form = filtroContato ? filtroContato.closest('form') : null;
        
        // Adicionar listener para Enter nos campos de filtro
        [filtroContato, filtroNumero, filtroStatus, filtroNome].forEach(campo => {
            if (campo) {
                campo.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (form) form.submit();
                    }
                });
                
                // Destacar campo quando tem valor
                if (campo.value.trim()) {
                    campo.classList.add('border-primary');
                }
                
                // Adicionar evento para destacar campo ativo
                campo.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.add('border-primary');
                    } else {
                        this.classList.remove('border-primary');
                    }
                });
                
                // Para select, usar evento change
                if (campo.tagName === 'SELECT') {
                    campo.addEventListener('change', function() {
                        if (this.value.trim()) {
                            this.classList.add('border-primary');
                        } else {
                            this.classList.remove('border-primary');
                        }
                    });
                }
            }
        });

        // Verificar status da API ao carregar a página
        verificarStatusAPI();

        // Verificar status da API a cada 30 segundos
        setInterval(verificarStatusAPI, 30000);
    });
</script>

<?php include 'app/Views/include/footer.php' ?>