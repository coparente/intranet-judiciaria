<?php

/**
 * [ UPLOAD TXT ] - Controlador responsável pelo upload e processamento de arquivos TXT.
 * 
 * Este controlador permite:
 * - Upload de arquivos TXT para diferentes sistemas
 * - Processamento e importação dos dados para o banco
 * - Visualização do progresso de importação
 * 
 * @author Cleyton Parente <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access public
 */
class UploadTxt extends Controllers
{
    private $usuarioModel;
    private $uploadTxtModel;

    public function __construct()
    {
        parent::__construct();
        // Verifica permissões
        if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem acessar essa página', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
        }

        $this->usuarioModel = $this->model('UsuarioModel');
        $this->uploadTxtModel = $this->model('UploadTxtModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('usuarios/login');
        }
    }

    /**
     * [ index ] - Exibe a página principal de upload de arquivos TXT
     * 
     * @return void
     */
    public function index()
    {
        // Verifica permissão para o módulo
        // Middleware::verificarPermissao(6); // ID do módulo 'Upload TXT'

        $dados = [
            'tituloPagina' => 'Upload de Arquivos TXT',
            'arquivos' => [
                ["NAC", "uploadtxt/processarNac", "arquivo_txt", "Importação de dados do NAC"],
                ["CUC", "uploadtxt/processarCuc", "arquivo_txt", "Importação de dados do CUC"],
                ["CÂMARAS", "uploadtxt/processarCamara", "arquivo_txt", "Importação de dados das Câmaras"]
            ]
        ];

        $this->view('uploadtxt/index', $dados);
    }

    /**
     * [ processarNac ] - Processa arquivo TXT do NAC
     * 
     * @return void
     */
    public function processarNac()
    {
        $this->processarArquivo('dados_nac', 'arquivo_txt', 'NAC');
    }

    /**
     * [ processarCuc ] - Processa arquivo TXT do CUC
     * 
     * @return void
     */
    public function processarCuc()
    {
        $this->processarArquivo('dados_cuc', 'arquivo_txt', 'CUC');
    }

    /**
     * [ processarCamara ] - Processa arquivo TXT da Câmara
     * 
     * @return void
     */
    public function processarCamara()
    {
        $this->processarArquivo('dados_camara', 'arquivo_txt', 'CÂMARA');
    }

    /**
     * [ processarArquivo ] - Método genérico para processar arquivos TXT
     * 
     * @param string $tabela Nome da tabela para inserir os dados
     * @param string $nomeArquivo Nome do campo do arquivo no formulário
     * @param string $tipoArquivo Tipo de arquivo (NAC, CUC, CÂMARAS)
     * @return void
     */
    private function processarArquivo($tabela, $nomeArquivo, $tipoArquivo)
    {
        // Verifica permissão para o módulo
        // Middleware::verificarPermissao(6);

        // Garantir que o cabeçalho seja definido apenas uma vez
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        set_time_limit(0); // Definir o tempo máximo de execução como ilimitado
        ob_clean(); // Limpar qualquer saída anterior

        try {
            // Verificar se a tabela existe, se não, criar
            if (!$this->uploadTxtModel->verificarTabelaExiste($tabela)) {
                if (!$this->uploadTxtModel->criarTabela($tabela)) {
                    throw new Exception("Não foi possível criar a tabela $tabela");
                }
            }

            // Verificar se o arquivo foi enviado
            if (!isset($_FILES[$nomeArquivo])) {
                throw new Exception('Nenhum arquivo enviado');
            }

            $file = $_FILES[$nomeArquivo];

            // Log para debug
            error_log("Processando arquivo: " . $file['name'] . ", tamanho: " . $file['size'] . " bytes");

            // Validações
            $this->validarArquivo($file);

            // Processar arquivo
            $lines = file($file['tmp_name']);
            $totalLines = count($lines);
            if ($totalLines === 0) {
                throw new Exception('Arquivo vazio');
            }

            error_log("Total de linhas no arquivo: " . $totalLines);

            // Converter codificação
            foreach ($lines as $key => $line) {
                $lines[$key] = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
            }

            // Processar linhas
            $insertCount = 0;
            $errorCount = 0;

            foreach ($lines as $index => $line) {
                // Pular cabeçalho
                if ($index === 0) continue;

                $line = trim($line);
                if (empty($line)) continue;

                // Dividir a linha pelos separadores
                $data = explode('#', $line);

                // Verificar se tem todos os campos necessários
                if (count($data) !== 5) {
                    error_log("Linha inválida (campos insuficientes): " . $line);
                    $errorCount++;
                    continue; // Linha inválida
                }

                // Formatar a data (se necessário)
                $dataFormatada = $data[3];
                if (strpos($dataFormatada, '.') !== false) {
                    // Remover a parte de milissegundos se existir
                    $dataFormatada = explode('.', $dataFormatada)[0];
                }

                try {
                    // Inserir dados usando o model
                    if ($this->uploadTxtModel->inserirDados(
                        $tabela,
                        $data[0],  // numero
                        $data[1],  // comarca
                        $data[2],  // movimentacao
                        $dataFormatada,  // data
                        $data[4]   // nome
                    )) {
                        $insertCount++;
                    } else {
                        error_log("Erro ao inserir linha: " . $line);
                        $errorCount++;
                    }
                } catch (Exception $e) {
                    error_log("Exceção ao inserir linha: " . $e->getMessage());
                    $errorCount++;
                }
            }

            error_log("Processamento concluído. Inseridos: $insertCount, Erros: $errorCount");

            echo json_encode([
                'status' => 'success',
                'message' => "Arquivo $tipoArquivo processado com sucesso! Linhas importadas: $insertCount" .
                    ($errorCount > 0 ? ", Erros: $errorCount" : "")
            ]);
        } catch (Exception $e) {
            error_log("Erro no processamento do arquivo: " . $e->getMessage());
            echo json_encode([
                'status' => 'danger',
                'message' => 'Erro: ' . $e->getMessage()
            ]);
        }

        // Garantir que não haja saída adicional após o JSON
        exit;
    }

    /**
     * [ validarArquivo ] - Valida o arquivo enviado
     * 
     * @param array $file Informações do arquivo
     * @return void
     * @throws Exception
     */
    private function validarArquivo($file)
    {
        // Verificar erros no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo PHP',
                UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido pelo formulário',
                UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi feito parcialmente',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo no disco',
                UPLOAD_ERR_EXTENSION => 'Uma extensão PHP interrompeu o upload'
            ];

            $errorMessage = isset($errorMessages[$file['error']])
                ? $errorMessages[$file['error']]
                : 'Erro desconhecido no upload';

            throw new Exception($errorMessage);
        }

        // Verificar se é um arquivo TXT
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');

        if ($extension !== 'txt') {
            throw new Exception('Apenas arquivos TXT são permitidos');
        }

        // Verificar tamanho do arquivo (opcional)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            throw new Exception('O arquivo excede o tamanho máximo permitido (10MB)');
        }
    }

    /**
     * [ debug ] - Exibe informações de debug para ajudar a identificar problemas
     * 
     * @return void
     */
    public function debug()
    {
        // Verifica se o usuário é administrador
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 'admin') {
            Helper::redirecionar('dashboard/inicial');
        }

        echo '<h1>Debug do Módulo de Upload TXT</h1>';

        // Verificar configurações do PHP
        echo '<h2>Configurações do PHP</h2>';
        echo '<ul>';
        echo '<li>post_max_size: ' . ini_get('post_max_size') . '</li>';
        echo '<li>upload_max_filesize: ' . ini_get('upload_max_filesize') . '</li>';
        echo '<li>max_execution_time: ' . ini_get('max_execution_time') . '</li>';
        echo '<li>memory_limit: ' . ini_get('memory_limit') . '</li>';
        echo '</ul>';

        // Verificar permissões de diretório
        echo '<h2>Permissões de Diretório</h2>';
        $tempDir = sys_get_temp_dir();
        echo '<ul>';
        echo '<li>Diretório temporário: ' . $tempDir . '</li>';
        echo '<li>Permissões: ' . substr(sprintf('%o', fileperms($tempDir)), -4) . '</li>';
        echo '<li>Gravável: ' . (is_writable($tempDir) ? 'Sim' : 'Não') . '</li>';
        echo '</ul>';

        // Verificar conexão com o banco de dados
        echo '<h2>Conexão com o Banco de Dados</h2>';
        try {
            $db = new Database();
            echo '<p style="color:green">Conexão com o banco de dados estabelecida com sucesso!</p>';

            // Verificar tabelas
            $tables = ['dados_nac', 'dados_cuc', 'dados_camara'];
            echo '<h3>Verificação de Tabelas</h3>';
            echo '<ul>';
            foreach ($tables as $table) {
                // Verificar se a tabela existe diretamente com uma consulta simples
                $db->query("SHOW TABLES LIKE '$table'");
                $result = $db->resultados();
                $exists = !empty($result);

                echo '<li>' . $table . ': ' . ($exists ? '<span style="color:green">Existe</span>' : '<span style="color:red">Não existe</span>') . '</li>';

                if ($exists) {
                    // Verificar estrutura da tabela
                    $db->query("DESCRIBE $table");
                    $columns = $db->resultados();
                    echo '<ul>';
                    foreach ($columns as $column) {
                        echo '<li>' . $column->Field . ' (' . $column->Type . ')</li>';
                    }
                    echo '</ul>';

                    // Verificar quantidade de registros
                    $db->query("SELECT COUNT(*) as total FROM $table");
                    $count = $db->resultado();
                    echo '<li>Total de registros: ' . ($count->total ?? 0) . '</li>';
                } else {
                    // Tentar criar a tabela
                    echo '<li><a href="javascript:void(0)" onclick="criarTabela(\'' . $table . '\')">Criar tabela</a></li>';
                }
            }
            echo '</ul>';

            // Adicionar script para criar tabelas via AJAX
            echo '<script>
            function criarTabela(tabela) {
                fetch("' . URL . '/uploadtxt/criarTabela/" + tabela)
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    alert("Erro ao criar tabela: " + error);
                });
            }
            </script>';
        } catch (Exception $e) {
            echo '<p style="color:red">Erro na conexão com o banco de dados: ' . $e->getMessage() . '</p>';
        }

        // Verificar logs de erro recentes
        echo '<h2>Logs de Erro Recentes</h2>';
        $logFile = ini_get('error_log');
        if (file_exists($logFile) && is_readable($logFile)) {
            $logs = file($logFile);
            $recentLogs = array_slice($logs, -20); // Últimas 20 linhas
            echo '<pre style="background-color: #f5f5f5; padding: 10px; max-height: 300px; overflow: auto;">';
            foreach ($recentLogs as $log) {
                echo htmlspecialchars($log);
            }
            echo '</pre>';
        } else {
            echo '<p>Não foi possível ler o arquivo de log: ' . $logFile . '</p>';
        }

        exit;
    }

    /**
     * [ criarTabela ] - Cria uma tabela específica
     * 
     * @param string $tabela Nome da tabela a ser criada
     * @return void
     */
    public function criarTabela($tabela)
    {
        header('Content-Type: application/json');

        try {
            if ($this->uploadTxtModel->criarTabela($tabela)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Tabela $tabela criada com sucesso!"
                ]);
            } else {
                echo json_encode([
                    'status' => 'danger',
                    'message' => "Erro ao criar tabela $tabela"
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'danger',
                'message' => 'Erro: ' . $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * [ estatisticas ] - Exibe estatísticas dos dados importados
     * 
     * @return void
     */
    public function estatisticas()
    {
        // Verifica permissão para o módulo
        // Middleware::verificarPermissao(6);
        
        // Processar filtros
        $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
        $dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;
        $responsavel = isset($_GET['responsavel']) ? $_GET['responsavel'] : null;
        
        // Parâmetros de paginação
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $itensPorPagina = isset($_GET['itens_por_pagina']) ? (int)$_GET['itens_por_pagina'] : 10;
        
        // Validar itens por página (permitir apenas valores específicos)
        $opcoesItensPorPagina = [5, 10, 20, 50, 100];
        if (!in_array($itensPorPagina, $opcoesItensPorPagina)) {
            $itensPorPagina = 10; // Valor padrão
        }
        
        $dados = [
            'tituloPagina' => 'Estatísticas de Movimentações',
            'descricaoPagina' => 'Visualize estatísticas das movimentações importadas',
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'responsavel' => $responsavel,
                'pagina' => $pagina,
                'itens_por_pagina' => $itensPorPagina
            ]
        ];
        
        // Obter estatísticas do NAC com paginação
        $resultadoPaginado = $this->uploadTxtModel->obterEstatisticasNACPaginadas(
            $pagina,
            $itensPorPagina,
            $dataInicio,
            $dataFim,
            $responsavel
        );
        
        $dados['estatisticasNAC'] = $resultadoPaginado['estatisticas'];
        $dados['paginacao'] = $resultadoPaginado['paginacao'];
        $dados['opcoesItensPorPagina'] = $opcoesItensPorPagina;
        
        // Obter tipos de movimentações para colunas
        $movimentacoes = $this->uploadTxtModel->obterMovimentacoesUnicas('dados_nac');
        $dados['movimentacoes'] = $movimentacoes;
        
        // Obter nomes de responsáveis para linhas (apenas os da página atual)
        $dados['responsaveis'] = $resultadoPaginado['responsaveis'];
        
        // Obter lista completa de responsáveis para filtro
        $dados['todosResponsaveis'] = $this->uploadTxtModel->obterResponsaveisUnicos('dados_nac');
        
        $this->view('uploadtxt/estatisticas', $dados);
    }

    /**
     * [ estatisticasDetalhadas ] - Exibe estatísticas detalhadas dos dados importados
     * 
     * @return void
     */
    public function estatisticasDetalhadas()
    {
        // Verifica permissão para o módulo
        // Middleware::verificarPermissao(6);
        
        // Processar filtros
        $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
        $dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;
        $responsavel = isset($_GET['responsavel']) ? $_GET['responsavel'] : null;
        $comarca = isset($_GET['comarca']) ? $_GET['comarca'] : null;
        $movimentacao = isset($_GET['movimentacao']) ? $_GET['movimentacao'] : null;
        
        // Parâmetros de paginação
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $itensPorPagina = isset($_GET['itens_por_pagina']) ? (int)$_GET['itens_por_pagina'] : 50;
        
        // Validar itens por página (permitir apenas valores específicos)
        $opcoesItensPorPagina = [25, 50, 100, 250, 500];
        if (!in_array($itensPorPagina, $opcoesItensPorPagina)) {
            $itensPorPagina = 50; // Valor padrão
        }
        
        $tabela = 'dados_nac'; // Podemos expandir para outras tabelas no futuro
        
        $dados = [
            'tituloPagina' => 'Estatísticas Detalhadas de Movimentações',
            'descricaoPagina' => 'Visualize estatísticas detalhadas das movimentações diárias',
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'responsavel' => $responsavel,
                'comarca' => $comarca,
                'movimentacao' => $movimentacao,
                'pagina' => $pagina,
                'itens_por_pagina' => $itensPorPagina
            ]
        ];
        
        // Obter movimentações detalhadas com paginação
        $resultadoPaginado = $this->uploadTxtModel->obterMovimentacoesDetalhadasPaginadas(
            $tabela, 
            $pagina,
            $itensPorPagina,
            $dataInicio, 
            $dataFim, 
            $responsavel, 
            $comarca, 
            $movimentacao
        );
        
        $dados['movimentacoes'] = $resultadoPaginado['dados'];
        $dados['paginacao'] = $resultadoPaginado['paginacao'];
        $dados['opcoesItensPorPagina'] = $opcoesItensPorPagina;
        
        // Obter estatísticas diárias
        $estatisticasDiarias = $this->uploadTxtModel->obterEstatisticasDiarias(
            $tabela,
            $dataInicio,
            $dataFim
        );
        $dados['estatisticasDiarias'] = $estatisticasDiarias;
        
        // Obter listas para filtros
        $dados['responsaveis'] = $this->uploadTxtModel->obterResponsaveisUnicos($tabela);
        $dados['comarcas'] = $this->uploadTxtModel->obterComarcasUnicas($tabela);
        $dados['tiposMovimentacao'] = $this->uploadTxtModel->obterMovimentacoesUnicas($tabela);
        
        // Calcular totais (usando os dados da paginação)
        $dados['totalMovimentacoes'] = $resultadoPaginado['paginacao']['total_registros'];
        
        // Para os totais únicos, use o model em vez de acessar o Database diretamente
        $dados['totalProcessosUnicos'] = $this->uploadTxtModel->contarProcessosUnicos(
            $tabela, $dataInicio, $dataFim, $responsavel, $comarca, $movimentacao
        );
        
        $dados['totalComarcasUnicas'] = $this->uploadTxtModel->contarComarcasUnicas(
            $tabela, $dataInicio, $dataFim, $responsavel, $comarca, $movimentacao
        );
        
        $dados['totalResponsaveisUnicos'] = $this->uploadTxtModel->contarResponsaveisUnicos(
            $tabela, $dataInicio, $dataFim, $responsavel, $comarca, $movimentacao
        );
        
        $this->view('uploadtxt/estatisticas_detalhadas', $dados);
    }
}
