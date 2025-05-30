<?php

/**
 * [ USUARIOS ] - Controlador responsável por gerenciar usuários do sistema.
 * 
 * Este controlador permite:
 * - Listar, cadastrar, editar e excluir usuários
 * - Gerenciar permissões de usuários
 * - Gerar relatórios de usuários
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */ 
class Usuarios extends Controllers
{
    private $usuariosPorPagina = ITENS_POR_PAGINA;
    private $usuarioModel;
    protected $moduloModel;  // Adicionando moduloModel como protected

    public function __construct()
    {
        parent::__construct();  // Chamando o construtor pai primeiro
        
        // Carrega o model de usuário
        $this->usuarioModel = $this->model('UsuarioModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('./');
        }

        // Verifica se é admin ou analista
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
        ) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        // Atualiza último acesso
        $this->usuarioModel->atualizarUltimoAcesso($_SESSION['usuario_id']);
    }

    /**
     * [ cadastrar ] - Processa o cadastro de um novo usuário.
     * 
     * @return void
     */
    public function cadastrar()
    {
        // Verifica se o usuário tem permissão de admin
        // if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'admin')) {
        //     Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem cadastrar usuários', 'alert alert-danger');
        //     Helper::redirecionar('usuarios/listar');
        //     return;
        // }
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem fazer upload de processos', 'alert alert-danger');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'senha' => trim($formulario['senha']),
                'confirma_senha' => trim($formulario['confirma_senha']),
                'perfil' => trim($formulario['perfil']),
                'biografia' => trim($formulario['biografia']),
                'status' => trim($formulario['status']),
                'nome_erro' => '',
                'email_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => ''
            ];

            if (empty($formulario['nome'])) :
                $dados['nome_erro'] = 'Preencha o campo nome';
            endif;

            if (empty($formulario['email'])) :
                $dados['email_erro'] = 'Preencha o campo e-mail';
            endif;

            if (empty($formulario['senha'])) :
                $dados['senha_erro'] = 'Preencha o campo senha';
            endif;

            if (empty($formulario['confirma_senha'])) :
                $dados['confirma_senha_erro'] = 'Confirme a Senha';
            endif;

            if (empty($dados['nome_erro']) && empty($dados['email_erro']) && empty($dados['senha_erro']) && empty($dados['confirma_senha_erro'])) :
                if (Helper::checarNome($formulario['nome'])) :
                    $dados['nome_erro'] = 'O nome informado é inválido';
                elseif (Helper::checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado é inválido';
                // elseif (strpos($formulario['email'], '@tjgo.jus.br') === false) :
                //     $dados['email_erro'] = 'Use seu e-mail institucional';
                elseif ($this->usuarioModel->checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado já está cadastrado';
                elseif (strlen($formulario['senha']) < 6) :
                    $dados['senha_erro'] = 'A senha deve ter no mínimo 6 caracteres';
                elseif ($formulario['senha'] != $formulario['confirma_senha']) :
                    $dados['confirma_senha_erro'] = 'As senhas são diferentes';
                else :
                    $dados['senha'] = password_hash($formulario['senha'], PASSWORD_DEFAULT);
                    if ($this->usuarioModel->armazenar($dados)) :
                        Helper::mensagem('usuario', '<i class="fas fa-check"></i> Cadastro realizado com sucesso');
                        Helper::mensagemSweetAlert('usuario', 'Cadastro realizado com sucesso', 'success');
                        Helper::redirecionar('usuarios/listar');
                    else :
                        die("Erro ao armazenar usuário no banco de dados");
                    endif;
                endif;
            endif;
        else :
            $dados = [
                'nome' => '',
                'email' => '',
                'senha' => '',
                'confirma_senha' => '',
                'perfil' => 'usuario',
                'biografia' => '',
                'status' => 'ativo',
                'nome_erro' => '',
                'email_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => ''
            ];
        endif;

        $this->view('usuarios/cadastrar', $dados);
    }

    /**
     * [ listar ] - Exibe a página de listagem de usuários.
     * 
     * @param int $pagina Página atual
     * @return void
     */
    public function listar($pagina = 1)
    {
        
        // Verifica permissão para o módulo de listagem de usuários
        // Middleware::verificarPermissao(2); // ID do módulo 'Listar Usuários'
        // Middleware::verificarPermissao(3); // ID do módulo 'Listar Usuários'

        $filtro = filter_input(INPUT_GET, 'filtro', FILTER_SANITIZE_STRING) ?: '';
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
        $perfil = filter_input(INPUT_GET, 'perfil', FILTER_SANITIZE_STRING);

        $totalUsuarios = $this->usuarioModel->contarUsuarios($filtro);
        $totalPaginas = ceil($totalUsuarios / $this->usuariosPorPagina);

        $usuarios = $this->usuarioModel->lerUsuarios($pagina, $this->usuariosPorPagina, $filtro, $status, $perfil);

        $dados = [
            'usuarios' => $usuarios,
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
            'total_usuarios' => $totalUsuarios,
            'filtro' => $filtro,
            'status' => $status,
            'perfil' => $perfil
        ];
        
        $this->view('usuarios/listar', $dados);
    }

    /**
     * [ editar ] - Exibe a página de edição de um usuário.
     * 
     * @param string $tipo Tipo de usuário a ser editado
     * @param int $id ID do usuário a ser editado
     * @return void 
     */
    public function editar($tipo, $id)
    {
        // Verifica se o usuário tem permissão
        if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'analista')) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Apenas analistas e administradores podem editar usuários', 'alert alert-danger');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        if ($tipo == 'usuario') {
            $this->editarUser($id);
        } else {
            Helper::redirecionar('paginas/error');
        }
    }

    private function editarUser($id)
    {
        $usuario = $this->usuarioModel->lerUsuarioPorId($id);
        
        // Apenas admin pode editar outros admins
        if ($usuario->perfil == 'admin' && !$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'admin')) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem editar outros administradores', 'alert alert-danger');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        // Impede analista de editar seu próprio perfil
        if ($_SESSION['usuario_perfil'] == 'analista' && $_SESSION['usuario_id'] == $id) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Analistas não podem alterar seu próprio perfil', 'alert alert-danger');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            // Se for analista editando seu próprio perfil, mantém o perfil original
            if ($_SESSION['usuario_perfil'] == 'analista' && $_SESSION['usuario_id'] == $id) {
                $formulario['perfil'] = $usuario->perfil;
            }

            $dados = [
                'id' => $id,
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'senha' => trim($formulario['senha']),
                'perfil' => trim($formulario['perfil']),
                'biografia' => trim($formulario['biografia']),
                'status' => trim($formulario['status']),
                'nome_erro' => '',
                'email_erro' => '',
                'senha_erro' => ''
            ];

            if (empty($formulario['senha'])) :
                $dados['senha'] = $usuario->senha;
            else :
                $dados['senha'] = password_hash($formulario['senha'], PASSWORD_DEFAULT);
            endif;

            if (empty($formulario['nome']) || empty($formulario['email'])) :
                if (empty($formulario['nome'])) :
                    $dados['nome_erro'] = 'Preencha o campo nome';
                endif;

                if (empty($formulario['email'])) :
                    $dados['email_erro'] = 'Preencha o campo e-mail';
                endif;
            else :
                if ($formulario['email'] == $usuario->email || !$this->usuarioModel->checarEmail($formulario['email'])) :
                    if ($this->usuarioModel->atualizar($dados)) :
                        Helper::mensagem('usuario', '<i class="fas fa-check"></i> Usuário atualizado com sucesso');
                        Helper::mensagemSweetAlert('usuario', 'Usuário atualizado com sucesso', 'success');
                        Helper::redirecionar('usuarios/listar');
                    endif;
                else :
                    $dados['email_erro'] = 'O e-mail informado já está cadastrado';
                endif;
            endif;
        else :
            $dados = [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'perfil' => $usuario->perfil,
                'biografia' => $usuario->biografia,
                'status' => $usuario->status,
                'nome_erro' => '',
                'email_erro' => '',
                'senha_erro' => ''
            ];
        endif;

        $this->view('usuarios/editar', $dados);
    }

    /**
     * [ deletar ] - Processa a exclusão de um usuário.
     * 
     * @param string $tipo Tipo de usuário a ser deletado
     * @param int $id ID do usuário a ser deletado
     * @return void 
     */
    public function deletar($tipo, $id)
    {
        // Verifica se o usuário tem permissão de admin
        if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'admin')) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem excluir usuários', 'alert alert-danger');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        if ($tipo == 'usuario') {
            $this->deletarUser($id);
        } else {
            Helper::redirecionar('paginas/error');
        }
    }

    /**
     * [ deletarUser ] - Processa a exclusão de um usuário.
     * 
     * @param int $id ID do usuário a ser deletado
     * @return void 
     */
    private function deletarUser($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $metodo = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);

        if ($id && $metodo == 'POST'):
            $usuario = $this->usuarioModel->lerUsuarioPorId($id);
            
            // Impede exclusão do próprio usuário
            if ($id == $_SESSION['usuario_id']) {
                Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Você não pode excluir seu próprio usuário', 'alert alert-danger');
                Helper::redirecionar('usuarios/listar');
                return;
            }

            // Apenas admin pode excluir outros admins
            if ($usuario->perfil == 'admin' && !$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'admin')) {
                Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem excluir outros administradores', 'alert alert-danger');
                Helper::redirecionar('usuarios/listar');
                return;
            }

            if ($this->usuarioModel->destruir($id)):
                Helper::mensagem('usuario', '<i class="fas fa-check"></i> Usuário excluído com sucesso!');
                Helper::mensagemSweetAlert('usuario', 'Usuário excluído com sucesso!', 'success');
                Helper::redirecionar('usuarios/listar');
            endif;
        endif;
    }

    /**
     * [ gerarPDF ] - Gera um relatório em formato PDF de todos os usuários.
     * 
     * @return void
     */
    public function gerarPDF()
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        try {
            // Verifica se o usuário tem permissão
            if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'analista')) {
                throw new Exception('Acesso negado: Apenas analistas e administradores podem gerar relatórios');
            }

            // Obtém todos os usuários
            $usuarios = $this->usuarioModel->lerTodosUsuarios();

            if (empty($usuarios)) {
                throw new Exception('Nenhum usuário encontrado para gerar o relatório.');
            }

            // Prepara os dados
            $dados = [
                'usuarios' => $usuarios,
                'data_geracao' => date('d/m/Y H:i:s'),
                'logo_url' => URL . '/public/img/logo.png' // URL pública da imagem
            ];

            // Inicia o buffer de saída
            ob_start();

            // Carrega a view do PDF
            $this->view('usuarios/pdf', $dados);
            
            $html = ob_get_clean();

            // Configura o DOMPDF
            $options = new \Dompdf\Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsPhpEnabled(true);
            $options->setIsRemoteEnabled(true); // Habilita carregamento de recursos remotos
            $options->setDefaultFont('Helvetica');
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="relatorio_usuarios.pdf"');
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            header('X-Content-Type-Options: nosniff');

            echo $dompdf->output();
            exit();

        } catch (Exception $e) {
            error_log('Erro ao gerar PDF: ' . $e->getMessage());
            Helper::mensagem('usuario', 'Erro ao gerar PDF: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('usuarios/listar');
        }
    }

    /**
     * [ permissoes ] - Exibe a página de gerenciamento de permissões de um usuário.
     * 
     * @param string $tipo Tipo de usuário a ser editado
     * @param int $id ID do usuário a ser editado
     * @return void 
     */
    public function permissoes($tipo, $id)
    {
        $usuario = $this->usuarioModel->lerUsuarioPorId($id);
        // Verifica se o usuário tem permissão de admin
        if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'analista')) {
            Helper::mensagem('usuario', 'Acesso negado: Apenas administradores podem gerenciar permissões', 'alert alert-danger');
            Helper::mensagemSweetAlert('usuario', 'Acesso negado: Apenas administradores podem gerenciar permissões', 'error');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        // Apenas admin pode editar outros admins
        if ($usuario->perfil == 'admin' && !$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'admin')) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem editar outros administradores', 'alert alert-danger');
            Helper::mensagemSweetAlert('usuario', 'Acesso negado: Apenas administradores podem editar outros administradores', 'error');
            Helper::redirecionar('usuarios/listar');
            return;
        }

        // Impede analista de editar seu próprio perfil
        if ($_SESSION['usuario_perfil'] == 'analista' && $_SESSION['usuario_id'] == $id) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Acesso negado: Analistas não podem alterar seu próprio perfil', 'alert alert-danger');
            Helper::mensagemSweetAlert('usuario', 'Acesso negado: Analistas não podem alterar seu próprio perfil', 'error');
           
            Helper::redirecionar('usuarios/listar');
            return;
        }

        


        $usuario = $this->usuarioModel->lerUsuarioPorId($id);
        if (!$usuario) {
            Helper::redirecionar('usuarios/listar');
        }

        $dados = [
            'tituloPagina' => 'Gerenciar Permissões',
            'usuario' => $usuario,
            'modulos' => $this->moduloModel->getModulosComSubmodulos(),
            'permissoes' => $this->usuarioModel->getPermissoesUsuario($id)
        ];

        $this->view('modulos/permissoes', $dados);
    }

    /**
     * [ salvarPermissoes ] - Salva as permissões de um usuário.
     * 
     * @param int $id ID do usuário a ser salvo
     * @return void 
     */
    public function salvarPermissoes($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $modulos = isset($_POST['modulos']) ? $_POST['modulos'] : [];
            
            // Garante que os módulos pai estejam marcados se houver submódulos
            $modulosCompletos = $this->moduloModel->getModulosComSubmodulos();
            foreach ($modulosCompletos as $modulo) {
                if (!empty($modulo['submodulos'])) {
                    foreach ($modulo['submodulos'] as $submodulo) {
                        if (in_array($submodulo['id'], $modulos) && !in_array($modulo['id'], $modulos)) {
                            $modulos[] = $modulo['id'];
                        }
                    }
                }
            }
            
            if ($this->usuarioModel->gerenciarPermissoes($id, $modulos)) {
                Helper::mensagem('permissao', '<i class="fas fa-check"></i> Permissões atualizadas com sucesso!');
                Helper::mensagemSweetAlert('permissao', 'Permissões atualizadas com sucesso!', 'success');
            } else {
                Helper::mensagem('permissao', '<i class="fas fa-ban"></i> Erro ao atualizar permissões', 'alert alert-danger');
                Helper::mensagemSweetAlert('permissao', 'Erro ao atualizar permissões', 'error');
            }
        }
        
        Helper::redirecionar('usuarios/permissoes/usuario/'.$id);
    }
}
