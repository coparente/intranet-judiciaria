<?php

/**
 * [ DASHBOARD ] - Controlador responsável por gerenciar a página inicial e funcionalidades do painel administrativo.
 * 
 * Este controlador permite:
 * - Exibir estatísticas gerais do sistema
 * - Gerenciar acessos dos usuários
 * - Monitorar atividades recentes
 * - Controlar uploads pendentes
 * - Gerenciar módulos e submódulos
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */

class Dashboard extends Controllers
{
    private $usuarioModel;
    private $loginModel;
    protected $moduloModel;
    private $processoModel;
    private $notificacaoModel;

    public function __construct()
    {
        parent::__construct();
        
        // Carrega os models necessários
        $this->loginModel = $this->model('LoginModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->processoModel = $this->model('ProcessoCustasModel');
        $this->notificacaoModel = $this->model('NotificacaoModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            Helper::redirecionar('./');
        }

        // Atualiza último acesso
        $this->usuarioModel->atualizarUltimoAcesso($_SESSION['usuario_id']);
    }

    /**
     * [ inicial ] - Exibe o dashboard com estatísticas e informações do sistema.
     * 
     * @return void
     */
    public function inicial()
    {
        // Coleta estatísticas para o dashboard
        $dados = [
            'tituloPagina' => 'Dashboard',
            'total_usuarios' => $this->usuarioModel->contarUsuarios(),
            'usuarios_ativos' => $this->usuarioModel->contarUsuariosPorStatus('ativo'),
            'acessos_hoje' => $this->usuarioModel->getAcessosHoje(),
            'atividades' => $this->usuarioModel->getUltimasAtividades(),
            'uploads_pendentes' => $this->getUploadsPendentes(),
            'modulos' => $this->moduloModel->getModulosComSubmodulos(),
        ];

        $this->view('dashboard/dashboard', $dados);
    }

    /**
     * [ getUploadsPendentes ] - Retorna o número de uploads pendentes.
     * 
     * @return int
     */
    private function getUploadsPendentes()
    {
        // TODO: Implementar contagem de uploads pendentes
        return 0;
    }

    /**
     * [ error ] - Exibe a página de erro quando uma página não é encontrada.
     * 
     * @return void
     */ 
    public function error()
    {
        $dados = [
            'tituloPagina' => 'Erro',
            'mensagem' => 'Página não encontrada'
        ];

        $this->view('paginas/error', $dados);
    }

    /**
     * Método para processar a pesquisa de processos e guias
     * @return void
     */
    public function pesquisar()
    {
        $dados = [
            'tituloPagina' => 'Resultado da Pesquisa',
            'numero_processo' => '',
            'numero_guia' => '',
            'resultados' => [],
            'erro' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitiza e valida os dados do formulário
            $filtros = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $numero_processo = trim($filtros['numero_processo'] ?? '');
            $numero_guia = trim($filtros['numero_guia'] ?? '');
            
            // Verifica se pelo menos um dos campos foi preenchido
            if (empty($numero_processo) && empty($numero_guia)) {
                Helper::mensagemSweetAlert('dashboard', 'Intimação registrada com sucesso!', 'success');
                Helper::mensagemSweetAlert('dashboard', 'Por favor, preencha o número do processo ou o número da guia', 'warning');
                Helper::mensagem('dashboard', 'Por favor, preencha o número do processo ou o número da guia', 'alert alert-danger');
                Helper::redirecionar('dashboard/inicial');
                return;
            }
            
            // Realiza a pesquisa usando o modelo
            $resultados = $this->processoModel->pesquisarProcessos(
                $numero_processo,
                $numero_guia
            );
            
            // Prepara os dados para a view
            $dados['numero_processo'] = $numero_processo;
            $dados['numero_guia'] = $numero_guia;
            $dados['resultados'] = $resultados;
        } else {
            // Se tentar acessar diretamente via GET, redireciona para o dashboard
            Helper::redirecionar('dashboard/inicial');
        }
        
        $this->view('dashboard/resultado_pesquisa', $dados);
    }
}
