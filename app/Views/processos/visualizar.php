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
                            <i class="fas fa-gavel me-2"></i> Processo <?= $dados['processo']->numero_processo ?>
                        </h1>
                        <div class="text-end">
                            <a href="<?= URL ?>/processos/listar" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                            <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                <a href="<?= URL ?>/processos/editar/<?= $dados['processo']->id ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-2"></i> Editar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card de Informações do Processo -->
                    <div class="card mb-4  <?= $dados['processo']->status == 'arquivado' ? 'card-informacoes' : '' ?>">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i> Informações do Processo
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Informações básicas -->
                                <div class="col-md-6">
                                    <p>
                                        <strong>Número do Processo:</strong>
                                        <a href="https://projudi.tjgo.jus.br/BuscaProcesso?PaginaAtual=2&ProcessoNumero=<?= $dados['processo']->numero_processo ?>"
                                            target="_blank"
                                            class="text-primary text-decoration-none">
                                            <?= $dados['processo']->numero_processo ?>
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </p>
                                    <p><strong>Comarca:</strong> <?= $dados['processo']->comarca_serventia ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Data de Cadastro:</strong> <?= date('d/m/Y H:i', strtotime($dados['processo']->data_cadastro)) ?></p>
                                    <p><strong>Cadastrado por:</strong> <?= $dados['processo']->usuario_cadastro_nome ?></p>
                                    <p><strong>Responsável:</strong> <?= $dados['processo']->responsavel_nome ?? 'Não atribuído' ?></p>
                                    <p>
                                        <strong>Status:</strong>
                                        <span class="badge bg-<?= $dados['processo']->status == 'arquivado' ? 'light status-arquivado' : ($dados['processo']->status == 'concluido' ? 'success' : ($dados['processo']->status == 'analise' ? 'primary' : ($dados['processo']->status == 'intimacao' ? 'warning' : ($dados['processo']->status == 'diligencia' ? 'info' : ($dados['processo']->status == 'aguardando' ? 'secondary' : ($dados['processo']->status == 'arquivado' ? 'dark' : 'light')))))) ?>">
                                            <?= ucfirst($dados['processo']->status) ?>
                                        </span>
                                    </p>
                                </div>
                                <hr>
                                <!-- <div class="row">
                                    <div class="col-12 mt-4">
                                        <h5><i class="fas fa-user-tie"></i> Advogados do Processo</h5>
                                        <?php if (!empty($dados['advogados'])): ?>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($dados['advogados'] as $advogado): ?>
                                                    <div class="bg-light p-2 rounded d-flex align-items-center">
                                                        <span class="me-2"><?= $advogado->nome ?> (OAB: <?= $advogado->oab ?>)</span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">Nenhum advogado cadastrado.</p>
                                        <?php endif; ?>
                                    </div>
                                </div> -->
                                <hr>
                                <!-- Resumo de Guias -->
                                <div class="col-md-4 mt-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Guias de Pagamento</h6>
                                            <h3 class="mb-0"><?= count($dados['guias']) ?></h3>
                                            <small class="text-muted">
                                                <?= array_reduce($dados['guias'], function ($carry, $guia) {
                                                    return $carry + ($guia->status == 'pago' ? 1 : 0);
                                                }, 0) ?> pagas
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumo de Pendências -->
                                <div class="col-md-4 mt-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Pendências</h6>
                                            <h3 class="mb-0"><?= count($dados['pendencias']) ?></h3>
                                            <small class="text-muted">
                                                <?= array_reduce($dados['pendencias'], function ($carry, $pendencia) {
                                                    return $carry + ($pendencia->status == 'resolvido' ? 1 : 0);
                                                }, 0) ?> resolvidas
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumo de Intimações -->
                                <div class="col-md-4 mt-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Intimações</h6>
                                            <h3 class="mb-0"><?= count($dados['intimacoes']) ?></h3>
                                            <small class="text-muted">
                                                <?= array_reduce($dados['intimacoes'], function ($carry, $intimacao) {
                                                    return $carry + ($intimacao->status == 'efetivada' ? 1 : 0);
                                                }, 0) ?> cumpridas
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adicionar após o card de Informações do Processo -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-sticky-note"></i> Observações</h4>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalObservacao">
                                <?php if (empty($dados['processo']->observacoes)): ?>
                                    <i class="fas fa-plus-circle"></i> Adicionar Observação
                                <?php else: ?>
                                    <i class="fas fa-edit"></i> Editar Observação
                                <?php endif; ?>
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['processo']->observacoes)): ?>
                                <p class="mb-0"><?= nl2br($dados['processo']->observacoes) ?></p>
                            <?php else: ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma observação registrada.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Seção de Partes -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Partes do Processo</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovaParte">
                                <i class="fas fa-plus-circle"></i> Nova Parte
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['partes'])): ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma parte cadastrada.
                                </p>
                            <?php else: ?>
                                <?php foreach ($dados['partes'] as $parte): ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-2">
                                                    <?= ucfirst($parte->tipo) ?>: <?= $parte->nome ?>
                                                </h6>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-toggle="modal"
                                                        data-target="#modalEditarParte<?= $parte->id ?>"
                                                        title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            data-toggle="modal"
                                                            data-target="#deleteModalParte<?= $parte->id ?>"
                                                            title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <p class="card-text mb-1">
                                                <strong><?= $parte->tipo_documento ?>:</strong> <?= $parte->documento ?>
                                            </p>
                                            <?php if ($parte->telefone): ?>
                                                <p class="card-text mb-1">
                                                    <strong>Telefone:</strong> <?= $parte->telefone ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($parte->email): ?>
                                                <p class="card-text mb-1">
                                                    <strong>Email:</strong> <?= $parte->email ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($parte->endereco): ?>
                                                <p class="card-text mb-1">
                                                    <strong>Endereço:</strong> <?= $parte->endereco ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Após o card de informações do processo, adicionar novo card para linha do tempo -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-history"></i> Linha do Tempo (Movimentações)</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['movimentacoes'])): ?>
                                <!-- The time line -->
                                <ul class="timeline">
                                    <?php
                                    $currentDate = null;

                                    foreach ($dados['movimentacoes'] as $mov):
                                        $dataMovimentacao = date("d/m/Y", strtotime($mov->data_movimentacao));

                                        // Verifica se a data mudou para adicionar um novo rótulo de data
                                        if ($dataMovimentacao != $currentDate):
                                    ?>
                                            <li class="time-label">
                                                <span class="bg-light-blue"><i class="fas fa-calendar-alt"></i> <?= $dataMovimentacao ?></span>
                                            </li>
                                        <?php
                                            $currentDate = $dataMovimentacao;
                                        endif;
                                        ?>
                                        <li>
                                            <i class="fas fa-<?= $mov->tipo == 'concluido' ? 'check' : ($mov->tipo == 'analise' ? 'search' : ($mov->tipo == 'intimacao' ? 'bell' : ($mov->tipo == 'diligencia' ? 'tasks' : ($mov->tipo == 'aguardando' ? 'clock' : 'file')))) ?> bg-<?= $mov->tipo == 'concluido' ? 'success' : ($mov->tipo == 'analise' ? 'primary' : ($mov->tipo == 'intimacao' ? 'warning' : ($mov->tipo == 'diligencia' ? 'info' : ($mov->tipo == 'aguardando' ? 'secondary' : 'dark')))) ?>"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fas fa-clock"></i> <?= date("H:i", strtotime($mov->data_movimentacao)) ?></span>
                                                <h3 class="timeline-header"><a href="#"><?= $mov->usuario_nome ?></a> - <?= ucfirst($mov->tipo) ?></h3>
                                                <div class="timeline-body">
                                                    <?= $mov->descricao ?>
                                                    <?php if ($mov->prazo): ?>
                                                        <br>
                                                        <small class="text-danger">
                                                            <i class="fas fa-calendar-day"></i> Prazo: <?= date('d/m/Y', strtotime($mov->prazo)) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                    <!-- Fim da timeline -->
                                    <li>
                                        <i class="fas fa-clock bg-gray"></i>
                                    </li>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma movimentação registrada.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card de Advogados -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-user-tie"></i> Advogados do Processo</h4>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAdicionarAdvogado">
                                <i class="fas fa-plus-circle"></i> Adicionar Advogado
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['advogados'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>OAB</th>
                                                <th width="100">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['advogados'] as $advogado): ?>
                                                <tr>
                                                    <td><?= $advogado->nome ?></td>
                                                    <td><?= $advogado->oab ?></td>
                                                    <td>
                                                        <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                data-toggle="modal"
                                                                data-target="#modalExcluirAdvogado<?= $advogado->id ?>"
                                                                title="Excluir Advogado">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">
                                    <i class="fas fa-info-circle"></i> Nenhum advogado cadastrado para este processo.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Card de Guias -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Guias de Pagamento</h5>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovaGuia">
                                    <i class="fas fa-plus-circle"></i> Nova Guia
                                </button>
                                <!-- <a href="<?= URL ?>/processos/consultarTodasGuiasProcesso/<?= $dados['processo']->id ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-sync-alt"></i> Consultar Todas
                                </a> -->
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dados['guias'])): ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma guia cadastrada.
                                </p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nº Guia</th>
                                                <th>Parte Responsável</th>
                                                <th>Valor</th>
                                                <th>Vencimento</th>
                                                <th>Status</th>
                                                <th>Data Pagamento</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['guias'] as $guia): ?>
                                                <tr>
                                                    <td><?= $guia->numero_guia ?></td>
                                                    <td>
                                                        <?php if ($guia->parte_nome): ?>
                                                            <?= $guia->parte_nome ?><br>
                                                            <small class="text-muted">
                                                                <?= ucfirst($guia->parte_tipo) ?> - <?= $guia->parte_documento ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Não definido</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>R$ <?= number_format($guia->valor, 2, ',', '.') ?></td>
                                                    <td><?= date('d/m/Y', strtotime($guia->data_vencimento)) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $guia->status == 'pago' ? 'success' : ($guia->status == 'pendente' ? 'warning' : ($guia->status == 'vencido' ? 'warning' : 'info')) ?>">
                                                            <?= ucfirst($guia->status) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($guia->data_pagamento)) ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <?php if ($guia->status !== 'pago' && ($_SESSION['usuario_perfil'] === 'admin' || $_SESSION['usuario_perfil'] === 'analista')): ?>
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Marcar como paga manualmente" onclick="confirmarMarcarPaga(<?= $guia->id ?>);">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalEditarGuia<?= $guia->id ?>" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModalGuia<?= $guia->id ?>" title="Excluir">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
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

                    <!-- Card de Pendências -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-exclamation-circle"></i> Pendências</h4>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovaPendencia">
                                <i class="fas fa-plus-circle"></i> Nova Pendência
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['pendencias'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descrição</th>
                                                <th>Status</th>
                                                <th>Data Cadastro</th>
                                                <th>Data Resolução</th>
                                                <th width="100">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['pendencias'] as $pendencia): ?>
                                                <tr>
                                                    <td><?= ucfirst($pendencia->tipo_pendencia) ?></td>
                                                    <td><?= $pendencia->descricao ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $pendencia->status == 'resolvido' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($pendencia->status) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($pendencia->data_cadastro)) ?></td>
                                                    <td><?= $pendencia->data_resolucao ? date('d/m/Y', strtotime($pendencia->data_resolucao)) : '-' ?></td>
                                                    <td class="text-nowrap">
                                                        <button type="button" class="btn btn-sm btn-warning d-inline-block me-1"
                                                            data-toggle="modal"
                                                            data-target="#modalEditarPendencia<?= $pendencia->id ?>"
                                                            title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger d-inline-block"
                                                                data-toggle="modal"
                                                                data-target="#deleteModalPendencia<?= $pendencia->id ?>"
                                                                title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma pendência cadastrada.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Intimações -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-bell"></i> Intimações</h4>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalIntimacao">
                                <i class="fas fa-plus-circle"></i> Nova Intimação
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['intimacoes'])): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Data Envio</th>
                                                <th>Tipo</th>
                                                <th>Destinatário</th>
                                                <th>Prazo</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['intimacoes'] as $int): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($int->data_envio)) ?></td>
                                                    <td>
                                                        <?php
                                                        $tipos = [
                                                            'whatsapp' => 'Intimação por WhatsApp',
                                                            'ecartas' => 'Intimação por Carta (e-Cartas)',
                                                            'projudi' => 'Intimação Eletrônica (Projudi)',
                                                            'email' => 'Intimação por E-mail'
                                                        ];
                                                        echo $tipos[$int->tipo_intimacao] ?? ucfirst($int->tipo_intimacao);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?= $int->parte_nome ?>
                                                        <?php if (!empty($int->parte_tipo)): ?>
                                                            <span class="badge bg-info">
                                                                <?= ucfirst($int->parte_tipo) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($int->prazo)) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?=
                                                                                $int->status == 'efetivada' ? 'success' : ($int->status == 'enviada' ? 'info' : ($int->status == 'pendente' ? 'warning' : ($int->status == 'nao_enviada' ? 'danger' : ($int->status == 'concluido' ? 'success' : 'secondary'))))
                                                                                ?>">
                                                            <?= str_replace('_', ' ', ucfirst($int->status)) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalAnexo<?= $int->id ?>">
                                                            <i class="fas fa-paperclip"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                            data-toggle="modal"
                                                            data-target="#modalEditarIntimacao<?= $int->id ?>"
                                                            title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] == 'admin' || $_SESSION['usuario_perfil'] == 'analista')): ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                data-toggle="modal"
                                                                data-target="#deleteModalIntimacao<?= $int->id ?>"
                                                                title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma intimação cadastrada.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Histórico de Movimentações -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-history"></i> Histórico de Movimentações</h4>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovaMovimentacao">
                                <i class="fas fa-plus-circle"></i> Nova Movimentação
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dados['movimentacoes'])): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Data/Hora</th>
                                                <th>Tipo</th>
                                                <th>Descrição</th>
                                                <th>Prazo</th>
                                                <th>Usuário</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados['movimentacoes'] as $mov): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($mov->data_movimentacao)) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $mov->tipo == 'conclusao' ? 'success' : ($mov->tipo == 'analise' ? 'primary' : ($mov->tipo == 'intimacao' ? 'warning' : 'info')) ?>">
                                                            <?= ucfirst($mov->tipo) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $mov->descricao ?></td>
                                                    <td><?= $mov->prazo ? date('d/m/Y', strtotime($mov->prazo)) : '-' ?></td>
                                                    <td><?= $mov->usuario_nome ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma movimentação registrada.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- </main>
