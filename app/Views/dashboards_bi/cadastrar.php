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
                            <i class="fas fa-plus me-2"></i> Cadastrar Dashboard BI
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/dashboardsbi/index" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="<?= URL ?>/dashboardsbi/cadastrar" method="post">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                        <input type="text" name="titulo" id="titulo" class="form-control <?= isset($dados['erros']['titulo']) ? 'is-invalid' : '' ?>" value="<?= $dados['titulo'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['erros']['titulo'] ?? '' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="categoria" class="form-label">Categoria</label>
                                        <input type="text" name="categoria" id="categoria" class="form-control" value="<?= $dados['categoria'] ?>">
                                        <small class="text-muted">Ex: Financeiro, RH, Operacional</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea name="descricao" id="descricao" class="form-control" rows="3"><?= $dados['descricao'] ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="url" class="form-label">URL do Dashboard <span class="text-danger">*</span></label>
                                    <input type="url" name="url" id="url" class="form-control <?= isset($dados['erros']['url']) ? 'is-invalid' : '' ?>" value="<?= $dados['url'] ?>" required>
                                    <div class="invalid-feedback">
                                        <?= $dados['erros']['url'] ?? '' ?>
                                    </div>
                                    <small class="text-muted">URL completa do dashboard no Power BI, Tableau, etc.</small>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="icone" class="form-label">Ícone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i id="icone-preview" class="fas <?= $dados['icone'] ?>"></i></span>
                                            <input type="text" name="icone" id="icone" class="form-control" value="<?= $dados['icone'] ?>">
                                        </div>
                                        <small class="text-muted">Ex: fa-chart-bar, fa-chart-pie, fa-chart-line</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ordem" class="form-label">Ordem</label>
                                        <input type="number" name="ordem" id="ordem" class="form-control" value="<?= $dados['ordem'] ?>" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="status" class="form-label">Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="ativo" <?= $dados['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                            <option value="inativo" <?= $dados['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Salvar
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

<script>
    // Atualiza o preview do ícone ao digitar
    document.getElementById('icone').addEventListener('input', function() {
        const iconePreview = document.getElementById('icone-preview');
        iconePreview.className = 'fas ' + this.value;
    });
</script>

<?php include 'app/Views/include/footer.php' ?>
