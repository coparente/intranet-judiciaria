// dashboard.js
// JavaScript principal da dashboard do Chat API
// Todas as funções de navegação, autenticação, requisições à API e integração com a interface
// Documentação em português

// URL base da API
const API_BASE = 'https://coparente.top/intranet/api/v1/'; // Ajuste conforme necessário
let currentToken = localStorage.getItem('chat_api_token');
let currentUser = JSON.parse(localStorage.getItem('chat_api_user') || 'null');
let currentChatContact = null;
let chatPollingInterval = null;

// Função para fazer requisições à API
async function apiRequest(endpoint, method = 'GET', data = null, token = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    const authToken = token || currentToken;
    if (authToken) {
        options.headers['Authorization'] = `Bearer ${authToken}`;
    }
    if (data) {
        options.body = JSON.stringify(data);
    }
    try {
        console.log(`Fazendo requisição: ${method} ${API_BASE + endpoint}`);
        const response = await fetch(API_BASE + endpoint, options);
        const json = await response.json();
        console.log('Resposta da API:', json);
        return json;
    } catch (error) {
        console.error('Erro na requisição:', error);
        return { error: 'Erro de conexão' };
    }
}

// Função para verificar autenticação
function isAuthenticated() {
    return currentToken !== null;
}

// Função para atualizar informações do usuário na interface
function updateUserInfo() {
    const userInfoElement = document.getElementById('current-user');
    const userInfoButton = document.getElementById('user-info');
    if (currentUser) {
        userInfoElement.textContent = currentUser.nome;
        userInfoButton.classList.remove('btn-outline-secondary');
        userInfoButton.classList.add('btn-outline-info');
    } else {
        userInfoElement.textContent = 'Não logado';
        userInfoButton.classList.remove('btn-outline-info');
        userInfoButton.classList.add('btn-outline-secondary');
    }
}

// Função para esconder todas as seções
function hideAllContent() {
    const sections = [
        'login-content', 'dashboard-content', 'users-content', 'messages-content',
        'contacts-content', 'webhook-content', 'message-status-content',
        'send-message-content', 'chat-content', 'read-confirmation-content'
    ];
    sections.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            console.log(`Ocultando seção: ${id}`);
        } else {
            console.warn(`Elemento não encontrado: ${id}`);
        }
    });
}

// Função para mostrar a tela de login
function showLogin() {
    console.log('Mostrando tela de login');
    hideAllContent();
    const loginContent = document.getElementById('login-content');
    if (loginContent) {
        loginContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Login';
    } else {
        console.error('Elemento login-content não encontrado');
    }
}

// Função para mostrar o dashboard
function showDashboard() {
    console.log('Mostrando dashboard');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const dashboardContent = document.getElementById('dashboard-content');
    if (dashboardContent) {
        dashboardContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Dashboard';
        updateActiveNav('dashboard');
        loadDashboard();
    } else {
        console.error('Elemento dashboard-content não encontrado');
    }
}

// Função para mostrar usuários
function showUsers() {
    console.log('Mostrando usuários');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const usersContent = document.getElementById('users-content');
    if (usersContent) {
        usersContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Gerenciar Usuários';
        updateActiveNav('users');
        loadUsers();
    } else {
        console.error('Elemento users-content não encontrado');
    }
}

// Função para mostrar mensagens
function showMessages() {
    console.log('Mostrando mensagens');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const messagesContent = document.getElementById('messages-content');
    if (messagesContent) {
        messagesContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Mensagens';
        updateActiveNav('messages');
        loadMessages();
    } else {
        console.error('Elemento messages-content não encontrado');
    }
}

// Função para mostrar contatos
function showContacts() {
    console.log('Mostrando contatos');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const contactsContent = document.getElementById('contacts-content');
    if (contactsContent) {
        contactsContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Contatos';
        updateActiveNav('contacts');
        loadContacts();
    } else {
        console.error('Elemento contacts-content não encontrado');
    }
}

