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
                    <?= Helper::mensagem('projudi') ?>
                    <?= Helper::mensagemSweetAlert('projudi') ?>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-gavel me-2"></i> Consulta Projudi
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard/inicial">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Consulta Projudi</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Card de Pesquisa -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-search me-2"></i> Pesquisar Processo no Projudi
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/projudi/index" method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="numeroProcesso" class="form-label">Número do Processo</label>
                                            <input type="text" class="form-control" id="numeroProcesso" name="numeroProcesso" 
                                                   value="<?= $dados['numeroProcesso'] ?>" 
                                                   placeholder="Digite o número do processo (Ex: 5536022-65.2023.8.09.0006)" required>
                                            <div class="form-text">Formato: NNNNNNN-DD.AAAA.J.TR.OOOO</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3 w-100">
                                            <label class="form-label">.</label>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-search me-2"></i> Pesquisar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($dados['resultado']): ?>
                    <!-- Card com o resultado da pesquisa Projudi -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i> Detalhes do Processo (Projudi)
                                </h5>
                                <?php if (isset($dados['resultado']->outrasInformacoes->status)): ?>
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($dados['resultado']->outrasInformacoes->status) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Informações Básicas -->
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">Informações Básicas</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Número:</strong> <?= htmlspecialchars($dados['resultado']->numero) ?></p>
                                        <p><strong>Área:</strong> <?= htmlspecialchars($dados['resultado']->area) ?></p>
                                        <?php if (isset($dados['resultado']->outrasInformacoes)): ?>
                                        <p><strong>Classe:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->classe) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (isset($dados['resultado']->outrasInformacoes)): ?>
                                        <p><strong>Serventia:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->serventia) ?></p>
                                        <p><strong>Data de Autuação:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->dataAutuacao) ?></p>
                                        <p><strong>Fase Processual:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->faseProcessual) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Assuntos -->
                            <?php if (isset($dados['resultado']->outrasInformacoes->assuntos->assunto)): ?>
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">Assuntos</h6>
                                <ul class="list-group">
                                    <?php foreach ($dados['resultado']->outrasInformacoes->assuntos->assunto as $assunto): ?>
                                    <li class="list-group-item"><?= htmlspecialchars($assunto) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Recursos -->
                            <?php if (isset($dados['resultado']->recursos->recurso)): ?>
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">Recursos</h6>
                                <?php foreach ($dados['resultado']->recursos->recurso as $recurso): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <strong>Classe do Recurso:</strong> <?= htmlspecialchars($recurso->classe) ?>
                                    </div>
                                    <div class="card-body">
                                        <h6>Partes do Recurso</h6>
                                        <ul class="list-group">
                                            <?php if (isset($recurso->partes->parte)): ?>
                                                <?php foreach ($recurso->partes->parte as $parte): ?>
                                                <li class="list-group-item">
                                                    <strong><?= htmlspecialchars($parte->parteTipo) ?>:</strong> <?= htmlspecialchars($parte->nome) ?>
                                                </li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Outras Informações -->
                            <?php if (isset($dados['resultado']->outrasInformacoes)): ?>
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">Outras Informações</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Valor da Causa:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->valorCausa) ?></p>
                                        <p><strong>Data de Distribuição:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->dataDistribuicao) ?></p>
                                        <p><strong>Segredo de Justiça:</strong> <?= ($dados['resultado']->outrasInformacoes->segredoJustica == "1" ? "Sim" : "Não") ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Prioridade:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->prioridade) ?></p>
                                        <p><strong>Processo Originário:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->processoOriginario) ?></p>
                                        <p><strong>Data de Trânsito em Julgado:</strong> <?= htmlspecialchars($dados['resultado']->outrasInformacoes->dataTransitoJulgado) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Informações Adicionais -->
                            <?php if (isset($dados['resultado']->informacoesAdicionais)): ?>
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">Informações Adicionais</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Réu Preso:</strong> <?= htmlspecialchars($dados['resultado']->informacoesAdicionais->reuPreso) ?></p>
                                        <p><strong>Número do Inquérito:</strong> <?= htmlspecialchars($dados['resultado']->informacoesAdicionais->numeroInquerito) ?></p>
                                        <p><strong>Data da Prisão:</strong> <?= htmlspecialchars($dados['resultado']->informacoesAdicionais->dataPrisao) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Data da Denúncia:</strong> <?= htmlspecialchars($dados['resultado']->informacoesAdicionais->dataOferecimentoDenunciaQueixa) ?></p>
                                        <p><strong>Data do Fato:</strong> <?= htmlspecialchars($dados['resultado']->informacoesAdicionais->dataFato) ?></p>
                                        <p><strong>Data da Prescrição:</strong> <?= htmlspecialchars($dados['resultado']->informacoesAdicionais->dataPrescricao) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Dados fornecidos pelo WebService do Projudi TJGO.
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Card de informações -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i> Informações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i> 
                                <strong>Sobre a consulta de processos no Projudi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Esta consulta utiliza o WebService oficial do Projudi TJGO.</li>
                                    <li>Os dados exibidos são públicos e não incluem informações de processos em segredo de justiça.</li>
                                    <li>Para consultar, digite o número completo do processo no formato padrão CNJ.</li>
                                    <li>Em caso de dúvidas, entre em contato com o suporte.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    // Máscara para o número do processo
    document.addEventListener('DOMContentLoaded', function() {
        const numeroProcesso = document.getElementById('numeroProcesso');
        
        numeroProcesso.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 20) {
                value = value.substring(0, 20);
            }
            
            // Aplicar a máscara: NNNNNNN-DD.AAAA.J.TR.OOOO
            if (value.length > 0) {
                let formattedValue = '';
                
                if (value.length > 7) {
                    formattedValue = value.substring(0, 7) + '-' + value.substring(7);
                } else {
                    formattedValue = value;
                }
                
                if (value.length > 9) {
                    formattedValue = formattedValue.substring(0, 10) + '.' + formattedValue.substring(10);
                }
                
                if (value.length > 13) {
                    formattedValue = formattedValue.substring(0, 15) + '.' + formattedValue.substring(15);
                }
                
                if (value.length > 14) {
                    formattedValue = formattedValue.substring(0, 17) + '.' + formattedValue.substring(17);
                }
                
                if (value.length > 16) {
                    formattedValue = formattedValue.substring(0, 20) + '.' + formattedValue.substring(20);
                }
                
                e.target.value = formattedValue;
            }
        });
    });
</script>

<?php include 'app/Views/include/footer.php' ?> 