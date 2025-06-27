<?php 

namespace App\Config;

/**
 * Classe de configuração da aplicação.
 */
class App
{
    /**
     * Configurações do banco de dados.
     */
    const DB_CONFIG = [
        'host' => 'localhost',
        'dbname' => 'chat_api',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];

    /**
     * Configurações da API Serpro.
     */
    const SERPRO_CONFIG = [
        'client_id' => '642958872237822',
        'client_secret' => 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF',
        'base_url' => 'https://api.whatsapp.serpro.gov.br',
        'waba_id' => '472202335973627',
        'phone_number_id' => '642958872237822',
        'api_key' => 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF', // Usando client_secret como api_key
        'phone_number' => '556232162929' // Número real: +55 62 3216-2929
    ];

    /**
     * Configurações de JWT.
     */
    const JWT_CONFIG = [
        'secret' => 'sua_chave_secreta_jwt_muito_segura', // Substitua por uma chave segura
        'expiration' => 86400 // 24 horas em segundos
    ];

    /**
     * Configurações do webhook.
     */
    const WEBHOOK_CONFIG = [
        'secret' => 'seu_webhook_secret', // Substitua por uma chave secreta
        'auto_reply_enabled' => true
    ];

    /**
     * Configurações gerais da aplicação.
     */
    const APP_CONFIG = [
        'name' => 'Chat API WhatsApp',
        'version' => '1.0.0',
        'api_version' => 'v1',
        'api_prefix' => 'v1',
        'debug' => true, // Mude para false em produção
        'timezone' => 'America/Sao_Paulo'
    ];

    /**
     * Obtém uma configuração específica.
     * 
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed Valor da configuração
     */
    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = null;

        switch ($keys[0]) {
            case 'db':
                $config = self::DB_CONFIG;
                break;
            case 'serpro':
                $config = self::SERPRO_CONFIG;
                break;
            case 'jwt':
                $config = self::JWT_CONFIG;
                break;
            case 'webhook':
                $config = self::WEBHOOK_CONFIG;
                break;
            case 'app':
                $config = self::APP_CONFIG;
                break;
            default:
                return $default;
        }

        if (count($keys) === 1) {
            return $config;
        }

        $value = $config;
        for ($i = 1; $i < count($keys); $i++) {
            if (isset($value[$keys[$i]])) {
                $value = $value[$keys[$i]];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Verifica se a aplicação está em modo debug.
     * 
     * @return bool True se estiver em debug
     */
    public static function isDebug()
    {
        return self::get('app.debug', false);
    }

    /**
     * Obtém a URL base da aplicação.
     * 
     * @return string URL base
     */
    public static function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . $path;
    }

    /**
     * Obtém a URL do webhook.
     * 
     * @return string URL do webhook
     */
    public static function getWebhookUrl()
    {
        return self::getBaseUrl() . '/webhook/receive';
    }
} 