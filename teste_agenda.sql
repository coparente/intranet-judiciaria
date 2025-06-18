-- Teste da Agenda - Comandos para verificar o banco

-- 1. Verificar se as tabelas existem
SELECT 'Tabelas existentes:' as teste;
SHOW TABLES LIKE 'agenda_%';

-- 2. Verificar estrutura das tabelas
SELECT 'Estrutura agenda_categorias:' as teste;
DESCRIBE agenda_categorias;

SELECT 'Estrutura agenda_eventos:' as teste;
DESCRIBE agenda_eventos;

-- 3. Verificar dados das categorias
SELECT 'Categorias cadastradas:' as teste;
SELECT id, nome, cor, ativo FROM agenda_categorias;

-- 4. Verificar dados dos eventos
SELECT 'Eventos cadastrados:' as teste;
SELECT id, titulo, data_inicio, data_fim, categoria_id, usuario_id, status FROM agenda_eventos;

-- 5. Teste da query principal
SELECT 'Query principal do sistema:' as teste;
SELECT 
    e.id,
    e.titulo as title,
    e.data_inicio as start,
    e.data_fim as end,
    c.cor as backgroundColor,
    c.cor as borderColor,
    e.evento_dia_inteiro
FROM agenda_eventos e
INNER JOIN agenda_categorias c ON e.categoria_id = c.id
WHERE c.ativo = 'S'
ORDER BY e.data_inicio ASC; 