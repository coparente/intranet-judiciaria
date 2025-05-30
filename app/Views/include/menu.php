<div class="box box-solid">
    <div class="box-header with-border box-infoClaro" id="linkPaginaInicial">
        <h3 class="box-title"> <a role="button" data-toggle="collapse" href="#menu01" aria-expanded="true" aria-controls="menu01">Menu</a> </h3>
    </div>
    <div id="menu01" class="show" data-parent="#menu01">
        <div class="box-body no-padding">
            <ul class="nav flex-column nav-stacked">
                <!-- Link para Dashboard -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between" href="<?= URL ?>/dashboard/inicial">
                        Dashboard <i class="fas fa-tachometer-alt"></i>
                    </a>
                </li>
                <!-- Mensagens -->
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <!-- Menu Dinâmico de Módulos -->
                    <?php if (isset($_SESSION['modulos']) && is_array($_SESSION['modulos'])): ?>
                        <?php foreach ($_SESSION['modulos'] as $modulo): ?>
                            <?php
                            // Verifica se estamos no módulo atual ou em algum de seus submódulos
                            $isCurrentModule = strpos($_SERVER['REQUEST_URI'], $modulo['rota']) !== false;

                            if (!$isCurrentModule && isset($modulo['submodulos'])) {
                                foreach ($modulo['submodulos'] as $submodulo) {
                                    if (strpos($_SERVER['REQUEST_URI'], $submodulo['rota']) !== false) {
                                        $isCurrentModule = true;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <li class="nav-item submenu">
                                <a class="nav-link d-flex justify-content-between"
                                    role="button"
                                    aria-expanded="<?= $isCurrentModule ? 'true' : 'false' ?>"
                                    data-toggle="collapse"
                                    aria-controls="modulo_<?= $modulo['id'] ?>"
                                    href="#modulo_<?= $modulo['id'] ?>">
                                    <span><i class="<?= $modulo['icone'] ?> me-2"></i> <?= $modulo['nome'] ?></span>
                                    <div>
                                        <span class="iconSubmenu"> <i class="fa fa-angle-left"></i> </span>
                                    </div>
                                </a>
                                <ul class="nav flex-column nav-stacked collapse <?= $isCurrentModule ? 'show' : '' ?>"
                                    id="modulo_<?= $modulo['id'] ?>"
                                    data-parent="#modulo_<?= $modulo['id'] ?>">
                                    <?php if (isset($modulo['submodulos']) && is_array($modulo['submodulos'])): ?>
                                        <?php foreach ($modulo['submodulos'] as $submodulo): ?>
                                            <li role="navigation">
                                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], $submodulo['rota']) !== false ? 'active' : '' ?>"
                                                    href="<?= URL . $submodulo['rota'] ?>">
                                                    <i class="<?= $submodulo['icone'] ?> me-2"></i> <?= $submodulo['nome'] ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Seção de Relatórios -->
                <!-- <?php if (isset($_SESSION['usuario_perfil']) && in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])): ?>
                    <li class="nav-item submenu">
                        <?php
                        // Verifica se está na seção de relatórios
                        $isRelatoriosSection = strpos($_SERVER['REQUEST_URI'], '/atividades/listar') !== false ||
                            strpos($_SERVER['REQUEST_URI'], '/estatisticas/atividades') !== false ||
                            strpos($_SERVER['REQUEST_URI'], '/relatorios/produtividade') !== false;
                        ?>
                        <a class="nav-link d-flex justify-content-between" role="button" aria-expanded="<?= $isRelatoriosSection ? 'true' : 'false' ?>" data-toggle="collapse" aria-controls="relatoriosCollapse" href="#relatoriosCollapse">
                            <span><i class="fas fa-chart-bar me-2"></i> Relatórios</span>
                            <div>
                                <span class="iconSubmenu"> <i class="fa fa-angle-left"></i> </span>
                            </div>
                        </a>
                        <ul class="nav flex-column nav-stacked collapse <?= $isRelatoriosSection ? 'show' : '' ?>" id="relatoriosCollapse" data-parent="#relatoriosCollapse">
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/atividades/listar') !== false ? 'active' : '' ?>" href="<?= URL ?>/atividades/listar">
                                    <i class="fas fa-history me-2"></i> Atividade de Usuários
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/estatisticas/atividades') !== false ? 'active' : '' ?>" href="<?= URL ?>/estatisticas/atividades">
                                    <i class="fas fa-chart-pie me-2"></i> Estatísticas
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/relatorios/produtividade') !== false ? 'active' : '' ?>" href="<?= URL ?>/relatorios/produtividade">
                                    <i class="fas fa-chart-line me-2"></i> Produtividade

                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?> -->

            </ul>
        </div><!-- fim box-body -->
    </div><!-- fim menu -->
</div><!-- fim box -->
<div class="card mt-3 shadow-sm text-center">
    <small class="text-muted mt-3">
        <i class="fas fa-info-circle me-1"></i> Status do Sistema
    </small>
    <div class="mt-2 mb-2">
        <span class="badge bg-success text-white">
            <i class="fas fa-check-circle me-1"></i> Online
        </span>
        <small class="text-muted ms-2">
            <?= date('d/m/Y H:i') ?>
        </small>
    </div>
</div>

<div class="card mt-3 shadow-sm">
    <div class="card-body">
        <h6 class="card-subtitle mb-2 text-muted">
            <i class="fas fa-lightbulb me-2 text-warning"></i> Dica Rápida
        </h6>
        <p class="card-text">
            Use o menu de acessibilidade no topo para ajustar o tamanho da fonte e alternar entre os temas claro e escuro.
        </p>
    </div>
</div>
<br>