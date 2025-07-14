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
        $this->chatModel = new ChatModel();
        $this->usuarioModel = $this->model('UsuarioModel');

        // Métodos que não exigem autenticação
        $metodosPublicos = ['webhook'];
        $metodoAtual = $_GET['url'] ?? '';
        $partesUrl = explode('/', trim($metodoAtual, '/'));
        $metodo = isset($partesUrl[1]) ? $partesUrl[1] : '';

        // Verifica se o usuário está logado (exceto para métodos públicos)
        if (!in_array($metodo, $metodosPublicos) && !isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }

        // Carrega o helper do SERPRO
        require_once APPROOT . '/Libraries/SerproHelper.php';

        // Inicializa o SerproHelper com as configurações
        SerproHelper::init();
    }

    /**
     * [ index ] - Exibe a página principal do chat
     */
    public function index()
    {
        // Parâmetros de filtro
        $filtroContato = $_GET['filtro_contato'] ?? '';
        $filtroNumero = $_GET['filtro_numero'] ?? '';
        $filtroStatus = $_GET['filtro_status'] ?? ''; // Novo filtro por status de ticket
        $filtroNome = $_GET['filtro_nome'] ?? ''; // Novo filtro por nome do contato
        
        // Parâmetro de aba (minhas, nao_atribuidas, todas)
        $aba = $_GET['aba'] ?? 'minhas';
        
        // Validar aba
        if (!in_array($aba, ['minhas', 'nao_atribuidas', 'todas'])) {
            $aba = 'minhas';
        }

        // Parâmetros de paginação
        $registrosPorPagina = 10;
        $paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $paginaAtual = max(1, $paginaAtual); // Garantir que não seja menor que 1

        // Calcular offset
        $offset = ($paginaAtual - 1) * $registrosPorPagina;

        // Buscar conversas baseado na aba selecionada
        switch ($aba) {
            case 'nao_atribuidas':
                // Verificar permissão para conversas não atribuídas
                if (!in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
                    Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado para conversas não atribuídas', 'alert alert-danger');
                    Helper::redirecionar('chat/index?aba=minhas');
                    return;
                }
                
                $conversas = $this->chatModel->buscarConversasNaoAtribuidas(
                    $filtroContato,
                    $filtroNumero,
                    $registrosPorPagina,
                    $offset,
                    $filtroStatus
                );
                
                $totalRegistros = $this->chatModel->contarConversasNaoAtribuidas(
                    $filtroContato,
                    $filtroNumero,
                    $filtroStatus
                );
                break;
                
            case 'todas':
                // Verificar permissão para todas as conversas
                if (!in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
                    Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado para todas as conversas', 'alert alert-danger');
                    Helper::redirecionar('chat/index?aba=minhas');
                    return;
                }
                
                $conversas = $this->chatModel->buscarTodasConversasComFiltros(
                    $filtroContato,
                    $filtroNumero,
                    $registrosPorPagina,
                    $offset,
                    $filtroStatus,
                    $filtroNome
                );
                
                $totalRegistros = $this->chatModel->contarTodasConversasComFiltros(
                    $filtroContato,
                    $filtroNumero,
                    $filtroStatus,
                    $filtroNome
                );
                break;
                
            case 'minhas':
            default:
                $conversas = $this->chatModel->buscarConversasComFiltros(
                    $_SESSION['usuario_id'],
                    $filtroContato,
                    $filtroNumero,
                    $registrosPorPagina,
                    $offset,
                    $filtroStatus,
                    $filtroNome
                );
                
                $totalRegistros = $this->chatModel->contarConversasComFiltros(
                    $_SESSION['usuario_id'],
                    $filtroContato,
                    $filtroNumero,
                    $filtroStatus,
                    $filtroNome
                );
                break;
        }

        // Calcular informações de paginação
        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
        $registroInicio = $totalRegistros > 0 ? $offset + 1 : 0;
        $registroFim = min($offset + $registrosPorPagina, $totalRegistros);

        // Construir query string para manter filtros na paginação
        $queryParams = [];
        $queryParams[] = 'aba=' . urlencode($aba);
        if (!empty($filtroContato)) {
            $queryParams[] = 'filtro_contato=' . urlencode($filtroContato);
        }
        if (!empty($filtroNumero)) {
            $queryParams[] = 'filtro_numero=' . urlencode($filtroNumero);
        }
        if (!empty($filtroStatus)) {
            $queryParams[] = 'filtro_status=' . urlencode($filtroStatus);
        }
        if (!empty($filtroNome)) {
            $queryParams[] = 'filtro_nome=' . urlencode($filtroNome);
        }
        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';

        // Buscar lista de usuários para atribuição (se for admin/analista)
        $usuarios = [];
        if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            $usuarios = $this->chatModel->buscarUsuariosParaAtribuicao();
        }

        $dados = [
            'tituloPagina' => 'Chat',
            'conversas' => $conversas,
            'total_registros' => $totalRegistros,
            'total_paginas' => $totalPaginas,
            'pagina_atual' => $paginaAtual,
            'registro_inicio' => $registroInicio,
            'registro_fim' => $registroFim,
            'query_string' => $queryString,
            'filtro_contato' => $filtroContato,
            'filtro_numero' => $filtroNumero,
            'filtro_status' => $filtroStatus,
            'filtro_nome' => $filtroNome,
            'aba_atual' => $aba,
            'usuarios' => $usuarios
        ];

        $this->view('chat/index', $dados);
    }

    /**
     * [ conversasNaoAtribuidas ] - Lista conversas não atribuídas a nenhum usuário
     */
    public function conversasNaoAtribuidas()
    {
        // Verificar permissão - apenas admins e analistas
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        // Parâmetros de filtro
        $filtroContato = $_GET['filtro_contato'] ?? '';
        $filtroNumero = $_GET['filtro_numero'] ?? '';

        // Parâmetros de paginação
        $registrosPorPagina = 10;
        $paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $paginaAtual = max(1, $paginaAtual);

        // Calcular offset
        $offset = ($paginaAtual - 1) * $registrosPorPagina;

        // Buscar conversas não atribuídas com filtros e paginação
        $conversas = $this->chatModel->buscarConversasNaoAtribuidas(
            $filtroContato,
            $filtroNumero,
            $registrosPorPagina,
            $offset
        );

        // Contar total de registros para paginação
        $totalRegistros = $this->chatModel->contarConversasNaoAtribuidas(
            $filtroContato,
            $filtroNumero
        );

        // Calcular informações de paginação
        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
        $registroInicio = $totalRegistros > 0 ? $offset + 1 : 0;
        $registroFim = min($offset + $registrosPorPagina, $totalRegistros);

        // Construir query string para manter filtros na paginação
        $queryParams = [];
        if (!empty($filtroContato)) {
            $queryParams[] = 'filtro_contato=' . urlencode($filtroContato);
        }
        if (!empty($filtroNumero)) {
            $queryParams[] = 'filtro_numero=' . urlencode($filtroNumero);
        }
        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';

        // Buscar lista de usuários para atribuição
        $usuarios = $this->chatModel->buscarUsuariosParaAtribuicao();

        $dados = [
            'tituloPagina' => 'Conversas Não Atribuídas',
            'conversas' => $conversas,
            'total_registros' => $totalRegistros,
            'total_paginas' => $totalPaginas,
            'pagina_atual' => $paginaAtual,
            'registro_inicio' => $registroInicio,
            'registro_fim' => $registroFim,
            'query_string' => $queryString,
            'filtro_contato' => $filtroContato,
            'filtro_numero' => $filtroNumero,
            'usuarios' => $usuarios
        ];

        $this->view('chat/conversas_nao_atribuidas', $dados);
    }

    /**
     * [ atribuirConversa ] - Atribui uma conversa a um usuário
     */
    public function atribuirConversa()
    {
        // Verificar permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('chat/index');
            return;
        }

        $conversa_id = $_POST['conversa_id'] ?? null;
        $usuario_id = $_POST['usuario_id'] ?? null;

        // Validação básica
        if (empty($conversa_id) || empty($usuario_id)) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Dados incompletos para atribuição', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Verificar se a conversa existe e está não atribuída
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        if ($conversa->usuario_id !== null && $conversa->usuario_id !== 0) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa já está atribuída a outro usuário', 'alert alert-warning');
            Helper::redirecionar('chat/index');
            return;
        }

        // ========== NOVO: CONTROLE DE CONVERSAS CONFLITANTES ==========
        
        // Verificar se existem outras conversas ativas do mesmo contato
        $conversasConflitantes = $this->chatModel->buscarConversasAtivasDoContato($conversa->contato_numero, $conversa_id);
        
        $mensagensInfo = [];
        
        if (!empty($conversasConflitantes)) {
            // Fechar conversas conflitantes
            $conversasFechadas = $this->chatModel->fecharConversasConflitantes(
                $conversa->contato_numero, 
                $conversa_id, 
                $usuario_id
            );
            
            if (!empty($conversasFechadas)) {
                $nomeAgentes = array_map(function($conv) {
                    return $conv['agente_anterior'] ?? 'Agente ID ' . $conv['usuario_id_anterior'];
                }, $conversasFechadas);
                
                $mensagensInfo[] = '<i class="fas fa-info-circle"></i> ' . count($conversasFechadas) . ' conversa(s) conflitante(s) foram fechadas automaticamente (agentes: ' . implode(', ', $nomeAgentes) . ')';
                
                error_log("CONTROLE CONFLITO: Fechadas " . count($conversasFechadas) . " conversas conflitantes para o contato " . $conversa->contato_numero);
            }
        }

        // ========== FIM CONTROLE DE CONVERSAS CONFLITANTES ==========

        // Atribuir a conversa
        if ($this->chatModel->atribuirConversa($conversa_id, $usuario_id)) {
            $mensagem = '<i class="fas fa-check"></i> Conversa atribuída com sucesso';
            
            // Adicionar informações sobre conversas fechadas
            if (!empty($mensagensInfo)) {
                $mensagem .= '<br><small>' . implode('<br>', $mensagensInfo) . '</small>';
            }
            
            Helper::mensagem('chat', $mensagem, 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Conversa atribuída com sucesso', 'success');    
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao atribuir conversa', 'alert alert-danger');
        }

        Helper::redirecionar('chat/index');
    }

    /**
     * Exibe conversa específica
     */
    public function conversa($conversa_id = null)
    {
        if (!$conversa_id) {
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        
        if (!$conversa) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // ========== NOVO: CONTROLE DE CONVERSAS CONFLITANTES ==========
        
        // Verificar se existe conflito de agentes para o mesmo contato
        $conversasConflitantes = $this->chatModel->buscarConversasAtivasDoContato($conversa->contato_numero, $conversa_id);
        
        if (!empty($conversasConflitantes)) {
            // Se o usuário atual tem uma conversa ativa com este contato, redirecionar para ela
            foreach ($conversasConflitantes as $conversaConflitante) {
                if ($conversaConflitante->usuario_id == $_SESSION['usuario_id']) {
                    Helper::mensagem('chat', '<i class="fas fa-info-circle"></i> Você já tem uma conversa ativa com este contato. Redirecionando...', 'alert alert-info');
                    Helper::redirecionar("chat/conversa/{$conversaConflitante->id}");
                    return;
                }
            }
        }

        // ========== FIM CONTROLE DE CONVERSAS CONFLITANTES ==========

        // Verificar permissão de acesso à conversa
        $temPermissao = false;
        $podeTomarConversa = false;
        
        // 1. Se a conversa pertence ao usuário logado
        if ($conversa->usuario_id == $_SESSION['usuario_id']) {
            $temPermissao = true;
        }
        
        // 2. Se é admin/analista e a conversa não está atribuída (para visualização de conversas não atribuídas)
        if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista']) && 
            ($conversa->usuario_id === null || $conversa->usuario_id == 0)) {
            $temPermissao = true;
        }
        
        // 3. NOVO: Se é admin/analista e a conversa está atribuída a outro usuário, pode "tomar" a conversa
        if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista']) && 
            $conversa->usuario_id !== null && $conversa->usuario_id != 0 && 
            $conversa->usuario_id != $_SESSION['usuario_id']) {
            $podeTomarConversa = true;
        }
        
        if (!$temPermissao && !$podeTomarConversa) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado a esta conversa', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Processar ações POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acao = $_POST['acao'] ?? '';

            switch ($acao) {
                case 'verificar_status':
                    $this->processarVerificacaoStatusManual($conversa_id, $conversa);
                    break;
                    
                case 'tomar_conversa':
                    if ($podeTomarConversa) {
                        $this->processarTomarConversa($conversa_id, $conversa);
                        return; // Função já redireciona
                    }
                    break;
            }
        }

        // ========== NOVO: EXIBIR AVISO SE PODE TOMAR CONVERSA ==========
        
        if ($podeTomarConversa) {
            // Buscar nome do agente atual
            $usuarioAtual = $this->chatModel->buscarUsuarioPorId($conversa->usuario_id);
            $nomeAgenteAtual = $usuarioAtual ? $usuarioAtual->nome : 'Agente ID ' . $conversa->usuario_id;
            
            Helper::mensagem('chat', 
                '<i class="fas fa-exclamation-triangle"></i> <strong>Atenção:</strong> Esta conversa está sendo atendida por <strong>' . $nomeAgenteAtual . '</strong>. ' .
                '<form method="POST" style="display:inline;"><input type="hidden" name="acao" value="tomar_conversa">' .
                '<button type="submit" class="btn btn-warning btn-sm" onclick="return confirm(\'Tem certeza que deseja assumir esta conversa? Isso fechará a conversa do outro agente.\')"><i class="fas fa-hand-paper"></i> Assumir Conversa</button></form>', 
                'alert alert-warning'
            );
            
            // Não permitir envio de mensagens enquanto não tomar a conversa
            // $mensagens = [];
            $mensagens = $this->chatModel->buscarMensagens($conversa_id);
            $dados = [
                'tituloPagina' => 'Conversa - ' . $conversa->contato_nome . ' (Conflito)',
                'conversa' => $conversa,
                'mensagens' => $mensagens,
                'bloqueado' => true
            ];
            
            $this->view('chat/conversa', $dados);
            return;
        }

        // ========== FIM CONTROLE ==========

        $mensagens = $this->chatModel->buscarMensagens($conversa_id);

        $dados = [
            'tituloPagina' => 'Conversa - ' . $conversa->contato_nome,
            'conversa' => $conversa,
            'mensagens' => $mensagens
        ];

        $this->view('chat/conversa', $dados);
    }

    /**
     * NOVO: Processa a ação de "tomar" uma conversa de outro agente
     */
    private function processarTomarConversa($conversa_id, $conversa)
    {
        // Verificar se é admin/analista
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Buscar nome do agente atual
        $usuarioAtual = $this->chatModel->buscarUsuarioPorId($conversa->usuario_id);
        $nomeAgenteAtual = $usuarioAtual ? $usuarioAtual->nome : 'Agente ID ' . $conversa->usuario_id;

        // Fechar conversas conflitantes
        $conversasFechadas = $this->chatModel->fecharConversasConflitantes(
            $conversa->contato_numero, 
            $conversa_id, 
            $_SESSION['usuario_id']
        );

        // Atribuir conversa ao usuário atual
        if ($this->chatModel->atribuirConversa($conversa_id, $_SESSION['usuario_id'])) {
            $mensagem = '<i class="fas fa-check"></i> Conversa assumida com sucesso!';
            
            if (!empty($conversasFechadas)) {
                $mensagem .= '<br><small><i class="fas fa-info-circle"></i> Conversa anterior de ' . $nomeAgenteAtual . ' foi fechada automaticamente.</small>';
            }
            
            Helper::mensagem('chat', $mensagem, 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Conversa assumida com sucesso', 'success');
            
            error_log("CONVERSA ASSUMIDA: Usuário {$_SESSION['usuario_id']} assumiu conversa {$conversa_id} do agente {$nomeAgenteAtual}");
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao assumir conversa', 'alert alert-danger');
        }

        Helper::redirecionar("chat/conversa/{$conversa_id}");
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

            
            // Verifica se o número tem pelo menos 11 dígitos
            if (strlen($numero) < 11) {
                Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> O número deve ter pelo menos 11 dígitos', 'alert alert-danger');
                Helper::mensagemSweetAlert('chat', 'O número deve ter pelo menos 11 dígitos', 'error');
                Helper::redirecionar('chat/novaConversa');
                return;
            }

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
            
            if ($this->chatModel->atualizarContatoConversa($dados)) {
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
        // DEBUG: Log inicial
        error_log("🚀 === INÍCIO ENVIAR MENSAGEM ===");
        error_log("🚀 Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("🚀 Conversa ID: " . ($conversa_id ?? 'null'));
        error_log("🚀 POST Data: " . print_r($_POST, true));
        error_log("🚀 FILES Data: " . print_r($_FILES, true));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("❌ Método não é POST");
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        if (!$conversa_id) {
            error_log("❌ ID da conversa não informado");
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> ID da conversa não informado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        // Buscar conversa
        error_log("🔍 Buscando conversa: {$conversa_id}");
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);

        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            error_log("❌ Conversa não encontrada ou sem permissão");
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        error_log("✅ Conversa encontrada: {$conversa->contato_nome} ({$conversa->contato_numero})");

        $mensagem = trim($_POST['mensagem'] ?? '');
        
        // Verificar arquivo em ambos os inputs
        $arquivo = null;
        $temArquivo = false;
        $tipoInput = '';
        
        // DEBUG: Verificar arquivos
        error_log("🔍 Verificando arquivos enviados...");
        if (isset($_FILES['midia']) && $_FILES['midia']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['midia'];
            $temArquivo = true;
            $tipoInput = 'midia';
            error_log("✅ Arquivo encontrado em 'midia': {$arquivo['name']} ({$arquivo['type']}, {$arquivo['size']} bytes)");
        } elseif (isset($_FILES['audio_gravado']) && $_FILES['audio_gravado']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['audio_gravado'];
            $temArquivo = true;
            $tipoInput = 'audio_gravado';
            error_log("✅ Arquivo encontrado em 'audio_gravado': {$arquivo['name']} ({$arquivo['type']}, {$arquivo['size']} bytes)");
        } else {
            error_log("❌ Nenhum arquivo válido encontrado");
            if (isset($_FILES['midia'])) {
                error_log("   midia error: " . $_FILES['midia']['error']);
            }
            if (isset($_FILES['audio_gravado'])) {
                error_log("   audio_gravado error: " . $_FILES['audio_gravado']['error']);
            }
        }

        // Verificar se há mensagem ou arquivo
        if (empty($mensagem) && !$temArquivo) {
            error_log("❌ Nem mensagem nem arquivo foram enviados");
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> É necessário informar uma mensagem ou anexar um arquivo', 'alert alert-danger');
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        // Verificar erro de upload APENAS se um arquivo foi selecionado (mas falhou)
        if ($arquivo && $arquivo['error'] !== UPLOAD_ERR_OK) {
            $errosUpload = [
                UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
                UPLOAD_ERR_PARTIAL => 'Upload parcial',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
                UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
                UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
            ];

            $erroMsg = $errosUpload[$arquivo['error']] ?? 'Erro desconhecido no upload';
            error_log("❌ Erro no upload: " . $erroMsg);
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro no upload: ' . $erroMsg, 'alert alert-danger');
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        // Verificar se é a primeira mensagem
        error_log("🔍 Verificando se é primeira mensagem...");
        $mensagensExistentes = $this->chatModel->contarMensagens($conversa_id);
        $precisaTemplate = ($mensagensExistentes == 0);
        error_log("📊 Mensagens existentes: {$mensagensExistentes}, Precisa template: " . ($precisaTemplate ? 'sim' : 'não'));

        $resultado = null;

        try {
            if ($temArquivo) {
                // DEBUG: Log específico para áudio
                if ($tipoInput === 'audio_gravado') {
                    error_log("🎵 === PROCESSANDO ÁUDIO GRAVADO ===");
                    error_log("🎵 Tipo de input: {$tipoInput}");
                    error_log("🎵 Arquivo: {$arquivo['name']}");
                    error_log("🎵 Tipo MIME: {$arquivo['type']}");
                    error_log("🎵 Tamanho: {$arquivo['size']} bytes");
                    error_log("🎵 Mensagem/Caption: " . ($mensagem ?: 'vazio'));
                    error_log("🎵 === CHAMANDO processarEnvioMidia ===");
                }
                
                // Processar envio de mídia
                $resultado = $this->processarEnvioMidia($conversa, $arquivo, $mensagem, $precisaTemplate);
                
                if ($tipoInput === 'audio_gravado') {
                    error_log("🎵 === RESULTADO processarEnvioMidia ===");
                    error_log("🎵 Status: " . ($resultado['status'] ?? 'indefinido'));
                    if (isset($resultado['error'])) {
                        error_log("🎵 Erro: " . $resultado['error']);
                    }
                    error_log("🎵 === FIM RESULTADO ===");
                }
            } else {
                // Processar envio de texto
                error_log("📝 Enviando mensagem de texto: " . substr($mensagem, 0, 50) . "...");

                if ($precisaTemplate) {
                    // Primeira mensagem - tentar template, se falhar usar mensagem normal
                    $resultado = $this->enviarPrimeiraMensagem($conversa->contato_numero, $mensagem);

                    // Se o template falhar, tentar mensagem normal
                    if (!$resultado || ($resultado['status'] !== 200 && $resultado['status'] !== 201)) {
                        error_log("⚠️ Template falhou, tentando mensagem normal");
                        $resultado = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $mensagem);
                    }
                } else {
                    // Conversa já iniciada - enviar mensagem normal
                    $resultado = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $mensagem);
                }
            }

            error_log("📊 Resultado final: Status " . ($resultado['status'] ?? 'indefinido'));
            
            if ($resultado && ($resultado['status'] == 200 || $resultado['status'] == 201)) {
                error_log("✅ Envio bem-sucedido!");
                
                // Salvar no banco
                $messageId = $resultado['response']['id'] ?? uniqid();

                $dadosMensagem = [
                    'conversa_id' => $conversa_id,
                    'remetente_id' => $_SESSION['usuario_id'],
                    'message_id' => $messageId,
                    'status' => 'enviado',
                    'enviado_em' => date('Y-m-d H:i:s')
                ];

                if ($temArquivo) {
                    // Determinar tipo de mídia para salvar no banco
                    $tipoMidia = $this->determinarTipoMidia($arquivo['type']);

                    $dadosMensagem['tipo'] = $tipoMidia;
                    $dadosMensagem['conteudo'] = $mensagem; // Caption se houver
                    $dadosMensagem['midia_nome'] = $arquivo['name'];
                    
                    // ✅ NOVO: Salvar mídia enviada no MinIO
                    $resultadoMinIO = $this->salvarMidiaEnviadaMinIO($arquivo, $tipoMidia);
                    
                    if ($resultadoMinIO['sucesso']) {
                        // Salvar caminho do MinIO (igual às mensagens recebidas)
                        $dadosMensagem['midia_url'] = $resultadoMinIO['caminho_minio'];
                        error_log("✅ Mídia ENVIADA salva no MinIO: {$resultadoMinIO['caminho_minio']}");
                    } else {
                        // Se falhar o MinIO, continua sem salvar o caminho
                        $dadosMensagem['midia_url'] = null;
                        error_log("⚠️ Falha ao salvar mídia ENVIADA no MinIO: " . $resultadoMinIO['erro']);
                    }
                } else {
                    $dadosMensagem['tipo'] = 'text';
                    $dadosMensagem['conteudo'] = $mensagem;
                }

                $this->chatModel->salvarMensagem($dadosMensagem);

                // Atualizar conversa
                $this->chatModel->atualizarConversa($conversa_id);

                Helper::mensagem('chat', '<i class="fas fa-check"></i> ' . ($temArquivo ? 'Mídia enviada' : 'Mensagem enviada') . ' com sucesso', 'alert alert-success');
            } else {
                $erro = $resultado['error'] ?? 'Erro desconhecido';
                error_log("❌ ERRO ENVIO: " . print_r($resultado, true));
                Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao enviar: ' . $erro, 'alert alert-danger');
            }
        } catch (Exception $e) {
            error_log("❌ EXCEÇÃO ENVIO: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro interno: ' . $e->getMessage(), 'alert alert-danger');
        }

        error_log("🚀 === FIM ENVIAR MENSAGEM ===");
        Helper::redirecionar("chat/conversa/{$conversa_id}");
    }

    /**
     * Processa envio de mídia
     */
    private function processarEnvioMidia($conversa, $arquivo, $caption, $precisaTemplate)
    {
        // DEBUG: Log inicial para áudio
        if (strpos($arquivo['type'], 'audio/') === 0 || strpos($arquivo['name'], 'audio_gravado') !== false) {
            error_log("🎵 === INÍCIO DEBUG ÁUDIO ===");
            error_log("🎵 Arquivo recebido:");
            error_log("🎵   Nome: {$arquivo['name']}");
            error_log("🎵   Tipo: {$arquivo['type']}");
            error_log("🎵   Tamanho: {$arquivo['size']} bytes");
            error_log("🎵   Tmp: {$arquivo['tmp_name']}");
            error_log("🎵   Caption: " . ($caption ? $caption : 'vazio'));
            error_log("🎵   PrecisaTemplate: " . ($precisaTemplate ? 'sim' : 'não'));
            error_log("🎵 === === === === === ===");
        }

        // === CORREÇÃO: Normalização avançada de tipos MIME ===
        $tipoOriginal = $arquivo['type'];
        $tipoNormalizado = $tipoOriginal;
        $isAudioGravado = strpos($arquivo['name'], 'audio_gravado') !== false;
        
        // Para áudios gravados, aplicar normalização específica
        if ($isAudioGravado || strpos($arquivo['type'], 'audio/') === 0) {
            
            // Verificar se o arquivo realmente existe e ler conteúdo
            if (!file_exists($arquivo['tmp_name'])) {
                error_log("❌ ÁUDIO: Arquivo temporário não existe: {$arquivo['tmp_name']}");
                throw new Exception('Arquivo temporário não encontrado');
            }
            
            $conteudoArquivo = file_get_contents($arquivo['tmp_name']);
            $tamanhoReal = strlen($conteudoArquivo);
            
            error_log("🎵 VERIFICAÇÃO ARQUIVO:");
            error_log("🎵   Tamanho relatado: {$arquivo['size']} bytes");
            error_log("🎵   Tamanho real: {$tamanhoReal} bytes");
            
            if ($tamanhoReal === 0) {
                error_log("❌ ÁUDIO: Arquivo vazio");
                throw new Exception('Arquivo de áudio está vazio');
            }
            
            // === CORREÇÃO: Detecção inteligente de formato ===
            $primeirosBytes = substr($conteudoArquivo, 0, 16);
            $tipoDetectado = $this->detectarTipoAudio($primeirosBytes, $arquivo['type']);
            
            if ($tipoDetectado && $tipoDetectado !== $tipoOriginal) {
                error_log("🎵 NORMALIZAÇÃO INTELIGENTE: {$tipoOriginal} → {$tipoDetectado}");
                $tipoNormalizado = $tipoDetectado;
            }
            
            // Simplificar tipos complexos
            if (strpos($tipoNormalizado, ';') !== false) {
                $parteTipo = explode(';', $tipoNormalizado)[0];
                error_log("🎵 SIMPLIFICAÇÃO MIME: {$tipoNormalizado} → {$parteTipo}");
                $tipoNormalizado = $parteTipo;
            }
            
            // Atualizar o tipo no array
            $arquivo['type'] = $tipoNormalizado;
            
            error_log("🎵 TIPO FINAL: {$tipoNormalizado}");
        }

        // Validar arquivo
        $validacao = $this->validarArquivoMidia($arquivo);
        if (!$validacao['valido']) {
            error_log("❌ ÁUDIO: Falha na validação - " . $validacao['erro']);
            throw new Exception($validacao['erro']);
        }

        // Log específico para áudio
        if (strpos($arquivo['type'], 'audio/') === 0) {
            error_log("✅ ÁUDIO: Validação passou!");
        }

        // === CORREÇÃO: Upload com retry para áudios gravados ===
        error_log("🔄 ÁUDIO: Iniciando upload para API SERPRO...");
        
        $tentativas = $isAudioGravado ? 2 : 1; // Retry para áudios gravados
        $resultadoUpload = null;
        
        for ($tentativa = 1; $tentativa <= $tentativas; $tentativa++) {
            if ($tentativa > 1) {
                error_log("🔄 ÁUDIO: Tentativa {$tentativa} de upload...");
                sleep(1); // Aguardar 1 segundo entre tentativas
            }
            
            $resultadoUpload = SerproHelper::uploadMidia($arquivo, $arquivo['type']);
            
            // DEBUG: Log resultado do upload
            if (strpos($arquivo['type'], 'audio/') === 0) {
                error_log("🎵 UPLOAD TENTATIVA {$tentativa}:");
                error_log("🎵   Status: " . $resultadoUpload['status']);
                error_log("🎵   Response: " . json_encode($resultadoUpload['response'] ?? []));
                if (isset($resultadoUpload['error'])) {
                    error_log("🎵   Erro: " . $resultadoUpload['error']);
                }
            }
            
            // Se sucesso, parar tentativas
            if ($resultadoUpload['status'] === 200 || $resultadoUpload['status'] === 201) {
                break;
            }
        }

        // Verificar se upload foi bem-sucedido
        if ($resultadoUpload['status'] !== 200 && $resultadoUpload['status'] !== 201) {
            $errorMsg = 'Erro no upload da mídia: ' . ($resultadoUpload['error'] ?? 'Erro desconhecido');
            
            // Para áudios gravados, dar erro mais específico
            if ($isAudioGravado) {
                $errorMsg = 'Erro no upload do áudio gravado: ' . ($resultadoUpload['error'] ?? 'Formato não suportado pela API');
            }
            
            error_log("❌ ÁUDIO: " . $errorMsg);
            throw new Exception($errorMsg);
        }

        $mediaId = $resultadoUpload['response']['id'];
        error_log("✅ ÁUDIO: Upload bem-sucedido - Media ID: $mediaId");

        // Determinar tipo de mídia
        $tipoMidia = $this->mapearTipoMidiaParaAPI($arquivo['type']);

        // === CORREÇÃO: Preparar parâmetros de envio específicos para áudio ===
        $filename = null;
        $captionParaEnvio = null;

        if ($tipoMidia === 'document') {
            // Para documentos: filename obrigatório, caption não permitido
            $filename = $arquivo['name'];
            error_log("MÍDIA: Enviando documento com filename: $filename");

            // Se há caption, enviar como mensagem de texto separada APÓS o documento
            if (!empty($caption)) {
                error_log("MÍDIA: Caption será enviado como mensagem separada após documento");
            }
        } elseif ($tipoMidia === 'audio') {
            // === CORREÇÃO: Para áudios, testar envio com e sem caption ===
            $captionParaEnvio = $caption;
            error_log("🎵 ÁUDIO: Enviando $tipoMidia" . ($caption ? " com caption" : " sem caption"));
            
            if ($isAudioGravado) {
                error_log("🎵 ÁUDIO GRAVADO: Preparando envio especial");
            }
        } else {
            // Para outras mídias
            $captionParaEnvio = $caption;
            error_log("MÍDIA: Enviando $tipoMidia" . ($caption ? " com caption" : " sem caption"));
        }

        // === CORREÇÃO: Template apenas se necessário e não for áudio gravado simples ===
        if ($precisaTemplate && !empty($caption) && $tipoMidia !== 'document') {
            error_log("🎵 ÁUDIO: Enviando template primeiro...");
            $resultadoTemplate = $this->enviarPrimeiraMensagem($conversa->contato_numero, $caption);

            if ($resultadoTemplate['status'] !== 200 && $resultadoTemplate['status'] !== 201) {
                error_log("❌ ÁUDIO: Falha no template - " . ($resultadoTemplate['error'] ?? 'Erro desconhecido'));
                throw new Exception('Erro ao enviar template: ' . ($resultadoTemplate['error'] ?? 'Erro desconhecido'));
            }

            error_log("✅ ÁUDIO: Template enviado com sucesso");
            sleep(1);
            $captionParaEnvio = null; // Não enviar caption novamente
        }

        // DEBUG: Log antes do envio
        if (strpos($arquivo['type'], 'audio/') === 0) {
            error_log("🎵 ÁUDIO: Preparando envio da mídia...");
            error_log("🎵   Destinatário: {$conversa->contato_numero}");
            error_log("🎵   Tipo mídia: {$tipoMidia}");
            error_log("🎵   Media ID: {$mediaId}");
            error_log("🎵   Caption: " . ($captionParaEnvio ? $captionParaEnvio : 'null'));
            error_log("🎵   Filename: " . ($filename ? $filename : 'null'));
        }

        // === CORREÇÃO: Envio com retry para áudios gravados ===
        $tentativasEnvio = $isAudioGravado ? 2 : 1;
        $resultadoEnvio = null;
        
        for ($tentativa = 1; $tentativa <= $tentativasEnvio; $tentativa++) {
            if ($tentativa > 1) {
                error_log("🔄 ÁUDIO: Tentativa {$tentativa} de envio...");
                sleep(2); // Aguardar mais tempo entre tentativas de envio
            }
            
            $resultadoEnvio = SerproHelper::enviarMidia($conversa->contato_numero, $tipoMidia, $mediaId, $captionParaEnvio, null, $filename);
            
            // DEBUG: Log resultado do envio
            if (strpos($arquivo['type'], 'audio/') === 0) {
                error_log("🎵 ENVIO TENTATIVA {$tentativa}:");
                error_log("🎵   Status: " . $resultadoEnvio['status']);
                error_log("🎵   Response: " . json_encode($resultadoEnvio['response'] ?? []));
                if (isset($resultadoEnvio['error'])) {
                    error_log("🎵   Erro: " . $resultadoEnvio['error']);
                }
            }
            
            // Se sucesso, parar tentativas
            if ($resultadoEnvio['status'] === 200 || $resultadoEnvio['status'] === 201) {
                break;
            }
        }
        
        error_log("MÍDIA: Resultado envio - Status: " . $resultadoEnvio['status']);

        // Se documento foi enviado com sucesso e há caption, enviar como mensagem separada
        if (
            $tipoMidia === 'document' &&
            ($resultadoEnvio['status'] === 200 || $resultadoEnvio['status'] === 201) &&
            !empty($caption)
        ) {
            error_log("MÍDIA: Enviando caption como mensagem separada...");
            sleep(1);
            $resultadoCaption = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $caption);
            error_log("MÍDIA: Caption enviado - Status: " . ($resultadoCaption['status'] ?? 'erro'));
        }

        if (strpos($arquivo['type'], 'audio/') === 0) {
            error_log("🎵 === FIM DEBUG ÁUDIO ===");
        }

        return $resultadoEnvio;
    }

    /**
     * Detecta tipo de áudio pelos bytes iniciais
     */
    private function detectarTipoAudio($primeirosBytes, $tipoOriginal)
    {
        // Assinaturas conhecidas de formatos de áudio
        $assinaturas = [
            'OggS' => 'audio/ogg',
            'ID3' => 'audio/mpeg',
            'RIFF' => 'audio/wav', // Pode ser WAV ou WebM
            'ftyp' => 'audio/mp4'
        ];
        
        foreach ($assinaturas as $assinatura => $tipo) {
            if (strpos($primeirosBytes, $assinatura) !== false) {
                // Para RIFF, verificar se é WebM
                if ($assinatura === 'RIFF' && strpos($primeirosBytes, 'WEBM') !== false) {
                    return 'audio/webm';
                }
                return $tipo;
            }
        }
        
        // Se não detectou nada, retornar o tipo original
        return $tipoOriginal;
    }

    /**
     * Valida arquivo de mídia
     */
    private function validarArquivoMidia($arquivo)
    {
        // DEBUG: Log do arquivo recebido
        error_log("🔍 DEBUG VALIDAÇÃO: Nome: {$arquivo['name']}, Tipo: {$arquivo['type']}, Tamanho: {$arquivo['size']}");
        
        // DEBUG específico para áudio gravado
        if (strpos($arquivo['name'], 'audio_gravado') !== false) {
            error_log("🎵 VALIDAÇÃO ÁUDIO GRAVADO: Arquivo detectado - Tipo: {$arquivo['type']}");
        }
        
        // === CORREÇÃO: Lista expandida de tipos aceitos ===
        $tiposPermitidos = [
            // Imagens
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            
            // Vídeo
            'video/mp4',
            'video/3gpp',
            'video/quicktime',
            
            // Áudio (EXPANDIDO para suportar todos os formatos do MediaRecorder)
            'audio/aac',
            'audio/amr', 
            'audio/mpeg',           // MP3
            'audio/mp3',            // Variação MP3
            'audio/mp4',            // M4A
            'audio/x-m4a',          // Variação M4A
            'audio/ogg',            // OGG
            'audio/ogg;codecs=opus',    // OGG com codec opus
            'audio/ogg;codecs=vorbis',  // OGG com codec vorbis
            'audio/webm',           // WebM audio
            'audio/webm;codecs=opus',   // WebM com opus
            'audio/wav',            // WAV (algumas implementações)
            
            // Documentos
            'application/pdf',
            'application/msword',
            'text/plain',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        // === CORREÇÃO: Verificação especial para arquivos de áudio ===
        $isAudioByExtension = preg_match('/\.(m4a|ogg|mp3|aac|amr|mp4|webm|wav)$/i', $arquivo['name']);
        $isAudioByType = strpos($arquivo['type'], 'audio/') === 0;
        $isAudioGravado = strpos($arquivo['name'], 'audio_gravado') !== false;
        
        // DEBUG: Log das verificações
        if ($isAudioByType || $isAudioGravado) {
            error_log("🎵 VALIDAÇÃO ÁUDIO:");
            error_log("🎵   Tipo permitido na lista? " . (in_array($arquivo['type'], $tiposPermitidos) ? 'SIM' : 'NÃO'));
            error_log("🎵   É áudio por extensão? " . ($isAudioByExtension ? 'SIM' : 'NÃO'));
            error_log("🎵   É áudio por tipo? " . ($isAudioByType ? 'SIM' : 'NÃO'));
            error_log("🎵   É áudio gravado? " . ($isAudioGravado ? 'SIM' : 'NÃO'));
        }
        
        // === CORREÇÃO: Lógica de validação mais permissiva para áudios ===
        $tipoValido = in_array($arquivo['type'], $tiposPermitidos) || 
                      ($isAudioByType && ($isAudioByExtension || $isAudioGravado));
        
        if (!$tipoValido) {
            error_log("❌ DEBUG VALIDAÇÃO: Arquivo rejeitado - Tipo: {$arquivo['type']}, Nome: {$arquivo['name']}");
            
            // Para áudios gravados, dar mensagem mais específica
            if ($isAudioGravado) {
                return ['valido' => false, 'erro' => 'Formato de áudio gravado não suportado: ' . $arquivo['type'] . '. Tente gravar novamente.'];
            }
            
            return ['valido' => false, 'erro' => 'Tipo de arquivo não permitido: ' . $arquivo['type']];
        }
        
        // Se passou na validação
        if ($isAudioByType || $isAudioGravado) {
            error_log("✅ DEBUG VALIDAÇÃO: Arquivo de áudio aceito - Tipo: {$arquivo['type']}, Nome: {$arquivo['name']}");
        }

        // === CORREÇÃO: Verificação de tamanho específica para áudios gravados ===
        $limiteTamanho = 5 * 1024 * 1024; // 5MB padrão
        
        if (strpos($arquivo['type'], 'video/') === 0 || strpos($arquivo['type'], 'audio/') === 0 || $isAudioByExtension) {
            $limiteTamanho = 16 * 1024 * 1024; // 16MB para vídeo/áudio
        } elseif (strpos($arquivo['type'], 'application/') === 0) {
            $limiteTamanho = 95 * 1024 * 1024; // 95MB para documentos
        }

        // Para áudios gravados, verificar tamanho mínimo também
        if ($isAudioGravado && $arquivo['size'] < 1024) {
            error_log("❌ DEBUG VALIDAÇÃO: Áudio gravado muito pequeno - {$arquivo['size']} bytes");
            return ['valido' => false, 'erro' => 'Áudio gravado muito pequeno. Grave por pelo menos 2 segundos.'];
        }

        if ($arquivo['size'] > $limiteTamanho) {
            $limiteMB = round($limiteTamanho / (1024 * 1024), 1);
            error_log("❌ DEBUG VALIDAÇÃO: Arquivo muito grande - {$arquivo['size']} bytes, limite: {$limiteTamanho} bytes");
            return ['valido' => false, 'erro' => "Arquivo muito grande. Limite: {$limiteMB}MB"];
        }

        error_log("✅ DEBUG VALIDAÇÃO: Arquivo validado com sucesso");
        return ['valido' => true, 'erro' => null];
    }

    /**
     * Mapeia tipo MIME para tipo da API
     */
    private function mapearTipoMidiaParaAPI($mimeType)
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            // === CORREÇÃO: Mapeamento mais abrangente para áudio ===
            return 'audio';
        } else {
            return 'document';
        }
    }

    /**
     * Determina tipo de mídia para salvar no banco
     */
    private function determinarTipoMidia($mimeType)
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            // === CORREÇÃO: Sempre retornar 'audio' para qualquer tipo de áudio ===
            return 'audio';
        } else {
            return 'document';
        }
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
            $resultado = SerproHelper::enviarMensagemTexto($numero, $mensagem);
        }

        if ($resultado['status'] == 200 || $resultado['status'] == 201) {
            // Salvar no banco
            $messageId = null;
            if (isset($resultado['response']['id'])) {
                $messageId = $resultado['response']['id'];
            }

            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa->id,
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'text',
                'conteudo' => $mensagem,
                'message_id' => $messageId ?? uniqid(),
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
        // Nome do template que deve estar aprovado na Meta
        $nomeTemplate = 'central_intimacao_remota'; // template aprovado

        // Parâmetros do template (se o template tiver variáveis)
        $parametros = [
            [
                'tipo' => 'text',
                'valor' => $mensagem
            ]

        ];

        return SerproHelper::enviarTemplate($numero, $nomeTemplate, $parametros);
    }

    /**
     * [ verificarStatusAPI ] - Verifica se a API do SERPRO está online (AJAX)
     */
    public function verificarStatusAPI()
    {
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

    /**
     * Webhook para receber mensagens do SERPRO
     */
    public function webhook()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $verify_token = $_GET['hub_verify_token'] ?? '';
            $challenge = $_GET['hub_challenge'] ?? '';

            if ($verify_token === WEBHOOK_VERIFY_TOKEN) {
                echo $challenge;
                exit;
            } else {
                http_response_code(403);
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = json_decode(file_get_contents("php://input"), true);

            // Registrar o payload bruto no log
            file_put_contents("log.txt", "RAW: " . json_encode($payload) . "\n", FILE_APPEND);

            $mensagemTexto = '';
            $numero = '';
            $messageId = '';
            $timestamp = '';

            // 1. Detectar mensagem vinda do WhatsApp SERPRO
            if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])) {
                $mensagemTexto = $payload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
                $numero = $payload['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $messageId = $payload['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $timestamp = $payload['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'];
            }
            // 2. Detectar mensagem vinda do n8n
            elseif (isset($payload['messages'][0]['text']['body'])) {
                $mensagemTexto = $payload['messages'][0]['text']['body'];
                $numero = $payload['messages'][0]['from'];
                $messageId = $payload['messages'][0]['id'] ?? uniqid('n8n_');
                $timestamp = $payload['messages'][0]['timestamp'] ?? time();
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Formato de mensagem não reconhecido']);
                exit;
            }

            // Registrar a mensagem simples no log
            file_put_contents("log.txt", "Mensagem: $mensagemTexto | De: $numero\n", FILE_APPEND);

            // 3. Processar mensagem do n8n diretamente
            if (isset($payload['messages'][0])) {
                $this->processarMensagemN8n($payload['messages'][0]);
            }
            // 4. Processar estrutura SERPRO padrão
            else {
                $mensagem = SerproHelper::processarWebhook($payload);
                if ($mensagem && $mensagem['type'] !== 'status') {
                    $this->processarMensagemRecebida($mensagem);
                }
            }

            echo json_encode(['data' => 'OK']);
        }
    }

    /**
     * Processa mensagem recebida do n8n
     */
    private function processarMensagemN8n($mensagemData)
    {
        try {
            // Carrega o helper do MinIO
            require_once APPROOT . '/Libraries/MinioHelper.php';
            
            $numero = $mensagemData['from'];
            $messageId = $mensagemData['id'] ?? uniqid('n8n_');
            $timestamp = $mensagemData['timestamp'] ?? time();
            $tipo = $mensagemData['type'] ?? 'text';
            
            // Extrair conteúdo e informações de mídia baseado no tipo
            $conteudo = '';
            $midiaId = null;
            $midiaTipo = null;
            $midiaFilename = null;
            $midiaUrl = null;
            $caminhoMinio = null;
            
            switch ($tipo) {
                case 'text':
                    $conteudo = $mensagemData['text']['body'] ?? '';
                    break;
                    
                case 'image':
                    $midiaId = $mensagemData['image']['id'] ?? '';
                    $midiaTipo = $mensagemData['image']['mime_type'] ?? 'image/jpeg';
                    $conteudo = $mensagemData['image']['caption'] ?? ''; // Temporário, será substituído pelo caminho do MinIO
                    // $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
                    break;
                    
                case 'audio':
                    $midiaId = $mensagemData['audio']['id'] ?? '';
                    $midiaTipo = $mensagemData['audio']['mime_type'] ?? 'audio/ogg';
                    // $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
                    $conteudo = $mensagemData['audio']['text'] ?? '';; // Temporário, será substituído pelo caminho do MinIO
                    break;
                    
                case 'video':
                    $midiaId = $mensagemData['video']['id'] ?? '';
                    $midiaTipo = $mensagemData['video']['mime_type'] ?? 'video/mp4';
                    $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
                    break;
                    
                case 'document':
                    $midiaId = $mensagemData['document']['id'] ?? '';
                    $midiaTipo = $mensagemData['document']['mime_type'] ?? 'application/octet-stream';
                    $midiaFilename = $mensagemData['document']['filename'] ?? 'documento';
                    // $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
                    $conteudo = $mensagemData['document']['caption'] ?? '';
                    break;
                    
                case 'button':
                    $conteudo = $mensagemData['button']['text'] ?? '';
                    break;
                    
                default:
                    $conteudo = json_encode($mensagemData);
            }

            // Buscar ou criar conversa
            $conversa = $this->chatModel->buscarOuCriarConversaPorNumero($numero);

            if ($conversa) {
                // Verificar se a mensagem já existe (evitar duplicatas)
                $mensagemExistente = $this->verificarMensagemExistente($messageId);
                
                if (!$mensagemExistente) {
                    // Se há mídia, fazer download da API SERPRO e upload para MinIO
                    if ($midiaId && in_array($tipo, ['image', 'audio', 'video', 'document'])) {
                        $resultadoDownload = $this->baixarESalvarMidiaMinIO($midiaId, $tipo, $midiaTipo, $midiaFilename);
                        
                        if ($resultadoDownload['sucesso']) {
                            // CORREÇÃO: Salvar apenas o caminho no banco, não a URL assinada
                            $caminhoMinio = $resultadoDownload['caminho_minio'];
                            $midiaFilename = $resultadoDownload['nome_arquivo'];
                            // $conteudo = $caminhoMinio; // Usar caminho do MinIO ao invés do ID
                            $midiaUrl = $caminhoMinio; // Salvar caminho no campo midia_url (não URL assinada)
                            
                            error_log("✅ Mídia N8N baixada e salva no MinIO: {$caminhoMinio}");
                        } else {
                            error_log("❌ Erro ao baixar/salvar mídia N8N: " . $resultadoDownload['erro']);
                            // Continua salvando com o ID da mídia mesmo se o download falhar
                        }
                    }
                    
                    // Salvar mensagem recebida
                    $dadosMensagem = [
                        'conversa_id' => $conversa->id,
                        'remetente_id' => null, // Mensagem recebida (não enviada pelo sistema)
                        'tipo' => $tipo,
                        'conteudo' => $conteudo,
                        'midia_url' => $midiaUrl, // (ex: document/2025/arquivo.pdf)
                        'midia_nome' => $midiaFilename,
                        'message_id' => $messageId,
                        'status' => 'recebido',
                        'enviado_em' => date('Y-m-d H:i:s', is_numeric($timestamp) ? $timestamp : strtotime($timestamp))
                    ];

                    $resultado = $this->chatModel->salvarMensagem($dadosMensagem);
                    
                    if ($resultado) {
                        // Atualizar conversa
                        $this->chatModel->atualizarConversa($conversa->id);
                        
                        // Log de sucesso
                        $tipoLog = $midiaId ? "mídia ($tipo)" : "texto";
                        error_log("✅ Mensagem N8N $tipoLog salva com sucesso: ID={$messageId}, Conversa={$conversa->id}");
                        
                        // Log específico para mídia
                        if ($midiaId && $midiaUrl) {
                            error_log("📁 Caminho salvo no banco: {$midiaUrl} (ao invés de URL assinada)");
                        }
                    } else {
                        error_log("❌ Erro ao salvar mensagem N8N no banco: " . print_r($dadosMensagem, true));
                    }
                } else {
                    error_log("⚠️ Mensagem N8N duplicada ignorada: ID={$messageId}");
                }
            } else {
                error_log("❌ Erro ao criar/buscar conversa N8N para número: {$numero}");
            }
            
        } catch (Exception $e) {
            error_log("❌ ERRO ao processar mensagem N8N: " . $e->getMessage());
            error_log("Dados da mensagem: " . print_r($mensagemData, true));
        }
    }

    /**
     * Baixa mídia da API SERPRO e salva no MinIO
     */
    private function baixarESalvarMidiaMinIO($midiaId, $tipo, $mimeType, $filename = null)
    {
        try {
            // Passo 1: Baixar mídia da API SERPRO
            $resultadoDownload = SerproHelper::downloadMidia($midiaId);
            
            if ($resultadoDownload['status'] !== 200) {
                return [
                    'sucesso' => false,
                    'erro' => 'Erro ao baixar mídia da API SERPRO: ' . ($resultadoDownload['error'] ?? 'Status ' . $resultadoDownload['status'])
                ];
            }
            
            // Passo 2: Upload para MinIO
            $resultadoUpload = MinioHelper::uploadMidia(
                $resultadoDownload['data'], 
                $tipo, 
                $mimeType, 
                $filename
            );
            
            if (!$resultadoUpload['sucesso']) {
                return [
                    'sucesso' => false,
                    'erro' => 'Erro ao fazer upload para MinIO: ' . $resultadoUpload['erro']
                ];
            }
            
            // Log de sucesso
            error_log("📁 Mídia {$midiaId} salva no MinIO: {$resultadoUpload['caminho_minio']} (Tamanho: " . 
                     number_format($resultadoUpload['tamanho'] / 1024, 2) . " KB)");
            
            return [
                'sucesso' => true,
                'caminho_minio' => $resultadoUpload['caminho_minio'],
                'url_minio' => $resultadoUpload['url_minio'],
                'nome_arquivo' => $resultadoUpload['nome_arquivo'],
                'tamanho' => $resultadoUpload['tamanho'],
                'mime_type' => $mimeType,
                'bucket' => $resultadoUpload['bucket']
            ];
            
        } catch (Exception $e) {
            error_log("❌ Erro ao baixar/salvar mídia {$midiaId}: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica se uma mensagem já existe no banco
     */
    private function verificarMensagemExistente($messageId)
    {
        try {
            return $this->chatModel->verificarMensagemExistente($messageId);
        } catch (Exception $e) {
            error_log("Erro ao verificar mensagem existente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * [ excluirConversa ] - Exclui uma conversa
     */
    public function excluirConversa($id = null)
    {
        // Verifica se tem permissão para acessar o módulo
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
        ) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::mensagemSweetAlert('dashboard', 'Acesso negado: Apenas administradores e analistas podem acessar essa página', 'error');
            Helper::redirecionar('dashboard/inicial');
        }
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> ID da conversa não fornecido', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Verificar se a conversa existe
        $conversa = $this->chatModel->buscarConversaPorId($id);
        if (!$conversa) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Excluir a conversa
        $resultado = $this->chatModel->excluirConversa($id);

        if ($resultado) {
            Helper::mensagem('chat', '<i class="fas fa-check-circle"></i> Conversa excluída com sucesso', 'alert alert-success');
            Helper::redirecionar('chat/index');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Erro ao excluir conversa', 'alert alert-danger');
            Helper::redirecionar('chat/index');
        }
    }

    /**
     * [ gerenciarTemplates ] - Gerencia templates do WhatsApp Business
     */
    public function gerenciarTemplates()
    {
        // Detectar se é uma requisição AJAX
        $isAjax = $_SERVER['REQUEST_METHOD'] == 'POST' ||
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 401, 'error' => 'Usuário não autenticado. Faça login novamente.']);
                return;
            }
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado. Apenas administradores podem gerenciar templates.']);
                return;
            }

            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Definir cabeçalho JSON para todas as respostas AJAX
            header('Content-Type: application/json');

            $acao = $_POST['acao'] ?? '';

            try {
                switch ($acao) {
                    case 'listar':
                        $templates = SerproHelper::listarTemplates();
                        echo json_encode($templates);
                        break;

                    case 'criar':
                        $dadosTemplate = [
                            'name' => $_POST['name'],
                            'category' => $_POST['category'],
                            'language' => $_POST['language'],
                            'components' => json_decode($_POST['components'], true)
                        ];
                        $resultado = SerproHelper::criarTemplate($dadosTemplate);
                        echo json_encode($resultado);
                        break;

                    case 'excluir':
                        $nomeTemplate = $_POST['nome_template'];
                        $resultado = SerproHelper::excluirTemplate($nomeTemplate);
                        echo json_encode($resultado);
                        break;

                    default:
                        echo json_encode(['status' => 400, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 500, 'error' => 'Erro interno: ' . $e->getMessage()]);
            }

            return;
        }

        // Para requisições GET, carregar templates diretamente no PHP
        $templates = [];
        $templateError = null;

        try {
            $resultado = SerproHelper::listarTemplates();
            if ($resultado['status'] == 200 && isset($resultado['response'])) {
                $templates = $resultado['response'];
            } else {
                $templateError = 'Erro ao carregar templates: ' . ($resultado['error'] ?? 'Erro desconhecido');
            }
        } catch (Exception $e) {
            $templateError = 'Erro ao conectar com a API: ' . $e->getMessage();
        }

        $dados = [
            'tituloPagina' => 'Gerenciar Templates',
            'templates' => $templates,
            'templateError' => $templateError
        ];

        $this->view('chat/templates', $dados);
    }

    /**
     * [ gerenciarWebhooks ] - Gerencia webhooks do SERPRO
     */
    public function gerenciarWebhooks()
    {
        // Detectar se é uma requisição AJAX
        $isAjax = $_SERVER['REQUEST_METHOD'] == 'POST' ||
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 401, 'error' => 'Usuário não autenticado. Faça login novamente.']);
                return;
            }
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado. Apenas administradores podem gerenciar webhooks.']);
                return;
            }

            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Definir cabeçalho JSON para todas as respostas AJAX
            header('Content-Type: application/json');

            $acao = $_POST['acao'] ?? '';

            try {
                switch ($acao) {
                    case 'listar':
                        $resultado = SerproHelper::listarWebhooks();
                        echo json_encode($resultado);
                        break;

                    case 'cadastrar':
                        $webhook = [
                            'uri' => $_POST['uri'],
                            'jwtToken' => $_POST['jwt_token'] ?? null
                        ];
                        $resultado = SerproHelper::cadastrarWebhook($webhook);
                        echo json_encode($resultado);
                        break;

                    case 'atualizar':
                        $webhook = [
                            'id' => $_POST['webhook_id'],
                            'uri' => $_POST['uri'],
                            'jwtToken' => $_POST['jwt_token'] ?? null
                        ];
                        $resultado = SerproHelper::atualizarWebhook($webhook);
                        echo json_encode($resultado);
                        break;

                    case 'excluir':
                        $webhookId = $_POST['webhook_id'];
                        $resultado = SerproHelper::excluirWebhook($webhookId);
                        echo json_encode($resultado);
                        break;

                    default:
                        echo json_encode(['status' => 400, 'error' => 'Ação não reconhecida']);
                        break;
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 500, 'error' => 'Erro interno: ' . $e->getMessage()]);
            }

            return;
        }

        // Para requisições GET, carregar webhooks diretamente no PHP
        $webhooks = [];
        $webhookError = null;

        try {
            $resultado = SerproHelper::listarWebhooks();
            if ($resultado['status'] == 200 && isset($resultado['response'])) {
                // A resposta da API SERPRO para webhooks vem diretamente como array
                if (is_array($resultado['response'])) {
                    $webhooks = $resultado['response'];
                } elseif (isset($resultado['response']['data'])) {
                    $webhooks = $resultado['response']['data'];
                }
            } else {
                $webhookError = 'Erro ao carregar webhooks: ' . ($resultado['error'] ?? 'Erro desconhecido');
            }
        } catch (Exception $e) {
            $webhookError = 'Erro ao conectar com a API: ' . $e->getMessage();
        }

        $dados = [
            'tituloPagina' => 'Gerenciar Webhooks',
            'webhooks' => $webhooks,
            'webhookError' => $webhookError
        ];

        $this->view('chat/webhooks', $dados);
    }

    /**
     * [ uploadMidia ] - Faz upload de mídia para a Meta
     */
    public function uploadMidia()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Erro no upload do arquivo']);
            return;
        }

        $arquivo = $_FILES['file'];
        $tipoMidia = $_POST['media_type'] ?? $arquivo['type'];

        // Validar tipo de arquivo
        $tiposPermitidos = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'video/3gpp',
            // Áudio (formatos aceitos pela API SERPRO)
            'audio/aac',
            'audio/amr',
            'audio/mpeg', 
            'audio/mp4',
            'audio/x-m4a', // Variação do MP4 criada por alguns navegadores
            'audio/ogg',
            'application/pdf',
            'application/msword',
            'text/plain',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($tipoMidia, $tiposPermitidos)) {
            echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido']);
            return;
        }

        // Verificar tamanho
        $limiteTamanho = 5 * 1024 * 1024; // 5MB padrão
        if (strpos($tipoMidia, 'video/') === 0 || strpos($tipoMidia, 'audio/') === 0) {
            $limiteTamanho = 16 * 1024 * 1024; // 16MB para vídeo/áudio
        } elseif (strpos($tipoMidia, 'application/') === 0) {
            $limiteTamanho = 95 * 1024 * 1024; // 95MB para documentos
        }

        if ($arquivo['size'] > $limiteTamanho) {
            echo json_encode(['success' => false, 'error' => 'Arquivo muito grande']);
            return;
        }

        // Log específico para arquivos OGG (padrão das mensagens recebidas)
        if ($tipoMidia === 'audio/ogg' || strpos($tipoMidia, 'audio/ogg') === 0) {
            error_log("✅ UPLOAD OGG: Formato padrão das mensagens recebidas - {$arquivo['name']}");
        }

        $resultado = SerproHelper::uploadMidia($arquivo, $tipoMidia);

        if ($resultado['status'] == 200) {
            echo json_encode([
                'success' => true,
                'media_id' => $resultado['response']['id'],
                'media_type' => $tipoMidia
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $resultado['error'] ?? 'Erro no upload'
            ]);
        }
    }

    /**
     * [ downloadMidia ] - Baixa mídia da Meta
     */
    public function downloadMidia($media_id = null)
    {
        if (!$media_id) {
            http_response_code(404);
            return;
        }

        $resultado = SerproHelper::downloadMidia($media_id);

        if ($resultado['status'] == 200) {
            // Definir headers apropriados
            header('Content-Type: ' . $resultado['content_type']);
            header('Content-Disposition: attachment; filename="' . $media_id . '"');
            echo $resultado['data'];
        } else {
            http_response_code(404);
        }
    }

    /**
     * [ enviarMensagemInterativa ] - Envia mensagens com botões ou listas
     */
    public function enviarMensagemInterativa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método não permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['conversa_id']) || !isset($input['tipo'])) {
            echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
            return;
        }

        $conversa = $this->chatModel->buscarConversaPorId($input['conversa_id']);

        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['success' => false, 'error' => 'Conversa não encontrada']);
            return;
        }

        $resultado = false;

        switch ($input['tipo']) {
            case 'botoes':
                $resultado = SerproHelper::enviarMensagemBotoes(
                    $conversa->contato_numero,
                    $input['texto_body'],
                    $input['botoes'],
                    $input['message_id'] ?? null
                );
                break;

            case 'lista':
                $resultado = SerproHelper::enviarMensagemLista(
                    $conversa->contato_numero,
                    $input['texto_body'],
                    $input['button_text'],
                    $input['secoes'],
                    $input['message_id'] ?? null
                );
                break;

            case 'localizacao':
                $resultado = SerproHelper::enviarSolicitacaoLocalizacao(
                    $conversa->contato_numero,
                    $input['texto_body'],
                    $input['message_id'] ?? null
                );
                break;
        }

        if ($resultado && ($resultado['status'] == 200 || $resultado['status'] == 201)) {
            // Salvar no banco
            $this->chatModel->salvarMensagem([
                'conversa_id' => $input['conversa_id'],
                'remetente_id' => $_SESSION['usuario_id'],
                'tipo' => 'interativa_' . $input['tipo'],
                'conteudo' => json_encode($input),
                'message_id' => $resultado['response']['id'] ?? uniqid(),
                'status' => 'enviado',
                'enviado_em' => date('Y-m-d H:i:s')
            ]);

            $this->chatModel->atualizarConversa($input['conversa_id']);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $resultado['error'] ?? 'Erro ao enviar mensagem interativa'
            ]);
        }
    }

    /**
     * [ consultarStatus ] - Consulta status detalhado de uma mensagem
     */
    public function consultarStatus($requisicao_id = null)
    {
        if (!$requisicao_id) {
            echo json_encode(['error' => 'ID da requisição não informado']);
            return;
        }

        $resultado = SerproHelper::consultarStatus($requisicao_id);
        echo json_encode($resultado);
    }

    /**
     * [ qrCode ] - Gerencia QR Codes para conexão
     */
    public function qrCode()
    {
        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $acao = $_POST['acao'] ?? '';

            try {
                switch ($acao) {
                    case 'gerar':
                        $dados = [
                            'mensagem_preenchida' => $_POST['mensagem'] ?? 'Olá! Entre em contato conosco.',
                            'codigo' => $_POST['codigo'] ?? ''
                        ];
                        $resultado = SerproHelper::gerarQRCode($dados);

                        if ($resultado['status'] == 200 || $resultado['status'] == 201) {
                            Helper::mensagem('chat', '<i class="fas fa-check"></i> QR Code gerado com sucesso!', 'alert alert-success');
                        } else {
                            $erro = $resultado['error'] ?? 'Erro desconhecido';
                            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao gerar QR code: ' . $erro, 'alert alert-danger');
                        }

                        Helper::redirecionar('chat/qrCode');
                        break;

                    case 'excluir':
                        $qrId = $_POST['qr_id'];

                        if (empty($qrId)) {
                            Helper::mensagem('chat', '<i class="fas fa-ban"></i> ID do QR code não informado', 'alert alert-danger');
                            Helper::redirecionar('chat/qrCode');
                            return;
                        }

                        $resultado = SerproHelper::excluirQRCode($qrId);

                        if ($resultado['status'] == 200) {
                            Helper::mensagem('chat', '<i class="fas fa-check"></i> QR Code excluído com sucesso!', 'alert alert-success');
                        } else {
                            $erro = $resultado['error'] ?? 'Erro desconhecido';
                            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao excluir QR code: ' . $erro, 'alert alert-danger');
                        }

                        Helper::redirecionar('chat/qrCode');
                        break;

                    default:
                        Helper::mensagem('chat', '<i class="fas fa-ban"></i> Ação não reconhecida', 'alert alert-danger');
                        Helper::redirecionar('chat/qrCode');
                        break;
                }
            } catch (Exception $e) {
                Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro interno: ' . $e->getMessage(), 'alert alert-danger');
                Helper::redirecionar('chat/qrCode');
            }

            return;
        }

        // Para requisições GET, carregar QR codes diretamente no PHP
        $qrCodes = [];
        $qrCodeError = null;
        $modo = $_GET['modo'] ?? 'combinado'; // 'combinado', 'imagem', 'dados'

        try {
            switch ($modo) {
                case 'imagem':
                    $resultado = SerproHelper::listarQRCodesComImagem();
                    break;
                case 'dados':
                    $resultado = SerproHelper::listarQRCodesSemImagem();
                    break;
                case 'combinado':
                default:
                    $resultado = SerproHelper::listarQRCodesCombinados();
                    break;
            }

            if ($resultado['status'] == 200 && isset($resultado['response'])) {
                // A resposta da API SERPRO para QR codes vem diretamente como array
                if (is_array($resultado['response'])) {
                    $qrCodes = $resultado['response'];
                } elseif (isset($resultado['response']['data'])) {
                    $qrCodes = $resultado['response']['data'];
                } else {
                    $qrCodes = [];
                }
            } else {
                $qrCodeError = 'Erro ao carregar QR codes: ' . ($resultado['error'] ?? 'Erro desconhecido');
            }
        } catch (Exception $e) {
            $qrCodeError = 'Erro ao conectar com a API: ' . $e->getMessage();
        }

        $dados = [
            'tituloPagina' => 'QR Codes',
            'qrCodes' => $qrCodes,
            'qrCodeError' => $qrCodeError,
            'modo' => $modo
        ];

        $this->view('chat/qr_codes', $dados);
    }

    /**
     * [ baixarQRCode ] - Faz download de QR Code via backend (contorna CORS)
     */
    public function baixarQRCode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('chat/configuracoes');
            return;
        }

        $qr_url = $_POST['qr_url'] ?? '';
        $nome_arquivo = $_POST['nome_arquivo'] ?? 'qrcode.png';

        if (empty($qr_url)) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> URL da imagem não fornecida', 'alert alert-danger');
            Helper::redirecionar('chat/qrCode');
            return;
        }

        try {
            // Fazer download da imagem usando cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $qr_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("Erro cURL: " . $error);
            }

            if ($httpCode !== 200 || empty($imageData)) {
                throw new Exception("Erro ao baixar imagem. Código HTTP: " . $httpCode);
            }

            // Definir headers para download
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
            header('Content-Length: ' . strlen($imageData));
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

            // Enviar a imagem
            echo $imageData;
            exit;
        } catch (Exception $e) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Erro ao baixar QR Code: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('chat/qrCode');
        }
    }

    /**
     * [ metricas ] - Exibe métricas do chat
     */
    public function metricas()
    {
        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        $inicio = $_GET['inicio'] ?? date('Y-m-01'); // Primeiro dia do mês
        $fim = $_GET['fim'] ?? date('Y-m-d'); // Hoje

        // Métricas da API SERPRO
        $metricas = SerproHelper::obterMetricas($inicio, $fim);

        // Métricas locais do banco de dados
        $metricasLocais = [
            'total_conversas' => $this->chatModel->contarConversas($_SESSION['usuario_id']),
            'mensagens_enviadas' => $this->chatModel->contarMensagensEnviadas($_SESSION['usuario_id']),
            'mensagens_recebidas' => $this->chatModel->contarMensagensRecebidas($_SESSION['usuario_id']),
            'conversas_ativas' => $this->chatModel->contarConversasAtivas($_SESSION['usuario_id'])
        ];

        $dados = [
            'tituloPagina' => 'Métricas do Chat',
            'metricas' => $metricas,
            'metricas_locais' => $metricasLocais,
            'periodo' => ['inicio' => $inicio, 'fim' => $fim]
        ];

        $this->view('chat/metricas', $dados);
    }

    /**
     * [ configuracoes ] - Página de configurações do chat
     */
    public function configuracoes()
    {
        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Atualizar configurações
            $configuracoes = [
                'template_padrao' => $_POST['template_padrao'] ?? '',
                'webhook_url' => $_POST['webhook_url'] ?? '',
                'auto_resposta' => isset($_POST['auto_resposta']) ? 1 : 0,
                'horario_atendimento' => $_POST['horario_atendimento'] ?? ''
            ];

            // Salvar no banco ou arquivo de configuração
            foreach ($configuracoes as $chave => $valor) {
                $this->chatModel->salvarConfiguracao($chave, $valor);
            }

            Helper::mensagem('chat', '<i class="fas fa-check"></i> Configurações salvas com sucesso', 'alert alert-success');
        }

        $configuracoes = $this->chatModel->obterConfiguracoes();

        $dados = [
            'tituloPagina' => 'Configurações do Chat',
            'configuracoes' => $configuracoes
        ];

        $this->view('chat/configuracoes', $dados);
    }

    /**
     * [ carregarNovasMensagens ] - Carrega novas mensagens de uma conversa via AJAX
     */
    public function carregarNovasMensagens($conversa_id = null, $ultima_mensagem_id = 0)
    {
        if (!$conversa_id) {
            echo json_encode(['error' => 'ID da conversa não informado']);
            return;
        }

        // Verificar se o usuário tem acesso à conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['error' => 'Acesso negado']);
            return;
        }

        $novasMensagens = $this->chatModel->buscarNovasMensagens($conversa_id, $ultima_mensagem_id);

        echo json_encode([
            'success' => true,
            'mensagens' => $novasMensagens
        ]);
    }

    /**
     * [ testarAPI ] - Testa conectividade com a API SERPRO
     */
    public function testarAPI()
    {
        // Verifica permissão
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 403, 'error' => 'Acesso negado']);
                return;
            }
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            try {
                // Testa obtenção de token
                $token = SerproHelper::getToken();

                if ($token) {
                    // Testa listagem de templates
                    $templates = SerproHelper::listarTemplates();

                    echo json_encode([
                        'status' => 200,
                        'token_obtido' => true,
                        'token_length' => strlen($token),
                        'api_templates' => $templates,
                        'configuracoes' => [
                            'base_url' => SERPRO_BASE_URL,
                            'client_id' => SERPRO_CLIENT_ID,
                            'waba_id' => SERPRO_WABA_ID,
                            'phone_number_id' => SERPRO_PHONE_NUMBER_ID
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'status' => 401,
                        'token_obtido' => false,
                        'error' => SerproHelper::getLastError(),
                        'configuracoes' => [
                            'base_url' => SERPRO_BASE_URL,
                            'client_id' => SERPRO_CLIENT_ID,
                            'waba_id' => SERPRO_WABA_ID,
                            'phone_number_id' => SERPRO_PHONE_NUMBER_ID
                        ]
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 500,
                    'error' => 'Erro interno: ' . $e->getMessage()
                ]);
            }

            return;
        }

        Helper::redirecionar('chat/configuracoes');
    }

    /**
     * [ atualizarStatusMensagens ] - Verifica e atualiza status das mensagens via API SERPRO
     */
    public function atualizarStatusMensagens($conversa_id = null)
    {
        header('Content-Type: application/json');

        try {
            if (!$conversa_id) {
                echo json_encode(['success' => false, 'error' => 'ID da conversa não informado']);
                return;
            }

            // Verificar se o usuário tem acesso à conversa
            $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
            if (!$conversa || $conversa->usuario_id != $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'error' => 'Acesso negado à conversa']);
                return;
            }

            // Buscar mensagens enviadas pendentes de atualização de status
            $mensagensParaVerificar = $this->chatModel->buscarMensagensComStatus($conversa_id, ['enviado', 'entregue']);

            if (empty($mensagensParaVerificar)) {
                echo json_encode([
                    'success' => true,
                    'mensagens_atualizadas' => [],
                    'total_verificadas' => 0,
                    'info' => 'Nenhuma mensagem pendente de verificação'
                ]);
                return;
            }

            $mensagensAtualizadas = [];
            $errosVerificacao = [];

            foreach ($mensagensParaVerificar as $mensagem) {
                if (!empty($mensagem->message_id)) {
                    try {
                        $resultado = SerproHelper::consultarStatus($mensagem->message_id);

                        if ($resultado['status'] == 200 && isset($resultado['response']['requisicoesEnvio'])) {
                            foreach ($resultado['response']['requisicoesEnvio'] as $requisicao) {
                                if ($requisicao['destinatario'] == $conversa->contato_numero) {
                                    $novoStatus = $this->determinarStatusMensagem($requisicao);

                                    if ($novoStatus && $novoStatus != $mensagem->status) {
                                        // Atualizar status no banco
                                        $updateResult = $this->chatModel->atualizarStatusMensagem($mensagem->message_id, $novoStatus);

                                        if ($updateResult) {
                                            $mensagensAtualizadas[] = [
                                                'id' => $mensagem->id,
                                                'message_id' => $mensagem->message_id,
                                                'status_anterior' => $mensagem->status,
                                                'status_novo' => $novoStatus
                                            ];
                                        } else {
                                            $errosVerificacao[] = "Erro ao atualizar mensagem {$mensagem->id} no banco";
                                        }
                                    }
                                    break;
                                }
                            }
                        } else {
                            $errosVerificacao[] = "Erro na API para mensagem {$mensagem->message_id}: " . ($resultado['error'] ?? 'Status ' . $resultado['status']);
                        }
                    } catch (Exception $e) {
                        $errosVerificacao[] = "Exceção ao verificar mensagem {$mensagem->message_id}: " . $e->getMessage();
                    }
                }
            }

            $response = [
                'success' => true,
                'mensagens_atualizadas' => $mensagensAtualizadas,
                'total_verificadas' => count($mensagensParaVerificar),
                'total_atualizadas' => count($mensagensAtualizadas)
            ];

            if (!empty($errosVerificacao)) {
                $response['warnings'] = $errosVerificacao;
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * [ determinarStatusMensagem ] - Determina o status baseado na resposta da API SERPRO
     */
    private function determinarStatusMensagem($requisicao)
    {
        // Ordem de prioridade: lido > entregue > enviado > falhou
        if (!empty($requisicao['read'])) {
            return 'lido';
        } elseif (!empty($requisicao['delivered'])) {
            return 'entregue';
        } elseif (!empty($requisicao['sent'])) {
            return 'enviado';
        } elseif (!empty($requisicao['failed'])) {
            return 'falhou';
        }

        return null; // Não mudou
    }

    /**
     * [ verificarStatusMensagem ] - Verifica status de uma mensagem específica
     */
    public function verificarStatusMensagem($message_id = null)
    {
        if (!$message_id) {
            echo json_encode(['error' => 'ID da mensagem não informado']);
            return;
        }

        $resultado = SerproHelper::consultarStatus($message_id);

        if ($resultado['status'] == 200) {
            echo json_encode([
                'success' => true,
                'dados_completos' => $resultado['response'],
                'status_requisicao' => $resultado['status']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $resultado['error'] ?? 'Erro ao consultar status',
                'status_requisicao' => $resultado['status']
            ]);
        }
    }

    /**
     * [ debugAPI ] - Debug da conectividade com API SERPRO
     */
    public function debugAPI()
    {
        header('Content-Type: application/json');

        try {
            $debug = [
                'configuracao' => [
                    'base_url' => defined('SERPRO_BASE_URL') ? SERPRO_BASE_URL : 'Não definido',
                    'client_id' => defined('SERPRO_CLIENT_ID') ? (SERPRO_CLIENT_ID ? 'Definido' : 'Vazio') : 'Não definido',
                    'waba_id' => defined('SERPRO_WABA_ID') ? (SERPRO_WABA_ID ? 'Definido' : 'Vazio') : 'Não definido',
                    'phone_number_id' => defined('SERPRO_PHONE_NUMBER_ID') ? (SERPRO_PHONE_NUMBER_ID ? 'Definido' : 'Vazio') : 'Não definido'
                ],
                'sessao' => [
                    'usuario_logado' => isset($_SESSION['usuario_id']),
                    'usuario_id' => $_SESSION['usuario_id'] ?? null,
                    'usuario_perfil' => $_SESSION['usuario_perfil'] ?? null
                ]
            ];

            // Testar obtenção de token
            try {
                $token = SerproHelper::getToken();
                $debug['token'] = [
                    'obtido' => !empty($token),
                    'comprimento' => $token ? strlen($token) : 0,
                    'erro' => $token ? null : SerproHelper::getLastError()
                ];
            } catch (Exception $e) {
                $debug['token'] = [
                    'obtido' => false,
                    'erro' => $e->getMessage()
                ];
            }

            // Testar verificação de status da API
            try {
                $statusAPI = SerproHelper::verificarStatusAPI();
                $debug['api_status'] = [
                    'online' => $statusAPI,
                    'erro' => $statusAPI ? null : SerproHelper::getLastError()
                ];
            } catch (Exception $e) {
                $debug['api_status'] = [
                    'online' => false,
                    'erro' => $e->getMessage()
                ];
            }

            echo json_encode([
                'success' => true,
                'debug_info' => $debug
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * [ processarVerificacaoStatusManual ] - Processa verificação de status manual
     */
    private function processarVerificacaoStatusManual($conversa_id, $conversa)
    {
        try {
            // Buscar mensagens enviadas pendentes de atualização de status
            $mensagensParaVerificar = $this->chatModel->buscarMensagensComStatus($conversa_id, ['enviado', 'entregue']);

            if (empty($mensagensParaVerificar)) {
                Helper::mensagem('chat', '<i class="fas fa-info-circle"></i> Nenhuma mensagem pendente de verificação', 'alert alert-info');
                return;
            }

            $mensagensAtualizadas = 0;
            $erros = [];

            foreach ($mensagensParaVerificar as $mensagem) {
                if (!empty($mensagem->message_id)) {
                    try {
                        $resultado = SerproHelper::consultarStatus($mensagem->message_id);

                        if ($resultado['status'] == 200 && isset($resultado['response']['requisicoesEnvio'])) {
                            foreach ($resultado['response']['requisicoesEnvio'] as $requisicao) {
                                if ($requisicao['destinatario'] == $conversa->contato_numero) {
                                    $novoStatus = $this->determinarStatusMensagem($requisicao);

                                    if ($novoStatus && $novoStatus != $mensagem->status) {
                                        // Atualizar status no banco
                                        if ($this->chatModel->atualizarStatusMensagem($mensagem->message_id, $novoStatus)) {
                                            $mensagensAtualizadas++;
                                        }
                                    }
                                    break;
                                }
                            }
                        } else {
                            $erros[] = "Erro na API para mensagem {$mensagem->message_id}";
                        }
                    } catch (Exception $e) {
                        $erros[] = "Erro ao verificar mensagem {$mensagem->message_id}: " . $e->getMessage();
                    }
                }
            }

            // Mostrar resultado
            if ($mensagensAtualizadas > 0) {
                Helper::mensagem('chat', "<i class='fas fa-check-circle'></i> {$mensagensAtualizadas} mensagem(ns) tiveram status atualizado", 'alert alert-success');
            } else {
                Helper::mensagem('chat', '<i class="fas fa-info-circle"></i> Nenhuma atualização de status encontrada', 'alert alert-info');
            }

            if (!empty($erros)) {
                foreach ($erros as $erro) {
                    Helper::mensagem('chat', "<i class='fas fa-exclamation-triangle'></i> {$erro}", 'alert alert-warning');
                }
            }
        } catch (Exception $e) {
            Helper::mensagem('chat', "<i class='fas fa-ban'></i> Erro na verificação: " . $e->getMessage(), 'alert alert-danger');
        }
    }

    /**
     * [ debugEnvioMidia ] - Debug do envio de mídia
     */
    public function debugEnvioMidia()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }

        header('Content-Type: application/json');

        try {
            // Verificar se foi enviado um arquivo
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Nenhum arquivo foi enviado ou erro no upload',
                    'files_info' => $_FILES
                ]);
                return;
            }

            $arquivo = $_FILES['file'];
            $destinatario = $_POST['destinatario'] ?? '5562999999999'; // Número de teste
            $caption = $_POST['caption'] ?? 'Teste de envio de mídia';

            echo json_encode([
                'step' => 'Iniciando debug...',
                'arquivo_info' => [
                    'nome' => $arquivo['name'],
                    'tipo' => $arquivo['type'],
                    'tamanho' => $arquivo['size'],
                    'tmp_name' => $arquivo['tmp_name']
                ],
                'destinatario' => $destinatario,
                'caption' => $caption
            ]);

            // Passo 1: Upload da mídia
            $resultadoUpload = SerproHelper::uploadMidia($arquivo, $arquivo['type']);

            if ($resultadoUpload['status'] !== 200) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Falha no upload da mídia',
                    'upload_response' => $resultadoUpload
                ]);
                return;
            }

            $mediaId = $resultadoUpload['response']['id'];

            // Passo 2: Envio da mídia
            $tipoMidia = $this->mapearTipoMidiaParaAPI($arquivo['type']);
            $resultadoEnvio = SerproHelper::enviarMidia($destinatario, $tipoMidia, $mediaId, $caption);

            echo json_encode([
                'success' => true,
                'upload_result' => $resultadoUpload,
                'send_result' => $resultadoEnvio,
                'media_id' => $mediaId,
                'tipo_midia' => $tipoMidia
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exceção: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Processa mensagem recebida via webhook SERPRO
     */
    private function processarMensagemRecebida($mensagem)
    {
        $numero = $mensagem['from'];

        // Buscar ou criar conversa
        $conversa = $this->chatModel->buscarOuCriarConversaPorNumero($numero);

        if ($conversa) {
            // Salvar mensagem recebida
            $conteudo = '';
            switch ($mensagem['type']) {
                case 'text':
                    $conteudo = $mensagem['text'];
                    break;
                case 'image':
                case 'audio':
                case 'video':
                case 'document':
                    $conteudo = json_encode($mensagem[$mensagem['type']]);
                    break;
            }

            $this->chatModel->salvarMensagem([
                'conversa_id' => $conversa->id,
                'remetente_id' => null, // Mensagem recebida
                'tipo' => $mensagem['type'],
                'conteudo' => $conteudo,
                'message_id' => $mensagem['id'],
                'status' => 'recebido',
                'enviado_em' => date('Y-m-d H:i:s', $mensagem['timestamp'])
            ]);

            // Atualizar conversa
            $this->chatModel->atualizarConversa($conversa->id);
        }
    }

    /**
     * Visualiza mídia do MinIO com autenticação (MÉTODO OTIMIZADO)
     */
    public function visualizarMidiaMinIO($caminhoMinio = null)
    {
        // Limpar qualquer saída anterior que possa corromper o arquivo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Verificar se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(403);
            echo "Acesso negado - usuário não logado";
            return;
        }

        if (!$caminhoMinio) {
            http_response_code(404);
            echo "Mídia não encontrada - caminho não informado";
            return;
        }

        // Decodificar caminho
        $caminhoMinio = urldecode($caminhoMinio);
        
        // Log para debug
        error_log("🔍 Visualizar mídia MinIO: {$caminhoMinio} (Usuário: {$_SESSION['usuario_id']})");

        // Verificar se o usuário tem acesso à mídia
        if (!$this->verificarAcessoMidiaMinIO($_SESSION['usuario_id'], $caminhoMinio)) {
            http_response_code(403);
            echo "Acesso negado a esta mídia";
            error_log("❌ Acesso negado à mídia {$caminhoMinio} para usuário {$_SESSION['usuario_id']}");
            return;
        }

        // Carregar helper do MinIO
        require_once APPROOT . '/Libraries/MinioHelper.php';

        try {
            // Usar método de acesso direto (mais confiável)
            $resultado = MinioHelper::acessoDirecto($caminhoMinio);

            if (!$resultado['sucesso']) {
                http_response_code(404);
                echo "Arquivo não encontrado: " . $resultado['erro'];
                error_log("❌ Arquivo não encontrado: {$caminhoMinio} - " . $resultado['erro']);
                return;
            }

            // Garantir que não há saída anterior
            if (headers_sent()) {
                error_log("⚠️ Headers já enviados ao tentar servir mídia: {$caminhoMinio}");
                echo "Erro: Headers já enviados";
                return;
            }

            // Limpar qualquer buffer de saída
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Definir headers apropriados
            header('Content-Type: ' . $resultado['content_type']);
            header('Content-Length: ' . $resultado['tamanho']);
            header('Cache-Control: private, max-age=3600');
            header('X-Content-Type-Options: nosniff');
            header('Pragma: public');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
            
            // Nome do arquivo para header
            $nomeArquivo = basename($caminhoMinio);
            $nomeArquivo = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nomeArquivo); // Sanitizar nome
            
            // Para documentos, forçar download
            if (strpos($resultado['content_type'], 'application/') === 0 || strpos($resultado['content_type'], 'text/') === 0) {
                header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            } else {
                // Para imagens, áudio e vídeo, permitir visualização inline
                header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
            }

            // Evitar timeout para arquivos grandes
            set_time_limit(0);
            
            // Log de sucesso
            error_log("✅ Servindo mídia: {$caminhoMinio} (" . number_format($resultado['tamanho'] / 1024, 2) . " KB)");

            // Servir o arquivo de forma segura
            echo $resultado['dados'];
            
            // Garantir que a saída seja enviada
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                flush();
            }
            
        } catch (Exception $e) {
            error_log("❌ Erro ao visualizar mídia MinIO: " . $e->getMessage());
            http_response_code(500);
            echo "Erro interno ao carregar mídia: " . $e->getMessage();
        }
    }

    /**
     * Verifica se o usuário tem acesso à mídia do MinIO
     */
    private function verificarAcessoMidiaMinIO($usuario_id, $caminhoMinio)
    {
        try {
            // Admins têm acesso a todas as mídias
            if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] === 'admin' || $_SESSION['usuario_perfil'] === 'analista')) {
                return true;
            }

            // Usar método do ChatModel
            return $this->chatModel->verificarAcessoMidiaMinIO($usuario_id, $caminhoMinio);

        } catch (Exception $e) {
            error_log("Erro ao verificar acesso à mídia MinIO: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gera URL temporária para visualização da mídia
     */
    public function gerarUrlMidia()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $caminhoMinio = $input['caminho_minio'] ?? '';

        if (empty($caminhoMinio)) {
            echo json_encode(['error' => 'Caminho da mídia não informado']);
            return;
        }

        // Verificar se o usuário tem acesso
        if (!$this->verificarAcessoMidiaMinIO($_SESSION['usuario_id'], $caminhoMinio)) {
            echo json_encode(['error' => 'Acesso negado']);
            return;
        }

        // Carregar helper do MinIO
        require_once APPROOT . '/Libraries/MinioHelper.php';

        try {
            // Gerar URL temporária com expiração mais longa (2 horas)
            $url = MinioHelper::gerarUrlVisualizacao($caminhoMinio, 7200);

            if ($url) {
                echo json_encode([
                    'success' => true,
                    'url' => $url,
                    'expires_in' => 7200,
                    'generated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                echo json_encode(['error' => 'Erro ao gerar URL']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Exceção: ' . $e->getMessage()]);
        }
    }

    /**
     * Gera URL fresca para download via GET (alternativa mais simples)
     */
    public function gerarUrlFresca($caminhoMinio = null)
    {
        // Verificar se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(403);
            echo "Acesso negado";
            return;
        }

        if (!$caminhoMinio) {
            http_response_code(404);
            echo "Caminho não informado";
            return;
        }

        // Decodificar caminho
        $caminhoMinio = urldecode($caminhoMinio);

        // Verificar se o usuário tem acesso
        if (!$this->verificarAcessoMidiaMinIO($_SESSION['usuario_id'], $caminhoMinio)) {
            http_response_code(403);
            echo "Acesso negado";
            return;
        }

        // Carregar helper do MinIO
        require_once APPROOT . '/Libraries/MinioHelper.php';

        try {
            // Gerar URL fresca e redirecionar
            $url = MinioHelper::gerarUrlVisualizacao($caminhoMinio, 3600);
            
            if ($url) {
                header("Location: " . $url);
                exit;
            } else {
                http_response_code(500);
                echo "Erro ao gerar URL";
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo "Erro: " . $e->getMessage();
        }
    }

    /**
     * Obtém estatísticas do MinIO
     */
    public function estatisticasMinIO()
    {
        // Verificar se tem permissão admin
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Acesso negado']);
            return;
        }

        // Carregar helper do MinIO
        require_once APPROOT . '/Libraries/MinioHelper.php';

        try {
            $estatisticas = MinioHelper::obterEstatisticas();

            // Adicionar estatísticas formatadas
            $estatisticas['tamanho_total_formatado'] = $this->formatarTamanho($estatisticas['tamanho_total']);
            
            foreach ($estatisticas['por_tipo'] as $tipo => &$dados) {
                $dados['size_formatted'] = $this->formatarTamanho($dados['size']);
            }

            echo json_encode([
                'success' => true,
                'estatisticas' => $estatisticas
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lista arquivos do MinIO por tipo
     */
    public function listarArquivosMinIO()
    {
        // Verificar se tem permissão admin
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Acesso negado']);
            return;
        }

        $tipo = $_GET['tipo'] ?? '';
        $ano = $_GET['ano'] ?? '';

        // Construir prefixo de busca
        $prefixo = '';
        if ($tipo) {
            $prefixo = $tipo . '/';
            if ($ano) {
                $prefixo .= $ano . '/';
            }
        }

        // Carregar helper do MinIO
        require_once APPROOT . '/Libraries/MinioHelper.php';

        try {
            $arquivos = MinioHelper::listarArquivos($prefixo, 100);

            echo json_encode([
                'success' => true,
                'arquivos' => $arquivos,
                'prefixo' => $prefixo,
                'total' => count($arquivos)
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao listar arquivos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Testa conexão com MinIO
     */
    public function testarMinIO()
    {
        // Verificar se tem permissão admin
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Acesso negado']);
            return;
        }

        // Carregar helper do MinIO
        require_once APPROOT . '/Libraries/MinioHelper.php';

        $resultado = MinioHelper::testarConexao();

        echo json_encode($resultado);
    }

    /**
     * Salva mídia enviada diretamente no MinIO
     */
    private function salvarMidiaEnviadaMinIO($arquivo, $tipoMidia)
    {
        try {
            // Carrega o helper do MinIO
            require_once APPROOT . '/Libraries/MinioHelper.php';
            
            // Ler o conteúdo do arquivo
            $dadosArquivo = file_get_contents($arquivo['tmp_name']);
            
            // Fazer upload para o MinIO
            $resultadoUpload = MinioHelper::uploadMidia(
                $dadosArquivo, 
                $tipoMidia, 
                $arquivo['type'], 
                $arquivo['name']
            );
            
            if (!$resultadoUpload['sucesso']) {
                return [
                    'sucesso' => false,
                    'erro' => 'Erro ao fazer upload para MinIO: ' . $resultadoUpload['erro']
                ];
            }
            
            // Log de sucesso
            // error_log("📁 Mídia ENVIADA salva no MinIO: {$resultadoUpload['caminho_minio']} (Tamanho: " . 
            //          number_format($resultadoUpload['tamanho'] / 1024, 2) . " KB)");
            
            return [
                'sucesso' => true,
                'caminho_minio' => $resultadoUpload['caminho_minio'],
                'url_minio' => $resultadoUpload['url_minio'],
                'nome_arquivo' => $resultadoUpload['nome_arquivo'],
                'tamanho' => $resultadoUpload['tamanho'],
                'mime_type' => $arquivo['type'],
                'bucket' => $resultadoUpload['bucket']
            ];
            
        } catch (Exception $e) {
            error_log("❌ Erro ao salvar mídia ENVIADA no MinIO: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }

    /**
     * NOVO: Relatório de conversas ativas por agente
     */
    public function relatorioConversasAtivas()
    {
        // Verificar permissão - apenas admin
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        $relatorio = $this->chatModel->relatorioConversasAtivas();

        $dados = [
            'tituloPagina' => 'Relatório de Conversas Ativas',
            'relatorio' => $relatorio
        ];

        $this->view('chat/relatorio_conversas_ativas', $dados);
    }

    /**
     * NOVO: Detectar e resolver conflitos no sistema
     */
    public function gerenciarConflitos()
    {
        // Verificar permissão - apenas admin
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Processar ações POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acao = $_POST['acao'] ?? '';

            switch ($acao) {
                case 'limpar_conflitos':
                    $resultados = $this->chatModel->limparTodosConflitos();
                    
                    if (!empty($resultados)) {
                        $totalResolvidos = count($resultados);
                        $mensagem = "<i class='fas fa-check'></i> <strong>$totalResolvidos conflito(s) resolvido(s):</strong><br>";
                        
                        foreach ($resultados as $resultado) {
                            $mensagem .= "<small>• {$resultado['nome']} ({$resultado['contato']}): mantida conversa de {$resultado['conversa_mantida']}, fechadas " . count($resultado['conversas_fechadas']) . " conversa(s)</small><br>";
                        }
                        
                        Helper::mensagem('chat', $mensagem, 'alert alert-success');
                        Helper::mensagemSweetAlert('chat', "Conflitos resolvidos: $totalResolvidos", 'success');
                    } else {
                        Helper::mensagem('chat', '<i class="fas fa-info-circle"></i> Nenhum conflito encontrado para resolver', 'alert alert-info');
                    }
                    break;
            }
        }

        $conflitos = $this->chatModel->detectarConflitos();

        $dados = [
            'tituloPagina' => 'Gerenciar Conflitos de Conversas',
            'conflitos' => $conflitos
        ];

        $this->view('chat/gerenciar_conflitos', $dados);
    }

    /**
     * =====================================================
     * SISTEMA DE TICKETS PARA CONVERSAS
     * =====================================================
     */

    /**
     * Altera status do ticket de uma conversa
     */
    public function alterarStatusTicket()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('chat/index');
            return;
        }

        $conversa_id = $_POST['conversa_id'] ?? null;
        $novo_status = $_POST['status'] ?? null;
        $observacao = $_POST['observacao'] ?? null;

        if (!$conversa_id || !$novo_status) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Dados incompletos', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Verificar se o usuário tem acesso à conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa || ($conversa->usuario_id != $_SESSION['usuario_id'] && !in_array($_SESSION['usuario_perfil'], ['admin', 'analista']))) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Alterar status
        if ($this->chatModel->alterarStatusTicket($conversa_id, $novo_status, $_SESSION['usuario_id'], $observacao)) {
            $statusNomes = [
                'aberto' => 'Aberto',
                'em_andamento' => 'Em Andamento',
                'aguardando_cliente' => 'Aguardando Cliente',
                'resolvido' => 'Resolvido',
                'fechado' => 'Fechado'
            ];

            Helper::mensagem('chat', 
                '<i class="fas fa-check"></i> Status do ticket alterado para: <strong>' . $statusNomes[$novo_status] . '</strong>', 
                'alert alert-success'
            );
            Helper::mensagemSweetAlert('chat', 'Status alterado com sucesso', 'success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao alterar status do ticket', 'alert alert-danger');
        }

        Helper::redirecionar("chat/conversa/{$conversa_id}");
    }

    /**
     * Página de gerenciamento de tickets
     */
    public function gerenciarTickets()
    {
        $filtroStatus = $_GET['status'] ?? 'todos';
        $limite = 20;
        $pagina = $_GET['pagina'] ?? 1;
        $offset = ($pagina - 1) * $limite;

        // Buscar conversas baseado no filtro
        if ($filtroStatus === 'todos') {
            $conversas = $this->chatModel->buscarConversasComFiltros($_SESSION['usuario_id'], '', '', $limite, $offset);
        } else {
            $conversas = $this->chatModel->buscarConversasPorStatusTicket($filtroStatus, $_SESSION['usuario_id'], $limite, $offset);
        }

        // Buscar estatísticas
        $estatisticas = $this->chatModel->estatisticasTickets($_SESSION['usuario_id']);
        $ticketsVencidos = $this->chatModel->buscarTicketsVencidos(24, $_SESSION['usuario_id']);

        $dados = [
            'tituloPagina' => 'Gerenciar Tickets',
            'conversas' => $conversas,
            'estatisticas' => $estatisticas,
            'tickets_vencidos' => $ticketsVencidos,
            'filtro_status' => $filtroStatus,
            'pagina_atual' => $pagina
        ];

        $this->view('chat/gerenciar_tickets', $dados);
    }

    /**
     * Dashboard de tickets (apenas admin/analista)
     */
    public function dashboardTickets()
    {
        // Verificar permissão
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Buscar dados do dashboard
        $dashboardGeral = $this->chatModel->dashboardTickets(); // Todos os tickets
        $dashboardUsuario = $this->chatModel->dashboardTickets($_SESSION['usuario_id']); // Tickets do usuário

        $dados = [
            'tituloPagina' => 'Dashboard de Tickets',
            'dashboard_geral' => $dashboardGeral,
            'dashboard_usuario' => $dashboardUsuario
        ];

        $this->view('chat/dashboard_tickets', $dados);
    }

    /**
     * Relatório de tickets
     */
    public function relatorioTickets()
    {
        // Verificar permissão
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        $periodo = $_GET['periodo'] ?? '30'; // Últimos 30 dias
        $usuario_filtro = $_GET['usuario'] ?? null;

        // Buscar dados do relatório
        $estatisticas = $this->chatModel->estatisticasTickets($usuario_filtro);
        $relatorioPorStatus = $this->chatModel->relatorioTicketsPorStatus($usuario_filtro);
        $ticketsVencidos = $this->chatModel->buscarTicketsVencidos(2, $usuario_filtro);
        $usuariosDisponiveis = $this->chatModel->buscarUsuariosParaAtribuicao();

        $dados = [
            'tituloPagina' => 'Relatório de Tickets',
            'estatisticas' => $estatisticas,
            'relatorio_status' => $relatorioPorStatus,
            'tickets_vencidos' => $ticketsVencidos,
            'usuarios_disponiveis' => $usuariosDisponiveis,
            'periodo_selecionado' => $periodo,
            'usuario_filtro' => $usuario_filtro
        ];

        $this->view('chat/relatorio_tickets', $dados);
    }

    /**
     * Exibir histórico de um ticket
     */
    public function historicoTicket($conversa_id = null)
    {
        if (!$conversa_id) {
            Helper::redirecionar('chat/index');
            return;
        }

        // Verificar acesso à conversa
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa || ($conversa->usuario_id != $_SESSION['usuario_id'] && !in_array($_SESSION['usuario_perfil'], ['admin', 'analista']))) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Buscar histórico
        $historico = $this->chatModel->buscarHistoricoTicket($conversa_id);

        $dados = [
            'tituloPagina' => 'Histórico do Ticket - ' . $conversa->contato_nome,
            'conversa' => $conversa,
            'historico' => $historico
        ];

        $this->view('chat/historico_ticket', $dados);
    }

    /**
     * Reabrir ticket fechado
     */
    public function reabrirTicket()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('chat/index');
            return;
        }

        $conversa_id = $_POST['conversa_id'] ?? null;
        $observacao = $_POST['observacao'] ?? 'Ticket reaberto pelo usuário';

        if (!$conversa_id) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> ID da conversa não informado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Verificar acesso
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa || ($conversa->usuario_id != $_SESSION['usuario_id'] && !in_array($_SESSION['usuario_perfil'], ['admin', 'analista']))) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }

        // Reabrir ticket
        if ($this->chatModel->reabrirTicket($conversa_id, $_SESSION['usuario_id'], $observacao)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Ticket reaberto com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Ticket reaberto', 'success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao reabrir ticket', 'alert alert-danger');
        }

        Helper::redirecionar("chat/conversa/{$conversa_id}");
    }

    /**
     * =====================================================
     * SISTEMA DE MENSAGENS RÁPIDAS
     * =====================================================
     */

    /**
     * Gerenciar mensagens rápidas
     */
    public function gerenciarMensagensRapidas()
    {
        // Verificar permissão
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
            Helper::redirecionar('chat/index');
            return;
        }



        // Processar ações POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acao = $_POST['acao'] ?? '';

            switch ($acao) {
                case 'criar':
                    $this->criarMensagemRapida();
                    return;
                    
                case 'editar':
                    $this->editarMensagemRapida();
                    return;
                    
                case 'excluir':
                    $this->excluirMensagemRapida();
                    return;
                    
                case 'reordenar':
                    $this->reordenarMensagensRapidas();
                    return;
            }
        }

        try {
            
            $tabelaCriada = $this->chatModel->criarTabelaMensagensRapidas();


            $mensagens = $this->chatModel->buscarMensagensRapidas(false); // Incluir inativas

            $totalMensagens = $this->chatModel->contarMensagensRapidas();

            $mensagensAtivas = $this->chatModel->contarMensagensRapidas(true);

            $dados = [
                'tituloPagina' => 'Gerenciar Mensagens Rápidas',
                'mensagens' => $mensagens,
                'total_mensagens' => $totalMensagens,
                'mensagens_ativas' => $mensagensAtivas
            ];

            $this->view('chat/gerenciar_mensagens_rapidas', $dados);

        } catch (Exception $e) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Erro ao carregar mensagens rápidas: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('chat/index');
        }
    }

    /**
     * Criar nova mensagem rápida
     */
    private function criarMensagemRapida()
    {
        $titulo = $_POST['titulo'] ?? '';
        $conteudo = $_POST['conteudo'] ?? '';
        $icone = $_POST['icone'] ?? 'fas fa-comment';
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = (int)($_POST['ordem'] ?? 0);

        // Validação
        if (empty($titulo) || empty($conteudo)) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Título e conteúdo são obrigatórios', 'alert alert-danger');
            Helper::redirecionar('chat/gerenciarMensagensRapidas');
            return;
        }

        $dados = [
            'titulo' => $titulo,
            'conteudo' => $conteudo,
            'icone' => $icone,
            'ativo' => $ativo,
            'ordem' => $ordem,
            'criado_por' => $_SESSION['usuario_id']
        ];

        if ($this->chatModel->criarMensagemRapida($dados)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Mensagem rápida criada com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Mensagem criada com sucesso', 'success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao criar mensagem rápida', 'alert alert-danger');
        }

        Helper::redirecionar('chat/gerenciarMensagensRapidas');
    }

    /**
     * Editar mensagem rápida
     */
    private function editarMensagemRapida()
    {
        $id = $_POST['id'] ?? null;
        $titulo = $_POST['titulo'] ?? '';
        $conteudo = $_POST['conteudo'] ?? '';
        $icone = $_POST['icone'] ?? 'fas fa-comment';
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = (int)($_POST['ordem'] ?? 0);

        // Validação
        if (!$id || empty($titulo) || empty($conteudo)) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Dados incompletos', 'alert alert-danger');
            Helper::redirecionar('chat/gerenciarMensagensRapidas');
            return;
        }

        // Verificar se a mensagem existe
        $mensagem = $this->chatModel->buscarMensagemRapidaPorId($id);
        if (!$mensagem) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Mensagem não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/gerenciarMensagensRapidas');
            return;
        }

        $dados = [
            'titulo' => $titulo,
            'conteudo' => $conteudo,
            'icone' => $icone,
            'ativo' => $ativo,
            'ordem' => $ordem
        ];

        if ($this->chatModel->atualizarMensagemRapida($id, $dados)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Mensagem rápida atualizada com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Mensagem atualizada com sucesso', 'success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao atualizar mensagem rápida', 'alert alert-danger');
        }

        Helper::redirecionar('chat/gerenciarMensagensRapidas');
    }

    /**
     * Excluir mensagem rápida
     */
    private function excluirMensagemRapida()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> ID da mensagem não informado', 'alert alert-danger');
            Helper::redirecionar('chat/gerenciarMensagensRapidas');
            return;
        }

        // Verificar se a mensagem existe
        $mensagem = $this->chatModel->buscarMensagemRapidaPorId($id);
        if (!$mensagem) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Mensagem não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/gerenciarMensagensRapidas');
            return;
        }

        if ($this->chatModel->excluirMensagemRapida($id)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Mensagem rápida excluída com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Mensagem excluída com sucesso', 'success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao excluir mensagem rápida', 'alert alert-danger');
        }

        Helper::redirecionar('chat/gerenciarMensagensRapidas');
    }

    /**
     * Reordenar mensagens rápidas
     */
    private function reordenarMensagensRapidas()
    {
        $ordens = $_POST['ordens'] ?? [];

        if (empty($ordens) || !is_array($ordens)) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Dados de ordenação inválidos', 'alert alert-danger');
            Helper::redirecionar('chat/gerenciarMensagensRapidas');
            return;
        }

        if ($this->chatModel->reordenarMensagensRapidas($ordens)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Ordem das mensagens atualizada com sucesso', 'alert alert-success');
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao reordenar mensagens', 'alert alert-danger');
        }

        Helper::redirecionar('chat/gerenciarMensagensRapidas');
    }

    /**
     * API para buscar mensagens rápidas (para o modal)
     */
    public function apiMensagensRapidas()
    {
        header('Content-Type: application/json');
        
        try {
            
            
            // Garantir que a tabela existe
            $tabelaCriada = $this->chatModel->criarTabelaMensagensRapidas();
            
            $mensagens = $this->chatModel->buscarMensagensRapidas(true); // Apenas ativas
            
            $resultado = array_map(function($msg) {
                return [
                    'id' => $msg->id,
                    'titulo' => $msg->titulo,
                    'conteudo' => $msg->conteudo,
                    'icone' => $msg->icone
                ];
            }, $mensagens);
            

            echo json_encode([
                'success' => true,
                'mensagens' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar mensagens rápidas: ' . $e->getMessage()
            ]);
        }
    }
}

