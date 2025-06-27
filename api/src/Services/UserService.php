<?php 

namespace App\Services;

use App\Http\JWT;
use App\Utils\Validator;
use Exception;
use PDOException;
use App\Models\User;

/**
 * Classe responsável pelos serviços relacionados aos usuários, como criação, autenticação,
 * atualização e exclusão de usuários, além da verificação de autorização com JWT.
 */
class UserService
{

    public static function getAll()
    {
        return User::getAll(); // Chama o método getAll do modelo User
    }
    /**
     * Cria um novo usuário no sistema.
     * 
     * @param array $data Dados do usuário, como nome, e-mail e senha.
     * 
     * @return mixed Retorna uma mensagem de sucesso ou um array de erro.
     */
    public static function create(array $data)
    {
        try {
            // Validação dos campos obrigatórios
            $fields = Validator::validate([
                'name'     => $data['name']     ?? '',
                'email'    => $data['email']    ?? '',
                'password' => $data['password'] ?? '',
            ]);

            // Criptografa a senha
            $fields['password'] = password_hash($fields['password'], PASSWORD_DEFAULT);

            // Salva o novo usuário
            $user = User::save($fields);

            if (!$user) {
                return ['error' => 'Desculpe, não foi possível criar sua conta.'];
            }

            return "Usuário criado com sucesso!";

        } catch (PDOException $e) {
            // Erros de conexão e duplicidade de usuário
            if ($e->errorInfo[0] === '08006') {
                return ['error' => 'Desculpe, não conseguimos conectar ao banco de dados.'];
            }
            if ($e->errorInfo[0] === '23505') {
                return ['error' => 'Desculpe, o usuário já existe.'];
            }
            return ['error' => $e->errorInfo[0]];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Autentica um usuário e gera um token JWT.
     * 
     * @param array $data Dados de autenticação (e-mail e senha).
     * 
     * @return mixed Retorna o token JWT ou um array de erro.
     */
    public static function auth(array $data)
    {
        try {
            // Validação dos campos de autenticação
            $fields = Validator::validate([
                'email'    => $data['email']    ?? '',
                'password' => $data['password'] ?? '',
            ]);

            // Autenticação do usuário
            $user = User::authentication($fields);

            if (!$user) {
                return ['error'=> 'Desculpe, não foi possível autenticar você.'];
            }

            // Gera o token JWT
            return JWT::generate($user);
        } catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') {
                return ['error' => 'Desculpe, não conseguimos conectar ao banco de dados.'];
            }
            return ['error' => $e->errorInfo[0]];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Busca um usuário autenticado com base no token JWT.
     * 
     * @param mixed $authorization Token JWT para autorização.
     * 
     * @return mixed Retorna os dados do usuário ou uma mensagem de erro.
     */
    public static function fetch($authorization)
    {
        try {
            // Verifica se há erros de autorização
            if (isset($authorization['error'])) {
                return ['unauthorized'=> $authorization['error']];
            }

            // Verifica o token JWT
            $userFromJWT = JWT::verify($authorization);

            if (!$userFromJWT) {
                return ['unauthorized'=> "Por favor, faça login para acessar este recurso."];
            }

            // Busca o usuário pelo ID contido no JWT
            $user = User::find($userFromJWT['id']);

            if (!$user) {
                return ['error'=> 'Desculpe, não conseguimos encontrar sua conta.'];
            }

            return $user;
        } catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') {
                return ['error' => 'Desculpe, não conseguimos conectar ao banco de dados.'];
            }
            return ['error' => $e->errorInfo[0]];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Atualiza os dados do usuário autenticado.
     * 
     * @param mixed $authorization Token JWT para autorização.
     * @param array $data Dados de atualização do usuário.
     * 
     * @return mixed Retorna uma mensagem de sucesso ou um array de erro.
     */
    public static function update($authorization, array $data)
    {
        try {
            // Verifica se há erros de autorização
            if (isset($authorization['error'])) {
                return ['unauthorized'=> $authorization['error']];
            }

            // Verifica o token JWT
            $userFromJWT = JWT::verify($authorization);

            if (!$userFromJWT) {
                return ['unauthorized'=> "Por favor, faça login para acessar este recurso."];
            }

            // Validação dos campos de atualização
            $fields = Validator::validate([
                'name' => $data['name'] ?? ''
            ]);

            // Atualiza o usuário com os novos dados
            $user = User::update($userFromJWT['id'], $fields);

            if (!$user) {
                return ['error'=> 'Desculpe, não foi possível atualizar sua conta.'];
            }

            return "Usuário atualizado com sucesso!";
        } catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') {
                return ['error' => 'Desculpe, não conseguimos conectar ao banco de dados.'];
            }
            return ['error' => $e->getMessage()];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Deleta um usuário com base no ID fornecido.
     * 
     * @param mixed $authorization Token JWT para autorização.
     * @param int|string $id ID do usuário a ser deletado.
     * 
     * @return mixed Retorna uma mensagem de sucesso ou um array de erro.
     */
    public static function delete($authorization, $id)
    {
        try {
            // Verifica se há erros de autorização
            if (isset($authorization['error'])) {
                return ['unauthorized'=> $authorization['error']];
            }

            // Verifica o token JWT
            $userFromJWT = JWT::verify($authorization);

            if (!$userFromJWT) {
                return ['unauthorized'=> "Por favor, faça login para acessar este recurso."];
            }

            // Deleta o usuário pelo ID
            $user = User::delete($id);

            if (!$user) {
                return ['error'=> 'Desculpe, não foi possível deletar sua conta.'];
            }

            return "Usuário deletado com sucesso!";
        } catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') {
                return ['error' => 'Desculpe, não conseguimos conectar ao banco de dados.'];
            }
            return ['error' => $e->getMessage()];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
}
