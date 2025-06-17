<?php
// Teste para simular mensagens do n8n sendo enviadas ao webhook
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste do Webhook n8n</h1>";

// URL do webhook
$webhook_url = 'http://localhost/intranet-judiciaria/chat/webhook';

echo "<h2>1. Testando Mensagem de Texto do n8n</h2>";

// Simular dados que vêm do n8n (baseado na sua resposta)
$mensagem_n8n = [
    "messages" => [
        [
            "from" => "556296185892",
            "id" => "wamid.HBgMNTU2Mjk2MTg1ODkyFQIAEhggRUY5ODJBRDMzQjUzM0M1MEY4MTMwQ0QxQ0QzMEVCNDgA",
            "timestamp" => "1750165034",
            "text" => [
                "body" => "Olá! Teste de mensagem do n8n"
            ],
            "type" => "text"
        ]
    ]
];

// Enviar para o webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensagem_n8n));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<strong>Dados enviados:</strong><br>";
echo "<pre>" . htmlspecialchars(json_encode($mensagem_n8n, JSON_PRETTY_PRINT)) . "</pre>";

echo "<strong>Resposta HTTP:</strong> $httpCode<br>";
echo "<strong>Resposta:</strong> " . htmlspecialchars($response) . "<br>";

if ($error) {
    echo "<strong>Erro cURL:</strong> $error<br>";
}

if ($httpCode == 200) {
    echo "✅ <strong>Mensagem processada com sucesso!</strong><br>";
} else {
    echo "❌ <strong>Erro ao processar mensagem</strong><br>";
}

echo "<hr>";

echo "<h2>2. Testando Múltiplas Mensagens</h2>";

$mensagens_multiplas = [
    [
        "from" => "556296185892",
        "id" => "msg_001_" . time(),
        "timestamp" => time(),
        "text" => ["body" => "Primeira mensagem"],
        "type" => "text"
    ],
    [
        "from" => "556296185892", 
        "id" => "msg_002_" . time(),
        "timestamp" => time(),
        "text" => ["body" => "Segunda mensagem"],
        "type" => "text"
    ],
    [
        "from" => "5511999887766",
        "id" => "msg_003_" . time(), 
        "timestamp" => time(),
        "text" => ["body" => "Mensagem de outro número"],
        "type" => "text"
    ]
];

foreach ($mensagens_multiplas as $index => $msg) {
    echo "<h3>Enviando mensagem " . ($index + 1) . "</h3>";
    
    $payload = ["messages" => [$msg]];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Mensagem: " . htmlspecialchars($msg['text']['body']) . "<br>";
    echo "De: " . $msg['from'] . "<br>";
    echo "Status: " . ($httpCode == 200 ? "✅ Sucesso" : "❌ Erro ($httpCode)") . "<br>";
    echo "Resposta: " . htmlspecialchars($response) . "<br><br>";
}

echo "<hr>";

echo "<h2>3. Verificar Logs</h2>";
echo "<p>Verifique o arquivo <code>log.txt</code> na raiz do projeto para ver os logs das mensagens processadas.</p>";

echo "<h2>4. Verificar Banco de Dados</h2>";
echo "<p>Acesse o chat na intranet para verificar se as mensagens foram salvas corretamente nas conversas.</p>";

echo "<h2>5. Estrutura Esperada do n8n</h2>";
echo "<p>O webhook agora suporta mensagens no formato:</p>";
echo "<pre>";
echo htmlspecialchars('{
    "messages": [
        {
            "from": "556296185892",
            "id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIAEhgg...",
            "timestamp": "1750165034",
            "text": {
                "body": "Texto da mensagem"
            },
            "type": "text"
        }
    ]
}');
echo "</pre>";

?> 