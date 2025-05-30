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
                    <?= Helper::mensagem('uploadtxt') ?>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-file-upload me-2"></i> Upload de Arquivos TXT
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard/inicial">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Upload de Arquivos TXT</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Alertas dinâmicos -->
                    <div id="alert-container"></div>
                    
                    <!-- Cards de Upload -->
                    <div class="row">
                        <?php foreach ($dados['arquivos'] as $arquivo): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-alt me-2"></i> <?= $arquivo[0] ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text text-muted mb-3"><?= $arquivo[3] ?></p>
                                        
                                        <form method="POST" action="<?= URL ?>/<?= $arquivo[1] ?>" 
                                              enctype="multipart/form-data" class="upload-form">
                                            <div class="mb-3">
                                                <label for="<?= $arquivo[2] ?>" class="form-label">Selecione o arquivo TXT</label>
                                                <input type="file" class="form-control" 
                                                       id="<?= $arquivo[2] ?>" name="<?= $arquivo[2] ?>" 
                                                       accept=".txt" required>
                                                <div class="form-text">Apenas arquivos .txt são aceitos (máx. 70MB)</div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-upload me-2"></i> Enviar
                                            </button>
                                            
                                            <div class="progress mt-3" style="display: none;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: 0%" aria-valuenow="0" 
                                                     aria-valuemin="0" aria-valuemax="100">0%</div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Card de informações -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i> Informações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i> 
                                <strong>Sobre o upload de arquivos TXT:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Os arquivos devem estar no formato TXT com codificação ISO-8859-1.</li>
                                    <li>Cada linha deve conter os dados separados por # (hashtag).</li>
                                    <li>A primeira linha (cabeçalho) será ignorada durante a importação.</li>
                                    <li>O formato esperado é: número#comarca#movimentação#data#nome</li>
                                    <li>Arquivos muito grandes podem levar mais tempo para processamento.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.upload-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const progressBar = form.querySelector('.progress-bar');
            const progressContainer = form.querySelector('.progress');
            const button = form.querySelector('button[type="submit"]');
            
            // Configurações iniciais
            button.disabled = true;
            progressContainer.style.display = 'block';
            
            const xhr = new XMLHttpRequest();
            const formData = new FormData(form);

            // Atualização do progresso
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                    progressBar.setAttribute('aria-valuenow', percent);
                }
            });

            // Resposta do servidor
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showAlert(response.status, response.message);
                        
                        // Se for sucesso, limpar o campo de arquivo
                        if (response.status === 'success') {
                            form.reset();
                        }
                    } catch (error) {
                        console.error('Erro ao processar JSON:', error);
                        console.log('Resposta do servidor:', xhr.responseText);
                        showAlert('danger', 'Erro ao processar resposta do servidor');
                    }
                } else {
                    showAlert('danger', `Erro na requisição: ${xhr.statusText}`);
                }
                
                // Resetar interface
                setTimeout(() => {
                    progressContainer.style.display = 'none';
                    progressBar.style.width = '0%';
                    progressBar.textContent = '0%';
                    progressBar.setAttribute('aria-valuenow', 0);
                    button.disabled = false;
                }, 2000);
            });

            // Tratamento de erros
            xhr.addEventListener('error', function() {
                showAlert('danger', 'Erro na conexão com o servidor');
                button.disabled = false;
                progressContainer.style.display = 'none';
            });

            xhr.open('POST', form.action);
            xhr.send(formData);
        });
    });

    function showAlert(type, message) {
        const container = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.prepend(alertDiv);
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }
});
</script>

<?php include 'app/Views/include/footer.php' ?> 