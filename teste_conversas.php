<?php
require_once 'app/configuracao.php';
require_once 'app/Libraries/Database.php';

echo 'Verificando conversas não atribuídas...' . "\n";

$db = new Database();
$sql = 'SELECT * FROM conversas WHERE usuario_id IS NULL LIMIT 5';
$db->query($sql);
$conversas = $db->resultados();

if (empty($conversas)) {
    echo 'Nenhuma conversa não atribuída encontrada.' . "\n";
    
    // Criar uma conversa de teste
    echo 'Criando conversa de teste...' . "\n";
    $sql = 'INSERT INTO conversas (contato_nome, contato_numero, criado_em, atualizado_em) VALUES (?, ?, NOW(), NOW())';
    $db->query($sql);
    $db->bind(1, 'Contato de Teste');
    $db->bind(2, '5562999999999');
    
    if ($db->executa()) {
        echo 'Conversa de teste criada com sucesso!' . "\n";
    } else {
        echo 'Erro ao criar conversa de teste.' . "\n";
    }
} else {
    echo 'Conversas não atribuídas encontradas:' . "\n";
    foreach ($conversas as $conversa) {
        echo '- ID: ' . $conversa->id . ' | Nome: ' . $conversa->contato_nome . ' | Número: ' . $conversa->contato_numero . "\n";
    }
}

// Verificar usuários disponíveis
$sql = 'SELECT id, nome, perfil FROM usuarios WHERE ativo = 1 LIMIT 5';
$db->query($sql);
$usuarios = $db->resultados();

echo "\n" . 'Usuários disponíveis para atribuição:' . "\n";
foreach ($usuarios as $usuario) {
    echo '- ID: ' . $usuario->id . ' | Nome: ' . $usuario->nome . ' | Perfil: ' . $usuario->perfil . "\n";
}
?> 