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

                    <!-- Botão Voltar -->
                    <div class="mb-3">
                        <a href="<?= URL ?>/chat/conversa/<?= $dados['conversa']->id ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Voltar para Conversa
                        </a>
                    </div>

                    <!-- Cabeçalho -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h1 class="h4 mb-0">
                                <i class="fas fa-history me-2"></i>Histórico do Ticket
                            </h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Informações do Contato</h5>
                                    <p><strong>Nome:</strong> <?= htmlspecialchars($dados['conversa']->contato_nome) ?></p>
                                    <p><strong>Número:</strong> <?= htmlspecialchars($dados['conversa']->contato_numero) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Status Atual do Ticket</h5>
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
                                    $status = $dados['conversa']->status_atendimento ?? 'aberto';
                                    ?>
                                    <span class="badge <?= $statusClass[$status] ?? 'badge-secondary' ?> p-2">
                                        <i class="fas fa-ticket-alt me-1"></i>
                                        <?= $statusNomes[$status] ?? 'Desconhecido' ?>
                                    </span>
                                    
                                    <?php if (isset($dados['conversa']->ticket_aberto_em)): ?>
                                        <p class="mt-2 mb-0">
                                            <strong>Aberto em:</strong> <?= date('d/m/Y H:i:s', strtotime($dados['conversa']->ticket_aberto_em)) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($dados['conversa']->ticket_fechado_em) && $dados['conversa']->ticket_fechado_em): ?>
                                        <p class="mb-0">
                                            <strong>Fechado em:</strong> <?= date('d/m/Y H:i:s', strtotime($dados['conversa']->ticket_fechado_em)) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Histórico de Alterações -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-timeline me-2"></i>Histórico de Alterações
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['historico'])): ?>
                                <div class="timeline">
                                    <?php foreach ($dados['historico'] as $index => $item): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                <?php
                                                $icon = 'fas fa-circle';
                                                $color = 'text-muted';
                                                
                                                switch ($item->status_novo) {
                                                    case 'aberto':
                                                        $icon = 'fas fa-play';
                                                        $color = 'text-danger';
                                                        break;
                                                    case 'em_andamento':
                                                        $icon = 'fas fa-cogs';
                                                        $color = 'text-warning';
                                                        break;
                                                    case 'aguardando_cliente':
                                                        $icon = 'fas fa-clock';
                                                        $color = 'text-info';
                                                        break;
                                                    case 'resolvido':
                                                        $icon = 'fas fa-check';
                                                        $color = 'text-success';
                                                        break;
                                                    case 'fechado':
                                                        $icon = 'fas fa-times';
                                                        $color = 'text-secondary';
                                                        break;
                                                }
                                                ?>
                                                <i class="<?= $icon ?> <?= $color ?>"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <h6 class="mb-1">
                                                        Status alterado para: 
                                                        <span class="badge <?= $statusClass[$item->status_novo] ?? 'badge-secondary' ?>">
                                                            <?= $statusNomes[$item->status_novo] ?? 'Desconhecido' ?>
                                                        </span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('d/m/Y H:i:s', strtotime($item->data_alteracao)) ?>
                                                        <?php if ($item->usuario_nome): ?>
                                                            • <i class="fas fa-user me-1"></i><?= htmlspecialchars($item->usuario_nome) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <?php if ($item->status_anterior): ?>
                                                    <div class="timeline-body">
                                                        <small class="text-muted">
                                                            Status anterior: <span class="badge badge-light"><?= $statusNomes[$item->status_anterior] ?? 'Desconhecido' ?></span>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($item->observacao): ?>
                                                    <div class="timeline-body mt-2">
                                                        <div class="alert alert-light mb-0">
                                                            <i class="fas fa-comment me-1"></i>
                                                            <?= htmlspecialchars($item->observacao) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum histórico encontrado</h5>
                                    <p class="text-muted">Este ticket ainda não possui histórico de alterações.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Observações do Ticket -->
                    <?php if (isset($dados['conversa']->observacoes) && !empty($dados['conversa']->observacoes)): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>Observações
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($dados['conversa']->observacoes) ?></pre>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 30px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 70px;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 20px;
    top: 0;
    width: 20px;
    height: 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    z-index: 1;
}

.timeline-content {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.timeline-header h6 {
    margin-bottom: 5px;
    color: #495057;
}

.timeline-body {
    margin-top: 10px;
}

.badge {
    font-size: 0.8rem;
    padding: 0.4em 0.8em;
}

.badge-light {
    background-color: #f8f9fa;
    color: #495057;
}

.alert-light {
    background-color: #fefefe;
    border-color: #e9ecef;
    color: #495057;
}

pre {
    background: transparent;
    border: none;
    padding: 0;
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}
</style>

<?php include 'app/Views/include/footer.php' ?> 