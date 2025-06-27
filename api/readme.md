# Chat API WhatsApp - Sistema de Chat com API Serpro

Uma API RESTful em PHP para sistema de chat via WhatsApp utilizando a API oficial da Serpro.

## 📋 Características

- ✅ **API RESTful** em PHP com arquitetura MVC
- ✅ **Autenticação JWT** para controle de acesso
- ✅ **Integração com API Serpro** WhatsApp
- ✅ **Sistema de webhook** para receber mensagens
- ✅ **Banco de dados MySQL** para armazenamento
- ✅ **Respostas automáticas** configuráveis
- ✅ **Filtros e paginação** nas consultas
- ✅ **Validação de dados** e sanitização
- ✅ **Documentação completa** da API
- ✅ **Envio de mídia** (imagem, documento, áudio, vídeo)
- ✅ **Gestão de conversas** e contatos

## 🚀 Instalação

### Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Composer
- Servidor web (Apache/Nginx)

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd chat-api
```

### 2. Instale as dependências

```bash
composer install
```

### 3. Configure o banco de dados

Execute o script SQL para criar as tabelas:

```bash
mysql -u root -p < database/schema.sql
```

### 4. Configure as variáveis

Edite o arquivo `src/Config/App.php` e atualize as configurações:

```php
// Configurações da API Serpro
const SERPRO_CONFIG = [
    'client_id' => '642958872237822',
    'client_secret' => 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF',
    'base_url' => 'https://api.whatsapp.serpro.gov.br',
    'waba_id' => '472202335973627',
    'phone_number_id' => '642958872237822',
    'phone_number' => '556232162929' // Seu número configurado na Serpro
];

// Configurações do banco de dados
const DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'chat_api',
    'username' => 'seu_usuario',
    'password' => 'sua_senha',
    'charset' => 'utf8mb4'
];

