-- =====================================================
-- CONTROLE DE TEMPO DE RESPOSTA DOS TEMPLATES
-- =====================================================
-- Adiciona campos para controlar quando um template foi enviado
-- e quando precisa de um novo template após 24 horas sem resposta
-- =====================================================

-- 1. Adicionar campo para controlar quando o template foi enviado
ALTER TABLE conversas 
ADD COLUMN template_enviado_em DATETIME DEFAULT NULL 
AFTER ticket_fechado_por;

-- 2. Adicionar campo para controlar se precisa de novo template
ALTER TABLE conversas 
ADD COLUMN precisa_novo_template TINYINT(1) DEFAULT 0 
AFTER template_enviado_em;

-- 3. Adicionar campo para controlar última resposta do cliente
ALTER TABLE conversas 
ADD COLUMN ultima_resposta_cliente DATETIME DEFAULT NULL 
AFTER precisa_novo_template;

-- 4. Adicionar índices para melhor performance
ALTER TABLE conversas 
ADD INDEX idx_template_enviado_em (template_enviado_em),
ADD INDEX idx_precisa_novo_template (precisa_novo_template),
ADD INDEX idx_ultima_resposta_cliente (ultima_resposta_cliente);

-- 5. Atualizar conversas existentes que têm mensagens mas não têm template_enviado_em
UPDATE conversas c 
SET c.template_enviado_em = (
    SELECT MIN(m.enviado_em) 
    FROM mensagens_chat m 
    WHERE m.conversa_id = c.id 
    AND m.remetente_id IS NOT NULL
    AND m.tipo = 'text'
)
WHERE c.template_enviado_em IS NULL 
AND EXISTS (
    SELECT 1 FROM mensagens_chat m 
    WHERE m.conversa_id = c.id 
    AND m.remetente_id IS NOT NULL
);

-- 6. Atualizar conversas existentes com última resposta do cliente
UPDATE conversas c 
SET c.ultima_resposta_cliente = (
    SELECT MAX(m.enviado_em) 
    FROM mensagens_chat m 
    WHERE m.conversa_id = c.id 
    AND m.remetente_id IS NULL
)
WHERE EXISTS (
    SELECT 1 FROM mensagens_chat m 
    WHERE m.conversa_id = c.id 
    AND m.remetente_id IS NULL
); 