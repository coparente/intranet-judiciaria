<?php 

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

/**
 * Classe NotFoundController responsável por gerenciar a resposta de rotas não encontradas.
 */
class NotFoundController
{
    /**
     * Retorna uma resposta JSON para rotas não encontradas.
     * 
     * @param Request $request O objeto de requisição.
     * @param Response $response O objeto de resposta.
     * @return void
     */
    public function index(Request $request, Response $response)
    {
        // Retorna a resposta de erro com a mensagem de rota não encontrada
        $response::json([
            'error'   => true,
            'success' => false,
            'message' => 'Sorry, route not found.'
        ], 404);
        return;
    }
}
