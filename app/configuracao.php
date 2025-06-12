<?php
// Carrega o Composer
// require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// $dotenv->load();

define('APP', dirname(__FILE__));
define('APPROOT', dirname(__FILE__));

/**
 * Configurações do Banco de Dados
 */
// define('HOST', 'localhost');
// define('PORTA',  '3306');
// define('BANCO', 'dir_judiciaria');
// define('USUARIO', 'root');
// define('SENHA', '');


define('HOST', 'localhost');
define('PORTA', '3306');
define('BANCO', 'copare52_dir_judiciaria');
define('USUARIO', 'copare52_dir_judiciaria');
define('SENHA', 'parente1010');


// define('HOST', $_ENV['DEV_DB_HOST'] ?: getenv('PROD_DB_HOST') ?: 'localhost');
// define('PORTA', $_ENV['DEV_DB_PORT'] ? intval($_ENV['DEV_DB_PORT']) : (getenv('PROD_DB_PORT') ? intval(getenv('PROD_DB_PORT')) : 3306));
// define('BANCO', $_ENV['DEV_DB_NAME'] ?: getenv('PROD_DB_NAME') ?: 'dir_judiciaria');
// define('USUARIO', $_ENV['DEV_DB_USERNAME'] ?: getenv('PROD_DB_USERNAME') ?: 'root');
// define('SENHA', $_ENV['DEV_DB_PASSWORD'] ?: getenv('PROD_DB_PASSWORD') ?: '');


/**
 * Configurações da Aplicação
 */
define('APP_NOME', 'Dir Judiciária');
define('APP_VERSAO', '1.2.0');
define('URL', 'http://sistemas.coparente.top/intranet/');
// define('URL', 'http://10.90.18.141/intranet-judiciaria');



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
