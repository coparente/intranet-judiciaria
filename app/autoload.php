<?php

/**
 * @author Cleyton  <cleytonparente@gmail.com>
 */
// Registro de um carregador automático de classes personalizado
spl_autoload_register(function ($classe) {
    // Diretórios onde o carregador automático irá procurar as classes
    $diretorios = [
        'Libraries',             // Diretório 'Core'
        'Helpers',
        'Models',    // Adicione esta linha
        'Controllers' // Adicionando diretório de Controllers
    ];

    // Itera pelos diretórios especificados
    foreach ($diretorios as $diretorio) {
        // Constrói o caminho completo para o arquivo da classe
        $arquivo = (__DIR__ . DIRECTORY_SEPARATOR . $diretorio . DIRECTORY_SEPARATOR . $classe . '.php');

        // Verifica se o arquivo da classe existe
        if (file_exists($arquivo)) {
            // Se o arquivo da classe for encontrado, ele é incluído, tornando a classe disponível
            require_once $arquivo;
        }
    }
});