// Função para mostrar webhook
function showWebhook() {
    console.log('Mostrando webhook');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const webhookContent = document.getElementById('webhook-content');
    if (webhookContent) {
        webhookContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Status do Webhook';
        updateActiveNav('webhook');
        loadWebhookStatus();
    } else {
        console.error('Elemento webhook-content não encontrado');
    }
}

// Função para mostrar status das mensagens
function showMessageStatus() {
    console.log('Mostrando status das mensagens');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const messageStatusContent = document.getElementById('message-status-content');
    if (messageStatusContent) {
        messageStatusContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Status das Mensagens';
        updateActiveNav('messageStatus');
        loadMessageStatus();
    } else {
        console.error('Elemento message-status-content não encontrado');
    }
}

// Função para mostrar envio de mensagens
function showSendMessage() {
    console.log('Mostrando envio de mensagens');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const sendMessageContent = document.getElementById('send-message-content');
    if (sendMessageContent) {
        sendMessageContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Enviar Mensagem';
        updateActiveNav('sendMessage');
    } else {
        console.error('Elemento send-message-content não encontrado');
    }
}

// Função para mostrar chat
function showChat() {
    console.log('Mostrando chat');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const chatContent = document.getElementById('chat-content');
    if (chatContent) {
        chatContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Chat';
        updateActiveNav('chat');
        
        // Reseta o chat
        resetChat();
        
        // Carrega contatos
        loadChatContacts();
    } else {
        console.error('Elemento chat-content não encontrado');
    }
}

// Reseta o estado do chat
function resetChat() {
    console.log('Resetando chat');
    currentChatContact = null;
    
    // Para o polling se estiver ativo
    stopChatPolling();
    
    // Reseta interface
    document.getElementById('chat-header').textContent = 'Selecione um contato para iniciar o chat';
    document.getElementById('chat-messages').innerHTML = '<p class="text-muted text-center">Selecione um contato para iniciar o chat</p>';
    document.getElementById('chat-input').value = '';
    
    // Desabilita campos de entrada
    document.getElementById('chat-input').disabled = true;
    document.getElementById('chat-send-btn').disabled = true;
    
    // Remove seleção ativa de todos os contatos
    document.querySelectorAll('.chat-contact').forEach(contact => {
        contact.classList.remove('active');
    });
}

// Função para mostrar confirmação de leitura
function showReadConfirmation() {
    console.log('Mostrando confirmação de leitura');
    if (!isAuthenticated()) {
        showLogin();
        return;
    }
    hideAllContent();
    const readConfirmationContent = document.getElementById('read-confirmation-content');
    if (readConfirmationContent) {
        readConfirmationContent.style.display = 'block';
        document.getElementById('page-title').textContent = 'Confirmação de Leitura';
        updateActiveNav('readConfirmation');
        loadUnreadMessages();
    } else {
        console.error('Elemento read-confirmation-content não encontrado');
    }
}

// Atualiza o menu ativo
function updateActiveNav(section) {
    console.log('Atualizando navegação ativa:', section);
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    let navMap = {
        'dashboard': 'showDashboard',
        'users': 'showUsers',
        'messages': 'showMessages',
        'sendMessage': 'showSendMessage',
        'contacts': 'showContacts',
        'webhook': 'showWebhook',
        'messageStatus': 'showMessageStatus',
        'chat': 'showChat',
        'readConfirmation': 'showReadConfirmation'
    };
    let func = navMap[section] || 'showDashboard';
    const activeLink = Array.from(document.querySelectorAll('.nav-link')).find(link => link.getAttribute('onclick') && link.getAttribute('onclick').includes(func));
    if (activeLink) {
        activeLink.classList.add('active');
        console.log('Menu ativo definido:', section);
    } else {
        console.warn('Link ativo não encontrado para:', section);
    }
}

