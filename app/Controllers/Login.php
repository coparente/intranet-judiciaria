<?php

/**
 * [ LOGIN ] - Controlador responsável por gerenciar o processo de login do usuário.
 * 
 * Este controlador permite:
 * - Verificar credenciais de login
 * - Criar e destruir sessões
 * - Redirecionar usuários com base em seu perfil
 * - Gerenciar atividades de login e logout
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */
class Login extends Controllers
{
    protected $moduloModel;
    private $loginModel;
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->loginModel = $this->model('LoginModel');
        $this->usuarioModel = $this->model('UsuarioModel');

        // Inicia a sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSAO_NOME);
            session_start();
        }
    }

    /**
     * [ login ] - Exibe a página de login e processa o login do usuário.
     * 
     * @return void
     */ 
    public function login()
    {
        // Se já estiver logado, redireciona para o dashboard
        if (isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('dashboard/inicial');
            return;
        }



        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) {
            $dados = [
                'email' => trim($formulario['email']),
                'senha' => trim($formulario['senha']),
                'email_erro' => '',
                'senha_erro' => ''
            ];

            // Validação dos campos
            if (empty($dados['email'])) {
                $dados['email_erro'] = 'Digite seu e-mail';
            } elseif (Helper::checarEmail($dados['email'])) {
                $dados['email_erro'] = 'E-mail inválido';
            } 
            // elseif (!strpos($dados['email'], '@tjgo.jus.br')) {
            //     $dados['email_erro'] = 'Use seu e-mail institucional';
            // }

            if (empty($dados['senha'])) {
                $dados['senha_erro'] = 'Digite sua senha';
            }

            // Se não houver erros
            if (empty($dados['email_erro']) && empty($dados['senha_erro'])) {
                // Tenta fazer login
                $usuario = $this->loginModel->checarLogin($dados['email'], $dados['senha']);

                // var_dump($usuario);
                // die();
                Helper::registrarAtividade(
                    'Login',
                    'Usuário realizou login no sistema'
                );

                // Verifica se o usuário está ativo
                if ($usuario) {
                    // Verifica se o usuário está ativo
                    if ($usuario->status === STATUS_INATIVO) {
                        Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Conta desativada. Entre em contato com o administrador.', 'alert alert-danger');
                    } else {
                        $this->criarSessaoUsuario($usuario);
                        // Registra a atividade APÓS criar a sessão
                        Helper::registrarAtividade(
                            'Login',
                            'Usuário realizou login no sistema'
                        );
                    }
                } else {
                    Helper::mensagem('usuario', '<i class="fas fa-exclamation-triangle"></i> E-mail ou senha incorretos', 'alert alert-danger');

                    // Log de tentativa falha (poderia ser implementado)
                    // $this->logarTentativaFalha($dados['email']);
                }
            }
        } else {
            $dados = [
                'email' => '',
                'senha' => '',
                'email_erro' => '',
                'senha_erro' => ''
            ];
        }

        $this->view('login/login', $dados);
    }

    /**
     * [ criarSessaoUsuario ] - Cria a sessão do usuário.
     * 
     * @param object $usuario Usuário logado
     * @return void
     */
    private function criarSessaoUsuario($usuario)
    {
        // Regenera o ID da sessão por segurança
        session_regenerate_id(true);

        // Define as variáveis de sessão
        $_SESSION['usuario_id'] = $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;
        $_SESSION['usuario_email'] = $usuario->email;
        $_SESSION['usuario_perfil'] = $usuario->perfil;
        $_SESSION['usuario_status'] = $usuario->status;
        $_SESSION['ultimo_acesso'] = time();

        // Buscar módulos permitidos para o usuário
        $this->moduloModel = $this->model('ModuloModel');
        $modulos = $this->moduloModel->getModulosComSubmodulos($usuario->id);

        // Salvar os módulos na sessão
        $_SESSION['modulos'] = $modulos;

        // Atualiza último acesso no banco
        $this->usuarioModel->atualizarUltimoAcesso($usuario->id);

        // Redireciona baseado no perfil
        Helper::redirecionar('dashboard/inicial');
    }

        /**
     * [ sair ] - Sai do sistema
     * 
     * @return void
     */
    public function sair()
    {
        // Registra a atividade antes de destruir a sessão
        Helper::registrarAtividade(
            'Logout', 
            'Usuário saiu do sistema'
        );

        // Limpa todas as variáveis de sessão
        $_SESSION = array();

        // Destrói o cookie da sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destrói a sessão
        session_destroy();

        // Redireciona para o login
        Helper::redirecionar('login/login');
    }

    /**
     * [ recuperarSenha ] - Exibe a página de recuperação de senha.
     * 
     * @return void
     */
    public function recuperarSenha()
    {
        // TODO: Implementar recuperação de senha
        $this->view('login/recuperar', []);
    }
}
