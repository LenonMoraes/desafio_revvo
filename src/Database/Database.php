<?php
namespace Database;

use PDO;
use PDOException;

// Classe responsável por gerenciar a conexão com o banco de dados
class Database {
    // Configurações de conexão com o banco de dados MySQL
    private $host = 'localhost';       // Endereço do servidor de banco de dados
    private $db_name = 'desafio_revvo'; // Nome do banco de dados
    private $username = 'root';         // Nome de usuário para acesso ao banco de dados
    private $password = '';             // Senha de acesso ao banco de dados
    private $connection;                // Armazena a instância da conexão PDO

    // Método para estabelecer conexão com o banco de dados
    public function connect() {
        // Verifica se já existe uma conexão ativa para evitar múltiplas conexões
        if ($this->connection === null) {
            try {
                // Cria uma nova conexão PDO com as configurações definidas
                $this->connection = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
                
                // Configura o modo de erro para lançar exceções em caso de problemas
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Define o modo padrão de busca para retornar resultados como array associativo
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Encerra a execução e exibe mensagem de erro em caso de falha na conexão
                die("Erro de conexão: " . $e->getMessage());
            }
        }
        // Retorna a instância da conexão
        return $this->connection;
    }
}