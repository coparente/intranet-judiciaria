 <!-- teste box -->
 <div class="box box-solid">
   <div class="box-header with-border box-primaryClaro" id="linkPaginaInicial">
     <h3 class="box-title"> <a role="button" data-toggle="collapse" href="#menu02" aria-expanded="true" aria-controls="menu02">Menu de Configurações </a> </h3>
   </div><!-- fim box-header se colocar o collapse na class= show o manu fica fechado e aria-expanded="true" fica com o sinal de menus-->
   <div id="menu02" class="show" data-parent="#menu02">
     <div class="box-body no-padding">
       <ul class="nav flex-column nav-stacked">
      <!-- Seção de Configurações -->
      <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                    <li class="nav-item submenu">
                        <?php
                        // Verifica se está na seção de configurações
                        $isConfigSection = strpos($_SERVER['REQUEST_URI'], '/usuarios/listar') !== false || strpos($_SERVER['REQUEST_URI'], '/modulos/listar') !== false || strpos($_SERVER['REQUEST_URI'], '/uploadtxt/index') !== false || strpos($_SERVER['REQUEST_URI'], '/uploadtxt/estatisticas') !== false || strpos($_SERVER['REQUEST_URI'], '/atividades/listar') !== false;
                        ?>
                        <a class="nav-link d-flex justify-content-between" role="button" aria-expanded="<?= $isConfigSection ? 'true' : 'false' ?>" data-toggle="collapse" aria-controls="configCollapse" href="#configCollapse">
                            <span><i class="fas fa-cogs me-2"></i> Configurações</span>
                            <div>
                                <span class="iconSubmenu"> <i class="fa fa-angle-left"></i> </span>
                            </div>
                        </a>
                        <ul class="nav flex-column nav-stacked collapse <?= $isConfigSection ? 'show' : '' ?>" id="configCollapse" data-parent="#configCollapse">
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/modulos/listar') !== false ? 'active' : '' ?>" href="<?= URL ?>/modulos/listar">
                                    <i class="fas fa-th-list me-2"></i> Gerenciar Módulos
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/usuarios/listar') !== false ? 'active' : '' ?>" href="<?= URL ?>/usuarios/listar">
                                    <i class="fas fa-users me-2"></i> Gerenciar Usuários
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/uploadtxt/index') !== false ? 'active' : '' ?>" href="<?= URL ?>/uploadtxt/index">
                                    <i class="fas fa-file-import me-2"></i> Importar TXT
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/uploadtxt/estatisticas') !== false ? 'active' : '' ?>" href="<?= URL ?>/uploadtxt/estatisticas">
                                    <i class="fas fa-chart-line me-2"></i> Estatísticas
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/atividades/listar') !== false ? 'active' : '' ?>" href="<?= URL ?>/atividades/listar">
                                    <i class="fas fa-list me-2"></i> Atividades
                                </a>
                            </li>
                            <li role="navigation">
                                <a class="nav-link d-flex justify-content-between <?= strpos($_SERVER['REQUEST_URI'], '/agenda/index') !== false ? 'active' : '' ?>" href="<?= URL ?>/agenda/index">
                                    <i class="fas fa-calendar-alt me-2"></i> Agenda
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

       </ul>
     </div><!-- fim box-body -->
   </div><!-- fim menu -->
 </div><!-- fim box -teste teste-->