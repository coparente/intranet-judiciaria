<?php

/**
 * [ MODULOS ] - Controlador responsável por gerenciar módulos e submódulos do sistema.
 * 
 * Este controlador permite:
 * - Listar módulos e submódulos    
 * - Cadastrar, editar e excluir módulos
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */
class Modulos extends Controllers
{
    protected $moduloModel;
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        
        $this->moduloModel = $this->model('ModuloModel');
        $this->usuarioModel = $this->model('UsuarioModel');

        // Verifica se é admin
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 'admin') {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem gerenciar módulos', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

    }

    /**
     * [ listar ] - Exibe a página de gerenciamento de módulos.
     * 
     * @return void
     */
    public function listar()
    {
        $dados = [
            'tituloPagina' => 'Gerenciar Módulos',
            'modulos' => $this->moduloModel->getModulosComSubmodulos()
        ];

        $this->view('modulos/listar', $dados);
    }

    /**
     * [ cadastrar ] - Processa o cadastro de um novo módulo.
     * 
     * @return void
     */
    public function cadastrar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validações básicas
            if (empty($dados['nome']) || empty($dados['rota']) || empty($dados['icone'])) {
                Helper::mensagem('modulo', 'Preencha todos os campos obrigatórios', 'alert alert-danger');
                Helper::redirecionar('modulos/listar');
                return;
            }
            
            if ($this->moduloModel->armazenar($dados)) {
                Helper::mensagem('modulo', 'Módulo cadastrado com sucesso!');
            } else {
                Helper::mensagem('modulo', 'Erro ao cadastrar módulo', 'alert alert-danger');
            }
            
            Helper::redirecionar('modulos/listar');
        }
    }

    /**
     * [ excluir ] - Processa a exclusão de um módulo.
     * 
     * @param int $id ID do módulo a ser excluído
     * @return void
     */
    public function excluir($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if ($id === false || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('modulos/listar');
            return;
        }

        if ($this->moduloModel->excluir($id)) {
            Helper::mensagem('modulo', 'Módulo excluído com sucesso!');
        } else {
            Helper::mensagem('modulo', 'Não é possível excluir um módulo que possui submódulos', 'alert alert-danger');
        }

        Helper::redirecionar('modulos/listar');
    }

    /**
     * [ editar ] - Exibe a página de edição de um módulo.
     * 
     * @param int $id ID do módulo a ser editado
     * @return void
     */
    public function editar($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if ($id === false) {
            Helper::redirecionar('modulos/listar');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $dados['id'] = $id;
            
            // Validações básicas
            if (empty($dados['nome']) || empty($dados['rota']) || empty($dados['icone'])) {
                Helper::mensagem('modulo', 'Preencha todos os campos obrigatórios', 'alert alert-danger');
                Helper::redirecionar('modulos/editar/' . $id);
                return;
            }
            
            if ($this->moduloModel->atualizar($dados)) {
                Helper::mensagem('modulo', 'Módulo atualizado com sucesso!');
                Helper::redirecionar('modulos/listar');
            } else {
                Helper::mensagem('modulo', 'Erro ao atualizar módulo', 'alert alert-danger');
                Helper::redirecionar('modulos/editar/' . $id);
            }
            return;
        }

        $dados = [
            'tituloPagina' => 'Editar Módulo',
            'modulo' => $this->moduloModel->lerModuloPorId($id),
            'modulos_pai' => $this->moduloModel->getModulosPai()
        ];

        $this->view('modulos/editar', $dados);
    }
} 