</div>
</div> -->

                </div>
            </div>
        </section>
    </div>
</main>

<!-- Incluir aqui todos os modais da página original -->

<?php include APP . '/Views/include/footer.php' ?>

<script>
    // // função para selecionar parte
    // document.querySelector('select[name="parte_id"]').addEventListener('change', function() {
    //     const selectedOption = this.options[this.selectedIndex];
    //     document.getElementById('destinatario').value = selectedOption.dataset.nome;
    // });
</script>

<!-- Modal Nova Movimentação -->
<div class="modal fade" id="modalNovaMovimentacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Nova Movimentação
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/movimentar/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Movimentação*</label>
                        <select name="tipo" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="analise">Análise</option>
                            <option value="intimacao">Intimação</option>
                            <option value="pagamento">Pagamento</option>
                            <option value="impugnacao">Impugnação</option>
                            <option value="conclusao">Conclusão</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prazo</label>
                        <input type="date" name="prazo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição*</label>
                        <textarea name="descricao" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Movimentação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Observação -->
<div class="modal fade" id="modalObservacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note"></i> Observações do Processo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/atualizarObservacoes/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="5"><?= $dados['processo']->observacoes ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Observações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Advogado -->
<?php foreach ($dados['advogados'] as $advogado): ?>
    <div class="modal fade" id="modalExcluirAdvogado<?= $advogado->id ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o advogado <strong><?= $advogado->nome ?></strong> (OAB: <?= $advogado->oab ?>)?</p>
                    <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="<?= URL ?>/processos/excluirAdvogado/<?= $advogado->id ?>" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de Confirmação de Exclusão de Guia -->
