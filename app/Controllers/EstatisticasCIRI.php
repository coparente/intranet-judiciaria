<?php

/**
 * [ EstatisticasCIRI ] - Controlador responsável pelas estatísticas da Central de Intimação Remota do Interior.
 * 
 * Este controlador permite:
 * - Visualizar estatísticas de processos por tipo de ato
 * - Gerar relatórios estatísticos
 * - Exportar dados para análise
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access public
 */
class EstatisticasCIRI extends Controllers
{
    private $ciriModel;
    private $estatisticasModel;

    public function __construct()
    {
        parent::__construct();
        
        // Verificar se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }
        
        // Verificar permissões (apenas admin e analista podem acessar)
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Você não tem permissão para acessar este módulo', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }
        
        $this->ciriModel = $this->model('CIRIModel');
        $this->estatisticasModel = $this->model('EstatisticasCIRIModel');
    }

    /**
     * [ index ] - Exibe a página principal de estatísticas
     * 
     * @return void
     */
    public function index()
    {
        // Obter filtros da URL
        $filtros = [
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) ?: date('Y-m-01'), // Primeiro dia do mês atual
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING) ?: date('Y-m-d'), // Dia atual
        ];
        
        // Obter estatísticas por tipo de ato
        $estatisticasTipoAto = $this->estatisticasModel->contarProcessosPorTipoAto($filtros);
        
        // Obter estatísticas por status
        $estatisticasStatus = $this->estatisticasModel->contarProcessosPorStatus($filtros);
        
        // Obter estatísticas por usuário
        $estatisticasUsuario = $this->estatisticasModel->contarProcessosPorUsuario($filtros);
        
        // Obter total de processos no período
        $totalProcessos = $this->estatisticasModel->contarTotalProcessos($filtros);
        
        $dados = [
            'tituloPagina' => 'Estatísticas CIRI',
            'filtros' => $filtros,
            'estatisticas_tipo_ato' => $estatisticasTipoAto,
            'estatisticas_status' => $estatisticasStatus,
            'estatisticas_usuario' => $estatisticasUsuario,
            'total_processos' => $totalProcessos
        ];
        
        $this->view('estatisticas_ciri/index', $dados);
    }
    
    /**
     * [ porTipoAto ] - Exibe estatísticas detalhadas por tipo de ato
     * 
     * @return void
     */
    public function porTipoAto()
    {
        // Obter filtros da URL
        $filtros = [
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) ?: date('Y-m-01'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING) ?: date('Y-m-d'),
        ];
        
        // Obter estatísticas por tipo de ato
        $estatisticas = $this->estatisticasModel->contarProcessosPorTipoAto($filtros);
        
        // Obter total de processos no período
        $totalProcessos = $this->estatisticasModel->contarTotalProcessos($filtros);
        
        $dados = [
            'tituloPagina' => 'Estatísticas por Tipo de Ato - CIRI',
            'filtros' => $filtros,
            'estatisticas' => $estatisticas,
            'total_processos' => $totalProcessos
        ];
        
        $this->view('estatisticas_ciri/por_tipo_ato', $dados);
    }
    
    

    /**
     * [ porUsuario ] - Exibe estatísticas detalhadas por usuário
     * 
     * @return void
     */
    public function porUsuario()
    {
        // Obter filtros da URL
        $filtros = [
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) ?: date('Y-m-01'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING) ?: date('Y-m-d'),
        ];
        
        // Obter estatísticas por usuário
        $estatisticas = $this->estatisticasModel->contarProcessosPorUsuario($filtros);
        
        // Obter total de processos no período
        $totalProcessos = $this->estatisticasModel->contarTotalProcessos($filtros);
        
        $dados = [
            'tituloPagina' => 'Estatísticas por Usuário - CIRI',
            'filtros' => $filtros,
            'estatisticas' => $estatisticas,
            'total_processos' => $totalProcessos
        ];
        
        $this->view('estatisticas_ciri/por_usuario', $dados);
    }

    /**
     * [ movimentacoesPorUsuario ] - Exibe estatísticas de movimentações por usuário
     * 
     * @return void
     */
    public function movimentacoesPorUsuario()
    {
        // Obter filtros da URL
        $filtros = [
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) ?: date('Y-m-01'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING) ?: date('Y-m-d'),
        ];
        
        // Obter estatísticas de movimentações por usuário
        $estatisticas = $this->estatisticasModel->contarMovimentacoesPorUsuario($filtros);
        
        // Obter total de movimentações no período
        $totalMovimentacoes = $this->estatisticasModel->contarTotalMovimentacoes($filtros);
        
        $dados = [
            'tituloPagina' => 'Movimentações por Usuário - CIRI',
            'filtros' => $filtros,
            'estatisticas' => $estatisticas,
            'total_movimentacoes' => $totalMovimentacoes
        ];
        
        $this->view('estatisticas_ciri/movimentacoes_por_usuario', $dados);
    }
} 