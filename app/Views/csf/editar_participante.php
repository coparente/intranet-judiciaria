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
                            <i class="fas fa-edit me-2"></i> Editar Participante
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/csf/visualizar/<?= $dados['visita_id'] ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-user me-2"></i> Dados do Participante
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= URL ?>/csf/editarParticipante/<?= $dados['id'] ?>">
                                <p class="text-muted"><small><sup class="text-danger">*</sup> Campos obrigatórios</small></p>
                                
                                <input type="hidden" name="id" value="<?= $dados['id'] ?>">
                                <input type="hidden" name="visita_id" value="<?= $dados['visita_id'] ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nome" class="form-label">Nome <sup class="text-danger">*</sup></label>
                                        <input type="text" class="form-control" id="nome" name="nome" value="<?= $dados['nome'] ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="cpf" class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="cpf" name="cpf" value="<?= $dados['cpf'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="contato" class="form-label">Contato</label>
                                        <input type="text" class="form-control" id="contato" name="contato" value="<?= $dados['contato'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="idade" class="form-label">Idade</label>
                                        <input type="number" class="form-control" id="idade" name="idade" value="<?= $dados['idade'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="qtd_pessoas" class="form-label">Quantidade de pessoas</label>
                                        <input type="number" class="form-control" id="qtd_pessoas" name="qtd_pessoas" value="<?= $dados['qtd_pessoas'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="menores" class="form-label">Menores</label>
                                        <input type="number" class="form-control" id="menores" name="menores" value="<?= $dados['menores'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="idosos" class="form-label">Idosos</label>
                                        <input type="number" class="form-control" id="idosos" name="idosos" value="<?= $dados['idosos'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="pessoa_deficiencia" class="form-label">Pessoa com deficiência</label>
                                        <select class="form-control" id="pessoa_deficiencia" name="pessoa_deficiencia">
                                            <option value="Não" <?= $dados['pessoa_deficiencia'] == 'Não' ? 'selected' : '' ?>>Não</option>
                                            <option value="Sim" <?= $dados['pessoa_deficiencia'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="gestante" class="form-label">Gestante</label>
                                        <select class="form-control" id="gestante" name="gestante">
                                            <option value="Não" <?= $dados['gestante'] == 'Não' ? 'selected' : '' ?>>Não</option>
                                            <option value="Sim" <?= $dados['gestante'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="auxilio" class="form-label">Recebe auxílio</label>
                                        <select class="form-control" id="auxilio" name="auxilio">
                                            <option value="Não" <?= $dados['auxilio'] == 'Não' ? 'selected' : '' ?>>Não</option>
                                            <option value="Sim" <?= $dados['auxilio'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="criancas_escola" class="form-label">Crianças na escola</label>
                                        <input type="number" class="form-control" id="frequentam_escola" name="frequentam_escola" value="<?= $dados['frequentam_escola'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="qtd_trabalham" class="form-label">Quantidade que trabalham</label>
                                        <input type="number" class="form-control" id="qtd_trabalham" name="qtd_trabalham" value="<?= $dados['qtd_trabalham'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="vulneravel" class="form-label">Vulnerável</label>
                                        <select class="form-control" id="vulneravel" name="vulneravel">
                                            <option value="Não" <?= $dados['vulneravel'] == 'Não' ? 'selected' : '' ?>>Não</option>
                                            <option value="Sim" <?= $dados['vulneravel'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lote_vago" class="form-label">Lote vago</label>
                                        <select class="form-control" id="lote_vago" name="lote_vago">
                                            <option value="Não" <?= $dados['lote_vago'] == 'Não' ? 'selected' : '' ?>>Não</option>
                                            <option value="Sim" <?= $dados['lote_vago'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="mora_local" class="form-label">Mora no local</label>
                                        <select class="form-control" id="mora_local" name="mora_local">
                                            <option value="Não" <?= $dados['mora_local'] == 'Não' ? 'selected' : '' ?>>Não</option>
                                            <option value="Sim" <?= $dados['mora_local'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="fonte_renda" class="form-label">Fonte de renda</label>
                                        <input type="text" class="form-control" id="fonte_renda" name="fonte_renda" value="<?= $dados['fonte_renda'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="descricao" class="form-label">Descrição/Observações</label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="4"><?= $dados['descricao'] ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Salvar Alterações
                                        </button>
                                    </div>
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