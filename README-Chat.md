# Módulo de Chat - Intranet Judiciária

## Descrição
Módulo integrado de comunicação via WhatsApp Business utilizando a API SERPRO. Permite comunicação oficial do Tribunal com cidadãos e advogados através de mensagens, templates, mídia e funcionalidades interativas.

## Arquitetura

### MVC (Model-View-Controller)
- **Model**: `app/Models/ChatModel.php` - Gerenciamento de dados de conversas e mensagens
- **View**: `app/Views/chat/` - Interfaces de usuário responsivas
- **Controller**: `app/Controllers/Chat.php` - Lógica de negócio e integração com API
- **Library**: `app/Libraries/SerproHelper.php` - Abstração da API SERPRO

## Configurações da API SERPRO

### Credenciais (definidas em `app/configuracao.php`)
```php
define('SERPRO_CLIENT_ID', '642958872237822');
define('SERPRO_CLIENT_SECRET', 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF');
define('SERPRO_BASE_URL', 'https://api.whatsapp.serpro.gov.br');
define('SERPRO_WABA_ID', '472202335973627');
define('SERPRO_PHONE_NUMBER_ID', '642958872237822');
```

### Autenticação OAuth2
- **Tipo**: Client Credentials
- **Expiração**: 10 minutos
- **Renovação**: Automática

## Funcionalidades Implementadas

### 1. Gerenciamento de Conversas
- **Arquivo**: `app/Views/chat/index.php`
- **Recursos**:
  - Listagem de conversas ativas
  - Filtros por data e status
  - Busca por número/nome
  - Contadores de mensagens não lidas
  - Interface responsiva

### 2. Interface de Chat Individual
- **Arquivo**: `app/Views/chat/conversa.php`
- **Recursos**:
  - Visualização em tempo real de mensagens
  - Envio de mensagens de texto
  - Upload e envio de mídia (imagem, vídeo, documento)
  - Histórico completo da conversa
  - Indicadores de status de entrega

### 3. Criação de Nova Conversa
- **Arquivo**: `app/Views/chat/nova_conversa.php`
- **Recursos**:
  - Formulário para iniciar conversa
  - Seleção de template inicial
  - Validação de número de telefone
  - Pré-visualização de template

### 4. Gerenciamento de Templates
- **Arquivo**: `app/Views/chat/templates.php`
- **Recursos**:
  - ✅ Listagem de templates existentes
  - ✅ Criação de novos templates
  - ✅ Edição e exclusão
  - ✅ Visualização de detalhes
  - ✅ Status de aprovação da Meta
  - ✅ Filtros e busca

### 5. Gerenciamento de Webhooks
- **Arquivo**: `app/Views/chat/webhooks.php`
- **Recursos**:
  - Configuração de URLs de webhook
  - Listagem de webhooks ativos
  - Teste de conectividade
  - Logs de eventos recebidos

### 6. QR Codes
- **Arquivo**: `app/Views/chat/qr_codes.php`
- **Recursos**:
  - Geração de códigos QR
  - Gerenciamento de códigos existentes
  - Histórico de uso

### 7. Métricas e Relatórios
- **Arquivo**: `app/Views/chat/metricas.php`
- **Recursos**:
  - Dashboard de estatísticas
  - Gráficos de mensagens enviadas/recebidas
  - Métricas de performance
  - Exportação de relatórios

### 8. Configurações do Sistema
- **Arquivo**: `app/Views/chat/configuracoes.php`
- **Recursos**:
  - Configuração de templates padrão
  - Gerenciamento de webhooks
  - Auto-resposta configurável
  - Horário de atendimento
  - Teste de conectividade com API

## Estrutura do Banco de Dados

### Tabela `conversas`
```sql
CREATE TABLE conversas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_contato VARCHAR(20) NOT NULL,
    nome_contato VARCHAR(100),
    usuario_id INT,
    status ENUM('ativa', 'encerrada', 'pausada') DEFAULT 'ativa',
    ultima_mensagem DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

### Tabela `mensagens_chat`
```sql
CREATE TABLE mensagens_chat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversa_id INT NOT NULL,
    usuario_id INT,
    tipo ENUM('enviada', 'recebida') NOT NULL,
    conteudo TEXT NOT NULL,
    tipo_midia ENUM('texto', 'imagem', 'video', 'audio', 'documento'),
    url_midia VARCHAR(500),
    message_id VARCHAR(100),
    status ENUM('enviando', 'enviada', 'entregue', 'lida', 'falhou'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversa_id) REFERENCES conversas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

