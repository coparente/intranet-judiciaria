<?php

/**
 * [ ProcessoCustasModel ] - Model responsável por gerenciar os processos de custas
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0   
 */
class ProcessoCustasModel {
    private $db;
    private $atividadeModel;

    public function __construct() {
        $this->db = new Database();
        $this->atividadeModel = new AtividadeModel();
    }

    /**
     * [ cadastrarProcesso ] - Cadastra um novo processo de custas
     * 
     * @param array $dados - Dados do processo de custas
     * @return int|bool - ID do processo cadastrado ou false em caso de erro
     */
    public function cadastrarProcesso($dados) {
        $this->db->query("INSERT INTO cuc_processos (
            numero_processo,
            comarca_serventia,
            status,
            data_cadastro,
            usuario_cadastro
        ) VALUES (
            :numero_processo,
            :comarca_serventia,
            'analise',
            NOW(),
            :usuario_cadastro
        )");

        $this->db->bind(':numero_processo', $dados['numero_processo']);
        $this->db->bind(':comarca_serventia', $dados['comarca']);
        $this->db->bind(':usuario_cadastro', $_SESSION['usuario_id']);

        if ($this->db->executa()) {
            $processo_id = $this->db->ultimoIdInserido();
            
            // Se for um processo duplicado, registra na tabela de atividades
            if (isset($dados['confirmar_duplicado']) && $dados['confirmar_duplicado']) {
                $this->atividadeModel->registrarAtividade(
                    $_SESSION['usuario_id'],
                    'Cadastro de Processo Duplicado',
                    "Cadastrou processo {$dados['numero_processo']} mesmo já existindo no sistema"
                );
            }
            
            return $processo_id;
        }
        
        return false;
    }

    /**
     * [ registrarMovimentacao ] - Registra movimentação e cria notificação
     * 
     * @param int $processo_id - ID do processo
     * @param string $tipo - Tipo de movimentação
     * @param string $descricao - Descrição da movimentação
     * @param string $prazo - Prazo da movimentação
     * @return bool - True se a movimentação foi registrada com sucesso, false caso contrário
     */
    public function registrarMovimentacao($processo_id, $tipo, $descricao, $prazo = null) {
        try {

            // Registra a movimentação
            $this->db->query("INSERT INTO cuc_movimentacoes (
                processo_id, tipo, descricao, prazo, usuario_id, data_movimentacao
            ) VALUES (
                :processo_id, :tipo, :descricao, :prazo, :usuario_id, NOW()
            )");

            $this->db->bind(':processo_id', $processo_id);
            $this->db->bind(':tipo', $tipo);
            $this->db->bind(':descricao', $descricao);
            $this->db->bind(':prazo', $prazo);
            $this->db->bind(':usuario_id', $_SESSION['usuario_id']);
            
            $movimentacao_ok = $this->db->executa();

            // Busca o responsável atual do processo
            $this->db->query("SELECT responsavel_id FROM cuc_processos WHERE id = :id");
            $this->db->bind(':id', $processo_id);
            $processo = $this->db->resultado();

            if ($movimentacao_ok && $processo && $processo->responsavel_id) {
                // Criar notificação para o responsável
                $notificacaoModel = new NotificacaoModel();
                $notificacao_ok = $notificacaoModel->criarNotificacao([
                    'usuario_id' => $processo->responsavel_id,
                    'processo_id' => $processo_id,
                    'tipo' => 'movimentacao',
                    'mensagem' => "Nova movimentação: {$tipo} - {$descricao}",
                    'data_prazo' => $prazo ?? date('Y-m-d', strtotime('+7 days'))
                ]);

                if ($movimentacao_ok && $notificacao_ok) {
                    
                    return true;
                }
            }

            
            return false;
        } catch (Exception $e) {
          
            return false;
        }
    }

