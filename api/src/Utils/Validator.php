<?php

namespace App\Utils;

/**
 * Classe utilitária para validação de campos de formulários.
 */
class Validator 
{
    /**
     * Método para validar os campos de um array, garantindo que nenhum campo esteja vazio.
     *
     * @param array $fields Um array associativo de campos a serem validados.
     * 
     * @return array Retorna o mesmo array de campos se todos os campos forem válidos.
     * 
     * @throws \Exception Se algum campo estiver vazio, lança uma exceção com a mensagem informando qual campo está ausente.
     */
    public static function validate(array $fields)
    {
        // Percorre todos os campos e verifica se algum deles está vazio
        foreach ($fields as $field => $value) {
            // Se o campo estiver vazio após remover os espaços, lança uma exceção
            if (empty(trim($value))) {
                throw new \Exception("O campo ($field) é obrigatório.");
            }
        }

        // Retorna os campos validados
        return $fields;
    }
}
