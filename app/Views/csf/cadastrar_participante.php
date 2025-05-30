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
                            <i class="fas fa-user-plus me-2"></i> Adicionar Participante
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
                            <form method="POST" action="<?= URL ?>/csf/cadastrarParticipante/<?= $dados['visita_id'] ?>">
                                <p class="text-muted"><small><sup class="text-danger">*</sup> Campos obrigatórios</small></p>
                                
                                <input type="hidden" name="visita_id" value="<?= $dados['visita_id'] ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nome" class="form-label">Nome: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="nome" id="nome" class="form-control <?= (!empty($dados['nome_erro'])) ? 'is-invalid' : '' ?>" value="<?= $dados['nome'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['nome_erro'] ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cpf" class="form-label">CPF: <sup class="text-danger">*</sup></label>
                                        <input type="text" name="cpf" id="cpf" class="form-control <?= (!empty($dados['cpf_erro'])) ? 'is-invalid' : '' ?>" value="<?= $dados['cpf'] ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $dados['cpf_erro'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contato" class="form-label">Contato:</label>
                                        <input type="text" name="contato" id="contato" class="form-control" value="<?= $dados['contato'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="idade" class="form-label">Idade:</label>
                                        <input type="number" name="idade" id="idade" class="form-control" value="<?= $dados['idade'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="qtd_pessoas" class="form-label">Quantidade de pessoas:</label>
                                        <input type="number" name="qtd_pessoas" id="qtd_pessoas" class="form-control" value="<?= $dados['qtd_pessoas'] ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="menores" class="form-label">Menores:</label>
                                        <input type="number" name="menores" id="menores" class="form-control" value="<?= $dados['menores'] ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="idosos" class="form-label">Idosos:</label>
                                        <input type="number" name="idosos" id="idosos" class="form-control" value="<?= $dados['idosos'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="pessoa_deficiencia" class="form-label">Pessoa com deficiência:</label>
                                        <select name="pessoa_deficiencia" id="pessoa_deficiencia" class="form-control">
                                            <option value="Sim" <?= ($dados['pessoa_deficiencia'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['pessoa_deficiencia'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gestante" class="form-label">Gestante:</label>
                                        <select name="gestante" id="gestante" class="form-control">
                                            <option value="Sim" <?= ($dados['gestante'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['gestante'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="auxilio" class="form-label">Recebe auxílio do governo:</label>
                                        <select name="auxilio" id="auxilio" class="form-control">
                                            <option value="Sim" <?= ($dados['auxilio'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['auxilio'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="frequentam_escola" class="form-label">Crianças frequentam escola:</label>
                                        <select name="frequentam_escola" id="frequentam_escola" class="form-control">
                                            <option value="Sim" <?= ($dados['frequentam_escola'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['frequentam_escola'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="qtd_trabalham" class="form-label">Quantidade que trabalham:</label>
                                        <input type="number" name="qtd_trabalham" id="qtd_trabalham" class="form-control" value="<?= $dados['qtd_trabalham'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fonte_renda" class="form-label">Fonte de renda:</label>
                                        <input type="text" name="fonte_renda" id="fonte_renda" class="form-control" value="<?= $dados['fonte_renda'] ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="vulneravel" class="form-label">Vulnerável:</label>
                                        <select name="vulneravel" id="vulneravel" class="form-control">
                                            <option value="Sim" <?= ($dados['vulneravel'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['vulneravel'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lote_vago" class="form-label">Lote vago:</label>
                                        <select name="lote_vago" id="lote_vago" class="form-control">
                                            <option value="Sim" <?= ($dados['lote_vago'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['lote_vago'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="mora_local" class="form-label">Mora no local:</label>
                                        <select name="mora_local" id="mora_local" class="form-control">
                                            <option value="Sim" <?= ($dados['mora_local'] == 'Sim') ? 'selected' : '' ?>>Sim</option>
                                            <option value="Não" <?= ($dados['mora_local'] == 'Não') ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label for="descricao" class="form-label">Descrição/Observações:</label>
                                        <textarea name="descricao" id="descricao" class="form-control" rows="4"><?= $dados['descricao'] ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i> Cadastrar Participante
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
    // Máscara para CPF
    $(document).ready(function() {
        $('#cpf').mask('000.000.000-00', {reverse: true});
        $('#contato').mask('(00) 00000-0000');
    });
</script>

<?php include 'app/Views/include/footer.php' ?> 