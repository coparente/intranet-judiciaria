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
                    <?= Helper::mensagem('consulta_processos') ?>
                    <?= Helper::mensagemSweetAlert('consulta_processos') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-search me-2"></i> Consulta de Processos (DataJud)
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard/inicial">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Consulta de Processos (DataJud)</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Card de Pesquisa -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-search me-2"></i> Pesquisar Processo (DataJud)
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/consultaprocessos/index" method="POST">
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
                    <!-- Card com o resultado da pesquisa -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i> Detalhes do Processo (DataJud)
                                </h5>
                                <span class="badge bg-primary">
                                    <?= $dados['resultado']['grau'] ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2 mb-3">Informações Gerais</h6>
                                    <p><strong>Número:</strong> <?= htmlspecialchars($dados['resultado']['numeroProcesso']) ?></p>
                                    <p><strong>Classe:</strong> <?= htmlspecialchars($dados['resultado']['classe']['nome']) ?> 
                                       (Cód. <?= htmlspecialchars($dados['resultado']['classe']['codigo']) ?>)</p>
                                    <p><strong>Sistema:</strong> <?= htmlspecialchars($dados['resultado']['sistema']['nome']) ?></p>
                                    <p><strong>Tribunal:</strong> <?= htmlspecialchars($dados['resultado']['tribunal']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2 mb-3">Datas e Localização</h6>
                                    <p><strong>Data de Ajuizamento:</strong> <?= date('d/m/Y', strtotime($dados['resultado']['dataAjuizamento'])) ?></p>
                                    <p><strong>Última Atualização:</strong> <?= date('d/m/Y H:i', strtotime($dados['resultado']['dataHoraUltimaAtualizacao'])) ?></p>
                                    <p><strong>Órgão Julgador:</strong> <?= htmlspecialchars($dados['resultado']['orgaoJulgador']['nome']) ?></p>
                                    <p><strong>Nível de Sigilo:</strong> <?= htmlspecialchars($dados['resultado']['nivelSigilo']) ?></p>
                                </div>
                            </div>
                            
                            <!-- Assuntos -->
                            <h6 class="border-bottom pb-2 mb-3">Assuntos</h6>
                            <div class="mb-4">
                                <ul class="list-group">
                                    <?php foreach ($dados['resultado']['assuntos'] as $assunto): ?>
                                    <li class="list-group-item">
                                        <i class="fas fa-tag me-2 text-muted"></i>
                                        <?= htmlspecialchars($assunto['nome']) ?> 
                                        <span class="badge bg-secondary">Cód. <?= htmlspecialchars($assunto['codigo']) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <!-- Movimentos -->
                            <h6 class="border-bottom pb-2 mb-3">Movimentações</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Movimento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dados['resultado']['movimentos'] as $movimento): ?>
                                        <tr>
                                            <td width="180"><?= date('d/m/Y H:i', strtotime($movimento['dataHora'])) ?></td>
                                            <td><?= htmlspecialchars($movimento['nome']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Dados fornecidos pela API pública do DataJud CNJ.
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
                                <strong>Sobre a consulta de processos:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Esta consulta utiliza a API pública do DataJud do CNJ.</li>
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