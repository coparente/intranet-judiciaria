<?php

/**
 * [ CHATMODEL ] - Model responsável por gerenciar as conversas e mensagens do chat.
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class ChatModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Busca conversas do usuário
     */
    public function buscarConversas($usuario_id)
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM mensagens_chat m 
                 WHERE m.conversa_id = c.id AND m.lido = 0 AND m.remetente_id IS NULL) as nao_lidas,
                (SELECT m2.conteudo FROM mensagens_chat m2 
                 WHERE m2.conversa_id = c.id 
                 ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_mensagem,
                (SELECT m2.enviado_em FROM mensagens_chat m2 
                 WHERE m2.conversa_id = c.id 
                 ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_atividade
                FROM conversas c 
                WHERE c.usuario_id = :usuario_id 
                ORDER BY c.atualizado_em DESC";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        return $this->db->resultados();
    }

    /**
     * Busca conversas com filtros
     */
    public function buscarConversasComFiltros($usuario_id, $filtroContato = '', $filtroNumero = '', $limite = 10, $offset = 0, $filtroStatus = '', $filtroNome = '')
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM mensagens_chat m 
                 WHERE m.conversa_id = c.id AND m.lido = 0 AND m.remetente_id IS NULL) as nao_lidas,
                (SELECT m2.conteudo FROM mensagens_chat m2 
                 WHERE m2.conversa_id = c.id 
                 ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_mensagem,
                (SELECT m2.enviado_em FROM mensagens_chat m2 
                 WHERE m2.conversa_id = c.id 
                 ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_atividade,
                u.nome as responsavel_nome,
                c.template_enviado_em,
                c.precisa_novo_template,
                c.ultima_resposta_cliente,
                TIMESTAMPDIFF(HOUR, COALESCE(c.ultima_resposta_cliente, c.template_enviado_em), NOW()) as horas_sem_resposta,
                CASE 
                    WHEN c.template_enviado_em IS NOT NULL AND c.precisa_novo_template = 1 THEN 'Template Vencido'
                    WHEN c.template_enviado_em IS NOT NULL AND c.precisa_novo_template = 0 THEN 'Template Ativo'
                    ELSE 'Sem Template'
                END as template_nome
                FROM conversas c 
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.usuario_id = :usuario_id";

        // Aplicar filtros
        $params = [':usuario_id' => $usuario_id];
        
        if (!empty($filtroContato)) {
            $sql .= " AND c.contato_nome LIKE :filtro_contato";
            $params[':filtro_contato'] = '%' . $filtroContato . '%';
        }
        
        if (!empty($filtroNumero)) {
            $sql .= " AND c.contato_numero LIKE :filtro_numero";
            $params[':filtro_numero'] = '%' . $filtroNumero . '%';
        }
        
        if (!empty($filtroStatus)) {
            $sql .= " AND c.status_atendimento = :filtro_status";
            $params[':filtro_status'] = $filtroStatus;
        } else {
            // Excluir conversas com status "fechado" quando não há filtro específico
            $sql .= " AND (c.status_atendimento != 'fechado' OR c.status_atendimento IS NULL)";
        }
        
        if (!empty($filtroNome)) {
            $sql .= " AND c.contato_nome LIKE :filtro_nome";
            $params[':filtro_nome'] = '%' . $filtroNome . '%';
        }

        $sql .= " ORDER BY c.atualizado_em DESC LIMIT :limite OFFSET :offset";

        $this->db->query($sql);
        
        // Bind dos parâmetros
        foreach ($params as $param => $valor) {
            $this->db->bind($param, $valor);
        }
        
        $this->db->bind(':limite', $limite, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultados();
    }

    /**
     * Conta conversas com filtros
     */
    public function contarConversasComFiltros($usuario_id, $filtroContato = '', $filtroNumero = '', $filtroStatus = '', $filtroNome = '')
    {
        $sql = "SELECT COUNT(*) as total FROM conversas c WHERE c.usuario_id = :usuario_id";

        // Aplicar filtros
        $params = [':usuario_id' => $usuario_id];
        
        if (!empty($filtroContato)) {
            $sql .= " AND c.contato_nome LIKE :filtro_contato";
            $params[':filtro_contato'] = '%' . $filtroContato . '%';
        }
        
        if (!empty($filtroNumero)) {
            $sql .= " AND c.contato_numero LIKE :filtro_numero";
            $params[':filtro_numero'] = '%' . $filtroNumero . '%';
        }
        
        if (!empty($filtroStatus)) {
            $sql .= " AND c.status_atendimento = :filtro_status";
            $params[':filtro_status'] = $filtroStatus;
        } else {
            // Excluir conversas com status "fechado" quando não há filtro específico
            $sql .= " AND (c.status_atendimento != 'fechado' OR c.status_atendimento IS NULL)";
        }

        if (!empty($filtroNome)) {
            $sql .= " AND c.contato_nome LIKE :filtro_nome";
            $params[':filtro_nome'] = '%' . $filtroNome . '%';
        }

        $this->db->query($sql);
        
        // Bind dos parâmetros
        foreach ($params as $param => $valor) {
            $this->db->bind($param, $valor);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Buscar ou criar conversa
     */
    public function buscarOuCriarConversa($usuario_id, $numero, $contato_nome = null, $processo_id = null)
    {
        try {
            // Primeiro, buscar se já existe uma conversa ativa
            $sql = "SELECT * FROM conversas WHERE contato_numero = :numero AND usuario_id = :usuario_id LIMIT 1";
            $this->db->query($sql);
            $this->db->bind(':numero', $numero);
            $this->db->bind(':usuario_id', $usuario_id);
            $conversa = $this->db->resultado();

            if ($conversa) {
                return $conversa;
            }

            // Se não existe, criar nova conversa com ticket aberto automaticamente
            $sql = "INSERT INTO conversas (
                        usuario_id, 
                        contato_nome, 
                        contato_numero, 
                        processo_id, 
                        status_atendimento,
                        ticket_aberto_em,
                        observacoes,
                        criado_em, 
                        atualizado_em
                    ) VALUES (
                        :usuario_id, 
                        :contato_nome, 
                        :contato_numero, 
                        :processo_id,
                        'aberto',
                        NOW(),
                        CONCAT('[', NOW(), '] Ticket aberto automaticamente.'),
                        NOW(), 
                        NOW()
                    )";

            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
            $this->db->bind(':contato_nome', $contato_nome ?: 'Contato');
            $this->db->bind(':contato_numero', $numero);
            $this->db->bind(':processo_id', $processo_id);

            if ($this->db->executa()) {
                $conversa_id = $this->db->ultimoIdInserido();
                
                // Inserir histórico inicial do ticket
                $sql_historico = "INSERT INTO tickets_historico (
                                    conversa_id, 
                                    status_anterior, 
                                    status_novo, 
                                    usuario_id, 
                                    observacao
                                  ) VALUES (
                                    :conversa_id,
                                    NULL,
                                    'aberto',
                                    :usuario_id,
                                    'Ticket aberto automaticamente ao criar conversa'
                                  )";
                
                $this->db->query($sql_historico);
                $this->db->bind(':conversa_id', $conversa_id);
                $this->db->bind(':usuario_id', $usuario_id);
                $this->db->executa();
                
                // Buscar a conversa criada
                return $this->buscarConversaPorId($conversa_id);
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro ao buscar ou criar conversa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca conversa por ID
     */
    public function buscarConversaPorId($id)
    {
        try {
            $sql = "SELECT c.*, u.nome as usuario_nome 
                    FROM conversas c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id 
                    WHERE c.id = :id LIMIT 1";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            return $this->db->resultado();
        } catch (Exception $e) {
            error_log("Erro ao buscar conversa por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca mensagens de uma conversa
     */
    public function buscarMensagens($conversa_id, $limite = 50)
    {
        $sql = "SELECT m.id, m.conversa_id, m.remetente_id, m.tipo, m.conteudo, 
                m.midia_url, m.midia_nome, m.message_id, m.status, m.lido, 
                m.lido_em, m.enviado_em, m.atualizado_em, u.nome as remetente_nome 
                FROM mensagens_chat m 
                LEFT JOIN usuarios u ON m.remetente_id = u.id 
                WHERE m.conversa_id = :conversa_id 
                ORDER BY m.enviado_em ASC 
                LIMIT :limite";

        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa_id);
        $this->db->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->db->resultados();
    }

    /**
     * Salva mensagem no banco
     */
    public function salvarMensagem($dados)
    {
        $sql = "INSERT INTO mensagens_chat (
                    conversa_id, remetente_id, tipo, conteudo, midia_url, 
                    midia_nome, message_id, status, enviado_em
                ) VALUES (
                    :conversa_id, :remetente_id, :tipo, :conteudo, :midia_url,
                    :midia_nome, :message_id, :status, :enviado_em
                )";

        $this->db->query($sql);
        $this->db->bind(':conversa_id', $dados['conversa_id']);
        $this->db->bind(':remetente_id', $dados['remetente_id'] ?? null);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':conteudo', $dados['conteudo'] ?? null);
        $this->db->bind(':midia_url', $dados['midia_url'] ?? null);
        $this->db->bind(':midia_nome', $dados['midia_nome'] ?? null);
        $this->db->bind(':message_id', $dados['message_id'] ?? null);
        $this->db->bind(':status', $dados['status']);
        $this->db->bind(':enviado_em', $dados['enviado_em']);

        return $this->db->executa();
    }

    /**
     * Atualiza status de mensagem
     */
    public function atualizarStatusMensagem($message_id, $status)
    {
        $sql = "UPDATE mensagens_chat SET 
                status = :status, 
                atualizado_em = NOW() 
                WHERE message_id = :message_id";

        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':message_id', $message_id);

        return $this->db->executa();
    }

    /**
     * Conta mensagens de uma conversa
     */
    public function contarMensagens($conversa_id)
    {
        $sql = "SELECT COUNT(*) as total FROM mensagens_chat WHERE conversa_id = :conversa_id";
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa_id);
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Atualiza conversa
     */
    public function atualizarConversa($conversa_id)
    {
        $sql = "UPDATE conversas SET atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $conversa_id);
        return $this->db->executa();
    }

    /**
     * Atualiza conversa
     */
    public function atualizarContatoConversa($dados)
    {
        $sql = "UPDATE conversas SET atualizado_em = NOW(), contato_nome = :contato_nome WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':contato_nome', $dados['contato_nome']);
        return $this->db->executa();
    }

    /**
     * Marca conversa como lida
     */
    public function marcarComoLida($conversa_id)
    {
        $sql = "UPDATE mensagens_chat SET lido = 1, lido_em = NOW() 
                WHERE conversa_id = :conversa_id AND lido = 0";
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa_id);
        return $this->db->executa();
    }

    /**
     * Marca conversa como lida (alias para compatibilidade)
     */
    public function marcarConversaComoLida($conversa_id)
    {
        return $this->marcarComoLida($conversa_id);
    }

    /**
     * Cria uma nova mensagem (alias para salvarMensagem)
     */
    public function criarMensagem($dados)
    {
        return $this->salvarMensagem($dados);
    }

    /**
     * Exclui uma conversa pelo ID
     * 
     * @param int $conversa_id ID da conversa a ser excluída
     * @return bool True se excluiu com sucesso, False caso contrário
     */
    public function excluirConversa($conversa_id)
    {
        $sql = "DELETE FROM conversas WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $conversa_id);
        return $this->db->executa();
    }

    /**
     * Busca conversa por número e usuário
     */
    public function buscarConversaPorNumero($numero, $usuario_id)
    {
        $sql = "SELECT * FROM conversas 
                WHERE contato_numero = :contato_numero AND usuario_id = :usuario_id 
                LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':contato_numero', $numero);
        $this->db->bind(':usuario_id', $usuario_id);
        return $this->db->resultado();
    }

    /**
     * Busca ou cria conversa por número (sem usuário específico)
     */
    public function buscarOuCriarConversaPorNumero($numero, $contato_nome = null)
    {
        // Buscar conversa existente por número
        $sql = "SELECT * FROM conversas WHERE contato_numero = :contato_numero LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':contato_numero', $numero);
        $conversa = $this->db->resultado();
        
        if ($conversa) {
            return $conversa;
        }

        // Se não encontrou, criar nova conversa
        $sql = "INSERT INTO conversas (contato_nome, contato_numero, criado_em, atualizado_em) 
                VALUES (:contato_nome, :contato_numero, NOW(), NOW())";
        
        $this->db->query($sql);
        $this->db->bind(':contato_nome', $contato_nome ?: 'Contato ' . $numero);
        $this->db->bind(':contato_numero', $numero);
        
        if ($this->db->executa()) {
            $conversa_id = $this->db->ultimoIdInserido();
            return $this->buscarConversaPorId($conversa_id);
        }
        
        return false;
    }

    /**
     * Busca novas mensagens após um ID específico
     */
    public function buscarNovasMensagens($conversa_id, $ultima_mensagem_id = 0)
    {
        $sql = "SELECT m.*, u.nome as remetente_nome 
                FROM mensagens_chat m 
                LEFT JOIN usuarios u ON m.remetente_id = u.id 
                WHERE m.conversa_id = :conversa_id AND m.id > :ultima_mensagem_id 
                ORDER BY m.enviado_em ASC";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa_id);
        $this->db->bind(':ultima_mensagem_id', $ultima_mensagem_id);
        return $this->db->resultados();
    }

    /**
     * Marca mensagens como lidas
     */
    public function marcarMensagensComoLidas($conversa_id)
    {
        $sql = "UPDATE mensagens_chat SET lido = 1, lido_em = NOW() 
                WHERE conversa_id = :conversa_id AND lido = 0 AND remetente_id IS NULL";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa_id);
        return $this->db->executa();
    }

    /**
     * Conta total de conversas do usuário
     */
    public function contarConversas($usuario_id = null)
    {
        if ($usuario_id) {
            $sql = "SELECT COUNT(*) as total FROM conversas WHERE usuario_id = :usuario_id";
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
        } else {
            $sql = "SELECT COUNT(*) as total FROM conversas";
            $this->db->query($sql);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Conta mensagens enviadas
     */
    public function contarMensagensEnviadas($usuario_id = null)
    {
        if ($usuario_id) {
            $sql = "SELECT COUNT(*) as total FROM mensagens_chat m
                    INNER JOIN conversas c ON m.conversa_id = c.id
                    WHERE c.usuario_id = :usuario_id AND m.remetente_id IS NOT NULL";
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
        } else {
            $sql = "SELECT COUNT(*) as total FROM mensagens_chat WHERE remetente_id IS NOT NULL";
            $this->db->query($sql);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Conta mensagens recebidas
     */
    public function contarMensagensRecebidas($usuario_id = null)
    {
        if ($usuario_id) {
            $sql = "SELECT COUNT(*) as total FROM mensagens_chat m
                    INNER JOIN conversas c ON m.conversa_id = c.id
                    WHERE c.usuario_id = :usuario_id AND m.remetente_id IS NULL";
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
        } else {
            $sql = "SELECT COUNT(*) as total FROM mensagens_chat WHERE remetente_id IS NULL";
            $this->db->query($sql);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Conta conversas ativas (com mensagens nos últimos 7 dias)
     */
    public function contarConversasAtivas($usuario_id = null)
    {
        if ($usuario_id) {
            $sql = "SELECT COUNT(DISTINCT c.id) as total FROM conversas c
                    INNER JOIN mensagens_chat m ON c.id = m.conversa_id
                    WHERE c.usuario_id = :usuario_id AND m.enviado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
        } else {
            $sql = "SELECT COUNT(DISTINCT c.id) as total FROM conversas c
                    INNER JOIN mensagens_chat m ON c.id = m.conversa_id
                    WHERE m.enviado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $this->db->query($sql);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Salva configuração do chat
     */
    public function salvarConfiguracao($chave, $valor)
    {
        // Primeiro, verifica se a configuração já existe
        $sql = "SELECT id FROM chat_configuracoes WHERE chave = :chave LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':chave', $chave);
        $existente = $this->db->resultado();

        if ($existente) {
            // Atualizar configuração existente
            $sql = "UPDATE chat_configuracoes SET valor = :valor, atualizado_em = NOW() WHERE chave = :chave";
            $this->db->query($sql);
            $this->db->bind(':valor', $valor);
            $this->db->bind(':chave', $chave);
        } else {
            // Criar nova configuração
            $sql = "INSERT INTO chat_configuracoes (chave, valor, criado_em, atualizado_em) 
                    VALUES (:chave, :valor, NOW(), NOW())";
            $this->db->query($sql);
            $this->db->bind(':chave', $chave);
            $this->db->bind(':valor', $valor);
        }

        return $this->db->executa();
    }

    /**
     * Obtém configurações do chat
     */
    public function obterConfiguracoes()
    {
        // Primeiro, tenta criar a tabela se não existir
        $this->criarTabelaConfiguracoes();

        $sql = "SELECT chave, valor FROM chat_configuracoes";
        $this->db->query($sql);
        $resultados = $this->db->resultados();

        $configuracoes = [];
        if ($resultados) {
            foreach ($resultados as $config) {
                $configuracoes[$config->chave] = $config->valor;
            }
        }

        // Valores padrão se não existirem
        $padroes = [
            'template_padrao' => 'simple_greeting',
            'webhook_url' => URL . '/chat/webhook',
            'auto_resposta' => '0',
            'horario_atendimento' => '08:00-18:00'
        ];

        return array_merge($padroes, $configuracoes);
    }

    /**
     * Cria tabela de configurações se não existir
     */
    private function criarTabelaConfiguracoes()
    {
        $sql = "CREATE TABLE IF NOT EXISTS chat_configuracoes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    chave VARCHAR(100) NOT NULL UNIQUE,
                    valor TEXT,
                    criado_em DATETIME NOT NULL,
                    atualizado_em DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
        $this->db->executa();
    }

    /**
     * Obtém configuração específica
     */
    public function obterConfiguracao($chave, $valorPadrao = null)
    {
        $sql = "SELECT valor FROM chat_configuracoes WHERE chave = :chave LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':chave', $chave);
        $resultado = $this->db->resultado();

        return $resultado ? $resultado->valor : $valorPadrao;
    }

    /**
     * Busca mensagens com status específicos para verificação de atualização
     */
    public function buscarMensagensComStatus($conversa_id, $status_array)
    {
        try {
            // Criar placeholders nomeados
            $placeholders = [];
            for ($i = 0; $i < count($status_array); $i++) {
                $placeholders[] = ":status_$i";
            }
            $statusPlaceholders = implode(',', $placeholders);
            
            $sql = "SELECT m.id, m.conversa_id, m.remetente_id, m.tipo, m.conteudo, 
                    m.midia_url, m.midia_nome, m.message_id, m.status, m.lido, 
                    m.lido_em, m.enviado_em, m.atualizado_em, u.nome as remetente_nome 
                    FROM mensagens_chat m 
                    LEFT JOIN usuarios u ON m.remetente_id = u.id 
                    WHERE m.conversa_id = :conversa_id 
                    AND m.status IN ($statusPlaceholders) 
                    AND m.remetente_id IS NOT NULL 
                    AND m.message_id IS NOT NULL
                    ORDER BY m.enviado_em ASC";
            
            $this->db->query($sql);
            
            // Bind da conversa_id
            $this->db->bind(':conversa_id', $conversa_id);
            
            // Bind dos status
            foreach ($status_array as $index => $status) {
                $this->db->bind(":status_$index", $status);
            }
            
            return $this->db->resultados();
            
        } catch (Exception $e) {
            // Log do erro para debugging
            error_log("Erro em buscarMensagensComStatus: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se uma mensagem já existe no banco
     */
    public function verificarMensagemExistente($messageId)
    {
        try {
            $sql = "SELECT id FROM mensagens_chat WHERE message_id = :message_id LIMIT 1";
            $this->db->query($sql);
            $this->db->bind(':message_id', $messageId);
            return $this->db->resultado();
        } catch (Exception $e) {
            error_log("Erro ao verificar mensagem existente no ChatModel: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca conversas não atribuídas
     */
    public function buscarConversasNaoAtribuidas($filtroContato = '', $filtroNumero = '', $limite = 10, $offset = 0, $filtroStatus = '')
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM mensagens_chat m 
                 WHERE m.conversa_id = c.id AND m.lido = 0 AND m.remetente_id IS NULL) as nao_lidas,
                (SELECT m2.conteudo FROM mensagens_chat m2 
                 WHERE m2.conversa_id = c.id 
                 ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_mensagem,
                (SELECT m2.enviado_em FROM mensagens_chat m2 
                 WHERE m2.conversa_id = c.id 
                 ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_atividade,
                u.nome as responsavel_nome,
                c.template_enviado_em,
                c.precisa_novo_template,
                c.ultima_resposta_cliente,
                TIMESTAMPDIFF(HOUR, COALESCE(c.ultima_resposta_cliente, c.template_enviado_em), NOW()) as horas_sem_resposta,
                CASE 
                    WHEN c.template_enviado_em IS NOT NULL AND c.precisa_novo_template = 1 THEN 'Template Vencido'
                    WHEN c.template_enviado_em IS NOT NULL AND c.precisa_novo_template = 0 THEN 'Template Ativo'
                    ELSE 'Sem Template'
                END as template_nome
                FROM conversas c 
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE (c.usuario_id IS NULL OR c.usuario_id = 0)";

        // Aplicar filtros
        $params = [];
        
        if (!empty($filtroContato)) {
            $sql .= " AND c.contato_nome LIKE :filtro_contato";
            $params[':filtro_contato'] = '%' . $filtroContato . '%';
        }
        
        if (!empty($filtroNumero)) {
            $sql .= " AND c.contato_numero LIKE :filtro_numero";
            $params[':filtro_numero'] = '%' . $filtroNumero . '%';
        }
        
        if (!empty($filtroStatus)) {
            $sql .= " AND c.status_atendimento = :filtro_status";
            $params[':filtro_status'] = $filtroStatus;
        }

        $sql .= " ORDER BY c.criado_em DESC LIMIT :limite OFFSET :offset";

        $this->db->query($sql);
        
        // Bind dos parâmetros
        foreach ($params as $param => $valor) {
            $this->db->bind($param, $valor);
        }
        
        $this->db->bind(':limite', $limite, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultados();
    }

    /**
     * Conta conversas não atribuídas
     */
    public function contarConversasNaoAtribuidas($filtroContato = '', $filtroNumero = '', $filtroStatus = '')
    {
        $sql = "SELECT COUNT(*) as total 
                FROM conversas c 
                WHERE (c.usuario_id IS NULL OR c.usuario_id = 0)";

        // Aplicar filtros
        $params = [];
        
        if (!empty($filtroContato)) {
            $sql .= " AND c.contato_nome LIKE :filtro_contato";
            $params[':filtro_contato'] = '%' . $filtroContato . '%';
        }
        
        if (!empty($filtroNumero)) {
            $sql .= " AND c.contato_numero LIKE :filtro_numero";
            $params[':filtro_numero'] = '%' . $filtroNumero . '%';
        }
        
        if (!empty($filtroStatus)) {
            $sql .= " AND c.status_atendimento = :filtro_status";
            $params[':filtro_status'] = $filtroStatus;
        }

        $this->db->query($sql);

        // Bind dos parâmetros
        foreach ($params as $param => $valor) {
            $this->db->bind($param, $valor);
        }

        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Busca usuários disponíveis para atribuição de conversas
     */
    public function buscarUsuariosParaAtribuicao()
    {
        // Primeiro, tentar buscar usuários com acesso ao módulo chat
        $sql = "SELECT DISTINCT u.id, u.nome, u.email, u.perfil 
                FROM usuarios u 
                INNER JOIN permissoes_usuario pu ON u.id = pu.usuario_id
                INNER JOIN modulos m ON pu.modulo_id = m.id
                WHERE u.status = 'ativo' 
                AND u.perfil IN ('admin', 'analista', 'usuario')
                AND (m.nome LIKE '%chat%' OR m.nome LIKE '%Chat%')
                ORDER BY u.nome ASC";
        
        $this->db->query($sql);
        $usuariosComPermissao = $this->db->resultados();
        
        // Se encontrou usuários com permissão específica, retornar
        if (!empty($usuariosComPermissao)) {
            return $usuariosComPermissao;
        }
        
        // Se não encontrou, retornar todos os usuários ativos com perfis adequados
        $sql = "SELECT u.id, u.nome, u.email, u.perfil 
                FROM usuarios u 
                WHERE u.status = 'ativo' 
                AND u.perfil IN ('admin', 'analista', 'usuario')
                ORDER BY u.nome ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * Atribui uma conversa a um usuário
     */
    public function atribuirConversa($conversa_id, $usuario_id)
    {
        try {
            error_log("DEBUG ATRIBUIR: Iniciando atribuição - conversa_id: $conversa_id, usuario_id: $usuario_id");
            
            $sql = "UPDATE conversas SET 
                    usuario_id = :usuario_id, 
                    atualizado_em = NOW() 
                    WHERE id = :conversa_id";

            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
            $this->db->bind(':conversa_id', $conversa_id);

            $resultado = $this->db->executa();
            error_log("DEBUG ATRIBUIR: Resultado da execução: " . ($resultado ? 'true' : 'false'));
            
            // Verificar quantas linhas foram afetadas
            $linhasAfetadas = $this->db->totalResultados();
            error_log("DEBUG ATRIBUIR: Linhas afetadas: $linhasAfetadas");
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("DEBUG ATRIBUIR: Erro na execução: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca conversas com mais mídias
     */
    public function buscarConversasComMaisMidias($limite = 10)
    {
        try {
            $sql = "SELECT 
                        c.id,
                        c.contato_nome,
                        c.contato_numero,
                        COUNT(m.id) as total_midias
                    FROM conversas c
                    INNER JOIN mensagens_chat m ON c.id = m.conversa_id
                    WHERE m.tipo IN ('image', 'audio', 'video', 'document')
                    GROUP BY c.id, c.contato_nome, c.contato_numero
                    ORDER BY total_midias DESC
                    LIMIT :limite";

            $this->db->query($sql);
            $this->db->bind(':limite', $limite, PDO::PARAM_INT);
            return $this->db->resultados();

        } catch (Exception $e) {
            error_log("Erro ao buscar conversas com mais mídias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se o usuário tem acesso à mídia do MinIO
     */
    public function verificarAcessoMidiaMinIO($usuario_id, $caminhoMinio)
    {
        // AND m.conteudo = :caminho_minio:
        try {
            // CORREÇÃO: Criar parâmetros distintos para cada uso do caminho
            $sql = "SELECT c.* FROM conversas c 
                    INNER JOIN mensagens_chat m ON c.id = m.conversa_id 
                    WHERE c.usuario_id = :usuario_id 
                    AND (m.midia_url = :caminho_minio1 OR m.conteudo = :caminho_minio2)
                    LIMIT 1";

            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
            $this->db->bind(':caminho_minio1', $caminhoMinio);
            $this->db->bind(':caminho_minio2', $caminhoMinio);

            return $this->db->resultado() !== false;

        } catch (Exception $e) {
            error_log("Erro ao verificar acesso à mídia MinIO: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca conversas ativas do mesmo número de telefone (exceto a conversa atual)
     */
    public function buscarConversasAtivasDoContato($contato_numero, $conversa_atual_id = null)
    {
        try {
            $sql = "SELECT c.*, u.nome as agente_nome 
                    FROM conversas c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id 
                    WHERE c.contato_numero = :contato_numero 
                    AND c.usuario_id IS NOT NULL 
                    AND c.usuario_id != 0";
            
            $params = [':contato_numero' => $contato_numero];
            
            // Se especificou conversa atual, exclui ela da busca
            if ($conversa_atual_id !== null) {
                $sql .= " AND c.id != :conversa_atual_id";
                $params[':conversa_atual_id'] = $conversa_atual_id;
            }
            
            $sql .= " ORDER BY c.atualizado_em DESC";

            $this->db->query($sql);
            
            foreach ($params as $param => $valor) {
                $this->db->bind($param, $valor);
            }
            
            return $this->db->resultados();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar conversas ativas do contato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Desativa (fecha) conversas do mesmo contato atribuídas a outros agentes
     */
    public function fecharConversasConflitantes($contato_numero, $conversa_atual_id, $usuario_atual_id)
    {
        try {
            // Buscar conversas conflitantes
            $conversasConflitantes = $this->buscarConversasAtivasDoContato($contato_numero, $conversa_atual_id);
            
            $conversasFechadas = [];
            
            foreach ($conversasConflitantes as $conversa) {
                // Só fecha se for de outro agente
                if ($conversa->usuario_id != $usuario_atual_id) {
                    // Atualizar para desatribuir a conversa
                    $sql = "UPDATE conversas SET 
                            usuario_id = NULL, 
                            atualizado_em = NOW(),
                            observacoes = CONCAT(COALESCE(observacoes, ''), '\n[', NOW(), '] Conversa fechada automaticamente - usuário atribuído a outro agente.')
                            WHERE id = :conversa_id";

                    $this->db->query($sql);
                    $this->db->bind(':conversa_id', $conversa->id);
                    
                    if ($this->db->executa()) {
                        $conversasFechadas[] = [
                            'id' => $conversa->id,
                            'agente_anterior' => $conversa->agente_nome,
                            'usuario_id_anterior' => $conversa->usuario_id
                        ];
                        
                        error_log("CONVERSA FECHADA: ID {$conversa->id} do agente {$conversa->agente_nome} (ID: {$conversa->usuario_id})");
                    }
                }
            }
            
            return $conversasFechadas;
            
        } catch (Exception $e) {
            error_log("Erro ao fechar conversas conflitantes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca usuário por ID
     */
    public function buscarUsuarioPorId($usuario_id)
    {
        try {
            $sql = "SELECT id, nome, email, perfil FROM usuarios WHERE id = :usuario_id LIMIT 1";
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
            return $this->db->resultado();
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * NOVO: Relatório de conversas ativas por agente
     */
    public function relatorioConversasAtivas()
    {
        try {
            $sql = "SELECT 
                        u.id as usuario_id,
                        u.nome as agente_nome,
                        u.email as agente_email,
                        COUNT(c.id) as total_conversas,
                        GROUP_CONCAT(
                            CONCAT(c.contato_nome, ' (', c.contato_numero, ')') 
                            ORDER BY c.atualizado_em DESC
                            SEPARATOR '; '
                        ) as contatos
                    FROM usuarios u 
                    LEFT JOIN conversas c ON u.id = c.usuario_id 
                    WHERE u.status = 'ativo' 
                    AND u.perfil IN ('admin', 'analista', 'usuario')
                    GROUP BY u.id, u.nome, u.email
                    ORDER BY total_conversas DESC, u.nome ASC";

            $this->db->query($sql);
            return $this->db->resultados();
            
        } catch (Exception $e) {
            error_log("Erro ao gerar relatório de conversas ativas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * NOVO: Detectar possíveis conflitos no sistema
     */
    public function detectarConflitos()
    {
        try {
            // Buscar contatos que têm conversas ativas com múltiplos agentes
            $sql = "SELECT 
                        c.contato_numero,
                        c.contato_nome,
                        COUNT(DISTINCT c.usuario_id) as agentes_distintos,
                        COUNT(c.id) as total_conversas,
                        GROUP_CONCAT(
                            CONCAT(u.nome, ' (ID: ', c.usuario_id, ' - Conversa: ', c.id, ')') 
                            ORDER BY c.atualizado_em DESC
                            SEPARATOR '; '
                        ) as detalhes_agentes
                    FROM conversas c 
                    INNER JOIN usuarios u ON c.usuario_id = u.id 
                    WHERE c.usuario_id IS NOT NULL 
                    AND c.usuario_id != 0
                    GROUP BY c.contato_numero, c.contato_nome
                    HAVING agentes_distintos > 1
                    ORDER BY agentes_distintos DESC, total_conversas DESC";

            $this->db->query($sql);
            return $this->db->resultados();
            
        } catch (Exception $e) {
            error_log("Erro ao detectar conflitos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * NOVO: Limpar todos os conflitos automaticamente
     */
    public function limparTodosConflitos()
    {
        try {
            $conflitos = $this->detectarConflitos();
            $resultados = [];
            
            foreach ($conflitos as $conflito) {
                // Para cada contato com conflito, manter apenas a conversa mais recente
                $sql = "SELECT c.*, u.nome as agente_nome 
                        FROM conversas c 
                        INNER JOIN usuarios u ON c.usuario_id = u.id 
                        WHERE c.contato_numero = :contato_numero 
                        AND c.usuario_id IS NOT NULL 
                        AND c.usuario_id != 0
                        ORDER BY c.atualizado_em DESC";

                $this->db->query($sql);
                $this->db->bind(':contato_numero', $conflito->contato_numero);
                $conversasContato = $this->db->resultados();
                
                if (count($conversasContato) > 1) {
                    // Manter a primeira (mais recente) e fechar as outras
                    $conversaMaisRecente = array_shift($conversasContato);
                    $conversasFechadas = [];
                    
                    foreach ($conversasContato as $conversaParaFechar) {
                        $sqlFechar = "UPDATE conversas SET 
                                     usuario_id = NULL, 
                                     atualizado_em = NOW(),
                                     observacoes = CONCAT(COALESCE(observacoes, ''), '\n[', NOW(), '] Conversa fechada automaticamente - limpeza de conflitos em lote.')
                                     WHERE id = :conversa_id";

                        $this->db->query($sqlFechar);
                        $this->db->bind(':conversa_id', $conversaParaFechar->id);
                        
                        if ($this->db->executa()) {
                            $conversasFechadas[] = $conversaParaFechar->agente_nome;
                        }
                    }
                    
                    $resultados[] = [
                        'contato' => $conflito->contato_numero,
                        'nome' => $conflito->contato_nome,
                        'conversa_mantida' => $conversaMaisRecente->agente_nome,
                        'conversas_fechadas' => $conversasFechadas
                    ];
                }
            }
            
            return $resultados;
            
        } catch (Exception $e) {
            error_log("Erro ao limpar conflitos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * =====================================================
     * SISTEMA DE TICKETS PARA CONVERSAS
     * =====================================================
     */

    /**
     * Altera status do ticket de uma conversa
     */
    public function alterarStatusTicket($conversa_id, $novo_status, $usuario_id, $observacao = null)
    {
        try {
            // Buscar status atual
            $sql = "SELECT status_atendimento FROM conversas WHERE id = :conversa_id";
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            $conversa = $this->db->resultado();
            
            if (!$conversa) {
                return false;
            }

            $status_anterior = $conversa->status_atendimento;

            // Atualizar status
            $sql = "UPDATE conversas SET 
                    status_atendimento = :status,
                    atualizado_em = NOW()";

            // Se está fechando o ticket, registrar quando e quem fechou
            if ($novo_status === 'fechado') {
                $sql .= ", ticket_fechado_em = NOW(), ticket_fechado_por = :usuario_id";
            }

            // Adicionar observação se fornecida
            if ($observacao) {
                $sql .= ", observacoes = CONCAT(COALESCE(observacoes, ''), '\n[', NOW(), '] ', :observacao)";
            }

            $sql .= " WHERE id = :conversa_id";

            $this->db->query($sql);
            $this->db->bind(':status', $novo_status);
            $this->db->bind(':conversa_id', $conversa_id);
            
            if ($novo_status === 'fechado') {
                $this->db->bind(':usuario_id', $usuario_id);
            }
            
            if ($observacao) {
                $this->db->bind(':observacao', $observacao);
            }

            return $this->db->executa();

        } catch (Exception $e) {
            error_log("Erro ao alterar status do ticket: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca histórico de um ticket
     */
    public function buscarHistoricoTicket($conversa_id)
    {
        try {
            $sql = "SELECT h.*, u.nome as usuario_nome 
                    FROM tickets_historico h
                    LEFT JOIN usuarios u ON h.usuario_id = u.id
                    WHERE h.conversa_id = :conversa_id
                    ORDER BY h.data_alteracao ASC";

            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            return $this->db->resultados();

        } catch (Exception $e) {
            error_log("Erro ao buscar histórico do ticket: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca relatório de tickets por status
     */
    public function relatorioTicketsPorStatus($usuario_id = null)
    {
        try {
            $sql = "SELECT 
                        status_atendimento as status,
                        COUNT(*) as quantidade,
                        AVG(TIMESTAMPDIFF(HOUR, ticket_aberto_em, COALESCE(ticket_fechado_em, NOW()))) as tempo_medio_horas
                    FROM conversas 
                    WHERE ticket_aberto_em IS NOT NULL";

            if ($usuario_id) {
                $sql .= " AND usuario_id = :usuario_id";
            }

            $sql .= " GROUP BY status_atendimento ORDER BY quantidade DESC";

            $this->db->query($sql);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            return $this->db->resultados();

        } catch (Exception $e) {
            error_log("Erro ao gerar relatório de tickets: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca tickets em aberto há mais de X horas
     */
    public function buscarTicketsVencidos($horas_limite = 24, $usuario_id = null)
    {
        try {
            $sql = "SELECT c.*, u.nome as responsavel_nome,
                           TIMESTAMPDIFF(HOUR, c.ticket_aberto_em, NOW()) as horas_em_aberto
                    FROM conversas c
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.status_atendimento IN ('aberto', 'em_andamento', 'aguardando_cliente')
                    AND c.ticket_aberto_em IS NOT NULL
                    AND TIMESTAMPDIFF(HOUR, c.ticket_aberto_em, NOW()) > :horas_limite";

            if ($usuario_id) {
                $sql .= " AND c.usuario_id = :usuario_id";
            }

            $sql .= " ORDER BY horas_em_aberto DESC";

            $this->db->query($sql);
            $this->db->bind(':horas_limite', $horas_limite, PDO::PARAM_INT);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            return $this->db->resultados();

        } catch (Exception $e) {
            error_log("Erro ao buscar tickets vencidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca conversas com filtro de status de ticket
     */
    public function buscarConversasPorStatusTicket($status_atendimento, $usuario_id = null, $limite = 10, $offset = 0)
    {
        try {
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM mensagens_chat m 
                     WHERE m.conversa_id = c.id AND m.lido = 0 AND m.remetente_id IS NULL) as nao_lidas,
                    (SELECT m2.conteudo FROM mensagens_chat m2 
                     WHERE m2.conversa_id = c.id 
                     ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_mensagem,
                    (SELECT m2.enviado_em FROM mensagens_chat m2 
                     WHERE m2.conversa_id = c.id 
                     ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_atividade,
                    TIMESTAMPDIFF(HOUR, c.ticket_aberto_em, COALESCE(c.ticket_fechado_em, NOW())) as horas_em_aberto,
                    u.nome as responsavel_nome
                    FROM conversas c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.status_atendimento = :status_atendimento";

            if ($usuario_id) {
                $sql .= " AND c.usuario_id = :usuario_id";
            }

            $sql .= " ORDER BY c.atualizado_em DESC LIMIT :limite OFFSET :offset";

            $this->db->query($sql);
            $this->db->bind(':status_atendimento', $status_atendimento);
            $this->db->bind(':limite', $limite, PDO::PARAM_INT);
            $this->db->bind(':offset', $offset, PDO::PARAM_INT);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            return $this->db->resultados();

        } catch (Exception $e) {
            error_log("Erro ao buscar conversas por status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca estatísticas de tickets
     */
    public function estatisticasTickets($usuario_id = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_tickets,
                        COUNT(CASE WHEN status_atendimento = 'aberto' THEN 1 END) as abertos,
                        COUNT(CASE WHEN status_atendimento = 'em_andamento' THEN 1 END) as em_andamento,
                        COUNT(CASE WHEN status_atendimento = 'aguardando_cliente' THEN 1 END) as aguardando_cliente,
                        COUNT(CASE WHEN status_atendimento = 'resolvido' THEN 1 END) as resolvidos,
                        COUNT(CASE WHEN status_atendimento = 'fechado' THEN 1 END) as fechados,
                        AVG(CASE 
                            WHEN status_atendimento = 'fechado' AND ticket_fechado_em IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, ticket_aberto_em, ticket_fechado_em) 
                            ELSE NULL 
                        END) as tempo_medio_resolucao,
                        COUNT(CASE 
                            WHEN status_atendimento IN ('aberto', 'em_andamento', 'aguardando_cliente') 
                            AND TIMESTAMPDIFF(HOUR, ticket_aberto_em, NOW()) > 24 
                            THEN 1 
                        END) as tickets_vencidos,
                        COUNT(CASE WHEN DATE(ticket_aberto_em) = CURDATE() THEN 1 END) as tickets_hoje
                    FROM conversas 
                    WHERE ticket_aberto_em IS NOT NULL";

            if ($usuario_id) {
                $sql .= " AND usuario_id = :usuario_id";
            }

            $this->db->query($sql);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            $resultado = $this->db->resultado();
            
            // Calcular campos derivados
            if ($resultado) {
                $resultado->tickets_pendentes = $resultado->abertos + $resultado->em_andamento + $resultado->aguardando_cliente;
                $resultado->tickets_resolvidos = $resultado->resolvidos + $resultado->fechados;
                $resultado->taxa_resolucao = $resultado->total_tickets > 0 ? 
                    ($resultado->tickets_resolvidos / $resultado->total_tickets) * 100 : 0;
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas de tickets: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reabrir ticket fechado
     */
    public function reabrirTicket($conversa_id, $usuario_id, $observacao = 'Ticket reaberto')
    {
        try {
            $sql = "UPDATE conversas SET 
                    status_atendimento = 'aberto',
                    ticket_fechado_em = NULL,
                    ticket_fechado_por = NULL,
                    atualizado_em = NOW(),
                    observacoes = CONCAT(COALESCE(observacoes, ''), '\n[', NOW(), '] ', :observacao)
                    WHERE id = :conversa_id";

            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            $this->db->bind(':observacao', $observacao);

            return $this->db->executa();

        } catch (Exception $e) {
            error_log("Erro ao reabrir ticket: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca tickets para dashboard
     */
    public function dashboardTickets($usuario_id = null)
    {
        try {
            $estatisticas = $this->estatisticasTickets($usuario_id);
            $ticketsVencidos = $this->buscarTicketsVencidos(24, $usuario_id);
            $relatorioPorStatus = $this->relatorioTicketsPorStatus($usuario_id);

            return [
                'estatisticas' => $estatisticas,
                'tickets_vencidos' => $ticketsVencidos,
                'relatorio_status' => $relatorioPorStatus
            ];

        } catch (Exception $e) {
            error_log("Erro ao buscar dados do dashboard: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca TODAS as conversas com filtros (apenas admin/analista)
     */
    public function buscarTodasConversasComFiltros($filtroContato = '', $filtroNumero = '', $limite = 10, $offset = 0, $filtroStatus = '', $filtroNome = '')
    {
        try {
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM mensagens_chat m 
                     WHERE m.conversa_id = c.id AND m.lido = 0 AND m.remetente_id IS NULL) as nao_lidas,
                    (SELECT m2.conteudo FROM mensagens_chat m2 
                     WHERE m2.conversa_id = c.id 
                     ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_mensagem,
                    (SELECT m2.enviado_em FROM mensagens_chat m2 
                     WHERE m2.conversa_id = c.id 
                     ORDER BY m2.enviado_em DESC LIMIT 1) as ultima_atividade,
                    u.nome as responsavel_nome,
                    c.template_enviado_em,
                    c.precisa_novo_template,
                    c.ultima_resposta_cliente,
                    TIMESTAMPDIFF(HOUR, COALESCE(c.ultima_resposta_cliente, c.template_enviado_em), NOW()) as horas_sem_resposta,
                    CASE 
                        WHEN c.template_enviado_em IS NOT NULL AND c.precisa_novo_template = 1 THEN 'Template Vencido'
                        WHEN c.template_enviado_em IS NOT NULL AND c.precisa_novo_template = 0 THEN 'Template Ativo'
                        ELSE 'Sem Template'
                    END as template_nome
                    FROM conversas c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE 1=1";

            // Aplicar filtros
            $params = [];
            
            if (!empty($filtroContato)) {
                $sql .= " AND c.contato_nome LIKE :filtro_contato";
                $params[':filtro_contato'] = '%' . $filtroContato . '%';
            }
            
            if (!empty($filtroNumero)) {
                $sql .= " AND c.contato_numero LIKE :filtro_numero";
                $params[':filtro_numero'] = '%' . $filtroNumero . '%';
            }
            
            if (!empty($filtroStatus)) {
                $sql .= " AND c.status_atendimento = :filtro_status";
                $params[':filtro_status'] = $filtroStatus;
            }
            
            if (!empty($filtroNome)) {
                $sql .= " AND c.usuario_id = :filtro_nome";
                $params[':filtro_nome'] = $filtroNome;
            }

            $sql .= " ORDER BY c.atualizado_em DESC LIMIT :limite OFFSET :offset";

            $this->db->query($sql);
            
            // Bind dos parâmetros
            foreach ($params as $param => $valor) {
                $this->db->bind($param, $valor);
            }
            
            $this->db->bind(':limite', $limite, PDO::PARAM_INT);
            $this->db->bind(':offset', $offset, PDO::PARAM_INT);

            return $this->db->resultados();

        } catch (Exception $e) {
            error_log("Erro ao buscar todas as conversas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta TODAS as conversas com filtros (apenas admin/analista)
     */
    public function contarTodasConversasComFiltros($filtroContato = '', $filtroNumero = '', $filtroStatus = '', $filtroNome = '')
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM conversas c WHERE 1=1";

            // Aplicar filtros
            $params = [];
            
            if (!empty($filtroContato)) {
                $sql .= " AND c.contato_nome LIKE :filtro_contato";
                $params[':filtro_contato'] = '%' . $filtroContato . '%';
            }
            
            if (!empty($filtroNumero)) {
                $sql .= " AND c.contato_numero LIKE :filtro_numero";
                $params[':filtro_numero'] = '%' . $filtroNumero . '%';
            }
            
            if (!empty($filtroStatus)) {
                $sql .= " AND c.status_atendimento = :filtro_status";
                $params[':filtro_status'] = $filtroStatus;
            }
            
            if (!empty($filtroNome)) {
                $sql .= " AND c.usuario_id = :filtro_nome";
                $params[':filtro_nome'] = $filtroNome;
            }

            $this->db->query($sql);

            // Bind dos parâmetros
            foreach ($params as $param => $valor) {
                $this->db->bind($param, $valor);
            }

            $resultado = $this->db->resultado();
            return $resultado ? $resultado->total : 0;
        } catch (Exception $e) {
            error_log("Erro ao contar todas as conversas: " . $e->getMessage());
            return 0;
        }
    }

    // =====================================================
    // SISTEMA DE MENSAGENS RÁPIDAS
    // =====================================================

    /**
     * Criar tabela de mensagens rápidas se não existir
     */
    public function criarTabelaMensagensRapidas()
    {
        $sql = "CREATE TABLE IF NOT EXISTS mensagens_rapidas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            conteudo TEXT NOT NULL,
            icone VARCHAR(100) DEFAULT 'fas fa-comment',
            ativo TINYINT(1) DEFAULT 1,
            ordem INT DEFAULT 0,
            criado_por INT NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_ativo (ativo),
            INDEX idx_ordem (ordem),
            FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->db->query($sql);
            $resultadoExecucao = $this->db->executa();
            
            if ($resultadoExecucao) {
                // Verificar se a tabela está vazia e inserir mensagem padrão
                $sqlVerificar = "SELECT COUNT(*) as total FROM mensagens_rapidas";
                $this->db->query($sqlVerificar);
                $resultado = $this->db->resultado();
                
                if ($resultado && $resultado->total == 0) {
                    $this->inserirMensagemPadrao();
                }
            }
            
            return $resultadoExecucao;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela mensagens_rapidas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserir mensagem padrão do TJGO
     */
    private function inserirMensagemPadrao()
    {
        $sql = "INSERT INTO mensagens_rapidas (titulo, conteudo, icone, criado_por, ordem) VALUES (:titulo, :conteudo, :icone, :criado_por, :ordem)";
        
        try {
            $this->db->query($sql);
            $this->db->bind(':titulo', "Template de mensagem padrão");
            $this->db->bind(':conteudo', "Somos da Central de Intimação do Tribunal de Justiça do Estado de Goiás (TJGO) ⚖️. Informamos que existe um processo judicial em seu nome, de número XX, em andamento na Comarca de XX.");
            $this->db->bind(':icone', "fas fa-gavel");
            $this->db->bind(':criado_por', 1);
            $this->db->bind(':ordem', 1);
            
            return $this->db->executa();
        } catch (Exception $e) {
            error_log("Erro ao inserir mensagem padrão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar todas as mensagens rápidas ativas
     */
    public function buscarMensagensRapidas($ativo = true)
    {
        $this->criarTabelaMensagensRapidas(); // Garantir que a tabela existe
        
        $sql = "SELECT mr.*, u.nome as criador_nome 
                FROM mensagens_rapidas mr
                LEFT JOIN usuarios u ON mr.criado_por = u.id";
        
        if ($ativo) {
            $sql .= " WHERE mr.ativo = 1";
        }
        
        $sql .= " ORDER BY mr.ordem ASC, mr.id ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * Buscar mensagem rápida por ID
     */
    public function buscarMensagemRapidaPorId($id)
    {
        $sql = "SELECT mr.*, u.nome as criador_nome 
                FROM mensagens_rapidas mr
                LEFT JOIN usuarios u ON mr.criado_por = u.id
                WHERE mr.id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * Criar nova mensagem rápida
     */
    public function criarMensagemRapida($dados)
    {
        $this->criarTabelaMensagensRapidas(); // Garantir que a tabela existe
        
        $sql = "INSERT INTO mensagens_rapidas (titulo, conteudo, icone, ativo, ordem, criado_por) 
                VALUES (:titulo, :conteudo, :icone, :ativo, :ordem, :criado_por)";
        
        $this->db->query($sql);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':conteudo', $dados['conteudo']);
        $this->db->bind(':icone', $dados['icone'] ?? 'fas fa-comment');
        $this->db->bind(':ativo', $dados['ativo'] ?? 1);
        $this->db->bind(':ordem', $dados['ordem'] ?? 0);
        $this->db->bind(':criado_por', $dados['criado_por']);
        
        return $this->db->executa();
    }

    /**
     * Atualizar mensagem rápida
     */
    public function atualizarMensagemRapida($id, $dados)
    {
        $sql = "UPDATE mensagens_rapidas 
                SET titulo = :titulo, conteudo = :conteudo, icone = :icone, ativo = :ativo, ordem = :ordem 
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':conteudo', $dados['conteudo']);
        $this->db->bind(':icone', $dados['icone'] ?? 'fas fa-comment');
        $this->db->bind(':ativo', $dados['ativo'] ?? 1);
        $this->db->bind(':ordem', $dados['ordem'] ?? 0);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * Excluir mensagem rápida
     */
    public function excluirMensagemRapida($id)
    {
        $sql = "DELETE FROM mensagens_rapidas WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * Contar mensagens rápidas
     */
    public function contarMensagensRapidas($ativo = null)
    {
        $sql = "SELECT COUNT(*) as total FROM mensagens_rapidas";
        
        if ($ativo !== null) {
            $sql .= " WHERE ativo = :ativo";
            $this->db->query($sql);
            $this->db->bind(':ativo', $ativo ? 1 : 0);
        } else {
            $this->db->query($sql);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * Reordenar mensagens rápidas
     */
    public function reordenarMensagensRapidas($ordens)
    {
        // Como não temos transações explícitas na classe Database, vamos fazer individualmente
        try {
            foreach ($ordens as $id => $ordem) {
                $sql = "UPDATE mensagens_rapidas SET ordem = :ordem WHERE id = :id";
                $this->db->query($sql);
                $this->db->bind(':ordem', $ordem);
                $this->db->bind(':id', $id);
                
                if (!$this->db->executa()) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao reordenar mensagens: " . $e->getMessage());
            return false;
        }
    }

    /**
     * API para buscar mensagens rápidas (para o modal)
     */
    public function apiMensagensRapidas()
    {
        header('Content-Type: application/json');
        
        try {
            
            
            // Garantir que a tabela existe
            $tabelaCriada = $this->criarTabelaMensagensRapidas();
            
            $mensagens = $this->buscarMensagensRapidas(true); // Apenas ativas
            
            $resultado = array_map(function($msg) {
                return [
                    'id' => $msg->id,
                    'titulo' => $msg->titulo,
                    'conteudo' => $msg->conteudo,
                    'icone' => $msg->icone
                ];
            }, $mensagens);
            

            echo json_encode([
                'success' => true,
                'mensagens' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar mensagens rápidas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * =====================================================
     * CONTROLE DE TEMPO DE RESPOSTA DOS TEMPLATES
     * =====================================================
     */

    /**
     * Marca quando um template foi enviado
     */
    public function marcarTemplateEnviado($conversa_id)
    {
        try {
            $sql = "UPDATE conversas SET 
                    template_enviado_em = NOW(),
                    precisa_novo_template = 0,
                    atualizado_em = NOW()
                    WHERE id = :conversa_id";
            
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            return $this->db->executa();
        } catch (Exception $e) {
            error_log("Erro ao marcar template enviado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca quando o cliente respondeu
     */
    public function marcarRespostaCliente($conversa_id)
    {
        try {
            $sql = "UPDATE conversas SET 
                    ultima_resposta_cliente = NOW(),
                    precisa_novo_template = 0,
                    atualizado_em = NOW()
                    WHERE id = :conversa_id";
            
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            return $this->db->executa();
        } catch (Exception $e) {
            error_log("Erro ao marcar resposta do cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se uma conversa precisa de novo template (24h sem resposta)
     */
    public function verificarPrecisaNovoTemplate($conversa_id)
    {
        try {
            $sql = "SELECT c.*, 
                    TIMESTAMPDIFF(HOUR, COALESCE(c.ultima_resposta_cliente, c.template_enviado_em), NOW()) as horas_sem_resposta
                    FROM conversas c 
                    WHERE c.id = :conversa_id";
            
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            $conversa = $this->db->resultado();
            
            if (!$conversa) {
                return false;
            }

            // Se tem template enviado e passou mais de 24 horas sem resposta
            if ($conversa->template_enviado_em && $conversa->horas_sem_resposta >= 24) {
                // Marcar como precisa de novo template
                $this->marcarPrecisaNovoTemplate($conversa_id);
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro ao verificar se precisa novo template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca conversa como precisa de novo template
     */
    public function marcarPrecisaNovoTemplate($conversa_id)
    {
        try {
            $sql = "UPDATE conversas SET 
                    precisa_novo_template = 1,
                    atualizado_em = NOW()
                    WHERE id = :conversa_id";
            
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            return $this->db->executa();
        } catch (Exception $e) {
            error_log("Erro ao marcar precisa novo template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca conversas que precisam de novo template
     */
    public function buscarConversasPrecisamNovoTemplate($usuario_id = null)
    {
        try {
            $sql = "SELECT c.*, 
                    TIMESTAMPDIFF(HOUR, COALESCE(c.ultima_resposta_cliente, c.template_enviado_em), NOW()) as horas_sem_resposta,
                    u.nome as responsavel_nome
                    FROM conversas c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.precisa_novo_template = 1";
            
            if ($usuario_id) {
                $sql .= " AND c.usuario_id = :usuario_id";
            }
            
            $sql .= " ORDER BY c.template_enviado_em ASC";
            
            $this->db->query($sql);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }
            
            return $this->db->resultados();
        } catch (Exception $e) {
            error_log("Erro ao buscar conversas que precisam de novo template: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualiza automaticamente o status de conversas que precisam de novo template
     */
    public function atualizarStatusTemplatesVencidos()
    {
        try {
            $sql = "UPDATE conversas SET 
                    precisa_novo_template = 1,
                    atualizado_em = NOW()
                    WHERE template_enviado_em IS NOT NULL 
                    AND precisa_novo_template = 0
                    AND (ultima_resposta_cliente IS NULL OR ultima_resposta_cliente < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    AND TIMESTAMPDIFF(HOUR, COALESCE(ultima_resposta_cliente, template_enviado_em), NOW()) >= 24";
            
            $this->db->query($sql);
            $resultado = $this->db->executa();
            
            if ($resultado) {
                error_log("Templates vencidos atualizados: " . $resultado . " conversas marcadas");
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status de templates vencidos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta conversas que precisam de novo template
     */
    public function contarConversasPrecisamNovoTemplate($usuario_id = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM conversas c WHERE c.precisa_novo_template = 1";
            
            if ($usuario_id) {
                $sql .= " AND c.usuario_id = :usuario_id";
            }

            $this->db->query($sql);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            $resultado = $this->db->resultado();
            return $resultado ? $resultado->total : 0;
        } catch (Exception $e) {
            error_log("Erro ao contar conversas que precisam de novo template: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verifica se uma conversa específica precisa de novo template
     */
    public function verificarConversaPrecisaNovoTemplate($conversa_id)
    {
        try {
            $sql = "SELECT precisa_novo_template, template_enviado_em, ultima_resposta_cliente 
                    FROM conversas 
                    WHERE id = :conversa_id";
            
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            
            $resultado = $this->db->resultado();
            
            if ($resultado) {
                return [
                    'precisa_novo_template' => (bool)$resultado->precisa_novo_template,
                    'template_enviado_em' => $resultado->template_enviado_em,
                    'ultima_resposta_cliente' => $resultado->ultima_resposta_cliente
                ];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao verificar se conversa precisa de novo template: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se template pode ser reenviado (após 24 horas sem resposta)
     */
    public function verificarPodeReenviarTemplate($conversa_id)
    {
        try {
            $sql = "SELECT c.template_enviado_em, c.ultima_resposta_cliente,
                    TIMESTAMPDIFF(HOUR, c.template_enviado_em, NOW()) as horas_passadas
                    FROM conversas c 
                    WHERE c.id = :conversa_id";
            
            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversa_id);
            $conversa = $this->db->resultado();
            
            if (!$conversa || !$conversa->template_enviado_em) {
                return false;
            }

            // Se não tem resposta do cliente e passaram 24 horas
            if (!$conversa->ultima_resposta_cliente && $conversa->horas_passadas >= 24) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro ao verificar se pode reenviar template: " . $e->getMessage());
            return false;
        }
    }
}
