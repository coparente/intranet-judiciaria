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
                    <?= Helper::mensagem('agenda') ?>
                    <?= Helper::mensagemSweetAlert('agenda') ?>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-tags me-2"></i> Gerenciar Categorias da Agenda
                            </h5>
                            <div>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalNovaCategoria">
                                    <i class="fas fa-plus me-1"></i> Nova Categoria
                                </button>
                                <a href="<?= URL ?>/agenda/index" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar à Agenda
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3">Categorias Cadastradas</h6>
                            
                            <?php if (empty($dados['categorias'])): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Nenhuma categoria cadastrada ainda.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nome</th>
                                                <th>Cor</th>
                                                <th>Descrição</th>
                                                <th>Status</th>
                                                <th>Criada em</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['categorias'] as $categoria): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($categoria->nome) ?></strong></td>
                                                    <td>
                                                        <span class="badge" style="background-color: <?= $categoria->cor ?>; color: white; padding: 8px 12px;">
                                                            <?= $categoria->cor ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($categoria->descricao ?? 'Sem descrição') ?></td>
                                                    <td>
                                                        <?php if ($categoria->ativo == 'S'): ?>
                                                            <span class="badge bg-success">Ativa</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inativa</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($categoria->created_at)) ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    data-toggle="modal" 
                                                                    data-target="#modalEditarCategoria<?= $categoria->id ?>" 
                                                                    title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    data-toggle="modal" 
                                                                    data-target="#modalExcluirCategoria<?= $categoria->id ?>" 
                                                                    title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal para Nova Categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1" role="dialog" aria-labelledby="modalNovaCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovaCategoriaLabel">
                    <i class="fas fa-plus"></i> Nova Categoria
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= URL ?>/agenda/criarCategoria" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="nome">Nome da Categoria <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="cor">Cor <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" class="form-control" id="cor" name="cor" value="#007bff" required style="width: 60px;">
                            <input type="text" class="form-control" id="cor_hex" value="#007bff" readonly>
                        </div>
                        <small class="form-text text-muted">Escolha uma cor para identificar eventos desta categoria</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="255"></textarea>
                        <small class="form-text text-muted">Descrição opcional para a categoria</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modais para Editar Categorias -->
<?php if (!empty($dados['categorias'])): ?>
    <?php foreach ($dados['categorias'] as $categoria): ?>
        <div class="modal fade" id="modalEditarCategoria<?= $categoria->id ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditarCategoriaLabel<?= $categoria->id ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarCategoriaLabel<?= $categoria->id ?>">
                            <i class="fas fa-edit"></i> Editar Categoria
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="<?= URL ?>/agenda/atualizarCategoria/<?= $categoria->id ?>" method="POST">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="nome_edit_<?= $categoria->id ?>">Nome da Categoria <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome_edit_<?= $categoria->id ?>" name="nome" 
                                       value="<?= htmlspecialchars($categoria->nome) ?>" required maxlength="100">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="cor_edit_<?= $categoria->id ?>">Cor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="cor_edit_<?= $categoria->id ?>" name="cor" 
                                           value="<?= $categoria->cor ?>" required style="width: 60px;">
                                    <input type="text" class="form-control" id="cor_hex_edit_<?= $categoria->id ?>" value="<?= $categoria->cor ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="descricao_edit_<?= $categoria->id ?>">Descrição</label>
                                <textarea class="form-control" id="descricao_edit_<?= $categoria->id ?>" name="descricao" 
                                          rows="3" maxlength="255"><?= htmlspecialchars($categoria->descricao ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="ativo_edit_<?= $categoria->id ?>">Status</label>
                                <select class="form-control" id="ativo_edit_<?= $categoria->id ?>" name="ativo">
                                    <option value="S" <?= $categoria->ativo == 'S' ? 'selected' : '' ?>>Ativa</option>
                                    <option value="N" <?= $categoria->ativo == 'N' ? 'selected' : '' ?>>Inativa</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Categoria
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modais para Excluir Categorias -->
<?php if (!empty($dados['categorias'])): ?>
    <?php foreach ($dados['categorias'] as $categoria): ?>
        <div class="modal fade" id="modalExcluirCategoria<?= $categoria->id ?>" tabindex="-1" role="dialog" aria-labelledby="modalExcluirCategoriaLabel<?= $categoria->id ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="modalExcluirCategoriaLabel<?= $categoria->id ?>">
                            <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Atenção!</strong> Você está prestes a excluir a categoria:</p>
                        <p><strong>Nome:</strong> <?= htmlspecialchars($categoria->nome) ?></p>
                        <p><strong>Cor:</strong> 
                            <span class="badge" style="background-color: <?= $categoria->cor ?>; color: white; padding: 4px 8px;">
                                <?= $categoria->cor ?>
                            </span>
                        </p>
                        
                        <?php
                        $totalEventos = 0;
                        // Verificar se existe método para contar eventos (será implementado)
                        ?>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i> 
                            Esta ação excluirá permanentemente esta categoria e não poderá ser desfeita.
                            <?php if ($totalEventos > 0): ?>
                                <br><strong>Atenção:</strong> Existem <?= $totalEventos ?> evento(s) usando esta categoria!
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <a href="<?= URL ?>/agenda/excluirCategoria/<?= $categoria->id ?>" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Excluir Categoria
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
// Sincronizar color picker com campo de texto
document.addEventListener('DOMContentLoaded', function() {
    // Para modal de nova categoria
    const corPicker = document.getElementById('cor');
    const corHex = document.getElementById('cor_hex');
    
    if (corPicker && corHex) {
        corPicker.addEventListener('change', function() {
            corHex.value = this.value;
        });
    }
    
    // Para modais de edição
    <?php if (!empty($dados['categorias'])): ?>
        <?php foreach ($dados['categorias'] as $categoria): ?>
            const corEditPicker<?= $categoria->id ?> = document.getElementById('cor_edit_<?= $categoria->id ?>');
            const corEditHex<?= $categoria->id ?> = document.getElementById('cor_hex_edit_<?= $categoria->id ?>');
            
            if (corEditPicker<?= $categoria->id ?> && corEditHex<?= $categoria->id ?>) {
                corEditPicker<?= $categoria->id ?>.addEventListener('change', function() {
                    corEditHex<?= $categoria->id ?>.value = this.value;
                });
            }
        <?php endforeach; ?>
    <?php endif; ?>
});
</script>

<?php include 'app/Views/include/footer.php' ?> 