
<!-- --------------------------------------------- TOPO --------------------------------------------- -->
<header class="main-header">

    <!-- --------------------------------------------- Barra de Navega&ccedil;&atilde;o --------------------------------------------- -->
    <nav class="navbar navbar-static-top cor-fundo-azul-extra-escuro" role="navigation">
        <div class="navbar-header">
            <!-- <a href="https://www.tjgo.jus.br/" target="_blank"><img src="<?= URL ?>/public/img/brasao-tjgo-branco.png" alt="mdo" width="240" height="50"
                    data-toggle="tooltip" title="Tribunal de Justiça do Estado de Goiás">
            </a> -->
            <!-- &emsp;&emsp;
        <a href="#"><img src="<?= URL ?>/public/img/150-anos_branco.png" alt="mdo" width="90" height="80"
                data-toggle="tooltip" title="TJGO - 150">
        </a> -->
        &emsp;&emsp;
            <a class="navbar-brand d-flex align-items-center" href="<?= URL ?>">
                <img src="<?= URL ?>/public/img/brasao-tjgo-branco.png"
                    alt="TJGO"
                    style="height: 60px; width: auto;"
                    class="me-2">
                <!-- <span class="d-none d-md-inline text-center"> Dir Judiciária</span> -->

            </a>

        </div> <!-- fim navbar-header -->
        <!-- Menu de navega&ccedil;&atilde;o -->
        <ul class="nav justify-content-end">

            <!-- Removido badge global de mensagens não lidas, pois agora a notificação é por conversa -->

            <!-- Acessibilidade -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle cor-texto-branco" data-toggle="dropdown" role="button"
                    aria-haspopup="true" aria-expanded="false" href="#" title="Acessibilidade" style="color: #ffffff !important; background-color: transparent !important;">
                    <i class="fas fa-universal-access me-1"></i> Acessibilidade
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" id="aumentarFonte" style="color: #212529 !important; background-color: transparent !important;">
                        <i class="fas fa-plus me-2"></i> Aumentar Fonte
                    </a>
                    <a class="dropdown-item" href="#" id="diminuirFonte" style="color: #212529 !important; background-color: transparent !important;">
                        <i class="fas fa-minus me-2"></i> Diminuir Fonte
                    </a>
                </div>
            </li>

            <!-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
            <li class="nav-item ">
                <div class="btn-group divTopServentia">
                    <button type="button" class="btn btn-default botaoPrimeiroTopServentias" tabindex="-1"
                        data-toggle="dropdown" aria-expanded="true">
                        <span>
                            <?= isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário' ?>
                            <br>
                            Perfil:
                            <?= isset($_SESSION['usuario_perfil']) ? ucfirst($_SESSION['usuario_perfil']) : 'N/A' ?>
                        </span>

                    </button>
                    <button type="button" class="btn btn-default dropdown-toggle " data-toggle="dropdown"
                        aria-expanded="false">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <ul class="menu">
                                <li style="list-style: none;">
                                    <ul class='ulServentiaTemplate'>
                                        <br>

                                        <li class="liUsuarioServentia">
                                            <h2 class="control-sidebar-subheading">
                                                <!-- Avisos
                                                <hr> -->
                                                <!-- Módulos com permissão -->
                                                <h2 class="control-sidebar-subheading mt-3">
                                                    Meus Módulos
                                                    <hr>
                                                </h2>
                                                <div class="modulos-permissao">
                                                    <?php if (isset($_SESSION['modulos']) && is_array($_SESSION['modulos']) && count($_SESSION['modulos']) > 0): ?>
                                                        <ul class="list-unstyled">
                                                            <?php foreach ($_SESSION['modulos'] as $modulo): ?>
                                                                <li class="mb-2">
                                                                    <a href="<?= URL ?>" class="d-flex align-items-center text-decoration-none">
                                                                        <i class="<?= $modulo['icone'] ?? 'fas fa-puzzle-piece' ?> me-2"></i>
                                                                        <?= $modulo['nome'] ?>
                                                                    </a>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p class="text-muted small">Nenhum módulo disponível.</p>
                                                    <?php endif; ?>
                                                </div>


                                            </h2>

                                        </li>

                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <br>
                        <li class="">
                            <div class="text-center mb-2">
                                <span class="text-muted d-block">Último acesso: <?= isset($_SESSION['ultimo_acesso']) ? date('d/m/Y H:i', $_SESSION['ultimo_acesso']) : 'Primeiro acesso' ?></span>
                            </div>
                            <div class="text-center">
                            <a class="btn btn-default btnsTopServentias" data-toggle="tooltip" title="Editar Perfil" href="<?= URL ?>/usuarios/editar/usuario/<?= $_SESSION['usuario_id'] ?>"><i
                                        class="fas fa-user me-2 "></i>
                                    Perfil
                                </a>
                            <a class="btn btn-danger btnsTopServentias" href="<?= URL ?>/login/sair" data-toggle="tooltip" title="Sair">
                                <i class="fa fa-fw fa-power-off me-2"></i> Sair
                            </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </li><!-- FIM das serventias -->
        </ul>


    </nav>
    <!-- Breadcrumb e Título da Página -->
    <div class="bg-light py-2 mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= URL ?>" class="text-decoration-none"><i class="fas fa-home"></i></a></li>
                            <?php
                            $url = trim($_SERVER['REQUEST_URI'], '/');
                            $segments = explode('/', $url);
                            $path = '';
                            foreach ($segments as $segment) {
                                if (!empty($segment)) {
                                    $path .= '/' . $segment;
                                    echo '<li class="breadcrumb-item">' . ucfirst($segment) . '</li>';
                                }
                            }
                            ?>
                        </ol>

                    </nav>
                </div>
            </div>
        </div>
    </div>
</header> <!-- fim TOPO -->