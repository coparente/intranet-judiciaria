<?php 

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Message;
use App\Models\User;
use App\Services\SerproService;
use App\Config\App;

/**
 * Controlador responsável por processar webhooks da API Serpro.
 */
class WebhookController
{
    /**
     * Processa webhook recebido da API Serpro.
     * 
     * @return void
     */
    public function receive()
    {
        try {
            // Obtém os dados do webhook
            $webhookData = Request::getBody();
            
            // Log do webhook recebido
            error_log('Webhook recebido: ' . json_encode($webhookData));

            // Processa os dados do webhook
            $processedData = SerproService::processWebhook($webhookData);

            if (!$processedData['success']) {
                error_log('Erro ao processar webhook: ' . $processedData['message']);
                return Response::json(['error' => $processedData['message']], 400);
            }

            $data = $processedData['data'];

            // Busca o usuário pelo número de telefone configurado
            // Aqui você pode implementar uma lógica para determinar qual usuário deve receber a mensagem
            // Por enquanto, vamos usar o primeiro usuário encontrado
            $users = User::getAll();
            
            if (empty($users)) {
                error_log('Nenhum usuário encontrado para processar webhook');
                return Response::json(['error' => 'Nenhum usuário configurado'], 500);
            }

            $userId = $users[0]['id']; // Usa o primeiro usuário (pode ser melhorado)

            // Verifica se é uma mensagem recebida ou status de mensagem
            if ($data['type'] === 'status') {
                // Processa atualização de status
                $this->processStatusUpdate($userId, $data);
            } else {
                // Processa mensagem recebida
                $this->processReceivedMessage($userId, $data);
            }

            return Response::json([
                'success' => true,
                'message' => 'Webhook processado com sucesso'
            ], 200);

        } catch (\Exception $e) {
            error_log('Erro ao processar webhook: ' . $e->getMessage());
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Processa mensagem recebida.
     * 
     * @param int $userId ID do usuário
     * @param array $data Dados da mensagem
     * @return void
     */
    private function processReceivedMessage($userId, $data)
    {
        // Formata o número do remetente
        $fromNumber = SerproService::formatPhoneNumber($data['from']);

        // Salva a mensagem recebida no banco de dados
        $messageData = [
            'usuario_id' => $userId,
            'numero' => $fromNumber,
            'mensagem' => $data['message'],
            'direcao' => 'recebida',
            'status' => 'recebida',
            'message_id' => $data['message_id'] ?? null
        ];

        if (Message::save($messageData)) {
            // Aqui você pode implementar lógica de resposta automática
            if (App::get('webhook.auto_reply_enabled', true)) {
                $this->processAutoReply($userId, $fromNumber, $data['message']);
            }
        } else {
            error_log('Erro ao salvar mensagem do webhook no banco');
        }
    }

    /**
     * Processa atualização de status de mensagem.
     * 
     * @param int $userId ID do usuário
     * @param array $data Dados do status
     * @return void
     */
    private function processStatusUpdate($userId, $data)
    {
        try {
            // Busca a mensagem pelo message_id da Serpro
            $message = $this->findMessageBySerproId($data['id']);
            
            if ($message) {
                // Mapeia o status da Serpro para status local
                $localStatus = $this->mapSerproStatus($data['status']);
                
                // Atualiza o status da mensagem
                Message::updateStatus($message['id'], $localStatus);
                
                error_log("Status atualizado: Mensagem ID {$message['id']} -> {$localStatus}");
            } else {
                error_log("Mensagem não encontrada para atualização de status: {$data['id']}");
            }
        } catch (\Exception $e) {
            error_log('Erro ao processar atualização de status: ' . $e->getMessage());
        }
    }

    /**
     * Busca mensagem pelo message_id da Serpro.
     * 
     * @param string $serproMessageId ID da mensagem da Serpro
     * @return array|false Dados da mensagem ou false se não encontrada
     */
    private function findMessageBySerproId($serproMessageId)
    {
        $pdo = \App\Models\Database::getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM mensagens WHERE message_id = ?");
        $stmt->execute([$serproMessageId]);
        
        return $stmt->fetch();
    }

    /**
     * Mapeia status da Serpro para status local.
     * 
     * @param string $serproStatus Status retornado pela Serpro
     * @return string Status local
     */
    private function mapSerproStatus($serproStatus)
    {
        $statusMap = [
            'sent' => 'enviada',
            'delivered' => 'entregue',
            'read' => 'lida',
            'failed' => 'falhou',
            'pending' => 'pendente'
        ];

        return $statusMap[$serproStatus] ?? 'pendente';
    }

    /**
     * Processa resposta automática (opcional).
     * 
     * @param int $userId ID do usuário
     * @param string $fromNumber Número do remetente
     * @param string $message Mensagem recebida
     * @return void
     */
    private function processAutoReply($userId, $fromNumber, $message)
    {
        try {
            // Exemplo de resposta automática simples
            $autoReply = $this->generateAutoReply($message);
            
            if ($autoReply) {
                // Configurações da API Serpro
                $clientId = App::get('serpro.client_id', '642958872237822');
                $clientSecret = App::get('serpro.client_secret', 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF');
                $wabaId = App::get('serpro.waba_id', '472202335973627');
                $phoneNumberId = App::get('serpro.phone_number_id', '642958872237822');
                $phoneNumber = App::get('serpro.phone_number', '5511999999999');
                $baseUrl = App::get('serpro.base_url', 'https://api.whatsapp.serpro.gov.br');
                
                $serproService = new SerproService($clientId, $clientSecret, $wabaId, $phoneNumberId, $phoneNumber, $baseUrl);
                
                // Envia resposta automática
                $result = $serproService->sendMessage($fromNumber, $autoReply);
                
                if ($result['success']) {
                    // Salva a resposta automática no banco
                    $replyData = [
                        'usuario_id' => $userId,
                        'numero' => $fromNumber,
                        'mensagem' => $autoReply,
                        'direcao' => 'enviada',
                        'status' => 'enviada'
                    ];
                    
                    Message::save($replyData);
                }
            }
        } catch (\Exception $e) {
            error_log('Erro ao processar resposta automática: ' . $e->getMessage());
        }
    }

    /**
     * Gera resposta automática baseada na mensagem recebida.
     * 
     * @param string $message Mensagem recebida
     * @return string|null Resposta automática ou null se não houver resposta
     */
    private function generateAutoReply($message)
    {
        $message = strtolower(trim($message));
        
        // Exemplos de respostas automáticas
        $autoReplies = [
            'oi' => 'Olá! Como posso ajudá-lo hoje?',
            'ola' => 'Olá! Como posso ajudá-lo hoje?',
            'bom dia' => 'Bom dia! Como posso ajudá-lo hoje?',
            'boa tarde' => 'Boa tarde! Como posso ajudá-lo hoje?',
            'boa noite' => 'Boa noite! Como posso ajudá-lo hoje?',
            'ajuda' => 'Estou aqui para ajudar! Digite "menu" para ver as opções disponíveis.',
            'menu' => 'Opções disponíveis:\n1. Informações\n2. Suporte\n3. Contato\nDigite o número da opção desejada.',
            '1' => 'Aqui estão as informações sobre nossos serviços...',
            '2' => 'Para suporte, entre em contato conosco pelo e-mail: suporte@exemplo.com',
            '3' => 'Nosso telefone de contato é: (11) 99999-9999'
        ];
        
        return $autoReplies[$message] ?? null;
    }

    /**
     * Endpoint para verificar se o webhook está funcionando.
     * 
     * @return void
     */
    public function status()
    {
        return Response::json([
            'success' => true,
            'message' => 'Webhook está funcionando',
            'timestamp' => date('Y-m-d H:i:s'),
            'webhook_url' => App::getWebhookUrl()
        ], 200);
    }
} 