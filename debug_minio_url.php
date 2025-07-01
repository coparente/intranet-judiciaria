<?php
/**
 * Debug da geração de URLs do MinIO
 * Script para identificar e corrigir problemas na autenticação de URLs
 */

require_once 'app/configuracao.php';
require_once 'app/Libraries/MinioHelper.php';

class DebugMinioUrl 
{
    private $testesPassed = 0;
    private $testesFailed = 0;

    public function executarTodos()
    {
        echo "<h2>🔍 DEBUG - Geração de URLs MinIO</h2>\n";
        echo "<hr>\n";

        $this->testarConfiguracoes();
        $this->testarConexao();
        $this->testarUrlExistente();
        $this->testarUrlComParametros();
        $this->exibirResumo();
    }

    private function testarConfiguracoes()
    {
        echo "<h3>1. 📋 Verificação de Configurações</h3>\n";

        $configs = [
            'MINIO_ENDPOINT' => MINIO_ENDPOINT,
            'MINIO_REGION' => MINIO_REGION,
            'MINIO_ACCESS_KEY' => MINIO_ACCESS_KEY ? '✓ Definido (' . strlen(MINIO_ACCESS_KEY) . ' chars)' : '❌ Não definido',
            'MINIO_SECRET_KEY' => MINIO_SECRET_KEY ? '✓ Definido (' . strlen(MINIO_SECRET_KEY) . ' chars)' : '❌ Não definido',
            'MINIO_BUCKET' => MINIO_BUCKET
        ];

        foreach ($configs as $nome => $valor) {
            echo "• <strong>{$nome}:</strong> {$valor}<br>\n";
        }

        if (MINIO_ENDPOINT && MINIO_ACCESS_KEY && MINIO_SECRET_KEY && MINIO_BUCKET) {
            $this->sucesso("Configurações", "Todas as configurações estão definidas");
        } else {
            $this->erro("Configurações", "Algumas configurações estão faltando");
        }
        echo "<br>\n";
    }

    private function testarConexao()
    {
        echo "<h3>2. 🌐 Teste de Conexão MinIO</h3>\n";

        try {
            $resultado = MinioHelper::testarConexao();
            
            if ($resultado['sucesso']) {
                $this->sucesso("Conexão", "Conectado ao MinIO: " . $resultado['endpoint']);
                echo "• <strong>Bucket:</strong> {$resultado['bucket']}<br>\n";
            } else {
                $this->erro("Conexão", "Falha na conexão: " . $resultado['erro']);
            }
        } catch (Exception $e) {
            $this->erro("Conexão", "Exceção: " . $e->getMessage());
        }
        echo "<br>\n";
    }

