<?php 

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Classe SerproService responsável por integrar com a API Serpro WhatsApp.
 */
class SerproService
{
    private $client;
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $wabaId;
    private $phoneNumberId;
    private $phoneNumber;
    private $lastError = '';

    /**
     * Construtor da classe SerproService.
     * 
     * @param string $clientId Client ID da API Serpro
     * @param string $clientSecret Client Secret da API Serpro
     * @param string $wabaId ID do WhatsApp Business Account
     * @param string $phoneNumberId ID do número de telefone
     * @param string $phoneNumber Número de telefone configurado
     * @param string $baseUrl URL base da API Serpro (opcional)
     */
    public function __construct($clientId, $clientSecret, $wabaId, $phoneNumberId, $phoneNumber, $baseUrl = 'https://api.whatsapp.serpro.gov.br')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->wabaId = $wabaId;
        $this->phoneNumberId = $phoneNumberId;
        $this->phoneNumber = $phoneNumber;
        $this->baseUrl = $baseUrl;
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Obtém um token de acesso usando client credentials.
     * 
     * @return string|null Token de acesso ou null se falhar
     */
    private function getAccessToken()
    {
        try {
            $response = $this->client->post('/oauth2/token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['access_token'] ?? null;

        } catch (\Exception $e) {
            $this->lastError = 'Erro ao obter token de acesso: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Envia uma mensagem de texto via WhatsApp usando a API Serpro.
     * 
     * @param string $toNumber Número de destino (formato: 5511999999999)
     * @param string $message Mensagem a ser enviada
     * @param array $options Opções adicionais (media, caption, etc.)
     * @return array Resposta da API Serpro
     * @throws \Exception Se houver erro na requisição
     */
    public function sendMessage($toNumber, $message, array $options = [])
    {
        try {
            // Obtém token de acesso
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Erro ao obter token de acesso: ' . $this->lastError,
                    'code' => 401
                ];
            }

            // Verifica se é primeira mensagem (template) ou conversa já iniciada
            $isFirstMessage = $options['is_first_message'] ?? false;
            
            if ($isFirstMessage) {
                // Envia template (primeira mensagem)
                $templateName = $options['template_name'] ?? 'hello_world';
                return $this->sendTemplate($toNumber, $message, $templateName);
            } else {
                // Envia mensagem de texto (conversa já iniciada)
                return $this->sendTextMessage($toNumber, $message, $options['message_id'] ?? null);
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Envia template (primeira mensagem).
     * 
     * @param string $destinatario Número do destinatário
     * @param string $mensagem Mensagem do template (será usada como parâmetro {{1}})
     * @param string $nomeTemplate Nome do template
     * @return array Resposta da API
     */
    public function sendTemplate($destinatario, $mensagem, $nomeTemplate = 'simple_greeting')
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Erro ao obter token de acesso',
                    'code' => 401
                ];
            }

            // Formato correto para templates da API Serpro
            // O template simple_greeting tem o texto: "Olá, {{1}}! Seja bem-vindo ao nosso serviço."
            // O parâmetro {{1}} será substituído pela mensagem fornecida
            $payload = [
                'nomeTemplate' => $nomeTemplate,
                'wabaId' => $this->wabaId,
                'destinatarios' => [$destinatario],
                'body' => [
                    'parametros' => [
                        [
                            'tipo' => 'text',
                            'valor' => $mensagem // Este valor substituirá {{1}} no template
                        ]
                    ]
                ]
            ];

            $response = $this->client->post("/client/{$this->phoneNumberId}/v2/requisicao/mensagem/template", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $data,
                'message_id' => $data['id'] ?? null
            ];

        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            
            return [
                'success' => false,
                'error' => $errorResponse['message'] ?? 'Erro ao enviar template',
                'code' => $e->getResponse()->getStatusCode()
            ];
        }
    }

