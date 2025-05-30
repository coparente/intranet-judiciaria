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
                <!-- Cabeçalho da Página -->

                <div class="col-md-9">
                    <!-- Alertas e Mensagens -->
                    <?= Helper::mensagem('usuario') ?>
                    <?= Helper::mensagemSweetAlert('usuario') ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <!-- Cabeçalho da Página -->
                        <h1 class="h2">
                            <i class="fas fa-user-plus me-2"></i> Novo Usuário
                        </h1>
                        <a href="<?= URL ?>/usuarios/listar" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Voltar
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <h3 id="tabelas" class="box-title"><i class="fas fa-user-plus me-2"></i> Novo Usuário</h3>

                                </div>
                                <!-- fim box-header -->
                                <fieldset aria-labelledby="tituloMenu">
                                    <div class="card-body">

                                        <form action="<?= URL ?>/usuarios/cadastrar" method="POST" class="needs-validation" novalidate>
                                            <div class="row g-3">
                                                <!-- Nome -->
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-floating">
                                                        <label for="nome">Nome Completo</label>
                                                        <input type="text"
                                                            class="form-control <?= $dados['nome_erro'] ? 'is-invalid' : '' ?>"
                                                            id="nome"
                                                            name="nome"
                                                            value="<?= $dados['nome'] ?>"
                                                            placeholder="Nome completo"
                                                            required>
                                                        <div class="invalid-feedback">
                                                            <?= $dados['nome_erro'] ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <label for="email">Email Institucional</label>
                                                        <input type="email"
                                                            class="form-control <?= $dados['email_erro'] ? 'is-invalid' : '' ?>"
                                                            id="email"
                                                            name="email"
                                                            value="<?= $dados['email'] ?>"
                                                            placeholder="nome@tjgo.jus.br"
                                                            required>
                                                        <div class="invalid-feedback">
                                                            <?= $dados['email_erro'] ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Senha -->
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-floating">
                                                        <label for="senha">Senha</label>
                                                        <input type="password"
                                                            class="form-control <?= $dados['senha_erro'] ? 'is-invalid' : '' ?>"
                                                            id="senha"
                                                            name="senha"
                                                            placeholder="Senha"
                                                            required>
                                                        <div class="invalid-feedback">
                                                            <?= $dados['senha_erro'] ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Confirmar Senha -->
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-floating">
                                                        <label for="confirma_senha">Confirmar Senha</label>
                                                        <input type="password"
                                                            class="form-control <?= $dados['confirma_senha_erro'] ? 'is-invalid' : '' ?>"
                                                            id="confirma_senha"
                                                            name="confirma_senha"
                                                            placeholder="Confirmar Senha"
                                                            required>
                                                        <div class="invalid-feedback">
                                                            <?= $dados['confirma_senha_erro'] ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Perfil -->
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-floating">
                                                        <label for="perfil">Perfil de Acesso</label>
                                                        <select class="form-control" id="perfil" name="perfil" required>
                                                            <option value="usuario" <?= $dados['perfil'] == 'usuario' ? 'selected' : '' ?>>Usuário</option>
                                                            <option value="analista" <?= $dados['perfil'] == 'analista' ? 'selected' : '' ?>>Analista</option>
                                                            <option value="admin" <?= $dados['perfil'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Status -->
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <label for="status">Status</label>
                                                        <select class="form-control" id="status" name="status" required>
                                                            <option value="ativo" <?= $dados['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                                            <option value="inativo" <?= $dados['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Biografia -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <label for="biografia">Biografia</label>
                                                        <textarea class="form-control"
                                                            id="biografia"
                                                            name="biografia"
                                                            style="height: 100px"
                                                            placeholder="Biografia"><?= $dados['biografia'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Informações Adicionais -->
                                            <div class="alert alert-info mt-4" role="alert">
                                                <h6 class="alert-heading mb-1">
                                                    <i class="fas fa-info-circle me-2"></i> Informações Importantes
                                                </h6>
                                                <ul class="mb-0">
                                                    <li>A senha deve ter no mínimo 6 caracteres</li>
                                                    <li>O email deve ser institucional (@tjgo.jus.br)</li>
                                                    <li>Todos os campos marcados com * são obrigatórios</li>
                                                </ul>
                                            </div>

                                            <!-- Botões -->
                                            <div class="col-12 justify-content-end text-right">
                                                <button type="reset" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-eraser me-2"></i> Limpar
                                                </button>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-save me-2"></i> Cadastrar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                            </div>


                            </fieldset>

                        </div><!-- fim box -->
                    </div>

                </div> <!-- fim row -->
            </div><!-- fim col-md-9 -->

        </section>
    </div>
    </div>
</main>
<?php include 'app/Views/include/footer.php' ?>