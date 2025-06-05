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
                                <i class="fas fa-chart-bar me-2"></i> Métricas do Chat
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/index" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filtro de Período -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <form method="GET" action="<?= URL ?>/chat/metricas" class="row g-3">
                                        <div class="col-md-4">
                                            <label for="inicio" class="form-label">Data Inicial:</label>
                                            <input type="date" class="form-control" id="inicio" name="inicio" 
                                                value="<?= $dados['periodo']['inicio'] ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fim" class="form-label">Data Final:</label>
                                            <input type="date" class="form-control" id="fim" name="fim" 
                                                value="<?= $dados['periodo']['fim'] ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-filter me-1"></i> Filtrar
                                                </button>
                                                <button type="button" class="btn btn-secondary" onclick="definirPeriodoRapido('hoje')">Hoje</button>
                                                <button type="button" class="btn btn-secondary" onclick="definirPeriodoRapido('semana')">7 dias</button>
                                                <button type="button" class="btn btn-secondary" onclick="definirPeriodoRapido('mes')">30 dias</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Cards de Métricas Gerais -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Total de Conversas</h6>
                                                    <h3 class="mb-0"><?= $dados['metricas_locais']['total_conversas'] ?? 0 ?></h3>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-comments fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Mensagens Enviadas</h6>
                                                    <h3 class="mb-0"><?= $dados['metricas_locais']['mensagens_enviadas'] ?? 0 ?></h3>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-paper-plane fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Mensagens Recebidas</h6>
                                                    <h3 class="mb-0"><?= $dados['metricas_locais']['mensagens_recebidas'] ?? 0 ?></h3>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-inbox fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Conversas Ativas</h6>
                                                    <h3 class="mb-0"><?= $dados['metricas_locais']['conversas_ativas'] ?? 0 ?></h3>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-comments-dollar fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Métricas da API SERPRO -->
                            <?php if (isset($dados['metricas']) && $dados['metricas']['status'] == 200): ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Métricas da API SERPRO</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <?php 
                                                $metricas_api = $dados['metricas']['response'] ?? [];
                                                foreach ($metricas_api as $metrica => $valor): 
                                                ?>
                                                <div class="col-md-4 mb-3">
                                                    <div class="card border">
                                                        <div class="card-body text-center">
                                                            <h5 class="card-title text-capitalize"><?= str_replace('_', ' ', $metrica) ?></h5>
                                                            <h3 class="text-primary"><?= is_numeric($valor) ? number_format($valor) : $valor ?></h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Gráfico de Mensagens por Dia -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>Mensagens por Dia</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="chartMensagensDia" height="100"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabela de Atividade Recente -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Tipos de Mensagem</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="chartTiposMensagem" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Top Conversas</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="topConversas">
                                                <p class="text-muted">Carregando dados...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumo de Status -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status de Entrega</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row" id="statusEntrega">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-primary" id="statusEnviado">-</h4>
                                                        <p class="text-muted">Enviado</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-info" id="statusEntregue">-</h4>
                                                        <p class="text-muted">Entregue</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-success" id="statusLido">-</h4>
                                                        <p class="text-muted">Lido</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-danger" id="statusErro">-</h4>
                                                        <p class="text-muted">Erro</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar gráficos
    inicializarGraficos();
    carregarDadosAdicionais();

    function definirPeriodoRapido(periodo) {
        const hoje = new Date();
        const inicioInput = document.getElementById('inicio');
        const fimInput = document.getElementById('fim');
        
        let dataInicio = new Date();
        
        switch(periodo) {
            case 'hoje':
                dataInicio = hoje;
                break;
            case 'semana':
                dataInicio.setDate(hoje.getDate() - 7);
                break;
            case 'mes':
                dataInicio.setDate(hoje.getDate() - 30);
                break;
        }
        
        inicioInput.value = dataInicio.toISOString().split('T')[0];
        fimInput.value = hoje.toISOString().split('T')[0];
    }

    function inicializarGraficos() {
        // Gráfico de Mensagens por Dia
        const ctxMensagens = document.getElementById('chartMensagensDia').getContext('2d');
        new Chart(ctxMensagens, {
            type: 'line',
            data: {
                labels: gerarUltimos7Dias(),
                datasets: [
                    {
                        label: 'Enviadas',
                        data: gerarDadosAleatorios(7, 0, 50),
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Recebidas',
                        data: gerarDadosAleatorios(7, 0, 30),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Tipos de Mensagem
        const ctxTipos = document.getElementById('chartTiposMensagem').getContext('2d');
        new Chart(ctxTipos, {
            type: 'doughnut',
            data: {
                labels: ['Texto', 'Imagem', 'Documento', 'Áudio', 'Vídeo'],
                datasets: [{
                    data: [<?= $dados['metricas_locais']['mensagens_enviadas'] ?? 65 ?>, 20, 10, 3, 2],
                    backgroundColor: [
                        '#4CAF50',
                        '#2196F3',
                        '#FF9800',
                        '#9C27B0',
                        '#F44336'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    function carregarDadosAdicionais() {
        // Simular carregamento de dados de status
        setTimeout(() => {
            document.getElementById('statusEnviado').textContent = '<?= $dados['metricas_locais']['mensagens_enviadas'] ?? 0 ?>';
            document.getElementById('statusEntregue').textContent = Math.floor((<?= $dados['metricas_locais']['mensagens_enviadas'] ?? 0 ?>) * 0.9);
            document.getElementById('statusLido').textContent = Math.floor((<?= $dados['metricas_locais']['mensagens_enviadas'] ?? 0 ?>) * 0.7);
            document.getElementById('statusErro').textContent = Math.floor((<?= $dados['metricas_locais']['mensagens_enviadas'] ?? 0 ?>) * 0.05);
        }, 1000);

        // Carregar top conversas
        setTimeout(() => {
            const topConversas = document.getElementById('topConversas');
            topConversas.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Contato #1
                        <span class="badge bg-primary rounded-pill">45 msgs</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Contato #2
                        <span class="badge bg-primary rounded-pill">32 msgs</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Contato #3
                        <span class="badge bg-primary rounded-pill">28 msgs</span>
                    </div>
                </div>
            `;
        }, 1500);
    }

    function gerarUltimos7Dias() {
        const dias = [];
        for (let i = 6; i >= 0; i--) {
            const data = new Date();
            data.setDate(data.getDate() - i);
            dias.push(data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
        }
        return dias;
    }

    function gerarDadosAleatorios(quantidade, min, max) {
        const dados = [];
        for (let i = 0; i < quantidade; i++) {
            dados.push(Math.floor(Math.random() * (max - min + 1)) + min);
        }
        return dados;
    }

    // Disponibilizar função globalmente
    window.definirPeriodoRapido = definirPeriodoRapido;
});
</script>

<?php include 'app/Views/include/footer.php' ?> 