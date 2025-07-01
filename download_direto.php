<?php
/**
 * Download direto via MinIO
 * Script simples para testar acesso direto aos arquivos
 */

require_once 'app/configuracao.php';
require_once 'app/Libraries/MinioHelper.php';

$arquivo = $_GET['arquivo'] ?? '';

if (empty($arquivo)) {
    http_response_code(400);
    echo "Arquivo não especificado";
    exit;
}

try {
    $resultado = MinioHelper::acessoDirecto($arquivo);
    
    if ($resultado['sucesso']) {
        // Definir headers
        header('Content-Type: ' . $resultado['content_type']);
        header('Content-Length: ' . $resultado['tamanho']);
        header('Content-Disposition: attachment; filename="' . basename($arquivo) . '"');
        header('Cache-Control: private');
        
        // Servir arquivo
        echo $resultado['dados'];
    } else {
        http_response_code(404);
        echo "Erro ao baixar arquivo: " . $resultado['erro'];
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Erro interno: " . $e->getMessage();
}
?> 