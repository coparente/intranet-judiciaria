<?php

/**
 * Controlador de Notificações
 * 
 * Este controlador gerencia as notificações do sistema, incluindo:
 * - Verificação de prazos
 * - Criação de notificações
 * - Marcação de notificações como lidas
 *  
 * @package Controllers
 * @author Cleyton Oliveira 
 * @version 1.0
 */
class Notificacoes extends Controllers {
    private $notificacaoModel;

    public function __construct() {
        parent::__construct();
        $this->notificacaoModel = $this->model('NotificacaoModel');
    }

    /**
     * [ verificarPrazos ] - Verifica e cria notificações para prazos próximos
     * 
     * @return void
     */ 
    public function verificarPrazos() {
        header('Content-Type: application/json');
        
        try {
            $prazos = $this->notificacaoModel->verificarPrazosVencendo();
            $notificacoesCriadas = 0;
            
            foreach ($prazos as $prazo) {
                $dados = [
                    'processo_id' => $prazo->processo_id,
                    'tipo' => 'prazo',
                    'mensagem' => "Prazo se aproximando para o processo {$prazo->numero_processo}: {$prazo->descricao}",
                    'data_prazo' => $prazo->prazo,
                    'usuario_id' => $prazo->usuario_id
                ];
                
                if ($this->notificacaoModel->criarNotificacao($dados)) {
                    $notificacoesCriadas++;
                }
            }
            
            // Previne que o layout seja renderizado
            ob_clean(); // Limpa qualquer saída anterior
            
            echo json_encode([
                'success' => true,
                'message' => "Verificação concluída. {$notificacoesCriadas} notificação(ões) criada(s).",
                'prazos_verificados' => count($prazos)
            ]);
            exit;
            
        } catch (Exception $e) {
            ob_clean(); // Limpa qualquer saída anterior
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao verificar prazos: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * [ isAjax ] - Verifica se a requisição é AJAX
     * 
     * @return bool
     */
    private function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * [ buscarPendentes ] - Busca notificações pendentes via AJAX
     * 
     * @return void
     */
    public function buscarPendentes() {
        header('Content-Type: application/json');
        ob_clean(); // Limpa qualquer saída anterior
        
        try {
            $notificacoes = $this->notificacaoModel->buscarNotificacoesPendentes($_SESSION['usuario_id']);
            echo json_encode($notificacoes);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    /**
     * [ marcarLida ] - Marca notificação como lida
     * 
     * @param int $id - ID da notificação
     * @return void
     */
    public function marcarLida($id) {
        if ($this->notificacaoModel->marcarComoLida($id)) {
            Helper::mensagem('notificacoes', '<i class="fas fa-check"></i> Notificação marcada como lida', 'alert alert-success');
            Helper::mensagemSweetAlert('notificacoes', 'Notificação marcada como lida', 'success');
        } else {
            Helper::mensagem('notificacoes', '<i class="fas fa-ban"></i> Erro ao marcar notificação', 'alert alert-danger');
            Helper::mensagemSweetAlert('notificacoes', 'Erro ao marcar notificação', 'error');
        }
        Helper::redirecionar('notificacoes/index');
    }

    /**
     * [ index ] - Exibe a lista de notificações
     * 
     * @return void
     */
    public function index() {
        $notificacoes = $this->notificacaoModel->buscarNotificacoesPendentes($_SESSION['usuario_id']);
        
        $dados = [
            'tituloPagina' => 'Notificações',
            'notificacoes' => $notificacoes
        ];
        
        $this->view('notificacoes/index', $dados);
    }
}