# 📅 Módulo de Agenda - Sistema Intranet Judiciária

## Visão Geral

O **Módulo de Agenda** é um sistema completo de gerenciamento de eventos desenvolvido para o Sistema Intranet Judiciária do TJGO. Utiliza o FullCalendar como base e oferece funcionalidades avançadas como categorização de eventos com cores, drag & drop, visualizações múltiplas e interface responsiva.

## 🚀 Recursos Principais

### ✨ Funcionalidades

- **📱 Interface Responsiva**: Adaptada para desktop, tablet e mobile
- **🎨 Categorização com Cores**: 8 categorias pré-definidas com cores distintas
- **📋 Visualizações Múltiplas**: Mês, semana e dia
- **🖱️ Drag & Drop**: Mover e redimensionar eventos diretamente no calendário
- **🔍 Filtros Avançados**: Filtrar eventos por categoria
- **📝 CRUD Completo**: Criar, visualizar, editar e excluir eventos
- **⏰ Eventos de Dia Inteiro**: Suporte a eventos que duram o dia todo
- **🔔 Status de Eventos**: Agendado, Confirmado, Cancelado, Concluído
- **🌐 Localização PT-BR**: Totalmente em português

### 🎯 Categorias de Eventos (com cores)

1. **🔴 Reunião** (#dc3545) - Reuniões e encontros de trabalho
2. **🔵 Audiência** (#007bff) - Audiências judiciais
3. **🟡 Prazo** (#ffc107) - Prazos processuais e administrativos
4. **🟢 Evento** (#28a745) - Eventos e cerimônias
5. **⚪ Tarefa** (#6c757d) - Tarefas e atividades gerais
6. **🟠 Urgente** (#fd7e14) - Compromissos urgentes
7. **🟣 Pessoal** (#6f42c1) - Compromissos pessoais
8. **🟢 Feriado** (#20c997) - Feriados e datas comemorativas

## 📦 Estrutura de Arquivos

```
📁 Módulo de Agenda/
├── 📄 db_agenda.sql                    # Estrutura do banco de dados
├── 📁 app/
│   ├── 📁 Controllers/
│   │   └── 📄 Agenda.php              # Controller principal
│   ├── 📁 Models/
│   │   └── 📄 AgendaModel.php         # Model de dados
│   └── 📁 Views/
│       └── 📁 agenda/
│           ├── 📄 index.php           # Página principal do calendário
│           └── 📄 formulario.php      # Formulário de eventos
└── 📁 public/
    ├── 📁 css/
    │   └── 📄 agenda.css              # Estilos personalizados
    └── 📁 js/
        └── 📄 agenda.js               # JavaScript personalizado
```

## 🛠️ Instalação

### 1. Banco de Dados

Execute o script SQL para criar as tabelas necessárias:

```bash
# No seu cliente MySQL/MariaDB
mysql -u usuario -p nome_do_banco < db_agenda.sql
```

**Ou execute manualmente as queries do arquivo `db_agenda.sql`**

### 2. Incluir CSS e JS

Adicione as seguintes linhas nos arquivos de template:

**No arquivo `app/Views/include/head.php`:**
```html
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- CSS personalizado da Agenda -->
<link href="<?= URL ?>/public/css/agenda.css" rel="stylesheet">
```

**No arquivo `app/Views/include/linkjs.php`:**
```html
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js'></script>

<!-- Moment.js (se não estiver incluído) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- JS personalizado da Agenda -->
<script src="<?= URL ?>/public/js/agenda.js"></script>
```

### 3. Adicionar ao Menu

Adicione o link da agenda no menu de navegação:

```html
<li class="nav-item">
    <a class="nav-link" href="<?= URL ?>/agenda">
        <i class="fas fa-calendar-alt"></i>
        <span>Agenda</span>
    </a>
</li>
```

## 🎯 Como Usar

### Acessando a Agenda

1. Faça login no sistema
2. Clique em **"Agenda"** no menu principal
3. A agenda será carregada mostrando todos os eventos

### Criando um Evento

**Método 1: Botão Novo Evento**
1. Clique no botão **"Novo Evento"**
2. Preencha os campos obrigatórios (título, datas, categoria)
3. Clique em **"Salvar Evento"**

**Método 2: Seleção no Calendário**
1. Clique e arraste no calendário para selecionar um período
2. Será redirecionado para o formulário com as datas pré-preenchidas

### Visualizando Detalhes

1. Clique em qualquer evento no calendário
2. Um modal será aberto com todos os detalhes
3. Use os botões **"Editar"** ou **"Excluir"** conforme necessário

### Movendo Eventos

1. Clique e arraste um evento para uma nova data/hora
2. O evento será movido automaticamente
3. Uma notificação confirmará a ação

### Filtrando Eventos

1. Use o seletor **"Filtrar por Categoria"**
2. Escolha uma categoria específica ou "Todas as categorias"
3. O calendário será atualizado automaticamente

### Alterando Visualização

Use os botões no canto superior direito:
- **📅 Mês**: Visualização mensal (padrão)
- **📊 Semana**: Visualização semanal com horários
- **📋 Dia**: Visualização diária detalhada

## 🔧 Personalização

### Adicionando Novas Categorias

```sql
INSERT INTO agenda_categorias (nome, cor, descricao) 
VALUES ('Nova Categoria', '#ff5722', 'Descrição da categoria');
```

### Alterando Cores das Categorias

```sql
UPDATE agenda_categorias 
SET cor = '#nova_cor' 
WHERE id = ID_DA_CATEGORIA;
```

### Customizando Estilos CSS

Edite o arquivo `public/css/agenda.css` para personalizar:

```css
/* Exemplo: Alterar cor do calendário */
.fc-toolbar-title {
    color: #sua_cor !important;
}

/* Exemplo: Customizar eventos */
.fc-event {
    border-radius: 8px !important;
    font-weight: 600 !important;
}
```

## 📱 Responsividade

O módulo é totalmente responsivo e se adapta automaticamente a diferentes tamanhos de tela:

- **Desktop**: Visualização completa com todos os recursos
- **Tablet**: Interface adaptada com controles otimizados
- **Mobile**: Layout simplificado e botões maiores

## 🔒 Permissões

### Controle de Acesso

- **Usuários Logados**: Podem visualizar e criar seus próprios eventos
- **Administradores**: Podem visualizar e gerenciar todos os eventos
- **Usuários Não Logados**: Redirecionados para login

### Editando Permissões

No arquivo `app/Controllers/Agenda.php`, modifique o método `__construct()`:

```php
public function __construct()
{
    parent::__construct();

    // Exemplo: Permitir apenas administradores
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
        Helper::redirecionar('dashboard');
    }

    $this->agendaModel = $this->model('AgendaModel');
}
```

## 🚨 Troubleshooting

### Problemas Comuns

**1. Calendário não carrega**
- Verifique se o FullCalendar está incluído corretamente
- Confirme se o elemento `#calendar` existe na página
- Verifique o console do navegador para erros JavaScript

**2. Eventos não aparecem**
- Confirme se as tabelas do banco foram criadas
- Verifique se há dados na tabela `agenda_eventos`
- Teste a URL `/agenda/eventos` diretamente

**3. Erros de data**
- Verifique se o fuso horário está configurado: `date_default_timezone_set('America/Sao_Paulo')`
- Confirme o formato das datas no banco (YYYY-MM-DD HH:MM:SS)

**4. Drag & Drop não funciona**
- Verifique se `editable: true` está configurado no FullCalendar
- Confirme se a função `moverEvento()` está sendo chamada

### Debug Mode

Para ativar logs de debug, adicione no início dos arquivos:

```php
// No Controller ou Model
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

```javascript
// No JavaScript
console.log('Debug:', variavel);
```

## 🔄 Atualizações Futuras

### Funcionalidades Planejadas

- [ ] **Notificações por Email**: Alertas automáticos antes dos eventos
- [ ] **Recorrência**: Eventos que se repetem automaticamente
- [ ] **Anexos**: Possibilidade de anexar arquivos aos eventos
- [ ] **Convites**: Convidar outros usuários para eventos
- [ ] **Integração com WhatsApp**: Notificações via WhatsApp
- [ ] **Exportação**: Exportar agenda em PDF/Excel
- [ ] **Sincronização**: Sync com Google Calendar/Outlook

### Como Contribuir

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📞 Suporte

Para suporte técnico ou dúvidas:

- **Email**: coparente@tjgo.jus.br
- **Sistema**: Intranet Judiciária TJGO
- **Versão**: 1.0.0

## 📄 Licença

Este módulo foi desenvolvido especificamente para o TJGO e faz parte do Sistema Intranet Judiciária.

---

**Desenvolvido com ❤️ para o TJGO - Tribunal de Justiça do Estado de Goiás**

*"Facilitando o gerenciamento de eventos e compromissos do sistema judiciário"* 