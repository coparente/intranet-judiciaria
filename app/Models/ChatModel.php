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
     * Busca mensagens de uma conversa
     */
    // public function buscarMensagens($conversa_id)
    // {
    //     $sql = "SELECT * FROM mensagens_chat 
    //             WHERE conversa_id = :conversa_id 
    //             ORDER BY enviado_em ASC";

    //     $this->db->query($sql);
    //     $this->db->bind(':conversa_id', $conversa_id);
    //     return $this->db->resultados();
    // }

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
}
