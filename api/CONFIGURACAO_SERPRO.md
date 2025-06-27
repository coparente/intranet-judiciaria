# 🔧 Configuração da API Serpro WhatsApp

Este documento contém as configurações específicas para integração com a API Serpro WhatsApp.

## 📋 Credenciais da API Serpro

### Configurações Atuais

```php
/**
 * Configurações da API do Serpro
 */
define('SERPRO_CLIENT_ID', '642958872237822');
define('SERPRO_CLIENT_SECRET', 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF');
define('SERPRO_BASE_URL', 'https://api.whatsapp.serpro.gov.br');
define('SERPRO_WABA_ID', '472202335973627');
define('SERPRO_PHONE_NUMBER_ID', '642958872237822');
```

### Detalhes das Configurações

| Configuração | Valor | Descrição |
|--------------|-------|-----------|
| **CLIENT_ID** | `642958872237822` | Identificador único da aplicação na Serpro |
| **CLIENT_SECRET** | `ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF` | Chave secreta para autenticação |
| **BASE_URL** | `https://api.whatsapp.serpro.gov.br` | URL base da API WhatsApp Serpro |
| **WABA_ID** | `472202335973627` | ID da conta WhatsApp Business |
| **PHONE_NUMBER_ID** | `642958872237822` | ID do número de telefone configurado |

## 🔐 Autenticação

A API Serpro utiliza autenticação OAuth 2.0 com client credentials:

```php
// Exemplo de obtenção do token de acesso
$response = $client->post('/oauth/token', [
    'form_params' => [
        'grant_type' => 'client_credentials',
        'client_id' => '642958872237822',
        'client_secret' => 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF'
    ]
]);
```

## 📤 Envio de Mensagens

### Endpoint
```
POST https://api.whatsapp.serpro.gov.br/v17.0/{phone_number_id}/messages
```

### Exemplo de Payload
```json
{
    "messaging_product": "whatsapp",
    "to": "5511999999999",
    "type": "text",
    "text": {
        "body": "Olá! Como posso ajudá-lo?"
    }
}
```

## 📥 Webhook

### URL do Webhook
```
https://seu-dominio.com/chat-api/webhook/receive
```

### Formato do Webhook
```json
{
    "object": "whatsapp_business_account",
    "entry": [
        {
            "id": "472202335973627",
            "changes": [
                {
                    "value": {
                        "messaging_product": "whatsapp",
                        "metadata": {
                            "display_phone_number": "5511999999999",
                            "phone_number_id": "642958872237822"
                        },
                        "messages": [
                            {
                                "from": "5511999999999",
                                "id": "wamid.xxx",
                                "timestamp": "1234567890",
                                "type": "text",
                                "text": {
                                    "body": "Mensagem recebida"
                                }
                            }
                        ]
                    },
                    "field": "messages"
                }
            ]
        }
    ]
}
```

## 🔧 Configuração no Sistema

### 1. Arquivo de Configuração
As configurações estão centralizadas em `src/Config/App.php`:

```php
const SERPRO_CONFIG = [
    'client_id' => '642958872237822',
    'client_secret' => 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF',
    'base_url' => 'https://api.whatsapp.serpro.gov.br',
    'waba_id' => '472202335973627',
    'phone_number_id' => '642958872237822',
    'phone_number' => '5511999999999'
];
```

### 2. Banco de Dados
As configurações também são armazenadas na tabela `configuracoes`:

```sql
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('serpro_client_id', '642958872237822', 'Client ID da API Serpro'),
('serpro_client_secret', 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF', 'Client Secret da API Serpro'),
('serpro_base_url', 'https://api.whatsapp.serpro.gov.br', 'URL base da API Serpro'),
('serpro_waba_id', '472202335973627', 'ID do WhatsApp Business Account'),
('serpro_phone_number_id', '642958872237822', 'ID do número de telefone');
```

## 🚀 Testando a Integração

### 1. Verificar Token de Acesso
```bash
curl -X POST https://api.whatsapp.serpro.gov.br/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=642958872237822&client_secret=ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF"
```

### 2. Enviar Mensagem de Teste
```bash
curl -X POST https://api.whatsapp.serpro.gov.br/v17.0/642958872237822/messages \
  -H "Authorization: Bearer {access_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "5511999999999",
    "type": "text",
    "text": {
        "body": "Teste de integração"
    }
  }'
```

### 3. Verificar Status do Webhook
```bash
curl -X GET http://localhost/chat-api/webhook/status
```

## ⚠️ Segurança

### Recomendações
1. **Nunca exponha as credenciais** em código público
2. **Use variáveis de ambiente** em produção
3. **Monitore os logs** de acesso à API
4. **Configure HTTPS** para o webhook
5. **Valide as assinaturas** dos webhooks

### Variáveis de Ambiente (Recomendado)
```env
SERPRO_CLIENT_ID=642958872237822
SERPRO_CLIENT_SECRET=ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF
SERPRO_BASE_URL=https://api.whatsapp.serpro.gov.br
SERPRO_WABA_ID=472202335973627
SERPRO_PHONE_NUMBER_ID=642958872237822
```

## 📞 Suporte

Para dúvidas sobre a API Serpro:
- **Documentação**: https://api.whatsapp.serpro.gov.br/docs
- **Suporte**: Entre em contato com a equipe Serpro
- **Status**: Verifique o status da API em tempo real

---

**Configurações atualizadas em: Janeiro 2024** 