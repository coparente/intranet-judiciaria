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
                    <?= Helper::mensagem('csf') ?>
                    <?= Helper::mensagemSweetAlert('csf') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-plus-circle me-2"></i> Nova Visita Técnica
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/csf/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-home me-2"></i> Dados da Visita Técnica
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= URL ?>/csf/cadastrar">
                                <p class="text-muted"><small><sup class="text-danger">*</sup> Campos obrigatórios</small></p>
                                
                                <h4 class="mb-3">Identificação do processo</h4>
                                <hr>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="processo" class="form-label">Número do processo: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="processo" id="processo" class="form-control <?= (!empty($dados['processo_erro'])) ? 'is-invalid' : '' ?>" value="<?= $dados['processo'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['processo_erro'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="comarca" class="form-label">Comarca: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="comarca" id="comarca" class="form-control <?= (!empty($dados['comarca_erro'])) ? 'is-invalid' : '' ?>" value="<?= $dados['comarca'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['comarca_erro'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="autor" class="form-label">Autor: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="autor" id="autor" class="form-control <?= (!empty($dados['autor_erro'])) ? 'is-invalid' : '' ?>" value="<?= $dados['autor'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['autor_erro'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="reu" class="form-label">Réu: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="reu" id="reu" class="form-control <?= (!empty($dados['reu_erro'])) ? 'is-invalid' : '' ?>" value="<?= $dados['reu'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['reu_erro'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="proad" class="form-label">Identificador do processo administrativo (PROAD): <sup class="text-danger">*</sup></label>
                                        <input type="text" name="proad" id="proad" class="form-control" value="<?= $dados['proad'] ?>" required>
                                    </div>
                                </div>
                                
                                <h4 class="mb-3">Identificação da Área</h4>
                                <hr>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="nome_ocupacao" class="form-label">Nome do assentamento (ocupação): <sup class="text-danger">*</sup></label>
                                        <input type="text" name="nome_ocupacao" id="nome_ocupacao" class="form-control" value="<?= $dados['nome_ocupacao'] ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="area_ocupada" class="form-label">Área Ocupada: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="area_ocupada" id="area_ocupada" class="form-control" value="<?= $dados['area_ocupada'] ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Descrição da Área: <sup class="text-danger">*</sup></label>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Energia Elétrica? <sup class="text-danger">*</sup></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="energia_eletrica" id="energia_sim" value="Sim" <?= ($dados['energia_eletrica'] == 'Sim') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="energia_sim">Sim</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="energia_eletrica" id="energia_nao" value="Não" <?= ($dados['energia_eletrica'] == 'Não') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="energia_nao">Não</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Água tratada? <sup class="text-danger">*</sup></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="agua_tratada" id="agua_sim" value="Sim" <?= ($dados['agua_tratada'] == 'Sim') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="agua_sim">Sim</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="agua_tratada" id="agua_nao" value="Não" <?= ($dados['agua_tratada'] == 'Não') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="agua_nao">Não</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Área de risco? <sup class="text-danger">*</sup></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="area_risco" id="risco_sim" value="Sim" <?= ($dados['area_risco'] == 'Sim') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="risco_sim">Sim</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="area_risco" id="risco_nao" value="Não" <?= ($dados['area_risco'] == 'Não') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="risco_nao">Não</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Tipo moradia? <sup class="text-danger">*</sup></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="moradia" id="moradia_alvenaria" value="Alvenaria" <?= ($dados['moradia'] == 'Alvenaria') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="moradia_alvenaria">Alvenaria</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="moradia" id="moradia_lona" value="Lona" <?= ($dados['moradia'] == 'Lona') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="moradia_lona">Lona</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i> Cadastrar Visita
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