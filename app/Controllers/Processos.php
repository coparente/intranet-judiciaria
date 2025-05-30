<?php

/**
 * [ PROCESSOS ] - Controlador responsável por gerenciar os processos de custas.
 * 
 * Este controlador permite:
 * - Cadastrar e gerenciar processos de custas
 * - Registrar movimentações
 * - Controlar intimações
 * - Gerar relatórios
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class Processos extends Controllers
{
    private $processoModel;
    private $atividadeModel;
    private $usuarioModel;
    private $guiaPagamentoModel;
    private $pendenciaModel;
    private $parteModel;
    private $db;

    public function __construct()
    {
        parent::__construct();

        // Verifica permissões
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['usuario', 'admin', 'analista'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        $this->processoModel = $this->model('ProcessoCustasModel');
        $this->atividadeModel = $this->model('AtividadeModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->guiaPagamentoModel = $this->model('GuiaPagamentoModel');
        $this->pendenciaModel = $this->model('PendenciaModel');
        $this->parteModel = $this->model('ParteModel');

        $this->db = new Database();
    }

    /**
     * [ index ] - Exibe a lista de processos com paginação
     * 
     * @param int $pagina Número da página atual
     * @return void
     */
    public function listar($pagina = 1)
    {

        // Verifica permissão para o módulo de listagem de processos
        Middleware::verificarPermissao(4); // ID do módulo 'Listar Processos'

        $filtros = [
            'numero_processo' => filter_input(INPUT_GET, 'numero_processo', FILTER_SANITIZE_STRING),
            'comarca_serventia' => filter_input(INPUT_GET, 'comarca_serventia', FILTER_SANITIZE_STRING),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING)
        ];

        $resultados = $this->processoModel->listarProcessos($pagina, ITENS_POR_PAGINA, $filtros);

        $dados = [
            'tituloPagina' => 'Processos de Custas',
            'processos' => $resultados['processos'],
            'total_processos' => $resultados['total'],
            'total_paginas' => $resultados['paginas'],
            'pagina_atual' => $pagina,
            'filtros' => $filtros,
            'usuarios' => $this->usuarioModel->buscarUsuariosComPermissao(4) // 4 é o ID do módulo de processos
        ];

        $this->view('processos/index', $dados);
    }

    /**
     * [ cadastrar ] - Cadastra novo processo
     * 
     * @return void
     */
    public function cadastrar()
    {
        // Verifica permissão para cadastrar processos
        // Middleware::verificarPermissao(5); // ID do módulo 'Cadastrar Processos'

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $dados = [
                'numero_processo' => trim($formulario['numero_processo']),
                'comarca' => trim($formulario['comarca']),
                'guias' => isset($formulario['guias']) ? $formulario['guias'] : [],
                'erros' => [],
                'processo_existente' => false,
                'confirmar_duplicado' => isset($formulario['confirmar_duplicado']) ? true : false
            ];

            // Validações
            if (empty($dados['numero_processo'])) {
                $dados['erros']['numero_processo'] = 'O número do processo é obrigatório';
            } else {
                // Verifica se o processo já existe e se o usuário não confirmou o cadastro
                if ($this->processoModel->verificarProcessoExistente($dados['numero_processo']) && !$dados['confirmar_duplicado']) {
                    $dados['processo_existente'] = true;
                    $this->view('processos/cadastrar', $dados);
                    return;
                }
            }
            if (empty($dados['comarca'])) {
                $dados['erros']['comarca'] = 'A comarca/serventia é obrigatória';
            }

            // Validar se há pelo menos uma guia
            if (empty($dados['guias'])) {
                $dados['erros']['guias'] = 'É necessário adicionar pelo menos uma guia de pagamento';
            }

            if (empty($dados['erros'])) {
                try {
                    // Inicia transação
                    $this->db->iniciarTransacao();

                    // Cadastra o processo
                    $processo_id = $this->processoModel->cadastrarProcesso($dados);

                    if ($processo_id) {
                        // Cadastra as guias
                        $guias_ok = true;
                        foreach ($dados['guias'] as $guia) {
                            // Preparar dados da guia
                            $dados_guia = [
                                'processo_id' => $processo_id,
                                'numero_guia' => $guia['numero'],
                                'valor' => Helper::formatarValorParaBD($guia['valor']),
                                'data_vencimento' => $guia['vencimento'],
                                'status' => 'pendente',
                                'observacao' => isset($guia['descricao']) ? $guia['descricao'] : '',
                                'parte_id' => null, // Pode ser atualizado posteriormente
                                'usuario_cadastro' => $_SESSION['usuario_id'] // Adicionando o usuário que está cadastrando
                            ];

                            if (!$this->guiaPagamentoModel->cadastrarGuia($dados_guia)) {
                                $guias_ok = false;
                                break;
                            }
                        }

                        if ($guias_ok) {
                            // Confirma transação
                            $this->db->confirmarTransacao();

                            $this->atividadeModel->registrarAtividade(
                                $_SESSION['usuario_id'],
                                'Cadastro de Processo',
                                "Cadastrou processo {$dados['numero_processo']} com " . count($dados['guias']) . " guias"
                            );

                            Helper::mensagem('processos', '<i class="fas fa-check"></i> Processo cadastrado com sucesso!', 'alert alert-success');
                            Helper::mensagemSweetAlert('processos', 'Processo cadastrado com sucesso!', 'success');
                            Helper::redirecionar('processos/listar');
                        } else {
                            // Cancela transação
                            $this->db->cancelarTransacao();
                            throw new Exception("Erro ao cadastrar guias de pagamento");
                        }
                    } else {
                        // Cancela transação
                        $this->db->cancelarTransacao();
                        throw new Exception("Erro ao cadastrar processo");
                    }
                } catch (Exception $e) {
                    // Garante que a transação seja cancelada em caso de erro
                    $this->db->cancelarTransacao();

                    Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao cadastrar processo: ' . $e->getMessage(), 'alert alert-danger');
                    Helper::mensagemSweetAlert('processos', 'Erro ao cadastrar processo: ' . $e->getMessage(), 'error');
                    $this->view('processos/cadastrar', $dados);
                }
            } else {
                $this->view('processos/cadastrar', $dados);
            }
        } else {
            $dados = [
                'numero_processo' => '',
                'comarca' => '',
                'erros' => []
            ];
            $this->view('processos/cadastrar', $dados);
        }
    }

    /**
     * [ movimentar ] - Registra movimentação no processo
     * 
     * @param int $processo_id - ID do processo
     * @return void
     */
    public function movimentar($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $dados = [
                'tipo' => trim($formulario['tipo']),
                'descricao' => trim($formulario['descricao']),
                'prazo' => !empty($formulario['prazo']) ? $formulario['prazo'] : null
            ];

            if ($this->processoModel->registrarMovimentacao($processo_id, $dados['tipo'], $dados['descricao'], $dados['prazo'])) {
                $this->atividadeModel->registrarAtividade(
                    $_SESSION['usuario_id'],
                    'Movimentação de Processo',
                    "Registrou movimentação no processo ID {$processo_id}"
                );

                Helper::mensagem('processos', '<i class="fas fa-check"></i> Movimentação registrada com sucesso!', 'alert alert-success');
                Helper::mensagemSweetAlert('processos', 'Movimentação registrada com sucesso!', 'success');
            }
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ visualizar ] - Visualiza detalhes do processo
     * 
     * @param int $id - ID do processo
     * @return void
     */
    public function visualizar($id)
    {
        $processo = $this->processoModel->buscarProcessoPorId($id);

        if (!$processo) {
            Helper::mensagem('processos', 'Processo não encontrado', 'alert alert-danger');
            Helper::redirecionar('processos/listar');
        }

        $dados = [
            'tituloPagina' => "Processo {$processo->numero_processo}",
            'processo' => $processo,
            'advogados' => $this->processoModel->buscarAdvogadosProcesso($id),
            'movimentacoes' => $this->processoModel->listarMovimentacoes($id),
            'intimacoes' => $this->processoModel->listarIntimacoes($id),
            'guias' => $this->guiaPagamentoModel->listarGuiasProcesso($id),
            'pendencias' => $this->pendenciaModel->listarPendenciasProcesso($id),
            'partes' => $this->parteModel->listarPartesProcesso($id)
        ];

        $this->view('processos/visualizar', $dados);
    }

    /**
     * [ registrarIntimacao ] - Registra uma nova intimação para o processo
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function registrarIntimacao($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $erros = [];
            if (empty($formulario['tipo_intimacao'])) {
                $erros['tipo_intimacao'] = 'O tipo de intimação é obrigatório';
            }
            if (empty($formulario['parte_id'])) {
                $erros['parte_id'] = 'A parte é obrigatória';
            }
            if (empty($formulario['prazo'])) {
                $erros['prazo'] = 'O prazo é obrigatório';
            } elseif (strtotime($formulario['prazo']) < strtotime('today')) {
                $erros['prazo'] = 'O prazo não pode ser menor que a data atual';
            }

            if (empty($erros)) {
                // Buscar os dados da parte selecionada
                $parte = $this->parteModel->buscarPartePorId($formulario['parte_id']);

                $dados = [
                    'processo_id' => $processo_id,
                    'tipo_intimacao' => $formulario['tipo_intimacao'],
                    'parte_id' => $formulario['parte_id'],
                    'destinatario' => $parte->nome, // Usa o nome da parte como destinatário
                    'prazo' => $formulario['prazo']
                ];


                if ($this->processoModel->registrarIntimacao($dados)) {
                    // Helper::enviarMensagem("55" . $parte->telefone, "Olá mundo");
                    Helper::mensagem('processos', '<i class="fas fa-check"></i> Intimação registrada com sucesso!', 'alert alert-success');
                    Helper::mensagemSweetAlert('processos', 'Intimação registrada com sucesso!', 'success');
                } else {
                    Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao registrar intimação', 'alert alert-danger');
                    Helper::mensagemSweetAlert('processos', 'Erro ao registrar intimação', 'error');
                }
            } else {
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro: ' . implode(', ', $erros), 'alert alert-danger');
            }
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ excluirIntimacao ] - Exclui uma intimação
     * 
     * @param int $intimacao_id ID da intimação
     * @return void
     */
    public function excluirIntimacao($intimacao_id)
    {
        $intimacao = $this->processoModel->buscarIntimacaoPorId($intimacao_id);
        if (!$intimacao) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Intimação não encontrada', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Intimação não encontrada', 'error');
            Helper::redirecionar('processos/listar');
        }

        if ($this->processoModel->excluirIntimacao($intimacao_id)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Exclusão de Intimação',
                "Excluiu intimação ID {$intimacao_id} do processo ID {$intimacao->processo_id}"
            );
            Helper::mensagem('processos', '<i class="fas fa-check"></i> Intimação excluída com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Intimação excluída com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao excluir intimação', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao excluir intimação', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$intimacao->processo_id}");
    }

    /**
     * [ atualizarIntimacao ] - Atualiza uma intimação
     * 
     * @param int $intimacao_id ID da intimação
     * @return void
     */
    public function atualizarIntimacao($intimacao_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $intimacao = $this->processoModel->buscarIntimacaoPorId($intimacao_id);
            if (!$intimacao) {
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> Intimação não encontrada', 'alert alert-danger');
                Helper::mensagemSweetAlert('processos', 'Intimação não encontrada', 'error');
                Helper::redirecionar('processos/listar');
            }
            // Buscar os dados da parte selecionada
            $parte = $this->parteModel->buscarPartePorId($formulario['parte_id']);

            $dados = [
                'id' => $intimacao_id,
                'tipo_intimacao' => $formulario['tipo_intimacao'],
                'destinatario' => $parte->nome, // Usa o nome da parte como destinatário
                'prazo' => $formulario['prazo'],
                'status' => $formulario['status']
            ];

            if ($this->processoModel->atualizarIntimacao($dados)) {
                $this->atividadeModel->registrarAtividade(
                    $_SESSION['usuario_id'],
                    'Atualização de Intimação',
                    "Atualizou intimação ID {$intimacao_id} do processo ID {$intimacao->processo_id}"
                );
                Helper::mensagem('processos', '<i class="fas fa-check"></i> Intimação atualizada com sucesso!', 'alert alert-success');
                Helper::mensagemSweetAlert('processos', 'Intimação atualizada com sucesso!', 'success');
            } else {
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar intimação', 'alert alert-danger');
                Helper::mensagemSweetAlert('processos', 'Erro ao atualizar intimação', 'error');
            }

            Helper::redirecionar("processos/visualizar/{$intimacao->processo_id}");
        }
    }

    /**
     * [ editar ] - Exibe formulário de edição do processo
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function editar($id)
    {
        // Verifica se o usuário tem permissão
        if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'analista')) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Acesso negado: Apenas analistas e administradores podem editar processos', 'alert alert-danger');
            Helper::redirecionar('processos/listar');
            return;
        }
        $processo = $this->processoModel->buscarProcessoPorId($id);

        if (!$processo) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Processo não encontrado', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Processo não encontrado', 'error');
            Helper::redirecionar('processos');
        }

        $dados = [
            'tituloPagina' => "Editar Processo {$processo->numero_processo}",
            'processo' => $processo,
            'advogados' => $this->processoModel->buscarAdvogadosProcesso($id)
        ];

        // Se for admin, busca lista de responsáveis
        if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin') {
            $dados['responsaveis'] = $this->usuarioModel->listarUsuarios();
        }

        $this->view('processos/editar', $dados);
    }

    /**
     * [ atualizar ] - Atualiza os dados do processo
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function atualizar($id)
    {
        // Verifica se o usuário tem permissão
        if (!$this->usuarioModel->verificarPerfil($_SESSION['usuario_id'], 'analista')) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Acesso negado: Apenas analistas e administradores podem editar processos', 'alert alert-danger');
            Helper::redirecionar('processos/listar');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos/visualizar/' . $id);
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $dados = [
            'id' => $id,
            'numero_processo' => trim($formulario['numero_processo']),
            'comarca_serventia' => trim($formulario['comarca_serventia']),
            'status' => trim($formulario['status']),
            'observacoes' => isset($formulario['observacoes']) ? trim($formulario['observacoes']) : null,
            'responsavel_id' => isset($formulario['responsavel_id']) && !empty($formulario['responsavel_id']) ?
                trim($formulario['responsavel_id']) : null,
            'data_conclusao' => isset($formulario['data_conclusao']) && !empty($formulario['data_conclusao']) ?
                $formulario['data_conclusao'] : null
        ];

        // Validações
        if (empty($dados['numero_processo'])) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> O número do processo é obrigatório', 'alert alert-danger');
            Helper::redirecionar('processos/editar/' . $id);
            return;
        }

        if (empty($dados['comarca_serventia'])) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> A comarca/serventia é obrigatória', 'alert alert-danger');
            Helper::redirecionar('processos/editar/' . $id);
            return;
        }

        if ($this->processoModel->atualizarProcesso($dados)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Atualização de Processo',
                "Atualizou dados do processo ID {$id}"
            );
            Helper::mensagem('processos', '<i class="fas fa-check"></i> Processo atualizado com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Processo atualizado com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar processo', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar processo', 'error');
        }

        Helper::redirecionar('processos/visualizar/' . $id);
    }

    /**
     * [ atualizarStatus ] - Atualiza o status do processo
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function atualizarStatus($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $status = $formulario['status'];

        if ($this->processoModel->atualizarStatus($processo_id, $status)) {
            // Registra movimentação
            $this->processoModel->registrarMovimentacao(
                $processo_id,
                $status,
                "Status alterado para: {$status}"
            );
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Atualização de Status',
                "Alterou status do processo ID {$processo_id} para {$status}"
            );
            Helper::mensagem('processos', '<i class="fas fa-check"></i> Status atualizado com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Status atualizado com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar status', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar status', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ adicionarAdvogado ] - Adiciona um advogado ao processo
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function adicionarAdvogado($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $dados = [
                'processo_id' => $processo_id,
                'nome' => trim($formulario['nome']),
                'oab' => trim($formulario['oab']),
                'erros' => []
            ];

            // Validações
            if (empty($dados['nome'])) {
                $dados['erros']['nome'] = 'O nome do advogado é obrigatório';
            } elseif (strlen($dados['nome']) < 3) {
                $dados['erros']['nome'] = 'O nome deve ter pelo menos 3 caracteres';
            }

            if (empty($dados['oab'])) {
                $dados['erros']['oab'] = 'O número da OAB é obrigatório';
            }

            // Se não houver erros, tenta cadastrar
            if (empty($dados['erros'])) {
                try {
                    if ($this->processoModel->adicionarAdvogado($dados)) {
                        $this->atividadeModel->registrarAtividade(
                            $_SESSION['usuario_id'],
                            'Cadastro de Advogado',
                            "Adicionou advogado {$dados['nome']} ao processo ID {$processo_id}"
                        );

                        // Registra a movimentação do processo
                        $this->processoModel->registrarMovimentacao(
                            $processo_id,
                            'Cadastro de Advogado',
                            "Advogado {$dados['nome']} adicionado(o) ao processo"
                        );

                        Helper::mensagem('processos', '<i class="fas fa-check"></i> Advogado adicionado com sucesso!', 'alert alert-success');
                        Helper::mensagemSweetAlert('processos', 'Advogado adicionado com sucesso!', 'success');
                    } else {
                        throw new Exception("Erro ao adicionar advogado");
                    }
                } catch (Exception $e) {
                    Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao adicionar advogado: ' . $e->getMessage(), 'alert alert-danger');
                    Helper::mensagemSweetAlert('processos', 'Erro ao adicionar advogado', 'error');
                }
            } else {
                // Se houver erros, exibe as mensagens
                $mensagens_erro = implode('<br>', $dados['erros']);
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> ' . $mensagens_erro, 'alert alert-danger');
                Helper::mensagemSweetAlert('processos', $mensagens_erro, 'error');
                // Helper::mensagemSweetAlert('processos', 'Erro na validação dos dados', 'error');
            }
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ excluirAdvogado ] - Remove um advogado do processo
     * 
     * @param int $advogado_id ID do advogado
     * @return void
     */
    public function excluirAdvogado($advogado_id)
    {
        $advogado = $this->processoModel->buscarAdvogadoPorId($advogado_id);
        if (!$advogado) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Advogado não encontrado', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Advogado não encontrado', 'error');
            Helper::redirecionar('processos/visualizar/' . $advogado->processo_id);
        }

        if ($this->processoModel->excluirAdvogado($advogado_id)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Exclusão de Advogado',
                "Removeu advogado ID {$advogado_id} do processo ID {$advogado->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $advogado->processo_id,
                'Exclusão de Advogado',
                "Advogado ID {$advogado_id} removido(o) do processo"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Advogado removido com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Advogado removido com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao remover advogado', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao remover advogado', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$advogado->processo_id}");
    }

    /**
     * [ atualizarObservacoes ] - Atualiza as observações do processo
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function atualizarObservacoes($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $dados = [
                'id' => $processo_id,
                'observacoes' => trim($formulario['observacoes'])
            ];

            if ($this->processoModel->atualizarObservacoes($dados)) {
                $this->atividadeModel->registrarAtividade(
                    $_SESSION['usuario_id'],
                    'Atualização de Observações',
                    "Atualizou observações do processo ID {$processo_id}"
                );
                Helper::mensagem('processos', '<i class="fas fa-check"></i> Observações atualizadas com sucesso!', 'alert alert-success');
                Helper::mensagemSweetAlert('processos', 'Observações atualizadas com sucesso!', 'success');
            } else {
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar observações', 'alert alert-danger');
                Helper::mensagemSweetAlert('processos', 'Erro ao atualizar observações', 'error');
            }
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ atualizarResponsavel ] - Atualiza o responsável do processo
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function atualizarResponsavel($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $responsavel_id = $formulario['responsavel_id'];

        if ($this->processoModel->atualizarResponsavel($processo_id, $responsavel_id)) {
            // Busca nome do novo responsável
            $novo_responsavel = $this->usuarioModel->lerUsuarioPorId($responsavel_id);

            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Atualização de Responsável',
                "Alterou responsável do processo ID {$processo_id} para {$novo_responsavel->nome}"
            );
            Helper::mensagem('processos', '<i class="fas fa-check"></i> Responsável atualizado com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Responsável atualizado com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar responsável', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar responsável', 'error');
        }

        Helper::redirecionar("processos/listar");
    }

    /**
     * [ delegarLote ] - Delega vários processos para um responsável
     * 
     * @return void
     */
    public function delegarLote()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $responsavel_id = $formulario['responsavel_id'];
        $processos = isset($formulario['processos']) ? $formulario['processos'] : [];

        if (empty($processos)) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Nenhum processo selecionado', 'alert alert-danger');
            Helper::redirecionar('processos/listar');
            return;
        }

        $sucessos = 0;
        $falhas = 0;

        foreach ($processos as $processo_id) {
            if ($this->processoModel->atualizarResponsavel($processo_id, $responsavel_id)) {
                $sucessos++;
            } else {
                $falhas++;
            }
        }

        $novo_responsavel = $this->usuarioModel->lerUsuarioPorId($responsavel_id);

        $this->atividadeModel->registrarAtividade(
            $_SESSION['usuario_id'],
            'Delegação em Lote',
            "Delegou {$sucessos} processo(s) para {$novo_responsavel->nome}"
        );

        if ($falhas == 0) {
            Helper::mensagem('processos', '<i class="fas fa-check"></i> Processos delegados com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Processos delegados com sucesso!', 'success');
        } else {
            Helper::mensagem(
                'processos',
                "<i class=\"fas fa-exclamation-triangle\"></i> {$sucessos} processo(s) delegado(s) com sucesso e {$falhas} falha(s)",
                'alert alert-warning'
            );
        }

        Helper::redirecionar('processos/listar');
    }

    /**
     * [ cadastrarGuia ] - Cadastra uma nova guia de pagamento
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function cadastrarGuia($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // Validação da parte
        if (empty($formulario['parte_id'])) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> É necessário selecionar uma parte', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'É necessário selecionar uma parte', 'error');
            Helper::redirecionar("processos/visualizar/{$processo_id}");
            return;
        }

        $dados = [
            'processo_id' => $processo_id,
            'numero_guia' => trim($formulario['numero_guia']),
            'valor' => trim($formulario['valor']),
            'data_vencimento' => $formulario['data_vencimento'],
            'status' => $formulario['status'],
            'observacao' => trim($formulario['observacao']),
            'parte_id' => $formulario['parte_id']
        ];

        if ($this->guiaPagamentoModel->cadastrarGuia($dados)) {
            // Buscar nome da parte
            $parte = $this->parteModel->buscarPartePorId($dados['parte_id']);

            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Cadastro de Guia',
                "Cadastrou guia {$dados['numero_guia']} para {$parte->nome} no processo ID {$processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $processo_id,
                'Cadastro de Guia',
                "Guia {$dados['numero_guia']} cadastrada para {$parte->nome} no valor de R$ {$dados['valor']}. Status: {$dados['status']}"
            );


            Helper::mensagem('processos', '<i class="fas fa-check"></i> Guia cadastrada com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Guia cadastrada com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao cadastrar guia', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao cadastrar guia', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ cadastrarPendencia ] - Cadastra uma nova pendência
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function cadastrarPendencia($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $dados = [
            'processo_id' => $processo_id,
            'tipo_pendencia' => trim($formulario['tipo_pendencia']),
            'descricao' => trim($formulario['descricao'])
        ];

        if ($this->pendenciaModel->cadastrarPendencia($dados)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Cadastro de Pendência',
                "Cadastrou pendência para o processo ID {$processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $processo_id,
                'Cadastro de Pendência',
                "Pendência Tipo: {$dados['tipo_pendencia']} - Descrição: {$dados['descricao']}"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Pendência cadastrada com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Pendência cadastrada com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao cadastrar pendência', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao cadastrar pendência', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }

    /**
     * [ excluirGuia ] - Exclui uma guia de pagamento
     * 
     * @param int $guia_id ID da guia
     * @return void
     */
    public function excluirGuia($guia_id)
    {
        $guia = $this->guiaPagamentoModel->buscarGuiaPorId($guia_id);
        if (!$guia) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Guia não encontrada', 'alert alert-danger');
            Helper::redirecionar('processos');
            return;
        }

        if ($this->guiaPagamentoModel->excluirGuia($guia_id)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Exclusão de Guia',
                "Excluiu guia {$guia->numero_guia} do processo ID {$guia->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $guia->processo_id,
                'Exclusão de Guia',
                "Guia {$guia->numero_guia} excluída do processo ID {$guia->processo_id}"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Guia excluída com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Guia excluída com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao excluir guia', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao excluir guia', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$guia->processo_id}");
    }

    /**
     * [ atualizarGuia ] - Atualiza uma guia de pagamento
     * 
     * @param int $guia_id ID da guia
     * @return void
     */
    public function atualizarGuia($guia_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $guia = $this->guiaPagamentoModel->buscarGuiaPorId($guia_id);
        if (!$guia) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Guia não encontrada', 'alert alert-danger');
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // Validação da parte
        if (empty($formulario['parte_id'])) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> É necessário selecionar uma parte', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'É necessário selecionar uma parte', 'error');
            Helper::redirecionar("processos/visualizar/{$guia->processo_id}");
            return;
        }

        // Tratamento dos campos de data
        $data_vencimento = !empty($formulario['data_vencimento']) ? trim($formulario['data_vencimento']) : null;
        $data_pagamento = !empty($formulario['data_pagamento']) ? trim($formulario['data_pagamento']) : null;

        $dados = [
            'id' => $guia_id,
            'numero_guia' => trim($formulario['numero_guia']),
            'valor' => trim($formulario['valor']),
            'data_vencimento' => $data_vencimento,
            'data_pagamento' => $data_pagamento,
            'status' => $formulario['status'],
            'observacao' => trim($formulario['observacao']),
            'parte_id' => $formulario['parte_id']
        ];

        if ($this->guiaPagamentoModel->atualizarGuia($dados)) {
            // Buscar nome da parte
            $parte = $this->parteModel->buscarPartePorId($dados['parte_id']);

            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Atualização de Guia',
                "Atualizou guia {$dados['numero_guia']} de {$parte->nome} do processo ID {$guia->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $guia->processo_id,
                'Atualização de Guia',
                "Guia {$dados['numero_guia']} atualizada para {$parte->nome} do processo ID {$guia->processo_id}"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Guia atualizada com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Guia atualizada com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar guia', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar guia', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$guia->processo_id}");
    }

    /**
     * [ atualizarPendencia ] - Atualiza uma pendência existente
     * 
     * @param int $pendencia_id ID da pendência
     * @return void
     */
    public function atualizarPendencia($pendencia_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $pendencia = $this->pendenciaModel->buscarPendenciaPorId($pendencia_id);
        if (!$pendencia) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Pendência não encontrada', 'alert alert-danger');
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $dados = [
            'id' => $pendencia_id,
            'tipo_pendencia' => trim($formulario['tipo_pendencia']),
            'descricao' => trim($formulario['descricao']),
            'status' => $formulario['status'],
        ];

        if ($this->pendenciaModel->atualizarPendencia($dados)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Atualização de Pendência',
                "Atualizou pendência ID {$pendencia_id} do processo ID {$pendencia->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $pendencia->processo_id,
                'Atualização de Pendência',
                "Pendência tipo: {$dados['tipo_pendencia']} - Descrição: {$dados['descricao']} - Status: {$dados['status']}"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Pendência atualizada com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Pendência atualizada com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar pendência', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar pendência', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$pendencia->processo_id}");
    }

    /**
     * [ excluirPendencia ] - Exclui uma pendência
     * 
     * @param int $pendencia_id ID da pendência
     * @return void
     */
    public function excluirPendencia($pendencia_id)
    {
        $pendencia = $this->pendenciaModel->buscarPendenciaPorId($pendencia_id);
        if (!$pendencia) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Pendência não encontrada', 'alert alert-danger');
            Helper::redirecionar('processos');
            return;
        }

        if ($this->pendenciaModel->excluirPendencia($pendencia_id)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Exclusão de Pendência',
                "Excluiu pendência ID {$pendencia_id} do processo ID {$pendencia->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $pendencia->processo_id,
                'Exclusão de Pendência',
                "Pendência tipo: {$pendencia->tipo_pendencia} - Descrição: {$pendencia->descricao} - Status: {$pendencia->status} - Excluída do processo ID {$pendencia->processo_id}"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Pendência excluída com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Pendência excluída com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao excluir pendência', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao excluir pendência', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$pendencia->processo_id}");
    }

    /**
     * [ cadastrarParte ] - Cadastra uma nova parte no processo
     * 
     * @param int $processo_id ID do processo
     * @return void
     */
    public function cadastrarParte($processo_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $dados = [
            'processo_id' => $processo_id,
            'tipo' => trim($formulario['tipo']),
            'nome' => trim($formulario['nome']),
            'documento' => trim($formulario['documento']),
            'tipo_documento' => trim($formulario['tipo_documento']),
            'telefone' => trim($formulario['telefone']),
            'email' => trim($formulario['email']),
            'endereco' => trim($formulario['endereco']),
            'erros' => []
        ];

        // Validações
        if (empty($dados['tipo'])) {
            $dados['erros']['tipo'] = 'O Tipo é obrigatório';
        } elseif (strlen($dados['nome']) < 3) {
            $dados['erros']['nome'] = 'O nome deve ter pelo menos 3 caracteres';
        }
        if (empty($dados['tipo_documento'])) {
            $dados['erros']['tipo_documento'] = 'O tipo de documento é obrigatório';
        } elseif (!Helper::validarCPFCNPJ($dados['documento'])) {
            $dados['erros']['documento'] = 'O documento informado é inválido';
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> CPF ou CNPJ inválido', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'CPF ou CNPJ inválido', 'error');
        }
        // Se não houver erros, tenta cadastrar
        if (empty($dados['erros'])) {

            try {
                if ($this->parteModel->cadastrarParte($dados)) {
                    $this->atividadeModel->registrarAtividade(
                        $_SESSION['usuario_id'],
                        'Cadastro de Parte',
                        "Cadastrou parte {$dados['nome']} no processo ID {$processo_id}"
                    );

                    // Registra a movimentação do processo
                    $this->processoModel->registrarMovimentacao(
                        $processo_id,
                        'Cadastro de Parte',
                        "Parte {$dados['nome']} cadastrada(o) no processo"
                    );

                    Helper::mensagem('processos', '<i class="fas fa-check"></i> Parte cadastrada com sucesso!', 'alert alert-success');
                    Helper::mensagemSweetAlert('processos', 'Parte cadastrada com sucesso!', 'success');
                } else {
                    throw new Exception("Erro ao cadastrar parte");
                    // Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao cadastrar parte', 'alert alert-danger');
                    // Helper::mensagemSweetAlert('processos', 'Erro ao cadastrar parte', 'error');
                }
            } catch (Exception $e) {
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao cadastrar parte: ' . $e->getMessage(), 'alert alert-danger');
                Helper::mensagemSweetAlert('processos', 'Erro ao cadastrar parte: ' . $e->getMessage(), 'error');
            }
        }

        Helper::redirecionar("processos/visualizar/{$processo_id}");
    }


    /**
     * [ atualizarParte ] - Atualiza os dados de uma parte
     * 
     * @param int $parte_id ID da parte
     * @return void
     */
    public function atualizarParte($parte_id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Helper::redirecionar('processos');
            return;
        }

        $parte = $this->parteModel->buscarPartePorId($parte_id);
        if (!$parte) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Parte não encontrada', 'alert alert-danger');
            Helper::redirecionar('processos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $dados = [
            'id' => $parte_id,
            'tipo' => trim($formulario['tipo']),
            'nome' => trim($formulario['nome']),
            'documento' => trim($formulario['documento']),
            'tipo_documento' => trim($formulario['tipo_documento']),
            'telefone' => trim($formulario['telefone']),
            'email' => trim($formulario['email']),
            'endereco' => trim($formulario['endereco'])
        ];

        if ($this->parteModel->atualizarParte($dados)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Atualização de Parte',
                "Atualizou parte ID {$parte_id} do processo ID {$parte->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $parte->processo_id,
                'Atualização de Parte',
                "Parte ID {$parte_id} atualizada(o) no processo"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Parte atualizada com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Parte atualizada com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao atualizar parte', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar parte', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$parte->processo_id}");
    }

    /**
     * [ excluirParte ] - Exclui uma parte do processo
     * 
     * @param int $id ID da parte
     * @return void
     */
    public function excluirParte($id)
    {
        $parte = $this->parteModel->buscarPartePorId($id);
        if (!$parte) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Parte não encontrada', 'alert alert-danger');
            Helper::redirecionar('processos');
            return;
        }

        // Verificar se a parte possui intimações
        $intimacoes = $this->processoModel->buscarIntimacoesParteId($id);
        if ($intimacoes) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Não é possível excluir a parte pois existem intimações vinculadas a ela', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Não é possível excluir a parte pois existem intimações vinculadas a ela', 'error');
            Helper::redirecionar("processos/visualizar/{$parte->processo_id}");
            return;
        }

        // Verificar se a parte possui guias de pagamento
        $guias = $this->guiaPagamentoModel->buscarGuiasPorParteId($id);
        if ($guias) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Não é possível excluir a parte pois existem guias de pagamento vinculadas a ela', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Não é possível excluir a parte pois existem guias de pagamento vinculadas a ela', 'error');
            Helper::redirecionar("processos/visualizar/{$parte->processo_id}");
            return;
        }

        if ($this->parteModel->excluirParte($id)) {
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Exclusão de Parte',
                "Excluiu parte {$parte->nome} do processo ID {$parte->processo_id}"
            );

            // Registra a movimentação do processo
            $this->processoModel->registrarMovimentacao(
                $parte->processo_id,
                'Exclusão de Parte',
                "Parte {$parte->nome} excluída(o) do processo"
            );

            Helper::mensagem('processos', '<i class="fas fa-check"></i> Parte excluída com sucesso!', 'alert alert-success');
            Helper::mensagemSweetAlert('processos', 'Parte excluída com sucesso!', 'success');
        } else {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao excluir parte', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao excluir parte', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$parte->processo_id}");
    }

    /**
     * [ enviarAnexo ] - Envia um anexo para uma intimação
     * 
     * @param int $intimacao_id ID da intimação
     * @return void
     */
    public function enviarAnexo($intimacao_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $intimacao = $this->processoModel->buscarIntimacaoPorId($intimacao_id);
            if (!$intimacao) {
                Helper::mensagem('processos', '<i class="fas fa-ban"></i> Intimação não encontrada', 'alert alert-danger');
                Helper::redirecionar('processos/listar');
                return;
            }

            $parte = $this->parteModel->buscarPartePorId($intimacao->parte_id);
            $tipo = $_POST['tipo_anexo'];
            $mensagem = $_POST['mensagem'];

            if ($tipo === 'text') {
                $resultado = Helper::enviarMensagemComMedia("55" . $parte->telefone, $mensagem, 'text');
            } else {
                $arquivo = $_FILES['arquivo'];
                $resultado = Helper::enviarMensagemComMedia(
                    "55" . $parte->telefone,
                    $mensagem,
                    $tipo,
                    $arquivo
                );
            }

            if ($resultado['status'] === 200) {
                Helper::mensagemSweetAlert('processos', 'Anexo enviado com sucesso!', 'success');
            } else {
                Helper::mensagemSweetAlert('processos', 'Erro ao enviar anexo: ' . json_encode($resultado), 'error');
            }

            Helper::redirecionar("processos/visualizar/{$intimacao->processo_id}");
        }
    }

    // /**
    //  * [ verificarProcessoExistente ] - Verifica se já existe um processo com o mesmo número
    //  * 
    //  * @param string $numero_processo - Número do processo
    //  * @return bool - True se o processo já existe, false caso contrário
    //  */
    // public function verificarProcessoExistente($numero_processo) {
    //     $this->db->query("SELECT COUNT(*) as total FROM cuc_processos WHERE numero_processo = :numero_processo");
    //     $this->db->bind(':numero_processo', $numero_processo);
    //     $resultado = $this->db->resultado();
    //     return $resultado->total > 0;
    // }

    /**
     * [ excluirProcesso ] - Exclui um processo e todos os seus registros relacionados
     * 
     * @param int $id ID do processo
     * @return void
     */
    public function excluirProcesso($id)
    {
        // Verifica se o usuário é administrador
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 'admin') {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Você não tem permissão para excluir processos', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Você não tem permissão para excluir processos', 'error');
            Helper::redirecionar('processos/listar');
            return;
        }

        // Busca o processo
        $processo = $this->processoModel->buscarProcessoPorId($id);
        if (!$processo) {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Processo não encontrado', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Processo não encontrado', 'error');
            Helper::redirecionar('processos/listar');
            return;
        }

        // Verifica se o processo está arquivado
        if ($processo->status === 'arquivado') {
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Não é possível excluir processos arquivados', 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Processos arquivados não podem ser excluídos', 'error');
            Helper::redirecionar('processos/visualizar/' . $id);
            return;
        }

        // Inicia transação
        $this->db->iniciarTransacao();

        try {
            // Exclui guias de pagamento
            $this->guiaPagamentoModel->excluirGuiasPorProcessoId($id);

            // Exclui pendências
            $this->pendenciaModel->excluirPendenciasPorProcessoId($id);

            // Exclui intimações
            $this->processoModel->excluirIntimacoesProcessoId($id);

            // Exclui partes
            $this->parteModel->excluirPartesPorProcessoId($id);

            // Exclui advogados
            $this->processoModel->excluirAdvogadosPorProcessoId($id);

            // Exclui movimentações
            $this->processoModel->excluirMovimentacoesPorProcessoId($id);

            // Exclui o processo
            if ($this->processoModel->excluirProcesso($id)) {
                $this->atividadeModel->registrarAtividade(
                    $_SESSION['usuario_id'],
                    'Exclusão de Processo',
                    "Excluiu processo {$processo->numero_processo}"
                );

                $this->db->confirmarTransacao();
                Helper::mensagem('processos', '<i class="fas fa-check"></i> Processo excluído com sucesso!', 'alert alert-success');
                Helper::mensagemSweetAlert('processos', 'Processo excluído com sucesso!', 'success');
            } else {
                throw new Exception("Erro ao excluir processo");
            }
        } catch (Exception $e) {
            $this->db->cancelarTransacao();
            Helper::mensagem('processos', '<i class="fas fa-ban"></i> Erro ao excluir processo: ' . $e->getMessage(), 'alert alert-danger');
            Helper::mensagemSweetAlert('processos', 'Erro ao excluir processo', 'error');
        }

        Helper::redirecionar('processos/listar');
    }

    
    /**
     * [ marcarGuiaPaga ] - Marca manualmente uma guia como paga
     * 
     * @param int $guia_id ID da guia
     * @return void
     */
    public function marcarGuiaPaga($guia_id)
    {
        // Verifica se o usuário está logado e tem permissão
        if (
            !isset($_SESSION['usuario_id']) ||
            ($_SESSION['usuario_perfil'] !== 'admin' && $_SESSION['usuario_perfil'] !== 'analista')
        ) {
            Helper::mensagemSweetAlert('processos', 'Você não tem permissão para esta ação', 'error');
            Helper::redirecionar('processos/listar');
            return;
        }

        // Busca a guia no banco de dados
        $guia = $this->guiaPagamentoModel->buscarGuiaPorId($guia_id);
        if (!$guia) {
            Helper::mensagemSweetAlert('processos', 'Guia não encontrada', 'error');
            Helper::redirecionar('processos/listar');
            return;
        }

        // Atualiza o status da guia para pago
        $data_pagamento = date('Y-m-d'); // Data atual
        if ($this->guiaPagamentoModel->atualizarStatusGuia($guia_id, 'pago', $data_pagamento)) {
            // Registra a atividade
            $this->atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                'Marcação Manual de Guia',
                "Marcou manualmente a guia {$guia->numero_guia} do processo ID {$guia->processo_id} como PAGA."
            );

            // Registra a movimentação
            $this->processoModel->registrarMovimentacao(
                $guia->processo_id,
                'Pagamento de Guia',
                "Guia {$guia->numero_guia} marcada como PAGA manualmente. Data de pagamento: " . date('d/m/Y')
            );

            Helper::mensagemSweetAlert('processos', 'Guia marcada como PAGA manualmente', 'success');
        } else {
            Helper::mensagemSweetAlert('processos', 'Erro ao atualizar status da guia', 'error');
        }

        Helper::redirecionar("processos/visualizar/{$guia->processo_id}");
    }

  
}
