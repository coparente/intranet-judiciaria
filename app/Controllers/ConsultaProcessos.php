<?php

/**
 * [ CONSULTA PROCESSOS ] - Controlador responsável por consultar movimentações de processos.
 * 
 * Este controlador permite:
 * - Consultar processos via API do DataJud
 * - Visualizar movimentações processuais
 * - Exibir detalhes de processos judiciais
 * 
 * @author Cleyton Parente <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access public
 */
class ConsultaProcessos extends Controllers
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = $this->model('UsuarioModel');
        
        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }
    }

    /**
     * [ index ] - Exibe a página principal de consulta de processos
     * 
     * @return void
     */
    public function index()
    {
        // Verifica permissão para o módulo de consulta de processos
        Middleware::verificarPermissao(5); // ID do módulo 'Consultar Processos'

        $dados = [
            'tituloPagina' => 'Consulta de Processos',
            'numeroProcesso' => isset($_POST['numeroProcesso']) ? $_POST['numeroProcesso'] : '',
            'resultado' => null,
            'erro' => null
        ];

        // Processar a consulta se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numeroProcesso'])) {
            $resultado = $this->consultarProcesso($_POST['numeroProcesso']);
            
            if (isset($resultado['erro'])) {
                $dados['erro'] = $resultado['erro'];
                Helper::mensagem('consulta_processos', '<i class="fas fa-exclamation-triangle"></i> ' . $resultado['erro'], 'alert alert-danger');
                Helper::mensagemSweetAlert('consulta_processos', $resultado['erro'], 'error');
            } else {
                $dados['resultado'] = $resultado;
            }
        }

        $this->view('consulta_processos/index', $dados);
    }

    /**
     * [ consultarProcesso ] - Consulta os dados de um processo via API do DataJud
     * 
     * @param string $numeroProcesso Número do processo a ser consultado
     * @return array Dados do processo ou mensagem de erro
     */
    private function consultarProcesso($numeroProcesso)
    {
        // URL da API
        $url = URL_DATAJUD;

        // Formatar o número do processo (remover caracteres especiais)
        $numeroProcesso = preg_replace('/[^0-9]/', '', $numeroProcesso);

        // Dados a serem enviados na requisição
        $data = [
            "query" => [
                "match" => [
                    "numeroProcesso" => $numeroProcesso
                ]
            ]
        ];

        // Converta os dados para o formato JSON
        $json_data = json_encode($data);

        // Configurações do cabeçalho
        $headers = [
            'Authorization: ApiKey ' . API_KEY_DATAJUD, // Chave pública
            'Content-Type: application/json'
        ];

        // Inicializa a sessão cURL
        $ch = curl_init($url);

        // Configura opções para a requisição
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Executa a requisição
        $response = curl_exec($ch);

        // Verifica se ocorreu um erro
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['erro' => 'Erro na consulta: ' . curl_error($ch)];
        }

        // Fecha a sessão cURL
        curl_close($ch);

        // Decodifica a resposta JSON
        $response_data = json_decode($response, true);

        // Verifica se a resposta é válida e contém dados
        if (isset($response_data['hits']['hits']) && !empty($response_data['hits']['hits'])) {
            return $response_data['hits']['hits'][0]['_source'];
        } else {
            return ['erro' => 'Nenhum processo encontrado com o número informado.'];
        }
    }
} 