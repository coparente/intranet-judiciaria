<?php 

namespace App\Http;

/**
 * Classe Response responsável por gerar respostas HTTP.
 */
class Response
{
    /**
     * Envia uma resposta JSON.
     * 
     * @param array $data Os dados a serem enviados na resposta.
     * @param int $status O código de status HTTP a ser enviado (padrão: 200).
     * 
     * @return void
     */
    public static function json(array $data = [], int $status = 200)
    {
        // Define o código de status HTTP da resposta
        http_response_code($status);

        // Define o cabeçalho da resposta como JSON
        header("Content-Type: application/json");

        // Codifica os dados em JSON e os envia na resposta
        echo json_encode($data);
    }
}
