<?php 

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Http\JWT;
use App\Models\User;

/**
 * Classe UserController responsável por gerenciar as operações relacionadas a usuários.
 */
class UserController
{
    /**
     * Lista todos os usuários.
     * 
     * @return void
     */
    public function index()
    {
        try {
            $users = User::getAll();

            return Response::json([
                'success' => true,
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Armazena um novo usuário.
     * 
     * @return void
     */
    public function store()
    {
        try {
            $data = Request::getBody();

            // Valida dados obrigatórios
            if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
                return Response::json(['error' => 'Nome, e-mail e senha são obrigatórios'], 400);
            }

            // Valida formato do e-mail
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return Response::json(['error' => 'Formato de e-mail inválido'], 400);
            }

            // Verifica se o e-mail já existe
            if (User::emailExists($data['email'])) {
                return Response::json(['error' => 'E-mail já cadastrado'], 400);
            }

            // Hash da senha
            $senhaHash = password_hash($data['senha'], PASSWORD_DEFAULT);

            $userData = [
                'nome' => $data['nome'],
                'email' => $data['email'],
                'senha_hash' => $senhaHash
            ];

            if (User::save($userData)) {
                return Response::json([
                    'success' => true,
                    'message' => 'Usuário criado com sucesso'
                ], 201);
            } else {
                return Response::json(['error' => 'Erro ao criar usuário'], 500);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Realiza o login de um usuário.
     * 
     * @return void
     */
    public function login()
    {
        try {
            $data = Request::getBody();

            // Valida dados obrigatórios
            if (empty($data['email']) || empty($data['senha'])) {
                return Response::json(['error' => 'E-mail e senha são obrigatórios'], 400);
            }

            $authData = [
                'email' => $data['email'],
                'senha' => $data['senha']
            ];

            $user = User::authentication($authData);

            if (!$user) {
                return Response::json(['error' => 'E-mail ou senha inválidos'], 401);
            }

            // Gera JWT
            $jwt = JWT::generate($user);

            return Response::json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => $user,
                    'token' => $jwt
                ]
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Recupera os dados do usuário autenticado.
     * 
     * @return void
     */
    public function fetch()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $user = User::find($userData['id']);

            if (!$user) {
                return Response::json(['error' => 'Usuário não encontrado'], 404);
            }

            return Response::json([
                'success' => true,
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza os dados de um usuário autenticado.
     * 
     * @return void
     */
    public function update()
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $data = Request::getBody();

            // Valida dados obrigatórios
            if (empty($data['nome'])) {
                return Response::json(['error' => 'Nome é obrigatório'], 400);
            }

            $updateData = [
                'nome' => $data['nome']
            ];

            // Se email foi fornecido, valida e adiciona
            if (!empty($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    return Response::json(['error' => 'Formato de e-mail inválido'], 400);
                }

                // Verifica se o e-mail já existe (excluindo o usuário atual)
                if (User::emailExists($data['email'], $userData['id'])) {
                    return Response::json(['error' => 'E-mail já cadastrado'], 400);
                }

                $updateData['email'] = $data['email'];
            }

            // Se senha foi fornecida, adiciona o hash
            if (!empty($data['senha'])) {
                if (strlen($data['senha']) < 6) {
                    return Response::json(['error' => 'Senha deve ter pelo menos 6 caracteres'], 400);
                }
                $updateData['senha_hash'] = password_hash($data['senha'], PASSWORD_DEFAULT);
            }

            if (User::update($userData['id'], $updateData)) {
                return Response::json([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso'
                ], 200);
            } else {
                return Response::json(['error' => 'Erro ao atualizar usuário'], 500);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Busca um usuário específico por ID.
     * 
     * @param array $params Parâmetros da URL (primeiro elemento é o ID)
     * @return void
     */
    public function show($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            // Obtém o ID do primeiro parâmetro
            $userId = $params[0] ?? null;
            if (!$userId) {
                return Response::json(['error' => 'ID do usuário não fornecido'], 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return Response::json(['error' => 'Usuário não encontrado'], 404);
            }

            return Response::json([
                'success' => true,
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove um usuário autenticado.
     * 
     * @param array $params Parâmetros da URL
     * @return void
     */
    public function remove($params)
    {
        try {
            // Verifica autenticação
            $token = Request::getAuthorizationToken();
            if (!$token) {
                return Response::json(['error' => 'Token de autenticação não fornecido'], 401);
            }

            $userData = JWT::verify($token);
            if (!$userData) {
                return Response::json(['error' => 'Token inválido'], 401);
            }

            $userId = $params[0] ?? null;

            if (!$userId) {
                return Response::json(['error' => 'ID do usuário é obrigatório'], 400);
            }

            // Verifica se o usuário existe
            $user = User::find($userId);
            if (!$user) {
                return Response::json(['error' => 'Usuário não encontrado'], 404);
            }

            // Verifica se o usuário está tentando deletar a si mesmo
            if ($userData['id'] == $userId) {
                return Response::json(['error' => 'Não é possível deletar seu próprio usuário'], 400);
            }

            if (User::delete($userId)) {
                return Response::json([
                    'success' => true,
                    'message' => 'Usuário removido com sucesso'
                ], 200);
            } else {
                return Response::json(['error' => 'Erro ao remover usuário'], 500);
            }

        } catch (\Exception $e) {
            return Response::json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
}
