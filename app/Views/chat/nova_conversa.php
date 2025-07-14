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
                                <i class="fas fa-plus-circle me-2"></i> Nova Conversa
                            </h5>
                            <div>
                                <a href="<?= URL ?>/chat/index" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/chat/novaConversa" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome" class="form-label">Nome do Contato: <span class="text-danger">*</span></label>
                                        <input type="text" name="nome" id="nome" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="numero" class="form-label">Número de Telefone: <span class="text-danger">*</span></label>
                                        <input type="text" name="numero" id="numero" class="form-control" placeholder="Ex: 55(62)99999-9999" required>
                                        <small class="form-text text-muted">
                                            Digite o código do país + DDD + número sem o nono dígito. Ex: 556296185598
                                        </small>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i> Salvar
                                    </button>
                                    <a href="<?= URL ?>/chat/index" class="btn btn-danger">
                                        <i class="fas fa-times me-1"></i> Cancelar
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Campo de telefone - apenas números, sem máscara
    const numeroInput = document.getElementById('numero');
    
    numeroInput.addEventListener('input', function(e) {
        // Remove tudo que não for número
        let value = e.target.value.replace(/\D/g, '');
        
        // Limita a 13 dígitos (código país + DDD + número)
        if (value.length > 13) {
            value = value.substring(0, 13);
        }
        
        e.target.value = value;
    });
    
    // Placeholder com exemplo sem máscara
    numeroInput.placeholder = 'Ex: 556296185795';
    
    // Posicionar cursor no final ao focar
    numeroInput.addEventListener('focus', function() {
        setTimeout(() => {
            this.setSelectionRange(this.value.length, this.value.length);
        }, 0);
    });
});
</script>

<?php include 'app/Views/include/footer.php' ?> 