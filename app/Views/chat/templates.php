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

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-file-alt me-2"></i> Gerenciar Templates
                            </h5>
                            <div>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalNovoTemplate">
                                    <i class="fas fa-plus me-1"></i> Novo Template
                                </button>
                                <a href="<?= URL ?>/chat/configuracoes" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <!-- Mensagem de Erro (se houver) -->
                            <?php if (!empty($dados['templateError'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> <?= $dados['templateError'] ?>
                                </div>
                            <?php endif; ?>

                            <!-- Templates carregados via PHP -->
                            <?php if (!empty($dados['templates']) && is_array($dados['templates'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Categoria</th>
                                                <th>Idioma</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['templates'] as $template): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($template['name'] ?? 'N/A') ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            <?= htmlspecialchars($template['category'] ?? 'N/A') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($template['language'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <?php 
                                                        $status = $template['status'] ?? 'UNKNOWN';
                                                        $badgeClass = '';
                                                        switch($status) {
                                                            case 'APPROVED':
                                                                $badgeClass = 'badge-success';
                                                                break;
                                                            case 'PENDING':
                                                                $badgeClass = 'badge-warning';
                                                                break;
                                                            case 'REJECTED':
                                                                $badgeClass = 'badge-danger';
                                                                break;
                                                            default:
                                                                $badgeClass = 'badge-secondary';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>">
                                                            <?= htmlspecialchars($status) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm" 
                                                                onclick="visualizarTemplate('<?= htmlspecialchars(json_encode($template), ENT_QUOTES) ?>')"
                                                                data-toggle="modal" data-target="#modalVisualizarTemplate">
                                                            <i class="fas fa-eye"></i> Ver
                                                        </button>
                                                        
                                                        <?php if ($status == 'APPROVED'): ?>
                                                            <button type="button" class="btn btn-warning btn-sm" 
                                                                    onclick="editarTemplate('<?= htmlspecialchars(json_encode($template), ENT_QUOTES) ?>')"
                                                                    data-toggle="modal" data-target="#modalEditarTemplate">
                                                                <i class="fas fa-edit"></i> Editar
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($status != 'APPROVED'): ?>
                                                            <button type="button" class="btn btn-danger btn-sm" 
                                                                    onclick="excluirTemplate('<?= htmlspecialchars($template['name'] ?? '') ?>')">
                                                                <i class="fas fa-trash"></i> Excluir
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <p class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Total de templates: <strong><?= count($dados['templates']) ?></strong>
                                    </p>
                                </div>
                                
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum template encontrado</h5>
                                    <p class="text-muted">Crie um novo template para começar</p>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNovoTemplate">
                                        <i class="fas fa-plus me-1"></i> Criar Template
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal Novo Template -->
<div class="modal fade" id="modalNovoTemplate" tabindex="-1" aria-labelledby="modalNovoTemplateLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formNovoTemplate" action="<?= URL ?>/chat/gerenciarTemplates" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovoTemplateLabel">Criar Novo Template</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Templates precisam ser aprovados pela Meta antes de serem utilizados.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="templateName" class="form-label">Nome do Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="templateName" name="name" required 
                                   placeholder="Ex: saudacao_inicial">
                            <small class="form-text text-muted">Apenas letras minúsculas, números e underline</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="templateCategory" class="form-label">Categoria <span class="text-danger">*</span></label>
                            <select class="form-control" id="templateCategory" name="category" required>
                                <option value="">Selecione uma categoria</option>
                                <option value="AUTHENTICATION">Autenticação</option>
                                <option value="MARKETING">Marketing</option>
                                <option value="UTILITY">Utilidade</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="templateLanguage" class="form-label">Idioma <span class="text-danger">*</span></label>
                            <select class="form-control" id="templateLanguage" name="language" required>
                                <option value="">Selecione um idioma</option>
                                <option value="pt_BR">Português (Brasil)</option>
                                <option value="en_US">Inglês (EUA)</option>
                                <option value="es_ES">Espanhol</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="templateHeader" class="form-label">Cabeçalho (Opcional)</label>
                        <input type="text" class="form-control" id="templateHeader" name="header" 
                               placeholder="Texto do cabeçalho" maxlength="60">
                        <small class="form-text text-muted">Máximo 60 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="templateBody" class="form-label">Corpo da Mensagem <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="templateBody" name="body" rows="4" required 
                                  placeholder="Texto principal da mensagem"></textarea>
                        <small class="form-text text-muted">Use {{1}}, {{2}}, etc. para variáveis</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="templateFooter" class="form-label">Rodapé (Opcional)</label>
                        <input type="text" class="form-control" id="templateFooter" name="footer" 
                               placeholder="Texto do rodapé" maxlength="60">
                        <small class="form-text text-muted">Máximo 60 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="criarTemplate()">
                        <i class="fas fa-save"></i> Criar Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizar Template -->
<div class="modal fade" id="modalVisualizarTemplate" tabindex="-1" aria-labelledby="modalVisualizarTemplateLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizarTemplateLabel">Detalhes do Template</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nome:</strong> <span id="visualizarNome">-</span></p>
                        <p><strong>Categoria:</strong> <span id="visualizarCategoria">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Idioma:</strong> <span id="visualizarIdioma">-</span></p>
                        <p><strong>Status:</strong> <span id="visualizarStatus">-</span></p>
                    </div>
                </div>
                
                <hr>
                
                <h6>Componentes:</h6>
                <div id="visualizarComponentes">
                    <!-- Componentes serão preenchidos via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Template -->
<div class="modal fade" id="modalEditarTemplate" tabindex="-1" aria-labelledby="modalEditarTemplateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarTemplateLabel">Editar Template</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editarInfo">
                    <!-- Informações sobre edição serão preenchidas via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Template -->
<div class="modal fade" id="modalExcluirTemplate" tabindex="-1" role="dialog" aria-labelledby="modalExcluirTemplateLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirTemplateLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o template <strong id="templateNameToDelete"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">
                    <i class="fas fa-trash me-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Templates carregados via PHP:', <?= json_encode($dados['templates'] ?? []) ?>);
});

// Função para visualizar template
function visualizarTemplate(templateData) {
    const template = JSON.parse(templateData);
    
    // Preencher modal de visualização
    document.getElementById('visualizarNome').textContent = template.name || 'N/A';
    document.getElementById('visualizarCategoria').textContent = template.category || 'N/A';
    document.getElementById('visualizarIdioma').textContent = template.language || 'N/A';
    document.getElementById('visualizarStatus').textContent = template.status || 'N/A';
    
    // Mostrar componentes
    const componentesDiv = document.getElementById('visualizarComponentes');
    componentesDiv.innerHTML = '';
    
    if (template.components && Array.isArray(template.components)) {
        template.components.forEach(component => {
            const div = document.createElement('div');
            div.className = 'mb-2 p-2 border rounded';
            div.innerHTML = `
                <strong>${component.type || 'N/A'}:</strong><br>
                <small class="text-muted">${component.text || 'Sem texto'}</small>
            `;
            componentesDiv.appendChild(div);
        });
    } else {
        componentesDiv.innerHTML = '<p class="text-muted">Nenhum componente encontrado</p>';
    }
}

// Função para editar template (apenas mostra informação)
function editarTemplate(templateData) {
    const template = JSON.parse(templateData);
    
    document.getElementById('editarInfo').innerHTML = `
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> Informação sobre Edição</h6>
            <p>Templates aprovados pela Meta não podem ser editados diretamente. Para modificar um template:</p>
            <ol>
                <li>Crie um novo template com as alterações desejadas</li>
                <li>Aguarde a aprovação da Meta</li>
                <li>Após aprovado, você pode excluir o template antigo se necessário</li>
            </ol>
            <p><strong>Template atual:</strong> ${template.name} (${template.status})</p>
        </div>
    `;
}

// Função para excluir template
function excluirTemplate(nomeTemplate) {
    if (!confirm(`Tem certeza que deseja excluir o template "${nomeTemplate}"?`)) {
        return;
    }

    // Criar form e submeter
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= URL ?>/chat/gerenciarTemplates';
    
    const acaoInput = document.createElement('input');
    acaoInput.type = 'hidden';
    acaoInput.name = 'acao';
    acaoInput.value = 'excluir';
    
    const nomeInput = document.createElement('input');
    nomeInput.type = 'hidden';
    nomeInput.name = 'nome_template';
    nomeInput.value = nomeTemplate;
    
    form.appendChild(acaoInput);
    form.appendChild(nomeInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Função para criar template
function criarTemplate() {
    const form = document.getElementById('formNovoTemplate');
    
    // Validar campos obrigatórios
    const nome = form.querySelector('[name="name"]').value.trim();
    const categoria = form.querySelector('[name="category"]').value.trim();
    const idioma = form.querySelector('[name="language"]').value.trim();
    const body = form.querySelector('[name="body"]').value.trim();
    
    if (!nome || !categoria || !idioma || !body) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
    }
    
    // Construir componentes
    const components = [];
    
    // Header (opcional)
    const header = form.querySelector('[name="header"]').value.trim();
    if (header) {
        components.push({
            type: 'HEADER',
            format: 'TEXT',
            text: header
        });
    }
    
    // Body (obrigatório)
    components.push({
        type: 'BODY',
        text: body
    });
    
    // Footer (opcional)
    const footer = form.querySelector('[name="footer"]').value.trim();
    if (footer) {
        components.push({
            type: 'FOOTER',
            text: footer
        });
    }
    
    // Criar input hidden para components
    let componentsInput = form.querySelector('[name="components"]');
    if (!componentsInput) {
        componentsInput = document.createElement('input');
        componentsInput.type = 'hidden';
        componentsInput.name = 'components';
        form.appendChild(componentsInput);
    }
    componentsInput.value = JSON.stringify(components);
    
    // Adicionar ação
    let acaoInput = form.querySelector('[name="acao"]');
    if (!acaoInput) {
        acaoInput = document.createElement('input');
        acaoInput.type = 'hidden';
        acaoInput.name = 'acao';
        form.appendChild(acaoInput);
    }
    acaoInput.value = 'criar';
    
    // Submeter form
    form.submit();
}
</script>

<?php include 'app/Views/include/footer.php' ?> 