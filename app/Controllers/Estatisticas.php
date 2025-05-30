<?php

/**
 * [ ESTATISTICAS ] - Controlador responsável por gerenciar as estatísticas do sistema.
 * 
 * Este controlador permite:
 * - Calcular o tempo total de sessão por usuário   
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class Estatisticas extends Controllers {
    private $estatisticaModel;

    public function __construct() {
        parent::__construct();

        // Verifica permissões
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Permissão insuficiente', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        $this->estatisticaModel = $this->model('EstatisticaModel');
    }

    /**
     * Exibe as estatísticas de atividades do sistema
     */ 
    public function atividades() {
        $data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING);
        $data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING);
        $nome_usuario = filter_input(INPUT_GET, 'nome_usuario', FILTER_SANITIZE_STRING);

        $estatisticas_usuario = null;
        if ($nome_usuario) {
            $estatisticas_usuario = $this->estatisticaModel->calcularEstatisticasTempoUsuario($nome_usuario);
        }

        $dados = [
            'tituloPagina' => 'Estatísticas do Sistema',
            'tempo_sessao' => $this->estatisticaModel->calcularTempoSessaoPorUsuario($data_inicio, $data_fim, $nome_usuario),
            'estatisticas_gerais' => $this->estatisticaModel->obterEstatisticasGerais(),
            'tempo_total_sistema' => $this->estatisticaModel->calcularTempoTotalUso(null, $data_inicio, $data_fim),
            'estatisticas_usuario' => $estatisticas_usuario,
            'filtros' => [
                'nome_usuario' => $nome_usuario,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ]
        ];

        $this->view('estatisticas/index', $dados);
    }

    /**
     * Gera PDF com as estatísticas filtradas
     */
    public function gerarPdf() {
        $data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING);
        $data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING);
        $nome_usuario = filter_input(INPUT_GET, 'nome_usuario', FILTER_SANITIZE_STRING);

        $estatisticas_usuario = null;
        if ($nome_usuario) {
            $estatisticas_usuario = $this->estatisticaModel->calcularEstatisticasTempoUsuario($nome_usuario);
        }

        $dados = [
            'tempo_sessao' => $this->estatisticaModel->calcularTempoSessaoPorUsuario($data_inicio, $data_fim, $nome_usuario),
            'estatisticas_gerais' => $this->estatisticaModel->obterEstatisticasGerais(),
            'tempo_total_sistema' => $this->estatisticaModel->calcularTempoTotalUso(null, $data_inicio, $data_fim),
            'estatisticas_usuario' => $estatisticas_usuario,
            'filtros' => [
                'nome_usuario' => $nome_usuario,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ],
            'data_geracao' => date('d/m/Y H:i:s'),
            'logo_url' => URL . '/public/img/logo.png'
        ];

        try {
            ob_start();
            $this->view('estatisticas/pdf', $dados);
            $html = ob_get_clean();

            $options = new \Dompdf\Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsPhpEnabled(true);
            $options->setIsRemoteEnabled(true);
            $options->setDefaultFont('Helvetica');
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="estatisticas_sistema.pdf"');
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            
            echo $dompdf->output();
            exit();

        } catch (Exception $e) {
            Helper::mensagem('estatisticas', 'Erro ao gerar PDF: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('estatisticas/atividades');
        }
    }
} 