// Função para login
async function doLogin(email, senha) {
    console.log('Tentando login com:', email);
    const response = await apiRequest('users/login', 'POST', { email, senha });
    if (response.success && response.data.token) {
        currentToken = response.data.token;
        currentUser = response.data.user;
        localStorage.setItem('chat_api_token', currentToken);
        localStorage.setItem('chat_api_user', JSON.stringify(currentUser));
        updateUserInfo();
        showDashboard();
        return true;
    } else {
        throw new Error(response.error || 'Erro no login');
    }
}

// Função para logout
function logout() {
    console.log('Fazendo logout');
    currentToken = null;
    currentUser = null;
    localStorage.removeItem('chat_api_token');
    localStorage.removeItem('chat_api_user');
    updateUserInfo();
    showLogin();
    // Para o polling do chat se estiver ativo
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
        chatPollingInterval = null;
    }
}

// ==================== FUNÇÕES DE USUÁRIOS ====================

// Carrega lista de usuários
async function loadUsers() {
    try {
        const response = await apiRequest('users');
        if (response.success) {
            displayUsers(response.data);
        } else {
            alert('Erro ao carregar usuários: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar usuários: ' + error.message);
    }
}

// Exibe usuários na tabela
function displayUsers(users) {
    const tbody = document.getElementById('users-table-body');
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum usuário encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${user.nome}</td>
            <td>${user.email}</td>
            <td>${user.ativo ? 'Ativo' : 'Inativo'}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                    <i class="fas fa-trash"></i> Deletar
                </button>
            </td>
        </tr>
    `).join('');
}

// Abre modal para cadastrar usuário
function openCreateUserModal() {
    document.getElementById('user-modal-title').textContent = 'Cadastrar Usuário';
    document.getElementById('user-form').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('user-modal').style.display = 'block';
}

// Abre modal para editar usuário
async function editUser(userId) {
    try {
        const response = await apiRequest(`users/${userId}`);
        if (response.success) {
            const user = response.data;
            document.getElementById('user-modal-title').textContent = 'Editar Usuário';
            document.getElementById('user-id').value = user.id;
            document.getElementById('user-nome').value = user.nome;
            document.getElementById('user-email').value = user.email;
            document.getElementById('user-ativo').checked = user.ativo;
            document.getElementById('user-modal').style.display = 'block';
        } else {
            alert('Erro ao carregar usuário: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar usuário: ' + error.message);
    }
}

// Confirma exclusão de usuário
function deleteUser(userId) {
    if (confirm('Tem certeza que deseja excluir este usuário?')) {
        performDeleteUser(userId);
    }
}

// Executa exclusão de usuário
async function performDeleteUser(userId) {
    try {
        const response = await apiRequest(`users/${userId}`, 'DELETE');
        if (response.success) {
            alert('Usuário excluído com sucesso!');
            loadUsers();
        } else {
            alert('Erro ao excluir usuário: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao excluir usuário: ' + error.message);
    }
}

// Salva usuário (criar ou atualizar)
async function saveUser() {
    const userId = document.getElementById('user-id').value;
    const nome = document.getElementById('user-nome').value;
    const email = document.getElementById('user-email').value;
    const ativo = document.getElementById('user-ativo').checked;
    const senha = document.getElementById('user-senha').value;
    
    if (!nome || !email) {
        alert('Nome e email são obrigatórios!');
        return;
    }
    
    const userData = { nome, email, ativo };
    if (senha) userData.senha = senha;
    
    try {
        let response;
        if (userId) {
            // Atualizar
            response = await apiRequest(`users/${userId}`, 'PUT', userData);
        } else {
            // Criar
            if (!senha) {
                alert('Senha é obrigatória para novos usuários!');
                return;
            }
            response = await apiRequest('users', 'POST', userData);
        }
        
        if (response.success) {
            alert(userId ? 'Usuário atualizado com sucesso!' : 'Usuário criado com sucesso!');
            document.getElementById('user-modal').style.display = 'none';
            loadUsers();
        } else {
            alert('Erro ao salvar usuário: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao salvar usuário: ' + error.message);
    }
}

// Fecha modal de usuário
function closeUserModal() {
    document.getElementById('user-modal').style.display = 'none';
}

// ==================== FUNÇÕES DE MENSAGENS ====================

// Carrega lista de mensagens
async function loadMessages() {
    try {
        const response = await apiRequest('messages?limit=100');
        if (response.success) {
            displayMessages(response.data);
        } else {
            alert('Erro ao carregar mensagens: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar mensagens: ' + error.message);
    }
}

// Exibe mensagens na tabela
function displayMessages(messages) {
    const tbody = document.getElementById('messages-table-body');
    if (!messages || messages.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhuma mensagem encontrada</td></tr>';
        return;
    }
    
    tbody.innerHTML = messages.map(message => `
        <tr>
            <td>${message.id}</td>
            <td>${message.numero}</td>
            <td>${message.mensagem}</td>
            <td>
                <span class="badge bg-${message.direcao === 'enviada' ? 'primary' : 'success'}">
                    ${message.direcao}
                </span>
            </td>
            <td>
                <span class="badge bg-${getStatusColor(message.status)}">
                    ${message.status}
                </span>
            </td>
            <td>${new Date(message.data_hora).toLocaleString()}</td>
        </tr>
    `).join('');
}

// Retorna cor do status
function getStatusColor(status) {
    switch (status) {
        case 'enviada': return 'info';
        case 'entregue': return 'success';
        case 'lida': return 'primary';
        case 'falha': return 'danger';
        default: return 'secondary';
    }
}

// ==================== FUNÇÕES DE CONTATOS ====================

// Carrega lista de contatos
async function loadContacts() {
    try {
        const response = await apiRequest('messages/contacts');
        if (response.success) {
            displayContacts(response.data);
        } else {
            alert('Erro ao carregar contatos: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar contatos: ' + error.message);
    }
}

// Exibe contatos na tabela
function displayContacts(contacts) {
    const tbody = document.getElementById('contacts-table-body');
    if (!contacts || contacts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum contato encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = contacts.map(contact => `
        <tr>
            <td>${contact.id}</td>
            <td>${contact.numero}</td>
            <td>${contact.nome || 'N/A'}</td>
            <td>${new Date(contact.data_criacao).toLocaleString()}</td>
        </tr>
    `).join('');
}

// ==================== FUNÇÕES DE WEBHOOK ====================

// Carrega status do webhook
async function loadWebhookStatus() {
    try {
        const response = await apiRequest('webhook/status');
        if (response.success) {
            displayWebhookStatus(response.data);
        } else {
            alert('Erro ao carregar status do webhook: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar status do webhook: ' + error.message);
    }
}

// Exibe status do webhook
function displayWebhookStatus(status) {
    const container = document.getElementById('webhook-status');
    container.innerHTML = `
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Status do Webhook</h5>
                <p><strong>URL:</strong> ${status.url || 'Não configurado'}</p>
                <p><strong>Última verificação:</strong> ${status.last_check ? new Date(status.last_check).toLocaleString() : 'Nunca'}</p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-${status.active ? 'success' : 'danger'}">
                        ${status.active ? 'Ativo' : 'Inativo'}
                    </span>
                </p>
            </div>
        </div>
    `;
}

// ==================== FUNÇÕES DE STATUS DE MENSAGENS ====================

// Carrega status das mensagens
async function loadMessageStatus() {
    try {
        const response = await apiRequest('messages/status');
        if (response.success) {
            displayMessageStatus(response.data);
        } else {
            alert('Erro ao carregar status das mensagens: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar status das mensagens: ' + error.message);
    }
}

// Exibe status das mensagens
function displayMessageStatus(data) {
    const container = document.getElementById('message-status-container');
    container.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h3 class="text-primary">${data.total || 0}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Enviadas</h5>
                        <h3 class="text-info">${data.enviadas || 0}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Entregues</h5>
                        <h3 class="text-success">${data.entregues || 0}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Lidas</h5>
                        <h3 class="text-primary">${data.lidas || 0}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <h5>Mensagens Não Lidas</h5>
            <div id="unread-messages-list">
                ${displayUnreadMessagesList(data.nao_lidas || [])}
            </div>
        </div>
    `;
}

// Exibe lista de mensagens não lidas
function displayUnreadMessagesList(messages) {
    if (!messages || messages.length === 0) {
        return '<p class="text-muted">Nenhuma mensagem não lida.</p>';
    }
    
    return messages.map(message => `
        <div class="card mb-2">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${message.numero}</strong>
                        <small class="text-muted ms-2">${message.mensagem}</small>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="markAsRead(${message.id})">
                        Marcar como Lida
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Marca mensagem como lida
async function markAsRead(messageId) {
    try {
        const response = await apiRequest(`messages/${messageId}/mark-read`, 'PUT');
        if (response.success) {
            alert('Mensagem marcada como lida!');
            loadMessageStatus();
        } else {
            alert('Erro ao marcar como lida: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao marcar como lida: ' + error.message);
    }
}

// Verifica status na Serpro
async function checkSerproStatus(messageId) {
    try {
        const response = await apiRequest(`messages/${messageId}/check-status`, 'GET');
        if (response.success) {
            alert('Status atualizado: ' + response.data.status);
            loadMessageStatus();
        } else {
            alert('Erro ao verificar status: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao verificar status: ' + error.message);
    }
}

// ==================== FUNÇÕES DE ENVIO DE MENSAGENS ====================

// Envia template
async function sendTemplate() {
    const numero = document.getElementById('template-numero').value;
    const templateName = document.getElementById('template-name').value;
    const parametro = document.getElementById('template-parametro').value;
    
    if (!numero || !templateName || !parametro) {
        alert('Todos os campos são obrigatórios!');
        return;
    }
    
    try {
        const response = await apiRequest('messages/send-template', 'POST', {
            numero: numero,
            template_name: templateName,
            parametro: parametro
        });
        
        if (response.success) {
            alert('Template enviado com sucesso!');
            document.getElementById('template-form').reset();
        } else {
            alert('Erro ao enviar template: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao enviar template: ' + error.message);
    }
}

// Envia mensagem de texto
async function sendTextMessage() {
    const numero = document.getElementById('text-numero').value;
    const mensagem = document.getElementById('text-mensagem').value;
    
    if (!numero || !mensagem) {
        alert('Número e mensagem são obrigatórios!');
        return;
    }
    
    try {
        const response = await apiRequest('messages/send', 'POST', {
            numero: numero,
            mensagem: mensagem
        });
        
        if (response.success) {
            alert('Mensagem enviada com sucesso!');
            document.getElementById('text-form').reset();
        } else {
            alert('Erro ao enviar mensagem: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao enviar mensagem: ' + error.message);
    }
}

// ==================== FUNÇÕES DE CHAT ====================

// Carrega contatos para o chat
async function loadChatContacts() {
    console.log('Carregando contatos para o chat...');
    try {
        const response = await apiRequest('messages/contacts');
        if (response.success) {
            displayChatContacts(response.data);
        } else {
            console.error('Erro ao carregar contatos:', response.error);
            document.getElementById('chat-contacts').innerHTML = '<p class="text-muted p-3">Erro ao carregar contatos</p>';
        }
    } catch (error) {
        console.error('Erro ao carregar contatos:', error);
        document.getElementById('chat-contacts').innerHTML = '<p class="text-muted p-3">Erro ao carregar contatos</p>';
    }
}

// Exibe contatos no chat
function displayChatContacts(contacts) {
    const container = document.getElementById('chat-contacts');
    if (!contacts || contacts.length === 0) {
        container.innerHTML = '<p class="text-muted p-3">Nenhum contato encontrado.</p>';
        return;
    }
    
    container.innerHTML = contacts.map(contact => `
        <div class="chat-contact" onclick="openChat('${contact.numero}')">
            <div class="contact-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="contact-info">
                <div class="contact-name">${contact.nome || contact.numero}</div>
                <div class="contact-number">${contact.numero}</div>
            </div>
        </div>
    `).join('');
}

// Abre chat com um contato
async function openChat(numero) {
    console.log('Abrindo chat com:', numero);
    currentChatContact = numero;
    
    // Atualiza cabeçalho
    document.getElementById('chat-header').textContent = `Chat com ${numero}`;
    
    // Limpa mensagens e input
    document.getElementById('chat-messages').innerHTML = '<p class="text-muted text-center">Carregando mensagens...</p>';
    document.getElementById('chat-input').value = '';
    
    // Habilita campos de entrada
    document.getElementById('chat-input').disabled = false;
    document.getElementById('chat-send-btn').disabled = false;
    
    // Remove seleção ativa de todos os contatos
    document.querySelectorAll('.chat-contact').forEach(contact => {
        contact.classList.remove('active');
    });
    
    // Adiciona seleção ativa ao contato clicado
    const clickedContact = Array.from(document.querySelectorAll('.chat-contact')).find(contact => 
        contact.querySelector('.contact-number').textContent === numero
    );
    if (clickedContact) {
        clickedContact.classList.add('active');
    }
    
    // Carrega mensagens do contato
    await loadChatMessages(numero);
    
    // Inicia polling para receber mensagens
    startChatPolling();
    
    // Marca mensagens como lidas
    await markChatAsRead(numero);
}

// Carrega mensagens do chat
async function loadChatMessages(numero) {
    console.log('Carregando mensagens para:', numero);
    try {
        const response = await apiRequest(`messages?numero=${numero}&limit=50`);
        if (response.success) {
            displayChatMessages(response.data);
        } else {
            console.error('Erro ao carregar mensagens:', response.error);
            document.getElementById('chat-messages').innerHTML = '<p class="text-muted text-center">Erro ao carregar mensagens</p>';
        }
    } catch (error) {
        console.error('Erro ao carregar mensagens:', error);
        document.getElementById('chat-messages').innerHTML = '<p class="text-muted text-center">Erro ao carregar mensagens</p>';
    }
}

// Exibe mensagens no chat
function displayChatMessages(messages) {
    const container = document.getElementById('chat-messages');
    if (!messages || messages.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Nenhuma mensagem</p>';
        return;
    }
    
    container.innerHTML = messages.map(message => `
        <div class="message ${message.direcao === 'enviada' ? 'sent' : 'received'}">
            <div class="message-content">
                <div class="message-text">${message.mensagem}</div>
                <div class="message-time">${new Date(message.data_hora).toLocaleTimeString()}</div>
                ${message.direcao === 'enviada' ? `
                    <div class="message-status">
                        <i class="fas fa-${getStatusIcon(message.status)}"></i>
                    </div>
                ` : ''}
            </div>
        </div>
    `).join('');
    
    // Rola para a última mensagem
    setTimeout(() => {
        container.scrollTop = container.scrollHeight;
    }, 100);
}

// Retorna ícone do status
function getStatusIcon(status) {
    switch (status) {
        case 'enviada': return 'check';
        case 'entregue': return 'check-double';
        case 'lida': return 'check-double text-primary';
        case 'falha': return 'times';
        default: return 'clock';
    }
}

// Inicia polling do chat
function startChatPolling() {
    console.log('Iniciando polling do chat');
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
    }
    
    chatPollingInterval = setInterval(async () => {
        if (currentChatContact) {
            console.log('Polling: atualizando mensagens para', currentChatContact);
            await loadChatMessages(currentChatContact);
        }
    }, 5000); // Atualiza a cada 5 segundos
}

// Para polling do chat
function stopChatPolling() {
    console.log('Parando polling do chat');
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
        chatPollingInterval = null;
    }
}

// Envia mensagem no chat
async function sendChatMessage() {
    const input = document.getElementById('chat-input');
    const mensagem = input.value.trim();
    
    if (!mensagem || !currentChatContact) {
        console.log('Mensagem vazia ou contato não selecionado');
        return;
    }
    
    console.log('Enviando mensagem:', mensagem, 'para:', currentChatContact);
    
    try {
        const response = await apiRequest('messages/send', 'POST', {
            numero: currentChatContact,
            mensagem: mensagem
        });
        
        if (response.success) {
            input.value = '';
            // Recarrega mensagens imediatamente
            await loadChatMessages(currentChatContact);
        } else {
            alert('Erro ao enviar mensagem: ' + response.error);
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        alert('Erro ao enviar mensagem: ' + error.message);
    }
}

// Marca chat como lido
async function markChatAsRead(numero) {
    try {
        console.log('Marcando mensagens como lidas para:', numero);
        await apiRequest(`messages/mark-read-serpro`, 'PUT', { numero: numero });
    } catch (error) {
        console.error('Erro ao marcar como lido:', error);
    }
}

// ==================== FUNÇÕES DE CONFIRMAÇÃO DE LEITURA ====================

// Carrega mensagens não lidas
async function loadUnreadMessages() {
    try {
        const response = await apiRequest('messages?status=entregue&limit=50');
        if (response.success) {
            displayUnreadMessages(response.data);
        } else {
            alert('Erro ao carregar mensagens: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao carregar mensagens: ' + error.message);
    }
}

// Exibe mensagens não lidas
function displayUnreadMessages(messages) {
    const container = document.getElementById('unread-messages-container');
    if (!messages || messages.length === 0) {
        container.innerHTML = '<p class="text-muted">Nenhuma mensagem não lida.</p>';
        return;
    }
    
    container.innerHTML = messages.map(message => `
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${message.numero}</strong>
                        <p class="mb-1">${message.mensagem}</p>
                        <small class="text-muted">${new Date(message.data_hora).toLocaleString()}</small>
                    </div>
                    <button class="btn btn-primary" onclick="markAsReadSerpro(${message.id})">
                        Marcar como Lida
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Marca como lida na Serpro
async function markAsReadSerpro(messageId) {
    try {
        const response = await apiRequest(`messages/mark-read-serpro`, 'PUT', { id: messageId });
        if (response.success) {
            alert('Mensagem marcada como lida na Serpro!');
            loadUnreadMessages();
        } else {
            alert('Erro ao marcar como lida: ' + response.error);
        }
    } catch (error) {
        alert('Erro ao marcar como lida: ' + error.message);
    }
}

// ==================== FUNÇÕES DO DASHBOARD ====================

// Carrega dados do dashboard (cards e mensagens recentes)
async function loadDashboard() {
    try {
        // Buscar estatísticas
        const users = await apiRequest('users');
        document.getElementById('users-count').textContent = users.data ? users.data.length : 0;
        
        const messages = await apiRequest('messages?limit=100');
        document.getElementById('messages-count').textContent = messages.data ? messages.data.length : 0;
        
        // Contar enviadas e recebidas
        let enviadas = 0, recebidas = 0;
        if (messages.data) {
            messages.data.forEach(msg => {
                if (msg.direcao === 'enviada') enviadas++;
                if (msg.direcao === 'recebida') recebidas++;
            });
        }
        document.getElementById('sent-count').textContent = enviadas;
        document.getElementById('received-count').textContent = recebidas;
        
        // Mensagens recentes
        displayRecentMessages(messages.data ? messages.data.slice(0, 5) : []);
    } catch (error) {
        // Em caso de erro, zera os cards
        document.getElementById('users-count').textContent = '0';
        document.getElementById('messages-count').textContent = '0';
        document.getElementById('sent-count').textContent = '0';
        document.getElementById('received-count').textContent = '0';
        displayRecentMessages([]);
    }
}

// Exibe as mensagens recentes no dashboard
function displayRecentMessages(messages) {
    const container = document.getElementById('recent-messages');
    if (!messages || messages.length === 0) {
        container.innerHTML = '<p class="text-muted">Nenhuma mensagem encontrada.</p>';
        return;
    }
    container.innerHTML = messages.map(message => `
        <div class="card mb-2">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${message.numero}</strong>
                        <span class="badge bg-${message.direcao === 'enviada' ? 'primary' : 'success'} ms-2">
                            ${message.direcao}
                        </span>
                    </div>
                    <small class="text-muted">${new Date(message.data_hora).toLocaleString()}</small>
                </div>
                <div class="mt-1">${message.mensagem}</div>
            </div>
        </div>
    `).join('');
}

// Atualiza dados ao clicar em "Atualizar"
async function refreshData() {
    await loadDashboard();
}

// ==================== INICIALIZAÇÃO ====================

// Função para verificar se todos os elementos estão presentes
function debugElements() {
    console.log('=== DEBUG: Verificando elementos ===');
    const requiredElements = [
        'login-content', 'dashboard-content', 'users-content', 'messages-content',
        'contacts-content', 'webhook-content', 'message-status-content',
        'send-message-content', 'chat-content', 'read-confirmation-content',
        'page-title', 'current-user', 'user-info'
    ];
    
    requiredElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            console.log(`✅ ${id}: OK`);
        } else {
            console.error(`❌ ${id}: NÃO ENCONTRADO`);
        }
    });
    
    // Verificar se o JavaScript está sendo carregado
    console.log('✅ dashboard.js carregado com sucesso');
    console.log('=== FIM DEBUG ===');
}

