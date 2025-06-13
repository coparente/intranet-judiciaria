<?php

/**
 * [ ROTA ] - Classe para gerenciar as rotas do sistema.
 * 
 * Esta classe fornece métodos para gerenciar as rotas do sistema, incluindo a definição de controladores e métodos.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected       
 */
class Rota
{
    private $controlador = CONTROLLER;
    private $metodo = METODO;
    private $parametros = [];

    /**
     * Mapeamento de URLs para nomes de Controllers
     * Para resolver problemas de case sensitivity entre desenvolvimento e produção
     */
    private $mapeamentoControllers = [
        'consultaprocessos' => 'ConsultaProcessos',
        'ciri' => 'CIRI',
        'projudi' => 'Projudi',
        'chat' => 'Chat',
        'dashboard' => 'Dashboard',
        'usuarios' => 'Usuarios',
        'perfis' => 'Perfis',
        'relatorios' => 'Relatorios',
        'configuracoes' => 'Configuracoes'
    ];

    public function __construct()
    {
        // Obtém a URL e define o controlador padrão se estiver vazia
        $url = $this->url();
        if(empty($url)):
            $this->controlador = CONTROLLER;
        else:
            // Se houver URL, usa o primeiro segmento como controlador
            $controllerName = $this->obterNomeController($url[0]);
            
            if (file_exists('./app/Controllers/' . $controllerName . '.php')):
                $this->controlador = $controllerName;
                unset($url[0]);
            else:
                $this->redirecionarParaErro();
            endif;
        endif;

        require_once './app/Controllers/' . $this->controlador . '.php';
        $this->controlador = new $this->controlador;

        // Define o método padrão se nenhum for especificado
        if (empty($url)):
            $this->metodo = METODO;
        else:
            if (isset($url[1])):
                if (method_exists($this->controlador, $url[1])):
                    $this->metodo = $url[1];
                    unset($url[1]);
                else:
                    $this->redirecionarParaErro();
                endif;
            endif;
        endif;

        $this->parametros = $url ? array_values($url) : [];
        call_user_func_array([$this->controlador, $this->metodo], $this->parametros);
    }

    /**
     * Obtém o nome correto do controller baseado no mapeamento
     * 
     * @param string $urlController Nome do controller da URL
     * @return string Nome correto do controller
     */
    private function obterNomeController($urlController) 
    {
        $urlLowerCase = strtolower($urlController);
        
        // Verifica se existe mapeamento específico
        if (isset($this->mapeamentoControllers[$urlLowerCase])) {
            return $this->mapeamentoControllers[$urlLowerCase];
        }
        
        // Fallback para o comportamento original
        return ucwords($urlController);
    }

    /**
     * funcção retorna a url em um array
     * o filtro FILTER_SANITIZE_URL remove todos os caracteres ilegais de uma URL
     * @return Array
     */
    private function url()
    {
        $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
        if (isset($url)):
            $url = trim(rtrim($url, '/'));
            $url = explode('/', $url);
            return $url;
        endif;
    }

    /**
     * Redireciona para a página de erro
     * 
     * @return void
     */
    private function redirecionarParaErro() {
        Helper::redirecionar('pagina/erro');
        exit();
    }
}
