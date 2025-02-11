<?php
namespace Helpers;

// Classe responsável por validar dados de usuário
class Validation {
    // Armazena mensagens de erro de validação
    private $errors = [];

    /**
     * Valida os dados de um usuário
     * 
     * @param array $data Dados do usuário a serem validados
     * @return bool Retorna true se todos os dados são válidos, false caso contrário
     */
    public function validateUser($data) {
        // Limpar array de erros de validações anteriores
        $this->errors = [];

        // VALIDAÇÃO DO NOME
        // Verifica se o nome foi preenchido
        if (empty($data['nome'])) {
            $this->errors['nome'] = 'Nome é obrigatório';
        } 
        // Verifica se o nome tem pelo menos 2 caracteres
        elseif (strlen($data['nome']) < 2) {
            $this->errors['nome'] = 'Nome deve ter pelo menos 2 caracteres';
        }

        // VALIDAÇÃO DO E-MAIL
        // Verifica se o e-mail foi preenchido
        if (empty($data['email'])) {
            $this->errors['email'] = 'E-mail é obrigatório';
        } 
        // Verifica se o e-mail tem formato válido
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'E-mail inválido';
        }

        // VALIDAÇÃO DO TELEFONE
        // Valida telefone apenas se for preenchido
        if (!empty($data['telefone'])) {
            // Remove todos os caracteres não numéricos
            $telefone = preg_replace('/[^\d]/', '', $data['telefone']);
            
            // Verifica se o telefone tem entre 10 e 11 dígitos
            if (strlen($telefone) < 10 || strlen($telefone) > 11) {
                $this->errors['telefone'] = 'Telefone inválido. Use o formato (DD) 9XXXX-XXXX';
            }
        }

        // VALIDAÇÃO DA DATA DE NASCIMENTO
        if (!empty($data['data_nascimento'])) {
            try {
                // Converte a data para objeto DateTime
                // Suporta entrada como string ou objeto DateTime
                if (is_string($data['data_nascimento'])) {
                    $dataNascimento = new \DateTime($data['data_nascimento']);
                } else {
                    $dataNascimento = $data['data_nascimento'];
                }

                // Calcula a idade atual
                $hoje = new \DateTime();
                $idade = $hoje->diff($dataNascimento)->y;

                // Verifica idade mínima de 16 anos
                if ($idade < 16) {
                    $this->errors['data_nascimento'] = 'Idade mínima é 16 anos';
                }

                // Impede datas de nascimento no futuro
                if ($dataNascimento > $hoje) {
                    $this->errors['data_nascimento'] = 'Data de nascimento não pode ser no futuro';
                }
            } catch (\Exception $e) {
                // Captura erros de conversão de data
                $this->errors['data_nascimento'] = 'Data de nascimento inválida';
            }
        }

        // Retorna true se não houver erros de validação
        return empty($this->errors);
    }

    /**
     * Recupera os erros de validação
     * 
     * @return array Lista de erros encontrados durante a validação
     */
    public function getErrors() {
        return $this->errors;
    }
}