<?php foreach ($dados['guias'] as $guia): ?>
    <div class="modal fade" id="deleteModalGuia<?= $guia->id ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a guia <strong><?= $guia->numero_guia ?></strong> no valor de <strong>R$ <?= number_format($guia->valor, 2, ',', '.') ?></strong>?</p>
                    <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="<?= URL ?>/processos/excluirGuia/<?= $guia->id ?>" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal Editar Guia -->
<?php foreach ($dados['guias'] as $guia): ?>
    <div class="modal fade" id="modalEditarGuia<?= $guia->id ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Editar Guia de Pagamento
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/atualizarGuia/<?= $guia->id ?>" method="POST">
                    <div class="modal-body">
                        <!-- Campo de Seleção de Parte -->
                        <div class="form-floating mb-3">
                            <select class="form-control" id="parte_id_<?= $guia->id ?>" name="parte_id" required>
                                <option value="">Selecione uma parte</option>
                                <?php foreach ($dados['partes'] as $parte): ?>
                                    <option value="<?= $parte->id ?>" <?= $guia->parte_id == $parte->id ? 'selected' : '' ?>>
                                        <?= $parte->nome ?> (<?= ucfirst($parte->tipo) ?>) - <?= $parte->documento ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="parte_id_<?= $guia->id ?>">Parte Responsável</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="numero_guia_<?= $guia->id ?>" name="numero_guia" value="<?= $guia->numero_guia ?>" required>
                            <label for="numero_guia_<?= $guia->id ?>">Número da Guia</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" step="0.01" class="form-control" id="valor_<?= $guia->id ?>" name="valor" value="<?= $guia->valor ?>" required>
                            <label for="valor_<?= $guia->id ?>">Valor (R$)</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="data_vencimento_<?= $guia->id ?>" name="data_vencimento" value="<?= $guia->data_vencimento ?>" required>
                            <label for="data_vencimento_<?= $guia->id ?>">Data de Vencimento</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="data_pagamento_<?= $guia->id ?>" name="data_pagamento" value="<?= $guia->data_pagamento ?>">
                            <label for="data_pagamento_<?= $guia->id ?>">Data de Pagamento</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-control" id="status_<?= $guia->id ?>" name="status" required>
                                <option value="pendente" <?= $guia->status == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="pago" <?= $guia->status == 'pago' ? 'selected' : '' ?>>Pago</option>
                                <option value="vencido" <?= $guia->status == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                <option value="cancelado" <?= $guia->status == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                            <label for="status_<?= $guia->id ?>">Status</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="observacao_<?= $guia->id ?>" name="observacao" style="height: 100px"><?= $guia->observacao ?></textarea>
                            <label for="observacao_<?= $guia->id ?>">Observação</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de Confirmação de Exclusão de Pendência -->
