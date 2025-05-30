<?php

class Webhook extends Controller
{
    private $chatModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
    }

    /**
     * Endpoint para receber webhooks do WhatsApp
     */
    public function whatsapp()
    {
        // Verificação do webhook (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->verificarWebhook();
            return;
        }

        // Processar webhook (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarWebhook();
            return;
        }

        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }

    /**
     * Verifica webhook (challenge do WhatsApp)
     */
    private function verificarWebhook()
    {
        $mode = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';

        // Token de verificação (configure no seu .env)
        $verify_token = WEBHOOK_VERIFY_TOKEN ?? 'meu_token_secreto';

        if ($mode === 'subscribe' && $token === $verify_token) {
            echo $challenge;
        } else {
            http_response_code(403);
            echo 'Forbidden';
        }
    }

    /**
     * Processa webhooks recebidos
     */
    private function processarWebhook()
    {
        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        // Log para debug
        error_log("Webhook recebido: " . $input);

        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Payload inválido']);
            return;
        }

        // Processar usando SerproHelper
        $resultado = SerproHelper::processarWebhook($payload);

        if ($resultado) {
            if (isset($resultado['type']) && $resultado['type'] === 'status') {
                // Atualizar status da mensagem
                $this->atualizarStatusMensagem($resultado);
            } else {
                // Salvar mensagem recebida
                $this->salvarMensagemRecebida($resultado);
            }
        }

        // Sempre responder com 200 para o WhatsApp
        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    }

    /**
     * Salva mensagem recebida no banco
     */
    private function salvarMensagemRecebida($dados)
    {
        $numero = $dados['from'];
        $conteudo = '';
        $tipo = $dados['type'];
        $midia_url = null;
        $midia_nome = null;

        // Extrair conteúdo baseado no tipo
        switch ($tipo) {
            case 'text':
                $conteudo = $dados['text'];
                break;
            case 'image':
                $midia_url = $dados['image']['link'] ?? null;
                $conteudo = $dados['image']['caption'] ?? '';
                break;
            case 'audio':
                $midia_url = $dados['audio']['link'] ?? null;
                break;
            case 'video':
                $midia_url = $dados['video']['link'] ?? null;
                $conteudo = $dados['video']['caption'] ?? '';
                break;
            case 'document':
                $midia_url = $dados['document']['link'] ?? null;
                $midia_nome = $dados['document']['filename'] ?? null;
                $conteudo = $dados['document']['caption'] ?? '';
                break;
        }

        // Buscar conversa existente ou criar nova
        $conversa = $this->chatModel->buscarOuCriarConversa(
            null, // Mensagem recebida não tem usuário específico
            $numero,
            'Contato ' . $numero
        );

        if ($conversa) {
            // Salvar mensagem
            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa->id,
                'remetente_id' => null, // Mensagem recebida
                'tipo' => $tipo,
                'conteudo' => $conteudo,
                'midia_url' => $midia_url,
                'midia_nome' => $midia_nome,
                'message_id' => $dados['id'],
                'status' => 'recebido',
                'lido' => 0,
                'enviado_em' => date('Y-m-d H:i:s', $dados['timestamp'])
            ]);

            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa->id);
        }
    }

    /**
     * Atualiza status de mensagem
     */
    private function atualizarStatusMensagem($dados)
    {
        $this->chatModel->atualizarStatusMensagem(
            $dados['id'],
            $dados['status']
        );
    }
} 