<?php

/**
 * [ RELATORIOS ] - Controlador responsável por gerenciar os relatórios do sistema.
 * 
 * Este controlador permite:
 * - Gerar relatórios de produtividade
 * - Visualizar estatísticas por usuário
 * - Exportar dados em diferentes formatos
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class Relatorios extends Controllers {

    private $processoModel;
    private $usuarioModel;
    private $atividadeModel;

    public function __construct() {
        parent::__construct();

        // Verifica permissões
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Permissão insuficiente', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        $this->processoModel = $this->model('ProcessoCustasModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->atividadeModel = $this->model('AtividadeModel');
    }

    /**
     * [ produtividade ] - Método responsável por gerar o relatório de produtividade
     * 
     * Este método carrega os dados necessários para a exibição do relatório de produtividade,
     * incluindo o título da página e os filtros de data e usuário.
     * 
     * @return void
     */
    public function produtividade() {
        $data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) 
            ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'data_inicio'))) 
            : date('Y-m-01');
        
        $data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING)
            ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'data_fim')))
            : date('Y-m-d');
        
        $usuario_id = filter_input(INPUT_GET, 'usuario_id', FILTER_SANITIZE_NUMBER_INT);

        $dados = [
            'tituloPagina' => 'Relatório de Produtividade',
            'usuarios' => $this->usuarioModel->listarUsuarios(),
            'filtros' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'usuario_id' => $usuario_id
            ]
        ];

        // Busca dados de produtividade (para um usuário específico ou todos)
        if ($usuario_id) {
            $dados['produtividade'] = $this->processoModel->obterProdutividadeDetalhada(
                $usuario_id,
                $data_inicio,
                $data_fim
            );
            
            // Adiciona resumo do usuário
            $dados['resumo_usuario'] = $this->processoModel->obterResumoProdutividadeUsuario(
                $usuario_id,
                $data_inicio,
                $data_fim
            );
            
            // Adiciona nome do usuário
            $usuario = $this->usuarioModel->buscarUsuarioPorId($usuario_id);
            if ($usuario) {
                $dados['nome_usuario'] = $usuario->nome;
            }
        } else {
            // Busca dados de todos os usuários
            $dados['produtividade_geral'] = $this->processoModel->obterProdutividadeTodosUsuarios(
                $data_inicio,
                $data_fim
            );
            
            // Adiciona resumo geral
            $dados['resumo_geral'] = $this->processoModel->obterResumoProdutividadeGeral(
                $data_inicio,
                $data_fim
            );
            
            // Adiciona log para depuração
            error_log('Resumo Geral: ' . print_r($dados['resumo_geral'], true));
            error_log('Produtividade Geral: ' . print_r($dados['produtividade_geral'], true));
        }

        $this->view('relatorios/produtividade', $dados);
    }
    
    /**
     * [ gerarPdfProdutividade ] - Método responsável por gerar o PDF do relatório de produtividade
     * 
     * Este método gera um arquivo PDF com os dados de produtividade filtrados pelo usuário.
     * 
     * @return void
     */
    public function gerarPdfProdutividade() {
        $data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) 
            ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'data_inicio'))) 
            : date('Y-m-01');
        
        $data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING)
            ? date('Y-m-d', strtotime(filter_input(INPUT_GET, 'data_fim')))
            : date('Y-m-d');
        
        $usuario_id = filter_input(INPUT_GET, 'usuario_id', FILTER_SANITIZE_NUMBER_INT);

        $dados = [
            'tituloPagina' => 'Relatório de Produtividade',
            'filtros' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'usuario_id' => $usuario_id
            ],
            'data_geracao' => date('d/m/Y H:i:s')
        ];

        if ($usuario_id) {
            $dados['produtividade'] = $this->processoModel->obterProdutividadeDetalhada(
                $usuario_id,
                $data_inicio,
                $data_fim
            );
            
            $dados['resumo_usuario'] = $this->processoModel->obterResumoProdutividadeUsuario(
                $usuario_id,
                $data_inicio,
                $data_fim
            );
            
            // Busca o nome do usuário
            $usuario = $this->usuarioModel->buscarUsuarioPorId($usuario_id);
            if ($usuario) {
                $dados['nome_usuario'] = $usuario->nome;
            } else {
                $dados['nome_usuario'] = 'Usuário #' . $usuario_id;
            }
        } else {
            $dados['produtividade_geral'] = $this->processoModel->obterProdutividadeTodosUsuarios(
                $data_inicio,
                $data_fim
            );
            
            $dados['resumo_geral'] = $this->processoModel->obterResumoProdutividadeGeral(
                $data_inicio,
                $data_fim
            );
        }

        try {
            ob_start();
            $this->view('relatorios/produtividade_pdf', $dados);
            $html = ob_get_clean();

            $options = new \Dompdf\Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsPhpEnabled(true);
            $options->setIsRemoteEnabled(true);
            $options->setDefaultFont('Helvetica');
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="relatorio_produtividade.pdf"');
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            
            echo $dompdf->output();
            exit();

        } catch (Exception $e) {
            Helper::mensagem('relatorios', 'Erro ao gerar PDF: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('relatorios/produtividade');
        }
    }
} 