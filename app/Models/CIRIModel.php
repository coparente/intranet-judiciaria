<?php

/**
 * [ CIRIModel ] - Modelo responsável por gerenciar os dados da Central de Intimação Remota do Interior.
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */
class CIRIModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ listarProcessos ] - Lista os processos de análise da CIRI
     * 
     * @param array $filtros Filtros de busca
     * @param int $limite Limite de registros por página
     * @param int $offset Offset para paginação
     * @return array Processos encontrados
     */
    public function listarProcessos($filtros = [], $limite = null, $offset = null)
    {
        $sql = "SELECT DISTINCT p.*, u.nome as usuario_nome, 
                ti.nome as tipo_intimacao_nome, 
                ta.nome as tipo_ato_nome
                FROM processos_analise_ciri p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN tipo_intimacao_ciri ti ON p.tipo_intimacao_ciri_id = ti.id
                LEFT JOIN tipo_ato_ciri ta ON p.tipo_ato_ciri_id = ta.id
                LEFT JOIN destinatarios_ciri dc ON p.id = dc.processo_id
                WHERE 1=1";

        // Aplicar filtros
        if (!empty($filtros['numero_processo'])) {
            $sql .= " AND p.numero_processo LIKE :numero_processo";
        }
        if (!empty($filtros['comarca'])) {
            $sql .= " AND p.comarca_serventia LIKE :comarca";
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND p.status_processo = :status";
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND p.data_atividade >= :data_inicio";
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND p.data_atividade <= :data_fim";
        }
        // Adicionar filtro por usuário
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND p.usuario_id = :usuario_id";
        }
        // Adicionar filtro por destinatário
        if (!empty($filtros['destinatario_ciri_id'])) {
            $sql .= " AND dc.id = :destinatario_ciri_id";
        }

        $sql .= " ORDER BY p.criado_em DESC";

        // Adicionar limite e offset para paginação
        if ($limite !== null && $offset !== null) {
            $sql .= " LIMIT :limite OFFSET :offset";
        }

        $this->db->query($sql);

        // Bind dos parâmetros
        if (!empty($filtros['numero_processo'])) {
            $this->db->bind(':numero_processo', '%' . $filtros['numero_processo'] . '%');
        }
        if (!empty($filtros['comarca'])) {
            $this->db->bind(':comarca', '%' . $filtros['comarca'] . '%');
        }
        if (!empty($filtros['status'])) {
            $this->db->bind(':status', $filtros['status']);
        }
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        // Bind do parâmetro de usuário
        if (!empty($filtros['usuario_id'])) {
            $this->db->bind(':usuario_id', $filtros['usuario_id']);
        }
        // Bind do parâmetro de destinatário
        if (!empty($filtros['destinatario_ciri_id'])) {
            $this->db->bind(':destinatario_ciri_id', $filtros['destinatario_ciri_id']);
        }

        // Bind dos parâmetros de paginação
        if ($limite !== null && $offset !== null) {
            $this->db->bind(':limite', $limite, PDO::PARAM_INT);
            $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $this->db->resultados();
    }

    /**
     * [ contarProcessos ] - Conta o total de processos com os filtros aplicados
     * 
     * @param array $filtros Filtros de busca
     * @return int Total de registros
     */
    public function contarProcessos($filtros = [])
    {
        $sql = "SELECT COUNT(DISTINCT p.id) as total 
                FROM processos_analise_ciri p
                LEFT JOIN destinatarios_ciri dc ON p.id = dc.processo_id
                WHERE 1=1";

        // Aplicar filtros
        if (!empty($filtros['numero_processo'])) {
            $sql .= " AND p.numero_processo LIKE :numero_processo";
        }
        if (!empty($filtros['comarca'])) {
            $sql .= " AND p.comarca_serventia LIKE :comarca";
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND p.status_processo = :status";
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND p.data_atividade >= :data_inicio";
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND p.data_atividade <= :data_fim";
        }
        // Adicionar filtro por usuário
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND p.usuario_id = :usuario_id";
        }
        // Adicionar filtro por destinatário
        if (!empty($filtros['destinatario_ciri_id'])) {
            $sql .= " AND dc.id = :destinatario_ciri_id";
        }

        $this->db->query($sql);

        // Bind dos parâmetros
        if (!empty($filtros['numero_processo'])) {
            $this->db->bind(':numero_processo', '%' . $filtros['numero_processo'] . '%');
        }
        if (!empty($filtros['comarca'])) {
            $this->db->bind(':comarca', '%' . $filtros['comarca'] . '%');
        }
        if (!empty($filtros['status'])) {
            $this->db->bind(':status', $filtros['status']);
        }
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind(':data_inicio', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $this->db->bind(':data_fim', $filtros['data_fim']);
        }
        // Bind do parâmetro de usuário
        if (!empty($filtros['usuario_id'])) {
            $this->db->bind(':usuario_id', $filtros['usuario_id']);
        }
        // Bind do parâmetro de destinatário
        if (!empty($filtros['destinatario_ciri_id'])) {
            $this->db->bind(':destinatario_ciri_id', $filtros['destinatario_ciri_id']);
        }

        $resultado = $this->db->resultado();
        return $resultado->total;
    }

    /**
     * [ cadastrarProcesso ] - Cadastra um novo processo
     * 
     * @param array $dados Dados do processo
     * @return int|bool ID do processo cadastrado ou false
     */
    public function cadastrarProcesso($dados)
    {
        $this->db->query("INSERT INTO processos_analise_ciri (
            usuario_id, 
            gratuidade_justica, 
            numero_processo, 
            comarca_serventia, 
            observacao_atividade, 
            tipo_intimacao_ciri_id, 
            tipo_ato_ciri_id, 
            status_processo, 
            criado_em, 
            atualizado_em
        ) VALUES (
            :usuario_id, 
            :gratuidade_justica, 
            :numero_processo, 
            :comarca_serventia, 
            :observacao_atividade, 
            :tipo_intimacao_ciri_id, 
            :tipo_ato_ciri_id, 
            :status_processo, 
            CURRENT_TIMESTAMP, 
            CURRENT_TIMESTAMP
        )");

        // Bind dos valores
        $this->db->bind(':usuario_id', $dados['usuario_id']);
        $this->db->bind(':gratuidade_justica', $dados['gratuidade_justica']);
        $this->db->bind(':numero_processo', $dados['numero_processo']);
        $this->db->bind(':comarca_serventia', $dados['comarca_serventia']);
        $this->db->bind(':observacao_atividade', $dados['observacao_atividade']);
        $this->db->bind(':tipo_intimacao_ciri_id', $dados['tipo_intimacao_ciri_id'] ?: null);
        $this->db->bind(':tipo_ato_ciri_id', $dados['tipo_ato_ciri_id'] ?: null);
        $this->db->bind(':status_processo', $dados['status_processo']);

        // Executar
        if ($this->db->executa()) {
            return $this->db->ultimoIdInserido();
        } else {
            return false;
        }
    }

    /**
     * [ obterProcessoPorId ] - Obtém um processo pelo ID
     * 
     * @param int $id ID do processo
     * @return object|bool Processo encontrado ou false
     */
    public function obterProcessoPorId($id)
    {
        $this->db->query("SELECT p.*, u.nome as usuario_nome, 
                          ti.nome as tipo_intimacao_nome, 
                          ta.nome as tipo_ato_nome 
                          FROM processos_analise_ciri p
                          LEFT JOIN usuarios u ON p.usuario_id = u.id
                          LEFT JOIN tipo_intimacao_ciri ti ON p.tipo_intimacao_ciri_id = ti.id
                          LEFT JOIN tipo_ato_ciri ta ON p.tipo_ato_ciri_id = ta.id
                          WHERE p.id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /**
     * [ atualizarProcesso ] - Atualiza um processo existente
     * 
     * @param array $dados Dados do processo
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarProcesso($dados)
    {
        $this->db->query("UPDATE processos_analise_ciri SET 
            gratuidade_justica = :gratuidade_justica, 
            numero_processo = :numero_processo, 
            comarca_serventia = :comarca_serventia, 
            data_atividade = :data_atividade, 
            observacao_atividade = :observacao_atividade, 
            destinatarios_ciri_id = :destinatarios_ciri_id, 
            tipo_intimacao_ciri_id = :tipo_intimacao_ciri_id, 
            tipo_ato_ciri_id = :tipo_ato_ciri_id, 
            status_processo = :status_processo,
            atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :id");

        // Bind dos valores
        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':gratuidade_justica', $dados['gratuidade_justica']);
        $this->db->bind(':numero_processo', $dados['numero_processo']);
        $this->db->bind(':comarca_serventia', $dados['comarca_serventia']);
        $this->db->bind(':data_atividade', $dados['data_atividade']);
        $this->db->bind(':observacao_atividade', $dados['observacao_atividade']);
        $this->db->bind(':destinatarios_ciri_id', $dados['destinatarios_ciri_id']);
        $this->db->bind(':tipo_intimacao_ciri_id', $dados['tipo_intimacao_ciri_id']);
        $this->db->bind(':tipo_ato_ciri_id', $dados['tipo_ato_ciri_id']);
        $this->db->bind(':status_processo', $dados['status_processo']);

        // Executa a query
        return $this->db->executa();
    }

    /**
     * [ excluirProcesso ] - Exclui um processo e seus relacionamentos
     * 
     * @param int $id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirProcesso($id)
    {
        // Inicia transação
        $this->db->iniciarTransacao();

        try {
            // Exclui movimentações
            $this->db->query("DELETE FROM movimentacao_ciri WHERE processo_id = :id");
            $this->db->bind(':id', $id);
            $this->db->executa();

            // Exclui destinatários
            $this->db->query("DELETE FROM destinatarios_ciri WHERE processo_id = :id");
            $this->db->bind(':id', $id);
            $this->db->executa();

            // Exclui o processo
            $this->db->query("DELETE FROM processos_analise_ciri WHERE id = :id");
            $this->db->bind(':id', $id);
            $resultado = $this->db->executa();

            // Confirma a transação
            $this->db->confirmarTransacao();
            return $resultado;
        } catch (Exception $e) {
            // Reverte a transação em caso de erro
            $this->db->reverterTransacao();
            return false;
        }
    }

    /**
     * [ listarMovimentacoes ] - Lista as movimentações de um processo
     * 
     * @param int $processoId ID do processo
     * @return array Movimentações encontradas
     */
    public function listarMovimentacoes($processoId)
    {
        $this->db->query("SELECT m.*, u.nome as nome_usuario 
                          FROM movimentacao_ciri m
                          LEFT JOIN usuarios u ON m.usuario_id = u.id
                          WHERE m.processo_id = :processo_id 
                          ORDER BY m.data_movimentacao DESC");
        $this->db->bind(':processo_id', $processoId);

        return $this->db->resultados();
    }

    /**
     * [ cadastrarMovimentacao ] - Cadastra uma nova movimentação
     * 
     * @param array $dados Dados da movimentação
     * @return bool True se cadastrado com sucesso, false caso contrário
     */
    public function cadastrarMovimentacao($dados)
    {
        $this->db->query("INSERT INTO movimentacao_ciri (
            processo_id, usuario_id, descricao
        ) VALUES (
            :processo_id, :usuario_id, :descricao
        )");

        // Bind dos valores
        $this->db->bind(':processo_id', $dados['processo_id']);
        $this->db->bind(':usuario_id', $dados['usuario_id']);
        $this->db->bind(':descricao', $dados['descricao']);

        // Executa a query
        return $this->db->executa();
    }

    /**
     * [ listarDestinatarios ] - Lista os destinatários de um processo
     * 
     * @param int $processoId ID do processo
     * @return array Destinatários encontrados
     */
    public function listarDestinatarios($processoId)
    {
        $this->db->query("SELECT * FROM destinatarios_ciri 
                          WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processoId);

        return $this->db->resultados();
    }

    /**
     * [ obterDestinatarioPorId ] - Obtém um destinatário pelo ID
     * 
     * @param int $id ID do destinatário
     * @return object|bool Destinatário encontrado ou false
     */
    public function obterDestinatarioPorId($id)
    {
        $this->db->query("SELECT * FROM destinatarios_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /**
     * [ cadastrarDestinatario ] - Cadastra um novo destinatário
     * 
     * @param array $dados Dados do destinatário
     * @return bool True se cadastrado com sucesso, false caso contrário
     */
    public function cadastrarDestinatario($dados)
    {
        $this->db->query("INSERT INTO destinatarios_ciri (
            processo_id, nome, telefone, email
        ) VALUES (
            :processo_id, :nome, :telefone, :email
        )");

        // Bind dos valores
        $this->db->bind(':processo_id', $dados['processo_id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':telefone', $dados['telefone']);
        $this->db->bind(':email', $dados['email']);

        // Executa a query
        return $this->db->executa();
    }

    /**
     * [ atualizarDestinatario ] - Atualiza um destinatário
     * 
     * @param array $dados Dados do destinatário
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarDestinatario($dados)
    {
        $this->db->query("UPDATE destinatarios_ciri 
                         SET nome = :nome, 
                             email = :email,
                             telefone = :telefone
                         WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':telefone', $dados['telefone']);

        return $this->db->executa();
    }

    /**
     * [ excluirDestinatario ] - Exclui um destinatário
     * 
     * @param int $id ID do destinatário
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirDestinatario($id)
    {
        $this->db->query("DELETE FROM destinatarios_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * [ listarTiposAto ] - Lista todos os tipos de ato
     * 
     * @return array Tipos de ato encontrados
     */
    public function listarTiposAto()
    {
        $this->db->query("SELECT * FROM tipo_ato_ciri ORDER BY nome");
        return $this->db->resultados();
    }

    /**
     * [ obterTipoAtoPorId ] - Obtém um tipo de ato pelo ID
     * 
     * @param int $id ID do tipo de ato
     * @return object|bool Tipo de ato ou false
     */
    public function obterTipoAtoPorId($id)
    {
        $this->db->query("SELECT * FROM tipo_ato_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /**
     * [ atualizarTipoAto ] - Atualiza um tipo de ato
     * 
     * @param array $dados Dados do tipo de ato
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarTipoAto($dados)
    {
        $this->db->query("UPDATE tipo_ato_ciri 
                         SET nome = :nome, 
                             descricao = :descricao
                         WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao']);

        return $this->db->executa();
    }

    /**
     * [ excluirTipoAto ] - Exclui um tipo de ato
     * 
     * @param int $id ID do tipo de ato
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirTipoAto($id)
    {
        $this->db->query("DELETE FROM tipo_ato_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * [ tipoAtoEmUso ] - Verifica se um tipo de ato está sendo usado em algum processo
     * 
     * @param int $id ID do tipo de ato
     * @return bool True se estiver em uso, false caso contrário
     */
    public function tipoAtoEmUso($id)
    {
        $this->db->query("SELECT COUNT(*) as total FROM processos_analise_ciri WHERE tipo_ato_ciri_id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado()->total > 0;
    }

    /**
     * [ adicionarTipoAto ] - Adiciona um novo tipo de ato
     * 
     * @param array $dados Dados do tipo de ato
     * @return bool True se adicionado com sucesso, false caso contrário
     */
    public function adicionarTipoAto($dados)
    {
        $this->db->query("INSERT INTO tipo_ato_ciri (nome, descricao) VALUES (:nome, :descricao)");

        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao']);

        return $this->db->executa();
    }

    /**
     * [ listarTiposIntimacao ] - Lista todos os tipos de intimação
     * 
     * @return array Tipos de intimação encontrados
     */
    public function listarTiposIntimacao()
    {
        $this->db->query("SELECT * FROM tipo_intimacao_ciri ORDER BY nome");
        return $this->db->resultados();
    }

    /**
     * [ obterTipoIntimacaoPorId ] - Obtém um tipo de intimação pelo ID
     * 
     * @param int $id ID do tipo de intimação
     * @return object|bool Tipo de intimação ou false
     */
    public function obterTipoIntimacaoPorId($id)
    {
        $this->db->query("SELECT * FROM tipo_intimacao_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /**
     * [ atualizarTipoIntimacao ] - Atualiza um tipo de intimação
     * 
     * @param array $dados Dados do tipo de intimação
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarTipoIntimacao($dados)
    {
        $this->db->query("UPDATE tipo_intimacao_ciri 
                         SET nome = :nome, 
                             descricao = :descricao
                         WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao']);

        return $this->db->executa();
    }

    /**
     * [ excluirTipoIntimacao ] - Exclui um tipo de intimação
     * 
     * @param int $id ID do tipo de intimação
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirTipoIntimacao($id)
    {
        $this->db->query("DELETE FROM tipo_intimacao_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * [ tipoIntimacaoEmUso ] - Verifica se um tipo de intimação está sendo usado em algum processo
     * 
     * @param int $id ID do tipo de intimação
     * @return bool True se estiver em uso, false caso contrário
     */
    public function tipoIntimacaoEmUso($id)
    {
        $this->db->query("SELECT COUNT(*) as total FROM processos_analise_ciri WHERE tipo_intimacao_ciri_id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado()->total > 0;
    }

    /**
     * [ adicionarTipoIntimacao ] - Adiciona um novo tipo de intimação
     * 
     * @param array $dados Dados do tipo de intimação
     * @return bool True se adicionado com sucesso, false caso contrário
     */
    public function adicionarTipoIntimacao($dados)
    {
        $this->db->query("INSERT INTO tipo_intimacao_ciri (nome, descricao) VALUES (:nome, :descricao)");

        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao']);

        return $this->db->executa();
    }

    /**
     * [ listarMovimentacoesPorProcesso ] - Alias para listarMovimentacoes
     * 
     * @param int $processoId ID do processo
     * @return array Movimentações encontradas
     */
    public function listarMovimentacoesPorProcesso($processoId)
    {
        return $this->listarMovimentacoes($processoId);
    }

    /**
     * [ listarDestinatariosPorProcesso ] - Alias para listarDestinatarios
     * 
     * @param int $processoId ID do processo
     * @return array Destinatários encontrados
     */
    public function listarDestinatariosPorProcesso($processoId)
    {
        return $this->listarDestinatarios($processoId);
    }

    /**
     * [ sortearProcessoParaUsuario ] - Sorteia um processo pendente para um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return int|bool ID do processo sorteado ou false
     */
    public function sortearProcessoParaUsuario($usuarioId)
    {
        // Buscar um processo pendente que não esteja atribuído a nenhum usuário
        $this->db->query("SELECT id FROM processos_analise_ciri 
                         WHERE status_processo = 'pendente' 
                         AND usuario_id IS NULL
                         ORDER BY RAND() 
                         LIMIT 1");

        $processo = $this->db->resultado();

        // Se não encontrou nenhum processo disponível
        if (!$processo) {
            return false;
        }

        // Atribuir o processo ao usuário
        $this->db->query("UPDATE processos_analise_ciri 
                         SET usuario_id = :usuario_id, 
                             status_processo = 'em_andamento',
                             atualizado_em = CURRENT_TIMESTAMP
                         WHERE id = :processo_id");

        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':processo_id', $processo->id);

        if ($this->db->executa()) {
            return $processo->id;
        } else {
            return false;
        }
    }

    /**
     * [ atualizarStatusProcesso ] - Atualiza o status de um processo
     * 
     * @param int $id ID do processo
     * @param string $status Novo status do processo
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarStatusProcesso($id, $status)
    {
        $this->db->query("UPDATE processos_analise_ciri 
                         SET status_processo = :status,
                             atualizado_em = CURRENT_TIMESTAMP
                         WHERE id = :id");

        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * [ concluirERemoverAtribuicaoProcesso ] - Conclui um processo e remove a atribuição do usuário
     * 
     * @param int $id ID do processo
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function concluirERemoverAtribuicaoProcesso($id)
    {
        $this->db->query("UPDATE processos_analise_ciri 
                         SET status_processo = 'concluido',
                             usuario_id = NULL,
                             atualizado_em = CURRENT_TIMESTAMP
                         WHERE id = :id");

        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * [ listarProcessosPorUsuario ] - Lista os processos atribuídos a um usuário específico
     * 
     * @param int $usuarioId ID do usuário
     * @param array $filtros Filtros de busca
     * @param int $pagina Número da página atual
     * @param int $itensPorPagina Quantidade de itens por página
     * @return array Processos do usuário
     */
    public function listarProcessosPorUsuario($usuarioId, $filtros = [], $pagina = 1, $itensPorPagina = 10)
    {
        $condicoes = ["p.usuario_id = :usuario_id"];
        $parametros = [':usuario_id' => $usuarioId];

        // Aplicar filtros
        if (!empty($filtros['numero_processo'])) {
            $condicoes[] = "p.numero_processo LIKE :numero_processo";
            $parametros[':numero_processo'] = '%' . $filtros['numero_processo'] . '%';
        }

        if (!empty($filtros['comarca'])) {
            $condicoes[] = "p.comarca_serventia LIKE :comarca";
            $parametros[':comarca'] = '%' . $filtros['comarca'] . '%';
        }

        if (!empty($filtros['status'])) {
            $condicoes[] = "p.status_processo = :status";
            $parametros[':status'] = $filtros['status'];
        } else {
            // Se não houver filtro de status, excluir os processos concluídos
            $condicoes[] = "p.status_processo != 'concluido'";
        }

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = "p.data_atividade >= :data_inicio";
            $parametros[':data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $condicoes[] = "p.data_atividade <= :data_fim";
            $parametros[':data_fim'] = $filtros['data_fim'];
        }

        $where = implode(' AND ', $condicoes);

        // Calcular offset para paginação
        $offset = ($pagina - 1) * $itensPorPagina;

        // Consulta para contar total de registros
        $this->db->query("SELECT COUNT(*) as total FROM processos_analise_ciri p WHERE $where");

        foreach ($parametros as $param => $valor) {
            $this->db->bind($param, $valor);
        }

        $totalRegistros = $this->db->resultado()->total;

        // Consulta para buscar os processos com paginação
        $this->db->query("SELECT p.*, 
                          ta.nome as tipo_ato_nome, 
                          ti.nome as tipo_intimacao_nome
                          FROM processos_analise_ciri p
                          LEFT JOIN tipos_ato_ciri ta ON p.tipo_ato_ciri_id = ta.id
                          LEFT JOIN tipos_intimacao_ciri ti ON p.tipo_intimacao_ciri_id = ti.id
                          WHERE $where
                          ORDER BY p.criado_em DESC
                          LIMIT :limit OFFSET :offset");

        foreach ($parametros as $param => $valor) {
            $this->db->bind($param, $valor);
        }

        $this->db->bind(':limit', $itensPorPagina);
        $this->db->bind(':offset', $offset);

        $processos = $this->db->resultados();

        return [
            'processos' => $processos,
            'total_registros' => $totalRegistros,
            'total_paginas' => ceil($totalRegistros / $itensPorPagina),
            'pagina_atual' => $pagina
        ];
    }

    /**
     * [ listarUsuariosParaDelegacao ] - Lista usuários disponíveis para delegação de processos
     * 
     * @return array Lista de usuários
     */
    public function listarUsuariosParaDelegacao()
    {
        // IDs dos módulos necessários para trabalhar com processos CIRI
        $modulosNecessarios = [10, 11, 12]; // Substitua pelos IDs corretos dos módulos CIRI

        // Converter array para string para usar na consulta
        $modulosString = implode(',', $modulosNecessarios);

        // Consulta para buscar usuários que têm pelo menos uma das permissões necessárias
        $this->db->query("SELECT DISTINCT u.id, u.nome 
                         FROM usuarios u
                         INNER JOIN permissoes_usuario pu ON u.id = pu.usuario_id
                         WHERE u.status = 'ativo'
                         AND (u.perfil IN ('analista', 'usuario'))
                         AND pu.modulo_id IN ($modulosString)
                         ORDER BY u.nome ASC");

        return $this->db->resultados();
    }

    /**
     * [ delegarProcesso ] - Delega um processo para um usuário específico
     * 
     * @param int $processoId ID do processo
     * @param int $usuarioId ID do usuário
     * @return bool True se delegado com sucesso, false caso contrário
     */
    public function delegarProcesso($processoId, $usuarioId)
    {
        $this->db->query("UPDATE processos_analise_ciri 
                         SET usuario_id = :usuario_id, 
                             status_processo = 'em_andamento',
                             atualizado_em = CURRENT_TIMESTAMP
                         WHERE id = :processo_id");

        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':processo_id', $processoId);

        return $this->db->executa();
    }

    /**
     * [ obterMovimentacaoPorId ] - Obtém uma movimentação pelo ID
     * 
     * @param int $id ID da movimentação
     * @return object|bool Movimentação ou false
     */
    public function obterMovimentacaoPorId($id)
    {
        $this->db->query("SELECT * FROM movimentacao_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /**
     * [ atualizarMovimentacao ] - Atualiza uma movimentação
     * 
     * @param array $dados Dados da movimentação
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarMovimentacao($dados)
    {
        $this->db->query("UPDATE movimentacao_ciri 
                         SET descricao = :descricao,
                             usuario_id = :usuario_id,
                             data_movimentacao = NOW()
                         WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':usuario_id', $dados['usuario_id']);

        return $this->db->executa();
    }

    /**
     * [ excluirMovimentacao ] - Exclui uma movimentação
     * 
     * @param int $id ID da movimentação
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirMovimentacao($id)
    {
        $this->db->query("DELETE FROM movimentacao_ciri WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * [ verificarProcessoExistente ] - Verifica se já existe um processo com o número informado
     * 
     * @param string $numeroProcesso Número do processo a ser verificado
     * @return bool Retorna true se o processo já existe, false caso contrário
     */
    public function verificarProcessoExistente($numeroProcesso)
    {
        $this->db->query("SELECT id FROM processos_analise_ciri WHERE numero_processo = :numero_processo");
        $this->db->bind(':numero_processo', $numeroProcesso);

        $resultado = $this->db->resultado();

        return !empty($resultado);
    }

    /**
     * Verifica se existem processos duplicados em uma lista
     * 
     * @param array $numeros_processo Array com números de processo
     * @return array Array com os números de processo que já existem
     */
    public function verificarProcessosDuplicados($numeros_processo)
    {
        $duplicados = [];

        foreach ($numeros_processo as $numero) {

            $this->db->query("SELECT numero_processo FROM processos_analise_ciri WHERE numero_processo = :numero_processo");
            $this->db->bind(':numero_processo', $numero);

            $resultado = $this->db->resultado();

            if ($resultado ) {
                $duplicados[] = $numero;
            }
        }

        return $duplicados;
    }

    /**
     * Lista todos os processos duplicados no sistema
     */
    public function listarProcessosDuplicados()
    {
        $sql = "SELECT numero_processo, COUNT(*) as quantidade 
                FROM processos_analise_ciri 
                GROUP BY numero_processo 
                HAVING COUNT(*) > 1 
                ORDER BY COUNT(*) DESC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * Lista os destinatários de um processo específico
     */
    public function listarDestinatariosPorProcessoId($processo_id = null)
    {
        if ($processo_id) {
            $sql = "SELECT DISTINCT d.* FROM destinatarios_ciri d 
                    WHERE d.processo_id = :processo_id";
            $this->db->query($sql);
            $this->db->bind(':processo_id', $processo_id);
        } else {
            $sql = "SELECT DISTINCT d.* FROM destinatarios_ciri d 
                    INNER JOIN processos_analise_ciri p ON d.processo_id = p.id";
            $this->db->query($sql);
        }
        return $this->db->resultados();
    }

    /**
     * Lista os destinatários dos processos de um usuário específico
     */
    public function listarDestinatariosPorUsuario($usuario_id)
    {
        $sql = "SELECT DISTINCT d.* FROM destinatarios_ciri d 
                INNER JOIN processos_analise_ciri p ON d.processo_id = p.id 
                WHERE p.usuario_id = :usuario_id 
                ORDER BY d.nome";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        return $this->db->resultados();
    }

}
