<?php

/**
 * [ CHAT ] - Controlador responsável por gerenciar o chat via API do SERPRO.
 * 
 * Este controlador permite:
 * - Enviar e receber mensagens via WhatsApp
 * - Gerenciar conversas e contatos
 * - Enviar diferentes tipos de mídia (texto, imagem, documento, vídeo)
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access public
 */
class Chat extends Controllers
{
    private $chatModel;
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->chatModel = $this->model('ChatModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        
        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }

        // Carrega o helper do SERPRO
        require_once APPROOT . '/Libraries/SerproHelper.php';
    }

    /**
     * [ index ] - Exibe a página principal do chat
     * 
     * @return void
     */
    public function index()
    {
        // Verifica permissão para o módulo de chat
        // Middleware::verificarPermissao(10); // ID do módulo 'Chat'

        $conversas = $this->chatModel->buscarConversas($_SESSION['usuario_id']);
        
        $dados = [
            'tituloPagina' => 'Chat',
            'conversas' => $conversas
        ];

        $this->view('chat/index', $dados);
    }

    /**
     * Exibe conversa específica
     */
    public function conversa($conversa_id = null)
    {
        if (!$conversa_id) {
            Helper::redirecionar('chat');
            return;
        }

        // Buscar conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        // Marcar mensagens como lidas
        $this->chatModel->marcarComoLida($conversa_id);

        // Buscar mensagens
        $mensagens = $this->chatModel->buscarMensagens($conversa_id);

        $data = [
            'titulo' => 'Chat - ' . $conversa->contato_nome,
            'conversa' => $conversa,
            'mensagens' => $mensagens,
            'conversas' => $this->chatModel->buscarConversas($_SESSION['usuario_id'])
        ];

        $this->view('chat/conversa', $data);
    }

    /**
     * [ novaConversa ] - Cria uma nova conversa
     */
    public function novaConversa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Formata o número de telefone (remove caracteres não numéricos)
            $numero = preg_replace('/[^0-9]/', '', $formulario['numero']);
            
            // Verifica se já existe uma conversa com este número
            $conversaExistente = $this->chatModel->buscarConversaPorNumero($numero, $_SESSION['usuario_id']);
            
            if ($conversaExistente) {
                Helper::mensagem('chat', '<i class="fas fa-info-circle"></i> Já existe uma conversa com este contato', 'alert alert-info');
                Helper::redirecionar("chat/conversa/{$conversaExistente->id}");
                return;
            }
            
            // Criar nova conversa
            $conversa = $this->chatModel->buscarOuCriarConversa(
                $_SESSION['usuario_id'],
                $numero,
                $formulario['nome'] ?? 'Contato ' . $numero
            );
            
            if ($conversa) {
                Helper::mensagem('chat', '<i class="fas fa-check"></i> Conversa criada com sucesso', 'alert alert-success');
                Helper::redirecionar("chat/conversa/{$conversa->id}");
            } else {
                Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao criar conversa', 'alert alert-danger');
                Helper::redirecionar('chat/index');
            }
        } else {
            $dados = [
                'tituloPagina' => 'Nova Conversa'
            ];

            $this->view('chat/nova_conversa', $dados);
        }
    }

    /**
     * [ atualizarConversa ] - Atualiza os dados de uma conversa
     * 
     * @param int $id ID da conversa
     * @return void
     */
    public function atualizarConversa($id)
    {
        // Verifica permissão para o módulo de chat
        Middleware::verificarPermissao(10); // ID do módulo 'Chat'

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $conversa = $this->chatModel->buscarConversaPorId($id);
            
            if (!$conversa) {
                Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa não encontrada', 'alert alert-danger');
                Helper::redirecionar('chat/index');
                return;
            }
            
            // Atualiza os dados da conversa
            $dados = [
                'id' => $id,
                'contato_nome' => $formulario['nome']
            ];
            
            if ($this->chatModel->atualizarConversa($dados)) {
                Helper::mensagem('chat', '<i class="fas fa-check"></i> Conversa atualizada com sucesso', 'alert alert-success');
            } else {
                Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao atualizar conversa', 'alert alert-danger');
            }
            
            Helper::redirecionar("chat/conversa/{$id}");
        }
    }

    /**
     * [ enviarMensagem ] - Envia uma mensagem via WhatsApp
     */
    public function enviarMensagem($conversa_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        if (!$conversa_id) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> ID da conversa não informado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        // Buscar conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        $mensagem = trim($_POST['mensagem'] ?? '');
        
        if (empty($mensagem)) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Mensagem não pode estar vazia', 'alert alert-danger');
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        // Verificar se é a primeira mensagem
        $mensagensExistentes = $this->chatModel->contarMensagens($conversa_id);
        $precisaTemplate = ($mensagensExistentes == 0);

        if ($precisaTemplate) {
            // Primeira mensagem - usar template
            $resultado = $this->enviarPrimeiraMensagem($conversa->contato_numero, $mensagem);
        } else {
            // Conversa já iniciada - enviar mensagem normal
            $resultado = SerproHelper::enviarMensagem($conversa->contato_numero, 'text', $mensagem);
        }

        if ($resultado['status'] == 200 || $resultado['status'] == 201) {
            // Salvar no banco
            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa_id,
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'text',
                'conteudo' => $mensagem,
                'message_id' => $resultado['response']['messages'][0]['id'] ?? uniqid(),
                'status' => 'enviado',
                'enviado_em' => date('Y-m-d H:i:s')
            ]);

            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa_id);
            
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Mensagem enviada com sucesso!', 'alert alert-success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao enviar mensagem: ' . ($resultado['error'] ?? 'Erro desconhecido'), 'alert alert-danger');
        }

        Helper::redirecionar("chat/conversa/{$conversa_id}");
    }

    /**
     * [ carregarNovasMensagens ] - Carrega novas mensagens de uma conversa (AJAX)
     * 
     * @param int $conversa_id ID da conversa
     * @param int $ultima_mensagem_id ID da última mensagem carregada
     * @return void
     */
    public function carregarNovasMensagens($conversa_id, $ultima_mensagem_id = 0)
    {
        // Verifica permissão para o módulo de chat
        // Middleware::verificarPermissao(10); // ID do módulo 'Chat'
        
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        
        if (!$conversa) {
            echo json_encode(['erro' => 'Conversa não encontrada']);
            return;
        }
        
        // Verifica se o usuário tem acesso a esta conversa
        if ($conversa->usuario_id != $_SESSION['usuario_id'] && $_SESSION['usuario_perfil'] !== 'admin') {
            echo json_encode(['erro' => 'Você não tem permissão para acessar esta conversa']);
            return;
        }
        
        // Busca novas mensagens
        $mensagens = $this->chatModel->buscarNovasMensagens($conversa_id, $ultima_mensagem_id);
        
        // Marca as mensagens como lidas
        if (!empty($mensagens)) {
            $this->chatModel->marcarMensagensComoLidas($conversa_id);
        }
        
        echo json_encode(['mensagens' => $mensagens]);
    }

    /**
     * [ excluirConversa ] - Exclui uma conversa
     * 
     * @param int $id ID da conversa
     * @return void
     */
    public function excluirConversa($id)
    {
        // Verifica permissão para o módulo de chat
        Middleware::verificarPermissao(10); // ID do módulo 'Chat'
        
        $conversa = $this->chatModel->buscarConversaPorId($id);
        
        if (!$conversa) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }
        
        // Verifica se o usuário tem acesso a esta conversa
        if ($conversa->usuario_id != $_SESSION['usuario_id'] && $_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Você não tem permissão para excluir esta conversa', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }
        
        if ($this->chatModel->excluirConversa($id)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Conversa excluída com sucesso', 'alert alert-success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao excluir conversa', 'alert alert-danger');
        }
        
        Helper::redirecionar('chat/index');
    }

    /**
     * [ verificarStatusAPI ] - Verifica se a API do SERPRO está online (AJAX)
     * 
     * @return void
     */
    public function verificarStatusAPI()
    {
        // Verifica permissão para o módulo de chat
        Middleware::verificarPermissao(10); // ID do módulo 'Chat'
        
        // Limpa qualquer saída anterior
        ob_clean();
        
        // Define o cabeçalho para JSON
        header('Content-Type: application/json');
        
        try {
            $status = SerproHelper::verificarStatusAPI();
            $response = [
                'online' => $status,
            ];
            
            // Adiciona mensagem de erro se houver
            if (!$status) {
                $response['error'] = SerproHelper::getLastError();
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'online' => false,
                'error' => $e->getMessage()
            ]);
        }
        
        // Garante que nenhum HTML seja adicionado à resposta
        exit;
    }

    /**
     * Envia mensagem via AJAX
     */
    public function enviarMensagemAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['numero']) || !isset($input['mensagem'])) {
            echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
            return;
        }

        $numero = preg_replace('/[^0-9]/', '', $input['numero']);
        $mensagem = trim($input['mensagem']);
        $processo_id = $input['processo_id'] ?? null;

        if (empty($mensagem)) {
            echo json_encode(['success' => false, 'error' => 'Mensagem não pode estar vazia']);
            return;
        }

        // Buscar ou criar conversa
        $conversa = $this->chatModel->buscarOuCriarConversa(
            $_SESSION['usuario_id'],
            $numero,
            $input['contato_nome'] ?? 'Contato ' . $numero,
            $processo_id
        );

        if (!$conversa) {
            echo json_encode(['success' => false, 'error' => 'Erro ao criar conversa']);
            return;
        }

        // Verificar se é a primeira mensagem da conversa
        $mensagensExistentes = $this->chatModel->contarMensagens($conversa->id);
        $precisaTemplate = ($mensagensExistentes == 0);

        if ($precisaTemplate) {
            // Primeira mensagem - usar template
            $resultado = $this->enviarPrimeiraMensagem($numero, $mensagem);
        } else {
            // Conversa já iniciada - enviar mensagem normal
            $resultado = SerproHelper::enviarMensagem($numero, 'text', $mensagem);
        }

        if ($resultado['status'] == 200 || $resultado['status'] == 201) {
            // Salvar no banco
            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa->id,
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'text',
                'conteudo' => $mensagem,
                'message_id' => $resultado['response']['messages'][0]['id'] ?? uniqid(),
                'status' => 'enviado',
                'enviado_em' => date('Y-m-d H:i:s')
            ]);

            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa->id);

            echo json_encode(['success' => true, 'precisou_template' => $precisaTemplate]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => $resultado['error'] ?? 'Erro ao enviar mensagem',
                'details' => $resultado
            ]);
        }
    }

    /**
     * Envia primeira mensagem usando template
     */
    private function enviarPrimeiraMensagem($numero, $mensagem)
    {
        $nomeTemplate = 'simple_greeting'; // Nome do template aprovado
        
        // Parâmetros do template
        $parametros = [
            [
                'tipo' => 'text',
                'valor' => $mensagem
            ]
        ];

        return SerproHelper::enviarTemplate($numero, $nomeTemplate, $parametros);
    }

    /**
     * Lista mensagens via AJAX
     */
    public function mensagens($conversa_id = null)
    {
        if (!$conversa_id) {
            echo json_encode(['error' => 'ID da conversa não informado']);
            return;
        }

        $mensagens = $this->chatModel->buscarMensagens($conversa_id);
        echo json_encode($mensagens);
    }

    /**
     * Busca conversas via AJAX
     */
    public function conversas()
    {
        $conversas = $this->chatModel->buscarConversas($_SESSION['usuario_id']);
        echo json_encode($conversas);
    }

    /**
     * Marca mensagens como lidas
     */
    public function marcarLida()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['conversa_id'])) {
            echo json_encode(['success' => false]);
            return;
        }

        $resultado = $this->chatModel->marcarConversaComoLida($input['conversa_id']);
        echo json_encode(['success' => $resultado]);
    }
}