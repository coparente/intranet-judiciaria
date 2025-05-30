<?php

/**
 * [ ParteModel ] - Model responsável por gerenciar as partes dos processos
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 */
class ParteModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Cadastra uma nova parte no processo
     * 
     * @param array $dados Dados da parte
     * @return bool True se cadastrou com sucesso, false caso contrário
     */
    public function cadastrarParte($dados) {
        $this->db->query("INSERT INTO cuc_partes (
            processo_id, tipo, nome, documento, tipo_documento,
            telefone, email, endereco, usuario_cadastro
        ) VALUES (
            :processo_id, :tipo, :nome, :documento, :tipo_documento,
            :telefone, :email, :endereco, :usuario_cadastro
        )");

        $this->db->bind(':processo_id', $dados['processo_id']);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':documento', $dados['documento']);
        $this->db->bind(':tipo_documento', $dados['tipo_documento']);
        $this->db->bind(':telefone', $dados['telefone']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':endereco', $dados['endereco']);
        $this->db->bind(':usuario_cadastro', $_SESSION['usuario_id']);

        return $this->db->executa();
    }

    /**
     * Lista todas as partes de um processo
     * 
     * @param int $processo_id ID do processo
     * @return array Lista de partes
     */
    public function listarPartesProcesso($processo_id) {
        $this->db->query("SELECT p.*, u.nome as usuario_cadastro_nome 
                         FROM cuc_partes p
                         JOIN cuc_usuarios u ON p.usuario_cadastro = u.id
                         WHERE p.processo_id = :processo_id
                         ORDER BY p.data_cadastro DESC");

        $this->db->bind(':processo_id', $processo_id);
        return $this->db->resultados();
    }

    /**
     * Atualiza os dados de uma parte
     * 
     * @param array $dados Dados da parte
     * @return bool True se atualizou com sucesso, false caso contrário
     */
    public function atualizarParte($dados) {
        $this->db->query("UPDATE cuc_partes SET 
            tipo = :tipo,
            nome = :nome,
            documento = :documento,
            tipo_documento = :tipo_documento,
            telefone = :telefone,
            email = :email,
            endereco = :endereco,
            data_atualizacao = NOW()
            WHERE id = :id");

        $this->db->bind(':id', $dados['id']);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':documento', $dados['documento']);
        $this->db->bind(':tipo_documento', $dados['tipo_documento']);
        $this->db->bind(':telefone', $dados['telefone']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':endereco', $dados['endereco']);

        return $this->db->executa();
    }

    /**
     * Exclui uma parte
     * 
     * @param int $id ID da parte
     * @return bool True se excluiu com sucesso, false caso contrário
     */
    public function excluirParte($id) {
        $this->db->query("DELETE FROM cuc_partes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * Busca uma parte pelo ID
     * 
     * @param int $id ID da parte
     * @return object|false Retorna a parte ou false se não encontrar
     */
    public function buscarPartePorId($id) {
        $this->db->query("SELECT * FROM cuc_partes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ excluirPartesPorProcessoId ] - Exclui todas as partes de um processo
     * 
     * @param int $processo_id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirPartesPorProcessoId($processo_id) {
        $this->db->query("DELETE FROM cuc_partes WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->executa();
    }
} 