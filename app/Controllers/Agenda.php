<?php

/**
 * [ AGENDA ] - Controlador responsável por gerenciar a agenda de eventos do sistema.
 * 
 * Este controlador permite:
 * - Visualizar agenda com FullCalendar
 * - Criar, editar e excluir eventos
 * - Gerenciar categorias de eventos com cores
 * - Filtrar eventos por categoria e período
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access protected
 */
class Agenda extends Controllers
{
    private $agendaModel;

    public function __construct()
    {
        parent::__construct();

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login');
        }

        $this->agendaModel = $this->model('AgendaModel');
    }

    /**
     * [ index ] - Método responsável por exibir a página principal da agenda
     * 
     * @return void
     */
    public function index()
    {
        try {
            $categorias = $this->agendaModel->listarCategorias();
            
            $dados = [
                'tituloPagina' => 'Agenda de Eventos',
                'categorias' => $categorias
            ];

            $this->view('agenda/index', $dados);
            
        } catch (Exception $e) {
            error_log('Erro em Agenda->index(): ' . $e->getMessage());
            echo "Erro ao carregar agenda: " . $e->getMessage();
        }
    }

    /**
     * [ eventos ] - Método responsável por retornar eventos em formato JSON para o FullCalendar
     * 
     * @return void
     */
    public function eventos()
    {
        try {
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            
            // Testa sem filtros primeiro
            $eventos = $this->agendaModel->listarEventosCalendar();
            
            header('Content-Type: application/json');
            echo json_encode($eventos);
            exit;
            
        } catch (Exception $e) {
            error_log('Erro ao buscar eventos: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['erro' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * [ novoEvento ] - Método responsável por exibir o formulário de novo evento
     * 
     * @return void
     */
    public function novoEvento()
    {
        $dados = [
            'tituloPagina' => 'Novo Evento',
            'categorias' => $this->agendaModel->listarCategorias(),
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? ''
        ];

        $this->view('agenda/formulario', $dados);
    }

    /**
     * [ editarEvento ] - Método responsável por exibir o formulário de edição de evento
     * 
     * @param int $id
     * @return void
     */
    public function editarEvento($id)
    {
        $evento = $this->agendaModel->buscarEvento($id);
        
        if (!$evento) {
            Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Evento não encontrado', 'alert alert-warning');
            Helper::redirecionar('agenda/index');
        }

        $dados = [
            'tituloPagina' => 'Editar Evento',
            'categorias' => $this->agendaModel->listarCategorias(),
            'evento' => $evento
        ];

        $this->view('agenda/formulario', $dados);
    }

    /**
     * [ salvarEvento ] - Método responsável por salvar um evento (novo ou editado)
     * 
     * @return void
     */
    public function salvarEvento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('agenda/index');
        }

        try {
            // Validação dos dados
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';
            $categoria_id = $_POST['categoria_id'] ?? '';
            $local = trim($_POST['local'] ?? '');
            $observacoes = trim($_POST['observacoes'] ?? '');
            $status = $_POST['status'] ?? 'agendado';
            $evento_dia_inteiro = isset($_POST['evento_dia_inteiro']) ? 'S' : 'N';
            $evento_id = $_POST['evento_id'] ?? null;

            // Validações básicas
            if (empty($titulo)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> O título do evento é obrigatório', 'alert alert-danger');
                if ($evento_id) {
                    Helper::redirecionar('agenda/editarEvento/' . $evento_id);
                } else {
                    Helper::redirecionar('agenda/novoEvento');
                }
            }

            if (empty($data_inicio) || empty($data_fim)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> As datas de início e fim são obrigatórias', 'alert alert-danger');
                if ($evento_id) {
                    Helper::redirecionar('agenda/editarEvento/' . $evento_id);
                } else {
                    Helper::redirecionar('agenda/novoEvento');
                }
            }

            if (empty($categoria_id)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> A categoria é obrigatória', 'alert alert-danger');
                if ($evento_id) {
                    Helper::redirecionar('agenda/editarEvento/' . $evento_id);
                } else {
                    Helper::redirecionar('agenda/novoEvento');
                }
            }

            // Conversão de datas
            $data_inicio = date('Y-m-d H:i:s', strtotime($data_inicio));
            $data_fim = date('Y-m-d H:i:s', strtotime($data_fim));

            // Validação de datas
            if ($data_inicio >= $data_fim) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> A data de fim deve ser posterior à data de início', 'alert alert-danger');
                if ($evento_id) {
                    Helper::redirecionar('agenda/editarEvento/' . $evento_id);
                } else {
                    Helper::redirecionar('agenda/novoEvento');
                }
            }

            $dados = [
                'titulo' => $titulo,
                'descricao' => $descricao,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'categoria_id' => $categoria_id,
                'usuario_id' => $_SESSION['usuario_id'],
                'local' => $local,
                'observacoes' => $observacoes,
                'status' => $status,
                'evento_dia_inteiro' => $evento_dia_inteiro
            ];

            if ($evento_id) {
                // Atualizar evento existente
                $resultado = $this->agendaModel->atualizarEvento($evento_id, $dados);
                if ($resultado) {
                    Helper::mensagem('agenda', '<i class="fas fa-check"></i> Evento atualizado com sucesso!', 'alert alert-success');
                } else {
                    Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao atualizar evento', 'alert alert-danger');
                }
            } else {
                // Criar novo evento
                $resultado = $this->agendaModel->inserirEvento($dados);
                if ($resultado) {
                    Helper::mensagem('agenda', '<i class="fas fa-check"></i> Evento criado com sucesso!', 'alert alert-success');
                } else {
                    Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao criar evento', 'alert alert-danger');
                }
            }

        } catch (Exception $e) {
            error_log('Erro ao salvar evento: ' . $e->getMessage());
            Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro interno do sistema', 'alert alert-danger');
        }

        Helper::redirecionar('agenda/index');
    }

    /**
     * [ excluirEvento ] - Método responsável por excluir um evento
     * 
     * @param int $id
     * @return void
     */
    public function excluirEvento($id)
    {
        if (!$id) {
            Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> ID do evento não informado', 'alert alert-warning');
            Helper::redirecionar('agenda');
        }

        try {
            $resultado = $this->agendaModel->excluirEvento($id);
            
            if ($resultado) {
                Helper::mensagem('agenda', '<i class="fas fa-check"></i> Evento excluído com sucesso!', 'alert alert-success');
            } else {
                Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao excluir evento', 'alert alert-danger');
            }
            
        } catch (Exception $e) {
            error_log('Erro ao excluir evento: ' . $e->getMessage());
            Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro interno do sistema', 'alert alert-danger');
        }

        Helper::redirecionar('agenda/index');
    }

    /**
     * [ detalhesEvento ] - Método responsável por retornar detalhes de um evento em JSON
     * 
     * @param int $id
     * @return void
     */
    public function detalhesEvento($id)
    {
        try {
            // Validar ID
            if (!$id || !is_numeric($id)) {
                header('Content-Type: application/json');
                echo json_encode(['erro' => 'ID do evento inválido']);
                exit;
            }
            
            $evento = $this->agendaModel->buscarEvento($id);
            
            if ($evento) {
                // Converter objeto para array se necessário
                if (is_object($evento)) {
                    $eventoArray = [];
                    foreach ($evento as $key => $value) {
                        $eventoArray[$key] = $value;
                    }
                    $evento = $eventoArray;
                }
                
                // Formatação de datas para exibição
                $evento['data_inicio_formatada'] = date('d/m/Y H:i', strtotime($evento['data_inicio']));
                $evento['data_fim_formatada'] = date('d/m/Y H:i', strtotime($evento['data_fim']));
                
                header('Content-Type: application/json');
                echo json_encode($evento);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['erro' => 'Evento não encontrado']);
            }
            exit;
            
        } catch (Exception $e) {
            error_log('Erro ao buscar detalhes do evento: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Erro interno do sistema: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * [ listarCategorias ] - Método responsável por retornar categorias em JSON
     * 
     * @return void
     */
    public function listarCategorias()
    {
        try {
            $categorias = $this->agendaModel->listarCategorias();
            
            header('Content-Type: application/json');
            echo json_encode($categorias);
            exit;
            
        } catch (Exception $e) {
            error_log('Erro ao listar categorias: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
    }

    /**
     * [ gerenciarCategorias ] - Método responsável por exibir a página de gerenciamento de categorias
     * 
     * @return void
     */
    public function gerenciarCategorias()
    {
        try {
            $categorias = $this->agendaModel->listarTodasCategorias(); // Incluir inativas também
            
            $dados = [
                'tituloPagina' => 'Gerenciar Categorias',
                'categorias' => $categorias
            ];

            $this->view('agenda/categorias', $dados);
            
        } catch (Exception $e) {
            error_log('Erro em gerenciarCategorias: ' . $e->getMessage());
            Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao carregar categorias', 'alert alert-danger');
            Helper::redirecionar('agenda');
        }
    }

    /**
     * [ criarCategoria ] - Método responsável por criar uma nova categoria
     * 
     * @return void
     */
    public function criarCategoria()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('agenda/gerenciarCategorias');
        }

        try {
            $nome = trim($_POST['nome'] ?? '');
            $cor = trim($_POST['cor'] ?? '#007bff');
            $descricao = trim($_POST['descricao'] ?? '');

            // Validações
            if (empty($nome)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Nome da categoria é obrigatório', 'alert alert-danger');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            if (!preg_match('/^#[a-fA-F0-9]{6}$/', $cor)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Cor inválida', 'alert alert-danger');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            // Verificar se já existe categoria com o mesmo nome
            if ($this->agendaModel->verificarCategoriaExistente($nome)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Já existe uma categoria com este nome', 'alert alert-warning');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            $dados = [
                'nome' => $nome,
                'cor' => $cor,
                'descricao' => $descricao,
                'ativo' => 'S'
            ];

            $resultado = $this->agendaModel->inserirCategoria($dados);

            if ($resultado) {
                Helper::mensagem('agenda', '<i class="fas fa-check"></i> Categoria criada com sucesso!', 'alert alert-success');
            } else {
                Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao criar categoria', 'alert alert-danger');
            }

        } catch (Exception $e) {
            error_log('Erro ao criar categoria: ' . $e->getMessage());
            Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro interno do sistema', 'alert alert-danger');
        }

        Helper::redirecionar('agenda/gerenciarCategorias');
    }

    /**
     * [ atualizarCategoria ] - Método responsável por atualizar uma categoria
     * 
     * @param int $id
     * @return void
     */
    public function atualizarCategoria($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('agenda/gerenciarCategorias');
        }

        try {
            if (!$id || !is_numeric($id)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> ID da categoria inválido', 'alert alert-danger');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            $nome = trim($_POST['nome'] ?? '');
            $cor = trim($_POST['cor'] ?? '#007bff');
            $descricao = trim($_POST['descricao'] ?? '');
            $ativo = $_POST['ativo'] ?? 'S';

            // Validações
            if (empty($nome)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Nome da categoria é obrigatório', 'alert alert-danger');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            if (!preg_match('/^#[a-fA-F0-9]{6}$/', $cor)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Cor inválida', 'alert alert-danger');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            // Verificar se a categoria existe
            if (!$this->agendaModel->buscarCategoria($id)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Categoria não encontrada', 'alert alert-warning');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            // Verificar se já existe outra categoria com o mesmo nome
            if ($this->agendaModel->verificarCategoriaExistente($nome, $id)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Já existe outra categoria com este nome', 'alert alert-warning');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            $dados = [
                'nome' => $nome,
                'cor' => $cor,
                'descricao' => $descricao,
                'ativo' => $ativo
            ];

            $resultado = $this->agendaModel->atualizarCategoria($id, $dados);

            if ($resultado) {
                Helper::mensagem('agenda', '<i class="fas fa-check"></i> Categoria atualizada com sucesso!', 'alert alert-success');
            } else {
                Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao atualizar categoria', 'alert alert-danger');
            }

        } catch (Exception $e) {
            error_log('Erro ao atualizar categoria: ' . $e->getMessage());
            Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro interno do sistema', 'alert alert-danger');
        }

        Helper::redirecionar('agenda/gerenciarCategorias');
    }

    /**
     * [ excluirCategoria ] - Método responsável por excluir uma categoria
     * 
     * @param int $id
     * @return void
     */
    public function excluirCategoria($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> ID da categoria inválido', 'alert alert-danger');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            // Verificar se a categoria existe
            if (!$this->agendaModel->buscarCategoria($id)) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Categoria não encontrada', 'alert alert-warning');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            // Verificar se existem eventos usando esta categoria
            $eventosComCategoria = $this->agendaModel->contarEventosPorCategoria($id);
            if ($eventosComCategoria > 0) {
                Helper::mensagem('agenda', '<i class="fas fa-exclamation-triangle"></i> Não é possível excluir a categoria pois existem ' . $eventosComCategoria . ' evento(s) usando ela. Exclua os eventos primeiro.', 'alert alert-warning');
                Helper::redirecionar('agenda/gerenciarCategorias');
            }

            $resultado = $this->agendaModel->excluirCategoria($id);

            if ($resultado) {
                Helper::mensagem('agenda', '<i class="fas fa-check"></i> Categoria excluída com sucesso!', 'alert alert-success');
            } else {
                Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro ao excluir categoria', 'alert alert-danger');
            }

        } catch (Exception $e) {
            error_log('Erro ao excluir categoria: ' . $e->getMessage());
            Helper::mensagem('agenda', '<i class="fas fa-times"></i> Erro interno do sistema', 'alert alert-danger');
        }

        Helper::redirecionar('agenda/gerenciarCategorias');
    }

    /**
     * [ moverEvento ] - Método responsável por mover um evento (drag & drop)
     * 
     * @return void
     */
    public function moverEvento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $evento_id = $input['id'] ?? null;
            $nova_data_inicio = $input['start'] ?? null;
            $nova_data_fim = $input['end'] ?? null;

            if (!$evento_id || !$nova_data_inicio) {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos']);
                exit;
            }

            // Buscar evento atual
            $evento = $this->agendaModel->buscarEvento($evento_id);
            if (!$evento) {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Evento não encontrado']);
                exit;
            }

            // Preparar dados para atualização
            $dados = [
                'titulo' => $evento['titulo'],
                'descricao' => $evento['descricao'],
                'data_inicio' => date('Y-m-d H:i:s', strtotime($nova_data_inicio)),
                'data_fim' => $nova_data_fim ? date('Y-m-d H:i:s', strtotime($nova_data_fim)) : date('Y-m-d H:i:s', strtotime($nova_data_inicio . ' +1 hour')),
                'categoria_id' => $evento['categoria_id'],
                'local' => $evento['local'],
                'observacoes' => $evento['observacoes'],
                'status' => $evento['status'],
                'evento_dia_inteiro' => $evento['evento_dia_inteiro']
            ];

            $resultado = $this->agendaModel->atualizarEvento($evento_id, $dados);

            header('Content-Type: application/json');
            echo json_encode(['sucesso' => $resultado]);
            exit;
            
        } catch (Exception $e) {
            error_log('Erro ao mover evento: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno do sistema']);
            exit;
        }
    }
    
    /**
     * [ teste ] - Método para testar a API
     * 
     * @return void
     */
    public function teste()
    {
        try {
            echo "<h1>Teste da Agenda</h1>";
            
            // Teste 1: Conexão com banco
            echo "<h3>1. Testando conexão com banco...</h3>";
            $categorias = $this->agendaModel->listarCategorias();
            echo "Categorias encontradas: " . count($categorias) . "<br>";
            
            if (!empty($categorias)) {
                echo "<h4>Detalhes das categorias:</h4>";
                foreach ($categorias as $categoria) {
                    $id = isset($categoria->id) ? $categoria->id : $categoria['id'];
                    $nome = isset($categoria->nome) ? $categoria->nome : $categoria['nome'];
                    $cor = isset($categoria->cor) ? $categoria->cor : $categoria['cor'];
                    echo "- ID: $id, Nome: $nome, Cor: $cor<br>";
                }
            } else {
                echo "❌ Nenhuma categoria encontrada!<br>";
                
                // Teste direto no banco
                echo "<h4>Testando conexão direta...</h4>";
                try {
                    $pdo = new PDO('mysql:host=localhost;dbname=dir_judiciaria', 'root', '');
                    $stmt = $pdo->query("SELECT * FROM agenda_categorias WHERE ativo = 'S' ORDER BY nome");
                    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "Categorias via PDO direto: " . count($cats) . "<br>";
                    foreach ($cats as $cat) {
                        echo "- PDO: ID: {$cat['id']}, Nome: {$cat['nome']}, Cor: {$cat['cor']}<br>";
                    }
                } catch (Exception $e) {
                    echo "❌ Erro PDO: " . $e->getMessage() . "<br>";
                }
            }
            
            // Teste 2: Listar eventos
            echo "<h3>2. Testando listagem de eventos...</h3>";
            $eventos = $this->agendaModel->listarEventosCalendar();
            echo "Eventos encontrados: " . count($eventos) . "<br>";
            
            // Teste 3: JSON dos eventos
            echo "<h3>3. JSON dos eventos:</h3>";
            echo "<pre>" . json_encode($eventos, JSON_PRETTY_PRINT) . "</pre>";
            
            // Teste 4: Configurações do sistema
            echo "<h3>4. Configurações:</h3>";
            echo "URL: " . URL . "<br>";
            echo "Usuário ID: " . ($_SESSION['usuario_id'] ?? 'Não definido') . "<br>";
            
            // Teste 5: Verificar se tabelas existem
            echo "<h3>5. Verificando tabelas no banco:</h3>";
            try {
                require_once APPROOT . '/Libraries/Database.php';
                $db = new Database();
                
                $db->query("SHOW TABLES LIKE 'agenda_%'");
                $tabelas = $db->resultados();
                echo "Tabelas agenda_* encontradas: " . count($tabelas) . "<br>";
                foreach ($tabelas as $tabela) {
                    $nomeTabela = current((array)$tabela);
                    echo "- $nomeTabela<br>";
                }
                
            } catch (Exception $e) {
                echo "❌ Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
            }
            
        } catch (Exception $e) {
            echo "Erro no teste: " . $e->getMessage();
        }
    }
} 