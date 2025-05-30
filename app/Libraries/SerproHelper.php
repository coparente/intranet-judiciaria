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
     * Obtém token de acesso
     */
    public static function getToken()
    {
        $url = self::$baseUrl . '/oauth/token';
        
        $data = [
            'grant_type' => 'client_credentials',
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
     * Verifica status da API
     */
    public static function verificarStatusAPI()
    {
        $token = self::getToken();
        
        if (!$token) {
            return [
                'online' => false,
                'error' => 'Falha na autenticação: ' . self::$lastError
            ];
        }

        // Testar endpoint de status ou informações da conta
        $url = self::$baseUrl . '/client/' . self::$clientId . '/v2/conta';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'online' => false,
                'error' => 'Erro de conexão: ' . $error
            ];
        }

        if ($httpCode === 200) {
            return [
                'online' => true,
                'message' => 'API funcionando normalmente'
            ];
        } else {
            return [
                'online' => false,
                'error' => 'API retornou código: ' . $httpCode,
                'response' => $response
            ];
        }
    }

    /**
     * Envia mensagem de texto (funciona sem template após primeira conversa)
     */
    public static function enviarMensagem($numero, $tipo, $conteudo, $caption = '', $filename = '')
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Falha na autenticação: ' . self::$lastError];
        }

        // Formatar número (remover caracteres especiais)
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        // URL correta baseada na documentação do SERPRO
        $url = self::$baseUrl . '/client/' . self::$clientId . '/v2/requisicao/mensagem';

        // Estrutura da mensagem baseada na documentação
        $data = [
            'wabaId' => self::$wabaId,
            'destinatarios' => [$numero]
        ];

        // Configurar dados por tipo
        switch ($tipo) {
            case 'text':
                $data['tipo'] = 'text';
                $data['conteudo'] = $conteudo;
                break;

            case 'image':
                $data['tipo'] = 'image';
                $data['midia'] = $conteudo; // URL da imagem
                if ($caption) {
                    $data['legenda'] = $caption;
                }
                break;

            case 'document':
                $data['tipo'] = 'document';
                $data['midia'] = $conteudo; // URL do documento
                if ($filename) {
                    $data['nomeArquivo'] = $filename;
                }
                if ($caption) {
                    $data['legenda'] = $caption;
                }
                break;

            case 'video':
                $data['tipo'] = 'video';
                $data['midia'] = $conteudo; // URL do vídeo
                if ($caption) {
                    $data['legenda'] = $caption;
                }
                break;

            case 'audio':
                $data['tipo'] = 'audio';
                $data['midia'] = $conteudo; // URL do áudio
                break;

            default:
                return ['status' => 400, 'error' => 'Tipo de mensagem não suportado'];
        }

        // Fazer requisição
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 500, 'error' => $error];
        }

        return [
            'status' => $httpCode,
            'response' => json_decode($response, true),
            'error' => $httpCode !== 200 ? json_decode($response, true)['error']['message'] ?? 'Erro desconhecido' : null
        ];
    }

    /**
     * Envia template (necessário para iniciar conversa)
     */
    public static function enviarTemplate($numero, $nomeTemplate, $parametros = [], $idMedia = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Falha na autenticação: ' . self::$lastError];
        }

        // Formatar número
        $numero = preg_replace('/[^0-9]/', '', $numero);

        // URL correta baseada no seu exemplo
        $url = self::$baseUrl . '/client/' . self::$clientId . '/v2/requisicao/mensagem/template';

        $data = [
            'nomeTemplate' => $nomeTemplate,
            'wabaId' => self::$wabaId,
            'destinatarios' => [$numero]
        ];

        if (!empty($parametros)) {
            $data['body'] = ['parametros' => $parametros];
        }

        if (!empty($idMedia)) {
            $data['header'] = ['idMedia' => $idMedia];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
            return ['status' => 500, 'error' => $error];
        }

        return [
            'status' => $httpCode,
            'response' => json_decode($response, true),
            'error' => $httpCode !== 200 && $httpCode !== 201 ? json_decode($response, true)['error']['message'] ?? 'Erro desconhecido' : null
        ];
    }

    /**
     * Verifica se já existe conversa ativa (últimas 24h)
     */
    public static function temConversaAtiva($numero)
    {
        // Aqui você pode implementar lógica para verificar se há conversa ativa
        // Por enquanto, vamos assumir que sempre precisa de template para iniciar
        return false;
    }

    /**
     * Processa webhook recebido
     */
    public static function processarWebhook($payload)
    {
        // Log do payload para debug
        error_log("Webhook recebido: " . json_encode($payload));

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
                    'timestamp' => $status['timestamp']
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
