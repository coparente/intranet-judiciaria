<?php

namespace App\Controllers;

use App\Config\App;

/**
 * Classe HomeController responsável por gerenciar a página inicial da API.
 */
class HomeController 
{
    /**
     * Retorna uma resposta JSON com informações sobre a API.
     * 
     * @return void
     */
    public function index()
    {
        // Retorna a mensagem de boas-vindas e informações da API em formato JSON
        echo json_encode([
            'message' => 'Bem-vindo à API de Integração com o Chat Serpro!',
            'description' => 'Esta API foi desenvolvida para intermediar a comunicação entre sistemas e a API de mensagens da Serpro, viabilizando o envio e recebimento de mensagens em tempo real.',
            'version' => App::get('app.version'),
            'api_version' => App::get('app.api_version'),
            'status' => 'A API está operando normalmente'
        ]);
        
    }
}