### Tabela `chat_configuracoes`
```sql
CREATE TABLE chat_configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## API SERPRO - Endpoints Utilizados

### Autenticação
- **POST** `/oauth2/token` - Obter token de acesso

### Mensagens
- **POST** `/client/{phone_number_id}/v2/requisicao/mensagem/template` - Enviar template
- **POST** `/client/{phone_number_id}/v2/requisicao/mensagem/texto` - Enviar texto
- **POST** `/client/{phone_number_id}/v2/requisicao/mensagem/media` - Enviar mídia

### Templates
- **GET** `/waba/{waba_id}/v2/templates` - Listar templates
- **POST** `/waba/{waba_id}/v2/templates` - Criar template
- **DELETE** `/waba/{waba_id}/v2/templates/{template_name}` - Excluir template

### Webhooks
- **GET** `/client/{phone_number_id}/v2/webhook` - Listar webhooks
- **POST** `/client/{phone_number_id}/v2/webhook` - Cadastrar webhook
- **PUT** `/client/{phone_number_id}/v2/webhook/{webhook_id}` - Atualizar webhook
- **DELETE** `/client/{phone_number_id}/v2/webhook/{webhook_id}` - Excluir webhook

### Mídia
- **POST** `/client/{phone_number_id}/v2/media` - Upload de mídia
- **GET** `/client/{phone_number_id}/v2/media/{media_id}` - Download de mídia

## Métodos do Controller

### Principais Métodos
1. **index()** - Lista de conversas
2. **conversa($id)** - Interface de chat individual
3. **novaConversa()** - Formulário para nova conversa
4. **enviarMensagem()** - Envio de mensagens (AJAX)
5. **receberWebhook()** - Recebimento de webhooks da API
6. **uploadMidia()** - Upload de arquivos de mídia
7. **gerenciarTemplates()** - CRUD de templates
8. **gerenciarWebhooks()** - CRUD de webhooks
9. **qrCode()** - Gerenciamento de QR codes
10. **metricas()** - Dashboard de métricas
11. **configuracoes()** - Configurações do sistema
12. **testarAPI()** - Teste de conectividade

### Métodos de Suporte
- **verificarStatusAPI()** - Verifica se a API está online
- **carregarNovasMensagens()** - Atualização em tempo real
- **consultarStatus()** - Status de entrega de mensagens

## Fluxo de Trabalho

### 1. Envio de Primeira Mensagem
1. Usuário acessa "Nova Conversa"
2. Informa número e seleciona template
3. Sistema envia via `/requisicao/mensagem/template`
4. Conversa é criada no banco
5. Redirecionamento para interface de chat

### 2. Continuação da Conversa
1. Mensagens são enviadas via `/requisicao/mensagem/texto`
2. Webhooks recebem mensagens do cliente
3. Interface atualiza em tempo real via AJAX
4. Histórico é mantido no banco

### 3. Gestão de Templates
1. Admin acessa gerenciamento de templates
2. Pode criar, visualizar e excluir templates
3. Templates precisam de aprovação da Meta
4. Status é sincronizado com a API

## Permissões e Segurança

### Níveis de Acesso
- **admin**: Acesso completo a todas as funcionalidades
- **analista**: Acesso às conversas e envio de mensagens
- **usuario**: Acesso limitado (se implementado)

### Validações
- Autenticação obrigatória
- Verificação de permissões por método
- Validação de entrada de dados
- Sanitização de conteúdo
- Rate limiting para API

## Configuração de Webhook

### URL de Recebimento
```
{URL_BASE}/chat/receberWebhook
```

### Validação de Token
```php
define('WEBHOOK_VERIFY_TOKEN', 'seu_token_de_verificacao_aqui');
```

### Eventos Suportados
- Mensagens recebidas
- Status de entrega
- Confirmação de leitura
- Erros de envio

## Tipos de Mídia Suportados

### Limitações por Tipo
- **Imagem**: Máximo 5MB (JPEG, PNG, GIF)
- **Vídeo**: Máximo 16MB (MP4, 3GP)
- **Áudio**: Máximo 16MB (MP3, OGG, AAC)
- **Documento**: Máximo 95MB (PDF, DOC, DOCX, XLS, XLSX)

### Upload e Processamento
1. Validação de tipo e tamanho
2. Upload via API SERPRO
3. Obtenção de media_id
4. Envio da mensagem com mídia
5. Armazenamento do URL no banco

## Troubleshooting

### Problemas Comuns

#### 1. Erro de Token
- **Sintoma**: Erro 401 ao fazer requisições
- **Solução**: Verificar credenciais SERPRO e conectividade

#### 2. Templates não Carregam
- **Sintoma**: Erro "Unexpected token '<'"
- **Solução**: Verificar inicialização do SerproHelper e permissões

#### 3. Webhook não Funciona
- **Sintoma**: Mensagens não chegam
- **Solução**: Verificar URL pública e token de verificação

#### 4. Upload de Mídia Falha
- **Sintoma**: Erro ao enviar arquivos
- **Solução**: Verificar tamanho e tipo do arquivo

### Logs e Debugging

#### Habilitar Logs
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

#### Teste de API
- Usar botão "Testar API" nas configurações
- Verificar logs do servidor web
- Utilizar ferramentas como Postman para testes diretos

## Manutenção

### Limpeza de Dados
- Implementar rotina de limpeza de mensagens antigas
- Backup regular do banco de dados
- Monitoramento de uso de storage

### Atualizações
- Verificar mudanças na API SERPRO
- Testar compatibilidade com novas versões
- Backup antes de atualizações

### Monitoramento
- Verificar logs de erro regularmente
- Monitorar performance da API
- Acompanhar métricas de uso

## Versão
- **Versão Atual**: 1.0.0
- **Data**: Janeiro 2025
- **Autor**: Desenvolvedor TJGO
- **Status**: Produção

## Dependências
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 4.6+
- jQuery 3.6+
- FontAwesome 5.15+
- cURL extension
- JSON extension

---

**Nota**: Este módulo segue as especificações oficiais da API SERPRO WhatsApp Business e está em conformidade com as diretrizes de segurança do Tribunal de Justiça de Goiás. 