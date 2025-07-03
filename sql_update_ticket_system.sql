-- =====================================================
-- SISTEMA DE TICKETS PARA CONVERSAS DO CHAT
-- =====================================================
-- Este script adiciona o sistema de tickets para controle
-- de status de atendimento nas conversas do chat WhatsApp
-- =====================================================

-- 1. Adicionar coluna de status de atendimento na tabela conversas
ALTER TABLE conversas 
ADD COLUMN status_atendimento ENUM('aberto', 'em_andamento', 'aguardando_cliente', 'resolvido', 'fechado') 
DEFAULT 'aberto' 
AFTER processo_id;

-- 2. Adicionar coluna de observações para histórico do ticket
ALTER TABLE conversas 
ADD COLUMN observacoes TEXT NULL 
AFTER status_atendimento;

-- 3. Adicionar colunas de controle de tempo do ticket
ALTER TABLE conversas 
ADD COLUMN ticket_aberto_em DATETIME DEFAULT NULL 
AFTER observacoes;

ALTER TABLE conversas 
ADD COLUMN ticket_fechado_em DATETIME DEFAULT NULL 
AFTER ticket_aberto_em;

ALTER TABLE conversas 
ADD COLUMN ticket_fechado_por INT(11) DEFAULT NULL 
AFTER ticket_fechado_em;

-- 4. Adicionar índices para melhor performance
ALTER TABLE conversas 
ADD INDEX idx_status_atendimento (status_atendimento);

ALTER TABLE conversas 
ADD INDEX idx_ticket_aberto_em (ticket_aberto_em);

ALTER TABLE conversas 
ADD INDEX idx_ticket_fechado_em (ticket_fechado_em);

-- 5. Adicionar chave estrangeira para quem fechou o ticket
ALTER TABLE conversas 
ADD CONSTRAINT fk_ticket_fechado_por 
FOREIGN KEY (ticket_fechado_por) REFERENCES usuarios(id) 
ON DELETE SET NULL;

-- 6. Criar tabela de histórico de status dos tickets
CREATE TABLE IF NOT EXISTS tickets_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversa_id INT NOT NULL,
    status_anterior ENUM('aberto', 'em_andamento', 'aguardando_cliente', 'resolvido', 'fechado') NULL,
    status_novo ENUM('aberto', 'em_andamento', 'aguardando_cliente', 'resolvido', 'fechado') NOT NULL,
    usuario_id INT NOT NULL,
    observacao TEXT NULL,
    data_alteracao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversa_id (conversa_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_data_alteracao (data_alteracao),
    CONSTRAINT fk_historico_conversa 
        FOREIGN KEY (conversa_id) REFERENCES conversas(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_historico_usuario 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Atualizar conversas existentes para abrir tickets automaticamente
UPDATE conversas 
SET 
    status_atendimento = 'aberto',
    ticket_aberto_em = criado_em,
    observacoes = CONCAT(
        COALESCE(observacoes, ''), 
        '\n[', NOW(), '] Ticket aberto automaticamente - migração do sistema.'
    )
WHERE status_atendimento IS NULL OR ticket_aberto_em IS NULL;

-- 8. Inserir histórico inicial para conversas existentes
INSERT INTO tickets_historico (conversa_id, status_anterior, status_novo, usuario_id, observacao, data_alteracao)
SELECT 
    id as conversa_id,
    NULL as status_anterior,
    'aberto' as status_novo,
    COALESCE(usuario_id, 1) as usuario_id,
    'Ticket aberto automaticamente durante migração do sistema' as observacao,
    criado_em as data_alteracao
FROM conversas 
WHERE id NOT IN (SELECT DISTINCT conversa_id FROM tickets_historico);

-- 9. Criar view para relatório de tickets
CREATE OR REPLACE VIEW view_tickets_relatorio AS
SELECT 
    c.id as conversa_id,
    c.contato_nome,
    c.contato_numero,
    c.status_atendimento,
    c.ticket_aberto_em,
    c.ticket_fechado_em,
    u_responsavel.nome as responsavel_atual,
    u_fechou.nome as fechado_por,
    TIMESTAMPDIFF(HOUR, c.ticket_aberto_em, COALESCE(c.ticket_fechado_em, NOW())) as horas_em_aberto,
    (SELECT COUNT(*) FROM mensagens_chat m WHERE m.conversa_id = c.id) as total_mensagens,
    (SELECT COUNT(*) FROM mensagens_chat m WHERE m.conversa_id = c.id AND m.remetente_id IS NOT NULL) as mensagens_enviadas,
    (SELECT COUNT(*) FROM mensagens_chat m WHERE m.conversa_id = c.id AND m.remetente_id IS NULL) as mensagens_recebidas,
    c.criado_em,
    c.atualizado_em
FROM conversas c
LEFT JOIN usuarios u_responsavel ON c.usuario_id = u_responsavel.id
LEFT JOIN usuarios u_fechou ON c.ticket_fechado_por = u_fechou.id;

-- 10. Criar view para estatísticas de tickets
CREATE OR REPLACE VIEW view_tickets_estatisticas AS
SELECT 
    status_atendimento,
    COUNT(*) as total_tickets,
    AVG(TIMESTAMPDIFF(HOUR, ticket_aberto_em, COALESCE(ticket_fechado_em, NOW()))) as tempo_medio_horas,
    MIN(TIMESTAMPDIFF(HOUR, ticket_aberto_em, COALESCE(ticket_fechado_em, NOW()))) as tempo_minimo_horas,
    MAX(TIMESTAMPDIFF(HOUR, ticket_aberto_em, COALESCE(ticket_fechado_em, NOW()))) as tempo_maximo_horas
FROM conversas 
WHERE ticket_aberto_em IS NOT NULL
GROUP BY status_atendimento;

-- =====================================================
-- TRIGGERS PARA AUTOMAÇÃO DO SISTEMA DE TICKETS
-- =====================================================

-- Trigger para abrir ticket automaticamente ao criar conversa
DELIMITER $$
CREATE TRIGGER tr_conversas_abrir_ticket
    BEFORE INSERT ON conversas
    FOR EACH ROW
BEGIN
    IF NEW.status_atendimento IS NULL THEN
        SET NEW.status_atendimento = 'aberto';
    END IF;
    
    IF NEW.ticket_aberto_em IS NULL THEN
        SET NEW.ticket_aberto_em = NOW();
    END IF;
    
    SET NEW.observacoes = CONCAT(
        COALESCE(NEW.observacoes, ''),
        '\n[', NOW(), '] Ticket aberto automaticamente.'
    );
END$$
DELIMITER ;

-- Trigger para registrar mudanças de status no histórico
DELIMITER $$
CREATE TRIGGER tr_conversas_status_historico
    AFTER UPDATE ON conversas
    FOR EACH ROW
BEGIN
    IF OLD.status_atendimento != NEW.status_atendimento THEN
        INSERT INTO tickets_historico (
            conversa_id, 
            status_anterior, 
            status_novo, 
            usuario_id, 
            observacao
        ) VALUES (
            NEW.id,
            OLD.status_atendimento,
            NEW.status_atendimento,
            COALESCE(NEW.usuario_id, 1),
            CONCAT('Status alterado automaticamente de ', OLD.status_atendimento, ' para ', NEW.status_atendimento)
        );
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================

-- Para aplicar este script:
-- 1. Faça backup do banco de dados
-- 2. Execute este script no seu banco MySQL
-- 3. Verifique se todas as alterações foram aplicadas corretamente
-- 4. Teste as funcionalidades do sistema

SELECT 'Sistema de Tickets implementado com sucesso!' as Status; 