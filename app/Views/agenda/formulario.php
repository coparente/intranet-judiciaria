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
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border" id="tituloMenu">
                                <h3 id="tabelas" class="box-title">
                                    <i class="fas fa-calendar-plus me-2"></i> <?= $dados['tituloPagina'] ?>
                                </h3>
                                <div class="pull-right">
                                    <a href="<?= URL ?>/agenda/index" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Voltar à Agenda
                                    </a>
                                </div>
                            </div>
                            <fieldset aria-labelledby="tituloMenu">
                                <div class="card-body">
                                    
                                    <form action="<?= URL ?>/agenda/salvarEvento" method="POST" id="formEvento">
                                        <?php if (isset($dados['evento'])): ?>
                                            <input type="hidden" name="evento_id" value="<?= $dados['evento']['id'] ?>">
                                        <?php endif; ?>

                                        <!-- Título do Evento -->
                                        <div class="form-group mb-3">
                                            <label for="titulo" class="form-label">
                                                <i class="fas fa-heading"></i> Título do Evento <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="titulo" 
                                                   name="titulo" 
                                                   value="<?= isset($dados['evento']) ? htmlspecialchars($dados['evento']['titulo']) : '' ?>"
                                                   placeholder="Digite o título do evento"
                                                   required>
                                        </div>

                                        <div class="row">
                                            <!-- Data e Hora de Início -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="data_inicio" class="form-label">
                                                        <i class="fas fa-calendar-day"></i> Data/Hora de Início <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="datetime-local" 
                                                           class="form-control" 
                                                           id="data_inicio" 
                                                           name="data_inicio"
                                                           value="<?= isset($dados['evento']) ? date('Y-m-d\TH:i', strtotime($dados['evento']['data_inicio'])) : (isset($dados['data_inicio']) && $dados['data_inicio'] ? date('Y-m-d\TH:i', strtotime($dados['data_inicio'])) : '') ?>"
                                                           required>
                                                </div>
                                            </div>

                                            <!-- Data e Hora de Fim -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="data_fim" class="form-label">
                                                        <i class="fas fa-calendar-day"></i> Data/Hora de Fim <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="datetime-local" 
                                                           class="form-control" 
                                                           id="data_fim" 
                                                           name="data_fim"
                                                           value="<?= isset($dados['evento']) ? date('Y-m-d\TH:i', strtotime($dados['evento']['data_fim'])) : (isset($dados['data_fim']) && $dados['data_fim'] ? date('Y-m-d\TH:i', strtotime($dados['data_fim'])) : '') ?>"
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Categoria -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="categoria_id" class="form-label">
                                                        <i class="fas fa-tags"></i> Categoria <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control" id="categoria_id" name="categoria_id" required>
                                                        <option value="">Selecione uma categoria</option>
                                                        <?php if (!empty($dados['categorias'])): ?>
                                                            <?php foreach ($dados['categorias'] as $categoria): ?>
                                                                <?php 
                                                                    // Compatibilidade objeto/array
                                                                    $id = isset($categoria->id) ? $categoria->id : $categoria['id'];
                                                                    $nome = isset($categoria->nome) ? $categoria->nome : $categoria['nome'];
                                                                    $cor = isset($categoria->cor) ? $categoria->cor : $categoria['cor'];
                                                                ?>
                                                                <option value="<?= $id ?>" 
                                                                        data-cor="<?= $cor ?>"
                                                                        <?= (isset($dados['evento']) && $dados['evento']['categoria_id'] == $id) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($nome) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="">❌ Nenhuma categoria disponível</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-circle" id="preview-cor" style="color: #6c757d;"></i>
                                                            <span id="preview-texto">Selecione uma categoria para ver a cor</span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="status" class="form-label">
                                                        <i class="fas fa-info-circle"></i> Status
                                                    </label>
                                                    <select class="form-control" id="status" name="status">
                                                        <option value="agendado" <?= (isset($dados['evento']) && $dados['evento']['status'] == 'agendado') ? 'selected' : '' ?>>
                                                            Agendado
                                                        </option>
                                                        <option value="confirmado" <?= (isset($dados['evento']) && $dados['evento']['status'] == 'confirmado') ? 'selected' : '' ?>>
                                                            Confirmado
                                                        </option>
                                                        <option value="cancelado" <?= (isset($dados['evento']) && $dados['evento']['status'] == 'cancelado') ? 'selected' : '' ?>>
                                                            Cancelado
                                                        </option>
                                                        <option value="concluido" <?= (isset($dados['evento']) && $dados['evento']['status'] == 'concluido') ? 'selected' : '' ?>>
                                                            Concluído
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Local -->
                                        <div class="form-group mb-3">
                                            <label for="local" class="form-label">
                                                <i class="fas fa-map-marker-alt"></i> Local
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="local" 
                                                   name="local" 
                                                   value="<?= isset($dados['evento']) ? htmlspecialchars($dados['evento']['local']) : '' ?>"
                                                   placeholder="Digite o local do evento">
                                        </div>

                                        <!-- Evento de Dia Inteiro -->
                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="evento_dia_inteiro" 
                                                       name="evento_dia_inteiro"
                                                       <?= (isset($dados['evento']) && $dados['evento']['evento_dia_inteiro'] == 'S') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="evento_dia_inteiro">
                                                    <i class="fas fa-clock"></i> Evento de dia inteiro
                                                </label>
                                                <small class="form-text text-muted d-block">
                                                    Marque esta opção se o evento durar o dia todo
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Descrição -->
                                        <div class="form-group mb-3">
                                            <label for="descricao" class="form-label">
                                                <i class="fas fa-align-left"></i> Descrição
                                            </label>
                                            <textarea class="form-control" 
                                                      id="descricao" 
                                                      name="descricao" 
                                                      rows="4" 
                                                      placeholder="Digite uma descrição detalhada do evento"><?= isset($dados['evento']) ? htmlspecialchars($dados['evento']['descricao']) : '' ?></textarea>
                                        </div>

                                        <!-- Observações -->
                                        <div class="form-group mb-3">
                                            <label for="observacoes" class="form-label">
                                                <i class="fas fa-sticky-note"></i> Observações
                                            </label>
                                            <textarea class="form-control" 
                                                      id="observacoes" 
                                                      name="observacoes" 
                                                      rows="3" 
                                                      placeholder="Digite observações adicionais sobre o evento"><?= isset($dados['evento']) ? htmlspecialchars($dados['evento']['observacoes']) : '' ?></textarea>
                                        </div>

                                        <!-- Botões -->
                                        <div class="form-group">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save"></i> 
                                                    <?= isset($dados['evento']) ? 'Atualizar Evento' : 'Salvar Evento' ?>
                                                </button>
                                                <a href="<?= URL ?>/agenda/index" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> Cancelar
                                                </a>
                                                <?php if (isset($dados['evento'])): ?>
                                                    <button type="button" class="btn btn-danger ms-auto" onclick="confirmarExclusao(<?= $dados['evento']['id'] ?>, '<?= htmlspecialchars($dados['evento']['titulo']) ?>')">
                                                        <i class="fas fa-trash"></i> Excluir Evento
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once APPROOT . '/Views/include/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview da cor da categoria
    const categoriaSelect = document.getElementById('categoria_id');
    const previewCor = document.getElementById('preview-cor');
    const previewTexto = document.getElementById('preview-texto');
    
    if (categoriaSelect && previewCor && previewTexto) {
        categoriaSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const cor = selectedOption.getAttribute('data-cor');
                if (cor) {
                    previewCor.style.color = cor;
                    previewTexto.textContent = `Cor da categoria: ${selectedOption.text}`;
                }
            } else {
                previewCor.style.color = '#6c757d';
                previewTexto.textContent = 'Selecione uma categoria para ver a cor';
            }
        });
        
        // Trigger inicial para mostrar cor se já tiver categoria selecionada
        if (categoriaSelect.value) {
            categoriaSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Validação de datas
    const formEvento = document.getElementById('formEvento');
    if (formEvento) {
        formEvento.addEventListener('submit', function(e) {
            const dataInicio = new Date(document.getElementById('data_inicio').value);
            const dataFim = new Date(document.getElementById('data_fim').value);
            
            if (dataInicio >= dataFim) {
                e.preventDefault();
                alert('A data de fim deve ser posterior à data de início');
                return false;
            }
        });
    }
    
    // Controle do checkbox "evento de dia inteiro"
    const checkboxDiaInteiro = document.getElementById('evento_dia_inteiro');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    
    if (checkboxDiaInteiro && dataInicio && dataFim) {
        checkboxDiaInteiro.addEventListener('change', function() {
            if (this.checked) {
                // Se marcado como dia inteiro, ajustar horários
                if (dataInicio.value) {
                    const data = dataInicio.value.split('T')[0];
                    dataInicio.value = data + 'T00:00';
                    dataFim.value = data + 'T23:59';
                }
            }
        });
    }
    
    // Auto-ajuste da data fim quando alterar data início
    if (dataInicio && dataFim) {
        dataInicio.addEventListener('change', function() {
            if (this.value && !dataFim.value) {
                const dataInicioObj = new Date(this.value);
                dataInicioObj.setHours(dataInicioObj.getHours() + 1);
                
                const ano = dataInicioObj.getFullYear();
                const mes = String(dataInicioObj.getMonth() + 1).padStart(2, '0');
                const dia = String(dataInicioObj.getDate()).padStart(2, '0');
                const hora = String(dataInicioObj.getHours()).padStart(2, '0');
                const minuto = String(dataInicioObj.getMinutes()).padStart(2, '0');
                
                dataFim.value = `${ano}-${mes}-${dia}T${hora}:${minuto}`;
            }
        });
    }
});

function confirmarExclusao(eventoId, titulo) {
    if (confirm(`Deseja realmente excluir o evento "${titulo}"?`)) {
        window.location.href = `<?= URL ?>/agenda/excluirEvento/${eventoId}`;
    }
}
</script>

<style>
.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    font-weight: 500;
}

.text-danger {
    font-weight: 600;
}

.card-body {
    background-color: #f8f9fa;
    border-radius: 8px;
}

#preview-cor {
    font-size: 1.2rem;
}

.d-flex.gap-2 > * {
    margin-right: 0.5rem;
}

.d-flex.gap-2 > *:last-child {
    margin-right: 0;
}
</style> 