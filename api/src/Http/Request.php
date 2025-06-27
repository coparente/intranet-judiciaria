<?php 

namespace App\Http;

/**
 * Classe Request responsável por lidar com as requisições HTTP.
 */
class Request
{
    /**
     * Obtém o método HTTP da requisição (GET, POST, PUT, DELETE).
     * 
     * @return string O método da requisição HTTP.
     */
    public static function method()
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * Obtém os dados da requisição com base no método HTTP.
     * 
     * @return array Os dados da requisição.
     */
    public static function body()
    {
        // Decodifica o corpo da requisição JSON
        $json = json_decode(file_get_contents('php://input'), true) ?? [];

        $data = [];
        // Verifica o método da requisição e obtém os dados apropriados
        switch (self::method()) {
            case 'GET':
                $data = $_GET; // Para requisições GET, obtém os dados do array global $_GET
                break;
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $data = $json; // Para POST, PUT e DELETE, obtém os dados do corpo JSON
                break;
        }

        return $data; // Retorna os dados obtidos
    }

    /**
     * Obtém o valor do cabeçalho de autorização.
     * 
     * @return string|array O token de autorização ou um array de erro.
     */
    public static function authorization()
    {
        $authorization = getallheaders(); // Obtém todos os cabeçalhos da requisição

        // Verifica se o cabeçalho de autorização está presente
        if (!isset($authorization['Authorization'])) {
            return ['error' => 'Sorry, no authorization header provided'];
        }

        // Divide o cabeçalho de autorização em partes
        $authorizationPartials = explode(' ', $authorization['Authorization']);

        // Verifica se o cabeçalho de autorização está no formato correto
        if (count($authorizationPartials) != 2) {
            return ['error'=> 'Please, provide a valid authorization header.'];
        }

        return $authorizationPartials[1] ?? ''; // Retorna o token de autorização
    }

    /**
     * Obtém o token de autorização do cabeçalho Authorization.
     * 
     * @return string|null O token de autorização ou null se não encontrado
     */
    public static function getAuthorizationToken()
    {
        $authorization = self::authorization();
        
        if (is_array($authorization) && isset($authorization['error'])) {
            return null;
        }
        
        return $authorization;
    }

    /**
     * Obtém o corpo da requisição (alias para body()).
     * 
     * @return array Os dados do corpo da requisição
     */
    public static function getBody()
    {
        return self::body();
    }

    /**
     * Obtém os parâmetros da query string.
     * 
     * @return array Os parâmetros da query string
     */
    public static function getQuery()
    {
        return $_GET;
    }
}
