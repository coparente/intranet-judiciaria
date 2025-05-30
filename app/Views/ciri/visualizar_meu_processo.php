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
                    <?= Helper::mensagem('ciri') ?>
                    <?= Helper::mensagemSweetAlert('ciri') ?>

                    <div class="card">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-eye me-2"></i> Visualizar Meu Processo CIRI
                            </h5>
                            <div>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#concluirProcessoModal<?= $dados['processo']->id ?>">
                                    <i class="fas fa-check-circle me-1"></i> Concluir
                                </button>
                                <a href="<?= URL ?>/ciri/meusProcessos" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3">Informações do Processo</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p><strong>Número do Processo:</strong> 
                                    <a href="https://projudi.tjgo.jus.br/BuscaProcesso?PaginaAtual=2&ProcessoNumero=<?= $dados['processo']->numero_processo ?>"
                                            target="_blank"
                                            class="text-primary text-decoration-none" data-toggle="tooltip" title="Ir para Projudi">
                                            <?= $dados['processo']->numero_processo ?>
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p><strong>Comarca/Serventia:</strong> <?= $dados['processo']->comarca_serventia ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p><strong>Gratuidade de Justiça:</strong> <?= $dados['processo']->gratuidade_justica == 'sim' ? 'Sim' : 'Não' ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p><strong>Data da Atividade:</strong> <?= date('d/m/Y', strtotime($dados['processo']->data_atividade)) ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p><strong>Tipo de Ato:</strong> <?= $dados['processo']->tipo_ato_nome ?? 'Não definido' ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p><strong>Tipo de Intimação:</strong> <?= $dados['processo']->tipo_intimacao_nome ?? 'Não definido' ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p><strong>Status do Processo:</strong>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    switch ($dados['processo']->status_processo) {
                                        case 'pendente':
                                            $statusClass = 'badge bg-warning';
                                            $statusText = 'Pendente';
                                            break;
                                        case 'em_andamento':
                                            $statusClass = 'badge bg-primary';
                                            $statusText = 'Em Andamento';
                                            break;
                                        case 'concluido':
                                            $statusClass = 'badge bg-success';
                                            $statusText = 'Concluído';
                                            break;
                                        case 'cancelado':
                                            $statusClass = 'badge bg-danger';
                                            $statusText = 'Cancelado';
                                            break;
                                        case 'PROCESSO FINALIZADO':
                                            $statusClass = 'badge bg-success';
                                            $statusText = 'Processo Finalizado';
                                            break;
                                        case 'RETORNAR PARA ANÁLISE':
                                            $statusClass = 'badge bg-warning';
                                            $statusText = 'Retornar para Análise';
                                            break;
                                        case 'AGUARDANDO RESPOSTA DE WHATSAPP':
                                            $statusClass = 'badge bg-warning';
                                            $statusText = 'Aguardando Resposta de Whatsapp';
                                            break;  
                                        case 'AGUARDANDO RESPOSTA DE E-MAIL':
                                            $statusClass = 'badge bg-warning';
                                            $statusText = 'Aguardando Resposta de E-mail';
                                            break;
                                        case 'AGUARDANDO PROVIDÊNCIA':
                                            $statusClass = 'badge bg-warning';
                                            $statusText = 'Aguardando Provência';
                                            break;
                                        default:
                                            $statusClass = 'badge bg-secondary';
                                            $statusText = 'Não definido';
                                    }
                                    ?>
                                    <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p><strong>Data de Cadastro:</strong> <?= date('d/m/Y H:i', strtotime($dados['processo']->criado_em)) ?></p>
                                </div>
                            </div>

                            <?php if (!empty($dados['processo']->observacao_atividade)): ?>
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Observações</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <p><?= nl2br($dados['processo']->observacao_atividade) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Destinatários -->
                    <div class="card mt-4">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-users me-2"></i> Destinatários
                            </h5>
                            <a href="<?= URL ?>/ciri/adicionarMeuDestinatario/<?= $dados['processo']->id ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> Adicionar
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['destinatarios'])): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Nenhum destinatário cadastrado.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nome</th>
                                                <th>Telefone</th>
                                                <th>E-mail</th>
                                                <th width="120">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['destinatarios'] as $destinatario): ?>
                                                <tr>
                                                    <td><?= $destinatario->nome ?></td>
                                                    <td><?= $destinatario->telefone ?: 'Não informado' ?></td>
                                                    <td><?= $destinatario->email ?: 'Não informado' ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= URL ?>/ciri/editarMeuDestinatario/<?= $destinatario->id ?>" class="btn btn-sm btn-warning" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista' ): ?>
                                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteDestinatarioModal<?= $destinatario->id ?>" title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Modal de exclusão -->
                                                        <div class="modal fade" id="deleteDestinatarioModal<?= $destinatario->id ?>" tabindex="-1" aria-labelledby="deleteDestinatarioModalLabel<?= $destinatario->id ?>" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteDestinatarioModalLabel<?= $destinatario->id ?>">Confirmar Exclusão</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Tem certeza que deseja excluir o destinatário <strong><?= $destinatario->nome ?></strong>?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                        <a href="<?= URL ?>/ciri/excluirDestinatario/<?= $destinatario->id ?>" class="btn btn-danger">Excluir</a>
                                                                    </div>
                                                                </div>
                                                            </div>
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

                    <!-- Movimentações -->
                    <div class="card mt-4">
                        <div class="card-header cor-fundo-azul-escuro text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-history me-2"></i> Movimentações
                            </h5>
                            <!-- <a href="<?= URL ?>/ciri/adicionarMovimentacao/<?= $dados['processo']->id ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> Adicionar
                            </a> -->
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['movimentacoes'])): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Nenhuma movimentação registrada.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Data</th>
                                                <th>Descrição</th>
                                                <th>Usuário</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['movimentacoes'] as $movimentacao): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($movimentacao->data_movimentacao)) ?></td>
                                                    <td><?= $movimentacao->descricao ?></td>
                                                    <td><?= $movimentacao->nome_usuario ?? 'Sistema' ?></td>
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

<!-- Adicionar o modal de confirmação no final da página, antes do fechamento da tag </main> -->
<div class="modal fade" id="concluirProcessoModal<?= $dados['processo']->id ?>" tabindex="-1" role="dialog" aria-labelledby="concluirProcessoModalLabel<?= $dados['processo']->id ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white" id="concluirProcessoModalLabel<?= $dados['processo']->id ?>">
                    <i class="fas fa-check-circle me-2"></i> Confirmar Conclusão
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Atenção!</strong> Você está prestes a concluir o processo:</p>
                <p><strong>Número:</strong> <?= $dados['processo']->numero_processo ?></p>
                <p><strong>Comarca/Serventia:</strong> <?= $dados['processo']->comarca_serventia ?></p>
                <p><strong>Tipo de Ato:</strong> <?= $dados['processo']->tipo_ato_nome ?></p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Após a conclusão, o processo não poderá mais ser editado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="<?= URL ?>/ciri/concluirProcesso/<?= $dados['processo']->id ?>" class="btn btn-success">
                    <i class="fas fa-check-circle me-2"></i> Confirmar Conclusão
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/Views/include/footer.php' ?> 