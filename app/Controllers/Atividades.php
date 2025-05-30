<?php

/**
 * [ ATIVIDADES ] - Controlador responsável por gerenciar o registro e exibição das atividades dos usuários no sistema.
 * 
 * Este controlador permite:
 * - Listar todas as atividades dos usuários
 * - Filtrar e pesquisar atividades 
 * - Exibir detalhes de cada atividade
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025-2025 TJGO
 * @version 1.0.1
 * @access protected
 */
class Atividades extends Controllers
{
    private $atividadeModel;

    public function __construct()
    {
        parent::__construct();

        // Verifica se é admin ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Permissão insuficiente', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        $this->atividadeModel = $this->model('AtividadeModel');
    }


    /**
     * [ listar ] - Método responsável por exibir a página de listagem de atividades
     * 
     * Este método carrega os dados necessários para a exibição da página de listagem de atividades,
     * incluindo o título da página.
     * 
     * @return void
     */
    public function listar()
    {
        $dados = [
            'tituloPagina' => 'Atividades dos Usuários'
        ];

        $this->view('atividades/listar', $dados);
    }


    /**
     * [ getAtividades ] - Método responsável por buscar e retornar as atividades dos usuários
     * 
     * Este método recebe parâmetros via POST para filtrar e ordenar as atividades,
     * e retorna os dados em formato JSON para a DataTable do frontend.
     *   
     * @return void
     */
    public function getAtividades()
    {
        try {
            // Debug para ver os parâmetros recebidos
            error_log('Parâmetros recebidos: ' . print_r($_POST, true));

            $params = [
                'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
                'start' => isset($_POST['start']) ? intval($_POST['start']) : 0,
                'length' => isset($_POST['length']) ? intval($_POST['length']) : 10,
                'search' => [
                    'value' => isset($_POST['search']['value']) ? $_POST['search']['value'] : ''
                ],
                'order' => [
                    [
                        'column' => isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0,
                        'dir' => isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc'
                    ]
                ]
            ];

            // Limpa qualquer saída anterior
            if (ob_get_length()) ob_clean();

            // Define o cabeçalho antes de qualquer saída
            header('Content-Type: application/json');

            $atividades = $this->atividadeModel->listarAtividadesDataTable($params);

            // Debug do resultado
            error_log('Resultado da consulta: ' . print_r($atividades, true));

            $response = [
                'draw' => $params['draw'],
                'recordsTotal' => $atividades['recordsTotal'],
                'recordsFiltered' => $atividades['recordsFiltered'],
                'data' => $atividades['data']
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        } catch (Exception $e) {
            error_log('Erro na getAtividades: ' . $e->getMessage());
            echo json_encode([
                'draw' => isset($params['draw']) ? intval($params['draw']) : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Erro ao processar dados'
            ]);
            exit;
        }
    }
}
