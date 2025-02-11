<?php
namespace Models;

use DateTime;

class User {
    public $id;
    public $nome;
    public $email;
    public $telefone;
    public $data_nascimento;
    public $data_formatada; 
    public $criado_em; 

    public function populate($data) {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->telefone = $data['telefone'] ?? null;
        $this->criado_em = $data['created_at'] ?? null;
        
        // Converter data de nascimento para DateTime se for uma string
        if (!empty($data['data_nascimento'])) {
            $this->data_nascimento = is_string($data['data_nascimento']) 
                ? new DateTime($data['data_nascimento']) 
                : $data['data_nascimento'];
            
            // Formatar data no padrão brasileiro manualmente
            $this->data_formatada = $this->formatDateBrazilian($this->data_nascimento);
        } else {
            $this->data_nascimento = null;
            $this->data_formatada = 'N/A';
        }
    }

    // Método para formatar data no padrão brasileiro
    public function formatDateBrazilian($date = null) {
        // Se nenhuma data for fornecida, use a data de nascimento do objeto
        if ($date === null) {
            $date = $this->data_nascimento;
        }

        // Se for um objeto DateTime, formate
        if ($date instanceof DateTime) {
            return $date->format('d/m/Y');
        }

        // Se já for uma string, tente converter
        if (is_string($date)) {
            try {
                $dateObj = new DateTime($date);
                return $dateObj->format('d/m/Y');
            } catch (\Exception $e) {
                // Se não puder converter, retorne a string original
                return $date;
            }
        }

        // Se não for possível formatar, retorne uma string vazia
        return '';
    }
}