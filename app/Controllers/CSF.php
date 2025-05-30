<?php

/**
 * [ CSF ] - Controlador responsável por gerenciar a Comissão de Soluções Fundiárias.
 * 
 * Este controlador permite:
 * - Listar, cadastrar, editar e excluir visitas técnicas
 * - Gerenciar participantes das visitas
 * - Gerar relatórios de visitas
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected    
 */ 
class CSF extends Controllers
{
    private $visitasPorPagina = ITENS_POR_PAGINA;
    private $csfModel;

    public function __construct()
    {
        parent::__construct();  // Chamando o construtor pai primeiro
        
        // Carrega o model de CSF
        $this->csfModel = $this->model('CSFModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('./');
        }

        // Verifica se tem permissão para acessar o módulo
        // Ajuste conforme as regras de permissão do seu sistema
        if (
            !isset($_SESSION['usuario_perfil']) ||
            !in_array($_SESSION['usuario_perfil'], ['admin', 'analista', 'usuario'])
        ) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e analistas podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

    }

    /**
     * [ index ] - Exibe a página inicial do módulo CSF
     * 
     * @return void
     */
    public function index()
    {
        // Redirecionar para a lista de visitas
        Helper::redirecionar('csf/listar');
    }

    /**
     * [ listar ] - Lista todas as visitas técnicas
     * 
     * @param int $pagina Número da página atual
     * @return void
     */
    public function listar($pagina = 1)
    {
        // Obter o número de itens por página da URL, se fornecido
        $itensPorPagina = isset($_GET['itens_por_pagina']) ? (int)$_GET['itens_por_pagina'] : $this->visitasPorPagina;
        
        // Obter filtros da URL
        $filtros = [
            'processo' => isset($_GET['processo']) ? trim($_GET['processo']) : '',
            'comarca' => isset($_GET['comarca']) ? trim($_GET['comarca']) : '',
            'autor' => isset($_GET['autor']) ? trim($_GET['autor']) : '',
            'reu' => isset($_GET['reu']) ? trim($_GET['reu']) : '',
            'proad' => isset($_GET['proad']) ? trim($_GET['proad']) : ''
        ];
        
        // Obter visitas com paginação e filtros
        $resultadoPaginado = $this->csfModel->obterVisitasPaginadas($pagina, $itensPorPagina, $filtros);
        
        // Obter participantes para cada visita
        $visitas = $resultadoPaginado['visitas'];
        foreach ($visitas as &$visita) {
            $visita->participantes = $this->csfModel->obterParticipantesPorVisitaId($visita->id);
        }
        
        $dados = [
            'titulo' => 'Visitas Técnicas',
            'visitas' => $visitas,
            'paginacao' => $resultadoPaginado['paginacao']
        ];
        
        $this->view('csf/listar', $dados);
    }

    /**
     * [ cadastrar ] - Processa o cadastro de uma nova visita técnica
     * 
     * @return void
     */
    public function cadastrar()
    {
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'processo' => trim($formulario['processo']),
                'comarca' => trim($formulario['comarca']),
                'autor' => trim($formulario['autor']),
                'reu' => trim($formulario['reu']),
                'proad' => trim($formulario['proad']),
                'nome_ocupacao' => trim($formulario['nome_ocupacao']),
                'area_ocupada' => trim($formulario['area_ocupada']),
                'energia_eletrica' => trim($formulario['energia_eletrica']),
                'agua_tratada' => trim($formulario['agua_tratada']),
                'area_risco' => trim($formulario['area_risco']),
                'moradia' => trim($formulario['moradia']),
                'processo_erro' => '',
                'comarca_erro' => '',
                'autor_erro' => '',
                'reu_erro' => ''
            ];

            // Validação dos campos obrigatórios
            if (empty($formulario['processo'])) :
                $dados['processo_erro'] = 'Preencha o campo processo';
            endif;

            if (empty($formulario['comarca'])) :
                $dados['comarca_erro'] = 'Preencha o campo comarca';
            endif;

            if (empty($formulario['autor'])) :
                $dados['autor_erro'] = 'Preencha o campo autor';
            endif;

            if (empty($formulario['reu'])) :
                $dados['reu_erro'] = 'Preencha o campo réu';
            endif;

            // Se não houver erros, cadastra a visita
            if (empty($dados['processo_erro']) && empty($dados['comarca_erro']) && 
                empty($dados['autor_erro']) && empty($dados['reu_erro'])) :
                
