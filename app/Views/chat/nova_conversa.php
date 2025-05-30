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
                                        <input type="text" name="numero" id="numero" class="form-control" placeholder="Ex: (62) 99999-9999" required>
                                        <small class="form-text text-muted">
                                            Digite apenas números ou no formato (XX) XXXXX-XXXX
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
    // Máscara para o campo de telefone
    const numeroInput = document.getElementById('numero');
    
    numeroInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length <= 11) {
            if (value.length > 2) {
                value = `(${value.substring(0, 2)}) ${value.substring(2)}`;
            }
            if (value.length > 10) {
                value = `${value.substring(0, 10)}-${value.substring(10)}`;
            }
        }
        
        e.target.value = value;
    });
});
</script>

<?php include 'app/Views/include/footer.php' ?> 