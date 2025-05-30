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
                    <?= Helper::mensagem('processos') ?>
                    <?= Helper::mensagemSweetAlert('processos') ?>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-edit me-2"></i> Editar Processo
                        </h1>
                        <a href="<?= URL ?>/processos/visualizar/<?= $dados['processo']->id ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Voltar
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <h3 id="tabelas" class="box-title">
                                        <i class="fas fa-gavel me-2"></i> Processo: <?= $dados['processo']->numero_processo ?>
                                    </h3>
                                </div>
                                
                                <div class="card-body">
                                    <form action="<?= URL ?>/processos/atualizar/<?= $dados['processo']->id ?>" method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Número do Processo*</label>
                                                <input type="text" name="numero_processo" class="form-control" 
                                                    value="<?= $dados['processo']->numero_processo ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Comarca/Serventia*</label>
                                                <input type="text" name="comarca_serventia" class="form-control" 
                                                    value="<?= $dados['processo']->comarca_serventia ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="analise" <?= $dados['processo']->status == 'analise' ? 'selected' : '' ?>>Em Análise</option>
                                                    <option value="intimacao" <?= $dados['processo']->status == 'intimacao' ? 'selected' : '' ?>>Em Intimação</option>
                                                    <option value="diligencia" <?= $dados['processo']->status == 'diligencia' ? 'selected' : '' ?>>Em Diligência</option>
                                                    <option value="aguardando" <?= $dados['processo']->status == 'aguardando' ? 'selected' : '' ?>>Aguardando Pagamento</option>
                                                    <option value="concluido" <?= $dados['processo']->status == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                                    <option value="arquivado" <?= $dados['processo']->status == 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Observações</label>
                                                <textarea name="observacoes" class="form-control" rows="3"><?= $dados['processo']->observacoes ?? '' ?></textarea>
                                            </div>
                                        </div>

                                        <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Responsável</label>
                                                <select name="responsavel_id" class="form-control">
                                                    <option value="">Selecione um responsável</option>
                                                    <?php foreach ($dados['responsaveis'] ?? [] as $responsavel): ?>
                                                        <option value="<?= $responsavel->id ?>" <?= ($dados['processo']->responsavel_id ?? 0) == $responsavel->id ? 'selected' : '' ?>>
                                                            <?= $responsavel->nome ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php if ($dados['processo']->status == 'concluido'): ?>
                                            <div class="col-md-6">
                                                <label class="form-label">Data de Conclusão</label>
                                                <input type="date" name="data_conclusao" class="form-control" 
                                                    value="<?= isset($dados['processo']->data_conclusao) ? date('Y-m-d', strtotime($dados['processo']->data_conclusao)) : '' ?>">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i> Salvar Alterações
                                                </button>
                                                <a href="<?= URL ?>/processos/visualizar/<?= $dados['processo']->id ?>" 
                                                    class="btn btn-secondary">
                                                    <i class="fas fa-times me-2"></i> Cancelar
                                                </a>
                                                
                                                <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                                                    <button type="button" class="btn btn-danger float-end" data-toggle="modal" data-target="#deleteModalProcessoEditar<?= $dados['processo']->id ?>">
                                                        <i class="fas fa-trash me-2"></i> Excluir Processo
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal de confirmação de exclusão do processo -->
<?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
    <div class="modal fade" id="deleteModalProcessoEditar<?= $dados['processo']->id ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Atenção!</strong> Você está prestes a excluir o processo:</p>
                    <p><strong>Número:</strong> <?= $dados['processo']->numero_processo ?></p>
                    <p><strong>Comarca:</strong> <?= $dados['processo']->comarca_serventia ?></p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> Esta ação excluirá permanentemente o processo e todos os seus registros relacionados (guias, partes, intimações, etc.) e não poderá ser desfeita.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="<?= URL ?>/processos/excluirProcesso/<?= $dados['processo']->id ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirmar Exclusão
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'app/Views/include/footer.php' ?>
