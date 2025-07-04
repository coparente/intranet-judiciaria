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
                    <?= Helper::mensagem('chat') ?>
                    <?= Helper::mensagemSweetAlert('chat') ?>

                    <!-- Header da Página -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt me-2"></i>
                                        Gerenciar Mensagens Rápidas
                                    </h5>
                                    <small class="text-muted">Configure mensagens predefinidas para uso rápido no chat</small>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCriarMensagem">
                                        <i class="fas fa-plus me-2"></i>
                                        Nova Mensagem
                                    </button>
                                    <a href="<?= URL ?>/chat/index" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Voltar ao Chat
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Estatísticas -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total de Mensagens</span>
                                            <span class="info-box-number"><?= $dados['total_mensagens'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Mensagens Ativas</span>
                                            <span class="info-box-number"><?= $dados['mensagens_ativas'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-pause-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Mensagens Inativas</span>
                                            <span class="info-box-number"><?= $dados['total_mensagens'] - $dados['mensagens_ativas'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Mensagens -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Lista de Mensagens Rápidas
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($dados['mensagens'])): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhuma mensagem cadastrada</h5>
                                    <p class="text-muted">Clique em "Nova Mensagem" para criar sua primeira mensagem rápida</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="50">Ordem</th>
                                                <th width="60">Status</th>
                                                <th width="80">Ícone</th>
                                                <th>Título</th>
                                                <th>Conteúdo</th>
                                                <th width="150">Criado por</th>
                                                <th width="120">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mensagensTable">
                                            <?php foreach ($dados['mensagens'] as $mensagem): ?>
                                                <tr data-id="<?= $mensagem->id ?>">
                                                    <td class="text-center">
                                                        <span class="badge badge-secondary"><?= $mensagem->ordem ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($mensagem->ativo): ?>
                                                            <span class="badge badge-success">Ativa</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Inativa</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <i class="<?= htmlspecialchars($mensagem->icone) ?> fa-lg text-primary"></i>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($mensagem->titulo) ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="message-preview">
                                                            <?= htmlspecialchars(substr($mensagem->conteudo, 0, 100)) ?>
                                                            <?php if (strlen($mensagem->conteudo) > 100): ?>
                                                                <span class="text-muted">...</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($mensagem->criador_nome ?? 'Desconhecido') ?><br>
                                                            <?= date('d/m/Y H:i', strtotime($mensagem->criado_em)) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    onclick="editarMensagem(<?= $mensagem->id ?>)" 
                                                                    title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="excluirMensagem(<?= $mensagem->id ?>, '<?= htmlspecialchars($mensagem->titulo) ?>')" 
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

<!-- Modal Criar Mensagem -->
<div class="modal fade" id="modalCriarMensagem" tabindex="-1" aria-labelledby="modalCriarMensagemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/gerenciarMensagensRapidas" method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCriarMensagemLabel">
                        <i class="fas fa-plus me-2"></i>
                        Criar Nova Mensagem Rápida
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="255">
                        <small class="form-text text-muted">Nome identificador da mensagem (máx. 255 caracteres)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="conteudo" class="form-label">Conteúdo da Mensagem *</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="5" required></textarea>
                        <small class="form-text text-muted">Texto que será inserido no chat</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icone" class="form-label">Ícone</label>
                                <select class="form-control" id="icone" name="icone">
                                    <option value="fas fa-comment">💬 Comentário</option>
                                    <option value="fas fa-gavel">⚖️ Justiça</option>
                                    <option value="fas fa-info-circle">ℹ️ Informação</option>
                                    <option value="fas fa-phone">📞 Telefone</option>
                                    <option value="fas fa-envelope">✉️ E-mail</option>
                                    <option value="fas fa-clock">🕐 Horário</option>
                                    <option value="fas fa-building">🏢 Instituição</option>
                                    <option value="fas fa-user">👤 Pessoa</option>
                                    <option value="fas fa-star">⭐ Destaque</option>
                                    <option value="fas fa-warning">⚠️ Aviso</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="ordem" class="form-label">Ordem</label>
                                <input type="number" class="form-control" id="ordem" name="ordem" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                                    <label class="form-check-label" for="ativo">
                                        Ativo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Criar Mensagem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Mensagem -->
<div class="modal fade" id="modalEditarMensagem" tabindex="-1" aria-labelledby="modalEditarMensagemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= URL ?>/chat/gerenciarMensagensRapidas" method="POST">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarMensagemLabel">
                        <i class="fas fa-edit me-2"></i>
                        Editar Mensagem Rápida
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="edit_titulo" name="titulo" required maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_conteudo" class="form-label">Conteúdo da Mensagem *</label>
                        <textarea class="form-control" id="edit_conteudo" name="conteudo" rows="5" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_icone" class="form-label">Ícone</label>
                                <select class="form-control" id="edit_icone" name="icone">
                                    <option value="fas fa-comment">💬 Comentário</option>
                                    <option value="fas fa-gavel">⚖️ Justiça</option>
                                    <option value="fas fa-info-circle">ℹ️ Informação</option>
                                    <option value="fas fa-phone">📞 Telefone</option>
                                    <option value="fas fa-envelope">✉️ E-mail</option>
                                    <option value="fas fa-clock">🕐 Horário</option>
                                    <option value="fas fa-building">🏢 Instituição</option>
                                    <option value="fas fa-user">👤 Pessoa</option>
                                    <option value="fas fa-star">⭐ Destaque</option>
                                    <option value="fas fa-warning">⚠️ Aviso</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_ordem" class="form-label">Ordem</label>
                                <input type="number" class="form-control" id="edit_ordem" name="ordem" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit_ativo" name="ativo">
                                    <label class="form-check-label" for="edit_ativo">
                                        Ativo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir Mensagem -->
<div class="modal fade" id="modalExcluirMensagem" tabindex="-1" aria-labelledby="modalExcluirMensagemLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirMensagemLabel">
                    <i class="fas fa-trash me-2 text-danger"></i>
                    Excluir Mensagem Rápida
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                </div>
                <p class="text-center">Tem certeza que deseja excluir a mensagem rápida:</p>
                <div class="alert alert-light text-center">
                    <strong id="nomeExcluirMensagem"></strong>
                </div>
                <p class="text-danger text-center">
                    <small><i class="fas fa-warning me-1"></i>Esta ação não pode ser desfeita.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmarExclusao">
                    <i class="fas fa-trash me-1"></i> Excluir Mensagem
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Dados das mensagens para JavaScript
const mensagensData = <?= json_encode($dados['mensagens']) ?>;

// Função para editar mensagem
function editarMensagem(id) {
    const mensagem = mensagensData.find(m => m.id == id);
    if (!mensagem) {
        alert('Mensagem não encontrada!');
        return;
    }
    
    // Preencher formulário de edição
    document.getElementById('edit_id').value = mensagem.id;
    document.getElementById('edit_titulo').value = mensagem.titulo;
    document.getElementById('edit_conteudo').value = mensagem.conteudo;
    document.getElementById('edit_icone').value = mensagem.icone;
    document.getElementById('edit_ordem').value = mensagem.ordem;
    document.getElementById('edit_ativo').checked = mensagem.ativo == 1;
    
    // Abrir modal
    $('#modalEditarMensagem').modal('show');
}

// Função para excluir mensagem
function excluirMensagem(id, titulo) {
    // Armazenar dados da mensagem a ser excluída
    window.mensagemParaExcluir = {
        id: id,
        titulo: titulo
    };
    
    document.getElementById('nomeExcluirMensagem').textContent = titulo;
    $('#modalExcluirMensagem').modal('show');
}

// Limitar caracteres no textarea
document.addEventListener('DOMContentLoaded', function() {
    // Event listener para o botão de confirmação de exclusão
    const btnConfirmarExclusao = document.getElementById('confirmarExclusao');
    
    if (btnConfirmarExclusao) {
        btnConfirmarExclusao.addEventListener('click', function() {
            if (window.mensagemParaExcluir) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= URL ?>/chat/gerenciarMensagensRapidas';
                
                const inputAcao = document.createElement('input');
                inputAcao.type = 'hidden';
                inputAcao.name = 'acao';
                inputAcao.value = 'excluir';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = window.mensagemParaExcluir.id;
                
                form.appendChild(inputAcao);
                form.appendChild(inputId);
                document.body.appendChild(form);
                
                // Fechar modal antes de enviar
                $('#modalExcluirMensagem').modal('hide');
                
                form.submit();
            }
        });
    }
    
    // Contador de caracteres nos textareas
    const textareas = document.querySelectorAll('textarea[name="conteudo"]');
    textareas.forEach(textarea => {
        const maxLength = 1000;
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted text-right';
        counter.innerHTML = `<span id="${textarea.id}_count">0</span>/${maxLength} caracteres`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById(`${textarea.id}_count`).textContent = count;
            
            if (count > maxLength) {
                counter.className = 'form-text text-danger text-right';
            } else if (count > maxLength * 0.8) {
                counter.className = 'form-text text-warning text-right';
            } else {
                counter.className = 'form-text text-muted text-right';
            }
        });
        
        // Trigger inicial
        textarea.dispatchEvent(new Event('input'));
    });
});
</script>

<style>
.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 4px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-top-left-radius: 4px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 4px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
    color: rgba(255,255,255,0.8);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 14px;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.bg-info { background-color: #17a2b8!important; }
.bg-success { background-color: #28a745!important; }
.bg-warning { background-color: #ffc107!important; }

.message-preview {
    max-width: 300px;
    word-wrap: break-word;
}

.table th {
    border-top: none;
}

.badge {
    font-size: 0.75em;
}
</style>

<?php include 'app/Views/include/footer.php' ?> 