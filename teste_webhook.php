<?php

/**
 * [ TESTE WEBHOOK ] - Teste para verificar se o webhook está acessível
 * 
 * @author Desenvolvedor TJGO
 * @copyright 2025 TJGO
 * @version 1.0.0
 */

echo "=== TESTE DE ACESSO AO WEBHOOK ===\n\n";

// URL do webhook
$webhook_url = 'http://10.90.18.141/intranet-judiciaria/chat/webhook';

echo "1. Testando acesso ao webhook: $webhook_url\n\n";

// Teste 1: Verificação GET com token CORRETO (como faria a Meta Business API)
echo "Teste 1: Verificação GET com token CORRETO\n";
$verify_token = 'tjgo_intranet_webhook_2025_secreto_serpro';
$challenge = 'teste-challenge-456';

$url_get = $webhook_url . "?hub_verify_token=$verify_token&hub_challenge=$challenge";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_get);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ ERRO cURL: $error\n";
} else {
    echo "✅ HTTP Status: $http_code\n";
    echo "📄 Resposta: $response\n";
    
    if ($http_code == 200 && $response == $challenge) {
        echo "🎉 Webhook FUNCIONANDO PERFEITAMENTE! Token validado e challenge retornado.\n";
    } elseif ($http_code == 403) {
        echo "⚠️ Webhook respondeu com 403 (token inválido)\n";
    } else {
        echo "⚠️ Status HTTP inesperado: $http_code\n";
    }
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// Teste 2: Verificação GET com token INCORRETO  
echo "Teste 2: Verificação GET com token INCORRETO\n";
$verify_token_wrong = 'token-incorreto';

$url_get_wrong = $webhook_url . "?hub_verify_token=$verify_token_wrong&hub_challenge=$challenge";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_get_wrong);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ ERRO cURL: $error\n";
} else {
    echo "✅ HTTP Status: $http_code\n";
    
    if ($http_code == 403) {
        echo "🎉 Webhook rejeitou token incorreto corretamente (403)!\n";
    } else {
        echo "⚠️ Status HTTP inesperado: $http_code\n";
    }
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// Teste 3: POST de mensagem simulada
echo "Teste 3: POST de mensagem simulada (webhook de recebimento)\n";

$payload_mensagem = [
    'entry' => [
        [
            'changes' => [
                [
                    'field' => 'messages',
                    'value' => [
                        'messages' => [
                            [
                                'id' => 'msg_test_123',
                                'from' => '62996185892',
                                'timestamp' => time(),
                                'type' => 'text',
                                'text' => [
                                    'body' => 'Olá, como vai?'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_mensagem));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ ERRO cURL: $error\n";
} else {
    echo "✅ HTTP Status: $http_code\n";
    echo "📄 Resposta: $response\n";
    
    if ($http_code == 200 && $response == 'OK') {
        echo "🎉 Webhook processou mensagem POST corretamente!\n";
    } else {
        echo "⚠️ Status HTTP: $http_code\n";
    }
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// Informações finais
echo "📋 RESUMO DO TESTE:\n";
echo "🔗 URL do Webhook: $webhook_url\n";
echo "🔑 Token de Verificação: tjgo_intranet_webhook_2025_secreto_serpro\n";
echo "📱 Para configurar na API SERPRO, use esta URL e token.\n";

echo "\n=== FIM DO TESTE ===\n";