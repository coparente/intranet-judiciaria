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
                'parametros' => $parametros,
                'header' => [
                    'filename' =>  "tjgo.png",
                    'linkMedia' => "https://coparente.top/intranet/public/img/tjgo.png"
                ]
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
    public static function enviarMidia($destinatario, $tipoMidia, $idMedia, $caption = null, $messageId = null, $filename = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/media';
        
        // Para documentos, tentar diferentes variações de campo filename
        if ($tipoMidia === 'document' && $filename) {
            $variationsToTry = [
                ['filename' => $filename],
                ['fileName' => $filename], 
                ['name' => $filename],
                ['nomeArquivo' => $filename],
                ['document' => ['filename' => $filename]],
                ['document' => ['fileName' => $filename]],
                ['document' => ['name' => $filename]]
            ];
            
            foreach ($variationsToTry as $index => $variation) {
                $payload = [
                    'destinatario' => $destinatario,
                    'tipoMedia' => $tipoMidia,
                    'idMedia' => $idMedia
                ];
                
                // Adicionar a variação atual
                $payload = array_merge($payload, $variation);
                
                // Adicionar message_id se fornecido
                if ($messageId) {
                    $payload['message_id'] = $messageId;
                }
                
                error_log("MÍDIA: Tentativa " . ($index + 1) . " - Payload: " . json_encode($payload));
                
                $resultado = self::executarRequisicaoMidia($url, $token, $payload);
                
                if ($resultado['status'] === 200 || $resultado['status'] === 201) {
                    error_log("MÍDIA: Sucesso com variação " . ($index + 1) . " - " . json_encode($variation));
                    return $resultado;
                } else {
                    error_log("MÍDIA: Tentativa " . ($index + 1) . " falhou - Status: " . $resultado['status']);
                }
            }
            
            // Se chegou até aqui, nenhuma variação funcionou
            error_log("MÍDIA: Todas as variações falharam");
            return $resultado; // Retorna o último resultado
        }
        
        // Para outros tipos de mídia (imagem, vídeo, áudio)
        $payload = [
            'destinatario' => $destinatario,
            'tipoMedia' => $tipoMidia,
            'idMedia' => $idMedia
        ];

        // Regras para outros tipos
        if ($tipoMidia === 'image') {
            if ($caption) {
                $payload['caption'] = $caption;
            }
        } elseif ($tipoMidia === 'video' || $tipoMidia === 'audio') {
            if ($caption) {
                $payload['caption'] = $caption;
            }
        }

        if ($messageId) {
            $payload['message_id'] = $messageId;
        }

        error_log("MÍDIA: Tipo='$tipoMidia', Payload enviado - " . json_encode($payload));
        
        return self::executarRequisicaoMidia($url, $token, $payload);
    }
    
    /**
     * Executa a requisição de mídia
     */
    private static function executarRequisicaoMidia($url, $token, $payload)
    {
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
     * Faz upload de mídia para a Meta
     */
    public static function uploadMidia($arquivo, $tipoMidia)
    {
        $token = self::getToken();
        if (!$token) {
            error_log("Erro ao obter token: " . self::$lastError);
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/media';
        
        // Verificar se o arquivo existe
        if (!file_exists($arquivo['tmp_name'])) {
            error_log("Arquivo temporário não existe: " . $arquivo['tmp_name']);
            return ['status' => 400, 'error' => 'Arquivo temporário não encontrado'];
        }
        
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

        // Log apenas em caso de erro
        if ($error || $httpCode >= 400) {
            error_log("ERRO UPLOAD - HTTP: $httpCode, cURL: $error, Response: $response");
        }

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

    /**
     * [ listarTemplates ] - Lista templates aprovados
     */
    public static function listarTemplates()
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/waba/' . self::$wabaId . '/v2/templates';
        
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
     * [ criarTemplate ] - Cria um novo template
     */
    public static function criarTemplate($dadosTemplate)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/waba/' . self::$wabaId . '/v2/templates';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dadosTemplate));
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
     * [ excluirTemplate ] - Exclui um template
     */
    public static function excluirTemplate($nomeTemplate)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/waba/' . self::$wabaId . '/v2/templates/' . $nomeTemplate;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
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
     * [ cadastrarWebhook ] - Cadastra webhook
     */
    public static function cadastrarWebhook($webhook)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/webhook';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook));
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
     * [ listarWebhooks ] - Lista webhooks cadastrados
     */
    public static function listarWebhooks()
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/webhook';
        
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
     * [ atualizarWebhook ] - Atualiza webhook
     */
    public static function atualizarWebhook($webhook)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/webhook/' . $webhook['id'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook));
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
     * [ excluirWebhook ] - Exclui webhook
     */
    public static function excluirWebhook($webhookId)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/webhook/' . $webhookId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
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
     * [ downloadMidia ] - Baixa mídia da Meta
     */
    public static function downloadMidia($mediaId)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/media/' . $mediaId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::$lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => self::$lastError];
        }

        return [
            'status' => $httpCode,
            'data' => $response,
            'content_type' => $contentType,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ enviarMensagemBotoes ] - Envia mensagem com botões
     */
    public static function enviarMensagemBotoes($destinatario, $textoBody, $botoes, $messageId = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/interativa-botoes';
        
        $payload = [
            'destinatario' => $destinatario,
            'textoBody' => $textoBody,
            'buttons' => $botoes
        ];

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
     * [ enviarMensagemLista ] - Envia mensagem com lista
     */
    public static function enviarMensagemLista($destinatario, $textoBody, $buttonText, $secoes, $messageId = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/interativa-lista';
        
        $payload = [
            'destinatario' => $destinatario,
            'textoBody' => $textoBody,
            'button' => $buttonText,
            'secoes' => $secoes
        ];

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
     * [ enviarSolicitacaoLocalizacao ] - Solicita localização do usuário
     */
    public static function enviarSolicitacaoLocalizacao($destinatario, $textoBody, $messageId = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/interativa-localizacao';
        
        $payload = [
            'destinatario' => $destinatario,
            'textoBody' => $textoBody
        ];

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
     * [ gerarQRCode ] - Gera QR Code para conectar
     */
    public static function gerarQRCode($dados)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/qrcode';
        
        // Estrutura correta conforme documentação da API SERPRO
        $payload = [
            'mensagemPrePreenchida' => $dados['mensagem_preenchida'] ?? $dados['mensagem'] ?? 'Olá! Entre em contato conosco.',
            'tipoDeImagem' => 'PNG'
        ];
        
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
     * [ listarQRCodes ] - Lista QR Codes criados
     */
    public static function listarQRCodes()
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/qrcode?tipoDeImagem=PNG';
        
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
     * [ listarQRCodesComImagem ] - Lista QR Codes com URLs das imagens
     */
    public static function listarQRCodesComImagem()
    {
        return self::listarQRCodes(); // Usa a função atual que já tem tipoDeImagem=PNG
    }

    /**
     * [ listarQRCodesSemImagem ] - Lista QR Codes com códigos e links (sem imagem)
     */
    public static function listarQRCodesSemImagem()
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/qrcode'; // SEM tipoDeImagem=PNG
        
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
     * [ listarQRCodesCombinados ] - Lista QR Codes combinando dados e imagens
     */
    public static function listarQRCodesCombinados()
    {
        // Buscar dados (código, link, mensagem)
        $dadosQR = self::listarQRCodesSemImagem();
        
        if ($dadosQR['status'] !== 200 || empty($dadosQR['response'])) {
            return $dadosQR; // Retorna erro ou lista vazia
        }

        // Buscar imagens
        $imagensQR = self::listarQRCodesComImagem();
        
        if ($imagensQR['status'] !== 200 || empty($imagensQR['response'])) {
            // Se não conseguir buscar imagens, retorna só os dados
            return $dadosQR;
        }

        // Combinar dados e imagens
        $qrCombinados = [];
        
        foreach ($dadosQR['response'] as $index => $qrDados) {
            $qrCompleto = $qrDados; // Começa com os dados
            
            // Adiciona imagem se existir na mesma posição
            if (isset($imagensQR['response'][$index]['qrImageUrl'])) {
                $qrCompleto['qrImageUrl'] = $imagensQR['response'][$index]['qrImageUrl'];
            }
            
            $qrCombinados[] = $qrCompleto;
        }

        return [
            'status' => 200,
            'response' => $qrCombinados,
            'error' => null,
            'combinado' => true // Flag para indicar que foi combinado
        ];
    }

    /**
     * [ excluirQRCode ] - Exclui QR Code
     */
    public static function excluirQRCode($qrId)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/qrcode/' . $qrId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
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
     * [ obterMetricas ] - Obtém métricas da API
     */
    public static function obterMetricas($inicio, $fim)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/waba/' . self::$wabaId . '/v2/metricas?inicio=' . $inicio . '&fim=' . $fim;
        
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
     * [ marcarComoLida ] - Marca mensagem como lida
     */
    public static function marcarComoLida($messageId)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/informacao-de-leitura';
        
        $payload = [
            'message_id' => $messageId
        ];

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
     * Envia mídia através de link
     */
    public static function enviarMidiaLink($destinatario, $tipoMidia, $linkMedia, $caption = null, $messageId = null, $filename = null)
    {
        $token = self::getToken();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . self::$lastError];
        }

        $url = self::$baseUrl . '/client/' . self::$phoneNumberId . '/v2/requisicao/mensagem/media';
        
        $payload = [
            'destinatario' => $destinatario,
            'tipoMedia' => $tipoMidia,
            'linkMedia' => $linkMedia
        ];

        // Regras específicas por tipo de mídia conforme API SERPRO
        if ($tipoMidia === 'document') {
            // Para documentos: filename é obrigatório, caption não é permitido
            if ($filename) {
                $payload['filename'] = $filename; // Usar padrão para links
            }
            // NÃO adicionar caption para documentos
        } elseif ($tipoMidia === 'image') {
            // Para imagens: caption é permitido, filename não é necessário
            if ($caption) {
                $payload['caption'] = $caption;
            }
        } elseif ($tipoMidia === 'video' || $tipoMidia === 'audio') {
            // Para vídeo/áudio: caption pode ser permitido (verificar documentação)
            if ($caption) {
                $payload['caption'] = $caption;
            }
        }

        // Adicionar message_id se fornecido (para responder a uma mensagem)
        if ($messageId) {
            $payload['message_id'] = $messageId;
        }

        return self::executarRequisicaoMidia($url, $token, $payload);
    }
}

// Inicializar configurações
SerproHelper::init();
