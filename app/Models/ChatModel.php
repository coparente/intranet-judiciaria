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
     * Busca conversas com filtros e paginação
     */
    public function buscarConversasComFiltros($usuario_id, $filtroContato = '', $filtroNumero = '', $limite = 10, $offset = 0)
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
    public function contarConversasComFiltros($usuario_id, $filtroContato = '', $filtroNumero = '')
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
        // Primeiro, buscar conversa existente
        if ($usuario_id) {
            $sql = "SELECT * FROM conversas 
                    WHERE usuario_id = :usuario_id AND contato_numero = :contato_numero 
                    LIMIT 1";
            
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
            $this->db->bind(':contato_numero', $numero);
            $conversa = $this->db->resultado();
            
            if ($conversa) {
                return $conversa;
            }
        }

        // Se não encontrou, criar nova conversa
        $sql = "INSERT INTO conversas (usuario_id, contato_nome, contato_numero, processo_id, criado_em, atualizado_em) 
                VALUES (:usuario_id, :contato_nome, :contato_numero, :processo_id, NOW(), NOW())";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':contato_nome', $contato_nome ?: 'Contato ' . $numero);
        $this->db->bind(':contato_numero', $numero);
        $this->db->bind(':processo_id', $processo_id);
        
        if ($this->db->executa()) {
            $conversa_id = $this->db->ultimoIdInserido();
            return $this->buscarConversaPorId($conversa_id);
        }
        
        return false;
    }

    /**
     * Busca conversa por ID
     */
    public function buscarConversaPorId($id)
    {
        $sql = "SELECT * FROM conversas WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->resultado();
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
     * Busca conversas não atribuídas a nenhum usuário
     */
    public function buscarConversasNaoAtribuidas($filtroContato = '', $filtroNumero = '', $limite = 10, $offset = 0)
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
                (SELECT COUNT(*) FROM mensagens_chat m3 
                 WHERE m3.conversa_id = c.id) as total_mensagens
                FROM conversas c 
                WHERE c.usuario_id IS NULL OR c.usuario_id = 0";

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
     * Conta conversas não atribuídas
     */
    public function contarConversasNaoAtribuidas($filtroContato = '', $filtroNumero = '')
    {
        $sql = "SELECT COUNT(*) as total FROM conversas c WHERE c.usuario_id IS NULL OR c.usuario_id = 0";

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
        $sql = "SELECT id, nome, email, perfil 
                FROM usuarios 
                WHERE status = 'ativo' 
                AND perfil IN ('admin', 'analista', 'usuario')
                ORDER BY nome ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * Atribui uma conversa a um usuário
     */
    public function atribuirConversa($conversa_id, $usuario_id)
    {
        $sql = "UPDATE conversas SET 
                usuario_id = :usuario_id, 
                atualizado_em = NOW() 
                WHERE id = :conversa_id AND (usuario_id IS NULL OR usuario_id = 0)";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':conversa_id', $conversa_id);

        return $this->db->executa();
    }
}
