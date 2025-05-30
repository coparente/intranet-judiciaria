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
                    <?= Helper::mensagem('ciri') ?>
                    <?= Helper::mensagemSweetAlert('ciri') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-file-upload me-2"></i> Upload de Processos
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i> Instruções
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Para importar múltiplos processos, siga as instruções abaixo:</p>
                            <ol>
                                <li>Crie um arquivo de texto (.txt) com os números dos processos separados pelo caractere <strong>#</strong></li>
                                <li>Exemplo de conteúdo: <code>5001234-12.2023.8.09.0051#5001235-12.2023.8.09.0051#5001236-12.2023.8.09.0051</code></li>
                                <li>Selecione o arquivo e clique em "Importar Processos" para iniciar o upload</li>
                            </ol>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i> Os processos serão importados com status "Pendente" e não serão atribuídos a nenhum usuário inicialmente. Os detalhes como tipo de ato, tipo de intimação e outros campos serão preenchidos durante a análise.
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-upload me-2"></i> Formulário de Upload
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/ciri/uploadProcessos" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="arquivo_txt" class="form-label">Arquivo TXT com números de processos:</label>
                                        <input type="file" name="arquivo_txt" id="arquivo_txt" class="form-control" accept=".txt" required>
                                        <div class="form-text">Apenas arquivos .txt são aceitos (máx. 10MB)</div>
                                    </div>
                                </div>                               
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i> Importar Processos
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?> 