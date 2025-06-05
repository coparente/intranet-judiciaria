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
                                    <i class="fas fa-plus me-1"></i> Nova Conversa
                                </a>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="card-body border-bottom">
                            <form method="GET" action="<?= URL ?>/chat/index" class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <label for="filtro_contato" class="form-label">
                                        <i class="fas fa-user me-1"></i> Filtrar por Contato
                                    </label>
                                    <input type="text" class="form-control" id="filtro_contato" name="filtro_contato" 
                                           placeholder="Nome do contato..." 
                                           value="<?= htmlspecialchars($_GET['filtro_contato'] ?? '') ?>"
                                           autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="filtro_numero" class="form-label">
                                        <i class="fas fa-phone me-1"></i> Filtrar por Número
                                    </label>
                                    <input type="text" class="form-control" id="filtro_numero" name="filtro_numero" 
                                           placeholder="Número de telefone..." 
                                           value="<?= htmlspecialchars($_GET['filtro_numero'] ?? '') ?>"
                                           autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-12 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                    <div class="ms-2">
                                        <a href="<?= URL ?>/chat/index" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> Limpar
                                        </a>
                                    </div>
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
                                            <?php if (!empty($_GET['filtro_contato']) || !empty($_GET['filtro_numero'])): ?>
                                                <span class="badge bg-info ms-2" title="Filtros aplicados">
                                                    <i class="fas fa-filter me-1"></i> Filtrado
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!empty($_GET['filtro_contato']) || !empty($_GET['filtro_numero'])): ?>
                                                <span class="text-warning">
                                                    <i class="fas fa-search me-1"></i>
                                                    Nenhuma conversa encontrada com os filtros aplicados
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Nenhuma conversa cadastrada
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($_GET['filtro_contato']) || !empty($_GET['filtro_numero'])): ?>
                                        <div class="text-start text-md-end">
                                            <small class="text-muted d-block mb-1">
                                                Filtros ativos:
                                            </small>
                                            <div>
                                                <?php if (!empty($_GET['filtro_contato'])): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?= htmlspecialchars($_GET['filtro_contato']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($_GET['filtro_numero'])): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-phone me-1"></i>
                                                        <?= htmlspecialchars($_GET['filtro_numero']) ?>
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
                                            <th>Contato</th>
                                            <th>Número</th>
                                            <th>Última Mensagem</th>
                                            <th>Atualizado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['conversas'])) : ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <?php if (!empty($_GET['filtro_contato']) || !empty($_GET['filtro_numero'])): ?>
                                                        <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                                        <p class="text-muted mb-0">Nenhuma conversa encontrada com os filtros aplicados</p>
                                                        <a href="<?= URL ?>/chat/index" class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="fas fa-times me-1"></i> Limpar Filtros
                                                        </a>
                                                    <?php else: ?>
                                                        <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                                        <p class="text-muted mb-0">Nenhuma conversa encontrada</p>
                                                        <a href="<?= URL ?>/chat/novaConversa" class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="fas fa-plus me-1"></i> Criar Nova Conversa
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php else : ?>
                                            <?php foreach ($dados['conversas'] as $conversa) : ?>
                                                <tr>
                                                    <td>
                                                        <?= $conversa->contato_nome ?>
                                                        <?php if (isset($conversa->lido) && $conversa->lido > 0) : ?>
                                                            <span class="badge bg-danger"><?= $conversa->lido ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $conversa->contato_numero ?></td>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_mensagem)) : ?>
                                                            <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                                <?= htmlspecialchars($conversa->ultima_mensagem) ?>
                                                            </span>
                                                        <?php else : ?>
                                                            <span class="text-muted">Sem mensagens</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($conversa->ultima_atualizacao)) : ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->ultima_atualizacao)) ?>
                                                        <?php else : ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->criado_em)) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-comments me-1"></i> Abrir
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalExcluirConversa<?= $conversa->id ?>">
                                                            <i class="fas fa-trash me-1"></i> Excluir
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Modal Excluir Conversa -->
                                                <div class="modal fade" id="modalExcluirConversa<?= $conversa->id ?>" tabindex="-1" aria-labelledby="modalExcluirConversaLabel<?= $conversa->id ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalExcluirConversaLabel<?= $conversa->id ?>">Confirmar Exclusão</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Tem certeza que deseja excluir a conversa com <strong><?= $conversa->contato_nome ?></strong>?</p>
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
            if (!apiStatus) return; // Verifica se o elemento existe

            // Adiciona um timestamp para evitar cache
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
                    // Verifica se o tipo de conteúdo é JSON
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

                        // Adiciona detalhes do erro ao título
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
        const form = filtroContato ? filtroContato.closest('form') : null;
        
        // Adicionar listener para Enter nos campos de filtro
        [filtroContato, filtroNumero].forEach(campo => {
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
            }
        });
        
        // Adicionar atalho Ctrl+F para focar no primeiro campo de filtro
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f' && filtroContato) {
                e.preventDefault();
                filtroContato.focus();
                filtroContato.select();
            }
        });

        // Verificar status da API ao carregar a página
        verificarStatusAPI();

        // Verificar status da API a cada 30 segundos
        setInterval(verificarStatusAPI, 30000);
        
        // Adicionar tooltip informativo se houver filtros ativos
        const filtrosAtivos = document.querySelector('.badge.bg-info');
        if (filtrosAtivos) {
            filtrosAtivos.title = 'Filtros aplicados: ' + 
                (filtroContato.value ? 'Contato: "' + filtroContato.value + '"' : '') +
                (filtroContato.value && filtroNumero.value ? ', ' : '') +
                (filtroNumero.value ? 'Número: "' + filtroNumero.value + '"' : '');
        }
    });
</script>

<?php include 'app/Views/include/footer.php' ?>