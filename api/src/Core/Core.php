<?php

namespace App\Core;

use App\Http\Request;
use App\Http\Response;

/**
 * Classe responsável por gerenciar o roteamento da aplicação e despachar a rota correspondente.
 */
class Core 
{
    /**
     * Método que processa as rotas e chama o controlador e ação correspondentes.
     *
     * @param array $routes Lista de rotas registradas no sistema.
     * 
     * Este método percorre as rotas, verifica se a URL atual corresponde a alguma rota registrada,
     * verifica o método HTTP, e invoca o controlador e ação correspondentes. 
     * Se a rota não for encontrada ou o método não for permitido, uma resposta de erro é retornada.
     */
    public static function dispatch(array $routes)
    {
        // URL padrão
        $url = '/';

        // Se houver uma URL no GET, concatena com a URL padrão
        isset($_GET['url']) && $url .= $_GET['url'];

        // Remove qualquer barra no final da URL (exceto a raiz '/')
        $url !== '/' && $url = rtrim($url, '/');

        // Prefixo para localizar os controladores na estrutura da aplicação
        $prefixController = 'App\\Controllers\\';

        // Flag para verificar se a rota foi encontrada
        $routeFound = false;

        // Percorre todas as rotas registradas
        foreach ($routes as $route) {
            // Define o padrão de regex para a rota, substituindo {id} por qualquer sequência alfanumérica
            $pattern = '#^'. preg_replace('/{id}/', '([\w-]+)', $route['path']) .'$#';

            // Verifica se a URL atual corresponde ao padrão da rota
            if (preg_match($pattern, $url, $matches)) {
                // Remove o primeiro item que corresponde à URL completa
                array_shift($matches);

                // Marca que a rota foi encontrada
                $routeFound = true;

                // Verifica se o método HTTP da requisição corresponde ao método esperado pela rota
                if ($route['method'] !== Request::method()) {
                    // Retorna uma resposta de erro com o código 405 (Método não permitido)
                    Response::json([
                        'error'   => true,
                        'success' => false,
                        'message' => 'Desculpe, método não permitido.'
                    ], 405);
                    return;
                }

                // Separa o controlador e a ação definidos na rota (por exemplo: "UserController@index")
                [$controller, $action] = explode('@', $route['action']);

                // Prefixa o namespace do controlador
                $controller = $prefixController . $controller;
                
                // Instancia o controlador
                $extendController = new $controller();

                // Chama a ação correspondente no controlador, passando apenas os parâmetros da URL
                $extendController->$action($matches);
                
                // Interrompe a execução após chamar o controlador
                return;
            }
        }

        // Se nenhuma rota foi encontrada, retorna para a página "Not Found"
        if (!$routeFound) {
            // Define o controlador padrão para "Not Found"
            $controller = $prefixController . 'NotFoundController';
            
            // Instancia o controlador de erro
            $extendController = new $controller();
            
            // Chama a ação padrão do controlador de erro (página 404)
            $extendController->index(new Request, new Response);
        }
    }
}
