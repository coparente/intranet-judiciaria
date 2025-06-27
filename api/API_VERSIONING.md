# Versionamento da API - Prefixo v1

## Visão Geral

A API agora suporta versionamento através de prefixos nas URLs. Todas as rotas principais da API foram migradas para usar o prefixo `v1`.

## Como Funciona

### URLs Antigas (ainda funcionam para compatibilidade)
```
GET  /
POST /users/create
GET  /users
...
```

### URLs Novas com Versionamento v1
```
GET  /v1/
POST /v1/users/create
GET  /v1/users
GET  /v1/users/fetch
PUT  /v1/users/update
GET  /v1/users/{id}
DELETE /v1/users/{id}/delete
...
```

## Rotas Disponíveis na v1

### Usuários
- `POST /v1/users/create` - Criar usuário
- `POST /v1/users/login` - Login de usuário
- `GET /v1/users/fetch` - Buscar dados do usuário atual
- `PUT /v1/users/update` - Atualizar usuário
- `GET /v1/users/{id}` - Buscar usuário por ID
- `DELETE /v1/users/{id}/delete` - Excluir usuário
- `GET /v1/users` - Listar usuários

### Mensagens
- `POST /v1/messages/send` - Enviar mensagem
- `GET /v1/messages/conversation` - Buscar conversas
- `GET /v1/messages/contacts` - Buscar contatos
- `GET /v1/messages/status-stats` - Estatísticas de status
- `POST /v1/messages/mark-read` - Marcar como lida
- `POST /v1/messages/mark-read-serpro` - Marcar como lida (Serpro)
- `GET /v1/messages` - Listar mensagens
- `GET /v1/messages/{id}` - Buscar mensagem por ID
- `DELETE /v1/messages/{id}` - Excluir mensagem
- `PUT /v1/messages/{id}/status` - Atualizar status da mensagem
- `GET /v1/messages/{id}/serpro-status` - Verificar status no Serpro

### Webhook
- `POST /v1/webhook/receive` - Receber webhook
- `GET /v1/webhook/status` - Status do webhook

## Implementação Técnica

### Classe Route Modificada

A classe `Route` foi estendida com as seguintes funcionalidades:

```php
// Definir prefixo global
Route::prefix('v1');

// Todas as rotas subsequentes terão o prefixo /v1
Route::get('/users', 'UserController@index'); // Vira /v1/users

// Limpar prefixo
Route::prefix('');

// Rotas sem prefixo
Route::get('/', 'HomeController@index'); // Permanece /
```

### Métodos Adicionados

- `Route::prefix($prefix)` - Define um prefixo global
- `Route::getPrefix()` - Retorna o prefixo atual
- `Route::clear()` - Limpa todas as rotas e prefixo

## Migração

### Para Desenvolvedores Frontend
Atualize suas chamadas de API para usar o prefixo `/v1`:

```javascript
// Antes
fetch('/users')

// Agora
fetch('/v1/users')
```

### Para Desenvolvedores Backend
O sistema é totalmente compatível com o código existente. Os controladores não precisam ser alterados.

## Compatibilidade

- ✅ URLs antigas ainda funcionam (rota raiz `/` sem prefixo)
- ✅ URLs novas com `/v1` também funcionam
- ✅ Webhooks externos continuam funcionando
- ✅ Nenhuma mudança nos controladores necessária

## Futuro

Este sistema permite facilmente criar novas versões da API:

```php
// Futuro: API v2
Route::prefix('v2');
Route::get('/users', 'V2\UserController@index');
```

## Testes

Para testar a API versionada:

```bash
# Testar rota v1
curl http://localhost/seu-projeto/v1/users

# Testar compatibilidade
curl http://localhost/seu-projeto/
``` 