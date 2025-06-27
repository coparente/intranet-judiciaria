<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat API Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .loading { display: none; }
        
        /* Estilos do Chat */
        .chat-container {
            height: 70vh;
            display: flex;
            flex-direction: column;
        }
        .chat-contacts {
            height: 100%;
            overflow-y: auto;
            border-right: 1px solid #dee2e6;
        }
        .chat-contact {
            padding: 10px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .chat-contact:hover {
            background-color: #f8f9fa;
        }
        .chat-contact.active {
            background-color: #e3f2fd;
        }
        .contact-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .contact-info {
            flex: 1;
        }
        .contact-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .contact-number {
            font-size: 0.8em;
            color: #6c757d;
        }
        .chat-messages {
            height: 100%;
            overflow-y: auto;
            padding: 15px;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 10px;
            display: flex;
        }
        .message.sent {
            justify-content: flex-end;
        }
        .message.received {
            justify-content: flex-start;
        }
        .message-content {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 15px;
            position: relative;
        }
        .message.sent .message-content {
            background: #667eea;
            color: white;
        }
        .message.received .message-content {
            background: white;
            border: 1px solid #dee2e6;
        }
        .message-time {
            font-size: 0.7em;
            opacity: 0.7;
            margin-top: 2px;
        }
        .message-status {
            position: absolute;
            bottom: -15px;
            right: 0;
            font-size: 0.7em;
        }
        .chat-input-container {
            padding: 15px;
            border-top: 1px solid #dee2e6;
            background: white;
        }
        .chat-input-group {
            display: flex;
            gap: 10px;
        }
        .chat-input {
            flex: 1;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 8px 15px;
        }
        .chat-send-btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fab fa-whatsapp me-2"></i>
                            Chat API
                        </h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" onclick="showDashboard()">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showUsers()">
                                <i class="fas fa-users me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showMessages()">
                                <i class="fas fa-comments me-2"></i>
                                Mensagens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showSendMessage()">
                                <i class="fas fa-paper-plane me-2"></i>
                                Enviar Mensagem
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showContacts()">
                                <i class="fas fa-address-book me-2"></i>
                                Contatos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showWebhook()">
                                <i class="fas fa-paper-plane me-2"></i>
                                Webhook
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showMessageStatus()">
                                <i class="fas fa-chart-bar me-2"></i>
                                Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showChat()">
                                <i class="fas fa-comments me-2"></i>
                                Chat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showReadConfirmation()">
                                <i class="fas fa-check-double me-2"></i>
                                Confirmação
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2" id="page-title">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                                <i class="fas fa-sync-alt"></i> Atualizar
                            </button>
                        </div>
                        <div class="btn-group">
                            <span class="btn btn-sm btn-outline-info" id="user-info">
                                <i class="fas fa-user me-1"></i>
                                <span id="current-user">Não logado</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div class="loading text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <!-- Login Content -->
                <div id="login-content">
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-header text-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Login
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="login-form">
                                        <div class="mb-3">
                                            <label for="login-email" class="form-label">E-mail</label>
                                            <input type="email" class="form-control" id="login-email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="login-senha" class="form-label">Senha</label>
                                            <input type="password" class="form-control" id="login-senha" required>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-sign-in-alt me-2"></i>
                                                Entrar
                                            </button>
                                        </div>
                                    </form>
                                    <div class="mt-3 text-center">
                                        <small class="text-muted">
                                            Use as credenciais do usuário admin:<br>
                                            Email: admin@exemplo.com<br>
                                            Senha: 123456
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div id="dashboard-content" style="display:none;">
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Usuários</div>
                                            <div class="h5 mb-0 font-weight-bold" id="users-count">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Mensagens</div>
                                            <div class="h5 mb-0 font-weight-bold" id="messages-count">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Enviadas</div>
                                            <div class="h5 mb-0 font-weight-bold" id="sent-count">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-paper-plane fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Recebidas</div>
                                            <div class="h5 mb-0 font-weight-bold" id="received-count">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-inbox fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Mensagens Recentes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Mensagens Recentes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="recent-messages">
                                        <p class="text-muted">Nenhuma mensagem encontrada.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Content -->
                <div id="users-content" style="display:none;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>
                                Gerenciar Usuários
                            </h5>
                            <button class="btn btn-primary" onclick="openCreateUserModal()">
                                <i class="fas fa-plus me-2"></i>
                                Novo Usuário
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="users-table-body">
                                        <tr>
                                            <td colspan="5" class="text-center">Carregando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Content -->
                <div id="messages-content" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comments me-2"></i>
                                Mensagens
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Número</th>
                                            <th>Mensagem</th>
                                            <th>Direção</th>
                                            <th>Status</th>
                                            <th>Data/Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody id="messages-table-body">
                                        <tr>
                                            <td colspan="6" class="text-center">Carregando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacts Content -->
                <div id="contacts-content" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-address-book me-2"></i>
                                Contatos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Número</th>
                                            <th>Nome</th>
                                            <th>Data Criação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contacts-table-body">
                                        <tr>
                                            <td colspan="4" class="text-center">Carregando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Webhook Content -->
                <div id="webhook-content" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-webhook me-2"></i>
                                Status do Webhook
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="webhook-status">
                                <p class="text-muted">Carregando...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Status Content -->
                <div id="message-status-content" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Status das Mensagens
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="message-status-container">
                                <p class="text-muted">Carregando...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Send Message Content -->
                <div id="send-message-content" style="display:none;">
                    <div class="row">
                        <!-- Template -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Enviar Template
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="template-form">
                                        <div class="mb-3">
                                            <label for="template-numero" class="form-label">Número</label>
                                            <input type="text" class="form-control" id="template-numero" placeholder="5511999999999" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="template-name" class="form-label">Nome do Template</label>
                                            <input type="text" class="form-control" id="template-name" value="simple_greeting" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="template-parametro" class="form-label">Parâmetro</label>
                                            <input type="text" class="form-control" id="template-parametro" placeholder="Nome do usuário" required>
                                            <small class="text-muted">Substituirá {{1}} no template "Olá, {{1}}! Seja bem-vindo ao nosso serviço."</small>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="sendTemplate()">
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Enviar Template
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Text Message -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-comment me-2"></i>
                                        Enviar Mensagem de Texto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="text-form">
                                        <div class="mb-3">
                                            <label for="text-numero" class="form-label">Número</label>
                                            <input type="text" class="form-control" id="text-numero" placeholder="5511999999999" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="text-mensagem" class="form-label">Mensagem</label>
                                            <textarea class="form-control" id="text-mensagem" rows="4" placeholder="Digite sua mensagem..." required></textarea>
                                        </div>
                                        <button type="button" class="btn btn-success" onclick="sendTextMessage()">
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Enviar Mensagem
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Content -->
                <div id="chat-content" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comments me-2"></i>
                                Chat
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <!-- Lista de Contatos -->
                                <div class="col-md-4">
                                    <div class="chat-contacts" id="chat-contacts">
                                        <p class="text-muted p-3">Carregando contatos...</p>
                                    </div>
                                </div>
                                
                                <!-- Área do Chat -->
                                <div class="col-md-8">
                                    <div class="chat-container">
                                        <!-- Cabeçalho do Chat -->
                                        <div class="bg-white border-bottom p-3">
                                            <h6 class="mb-0" id="chat-header">Selecione um contato para iniciar o chat</h6>
                                        </div>
                                        
                                        <!-- Mensagens -->
                                        <div class="chat-messages" id="chat-messages">
                                            <p class="text-muted text-center">Selecione um contato para iniciar o chat</p>
                                        </div>
                                        
                                        <!-- Input de Mensagem -->
                                        <div class="chat-input-container">
                                            <div class="chat-input-group">
                                                <input type="text" class="form-control chat-input" id="chat-input" placeholder="Digite sua mensagem..." disabled>
                                                <button class="btn btn-primary chat-send-btn" id="chat-send-btn" onclick="sendChatMessage()" disabled>
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Read Confirmation Content -->
                <div id="read-confirmation-content" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-check-double me-2"></i>
                                Confirmação de Leitura
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="unread-messages-container">
                                <p class="text-muted">Carregando...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Cadastro/Edição de Usuário -->
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 id="user-modal-title">Cadastrar Usuário</h5>
                <span class="close" onclick="closeUserModal()">&times;</span>
            </div>
            <form id="user-form">
                <input type="hidden" id="user-id">
                <div class="mb-3">
                    <label for="user-nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="user-nome" required>
                </div>
                <div class="mb-3">
                    <label for="user-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="user-email" required>
                </div>
                <div class="mb-3">
                    <label for="user-senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="user-senha">
                    <small class="text-muted">Deixe em branco para manter a senha atual (ao editar)</small>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="user-ativo" checked>
                        <label class="form-check-label" for="user-ativo">
                            Usuário Ativo
                        </label>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html> 