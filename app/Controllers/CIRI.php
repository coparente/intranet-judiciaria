<?php

/**
 * [ CIRI ] - Controlador responsável por gerenciar a Central de Intimação Remota do Interior.
 * 
 * Este controlador permite:
 * - Listar, cadastrar, editar e excluir processos de análise
 * - Gerenciar movimentações dos processos
 * - Gerenciar destinatários das intimações
 * - Gerenciar tipos de atos e intimações
 * 
 * @author Seu Nome <seu.email@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */
class CIRI extends Controllers
{
    private $processosPorPagina = ITENS_POR_PAGINA;
    private $ciriModel;

    public function __construct()
    {
        parent::__construct();

        // Carrega o model de CIRI
        $this->ciriModel = $this->model('CIRIModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('./');
        }

        // Verifica se tem permissão para acessar o módulo
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista', 'usuario'])
        ) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }
    }

    /**
     * [ index ] - Exibe a página inicial do módulo CIRI
     * 
     * @return void
     */
    public function index()
    {

        // Redirecionar para a lista de processos
        Helper::redirecionar('ciri/listar');
    }

    /**
     * [ listar ] - Lista os processos de análise da CIRI
     * 
     * @param int $pagina Número da página atual
     * @return void
     */
    public function listar($pagina = 1)
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
        // Verifica permissão para o módulo de listagem de processos
        Middleware::verificarPermissao(10); // ID do módulo 'Listar Processos CIRI'
        // Filtros de busca
        $filtros = [
            'numero_processo' => filter_input(INPUT_GET, 'numero_processo', FILTER_SANITIZE_STRING),
            'comarca' => filter_input(INPUT_GET, 'comarca', FILTER_SANITIZE_STRING),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING),
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING),
            'usuario_id' => filter_input(INPUT_GET, 'usuario_id', FILTER_SANITIZE_STRING),
            'destinatario_ciri_id' => filter_input(INPUT_GET, 'destinatario_ciri_id', FILTER_SANITIZE_STRING)
        ];

        // Obter total de processos e configurar paginação
        $totalProcessos = $this->ciriModel->contarProcessos($filtros);
        $paginacao = [
            'pagina_atual' => $pagina,
            'total_registros' => $totalProcessos,
            'itens_por_pagina' => $this->processosPorPagina,
            'total_paginas' => ceil($totalProcessos / $this->processosPorPagina)
        ];

        // Obter processos para a página atual
        $processos = $this->ciriModel->listarProcessos(
            $filtros,
            $this->processosPorPagina,
            ($pagina - 1) * $this->processosPorPagina
        );

        // Após obter os processos, busque os destinatários de cada um
        foreach ($processos as &$processo) {
            $processo->destinatarios = $this->ciriModel->listarDestinatariosPorProcessoId($processo->id);
        }

        // Obter tipos de atos e intimações para os filtros
        $tiposAto = $this->ciriModel->listarTiposAto();
        $tiposIntimacao = $this->ciriModel->listarTiposIntimacao();

        // Obter lista de usuários para delegação
        $usuarios = $this->ciriModel->listarUsuariosParaDelegacao();
        $duplicados = $this->ciriModel->listarProcessosDuplicados();

        $dados = [
            'processos' => $processos,
            'paginacao' => $paginacao,
            'filtros' => $filtros,
            'tipos_ato' => $tiposAto,
            'tipos_intimacao' => $tiposIntimacao,
            'usuarios' => $usuarios,
            'destinatarios' => $this->ciriModel->listarDestinatariosPorProcessoId(),
            'duplicados' => $duplicados
        ];

        $this->view('ciri/listar', $dados);
    }

    /**
     * [ cadastrar ] - Cadastra um novo processo CIRI
     * 
     * @return void
     */
    public function cadastrar()
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

        // Verificar se o formulário foi submetido
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Preparar dados
            $dados = [
                'numero_processo' => trim($_POST['numero_processo']),
                'comarca_serventia' => trim($_POST['comarca_serventia']),
                'gratuidade_justica' => isset($_POST['gratuidade_justica']) ? $_POST['gratuidade_justica'] : null,
                'tipo_ato_ciri_id' => isset($_POST['tipo_ato_ciri_id']) ? $_POST['tipo_ato_ciri_id'] : null,
                'tipo_intimacao_ciri_id' => isset($_POST['tipo_intimacao_ciri_id']) ? $_POST['tipo_intimacao_ciri_id'] : null,
                'observacao_atividade' => trim($_POST['observacao_atividade']),
                'status_processo' => $_POST['status_processo'],
                'usuario_id' => null, // Não atribuir a nenhum usuário inicialmente
                'tipos_ato' => $this->ciriModel->listarTiposAto(),
                'tipos_intimacao' => $this->ciriModel->listarTiposIntimacao(),
                'confirmar_duplicado' => isset($_POST['confirmar_duplicado']) ? $_POST['confirmar_duplicado'] : 0
            ];

            // Validar dados
            $validacao = true;

            if (empty($dados['numero_processo'])) {
                $dados['numero_processo_erro'] = 'O número do processo é obrigatório';
                $validacao = false;
            } else {
                // Verificar se o processo já existe e se o usuário não confirmou o cadastro
                if ($this->ciriModel->verificarProcessoExistente($dados['numero_processo']) && !$dados['confirmar_duplicado']) {
                    $dados['processo_existente'] = true;
                    $this->view('ciri/cadastrar', $dados);
                    return;
                }
            }

            if (empty($dados['comarca_serventia'])) {
                $dados['comarca_serventia_erro'] = 'A comarca/serventia é obrigatória';
                $validacao = false;
            }

            // Se a validação passar, cadastrar o processo
            if ($validacao) {
                // Converter gratuidade de justiça para formato do banco
                if ($dados['gratuidade_justica'] == 'sim') {
                    $dados['gratuidade_justica'] = 'S';
                } elseif ($dados['gratuidade_justica'] == 'nao') {
                    $dados['gratuidade_justica'] = 'N';
                } else {
                    $dados['gratuidade_justica'] = null;
                }

                // Cadastrar o processo
                $processoId = $this->ciriModel->cadastrarProcesso($dados);

                if ($processoId) {
                    // Cadastrar movimentação inicial
                    $movimentacao = [
                        'processo_id' => $processoId,
                        'usuario_id' => $_SESSION['usuario_id'], // Mantém o registro de quem cadastrou
                        'descricao' => 'Processo cadastrado no sistema'
                    ];

                    $this->ciriModel->cadastrarMovimentacao($movimentacao);

                    // Se for um processo duplicado, registrar na movimentação
                    if (isset($dados['confirmar_duplicado']) && $dados['confirmar_duplicado']) {
                        $movimentacaoDuplicado = [
                            'processo_id' => $processoId,
                            'usuario_id' => $_SESSION['usuario_id'],
                            'descricao' => 'Processo cadastrado mesmo já existindo no sistema (duplicado)'
                        ];

                        $this->ciriModel->cadastrarMovimentacao($movimentacaoDuplicado);
                    }

                    Helper::mensagem('ciri', '<i class="fas fa-check-circle"></i> Processo cadastrado com sucesso!', 'alert alert-success');
                    Helper::mensagemSweetAlert('ciri', 'Processo cadastrado com sucesso!', 'success');
                    Helper::redirecionar('ciri/listar');
                } else {
                    Helper::mensagem('ciri', '<i class="fas fa-times-circle"></i> Erro ao cadastrar processo!', 'alert alert-danger');
                    Helper::mensagemSweetAlert('ciri', 'Erro ao cadastrar processo!', 'error');
                    $this->view('ciri/cadastrar', $dados);
                }
            } else {
                // Se a validação falhar, exibir o formulário novamente com os erros
                $this->view('ciri/cadastrar', $dados);
            }
        } else {
            // Exibir o formulário
            $dados = [
                'numero_processo' => '',
                'comarca_serventia' => '',
                'gratuidade_justica' => '',
                'tipo_ato_ciri_id' => '',
                'tipo_intimacao_ciri_id' => '',
                'observacao_atividade' => '',
                'status_processo' => 'pendente',
                'tipos_ato' => $this->ciriModel->listarTiposAto(),
                'tipos_intimacao' => $this->ciriModel->listarTiposIntimacao()
            ];

            $this->view('ciri/cadastrar', $dados);
        }
    }

    /**
     * [ visualizar ] - Visualiza os detalhes de um processo
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function visualizar($id = null)
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
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }


        // Obter dados do processo
        $processo = $this->ciriModel->obterProcessoPorId($id);

        // Verificar se o processo existe
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter movimentações do processo
        $movimentacoes = $this->ciriModel->listarMovimentacoes($id);

        // Obter destinatários do processo
        $destinatarios = $this->ciriModel->listarDestinatariosPorProcesso($id);

        // Obter lista de usuários para delegação
        $usuarios = $this->ciriModel->listarUsuariosParaDelegacao();

        $dados = [
            'processo' => $processo,
            'movimentacoes' => $movimentacoes,
            'destinatarios' => $destinatarios,
            'usuarios' => $usuarios
        ];

        $this->view('ciri/visualizar', $dados);
    }


    /**
     * [ editar ] - Edita um processo existente
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function editar($id = null)
    {
        // Verifica se tem permissão para acessar o módulo
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
        ) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::mensagemSweetAlert('ciri', 'Acesso negado: Apenas administradores e analistas podem acessar essa página', 'error');
            Helper::redirecionar('ciri/listar');
        }
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter dados do processo
        $processo = $this->ciriModel->obterProcessoPorId($id);

        // Verificar se o processo existe
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter tipos de atos e intimações para o formulário
        $tiposAto = $this->ciriModel->listarTiposAto();
        $tiposIntimacao = $this->ciriModel->listarTiposIntimacao();

        // Obter destinatários do processo
        $destinatarios = $this->ciriModel->listarDestinatariosPorProcesso($id);

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'id' => $id,
                'gratuidade_justica' => trim($formulario['gratuidade_justica']),
                'numero_processo' => trim($formulario['numero_processo']),
                'comarca_serventia' => trim($formulario['comarca_serventia']),
                'data_atividade' => trim($formulario['data_atividade']),
                'observacao_atividade' => trim($formulario['observacao_atividade']),
                'tipo_intimacao_ciri_id' => trim($formulario['tipo_intimacao_ciri_id']),
                'tipo_ato_ciri_id' => trim($formulario['tipo_ato_ciri_id']),
                'status_processo' => trim($formulario['status_processo']),
                'numero_processo_erro' => '',
                'comarca_serventia_erro' => '',
                'tipos_ato' => $tiposAto,
                'tipos_intimacao' => $tiposIntimacao,
                'destinatarios' => $destinatarios
            ];

            // Validação dos campos
            if (empty($dados['numero_processo'])) :
                $dados['numero_processo_erro'] = 'Preencha o número do processo';
            endif;

            if (empty($dados['comarca_serventia'])) :
                $dados['comarca_serventia_erro'] = 'Preencha a comarca/serventia';
            endif;

            // Se não houver erros, atualiza o processo
            if (empty($dados['numero_processo_erro']) && empty($dados['comarca_serventia_erro'])) :
                if ($this->ciriModel->atualizarProcesso($dados)) :
                    // Registrar movimentação de atualização
                    $movimentacao = [
                        'processo_id' => $id,
                        'usuario_id' => $_SESSION['usuario_id'],
                        'descricao' => 'Processo atualizado'
                    ];

                    $this->ciriModel->cadastrarMovimentacao($movimentacao);

                    Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo atualizado com sucesso');
                    Helper::mensagemSweetAlert('ciri', 'Processo atualizado com sucesso', 'success');
                    Helper::redirecionar('ciri/visualizar/' . $id);
                else :
                    die("Erro ao atualizar processo no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'id' => $id,
                'gratuidade_justica' => $processo->gratuidade_justica,
                'numero_processo' => $processo->numero_processo,
                'comarca_serventia' => $processo->comarca_serventia,
                'data_atividade' => $processo->data_atividade,
                'observacao_atividade' => $processo->observacao_atividade,
                'tipo_intimacao_ciri_id' => $processo->tipo_intimacao_ciri_id,
                'tipo_ato_ciri_id' => $processo->tipo_ato_ciri_id,
                'status_processo' => $processo->status_processo,
                'numero_processo_erro' => '',
                'comarca_serventia_erro' => '',
                'tipos_ato' => $tiposAto,
                'tipos_intimacao' => $tiposIntimacao,
                'destinatarios' => $destinatarios
            ];
        endif;

        $this->view('ciri/editar', $dados);
    }

    /**
     * [ editar ] - Edita um processo existente
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function editarMeusProcessos($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Obter dados do processo
        $processo = $this->ciriModel->obterProcessoPorId($id);

        // Verificar se o processo existe
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Obter tipos de atos e intimações para o formulário
        $tiposAto = $this->ciriModel->listarTiposAto();
        $tiposIntimacao = $this->ciriModel->listarTiposIntimacao();

        // Obter destinatários do processo
        $destinatarios = $this->ciriModel->listarDestinatariosPorProcesso($id);

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'id' => $id,
                'gratuidade_justica' => trim($formulario['gratuidade_justica']),
                'numero_processo' => trim($formulario['numero_processo']),
                'comarca_serventia' => trim($formulario['comarca_serventia']),
                'data_atividade' => trim($formulario['data_atividade']),
                'observacao_atividade' => trim($formulario['observacao_atividade']),
                'tipo_intimacao_ciri_id' => trim($formulario['tipo_intimacao_ciri_id']),
                'tipo_ato_ciri_id' => trim($formulario['tipo_ato_ciri_id']),
                'status_processo' => trim($formulario['status_processo']),
                'numero_processo_erro' => '',
                'comarca_serventia_erro' => '',
                'tipos_ato' => $tiposAto,
                'tipos_intimacao' => $tiposIntimacao,
                'destinatarios' => $destinatarios
            ];

            // Validação dos campos
            if (empty($dados['numero_processo'])) :
                $dados['numero_processo_erro'] = 'Preencha o número do processo';
            endif;

            if (empty($dados['comarca_serventia'])) :
                $dados['comarca_serventia_erro'] = 'Preencha a comarca/serventia';
            endif;

            // Se não houver erros, atualiza o processo
            if (empty($dados['numero_processo_erro']) && empty($dados['comarca_serventia_erro'])) :
                if ($this->ciriModel->atualizarProcesso($dados)) :
                    // Registrar movimentação de atualização
                    $movimentacao = [
                        'processo_id' => $id,
                        'usuario_id' => $_SESSION['usuario_id'],
                        'descricao' => 'Processo atualizado '
                    ];

                    $this->ciriModel->cadastrarMovimentacao($movimentacao);

                    Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo atualizado com sucesso');
                    Helper::mensagemSweetAlert('ciri', 'Processo atualizado com sucesso', 'success');
                    // Helper::redirecionar('ciri/visualizarMeusProcessos/' . $id);
                    Helper::redirecionar('ciri/meusProcessos');
                else :
                    die("Erro ao atualizar processo no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'id' => $id,
                'gratuidade_justica' => $processo->gratuidade_justica,
                'numero_processo' => $processo->numero_processo,
                'comarca_serventia' => $processo->comarca_serventia,
                'data_atividade' => $processo->data_atividade,
                'observacao_atividade' => $processo->observacao_atividade,
                'tipo_intimacao_ciri_id' => $processo->tipo_intimacao_ciri_id,
                'tipo_ato_ciri_id' => $processo->tipo_ato_ciri_id,
                'status_processo' => $processo->status_processo,
                'numero_processo_erro' => '',
                'comarca_serventia_erro' => '',
                'tipos_ato' => $tiposAto,
                'tipos_intimacao' => $tiposIntimacao,
                'destinatarios' => $destinatarios
            ];
        endif;

        $this->view('ciri/editar_meus_processos', $dados);
    }

    /**
     * [ excluir ] - Exclui um processo
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function excluir($id = null)
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
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Verificar se o processo existe
        $processo = $this->ciriModel->obterProcessoPorId($id);
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Excluir o processo
        if ($this->ciriModel->excluirProcesso($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo excluído com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Processo excluído com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao excluir processo', 'alert alert-danger');
            Helper::mensagemSweetAlert('ciri', 'Erro ao excluir processo', 'error');
        }

        Helper::redirecionar('ciri/listar');
    }

    /**
     * [ adicionarMovimentacao ] - Adiciona uma nova movimentação ao processo
     * 
     * @param int $processoId ID do processo
     * @return void
     */
    public function adicionarMovimentacao($processoId = null)
    {
        // Verifica se tem permissão para acessar o módulo
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
        ) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::mensagemSweetAlert('ciri', 'Acesso negado: Apenas administradores e analistas podem acessar essa página', 'error');
            Helper::redirecionar('ciri/visualizar/' . $processoId);
            return;
        }
        // Verificar se o ID do processo foi fornecido
        if (!$processoId) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Verificar se o processo existe
        $processo = $this->ciriModel->obterProcessoPorId($processoId);
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'processo_id' => $processoId,
                'usuario_id' => $_SESSION['usuario_id'],
                'descricao' => trim($formulario['descricao']),
                'descricao_erro' => ''
            ];

            // Validação dos campos
            if (empty($dados['descricao'])) :
                $dados['descricao_erro'] = 'Preencha a descrição da movimentação';
            endif;

            // Se não houver erros, cadastra a movimentação
            if (empty($dados['descricao_erro'])) :
                if ($this->ciriModel->cadastrarMovimentacao($dados)) :
                    Helper::mensagem('ciri', '<i class="fas fa-check"></i> Movimentação adicionada com sucesso');
                    Helper::mensagemSweetAlert('ciri', 'Movimentação adicionada com sucesso', 'success');
                    Helper::redirecionar('ciri/visualizar/' . $processoId);
                else :
                    die("Erro ao adicionar movimentação no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'processo_id' => $processoId,
                'usuario_id' => $_SESSION['usuario_id'],
                'descricao' => '',
                'descricao_erro' => '',
                'processo' => $processo
            ];
        endif;

        $this->view('ciri/adicionar_movimentacao', $dados);
    }

    /**
     * [ adicionarDestinatario ] - Adiciona um novo destinatário ao processo
     * 
     * @param int $processoId ID do processo
     * @return void
     */
    public function adicionarDestinatario($processoId = null)
    {
        // Verificar se o ID do processo foi fornecido
        if (!$processoId) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Verificar se o processo existe
        $processo = $this->ciriModel->obterProcessoPorId($processoId);
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'processo_id' => $processoId,
                'nome' => trim($formulario['nome']),
                'telefone' => trim($formulario['telefone']),
                'email' => trim($formulario['email']),
                'nome_erro' => ''
            ];

            // Validação dos campos
            if (empty($dados['nome'])) :
                $dados['nome_erro'] = 'Preencha o nome do destinatário';
            endif;

            // Se não houver erros, cadastra o destinatário
            if (empty($dados['nome_erro'])) :
                if ($this->ciriModel->cadastrarDestinatario($dados)) :
                    Helper::mensagem('ciri', '<i class="fas fa-check"></i> Destinatário adicionado com sucesso');
                    Helper::mensagemSweetAlert('ciri', 'Destinatário adicionado com sucesso', 'success');
                    Helper::redirecionar('ciri/visualizar/' . $processoId);
                else :
                    die("Erro ao adicionar destinatário no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'processo_id' => $processoId,
                'nome' => '',
                'telefone' => '',
                'email' => '',
                'nome_erro' => '',
                'processo' => $processo
            ];
        endif;

        $this->view('ciri/adicionar_destinatario', $dados);
    }

    /**
     * [ adicionarDestinatario ] - Adiciona um novo destinatário ao processo
     * 
     * @param int $processoId ID do processo
     * @return void
     */
    public function adicionarMeuDestinatario($processoId = null)
    {
        // Verificar se o ID do processo foi fornecido
        if (!$processoId) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do processo não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Verificar se o processo existe
        $processo = $this->ciriModel->obterProcessoPorId($processoId);
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'processo_id' => $processoId,
                'nome' => trim($formulario['nome']),
                'telefone' => trim($formulario['telefone']),
                'email' => trim($formulario['email']),
                'nome_erro' => ''
            ];

            // Validação dos campos
            if (empty($dados['nome'])) :
                $dados['nome_erro'] = 'Preencha o nome do destinatário';
            endif;

            // Se não houver erros, cadastra o destinatário
            if (empty($dados['nome_erro'])) :
                if ($this->ciriModel->cadastrarDestinatario($dados)) :
                    Helper::mensagem('ciri', '<i class="fas fa-check"></i> Destinatário adicionado com sucesso');
                    Helper::mensagemSweetAlert('ciri', 'Destinatário adicionado com sucesso', 'success');
                    Helper::redirecionar('ciri/visualizarMeuProcesso/' . $processoId);
                else :
                    die("Erro ao adicionar destinatário no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'processo_id' => $processoId,
                'nome' => '',
                'telefone' => '',
                'email' => '',
                'nome_erro' => '',
                'processo' => $processo
            ];
        endif;

        $this->view('ciri/adicionar_meu_destinatario', $dados);
    }

    /**
     * [ excluirDestinatario ] - Exclui um destinatário
     * 
     * @param int $id ID do destinatário
     * @return void
     */
    public function excluirDestinatario($id = null)
    {
        // Verifica se tem permissão para acessar o módulo
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])
        ) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::mensagemSweetAlert('ciri', 'Acesso negado: Apenas administradores e analistas podem acessar essa página', 'error');
            Helper::redirecionar('ciri/listar');
        }
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do destinatário não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter dados do destinatário
        $destinatario = $this->ciriModel->obterDestinatarioPorId($id);

        // Verificar se o destinatário existe
        if (!$destinatario) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Destinatário não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter o ID do processo associado ao destinatário
        $processoId = $destinatario->processo_id;

        // Excluir o destinatário
        if ($this->ciriModel->excluirDestinatario($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Destinatário excluído com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Destinatário excluído com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao excluir destinatário', 'alert alert-danger');
        }

        Helper::redirecionar('ciri/visualizar/' . $processoId);
    }

    /**
     * [ editarDestinatario ] - Edita um destinatário existente
     * 
     * @param int $id ID do destinatário
     * @return void
     */
    public function editarDestinatario($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do destinatário não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter dados do destinatário
        $destinatario = $this->ciriModel->obterDestinatarioPorId($id);

        // Verificar se o destinatário existe
        if (!$destinatario) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Destinatário não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter o ID do processo associado ao destinatário
        $processoId = $destinatario->processo_id;

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            if (empty($formulario['nome'])) {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O nome do destinatário é obrigatório', 'alert alert-danger');
                $this->view('ciri/editar_destinatario', ['destinatario' => $destinatario]);
                return;
            }

            // Preparar dados para atualização
            $dados = [
                'id' => $id,
                'nome' => $formulario['nome'],
                'email' => $formulario['email'] ?? '',
                'telefone' => $formulario['telefone'] ?? '',
            ];

            // Atualizar o destinatário
            if ($this->ciriModel->atualizarDestinatario($dados)) {
                Helper::mensagem('ciri', '<i class="fas fa-check"></i> Destinatário atualizado com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('ciri', 'Destinatário atualizado com sucesso', 'success');
                Helper::redirecionar('ciri/visualizar/' . $processoId);
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao atualizar destinatário', 'alert alert-danger');
                $this->view('ciri/editar_destinatario', ['destinatario' => $destinatario]);
            }
        } else {
            // Exibir o formulário
            $this->view('ciri/editar_destinatario', ['destinatario' => $destinatario]);
        }
    }

    /**
     * [ editarDestinatario ] - Edita um destinatário existente
     * 
     * @param int $id ID do destinatário
     * @return void
     */
    public function editarMeuDestinatario($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do destinatário não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Obter dados do destinatário
        $destinatario = $this->ciriModel->obterDestinatarioPorId($id);

        // Verificar se o destinatário existe
        if (!$destinatario) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Destinatário não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Obter o ID do processo associado ao destinatário
        $processoId = $destinatario->processo_id;

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            if (empty($formulario['nome'])) {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O nome do destinatário é obrigatório', 'alert alert-danger');
                $this->view('ciri/editar_meu_destinatario', ['destinatario' => $destinatario]);
                return;
            }

            // Preparar dados para atualização
            $dados = [
                'id' => $id,
                'nome' => $formulario['nome'],
                'email' => $formulario['email'] ?? '',
                'telefone' => $formulario['telefone'] ?? '',
            ];

            // Atualizar o destinatário
            if ($this->ciriModel->atualizarDestinatario($dados)) {
                Helper::mensagem('ciri', '<i class="fas fa-check"></i> Destinatário atualizado com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('ciri', 'Destinatário atualizado com sucesso', 'success');
                Helper::redirecionar('ciri/visualizarMeuProcesso/' . $processoId);
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao atualizar destinatário', 'alert alert-danger');
                $this->view('ciri/editar_meu_destinatario', ['destinatario' => $destinatario]);
            }
        } else {
            // Exibir o formulário
            $this->view('ciri/editar_meu_destinatario', ['destinatario' => $destinatario]);
        }
    }

    /**
     * [ gerenciarTiposAto ] - Gerencia os tipos de ato
     * 
     * @return void
     */
    public function gerenciarTiposAto()
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Listar todos os tipos de ato
        $tiposAto = $this->ciriModel->listarTiposAto();

        $dados = [
            'tipos_ato' => $tiposAto
        ];

        $this->view('ciri/gerenciar_tipos_ato', $dados);
    }

    /**
     * [ editarTipoAto ] - Exibe o formulário para editar um tipo de ato
     * 
     * @param int $id ID do tipo de ato
     * @return void
     */
    public function editarTipoAto($id = null)
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do tipo de ato não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Buscar o tipo de ato pelo ID
        $tipoAto = $this->ciriModel->obterTipoAtoPorId($id);

        // Verificar se o tipo de ato existe
        if (!$tipoAto) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Tipo de ato não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            if (empty($formulario['nome'])) {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O nome do tipo de ato é obrigatório', 'alert alert-danger');
                $this->view('ciri/editar_tipo_ato', ['tipo_ato' => $tipoAto]);
                return;
            }

            // Preparar dados para atualização
            $dados = [
                'id' => $id,
                'nome' => $formulario['nome'],
                'descricao' => $formulario['descricao'] ?? ''
            ];

            // Atualizar o tipo de ato
            if ($this->ciriModel->atualizarTipoAto($dados)) {
                Helper::mensagem('ciri', '<i class="fas fa-check"></i> Tipo de ato atualizado com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('ciri', 'Tipo de ato atualizado com sucesso', 'success');
                Helper::redirecionar('ciri/gerenciarTiposAto');
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao atualizar tipo de ato', 'alert alert-danger');
                $this->view('ciri/editar_tipo_ato', ['tipo_ato' => $tipoAto]);
            }
        } else {
            // Exibir o formulário
            $this->view('ciri/editar_tipo_ato', ['tipo_ato' => $tipoAto]);
        }
    }

    /**
     * [ excluirTipoAto ] - Exclui um tipo de ato
     * 
     * @param int $id ID do tipo de ato
     * @return void
     */
    public function excluirTipoAto($id = null)
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do tipo de ato não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Verificar se o tipo de ato está sendo usado em algum processo
        if ($this->ciriModel->tipoAtoEmUso($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Este tipo de ato não pode ser excluído porque está sendo usado em processos', 'alert alert-warning');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Excluir o tipo de ato
        if ($this->ciriModel->excluirTipoAto($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Tipo de ato excluído com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Tipo de ato excluído com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao excluir tipo de ato', 'alert alert-danger');
        }

        Helper::redirecionar('ciri/gerenciarTiposAto');
    }

    /**
     * [ adicionarTipoAto ] - Exibe o formulário para adicionar um novo tipo de ato
     * 
     * @return void
     */
    public function adicionarTipoAto()
    {

        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposAto');
            return;
        }

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            if (empty($formulario['nome'])) {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O nome do tipo de ato é obrigatório', 'alert alert-danger');
                $this->view('ciri/adicionar_tipo_ato');
                return;
            }

            // Preparar dados para inserção
            $dados = [
                'nome' => $formulario['nome'],
                'descricao' => $formulario['descricao'] ?? ''
            ];

            // Adicionar o tipo de ato
            if ($this->ciriModel->adicionarTipoAto($dados)) {
                Helper::mensagem('ciri', '<i class="fas fa-check"></i> Tipo de ato adicionado com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('ciri', 'Tipo de ato adicionado com sucesso', 'success');
                Helper::redirecionar('ciri/gerenciarTiposAto');
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao adicionar tipo de ato', 'alert alert-danger');
                $this->view('ciri/adicionar_tipo_ato');
            }
        } else {
            // Exibir o formulário
            $this->view('ciri/adicionar_tipo_ato');
        }
    }

    /**
     * [ gerenciarTiposIntimacao ] - Gerencia os tipos de intimação
     * 
     * @return void
     */
    public function gerenciarTiposIntimacao()
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Listar todos os tipos de intimação
        $tiposIntimacao = $this->ciriModel->listarTiposIntimacao();

        $dados = [
            'tipos_intimacao' => $tiposIntimacao
        ];

        $this->view('ciri/gerenciar_tipos_intimacao', $dados);
    }

    /**
     * [ editarTipoIntimacao ] - Exibe o formulário para editar um tipo de intimação
     * 
     * @param int $id ID do tipo de intimação
     * @return void
     */
    public function editarTipoIntimacao($id = null)
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do tipo de intimação não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Buscar o tipo de intimação pelo ID
        $tipoIntimacao = $this->ciriModel->obterTipoIntimacaoPorId($id);

        // Verificar se o tipo de intimação existe
        if (!$tipoIntimacao) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Tipo de intimação não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            if (empty($formulario['nome'])) {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O nome do tipo de intimação é obrigatório', 'alert alert-danger');
                $this->view('ciri/editar_tipo_intimacao', ['tipo_intimacao' => $tipoIntimacao]);
                return;
            }

            // Preparar dados para atualização
            $dados = [
                'id' => $id,
                'nome' => $formulario['nome'],
                'descricao' => $formulario['descricao'] ?? ''
            ];

            // Atualizar o tipo de intimação
            if ($this->ciriModel->atualizarTipoIntimacao($dados)) {
                Helper::mensagem('ciri', '<i class="fas fa-check"></i> Tipo de intimação atualizado com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('ciri', 'Tipo de intimação atualizado com sucesso', 'success');
                Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao atualizar tipo de intimação', 'alert alert-danger');
                $this->view('ciri/editar_tipo_intimacao', ['tipo_intimacao' => $tipoIntimacao]);
            }
        } else {
            // Exibir o formulário
            $this->view('ciri/editar_tipo_intimacao', ['tipo_intimacao' => $tipoIntimacao]);
        }
    }

    /**
     * [ excluirTipoIntimacao ] - Exclui um tipo de intimação
     * 
     * @param int $id ID do tipo de intimação
     * @return void
     */
    public function excluirTipoIntimacao($id = null)
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID do tipo de intimação não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Verificar se o tipo de intimação está sendo usado em algum processo
        if ($this->ciriModel->tipoIntimacaoEmUso($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Este tipo de intimação não pode ser excluído porque está sendo usado em processos', 'alert alert-warning');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Excluir o tipo de intimação
        if ($this->ciriModel->excluirTipoIntimacao($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Tipo de intimação excluído com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Tipo de intimação excluído com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao excluir tipo de intimação', 'alert alert-danger');
        }

        Helper::redirecionar('ciri/gerenciarTiposIntimacao');
    }

    /**
     * [ adicionarTipoIntimacao ] - Adiciona um novo tipo de intimação
     * 
     * @return void
     */
    public function adicionarTipoIntimacao()
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem adicionar tipos de intimação', 'alert alert-danger');
            Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            return;
        }

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            if (empty($formulario['nome'])) {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O nome do tipo de intimação é obrigatório', 'alert alert-danger');
                $this->view('ciri/adicionar_tipo_intimacao');
                return;
            }

            // Preparar dados para inserção
            $dados = [
                'nome' => $formulario['nome'],
                'descricao' => $formulario['descricao'] ?? ''
            ];

            // Adicionar o tipo de intimação
            if ($this->ciriModel->adicionarTipoIntimacao($dados)) {
                Helper::mensagem('ciri', '<i class="fas fa-check"></i> Tipo de intimação adicionado com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('ciri', 'Tipo de intimação adicionado com sucesso', 'success');
                Helper::redirecionar('ciri/gerenciarTiposIntimacao');
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao adicionar tipo de intimação', 'alert alert-danger');
                $this->view('ciri/adicionar_tipo_intimacao');
            }
        } else {
            // Exibir o formulário
            $this->view('ciri/adicionar_tipo_intimacao');
        }
    }

    /**
     * [ meusProcessos ] - Exibe os processos atribuídos ao usuário logado
     * 
     * @param int $pagina Número da página atual
     * @return void
     */
    public function meusProcessos($pagina = 1)
    {
        // Obter filtros da URL
        $filtros = [
            'numero_processo' => filter_input(INPUT_GET, 'numero_processo', FILTER_SANITIZE_STRING),
            'comarca' => filter_input(INPUT_GET, 'comarca', FILTER_SANITIZE_STRING),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING),
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING),
            'destinatario_ciri_id' => filter_input(INPUT_GET, 'destinatario_ciri_id', FILTER_SANITIZE_STRING),
            'usuario_id' => $_SESSION['usuario_id'] // Filtrar apenas pelo usuário logado
        ];

        // Obter destinatários do processo
        // $destinatarios = $this->ciriModel->listarDestinatariosPorProcesso();

        // Calcular offset para paginação
        $offset = ($pagina - 1) * $this->processosPorPagina;

        // Obter total de registros para paginação
        $totalRegistros = $this->ciriModel->contarProcessos($filtros);

        // Obter processos com paginação
        $processos = $this->ciriModel->listarProcessos(
            $filtros,
            $this->processosPorPagina,
            $offset
        );

        // Adicionar destinatários para cada processo
        foreach ($processos as &$processo) {
            $processo->destinatarios = $this->ciriModel->listarDestinatariosPorProcessoId($processo->id);
        }

        // Calcular total de páginas
        $totalPaginas = ceil($totalRegistros / $this->processosPorPagina);

        $dados = [
            'processos' => $processos,
            'filtros' => $filtros,
            'destinatarios' => $this->ciriModel->listarDestinatariosPorUsuario($_SESSION['usuario_id']), // Modificada esta linha
            'paginacao' => [
                'pagina_atual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalRegistros
            ]
        ];

        $this->view('ciri/meus_processos', $dados);
    }

    /**
     * [ sortearProcesso ] - Sorteia um processo pendente para o usuário logado
     * 
     * @return void
     */
    public function sortearProcesso()
    {
        // Verificar se o usuário tem permissão
        if (!isset($_SESSION['usuario_id'])) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Você precisa estar logado para sortear processos', 'alert alert-danger');
            Helper::redirecionar('usuarios/login');
            return;
        }

        // Obter o ID do usuário logado
        $usuarioId = $_SESSION['usuario_id'];

        // Tentar sortear um processo
        $resultado = $this->ciriModel->sortearProcessoParaUsuario($usuarioId);

        if ($resultado) {
            // Cadastrar movimentação
            $movimentacao = [
                'processo_id' => $resultado,
                'usuario_id' => $usuarioId,
                'data_movimentacao' => date('Y-m-d'),
                'descricao' => 'Processo atribuído por sorteio ao usuário'
            ];

            $this->ciriModel->cadastrarMovimentacao($movimentacao);

            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo sorteado com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Processo sorteado com sucesso!', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Não há processos pendentes disponíveis para sorteio', 'alert alert-warning');
            Helper::mensagemSweetAlert('ciri', 'Não há processos pendentes disponíveis para sorteio', 'warning');
        }

        // Redirecionar para a página de meus processos
        Helper::redirecionar('ciri/meusProcessos');
    }

    /**
     * [ visualizarMeuProcesso ] - Exibe os detalhes de um processo específico do usuário logado
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function visualizarMeuProcesso($id)
    {
        // Verificar se o processo existe e pertence ao usuário logado
        $processo = $this->ciriModel->obterProcessoPorId($id);

        if (!$processo || $processo->usuario_id != $_SESSION['usuario_id']) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado ou você não tem permissão para visualizá-lo', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Obter destinatários do processo
        $destinatarios = $this->ciriModel->listarDestinatariosPorProcesso($id);

        // Obter movimentações do processo
        $movimentacoes = $this->ciriModel->listarMovimentacoesPorProcesso($id);

        $dados = [
            'processo' => $processo,
            'destinatarios' => $destinatarios,
            'movimentacoes' => $movimentacoes
        ];

        $this->view('ciri/visualizar_meu_processo', $dados);
    }

    /**
     * [ concluirProcesso ] - Marca um processo como concluído
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function concluirProcesso($id)
    {
        // Verificar se o processo existe e pertence ao usuário logado
        $processo = $this->ciriModel->obterProcessoPorId($id);

        if (!$processo || $processo->usuario_id != $_SESSION['usuario_id']) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado ou você não tem permissão para concluí-lo', 'alert alert-danger');
            Helper::redirecionar('ciri/meusProcessos');
            return;
        }

        // Atualizar status do processo para concluído e remover atribuição do usuário
        if ($this->ciriModel->concluirERemoverAtribuicaoProcesso($id)) {
            // Registrar movimentação
            $movimentacao = [
                'processo_id' => $id,
                'usuario_id' => $_SESSION['usuario_id'],
                'descricao' => 'Processo concluído pelo usuário'
            ];

            $this->ciriModel->cadastrarMovimentacao($movimentacao);

            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo concluído com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Processo concluído com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Erro ao concluir o processo', 'alert alert-danger');
        }

        Helper::redirecionar('ciri/meusProcessos');
    }

    /**
     * [ delegarProcesso ] - Delega um processo específico para um usuário
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function delegarProcesso($id)
    {
        // Verificar se o processo existe
        $processo = $this->ciriModel->obterProcessoPorId($id);
        if (!$processo) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Verificar se o formulário foi enviado
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (!isset($formulario['usuario_id']) || empty($formulario['usuario_id'])) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Selecione um usuário para delegação', 'alert alert-danger');
            Helper::redirecionar('ciri/visualizar/' . $id);
            return;
        }

        $usuarioId = $formulario['usuario_id'];

        // Delegar o processo
        if ($this->ciriModel->delegarProcesso($id, $usuarioId)) {
            // Registrar movimentação
            $movimentacao = [
                'processo_id' => $id,
                'usuario_id' => $_SESSION['usuario_id'],
                'data_movimentacao' => date('Y-m-d'),
                'descricao' => 'Processo delegado para outro usuário'
            ];

            $this->ciriModel->cadastrarMovimentacao($movimentacao);

            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Processo delegado com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Processo delegado com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao delegar processo', 'alert alert-danger');
        }

        Helper::redirecionar('ciri/visualizar/' . $id);
    }

    /**
     * [ delegarProcessos ] - Delega múltiplos processos para um usuário
     * 
     * @return void
     */
    public function delegarProcessos()
    {
        // Verificar se o formulário foi enviado
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (!isset($formulario['usuario_id']) || empty($formulario['usuario_id'])) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Selecione um usuário para delegação', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        if (!isset($formulario['processos']) || empty($formulario['processos'])) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Selecione pelo menos um processo para delegação', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        $usuarioId = $formulario['usuario_id'];
        $processos = $formulario['processos'];

        $sucessos = 0;
        $falhas = 0;

        foreach ($processos as $processoId) {
            if ($this->ciriModel->delegarProcesso($processoId, $usuarioId)) {
                // Registrar movimentação
                $movimentacao = [
                    'processo_id' => $processoId,
                    'usuario_id' => $_SESSION['usuario_id'],
                    'data_movimentacao' => date('Y-m-d'),
                    'descricao' => 'Processo delegado para outro usuário'
                ];

                $this->ciriModel->cadastrarMovimentacao($movimentacao);
                $sucessos++;
            } else {
                $falhas++;
            }
        }

        if ($sucessos > 0) {
            Helper::mensagem('ciri', '<i class="fas fa-check"></i> ' . $sucessos . ' processo(s) delegado(s) com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', $sucessos . ' processo(s) delegado(s) com sucesso', 'success');
        }

        if ($falhas > 0) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ' . $falhas . ' processo(s) não puderam ser delegados', 'alert alert-warning');
        }

        Helper::redirecionar('ciri/listar');
    }

    /**
     * [ editarMovimentacao ] - Edita uma movimentação existente
     * 
     * @param int $id ID da movimentação
     * @return void
     */
    public function editarMovimentacao($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID da movimentação não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter dados da movimentação
        $movimentacao = $this->ciriModel->obterMovimentacaoPorId($id);

        // Verificar se a movimentação existe
        if (!$movimentacao) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Movimentação não encontrada', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter o ID do processo associado à movimentação
        $processoId = $movimentacao->processo_id;

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validar dados
            $dados = [
                'id' => $id,
                'processo_id' => $processoId,
                'usuario_id' => $_SESSION['usuario_id'],
                'descricao' => $formulario['descricao'] ?? '',
                'descricao_erro' => ''
            ];

            // Validar descrição
            if (empty($dados['descricao'])) {
                $dados['descricao_erro'] = 'A descrição da movimentação é obrigatória';
            }

            // Se não houver erros, atualiza a movimentação
            if (empty($dados['descricao_erro'])) {
                if ($this->ciriModel->atualizarMovimentacao($dados)) {
                    Helper::mensagem('ciri', '<i class="fas fa-check"></i> Movimentação atualizada com sucesso', 'alert alert-success');
                    Helper::mensagemSweetAlert('ciri', 'Movimentação atualizada com sucesso', 'success');
                    Helper::redirecionar('ciri/visualizar/' . $processoId);
                } else {
                    Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao atualizar movimentação', 'alert alert-danger');
                    $this->view('ciri/editar_movimentacao', $dados);
                }
            } else {
                // Se houver erros, exibe o formulário novamente
                $this->view('ciri/editar_movimentacao', $dados);
            }
        } else {
            // Exibir o formulário
            $dados = [
                'id' => $id,
                'processo_id' => $processoId,
                'usuario_id' => $movimentacao->usuario_id,
                'descricao' => $movimentacao->descricao,
                'descricao_erro' => '',
                'data_movimentacao' => $movimentacao->data_movimentacao
            ];

            $this->view('ciri/editar_movimentacao', $dados);
        }
    }

    /**
     * [ excluirMovimentacao ] - Exclui uma movimentação
     * 
     * @param int $id ID da movimentação
     * @return void
     */
    public function excluirMovimentacao($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> ID da movimentação não fornecido', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter dados da movimentação
        $movimentacao = $this->ciriModel->obterMovimentacaoPorId($id);

        // Verificar se a movimentação existe
        if (!$movimentacao) {
            Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Movimentação não encontrada', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Obter o ID do processo associado à movimentação
        $processoId = $movimentacao->processo_id;

        // Excluir a movimentação
        if ($this->ciriModel->excluirMovimentacao($id)) {
            Helper::mensagem('ciri', '<i class="fas fa-check"></i> Movimentação excluída com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('ciri', 'Movimentação excluída com sucesso', 'success');
        } else {
            Helper::mensagem('ciri', '<i class="fas fa-times"></i> Erro ao excluir movimentação', 'alert alert-danger');
        }

        Helper::redirecionar('ciri/visualizar/' . $processoId);
    }

    /**
     * [ uploadProcessos ] - Exibe a página de upload de processos por arquivo TXT
     * 
     * @return void
     */
    public function uploadProcessos()
    {
        // Verificar se é administrador ou analista
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'analista'])) {
            Helper::mensagem('ciri', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem fazer upload de processos', 'alert alert-danger');
            Helper::redirecionar('ciri/listar');
            return;
        }

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verificar se um arquivo foi enviado
            if (isset($_FILES['arquivo_txt']) && $_FILES['arquivo_txt']['error'] == 0) {
                $arquivo = $_FILES['arquivo_txt'];

                // Verificar tipo de arquivo
                $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
                if (strtolower($extensao) != 'txt') {
                    Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Apenas arquivos TXT são permitidos', 'alert alert-danger');
                    $this->view('ciri/upload_processos');
                    return;
                }

                // Verificar tamanho do arquivo (máximo 10MB)
                if ($arquivo['size'] > 10 * 1024 * 1024) {
                    Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O arquivo é muito grande. Tamanho máximo: 10MB', 'alert alert-danger');
                    $this->view('ciri/upload_processos');
                    return;
                }

                // Ler o conteúdo do arquivo
                $conteudo = file_get_contents($arquivo['tmp_name']);

                // Dividir o conteúdo pelos separadores #
                $numerosProcesso = explode('#', $conteudo);

                // Limpar os números (remover espaços, quebras de linha, etc.)
                $numerosProcesso = array_map('trim', $numerosProcesso);
                $numerosProcesso = array_filter($numerosProcesso); // Remover itens vazios

                if (empty($numerosProcesso)) {
                    Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> O arquivo não contém números de processo válidos.', 'alert alert-danger');
                    $this->view('ciri/upload_processos');
                    return;
                }

                // Verificar duplicados antes de cadastrar
                $duplicados = $this->ciriModel->verificarProcessosDuplicados($numerosProcesso);

                // Separar os números que não são duplicados
                $numerosUnicos = array_diff($numerosProcesso, $duplicados);

                // Obter dados do formulário
                $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                $comarcaServentia = $formulario['comarca_serventia'] ?? null;

                $totalProcessados = 0;
                $totalErros = 0;
                $erros = [];

                // Processar os números que não são duplicados
                foreach ($numerosUnicos as $numeroProcesso) {
                    $dados = [
                        'usuario_id' => null,
                        'gratuidade_justica' => 'N',
                        'numero_processo' => $numeroProcesso,
                        'comarca_serventia' => $comarcaServentia,  // ajuste: usar o valor recebido
                        'observacao_atividade' => 'Importado via upload de arquivo TXT',
                        'tipo_intimacao_ciri_id' => null,
                        'tipo_ato_ciri_id' => null,
                        'status_processo' => 'pendente'
                    ];

                    $processoId = $this->ciriModel->cadastrarProcesso($dados);
                    if ($processoId) {
                        $movimentacao = [
                            'processo_id' => $processoId,
                            'usuario_id' => $_SESSION['usuario_id'],
                            'descricao' => 'Processo importado via upload de arquivo TXT'
                        ];
                        $this->ciriModel->cadastrarMovimentacao($movimentacao);
                        $totalProcessados++;
                    } else {
                        $totalErros++;
                        $erros[] = $numeroProcesso;
                    }
                }
                // Montar todas as mensagens
                $mensagens = [];

                if ($totalProcessados > 0) {
                    $mensagens[] = "✅ $totalProcessados - processos importados com sucesso.";
                }

                if (!empty($duplicados)) {
                    $mensagens[] = "⚠️ Não importados por serem duplicados: " . implode(', ', $duplicados);
                }

                if ($totalErros > 0) {
                    $mensagens[] = "❌ Erro ao importar $totalErros processos: " . implode(', ', $erros);
                }

                // Exibir tudo junto no SweetAlert
                Helper::mensagemSweetAlert('ciri', implode('<br>',$mensagens), 'info');

                // Redirecionar
                Helper::redirecionar('ciri/listar');
                return;
            } else {
                Helper::mensagem('ciri', '<i class="fas fa-exclamation-triangle"></i> Nenhum arquivo foi enviado ou ocorreu um erro no upload', 'alert alert-danger');
            }
        }

        $this->view('ciri/upload_processos');
    }

    
}
