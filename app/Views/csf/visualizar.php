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
                    <?= Helper::mensagem('csf') ?>
                    <?= Helper::mensagemSweetAlert('csf') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-home me-2"></i> Visita Técnica
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/csf/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                            <a href="<?= URL ?>/csf/editar/<?= $dados['visita']->id ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-2"></i> Editar
                            </a>
                            <a href="<?= URL ?>/csf/cadastrarParticipante/<?= $dados['visita']->id ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-user-plus me-2"></i> Adicionar Participante
                            </a>
                            <a href="<?= URL ?>/csf/gerarPDF/<?= $dados['visita']->id ?>" class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Gerar PDF
                            </a>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro ">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-info-circle me-2"></i> 
                                Data de Cadastro: <?= date("d/m/Y", strtotime($dados['visita']->cadastrado_em)) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <h4>Identificação do processo</h4>
                                    <hr>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <p><strong>Processo:</strong>
                                            <a href="https://projudi.tjgo.jus.br/BuscaProcesso?PaginaAtual=2&ProcessoNumero=<?= $dados['visita']->processo ?>"
                                            target="_blank"
                                            class="text-primary text-decoration-none" data-toggle="tooltip" title="Ir para Projudi">
                                            <?= $dados['visita']->processo ?>
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a> </p>
                                            <p><strong>Comarca:</strong> <?= $dados['visita']->comarca ?></p>
                                            <p><strong>Autor:</strong> <?= $dados['visita']->autor ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Réu:</strong> <?= $dados['visita']->reu ?></p>
                                            <p><strong>Proad:</strong> 
                                            <a href="https://proad-v2.tjgo.jus.br/proad/processo/cadastro?id=<?= $dados['visita']->proad ?>"
                                            target="_blank"
                                            class="text-primary text-decoration-none" data-toggle="tooltip" title="Ir para Proad">
                                            <?= $dados['visita']->proad ?>
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a></p>
                                        </div>
                                    </div>

                                    <h4>Identificação da área</h4>
                                    <hr>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <p><strong>Nome do Assentamento (Ocupação):</strong> <?= $dados['visita']->nome_ocupacao ?></p>
                                            <p><strong>Área ocupada:</strong> <?= $dados['visita']->area_ocupada ?></p>
                                            <p><strong>Energia Elétrica:</strong> <?= $dados['visita']->energia_eletrica ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Água Tratada:</strong> <?= $dados['visita']->agua_tratada ?></p>
                                            <p><strong>Área de Risco:</strong> <?= $dados['visita']->area_risco ?></p>
                                            <p><strong>Moradia:</strong> <?= $dados['visita']->moradia ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Calcular estatísticas
                    $total_participantes = count($dados['participantes']);
                    $total_pessoas = 0;
                    $total_vulneravel = 0;
                    $total_lote_vago = 0;
                    $total_auxilio = 0;
                    $total_mora_local = 0;

                    foreach ($dados['participantes'] as $participante) {
                        $total_pessoas += $participante->qtd_pessoas;
                        if ($participante->vulneravel == 'Sim' || $participante->vulneravel == 'sim') $total_vulneravel++;
                        if ($participante->lote_vago == 'Sim' || $participante->lote_vago == 'sim') $total_lote_vago++;
                        if ($participante->auxilio == 'Sim' || $participante->auxilio == 'sim') $total_auxilio++;
                        if ($participante->mora_local == 'Sim' || $participante->mora_local == 'sim') $total_mora_local++;
                    }
                    ?>

                    <div class="card mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title mb-0 text-white">Estatísticas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Famílias</h5>
                                            <h2><?= $total_participantes ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Pessoas</h5>
                                            <h2><?= $total_pessoas ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Vulneráveis</h5>
                                            <h2><?= $total_vulneravel ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Lotes Vagos</h5>
                                            <h2><?= $total_lote_vago ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Recebem Auxílio</h5>
                                            <h2><?= $total_auxilio ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Moram no Local</h5>
                                            <h2><?= $total_mora_local ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title text-white">Participantes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tabela" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="small">Nome</th>
                                            <th class="small">CPF</th>
                                            <th class="small">Contato</th>
                                            <th class="small">Idade</th>
                                            <th class="small">Qtd pessoas</th>
                                            <th class="small">Menores</th>
                                            <th class="small">Idosos</th>
                                            <th class="small">Deficiência</th>
                                            <th class="small">Gestante</th>
                                            <th class="small">Auxílios</th>
                                            <th class="small">Crianças na escola</th>
                                            <th class="small">Qtd trabalham</th>
                                            <th class="small">Vulnerável</th>
                                            <th class="small">L. Vago</th>
                                            <th class="small">M. local</th>
                                            <th class="small">Renda</th>
                                            <th class="small">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['participantes'])): ?>
                                            <tr>
                                                <td colspan="17" class="text-center">Nenhum participante cadastrado</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dados['participantes'] as $participante): ?>
                                                <tr data-toggle="tooltip" title="Descrição: <?= $participante->descricao ?>">
                                                    <td class="small"><?= $participante->nome ?></td>
                                                    <td class="small"><?= $participante->cpf ?></td>
                                                    <td class="small"><?= $participante->contato ?></td>
                                                    <td class="small"><?= $participante->idade ?></td>
                                                    <td class="small"><?= $participante->qtd_pessoas ?></td>
                                                    <td class="small"><?= $participante->menores ?></td>
                                                    <td class="small"><?= $participante->idosos ?></td>
                                                    <td class="small"><?= $participante->pessoa_deficiencia ?></td>
                                                    <td class="small"><?= $participante->gestante ?></td>
                                                    <td class="small"><?= $participante->auxilio ?></td>
                                                    <td class="small"><?= $participante->frequentam_escola ?></td>
                                                    <td class="small"><?= $participante->qtd_trabalham ?></td>
                                                    <td class="small"><?= $participante->vulneravel ?></td>
                                                    <td class="small"><?= $participante->lote_vago ?></td>
                                                    <td class="small"><?= $participante->mora_local ?></td>
                                                    <td class="small"><?= $participante->fonte_renda ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= URL ?>/csf/editarParticipante/<?= $participante->id ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $participante->id ?>, <?= $dados['visita']->id ?>)" data-toggle="tooltip" title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Atenção!</strong> Você está prestes a excluir este participante.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> Esta ação não poderá ser desfeita.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="btn-confirmar-exclusao" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para confirmar exclusão
    function confirmDelete(id, visitaId) {
        // Configurar o link de confirmação
        document.getElementById('btn-confirmar-exclusao').href = '<?= URL ?>/csf/excluirParticipante/' + id + '/' + visitaId;
        
        // Exibir o modal
        $('#deleteModal').modal('show');
    }
    
    // Inicializar DataTables
    $(document).ready(function() {
        $('#tabela').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "pageLength": 10,
            "order": [[0, 'asc']]
        });
        
        // Inicializar tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<?php include 'app/Views/include/footer.php' ?> 