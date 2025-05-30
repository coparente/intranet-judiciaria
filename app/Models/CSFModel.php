<?php

/**
 * [ CSFModel ] - Modelo responsável por gerenciar os dados da Comissão de Soluções Fundiárias.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */
class CSFModel
{
    private $db;
    
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->db = new Database;
    }
    
    /**
     * [ obterVisitasPaginadas ] - Obtém visitas técnicas com paginação e filtros
     * 
     * @param int $pagina Número da página atual
     * @param int $itensPorPagina Quantidade de itens por página
     * @param array $filtros Filtros de busca
     * @return array Array contendo visitas e informações de paginação
     */
    public function obterVisitasPaginadas($pagina = 1, $itensPorPagina = 10, $filtros = [])
    {
        // Calcular o offset para a consulta SQL
        $offset = ($pagina - 1) * $itensPorPagina;
        
        // Construir a consulta SQL com filtros
        $sql = "SELECT * FROM visita WHERE 1=1";
        $params = [];
        
        // Adicionar filtros à consulta
        if (!empty($filtros['processo'])) {
            $sql .= " AND processo LIKE :processo";
            $params[':processo'] = '%' . $filtros['processo'] . '%';
        }
        
        if (!empty($filtros['comarca'])) {
            $sql .= " AND comarca LIKE :comarca";
            $params[':comarca'] = '%' . $filtros['comarca'] . '%';
        }
        
        if (!empty($filtros['autor'])) {
            $sql .= " AND autor LIKE :autor";
            $params[':autor'] = '%' . $filtros['autor'] . '%';
        }
        
        if (!empty($filtros['reu'])) {
            $sql .= " AND reu LIKE :reu";
            $params[':reu'] = '%' . $filtros['reu'] . '%';
        }
        
        if (!empty($filtros['proad'])) {
            $sql .= " AND proad LIKE :proad";
            $params[':proad'] = '%' . $filtros['proad'] . '%';
        }
        
        // Contar o total de registros com os filtros aplicados
        $sqlCount = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
        $this->db->query($sqlCount);
        
        // Vincular parâmetros para a contagem
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $totalRegistros = $this->db->resultado()->total;
        
        // Calcular o total de páginas
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);
        
        // Adicionar ordenação e limite à consulta principal
        $sql .= " ORDER BY cadastrado_em DESC LIMIT :offset, :limit";
        
        // Executar a consulta principal
        $this->db->query($sql);
        
        // Vincular parâmetros para a consulta principal
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $this->db->bind(':limit', $itensPorPagina, PDO::PARAM_INT);
        
        $visitas = $this->db->resultados();
        
        // Retornar os dados e informações de paginação
        return [
            'visitas' => $visitas,
            'paginacao' => [
                'pagina_atual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalRegistros,
                'itens_por_pagina' => $itensPorPagina
            ]
        ];
    }
    
    /**
     * [ obterVisitas ] - Obtém todas as visitas técnicas
     * 
     * @return array Lista de visitas técnicas
     */
    public function obterVisitas()
    {
        $this->db->query("SELECT * FROM visita ORDER BY cadastrado_em DESC");
        return $this->db->resultados();
    }
    
    /**
     * [ obterVisitaPorId ] - Obtém uma visita técnica pelo ID
     * 
     * @param int $id ID da visita
     * @return object Dados da visita
     */
    public function obterVisitaPorId($id)
    {
        $this->db->query("SELECT * FROM visita WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }
    
    /**
     * [ obterParticipantesPorVisitaId ] - Obtém os participantes de uma visita
     * 
     * @param int $visitaId ID da visita
     * @return array Lista de participantes
     */
    public function obterParticipantesPorVisitaId($visitaId)
    {
        $this->db->query("SELECT * FROM participantes WHERE visita_id = :visita_id ORDER BY nome ASC");
        $this->db->bind(':visita_id', $visitaId);
        return $this->db->resultados();
    }
    
    /**
     * [ cadastrarVisita ] - Cadastra uma nova visita técnica
     * 
     * @param array $dados Dados da visita
     * @return bool Sucesso ou falha
     */
    public function cadastrarVisita($dados)
    {
        $this->db->query("INSERT INTO visita (processo, comarca, autor, reu, proad, nome_ocupacao, 
                          area_ocupada, energia_eletrica, agua_tratada, area_risco, moradia) 
                          VALUES (:processo, :comarca, :autor, :reu, :proad, :nome_ocupacao, 
                          :area_ocupada, :energia_eletrica, :agua_tratada, :area_risco, :moradia)");
        
        // Vincular valores
        $this->db->bind(':processo', $dados['processo']);
        $this->db->bind(':comarca', $dados['comarca']);
        $this->db->bind(':autor', $dados['autor']);
        $this->db->bind(':reu', $dados['reu']);
        $this->db->bind(':proad', $dados['proad']);
        $this->db->bind(':nome_ocupacao', $dados['nome_ocupacao']);
        $this->db->bind(':area_ocupada', $dados['area_ocupada']);
        $this->db->bind(':energia_eletrica', $dados['energia_eletrica']);
        $this->db->bind(':agua_tratada', $dados['agua_tratada']);
        $this->db->bind(':area_risco', $dados['area_risco']);
        $this->db->bind(':moradia', $dados['moradia']);
        
        // Executar
        return $this->db->executa();
    }
    
    /**
     * [ atualizarVisita ] - Atualiza uma visita técnica
     * 
     * @param array $dados Dados da visita
     * @return bool Sucesso ou falha
     */
    public function atualizarVisita($dados)
    {
        $this->db->query("UPDATE visita SET processo = :processo, comarca = :comarca, 
                          autor = :autor, reu = :reu, proad = :proad, nome_ocupacao = :nome_ocupacao, 
                          area_ocupada = :area_ocupada, energia_eletrica = :energia_eletrica, 
                          agua_tratada = :agua_tratada, area_risco = :area_risco, moradia = :moradia 
                          WHERE id = :id");
        
        // Vincular valores
        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':processo', $dados['processo']);
        $this->db->bind(':comarca', $dados['comarca']);
        $this->db->bind(':autor', $dados['autor']);
        $this->db->bind(':reu', $dados['reu']);
        $this->db->bind(':proad', $dados['proad']);
        $this->db->bind(':nome_ocupacao', $dados['nome_ocupacao']);
        $this->db->bind(':area_ocupada', $dados['area_ocupada']);
        $this->db->bind(':energia_eletrica', $dados['energia_eletrica']);
        $this->db->bind(':agua_tratada', $dados['agua_tratada']);
        $this->db->bind(':area_risco', $dados['area_risco']);
        $this->db->bind(':moradia', $dados['moradia']);
        
        // Executar
        return $this->db->executa();
    }
    
    /**
     * [ excluirVisita ] - Exclui uma visita técnica
     * 
     * @param int $id ID da visita
     * @return bool Sucesso ou falha
     */
    public function excluirVisita($id)
    {
        // Primeiro excluir os participantes relacionados
        $this->db->query("DELETE FROM participantes WHERE visita_id = :id");
        $this->db->bind(':id', $id);
        $this->db->executa();
        
        // Depois excluir a visita
        $this->db->query("DELETE FROM visita WHERE id = :id");
        $this->db->bind(':id', $id);
        
        // Executar
        return $this->db->executa();
    }
    
    /**
     * [ cadastrarParticipante ] - Cadastra um novo participante
     * 
     * @param array $dados Dados do participante
     * @return bool Sucesso ou falha
     */
    public function cadastrarParticipante($dados)
    {
        $this->db->query("INSERT INTO participantes (nome, cpf, contato, idade, qtd_pessoas, menores, idosos, 
                          pessoa_deficiencia, gestante, auxilio, frequentam_escola, qtd_trabalham, vulneravel, 
                          lote_vago, fonte_renda, mora_local, descricao, visita_id) 
                          VALUES (:nome, :cpf, :contato, :idade, :qtd_pessoas, :menores, :idosos, 
                          :pessoa_deficiencia, :gestante, :auxilio, :frequentam_escola, :qtd_trabalham, :vulneravel, 
                          :lote_vago, :fonte_renda, :mora_local, :descricao, :visita_id)");
        
        // Vincular valores
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':cpf', $dados['cpf']);
        $this->db->bind(':contato', $dados['contato']);
        $this->db->bind(':idade', $dados['idade']);
        $this->db->bind(':qtd_pessoas', $dados['qtd_pessoas']);
        $this->db->bind(':menores', $dados['menores']);
        $this->db->bind(':idosos', $dados['idosos']);
        $this->db->bind(':pessoa_deficiencia', $dados['pessoa_deficiencia']);
        $this->db->bind(':gestante', $dados['gestante']);
        $this->db->bind(':auxilio', $dados['auxilio']);
        $this->db->bind(':frequentam_escola', $dados['frequentam_escola']);
        $this->db->bind(':qtd_trabalham', $dados['qtd_trabalham']);
        $this->db->bind(':vulneravel', $dados['vulneravel']);
        $this->db->bind(':lote_vago', $dados['lote_vago']);
        $this->db->bind(':fonte_renda', $dados['fonte_renda']);
        $this->db->bind(':mora_local', $dados['mora_local']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':visita_id', $dados['visita_id']);
        
        // Executar
        return $this->db->executa();
    }
    
    /**
     * [ obterParticipantePorId ] - Obtém um participante pelo ID
     * 
     * @param int $id ID do participante
     * @return object Dados do participante
     */
    public function obterParticipantePorId($id)
    {
        $this->db->query("SELECT * FROM participantes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }
    
    /**
     * [ atualizarParticipante ] - Atualiza os dados de um participante
     * 
     * @param array $dados Dados do participante
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarParticipante($dados)
    {
        $this->db->query("UPDATE participantes SET 
            nome = :nome,
            cpf = :cpf,
            contato = :contato,
            idade = :idade,
            qtd_pessoas = :qtd_pessoas,
            menores = :menores,
            idosos = :idosos,
            pessoa_deficiencia = :pessoa_deficiencia,
            gestante = :gestante,
            auxilio = :auxilio,
            frequentam_escola = :frequentam_escola,
            qtd_trabalham = :qtd_trabalham,
            vulneravel = :vulneravel,
            lote_vago = :lote_vago,
            mora_local = :mora_local,
            fonte_renda = :fonte_renda,
            descricao = :descricao,
            visita_id = :visita_id
            WHERE id = :id");
        
        // Vincular valores
        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':cpf', $dados['cpf']);
        $this->db->bind(':contato', $dados['contato']);
        $this->db->bind(':idade', $dados['idade']);
        $this->db->bind(':qtd_pessoas', $dados['qtd_pessoas']);
        $this->db->bind(':menores', $dados['menores']);
        $this->db->bind(':idosos', $dados['idosos']);
        $this->db->bind(':pessoa_deficiencia', $dados['pessoa_deficiencia']);
        $this->db->bind(':gestante', $dados['gestante']);
        $this->db->bind(':auxilio', $dados['auxilio']);
        $this->db->bind(':frequentam_escola', $dados['frequentam_escola']);
        $this->db->bind(':qtd_trabalham', $dados['qtd_trabalham']);
        $this->db->bind(':vulneravel', $dados['vulneravel']);
        $this->db->bind(':lote_vago', $dados['lote_vago']);
        $this->db->bind(':mora_local', $dados['mora_local']);
        $this->db->bind(':fonte_renda', $dados['fonte_renda']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':visita_id', $dados['visita_id']);
        
        // Executar
        return $this->db->executa();
    }
    
    /**
     * [ excluirParticipante ] - Exclui um participante
     * 
     * @param int $id ID do participante
     * @return bool Sucesso ou falha
     */
    public function excluirParticipante($id)
    {
        $this->db->query("DELETE FROM participantes WHERE id = :id");
        $this->db->bind(':id', $id);
        
        // Executar
        return $this->db->executa();
    }
    
    /**
     * [ contarParticipantesPorVisita ] - Conta o número de participantes de uma visita
     * 
     * @param int $visitaId ID da visita
     * @return int Número de participantes
     */
    public function contarParticipantesPorVisita($visitaId)
    {
        $this->db->query("SELECT COUNT(*) as total FROM participantes WHERE visita_id = :visita_id");
        $this->db->bind(':visita_id', $visitaId);
        return $this->db->resultado()->total;
    }
    
    /**
     * [ buscarVisitas ] - Busca visitas por termo
     * 
     * @param string $termo Termo de busca
     * @return array Lista de visitas encontradas
     */
    public function buscarVisitas($termo)
    {
        $this->db->query("SELECT * FROM visita 
                          WHERE processo LIKE :termo 
                          OR comarca LIKE :termo 
                          OR autor LIKE :termo 
                          OR reu LIKE :termo 
                          OR proad LIKE :termo 
                          OR nome_ocupacao LIKE :termo 
                          ORDER BY cadastrado_em DESC");
        
        $this->db->bind(':termo', '%' . $termo . '%');
        return $this->db->resultados();
    }
}