<?php

/**
 * Teste da API v1 - Chat API WhatsApp
 * 
 * Este arquivo demonstra como fazer requisições para a API v1.
 */

// Configurações
$base_url = 'http://localhost/chat-api'; // Altere para sua URL
$api_version = 'v1';

echo "=== TESTE DA API v1 ===\n\n";

/**
 * Função para fazer requisições HTTP
 */
function makeRequest($url, $method = 'GET', $data = null) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ]
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return [
        'status' => $httpCode,
        'body' => $response
    ];
}

// Testes das rotas

echo "1. Testando rota raiz v1:\n";
echo "GET {$base_url}/{$api_version}/\n";
$response = makeRequest("{$base_url}/{$api_version}/");
echo "Status: {$response['status']}\n";
echo "Response: {$response['body']}\n\n";

echo "2. Testando listagem de usuários v1:\n";
echo "GET {$base_url}/{$api_version}/users\n";
$response = makeRequest("{$base_url}/{$api_version}/users");
echo "Status: {$response['status']}\n";
echo "Response: {$response['body']}\n\n";

echo "3. Testando webhook status v1:\n";
echo "GET {$base_url}/{$api_version}/webhook/status\n";
$response = makeRequest("{$base_url}/{$api_version}/webhook/status");
echo "Status: {$response['status']}\n";
echo "Response: {$response['body']}\n\n";

echo "4. Testando compatibilidade (rota sem prefixo):\n";
echo "GET {$base_url}/\n";
$response = makeRequest("{$base_url}/");
echo "Status: {$response['status']}\n";
echo "Response: {$response['body']}\n\n";

echo "=== URLs DISPONÍVEIS NA API v1 ===\n\n";

$routes = [
    'Usuários' => [
        'POST /v1/users/create' => 'Criar usuário',
        'POST /v1/users/login' => 'Login de usuário',
        'GET /v1/users/fetch' => 'Buscar dados do usuário atual',
        'PUT /v1/users/update' => 'Atualizar usuário',
        'GET /v1/users/{id}' => 'Buscar usuário por ID',
        'DELETE /v1/users/{id}/delete' => 'Excluir usuário',
        'GET /v1/users' => 'Listar usuários'
    ],
    'Mensagens' => [
        'POST /v1/messages/send' => 'Enviar mensagem',
        'GET /v1/messages/conversation' => 'Buscar conversas',
        'GET /v1/messages/contacts' => 'Buscar contatos',
        'GET /v1/messages/status-stats' => 'Estatísticas de status',
        'POST /v1/messages/mark-read' => 'Marcar como lida',
        'POST /v1/messages/mark-read-serpro' => 'Marcar como lida (Serpro)',
        'GET /v1/messages' => 'Listar mensagens',
        'GET /v1/messages/{id}' => 'Buscar mensagem por ID',
        'DELETE /v1/messages/{id}' => 'Excluir mensagem',
        'PUT /v1/messages/{id}/status' => 'Atualizar status da mensagem',
        'GET /v1/messages/{id}/serpro-status' => 'Verificar status no Serpro'
    ],
    'Webhook' => [
        'POST /v1/webhook/receive' => 'Receber webhook',
        'GET /v1/webhook/status' => 'Status do webhook'
    ]
];

foreach ($routes as $category => $categoryRoutes) {
    echo "### {$category}\n";
    foreach ($categoryRoutes as $route => $description) {
        echo "  {$route} - {$description}\n";
    }
    echo "\n";
}

echo "=== EXEMPLO DE USO COM cURL ===\n\n";

echo "# Testar API v1\n";
echo "curl -X GET \"{$base_url}/{$api_version}/users\"\n\n";

echo "# Criar usuário v1\n";
echo "curl -X POST \"{$base_url}/{$api_version}/users/create\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"name\": \"João\", \"email\": \"joao@example.com\"}'\n\n";

echo "# Login v1\n";
echo "curl -X POST \"{$base_url}/{$api_version}/users/login\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"email\": \"joao@example.com\", \"password\": \"senha123\"}'\n\n";

echo "=== EXEMPLO DE USO COM JavaScript ===\n\n";

echo "// Fetch API\n";
echo "fetch('{$base_url}/{$api_version}/users')\n";
echo "  .then(response => response.json())\n";
echo "  .then(data => console.log(data));\n\n";

echo "// Axios\n";
echo "axios.get('{$base_url}/{$api_version}/users')\n";
echo "  .then(response => console.log(response.data));\n\n";

echo "// jQuery\n";
echo "$.get('{$base_url}/{$api_version}/users', function(data) {\n";
echo "  console.log(data);\n";
echo "});\n\n";

echo "=== FIM DOS TESTES ===\n"; 