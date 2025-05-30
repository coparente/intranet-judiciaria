<?php

/**
 * [ PROJUDI ] - Controlador responsável por consultar o WebService do Projudi.
 * 
 * Este controlador permite:
 * - Consultar processos via WebService do Projudi
 * - Visualizar detalhes completos de processos
 * - Exibir informações de partes e movimentações
 * 
 * @author Cleyton Parente <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access public
 */
class Projudi extends Controllers
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
     * [ index ] - Exibe a página principal de consulta do Projudi
     * 
     * @return void
     */
    public function index()
    {
        // Verifica permissão para o módulo de consulta do Projudi
        Middleware::verificarPermissao(5); // ID do módulo 'Consultar Projudi'

        $dados = [
            'tituloPagina' => 'Consulta Projudi',
            'numeroProcesso' => isset($_POST['numeroProcesso']) ? $_POST['numeroProcesso'] : '',
            'resultado' => null,
            'erro' => null
        ];

        // Processar a consulta se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numeroProcesso'])) {
            $resultado = $this->consultarProcesso($_POST['numeroProcesso']);
            
            if ($resultado === null) {
                $dados['erro'] = 'Não foi possível obter dados do processo. Verifique o número e tente novamente.';
                Helper::mensagem('projudi', '<i class="fas fa-exclamation-triangle"></i> ' . $dados['erro'], 'alert alert-danger');
                Helper::mensagemSweetAlert('projudi', $dados['erro'], 'error');
            } else {
                $dados['resultado'] = $resultado;
            }
        }

        $this->view('projudi/index', $dados);
    }

    /**
     * [ consultarProcesso ] - Consulta os dados de um processo via WebService do Projudi
     * 
     * @param string $numeroProcesso Número do processo a ser consultado
     * @return SimpleXMLElement|null Dados do processo ou null se não encontrado
     */
    private function consultarProcesso($numeroProcesso)
    {
        // Usar o método simplificado que está funcionando
        return $this->consultaServico("https://projudi.tjgo.jus.br/ServicosPublicos?PaginaAtual=2&a=" . urlencode($numeroProcesso));
    }
    
    /**
     * [ consultaServico ] - Função auxiliar para fazer requisição cURL e retornar o resultado como SimpleXMLElement
     * 
     * @param string $url URL do serviço a ser consultado
     * @return SimpleXMLElement|null Resultado da consulta ou null se falhar
     */
    private function consultaServico($url)
    {
        // Configuração básica do cURL (como no exemplo que funciona)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Executar a requisição
        $response = curl_exec($ch);
        
        // Verificar erros
        if (curl_errno($ch)) {
            error_log('Erro cURL Projudi: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        // Salvar a resposta para debug (opcional)
        // if (!is_dir(APPROOT . '/logs')) {
        //     mkdir(APPROOT . '/logs', 0755, true);
        // }
        // file_put_contents(APPROOT . '/logs/projudi_response_' . date('Y-m-d_H-i-s') . '.xml', $response);
        
        // Tenta carregar o XML da resposta
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            error_log('Erro ao processar XML do Projudi');
            return null;
        }
        
        // Verificar se obteve resposta válida
        if (isset($xml->processos->dadosProcesso)) {
            return $xml->processos->dadosProcesso;
        }
        
        return null;
    }

   
    
} 