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
                            <i class="fas fa-plus-circle me-2"></i> Cadastrar Novo Processo
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/processos/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro text-white">
                            <h5 class="mb-0 text-white"><i class="fas fa-file-alt me-2"></i> Cadastrar Novo Processo</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($dados['processo_existente']) && $dados['processo_existente']): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Atenção!</strong> Já existe um processo cadastrado com o número "<?= $dados['numero_processo'] ?>".
                                    <p class="mt-2">Se você continuar, um novo processo com o mesmo número será cadastrado, o que pode gerar duplicidade.</p>
                                    <div class="mt-3">
                                        <form method="post" action="<?= URL ?>/processos/cadastrar">
                                            <input type="hidden" name="numero_processo" value="<?= $dados['numero_processo'] ?>">
                                            <input type="hidden" name="comarca" value="<?= $dados['comarca'] ?>">
                                            <input type="hidden" name="confirmar_duplicado" value="1">

                                            <?php if (!empty($dados['guias'])): ?>
                                                <?php foreach ($dados['guias'] as $index => $guia): ?>
                                                    <input type="hidden" name="guias[<?= $index ?>][numero]" value="<?= $guia['numero'] ?>">
                                                    <input type="hidden" name="guias[<?= $index ?>][valor]" value="<?= $guia['valor'] ?>">
                                                    <input type="hidden" name="guias[<?= $index ?>][vencimento]" value="<?= $guia['vencimento'] ?>">
                                                    <input type="hidden" name="guias[<?= $index ?>][descricao]" value="<?= $guia['descricao'] ?? '' ?>">
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i> Continuar mesmo assim
                                            </button>
                                            <a href="<?= URL ?>/processos/listar" class="btn btn-secondary ms-2">
                                                <i class="fas fa-times me-2"></i> Cancelar
                                            </a>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Resto do formulário permanece igual... -->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box box-info">
                                        <div class="card-body">
                                            <form action="<?= URL ?>/processos/cadastrar" method="POST">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Número do Processo*</label>
                                                        <input type="text" name="numero_processo" id="n_processo" class="form-control <?= isset($dados['erros']['numero_processo']) ? 'is-invalid' : '' ?>"
                                                            value="<?= $dados['numero_processo'] ?? '' ?>" required>
                                                        <div class="invalid-feedback">
                                                            <?= $dados['erros']['numero_processo'] ?? '' ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Comarca*</label>
                                                        <input type="text" name="comarca" class="form-control <?= isset($dados['erros']['comarca']) ? 'is-invalid' : '' ?>"
                                                            value="<?= $dados['comarca'] ?? '' ?>" required>
                                                        <div class="invalid-feedback">
                                                            <?= $dados['erros']['comarca'] ?? '' ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Seção de Guias -->
                                                <div class="card mb-3">
                                                    <div class="card-header bg-light">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Guias de Pagamento</h5>
                                                            <button type="button" class="btn btn-sm btn-success" id="btnAdicionarGuia">
                                                                <i class="fas fa-plus"></i> Adicionar Guia
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (isset($dados['erros']['guias'])): ?>
                                                            <div class="alert alert-danger">
                                                                <?= $dados['erros']['guias'] ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div id="guiasContainer">
                                                            <button type="button" class="btn btn-sm btn-danger top-0 end-0 m-2 remover-guia">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            <!-- Primeira guia (padrão) -->
                                                            <div class="guia-item mb-3 p-3 border rounded position-relative">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Número da Guia*</label>
                                                                        <input type="text" name="guias[0][numero]" class="form-control" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Valor (R$)*</label>
                                                                        <input type="text" name="guias[0][valor]" class="form-control money" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Data de Vencimento*</label>
                                                                        <input type="date" name="guias[0][vencimento]" class="form-control" required>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-12">
                                                                        <label class="form-label">Descrição</label>
                                                                        <textarea name="guias[0][descricao]" class="form-control" rows="2"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12 text-end">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save me-2"></i> Cadastrar Processo
                                                        </button>
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

<?php include APP . '/Views/include/footer.php' ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa máscaras para campos monetários (se jQuery estiver disponível)
        // if (typeof $ !== 'undefined') {
        //     $('.money').mask('#.##0,00', {
        //         reverse: true
        //     });
        // }

        // Contador para IDs únicos de guias
        let guiaCounter = 1;

        // Função para adicionar nova guia
        const btnAdicionar = document.getElementById('btnAdicionarGuia');
        if (btnAdicionar) {
            btnAdicionar.addEventListener('click', function(e) {
                e.preventDefault();

                const guiasContainer = document.getElementById('guiasContainer');
                if (!guiasContainer) {
                    console.error('Container de guias não encontrado!');
                    return;
                }

                // Cria elemento div para a nova guia
                const novaGuia = document.createElement('div');
                novaGuia.className = 'guia-item mb-3 p-3 border rounded position-relative';

                // Define o HTML interno
                novaGuia.innerHTML = `
                    <button type="button" class="btn btn-sm btn-danger top-0 end-0 m-2 remover-guia">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Número da Guia*</label>
                            <input type="text" name="guias[${guiaCounter}][numero]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor (R$)*</label>
                            <input type="text" name="guias[${guiaCounter}][valor]" class="form-control money" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data de Vencimento*</label>
                            <input type="date" name="guias[${guiaCounter}][vencimento]" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="guias[${guiaCounter}][descricao]" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                `;

                // Adiciona ao container
                guiasContainer.appendChild(novaGuia);

                // Aplica máscara se jQuery estiver disponível
                // if (typeof $ !== 'undefined') {
                //     $(novaGuia).find('.money').mask('#.##0,00', {reverse: true});
                // }

                // Incrementa contador
                guiaCounter++;

                console.log('Nova guia adicionada. Total:', document.querySelectorAll('.guia-item').length);
            });
        } else {
            console.error('Botão de adicionar guia não encontrado!');
        }

        // Evento para remover guias (usando delegação)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remover-guia')) {
                const guiaItems = document.querySelectorAll('.guia-item');

                if (guiaItems.length > 1) {
                    e.target.closest('.guia-item').remove();
                    console.log('Guia removida. Total:', document.querySelectorAll('.guia-item').length);
                } else {
                    alert('É necessário manter pelo menos uma guia de pagamento.');
                }
            }
        });

        // Log inicial
        console.log('Script carregado. Elementos encontrados:');
        console.log('- Botão adicionar:', !!document.getElementById('btnAdicionarGuia'));
        console.log('- Container de guias:', !!document.getElementById('guiasContainer'));
        console.log('- Número inicial de guias:', document.querySelectorAll('.guia-item').length);
    });
</script>