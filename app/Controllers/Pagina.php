<?php

/**
 * [ PAGINA ] - Controlador responsável por gerenciar as páginas do sistema.
 * 
 * Este controlador permite:
 * - Exibir páginas de erro e outras páginas do sistema
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */
class Pagina extends Controllers
{
    private $loginModel;
    
    /**
     * [ __construct ] - Construtor da classe.
     * 
     * @return void
     */
    public function __construct()
    {
        //$this = Pseudo-variável é um nome que será utilizado como se fosse uma variável, para chamar o modelo de Usuarios que realiza a comunicação com o banco de dados
        $this->loginModel = $this->model('LoginModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            // Se não estiver logado, redireciona para a página de login
            Helper::redirecionar('./');
        }

    }

    /**
     * [ erro ] - Exibe a página de erro.
     * 
     * @return void
     */
    public function erro()
    {
        $dados = [
            'tituloPagina' => 'Página de ERRO'
        ];

        $this->view('paginas/error', $dados);
    }
}
