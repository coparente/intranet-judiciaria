<?php

/**
 * [ DASHBOARDSBI ] - Controlador responsável por gerenciar os dashboards BI.
 * 
 * Este controlador permite:
 * - Listar dashboards BI
 * - Cadastrar novos dashboards
 * - Editar dashboards existentes
 * - Excluir dashboards
 * - Visualizar dashboards
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class DashboardsBi extends Controllers
{
    private $dashboardBiModel;
    private $atividadeModel;

    public function __construct()
    {
        parent::__construct();
        
        // Verifica permissões
        // Verifica permissões
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['usuario', 'admin', 'analista'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }
        
        $this->dashboardBiModel = $this->model('DashboardBiModel');
        $this->atividadeModel = $this->model('AtividadeModel');
    }

    /**
     * [ index ] - Exibe a lista de dashboards BI
     * 
     * @return void
     */
    public function index()
    {
        // Verifica permissão para o módulo
        Middleware::verificarPermissao(1); // Substitua pelo ID correto do módulo
        
        $filtro = filter_input(INPUT_GET, 'filtro', FILTER_SANITIZE_STRING) ?? '';
        
        $dados = [
            'tituloPagina' => 'Dashboards BI',
            'dashboards' => $this->dashboardBiModel->listarDashboards($filtro),
            'filtro' => $filtro,
            'categorias' => $this->dashboardBiModel->listarCategorias()
        ];
        
        $this->view('dashboards_bi/index', $dados);
    }

    /**
     * [ cadastrar ] - Exibe o formulário de cadastro e processa o envio
     * 
     * @return void
     */
    public function cadastrar()
    {
        // Verifica permissão para o módulo
        Middleware::verificarPermissao(1); // Substitua pelo ID correto do módulo
        
        $dados = [
            'tituloPagina' => 'Cadastrar Dashboard BI',
            'titulo' => '',
            'descricao' => '',
            'url' => '',
            'categoria' => '',
            'icone' => 'fa-chart-bar',
            'ordem' => 0,
            'status' => 'ativo',
            'erros' => []
        ];
        
        // Se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $dados['titulo'] = trim($_POST['titulo']);
            $dados['descricao'] = trim($_POST['descricao']);
            $dados['url'] = trim($_POST['url']);
            $dados['categoria'] = trim($_POST['categoria']);
            $dados['icone'] = trim($_POST['icone']);
            $dados['ordem'] = (int)$_POST['ordem'];
            $dados['status'] = $_POST['status'];
            
            // Validação
            if (empty($dados['titulo'])) {
                $dados['erros']['titulo'] = 'O título é obrigatório';
            }
            
            if (empty($dados['url'])) {
                $dados['erros']['url'] = 'A URL é obrigatória';
            }
            
            // Se não houver erros, cadastra o dashboard
            if (empty($dados['erros'])) {
                if ($this->dashboardBiModel->cadastrarDashboard($dados)) {
                    // Registra a atividade
                    $this->atividadeModel->registrarAtividade($_SESSION['usuario_id'], 'cadastro', 'Cadastrou o dashboard BI: ' . $dados['titulo']);
                    
                    Helper::mensagem('dashboards_bi', '<i class="fas fa-check"></i> Dashboard cadastrado com sucesso!', 'alert alert-success');
                    Helper::mensagemSweetAlert('dashboards_bi', 'Dashboard cadastrado com sucesso!', 'success');
                    Helper::redirecionar('dashboardsbi/index');
                } else {
                    Helper::mensagem('dashboards_bi', '<i class="fas fa-ban"></i> Erro ao cadastrar dashboard', 'alert alert-danger');
                    Helper::mensagemSweetAlert('dashboards_bi', 'Erro ao cadastrar dashboard', 'error');
                    $this->view('dashboards_bi/cadastrar', $dados);
                }
            } else {
                $this->view('dashboards_bi/cadastrar', $dados);
            }
        } else {
            $this->view('dashboards_bi/cadastrar', $dados);
        }
    }

    /**
     * [ editar ] - Exibe o formulário de edição e processa o envio
     * 
     * @param int $id ID do dashboard
     * @return void
     */
    public function editar($id)
    {
        // Verifica permissão para o módulo
        Middleware::verificarPermissao(1); // Substitua pelo ID correto do módulo
        
        // Busca o dashboard
        $dashboard = $this->dashboardBiModel->buscarDashboardPorId($id);
        
        if (!$dashboard) {
            Helper::mensagem('dashboards_bi', '<i class="fas fa-ban"></i> Dashboard não encontrado', 'alert alert-danger');
            Helper::mensagemSweetAlert('dashboards_bi', 'Dashboard não encontrado', 'error');
            Helper::redirecionar('dashboardsbi/index');
        }
        
        $dados = [
            'tituloPagina' => 'Editar Dashboard BI',
            'id' => $dashboard->id,
            'titulo' => $dashboard->titulo,
            'descricao' => $dashboard->descricao,
            'url' => $dashboard->url,
            'categoria' => $dashboard->categoria,
            'icone' => $dashboard->icone,
            'ordem' => $dashboard->ordem,
            'status' => $dashboard->status,
            'erros' => []
        ];
        
        // Se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $dados['titulo'] = trim($_POST['titulo']);
            $dados['descricao'] = trim($_POST['descricao']);
            $dados['url'] = trim($_POST['url']);
            $dados['categoria'] = trim($_POST['categoria']);
            $dados['icone'] = trim($_POST['icone']);
            $dados['ordem'] = (int)$_POST['ordem'];
            $dados['status'] = $_POST['status'];
            
            // Validação
            if (empty($dados['titulo'])) {
                $dados['erros']['titulo'] = 'O título é obrigatório';
            }
            
            if (empty($dados['url'])) {
                $dados['erros']['url'] = 'A URL é obrigatória';
            }
            
            // Se não houver erros, atualiza o dashboard
            if (empty($dados['erros'])) {
                if ($this->dashboardBiModel->atualizarDashboard($dados)) {
                    // Registra a atividade
                    $this->atividadeModel->registrarAtividade($_SESSION['usuario_id'], 'edicao', 'Editou o dashboard BI: ' . $dados['titulo']);
                    
                    Helper::mensagem('dashboards_bi', '<i class="fas fa-check"></i> Dashboard atualizado com sucesso!', 'alert alert-success');
                    Helper::mensagemSweetAlert('dashboards_bi', 'Dashboard atualizado com sucesso!', 'success');
                    Helper::redirecionar('dashboardsbi/index');
                } else {
                    Helper::mensagem('dashboards_bi', '<i class="fas fa-ban"></i> Erro ao atualizar dashboard', 'alert alert-danger');
                    Helper::mensagemSweetAlert('dashboards_bi', 'Erro ao atualizar dashboard', 'error');
                    $this->view('dashboards_bi/editar', $dados);
                }
            } else {
                $this->view('dashboards_bi/editar', $dados);
            }
        } else {
            $this->view('dashboards_bi/editar', $dados);
        }
    }

    /**
     * [ excluir ] - Exclui um dashboard
     * 
     * @param int $id ID do dashboard
     * @return void
     */
    public function excluir($id)
    {
        // Verifica permissão para o módulo
        Middleware::verificarPermissao(1); // Substitua pelo ID correto do módulo
        
        // Verifica se o método é POST
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('dashboardsbi/index');
        }
        
        // Busca o dashboard
        $dashboard = $this->dashboardBiModel->buscarDashboardPorId($id);
        
        if (!$dashboard) {
            Helper::mensagem('dashboards_bi', '<i class="fas fa-ban"></i> Dashboard não encontrado', 'alert alert-danger');
            Helper::redirecionar('dashboardsbi/index');
        }
        
        if ($this->dashboardBiModel->excluirDashboard($id)) {
            // Registra a atividade
            $this->atividadeModel->registrarAtividade($_SESSION['usuario_id'], 'exclusao', 'Excluiu o dashboard BI: ' . $dashboard->titulo);
            
            Helper::mensagem('dashboards_bi', '<i class="fas fa-check"></i> Dashboard excluído com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('dashboards_bi', 'Dashboard excluído com sucesso!', 'success');  
        } else {
            Helper::mensagem('dashboards_bi', '<i class="fas fa-ban"></i> Erro ao excluir dashboard', 'alert alert-danger');
            Helper::mensagemSweetAlert('dashboards_bi', 'Erro ao excluir dashboard', 'error');
        }
        
        Helper::redirecionar('dashboardsbi/index');
    }

    /**
     * [ visualizar ] - Exibe um dashboard específico
     * 
     * @param int $id ID do dashboard
     * @return void
     */
    public function visualizar($id)
    {
        // Verifica permissão para o módulo
        Middleware::verificarPermissao(1); // Substitua pelo ID correto do módulo
        
        // Busca o dashboard
        $dashboard = $this->dashboardBiModel->buscarDashboardPorId($id);
        
        if (!$dashboard) {
            Helper::mensagem('dashboards_bi', '<i class="fas fa-ban"></i> Dashboard não encontrado', 'alert alert-danger');
            Helper::mensagemSweetAlert('dashboards_bi', 'Dashboard não encontrado', 'error');
            Helper::redirecionar('dashboardsbi/index');
        }
        
        // Registra a atividade
        $this->atividadeModel->registrarAtividade($_SESSION['usuario_id'], 'visualizacao', 'Visualizou o dashboard BI: ' . $dashboard->titulo);
        
        $dados = [
            'tituloPagina' => $dashboard->titulo,
            'dashboard' => $dashboard
        ];
        
        $this->view('dashboards_bi/visualizar', $dados);
    }

    /**
     * [ painel ] - Exibe todos os dashboards ativos
     * 
     * @return void
     */
    public function painel()
    {
        // Verifica permissão para o módulo
        Middleware::verificarPermissao(10); // Substitua pelo ID correto do módulo
        
        $categoria = filter_input(INPUT_GET, 'categoria', FILTER_SANITIZE_STRING) ?? '';
        
        $dados = [
            'tituloPagina' => 'Painel de Dashboards BI',
            'dashboards' => $this->dashboardBiModel->listarDashboardsAtivos($categoria),
            'categorias' => $this->dashboardBiModel->listarCategorias(),
            'categoria_atual' => $categoria
        ];
        
        $this->view('dashboards_bi/painel', $dados);
    }
}
