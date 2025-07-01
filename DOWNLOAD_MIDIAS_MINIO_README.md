# 📁 Sistema de Download de Mídias SERPRO + MinIO

## 🎯 Visão Geral

Este sistema implementa o download automático de mídias recebidas via webhook WhatsApp Business API do SERPRO e armazena no MinIO com organização estruturada por tipo e ano.

## 🏗️ Arquitetura

### Fluxo de Funcionamento

1. **📨 Recebimento**: Webhook N8N recebe mensagem com mídia
2. **🔍 Detecção**: Sistema identifica tipo de mídia e extrai ID
3. **📥 Download**: API SERPRO baixa arquivo usando endpoint oficial
4. **☁️ Upload**: Arquivo enviado para MinIO com organização estruturada
5. **💾 Registro**: Informações salvas no banco de dados
6. **🔗 Acesso**: URLs temporárias geradas para visualização

### Estrutura de Armazenamento

```
MinIO Bucket: chatserpro/
├── image/
│   └── 2025/
│       ├── imagem_123.jpg
│       └── foto_456.png
├── audio/
│   └── 2025/
│       ├── audio_789.ogg
│       └── gravacao_012.mp3
├── video/
│   └── 2025/
│       └── video_345.mp4
└── document/
    └── 2025/
        ├── documento_678.pdf
        └── planilha_901.xlsx
```

## 🛠️ Componentes Implementados

### 1. MinioHelper.php
**Localização**: `app/Libraries/MinioHelper.php`

#### Funcionalidades Principais:
- **`uploadMidia()`** - Upload de mídia com organização automática
- **`baixarArquivo()`** - Download de arquivos do MinIO
- **`gerarUrlVisualizacao()`** - URLs temporárias assinadas
- **`excluirArquivo()`** - Remoção de arquivos
- **`listarArquivos()`** - Listagem com filtros
- **`obterEstatisticas()`** - Métricas de uso
- **`testarConexao()`** - Verificação de conectividade

#### Configuração:
```php
// Configurações MinIO no MinioHelper.php
$endpoint = 'https://minioapidj.helpersti.online';
$region = 'sa-east-1';
$accessKeyId = 'pBb2oG0RcNzZfEJJzOrh';
$secretAccessKey = 'J401ezyGzLCgNgVLGRmvjPXfZqXeS10OzI0JdI01';
$bucket = 'chatserpro';
```

### 2. SerproHelper.php - Método Atualizado
**Método**: `downloadMidia()`

#### Endpoint Oficial SERPRO:
```
GET https://api.whatsapp.serpro.gov.br/client/{fromPhoneNumberId}/v2/media/{id}
Headers:
- Authorization: Bearer {token}
- Accept: application/json
```

### 3. Chat Controller - Métodos Adicionados

#### `processarMensagemN8n()` - Atualizado
- Detecta mídias automaticamente
- Chama download e upload
- Salva informações no banco

#### `baixarESalvarMidiaMinIO()`
- Orquestra download da API SERPRO
- Faz upload para MinIO
- Retorna informações completas

#### `visualizarMidiaMinIO()`
- Serve mídias com autenticação
- Verifica permissões de acesso
- Headers apropriados por tipo

#### Métodos de Gestão:
- `gerarUrlMidia()` - URLs temporárias via AJAX
- `estatisticasMinIO()` - Métricas administrativas
- `listarArquivosMinIO()` - Listagem filtrada
- `testarMinIO()` - Teste de conectividade

### 4. ChatModel.php - Método Adicionado
#### `verificarAcessoMidiaMinIO()`
- Controla acesso baseado em propriedade da conversa
- Respeita padrão MVC (SQL no Model)

## 📋 Pré-requisitos

### 1. Dependências PHP
```bash
composer require aws/aws-sdk-php
```

### 2. Extensões PHP Necessárias
- `curl` - Para requisições HTTP
- `json` - Para manipulação JSON
- `mbstring` - Para strings multibyte

### 3. Configurações SERPRO
- `SERPRO_BASE_URL`
- `SERPRO_CLIENT_ID`
- `SERPRO_CLIENT_SECRET`
- `SERPRO_PHONE_NUMBER_ID`

### 4. Acesso MinIO
- Endpoint configurado
- Credenciais válidas
- Bucket `chatserpro` criado

## 🚀 Instalação

### 1. Descomente Configurações MinIO
```php
// Em app/configuracao.php - descomente e ajuste:
$endpoint = 'https://minioapidj.helpersti.online';
$region = 'sa-east-1';
$use_path_style_endpoint = true;
$access_key_id = 'pBb2oG0RcNzZfEJJzOrh';
$secret_access_key = 'J401ezyGzLCgNgVLGRmvjPXfZqXeS10OzI0JdI01';
$bucket = 'chatserpro';
```

### 2. Instalar AWS SDK
```bash
composer require aws/aws-sdk-php
```

### 3. Executar Teste
```bash
# Via navegador
http://seudominio.com/teste_download_minio.php

# Ou via CLI
php teste_download_minio.php
```

### 4. Verificar Estrutura Banco
```sql
-- Adicionar colunas se não existirem
ALTER TABLE mensagens_chat 
ADD COLUMN midia_url VARCHAR(500) NULL,
ADD COLUMN midia_nome VARCHAR(255) NULL;
```

## 🔧 Configuração Avançada

### Tipos MIME Suportados

#### Imagens
- `image/jpeg`, `image/png`, `image/gif`, `image/webp`, `image/bmp`

