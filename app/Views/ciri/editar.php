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
                            <i class="fas fa-edit me-2"></i> Editar Processo CIRI
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/ciri/visualizar/<?= $dados['id'] ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-file-alt me-2"></i> Dados do Processo
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/ciri/editar/<?= $dados['id'] ?>" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="numero_processo">Número do Processo: <span class="text-danger">*</span></label>
                                        <input type="text" name="numero_processo" id="numero_processo" class="form-control <?= isset($dados['numero_processo_erro']) && !empty($dados['numero_processo_erro']) ? 'is-invalid' : '' ?>" value="<?= $dados['numero_processo'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['numero_processo_erro'] ?? '' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="comarca_serventia">Comarca/Serventia: <span class="text-danger">*</span></label>
                                        <input type="text" name="comarca_serventia" id="comarca_serventia" class="form-control <?= isset($dados['comarca_serventia_erro']) && !empty($dados['comarca_serventia_erro']) ? 'is-invalid' : '' ?>" value="<?= $dados['comarca_serventia'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['comarca_serventia_erro'] ?? '' ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="gratuidade_justica">Gratuidade de Justiça:</label>
                                        <select name="gratuidade_justica" id="gratuidade_justica" class="form-control">
                                            <option value="">Selecione</option>
                                            <option value="sim" <?= $dados['gratuidade_justica'] == 'sim' ? 'selected' : '' ?>>Sim</option>
                                            <option value="nao" <?= $dados['gratuidade_justica'] == 'nao' ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_ato_ciri_id">Tipo de Ato:</label>
                                        <select name="tipo_ato_ciri_id" id="tipo_ato_ciri_id" class="form-control">
                                            <option value="">Selecione</option>
                                            <?php foreach ($dados['tipos_ato'] as $tipo): ?>
                                                <option value="<?= $tipo->id ?>" <?= $dados['tipo_ato_ciri_id'] == $tipo->id ? 'selected' : '' ?>>
                                                    <?= $tipo->nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_intimacao_ciri_id">Tipo de Intimação:</label>
                                        <select name="tipo_intimacao_ciri_id" id="tipo_intimacao_ciri_id" class="form-control">
                                            <option value="">Selecione</option>
                                            <?php foreach ($dados['tipos_intimacao'] as $tipo): ?>
                                                <option value="<?= $tipo->id ?>" <?= $dados['tipo_intimacao_ciri_id'] == $tipo->id ? 'selected' : '' ?>>
                                                    <?= $tipo->nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status_processo">Status do Processo:</label>
                                        <select name="status_processo" id="status_processo" class="form-control">
                                            <option value="pendente" <?= $dados['status_processo'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                            <option value="em_andamento" <?= $dados['status_processo'] == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                            <option value="concluido" <?= $dados['status_processo'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                            <option value="cancelado" <?= $dados['status_processo'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                            <option value="PROCESSO FINALIZADO" <?= $dados['status_processo'] == 'PROCESSO FINALIZADO' ? 'selected' : '' ?>>PROCESSO FINALIZADO</option>
                                            <option value="RETORNAR PARA ANÁLISE" <?= $dados['status_processo'] == 'RETORNAR PARA ANÁLISE' ? 'selected' : '' ?>>RETORNAR PARA ANÁLISE</option>
                                            <option value="AGUARDANDO RESPOSTA DE WHATSAPP" <?= $dados['status_processo'] == 'AGUARDANDO RESPOSTA DE WHATSAPP' ? 'selected' : '' ?>>AGUARDANDO RESPOSTA DE WHATSAPP</option>
                                            <option value="AGUARDANDO RESPOSTA DE E-MAIL" <?= $dados['status_processo'] == 'AGUARDANDO RESPOSTA DE E-MAIL' ? 'selected' : '' ?>>AGUARDANDO RESPOSTA DE E-MAIL</option>
                                            <option value="AGUARDANDO PROVIDÊNCIA" <?= $dados['status_processo'] == 'AGUARDANDO PROVIDÊNCIA' ? 'selected' : '' ?>>AGUARDANDO PROVIDÊNCIA</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="observacao_atividade">Observações:</label>
                                        <textarea name="observacao_atividade" id="observacao_atividade" class="form-control" rows="4"><?= $dados['observacao_atividade'] ?></textarea>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i> Salvar Alterações
                                    </button>
                                    <a href="<?= URL ?>/ciri/visualizar/<?= $dados['id'] ?>" class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i> Cancelar
                                    </a>
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