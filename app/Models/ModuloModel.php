<?php

/**
 * [ MODULOMODEL ] - Modelo para gerenciar módulos e submódulos do sistema
 * 
 * Esta classe é responsável por todas as operações relacionadas aos módulos do sistema,
 * incluindo gerenciamento de permissões, hierarquia de módulos e submódulos.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025-2025 TJGO
 * @version 1.0.1
 * @access protected
 */ 
class ModuloModel {
    /** @var Database Instância da conexão com o banco de dados */
    private $db;

    /**
     * Construtor da classe
     * Inicializa a conexão com o banco de dados
     */
    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Obtém os módulos e seus submódulos ativos, considerando as permissões do usuário
     * 
     * @param int|null $usuario_id ID do usuário para verificar permissões. Se null, retorna todos os módulos
     * @return array Array associativo com os módulos e seus submódulos organizados hierarquicamente
     */
    public function getModulosComSubmodulos($usuario_id = null) {
        $sql = "
            WITH modulos_ordenados AS (
                SELECT DISTINCT m.*,
                       CASE WHEN m.pai_id IS NULL THEN 1 ELSE 0 END as is_pai
                FROM modulos m
                WHERE (
                    EXISTS (
                        SELECT 1 
                        FROM permissoes_usuario pu 
                        WHERE pu.modulo_id = m.id 
                        AND pu.usuario_id = :usuario_id1
                    )
                    OR EXISTS (
                        SELECT 1 
                        FROM usuarios u 
                        WHERE u.id = :usuario_id2 
                        AND u.perfil = 'admin'
                    )
                    OR :usuario_id3 IS NULL
                )
            )
            SELECT *
            FROM modulos_ordenados
            ORDER BY is_pai DESC, nome
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id1', $usuario_id);
        $this->db->bind(':usuario_id2', $usuario_id);
        $this->db->bind(':usuario_id3', $usuario_id);
        $resultados = $this->db->resultados();

        // Inicializa array para armazenar módulos
        $modulos = [];

        // Agrupa os resultados por módulo
        foreach ($resultados as $modulo) {
            if (!$modulo->pai_id) {
                $modulos[$modulo->id] = [
                    'id' => $modulo->id,
                    'nome' => $modulo->nome,
                    'rota' => $modulo->rota,
                    'icone' => $modulo->icone,
                    'status' => $modulo->status,
                    'pai_id' => $modulo->pai_id,
                    'submodulos' => []
                ];
            } else {
                if (isset($modulos[$modulo->pai_id])) {
                    $modulos[$modulo->pai_id]['submodulos'][] = [
                        'id' => $modulo->id,
                        'nome' => $modulo->nome,
                        'rota' => $modulo->rota,
                        'icone' => $modulo->icone,
                        'status' => $modulo->status,
                        'pai_id' => $modulo->pai_id
                    ];
                }
            }
        }

        return $modulos;
    }

    /**
     * Verifica se um usuário tem permissão para acessar um módulo específico
     * 
     * @param int $usuario_id ID do usuário
     * @param int $modulo_id ID do módulo
     * @return bool True se tem permissão, False caso contrário
     */
    public function verificarPermissao($usuario_id, $modulo_id) {
        $sql = "
            SELECT 1 
            FROM permissoes_usuario pu
            JOIN usuarios u ON u.id = pu.usuario_id
            WHERE (pu.usuario_id = :usuario_id AND pu.modulo_id = :modulo_id)
            OR u.perfil = 'admin'
        ";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':modulo_id', $modulo_id);
        
        return (bool)$this->db->resultado();
    }

    /**
     * Cadastra um novo módulo no sistema
     * 
     * @param array $dados Array contendo os dados do módulo
     * @return bool True se o cadastro foi bem sucedido, False caso contrário
     */
    public function armazenar($dados)
    {
        $sql = "INSERT INTO modulos (
            nome, 
            descricao, 
            rota, 
            icone, 
            status, 
            criado_em,
            pai_id
        ) VALUES (
            :nome, 
            :descricao, 
            :rota, 
            :icone, 
            :status, 
            NOW(),
            :pai_id
        )";

        $this->db->query($sql);

        // Bind dos valores
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao'] ?? 'Módulo do sistema');
        $this->db->bind(':rota', $dados['rota']);
        $this->db->bind(':icone', $dados['icone']);
        $this->db->bind(':status', 'ativo');
        $this->db->bind(':pai_id', empty($dados['pai_id']) ? null : $dados['pai_id']);

        return $this->db->executa();
    }

    /**
     * Retorna todos os módulos que podem ser módulos pai
     * 
     * @return array Lista de módulos que podem ser pai
     */
    public function getModulosPai()
    {
        $this->db->query("SELECT id, nome FROM modulos WHERE pai_id IS NULL AND status = 'ativo' ORDER BY nome");
        return $this->db->resultados();
    }

    public function excluir($id)
    {
        // Primeiro verifica se existem submódulos
        $this->db->query("SELECT COUNT(*) as total FROM modulos WHERE pai_id = :id");
        $this->db->bind(':id', $id);
        $resultado = $this->db->resultado();

        if ($resultado->total > 0) {
            return false; // Não pode excluir módulo com submódulos
        }

        // Exclui as permissões relacionadas
        $this->db->query("DELETE FROM permissoes_usuario WHERE modulo_id = :id");
        $this->db->bind(':id', $id);
        $this->db->executa();

        // Exclui o módulo
        $this->db->query("DELETE FROM modulos WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    public function lerModuloPorId($id)
    {
        $this->db->query("SELECT * FROM modulos WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    public function atualizar($dados)
    {
        $sql = "UPDATE modulos SET 
                nome = :nome,
                descricao = :descricao,
                rota = :rota,
                icone = :icone,
                pai_id = :pai_id,
                status = :status,
                atualizado_em = NOW()
                WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao'] ?? 'Módulo do sistema');
        $this->db->bind(':rota', $dados['rota']);
        $this->db->bind(':icone', $dados['icone']);
        $this->db->bind(':pai_id', empty($dados['pai_id']) ? null : $dados['pai_id']);
        $this->db->bind(':status', $dados['status']);

        return $this->db->executa();
    }
}
