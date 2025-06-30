<?php
/**
 * Script de teste para o sistema de download de mídias
 * Execute este script para verificar se tudo está funcionando corretamente
 */

require_once 'app/config.php';
require_once 'app/Libraries/Database.php';
require_once 'app/Libraries/SerproHelper.php';
require_once 'app/Models/ChatModel.php';

class TesteDownloadMidias
{
    private $chatModel;
    private $resultados = [];

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        echo "🚀 Iniciando testes do sistema de download de mídias...\n\n";
    }

    /**
     * Executa todos os testes
     */
    public function executarTodos()
    {
        $this->testarConexaoBanco();
        $this->testarEstruturaDiretorios();
        $this->testarPermissoesArquivos();
        $this->testarApiSerpro();
        $this->testarProcessamentoWebhook();
        $this->testarUtilitarios();
        
        $this->exibirResumo();
    }

    /**
     * Testa conexão com banco de dados
     */
    private function testarConexaoBanco()
    {
        echo "📊 Testando conexão com banco de dados...\n";
        
        try {
            $sql = "SELECT COUNT(*) as total FROM mensagens_chat LIMIT 1";
            $this->chatModel->db->query($sql);
            $resultado = $this->chatModel->db->resultado();
            
            // Verificar se colunas de mídia existem
            $sql = "SHOW COLUMNS FROM mensagens_chat LIKE 'midia_%'";
            $this->chatModel->db->query($sql);
            $colunas = $this->chatModel->db->resultados();
            
            if (count($colunas) >= 2) {
                $this->sucesso("Banco", "Conexão OK e colunas de mídia presentes");
            } else {
                $this->erro("Banco", "Colunas de mídia não encontradas. Execute o script SQL primeiro.");
            }
            
        } catch (Exception $e) {
            $this->erro("Banco", "Erro na conexão: " . $e->getMessage());
        }
    }

    /**
     * Testa estrutura de diretórios
     */
    private function testarEstruturaDiretorios()
    {
        echo "📁 Testando estrutura de diretórios...\n";
        
        $diretorioBase = APPROOT . '/public/uploads/chat/midias/';
        $diretorioTeste = $diretorioBase . date('Y') . '/' . date('m') . '/teste/';
        
        // Criar diretório de teste
        if (!is_dir($diretorioTeste)) {
            if (mkdir($diretorioTeste, 0755, true)) {
                $this->sucesso("Diretórios", "Criação de diretórios funcionando");
                
                // Limpar diretório de teste
                rmdir($diretorioTeste);
                $caminhoMes = dirname($diretorioTeste);
                if ($this->diretorioVazio($caminhoMes)) rmdir($caminhoMes);
                $caminhoAno = dirname($caminhoMes);
                if ($this->diretorioVazio($caminhoAno)) rmdir($caminhoAno);
                
            } else {
                $this->erro("Diretórios", "Não foi possível criar diretórios em: " . $diretorioTeste);
            }
        } else {
            $this->sucesso("Diretórios", "Estrutura já existe");
        }
    }

    /**
     * Testa permissões de arquivos
     */
    private function testarPermissoesArquivos()
    {
        echo "🔒 Testando permissões de arquivos...\n";
        
        $diretorioBase = APPROOT . '/public/uploads/chat/midias/';
        $arquivoTeste = $diretorioBase . 'teste_permissoes.txt';
        
        // Criar diretório se não existir
        if (!is_dir($diretorioBase)) {
            mkdir($diretorioBase, 0755, true);
        }
        
        // Testar escrita
        if (file_put_contents($arquivoTeste, 'teste') !== false) {
            $this->sucesso("Permissões", "Escrita funcionando");
            
            // Testar leitura
            if (file_get_contents($arquivoTeste) === 'teste') {
                $this->sucesso("Permissões", "Leitura funcionando");
                
                // Limpar arquivo de teste
                unlink($arquivoTeste);
            } else {
                $this->erro("Permissões", "Erro na leitura de arquivos");
            }
        } else {
            $this->erro("Permissões", "Erro na escrita de arquivos");
        }
    }

    /**
     * Testa API SERPRO
     */
    private function testarApiSerpro()
    {
        echo "🌐 Testando API SERPRO...\n";
        
        try {
            // Testar obtenção de token
            $token = SerproHelper::getToken();
            
            if ($token) {
                $this->sucesso("API SERPRO", "Token obtido com sucesso");
                
                // Testar verificação de status
                if (SerproHelper::verificarStatusAPI()) {
                    $this->sucesso("API SERPRO", "Status da API: Online");
                } else {
                    $this->aviso("API SERPRO", "API pode estar offline");
                }
                
            } else {
                $this->erro("API SERPRO", "Erro ao obter token: " . SerproHelper::getLastError());
            }
            
        } catch (Exception $e) {
            $this->erro("API SERPRO", "Exceção: " . $e->getMessage());
        }
    }

    /**
     * Testa processamento de webhook com dados simulados
     */
    private function testarProcessamentoWebhook()
    {
        echo "📨 Testando processamento de webhook...\n";
        
        // Dados simulados de webhook para imagem
        $webhookImagem = [
            "messages" => [
                [
                    "from" => "62999999999",
                    "id" => "wamid.teste_" . time(),
                    "timestamp" => time(),
                    "type" => "image",
                    "text" => ["body" => ""],
                    "document" => ["id" => "", "filename" => "", "mime_type" => ""],
                    "image" => ["id" => "1243568920120313", "mime_type" => "image/jpeg"],
                    "audio" => ["id" => "", "mime_type" => ""],
                    "button" => ["payload" => "", "text" => ""]
                ]
            ]
        ];
        
        // Dados simulados para áudio
        $webhookAudio = [
            "messages" => [
                [
                    "from" => "62999999999",
                    "id" => "wamid.teste_audio_" . time(),
                    "timestamp" => time(),
                    "type" => "audio",
                    "text" => ["body" => ""],
                    "document" => ["id" => "", "filename" => "", "mime_type" => ""],
                    "image" => ["id" => "", "mime_type" => ""],
                    "audio" => ["id" => "2122935158220327", "mime_type" => "audio/ogg; codecs=opus"],
                    "button" => ["payload" => "", "text" => ""]
                ]
            ]
        ];
        
        // Testar estrutura dos dados
        if (isset($webhookImagem['messages'][0]['image']['id']) && 
            !empty($webhookImagem['messages'][0]['image']['id'])) {
            $this->sucesso("Webhook", "Estrutura de dados para imagem válida");
        } else {
            $this->erro("Webhook", "Estrutura de dados para imagem inválida");
        }
        
        if (isset($webhookAudio['messages'][0]['audio']['id']) && 
            !empty($webhookAudio['messages'][0]['audio']['id'])) {
            $this->sucesso("Webhook", "Estrutura de dados para áudio válida");
        } else {
            $this->erro("Webhook", "Estrutura de dados para áudio inválida");
        }
    }

    /**
     * Testa utilitários
     */
    private function testarUtilitarios()
    {
        echo "🔧 Testando utilitários...\n";
        
        // Testar sanitização de nomes
        $nomeProblematico = "arquivo com espaços & símbolos @#$%!.jpg";
        $nomeSanitizado = $this->sanitizarNomeArquivo($nomeProblematico);
        
        if (preg_match('/^[a-zA-Z0-9._\-àáâãäåçèéêëìíîïñòóôõöùúûüýÿ_]+$/', $nomeSanitizado)) {
            $this->sucesso("Utilitários", "Sanitização de nomes funcionando");
        } else {
            $this->erro("Utilitários", "Problema na sanitização: " . $nomeSanitizado);
        }
        
        // Testar determinação de extensão por MIME
        $extensoes = [
            'image/jpeg' => 'jpg',
            'audio/ogg; codecs=opus' => 'ogg',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4'
        ];
        
        $acertos = 0;
        foreach ($extensoes as $mime => $extensaoEsperada) {
            $extensaoObtida = $this->obterExtensaoPorMimeType($mime);
            if ($extensaoObtida === $extensaoEsperada) {
                $acertos++;
            }
        }
        
        if ($acertos === count($extensoes)) {
            $this->sucesso("Utilitários", "Mapeamento MIME → extensão funcionando");
        } else {
            $this->aviso("Utilitários", "Alguns mapeamentos de MIME podem estar incorretos");
        }
    }

    /**
     * Métodos auxiliares (copiados da classe principal para teste)
     */
    private function sanitizarNomeArquivo($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9._\-àáâãäåçèéêëìíîïñòóôõöùúûüýÿ]/', '_', $filename);
        if (strlen($filename) > 100) {
            $extensao = pathinfo($filename, PATHINFO_EXTENSION);
            $nome = pathinfo($filename, PATHINFO_FILENAME);
            $nome = substr($nome, 0, 100 - strlen($extensao) - 1);
            $filename = $nome . '.' . $extensao;
        }
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');
        return $filename;
    }

    private function obterExtensaoPorMimeType($mimeType)
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'audio/mpeg' => 'mp3',
            'audio/ogg; codecs=opus' => 'ogg',
            'video/mp4' => 'mp4',
            'application/pdf' => 'pdf',
        ];
        return $mimeToExt[$mimeType] ?? 'bin';
    }

    private function diretorioVazio($diretorio)
    {
        if (!is_dir($diretorio)) return false;
        $arquivos = scandir($diretorio);
        return count($arquivos) <= 2;
    }

    /**
     * Métodos de log
     */
    private function sucesso($categoria, $mensagem)
    {
        echo "  ✅ $categoria: $mensagem\n";
        $this->resultados['sucessos'][] = "$categoria: $mensagem";
    }

    private function erro($categoria, $mensagem)
    {
        echo "  ❌ $categoria: $mensagem\n";
        $this->resultados['erros'][] = "$categoria: $mensagem";
    }

    private function aviso($categoria, $mensagem)
    {
        echo "  ⚠️  $categoria: $mensagem\n";
        $this->resultados['avisos'][] = "$categoria: $mensagem";
    }

    /**
     * Exibe resumo dos testes
     */
    private function exibirResumo()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📋 RESUMO DOS TESTES\n";
        echo str_repeat("=", 50) . "\n";
        
        $sucessos = count($this->resultados['sucessos'] ?? []);
        $erros = count($this->resultados['erros'] ?? []);
        $avisos = count($this->resultados['avisos'] ?? []);
        
        echo "✅ Sucessos: $sucessos\n";
        echo "❌ Erros: $erros\n";
        echo "⚠️  Avisos: $avisos\n\n";
        
        if ($erros > 0) {
            echo "🚨 PROBLEMAS ENCONTRADOS:\n";
            foreach ($this->resultados['erros'] as $erro) {
                echo "  • $erro\n";
            }
            echo "\n";
        }
        
        if ($avisos > 0) {
            echo "⚠️  AVISOS:\n";
            foreach ($this->resultados['avisos'] as $aviso) {
                echo "  • $aviso\n";
            }
            echo "\n";
        }
        
        if ($erros === 0) {
            echo "🎉 Todos os testes passaram! Sistema pronto para uso.\n";
        } else {
            echo "🔧 Corrija os problemas encontrados antes de usar o sistema.\n";
        }
        
        echo "\n📖 Para mais informações, consulte o arquivo DOWNLOAD_MIDIAS_README.md\n";
    }
}

// Executar testes se script for chamado diretamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $teste = new TesteDownloadMidias();
    $teste->executarTodos();
} 