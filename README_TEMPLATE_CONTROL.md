# Sistema de Controle de Tempo de Resposta dos Templates

## Visão Geral

Este sistema monitora automaticamente o tempo de resposta dos templates enviados via WhatsApp. Quando um template é enviado e o cliente não responde em 24 horas, o sistema marca a conversa como precisando de um novo template.

## Como Funciona

### 1. Controle Automático
- **Template Enviado**: Quando a primeira mensagem é enviada (template), o sistema registra a data/hora
- **Resposta do Cliente**: Quando o cliente responde, o sistema atualiza a data da última resposta
- **24 Horas Sem Resposta**: Se passarem 24 horas sem resposta, o sistema marca como "precisa novo template"

### 2. Campos Adicionados na Tabela `conversas`
- `template_enviado_em`: Data/hora quando o template foi enviado
- `precisa_novo_template`: Flag indicando se precisa de novo template (0/1)
- `ultima_resposta_cliente`: Data/hora da última resposta do cliente

### 3. Funcionalidades

#### Na Página Principal do Chat
- **Badge de Alertas**: Mostra quantas conversas precisam de novo template
- **Botão de Acesso**: Link direto para a lista de conversas com templates vencidos
- **Verificação Automática**: A cada 5 minutos verifica templates vencidos

#### Página de Templates Vencidos
- Lista todas as conversas que precisam de novo template
- Mostra tempo sem resposta
- Permite marcar como template reenviado
- Botão para atualizar status

## Instalação

### 1. Executar Script SQL
```bash
mysql -u usuario -p banco_de_dados < sql_template_control.sql
```

### 2. Configurar Cron Job (Opcional)
Para verificação automática via cron job:

```bash
# Editar crontab
crontab -e

# Adicionar linha para executar a cada hora
0 * * * * php /caminho/para/seu/projeto/cron_verificar_templates.php
```

## Uso

### Verificação Manual
```php
// No controlador
$chatModel = new ChatModel();

// Verificar se uma conversa precisa de novo template
$precisaNovoTemplate = $chatModel->verificarPrecisaNovoTemplate($conversa_id);

// Buscar todas as conversas que precisam de novo template
$conversas = $chatModel->buscarConversasPrecisamNovoTemplate();

// Contar total
$total = $chatModel->contarConversasPrecisamNovoTemplate();
```

### API Endpoints
- `POST /chat/verificarTemplatesVencidos` - Atualiza status dos templates vencidos
- `GET /chat/conversasPrecisamNovoTemplate` - Lista conversas com templates vencidos
- `POST /chat/marcarTemplateReenviado` - Marca template como reenviado

## Interface do Usuário

### Badges e Alertas
- **Badge Amarelo**: Indica quantas conversas precisam de novo template
- **Badge Vermelho**: Na tabela, indica conversa com template vencido
- **Botão de Acesso**: Link direto para gerenciar templates vencidos

### Página de Gerenciamento
- Lista detalhada de conversas com templates vencidos
- Informações sobre tempo sem resposta
- Ações para marcar como reenviado
- Botão para atualizar status

## Logs

O sistema gera logs para monitoramento:
- `logs/templates_vencidos.log` - Log das verificações automáticas
- Logs no console do navegador para verificações em tempo real

## Configuração

### Tempo de Vencimento
Por padrão, o sistema considera 24 horas. Para alterar:

```php
// No ChatModel.php, método verificarPrecisaNovoTemplate()
if ($conversa->template_enviado_em && $conversa->horas_sem_resposta >= 24) {
    // Alterar 24 para o número de horas desejado
}
```

### Frequência de Verificação
- **Interface**: A cada 5 minutos
- **Cron Job**: A cada hora (configurável)

## Troubleshooting

### Problemas Comuns

1. **Templates não sendo marcados como enviados**
   - Verificar se o método `marcarTemplateEnviado()` está sendo chamado
   - Verificar logs de erro

2. **Respostas do cliente não sendo registradas**
   - Verificar se o método `marcarRespostaCliente()` está sendo chamado
   - Verificar webhook de recebimento de mensagens

3. **Verificação automática não funcionando**
   - Verificar se o cron job está configurado corretamente
   - Verificar permissões do arquivo `cron_verificar_templates.php`

### Logs de Debug
```bash
# Verificar logs de templates vencidos
tail -f logs/templates_vencidos.log

# Verificar logs do PHP
tail -f /var/log/apache2/error.log
```

## Exemplo de Uso

1. **Envio de Template**: Usuário envia primeira mensagem → Sistema marca `template_enviado_em`
2. **Cliente Responde**: Cliente responde → Sistema marca `ultima_resposta_cliente`
3. **24h Sem Resposta**: Sistema verifica automaticamente → Marca `precisa_novo_template = 1`
4. **Alerta na Interface**: Badge amarelo aparece na página principal
5. **Gerenciamento**: Usuário acessa lista de templates vencidos
6. **Reenvio**: Usuário reenvia template → Sistema reseta `precisa_novo_template = 0`

## Benefícios

- **Controle Automático**: Não precisa verificar manualmente
- **Alertas Visuais**: Interface clara sobre templates vencidos
- **Histórico Completo**: Registra quando templates foram enviados e respostas recebidas
- **Flexibilidade**: Configurável para diferentes tempos de vencimento
- **Logs Detalhados**: Para auditoria e troubleshooting 