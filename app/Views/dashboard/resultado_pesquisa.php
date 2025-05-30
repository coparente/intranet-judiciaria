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
                    <?= Helper::mensagem('dashboard') ?>
                    <?= Helper::mensagemSweetAlert('dashboard') ?>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-search me-2"></i> Resultados da Pesquisa
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/dashboard/inicial" class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <!-- Filtros Aplicados -->
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Filtros aplicados:</h6>
                                        <p class="mb-0">
                                            <?php if (!empty($dados['numero_processo'])): ?>
                                                <span class="badge bg-primary me-2 text-white">Processo: <?= $dados['numero_processo'] ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($dados['numero_guia'])): ?>
                                                <span class="badge bg-primary me-2 text-white">Guia: <?= $dados['numero_guia'] ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <!-- fim box-header -->
                                <fieldset aria-labelledby="tituloMenu">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" class="display" id="processos" width="100%" cellspacing="0">
                                                <thead class="cor-fundo-azul-escuro text-white">
                                                    <tr>
                                                        <th>Nº Processo</th>
                                                        <th>Comarca</th>
                                                        <th width="120">Status</th>
                                                        <th width="150">Responsável</th>
                                                        <th width="180">Guias</th>
                                                        <th width="100" class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($dados['resultados']) && !empty($dados['resultados'])): ?>
                                                        <?php foreach ($dados['resultados'] as $resultado): ?>
                                                            <?php 
                                                            $tooltipInfo = [];
                                                            
                                                            // Decodifica o JSON das guias se existir
                                                            $guias = json_decode($resultado->guias);
                                                            
                                                            if (!empty($guias)) {
                                                                $tooltipInfo[] = "<strong>Guias de Pagamento:</strong>";
                                                                foreach ($guias as $guia) {
                                                                    $statusClass = '';
                                                                    switch ($guia->status) {
                                                                        case 'pago':
                                                                            $statusClass = 'text-success';
                                                                            break;
                                                                        case 'pendente':
                                                                            $statusClass = 'text-warning';
                                                                            break;
                                                                        default:
                                                                            $statusClass = 'text-danger';
                                                                            break;
                                                                    }
                                                                    
                                                                    $tooltipInfo[] = sprintf(
                                                                        "Guia %s: R$ %s - <span class='%s'>%s</span>",
                                                                        $guia->numero_guia,
                                                                        number_format($guia->valor, 2, ',', '.'),
                                                                        $statusClass,
                                                                        ucfirst($guia->status)
                                                                    );
                                                                }
                                                            }
                                                            
                                                            $tooltipText = !empty($tooltipInfo) ? implode("<br>", $tooltipInfo) : "Sem informações adicionais";
                                                            ?>
                                                            <tr data-toggle="tooltip"
                                                                data-html="true"
                                                                data-placement="right"
                                                                title="<?= $tooltipText ?>">
                                                                <td><?= $resultado->numero_processo ?></td>
                                                                <td><?= $resultado->comarca_serventia ?? 'N/A' ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?= $resultado->status == 'concluido' ? 'success' : ($resultado->status == 'analise' ? 'primary text-white' : ($resultado->status == 'intimacao' ? 'warning' : ($resultado->status == 'diligencia' ? 'info' : ($resultado->status == 'aguardando' ? 'secondary' : ($resultado->status == 'arquivado' ? 'dark text-white' : 'light'))))) ?>">
                                                                        <?= $resultado->status_formatado ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= $resultado->responsavel_nome ?? 'Não atribuído' ?></td>
                                                                <td>
                                                                    <?php if (!empty($resultado->guias)): ?>
                                                                        <?php $guias = json_decode($resultado->guias); ?>
                                                                        <?php foreach ($guias as $guia): ?>
                                                                            <div class="mb-1">
                                                                                <span class="badge bg-secondary">
                                                                                    <?= $guia->numero_guia ?>
                                                                                </span>
                                                                                <span class="badge bg-<?= $guia->status == 'pago' ? 'success' : 'warning' ?>">
                                                                                    <?= ucfirst($guia->status) ?>
                                                                                </span>
                                                                                <?php if (!empty($guia->valor)): ?>
                                                                                    <span class="badge bg-info">
                                                                                        R$ <?= number_format($guia->valor, 2, ',', '.') ?>
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Sem guias</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="<?= URL ?>/processos/visualizar/<?= $resultado->id ?>" 
                                                                       class="btn btn-sm btn-primary" title="Visualizar processo">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4">
                                                                <div class="text-muted">
                                                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                                                    <p>Nenhum resultado encontrado para os critérios de pesquisa.</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </fieldset>
                            </div><!-- fim box -->
                        </div>
                    </div> <!-- fim row -->
                </div><!-- fim col-md-9 -->
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?>

<!-- Inicialização dos tooltips -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa os tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script> 