// Chave secreta para JWT (mude para uma chave segura)
const JWT_CONFIG = [
    'secret' => 'sua_chave_secreta_jwt_muito_segura',
    'expiration' => 86400
];
```

### 5. Configure o servidor web

#### Apache (.htaccess já configurado)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 📚 Documentação Completa da API

### Base URL
```
http://localhost/chat-api
```

### Autenticação

A API utiliza JWT (JSON Web Token) para autenticação. Inclua o token no header das requisições:

```
Authorization: Bearer <seu_token_jwt>
```

### 📋 Resumo dos Endpoints

| Método | Endpoint                       | Descrição                                 | Autenticação |
|--------|-------------------------------|-------------------------------------------|--------------|
| POST   | /users/create                 | Criar usuário                             | Não          |
| POST   | /users/login                  | Login                                     | Não          |
| GET    | /users/fetch                  | Buscar dados do usuário logado            | Sim          |
| PUT    | /users/update                 | Atualizar usuário                         | Sim          |
| GET    | /users/{id}                   | Buscar usuário específico por ID          | Sim          |
| GET    | /users                        | Listar usuários                           | Não          |
| DELETE | /users/{id}/delete            | Deletar usuário                           | Sim          |
| POST   | /messages/send                | Enviar mensagem de texto                  | Sim          |
| POST   | /messages/send-media          | Enviar mídia (imagem, doc, áudio, vídeo)  | Sim          |
| GET    | /messages                     | Listar mensagens                          | Sim          |
| GET    | /messages/conversation        | Buscar conversa com número                | Sim          |
| GET    | /messages/contacts            | Listar contatos recentes                  | Sim          |
| GET    | /messages/{id}                | Buscar mensagem específica                | Sim          |
| DELETE | /messages/{id}                | Deletar mensagem                          | Sim          |
| GET    | /messages/status-stats        | Estatísticas de status das mensagens     | Sim          |
| POST   | /messages/mark-read           | Marcar mensagens como lidas               | Sim          |
| POST   | /messages/mark-read-serpro    | Marcar mensagens como lidas (local + Serpro) | Sim          |
| PUT    | /messages/{id}/status         | Atualizar status de mensagem              | Sim          |
| GET    | /messages/{id}/serpro-status  | Verificar status na API Serpro            | Sim          |
| GET    | /webhook/status               | Status do webhook                         | Não          |
| POST   | /webhook/receive              | Receber webhook da Serpro                 | Não          |

---

## 👤 **Endpoints de Usuários**

### 1. Criar Usuário
**POST** `/users/create`

**Body:**
```json
{
    "nome": "João Silva",
    "email": "joao@exemplo.com",
    "senha": "senha123"
}
```

**Resposta de Sucesso (201):**
```json
{
    "success": true,
    "message": "Usuário criado com sucesso"
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost/chat-api/users/create \
  -H "Content-Type: application/json" \
  -d '{"nome":"João Silva","email":"joao@exemplo.com","senha":"senha123"}'
```

### 2. Login
**POST** `/users/login`

**Body:**
```json
{
    "email": "joao@exemplo.com",
    "senha": "senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Login realizado com sucesso",
    "data": {
        "user": {
            "id": 1,
            "nome": "João Silva",
            "email": "joao@exemplo.com"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost/chat-api/users/login \
  -H "Content-Type: application/json" \
  -d '{"email":"joao@exemplo.com","senha":"senha123"}'
```

### 3. Buscar Dados do Usuário Logado
**GET** `/users/fetch`

**Headers:**
```
Authorization: Bearer <token>
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nome": "João Silva",
        "email": "joao@exemplo.com",
        "criado_em": "2024-01-01 12:00:00"
    }
}
```

### 4. Atualizar Usuário
**PUT** `/users/update`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body (campos opcionais):**
```json
{
    "nome": "João Silva Atualizado",
    "email": "joao.novo@exemplo.com",
    "senha": "nova_senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Usuário atualizado com sucesso"
}
```

**Exemplo cURL - Atualizar apenas nome:**
```bash
curl -X PUT http://localhost/chat-api/users/update \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"nome":"João Silva Atualizado"}'
```

**Exemplo cURL - Atualizar nome e email:**
```bash
curl -X PUT http://localhost/chat-api/users/update \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"nome":"João Silva Atualizado","email":"joao.novo@exemplo.com"}'
```

**Exemplo cURL - Atualizar senha:**
```bash
curl -X PUT http://localhost/chat-api/users/update \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"senha":"nova_senha123"}'
```

### 5. Buscar Usuário Específico por ID
**GET** `/users/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nome": "João Silva",
        "email": "joao@exemplo.com",
        "criado_em": "2024-01-01 12:00:00"
    }
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost/chat-api/users/1 \
  -H "Authorization: Bearer <token>"
```

### 6. Listar Todos os Usuários
**GET** `/users`

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nome": "João Silva",
            "email": "joao@exemplo.com",
            "criado_em": "2024-01-01 12:00:00"
        },
        {
            "id": 2,
            "nome": "Maria Santos",
            "email": "maria@exemplo.com",
            "criado_em": "2024-01-02 10:30:00"
        }
    ]
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost/chat-api/users
```

### 7. Deletar Usuário
**DELETE** `/users/{id}/delete`

**Headers:**
```
Authorization: Bearer <token>
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Usuário removido com sucesso"
}
```

**⚠️ Observações importantes:**
- O usuário não pode deletar a si mesmo
- Apenas usuários autenticados podem deletar outros usuários
- A exclusão é permanente e não pode ser desfeita

---

## 💬 **Endpoints de Mensagens**

### 1. Enviar Mensagem de Texto
**POST** `/messages/send`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body:**
```json
{
    "numero": "5511999999999",
    "mensagem": "Olá! Como posso ajudá-lo?"
}
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Mensagem enviada com sucesso",
    "data": {
        "message_id": "c3499f31-39f1-48b1-899d-21a0775c5f59",
        "numero": "5511999999999",
        "status": "enviada"
    }
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost/chat-api/messages/send \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"numero":"5511999999999","mensagem":"Olá! Como posso ajudá-lo?"}'
```

### 1.1. Enviar Template (Primeira Mensagem)
**POST** `/messages/send`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body para Template:**
```json
{
    "numero": "5511999999999",
    "mensagem": "João Silva",
    "is_first_message": true,
    "template_name": "simple_greeting"
}
```

**📋 Informações sobre Templates:**

O template `simple_greeting` tem a seguinte estrutura:
- **Nome**: `simple_greeting`
- **Texto**: `"Olá, {{1}}! Seja bem-vindo ao nosso serviço."`
- **Parâmetro**: `{{1}}` será substituído pela mensagem enviada

**Exemplos de uso:**

1. **Com nome de pessoa:**
```json
{
    "numero": "5511999999999",
    "mensagem": "João Silva",
    "is_first_message": true,
    "template_name": "simple_greeting"
}
```
**Resultado:** "Olá, João Silva! Seja bem-vindo ao nosso serviço."

2. **Com identificação personalizada:**
```json
{
    "numero": "5511999999999",
    "mensagem": "Cliente VIP",
    "is_first_message": true,
    "template_name": "simple_greeting"
}
```
**Resultado:** "Olá, Cliente VIP! Seja bem-vindo ao nosso serviço."

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Template enviado com sucesso",
    "data": {
        "message_id": "c3499f31-39f1-48b1-899d-21a0775c5f59",
        "numero": "5511999999999",
        "status": "enviada",
        "tipo": "template"
    }
}
```

**Exemplo cURL - Template:**
```bash
curl -X POST http://localhost/chat-api/messages/send \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"numero":"5511999999999","mensagem":"João Silva","is_first_message":true,"template_name":"simple_greeting"}'
```

**⚠️ Importante sobre Templates:**
- Templates são obrigatórios para a **primeira mensagem** enviada a um número
- Após o destinatário responder ao template, você pode enviar mensagens normais
- O parâmetro `{{1}}` é substituído dinamicamente pelo texto enviado
- Use `is_first_message: true` para indicar que é a primeira mensagem

### 2. Enviar Mídia (Imagem, Documento, Áudio, Vídeo)
**POST** `/messages/send-media`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Campos:**
- `numero`: número do destinatário (ex: 5511999999999)
- `tipo`: tipo da mídia (`image`, `document`, `audio`, `video`)
- `arquivo`: arquivo a ser enviado (campo de upload)
- `mensagem` (opcional): legenda ou texto

**Exemplo cURL - Enviar Imagem:**
```bash
curl -X POST http://localhost/chat-api/messages/send-media \
  -H "Authorization: Bearer <token>" \
  -F "numero=5511999999999" \
  -F "tipo=image" \
  -F "arquivo=@/caminho/para/imagem.jpg" \
  -F "mensagem=Veja esta imagem"
