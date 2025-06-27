# 📋 Guia de Instalação - Chat API WhatsApp

Este guia irá ajudá-lo a configurar e executar o sistema de chat WhatsApp com API Serpro.

## 🚀 Passos de Instalação

### 1. Pré-requisitos

Certifique-se de ter instalado:
- ✅ **PHP 7.4 ou superior**
- ✅ **MySQL 5.7 ou superior**
- ✅ **Composer**
- ✅ **Servidor web (Apache/Nginx)**
- ✅ **Extensões PHP**: PDO, PDO_MySQL, curl, json

### 2. Configuração do Banco de Dados

#### 2.1 Criar o banco de dados

Execute o script SQL para criar as tabelas:

```bash
# Via linha de comando
mysql -u root -p < database/schema.sql

# Ou via phpMyAdmin
# 1. Acesse o phpMyAdmin
# 2. Crie um novo banco chamado 'chat_api'
# 3. Importe o arquivo database/schema.sql
```

#### 2.2 Verificar as tabelas criadas

Após executar o script, você deve ter as seguintes tabelas:
- `usuarios` - Armazena os usuários do sistema
- `mensagens` - Armazena todas as mensagens
- `configuracoes` - Armazena configurações do sistema

### 3. Configuração da Aplicação

#### 3.1 Instalar dependências

```bash
composer install
```

#### 3.2 Configurar as variáveis

Edite o arquivo `src/Config/App.php` e atualize as configurações:

```php
// Configurações do banco de dados
const DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'chat_api',
    'username' => 'seu_usuario_mysql',
    'password' => 'sua_senha_mysql',
    'charset' => 'utf8mb4'
];

// Configurações da API Serpro
const SERPRO_CONFIG = [
    'api_key' => 'sua_chave_api_serpro',
    'phone_number' => '5511999999999', // Seu número configurado na Serpro
    'base_url' => 'https://api.serpro.gov.br'
];

// Chave secreta para JWT (IMPORTANTE: mude para uma chave segura)
const JWT_CONFIG = [
    'secret' => 'sua_chave_secreta_jwt_muito_segura_e_unica',
    'expiration' => 86400
];
```

### 4. Configuração da API Serpro

#### 4.1 Obter credenciais

1. Acesse o portal da Serpro
2. Solicite acesso à API WhatsApp
3. Obtenha sua chave de API
4. Configure seu número de telefone

#### 4.2 Configurar webhook

Configure o webhook na plataforma Serpro apontando para:
```
https://seu-dominio.com/chat-api/webhook/receive
```

### 5. Configuração do Servidor Web

#### 5.1 Apache (.htaccess já configurado)

O arquivo `.htaccess` já está configurado para redirecionar todas as requisições para o `index.php`.

#### 5.2 Nginx

Se estiver usando Nginx, adicione esta configuração:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 6. Testando a Instalação

#### 6.1 Verificar se a API está funcionando

Acesse: `http://localhost/chat-api/`

Você deve ver uma resposta JSON indicando que a API está funcionando.

#### 6.2 Testar com o script de exemplo

Execute o script de teste:

```bash
php examples/test_api.php
```

#### 6.3 Acessar o painel web

Acesse: `http://localhost/chat-api/public/dashboard/`

## 🔧 Configurações Avançadas

### Variáveis de Ambiente (Recomendado para Produção)

Para maior segurança, use variáveis de ambiente:

1. Crie um arquivo `.env` na raiz do projeto:

```env
DB_HOST=localhost
DB_NAME=chat_api
DB_USER=seu_usuario
DB_PASS=sua_senha

SERPRO_API_KEY=sua_chave_api_serpro
SERPRO_PHONE=5511999999999
SERPRO_BASE_URL=https://api.serpro.gov.br

JWT_SECRET=sua_chave_secreta_jwt_muito_segura
WEBHOOK_SECRET=seu_webhook_secret
```

2. Modifique `src/Config/App.php` para usar as variáveis:

```php
const DB_CONFIG = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'chat_api',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4'
];
```

### Configuração de Logs

Para habilitar logs detalhados, configure no PHP:

```ini
log_errors = On
error_log = /path/to/your/error.log
```

### Configuração de Segurança

1. **HTTPS**: Use HTTPS em produção
2. **Firewall**: Configure firewall adequadamente
3. **Rate Limiting**: Implemente limitação de requisições
4. **Validação**: Todas as entradas são validadas automaticamente

## 🚨 Solução de Problemas

### Erro de Conexão com Banco

```
Fatal error: Uncaught PDOException: SQLSTATE[HY000] [1045] Access denied
```

**Solução**: Verifique as credenciais do banco em `src/Config/App.php`

### Erro de Autoload

```
Fatal error: Uncaught Error: Class 'App\Models\Database' not found
```

**Solução**: Execute `composer dump-autoload`

### Erro de Permissão

```
Warning: file_put_contents(): failed to open stream: Permission denied
```

**Solução**: Configure permissões adequadas nas pastas

### Erro de API Serpro

```
Error: Erro ao enviar mensagem
```

**Solução**: 
1. Verifique se a chave da API está correta
2. Verifique se o número está configurado na Serpro
3. Verifique se a API está ativa

## 📞 Suporte

Se encontrar problemas:

1. Verifique os logs de erro do PHP
2. Consulte a documentação da API Serpro
3. Verifique se todas as dependências estão instaladas
4. Teste com o script de exemplo

## 🔄 Atualizações

Para atualizar o sistema:

```bash
git pull origin main
composer update
```

## 📝 Notas Importantes

- ✅ Sempre use HTTPS em produção
- ✅ Mude as chaves secretas padrão
- ✅ Configure backup do banco de dados
- ✅ Monitore os logs regularmente
- ✅ Mantenha as dependências atualizadas

---

**Sistema desenvolvido para facilitar a integração com WhatsApp via API Serpro** 