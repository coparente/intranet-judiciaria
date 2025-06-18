<?php
try {
    // Conexão com o banco
    $pdo = new PDO('mysql:host=localhost;dbname=dir_judiciaria', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Teste de Conexão com Banco - Agenda</h2>";
    
    // Verificar tabelas da agenda
    echo "<h3>1. Verificando tabelas da agenda...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'agenda_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas encontradas: " . count($tables) . "<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
    
    // Verificar categorias
    echo "<h3>2. Verificando categorias...</h3>";
    try {
        $stmt = $pdo->query("SELECT * FROM agenda_categorias WHERE ativo = 'S' ORDER BY nome");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Categorias encontradas: " . count($categorias) . "<br>";
        foreach ($categorias as $categoria) {
            echo "- ID: {$categoria['id']}, Nome: {$categoria['nome']}, Cor: {$categoria['cor']}<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao consultar categorias: " . $e->getMessage() . "<br>";
    }
    
    // Verificar eventos
    echo "<h3>3. Verificando eventos...</h3>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM agenda_eventos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total de eventos: " . $result['total'] . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao consultar eventos: " . $e->getMessage() . "<br>";
    }
    
    // Testar a classe Database do sistema
    echo "<h3>4. Testando classe Database do sistema...</h3>";
    require_once 'app/Libraries/Database.php';
    
    $db = new Database();
    $db->query("SELECT * FROM agenda_categorias WHERE ativo = 'S' ORDER BY nome");
    $categoriasDb = $db->resultados();
    
    echo "Categorias via classe Database: " . count($categoriasDb) . "<br>";
    foreach ($categoriasDb as $cat) {
        echo "- Objeto: " . print_r($cat, true) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage();
}
?> 