// Submissão do formulário de login
window.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando dashboard...');
    
    // Debug dos elementos
    debugElements();
    
    // Atualiza informações do usuário
    updateUserInfo();
    
    // Inicializa tela correta
    if (isAuthenticated()) {
        console.log('Usuário autenticado, mostrando dashboard');
        showDashboard();
    } else {
        console.log('Usuário não autenticado, mostrando login');
        showLogin();
    }
    
    // Login
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.onsubmit = async function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const senha = document.getElementById('login-senha').value;
            try {
                await doLogin(email, senha);
            } catch (error) {
                alert('Erro no login: ' + error.message);
            }
        };
    }
    
    // Event listeners para modais
    const userModal = document.getElementById('user-modal');
    if (userModal) {
        // Fecha modal ao clicar fora
        window.onclick = function(event) {
            if (event.target === userModal) {
                userModal.style.display = 'none';
            }
        }
    }
    
    // Event listener para envio de mensagem no chat
    const chatInput = document.getElementById('chat-input');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
    }
    
    console.log('Dashboard inicializada com sucesso!');
});

// Funções globais para uso nos onclick do HTML
window.showLogin = showLogin;
window.showDashboard = showDashboard;
window.showUsers = showUsers;
window.showMessages = showMessages;
window.showContacts = showContacts;
window.showWebhook = showWebhook;
window.showMessageStatus = showMessageStatus;
window.showSendMessage = showSendMessage;
window.showChat = showChat;
window.showReadConfirmation = showReadConfirmation;
window.logout = logout;
window.refreshData = refreshData;
window.openCreateUserModal = openCreateUserModal;
window.editUser = editUser;
window.deleteUser = deleteUser;
window.saveUser = saveUser;
window.closeUserModal = closeUserModal;
window.sendTemplate = sendTemplate;
window.sendTextMessage = sendTextMessage;
window.openChat = openChat;
window.sendChatMessage = sendChatMessage;
window.markAsRead = markAsRead;
window.markAsReadSerpro = markAsReadSerpro;
window.checkSerproStatus = checkSerproStatus;
window.debugElements = debugElements;
window.resetChat = resetChat;

// Log de inicialização
console.log('dashboard.js carregado - todas as funções disponíveis'); 