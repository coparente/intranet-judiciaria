<?php

// WABA ID - 472202335973627
// Phone Number - +55 62 3216-2929
// Phone Number ID - 642958872237822
// Client - 642958872237822
// Secret - ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF

/**
 * [ SERPROHELPER ] - Classe auxiliar para integração com a API do SERPRO WhatsApp Business.
 * 
 * Esta classe fornece métodos para autenticação e comunicação com a API do SERPRO,
 * permitindo envio e recebimento de mensagens em diversos formatos.
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class SerproHelper
{
    private static $baseUrl;
    private static $clientId;
    private static $clientSecret;
    private static $phoneNumberId;
    private static $wabaId;
    private static $lastError = '';

    public static function init()
    {
        self::$baseUrl = SERPRO_BASE_URL;
        self::$clientId = SERPRO_CLIENT_ID;
        self::$clientSecret = SERPRO_CLIENT_SECRET;
        self::$phoneNumberId = SERPRO_PHONE_NUMBER_ID;
        self::$wabaId = SERPRO_WABA_ID;
    }

    /**
     * Obtém token de acesso seguindo a documentação oficial
     */
    public static function getToken()
    {
        $url = self::$baseUrl . '/oauth2/token';
        
        $data = [
            'client_id' => self::$clientId,
            'client_secret' => self::$clientSecret
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return false;
        }

        if ($httpCode !== 200) {
            self::$lastError = "Erro HTTP: " . $httpCode . " - " . $response;
            return false;
        }

        $responseData = json_decode($response, true);
        
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        }

        self::$lastError = "Token não encontrado na resposta";
        return false;
    }

    /**
     * Envia template (primeira mensagem) seguindo a documentação
     */
    public static function enviarTemplate($destinatario, $nomeTemplate, $parametros = [])
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/template';
        
        $payload = [
            'nomeTemplate' => $nomeTemplate,
            'wabaId' => self::$wabaId,
            'destinatarios' => [$destinatario]
        ];

        // Adiciona parâmetros se fornecidos
        if (!empty($parametros)) {
            $payload['body'] = [
                'parametros' => $parametros
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => self::$lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * Envia mensagem de texto (conversa já iniciada)
     */
    public static function enviarMensagemTexto($destinatario, $mensagem, $messageId = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/texto';
        
        $payload = [
            'destinatario' => $destinatario,
            'body' => $mensagem,
            'preview_url' => false
        ];

        // Adiciona contexto se fornecido (resposta a uma mensagem)
        if ($messageId) {
            $payload['message_id'] = $messageId;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => self::$lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * Envia mídia (imagem, documento, etc.)
     */
    public static function enviarMidia($destinatario, $tipoMidia, $idMedia, $caption = null, $messageId = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/media';
        
        $payload = [
            'destinatario' => $destinatario,
            'tipoMedia' => $tipoMidia,
            'idMedia' => $idMedia
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }

        if ($messageId) {
            $payload['message_id'] = $messageId;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => self::$lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * Faz upload de mídia para a Meta
     */
    public static function uploadMidia($arquivo, $tipoMidia)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/media';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'mediaType' => $tipoMidia,
            'file' => new CURLFile($arquivo['tmp_name'], $tipoMidia, $arquivo['name'])
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => self::$lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * Consulta status de uma requisição
     */
    public static function consultarStatus($idRequisicao)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/' . $idRequisicao;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => self::$lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * Verifica se a API está online
     */
    public static function verificarStatusAPI()
    {
        $token = self::getToken();
        return $token !== false;
    }

    /**
     * Processa webhook recebido
     */
    public static function processarWebhook($payload)
    {
        if (!isset($payload['entry'])) {
            return false;
        }

        foreach ($payload['entry'] as $entry) {
            if (isset($entry['changes'])) {
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        return self::processarMensagens($change['value']);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Processa mensagens do webhook
     */
    private static function processarMensagens($value)
    {
        // Processar mensagens recebidas
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $dadosMensagem = [
                    'id' => $message['id'],
                    'from' => $message['from'],
                    'timestamp' => $message['timestamp'],
                    'type' => $message['type']
                ];

                // Extrair conteúdo baseado no tipo
                switch ($message['type']) {
                    case 'text':
                        $dadosMensagem['text'] = $message['text']['body'];
                        break;
                    case 'image':
                        $dadosMensagem['image'] = $message['image'];
                        break;
                    case 'audio':
                        $dadosMensagem['audio'] = $message['audio'];
                        break;
                    case 'video':
                        $dadosMensagem['video'] = $message['video'];
                        break;
                    case 'document':
                        $dadosMensagem['document'] = $message['document'];
                        break;
                }

                return $dadosMensagem;
            }
        }

        // Processar status de mensagens
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                return [
                    'type' => 'status',
                    'id' => $status['id'],
                    'status' => $status['status'],
                    'timestamp' => $status['timestamp'],
                    'idRequisicao' => $status['idRequisicao'] ?? null
                ];
            }
        }

        return false;
    }

    /**
     * Retorna último erro
     */
    public static function getLastError()
    {
        return self::$lastError;
    }
}

// Inicializar configurações
SerproHelper::init();
