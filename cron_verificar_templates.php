<?php
/**
 * Script para verificar templates vencidos via cron job
 * Execute este script a cada hora para atualizar o status dos templates
 * 
 * Exemplo de cron job:
 * 0 * * * * php /caminho/para/cron_verificar_templates.php
 */

// Incluir configurações
require_once 'app/Config/config.php';
require_once 'app/Core/Database.php';
require_once 'app/Models/ChatModel.php';

try {
    // Inicializar modelo
    $chatModel = new ChatModel();
    
    // Atualizar status de templates vencidos
    $templatesAtualizados = $chatModel->atualizarStatusTemplatesVencidos();
    
    // Buscar conversas que precisam de novo template
    $conversasPrecisamTemplate = $chatModel->buscarConversasPrecisamNovoTemplate();
    $totalPrecisamTemplate = $chatModel->contarConversasPrecisamNovoTemplate();
    
    // Log do resultado
    $logMessage = date('Y-m-d H:i:s') . " - Templates vencidos verificados: " . 
                  $templatesAtualizados . " atualizados, " . 
                  $totalPrecisamTemplate . " precisam de novo template\n";
    
    // Salvar log
    file_put_contents('logs/templates_vencidos.log', $logMessage, FILE_APPEND);
    
    echo "Verificação concluída: $templatesAtualizados templates atualizados, $totalPrecisamTemplate precisam de novo template\n";
    
} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - Erro ao verificar templates vencidos: " . $e->getMessage() . "\n";
    file_put_contents('logs/templates_vencidos.log', $errorMessage, FILE_APPEND);
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?> 