    /**
     * [ atualizarStatus ] - Atualiza status do processo e notifica o responsável
     * 
     * @param int $processo_id - ID do processo
     * @param string $status - Status do processo
     * @return bool - True se o status foi atualizado com sucesso, false caso contrário
     */
    public function atualizarStatus($processo_id, $status) {
        try {
            // Atualiza o status
            $this->db->query("UPDATE cuc_processos SET status = :status WHERE id = :id");
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $processo_id);
            
            return $this->db->executa();

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [ obterProdutividadeUsuario ] - Obtém estatísticas de produtividade por usuário
     * 
     * @param int $usuario_id - ID do usuário
     * @param string $data_inicio - Data de início
     * @param string $data_fim - Data de fim
     * @return array - Array com estatísticas de produtividade
     */
    public function obterProdutividadeUsuario($usuario_id, $data_inicio, $data_fim) {
        $this->db->query("SELECT 
            COUNT(*) as total_processos,
            COUNT(CASE WHEN status = 'concluido' THEN 1 END) as processos_concluidos,
            COUNT(CASE WHEN status = 'pendente' THEN 1 END) as processos_pendentes,
            AVG(EXTRACT(EPOCH FROM (data_conclusao - data_cadastro))/86400) as media_dias_conclusao
        FROM cuc_processos
        WHERE usuario_cadastro = :usuario_id
        AND data_cadastro BETWEEN :data_inicio AND :data_fim");

        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':data_inicio', $data_inicio);
        $this->db->bind(':data_fim', $data_fim);

        return $this->db->resultado();
    }

    /**
     * Obtém estatísticas detalhadas de produtividade por usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param string $data_inicio Data inicial
     * @param string $data_fim Data final
     * @return object Estatísticas detalhadas
     */
    public function obterProdutividadeDetalhada($usuario_id, $data_inicio = null, $data_fim = null) {
        $sql = "WITH dados_produtividade AS (
            SELECT 
                p.usuario_cadastro,
                p.status,
                p.data_cadastro::date as data,
                COUNT(DISTINCT p.id) as total_processos,
                COUNT(CASE WHEN p.status = 'concluido' THEN 1 END) as concluidos,
                COUNT(CASE WHEN p.status = 'analise' THEN 1 END) as em_analise,
                COUNT(CASE WHEN p.status = 'intimacao' THEN 1 END) as em_intimacao,
                COUNT(CASE WHEN p.status = 'diligencia' THEN 1 END) as em_diligencia,
                COALESCE(SUM(CASE WHEN p.data_conclusao IS NOT NULL 
                    THEN EXTRACT(EPOCH FROM (p.data_conclusao - p.data_cadastro))/86400 
                    END), 0)::numeric(10,2) as media_dias_conclusao,
                COUNT(DISTINCT m.id) as total_movimentacoes,
                COUNT(DISTINCT n.id) as total_notificacoes
            FROM cuc_processos p
            LEFT JOIN cuc_movimentacoes m ON p.id = m.processo_id
            LEFT JOIN cuc_notificacoes n ON p.id = n.processo_id
            WHERE p.usuario_cadastro = :usuario_id
            AND p.data_cadastro::date >= COALESCE(:data_inicio::date, p.data_cadastro::date)
            AND p.data_cadastro::date <= COALESCE(:data_fim::date, p.data_cadastro::date)
            GROUP BY p.usuario_cadastro, p.status, p.data_cadastro::date
        )
        SELECT 
            u.nome as nome_usuario,
            u.perfil as perfil_usuario,
            dp.*
        FROM dados_produtividade dp
        JOIN cuc_usuarios u ON dp.usuario_cadastro = u.id
        ORDER BY dp.data DESC";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':data_inicio', $data_inicio);
        $this->db->bind(':data_fim', $data_fim);

        return $this->db->resultados();
    }

