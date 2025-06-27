<?php 

namespace App\Http;

use App\Config\App;

/**
 * Classe JWT responsável por gerar e verificar JSON Web Tokens (JWT).
 */
class JWT 
{
    /**
     * Gera um JSON Web Token (JWT) a partir dos dados fornecidos.
     * 
     * @param array $data Dados a serem incluídos no payload do JWT.
     * @return string O JWT gerado.
     */
    public static function generate(array $data = [])
    {
        $header  = json_encode(['typ' => 'JWT', 'alg' => 'HS256']); // Cabeçalho do JWT
        $payload = json_encode($data); // Payload do JWT
        
        // Codifica o cabeçalho e o payload em base64 URL
        $base64UrlHeader  = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);

        // Gera a assinatura do JWT
        $signature = self::signature($base64UrlHeader, $base64UrlPayload);

        // Constrói o JWT completo
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $signature;

        return $jwt; // Retorna o JWT gerado
    }

    /**
     * Verifica a validade de um JSON Web Token (JWT).
     * 
     * @param string $jwt O JWT a ser verificado.
     * @return array|false Os dados decodificados do payload se o JWT for válido, caso contrário, false.
     */
    public static function verify(string $jwt)
    {
        $tokenPartials = explode('.', $jwt); // Divide o JWT em partes

        // Verifica se o JWT possui três partes
        if (count($tokenPartials) != 3) return false;

        [$header, $payload, $signature] = $tokenPartials;

        // Verifica se a assinatura corresponde
        if ($signature !== self::signature($header, $payload)) return false;

        return self::base64url_decode($payload); // Retorna o payload decodificado
    }

    /**
     * Gera a assinatura para um JWT a partir do cabeçalho e do payload.
     * 
     * @param string $header O cabeçalho do JWT.
     * @param string $payload O payload do JWT.
     * @return string A assinatura codificada em base64 URL.
     */
    public static function signature(string $header, string $payload)
    {
        $secret = App::get('jwt.secret', 'secret-key');
        
        // Gera a assinatura utilizando HMAC SHA256
        $signature = hash_hmac('sha256', $header . "." . $payload, $secret, true);

        return self::base64url_encode($signature); // Retorna a assinatura codificada
    }

    /**
     * Codifica os dados em base64 URL.
     * 
     * @param mixed $data Os dados a serem codificados.
     * @return string Os dados codificados em base64 URL.
     */
    public static function base64url_encode($data)
    {
        // Converte para base64 URL e remove o padding
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodifica os dados de base64 URL.
     * 
     * @param string $data Os dados codificados em base64 URL.
     * @return array Os dados decodificados.
     */
    public static function base64url_decode($data)
    {
        $padding = strlen($data) % 4;

        // Adiciona padding se necessário
        $padding !== 0 && $data .= str_repeat('=', 4 -  $padding);

        // Converte de volta para base64 padrão
        $data = strtr($data, '-_', '+/');

        return json_decode(base64_decode($data), true); // Retorna os dados decodificados
    }
}
