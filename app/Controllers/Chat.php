<?php

/**
 * [ CHAT ] - Controlador responsável por gerenciar o chat via API do SERPRO.
 * 
 * Este controlador permite:
 * - Enviar e receber mensagens via WhatsApp
 * - Gerenciar conversas e contatos
 * - Enviar diferentes tipos de mídia (texto, imagem, documento, vídeo)
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access public
 */
class Chat extends Controllers
{
    private $chatModel;
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->chatModel = $this->model('ChatModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        
        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }

        // Carrega o helper do SERPRO
        require_once APPROOT . '/Libraries/SerproHelper.php';
        
        // Inicializa o SerproHelper com as configurações
        SerproHelper::init();
    }

    /**
     * [ index ] - Exibe a página principal do chat
     */
    public function index()
    {
        $conversas = $this->chatModel->buscarConversas($_SESSION['usuario_id']);
        
        $dados = [
            'tituloPagina' => 'Chat',
            'conversas' => $conversas
        ];

        $this->view('chat/index', $dados);
    }

    /**
     * Exibe conversa específica
     */
    public function conversa($conversa_id = null)
    {
        if (!$conversa_id) {
            Helper::redirecionar('chat');
            return;
        }

        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        $mensagens = $this->chatModel->buscarMensagens($conversa_id);
        
        $dados = [
            'tituloPagina' => 'Conversa - ' . $conversa->contato_nome,
            'conversa' => $conversa,
            'mensagens' => $mensagens
        ];

        $this->view('chat/conversa', $dados);
    }

    /**
     * [ novaConversa ] - Cria uma nova conversa
     */
    public function novaConversa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Formata o número de telefone (remove caracteres não numéricos)
            $numero = preg_replace('/[^0-9]/', '', $formulario['numero']);
            
            // Verifica se já existe uma conversa com este número
            $conversaExistente = $this->chatModel->buscarConversaPorNumero($numero, $_SESSION['usuario_id']);
            
            if ($conversaExistente) {
                Helper::mensagem('chat', '<i class="fas fa-info-circle"></i> Já existe uma conversa com este contato', 'alert alert-info');
                Helper::redirecionar("chat/conversa/{$conversaExistente->id}");
                return;
            }
            
            // Criar nova conversa
            $conversa = $this->chatModel->buscarOuCriarConversa(
                $_SESSION['usuario_id'],
                $numero,
                $formulario['nome'] ?? 'Contato ' . $numero
            );
            
            if ($conversa) {
                Helper::mensagem('chat', '<i class="fas fa-check"></i> Conversa criada com sucesso', 'alert alert-success');
                Helper::redirecionar("chat/conversa/{$conversa->id}");
            } else {
                Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao criar conversa', 'alert alert-danger');
                Helper::redirecionar('chat/index');
            }
        } else {
            $dados = [
                'tituloPagina' => 'Nova Conversa'
            ];

            $this->view('chat/nova_conversa', $dados);
        }
    }

    /**
     * [ enviarMensagem ] - Envia uma mensagem via WhatsApp
     */
    public function enviarMensagem($conversa_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        if (!$conversa_id) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> ID da conversa não informado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        // Buscar conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        $mensagem = trim($_POST['mensagem'] ?? '');
        
        if (empty($mensagem)) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Mensagem não pode estar vazia', 'alert alert-danger');
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        // Verificar se é a primeira mensagem
        $mensagensExistentes = $this->chatModel->contarMensagens($conversa_id);
        $precisaTemplate = ($mensagensExistentes == 0);

        if ($precisaTemplate) {
            // Primeira mensagem - usar template
            $resultado = $this->enviarPrimeiraMensagem($conversa->contato_numero, $mensagem);
        } else {
            // Conversa já iniciada - enviar mensagem normal
            $resultado = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $mensagem);
        }

        if ($resultado['status'] == 200 || $resultado['status'] == 201) {
            // Salvar no banco
            $messageId = null;
            if (isset($resultado['response']['id'])) {
                $messageId = $resultado['response']['id'];
            }

            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa_id,
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'text',
                'conteudo' => $mensagem,
                'message_id' => $messageId ?? uniqid(),
                'status' => 'enviado',
                'enviado_em' => date('Y-m-d H:i:s')
            ]);

            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa_id);

            Helper::mensagem('chat', '<i class="fas fa-check"></i> Mensagem enviada com sucesso', 'alert alert-success');
        } else {
            $erro = $resultado['error'] ?? 'Erro desconhecido';
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao enviar mensagem: ' . $erro, 'alert alert-danger');
        }

        Helper::redirecionar("chat/conversa/{$conversa_id}");
    }

    /**
     * Envia mensagem via AJAX
     */
    public function enviarMensagemAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['numero']) || !isset($input['mensagem'])) {
            echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
            return;
        }

        $numero = preg_replace('/[^0-9]/', '', $input['numero']);
        $mensagem = trim($input['mensagem']);
        $processo_id = $input['processo_id'] ?? null;

        if (empty($mensagem)) {
            echo json_encode(['success' => false, 'error' => 'Mensagem não pode estar vazia']);
            return;
        }

        // Buscar ou criar conversa
        $conversa = $this->chatModel->buscarOuCriarConversa(
            $_SESSION['usuario_id'],
            $numero,
            $input['contato_nome'] ?? 'Contato ' . $numero,
            $processo_id
        );

        if (!$conversa) {
            echo json_encode(['success' => false, 'error' => 'Erro ao criar conversa']);
            return;
        }

        // Verificar se é a primeira mensagem da conversa
        $mensagensExistentes = $this->chatModel->contarMensagens($conversa->id);
        $precisaTemplate = ($mensagensExistentes == 0);

        if ($precisaTemplate) {
            // Primeira mensagem - usar template
            $resultado = $this->enviarPrimeiraMensagem($numero, $mensagem);
        } else {
            // Conversa já iniciada - enviar mensagem normal
            $resultado = SerproHelper::enviarMensagemTexto($numero, $mensagem);
        }

        if ($resultado['status'] == 200 || $resultado['status'] == 201) {
            // Salvar no banco
            $messageId = null;
            if (isset($resultado['response']['id'])) {
                $messageId = $resultado['response']['id'];
            }

            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa->id,
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'text',
                'conteudo' => $mensagem,
                'message_id' => $messageId ?? uniqid(),
                'status' => 'enviado',
                'enviado_em' => date('Y-m-d H:i:s')
            ]);

            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa->id);

            echo json_encode(['success' => true, 'precisou_template' => $precisaTemplate]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => $resultado['error'] ?? 'Erro ao enviar mensagem',
                'details' => $resultado
            ]);
        }
    }

    /**
     * Envia primeira mensagem usando template
     */
    private function enviarPrimeiraMensagem($numero, $mensagem)
    {
        // Nome do template que deve estar aprovado na Meta
        $nomeTemplate = 'simple_greeting'; // Substitua pelo nome do seu template aprovado
        
        // Parâmetros do template (se o template tiver variáveis)
        $parametros = [
            [
                'tipo' => 'text',
                'valor' => $mensagem
            ]
            // ,
            // [
            //     "tipo" => "text",
            //     "valor" => "João Pereira" // {{2}} Nome do agente
            // ],
            // [
            //     "tipo" => "text",
            //     "valor" => "3ª Vara Cível de Goiânia" // {{3}} Comarca
            // ],
            // [
            //     "tipo" => "text",
            //     "valor" => "1234567-89.2024.8.09.0001" // {{4}} Número do processo
            // ]
        ];

        return SerproHelper::enviarTemplate($numero, $nomeTemplate, $parametros);
    }

    /**
     * [ verificarStatusAPI ] - Verifica se a API do SERPRO está online (AJAX)
     */
    public function verificarStatusAPI()
    {
        // Limpa qualquer saída anterior
        ob_clean();
        
        // Define o cabeçalho para JSON
        header('Content-Type: application/json');
        
        try {
            $status = SerproHelper::verificarStatusAPI();
            $response = [
                'online' => $status,
            ];
            
            // Adiciona mensagem de erro se houver
            if (!$status) {
                $response['error'] = SerproHelper::getLastError();
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'online' => false,
                'error' => $e->getMessage()
            ]);
        }
        
        // Garante que nenhum HTML seja adicionado à resposta
        exit;
    }

    /**
     * Lista mensagens via AJAX
     */
    public function mensagens($conversa_id = null)
    {
        if (!$conversa_id) {
            echo json_encode(['error' => 'ID da conversa não informado']);
            return;
        }

        $mensagens = $this->chatModel->buscarMensagens($conversa_id);
        echo json_encode($mensagens);
    }

    /**
     * Busca conversas via AJAX
     */
    public function conversas()
    {
        $conversas = $this->chatModel->buscarConversas($_SESSION['usuario_id']);
        echo json_encode($conversas);
    }

    /**
     * Marca mensagens como lidas
     */
    public function marcarLida()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['conversa_id'])) {
            echo json_encode(['success' => false]);
            return;
        }

        $resultado = $this->chatModel->marcarConversaComoLida($input['conversa_id']);
        echo json_encode(['success' => $resultado]);
    }

    /**
     * Webhook para receber mensagens do SERPRO
     */
    public function webhook()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Verificação do webhook
            $verify_token = $_GET['hub_verify_token'] ?? '';
            $challenge = $_GET['hub_challenge'] ?? '';
            
            if ($verify_token === WEBHOOK_VERIFY_TOKEN) {
                echo $challenge;
                exit;
            } else {
                http_response_code(403);
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents('php://input');
            $payload = json_decode($input, true);
            
            if ($payload) {
                $mensagem = SerproHelper::processarWebhook($payload);
                
                if ($mensagem && $mensagem['type'] !== 'status') {
                    // Processar mensagem recebida
                    $this->processarMensagemRecebida($mensagem);
                }
            }
            
            http_response_code(200);
            echo 'OK';
            exit;
        }
    }

    /**
     * Processa mensagem recebida via webhook
     */
    private function processarMensagemRecebida($mensagem)
    {
        $numero = $mensagem['from'];
        
        // Buscar ou criar conversa
        $conversa = $this->chatModel->buscarOuCriarConversaPorNumero($numero);
        
        if ($conversa) {
            // Salvar mensagem recebida
            $conteudo = '';
            switch ($mensagem['type']) {
                case 'text':
                    $conteudo = $mensagem['text'];
                    break;
                case 'image':
                case 'audio':
                case 'video':
                case 'document':
                    $conteudo = json_encode($mensagem[$mensagem['type']]);
                    break;
            }
            
            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa->id,
                'remetente_id' => null, // Mensagem recebida
                'tipo' => $mensagem['type'],
                'conteudo' => $conteudo,
                'message_id' => $mensagem['id'],
                'status' => 'recebido',
                'recebido_em' => date('Y-m-d H:i:s', $mensagem['timestamp'])
            ]);
            
            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa->id);
        }
    }

    /**
     * [ excluirConversa ] - Exclui uma conversa
     */
    public function excluirConversa($id = null)
    {
        // Verifica se tem permissão para acessar o módulo
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
        ) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::mensagemSweetAlert('dashboard', 'Acesso negado: Apenas administradores e analistas podem acessar essa página', 'error');
            Helper::redirecionar('dashboard/inicial');
        }
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> ID da conversa não fornecido', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Verificar se a conversa existe
        $conversa = $this->chatModel->buscarConversaPorId($id);
        if (!$conversa) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Excluir a conversa
        $resultado = $this->chatModel->excluirConversa($id);
        
        if ($resultado) {
            Helper::mensagem('chat', '<i class="fas fa-check-circle"></i> Conversa excluída com sucesso', 'alert alert-success');
            Helper::redirecionar('chat/index');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Erro ao excluir conversa', 'alert alert-danger');
            Helper::redirecionar('chat/index');
        }
    }

    /**
     * [ gerenciarTemplates ] - Gerencia templates do WhatsApp Business
     */
    public function gerenciarTemplates()
    {
        // Detectar se é uma requisição AJAX
        $isAjax = $_SERVER['REQUEST_METHOD'] == 'POST' || 
                 isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 401, 'error' => 'Usuário não autenticado. Faça login novamente.']);
                return;
            }
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado. Apenas administradores podem gerenciar templates.']);
                return;
            }
            
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Definir cabeçalho JSON para todas as respostas AJAX
            header('Content-Type: application/json');
            
            $acao = $_POST['acao'] ?? '';
            
            try {
                switch ($acao) {
                    case 'listar':
                        $templates = SerproHelper::listarTemplates();
                        echo json_encode($templates);
                        break;
                        
                    case 'criar':
                        $dadosTemplate = [
                            'name' => $_POST['name'],
                            'category' => $_POST['category'],
                            'language' => $_POST['language'],
                            'components' => json_decode($_POST['components'], true)
                        ];
                        $resultado = SerproHelper::criarTemplate($dadosTemplate);
                        echo json_encode($resultado);
                        break;
                        
                    case 'excluir':
                        $nomeTemplate = $_POST['nome_template'];
                        $resultado = SerproHelper::excluirTemplate($nomeTemplate);
                        echo json_encode($resultado);
                        break;
                        
                    default:
                        echo json_encode(['status' => 400, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 500, 'error' => 'Erro interno: ' . $e->getMessage()]);
            }
            
            return;
        }

        // Para requisições GET, carregar templates diretamente no PHP
        $templates = [];
        $templateError = null;
        
        try {
            $resultado = SerproHelper::listarTemplates();
            if ($resultado['status'] == 200 && isset($resultado['response'])) {
                $templates = $resultado['response'];
            } else {
                $templateError = 'Erro ao carregar templates: ' . ($resultado['error'] ?? 'Erro desconhecido');
            }
        } catch (Exception $e) {
            $templateError = 'Erro ao conectar com a API: ' . $e->getMessage();
        }

        $dados = [
            'tituloPagina' => 'Gerenciar Templates',
            'templates' => $templates,
            'templateError' => $templateError
        ];
        
        $this->view('chat/templates', $dados);
    }

    /**
     * [ gerenciarWebhooks ] - Gerencia webhooks do SERPRO
     */
    public function gerenciarWebhooks()
    {
        // Detectar se é uma requisição AJAX
        $isAjax = $_SERVER['REQUEST_METHOD'] == 'POST' || 
                 isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 401, 'error' => 'Usuário não autenticado. Faça login novamente.']);
                return;
            }
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado. Apenas administradores podem gerenciar webhooks.']);
                return;
            }
            
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Definir cabeçalho JSON para todas as respostas AJAX
            header('Content-Type: application/json');
            
            $acao = $_POST['acao'] ?? '';
            
            try {
                switch ($acao) {
                    case 'listar':
                        $resultado = SerproHelper::listarWebhooks();
                        echo json_encode($resultado);
                        break;
                        
                    case 'cadastrar':
                        $webhook = [
                            'uri' => $_POST['uri'],
                            'jwtToken' => $_POST['jwt_token'] ?? null
                        ];
                        $resultado = SerproHelper::cadastrarWebhook($webhook);
                        echo json_encode($resultado);
                        break;
                        
                    case 'atualizar':
                        $webhook = [
                            'id' => $_POST['webhook_id'],
                            'uri' => $_POST['uri'],
                            'jwtToken' => $_POST['jwt_token'] ?? null
                        ];
                        $resultado = SerproHelper::atualizarWebhook($webhook);
                        echo json_encode($resultado);
                        break;
                        
                    case 'excluir':
                        $webhookId = $_POST['webhook_id'];
                        $resultado = SerproHelper::excluirWebhook($webhookId);
                        echo json_encode($resultado);
                        break;
                        
                    default:
                        echo json_encode(['status' => 400, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 500, 'error' => 'Erro interno: ' . $e->getMessage()]);
            }
            
            return;
        }

        // Para requisições GET, carregar webhooks diretamente no PHP
        $webhooks = [];
        $webhookError = null;
        
        try {
            $resultado = SerproHelper::listarWebhooks();
            if ($resultado['status'] == 200 && isset($resultado['response']['data'])) {
                $webhooks = $resultado['response']['data'];
            } else {
                $webhookError = 'Erro ao carregar webhooks: ' . ($resultado['error'] ?? 'Erro desconhecido');
            }
        } catch (Exception $e) {
            $webhookError = 'Erro ao conectar com a API: ' . $e->getMessage();
        }

        $dados = [
            'tituloPagina' => 'Gerenciar Webhooks',
            'webhooks' => $webhooks,
            'webhookError' => $webhookError
        ];
        
        $this->view('chat/webhooks', $dados);
    }

    /**
     * [ uploadMidia ] - Faz upload de mídia para a Meta
     */
    public function uploadMidia()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Erro no upload do arquivo']);
            return;
        }

        $arquivo = $_FILES['file'];
        $tipoMidia = $_POST['media_type'] ?? $arquivo['type'];

        // Validar tipo de arquivo
        $tiposPermitidos = [
            'image/jpeg', 'image/png', 'image/gif',
            'video/mp4', 'video/3gpp',
            'audio/aac', 'audio/amr', 'audio/mpeg', 'audio/mp4', 'audio/ogg',
            'application/pdf', 'application/msword', 'text/plain',
            'application/vnd.ms-powerpoint', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($tipoMidia, $tiposPermitidos)) {
            echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido']);
            return;
        }

        // Verificar tamanho
        $limiteTamanho = 5 * 1024 * 1024; // 5MB padrão
        if (strpos($tipoMidia, 'video/') === 0 || strpos($tipoMidia, 'audio/') === 0) {
            $limiteTamanho = 16 * 1024 * 1024; // 16MB para vídeo/áudio
        } elseif (strpos($tipoMidia, 'application/') === 0) {
            $limiteTamanho = 95 * 1024 * 1024; // 95MB para documentos
        }

        if ($arquivo['size'] > $limiteTamanho) {
            echo json_encode(['success' => false, 'error' => 'Arquivo muito grande']);
            return;
        }

        $resultado = SerproHelper::uploadMidia($arquivo, $tipoMidia);
        
        if ($resultado['status'] == 200) {
            echo json_encode([
                'success' => true,
                'media_id' => $resultado['response']['id'],
                'media_type' => $tipoMidia
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $resultado['error'] ?? 'Erro no upload'
            ]);
        }
    }

    /**
     * [ downloadMidia ] - Baixa mídia da Meta
     */
    public function downloadMidia($media_id = null)
    {
        if (!$media_id) {
            http_response_code(404);
            return;
        }

        $resultado = SerproHelper::downloadMidia($media_id);
        
        if ($resultado['status'] == 200) {
            // Definir headers apropriados
            header('Content-Type: ' . $resultado['content_type']);
            header('Content-Disposition: attachment; filename="' . $media_id . '"');
            echo $resultado['data'];
        } else {
            http_response_code(404);
        }
    }

    /**
     * [ enviarMensagemInterativa ] - Envia mensagens com botões ou listas
     */
    public function enviarMensagemInterativa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['conversa_id']) || !isset($input['tipo'])) {
            echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
            return;
        }

        $conversa = $this->chatModel->buscarConversaPorId($input['conversa_id']);
        
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['success' => false, 'error' => 'Conversa não encontrada']);
            return;
        }

        $resultado = false;
        
        switch ($input['tipo']) {
            case 'botoes':
                $resultado = SerproHelper::enviarMensagemBotoes(
                    $conversa->contato_numero,
                    $input['texto_body'],
                    $input['botoes'],
                    $input['message_id'] ?? null
                );
                break;
                
            case 'lista':
                $resultado = SerproHelper::enviarMensagemLista(
                    $conversa->contato_numero,
                    $input['texto_body'],
                    $input['button_text'],
                    $input['secoes'],
                    $input['message_id'] ?? null
                );
                break;
                
            case 'localizacao':
                $resultado = SerproHelper::enviarSolicitacaoLocalizacao(
                    $conversa->contato_numero,
                    $input['texto_body'],
                    $input['message_id'] ?? null
                );
                break;
        }

        if ($resultado && ($resultado['status'] == 200 || $resultado['status'] == 201)) {
            // Salvar no banco
            $this->chatModel->salvarMensagem([
                'conversa_id' => $input['conversa_id'],
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'interativa_' . $input['tipo'],
                'conteudo' => json_encode($input),
                'message_id' => $resultado['response']['id'] ?? uniqid(),
                'status' => 'enviado',
                'enviado_em' => date('Y-m-d H:i:s')
            ]);

            $this->chatModel->atualizarConversa($input['conversa_id']);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $resultado['error'] ?? 'Erro ao enviar mensagem interativa'
            ]);
        }
    }

    /**
     * [ consultarStatus ] - Consulta status detalhado de uma mensagem
     */
    public function consultarStatus($requisicao_id = null)
    {
        if (!$requisicao_id) {
            echo json_encode(['error' => 'ID da requisição não informado']);
            return;
        }

        $resultado = SerproHelper::consultarStatus($requisicao_id);
        echo json_encode($resultado);
    }

    /**
     * [ qrCode ] - Gerencia QR Codes para conexão
     */
    public function qrCode()
    {
        // Detectar se é uma requisição AJAX
        $isAjax = $_SERVER['REQUEST_METHOD'] == 'POST' || 
                 isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 401, 'error' => 'Usuário não autenticado. Faça login novamente.']);
                return;
            }
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado. Apenas administradores podem gerenciar QR codes.']);
                return;
            }
            
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Definir cabeçalho JSON para todas as respostas AJAX
            header('Content-Type: application/json');
            
            $acao = $_POST['acao'] ?? '';
            
            try {
                switch ($acao) {
                    case 'gerar':
                        $dados = [
                            'mensagem_preenchida' => $_POST['mensagem'] ?? 'Olá! Entre em contato conosco.',
                            'codigo' => $_POST['codigo'] ?? ''
                        ];
                        $resultado = SerproHelper::gerarQRCode($dados);
                        echo json_encode($resultado);
                        break;
                        
                    case 'listar':
                        $resultado = SerproHelper::listarQRCodes();
                        echo json_encode($resultado);
                        break;
                        
                    case 'excluir':
                        $qrId = $_POST['qr_id'];
                        $resultado = SerproHelper::excluirQRCode($qrId);
                        echo json_encode($resultado);
                        break;
                        
                    default:
                        echo json_encode(['status' => 400, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 500, 'error' => 'Erro interno: ' . $e->getMessage()]);
            }
            
            return;
        }

        // Para requisições GET, carregar QR codes diretamente no PHP
        $qrCodes = [];
        $qrCodeError = null;
        
        try {
            $resultado = SerproHelper::listarQRCodes();
            if ($resultado['status'] == 200 && isset($resultado['response'])) {
                $qrCodes = $resultado['response'];
            } else {
                $qrCodeError = 'Erro ao carregar QR codes: ' . ($resultado['error'] ?? 'Erro desconhecido');
            }
        } catch (Exception $e) {
            $qrCodeError = 'Erro ao conectar com a API: ' . $e->getMessage();
        }

        $dados = [
            'tituloPagina' => 'QR Codes',
            'qrCodes' => $qrCodes,
            'qrCodeError' => $qrCodeError
        ];
        
        $this->view('chat/qr_codes', $dados);
    }

    /**
     * [ metricas ] - Exibe métricas do chat
     */
    public function metricas()
    {
        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        $inicio = $_GET['inicio'] ?? date('Y-m-01'); // Primeiro dia do mês
        $fim = $_GET['fim'] ?? date('Y-m-d'); // Hoje

        // Métricas da API SERPRO
        $metricas = SerproHelper::obterMetricas($inicio, $fim);
        
        // Métricas locais do banco de dados
        $metricasLocais = [
            'total_conversas' => $this->chatModel->contarConversas($_SESSION['usuario_id']),
            'mensagens_enviadas' => $this->chatModel->contarMensagensEnviadas($_SESSION['usuario_id']),
            'mensagens_recebidas' => $this->chatModel->contarMensagensRecebidas($_SESSION['usuario_id']),
            'conversas_ativas' => $this->chatModel->contarConversasAtivas($_SESSION['usuario_id'])
        ];

        $dados = [
            'tituloPagina' => 'Métricas do Chat',
            'metricas' => $metricas,
            'metricas_locais' => $metricasLocais,
            'periodo' => ['inicio' => $inicio, 'fim' => $fim]
        ];

        $this->view('chat/metricas', $dados);
    }

    /**
     * [ configuracoes ] - Página de configurações do chat
     */
    public function configuracoes()
    {
        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Atualizar configurações
            $configuracoes = [
                'template_padrao' => $_POST['template_padrao'] ?? '',
                'webhook_url' => $_POST['webhook_url'] ?? '',
                'auto_resposta' => isset($_POST['auto_resposta']) ? 1 : 0,
                'horario_atendimento' => $_POST['horario_atendimento'] ?? ''
            ];
            
            // Salvar no banco ou arquivo de configuração
            foreach ($configuracoes as $chave => $valor) {
                $this->chatModel->salvarConfiguracao($chave, $valor);
            }
            
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Configurações salvas com sucesso', 'alert alert-success');
        }

        $configuracoes = $this->chatModel->obterConfiguracoes();
        
        $dados = [
            'tituloPagina' => 'Configurações do Chat',
            'configuracoes' => $configuracoes
        ];

        $this->view('chat/configuracoes', $dados);
    }

    /**
     * [ carregarNovasMensagens ] - Carrega novas mensagens de uma conversa via AJAX
     */
    public function carregarNovasMensagens($conversa_id = null, $ultima_mensagem_id = 0)
    {
        if (!$conversa_id) {
            echo json_encode(['error' => 'ID da conversa não informado']);
            return;
        }

        // Verificar se o usuário tem acesso à conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['error' => 'Acesso negado']);
            return;
        }

        $novasMensagens = $this->chatModel->buscarNovasMensagens($conversa_id, $ultima_mensagem_id);
        
        echo json_encode([
            'success' => true,
            'mensagens' => $novasMensagens
        ]);
    }

    /**
     * [ testarAPI ] - Testa conectividade com a API SERPRO
     */
    public function testarAPI()
    {
        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado']);
                return;
            }
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Testa obtenção de token
                $token = SerproHelper::getToken();
                
                if ($token) {
                    // Testa listagem de templates
                    $templates = SerproHelper::listarTemplates();
                    
                    echo json_encode([
                        'status' => 200,
                        'token_obtido' => true,
                        'token_length' => strlen($token),
                        'api_templates' => $templates,
                        'configuracoes' => [
                            'base_url' => SERPRO_BASE_URL,
                            'client_id' => SERPRO_CLIENT_ID,
                            'waba_id' => SERPRO_WABA_ID,
                            'phone_number_id' => SERPRO_PHONE_NUMBER_ID
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'status' => 401,
                        'token_obtido' => false,
                        'error' => SerproHelper::getLastError(),
                        'configuracoes' => [
                            'base_url' => SERPRO_BASE_URL,
                            'client_id' => SERPRO_CLIENT_ID,
                            'waba_id' => SERPRO_WABA_ID,
                            'phone_number_id' => SERPRO_PHONE_NUMBER_ID
                        ]
                    ]);
                }
                
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 500,
                    'error' => 'Erro interno: ' . $e->getMessage()
                ]);
            }
            
            return;
        }

        Helper::redirecionar('chat/configuracoes');
    }
}