    /**
     * Envia mensagem de texto (conversa já iniciada).
     * 
     * @param string $destinatario Número do destinatário
     * @param string $mensagem Mensagem a ser enviada
     * @param string|null $messageId ID da mensagem para resposta
     * @return array Resposta da API
     */
    private function sendTextMessage($destinatario, $mensagem, $messageId = null)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Erro ao obter token de acesso',
                    'code' => 401
                ];
            }

            $payload = [
                'destinatario' => $destinatario,
                'body' => $mensagem,
                'preview_url' => false
            ];

            // Adiciona contexto se fornecido (resposta a uma mensagem)
            if ($messageId) {
                $payload['message_id'] = $messageId;
            }

            $response = $this->client->post("/client/{$this->phoneNumberId}/v2/requisicao/mensagem/texto", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $data,
                'message_id' => $data['id'] ?? null
            ];

        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            
            return [
                'success' => false,
                'error' => $errorResponse['message'] ?? 'Erro ao enviar mensagem',
                'code' => $e->getResponse()->getStatusCode()
            ];
        }
    }

    /**
     * Verifica o status de uma mensagem enviada.
     * 
     * @param string $idRequisicao ID da requisição
     * @return array Status da mensagem
     */
    public function getMessageStatus($idRequisicao)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Erro ao obter token de acesso',
                    'code' => 401
                ];
            }

            $response = $this->client->get("/client/{$this->phoneNumberId}/v2/requisicao/{$idRequisicao}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            
            return [
                'success' => false,
                'error' => $errorResponse['message'] ?? 'Erro ao verificar status',
                'code' => $e->getResponse()->getStatusCode()
            ];
        }
    }

    /**
     * Obtém informações sobre o número de telefone configurado.
     * 
     * @return array Informações do número
     */
    public function getPhoneInfo()
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Erro ao obter token de acesso',
                    'code' => 401
                ];
            }

            $response = $this->client->get("/client/{$this->phoneNumberId}/v2/info", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            
            return [
                'success' => false,
                'error' => $errorResponse['message'] ?? 'Erro ao obter informações do número',
                'code' => $e->getResponse()->getStatusCode()
            ];
        }
    }

    /**
     * Valida se um número de telefone está no formato correto.
     * 
     * @param string $number Número a ser validado
     * @return bool True se o número é válido
     */
    public static function validatePhoneNumber($number)
    {
        // Remove caracteres especiais
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        
        // Verifica se tem pelo menos 10 dígitos (código do país + DDD + número)
        if (strlen($cleanNumber) < 10) {
            return false;
        }

        // Verifica se começa com código do país (55 para Brasil)
        if (!preg_match('/^55\d{10,11}$/', $cleanNumber)) {
            return false;
        }

        return true;
    }

    /**
     * Formata um número de telefone para o padrão da API Serpro.
     * 
     * @param string $number Número a ser formatado
     * @return string Número formatado
     */
    public static function formatPhoneNumber($number)
    {
        // Remove caracteres especiais
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        
        // Se não tem código do país, adiciona 55 (Brasil)
        if (!preg_match('/^55/', $cleanNumber)) {
            $cleanNumber = '55' . $cleanNumber;
        }

        return $cleanNumber;
    }

    /**
     * Processa dados recebidos via webhook da Serpro.
     * 
     * @param array $webhookData Dados recebidos do webhook
     * @return array Dados processados
     */
    public static function processWebhook($webhookData)
    {
        $processedData = [
            'success' => false,
            'message' => '',
            'data' => null
        ];

        try {
            // Verifica se é uma mensagem recebida do WhatsApp
            if (isset($webhookData['entry']) && !empty($webhookData['entry'])) {
                $entry = $webhookData['entry'][0];
                
                if (isset($entry['changes']) && !empty($entry['changes'])) {
                    $change = $entry['changes'][0];
                    
                    if ($change['field'] === 'messages' && isset($change['value']['messages']) && !empty($change['value']['messages'])) {
                        $message = $change['value']['messages'][0];
                        
                        $processedData['success'] = true;
                        $processedData['data'] = [
                            'from' => $message['from'] ?? '',
                            'to' => $change['value']['metadata']['phone_number_id'] ?? '',
                            'message' => $message['text']['body'] ?? '',
                            'message_id' => $message['id'] ?? '',
                            'timestamp' => date('Y-m-d H:i:s', $message['timestamp']),
                            'type' => $message['type'] ?? 'text',
                            'media' => isset($message['image']) ? $message['image']['id'] : null
                        ];
                        $processedData['message'] = 'Mensagem processada com sucesso';
                    } else if (isset($change['value']['statuses']) && !empty($change['value']['statuses'])) {
                        $status = $change['value']['statuses'][0];
                        
                        $processedData['success'] = true;
                        $processedData['data'] = [
                            'type' => 'status',
                            'id' => $status['id'] ?? '',
                            'status' => $status['status'] ?? '',
                            'timestamp' => date('Y-m-d H:i:s', $status['timestamp']),
                            'idRequisicao' => $status['idRequisicao'] ?? null,
                            'recipient_id' => $status['recipient_id'] ?? null,
                            'conversation' => $status['conversation'] ?? null,
                            'pricing' => $status['pricing'] ?? null
                        ];
                        $processedData['message'] = 'Status processado com sucesso';
                    } else if (isset($change['value']['message_statuses']) && !empty($change['value']['message_statuses'])) {
                        // Formato alternativo para status de mensagens
                        $status = $change['value']['message_statuses'][0];
                        
                        $processedData['success'] = true;
                        $processedData['data'] = [
                            'type' => 'status',
                            'id' => $status['id'] ?? '',
                            'status' => $status['status'] ?? '',
                            'timestamp' => date('Y-m-d H:i:s', $status['timestamp']),
                            'idRequisicao' => $status['idRequisicao'] ?? null
                        ];
                        $processedData['message'] = 'Status processado com sucesso';
                    }
                }
            } else {
                $processedData['message'] = 'Formato de webhook não suportado';
            }

        } catch (\Exception $e) {
            $processedData['message'] = 'Erro ao processar webhook: ' . $e->getMessage();
        }

        return $processedData;
    }

    /**
     * Marca uma mensagem como lida na API Serpro.
     * 
     * @param string $messageId ID da mensagem da Serpro
     * @return array Resposta da API Serpro
     */
    public function marcarComoLida($messageId)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Erro ao obter token de acesso',
                    'code' => 401
                ];
            }

            $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/requisicao/informacao-de-leitura';
            
            $payload = [
                'message_id' => $messageId
            ];

            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $data,
                'code' => $response->getStatusCode()
            ];

        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            
            return [
                'success' => false,
                'error' => $errorResponse['message'] ?? 'Erro ao marcar como lida',
                'code' => $e->getResponse()->getStatusCode()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Retorna último erro
     */
    public function getLastError()
    {
        return $this->lastError;
    }
} 