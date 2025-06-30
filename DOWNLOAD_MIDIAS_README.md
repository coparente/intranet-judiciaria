# Sistema de Download de Mídias - Chat WhatsApp SERPRO

## 📋 Visão Geral

Este sistema implementa o download automático de mídias (imagens, áudios, vídeos e documentos) recebidas via webhook do WhatsApp Business API do SERPRO. As mídias são baixadas, salvas localmente e organizadas de forma segura.

## 🚀 Funcionalidades Implementadas

### 1. Download Automático de Mídias
- **Processamento automático** de mensagens recebidas via webhook
- **Download automático** de mídias usando a API SERPRO
- **Suporte a todos os tipos**: imagem, áudio, vídeo, documento
- **Organização por data**: estrutura `uploads/chat/midias/YYYY/MM/`
- **Nomes únicos**: evita conflitos de arquivos duplicados

### 2. Tipos de Mídia Suportados

#### Imagens
- `image/jpeg`, `image/png`, `image/gif`, `image/webp`, `image/bmp`

#### Áudio  
- `audio/mpeg`, `audio/mp4`, `audio/aac`, `audio/ogg`, `audio/wav`, `audio/amr`
- Suporte especial para `audio/ogg; codecs=opus` (áudios do WhatsApp)

#### Vídeo
- `video/mp4`, `video/mpeg`, `video/quicktime`, `video/avi`, `video/3gpp`, `video/webm`

#### Documentos
- PDF, Word, Excel, PowerPoint, TXT, CSV, ZIP, RAR
- Preserva nomes originais quando possível

### 3. Estrutura de Armazenamento

```
public/uploads/chat/midias/
├── 2025/
│   ├── 01/
│   │   ├── imagem_123.jpg
│   │   ├── audio_456.ogg  
│   │   └── documento_789.pdf
│   └── 02/
│       └── video_101.mp4
└── 2024/
    └── 12/
        └── arquivo_antigo.jpg
```

## 🔧 Como Funciona

### 1. Recebimento via Webhook

Quando uma mensagem com mídia é recebida, o sistema identifica o tipo e extrai informações:

```json
{
  "messages": [
    {
      "from": "62996185892",
      "id": "wamid.xxx",
      "type": "image",
      "image": {
        "id": "1243568920120313",
        "mime_type": "image/jpeg"
      }
    }
  ]
}
```

### 2. Processamento Automático

1. **Identificação**: Extrai ID da mídia e tipo MIME
2. **Download**: Chama API SERPRO para baixar o arquivo
3. **Validação**: Verifica integridade e tipo do arquivo
4. **Armazenamento**: Salva com nome único na estrutura organizada
5. **Banco de Dados**: Atualiza registro com caminho local

### 3. Métodos Principais

#### `processarMensagemN8n($mensagemData)`
- Processa mensagens do formato n8n/custom
- Extrai informações de mídia automaticamente
- Chama download se necessário

#### `baixarMidiaRecebida($midiaId, $tipo, $mimeType, $filename)`
- Faz download da mídia via API SERPRO
- Determina extensão correta baseada no MIME type
- Cria estrutura de diretórios necessária
- Gera nome único para evitar conflitos
- Retorna informações do arquivo salvo

#### `visualizarMidia($caminho_relativo)`
- Serve mídias com autenticação
- Controla acesso baseado no usuário
- Headers apropriados por tipo de arquivo

## 🔐 Segurança

### Controle de Acesso
- **Autenticação obrigatória** para visualizar mídias
- **Verificação de propriedade**: usuários só acessam suas mídias
- **Admins** têm acesso total
- **Sanitização** de nomes de arquivos

### Validação de Arquivos
- **Verificação de MIME type**
- **Limitação de tamanho**
- **Validação de extensões**
- **Prevenção de path traversal**

## 🛠 Endpoints Disponíveis

### Chat Principal
- `GET /chat/conversa/{id}` - Visualizar conversa com mídias
- `POST /chat/webhook` - Receber mensagens (incluindo mídias)

### Visualização de Mídias
- `GET /chat/visualizarMidia/{caminho}` - Servir arquivo com autenticação
- `GET /chat/listarMidias/{conversa_id}` - Listar mídias de uma conversa

### Administração (Admin apenas)
- `POST /chat/limparMidiasAntigas` - Remove mídias com mais de 90 dias
- `GET /chat/estatisticasMidias` - Estatísticas de uso de espaço

## 📊 Gerenciamento de Espaço

