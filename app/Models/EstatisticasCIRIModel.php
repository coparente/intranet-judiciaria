<?php

/**
 * [ EstatisticasCIRIModel ] - Modelo responsável por gerenciar os dados estatísticos da CIRI.
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class EstatisticasCIRIModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ contarProcessosPorTipoAto ] - Conta a quantidade de processos por tipo de ato
     * 
     * @param array $filtros Filtros de data
     * @return array Estatísticas por tipo de ato
     */
    public function contarProcessosPorTipoAto($filtros = [])
    {
        $sql = "SELECT ta.id, ta.nome as nome_tipo_ato, COUNT(p.id) as total
                FROM processos_analise_ciri p
                LEFT JOIN tipo_ato_ciri ta ON p.tipo_ato_ciri_id = ta.id
                WHERE 1=1";
        
        // Aplicar filtros de data
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(p.criado_em) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(p.criado_em) <= :data_fim";
        }
        
        $sql .= " GROUP BY ta.id, ta.nome
                  ORDER BY total DESC";
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        
        return $this->db->resultados();
    }
    
    /**
     * [ contarProcessosPorStatus ] - Conta a quantidade de processos por status
     * 
     * @param array $filtros Filtros de data
     * @return array Estatísticas por status
     */
    public function contarProcessosPorStatus($filtros = [])
    {
        $sql = "SELECT p.status_processo, COUNT(p.id) as total
                FROM processos_analise_ciri p
                WHERE 1=1";
        
        // Aplicar filtros de data
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(p.criado_em) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(p.criado_em) <= :data_fim";
        }
        
        $sql .= " GROUP BY p.status_processo
                  ORDER BY total DESC";
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        
        return $this->db->resultados();
    }
    
    /**
     * [ contarProcessosPorUsuario ] - Conta a quantidade de processos por usuário
     * 
     * @param array $filtros Filtros de data
     * @return array Estatísticas por usuário
     */
    public function contarProcessosPorUsuario($filtros = [])
    {
        $sql = "SELECT u.id, u.nome as nome_usuario, COUNT(p.id) as total
                FROM processos_analise_ciri p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE 1=1";
        
        // Aplicar filtros de data
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(p.criado_em) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(p.criado_em) <= :data_fim";
        }
        
        $sql .= " GROUP BY u.id, u.nome
                  ORDER BY total DESC";
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        
        return $this->db->resultados();
    }
    
    /**
     * [ contarTotalProcessos ] - Conta o total de processos no período
     * 
     * @param array $filtros Filtros de data
     * @return int Total de processos
     */
    public function contarTotalProcessos($filtros = [])
    {
        $sql = "SELECT COUNT(id) as total FROM processos_analise_ciri WHERE 1=1";
        
        // Aplicar filtros de data
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(criado_em) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(criado_em) <= :data_fim";
        }
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        
        $resultado = $this->db->resultado();
        return $resultado->total;
    }

    /**
     * [ contarMovimentacoesPorUsuario ] - Conta a quantidade de movimentações por usuário
     * 
     * @param array $filtros Filtros de data
     * @return array Estatísticas de movimentações por usuário
     */
    public function contarMovimentacoesPorUsuario($filtros = [])
    {
        $sql = "SELECT u.id, u.nome as nome_usuario, COUNT(m.id) as total
                FROM movimentacao_ciri m
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE 1=1";
        
        // Aplicar filtros de data
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(m.data_movimentacao) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(m.data_movimentacao) <= :data_fim";
        }
        
        $sql .= " GROUP BY u.id, u.nome
                  ORDER BY total DESC";
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ contarTotalMovimentacoes ] - Conta o total de movimentações no período
     * 
     * @param array $filtros Filtros de data
     * @return int Total de movimentações
     */
    public function contarTotalMovimentacoes($filtros = [])
    {
        $sql = "SELECT COUNT(id) as total FROM movimentacao_ciri WHERE 1=1";
        
        // Aplicar filtros de data
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(data_movimentacao) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(data_movimentacao) <= :data_fim";
        }
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        
        $resultado = $this->db->resultado();
        return $resultado->total;
    }
} 