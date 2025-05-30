<?php

class GuiaPagamentoModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function cadastrarGuia($dados) {
        $this->db->query("INSERT INTO cuc_guias_pagamento (
            processo_id, numero_guia, valor, data_vencimento, 
            status, observacao, usuario_cadastro, parte_id
        ) VALUES (
            :processo_id, :numero_guia, :valor, :data_vencimento, 
            :status, :observacao, :usuario_cadastro, :parte_id
        )");

        $this->db->bind(':processo_id', $dados['processo_id']);
        $this->db->bind(':numero_guia', $dados['numero_guia']);
        $this->db->bind(':valor', $dados['valor']);
        $this->db->bind(':data_vencimento', $dados['data_vencimento']);
        $this->db->bind(':status', $dados['status']);
        $this->db->bind(':observacao', $dados['observacao']);
        $this->db->bind(':usuario_cadastro', $_SESSION['usuario_id']);
        $this->db->bind(':parte_id', $dados['parte_id']);

        return $this->db->executa();
    }

    /**
     * [ atualizarStatusGuia ] - Atualiza o status de uma guia de pagamento
     * 
     * @param int $id_guia ID da guia
     * @param string $status Status da guia (pago, pendente, etc)
     * @param string $data_pagamento Data de pagamento (pode ser null)
     * @return bool Sucesso da operação
     */
    public function atualizarStatusGuia($id_guia, $status, $data_pagamento = null)
    {
        // Verifica se o status tem mais de 20 caracteres e trunca se necessário
        if (strlen($status) > 20) {
            $status = substr($status, 0, 20);
        }
        
        $sql = "UPDATE cuc_guias_pagamento SET 
                status = :status,
                data_pagamento = :data_pagamento
                WHERE id = :id_guia";
        
        $this->db->query($sql);
        $this->db->bind(':id_guia', $id_guia);
        $this->db->bind(':status', $status);
        
        // Trata o caso de data_pagamento ser null
        if ($data_pagamento === null) {
            $this->db->bind(':data_pagamento', null, PDO::PARAM_NULL);
        } else {
            $this->db->bind(':data_pagamento', $data_pagamento);
        }
        
        return $this->db->executa();
    }

    public function listarGuiasProcesso($processo_id) {
        $this->db->query("SELECT g.*, 
                         u.nome as usuario_nome,
                         p.nome as parte_nome,
                         p.documento as parte_documento,
                         p.tipo as parte_tipo
                         FROM cuc_guias_pagamento g
                         JOIN cuc_usuarios u ON g.usuario_cadastro = u.id
                         LEFT JOIN cuc_partes p ON g.parte_id = p.id
                         WHERE g.processo_id = :processo_id
                         ORDER BY g.data_cadastro DESC");

        $this->db->bind(':processo_id', $processo_id);
        return $this->db->resultados();
    }

 
    /**
     * [ excluirGuia ] - Exclui uma guia de pagamento
     * 
     * @param int $guia_id ID da guia
     * @return bool True se a exclusão foi realizada com sucesso, false caso contrário
     */
    public function excluirGuia($guia_id) {
        $this->db->query("DELETE FROM cuc_guias_pagamento WHERE id = :id");
        $this->db->bind(':id', $guia_id);
        return $this->db->executa();
    }

    /**
     * [ buscarGuiaPorId ] - Busca uma guia de pagamento pelo ID
     * 
     * @param int $guia_id ID da guia
     * @return array|false Array com os dados da guia ou false se não encontrada
     */
    public function buscarGuiaPorId($guia_id) {
        $this->db->query("SELECT g.*, 
                         p.nome as parte_nome,
                         p.documento as parte_documento,
                         p.tipo as parte_tipo
                         FROM cuc_guias_pagamento g
                         LEFT JOIN cuc_partes p ON g.parte_id = p.id
                         WHERE g.id = :id");
        $this->db->bind(':id', $guia_id);
        return $this->db->resultado();
    }

    /**
     * [ atualizarGuia ] - Atualiza uma guia de pagamento
     * 
     * @param int $guia_id ID da guia
     * @param array $dados Array com os dados da guia
     * @return bool True se a atualização foi realizada com sucesso, false caso contrário
     */
    public function atualizarGuia($dados) {
        // Tratamento para datas vazias
        $data_vencimento = !empty($dados['data_vencimento']) ? $dados['data_vencimento'] : null;
        $data_pagamento = !empty($dados['data_pagamento']) ? $dados['data_pagamento'] : null;

        $this->db->query("UPDATE cuc_guias_pagamento 
                        SET numero_guia = :numero_guia, 
                            valor = :valor,     
                            data_vencimento = :data_vencimento,
                            status = :status,
                            data_pagamento = :data_pagamento,
                            observacao = :observacao,
                            parte_id = :parte_id
                        WHERE id = :id");

        $this->db->bind(':numero_guia', $dados['numero_guia']);
        $this->db->bind(':valor', $dados['valor']);
        $this->db->bind(':data_vencimento', $data_vencimento);
        $this->db->bind(':status', $dados['status']);
        $this->db->bind(':data_pagamento', $data_pagamento);
        $this->db->bind(':observacao', $dados['observacao']);
        $this->db->bind(':parte_id', $dados['parte_id']);
        $this->db->bind(':id', $dados['id']);

        return $this->db->executa();
    }

    /**
     * [ buscarGuiasPorParteId ] - Busca todas as guias vinculadas a uma parte
     * 
     * @param int $parte_id ID da parte
     * @return array|false Array com as guias ou false se não encontradas
     */
    public function buscarGuiasPorParteId($parte_id) {
        $this->db->query("SELECT * FROM cuc_guias_pagamento WHERE parte_id = :parte_id");
        $this->db->bind(':parte_id', $parte_id);
        return $this->db->resultados();
    }

    /**
     * [ excluirGuiasPorProcessoId ] - Exclui todas as guias de um processo
     * 
     * @param int $processo_id ID do processo
     * @return bool True se excluído com sucesso, false caso contrário
     */
    public function excluirGuiasPorProcessoId($processo_id) {
        $this->db->query("DELETE FROM cuc_guias_pagamento WHERE processo_id = :processo_id");
        $this->db->bind(':processo_id', $processo_id);
        return $this->db->executa();
    }

} 