### Limpeza Automática
- **Mídias antigas**: Remoção automática após 90 dias (configurável)
- **Diretórios vazios**: Limpeza automática da estrutura
- **Atualização do banco**: Marca arquivos como removidos

### Estatísticas
- **Total de arquivos** e espaço usado
- **Distribuição por tipo** de mídia
- **Uso por mês**
- **Relatórios detalhados** para administradores

## 🔧 Configuração

### Estrutura de Diretórios
Certifique-se que os diretórios tenham permissões adequadas:

```bash
# Criar estrutura base
mkdir -p public/uploads/chat/midias
chmod 755 public/uploads/chat/midias

# Permissões do Apache/Nginx
chown -R www-data:www-data public/uploads/
```

### Configuração do Banco
As mídias são referenciadas na tabela `mensagens_chat`:

```sql
ALTER TABLE mensagens_chat 
ADD COLUMN midia_url VARCHAR(500),
ADD COLUMN midia_nome VARCHAR(255);
```

## 📱 Uso no Frontend

### Visualização de Mídias
```javascript
// Listar mídias de uma conversa
fetch(`/chat/listarMidias/${conversaId}`)
    .then(response => response.json())
    .then(data => {
        data.midias.forEach(midia => {
            console.log(`${midia.tipo}: ${midia.url_visualizacao}`);
        });
    });
```

### Download/Visualização
```html
<!-- Imagem -->
<img src="/chat/visualizarMidia/uploads/chat/midias/2025/01/imagem.jpg" />

<!-- Áudio -->
<audio controls>
    <source src="/chat/visualizarMidia/uploads/chat/midias/2025/01/audio.ogg" type="audio/ogg">
</audio>

<!-- Download de documento -->
<a href="/chat/visualizarMidia/uploads/chat/midias/2025/01/documento.pdf" download>
    Baixar PDF
</a>
```

## 🚨 Monitoramento e Logs

### Logs de Sistema
- ✅ **Sucesso**: `"✅ Mídia baixada com sucesso: {url}"`
- ❌ **Erro**: `"❌ Erro ao baixar mídia: {erro}"`
- 📁 **Arquivo salvo**: `"📁 Mídia salva: {caminho} (Tamanho: X KB)"`

### Verificação de Status
```bash
# Verificar logs do sistema
tail -f /var/log/php_errors.log | grep "Mídia"

# Verificar espaço usado
du -sh public/uploads/chat/midias/

# Contar arquivos por tipo
find public/uploads/chat/midias/ -name "*.jpg" | wc -l
find public/uploads/chat/midias/ -name "*.ogg" | wc -l
```

## 🔄 Manutenção

### Limpeza Manual
```bash
# Limpar mídias antigas (mais de 90 dias)
find public/uploads/chat/midias/ -type f -mtime +90 -delete

# Remover diretórios vazios
find public/uploads/chat/midias/ -type d -empty -delete
```

### Backup de Mídias
```bash
# Backup completo
tar -czf midias_backup_$(date +%Y%m%d).tar.gz public/uploads/chat/midias/

# Backup incremental (últimos 30 dias)
find public/uploads/chat/midias/ -type f -mtime -30 | tar -czf midias_recent_$(date +%Y%m%d).tar.gz -T -
```

## 🆘 Solução de Problemas

### Erro: "Erro ao criar diretório"
```bash
# Verificar permissões
ls -la public/uploads/
chmod 755 public/uploads/chat/
chown www-data:www-data public/uploads/chat/
```

### Erro: "Arquivo não encontrado"
- Verificar se o webhook está configurado corretamente
- Conferir se a API SERPRO está respondendo
- Verificar logs de erro para detalhes

### Erro: "Acesso negado a esta mídia"
- Verificar se o usuário está logado
- Conferir se a mídia pertence ao usuário
- Admins têm acesso total

### Performance
- **Mídias grandes**: Considere usar streaming para arquivos > 10MB
- **Muitos arquivos**: Implemente paginação na listagem
- **Limpeza**: Configure cron job para limpeza automática

## 📈 Próximos Passos

1. **Thumbnails**: Gerar previews para imagens e vídeos
2. **Compressão**: Implementar compressão automática
3. **CDN**: Integração com serviços de CDN
4. **Backup automático**: Sincronização com cloud storage
5. **Métricas avançadas**: Dashboard de uso detalhado

---

Este sistema garante que todas as mídias recebidas via WhatsApp sejam automaticamente baixadas, organizadas e disponibilizadas de forma segura para os usuários do sistema. 