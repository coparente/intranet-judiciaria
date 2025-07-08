<?php
/**
 * Script para limpar o arquivo de diagnóstico após resolver o problema
 * EXECUTE APENAS APÓS CONFIRMAR QUE O PROBLEMA FOI RESOLVIDO!
 */

$arquivo_debug = 'debug_imagens.php';

if (file_exists($arquivo_debug)) {
    if (unlink($arquivo_debug)) {
        echo "✅ Arquivo de diagnóstico removido com sucesso!";
    } else {
        echo "❌ Erro ao remover arquivo de diagnóstico.";
    }
} else {
    echo "ℹ️ Arquivo de diagnóstico não encontrado.";
}

// Auto-remover este script também
if (file_exists(__FILE__)) {
    unlink(__FILE__);
}
?> 