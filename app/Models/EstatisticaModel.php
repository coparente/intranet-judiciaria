<?php

/**
 * [ ESTATISTICAMODEL ] - Model responsável por gerenciar as estatísticas do sistema
 * 
 * Esta classe lida com o cálculo e recuperação de estatísticas baseadas nas atividades
 * dos usuários, incluindo tempo de sessão e métricas gerais.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class EstatisticaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Calcula o tempo total de sessão por usuário
     * @param string $data_inicio Data inicial no formato Y-m-d
     * @param string $data_fim Data final no formato Y-m-d
     * @param string $nome_usuario Nome do usuário para filtrar
     * @return array Array com estatísticas de tempo de sessão
     */
    public function calcularTempoSessaoPorUsuario($data_inicio = null, $data_fim = null, $nome_usuario = null) {
        $sql = "SELECT 
                    u.nome,
                    u.perfil,
                    COUNT(DISTINCT DATE(a.data_hora)) as dias_ativos,
                    MIN(a.data_hora) as primeira_atividade,
                    MAX(a.data_hora) as ultima_atividade
                FROM cuc_usuarios u
                LEFT JOIN cuc_atividades a ON u.id = a.usuario_id
                WHERE a.acao IN ('Login', 'Logout')";

        if ($data_inicio && $data_fim) {
            $sql .= " AND DATE(a.data_hora) BETWEEN :data_inicio AND :data_fim";
        }

        if ($nome_usuario) {
            $sql .= " AND u.nome ILIKE :nome_usuario";
        }

        $sql .= " GROUP BY u.id, u.nome, u.perfil
                  ORDER BY dias_ativos DESC";

        $this->db->query($sql);

        if ($data_inicio && $data_fim) {
            $this->db->bind(':data_inicio', $data_inicio);
            $this->db->bind(':data_fim', $data_fim);
        }

        if ($nome_usuario) {
            $this->db->bind(':nome_usuario', '%' . $nome_usuario . '%');
        }

        return $this->db->resultados();
    }

    /**
     * Obtém estatísticas gerais do sistema
     * @return array Array com estatísticas gerais
     */
    public function obterEstatisticasGerais() {
        return [
            'total_atividades' => $this->contarTotalAtividades(),
            'usuarios_ativos_hoje' => $this->contarUsuariosAtivosHoje(),
            'media_tempo_sessao' => $this->calcularMediaTempoSessao(),
            'atividades_por_tipo' => $this->contarAtividadesPorTipo()
        ];
    }

    /**
     * Conta o total de atividades
     * @return int Total de atividades
     */
    private function contarTotalAtividades() {
        $this->db->query("SELECT COUNT(*) as total FROM cuc_atividades");
        return $this->db->resultado()->total;
    }

    /**
     * Conta usuários ativos hoje
     * @return int Número de usuários ativos hoje
     */
    private function contarUsuariosAtivosHoje() {
        $this->db->query("SELECT COUNT(DISTINCT usuario_id) as total 
                         FROM cuc_atividades 
                         WHERE DATE(data_hora) = CURRENT_DATE");
        return $this->db->resultado()->total;
    }

    /**
     * Calcula a média de tempo de sessão
     * @return float Média de tempo em minutos
     */
    private function calcularMediaTempoSessao() {
        $this->db->query("SELECT AVG(EXTRACT(EPOCH FROM (ultima - primeira))/60) as media_minutos
                         FROM (
                             SELECT 
                                 usuario_id,
                                 MIN(data_hora) as primeira,
                                 MAX(data_hora) as ultima
                             FROM cuc_atividades
                             WHERE acao IN ('Login', 'Logout')
                             GROUP BY usuario_id, DATE(data_hora)
                         ) as sessoes");
        $resultado = $this->db->resultado();
        return $resultado ? round($resultado->media_minutos, 2) : 0;
    }

    /**
     * Conta atividades por tipo
     * @return array Array com contagem por tipo de atividade
     */
    private function contarAtividadesPorTipo() {
        $this->db->query("SELECT acao, COUNT(*) as total 
                         FROM cuc_atividades 
                         GROUP BY acao 
                         ORDER BY total DESC");
        return $this->db->resultados();
    }

    /**
     * Calcula o tempo total de uso do sistema por usuário
     * @param int $usuario_id ID do usuário (opcional)
     * @param string $data_inicio Data inicial
     * @param string $data_fim Data final
     * @return float Tempo total em minutos
     */
    public function calcularTempoTotalUso($usuario_id = null, $data_inicio = null, $data_fim = null) {
        $sql = "WITH sessoes AS (
            SELECT 
                usuario_id,
                data_hora as inicio,
                LEAD(data_hora) OVER (PARTITION BY usuario_id ORDER BY data_hora) as fim
            FROM cuc_atividades
            WHERE acao IN ('Login', 'Logout')
            ORDER BY usuario_id, data_hora
        )
        SELECT 
            COALESCE(SUM(
                EXTRACT(EPOCH FROM (fim - inicio))/60
            ), 0) as tempo_total
        FROM sessoes
        WHERE fim IS NOT NULL";

        $params = [];
        if ($usuario_id) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuario_id;
        }
        if ($data_inicio && $data_fim) {
            $sql .= " AND DATE(inicio) BETWEEN :data_inicio AND :data_fim";
            $params[':data_inicio'] = $data_inicio;
            $params[':data_fim'] = $data_fim;
        }

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        $resultado = $this->db->resultado();
        return round($resultado->tempo_total, 2);
    }

    /**
     * Calcula estatísticas detalhadas de tempo por usuário
     * @param string $nome_usuario Nome do usuário para filtrar
     * @return object Estatísticas detalhadas
     */
    public function calcularEstatisticasTempoUsuario($nome_usuario) {
        $sql = "WITH sessoes AS (
            SELECT 
                u.id,
                u.nome,
                a.data_hora as inicio,
                LEAD(a.data_hora) OVER (PARTITION BY u.id ORDER BY a.data_hora) as fim
            FROM cuc_usuarios u
            JOIN cuc_atividades a ON u.id = a.usuario_id
            WHERE a.acao IN ('Login', 'Logout')
            AND u.nome ILIKE :nome_usuario
        )
        SELECT 
            nome,
            COUNT(DISTINCT DATE(inicio)) as total_dias,
            ROUND(AVG(EXTRACT(EPOCH FROM (fim - inicio))/60), 2) as media_minutos_dia,
            ROUND(SUM(EXTRACT(EPOCH FROM (fim - inicio))/60), 2) as total_minutos
        FROM sessoes
        WHERE fim IS NOT NULL
        GROUP BY nome";

        $this->db->query($sql);
        $this->db->bind(':nome_usuario', '%' . $nome_usuario . '%');
        return $this->db->resultado();
    }
} 