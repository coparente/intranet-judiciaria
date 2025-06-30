-- Script SQL para atualizar estrutura do banco para suporte a mídias
-- Execute este script para adicionar as colunas necessárias

-- 1. Adicionar colunas para informações de mídia na tabela mensagens_chat
ALTER TABLE mensagens_chat 
ADD COLUMN IF NOT EXISTS midia_url VARCHAR(500) COMMENT 'URL da mídia baixada localmente',
ADD COLUMN IF NOT EXISTS midia_nome VARCHAR(255) COMMENT 'Nome original do arquivo de mídia';

-- 2. Criar índices para melhorar performance nas consultas de mídia
ALTER TABLE mensagens_chat 
ADD INDEX idx_midia_tipo (tipo),
ADD INDEX idx_midia_url (midia_url(100)),
ADD INDEX idx_conversa_midia (conversa_id, tipo);

-- 3. Criar tabela para estatísticas de mídias (opcional)
CREATE TABLE IF NOT EXISTS chat_midias_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_referencia DATE NOT NULL,
    tipo_midia VARCHAR(50) NOT NULL,
    total_arquivos INT DEFAULT 0,
    espaco_usado BIGINT DEFAULT 0 COMMENT 'Espaço em bytes',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_data_tipo (data_referencia, tipo_midia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Criar tabela de configurações para o chat (se não existir)
CREATE TABLE IF NOT EXISTS chat_configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descricao TEXT,
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NOT NULL,
    INDEX idx_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Inserir configurações padrão para mídias
INSERT IGNORE INTO chat_configuracoes (chave, valor, descricao, criado_em, atualizado_em) VALUES
('midia_download_automatico', '1', 'Ativar download automático de mídias (1=sim, 0=não)', NOW(), NOW()),
('midia_dias_manter', '90', 'Número de dias para manter mídias antes da limpeza automática', NOW(), NOW()),
('midia_tamanho_maximo', '104857600', 'Tamanho máximo de arquivo em bytes (100MB padrão)', NOW(), NOW()),
('midia_tipos_permitidos', 'image,audio,video,document', 'Tipos de mídia permitidos para download', NOW(), NOW());

-- 6. Atualizar descrição da tabela mensagens_chat
ALTER TABLE mensagens_chat COMMENT = 'Mensagens do chat com suporte a mídias baixadas automaticamente';

-- 7. Verificar estrutura final
DESCRIBE mensagens_chat;

-- 8. Script para limpeza de mídias antigas (exemplo de uso)
/*
-- Buscar mídias antigas (mais de 90 dias)
SELECT 
    m.*,
    CASE 
        WHEN m.conteudo LIKE 'uploads/%' THEN m.conteudo
        ELSE NULL
    END as caminho_arquivo
FROM mensagens_chat m
WHERE m.enviado_em < DATE_SUB(NOW(), INTERVAL 90 DAY)
AND m.tipo IN ('image', 'audio', 'video', 'document')
AND (m.midia_url IS NOT NULL OR m.conteudo LIKE 'uploads/%');
*/

-- 9. Consulta para estatísticas de uso de mídias
/*
SELECT 
    tipo,
    COUNT(*) as total_mensagens,
    COUNT(CASE WHEN midia_url IS NOT NULL THEN 1 END) as midias_baixadas,
    DATE(enviado_em) as data_envio
FROM mensagens_chat 
WHERE tipo IN ('image', 'audio', 'video', 'document')
GROUP BY tipo, DATE(enviado_em)
ORDER BY data_envio DESC, tipo;
*/

-- 10. Criar view para facilitar consultas de mídias
CREATE OR REPLACE VIEW vw_chat_midias AS
SELECT 
    m.id,
    m.conversa_id,
    m.tipo,
    m.conteudo,
    m.midia_url,
    m.midia_nome,
    m.message_id,
    m.enviado_em,
    c.contato_nome,
    c.contato_numero,
    c.usuario_id,
    u.nome as usuario_nome,
    CASE 
        WHEN m.midia_url IS NOT NULL THEN 'local'
        WHEN m.conteudo LIKE 'uploads/%' THEN 'local'
        ELSE 'remoto'
    END as status_midia,
    CASE 
        WHEN m.tipo = 'image' THEN 'Imagem'
        WHEN m.tipo = 'audio' THEN 'Áudio'
        WHEN m.tipo = 'video' THEN 'Vídeo'
        WHEN m.tipo = 'document' THEN 'Documento'
        ELSE 'Outro'
    END as tipo_midia_legivel
FROM mensagens_chat m
LEFT JOIN conversas c ON m.conversa_id = c.id
LEFT JOIN usuarios u ON c.usuario_id = u.id
WHERE m.tipo IN ('image', 'audio', 'video', 'document')
ORDER BY m.enviado_em DESC;

-- Concluído! Estrutura atualizada para suporte completo a mídias 