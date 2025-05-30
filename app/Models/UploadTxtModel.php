<?php

/**
 * [ UploadTxtModel ] - Model responsável por gerenciar os dados de uploads de arquivos TXT
 * 
 * Esta model permite:
 * - Inserir dados de diferentes tipos de arquivos TXT (NAC, CUC, CÂMARAS)
 * - Limpar tabelas antes de novas importações
 * - Contar registros nas tabelas
 * 
 * @author Cleyton Parente <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access public
 */
class UploadTxtModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ inserirDados ] - Insere dados em uma tabela específica
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @param string $numero Número do processo
     * @param string $comarca Comarca do processo
     * @param string $movimentacao Tipo de movimentação
     * @param string $data Data da movimentação
     * @param string $nome Nome do responsável
     * @return bool Retorna true se a inserção for bem-sucedida, false caso contrário
     */
    public function inserirDados($tabela, $numero, $comarca, $movimentacao, $data, $nome)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return false;
        }

        $this->db->query("INSERT INTO $tabela 
            (numero, comarca, movimentacao, data, nome) 
            VALUES (:numero, :comarca, :movimentacao, :data, :nome)");

        $this->db->bind(':numero', $numero);
        $this->db->bind(':comarca', $comarca);
        $this->db->bind(':movimentacao', $movimentacao);
        $this->db->bind(':data', $data);
        $this->db->bind(':nome', $nome);
        
        return $this->db->executa();
    }

    /**
     * [ limparTabela ] - Limpa todos os registros de uma tabela específica
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @return bool Retorna true se a operação for bem-sucedida, false caso contrário
     */
    public function limparTabela($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de limpar tabela não permitida: $tabela");
            return false;
        }

        $this->db->query("TRUNCATE TABLE $tabela");
        return $this->db->executa();
    }

    /**
     * [ contarRegistros ] - Conta o número de registros em uma tabela específica
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @return int Número de registros
     */
    public function contarRegistros($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de contar registros em tabela não permitida: $tabela");
            return 0;
        }

        $this->db->query("SELECT COUNT(*) as total FROM $tabela");
        $resultado = $this->db->resultado();
        return $resultado->total ?? 0;
    }

    /**
     * [ verificarTabelaExiste ] - Verifica se uma tabela existe no banco de dados
     * 
     * @param string $tabela Nome da tabela a verificar
     * @return bool Retorna true se a tabela existir, false caso contrário
     */
    public function verificarTabelaExiste($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de verificar tabela não permitida: $tabela");
            return false;
        }
        
        // Usar a tabela diretamente na consulta, já que foi validada
        $this->db->query("SHOW TABLES LIKE '$tabela'");
        $resultado = $this->db->resultados();
        return !empty($resultado);
    }

    /**
     * [ criarTabela ] - Cria uma tabela se ela não existir
     * 
     * @param string $tabela Nome da tabela a criar
     * @return bool Retorna true se a operação for bem-sucedida, false caso contrário
     */
    public function criarTabela($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de criar tabela não permitida: $tabela");
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS $tabela (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(50) NOT NULL,
            comarca VARCHAR(255) NOT NULL,
            movimentacao VARCHAR(255) NOT NULL,
            data DATETIME NOT NULL,
            nome VARCHAR(255) NOT NULL,
            data_importacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->query($sql);
        return $this->db->executa();
    }

    /**
     * [ obterMovimentacoesUnicas ] - Obtém lista de movimentações únicas
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @return array Lista de movimentações únicas
     */
    public function obterMovimentacoesUnicas($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return [];
        }
        
        $this->db->query("SELECT DISTINCT movimentacao FROM $tabela ORDER BY movimentacao");
        return $this->db->resultados();
    }

    /**
     * [ obterResponsaveisUnicos ] - Obtém lista de responsáveis únicos
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @return array Lista de responsáveis únicos
     */
    public function obterResponsaveisUnicos($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return [];
        }
        
        $this->db->query("SELECT DISTINCT nome FROM $tabela ORDER BY nome");
        return $this->db->resultados();
    }

    /**
     * [ obterEstatisticasNAC ] - Obtém estatísticas de movimentações do NAC
     * 
     * @return array Matriz com contagens de movimentações por responsável
     */
    public function obterEstatisticasNAC()
    {
        $tabela = 'dados_nac';
        
        // Obter todos os responsáveis
        $responsaveis = $this->obterResponsaveisUnicos($tabela);
        
        // Obter todas as movimentações
        $movimentacoes = $this->obterMovimentacoesUnicas($tabela);
        
        // Inicializar matriz de resultados
        $estatisticas = [];
        
        // Para cada responsável, obter contagem de cada tipo de movimentação
        foreach ($responsaveis as $responsavel) {
            $nome = $responsavel->nome;
            $estatisticas[$nome] = [];
            $totalResponsavel = 0;
            
            foreach ($movimentacoes as $movimentacao) {
                $tipoMovimentacao = $movimentacao->movimentacao;
                
                // Contar registros para este responsável e esta movimentação
                $this->db->query("SELECT COUNT(*) as total FROM $tabela 
                                 WHERE nome = :nome AND movimentacao = :movimentacao");
                $this->db->bind(':nome', $nome);
                $this->db->bind(':movimentacao', $tipoMovimentacao);
                $resultado = $this->db->resultado();
                
                $contagem = $resultado->total;
                $estatisticas[$nome][$tipoMovimentacao] = $contagem;
                $totalResponsavel += $contagem;
            }
            
            // Adicionar total por responsável
            $estatisticas[$nome]['total'] = $totalResponsavel;
        }
        
        // Calcular totais por movimentação
        $totaisMovimentacao = [];
        foreach ($movimentacoes as $movimentacao) {
            $tipoMovimentacao = $movimentacao->movimentacao;
            $this->db->query("SELECT COUNT(*) as total FROM $tabela WHERE movimentacao = :movimentacao");
            $this->db->bind(':movimentacao', $tipoMovimentacao);
            $resultado = $this->db->resultado();
            $totaisMovimentacao[$tipoMovimentacao] = $resultado->total;
        }
        
        // Adicionar linha de totais
        $estatisticas['totais'] = $totaisMovimentacao;
        
        // Calcular total geral
        $this->db->query("SELECT COUNT(*) as total FROM $tabela");
        $resultado = $this->db->resultado();
        $estatisticas['totais']['total'] = $resultado->total;
        
        return $estatisticas;
    }

    /**
     * [ obterMovimentacoesDetalhadas ] - Obtém detalhes de todas as movimentações
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @param string $dataInicio Data inicial no formato Y-m-d (opcional)
     * @param string $dataFim Data final no formato Y-m-d (opcional)
     * @param string $responsavel Nome do responsável (opcional)
     * @param string $comarca Nome da comarca (opcional)
     * @param string $movimentacao Tipo de movimentação (opcional)
     * @return array Lista detalhada de movimentações
     */
    public function obterMovimentacoesDetalhadas($tabela, $dataInicio = null, $dataFim = null, $responsavel = null, $comarca = null, $movimentacao = null)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return [];
        }
        
        $sql = "SELECT numero, comarca, movimentacao, data, nome 
                FROM $tabela 
                WHERE 1=1";
        
        $params = [];
        
        // Adicionar filtros se fornecidos
        if ($dataInicio && $dataFim) {
            $sql .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            $params[':dataInicio'] = $dataInicio;
            $params[':dataFim'] = $dataFim;
        } elseif ($dataInicio) {
            $sql .= " AND DATE(data) >= :dataInicio";
            $params[':dataInicio'] = $dataInicio;
        } elseif ($dataFim) {
            $sql .= " AND DATE(data) <= :dataFim";
            $params[':dataFim'] = $dataFim;
        }
        
        if ($responsavel) {
            $sql .= " AND nome = :responsavel";
            $params[':responsavel'] = $responsavel;
        }
        
        if ($comarca) {
            $sql .= " AND comarca = :comarca";
            $params[':comarca'] = $comarca;
        }
        
        if ($movimentacao) {
            $sql .= " AND movimentacao = :movimentacao";
            $params[':movimentacao'] = $movimentacao;
        }
        
        // Ordenar por data (mais recente primeiro) e depois por nome
        $sql .= " ORDER BY data DESC, nome ASC";
        
        $this->db->query($sql);
        
        // Vincular parâmetros
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ obterComarcasUnicas ] - Obtém lista de comarcas únicas
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @return array Lista de comarcas únicas
     */
    public function obterComarcasUnicas($tabela)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return [];
        }
        
        $this->db->query("SELECT DISTINCT comarca FROM $tabela ORDER BY comarca");
        return $this->db->resultados();
    }

    /**
     * [ obterEstatisticasDiarias ] - Obtém estatísticas de movimentações por dia
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @param string $dataInicio Data inicial no formato Y-m-d (opcional)
     * @param string $dataFim Data final no formato Y-m-d (opcional)
     * @return array Estatísticas diárias
     */
    public function obterEstatisticasDiarias($tabela, $dataInicio = null, $dataFim = null)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return [];
        }
        
        $sql = "SELECT 
                    DATE(data) as dia,
                    COUNT(*) as total_movimentacoes,
                    COUNT(DISTINCT numero) as total_processos,
                    COUNT(DISTINCT comarca) as total_comarcas,
                    COUNT(DISTINCT nome) as total_responsaveis
                FROM $tabela
                WHERE 1=1";
        
        $params = [];
        
        // Adicionar filtros se fornecidos
        if ($dataInicio && $dataFim) {
            $sql .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            $params[':dataInicio'] = $dataInicio;
            $params[':dataFim'] = $dataFim;
        } elseif ($dataInicio) {
            $sql .= " AND DATE(data) >= :dataInicio";
            $params[':dataInicio'] = $dataInicio;
        } elseif ($dataFim) {
            $sql .= " AND DATE(data) <= :dataFim";
            $params[':dataFim'] = $dataFim;
        }
        
        $sql .= " GROUP BY dia ORDER BY dia DESC";
        
        $this->db->query($sql);
        
        // Vincular parâmetros
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ obterMovimentacoesDetalhadasPaginadas ] - Obtém detalhes de movimentações com paginação
     * 
     * @param string $tabela Nome da tabela (dados_nac, dados_cuc, dados_camara)
     * @param int $pagina Número da página atual
     * @param int $itensPorPagina Quantidade de itens por página
     * @param string $dataInicio Data inicial no formato Y-m-d (opcional)
     * @param string $dataFim Data final no formato Y-m-d (opcional)
     * @param string $responsavel Nome do responsável (opcional)
     * @param string $comarca Nome da comarca (opcional)
     * @param string $movimentacao Tipo de movimentação (opcional)
     * @return array Array contendo os resultados e informações de paginação
     */
    public function obterMovimentacoesDetalhadasPaginadas($tabela, $pagina = 1, $itensPorPagina = 50, $dataInicio = null, $dataFim = null, $responsavel = null, $comarca = null, $movimentacao = null)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return [
                'dados' => [],
                'paginacao' => [
                    'pagina_atual' => 1,
                    'total_paginas' => 0,
                    'total_registros' => 0,
                    'itens_por_pagina' => $itensPorPagina
                ]
            ];
        }
        
        // Construir a parte WHERE da consulta
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Adicionar filtros se fornecidos
        if ($dataInicio && $dataFim) {
            $whereClause .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            $params[':dataInicio'] = $dataInicio;
            $params[':dataFim'] = $dataFim;
        } elseif ($dataInicio) {
            $whereClause .= " AND DATE(data) >= :dataInicio";
            $params[':dataInicio'] = $dataInicio;
        } elseif ($dataFim) {
            $whereClause .= " AND DATE(data) <= :dataFim";
            $params[':dataFim'] = $dataFim;
        }
        
        if ($responsavel) {
            $whereClause .= " AND nome = :responsavel";
            $params[':responsavel'] = $responsavel;
        }
        
        if ($comarca) {
            $whereClause .= " AND comarca = :comarca";
            $params[':comarca'] = $comarca;
        }
        
        if ($movimentacao) {
            $whereClause .= " AND movimentacao = :movimentacao";
            $params[':movimentacao'] = $movimentacao;
        }
        
        // Contar total de registros para paginação
        $sqlCount = "SELECT COUNT(*) as total FROM $tabela $whereClause";
        $this->db->query($sqlCount);
        
        // Vincular parâmetros para a contagem
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $resultado = $this->db->resultado();
        $totalRegistros = $resultado->total;
        
        // Calcular total de páginas
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);
        
        // Garantir que a página atual seja válida
        $pagina = max(1, min($pagina, $totalPaginas));
        
        // Calcular offset para LIMIT
        $offset = ($pagina - 1) * $itensPorPagina;
        
        // Consulta principal com paginação
        $sql = "SELECT numero, comarca, movimentacao, data, nome 
                FROM $tabela 
                $whereClause
                ORDER BY data DESC, nome ASC
                LIMIT :offset, :limit";
        
        $this->db->query($sql);
        
        // Vincular parâmetros para a consulta principal
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $this->db->bind(':limit', $itensPorPagina, PDO::PARAM_INT);
        
        $dados = $this->db->resultados();
        
        // Retornar dados e informações de paginação
        return [
            'dados' => $dados,
            'paginacao' => [
                'pagina_atual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalRegistros,
                'itens_por_pagina' => $itensPorPagina
            ]
        ];
    }

    /**
     * [ contarProcessosUnicos ] - Conta o número de processos únicos com filtros
     */
    public function contarProcessosUnicos($tabela, $dataInicio = null, $dataFim = null, $responsavel = null, $comarca = null, $movimentacao = null)
    {
        return $this->contarRegistrosUnicos($tabela, 'numero', $dataInicio, $dataFim, $responsavel, $comarca, $movimentacao);
    }

    /**
     * [ contarComarcasUnicas ] - Conta o número de comarcas únicas com filtros
     */
    public function contarComarcasUnicas($tabela, $dataInicio = null, $dataFim = null, $responsavel = null, $comarca = null, $movimentacao = null)
    {
        return $this->contarRegistrosUnicos($tabela, 'comarca', $dataInicio, $dataFim, $responsavel, $comarca, $movimentacao);
    }

    /**
     * [ contarResponsaveisUnicos ] - Conta o número de responsáveis únicos com filtros
     */
    public function contarResponsaveisUnicos($tabela, $dataInicio = null, $dataFim = null, $responsavel = null, $comarca = null, $movimentacao = null)
    {
        return $this->contarRegistrosUnicos($tabela, 'nome', $dataInicio, $dataFim, $responsavel, $comarca, $movimentacao);
    }

    /**
     * [ contarRegistrosUnicos ] - Método genérico para contar registros únicos
     */
    private function contarRegistrosUnicos($tabela, $campo, $dataInicio = null, $dataFim = null, $responsavel = null, $comarca = null, $movimentacao = null)
    {
        // Validar nome da tabela para evitar SQL Injection
        $tabelasPermitidas = ['dados_nac', 'dados_cuc', 'dados_camara'];
        if (!in_array($tabela, $tabelasPermitidas)) {
            error_log("Tentativa de acesso a tabela não permitida: $tabela");
            return 0;
        }
        
        $sql = "SELECT COUNT(DISTINCT $campo) as total FROM $tabela WHERE 1=1";
        $params = [];
        
        // Adicionar filtros se fornecidos
        if ($dataInicio && $dataFim) {
            $sql .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            $params[':dataInicio'] = $dataInicio;
            $params[':dataFim'] = $dataFim;
        } elseif ($dataInicio) {
            $sql .= " AND DATE(data) >= :dataInicio";
            $params[':dataInicio'] = $dataInicio;
        } elseif ($dataFim) {
            $sql .= " AND DATE(data) <= :dataFim";
            $params[':dataFim'] = $dataFim;
        }
        
        if ($responsavel) {
            $sql .= " AND nome = :responsavel";
            $params[':responsavel'] = $responsavel;
        }
        
        if ($comarca) {
            $sql .= " AND comarca = :comarca";
            $params[':comarca'] = $comarca;
        }
        
        if ($movimentacao) {
            $sql .= " AND movimentacao = :movimentacao";
            $params[':movimentacao'] = $movimentacao;
        }
        
        $this->db->query($sql);
        
        // Vincular parâmetros
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $resultado = $this->db->resultado();
        return $resultado->total;
    }

    /**
     * [ obterEstatisticasNACPaginadas ] - Obtém estatísticas do NAC com paginação
     * 
     * @param int $pagina Número da página atual
     * @param int $itensPorPagina Quantidade de itens por página
     * @param string $dataInicio Data inicial no formato Y-m-d (opcional)
     * @param string $dataFim Data final no formato Y-m-d (opcional)
     * @param string $responsavel Nome do responsável (opcional)
     * @return array Array contendo estatísticas e informações de paginação
     */
    public function obterEstatisticasNACPaginadas($pagina = 1, $itensPorPagina = 10, $dataInicio = null, $dataFim = null, $responsavel = null)
    {
        $tabela = 'dados_nac';
        
        // Construir a parte WHERE da consulta
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Adicionar filtros se fornecidos
        if ($dataInicio && $dataFim) {
            $whereClause .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            $params[':dataInicio'] = $dataInicio;
            $params[':dataFim'] = $dataFim;
        } elseif ($dataInicio) {
            $whereClause .= " AND DATE(data) >= :dataInicio";
            $params[':dataInicio'] = $dataInicio;
        } elseif ($dataFim) {
            $whereClause .= " AND DATE(data) <= :dataFim";
            $params[':dataFim'] = $dataFim;
        }
        
        if ($responsavel) {
            $whereClause .= " AND nome = :responsavel";
            $params[':responsavel'] = $responsavel;
        }
        
        // Contar total de responsáveis únicos para paginação
        $sqlCount = "SELECT COUNT(DISTINCT nome) as total FROM $tabela $whereClause";
        $this->db->query($sqlCount);
        
        // Vincular parâmetros para a contagem
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $resultado = $this->db->resultado();
        $totalResponsaveis = $resultado->total;
        
        // Calcular total de páginas
        $totalPaginas = ceil($totalResponsaveis / $itensPorPagina);
        
        // Garantir que a página atual seja válida
        $pagina = max(1, min($pagina, $totalPaginas > 0 ? $totalPaginas : 1));
        
        // Calcular offset para LIMIT
        $offset = ($pagina - 1) * $itensPorPagina;
        
        // Obter responsáveis para a página atual
        $sqlResponsaveis = "SELECT DISTINCT nome FROM $tabela $whereClause ORDER BY nome ASC LIMIT :offset, :limit";
        $this->db->query($sqlResponsaveis);
        
        // Vincular parâmetros para a consulta de responsáveis
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $this->db->bind(':limit', $itensPorPagina, PDO::PARAM_INT);
        
        $responsaveisPaginados = $this->db->resultados();
        
        // Obter tipos de movimentações
        $movimentacoes = $this->obterMovimentacoesUnicas($tabela);
        
        // Inicializar array de estatísticas
        $estatisticas = [];
        
        // Para cada responsável na página atual, obter estatísticas
        foreach ($responsaveisPaginados as $resp) {
            $nomeResponsavel = $resp->nome;
            $estatisticas[$nomeResponsavel] = [];
            
            // Para cada tipo de movimentação, contar ocorrências
            foreach ($movimentacoes as $mov) {
                $tipoMovimentacao = $mov->movimentacao;
                
                $sqlContagem = "SELECT COUNT(*) as total FROM $tabela 
                               WHERE nome = :nome AND movimentacao = :movimentacao";
                
                // Adicionar filtros de data se fornecidos
                if ($dataInicio && $dataFim) {
                    $sqlContagem .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
                } elseif ($dataInicio) {
                    $sqlContagem .= " AND DATE(data) >= :dataInicio";
                } elseif ($dataFim) {
                    $sqlContagem .= " AND DATE(data) <= :dataFim";
                }
                
                $this->db->query($sqlContagem);
                $this->db->bind(':nome', $nomeResponsavel);
                $this->db->bind(':movimentacao', $tipoMovimentacao);
                
                // Adicionar parâmetros de data se necessário
                if ($dataInicio && $dataFim) {
                    $this->db->bind(':dataInicio', $dataInicio);
                    $this->db->bind(':dataFim', $dataFim);
                } elseif ($dataInicio) {
                    $this->db->bind(':dataInicio', $dataInicio);
                } elseif ($dataFim) {
                    $this->db->bind(':dataFim', $dataFim);
                }
                
                $resultado = $this->db->resultado();
                $estatisticas[$nomeResponsavel][$tipoMovimentacao] = $resultado->total;
            }
            
            // Calcular total para este responsável
            $sqlTotal = "SELECT COUNT(*) as total FROM $tabela WHERE nome = :nome";
            
            // Adicionar filtros de data se fornecidos
            if ($dataInicio && $dataFim) {
                $sqlTotal .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            } elseif ($dataInicio) {
                $sqlTotal .= " AND DATE(data) >= :dataInicio";
            } elseif ($dataFim) {
                $sqlTotal .= " AND DATE(data) <= :dataFim";
            }
            
            $this->db->query($sqlTotal);
            $this->db->bind(':nome', $nomeResponsavel);
            
            // Adicionar parâmetros de data se necessário
            if ($dataInicio && $dataFim) {
                $this->db->bind(':dataInicio', $dataInicio);
                $this->db->bind(':dataFim', $dataFim);
            } elseif ($dataInicio) {
                $this->db->bind(':dataInicio', $dataInicio);
            } elseif ($dataFim) {
                $this->db->bind(':dataFim', $dataFim);
            }
            
            $resultado = $this->db->resultado();
            $estatisticas[$nomeResponsavel]['total'] = $resultado->total;
        }
        
        // Calcular totais por tipo de movimentação
        $totaisMovimentacao = [];
        
        foreach ($movimentacoes as $movimentacao) {
            $tipoMovimentacao = $movimentacao->movimentacao;
            
            $sqlTotalMov = "SELECT COUNT(*) as total FROM $tabela WHERE movimentacao = :movimentacao";
            
            // Adicionar filtros se fornecidos
            if ($dataInicio && $dataFim) {
                $sqlTotalMov .= " AND DATE(data) BETWEEN :dataInicio AND :dataFim";
            } elseif ($dataInicio) {
                $sqlTotalMov .= " AND DATE(data) >= :dataInicio";
            } elseif ($dataFim) {
                $sqlTotalMov .= " AND DATE(data) <= :dataFim";
            }
            
            if ($responsavel) {
                $sqlTotalMov .= " AND nome = :responsavel";
            }
            
            $this->db->query($sqlTotalMov);
            $this->db->bind(':movimentacao', $tipoMovimentacao);
            
            // Vincular outros parâmetros se necessário
            if ($dataInicio && $dataFim) {
                $this->db->bind(':dataInicio', $dataInicio);
                $this->db->bind(':dataFim', $dataFim);
            } elseif ($dataInicio) {
                $this->db->bind(':dataInicio', $dataInicio);
            } elseif ($dataFim) {
                $this->db->bind(':dataFim', $dataFim);
            }
            
            if ($responsavel) {
                $this->db->bind(':responsavel', $responsavel);
            }
            
            $resultado = $this->db->resultado();
            $totaisMovimentacao[$tipoMovimentacao] = $resultado->total;
        }
        
        // Calcular total geral
        $sqlTotalGeral = "SELECT COUNT(*) as total FROM $tabela $whereClause";
        $this->db->query($sqlTotalGeral);
        
        // Vincular parâmetros
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $resultado = $this->db->resultado();
        $totaisMovimentacao['total'] = $resultado->total;
        
        // Adicionar linha de totais
        $estatisticas['totais'] = $totaisMovimentacao;
        
        // Retornar dados e informações de paginação
        return [
            'estatisticas' => $estatisticas,
            'responsaveis' => $responsaveisPaginados,
            'paginacao' => [
                'pagina_atual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalResponsaveis,
                'itens_por_pagina' => $itensPorPagina
            ]
        ];
    }
} 