<?php include 'app/Views/include/nav.php' ?>
<main>
    <div class="content">
       
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Informações da Guia</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número da Guia:</strong> <?= $dados['guia']->numero_guia ?></p>
                                    <p><strong>Valor:</strong> R$ <?= number_format($dados['guia']->valor, 2, ',', '.') ?></p>
                                    <p><strong>Status Atual:</strong> <?= ucfirst($dados['guia']->status) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Data de Vencimento:</strong> <?= $dados['guia']->data_vencimento ? date('d/m/Y', strtotime($dados['guia']->data_vencimento)) : 'Não informada' ?></p>
                                    <p><strong>Data de Pagamento:</strong> <?= $dados['guia']->data_pagamento ? date('d/m/Y', strtotime($dados['guia']->data_pagamento)) : 'Não informada' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Resultado da Consulta</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong>Sucesso:</strong> <?= $dados['resultado']['sucesso'] ? 'Sim' : 'Não' ?></p>
                                    <?php if (!$dados['resultado']['sucesso']): ?>
                                        <p><strong>Mensagem de Erro:</strong> <?= $dados['resultado']['mensagem'] ?></p>
                                    <?php else: ?>
                                        <p><strong>Status Detectado:</strong> <?= $dados['resultado']['paga'] ? '<span class="text-success">PAGO</span>' : '<span class="text-danger">NÃO PAGO</span>' ?></p>
                                        <p><strong>Data de Pagamento Detectada:</strong> <?= $dados['resultado']['data_pagamento'] ? date('d/m/Y', strtotime($dados['resultado']['data_pagamento'])) : 'Não detectada' ?></p>
                                        <p><strong>Valor Detectado:</strong> <?= $dados['resultado']['valor'] ? 'R$ ' . number_format($dados['resultado']['valor'], 2, ',', '.') : 'Não detectado' ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Informações de Debug</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong>Arquivo de Log:</strong> <?= $dados['resultado']['debug']['arquivo_log'] ?></p>
                                    <p><strong>Arquivo Existe:</strong> <?= $dados['resultado']['debug']['existe_arquivo'] ? 'Sim' : 'Não' ?></p>
                                    <p><strong>Tamanho do Arquivo:</strong> <?= number_format($dados['resultado']['debug']['tamanho_arquivo'] / 1024, 2) ?> KB</p>
                                    <p><strong>Data da Consulta:</strong> <?= $dados['resultado']['debug']['data_consulta'] ?></p>

                                    <?php if ($dados['resultado']['debug']['existe_arquivo']): ?>
                                        <div class="mt-4">
                                            <h4>Conteúdo do HTML da Resposta:</h4>
                                            <div class="bg-light p-3" style="max-height: 400px; overflow-y: auto;">
                                                <pre><?= htmlspecialchars(file_get_contents($dados['resultado']['debug']['arquivo_log'])) ?></pre>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?= URL ?>/processos/visualizar/<?= $dados['guia']->processo_id ?>" class="btn btn-secondary">Voltar</a>
                        <a href="<?= URL ?>/processos/consultarStatusGuia/<?= $dados['guia']->id ?>" class="btn btn-info">Consultar Novamente</a>
                        <?php if (!$dados['resultado']['paga']): ?>
                            <a href="<?= URL ?>/processos/marcarGuiaPaga/<?= $dados['guia']->id ?>" class="btn btn-success" onclick="return confirm('Tem certeza que deseja marcar esta guia como PAGA manualmente?');">Marcar como Paga</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>
</main>

<?php include APP . '/Views/include/footer.php' ?>