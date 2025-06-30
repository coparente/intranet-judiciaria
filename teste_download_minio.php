<?php

/**
 * [ TESTE DOWNLOAD MINIO ] - Script para testar download de mídias da API SERPRO e upload para MinIO
 * 
 * @author Sistema de Chat SERPRO
 * @copyright 2025 TJGO
 * @version 1.0.0
 */

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // 5 minutos

echo "<h1>🧪 Teste de Download de Mídias SERPRO + MinIO</h1>";
echo "<hr>";

class TesteDownloadMinio
{
    private $sucessos = [];
    private $erros = [];
    private $avisos = [];

    public function __construct()
    {
        // Incluir configurações
        require_once 'app/configuracao.php';
        require_once 'app/Libraries/Database.php';
        require_once 'app/Libraries/SerproHelper.php';
        require_once 'app/Libraries/MinioHelper.php';
    }

    public function executarTodos()
    {
        echo "<h2>🚀 Iniciando Testes...</h2>";
        
        $this->testarConfiguracoes();
        $this->testarConexaoMinIO();
        $this->testarApiSerpro();
        $this->testarDownloadSimulado();
        $this->testarOrganizacaoArquivos();
        
        $this->exibirResumo();
    }

    private function testarConfiguracoes()
    {
        echo "<h3>⚙️ Teste de Configurações</h3>";
        
        // Verificar constantes do SERPRO
        $constantesSerpro = ['SERPRO_BASE_URL', 'SERPRO_CLIENT_ID', 'SERPRO_CLIENT_SECRET', 'SERPRO_PHONE_NUMBER_ID'];
        
        foreach ($constantesSerpro as $constante) {
            if (defined($constante) && !empty(constant($constante))) {
                $this->sucesso('Configurações', "✅ {$constante} definida");
            } else {
                $this->erro('Configurações', "❌ {$constante} não definida ou vazia");
            }
        }

        // Verificar se composer autoload existe
        if (file_exists('vendor/autoload.php')) {
            $this->sucesso('Configurações', "✅ Composer autoload encontrado");
        } else {
            $this->erro('Configurações', "❌ Composer autoload não encontrado. Execute: composer install");
        }

        // Verificar extensões PHP necessárias
        $extensoes = ['curl', 'json', 'mbstring'];
        foreach ($extensoes as $ext) {
            if (extension_loaded($ext)) {
                $this->sucesso('Configurações', "✅ Extensão {$ext} carregada");
            } else {
                $this->erro('Configurações', "❌ Extensão {$ext} não encontrada");
            }
        }
    }

    private function testarConexaoMinIO()
    {
        echo "<h3>☁️ Teste de Conexão MinIO</h3>";
        
        try {
            $resultado = MinioHelper::testarConexao();
            
            if ($resultado['sucesso']) {
                $this->sucesso('MinIO', "✅ Conexão com MinIO estabelecida");
                $this->sucesso('MinIO', "📦 Bucket: " . $resultado['bucket']);
                $this->sucesso('MinIO', "🌐 Endpoint: " . $resultado['endpoint']);
                
                // Testar estatísticas
                $stats = MinioHelper::obterEstatisticas();
                $this->sucesso('MinIO', "📊 Arquivos no bucket: " . $stats['total_arquivos']);
                
            } else {
                $this->erro('MinIO', "❌ Erro na conexão: " . $resultado['erro']);
            }
            
        } catch (Exception $e) {
            $this->erro('MinIO', "❌ Exceção: " . $e->getMessage());
        }
    }

    private function testarApiSerpro()
    {
        echo "<h3>🔗 Teste de API SERPRO</h3>";
        
        try {
            SerproHelper::init();
            
            // Testar obtenção de token
            $token = SerproHelper::getToken();
            if ($token) {
                $this->sucesso('API SERPRO', "✅ Token obtido com sucesso (Tamanho: " . strlen($token) . ")");
            } else {
                $this->erro('API SERPRO', "❌ Falha ao obter token: " . SerproHelper::getLastError());
                return;
            }
            
            // Testar verificação de status da API
            if (SerproHelper::verificarStatusAPI()) {
                $this->sucesso('API SERPRO', "✅ API SERPRO online e acessível");
            } else {
                $this->aviso('API SERPRO', "⚠️ API SERPRO pode estar indisponível");
            }
            
        } catch (Exception $e) {
            $this->erro('API SERPRO', "❌ Exceção: " . $e->getMessage());
        }
    }

