<?php

/**
 * [ NOTIFICACAOMODEL ] - Model responsável por gerenciar as notificações de prazos.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class NotificacaoModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * [ criarNotificacao ] - Cria uma nova notificação
     * 
     * @param array $dados - Dados da notificação
     * @return bool - True se a notificação foi criada com sucesso
     */
    public function criarNotificacao($dados) {
        try {
            $this->db->query("INSERT INTO notificacoes (
                usuario_id, tipo, mensagem, data_prazo, status, data_criacao
            ) VALUES (
                :usuario_id, :tipo, :mensagem, :data_prazo, 'pendente', NOW()
            )");

            $this->db->bind(':usuario_id', $dados['usuario_id']);
            $this->db->bind(':tipo', $dados['tipo']);
            $this->db->bind(':mensagem', $dados['mensagem']);
            $this->db->bind(':data_prazo', $dados['data_prazo']);

            return $this->db->executa();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [ criarNotificacaoResponsavel ] - Cria notificação para o responsável do processo
     * 
     * @param int $usuario_id - ID do usuário responsável
     * @param string $tipo - Tipo da notificação
     * @param string $mensagem - Mensagem da notificação
     * @param string $data_prazo - Data prazo da notificação
     * @return bool - True se a notificação foi criada com sucesso
     */
    public function criarNotificacaoResponsavel($usuario_id, $tipo, $mensagem, $data_prazo = null) {
        try {
            if (!$usuario_id) {
                return false;
            }

            return $this->criarNotificacao([
                'usuario_id' => $usuario_id,
                'tipo' => $tipo,
                'mensagem' => $mensagem,
                'data_prazo' => $data_prazo ?? date('Y-m-d', strtotime('+7 days'))
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [ buscarNotificacoesPendentes ] - Busca notificações pendentes para um usuário
     * 
     * @param int $usuario_id - ID do usuário
     * @return array - Array de notificações pendentes
     */
    public function buscarNotificacoesPendentes($usuario_id) {
        $this->db->query("SELECT n.* 
                         FROM notificacoes n
                         WHERE n.usuario_id = :usuario_id 
                         AND n.status = 'pendente'
                         AND n.data_prazo >= CURDATE()
                         ORDER BY n.data_prazo ASC");

        $this->db->bind(':usuario_id', $usuario_id);
        return $this->db->resultados();
    }

    /**
     * [ marcarComoLida ] - Marca notificação como lida
     * 
     * @param int $id - ID da notificação
     * @return bool - True se a notificação foi marcada como lida com sucesso, false caso contrário
     */
    public function marcarComoLida($id) {
        $this->db->query("UPDATE notificacoes 
                         SET status = 'lida', 
                             data_leitura = NOW() 
                         WHERE id = :id");

        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ verificarPrazosVencendo ] - Verifica prazos que estão vencendo nos próximos dias
     * 
     * @return array - Array de prazos vencendo
     */
    public function verificarPrazosVencendo() {
        $sql = "SELECT 
                    u.id as usuario_id,
                    u.nome as usuario_nome,
                    DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '%Y-%m-%d') as prazo,
                    'Prazo de verificação' as descricao
                FROM 
                    usuarios u
                WHERE 
                    u.status = 'ativo'
                AND NOT EXISTS (
                    SELECT 1 
                    FROM notificacoes n 
                    WHERE n.usuario_id = u.id 
                    AND n.data_prazo = DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '%Y-%m-%d')
                )
                ORDER BY u.nome ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ buscarNotificacoesUsuario ] - Busca todas as notificações do usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param string $status Status das notificações (pendente, lida, todas)
     * @return array Array de notificações
     */
    public function buscarNotificacoesUsuario($usuario_id, $status = 'todas') {
        $sql = "SELECT n.* 
                FROM notificacoes n
                WHERE n.usuario_id = :usuario_id";
        
        if ($status !== 'todas') {
            $sql .= " AND n.status = :status";
        }
        
        $sql .= " ORDER BY n.data_criacao DESC";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        
        if ($status !== 'todas') {
            $this->db->bind(':status', $status);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ contarIntimacoesPorTipo ] - Retorna contagem de intimações agrupadas por tipo
     * 
     * @return array Array associativo com tipo_intimacao e total
     */
    public function contarIntimacoesPorTipo() {
        $sql = "SELECT 
                    tipo_intimacao, 
                    COUNT(*) AS total 
                FROM 
                    intimacoes 
                GROUP BY 
                    tipo_intimacao 
                ORDER BY 
                    total DESC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ buscarIntimacoesVencidas ] - Busca intimações com prazo vencido
     * 
     * @return array Array de intimações vencidas
     */
    public function buscarIntimacoesVencidas() {
        $sql = "SELECT 
                    i.*,
                    pt.nome AS parte_nome
                FROM 
                    intimacoes i
                JOIN
                    partes pt ON i.parte_id = pt.id
                WHERE 
                    i.prazo < CURDATE()
                AND 
                    i.status = 'pendente'
                ORDER BY 
                    i.prazo ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ buscarIntimacoesProximasAoVencimento ] - Busca intimações prestes a vencer nos próximos dias
     * 
     * @param int $dias Quantidade de dias para verificar
     * @return array Array de intimações prestes a vencer
     */
    public function buscarIntimacoesProximasAoVencimento($dias = 2) {
        $sql = "SELECT 
                    i.*,
                    pt.nome AS parte_nome,
                    DATEDIFF(i.prazo, CURDATE()) AS dias_restantes
                FROM 
                    intimacoes i
                JOIN
                    partes pt ON i.parte_id = pt.id
                WHERE 
                    i.prazo >= CURDATE()
                AND 
                    i.prazo <= DATE_ADD(CURDATE(), INTERVAL " . $dias . " DAY)
                AND 
                    i.status = 'pendente'
                ORDER BY 
                    i.prazo ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ contarTodasIntimacoes ] - Retorna o total de intimações no sistema
     * 
     * @return int Total de intimações
     */
    public function contarTodasIntimacoes() {
        $this->db->query("SELECT COUNT(*) AS total FROM intimacoes");
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }
}