<?php foreach ($dados['pendencias'] as $pendencia): ?>
    <div class="modal fade" id="deleteModalPendencia<?= $pendencia->id ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir esta pendência do tipo <strong><?= str_replace('_', ' ', ucfirst($pendencia->tipo_pendencia)) ?></strong>?</p>
                    <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="<?= URL ?>/processos/excluirPendencia/<?= $pendencia->id ?>" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal Editar Pendência -->
<?php foreach ($dados['pendencias'] as $pendencia): ?>
    <div class="modal fade" id="modalEditarPendencia<?= $pendencia->id ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Editar Pendência
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/atualizarPendencia/<?= $pendencia->id ?>" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Pendência*</label>
                            <select name="tipo_pendencia" class="form-control" required>
                                <option value="documentacao" <?= $pendencia->tipo_pendencia == 'documentacao' ? 'selected' : '' ?>>Documentação</option>
                                <option value="pagamento" <?= $pendencia->tipo_pendencia == 'pagamento' ? 'selected' : '' ?>>Pagamento</option>
                                <option value="diligencia" <?= $pendencia->tipo_pendencia == 'diligencia' ? 'selected' : '' ?>>Diligência</option>
                                <option value="outros" <?= $pendencia->tipo_pendencia == 'outros' ? 'selected' : '' ?>>Outros</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição*</label>
                            <textarea name="descricao" class="form-control" rows="3" required><?= $pendencia->descricao ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status*</label>
                            <select name="status" class="form-control" required>
                                <option value="pendente" <?= $pendencia->status == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="resolvido" <?= $pendencia->status == 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
                                <option value="cancelado" <?= $pendencia->status == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal Nova Parte -->