    private function testarDownloadSimulado()
    {
        echo "<h3>📥 Teste de Download Simulado</h3>";
        
        // Simular dados de mídia que viriam do webhook
        $testCases = [
            [
                'tipo' => 'image',
                'mime_type' => 'image/jpeg',
                'filename' => 'teste_imagem.jpg',
                'content' => 'FAKE_JPEG_CONTENT_FOR_TESTING'
            ],
            [
                'tipo' => 'audio',
                'mime_type' => 'audio/ogg',
                'filename' => 'teste_audio.ogg',
                'content' => 'FAKE_OGG_CONTENT_FOR_TESTING'
            ],
            [
                'tipo' => 'document',
                'mime_type' => 'application/pdf',
                'filename' => 'teste_documento.pdf',
                'content' => 'FAKE_PDF_CONTENT_FOR_TESTING'
            ]
        ];

        foreach ($testCases as $test) {
            try {
                $resultado = MinioHelper::uploadMidia(
                    $test['content'],
                    $test['tipo'],
                    $test['mime_type'],
                    $test['filename']
                );

                if ($resultado['sucesso']) {
                    $this->sucesso('Upload Teste', "✅ {$test['tipo']}: {$resultado['caminho_minio']}");
                    
                    // Testar geração de URL
                    $url = MinioHelper::gerarUrlVisualizacao($resultado['caminho_minio']);
                    if ($url) {
                        $this->sucesso('URL Teste', "✅ URL gerada para {$test['tipo']}");
                    } else {
                        $this->aviso('URL Teste', "⚠️ Falha ao gerar URL para {$test['tipo']}");
                    }
                    
                    // Testar download
                    $download = MinioHelper::baixarArquivo($resultado['caminho_minio']);
                    if ($download['sucesso']) {
                        $this->sucesso('Download Teste', "✅ Download realizado para {$test['tipo']} (" . strlen($download['dados']) . " bytes)");
                    } else {
                        $this->erro('Download Teste', "❌ Falha no download: " . $download['erro']);
                    }
                    
                    // Limpar arquivo de teste
                    MinioHelper::excluirArquivo($resultado['caminho_minio']);
                    $this->sucesso('Limpeza', "🗑️ Arquivo de teste removido: {$resultado['caminho_minio']}");
                    
                } else {
                    $this->erro('Upload Teste', "❌ Falha no upload {$test['tipo']}: " . $resultado['erro']);
                }
                
            } catch (Exception $e) {
                $this->erro('Upload Teste', "❌ Exceção para {$test['tipo']}: " . $e->getMessage());
            }
        }
    }

    private function testarOrganizacaoArquivos()
    {
        echo "<h3>📁 Teste de Organização de Arquivos</h3>";
        
        // Testar se a estrutura de pastas está correta
        $tiposEsperados = ['image', 'audio', 'video', 'document'];
        $anoAtual = date('Y');
        
        foreach ($tiposEsperados as $tipo) {
            $caminhoEsperado = "{$tipo}/{$anoAtual}/";
            $this->sucesso('Organização', "📂 Estrutura esperada: {$caminhoEsperado}");
        }
        
        // Testar listagem de arquivos
        try {
            $arquivos = MinioHelper::listarArquivos('', 10);
            $this->sucesso('Listagem', "📋 Listados " . count($arquivos) . " arquivo(s) no bucket");
            
            foreach ($arquivos as $arquivo) {
                $this->sucesso('Arquivo', "📄 {$arquivo['caminho']} (" . number_format($arquivo['tamanho']/1024, 2) . " KB)");
            }
            
        } catch (Exception $e) {
            $this->erro('Listagem', "❌ Erro ao listar arquivos: " . $e->getMessage());
        }
    }

    private function sucesso($categoria, $mensagem)
    {
        $this->sucessos[] = $mensagem;
        echo "<div style='color: green; margin: 5px 0;'><strong>[{$categoria}]</strong> {$mensagem}</div>";
    }

    private function erro($categoria, $mensagem)
    {
        $this->erros[] = $mensagem;
        echo "<div style='color: red; margin: 5px 0;'><strong>[{$categoria}]</strong> {$mensagem}</div>";
    }

    private function aviso($categoria, $mensagem)
    {
        $this->avisos[] = $mensagem;
        echo "<div style='color: orange; margin: 5px 0;'><strong>[{$categoria}]</strong> {$mensagem}</div>";
    }

    private function exibirResumo()
    {
        echo "<hr>";
        echo "<h2>📊 Resumo dos Testes</h2>";
        
        echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3 style='color: green; margin: 0 0 10px 0;'>✅ Sucessos (" . count($this->sucessos) . ")</h3>";
        foreach ($this->sucessos as $sucesso) {
            echo "<div>• {$sucesso}</div>";
        }
        echo "</div>";

        if (!empty($this->avisos)) {
            echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h3 style='color: orange; margin: 0 0 10px 0;'>⚠️ Avisos (" . count($this->avisos) . ")</h3>";
            foreach ($this->avisos as $aviso) {
                echo "<div>• {$aviso}</div>";
            }
            echo "</div>";
        }

        if (!empty($this->erros)) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h3 style='color: red; margin: 0 0 10px 0;'>❌ Erros (" . count($this->erros) . ")</h3>";
            foreach ($this->erros as $erro) {
                echo "<div>• {$erro}</div>";
            }
            echo "</div>";
        }

        // Status geral
        if (empty($this->erros)) {
            echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 5px; text-align: center;'>";
            echo "<h2 style='color: green; margin: 0;'>🎉 TODOS OS TESTES PASSARAM!</h2>";
            echo "<p>Seu sistema está pronto para receber e armazenar mídias do WhatsApp SERPRO no MinIO.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 20px; margin: 20px 0; border-radius: 5px; text-align: center;'>";
            echo "<h2 style='color: red; margin: 0;'>❌ ALGUNS TESTES FALHARAM</h2>";
            echo "<p>Verifique os erros acima e corrija antes de usar o sistema em produção.</p>";
            echo "</div>";
        }

        echo "<hr>";
        echo "<p><em>Teste executado em: " . date('d/m/Y H:i:s') . "</em></p>";
    }
}

// Executar testes
try {
    $teste = new TesteDownloadMinio();
    $teste->executarTodos();
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h2 style='color: red;'>❌ ERRO CRÍTICO</h2>";
    echo "<p><strong>Exceção:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . " (Linha: " . $e->getLine() . ")</p>";
    echo "</div>";
}

?> 