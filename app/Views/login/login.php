<div class="login-box shadow">
    <div class="login-box-body ">
        <div class="text-center"><img height="180" src="<?= URL ?>/public/img/logo.png" alt="logo.png">
        </div>
        <br>
        <!-- <h3 class="text-center txt-primary">
            <?= APP_NOME ?>
        </h3> -->
        <!-- Mensagens de Erro/Sucesso -->
        <?= Helper::mensagem('usuario') ?>
        <p class="login-box-msg">Identifique-se para acessar o sistema</p>
        <form action="<?= URL ?>/login/login" method="POST" class="needs-validation" novalidate>
            <label class="sr-only" for="inlineFormInputGroup">Usuário</label>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text"><i class="fas fa-user"></i></div>
                </div>
                <input type="email" class="form-control <?= $dados['email_erro'] ? 'is-invalid' : '' ?>" name="email" id="email"
                    value="<?= $dados['email'] ?>" placeholder="Digite seu Email">
                <div class="invalid-feedback">
                    <?= $dados['email_erro'] ?>
                </div>
            </div>
            <!-- Email -->


            <!-- Senha -->
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text"><i class="fas fa-lock"></i></div>
                </div>
                <input type="password" class="form-control <?= $dados['senha_erro'] ? 'is-invalid' : '' ?>" name="senha" id="senha"
                    placeholder="Digite sua Senha" required>
                <div class="invalid-feedback">
                    <?= $dados['senha_erro'] ?>
                </div>
            </div>
            <div class="form-check mb-3 ">
                <input class="form-check-input" type="checkbox" id="showPasswordCheckbox">
                <label class="form-check-label" for="showPasswordCheckbox">Exibir Senha</label>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Autenticar</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div> <!-- fim login-box-body -->
</div> <!-- fim login-box -->
<!-- Informações Adicionais -->
<div class="text-center mt-4  text-muted">
    <p class="mb-1">
        <i class="fas fa-shield-alt me-1"></i> Acesso restrito a usuários autorizados
    </p>
    <p class="mb-0">
        <i class="fas fa-code me-1"></i> Versão <?= APP_VERSAO ?>
        <span class="mx-1">&bullet;</span>
        <i class="fas fa-clock me-1"></i> <?= date('Y') ?>
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Corrigindo o ID do campo de senha para 'senha' em vez de 'password'
        const senhaInput = document.getElementById('senha');
        const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');

        showPasswordCheckbox.addEventListener('change', function () {
            senhaInput.type = this.checked ? 'text' : 'password';
        });
    });
</script>