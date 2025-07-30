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
                                <i class="fas fa-exclamation-triangle me-2"></i> 
                                Conversas que Precisam de Novo Template
                                <span class="badge bg-warning ms-2"><?= $dados['total'] ?></span>
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/index" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </a>
                                <button type="button" class="btn btn-info btn-sm" onclick="verificarTemplatesVencidos()">
                                    <i class="fas fa-sync-alt"></i> Atualizar
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Filtros -->
                            <form method="GET" action="<?= URL ?>/chat/conversasPrecisamNovoTemplate" class="row g-3 mb-4">
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
                                    <label for="filtro_responsavel" class="form-label">
                                        <i class="fas fa-user-tie me-1"></i> Filtrar por Responsável
                                    </label>
                                    <select class="form-control" id="filtro_responsavel" name="filtro_responsavel">
                                        <option value="">Todos os Responsáveis</option>
                                        <?php foreach ($dados['usuarios'] as $usuario): ?>
                                            <option value="<?= htmlspecialchars($usuario->id) ?>" <?= $dados['filtro_responsavel'] == $usuario->id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($usuario->nome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-lg-3 col-md-6 d-flex align-items-end">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Filtrar
                                        </button>
                                        <a href="<?= URL ?>/chat/conversasPrecisamNovoTemplate" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> Limpar
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Informações de filtros ativos -->
                            <?php 
                            $filtrosAtivos = [];
                            if (!empty($dados['filtro_contato'])) $filtrosAtivos[] = 'contato';
                            if (!empty($dados['filtro_numero'])) $filtrosAtivos[] = 'número';
                            if (!empty($dados['filtro_responsavel'])) $filtrosAtivos[] = 'responsável';
                            ?>
                            
                            <?php if (!empty($filtrosAtivos)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-filter me-2"></i>
                                    <strong>Filtros ativos:</strong>
                                    <?php foreach ($filtrosAtivos as $index => $filtro): ?>
                                        <?= ucfirst($filtro) ?><?= $index < count($filtrosAtivos) - 1 ? ', ' : '' ?>
                                    <?php endforeach; ?>
                                    
                                    <div class="mt-2">
                                        <?php if (!empty($dados['filtro_contato'])): ?>
                                            <span class="badge bg-light text-dark me-1">
                                                <i class="fas fa-user me-1"></i>
                                                <?= htmlspecialchars($dados['filtro_contato']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($dados['filtro_numero'])): ?>
                                            <span class="badge bg-light text-dark me-1">
                                                <i class="fas fa-phone me-1"></i>
                                                <?= htmlspecialchars($dados['filtro_numero']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($dados['filtro_responsavel'])): ?>
                                            <span class="badge bg-light text-dark me-1">
                                                <i class="fas fa-user-tie me-1"></i>
                                                <?php 
                                                $nomeResponsavel = '';
                                                foreach ($dados['usuarios'] as $usuario) {
                                                    if ($usuario->id == $dados['filtro_responsavel']) {
                                                        $nomeResponsavel = $usuario->nome;
                                                        break;
                                                    }
                                                }
                                                echo htmlspecialchars($nomeResponsavel);
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Informações de paginação -->
                            <?php if (isset($dados['total_registros']) && $dados['total_registros'] > 0): ?>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                    <div class="text-muted mb-2 mb-md-0">
                                        Mostrando <?= $dados['registro_inicio'] ?> a <?= $dados['registro_fim'] ?> 
                                        de <?= $dados['total_registros'] ?> conversa<?= $dados['total_registros'] != 1 ? 's' : '' ?>
                                        
                                        <?php 
                                        $filtrosAtivos = [];
                                        if (!empty($dados['filtro_contato'])) $filtrosAtivos[] = 'contato';
                                        if (!empty($dados['filtro_numero'])) $filtrosAtivos[] = 'número';
                                        if (!empty($dados['filtro_responsavel'])) $filtrosAtivos[] = 'responsável';
                                        ?>
                                        
                                        <?php if (!empty($filtrosAtivos)): ?>
                                            <span class="badge bg-info ms-2" title="Filtros aplicados">
                                                <i class="fas fa-filter me-1"></i> Filtrado
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
                                                <?php if (!empty($dados['filtro_responsavel'])): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        <i class="fas fa-user-tie me-1"></i>
                                                        <?php 
                                                        $nomeResponsavel = '';
                                                        foreach ($dados['usuarios'] as $usuario) {
                                                            if ($usuario->id == $dados['filtro_responsavel']) {
                                                                $nomeResponsavel = $usuario->nome;
                                                                break;
                                                            }
                                                        }
                                                        echo htmlspecialchars($nomeResponsavel);
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($dados['conversas'])): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h5 class="text-success">Nenhuma conversa precisa de novo template!</h5>
                                    <p class="text-muted">Todas as conversas estão com templates válidos ou já receberam resposta do cliente.</p>
                                    <a href="<?= URL ?>/chat/index" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i> Voltar ao Chat
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Atenção:</strong> Estas conversas tiveram template enviado há mais de 24 horas sem resposta do cliente. 
                                    É necessário enviar um novo template para manter a conversa ativa.
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Contato</th>
                                                <th>Número</th>
                                                <th>Responsável</th>
                                                <th>Template Enviado</th>
                                                <th>Última Resposta</th>
                                                <th>Tempo Sem Resposta</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['conversas'] as $conversa): ?>
                                                <tr>
                                                    <td>
                                                        <?= htmlspecialchars($conversa->contato_nome) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($conversa->contato_numero) ?></td>
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
                                                    <td>
                                                        <?php if ($conversa->template_enviado_em): ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->template_enviado_em)) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Não registrado</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($conversa->ultima_resposta_cliente): ?>
                                                            <?= date('d/m/Y H:i', strtotime($conversa->ultima_resposta_cliente)) ?>
                                                        <?php else: ?>
                                                            <span class="text-danger">
                                                                <i class="fas fa-times me-1"></i>
                                                                Sem resposta
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($conversa->horas_sem_resposta)): ?>
                                                            <span class="badge bg-warning">
                                                                <?= $conversa->horas_sem_resposta ?> horas
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" class="btn btn-info btn-sm"
                                                            title="Abrir Conversa">
                                                            <i class="fas fa-comments me-1"></i>
                                                        </a>
                                                        
                                                        <!-- <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="marcarTemplateReenviado(<?= $conversa->id ?>)"
                                                                title="Marcar como template reenviado">
                                                            <i class="fas fa-check me-1"></i>
                                                        </button> -->
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
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
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasPrecisamNovoTemplate?pagina=1<?= $dados['query_string'] ?>">
                                                        <i class="fas fa-angle-double-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Página anterior -->
                                            <?php if ($dados['pagina_atual'] > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasPrecisamNovoTemplate?pagina=<?= $dados['pagina_atual'] - 1 ?><?= $dados['query_string'] ?>">
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
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasPrecisamNovoTemplate?pagina=<?= $i ?><?= $dados['query_string'] ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <!-- Próxima página -->
                                            <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasPrecisamNovoTemplate?pagina=<?= $dados['pagina_atual'] + 1 ?><?= $dados['query_string'] ?>">
                                                        <i class="fas fa-angle-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Última página -->
                                            <?php if ($dados['pagina_atual'] < $dados['total_paginas']): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= URL ?>/chat/conversasPrecisamNovoTemplate?pagina=<?= $dados['total_paginas'] ?><?= $dados['query_string'] ?>">
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
        // Melhorias para os filtros
        const filtroContato = document.getElementById('filtro_contato');
        const filtroNumero = document.getElementById('filtro_numero');
        const filtroResponsavel = document.getElementById('filtro_responsavel');
        const form = filtroContato ? filtroContato.closest('form') : null;
        
        // Adicionar listener para Enter nos campos de filtro
        [filtroContato, filtroNumero, filtroResponsavel].forEach(campo => {
            if (campo) {
                campo.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (form) {
                            // Resetar para primeira página ao filtrar
                            const paginaInput = document.createElement('input');
                            paginaInput.type = 'hidden';
                            paginaInput.name = 'pagina';
                            paginaInput.value = '1';
                            form.appendChild(paginaInput);
                            form.submit();
                        }
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

        // Adicionar evento para auto-submit no select de responsável
        if (filtroResponsavel) {
            filtroResponsavel.addEventListener('change', function() {
                if (form) {
                    // Resetar para primeira página ao filtrar
                    const paginaInput = document.createElement('input');
                    paginaInput.type = 'hidden';
                    paginaInput.name = 'pagina';
                    paginaInput.value = '1';
                    form.appendChild(paginaInput);
                    form.submit();
                }
            });
        }
    });

    function verificarTemplatesVencidos() {
        fetch('<?= URL ?>/chat/verificarTemplatesVencidos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao verificar templates vencidos: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao verificar templates vencidos');
        });
    }

    function marcarTemplateReenviado(conversaId) {
        if (confirm('Tem certeza que deseja marcar o template como reenviado para esta conversa?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= URL ?>/chat/marcarTemplateReenviado';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'conversa_id';
            input.value = conversaId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include 'app/Views/include/footer.php' ?> 