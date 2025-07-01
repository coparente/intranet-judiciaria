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

        // Parâmetros de paginação
        $registrosPorPagina = 10;
        $paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $paginaAtual = max(1, $paginaAtual); // Garantir que não seja menor que 1

        // Calcular offset
        $offset = ($paginaAtual - 1) * $registrosPorPagina;

        // Buscar conversas com filtros e paginação
        $conversas = $this->chatModel->buscarConversasComFiltros(
            $_SESSION['usuario_id'],
            $filtroContato,
            $filtroNumero,
            $registrosPorPagina,
            $offset
        );

        // Contar total de registros para paginação
        $totalRegistros = $this->chatModel->contarConversasComFiltros(
            $_SESSION['usuario_id'],
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
            'filtro_numero' => $filtroNumero
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
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        $conversa_id = $_POST['conversa_id'] ?? null;
        $usuario_id = $_POST['usuario_id'] ?? null;

        // Validação básica
        if (empty($conversa_id) || empty($usuario_id)) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Dados incompletos para atribuição', 'alert alert-danger');
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        
        
        // Verificar se a conversa existe e está não atribuída
        $conversa = $this->chatModel->buscarConversaPorId($conversa_id);
        if (!$conversa) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa não encontrada', 'alert alert-danger');
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        if ($conversa->usuario_id !== null && $conversa->usuario_id !== 0) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Conversa já está atribuída a outro usuário', 'alert alert-warning');
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        // Atribuir a conversa
        if ($this->chatModel->atribuirConversa($conversa_id, $usuario_id)) {
            Helper::mensagem('chat', '<i class="fas fa-check"></i> Conversa atribuída com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('chat', 'Conversa atribuída com sucesso', 'success');    
        } else {
            Helper::mensagem('chat', '<i class="fas fa-times"></i> Erro ao atribuir conversa', 'alert alert-danger');
        }

        Helper::redirecionar('chat/conversasNaoAtribuidas');
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
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        // Verificar permissão de acesso à conversa
        $temPermissao = false;
        
        // 1. Se a conversa pertence ao usuário logado
        if ($conversa->usuario_id == $_SESSION['usuario_id']) {
            $temPermissao = true;
        }
        
        // 2. Se é admin/analista e a conversa não está atribuída (para visualização de conversas não atribuídas)
        if (in_array($_SESSION['usuario_perfil'], ['admin', 'analista']) && 
            ($conversa->usuario_id === null || $conversa->usuario_id == 0)) {
            $temPermissao = true;
        }
        
        if (!$temPermissao) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Acesso negado a esta conversa', 'alert alert-danger');
            Helper::redirecionar('chat/conversasNaoAtribuidas');
            return;
        }

        // Processar ações POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acao = $_POST['acao'] ?? '';

            switch ($acao) {
                case 'verificar_status':
                    $this->processarVerificacaoStatusManual($conversa_id, $conversa);
                    break;
            }
        }

        $mensagens = $this->chatModel->buscarMensagens($conversa_id);

        $dados = [
            'tituloPagina' => 'Conversa - ' . $conversa->contato_nome,
            'conversa' => $conversa,
            'mensagens' => $mensagens
        ];

        $this->view('chat/conversa', $dados);
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
            Helper::redirecionar('chat/index');
            return;
        }

        $mensagem = trim($_POST['mensagem'] ?? '');
        $temArquivo = isset($_FILES['midia']) && $_FILES['midia']['error'] === UPLOAD_ERR_OK;

        // Verificar se há mensagem ou arquivo
        if (empty($mensagem) && !$temArquivo) {
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> É necessário informar uma mensagem ou anexar um arquivo', 'alert alert-danger');
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        // Verificar erro de upload APENAS se um arquivo foi selecionado (mas falhou)
        if (isset($_FILES['midia']) && $_FILES['midia']['error'] !== UPLOAD_ERR_OK && $_FILES['midia']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errosUpload = [
                UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
                UPLOAD_ERR_PARTIAL => 'Upload parcial',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
                UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
                UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
            ];

            $erroMsg = $errosUpload[$_FILES['midia']['error']] ?? 'Erro desconhecido no upload';
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro no upload: ' . $erroMsg, 'alert alert-danger');
            Helper::redirecionar("chat/conversa/{$conversa_id}");
            return;
        }

        // Verificar se é a primeira mensagem
        $mensagensExistentes = $this->chatModel->contarMensagens($conversa_id);
        $precisaTemplate = ($mensagensExistentes == 0);

        $resultado = null;

        try {
            if ($temArquivo) {
                // Processar envio de mídia
                $resultado = $this->processarEnvioMidia($conversa, $_FILES['midia'], $mensagem, $precisaTemplate);
            } else {
                // Processar envio de texto
                error_log("DEBUG: Enviando mensagem de texto");

                if ($precisaTemplate) {
                    // Primeira mensagem - tentar template, se falhar usar mensagem normal
                    $resultado = $this->enviarPrimeiraMensagem($conversa->contato_numero, $mensagem);

                    // Se o template falhar, tentar mensagem normal
                    if (!$resultado || ($resultado['status'] !== 200 && $resultado['status'] !== 201)) {
                        error_log("DEBUG: Template falhou, tentando mensagem normal");
                        $resultado = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $mensagem);
                    }
                } else {
                    // Conversa já iniciada - enviar mensagem normal
                    $resultado = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $mensagem);
                }
            }

            if ($resultado && ($resultado['status'] == 200 || $resultado['status'] == 201)) {
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
                    // Determinar tipo de mídia
                    $tipoMidia = $this->determinarTipoMidia($_FILES['midia']['type']);

                    $dadosMensagem['tipo'] = $tipoMidia;
                    $dadosMensagem['conteudo'] = $mensagem; // Caption se houver
                    $dadosMensagem['midia_nome'] = $_FILES['midia']['name'];
                    $dadosMensagem['midia_url'] = null; // Será preenchido quando baixarmos da API
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
                error_log("ERRO ENVIO: " . print_r($resultado, true));
                Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro ao enviar: ' . $erro, 'alert alert-danger');
            }
        } catch (Exception $e) {
            error_log("EXCEÇÃO ENVIO: " . $e->getMessage());
            Helper::mensagem('chat', '<i class="fas fa-ban"></i> Erro interno: ' . $e->getMessage(), 'alert alert-danger');
        }

        Helper::redirecionar("chat/conversa/{$conversa_id}");
    }

    /**
     * Processa envio de mídia
     */
    private function processarEnvioMidia($conversa, $arquivo, $caption, $precisaTemplate)
    {
        // Validar arquivo
        $validacao = $this->validarArquivoMidia($arquivo);
        if (!$validacao['valido']) {
            throw new Exception($validacao['erro']);
        }

        // Fazer upload da mídia primeiro
        $resultadoUpload = SerproHelper::uploadMidia($arquivo, $arquivo['type']);

        // CORREÇÃO: Aceitar tanto 200 quanto 201 como sucesso
        if ($resultadoUpload['status'] !== 200 && $resultadoUpload['status'] !== 201) {
            throw new Exception('Erro no upload da mídia: ' . ($resultadoUpload['error'] ?? 'Erro desconhecido'));
        }

        $mediaId = $resultadoUpload['response']['id'];
        error_log("MÍDIA: Upload bem-sucedido - Media ID: $mediaId");

        // Determinar tipo de mídia
        $tipoMidia = $this->mapearTipoMidiaParaAPI($arquivo['type']);

        // Preparar parâmetros conforme tipo de mídia
        $filename = null;
        $captionParaEnvio = null;

        if ($tipoMidia === 'document') {
            // Para documentos: filename obrigatório, caption não permitido
            $filename = $arquivo['name'];
            error_log("MÍDIA: Enviando documento com filename: $filename");

            // Se há caption, enviar como mensagem de texto separada APÓS o documento
            if (!empty($caption)) {
                // Não enviar caption junto com documento, será enviado depois
                error_log("MÍDIA: Caption será enviado como mensagem separada após documento");
            }
        } elseif ($tipoMidia === 'image') {
            // Para imagens: caption permitido
            $captionParaEnvio = $caption;
            error_log("MÍDIA: Enviando imagem" . ($caption ? " com caption" : " sem caption"));
        } else {
            // Para vídeo/áudio: testar se caption é permitido
            $captionParaEnvio = $caption;
            error_log("MÍDIA: Enviando $tipoMidia" . ($caption ? " com caption" : " sem caption"));
        }

        if ($precisaTemplate && !empty($caption) && $tipoMidia !== 'document') {
            // Se é primeira mensagem e tem caption (exceto documentos), enviar template primeiro
            $resultadoTemplate = $this->enviarPrimeiraMensagem($conversa->contato_numero, $caption);

            if ($resultadoTemplate['status'] !== 200 && $resultadoTemplate['status'] !== 201) {
                throw new Exception('Erro ao enviar template: ' . ($resultadoTemplate['error'] ?? 'Erro desconhecido'));
            }

            // Aguardar um pouco antes de enviar a mídia
            sleep(1);
            // Não enviar caption novamente na mídia
            $captionParaEnvio = null;
        }

        $resultadoEnvio = SerproHelper::enviarMidia($conversa->contato_numero, $tipoMidia, $mediaId, $captionParaEnvio, null, $filename);
        error_log("MÍDIA: Resultado envio - Status: " . $resultadoEnvio['status']);

        // Se documento foi enviado com sucesso e há caption, enviar como mensagem separada
        if (
            $tipoMidia === 'document' &&
            ($resultadoEnvio['status'] === 200 || $resultadoEnvio['status'] === 201) &&
            !empty($caption)
        ) {

            error_log("MÍDIA: Enviando caption como mensagem separada...");
            sleep(1); // Aguardar um pouco

            // Enviar caption como mensagem de texto normal
            $resultadoCaption = SerproHelper::enviarMensagemTexto($conversa->contato_numero, $caption);
            error_log("MÍDIA: Caption enviado - Status: " . ($resultadoCaption['status'] ?? 'erro'));
        }

        return $resultadoEnvio;
    }

    /**
     * Valida arquivo de mídia
     */
    private function validarArquivoMidia($arquivo)
    {
        $tiposPermitidos = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'video/3gpp',
            'audio/aac',
            'audio/amr',
            'audio/mpeg',
            'audio/mp4',
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

        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            return ['valido' => false, 'erro' => 'Tipo de arquivo não permitido: ' . $arquivo['type']];
        }

        // Verificar tamanho
        $limiteTamanho = 5 * 1024 * 1024; // 5MB padrão
        if (strpos($arquivo['type'], 'video/') === 0 || strpos($arquivo['type'], 'audio/') === 0) {
            $limiteTamanho = 16 * 1024 * 1024; // 16MB para vídeo/áudio
        } elseif (strpos($arquivo['type'], 'application/') === 0) {
            $limiteTamanho = 95 * 1024 * 1024; // 95MB para documentos
        }

        if ($arquivo['size'] > $limiteTamanho) {
            $limiteMB = round($limiteTamanho / (1024 * 1024), 1);
            return ['valido' => false, 'erro' => "Arquivo muito grande. Limite: {$limiteMB}MB"];
        }

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
        $nomeTemplate = 'central_intimacao_remota'; // Substitua pelo nome do seu template aprovado

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
                    $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
                    break;
                    
                case 'audio':
                    $midiaId = $mensagemData['audio']['id'] ?? '';
                    $midiaTipo = $mensagemData['audio']['mime_type'] ?? 'audio/ogg';
                    $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
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
                    $conteudo = $midiaId; // Temporário, será substituído pelo caminho do MinIO
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
                            $conteudo = $caminhoMinio; // Usar caminho do MinIO ao invés do ID
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
                        'midia_url' => $midiaUrl, // Agora contém apenas o caminho (ex: document/2025/arquivo.pdf)
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
            'audio/aac',
            'audio/amr',
            'audio/mpeg',
            'audio/mp4',
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
            if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'admin') {
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
}

