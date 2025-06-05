-- Criar o schema sicuc se não existir
CREATE SCHEMA IF NOT EXISTS sicuc;

-- Estrutura para tabela usuarios
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('usuario', 'analista', 'admin') DEFAULT 'usuario',
    biografia TEXT DEFAULT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    ultimo_acesso DATETIME DEFAULT NULL,
    token_recuperacao VARCHAR(64) DEFAULT NULL,
    token_expiracao DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL
);

-- Estrutura para tabela log_acessos
DROP TABLE IF EXISTS log_acessos;
CREATE TABLE log_acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email VARCHAR(255) DEFAULT NULL,
    ip VARCHAR(45) NOT NULL,
    sucesso BOOLEAN NOT NULL DEFAULT FALSE,
    data_hora DATETIME NOT NULL,
    CONSTRAINT fk_usuario_log FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Estrutura para tabela modulos
DROP TABLE IF EXISTS modulos;
CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT DEFAULT NULL,
    rota VARCHAR(255) NOT NULL UNIQUE,
    icone VARCHAR(50) DEFAULT NULL,
    pai_id INT DEFAULT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL,
    CONSTRAINT fk_pai_modulo FOREIGN KEY (pai_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Estrutura para tabela permissoes_usuario
DROP TABLE IF EXISTS permissoes_usuario;
CREATE TABLE permissoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo_id INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL,
    UNIQUE KEY uq_usuario_modulo (usuario_id, modulo_id),
    CONSTRAINT fk_usuario_permissao FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_modulo_permissao FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Estrutura para tabela atividades
DROP TABLE IF EXISTS atividades;
CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_atividade FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Estrutura para tabela notificacoes
DROP TABLE IF EXISTS notificacoes;
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    mensagem TEXT NOT NULL,
    data_prazo DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    usuario_id INT NOT NULL,
    data_criacao DATETIME NOT NULL,
    data_leitura DATETIME DEFAULT NULL,
    CONSTRAINT fk_usuario_notificacao FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Estrutura para tabela dashboards_bi
DROP TABLE IF EXISTS dashboards_bi;
CREATE TABLE dashboards_bi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    url VARCHAR(1000) NOT NULL,
    categoria VARCHAR(100),
    icone VARCHAR(50) DEFAULT 'fa-chart-bar',
    ordem INT DEFAULT 0,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    usuario_id INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL,
    CONSTRAINT fk_usuario_dashboard FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela para dados do NAC
CREATE TABLE IF NOT EXISTS dados_nac (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL,
    comarca VARCHAR(100) NOT NULL,
    movimentacao TEXT NOT NULL,
    data DATE NOT NULL,
    nome VARCHAR(255) NOT NULL,
    data_importacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para dados do CUC
CREATE TABLE IF NOT EXISTS dados_cuc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL,
    comarca VARCHAR(100) NOT NULL,
    movimentacao TEXT NOT NULL,
    data DATE NOT NULL,
    nome VARCHAR(255) NOT NULL,
    data_importacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para dados das Câmaras
CREATE TABLE IF NOT EXISTS dados_camara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL,
    comarca VARCHAR(100) NOT NULL,
    movimentacao TEXT NOT NULL,
    data DATE NOT NULL,
    nome VARCHAR(255) NOT NULL,
    data_importacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


--
-- Estrutura da tabela `visita`
--

CREATE TABLE visita (
  id int(11) NOT NULL,
  processo varchar(100) NOT NULL,
  comarca varchar(100) NOT NULL,
  autor varchar(220) NOT NULL,
  reu varchar(220) NOT NULL,
  proad varchar(100) NOT NULL,
  nome_ocupacao varchar(220) NOT NULL,
  area_ocupada varchar(100) NOT NULL,
  energia_eletrica varchar(5) NOT NULL,
  agua_tratada varchar(5) NOT NULL,
  area_risco varchar(5) NOT NULL,
  moradia varchar(30) NOT NULL,
  cadastrado_em datetime NOT NULL DEFAULT current_timestamp()
);

--
-- Estrutura da tabela `participantes`
--

CREATE TABLE participantes (
  id int(11) NOT NULL,
  nome varchar(220) NOT NULL,
  cpf varchar(20) NOT NULL,
  contato varchar(30) NOT NULL,
  idade int(11) NOT NULL,
  qtd_pessoas int(11) NOT NULL,
  menores varchar(20) NOT NULL,
  idosos varchar(20) NOT NULL,
  pessoa_deficiencia varchar(220) NOT NULL,
  gestante varchar(220) NOT NULL,
  auxilio varchar(220) NOT NULL,
  frequentam_escola varchar(220) NOT NULL,
  qtd_trabalham varchar(100) NOT NULL,
  vulneravel varchar(4) NOT NULL,
  lote_vago varchar(3) NOT NULL,
  fonte_renda varchar(220) NOT NULL,
  mora_local varchar(4) NOT NULL,
  descricao varchar(1500) NOT NULL,
  visita_id int(11) NOT NULL,
  cadastrado_em datetime NOT NULL DEFAULT current_timestamp()
);


CREATE TABLE processos_analise_ciri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    gratuidade_justica VARCHAR(50),
    numero_processo VARCHAR(30),
    comarca_serventia VARCHAR(150),
    data_atividade DATE,
    observacao_atividade TEXT,
    destinatarios_ciri_id INT NOT NULL,
    tipo_intimacao_ciri_id INT NOT NULL,
    tipo_ato_ciri_id INT NOT NULL,
    status_processo VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_processo FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE movimentacao_ciri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    processo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_movimentacao DATE,
    descricao TEXT,
    CONSTRAINT fk_processo_movimentacao FOREIGN KEY (processo_id) REFERENCES processos_analise_ciri(id) ON DELETE CASCADE,
    CONSTRAINT fk_usuario_movimentacao FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE destinatarios_ciri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    processo_id INT NOT NULL,
    nome VARCHAR(100),
    telefone VARCHAR(100),
    email VARCHAR(100),
    CONSTRAINT fk_processo_destinatario FOREIGN KEY (processo_id) REFERENCES processos_analise_ciri(id) ON DELETE CASCADE
);

CREATE TABLE tipo_ato_ciri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tipo_intimacao_ciri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Tabela de conversas
CREATE TABLE `conversas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `contato_nome` varchar(100) NOT NULL,
  `contato_numero` varchar(20) NOT NULL,
  `processo_id` int(11) DEFAULT NULL,
  `criado_em` datetime NOT NULL,
  `atualizado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `processo_id` (`processo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de mensagens
CREATE TABLE `mensagens_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversa_id` int(11) NOT NULL,
  `remetente_id` int(11) DEFAULT NULL,
  `tipo` enum('text','image','video','audio','document') NOT NULL DEFAULT 'text',
  `conteudo` text DEFAULT NULL,
  `midia_url` varchar(255) DEFAULT NULL,
  `midia_nome` varchar(255) DEFAULT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'enviado',
  `lido` tinyint(1) NOT NULL DEFAULT 0,
  `lido_em` datetime DEFAULT NULL,
  `enviado_em` datetime NOT NULL,
  `atualizado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversa_id` (`conversa_id`),
  KEY `remetente_id` (`remetente_id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de configurações do chat
CREATE TABLE IF NOT EXISTS chat_configuracoes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    chave VARCHAR(100) NOT NULL UNIQUE,
                    valor TEXT,
                    criado_em DATETIME NOT NULL,
                    atualizado_em DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

-- Inserção de dados iniciais para a tabela usuarios
INSERT INTO usuarios (nome, email, senha, perfil, biografia, status, criado_em) VALUES
('Administrador', 'admin@tjgo.jus.br', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'admin', 'Administrador do Sistema', 'ativo', NOW()),
('Analista', 'analista@tjgo.jus.br', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'analista', 'Analista do Sistema', 'ativo', NOW()),
('Usuário', 'usuario@tjgo.jus.br', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'usuario', 'Usuário do Sistema', 'ativo', NOW());