<div class="modal fade" id="modalNovaParte" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nova Parte</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/cadastrarParte/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo*</label>
                        <select name="tipo" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="autor">Autor</option>
                            <option value="reu">Réu</option>
                            <option value="terceiro">Terceiro Interessado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome*</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Documento*</label>
                            <select name="tipo_documento" class="form-control" required>
                                <option value="cpf">CPF</option>
                                <option value="cnpj">CNPJ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número do Documento*</label>
                            <input type="text" name="documento" id="cpfcnpj" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="tel" name="telefone" id="telefone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <textarea name="endereco" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Parte -->
<?php foreach ($dados['partes'] as $parte): ?>
    <div class="modal fade" id="modalEditarParte<?= $parte->id ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit"></i>
                        Editar Parte
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/atualizarParte/<?= $parte->id ?>" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo*</label>
                            <select name="tipo" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="autor" <?= $parte->tipo == 'autor' ? 'selected' : '' ?>>Autor</option>
                                <option value="reu" <?= $parte->tipo == 'reu' ? 'selected' : '' ?>>Réu</option>
                                <option value="terceiro" <?= $parte->tipo == 'terceiro' ? 'selected' : '' ?>>Terceiro Interessado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome*</label>
                            <input type="text" name="nome" class="form-control" value="<?= $parte->nome ?>" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Documento*</label>
                                <select name="tipo_documento" class="form-control" required>
                                    <option value="cpf" <?= $parte->tipo_documento == 'cpf' ? 'selected' : '' ?>>CPF</option>
                                    <option value="cnpj" <?= $parte->tipo_documento == 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Número do Documento*</label>
                                <input type="text" name="documento" id="cpfcnpjEditar" class="form-control" value="<?= $parte->documento ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="tel" name="telefone" id="telefoneEditar" class="form-control" value="<?= $parte->telefone ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $parte->email ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Endereço</label>
                            <textarea name="endereco" class="form-control" rows="2"><?= $parte->endereco ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de Confirmação de Exclusão de Parte -->