#### Áudio
- `audio/mpeg`, `audio/mp4`, `audio/aac`, `audio/ogg`, `audio/wav`, `audio/amr`
- `audio/ogg; codecs=opus` (WhatsApp específico)

#### Vídeo
- `video/mp4`, `video/mpeg`, `video/quicktime`, `video/avi`, `video/3gpp`, `video/webm`

#### Documentos
- `application/pdf`, `application/msword`, `text/plain`
- Formatos Office (docx, xlsx, pptx)
- Arquivos compactados (zip, rar)

### Sanitização de Nomes
- Remove caracteres especiais perigosos
- Limita tamanho do nome (100 caracteres)
- Preserva caracteres acentuados
- Adiciona extensão automaticamente

## 📊 Monitoramento

### Logs Disponíveis
```php
// Sucessos
error_log("✅ Mídia N8N baixada e salva no MinIO: {$caminhoMinio}");
error_log("📁 Mídia {$midiaId} salva no MinIO: {$caminho}");

// Erros
error_log("❌ Erro ao baixar/salvar mídia N8N: {$erro}");
error_log("❌ Erro cURL download mídia: {$erro}");
```

### Métricas via API
```javascript
// Estatísticas MinIO
fetch('/chat/estatisticasMinIO')
.then(response => response.json())
.then(data => console.log(data.estatisticas));

// Teste de conectividade
fetch('/chat/testarMinIO')
.then(response => response.json())
.then(data => console.log(data));
```

## 🔒 Segurança

### Controle de Acesso
- **Autenticação obrigatória** para visualização
- **Verificação de propriedade** da conversa
- **URLs temporárias** com expiração (1 hora padrão)
- **Headers apropriados** por tipo de conteúdo

### Validações
- **Tipos MIME permitidos** apenas
- **Tamanho de arquivo** limitado
- **Sanitização rigorosa** de nomes
- **Path traversal** prevenido

## 🧪 Testes

### Script de Teste Completo
Execute `teste_download_minio.php` para verificar:

1. ✅ **Configurações** - Constantes e extensões
2. ☁️ **Conectividade MinIO** - Acesso ao bucket
3. 🔗 **API SERPRO** - Token e status
4. 📥 **Upload/Download** - Fluxo completo simulado
5. 📁 **Organização** - Estrutura de pastas

### Casos de Teste
- Upload de diferentes tipos (image, audio, video, document)
- Geração de URLs temporárias
- Download e verificação de integridade
- Limpeza de arquivos de teste

## 🐛 Solução de Problemas

### Erro: "Bucket não encontrado"
```bash
# Verificar se bucket existe no MinIO
# Criar bucket 'chatserpro' se necessário
```

### Erro: "Token não obtido"
```bash
# Verificar configurações SERPRO
# Validar CLIENT_ID e CLIENT_SECRET
```

### Erro: "Composer autoload não encontrado"
```bash
composer install
```

### Erro: "Extensão não encontrada"
```bash
# Instalar extensões PHP necessárias
sudo apt install php-curl php-json php-mbstring
```

## 🔄 Fluxo de Webhook

### Dados Recebidos (N8N)
```json
{
  "from": "5562999999999",
  "id": "message_id_123",
  "timestamp": 1703097600,
  "type": "image",
  "image": {
    "id": "1243568920120313",
    "mime_type": "image/jpeg"
  }
}
```

### Processamento
1. **Detecção**: `$tipo = 'image'`, `$midiaId = '1243568920120313'`
2. **Download**: `SerproHelper::downloadMidia($midiaId)`
3. **Upload**: `MinioHelper::uploadMidia($dados, 'image', 'image/jpeg')`
4. **Resultado**: `image/2025/1243568920120313.jpg`

### Banco de Dados
```sql
INSERT INTO mensagens_chat (
  conversa_id, tipo, conteudo, midia_url, midia_nome, message_id, status
) VALUES (
  123, 'image', 'image/2025/1243568920120313.jpg', 
  'https://presigned-url...', '1243568920120313.jpg', 'message_id_123', 'recebido'
);
```

## 📈 Performance

### Otimizações Implementadas
- **Upload assíncrono** não bloqueia webhook
- **URLs presignadas** evitam proxy de arquivos
- **Organização por ano** facilita manutenção
- **Logs estruturados** para monitoramento

### Limites Recomendados
- **Timeout cURL**: 60 segundos
- **Tempo execução**: 300 segundos (webhook)
- **Tamanho máximo**: Conforme API SERPRO
- **URL expiração**: 3600 segundos (1 hora)

## 🔄 Manutenção

### Limpeza Automática
```php
// Implementar rotina para arquivos antigos
// Baseado em timestamp de upload
// Manter sincronização com banco
```

### Backup
```bash
# MinIO suporta backup via CLI
mc mirror minio/chatserpro /backup/chatserpro
```

### Monitoramento
- Verificar espaço usado no bucket
- Acompanhar logs de erro
- Validar integridade de uploads
- Testar conectividade periodicamente

---

## 🎉 Status

✅ **Download de mídias** via API SERPRO implementado  
✅ **Upload para MinIO** com organização estruturada  
✅ **Controle de acesso** baseado em usuário/conversa  
✅ **URLs temporárias** para visualização segura  
✅ **Testes automatizados** para validação completa  
✅ **Padrão MVC** respeitado em toda implementação  

Sistema pronto para produção! 🚀 