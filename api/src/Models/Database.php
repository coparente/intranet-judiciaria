<?php 

namespace App\Models;

use App\Config\App;
use PDO;

/**
 * Classe Database responsável por gerenciar a conexão com o banco de dados MySQL.
 */
class Database 
{
    /**
     * Estabelece uma conexão com o banco de dados MySQL.
     * 
     * @return PDO Retorna uma instância do PDO configurada para se conectar ao banco de dados.
     * 
     * @throws PDOException Se a conexão falhar.
     */
    public static function getConnection()
    {
        $host = App::get('db.host', 'localhost');
        $port = App::get('db.port', 3306);
        $dbname = App::get('db.dbname', 'chat_api');
        $username = App::get('db.username', 'root');
        $password = App::get('db.password', '');
        $charset = App::get('db.charset', 'utf8mb4');

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, $username, $password, $options);

        return $pdo;
    }
}