    private function testarUrlExistente()
    {
        echo "<h3>3. 📁 Teste de URL para Arquivo Existente</h3>\n";
        
        $caminhoTeste = 'document/2025/boleto_renner.pdf'; // O arquivo que deu erro
        
        echo "• <strong>Testando arquivo:</strong> {$caminhoTeste}<br>\n";

        try {
            // Verificar se o arquivo existe
            $arquivos = MinioHelper::listarArquivos('document/2025/', 100);
            $arquivoEncontrado = false;
            
            echo "• <strong>Arquivos na pasta document/2025/:</strong><br>\n";
            foreach ($arquivos as $arquivo) {
                echo "&nbsp;&nbsp;- {$arquivo['caminho']} ({$arquivo['tamanho']} bytes)<br>\n";
                if ($arquivo['caminho'] === $caminhoTeste) {
                    $arquivoEncontrado = true;
                }
            }

            if ($arquivoEncontrado) {
                $this->sucesso("Arquivo", "Arquivo encontrado no MinIO");
                
                // Testar geração de URL
                echo "<br>• <strong>Gerando URL assinada...</strong><br>\n";
                $url = MinioHelper::gerarUrlVisualizacao($caminhoTeste, 3600);
                
                if ($url) {
                    $this->sucesso("URL", "URL gerada com sucesso");
                    echo "• <strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a><br>\n";
                    
                    // Verificar se tem parâmetros necessários
                    $parametrosNecessarios = [
                        'X-Amz-Algorithm',
                        'X-Amz-Credential', 
                        'X-Amz-Date',
                        'X-Amz-Expires',
                        'X-Amz-SignedHeaders',
                        'X-Amz-Signature'
                    ];
                    
                    $urlParts = parse_url($url);
                    parse_str($urlParts['query'] ?? '', $queryParams);
                    
                    echo "<br>• <strong>Parâmetros da URL:</strong><br>\n";
                    $parametrosFaltando = [];
                    foreach ($parametrosNecessarios as $param) {
                        if (isset($queryParams[$param])) {
                            echo "&nbsp;&nbsp;✓ {$param}: ✓<br>\n";
                        } else {
                            echo "&nbsp;&nbsp;❌ {$param}: FALTANDO<br>\n";
                            $parametrosFaltando[] = $param;
                        }
                    }
                    
                    if (empty($parametrosFaltando)) {
                        $this->sucesso("Parâmetros", "Todos os parâmetros AWS necessários estão presentes");
                    } else {
                        $this->erro("Parâmetros", "Parâmetros faltando: " . implode(', ', $parametrosFaltando));
                    }
                    
                } else {
                    $this->erro("URL", "Falha ao gerar URL");
                }
                
            } else {
                $this->aviso("Arquivo", "Arquivo não encontrado no MinIO");
            }
            
        } catch (Exception $e) {
            $this->erro("URL", "Exceção: " . $e->getMessage());
        }
        echo "<br>\n";
    }

    private function testarUrlComParametros()
    {
        echo "<h3>4. 🔧 Teste com Diferentes Configurações</h3>\n";

        echo "• <strong>Testando diferentes tempos de expiração:</strong><br>\n";
        
        $tempos = [300, 3600, 7200]; // 5 min, 1 hora, 2 horas
        $caminhoTeste = 'document/2025/boleto_renner.pdf';

        foreach ($tempos as $tempo) {
            try {
                $url = MinioHelper::gerarUrlVisualizacao($caminhoTeste, $tempo);
                if ($url) {
                    echo "&nbsp;&nbsp;✓ {$tempo}s: URL gerada<br>\n";
                } else {
                    echo "&nbsp;&nbsp;❌ {$tempo}s: Falha na geração<br>\n";
                }
            } catch (Exception $e) {
                echo "&nbsp;&nbsp;❌ {$tempo}s: Erro - " . $e->getMessage() . "<br>\n";
            }
        }
        echo "<br>\n";
    }

    private function sucesso($categoria, $mensagem)
    {
        echo "✅ <strong>{$categoria}:</strong> {$mensagem}<br>\n";
        $this->testesPassed++;
    }

    private function erro($categoria, $mensagem)
    {
        echo "❌ <strong>{$categoria}:</strong> {$mensagem}<br>\n";
        $this->testesFailed++;
    }

    private function aviso($categoria, $mensagem)
    {
        echo "⚠️ <strong>{$categoria}:</strong> {$mensagem}<br>\n";
    }

    private function exibirResumo()
    {
        echo "<hr>\n";
        echo "<h3>📊 Resumo dos Testes</h3>\n";
        echo "• <strong>Sucessos:</strong> {$this->testesPassed}<br>\n";
        echo "• <strong>Falhas:</strong> {$this->testesFailed}<br>\n";
        
        if ($this->testesFailed === 0) {
            echo "<p style='color: green;'><strong>✅ Todos os testes passaram!</strong></p>\n";
        } else {
            echo "<p style='color: red;'><strong>❌ Alguns testes falharam. Verifique as configurações.</strong></p>\n";
        }
    }
}

// Executar testes
$debug = new DebugMinioUrl();
$debug->executarTodos(); 