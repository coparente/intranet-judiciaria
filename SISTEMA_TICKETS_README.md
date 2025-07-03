# 🎫 Sistema de Tickets para Chat WhatsApp

## 📋 Visão Geral

O sistema de tickets foi implementado para controlar o status de atendimento das conversas no chat WhatsApp, permitindo um melhor acompanhamento e gestão dos atendimentos.

## 🚀 Funcionalidades Implementadas

### 📊 **Status de Tickets**
- **Aberto**: Ticket recém-criado, aguardando atendimento
- **Em Andamento**: Ticket sendo atendido pelo agente
- **Aguardando Cliente**: Aguardando resposta do cliente
- **Resolvido**: Problema resolvido, aguardando confirmação
- **Fechado**: Ticket encerrado

### 🔧 **Funcionalidades Principais**

#### 1. **Abertura Automática de Tickets**
- Todo novo contato/conversa abre um ticket automaticamente
- Status inicial: "Aberto"
- Registra data/hora de abertura
- Cria histórico inicial

#### 2. **Controle de Status**
- Alteração de status através de dropdown na conversa
- Histórico completo de mudanças
- Observações em cada alteração
- Controle de quem alterou e quando

#### 3. **Encerramento de Tickets**
- Botão específico para encerrar tickets
- Modal de confirmação com campo de observação
- Bloqueia envio de mensagens quando fechado
- Registra quem e quando fechou

#### 4. **Reabertura de Tickets**
- Possibilidade de reabrir tickets fechados
- Modal com campo para justificativa
- Volta ao status "Aberto"

#### 5. **Gestão e Relatórios**
- Dashboard com estatísticas completas
- Gerenciamento de tickets por status
- Relatórios detalhados
- Identificação de tickets vencidos (>24h)

## 🛠️ Instalação

### 1. **Aplicar Script SQL**

Execute o arquivo `sql_update_ticket_system.sql` no seu banco MySQL:

```bash
mysql -u seu_usuario -p sua_base_dados < sql_update_ticket_system.sql
```

### 2. **Verificar Aplicação**

Após executar o script, verifique se as seguintes alterações foram aplicadas:

#### **Tabela `conversas` - Novos Campos:**
- `status_atendimento` - ENUM com os status
- `observacoes` - TEXT para observações
- `ticket_aberto_em` - DATETIME da abertura
- `ticket_fechado_em` - DATETIME do fechamento
- `ticket_fechado_por` - INT referência ao usuário

#### **Nova Tabela `tickets_historico`:**
- Registra todas as mudanças de status
- Mantém histórico completo dos tickets

#### **Views Criadas:**
- `view_tickets_relatorio` - Relatório completo
- `view_tickets_estatisticas` - Estatísticas consolidadas

#### **Triggers Automáticos:**
- `tr_conversas_abrir_ticket` - Abre ticket ao criar conversa
- `tr_conversas_status_historico` - Registra mudanças no histórico

## 📱 Como Usar

### **Para Agentes:**

#### 1. **Visualizar Status do Ticket**
- O status aparece no cabeçalho da conversa
- Cores diferentes para cada status
- Data de abertura exibida

#### 2. **Alterar Status**
- Clique no dropdown "Ticket" no cabeçalho
- Selecione o novo status
- Adicione observação se necessário

#### 3. **Encerrar Ticket**
- Use o botão "Encerrar Ticket" no dropdown
- Confirme no modal e adicione observação
- Chat será bloqueado para envio

#### 4. **Reabrir Ticket**
- Para tickets fechados, use "Reabrir Ticket"
- Adicione justificativa no modal
- Chat voltará a funcionar normalmente

### **Para Administradores:**

#### 1. **Dashboard de Tickets**
Acesse: `URL/chat/dashboardTickets`
- Estatísticas gerais do sistema
- Estatísticas pessoais
- Tickets vencidos em destaque
- Gráficos de distribuição por status

#### 2. **Gerenciar Tickets**
Acesse: `URL/chat/gerenciarTickets`
- Lista todos os tickets
- Filtros por status
- Tickets vencidos em destaque
- Acesso rápido às conversas

#### 3. **Relatórios**
Acesse: `URL/chat/relatorioTickets`
- Relatórios detalhados
- Filtros por período e usuário
- Estatísticas de performance
- Tempo médio de resolução

#### 4. **Histórico do Ticket**
Acesse: `URL/chat/historicoTicket/{conversa_id}`
- Timeline completa das alterações
- Quem alterou cada status
- Observações registradas
- Tempo total em cada status

## 🎯 Recursos Adicionais

### **Alertas e Notificações**
- Tickets vencidos destacados em vermelho
- Contadores de tickets por status
- Avisos visuais para tickets fechados

### **Controles de Acesso**
- Agentes podem gerenciar apenas seus tickets
- Admins/Analistas têm acesso total
- Histórico preservado e imutável

### **Performance**
- Índices otimizados para consultas rápidas
- Views pré-calculadas para relatórios
- Triggers automáticos para consistência

## 📊 Métricas e KPIs

O sistema fornece métricas importantes:

- **Total de Tickets**: Contador geral
- **Tickets por Status**: Distribuição atual
- **Tempo Médio de Resolução**: Performance da equipe
- **Tickets Vencidos**: Identificação de gargalos
- **Produtividade por Agente**: Relatórios individuais

## 🔧 Configurações

### **Personalizar Tempo de Vencimento**
No arquivo `app/Models/ChatModel.php`, função `buscarTicketsVencidos()`:
```php
// Alterar 24 para o número de horas desejado
$horas_limite = 24;
```

### **Adicionar Novos Status**
1. Alterar ENUM na tabela `conversas`
2. Atualizar arrays `$statusClass` e `$statusNomes` nas views
3. Adicionar lógica específica se necessário

## 🐛 Solução de Problemas

### **Tickets não aparecem**
- Verifique se o script SQL foi executado
- Confirme se os triggers foram criados
- Execute manualmente: `UPDATE conversas SET status_atendimento = 'aberto' WHERE status_atendimento IS NULL`

### **Erro ao alterar status**
- Verifique permissões do usuário
- Confirme se a tabela `tickets_historico` existe
- Verifique logs de erro do PHP

### **Interface não carrega**
- Limpe cache do navegador
- Verifique se todas as views foram criadas
- Confirme se os arquivos PHP foram atualizados

## 📈 Roadmap Futuro

Possíveis melhorias:
- Notificações automáticas por email
- SLA (Service Level Agreement) configurável
- Integração com outros sistemas
- Relatórios avançados com gráficos
- API para integração externa

## 🤝 Suporte

Para dúvidas ou problemas:
1. Verifique os logs de erro do PHP
2. Consulte este README
3. Verifique se todas as dependências foram instaladas
4. Entre em contato com a equipe de desenvolvimento

---

**🎉 Sistema de Tickets implementado com sucesso!**

O sistema está pronto para uso e irá melhorar significativamente o controle e gestão dos atendimentos via WhatsApp. 