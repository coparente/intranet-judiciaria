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
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['conversa_id'])) {
                echo json_encode(['success' => false, 'error' => 'ID da conversa não fornecido']);
                return;
            }

            $conversa_id = $input['conversa_id'];
            
            // Verificar se a conversa pertence ao usuário logado
            $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
            
            if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'error' => 'Conversa não encontrada ou sem permissão']);
                return;
            }

            // Excluir a conversa
            $resultado = $this->chatModel->excluirConversa($conversa_id);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Conversa excluída com sucesso']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erro ao excluir conversa']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
        }
    }

    // /**
    //  * [ excluir ] - Exclui um processo
    //  * 
    //  * @param int $id ID do processo
    //  * @return void
    //  */
    // public function excluir($id = null)
    // {
    //     // Verifica se tem permissão para acessar o módulo
    //     if (
    //         !isset($_SESSION['usuario_perfil']) ||
    //         !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
    //     ) {
    //         Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
    //         Helper::mensagemSweetAlert('dashboard', 'Acesso negado: Apenas administradores e analistas podem acessar essa página', 'error');
    //         Helper::redirecionar('dashboard/inicial');
    //     }
    //     // Verificar se o ID foi fornecido
    //     if (!$id) {
    //         Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
    //         Helper::redirecionar('ciri/listar');
    //         return;
    //     }

    //     // Verificar se o processo existe
    //     $processo = $this->ciriModel->obterProcessoPorId($id);
    //     if (!$processo) {
    //         Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
    //         Helper::redirecionar('ciri/listar');
    //         return;
    //     }

    //     // Excluir o processo
    //     if ($this->ciriModel->excluirProcesso($id)) {
    //         Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo excluído com sucesso', 'alert alert-success');
    //         Helper::mensagemSweetAlert('ciri', 'Processo excluído com sucesso', 'success');
    //     } else {
    //         Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao excluir processo', 'alert alert-danger');
    //         Helper::mensagemSweetAlert('ciri', 'Erro ao excluir processo', 'error');
    //     }

    //     Helper::redirecionar('ciri/listar');
    // }
}
}