                if ($this->csfModel->cadastrarVisita($dados)) :
                    Helper::mensagem('csf', '<i class="fas fa-check"></i> Visita cadastrada com sucesso');
                    Helper::mensagemSweetAlert('csf', 'Visita cadastrada com sucesso', 'success');
                    Helper::redirecionar('csf/listar');
                else :
                    die("Erro ao cadastrar visita no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'processo' => '',
                'comarca' => '',
                'autor' => '',
                'reu' => '',
                'proad' => '',
                'nome_ocupacao' => '',
                'area_ocupada' => '',
                'energia_eletrica' => 'Não',
                'agua_tratada' => 'Não',
                'area_risco' => 'Não',
                'moradia' => '',
                'processo_erro' => '',
                'comarca_erro' => '',
                'autor_erro' => '',
                'reu_erro' => ''
            ];
        endif;

        $this->view('csf/cadastrar', $dados);
    }

    /**
     * [ editar ] - Processa a edição de uma visita técnica
     * 
     * @param int $id ID da visita
     * @return void
     */
    public function editar($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID da visita não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Obter dados da visita
        $visita = $this->csfModel->obterVisitaPorId($id);
        
        // Verificar se a visita existe
        if (!$visita) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Visita não encontrada', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'id' => $id,
                'processo' => trim($formulario['processo']),
                'comarca' => trim($formulario['comarca']),
                'autor' => trim($formulario['autor']),
                'reu' => trim($formulario['reu']),
                'proad' => trim($formulario['proad']),
                'nome_ocupacao' => trim($formulario['nome_ocupacao']),
                'area_ocupada' => trim($formulario['area_ocupada']),
                'energia_eletrica' => trim($formulario['energia_eletrica']),
                'agua_tratada' => trim($formulario['agua_tratada']),
                'area_risco' => trim($formulario['area_risco']),
                'moradia' => trim($formulario['moradia']),
                'processo_erro' => '',
                'comarca_erro' => '',
                'autor_erro' => '',
                'reu_erro' => ''
            ];

            // Validação dos campos obrigatórios
            if (empty($formulario['processo'])) :
                $dados['processo_erro'] = 'Preencha o campo processo';
            endif;

            if (empty($formulario['comarca'])) :
                $dados['comarca_erro'] = 'Preencha o campo comarca';
            endif;

            if (empty($formulario['autor'])) :
                $dados['autor_erro'] = 'Preencha o campo autor';
            endif;

            if (empty($formulario['reu'])) :
                $dados['reu_erro'] = 'Preencha o campo réu';
            endif;

            // Se não houver erros, atualiza a visita
            if (empty($dados['processo_erro']) && empty($dados['comarca_erro']) && 
                empty($dados['autor_erro']) && empty($dados['reu_erro'])) :
                
                if ($this->csfModel->atualizarVisita($dados)) :
                    Helper::mensagem('csf', '<i class="fas fa-check"></i> Visita atualizada com sucesso');
                    Helper::mensagemSweetAlert('csf', 'Visita atualizada com sucesso', 'success');
                    Helper::redirecionar('csf/listar');
                else :
                    die("Erro ao atualizar visita no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'id' => $id,
                'processo' => $visita->processo,
                'comarca' => $visita->comarca,
                'autor' => $visita->autor,
                'reu' => $visita->reu,
                'proad' => $visita->proad,
                'nome_ocupacao' => $visita->nome_ocupacao,
                'area_ocupada' => $visita->area_ocupada,
                'energia_eletrica' => $visita->energia_eletrica,
                'agua_tratada' => $visita->agua_tratada,
                'area_risco' => $visita->area_risco,
                'moradia' => $visita->moradia,
                'processo_erro' => '',
                'comarca_erro' => '',
                'autor_erro' => '',
                'reu_erro' => ''
            ];
        endif;

        $this->view('csf/editar', $dados);
    }

    /**
     * [ visualizar ] - Exibe os detalhes de uma visita técnica
     * 
     * @param int $id ID da visita
     * @return void
     */
    public function visualizar($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID da visita não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Obter dados da visita
        $visita = $this->csfModel->obterVisitaPorId($id);
        
        // Verificar se a visita existe
        if (!$visita) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Visita não encontrada', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Obter participantes da visita
        $participantes = $this->csfModel->obterParticipantesPorVisitaId($id);
        
        $dados = [
            'titulo' => 'Detalhes da Visita Técnica',
            'visita' => $visita,
            'participantes' => $participantes
        ];
        
        $this->view('csf/visualizar', $dados);
    }

    /**
     * [ excluir ] - Exclui uma visita técnica
     * 
     * @param int $id ID da visita
     * @return void
     */
    public function excluir($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID da visita não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Excluir a visita
        if ($this->csfModel->excluirVisita($id)) {
            Helper::mensagem('csf', '<i class="fas fa-check"></i> Visita excluída com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('csf', 'Visita excluída com sucesso', 'success');
        } else {
            Helper::mensagem('csf', '<i class="fas fa-times"></i> Erro ao excluir visita', 'alert alert-danger');
            Helper::mensagemSweetAlert('csf', 'Erro ao excluir visita', 'error');
        }
        
        Helper::redirecionar('csf/listar');
    }

    /**
     * [ excluirLote ] - Exclui múltiplas visitas técnicas
     * 
     * @return void
     */
    public function excluirLote()
    {
        // Verificar se o formulário foi submetido
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_users'])) {
            $ids = $_POST['selected_users'];
            
            // Excluir as visitas selecionadas
            $sucesso = true;
            foreach ($ids as $id) {
                if (!$this->csfModel->excluirVisita($id)) {
                    $sucesso = false;
                }
            }
            
            if ($sucesso) {
                Helper::mensagem('csf', '<i class="fas fa-check"></i> Visitas excluídas com sucesso', 'alert alert-success');
                Helper::mensagemSweetAlert('csf', 'Visitas excluídas com sucesso', 'success');
            } else {
                Helper::mensagem('csf', '<i class="fas fa-times"></i> Erro ao excluir algumas visitas', 'alert alert-danger');
                Helper::mensagemSweetAlert('csf', 'Erro ao excluir algumas visitas', 'error');
            }
        } else {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Nenhuma visita selecionada', 'alert alert-warning');
            Helper::mensagemSweetAlert('csf', 'Nenhuma visita selecionada', 'warning');
        }
        
        Helper::redirecionar('csf/listar');
    }

    /**
     * [ cadastrarParticipante ] - Cadastra um novo participante para uma visita
     * 
     * @param int $visitaId ID da visita
     * @return void
     */
    public function cadastrarParticipante($visitaId = null)
    {
        // Verificar se o ID da visita foi fornecido
        if (!$visitaId) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID da visita não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Obter dados da visita
        $visita = $this->csfModel->obterVisitaPorId($visitaId);
        
        // Verificar se a visita existe
        if (!$visita) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Visita não encontrada', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (isset($formulario)) :
            $dados = [
                'nome' => trim($formulario['nome']),
                'cpf' => trim($formulario['cpf']),
                'contato' => trim($formulario['contato']),
                'idade' => (int)$formulario['idade'],
                'qtd_pessoas' => (int)$formulario['qtd_pessoas'],
                'menores' => trim($formulario['menores']),
                'idosos' => trim($formulario['idosos']),
                'pessoa_deficiencia' => trim($formulario['pessoa_deficiencia']),
                'gestante' => trim($formulario['gestante']),
                'auxilio' => trim($formulario['auxilio']),
                'frequentam_escola' => trim($formulario['frequentam_escola']),
                'qtd_trabalham' => trim($formulario['qtd_trabalham']),
                'vulneravel' => trim($formulario['vulneravel']),
                'lote_vago' => trim($formulario['lote_vago']),
                'fonte_renda' => trim($formulario['fonte_renda']),
                'mora_local' => trim($formulario['mora_local']),
                'descricao' => trim($formulario['descricao']),
                'visita_id' => $visitaId,
                'nome_erro' => '',
                'cpf_erro' => ''
            ];

            // Validação dos campos obrigatórios
            if (empty($formulario['nome'])) :
                $dados['nome_erro'] = 'Preencha o nome do participante';
            endif;

            if (empty($formulario['cpf'])) :
                $dados['cpf_erro'] = 'Preencha o CPF do participante';
            endif;

            // Se não houver erros, cadastra o participante
            if (empty($dados['nome_erro']) && empty($dados['cpf_erro'])) :
                if ($this->csfModel->cadastrarParticipante($dados)) :
                    Helper::mensagem('csf', '<i class="fas fa-check"></i> Participante cadastrado com sucesso');
                    Helper::mensagemSweetAlert('csf', 'Participante cadastrado com sucesso', 'success');
                    Helper::redirecionar('csf/visualizar/' . $visitaId);
                else :
                    die("Erro ao cadastrar participante no banco de dados");
                endif;
            endif;
        else :
            $dados = [
                'nome' => '',
                'cpf' => '',
                'contato' => '',
                'idade' => '',
                'qtd_pessoas' => '',
                'menores' => '',
                'idosos' => '',
                'pessoa_deficiencia' => '',
                'gestante' => '',
                'auxilio' => '',
                'frequentam_escola' => '',
                'qtd_trabalham' => '',
                'vulneravel' => 'Não',
                'lote_vago' => 'Não',
                'fonte_renda' => '',
                'mora_local' => 'Sim',
                'descricao' => '',
                'visita_id' => $visitaId,
                'nome_erro' => '',
                'cpf_erro' => '',
                'visita' => $visita
            ];
        endif;

        $this->view('csf/cadastrar_participante', $dados);
    }

    /**
     * [ excluirParticipante ] - Exclui um participante
     * 
     * @param int $id ID do participante
     * @param int $visitaId ID da visita
     * @return void
     */
    public function excluirParticipante($id = null, $visitaId = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id || !$visitaId) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID do participante ou da visita não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Verificar se o participante existe
        $participante = $this->csfModel->obterParticipantePorId($id);
        if (!$participante) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Participante não encontrado', 'alert alert-danger');
            Helper::redirecionar('csf/visualizar/' . $visitaId);
            return;
        }
        
        // Excluir o participante
        if ($this->csfModel->excluirParticipante($id)) {
            Helper::mensagem('csf', '<i class="fas fa-check"></i> Participante excluído com sucesso', 'alert alert-success');
            Helper::mensagemSweetAlert('csf', 'Participante excluído com sucesso', 'success');
        } else {
            Helper::mensagem('csf', '<i class="fas fa-times"></i> Erro ao excluir participante', 'alert alert-danger');
            Helper::mensagemSweetAlert('csf', 'Erro ao excluir participante', 'error');
        }
        
        Helper::redirecionar('csf/visualizar/' . $visitaId);
    }

    /**
     * [ gerarPDF ] - Gera um relatório em formato PDF de uma visita técnica
     * 
     * @param int $id ID da visita
     * @return void
     */
    public function gerarPDF($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID da visita não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Obter dados da visita
        $visita = $this->csfModel->obterVisitaPorId($id);
        if (!$visita) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Visita não encontrada', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Obter participantes da visita
        $participantes = $this->csfModel->obterParticipantesPorVisitaId($id);
        
        // Calcular estatísticas
        $total_participantes = count($participantes);
        $total_pessoas = 0;
        $total_vulneravel = 0;
        $total_lote_vago = 0;
        $total_auxilio = 0;
        $total_mora_local = 0;
        
        foreach ($participantes as $participante) {
            $total_pessoas += $participante->qtd_pessoas;
            
            if (strtolower($participante->vulneravel) === 'sim') {
                $total_vulneravel++;
            }
            
            if (strtolower($participante->lote_vago) === 'sim') {
                $total_lote_vago++;
            }
            
            if (strtolower($participante->auxilio) === 'sim') {
                $total_auxilio++;
            }
            
            if (strtolower($participante->mora_local) === 'sim') {
                $total_mora_local++;
            }
        }
        
        // Preparar dados para a view
        $dados = [
            'visita' => $visita,
            'participantes' => $participantes,
            'estatisticas' => [
                'total_participantes' => $total_participantes,
                'total_pessoas' => $total_pessoas,
                'total_vulneravel' => $total_vulneravel,
                'total_lote_vago' => $total_lote_vago,
                'total_auxilio' => $total_auxilio,
                'total_mora_local' => $total_mora_local
            ],
            'data_geracao' => date('d/m/Y H:i:s'),
            'logo_url' => URL . '/public/img/logo.png',
            'logo_150_anos' => URL . '/public/img/150-anos_branco.png'
        ];
        
        try {
            // Limpar qualquer saída anterior
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Iniciar buffer de saída
            ob_start();
            
            // Carregar a view do PDF
            $this->view('csf/pdf', $dados);
            
            // Capturar o HTML gerado
            $html = ob_get_clean();
            
            // Configurar o DOMPDF
            $options = new \Dompdf\Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsPhpEnabled(true);
            $options->setIsRemoteEnabled(true);
            
            // Configurar margens e orientação
            $options->setDefaultPaperSize('A4');
            $options->set('defaultPaperOrientation', 'portrait');
            $options->set('marginTop', 5);
            $options->set('marginRight', 10);
            $options->set('marginBottom', 10);
            $options->set('marginLeft', 10);
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            
            // Renderizar o PDF
            $dompdf->render();
            
            // Limpar novamente qualquer saída
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Configurar cabeçalhos para exibir o PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $visita->proad . '.pdf"');
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            
            // Saída do PDF
            echo $dompdf->output();
            exit();
            
        } catch (Exception $e) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Erro ao gerar PDF: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('csf/visualizar/' . $id);
        }
    }

    /**
     * [ editarParticipante ] - Exibe o formulário para editar um participante
     * 
     * @param int $id ID do participante
     * @return void
     */
    public function editarParticipante($id = null)
    {
        // Verificar se o ID foi fornecido
        if (!$id) {
            Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> ID do participante não fornecido', 'alert alert-danger');
            Helper::redirecionar('csf/listar');
            return;
        }
        
        // Verificar se o formulário foi submetido
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Processar o formulário
            
            // Sanitizar os dados do POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Preparar os dados para atualização
            $dados = [
                'id' => $id,
                'visita_id' => $_POST['visita_id'],
                'nome' => trim($_POST['nome']),
                'cpf' => trim($_POST['cpf']),
                'contato' => trim($_POST['contato']),
                'idade' => trim($_POST['idade']),
                'qtd_pessoas' => trim($_POST['qtd_pessoas']),
                'menores' => trim($_POST['menores']),
                'idosos' => trim($_POST['idosos']),
                'pessoa_deficiencia' => trim($_POST['pessoa_deficiencia']),
                'gestante' => trim($_POST['gestante']),
                'auxilio' => trim($_POST['auxilio']),
                'frequentam_escola' => trim($_POST['frequentam_escola']),
                'qtd_trabalham' => trim($_POST['qtd_trabalham']),
                'vulneravel' => trim($_POST['vulneravel']),
                'lote_vago' => trim($_POST['lote_vago']),
                'mora_local' => trim($_POST['mora_local']),
                'fonte_renda' => trim($_POST['fonte_renda']),
                'descricao' => trim($_POST['descricao'])
            ];
            
            // Validar os campos obrigatórios
            if (empty($dados['nome'])) {
                Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> O nome do participante é obrigatório', 'alert alert-danger');
                $this->view('csf/editar_participante', $dados);
                return;
            }
            
            // Atualizar o participante
            if ($this->csfModel->atualizarParticipante($dados)) {
                Helper::mensagem('csf', '<i class="fas fa-check-circle"></i> Participante atualizado com sucesso', 'alert alert-success');
                Helper::redirecionar('csf/visualizar/' . $dados['visita_id']);
            } else {
                Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Erro ao atualizar participante', 'alert alert-danger');
                $this->view('csf/editar_participante', $dados);
            }
        } else {
            // Obter dados do participante
            $participante = $this->csfModel->obterParticipantePorId($id);
            
            if (!$participante) {
                Helper::mensagem('csf', '<i class="fas fa-exclamation-triangle"></i> Participante não encontrado', 'alert alert-danger');
                Helper::redirecionar('csf/listar');
                return;
            }
            
            // Preparar dados para a view
            $dados = [
                'id' => $participante->id,
                'visita_id' => $participante->visita_id,
                'nome' => $participante->nome,
                'cpf' => $participante->cpf,
                'contato' => $participante->contato,
                'idade' => $participante->idade,
                'qtd_pessoas' => $participante->qtd_pessoas,
                'menores' => $participante->menores,
                'idosos' => $participante->idosos,
                'pessoa_deficiencia' => $participante->pessoa_deficiencia,
                'gestante' => $participante->gestante,
                'auxilio' => $participante->auxilio,
                'frequentam_escola' => $participante->frequentam_escola,
                'qtd_trabalham' => $participante->qtd_trabalham,
                'vulneravel' => $participante->vulneravel,
                'lote_vago' => $participante->lote_vago,
                'mora_local' => $participante->mora_local,
                'fonte_renda' => $participante->fonte_renda,
                'descricao' => $participante->descricao
            ];
            
            // Carregar a view
            $this->view('csf/editar_participante', $dados);
        }
    }
}