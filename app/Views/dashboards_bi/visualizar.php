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
                    <?= Helper::mensagem('dashboards_bi') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas <?= $dados['dashboard']->icone ?> me-2"></i> <?= $dados['dashboard']->titulo ?>
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/dashboardsbi/index" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                <a href="<?= URL ?>/dashboardsbi/editar/<?= $dados['dashboard']->id ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-2"></i> Editar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card principal no padrão do sistema -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas <?= $dados['dashboard']->icone ?> me-2"></i>
                                    <?= $dados['dashboard']->titulo ?>
                                </h5>
                                <a href="<?= $dados['dashboard']->url ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-expand me-2"></i> Abrir em tela cheia
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($dados['dashboard']->descricao)): ?>
                            <div class="card-body bg-light border-bottom">
                                <p class="card-text">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <?= $dados['dashboard']->descricao ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="card-body p-0">
                            <div id="iframe-container" style="height: 700px; width: 100%; position: relative;">
                                <!-- Iframe para o dashboard -->
                                <iframe
                                    id="dashboard-iframe"
                                    src="<?= $dados['dashboard']->url ?>"
                                    style="width: 100%; height: 100%; border: none;"
                                    frameborder="0"
                                    allowfullscreen></iframe>

                                <!-- Indicador de carregamento -->
                                <div id="loading-indicator" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255,255,255,0.9); padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mb-0 fw-medium">Carregando dashboard...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-info me-2">
                                        <i class="fas fa-tag me-1"></i> <?= $dados['dashboard']->categoria ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i> Criado em: <?= date('d/m/Y H:i', strtotime($dados['dashboard']->criado_em)) ?>
                                    </small>
                                </div>
                                <div>
                                    <button id="refresh-btn" class="btn btn-sm btn-secondary" title="Recarregar dashboard">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card de informações adicionais -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i> Informações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Dica:</strong> Para melhor visualização, recomendamos abrir o dashboard em tela cheia clicando no botão "Abrir em tela cheia".
                                Se o dashboard não carregar corretamente, tente recarregá-lo clicando no botão <i class="fas fa-sync-alt"></i> no canto inferior direito.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Script para gerenciar o carregamento do iframe -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const iframe = document.getElementById('dashboard-iframe');
        const loadingIndicator = document.getElementById('loading-indicator');
        const refreshBtn = document.getElementById('refresh-btn');

        // Ajustar altura do iframe
        function ajustarAlturaIframe() {
            const alturaJanela = window.innerHeight;
            const container = document.getElementById('iframe-container');
            container.style.height = Math.max(700, alturaJanela * 0.7) + 'px';
        }

        // Ajustar altura inicial e ao redimensionar
        ajustarAlturaIframe();
        window.addEventListener('resize', ajustarAlturaIframe);

        // Esconder o indicador de carregamento quando o iframe carregar
        iframe.addEventListener('load', function() {
            loadingIndicator.style.display = 'none';
        });

        // Botão de recarregar
        refreshBtn.addEventListener('click', function() {
            loadingIndicator.style.display = 'block';
            iframe.src = iframe.src;
        });

        // Se o iframe não carregar em 10 segundos, mostrar mensagem
        setTimeout(function() {
            if (loadingIndicator.style.display !== 'none') {
                loadingIndicator.innerHTML = `
                    <div class="text-center">
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            O dashboard está demorando para carregar.
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="document.getElementById('dashboard-iframe').src = document.getElementById('dashboard-iframe').src">
                                <i class="fas fa-sync-alt me-2"></i> Tentar novamente
                            </button>
                            <a href="${iframe.src}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i> Abrir em nova aba
                            </a>
                        </div>
                    </div>
                `;
            }
        }, 10000);
    });
</script>

<?php include 'app/Views/include/footer.php' ?>