    /**
     * Lista todos os processos com filtros opcionais e paginação
     * @param int $pagina Página atual
     * @param int $processosPorPagina Quantidade de processos por página
     * @param array $filtros Array com filtros opcionais (numero_processo, comarca, status)
     * @return array Array contendo processos e informações de paginação
     */
    public function listarProcessos($pagina = 1, $processosPorPagina = 10, $filtros = []) {
        $offset = ($pagina - 1) * $processosPorPagina;
        
        // Query base com subquery para guias
        $sql = "SELECT p.*, 
                u.nome as responsavel_nome,
                (
                    SELECT json_agg(
                        json_build_object(
                            'numero_guia', g.numero_guia,
                            'valor', g.valor,
                            'status', g.status,
                            'observacao', g.observacao
                        )
                    )
                    FROM cuc_guias_pagamento g
                    WHERE g.processo_id = p.id
                ) as guias
                FROM cuc_processos p 
                LEFT JOIN cuc_usuarios u ON p.responsavel_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        // Aplicar filtros se existirem
        if (!empty($filtros['numero_processo'])) {
            $sql .= " AND p.numero_processo ILIKE :numero_processo";
            $params[':numero_processo'] = '%' . $filtros['numero_processo'] . '%';
        }
        
        if (!empty($filtros['comarca_serventia'])) {
            $sql .= " AND p.comarca_serventia ILIKE :comarca_serventia";
            $params[':comarca_serventia'] = '%' . $filtros['comarca_serventia'] . '%';
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filtros['status'];
        }

        // Modifique a query de contagem (por volta da linha 247-252)
        $sqlCount = "SELECT COUNT(*) as total FROM (
            SELECT p.id 
            FROM cuc_processos p 
            LEFT JOIN cuc_usuarios u ON p.responsavel_id = u.id 
            WHERE 1=1";

        if (!empty($filtros['numero_processo'])) {
            $sqlCount .= " AND p.numero_processo ILIKE :numero_processo";
        }

        if (!empty($filtros['comarca_serventia'])) {
            $sqlCount .= " AND p.comarca_serventia ILIKE :comarca_serventia";
        }

        if (!empty($filtros['status'])) {
            $sqlCount .= " AND p.status = :status";
        }

        $sqlCount .= ") as sub";

        $this->db->query($sqlCount);
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        $total = $this->db->resultado()->total;
        
        // Adicionar ordenação e paginação
        $sql .= " ORDER BY p.data_cadastro DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $this->db->bind(':limit', $processosPorPagina, PDO::PARAM_INT);
        
        return [
            'processos' => $this->db->resultados(),
            'total' => $total,
            'paginas' => ceil($total / $processosPorPagina)
        ];
    }