```

**Exemplo cURL - Enviar Documento:**
```bash
curl -X POST http://localhost/chat-api/messages/send-media \
  -H "Authorization: Bearer <token>" \
  -F "numero=5511999999999" \
  -F "tipo=document" \
  -F "arquivo=@/caminho/para/documento.pdf" \
  -F "mensagem=Aqui está o documento solicitado"
```

**Exemplo cURL - Enviar Áudio:**
```bash
curl -X POST http://localhost/chat-api/messages/send-media \
  -H "Authorization: Bearer <token>" \
  -F "numero=5511999999999" \
  -F "tipo=audio" \
  -F "arquivo=@/caminho/para/audio.mp3"
```

**Exemplo cURL - Enviar Vídeo:**
```bash
curl -X POST http://localhost/chat-api/messages/send-media \
  -H "Authorization: Bearer <token>" \
  -F "numero=5511999999999" \
  -F "tipo=video" \
  -F "arquivo=@/caminho/para/video.mp4" \
  -F "mensagem=Confira este vídeo"
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Mídia enviada com sucesso",
    "data": {
        "message_id": "uuid-da-mensagem",
        "numero": "5511999999999",
        "status": "enviada",
        "media_url": "url-da-midia"
    }
}
```

### 3. Listar Mensagens
**GET** `/messages`

**Headers:**
```
Authorization: Bearer <token>
```

**Parâmetros de Query (opcionais):**
- `limit`: Limite de mensagens (padrão: 50)
- `offset`: Offset para paginação (padrão: 0)
- `numero`: Filtrar por número
- `direcao`: Filtrar por direção (`enviada`/`recebida`)
- `status`: Filtrar por status
- `data_inicio`: Filtrar por data de início (YYYY-MM-DD)
- `data_fim`: Filtrar por data de fim (YYYY-MM-DD)

**Exemplo:**
```
GET /messages?limit=10&offset=0&numero=5511999999999&direcao=enviada
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "usuario_id": 1,
            "numero": "5511999999999",
            "mensagem": "Olá! Como posso ajudá-lo?",
            "direcao": "enviada",
            "status": "enviada",
            "data_hora": "2024-01-01 12:00:00",
            "message_id": "uuid-da-mensagem",
            "tipo": "text",
            "media_url": null
        }
    ],
    "total": 1
}
```

### 4. Buscar Conversa com Número
**GET** `/messages/conversation`

**Headers:**
```
Authorization: Bearer <token>
```

**Parâmetros de Query:**
- `numero`: Número obrigatório para buscar conversa
- `limit`: Limite de mensagens (padrão: 50)
- `offset`: Offset para paginação (padrão: 0)

**Exemplo:**
```
GET /messages/conversation?numero=5511999999999&limit=20&offset=0
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "usuario_id": 1,
            "numero": "5511999999999",
            "mensagem": "Olá!",
            "direcao": "enviada",
            "status": "enviada",
            "data_hora": "2024-01-01 12:00:00"
        },
        {
            "id": 2,
            "usuario_id": 1,
            "numero": "5511999999999",
            "mensagem": "Oi! Como vai?",
            "direcao": "recebida",
            "status": "recebida",
            "data_hora": "2024-01-01 12:05:00"
        }
    ],
    "numero": "5511999999999",
    "total": 2
}
```

### 5. Listar Contatos Recentes
**GET** `/messages/contacts`

**Headers:**
```
Authorization: Bearer <token>
```

**Parâmetros de Query:**
- `limit`: Limite de contatos (padrão: 20)

**Exemplo:**
```
GET /messages/contacts?limit=10
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": [
        {
            "numero": "5511999999999",
            "ultima_mensagem": "2024-01-01 12:00:00",
            "total_mensagens": 5,
            "mensagens_recebidas": "2",
            "mensagens_enviadas": "3"
        },
        {
            "numero": "5511888888888",
            "ultima_mensagem": "2024-01-01 11:30:00",
            "total_mensagens": 3,
            "mensagens_recebidas": "1",
            "mensagens_enviadas": "2"
        }
    ],
    "total": 2
}
```

### 6. Buscar Mensagem Específica
**GET** `/messages/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "usuario_id": 1,
        "numero": "5511999999999",
        "mensagem": "Olá! Como posso ajudá-lo?",
        "direcao": "enviada",
        "status": "enviada",
        "data_hora": "2024-01-01 12:00:00",
        "message_id": "uuid-da-mensagem",
        "tipo": "text",
        "media_url": null
    }
}
```

### 7. Deletar Mensagem
**DELETE** `/messages/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Mensagem deletada com sucesso"
}
```

### 8. Estatísticas de Status das Mensagens
**GET** `/messages/status-stats`

**Headers:**
```
Authorization: Bearer <token>
```

**Parâmetros de Query (opcionais):**
- `data_inicio`: Data de início (YYYY-MM-DD)
- `data_fim`: Data de fim (YYYY-MM-DD)

**Exemplo:**
```
GET /messages/status-stats?data_inicio=2024-01-01&data_fim=2024-01-31
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": {
        "total_mensagens": 150,
        "enviadas": 80,
        "recebidas": 70,
        "por_status": {
            "enviada": {
                "total": 45,
                "enviadas": 45,
                "recebidas": 0
            },
            "entregue": {
                "total": 35,
                "enviadas": 35,
                "recebidas": 0
            },
            "lida": {
                "total": 40,
                "enviadas": 0,
                "recebidas": 40
            },
            "recebida": {
                "total": 30,
                "enviadas": 0,
                "recebidas": 30
            }
        }
    }
}
```

### 9. Marcar Mensagens como Lidas
**POST** `/messages/mark-read`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body (opções):**

**Opção 1 - Marcar mensagens específicas:**
```json
{
    "message_ids": [1, 2, 3]
}
```

**Opção 2 - Marcar todas as mensagens de um número:**
```json
{
    "numero": "5511999999999"
}
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Mensagens marcadas como lidas",
    "data": {
        "updated_count": 3
    }
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost/chat-api/messages/mark-read \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"message_ids":[1,2,3]}'
```

### 9.1. Marcar Mensagens como Lidas na API Serpro
**POST** `/messages/mark-read-serpro`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body (opções):**

**Opção 1 - Marcar mensagens específicas:**
```json
{
    "message_ids": [1, 2, 3]
}
```

**Opção 2 - Marcar todas as mensagens de um número:**
```json
{
    "numero": "5511999999999"
}
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Mensagens marcadas como lidas",
    "data": {
        "updated_count": 3,
        "serpro_updated_count": 2
    }
}
```

**📋 Diferenças entre os endpoints:**

| Endpoint | Local | API Serpro | Uso Recomendado |
|----------|-------|------------|-----------------|
| `/messages/mark-read` | ✅ | ❌ | Marcação apenas local |
| `/messages/mark-read-serpro` | ✅ | ✅ | **Marcação completa (recomendado)** |

**Exemplo cURL:**
```bash
curl -X POST http://localhost/chat-api/messages/mark-read-serpro \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"numero":"5511999999999"}'
```

**💡 Importante:**
- O endpoint `/messages/mark-read-serpro` marca como lida **localmente E na API Serpro**
- Isso garante que o remetente veja a confirmação de leitura no WhatsApp
- Use este endpoint para funcionalidade completa de confirmação de leitura

### 10. Atualizar Status de Mensagem
**PUT** `/messages/{id}/status`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body:**
```json
{
    "status": "entregue"
}
```

**Status permitidos:**
- `enviada` - Mensagem enviada
- `entregue` - Mensagem entregue
- `lida` - Mensagem lida
- `falhou` - Mensagem falhou
- `pendente` - Mensagem pendente

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Status atualizado com sucesso",
    "data": {
        "id": 1,
        "status": "entregue"
    }
}
```

