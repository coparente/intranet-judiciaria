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

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-plus-circle me-2"></i> Novo Processo CIRI
                            </h5>
                            <div>
                                <a href="<?= URL ?>/ciri/listar" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (isset($dados['processo_existente']) && $dados['processo_existente']): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Atenção!</strong> Já existe um processo cadastrado com o número "<?= $dados['numero_processo'] ?>".
                                    <p class="mt-2">Se você continuar, um novo processo com o mesmo número será cadastrado, o que pode gerar duplicidade.</p>
                                    <div class="mt-3">
                                        <form method="post" action="<?= URL ?>/ciri/cadastrar">
                                            <input type="hidden" name="numero_processo" value="<?= $dados['numero_processo'] ?>">
                                            <input type="hidden" name="comarca_serventia" value="<?= $dados['comarca_serventia'] ?>">
                                            <input type="hidden" name="gratuidade_justica" value="<?= $dados['gratuidade_justica'] ?? '' ?>">
                                            <input type="hidden" name="tipo_ato_ciri_id" value="<?= $dados['tipo_ato_ciri_id'] ?? '' ?>">
                                            <input type="hidden" name="tipo_intimacao_ciri_id" value="<?= $dados['tipo_intimacao_ciri_id'] ?? '' ?>">
                                            <input type="hidden" name="observacao_atividade" value="<?= $dados['observacao_atividade'] ?? '' ?>">
                                            <input type="hidden" name="status_processo" value="<?= $dados['status_processo'] ?? 'pendente' ?>">
                                            <input type="hidden" name="confirmar_duplicado" value="1">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-check me-1"></i> Continuar mesmo assim
                                            </button>
                                            <a href="<?= URL ?>/ciri/cadastrar" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i> Cancelar
                                            </a>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form action="<?= URL ?>/ciri/cadastrar" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="numero_processo" class="form-label">Número do Processo: <span class="text-danger">*</span></label>
                                            <input type="text" name="numero_processo" id="numero_processo" class="form-control <?= isset($dados['numero_processo_erro']) && !empty($dados['numero_processo_erro']) ? 'is-invalid' : '' ?>" value="<?= $dados['numero_processo'] ?? '' ?>" required>
                                            <div class="invalid-feedback">
                                                <?= $dados['numero_processo_erro'] ?? '' ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="comarca_serventia" class="form-label">Comarca/Serventia: <span class="text-danger">*</span></label>
                                            <input type="text" name="comarca_serventia" id="comarca_serventia" class="form-control <?= isset($dados['comarca_serventia_erro']) && !empty($dados['comarca_serventia_erro']) ? 'is-invalid' : '' ?>" value="<?= $dados['comarca_serventia'] ?? '' ?>" required>
                                            <div class="invalid-feedback">
                                                <?= $dados['comarca_serventia_erro'] ?? '' ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="gratuidade_justica" class="form-label">Gratuidade de Justiça:</label>
                                            <select name="gratuidade_justica" id="gratuidade_justica" class="form-control">
                                                <option value="">Selecione</option>
                                                <option value="sim" <?= isset($dados['gratuidade_justica']) && $dados['gratuidade_justica'] == 'sim' ? 'selected' : '' ?>>Sim</option>
                                                <option value="nao" <?= isset($dados['gratuidade_justica']) && $dados['gratuidade_justica'] == 'nao' ? 'selected' : '' ?>>Não</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="tipo_ato_ciri_id" class="form-label">Tipo de Ato:</label>
                                            <select name="tipo_ato_ciri_id" id="tipo_ato_ciri_id" class="form-control">
                                                <option value="">Selecione</option>
                                                <?php foreach ($dados['tipos_ato'] as $tipo): ?>
                                                    <option value="<?= $tipo->id ?>" <?= isset($dados['tipo_ato_ciri_id']) && $dados['tipo_ato_ciri_id'] == $tipo->id ? 'selected' : '' ?>>
                                                        <?= $tipo->nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="tipo_intimacao_ciri_id" class="form-label">Tipo de Intimação:</label>
                                            <select name="tipo_intimacao_ciri_id" id="tipo_intimacao_ciri_id" class="form-control">
                                                <option value="">Selecione</option>
                                                <?php foreach ($dados['tipos_intimacao'] as $tipo): ?>
                                                    <option value="<?= $tipo->id ?>" <?= isset($dados['tipo_intimacao_ciri_id']) && $dados['tipo_intimacao_ciri_id'] == $tipo->id ? 'selected' : '' ?>>
                                                        <?= $tipo->nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="observacao_atividade" class="form-label">Observações:</label>
                                            <textarea name="observacao_atividade" id="observacao_atividade" class="form-control" rows="4"><?= $dados['observacao_atividade'] ?? '' ?></textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="status_processo" id="status_processo" value="pendente">

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-1"></i> Salvar
                                        </button>
                                        <a href="<?= URL ?>/ciri/listar" class="btn btn-danger">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'app/Views/include/footer.php' ?> 