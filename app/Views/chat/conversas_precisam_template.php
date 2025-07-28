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
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
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