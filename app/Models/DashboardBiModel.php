<?php

/**
 * [ DASHBOARDBIMODEL ] - Model responsável por gerenciar os dashboards BI.
 * 
 * Esta classe lida com a persistência e recuperação de dados relacionados aos dashboards BI,
 * incluindo listagem, cadastro, edição e exclusão.
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class DashboardBiModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ listarDashboards ] - Lista todos os dashboards BI
     * 
     * @param string $filtro Filtro de busca opcional
     * @return array Array de dashboards
     */
    public function listarDashboards($filtro = '')
    {
        $sql = "SELECT d.*, u.nome as usuario_nome 
                FROM dashboards_bi d
                JOIN usuarios u ON d.usuario_id = u.id
                WHERE (d.titulo LIKE :filtro_titulo OR d.descricao LIKE :filtro_desc)
                ORDER BY d.ordem, d.titulo";
        
        $this->db->query($sql);
        $this->db->bind(':filtro_titulo', "%$filtro%");
        $this->db->bind(':filtro_desc', "%$filtro%");
        
        return $this->db->resultados();
    }

    /**
     * [ cadastrarDashboard ] - Cadastra um novo dashboard BI
     * 
     * @param array $dados Dados do dashboard
     * @return bool True se cadastrado com sucesso, false caso contrário
     */
    public function cadastrarDashboard($dados)
    {
        $this->db->query("INSERT INTO dashboards_bi (
                            titulo, 
                            descricao, 
                            url, 
                            categoria, 
                            icone, 
                            ordem, 
                            status, 
                            usuario_id, 
                            criado_em
                        ) VALUES (
                            :titulo, 
                            :descricao, 
                            :url, 
                            :categoria, 
                            :icone, 
                            :ordem, 
                            :status, 
                            :usuario_id, 
                            NOW()
                        )");

        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':url', $dados['url']);
        $this->db->bind(':categoria', $dados['categoria']);
        $this->db->bind(':icone', $dados['icone'] ?? 'fa-chart-bar');
        $this->db->bind(':ordem', $dados['ordem'] ?? 0);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        $this->db->bind(':usuario_id', $_SESSION['usuario_id']);

        return $this->db->executa();
    }

    /**
     * [ buscarDashboardPorId ] - Busca um dashboard pelo ID
     * 
     * @param int $id ID do dashboard
     * @return object Dashboard encontrado ou null
     */
    public function buscarDashboardPorId($id)
    {
        $this->db->query("SELECT * FROM dashboards_bi WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->resultado();
    }

    /**
     * [ atualizarDashboard ] - Atualiza um dashboard existente
     * 
     * @param array $dados Dados do dashboard
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarDashboard($dados)
    {
        $this->db->query("UPDATE dashboards_bi SET 
                            titulo = :titulo, 
                            descricao = :descricao, 
                            url = :url, 
                            categoria = :categoria, 
                            icone = :icone, 
                            ordem = :ordem, 
                            status = :status, 
                            atualizado_em = NOW()
                        WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':url', $dados['url']);
        $this->db->bind(':categoria', $dados['categoria']);
        $this->db->bind(':icone', $dados['icone']);
        $this->db->bind(':ordem', $dados['ordem']);
        $this->db->bind(':status', $dados['status']);

        return $this->db->executa();
    }

    /**
     * [ excluirDashboard ] - Exclui um dashboard
     * 
     * @param int $id ID do dashboard
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirDashboard($id)
    {
        $this->db->query("DELETE FROM dashboards_bi WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * [ listarDashboardsAtivos ] - Lista dashboards ativos para exibição
     * 
     * @param string $categoria Categoria opcional para filtrar
     * @return array Array de dashboards ativos
     */
    public function listarDashboardsAtivos($categoria = '')
    {
        $sql = "SELECT * FROM dashboards_bi WHERE status = 'ativo'";
        
        if (!empty($categoria)) {
            $sql .= " AND categoria = :categoria";
        }
        
        $sql .= " ORDER BY ordem, titulo";
        
        $this->db->query($sql);
        
        if (!empty($categoria)) {
            $this->db->bind(':categoria', $categoria);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ listarCategorias ] - Lista todas as categorias distintas
     * 
     * @return array Array de categorias
     */
    public function listarCategorias()
    {
        $this->db->query("SELECT DISTINCT categoria FROM dashboards_bi WHERE categoria IS NOT NULL ORDER BY categoria");
        return $this->db->resultados();
    }
}
