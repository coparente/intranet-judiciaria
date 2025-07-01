<?php
// Carrega o Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// $dotenv->load();

define('APP', dirname(__FILE__));
define('APPROOT', dirname(__FILE__));

// $ambiente = 'producao';
// $ambiente = 'local';
// Detecção automática do ambiente
$host = $_SERVER['HTTP_HOST'] ?? '';
$ambiente = (strpos($host, 'coparente.top') !== false) ? 'producao' : 'local';

/**
 * Configurações do Banco de Dados
 */

if ($ambiente === 'local') {
    define('HOST', 'localhost');
    define('PORTA', 3306);
    define('BANCO', 'dir_judiciaria');
    define('USUARIO', 'root');
    define('SENHA', '');
    //URL do Intranet Judiciária local
    define('URL', 'http://10.90.18.141/intranet-judiciaria');
} else {
    define('HOST', 'localhost');
    define('PORTA', 3306);
    define('BANCO', 'copare52_dir_judiciaria');
    define('USUARIO', 'copare52_dir_judiciaria');
    define('SENHA', 'parente1010');
    //URL do Intranet Judiciária produção
    define('URL', 'https://coparente.top/intranet');
}

/**
 * Configurações do MinIO
 */ 
define('MINIO_ENDPOINT', 'https://minioapidj.helpersti.online');
define('MINIO_REGION', 'sa-east-1');
define('MINIO_ACCESS_KEY', 'pBb2oG0RcNzZfEJJzOrh');
define('MINIO_SECRET_KEY', 'J401ezyGzLCgNgVLGRmvjPXfZqXeS10OzI0JdI01');
define('MINIO_BUCKET', 'chatserpro');
define('MINIO_USE_PATH_STYLE_ENDPOINT', true);

// $endpoint = 'https://minioapidj.helpersti.online'; // Host correto da API
// $region = 'sa-east-1';
// $use_path_style_endpoint = true; // Essencial para MinIO
// $access_key_id = 'pBb2oG0RcNzZfEJJzOrh';
// $secret_access_key = 'J401ezyGzLCgNgVLGRmvjPXfZqXeS10OzI0JdI01';
// $bucket = 'chatserpro';


/**
 * Configurações da Aplicação
 */
define('APP_NOME', 'Dir Judiciária');
define('APP_VERSAO', '1.2.0');

/**
 * Configurações da API do Google AI
 */
define('API_KEY', 'AIzaSyDGO0Uh1zNMOsJRRhsAMtCrGk20NdXEriA');

/**
 * Configurações da API do Serpro
 */
define('SERPRO_CLIENT_ID', '642958872237822');
define('SERPRO_CLIENT_SECRET', 'ewW08ZJW1F1G6dkr8tExGGDQwTyua2jF');
define('SERPRO_BASE_URL', 'https://api.whatsapp.serpro.gov.br');
define('SERPRO_WABA_ID', '472202335973627');
define('SERPRO_PHONE_NUMBER_ID', '642958872237822');

// Configurações do Webhook
define('WEBHOOK_VERIFY_TOKEN', 'tjgo_intranet_webhook_2025_secreto_serpro');



/**
 * Configurações da API do DataJud
 */
define('API_KEY_DATAJUD', 'cDZHYzlZa0JadVREZDJCendQbXY6SkJlTzNjLV9TRENyQk1RdnFKZGRQdw==');
define('URL_DATAJUD', 'https://api-publica.datajud.cnj.jus.br/api_publica_tjgo/_search');
/**
 * Rotas Padrão
 */
define('CONTROLLER', 'Login');
define('METODO', 'login');

/**
 * Configurações de Email
 */
define('EMAIL_ADMIN', 'admin@tjgo.jus.br');
define('EMAIL_SISTEMA', 'sistema@tjgo.jus.br');

/**
 * Configurações de Upload
 */
define('UPLOAD_MAX_SIZE', 40 * 1024 * 1024); // 40MB em bytes
define('UPLOAD_TIPOS_PERMITIDOS', ['txt']);
define('UPLOAD_DIR', 'uploads/');

/**
 * Níveis de Acesso
 */
define('PERFIL_USUARIO', 'usuario');
define('PERFIL_ANALISTA', 'analista');
define('PERFIL_ADMIN', 'admin');

/**
 * Status de Usuário
 */
define('STATUS_ATIVO', 'ativo');
define('STATUS_INATIVO', 'inativo');

/**
 * Configurações de Sessão
 */
define('SESSAO_TEMPO_LIMITE', 30 * 60); // 30 minutos em segundos
define('SESSAO_NOME', 'cuc_sessao');

/**
 * Configurações de Segurança
 */
define('HASH_COST', 10); // Custo do bcrypt
define('TOKEN_EXPIRACAO', 24 * 60 * 60); // 24 horas em segundos

/**
 * Configurações de Paginação
 */
define('ITENS_POR_PAGINA', 20);
define('MAX_PAGINAS_NAVEGACAO', 5);

/**
 * Configurações de Timezone
 */
date_default_timezone_set('America/Sao_Paulo');
