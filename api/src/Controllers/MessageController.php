<?php 

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Http\JWT;
use App\Models\Message;
use App\Models\User;
use App\Services\SerproService;
use App\Config\App;

/**
 * Controlador responsável por gerenciar as operações de mensagens do sistema de chat.
 */
class MessageController
{
    private $serproService;

    /**
     * Construtor do MessageController.
     */
    public function __construct()
    {
        // Configurações da API Serpro
        $clientId = App::get('serpro.client_id', '642958872237822');
        $clientSecret = App::get('serpro.client_secret', 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF');
        $wabaId = App::get('serpro.waba_id', '472202335973627');
        $phoneNumberId = App::get('serpro.phone_number_id', '642958872237822');
        $phoneNumber = App::get('serpro.phone_number', '5511999999999');
        $baseUrl = App::get('serpro.base_url', 'https://api.whatsapp.serpro.gov.br');
        
        $this->serproService = new SerproService($clientId, $clientSecret, $wabaId, $phoneNumberId, $phoneNumber, $baseUrl);
    }

    /**
     * Envia uma mensagem via WhatsApp.
     * 
     * @return void
     */
    public function send()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $data = Request::getBody();

            // Valida dados obrigatórios
            if (empty($data['numero']) || empty($data['mensagem'])) {
                return Response::json(['error' => 'Número e mensagem são obrigatórios'], 400);
            }

            // Valida formato do número
            if (!SerproService::validatePhoneNumber($data['numero'])) {
                return Response::json(['error' => 'Formato de número inválido'], 400);
            }

            // Formata o número
            $formattedNumber = SerproService::formatPhoneNumber($data['numero']);

            // Verifica se é uma primeira mensagem (template)
            $isFirstMessage = isset($data['is_first_message']) && $data['is_first_message'] === true;
            $templateName = isset($data['template_name']) ? $data['template_name'] : null;

            // Envia mensagem via API Serpro
            if ($isFirstMessage) {
                // Envia template (primeira mensagem)
                $result = $this->serproService->sendTemplate($formattedNumber, $data['mensagem'], $templateName);
            } else {
                // Envia mensagem normal
                $result = $this->serproService->sendMessage($formattedNumber, $data['mensagem']);
            }

            if (!$result['success']) {
                return Response::json(['error' => $result['error']], 400);
            }

            // Salva mensagem no banco de dados
            $messageData = [
                'usuario_id' => $userData['id'],
                'numero' => $formattedNumber,
                'mensagem' => $data['mensagem'],
                'direcao' => 'enviada',
                'status' => 'enviada',
                'tipo' => $isFirstMessage ? 'template' : 'text'
            ];

            if (Message::save($messageData)) {
                return Response::json([
                    'success' => true,
                    'message' => $isFirstMessage ? 'Template enviado com sucesso' : 'Mensagem enviada com sucesso',
                    'data' => [
                        'message_id' => $result['message_id'],
                        'numero' => $formattedNumber,
                        'status' => 'enviada',
                        'tipo' => $isFirstMessage ? 'template' : 'text'
                    ]
                ], 200);
            } else {
                return Response::json(['error' => 'Erro ao salvar mensagem no banco'], 500);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lista mensagens do usuário autenticado.
     * 
     * @return void
     */
    public function list()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $query = Request::getQuery();
            $limit = isset($query['limit']) ? (int)$query['limit'] : 50;
            $offset = isset($query['offset']) ? (int)$query['offset'] : 0;

            // Aplica filtros se fornecidos
            $filters = [];
            if (!empty($query['numero'])) {
                $filters['numero'] = SerproService::formatPhoneNumber($query['numero']);
            }
            if (!empty($query['direcao'])) {
                $filters['direcao'] = $query['direcao'];
            }
            if (!empty($query['status'])) {
                $filters['status'] = $query['status'];
            }
            if (!empty($query['data_inicio'])) {
                $filters['data_inicio'] = $query['data_inicio'];
            }
            if (!empty($query['data_fim'])) {
                $filters['data_fim'] = $query['data_fim'];
            }

            $messages = Message::getWithFilters($userData['id'], $filters, $limit, $offset);

            return Response::json([
                'success' => true,
                'data' => $messages,
                'total' => count($messages)
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtém uma conversa específica por número.
     * 
     * @return void
     */
    public function conversation()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $query = Request::getQuery();
            
            if (empty($query['numero'])) {
                return Response::json(['error' => 'Número é obrigatório'], 400);
            }

            // Formata o número
            $formattedNumber = SerproService::formatPhoneNumber($query['numero']);
            
            $limit = isset($query['limit']) ? (int)$query['limit'] : 50;
            $offset = isset($query['offset']) ? (int)$query['offset'] : 0;

            $conversation = Message::getConversation($userData['id'], $formattedNumber, $limit, $offset);

            return Response::json([
                'success' => true,
                'data' => $conversation,
                'numero' => $formattedNumber,
                'total' => count($conversation)
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtém os últimos contatos do usuário.
     * 
     * @return void
     */
    public function contacts()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $query = Request::getQuery();
            $limit = isset($query['limit']) ? (int)$query['limit'] : 20;

            $contacts = Message::getLastContacts($userData['id'], $limit);

            return Response::json([
                'success' => true,
                'data' => $contacts,
                'total' => count($contacts)
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtém uma mensagem específica por ID.
     * 
     * @param array $params Parâmetros da URL (primeiro elemento é o ID)
     * @return void
     */
    public function show($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            // Obtém o ID do primeiro parâmetro
            $id = $params[0] ?? null;
            if (!$id) {
                return Response::json(['error' => 'ID da mensagem não fornecido'], 400);
            }

            $message = Message::find($id);

            if (!$message) {
                return Response::json(['error' => 'Mensagem não encontrada'], 404);
            }

            // Verifica se a mensagem pertence ao usuário
            if ($message['usuario_id'] != $userData['id']) {
                return Response::json(['error' => 'Acesso negado'], 403);
            }

            return Response::json([
                'success' => true,
                'data' => $message
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Deleta uma mensagem específica.
     * 
     * @param array $params Parâmetros da URL
     * @return void
     */
    public function delete($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $messageId = $params['id'] ?? null;
            if (!$messageId) {
                return Response::json(['error' => 'ID da mensagem é obrigatório'], 400);
            }

            // Busca a mensagem
            $message = Message::findById($messageId);
            if (!$message) {
                return Response::json(['error' => 'Mensagem não encontrada'], 404);
            }

            // Verifica se a mensagem pertence ao usuário
            if ($message['usuario_id'] != $userData['id']) {
                return Response::json(['error' => 'Acesso negado'], 403);
            }

            // Deleta a mensagem
            if (Message::delete($messageId)) {
                return Response::json([
                    'success' => true,
                    'message' => 'Mensagem deletada com sucesso'
                ], 200);
            } else {
                return Response::json(['error' => 'Erro ao deletar mensagem'], 500);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza o status de uma mensagem.
     * 
     * @param array $params Parâmetros da URL
     * @return void
     */
    public function updateStatus($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $messageId = $params['id'] ?? null;
            if (!$messageId) {
                return Response::json(['error' => 'ID da mensagem é obrigatório'], 400);
            }

            $data = Request::getBody();
            $newStatus = $data['status'] ?? null;
            
            if (!$newStatus) {
                return Response::json(['error' => 'Status é obrigatório'], 400);
            }

            // Valida status permitidos
            $allowedStatuses = ['enviada', 'entregue', 'lida', 'falhou', 'pendente'];
            if (!in_array($newStatus, $allowedStatuses)) {
                return Response::json(['error' => 'Status inválido'], 400);
            }

            // Busca a mensagem
            $message = Message::findById($messageId);
            if (!$message) {
                return Response::json(['error' => 'Mensagem não encontrada'], 404);
            }

            // Verifica se a mensagem pertence ao usuário
            if ($message['usuario_id'] != $userData['id']) {
                return Response::json(['error' => 'Acesso negado'], 403);
            }

            // Atualiza o status
            if (Message::updateStatus($messageId, $newStatus)) {
                return Response::json([
                    'success' => true,
                    'message' => 'Status atualizado com sucesso',
                    'data' => [
                        'id' => $messageId,
                        'status' => $newStatus
                    ]
                ], 200);
            } else {
                return Response::json(['error' => 'Erro ao atualizar status'], 500);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marca mensagens como lidas.
     * 
     * @param array $params Parâmetros da URL
     * @return void
     */
    public function markAsRead($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $data = Request::getBody();
            $messageIds = $data['message_ids'] ?? [];
            $numero = $data['numero'] ?? null;

            if (empty($messageIds) && !$numero) {
                return Response::json(['error' => 'IDs das mensagens ou número é obrigatório'], 400);
            }

            $updatedCount = 0;

            if (!empty($messageIds)) {
                // Marca mensagens específicas como lidas
                foreach ($messageIds as $messageId) {
                    $message = Message::findById($messageId);
                    if ($message && $message['usuario_id'] == $userData['id'] && $message['direcao'] == 'recebida') {
                        if (Message::updateStatus($messageId, 'lida')) {
                            $updatedCount++;
                        }
                    }
                }
            } else {
                // Marca todas as mensagens recebidas do número como lidas
                $formattedNumber = SerproService::formatPhoneNumber($numero);
                $updatedCount = Message::markAllAsRead($userData['id'], $formattedNumber);
            }

            return Response::json([
                'success' => true,
                'message' => 'Mensagens marcadas como lidas',
                'data' => [
                    'updated_count' => $updatedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtém estatísticas de status das mensagens.
     * 
     * @return void
     */
    public function statusStats()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $query = Request::getQuery();
            $dataInicio = $query['data_inicio'] ?? null;
            $dataFim = $query['data_fim'] ?? null;

            $stats = Message::getStatusStats($userData['id'], $dataInicio, $dataFim);

            return Response::json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verifica o status de uma mensagem na API Serpro.
     * 
     * @param array $params Parâmetros da URL
     * @return void
     */
    public function checkSerproStatus($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $messageId = $params['id'] ?? null;
            if (!$messageId) {
                return Response::json(['error' => 'ID da mensagem é obrigatório'], 400);
            }

            // Busca a mensagem
            $message = Message::findById($messageId);
            if (!$message) {
                return Response::json(['error' => 'Mensagem não encontrada'], 404);
            }

            // Verifica se a mensagem pertence ao usuário
            if ($message['usuario_id'] != $userData['id']) {
                return Response::json(['error' => 'Acesso negado'], 403);
            }

            // Verifica se tem message_id da Serpro
            if (empty($message['message_id'])) {
                return Response::json(['error' => 'Mensagem não possui ID da Serpro'], 400);
            }

            // Consulta status na API Serpro
            $result = $this->serproService->getMessageStatus($message['message_id']);

            if ($result['success']) {
                // Atualiza status no banco se necessário
                $serproStatus = $result['data']['status'] ?? 'unknown';
                $localStatus = $this->mapSerproStatus($serproStatus);
                
                if ($localStatus != $message['status']) {
                    Message::updateStatus($messageId, $localStatus);
                }

                return Response::json([
                    'success' => true,
                    'data' => [
                        'message_id' => $messageId,
                        'serpro_status' => $serproStatus,
                        'local_status' => $localStatus,
                        'details' => $result['data']
                    ]
                ], 200);
            } else {
                return Response::json(['error' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marca mensagens como lidas na API Serpro automaticamente.
     * 
     * @param array $params Parâmetros da URL
     * @return void
     */
    public function markAsReadSerpro($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $data = Request::getBody();
            $messageIds = $data['message_ids'] ?? [];
            $numero = $data['numero'] ?? null;

            if (empty($messageIds) && !$numero) {
                return Response::json(['error' => 'IDs das mensagens ou número é obrigatório'], 400);
            }

            $updatedCount = 0;
            $serproUpdatedCount = 0;

            if (!empty($messageIds)) {
                // Marca mensagens específicas como lidas
                foreach ($messageIds as $messageId) {
                    $message = Message::findById($messageId);
                    if ($message && $message['usuario_id'] == $userData['id'] && $message['direcao'] == 'recebida') {
                        if (Message::updateStatus($messageId, 'lida')) {
                            $updatedCount++;
                            
                            // Marca como lida na API Serpro se tiver message_id
                            if (!empty($message['message_id'])) {
                                $result = $this->serproService->marcarComoLida($message['message_id']);
                                if ($result['success']) {
                                    $serproUpdatedCount++;
                                }
                            }
                        }
                    }
                }
            } else {
                // Marca todas as mensagens recebidas do número como lidas
                $formattedNumber = SerproService::formatPhoneNumber($numero);
                $messages = Message::getConversation($userData['id'], $formattedNumber, 100, 0);
                
                foreach ($messages as $message) {
                    if ($message['direcao'] == 'recebida' && $message['status'] != 'lida') {
                        if (Message::updateStatus($message['id'], 'lida')) {
                            $updatedCount++;
                            
                            // Marca como lida na API Serpro se tiver message_id
                            if (!empty($message['message_id'])) {
                                $result = $this->serproService->marcarComoLida($message['message_id']);
                                if ($result['success']) {
                                    $serproUpdatedCount++;
                                }
                            }
                        }
                    }
                }
            }

            return Response::json([
                'success' => true,
                'message' => 'Mensagens marcadas como lidas',
                'data' => [
                    'updated_count' => $updatedCount,
                    'serpro_updated_count' => $serproUpdatedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
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
} 