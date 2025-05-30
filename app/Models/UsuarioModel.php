<?php

/**
 * [ USUARIONMODEL ] - Model responsável por gerenciar os usuários do sistema
 * 
 * Esta classe lida com a persistência e recuperação de dados relacionados aos usuários,
 * incluindo a busca por ID, atualização de dados e gerenciamento de permissões.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025-2025 TJGO
 * @version 1.0.1
 * @access protected
 */     
class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ buscarModulosUsuario ] - Método responsável por buscar os módulos do usuário
     * 
     * Este método consulta o banco de dados para obter os módulos associados a um determinado usuário
     * identificado pelo ID fornecido.
     *   
     * @param int $usuario_id ID do usuário para o qual os módulos serão buscados
     * @return array Array contendo os módulos associados ao usuário
     */ 
    public function buscarModulosUsuario($usuario_id)
    {
        $sql = "SELECT m.id, m.nome, m.rota, m.icone, m.pai_id
                FROM modulos m
                JOIN permissoes_usuario pu ON m.id = pu.modulo_id
                WHERE pu.usuario_id = :usuario_id AND m.status = 'ativo'
                ORDER BY m.nome";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);

        return $this->db->resultados(); // Retorna um array com os módulos ativos do usuário
    }

    /**
     * Verifica se um email já existe no banco de dados
     * 
     * @param string $email Email a ser verificado
     * @return bool True se o email existir, False caso contrário
     */
    public function checarEmail($email)
    {
        $this->db->query("SELECT email FROM usuarios WHERE email = :e");
        $this->db->bind(":e", $email);

        if ($this->db->resultado()) :
            return true;
        else :
            return false;
        endif;
    }

    public function armazenar($dados)
    {
        $this->db->query("INSERT INTO usuarios(nome, email, senha, perfil, biografia, status) VALUES (:nome, :email, :senha, :perfil, :biografia, :status)");

        $this->db->bind("nome", $dados['nome']);
        $this->db->bind("email", $dados['email']);
        $this->db->bind("senha", $dados['senha']);
        $this->db->bind("perfil", $dados['perfil'] ?? 'usuario');
        $this->db->bind("biografia", $dados['biografia'] ?? null);
        $this->db->bind("status", $dados['status'] ?? 'ativo');

        if ($this->db->executa()) :
            return true;
        else :
            return false;
        endif;
    }

    /**
     * [ atualizar ] - Método responsável por atualizar um usuário no banco de dados
     * 
     * Este método consulta o banco de dados para atualizar um usuário existente com os dados fornecidos.
     * 
     * @param array $dados Array contendo os dados a serem atualizados
     * @return bool True se a atualização foi realizada com sucesso, False caso contrário
     */
    public function atualizar($dados)
    {
        $this->db->query("UPDATE usuarios SET 
            nome = :nome, 
            email = :email, 
            senha = :senha,
            perfil = :perfil,
            biografia = :biografia,
            status = :status,
            atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :id");

        $this->db->bind("id", $dados['id']);
        $this->db->bind("nome", $dados['nome']);
        $this->db->bind("email", $dados['email']);
        $this->db->bind("senha", $dados['senha']);
        $this->db->bind("perfil", $dados['perfil']);
        $this->db->bind("biografia", $dados['biografia']);
        $this->db->bind("status", $dados['status']);

        if ($this->db->executa()) :
            return true;
        else :
            return false;
        endif;
    }
    /**
     * //deleta o o formulario no banco de dados por seu ID
     *
     * @param int $id
     * @return boolean
     */
    public function destruir($id)
    {
        $this->db->query("DELETE FROM usuarios WHERE id = :id");
        $this->db->bind("id", $id);

        if ($this->db->executa()):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * [ lerUsuarioPorId ] - Método responsável por buscar um usuário pelo ID
     * 
     * Este método consulta o banco de dados para obter um usuário associado ao ID fornecido.
     * 
     * @param int $id ID do usuário a ser buscado
     * @return object|false Retorna o objeto usuário se encontrado, False caso contrário
     */
    public function lerUsuarioPorId($id)
    {
        $this->db->query("SELECT * FROM usuarios WHERE id = :id");
        $this->db->bind('id', $id);

        return $this->db->resultado();
    }

    /**
     * [ lerUsuarios ] - Método responsável por buscar todos os usuários ordenados por nome
     * 
     * Este método consulta o banco de dados para obter todos os usuários armazenados na tabela 'cuc_usuarios'
     * e retorna os resultados ordenados alfabeticamente pelo campo 'nome'.
     * 
     * @param int $pagina Página a ser exibida
     * @param int $usuariosPorPagina Número de usuários por página
     * @param string $filtro Filtro para a busca
     * @param string $status Status do usuário
     * @param string $perfil Perfil do usuário
     * @return array Array contendo todos os usuários ordenados por nome
     */ 

    public function lerUsuarios($pagina = 1, $usuariosPorPagina = 10, $filtro = '', $status = null, $perfil = null)
    {
        $offset = ($pagina - 1) * $usuariosPorPagina;
        $sql = "SELECT * FROM usuarios WHERE (nome LIKE :filtro_nome OR email LIKE :filtro_email)";

        if ($status) {
            $sql .= " AND status = :status";
        }
        if ($perfil) {
            $sql .= " AND perfil = :perfil";
        }

        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(":filtro_nome", "%$filtro%");
        $this->db->bind(":filtro_email", "%$filtro%");

        if ($status) {
            $this->db->bind(":status", $status);
        }
        if ($perfil) {
            $this->db->bind(":perfil", $perfil);
        }

        $this->db->bind(":offset", $offset, PDO::PARAM_INT);
        $this->db->bind(":limit", $usuariosPorPagina, PDO::PARAM_INT);
        return $this->db->resultados();
    }

    /**
     * [ atualizarUltimoAcesso ] - Método responsável por atualizar o último acesso do usuário
     * 
     * Este método consulta o banco de dados para atualizar o último acesso do usuário
     * identificado pelo ID fornecido.
     * 
     * @param int $id ID do usuário a ser atualizado    
     * @return bool True se a atualização foi realizada com sucesso, False caso contrário
     */
    public function atualizarUltimoAcesso($id)
    {
        $this->db->query("UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind("id", $id);
        return $this->db->executa();
    }

    /**
     * [ verificarPerfil ] - Método responsável por verificar se o usuário tem permissão para acessar um determinado perfil
     * 
     * Este método consulta o banco de dados para verificar se o usuário tem permissão para acessar um determinado perfil.
     * 
     * @param int $id ID do usuário a ser verificado
     * @param string $perfilMinimo Perfil mínimo a ser verificado
     * @return bool True se o usuário tem permissão, False caso contrário
     */
    public function verificarPerfil($id, $perfilMinimo)
    {
        $this->db->query("SELECT perfil FROM usuarios WHERE id = :id");
        $this->db->bind("id", $id);
        $usuario = $this->db->resultado();

        if (!$usuario) {
            return false;
        }

        $niveis = ['usuario' => 1, 'analista' => 2, 'admin' => 3];
        return $niveis[$usuario->perfil] >= $niveis[$perfilMinimo];
    }

    /**
     * [ contarUsuarios ] - Método responsável por contar o número de usuários
     * 
     * Este método consulta o banco de dados para obter o número de usuários associados a um determinado filtro.
     *   
     * @param string $filtro Filtro a ser contado
     * @return int Número de usuários associados ao filtro
     */
    public function contarUsuarios($filtro = '')
    {
        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE nome LIKE :filtro_nome OR email LIKE :filtro_email");
        $this->db->bind(":filtro_nome", "%$filtro%");
        $this->db->bind(":filtro_email", "%$filtro%");
        return $this->db->resultado()->total;
    }

    /**
     * [ contarUsuariosPorStatus ] - Método responsável por contar o número de usuários por status
     * 
     * Este método consulta o banco de dados para obter o número de usuários associados a um determinado status.
     * 
     * @param string $status Status a ser contado
     * @return int Número de usuários associados ao status
     */
    public function contarUsuariosPorStatus($status)
    {
        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE status = :status");
        $this->db->bind(":status", $status);
        return $this->db->resultado()->total;
    }

    /**
     * [ contarUsuariosPorPerfil ] - Método responsável por contar o número de usuários por perfil
     * 
     * Este método consulta o banco de dados para obter o número de usuários associados a um determinado perfil.
     * 
     * @param string $perfil Perfil a ser contado
     * @return int Número de usuários associados ao perfil
     */
    public function contarUsuariosPorPerfil($perfil)
    {
        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE perfil = :perfil");
        $this->db->bind(":perfil", $perfil);
        return $this->db->resultado()->total;
    }

    /**
     * [ getUltimasAtividades ] - Método responsável por buscar as últimas atividades dos usuários
     * 
     * Este método consulta o banco de dados para obter as últimas atividades realizadas pelos usuários
     * identificando os usuários que acessaram o sistema no dia atual.
     *  
     * @return int Número de acessos hoje
     */
    public function getUltimasAtividades($limite = 5)
    {
        $this->db->query("SELECT u.nome, u.perfil, u.ultimo_acesso 
                          FROM usuarios u 
                         WHERE u.ultimo_acesso IS NOT NULL 
                         ORDER BY u.ultimo_acesso DESC 
                         LIMIT :limite");
        $this->db->bind(":limite", $limite, PDO::PARAM_INT);
        return $this->db->resultados();
    }

    /**
     * [ getAcessosHoje ] - Método responsável por contar o número de acessos hoje
     * 
     * Este método consulta o banco de dados para obter o número de acessos realizados hoje
     * identificando os usuários que acessaram o sistema no dia atual.
     *  
     * @return int Número de acessos hoje
     */
    public function getAcessosHoje()
    {
        $this->db->query("SELECT COUNT(DISTINCT id) as total 
                         FROM usuarios 
                         WHERE DATE(ultimo_acesso) = CURRENT_DATE");
        return $this->db->resultado()->total;
    }

    /** 
     * [ lerTodosUsuarios ] - Método responsável por buscar todos os usuários ordenados por nome
     * Retorna todos os usuários armazenados na tabela 'cuc_usuarios' para gerar o pdf
     * Este método consulta o banco de dados para obter todos os usuários armazenados na tabela 'cuc_usuarios'
     * e retorna os resultados ordenados alfabeticamente pelo campo 'nome'.
     * 
     * @return array Array contendo todos os usuários ordenados por nome
     */

    public function lerTodosUsuarios()
    {
        $this->db->query("SELECT * FROM usuarios ORDER BY nome ASC");
        return $this->db->resultados();
    }

    /**
     * [ gerenciarPermissoes ] - Método responsável por gerenciar as permissões do usuário
     * 
     * Este método remove todas as permissões existentes para um usuário e insere novas permissões
     * associadas aos módulos especificados.
     * 
     * @param int $usuario_id ID do usuário para o qual as permissões serão gerenciadas
     * @param array $modulos_ids IDs dos módulos para os quais as permissões serão atribuídas
     * @return bool True se as permissões foram gerenciadas com sucesso, False caso contrário
     */
    public function gerenciarPermissoes($usuario_id, $modulos_ids)
    {
        // Primeiro remove todas as permissões existentes
        $this->db->query("DELETE FROM permissoes_usuario WHERE usuario_id = :usuario_id");
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->executa();

        // Insere as novas permissões
        if (!empty($modulos_ids)) {
            $valores = array_map(function ($modulo_id) use ($usuario_id) {
                return "($usuario_id, $modulo_id)";
            }, $modulos_ids);

            $sql = "INSERT INTO permissoes_usuario (usuario_id, modulo_id) VALUES " . implode(',', $valores);
            $this->db->query($sql);
            return $this->db->executa();
        }

        return true;
    }

    /**
     * [ getPermissoesUsuario ] - Método responsável por buscar as permissões do usuário
     * 
     * Este método consulta o banco de dados para obter as permissões associadas a um determinado usuário
     * identificado pelo ID fornecido.
     * 
     * @param int $usuario_id ID do usuário para o qual as permissões serão buscadas
     * @return array Array contendo os IDs dos módulos associados ao usuário
     */
    public function getPermissoesUsuario($usuario_id)
    {
        $this->db->query("SELECT modulo_id FROM permissoes_usuario WHERE usuario_id = :usuario_id");
        $this->db->bind(':usuario_id', $usuario_id);

        $resultados = $this->db->resultados();
        return array_map(function ($item) {
            return $item->modulo_id;
        }, $resultados);
    }

    /**
     * [ buscarUsuariosComPermissao ] - Busca usuários que têm acesso a um módulo específico
     * 
     * @param int $modulo_id ID do módulo
     * @return array Lista de usuários com permissão
     */
    public function buscarUsuariosComPermissao($modulo_id) {
        try {
            $this->db->query("
                SELECT DISTINCT u.* 
                FROM usuarios u 
                INNER JOIN permissoes_usuario up ON u.id = up.usuario_id 
                WHERE up.modulo_id = :modulo_id 
                AND u.status = 'ativo'
                ORDER BY u.nome ASC
            ");
            
            $this->db->bind(':modulo_id', $modulo_id);
            return $this->db->resultados();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * [ listarUsuarios ] - Método responsável por listar todos os usuários ativos
     * 
     * Este método consulta o banco de dados para obter todos os usuários ativos
     * ordenados por nome.
     * 
     * @return array Array contendo todos os usuários ativos ordenados por nome
     */
    public function listarUsuarios()
    {
        $this->db->query("SELECT id, nome, perfil, status 
                          FROM usuarios 
                          WHERE status = 'ativo'
                          ORDER BY nome ASC");
        return $this->db->resultados();
    }

    /**
     * [ buscarUsuarioPorId ] - Método responsável por buscar um usuário pelo ID
     * 
     * Este método consulta o banco de dados para obter os dados de um usuário específico
     * com base no ID fornecido.
     * 
     * @param int $id ID do usuário a ser buscado
     * @return object Dados do usuário encontrado ou null se não encontrar
     */
    public function buscarUsuarioPorId($id) {
        $this->db->query("SELECT * FROM usuarios WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

}
