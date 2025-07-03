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
                        <a href="<?= URL ?>/chat/index" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Voltar para Chat
                        </a>
                    </div>

                    <!-- Cabeçalho -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Gerenciar Conflitos de Conversas
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="text-muted">
                                        Esta página detecta e resolve conflitos onde um mesmo contato tem conversas ativas com múltiplos agentes.
                                        O sistema manterá apenas a conversa mais recente e fechará as outras automaticamente.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php if (!empty($dados['conflitos'])): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="acao" value="limpar_conflitos">
                                            <button type="submit" class="btn btn-warning" 
                                                    onclick="return confirm('Tem certeza que deseja resolver todos os conflitos automaticamente? Esta ação não pode ser desfeita.')">
                                                <i class="fas fa-magic me-2"></i>
                                                Resolver Todos
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Conflitos -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>
                                Conflitos Detectados
                                <span class="badge badge-warning ms-2"><?= count($dados['conflitos']) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['conflitos'])): ?>
                                <div class="alert alert-success text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h5>Nenhum conflito detectado!</h5>
                                    <p class="mb-0">Todas as conversas estão sendo gerenciadas corretamente.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Contato</th>
                                                <th>Número</th>
                                                <th>Agentes Envolvidos</th>
                                                <th>Total de Conversas</th>
                                                <th>Detalhes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['conflitos'] as $conflito): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($conflito->contato_nome) ?></strong>
                                                    </td>
                                                    <td>
                                                        <code><?= htmlspecialchars($conflito->contato_numero) ?></code>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-danger">
                                                            <?= $conflito->agentes_distintos ?> agentes
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-warning">
                                                            <?= $conflito->total_conversas ?> conversas
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($conflito->detalhes_agentes) ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Informações Adicionais -->
                                <div class="alert alert-info mt-3">
                                    <h6><i class="fas fa-info-circle me-2"></i> Como funciona a resolução:</h6>
                                    <ul class="mb-0">
                                        <li>Para cada contato com conflito, apenas a conversa mais recente será mantida</li>
                                        <li>As outras conversas serão fechadas (desatribuídas) automaticamente</li>
                                        <li>Uma observação será adicionada nas conversas fechadas</li>
                                        <li>Os agentes afetados poderão ver suas conversas na lista de "não atribuídas"</li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Ações Rápidas -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tools me-2"></i>
                                Ações Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= URL ?>/chat/relatorioConversasAtivas" class="btn btn-info btn-block">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Relatório de Conversas Ativas
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="<?= URL ?>/chat/conversasNaoAtribuidas" class="btn btn-primary btn-block">
                                        <i class="fas fa-inbox me-2"></i>
                                        Conversas Não Atribuídas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?> 