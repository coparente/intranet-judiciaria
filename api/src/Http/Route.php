<?php 

namespace App\Http;

/**
 * Classe Route responsável pelo gerenciamento de rotas da aplicação.
 */
class Route 
{
    /**
     * Array estático que armazena as rotas definidas.
     * 
     * @var array
     */
    private static array $routes = [];

    /**
     * Prefixo global para todas as rotas.
     * 
     * @var string
     */
    private static string $prefix = '';

    /**
     * Define um prefixo global para todas as rotas.
     * 
     * @param string $prefix O prefixo a ser aplicado (ex: 'v1', 'api/v1')
     * 
     * @return void
     */
    public static function prefix(string $prefix)
    {
        self::$prefix = '/' . ltrim($prefix, '/');
    }

    /**
     * Aplica o prefixo global ao caminho da rota.
     * 
     * @param string $path O caminho original da rota
     * 
     * @return string O caminho com prefixo aplicado
     */
    private static function applyPrefix(string $path)
    {
        if (empty(self::$prefix)) {
            return $path;
        }

        // Se a rota é '/', retorna apenas o prefixo
        if ($path === '/') {
            return self::$prefix;
        }

        // Aplica o prefixo à rota
        return self::$prefix . $path;
    }

    /**
     * Define uma rota do tipo GET.
     * 
     * @param string $path O caminho da rota.
     * @param string $action A ação a ser executada quando a rota for acessada.
     * 
     * @return void
     */
    public static function get(string $path, string $action)
    {
        self::$routes[] = [
            'path'   => self::applyPrefix($path),
            'action' => $action,
            'method' => 'GET'
        ];
    }

    /**
     * Define uma rota do tipo POST.
     * 
     * @param string $path O caminho da rota.
     * @param string $action A ação a ser executada quando a rota for acessada.
     * 
     * @return void
     */
    public static function post(string $path, string $action)
    {
        self::$routes[] = [
            'path'   => self::applyPrefix($path),
            'action' => $action,
            'method' => 'POST'
        ];
    }

    /**
     * Define uma rota do tipo PUT.
     * 
     * @param string $path O caminho da rota.
     * @param string $action A ação a ser executada quando a rota for acessada.
     * 
     * @return void
     */
    public static function put(string $path, string $action)
    {
        self::$routes[] = [
            'path'   => self::applyPrefix($path),
            'action' => $action,
            'method' => 'PUT'
        ];
    }

    /**
     * Define uma rota do tipo DELETE.
     * 
     * @param string $path O caminho da rota.
     * @param string $action A ação a ser executada quando a rota for acessada.
     * 
     * @return void
     */
    public static function delete(string $path, string $action)
    {
        self::$routes[] = [
            'path'   => self::applyPrefix($path),
            'action' => $action,
            'method' => 'DELETE'
        ];
    }

    /**
     * Retorna todas as rotas definidas.
     * 
     * @return array Retorna um array com as rotas.
     */
    public static function routes()
    {
        return self::$routes;
    }

    /**
     * Limpa todas as rotas e o prefixo (útil para testes).
     * 
     * @return void
     */
    public static function clear()
    {
        self::$routes = [];
        self::$prefix = '';
    }

    /**
     * Retorna o prefixo atual.
     * 
     * @return string O prefixo atual
     */
    public static function getPrefix()
    {
        return self::$prefix;
    }
}
