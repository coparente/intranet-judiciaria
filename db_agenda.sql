-- Tabela para categorias de eventos (legendas com cores)
CREATE TABLE IF NOT EXISTS `agenda_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cor` varchar(7) NOT NULL DEFAULT '#007bff',
  `descricao` text,
  `ativo` enum('S','N') NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela para eventos da agenda
CREATE TABLE IF NOT EXISTS `agenda_eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `local` varchar(255),
  `observacoes` text,
  `status` enum('agendado','confirmado','cancelado','concluido') NOT NULL DEFAULT 'agendado',
  `evento_dia_inteiro` enum('S','N') NOT NULL DEFAULT 'N',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_agenda_categoria` (`categoria_id`),
  KEY `fk_agenda_usuario` (`usuario_id`),
  KEY `idx_data_inicio` (`data_inicio`),
  KEY `idx_data_fim` (`data_fim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserção de categorias padrão com cores diferentes
INSERT INTO `agenda_categorias` (`nome`, `cor`, `descricao`) VALUES
('Reunião', '#dc3545', 'Reuniões e encontros de trabalho'),
('Audiência', '#007bff', 'Audiências judiciais'),
('Prazo', '#ffc107', 'Prazos processuais e administrativos'),
('Evento', '#28a745', 'Eventos e cerimônias'),
('Tarefa', '#6c757d', 'Tarefas e atividades gerais'),
('Urgente', '#fd7e14', 'Compromissos urgentes'),
('Pessoal', '#6f42c1', 'Compromissos pessoais'),
('Feriado', '#20c997', 'Feriados e datas comemorativas');

-- Inserção de eventos de teste
INSERT INTO `agenda_eventos` (`titulo`, `descricao`, `data_inicio`, `data_fim`, `categoria_id`, `usuario_id`, `local`, `status`) VALUES
('Reunião de Equipe', 'Reunião semanal da equipe de desenvolvimento', '2025-01-20 09:00:00', '2025-01-20 10:00:00', 1, 1, 'Sala de Reuniões', 'agendado'),
('Audiência Processo 123', 'Audiência do processo 123/2025', '2025-01-21 14:00:00', '2025-01-21 16:00:00', 2, 1, 'Vara Cível', 'confirmado'),
('Prazo Recurso', 'Prazo para interposição de recurso', '2025-01-22 23:59:00', '2025-01-22 23:59:00', 3, 1, '', 'agendado'),
('Evento Teste', 'Evento de teste para verificar o funcionamento', '2025-01-23 15:30:00', '2025-01-23 17:00:00', 4, 1, 'Local de Teste', 'agendado'),
('Tarefa Diária', 'Verificação de documentos', '2025-01-24 08:00:00', '2025-01-24 12:00:00', 5, 1, '', 'agendado');

-- Constraints de chave estrangeira (se as tabelas de usuário existirem)
-- ALTER TABLE `agenda_eventos` 
-- ADD CONSTRAINT `fk_agenda_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `agenda_categorias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Se existir tabela de usuários, descomente a linha abaixo:
-- ALTER TABLE `agenda_eventos` 
-- ADD CONSTRAINT `fk_agenda_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE; 