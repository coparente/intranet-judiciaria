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
                                <i class="fas fa-chart-bar text-info me-2"></i>
                                Relatório de Conversas Ativas por Agente
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="text-muted">
                                        Este relatório mostra a distribuição de conversas ativas entre os agentes do sistema.
                                        Utilize esta informação para balancear a carga de trabalho e identificar possíveis conflitos.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="<?= URL ?>/chat/gerenciarConflitos" class="btn btn-warning btn-sm">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Gerenciar Conflitos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Relatório -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>
                                Agentes e Suas Conversas Ativas
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['relatorio'])): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <h5>Nenhum agente encontrado</h5>
                                    <p class="mb-0">Não há agentes cadastrados no sistema.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Agente</th>
                                                <th>E-mail</th>
                                                <th>Conversas Ativas</th>
                                                <th>Contatos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalConversas = 0;
                                            foreach ($dados['relatorio'] as $agente): 
                                                $totalConversas += $agente->total_conversas;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($agente->agente_nome) ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?= $agente->usuario_id ?></small>
                                                    </td>
                                                    <td>
                                                        <code><?= htmlspecialchars($agente->agente_email) ?></code>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $agente->total_conversas > 0 ? 'primary' : 'secondary' ?>">
                                                            <?= $agente->total_conversas ?> conversas
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($agente->total_conversas > 0): ?>
                                                            <div class="collapse" id="contatos<?= $agente->usuario_id ?>">
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars($agente->contatos) ?>
                                                                </small>
                                                            </div>
                                                            <button class="btn btn-sm btn-outline-secondary" 
                                                                    type="button" 
                                                                    data-toggle="collapse" 
                                                                    data-target="#contatos<?= $agente->usuario_id ?>" 
                                                                    aria-expanded="false">
                                                                <i class="fas fa-eye me-1"></i> Ver Contatos
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">Sem conversas ativas</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <?php if (!empty($dados['relatorio'])): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Estatísticas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h3><?= count($dados['relatorio']) ?></h3>
                                                <p class="mb-0">Total de Agentes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h3><?= $totalConversas ?></h3>
                                                <p class="mb-0">Conversas Ativas</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <?php 
                                                $agentesAtivos = count(array_filter($dados['relatorio'], function($a) { return $a->total_conversas > 0; }));
                                                ?>
                                                <h3><?= $agentesAtivos ?></h3>
                                                <p class="mb-0">Agentes Ativos</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <?php 
                                                $mediaConversas = $totalConversas > 0 ? round($totalConversas / count($dados['relatorio']), 1) : 0;
                                                ?>
                                                <h3><?= $mediaConversas ?></h3>
                                                <p class="mb-0">Média por Agente</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

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
                                    <a href="<?= URL ?>/chat/gerenciarConflitos" class="btn btn-warning btn-block">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Gerenciar Conflitos
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