    /**
     * Busca um processo pelo ID
     * @param int $id ID do processo
     * @return object|false Retorna o processo ou false se não encontrar
     */
    public function buscarProcessoPorId($id) {
        $this->db->query("SELECT p.*, 
                          u_cadastro.nome as usuario_cadastro_nome,
                          u_resp.nome as responsavel_nome
                          FROM cuc_processos p
                          LEFT JOIN cuc_usuarios u_cadastro ON p.usuario_cadastro = u_cadastro.id
                          LEFT JOIN cuc_usuarios u_resp ON p.responsavel_id = u_resp.id
                          WHERE p.id = :id");
        
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * Lista movimentações de um processo
     * @param int $processo_id ID do processo
     * @return array Lista de movimentações
     */
    public function listarMovimentacoes($processo_id) {
        $this->db->query("SELECT m.*, u.nome as usuario_nome 
                          FROM cuc_movimentacoes m 
                          JOIN cuc_usuarios u ON m.usuario_id = u.id 
                          WHERE m.processo_id = :processo_id 
                          ORDER BY m.data_movimentacao DESC");
        
        $this->db->bind(':processo_id', $processo_id);
        
        return $this->db->resultados();
    }

    /**
     * Lista intimações de um processo
     * @param int $processo_id ID do processo
     * @return array Lista de intimações
     */
    public function listarIntimacoes($processo_id) {
        $this->db->query("SELECT i.*, p.nome as parte_nome, p.tipo as parte_tipo, p.telefone, p.email 
                          FROM cuc_intimacoes i 
                          LEFT JOIN cuc_partes p ON i.parte_id = p.id 
                          WHERE i.processo_id = :processo_id 
                          ORDER BY i.data_envio DESC");
        
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->resultados();
    }

    /**
     * Busca o usuário atual do processo
     */
    public function buscarUsuarioAtualProcesso($processo_id) {
        $this->db->query("SELECT 
                            COALESCE(
                                (SELECT usuario_id 
                                 FROM cuc_movimentacoes 
                                 WHERE processo_id = :processo_id 
                                 ORDER BY data_movimentacao DESC 
                                 LIMIT 1),
                                usuario_cadastro
                            ) as usuario_id
                          FROM cuc_processos 
                          WHERE id = :processo_id");
        
        $this->db->bind(':processo_id', $processo_id);
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->usuario_id : null;
    }

    /**
     * [ registrarIntimacao ] - Registra uma nova intimação para o processo
     * 
     * @param array $dados Dados da intimação (processo_id, tipo_intimacao, destinatario, prazo)
     * @return bool True se a intimação foi registrada com sucesso, false caso contrário
     */
    public function registrarIntimacao($dados) {
        $this->db->query("INSERT INTO cuc_intimacoes (
            processo_id,
            tipo_intimacao,
            destinatario,
            parte_id,
            data_envio,
            prazo,
            status,
            comprovante_path
        ) VALUES (
            :processo_id,
            :tipo_intimacao,
            :destinatario,
            :parte_id,
            NOW(),
            :prazo,
            'pendente',
            :comprovante_path
        )");

        $this->db->bind(':processo_id', $dados['processo_id']);
        $this->db->bind(':tipo_intimacao', $dados['tipo_intimacao']);
        $this->db->bind(':destinatario', $dados['destinatario']);
        $this->db->bind(':parte_id', $dados['parte_id']);
        $this->db->bind(':prazo', $dados['prazo']);
        $this->db->bind(':comprovante_path', $dados['comprovante_path'] ?? null);

        $resultado = $this->db->executa();
        
        if ($resultado) {
            // Tenta registrar a movimentação, mas não afeta o resultado da intimação
            $this->registrarMovimentacao(
                $dados['processo_id'],
                'intimacao',
                "Intimação enviada para {$dados['destinatario']}",
                $dados['prazo']
            );
        }
        
        return $resultado;
    }

    /**
     * [ excluirIntimacao ] - Exclui uma intimação
     * 
     * @param int $intimacao_id ID da intimação
     * @return bool True se excluiu com sucesso, false caso contrário
     */
    public function excluirIntimacao($intimacao_id) {
        $this->db->query("DELETE FROM cuc_intimacoes WHERE id = :id");
        $this->db->bind(':id', $intimacao_id);
        return $this->db->executa();
    }

    /**
     * [ atualizarIntimacao ] - Atualiza dados da intimação
     * 
     * @param array $dados Dados da intimação
     * @return bool True se atualizou com sucesso, false caso contrário
     */
    public function atualizarIntimacao($dados) {
        $this->db->query("UPDATE cuc_intimacoes SET 
            tipo_intimacao = :tipo_intimacao,
            destinatario = :destinatario,
            prazo = :prazo,
            status = :status
            WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':tipo_intimacao', $dados['tipo_intimacao']);
        $this->db->bind(':destinatario', $dados['destinatario']);
        $this->db->bind(':prazo', $dados['prazo']);
        $this->db->bind(':status', $dados['status']);

        return $this->db->executa();
    }

    /**
     * [ buscarIntimacaoPorId ] - Busca uma intimação pelo ID
     * 
     * @param int $id ID da intimação
     * @return object|false Retorna a intimação ou false se não encontrar
     */
    public function buscarIntimacaoPorId($id) {
        $this->db->query("SELECT * FROM cuc_intimacoes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ buscarAdvogadosProcesso ] - Busca os advogados vinculados ao processo
     * 
     * @param int $processo_id ID do processo
     * @return array Lista de advogados
     */
    public function buscarAdvogadosProcesso($processo_id) {
        $this->db->query("SELECT * FROM cuc_advogados WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->resultados();
    }

    /**
     * [ atualizarProcesso ] - Atualiza os dados de um processo
     * 
     * @param array $dados Dados do processo
     * @return bool True se atualizado com sucesso, false caso contrário
     */
    public function atualizarProcesso($dados) {
        $this->db->query("UPDATE cuc_processos SET 
            numero_processo = :numero_processo,
            comarca_serventia = :comarca_serventia,
            status = :status,
            observacoes = :observacoes,
            responsavel_id = :responsavel_id,
            data_conclusao = :data_conclusao
            WHERE id = :id");
        
        $this->db->bind(':numero_processo', $dados['numero_processo']);
        $this->db->bind(':comarca_serventia', $dados['comarca_serventia']);
        $this->db->bind(':status', $dados['status']);
        $this->db->bind(':observacoes', $dados['observacoes']);
        $this->db->bind(':responsavel_id', $dados['responsavel_id']);
        $this->db->bind(':data_conclusao', $dados['data_conclusao']);
        $this->db->bind(':id', $dados['id']);
        
        return $this->db->executa();
    }

    /**
     * [ adicionarAdvogado ] - Adiciona um novo advogado ao processo
     * 
     * @param array $dados Dados do advogado
     * @return bool True se adicionou com sucesso, false caso contrário
     */
    public function adicionarAdvogado($dados) {
        try {
            $this->db->query("INSERT INTO cuc_advogados (
                processo_id,
                nome,
                oab,
                data_cadastro
            ) VALUES (
                :processo_id,
                :nome,
                :oab,
                NOW()
            )");

            $this->db->bind(':processo_id', $dados['processo_id']);
            $this->db->bind(':nome', $dados['nome']);
            $this->db->bind(':oab', $dados['oab']);

            return $this->db->executa();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [ buscarAdvogadoPorId ] - Busca um advogado pelo ID
     * 
     * @param int $id ID do advogado
     * @return object|false Retorna o advogado ou false se não encontrar
     */
    public function buscarAdvogadoPorId($id) {
        $this->db->query("SELECT * FROM cuc_advogados WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ excluirAdvogado ] - Remove um advogado do processo
     * 
     * @param int $id ID do advogado
     * @return bool True se excluiu com sucesso, false caso contrário
     */
    public function excluirAdvogado($id) {
        try {
            $this->db->query("DELETE FROM cuc_advogados WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->executa();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [ atualizarObservacoes ] - Atualiza as observações do processo
     * 
     * @param array $dados Dados do processo
     * @return bool True se atualizou com sucesso, false caso contrário
     */
    public function atualizarObservacoes($dados) {
        try {
            $this->db->query("UPDATE cuc_processos SET observacoes = :observacoes WHERE id = :id");
            $this->db->bind(':observacoes', $dados['observacoes']);
            $this->db->bind(':id', $dados['id']);
            return $this->db->executa();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [ atualizarResponsavel ] - Atualiza responsável e cria notificação
     * 
     * @param int $processo_id ID do processo
     * @param int $usuario_id ID do novo responsável
     * @return bool True se atualizou com sucesso, false caso contrário
     */
    public function atualizarResponsavel($processo_id, $usuario_id) {
        try {

            // Atualiza o responsável
            $this->db->query("UPDATE cuc_processos SET responsavel_id = :responsavel_id WHERE id = :id");
            $this->db->bind(':responsavel_id', $usuario_id);
            $this->db->bind(':id', $processo_id);
            
            if ($this->db->executa()) {
                // Criar notificação para o novo responsável
                $notificacaoModel = new NotificacaoModel();
                $notificacaoModel->criarNotificacao([
                    'usuario_id' => $usuario_id,
                    'processo_id' => $processo_id,
                    'tipo' => 'responsabilidade',
                    'mensagem' => "Você foi designado como responsável por este processo",
                    'data_prazo' => date('Y-m-d', strtotime('+7 days'))
                ]);

               
                return true;
            }

            
            return false;
        } catch (Exception $e) {
            
            return false;
        }
    }

    /**
     * [ buscarIntimacoesParteId ] - Busca as intimações de uma parte
     * 
     * @param int $parte_id ID da parte
     * @return object|false Retorna as intimações ou false se não encontrar
     */
    public function buscarIntimacoesParteId($parte_id) {
        $this->db->query("SELECT * FROM cuc_intimacoes WHERE parte_id = :parte_id");
        $this->db->bind(':parte_id', $parte_id);
        return $this->db->resultado();
    }

    /**
     * Pesquisa processos por número do processo ou número da guia
     * @param string|null $numero_processo Número do processo
     * @param string|null $numero_guia Número da guia
     * @return array Resultados da pesquisa
     */
    public function pesquisarProcessos($numero_processo = null, $numero_guia = null)
    {
        $sql = "SELECT p.*, 
                       CASE 
                           WHEN p.status = 'analise' THEN 'Em Análise'
                           WHEN p.status = 'intimacao' THEN 'Em Intimação'
                           WHEN p.status = 'diligencia' THEN 'Em Diligência'
                           ELSE p.status
                       END as status_formatado,
                       u.nome as responsavel_nome,
                       (
                           SELECT json_agg(
                               json_build_object(
                                   'numero_guia', g.numero_guia,
                                   'valor', g.valor,
                                   'status', g.status,
                                   'data_vencimento', g.data_vencimento,
                                   'observacao', g.observacao
                               )
                           )
                           FROM cuc_guias_pagamento g
                           WHERE g.processo_id = p.id
                       ) as guias
                FROM cuc_processos p
                LEFT JOIN cuc_usuarios u ON p.responsavel_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($numero_processo)) {
            $sql .= " AND p.numero_processo ILIKE :numero_processo";
            $params[':numero_processo'] = "%{$numero_processo}%";
        }
        
        if (!empty($numero_guia)) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM cuc_guias_pagamento g 
                WHERE g.processo_id = p.id 
                AND g.numero_guia ILIKE :numero_guia
            )";
            $params[':numero_guia'] = "%{$numero_guia}%";
        }
        
        $sql .= " ORDER BY p.data_cadastro DESC";
        
        $this->db->query($sql);
        
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return $this->db->resultados();
    }

    /**
     * Obtém resumo da produtividade de um usuário específico
     * 
     * @param int $usuario_id ID do usuário
     * @param string $data_inicio Data inicial
     * @param string $data_fim Data final
     * @return object Resumo da produtividade
     */
    public function obterResumoProdutividadeUsuario($usuario_id, $data_inicio = null, $data_fim = null) {
        $sql = "SELECT 
            COALESCE(COUNT(DISTINCT p.id), 0) as total_processos,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'concluido' THEN p.id END), 0) as total_concluidos,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'analise' THEN p.id END), 0) as total_analise,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'intimacao' THEN p.id END), 0) as total_intimacao,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'diligencia' THEN p.id END), 0) as total_diligencia,
            COALESCE(AVG(CASE WHEN p.data_conclusao IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (p.data_conclusao - p.data_cadastro))/86400 
                END), 0)::numeric(10,2) as media_dias_conclusao,
            COALESCE(COUNT(DISTINCT m.id), 0) as total_movimentacoes,
            COALESCE(COUNT(DISTINCT n.id), 0) as total_notificacoes
        FROM cuc_processos p
        LEFT JOIN cuc_movimentacoes m ON p.id = m.processo_id
        LEFT JOIN cuc_notificacoes n ON p.id = n.processo_id
        WHERE p.usuario_cadastro = :usuario_id
        AND p.data_cadastro::date >= COALESCE(:data_inicio::date, p.data_cadastro::date)
        AND p.data_cadastro::date <= COALESCE(:data_fim::date, p.data_cadastro::date)";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':data_inicio', $data_inicio);
        $this->db->bind(':data_fim', $data_fim);
        
        $resultado = $this->db->resultado();
        
        // Se não retornar resultados, cria um objeto padrão
        if (!$resultado) {
            $resultado = (object)[
                'total_processos' => 0,
                'total_concluidos' => 0,
                'total_analise' => 0,
                'total_intimacao' => 0,
                'total_diligencia' => 0,
                'media_dias_conclusao' => 0,
                'total_movimentacoes' => 0,
                'total_notificacoes' => 0
            ];
        }
        
        return $resultado;
    }

    /**
     * Obtém resumo da produtividade geral de todos os usuários
     * 
     * @param string $data_inicio Data inicial
     * @param string $data_fim Data final
     * @return object Resumo da produtividade geral
     */
    public function obterResumoProdutividadeGeral($data_inicio = null, $data_fim = null) {
        $sql = "SELECT 
            COALESCE(COUNT(DISTINCT p.id), 0) as total_processos,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'concluido' THEN p.id END), 0) as total_concluidos,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'analise' THEN p.id END), 0) as total_analise,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'intimacao' THEN p.id END), 0) as total_intimacao,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'diligencia' THEN p.id END), 0) as total_diligencia,
            COALESCE(AVG(CASE WHEN p.data_conclusao IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (p.data_conclusao - p.data_cadastro))/86400 
                END), 0)::numeric(10,2) as media_dias_conclusao,
            COALESCE(COUNT(DISTINCT m.id), 0) as total_movimentacoes,
            COALESCE(COUNT(DISTINCT n.id), 0) as total_notificacoes
        FROM cuc_processos p
        LEFT JOIN cuc_movimentacoes m ON p.id = m.processo_id
        LEFT JOIN cuc_notificacoes n ON p.id = n.processo_id
        WHERE p.data_cadastro::date >= COALESCE(:data_inicio::date, p.data_cadastro::date)
        AND p.data_cadastro::date <= COALESCE(:data_fim::date, p.data_cadastro::date)";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $data_inicio);
        $this->db->bind(':data_fim', $data_fim);
        
        $resultado = $this->db->resultado();
        
        // Se não retornar resultados, cria um objeto padrão
        if (!$resultado) {
            $resultado = (object)[
                'total_processos' => 0,
                'total_concluidos' => 0,
                'total_analise' => 0,
                'total_intimacao' => 0,
                'total_diligencia' => 0,
                'media_dias_conclusao' => 0,
                'total_movimentacoes' => 0,
                'total_notificacoes' => 0
            ];
        }
        
        return $resultado;
    }

    /**
     * Obtém produtividade de todos os usuários agrupada por usuário
     * 
     * @param string $data_inicio Data inicial
     * @param string $data_fim Data final
     * @return array Produtividade de todos os usuários
     */
    public function obterProdutividadeTodosUsuarios($data_inicio = null, $data_fim = null) {
        $sql = "SELECT 
            u.id as usuario_id,
            u.nome as nome_usuario,
            u.perfil as perfil_usuario,
            COALESCE(COUNT(DISTINCT p.id), 0) as total_processos,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'concluido' THEN p.id END), 0) as total_concluidos,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'analise' THEN p.id END), 0) as total_analise,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'intimacao' THEN p.id END), 0) as total_intimacao,
            COALESCE(COUNT(DISTINCT CASE WHEN p.status = 'diligencia' THEN p.id END), 0) as total_diligencia,
            COALESCE(AVG(CASE WHEN p.data_conclusao IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (p.data_conclusao - p.data_cadastro))/86400 
                END), 0)::numeric(10,2) as media_dias_conclusao,
            COALESCE(COUNT(DISTINCT m.id), 0) as total_movimentacoes
        FROM cuc_usuarios u
        LEFT JOIN cuc_processos p ON u.id = p.usuario_cadastro
            AND p.data_cadastro::date >= COALESCE(:data_inicio::date, p.data_cadastro::date)
            AND p.data_cadastro::date <= COALESCE(:data_fim::date, p.data_cadastro::date)
        LEFT JOIN cuc_movimentacoes m ON p.id = m.processo_id
        GROUP BY u.id, u.nome, u.perfil
        ORDER BY u.nome";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $data_inicio);
        $this->db->bind(':data_fim', $data_fim);
        
        $resultados = $this->db->resultados();
        
        // Se não retornar resultados, retorna um array vazio
        if (empty($resultados)) {
            // Busca todos os usuários e cria um array com valores zerados
            $this->db->query("SELECT id as usuario_id, nome as nome_usuario, perfil as perfil_usuario FROM cuc_usuarios ORDER BY nome");
            $usuarios = $this->db->resultados();
            
            foreach ($usuarios as $usuario) {
                $usuario->total_processos = 0;
                $usuario->total_concluidos = 0;
                $usuario->total_analise = 0;
                $usuario->total_intimacao = 0;
                $usuario->total_diligencia = 0;
                $usuario->media_dias_conclusao = 0;
                $usuario->total_movimentacoes = 0;
            }
            
            return $usuarios;
        }
        
        return $resultados;
    }

    /**
     * [ verificarProcessoExistente ] - Verifica se já existe um processo com o mesmo número
     * 
     * @param string $numero_processo - Número do processo
     * @return bool - True se o processo já existe, false caso contrário
     */
    public function verificarProcessoExistente($numero_processo) {
        $this->db->query("SELECT COUNT(*) as total FROM cuc_processos WHERE numero_processo = :numero_processo");
        $this->db->bind(':numero_processo', $numero_processo);
        $resultado = $this->db->resultado();
        return $resultado->total > 0;
    }

    /**
     * [ excluirIntimacoesProcessoId ] - Exclui todas as intimações de um processo
     * 
     * @param int $processo_id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirIntimacoesProcessoId($processo_id) {
        $this->db->query("DELETE FROM cuc_intimacoes WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->executa();
    }

    /**
     * [ excluirAdvogadosPorProcessoId ] - Exclui todos os advogados de um processo
     * 
     * @param int $processo_id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirAdvogadosPorProcessoId($processo_id) {
        $this->db->query("DELETE FROM cuc_advogados WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->executa();
    }

    /**
     * [ excluirMovimentacoesPorProcessoId ] - Exclui todas as movimentações de um processo
     * 
     * @param int $processo_id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirMovimentacoesPorProcessoId($processo_id) {
        $this->db->query("DELETE FROM cuc_movimentacoes WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->executa();
    }

    /**
     * [ excluirProcesso ] - Exclui um processo
     * 
     * @param int $id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirProcesso($id) {
        $this->db->query("DELETE FROM cuc_processos WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }
} 