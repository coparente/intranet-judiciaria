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
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border" id="tituloMenu">
                                <h3 id="tabelas" class="box-title"><i class="fas fa-history me-2"></i> Atividades dos Usuários</h3>
                            </div>
                            <fieldset aria-labelledby="tituloMenu">
                                <div class="card-body">
                                    <div class="table">
                                        <table class="table table-hover" id="tabelaAtividades">
                                            <thead class="cor-fundo-azul-escuro text-white">
                                                <tr>
                                                    <th>Data/Hora</th>
                                                    <th>Usuário</th>
                                                    <!-- <th>Perfil</th> -->
                                                    <th>Ação</th>
                                                    <th>Descrição</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </section>
    </div>
</main>
<?php require_once APPROOT . '/Views/include/footer.php'; ?>