<?php foreach ($dados['partes'] as $parte): ?>
    <div class="modal fade" id="deleteModalParte<?= $parte->id ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a parte <strong><?= $parte->nome ?></strong> (<?= strtoupper($parte->tipo) ?>)?</p>
                    <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="<?= URL ?>/processos/excluirParte/<?= $parte->id ?>" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de Confirmação de Exclusão de Intimação -->
<?php foreach ($dados['intimacoes'] as $int): ?>
    <div class="modal fade" id="deleteModalIntimacao<?= $int->id ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir esta intimação para <strong><?= $int->destinatario ?></strong>?</p>
                    <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="<?= URL ?>/processos/excluirIntimacao/<?= $int->id ?>" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de Edição de Intimação -->
<?php foreach ($dados['intimacoes'] as $int): ?>
    <div class="modal fade" id="modalEditarIntimacao<?= $int->id ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Intimação</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/atualizarIntimacao/<?= $int->id ?>" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Intimação*</label>
                            <select name="tipo_intimacao" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="whatsapp" <?= $int->tipo_intimacao == 'whatsapp' ? 'selected' : '' ?>>Intimação por WhatsApp</option>
                                <option value="ecartas" <?= $int->tipo_intimacao == 'ecartas' ? 'selected' : '' ?>>Intimação por Carta (e-Cartas)</option>
                                <option value="projudi" <?= $int->tipo_intimacao == 'projudi' ? 'selected' : '' ?>>Intimação Eletrônica (Projudi)</option>
                                <option value="email" <?= $int->tipo_intimacao == 'email' ? 'selected' : '' ?>>Intimação por E-mail</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parte*</label>
                            <select name="parte_id" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($dados['partes'] as $parte): ?>
                                    <option value="<?= $parte->id ?>" <?= $int->parte_id == $parte->id ? 'selected' : '' ?>>
                                        <?= $parte->nome ?> (<?= $parte->tipo ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prazo*</label>
                            <input type="date" name="prazo" class="form-control"
                                value="<?= date('Y-m-d', strtotime($int->prazo)) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status*</label>
                            <select name="status" class="form-control" required>
                                <option value="pendente" <?= $int->status == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="enviada" <?= $int->status == 'enviada' ? 'selected' : '' ?>>Enviada</option>
                                <option value="efetivada" <?= $int->status == 'efetivada' ? 'selected' : '' ?>>Efetivada</option>
                                <option value="nao_enviada" <?= $int->status == 'nao_enviada' ? 'selected' : '' ?>>Não Enviada</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal Nova Intimação -->
<div class="modal fade" id="modalIntimacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bell"></i> Nova Intimação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/registrarIntimacao/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Intimação*</label>
                        <select name="tipo_intimacao" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="whatsapp">Intimação por WhatsApp</option>
                            <option value="ecartas">Intimação por Carta (e-Cartas)</option>
                            <option value="projudi">Intimação Eletrônica (Projudi)</option>
                            <option value="email">Intimação por E-mail</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parte*</label>
                        <select name="parte_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($dados['partes'] as $parte): ?>
                                <option value="<?= $parte->id ?>"
                                    data-nome="<?= $parte->nome ?>"
                                    data-telefone="<?= $parte->telefone ?>"
                                    data-email="<?= $parte->email ?>">
                                    <?= $parte->nome ?>
                                    (<?= $parte->tipo ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="destinatario" id="destinatario">
                    <div class="mb-3">
                        <label class="form-label">Prazo*</label>
                        <input type="date" name="prazo" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Intimação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Anexo -->
<?php foreach ($dados['intimacoes'] as $int): ?>
    <div class="modal fade" id="modalAnexo<?= $int->id ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paperclip"></i> Enviar Anexo
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                        title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <form action="<?= URL ?>/processos/enviarAnexo/<?= $int->id ?>" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Anexo*</label>
                            <select name="tipo_anexo" class="form-control tipo-anexo" required>
                                <option value="">Selecione...</option>
                                <option value="document">Documento (PDF)</option>
                                <option value="text">Apenas Texto</option>
                            </select>
                        </div>
                        <div class="mb-3 campo-arquivo">
                            <label class="form-label">Arquivo*</label>
                            <input type="file" name="arquivo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensagem</label>
                            <textarea name="mensagem" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    document.querySelectorAll('.tipo-anexo').forEach(select => {
        select.addEventListener('change', function() {
            const campoArquivo = this.closest('.modal-body').querySelector('.campo-arquivo');
            const inputArquivo = campoArquivo.querySelector('input[type="file"]');

            if (this.value === 'text') {
                campoArquivo.style.display = 'none';
                inputArquivo.removeAttribute('required');
            } else {
                campoArquivo.style.display = 'block';
                inputArquivo.setAttribute('required', 'required');
            }
        });
    });
</script>

<!-- Modal Nova Guia -->
<div class="modal fade" id="modalNovaGuia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Guia de Pagamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/cadastrarGuia/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <!-- Campo de Seleção de Parte -->
                    <div class="form-floating mb-3">
                        <label for="parte_id">Parte Responsável</label>
                        <select class="form-control" id="parte_id" name="parte_id" required>
                            <option value="">Selecione uma parte</option>
                            <?php foreach ($dados['partes'] as $parte): ?>
                                <option value="<?= $parte->id ?>">
                                    <?= $parte->nome ?> (<?= ucfirst($parte->tipo) ?>) - <?= $parte->documento ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-floating mb-3">
                        <label for="numero_guia">Número da Guia</label>
                        <input type="text" class="form-control" id="n_guia" name="numero_guia" required>
                    </div>
                    <div class="form-floating mb-3">
                        <label for="valor">Valor (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                    </div>
                    <div class="form-floating mb-3">
                        <label for="data_vencimento">Data de Vencimento</label>
                        <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" required>
                    </div>
                    <div class="form-floating mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pendente">Pendente</option>
                            <option value="pago">Pago</option>
                            <option value="vencido">Vencido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="form-floating mb-3">
                        <label for="observacao">Observação</label>
                        <textarea class="form-control" id="observacao" name="observacao" style="height: 100px"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nova Pendência -->
<div class="modal fade" id="modalNovaPendencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-circle"></i> Nova Pendência</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/cadastrarPendencia/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Pendência*</label>
                        <select name="tipo_pendencia" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="documentacao">Documentação</option>
                            <option value="pagamento">Pagamento</option>
                            <option value="diligencia">Diligência</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição*</label>
                        <textarea name="descricao" class="form-control" rows="3"
                            placeholder="Descreva detalhadamente a pendência..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Adicionar Advogado -->
<div class="modal fade" id="modalAdicionarAdvogado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-tie"></i> Adicionar Advogado
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar modal"
                    title="Fechar modal"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <form action="<?= URL ?>/processos/adicionarAdvogado/<?= $dados['processo']->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome do Advogado*</label>
                        <input type="text" name="nome" class="form-control <?= isset($dados['erros']['nome']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($dados['erros']['nome'])): ?>
                            <div class="invalid-feedback">
                                <?= $dados['erros']['nome'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número da OAB*</label>
                        <input type="text" name="oab" class="form-control <?= isset($dados['erros']['oab']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($dados['erros']['oab'])): ?>
                            <div class="invalid-feedback">
                                <?= $dados['erros']['oab'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<!-- Adicione este script no final da view -->
<script>
/**
 * Exibe um modal de confirmação para marcar uma guia como paga
 * 
 * @param {number} guiaId - ID da guia a ser marcada como paga
 */
function confirmarMarcarPaga(guiaId) {
    Swal.fire({
        title: 'Confirmar pagamento',
        text: 'Tem certeza que deseja marcar esta guia como PAGA manualmente?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Sim, marcar como paga',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redireciona para a URL de marcação de guia como paga
            window.location.href = '<?= URL ?>/processos/marcarGuiaPaga/' + guiaId;
        }
    });
}
</script>