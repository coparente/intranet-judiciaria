<?php 

use App\Http\Route;

// Define o prefixo v1 para a API
Route::prefix('v1');

// Rotas públicas com prefixo v1
Route::get('/',                     'HomeController@index');

// Rotas de usuários
Route::post('/users/create',        'UserController@store');
Route::post('/users/login',         'UserController@login');
Route::get('/users/fetch',          'UserController@fetch');
Route::put('/users/update',         'UserController@update');
Route::get('/users/{id}',           'UserController@show');
Route::delete('/users/{id}/delete', 'UserController@remove');
Route::get('/users',                'UserController@index');

// Rotas de mensagens (requerem autenticação)
// Rotas específicas primeiro
Route::post('/messages/send',       'MessageController@send');
Route::get('/messages/conversation','MessageController@conversation');
Route::get('/messages/contacts',    'MessageController@contacts');
Route::get('/messages/status-stats','MessageController@statusStats');
Route::post('/messages/mark-read',  'MessageController@markAsRead');
Route::post('/messages/mark-read-serpro', 'MessageController@markAsReadSerpro');
// Rotas com parâmetros depois
Route::get('/messages',             'MessageController@list');
Route::get('/messages/{id}',        'MessageController@show');
Route::delete('/messages/{id}',     'MessageController@delete');
Route::put('/messages/{id}/status', 'MessageController@updateStatus');
Route::get('/messages/{id}/serpro-status', 'MessageController@checkSerproStatus');

// Rotas de webhook (públicas)
Route::post('/webhook/receive',     'WebhookController@receive');
Route::get('/webhook/status',       'WebhookController@status');

// Limpa o prefixo para rotas sem versão (para compatibilidade com versões antigas ou webhooks externos)
Route::prefix('');

// Rota de health check sem prefixo (compatibilidade)
Route::get('/',                     'HomeController@index');
