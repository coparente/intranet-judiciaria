<?php 

namespace App\Models;

use PDO;

/**
 * Classe Message responsável por gerenciar as mensagens do sistema de chat.
 */
class Message extends Database
{
    /**
     * Salva uma nova mensagem no banco de dados.
     * 
     * @param array $data Dados da mensagem (usuario_id, numero, mensagem, direcao, status)
     * @return bool Retorna true se a mensagem foi salva com sucesso
     */
    public static function save(array $data)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO mensagens (usuario_id, numero, mensagem, direcao, status, data_hora) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['usuario_id'],
            $data['numero'],
            $data['mensagem'],
            $data['direcao'],
            $data['status'] ?? 'enviada'
        ]);

        return $pdo->lastInsertId() > 0;
    }

    /**
     * Busca mensagens por usuário.
     * 
     * @param int $usuarioId ID do usuário
     * @param int $limit Limite de mensagens (padrão: 50)
     * @param int $offset Offset para paginação (padrão: 0)
     * @return array Array com as mensagens
     */
    public static function getByUser($usuarioId, $limit = 50, $offset = 0)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM mensagens 
            WHERE usuario_id = ? 
            ORDER BY data_hora DESC 
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$usuarioId, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Busca conversas por número de telefone.
     * 
     * @param int $usuarioId ID do usuário
     * @param string $numero Número de telefone
     * @param int $limit Limite de mensagens (padrão: 50)
     * @param int $offset Offset para paginação (padrão: 0)
     * @return array Array com as mensagens da conversa
     */
    public static function getConversation($usuarioId, $numero, $limit = 50, $offset = 0)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM mensagens 
            WHERE usuario_id = ? AND numero = ? 
            ORDER BY data_hora ASC 
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$usuarioId, $numero, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Busca os últimos contatos do usuário.
     * 
     * @param int $usuarioId ID do usuário
     * @param int $limit Limite de contatos (padrão: 20)
     * @return array Array com os últimos contatos
     */
    public static function getLastContacts($usuarioId, $limit = 20)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            SELECT numero, MAX(data_hora) as ultima_mensagem, 
                   COUNT(*) as total_mensagens,
                   SUM(CASE WHEN direcao = 'recebida' THEN 1 ELSE 0 END) as mensagens_recebidas,
                   SUM(CASE WHEN direcao = 'enviada' THEN 1 ELSE 0 END) as mensagens_enviadas
            FROM mensagens 
            WHERE usuario_id = ? 
            GROUP BY numero 
            ORDER BY ultima_mensagem DESC 
            LIMIT ?
        ");

        $stmt->execute([$usuarioId, $limit]);

        return $stmt->fetchAll();
    }

    /**
     * Busca mensagens com filtros.
     * 
     * @param int $usuarioId ID do usuário
     * @param array $filters Filtros (numero, direcao, status, data_inicio, data_fim)
     * @param int $limit Limite de mensagens (padrão: 50)
     * @param int $offset Offset para paginação (padrão: 0)
     * @return array Array com as mensagens filtradas
     */
    public static function getWithFilters($usuarioId, array $filters = [], $limit = 50, $offset = 0)
    {
        $pdo = self::getConnection();

        $where = "WHERE usuario_id = ?";
        $params = [$usuarioId];

        if (!empty($filters['numero'])) {
            $where .= " AND numero = ?";
            $params[] = $filters['numero'];
        }

        if (!empty($filters['direcao'])) {
            $where .= " AND direcao = ?";
            $params[] = $filters['direcao'];
        }

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['data_inicio'])) {
            $where .= " AND data_hora >= ?";
            $params[] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $where .= " AND data_hora <= ?";
            $params[] = $filters['data_fim'];
        }

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare("
            SELECT * FROM mensagens 
            $where
            ORDER BY data_hora DESC 
            LIMIT ? OFFSET ?
        ");

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Atualiza o status de uma mensagem.
     * 
     * @param int $id ID da mensagem
     * @param string $status Novo status
     * @return bool Retorna true se a atualização foi bem-sucedida
     */
    public static function updateStatus($id, $status)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("UPDATE mensagens SET status = ? WHERE id = ?");

        $stmt->execute([$status, $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Busca uma mensagem por ID.
     * 
     * @param int $id ID da mensagem
     * @return array|false Dados da mensagem ou false se não encontrada
     */
    public static function find($id)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM mensagens WHERE id = ?");

        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    /**
     * Busca uma mensagem por ID (alias para find).
     * 
     * @param int $id ID da mensagem
     * @return array|false Dados da mensagem ou false se não encontrada
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Marca todas as mensagens recebidas de um número como lidas.
     * 
     * @param int $usuarioId ID do usuário
     * @param string $numero Número de telefone
     * @return int Número de mensagens atualizadas
     */
    public static function markAllAsRead($usuarioId, $numero)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            UPDATE mensagens 
            SET status = 'lida' 
            WHERE usuario_id = ? AND numero = ? AND direcao = 'recebida' AND status != 'lida'
        ");

        $stmt->execute([$usuarioId, $numero]);

        return $stmt->rowCount();
    }

    /**
     * Obtém estatísticas de status das mensagens.
     * 
     * @param int $usuarioId ID do usuário
     * @param string|null $dataInicio Data de início (YYYY-MM-DD)
     * @param string|null $dataFim Data de fim (YYYY-MM-DD)
     * @return array Estatísticas de status
     */
    public static function getStatusStats($usuarioId, $dataInicio = null, $dataFim = null)
    {
        $pdo = self::getConnection();

        $where = "WHERE usuario_id = ?";
        $params = [$usuarioId];

        if ($dataInicio) {
            $where .= " AND data_hora >= ?";
            $params[] = $dataInicio . ' 00:00:00';
        }

        if ($dataFim) {
            $where .= " AND data_hora <= ?";
            $params[] = $dataFim . ' 23:59:59';
        }

        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as total,
                SUM(CASE WHEN direcao = 'enviada' THEN 1 ELSE 0 END) as enviadas,
                SUM(CASE WHEN direcao = 'recebida' THEN 1 ELSE 0 END) as recebidas
            FROM mensagens 
            $where
            GROUP BY status
            ORDER BY total DESC
        ");

        $stmt->execute($params);

        $statusStats = $stmt->fetchAll();

        // Calcula totais
        $totalStats = [
            'total_mensagens' => 0,
            'enviadas' => 0,
            'recebidas' => 0,
            'por_status' => []
        ];

        foreach ($statusStats as $stat) {
            $totalStats['total_mensagens'] += $stat['total'];
            $totalStats['enviadas'] += $stat['enviadas'];
            $totalStats['recebidas'] += $stat['recebidas'];
            $totalStats['por_status'][$stat['status']] = [
                'total' => $stat['total'],
                'enviadas' => $stat['enviadas'],
                'recebidas' => $stat['recebidas']
            ];
        }

        return $totalStats;
    }

    /**
     * Obtém mensagens não lidas de um usuário.
     * 
     * @param int $usuarioId ID do usuário
     * @param int $limit Limite de mensagens (padrão: 50)
     * @return array Array com as mensagens não lidas
     */
    public static function getUnreadMessages($usuarioId, $limit = 50)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM mensagens 
            WHERE usuario_id = ? AND direcao = 'recebida' AND status != 'lida'
            ORDER BY data_hora DESC 
            LIMIT ?
        ");

        $stmt->execute([$usuarioId, $limit]);

        return $stmt->fetchAll();
    }

    /**
     * Conta mensagens não lidas de um usuário.
     * 
     * @param int $usuarioId ID do usuário
     * @return int Número de mensagens não lidas
     */
    public static function countUnreadMessages($usuarioId)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM mensagens 
            WHERE usuario_id = ? AND direcao = 'recebida' AND status != 'lida'
        ");

        $stmt->execute([$usuarioId]);

        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Atualiza o message_id de uma mensagem.
     * 
     * @param int $id ID da mensagem
     * @param string $messageId ID da mensagem da Serpro
     * @return bool Retorna true se a atualização foi bem-sucedida
     */
    public static function updateMessageId($id, $messageId)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("UPDATE mensagens SET message_id = ? WHERE id = ?");

        $stmt->execute([$messageId, $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Deleta uma mensagem.
     * 
     * @param int $id ID da mensagem
     * @return bool Retorna true se a mensagem foi deletada com sucesso
     */
    public static function delete($id)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("DELETE FROM mensagens WHERE id = ?");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
} 