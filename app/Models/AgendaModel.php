<?php

/**
 * [ AGENDA MODEL ] - Modelo responsável por gerenciar os dados da agenda
 * 
 * Este modelo permite:
 * - Listar, criar, editar e excluir eventos
 * - Gerenciar categorias de eventos
 * - Buscar eventos por período
 * - Formatação de dados para FullCalendar
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class AgendaModel
{
    private $db;
    private $tabela_eventos = 'agenda_eventos';
    private $tabela_categorias = 'agenda_categorias';

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Listar todos os eventos para o FullCalendar
     * @param string $start Data de início (formato Y-m-d)
     * @param string $end Data de fim (formato Y-m-d)
     * @return array
     */
    public function listarEventosCalendar($start = null, $end = null)
    {
        try {
            // Query simplificada para teste
            $sql = "SELECT 
                        e.id,
                        e.titulo as title,
                        e.data_inicio as start,
                        e.data_fim as end,
                        c.cor as backgroundColor,
                        c.cor as borderColor,
                        e.evento_dia_inteiro
                    FROM {$this->tabela_eventos} e
                    INNER JOIN {$this->tabela_categorias} c ON e.categoria_id = c.id
                    WHERE c.ativo = 'S'
                    ORDER BY e.data_inicio ASC";
            
            $this->db->query($sql);
            $eventos = $this->db->resultados();
            
            // Converte objetos para array e formata para FullCalendar
            $eventosFormatados = [];
            foreach ($eventos as $evento) {
                $eventoArray = [
                    'id' => $evento->id,
                    'title' => $evento->title,
                    'start' => $evento->start,
                    'end' => $evento->end,
                    'backgroundColor' => $evento->backgroundColor,
                    'borderColor' => $evento->borderColor,
                    'allDay' => ($evento->evento_dia_inteiro == 'S')
                ];
                $eventosFormatados[] = $eventoArray;
            }
            
            return $eventosFormatados;
            
        } catch (Exception $e) {
            error_log("Erro ao listar eventos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar evento por ID
     * @param int $id
     * @return array|false
     */
    public function buscarEvento($id)
    {
        try {
            $sql = "SELECT 
                        e.*,
                        c.nome as categoria_nome,
                        c.cor as categoria_cor,
                        u.nome as usuario_nome
                    FROM {$this->tabela_eventos} e
                    INNER JOIN {$this->tabela_categorias} c ON e.categoria_id = c.id
                    LEFT JOIN usuarios u ON e.usuario_id = u.id
                    WHERE e.id = :id";
            
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            
            $resultado = $this->db->resultado();
            
            if ($resultado) {
                // Converter objeto para array
                $evento = [];
                foreach ($resultado as $key => $value) {
                    $evento[$key] = $value;
                }
                return $evento;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserir novo evento
     * @param array $dados
     * @return bool|int
     */
    public function inserirEvento($dados)
    {
        try {
            $sql = "INSERT INTO {$this->tabela_eventos} 
                    (titulo, descricao, data_inicio, data_fim, categoria_id, usuario_id, 
                     local, observacoes, status, evento_dia_inteiro) 
                    VALUES (:titulo, :descricao, :data_inicio, :data_fim, :categoria_id, :usuario_id, 
                            :local, :observacoes, :status, :evento_dia_inteiro)";

            $this->db->query($sql);
            $this->db->bind(':titulo', $dados['titulo']);
            $this->db->bind(':descricao', $dados['descricao'] ?? null);
            $this->db->bind(':data_inicio', $dados['data_inicio']);
            $this->db->bind(':data_fim', $dados['data_fim']);
            $this->db->bind(':categoria_id', $dados['categoria_id']);  
            $this->db->bind(':usuario_id', $dados['usuario_id']);
            $this->db->bind(':local', $dados['local'] ?? null);
            $this->db->bind(':observacoes', $dados['observacoes'] ?? null);
            $this->db->bind(':status', $dados['status'] ?? 'agendado');
            $this->db->bind(':evento_dia_inteiro', $dados['evento_dia_inteiro'] ?? 'N');

            if ($this->db->executa()) {
                return $this->db->ultimoIdInserido();
            }
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao inserir evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar evento
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizarEvento($id, $dados)
    {
        try {
            $sql = "UPDATE {$this->tabela_eventos} SET 
                    titulo = :titulo, descricao = :descricao, data_inicio = :data_inicio, data_fim = :data_fim, 
                    categoria_id = :categoria_id, local = :local, observacoes = :observacoes, status = :status, 
                    evento_dia_inteiro = :evento_dia_inteiro, updated_at = NOW()
                    WHERE id = :id";

            $this->db->query($sql);
            $this->db->bind(':titulo', $dados['titulo']);
            $this->db->bind(':descricao', $dados['descricao'] ?? null);
            $this->db->bind(':data_inicio', $dados['data_inicio']);
            $this->db->bind(':data_fim', $dados['data_fim']);
            $this->db->bind(':categoria_id', $dados['categoria_id']);
            $this->db->bind(':local', $dados['local'] ?? null);
            $this->db->bind(':observacoes', $dados['observacoes'] ?? null);
            $this->db->bind(':status', $dados['status'] ?? 'agendado');
            $this->db->bind(':evento_dia_inteiro', $dados['evento_dia_inteiro'] ?? 'N');
            $this->db->bind(':id', $id);

            return $this->db->executa();
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir evento
     * @param int $id
     * @return bool
     */
    public function excluirEvento($id)
    {
        try {
            $sql = "DELETE FROM {$this->tabela_eventos} WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            return $this->db->executa();
            
        } catch (Exception $e) {
            error_log("Erro ao excluir evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas as categorias ativas
     * @return array
     */
    public function listarCategorias()
    {
        try {
            $sql = "SELECT * FROM {$this->tabela_categorias} WHERE ativo = 'S' ORDER BY nome";
            
            $this->db->query($sql);
            $resultados = $this->db->resultados();
            
            return $resultados;
            
        } catch (Exception $e) {
            error_log("Erro ao listar categorias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar todas as categorias (ativas e inativas)
     * @return array
     */
    public function listarTodasCategorias()
    {
        try {
            $sql = "SELECT * FROM {$this->tabela_categorias} ORDER BY ativo DESC, nome ASC";
            
            $this->db->query($sql);
            $resultados = $this->db->resultados();
            
            return $resultados;
            
        } catch (Exception $e) {
            error_log("Erro ao listar todas as categorias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar categoria por ID
     * @param int $id
     * @return array|false
     */
    public function buscarCategoria($id)
    {
        try {
            $sql = "SELECT * FROM {$this->tabela_categorias} WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            
            return $this->db->resultado();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserir nova categoria
     * @param array $dados
     * @return bool|int
     */
    public function inserirCategoria($dados)
    {
        try {
            $sql = "INSERT INTO {$this->tabela_categorias} (nome, cor, descricao, ativo) 
                    VALUES (:nome, :cor, :descricao, :ativo)";

            $this->db->query($sql);
            $this->db->bind(':nome', $dados['nome']);
            $this->db->bind(':cor', $dados['cor']);
            $this->db->bind(':descricao', $dados['descricao'] ?? null);
            $this->db->bind(':ativo', $dados['ativo'] ?? 'S');

            if ($this->db->executa()) {
                return $this->db->ultimoIdInserido();
            }
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao inserir categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar categoria
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizarCategoria($id, $dados)
    {
        try {
            $sql = "UPDATE {$this->tabela_categorias} SET 
                    nome = :nome, cor = :cor, descricao = :descricao, ativo = :ativo, updated_at = NOW()
                    WHERE id = :id";

            $this->db->query($sql);
            $this->db->bind(':nome', $dados['nome']);
            $this->db->bind(':cor', $dados['cor']);
            $this->db->bind(':descricao', $dados['descricao'] ?? null);
            $this->db->bind(':ativo', $dados['ativo'] ?? 'S');
            $this->db->bind(':id', $id);

            return $this->db->executa();
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir categoria
     * @param int $id
     * @return bool
     */
    public function excluirCategoria($id)
    {
        try {
            $sql = "DELETE FROM {$this->tabela_categorias} WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            return $this->db->executa();
            
        } catch (Exception $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se categoria já existe
     * @param string $nome
     * @param int $excluirId ID para excluir da verificação (para updates)
     * @return bool
     */
    public function verificarCategoriaExistente($nome, $excluirId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->tabela_categorias} WHERE nome = :nome";
            
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
            }

            $this->db->query($sql);
            $this->db->bind(':nome', $nome);
            
            if ($excluirId) {
                $this->db->bind(':excluir_id', $excluirId);
            }

            $resultado = $this->db->resultado();
            return $resultado->total > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar categoria existente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Contar eventos por categoria
     * @param int $categoria_id
     * @return int
     */
    public function contarEventosPorCategoria($categoria_id)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->tabela_eventos} WHERE categoria_id = :categoria_id";
            $this->db->query($sql);
            $this->db->bind(':categoria_id', $categoria_id);
            
            $resultado = $this->db->resultado();
            return $resultado->total ?? 0;
            
        } catch (Exception $e) {
            error_log("Erro ao contar eventos por categoria: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Listar eventos por período e usuário
     * @param string $data_inicio
     * @param string $data_fim
     * @param int $usuario_id
     * @return array
     */
    public function listarEventosPorPeriodo($data_inicio, $data_fim, $usuario_id = null)
    {
        try {
            $sql = "SELECT 
                        e.*,
                        c.nome as categoria_nome,
                        c.cor as categoria_cor,
                        u.nome as usuario_nome
                    FROM {$this->tabela_eventos} e
                    INNER JOIN {$this->tabela_categorias} c ON e.categoria_id = c.id
                    LEFT JOIN usuarios u ON e.usuario_id = u.id
                    WHERE (e.data_inicio BETWEEN :data_inicio AND :data_fim OR e.data_fim BETWEEN :data_inicio2 AND :data_fim2)";

            if ($usuario_id) {
                $sql .= " AND e.usuario_id = :usuario_id";
            }

            $sql .= " ORDER BY e.data_inicio ASC";

            $this->db->query($sql);
            $this->db->bind(':data_inicio', $data_inicio);
            $this->db->bind(':data_fim', $data_fim);
            $this->db->bind(':data_inicio2', $data_inicio);
            $this->db->bind(':data_fim2', $data_fim);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            return $this->db->resultados();
            
        } catch (Exception $e) {
            error_log("Erro ao listar eventos por período: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar eventos por status
     * @param int $usuario_id
     * @return array
     */
    public function contarEventosPorStatus($usuario_id = null)
    {
        try {
            $sql = "SELECT status, COUNT(*) as total FROM {$this->tabela_eventos}";
            
            if ($usuario_id) {
                $sql .= " WHERE usuario_id = :usuario_id";
            }
            
            $sql .= " GROUP BY status";

            $this->db->query($sql);
            
            if ($usuario_id) {
                $this->db->bind(':usuario_id', $usuario_id);
            }

            return $this->db->resultados();
            
        } catch (Exception $e) {
            error_log("Erro ao contar eventos por status: " . $e->getMessage());
            return [];
        }
    }
} 