**Exemplo cURL:**
```bash
curl -X PUT http://localhost/chat-api/messages/1/status \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"status":"entregue"}'
```

### 11. Verificar Status na API Serpro
**GET** `/messages/{id}/serpro-status`

**Headers:**
```
Authorization: Bearer <token>
```

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "data": {
        "message_id": 1,
        "serpro_status": "delivered",
        "local_status": "entregue",
        "details": {
            "id": "wamid.123456",
            "status": "delivered",
            "timestamp": "1704110400",
            "recipient_id": "5511999999999"
        }
    }
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost/chat-api/messages/1/serpro-status \
  -H "Authorization: Bearer <token>"
```

**📋 Status da API Serpro:**
- `sent` → `enviada`
- `delivered` → `entregue`
- `read` → `lida`
- `failed` → `falhou`
- `pending` → `pendente`

---

## 🔗 **Endpoints de Webhook**

### 1. Status do Webhook
**GET** `/webhook/status`

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "webhook_url": "http://localhost/chat-api/webhook/receive",
    "timestamp": "2024-01-01 12:00:00",
    "status": "ativo"
}
```

### 2. Receber Webhook (configurado na Serpro)
**POST** `/webhook/receive`

**Content-Type:** `application/json`

**Body (exemplo de mensagem recebida):**
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
                            "display_phone_number": "556232162929",
                            "phone_number_id": "642958872237822"
                        },
                        "messages": [
                            {
                                "from": "5511999999999",
                                "id": "wamid.test123",
                                "timestamp": "1704110400",
                                "type": "text",
                                "text": {
                                    "body": "Olá! Como vai?"
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

**Resposta de Sucesso (200):**
```json
{
    "success": true,
    "message": "Webhook processado com sucesso"
}
```

---

## 🗄️ Estrutura do Banco de Dados

### Tabela `usuarios`
- `id`: ID único do usuário (AUTO_INCREMENT)
- `nome`: Nome completo (VARCHAR 255)
- `email`: E-mail único (VARCHAR 255)
- `senha_hash`: Hash da senha (VARCHAR 255)
- `criado_em`: Data de criação (TIMESTAMP)
- `atualizado_em`: Data de atualização (TIMESTAMP)

### Tabela `mensagens`
- `id`: ID único da mensagem (AUTO_INCREMENT)
- `usuario_id`: ID do usuário (FOREIGN KEY)
- `numero`: Número de telefone (VARCHAR 20)
- `mensagem`: Conteúdo da mensagem (TEXT)
- `direcao`: Enviada ou recebida (ENUM: 'enviada', 'recebida')
- `status`: Status da mensagem (VARCHAR 50)
- `data_hora`: Data/hora da mensagem (TIMESTAMP)
- `message_id`: ID da mensagem da Serpro (VARCHAR 255)
- `tipo`: Tipo da mensagem (VARCHAR 50)
- `media_url`: URL da mídia (VARCHAR 500)

### Tabela `configuracoes`
- `chave`: Chave da configuração (VARCHAR 100)
- `valor`: Valor da configuração (TEXT)
- `descricao`: Descrição da configuração (TEXT)

---

## ⚙️ Configuração da API Serpro

### 1. Obtenha suas credenciais
- Acesse o portal da Serpro
- Solicite acesso à API WhatsApp
- Obtenha suas credenciais:
  - Client ID
  - Client Secret
  - WABA ID
  - Phone Number ID
  - Número de telefone

### 2. Configure o webhook
Configure o webhook na plataforma Serpro apontando para:
```
https://seu-dominio.com/chat-api/webhook/receive
```

### 3. Atualize as configurações
Edite `src/Config/App.php` com suas credenciais reais:

```php
const SERPRO_CONFIG = [
    'client_id' => 'SEU_CLIENT_ID',
    'client_secret' => 'SEU_CLIENT_SECRET',
    'base_url' => 'https://api.whatsapp.serpro.gov.br',
    'waba_id' => 'SEU_WABA_ID',
    'phone_number_id' => 'SEU_PHONE_NUMBER_ID',
    'phone_number' => 'SEU_NUMERO_TELEFONE'
];
```

---

## 🔧 Configurações Avançadas

### Respostas Automáticas

O sistema inclui respostas automáticas configuráveis. Edite o método `generateAutoReply()` em `WebhookController.php`:

```php
private function generateAutoReply($message)
{
    $message = strtolower(trim($message));
    
    $autoReplies = [
        'oi' => 'Olá! Como posso ajudá-lo hoje?',
        'olá' => 'Oi! Em que posso ser útil?',
        'ajuda' => 'Estou aqui para ajudar! Digite sua dúvida.',
        'horário' => 'Nosso horário de atendimento é de 8h às 18h.',
        'contato' => 'Entre em contato pelo telefone (11) 99999-9999.',
        // Adicione mais respostas aqui
    ];
    
    return $autoReplies[$message] ?? null;
}
```

### Múltiplos Números

Para suportar múltiplos números, você pode:
1. Criar uma tabela `numeros_whatsapp` no banco
2. Modificar o `SerproService` para usar números dinâmicos
3. Implementar lógica de roteamento de mensagens

### Templates de Mensagem

Para enviar templates (primeira mensagem), use:

```json
{
    "numero": "5511999999999",
    "mensagem": "Template de boas-vindas",
    "is_first_message": true,
    "template_name": "hello_world",
    "template_params": ["João"]
}
```

---

## 🚨 Segurança

### Recomendações
- Use HTTPS em produção
- Mude as chaves secretas padrão
- Configure firewall adequadamente
- Monitore logs de acesso
- Implemente rate limiting
- Valide todas as entradas
- Use variáveis de ambiente para credenciais

### Variáveis de Ambiente
Para maior segurança, considere usar variáveis de ambiente:

```php
// Em src/Config/App.php
const SERPRO_CONFIG = [
    'client_id' => $_ENV['SERPRO_CLIENT_ID'] ?? 'default_id',
    'client_secret' => $_ENV['SERPRO_CLIENT_SECRET'] ?? 'default_secret',
    'base_url' => $_ENV['SERPRO_BASE_URL'] ?? 'https://api.whatsapp.serpro.gov.br',
    'waba_id' => $_ENV['SERPRO_WABA_ID'] ?? 'default_waba_id',
    'phone_number_id' => $_ENV['SERPRO_PHONE_NUMBER_ID'] ?? 'default_phone_id',
    'phone_number' => $_ENV['SERPRO_PHONE_NUMBER'] ?? 'default_number'
];
```

---

## 📝 Logs

O sistema registra logs importantes:
- Webhooks recebidos
- Erros de processamento
- Respostas automáticas
- Tentativas de autenticação

Verifique os logs do PHP para debug:
```bash
tail -f /var/log/apache2/error.log
```

---

## 🧪 Testando a API

### Script de Teste Automático

Execute o script de teste incluído:

#### Teste de Usuários
```bash
php examples/test_users.php
```

Este script testa todas as operações CRUD de usuários:
- Criar usuário
- Login
- Buscar usuário por ID
- Atualizar usuário
- Deletar usuário

#### Teste de Status das Mensagens
```bash
php examples/test_message_status.php
```

Este script testa os endpoints de status das mensagens:
- Estatísticas de status
- Atualizar status de mensagem
- Marcar mensagens como lidas
- Verificar status na API Serpro
- Filtros de data nas estatísticas
