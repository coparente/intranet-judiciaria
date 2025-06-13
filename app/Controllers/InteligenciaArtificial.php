<?php

/**
 * [ INTELIGENCIA ARTIFICIAL ] - Controlador responsável por gerenciar as funcionalidades de IA.
 * 
 * Este controlador permite:
 * - Analisar documentos PDF com IA
 * - Gerar descrições e análises de documentos
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access public
 */
class IA extends Controllers
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
     * [ index ] - Exibe a página principal do módulo de IA
     * 
     * @return void
     */
    public function index()
    {
        $dados = [
            'tituloPagina' => 'Inteligência Artificial',
            'descricaoTexto' => '',
            'descricao' => isset($_POST['descricao']) ? $_POST['descricao'] : ''
        ];

        // Processar o upload e análise do PDF se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile']) && isset($_POST['descricao'])) {
            $dados['descricaoTexto'] = $this->processarPDF($_FILES['pdfFile'], $_POST['descricao']);
        }

        $this->view('inteligencia_artificial/index', $dados);
    }

    /**
     * [ processarPDF ] - Processa o arquivo PDF e gera a descrição usando a API do Google
     * 
     * @param array $pdfFile Arquivo PDF enviado
     * @param string $userDescription Descrição fornecida pelo usuário
     * @return string Texto da descrição gerada
     */
    private function processarPDF($pdfFile, $userDescription)
    {
        // Configurações
        $apiKey = API_KEY;
        $descricaoTexto = '';

        if ($pdfFile['type'] === 'application/pdf') {
            // Carregar o parser de PDF
            require_once 'vendor/autoload.php';
            $parser = new \Smalot\PdfParser\Parser();
            
            try {
                $pdf = $parser->parseFile($pdfFile['tmp_name']);
                $pdfContent = $pdf->getText();
                $pdfContent = substr($pdfContent, 0, 4000); // Limite de 4000 caracteres

                $descricao = $this->descreverPDF($apiKey, $pdfContent, $userDescription);

                // Extrair o texto específico da resposta
                if (isset($descricao['candidates'][0]['content']['parts'][0]['text'])) {
                    $descricaoTexto = $descricao['candidates'][0]['content']['parts'][0]['text'];
                } else {
                    Helper::mensagem('ia', '<i class="fas fa-exclamation-triangle"></i> Não foi possível gerar a descrição. Tente novamente.', 'alert alert-warning');
                    Helper::mensagemSweetAlert('ia', 'Não foi possível gerar a descrição. Tente novamente.', 'warning');
                }
            } catch (Exception $e) {
                Helper::mensagem('ia', '<i class="fas fa-exclamation-triangle"></i> Erro ao processar o PDF: ' . $e->getMessage(), 'alert alert-danger');
                Helper::mensagemSweetAlert('ia', 'Erro ao processar o PDF: ' . $e->getMessage(), 'error');
            }
        } else {
            Helper::mensagem('ia', '<i class="fas fa-exclamation-triangle"></i> Por favor, envie um arquivo PDF válido.', 'alert alert-warning');
            Helper::mensagemSweetAlert('ia', 'Por favor, envie um arquivo PDF válido.', 'warning');
        }

        return $descricaoTexto;
    }

    /**
     * [ descreverPDF ] - Envia o conteúdo do PDF para a API do Google Gemini
     * 
     * @param string $apiKey Chave da API do Google
     * @param string $pdfContent Conteúdo do PDF
     * @param string $userDescription Descrição fornecida pelo usuário
     * @return array Resposta da API
     */
    private function descreverPDF($apiKey, $pdfContent, $userDescription)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Descreva o conteúdo do PDF de acordo com a seguinte solicitação: \"$userDescription\". O conteúdo é: $pdfContent"
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Helper::mensagemSweetAlert('ia', 'Erro na API: ' . curl_error($ch), 'error');
            Helper::mensagem('ia', '<i class="fas fa-exclamation-triangle"></i> Erro na API: ' . curl_error($ch), 'alert alert-danger');
            return null;
        }

        curl_close($ch);
        return json_decode($response, true);
    }
} 