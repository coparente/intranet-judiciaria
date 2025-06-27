<?php 

namespace App\Models;

use App\Models\Database;
use PDO;

/**
 * Classe que representa a entidade User e fornece métodos para interagir
 * com a tabela de usuários no banco de dados.
 */
class User extends Database
{
    /**
     * Busca todos os usuários.
     * 
     * @return array Array com todos os usuários
     */
    public static function getAll()
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("SELECT id, nome, email, criado_em FROM usuarios");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Salva um novo usuário no banco de dados.
     * 
     * @param array $data Dados do usuário a serem salvos, incluindo nome, e-mail e senha.
     * 
     * @return bool Retorna true se o usuário foi salvo com sucesso, false caso contrário.
     */
    public static function save(array $data)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, criado_em) VALUES (?, ?, ?, NOW())");

        $stmt->execute([
            $data['nome'],
            $data['email'],
            $data['senha'],
        ]);

        return $pdo->lastInsertId() > 0 ? true : false;
    }

    /**
     * Autentica um usuário com base no e-mail e senha fornecidos.
     * 
     * @param array $data Dados de autenticação, incluindo e-mail e senha.
     * 
     * @return mixed Retorna um array com os dados do usuário autenticado ou false se a autenticação falhar.
     */
    public static function authentication(array $data)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");

        $stmt->execute([$data['email']]);

        if ($stmt->rowCount() < 1) {
            return false; // E-mail não encontrado
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se a senha está correta
        if (!password_verify($data['senha'], $user['senha'])) {
            return false; // Senha incorreta
        }

        return [
            'id'   => $user['id'],
            'nome' => $user['nome'],
            'email'=> $user['email'],
        ];
    }

    /**
     * Encontra um usuário pelo ID.
     * 
     * @param int|string $id ID do usuário a ser encontrado.
     * 
     * @return mixed Retorna um array com os dados do usuário ou null se não encontrado.
     */
    public static function find($id)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare('SELECT id, nome, email, criado_em FROM usuarios WHERE id = ?');

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza os dados de um usuário existente.
     * 
     * @param int|string $id ID do usuário a ser atualizado.
     * @param array $data Dados a serem atualizados, incluindo nome, email e senha.
     * 
     * @return bool Retorna true se a atualização foi bem-sucedida, false caso contrário.
     */
    public static function update($id, array $data)
    {
        $pdo = self::getConnection();
        
        $fields = [];
        $values = [];
        
        // Adiciona campos dinamicamente
        if (isset($data['nome'])) {
            $fields[] = 'nome = ?';
            $values[] = $data['nome'];
        }
        
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $values[] = $data['email'];
        }
        
        if (isset($data['senha'])) {
            $fields[] = 'senha = ?';
            $values[] = $data['senha'];
        }
        
        // Adiciona campo de atualização
        $fields[] = 'atualizado_em = NOW()';
        
        // Adiciona o ID no final
        $values[] = $id;
        
        $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);

        $stmt->execute($values);

        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Deleta um usuário com base no ID fornecido.
     * 
     * @param int|string $id ID do usuário a ser deletado.
     * 
     * @return bool Retorna true se o usuário foi deletado com sucesso, false caso contrário.
     */
    public static function delete($id)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Verifica se um e-mail já existe no banco de dados.
     * 
     * @param string $email E-mail a ser verificado
     * @param int|null $excludeId ID do usuário a ser excluído da verificação (para atualizações)
     * @return bool True se o e-mail já existe
     */
    public static function emailExists($email, $excludeId = null)
    {
        $pdo = self::getConnection();

        if ($excludeId) {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
        }

        return $stmt->rowCount() > 0;
    }
}
