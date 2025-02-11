<?php
namespace Repositories;

use Database\Database;
use Models\User;
use PDO;
use Helpers\Validation;
use DateTime;

// Classe responsável por gerenciar operações de persistência de usuários no banco de dados
class UserRepository {
    // Conexão com o banco de dados
    private $connection;
    
    // Objeto de validação de dados
    private $validation;

    /**
     * Construtor da classe
     * Inicializa a conexão com o banco de dados e o objeto de validação
     */
    public function __construct() {
        $db = new Database();
        $this->connection = $db->connect();
        $this->validation = new Validation();
    }

    /**
     * Formata uma data para o padrão brasileiro (dd/mm/aaaa)
     * 
     * @param DateTime $date Data a ser formatada
     * @return string Data formatada ou 'N/A' se inválida
     */
    public function formatDateBrazilian($date) {
        // Verifica se o parâmetro é uma instância válida de DateTime
        if (!$date instanceof DateTime) {
            return 'N/A';
        }
        
        // Retorna data no formato brasileiro
        return $date->format('d/m/Y');
    }

    /**
     * Recupera todos os usuários do banco de dados
     * 
     * @return array Lista de usuários
     */
    public function all() {
        // Consulta para buscar todos os usuários
        $stmt = $this->connection->query("SELECT * FROM usuarios");
        $usuarios = [];
        
        // Percorre os resultados e converte para objetos User
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuario = new User();
            $usuario->populate($row);

            // Adiciona formatação de data de nascimento
            if ($usuario->data_nascimento) {
                $usuario->data_formatada = $this->formatDateBrazilian($usuario->data_nascimento);
            }
            $usuarios[] = $usuario;
        }
        
        return $usuarios;
    }

    /**
     * Busca um usuário específico por ID
     * 
     * @param int $id Identificador único do usuário
     * @return User|null Usuário encontrado ou null se não existir
     */
    public function find($id): ?User {
        // Prepara consulta parametrizada para buscar usuário por ID
        $stmt = $this->connection->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $usuario = new User();
            $usuario->populate($row);
            
            // Converte data de nascimento para objeto DateTime
            if (!empty($row['data_nascimento'])) {
                $usuario->data_nascimento = new DateTime($row['data_nascimento']);
                $usuario->data_formatada = $this->formatDateBrazilian($usuario->data_nascimento);
            }
            
            return $usuario;
        }
        
        return null;
    }

    /**
     * Exclui um usuário do banco de dados
     * 
     * @param int $id Identificador único do usuário a ser excluído
     * @return bool Verdadeiro se exclusão for bem-sucedida
     */
    public function delete($id) {
        // Prepara consulta parametrizada para exclusão de usuário
        $stmt = $this->connection->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Cria um novo usuário no banco de dados
     * 
     * @param User $user Objeto de usuário a ser criado
     * @throws \Exception Se houver erros de validação ou email duplicado
     * @return int ID do usuário criado
     */
    public function create(User $user) {
        // Converte data de nascimento para objeto DateTime se for string
        if (is_string($user->data_nascimento)) {
            try {
                $user->data_nascimento = new \DateTime($user->data_nascimento);
            } catch (\Exception $e) {
                // Se a conversão falhar, define como null
                $user->data_nascimento = null;
            }
        }

        // Verifica se o email já existe no banco de dados
        if ($this->emailExists($user->email)) {
            throw new \Exception(json_encode([
                'email' => 'Este email já está cadastrado'
            ]));
        }

        // Prepara dados para validação
        $userData = [
            'nome' => $user->nome,
            'email' => $user->email,
            'telefone' => $user->telefone,
            'data_nascimento' => $user->data_nascimento
        ];

        // Valida os dados do usuário
        if (!$this->validation->validateUser($userData)) {
            $errors = $this->validation->getErrors();
            throw new \Exception(json_encode($errors));
        }

        // Prepara consulta para inserção de usuário
        $stmt = $this->connection->prepare(
            "INSERT INTO usuarios (nome, email, telefone, data_nascimento) 
             VALUES (:nome, :email, :telefone, :data_nascimento)"
        );
        
        // Executa a inserção
        $result = $stmt->execute([
            ':nome' => $user->nome,
            ':email' => $user->email,
            ':telefone' => $user->telefone,
            ':data_nascimento' => $user->data_nascimento ? $user->data_nascimento->format('Y-m-d') : null
        ]);

        // Retorna o ID do usuário criado
        return $result ? $this->connection->lastInsertId() : false;
    }

    /**
     * Atualiza um usuário existente no banco de dados
     * 
     * @param User $user Objeto de usuário a ser atualizado
     * @throws \Exception Se houver erros de validação ou usuário não encontrado
     * @return bool Verdadeiro se atualização for bem-sucedida
     */
    public function update(User $user) {
        // Verifica se o ID do usuário existe
        $existingUser = $this->find($user->id);
        if (!$existingUser) {
            throw new \Exception(json_encode([
                'id' => 'Usuário não encontrado'
            ]));
        }

        // Converte data de nascimento para objeto DateTime se for string
        if (is_string($user->data_nascimento)) {
            try {
                $user->data_nascimento = new \DateTime($user->data_nascimento);
            } catch (\Exception $e) {
                // Se a conversão falhar, mantém a data original ou define como null
                $user->data_nascimento = $existingUser->data_nascimento ?? null;
            }
        }

        // Prepara dados para validação
        $userData = [
            'nome' => $user->nome,
            'email' => $user->email,
            'telefone' => $user->telefone,
            'data_nascimento' => $user->data_nascimento
        ];

        // Valida os dados do usuário
        if (!$this->validation->validateUser($userData)) {
            $errors = $this->validation->getErrors();
            throw new \Exception(json_encode($errors));
        }

        // Prepara consulta para atualização de usuário
        $stmt = $this->connection->prepare(
            "UPDATE usuarios 
             SET nome = :nome, 
                 email = :email, 
                 telefone = :telefone, 
                 data_nascimento = :data_nascimento 
             WHERE id = :id"
        );
        
        // Executa a atualização
        $result = $stmt->execute([
            ':id' => $user->id,
            ':nome' => $user->nome,
            ':email' => $user->email,
            ':telefone' => $user->telefone,
            ':data_nascimento' => $user->data_nascimento ? $user->data_nascimento->format('Y-m-d') : null
        ]);

        // Verifica se a atualização foi bem-sucedida
        if (!$result) {
            throw new \Exception(json_encode([
                'update' => 'Falha ao atualizar usuário'
            ]));
        }

        return true;
    }

    /**
     * Verifica se um email já existe no banco de dados
     * 
     * @param string $email Email a ser verificado
     * @param int|null $excludeId ID do usuário a ser excluído da verificação
     * @return bool Verdadeiro se o email existir
     */
    public function emailExists($email, $excludeId = null): bool {
        // Prepara consulta para verificar existência de email
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Realiza uma busca de usuários com paginação
     * 
     * @param string $termo Termo de busca
     * @param int $pagina Número da página
     * @param int $itensPorPagina Número de itens por página
     * @return array Lista de usuários encontrados
     */
    public function search($termo = '', $pagina = 1, $itensPorPagina = 10) {
        try {
            // Valida parâmetros de entrada
            $pagina = max(1, intval($pagina));
            $itensPorPagina = max(1, intval($itensPorPagina));
            $termo = trim($termo);

            // Calcula offset para paginação
            $offset = ($pagina - 1) * $itensPorPagina;

            // Log de depuração
            error_log(sprintf(
                "UserRepository::search - Params: termo='%s', pagina=%d, itensPorPagina=%d, offset=%d", 
                $termo, $pagina, $itensPorPagina, $offset
            ));

            // Prepara consulta de pesquisa
            $query = "SELECT * FROM usuarios 
                      WHERE 1=1 
                      " . (!empty($termo) ? "AND (nome LIKE :termo OR email LIKE :termo)" : "") . "
                      LIMIT :limite OFFSET :offset";

            $stmt = $this->connection->prepare($query);

            // Bind de parâmetros com verificações adicionais
            if (!empty($termo)) {
                $stmt->bindValue(':termo', "%{$termo}%", PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $itensPorPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            // Executa consulta
            $executeResult = $stmt->execute();
            
            // Verifica erro na execução
            if ($executeResult === false) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception("Erro na consulta SQL: " . print_r($errorInfo, true));
            }

            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuario = new User();
                $usuario->populate($row);
                
                // Converte data de nascimento para objeto DateTime
                if (!empty($row['data_nascimento'])) {
                    $usuario->data_nascimento = new DateTime($row['data_nascimento']);
                    $usuario->data_formatada = $this->formatDateBrazilian($usuario->data_nascimento);
                }
                
                $usuarios[] = $usuario;
            }

            // Log de depuração
            error_log(sprintf(
                "UserRepository::search - Usuários encontrados: %d", 
                count($usuarios)
            ));

            return $usuarios;
        } catch (\Exception $e) {
            // Log detalhado do erro
            error_log(sprintf(
                "UserRepository::search - Erro: %s\nTrace: %s", 
                $e->getMessage(), 
                $e->getTraceAsString()
            ));

            // Relançar exceção para tratamento no nível superior
            throw $e;
        }
    }

    /**
     * Conta o total de usuários para paginação
     * 
     * @param string $termo Termo de busca
     * @return int Total de usuários
     */
    public function countTotal($termo = '') {
        // Prepara consulta para contar total de usuários
        $query = "SELECT COUNT(*) as total FROM usuarios 
                  WHERE nome LIKE :termo 
                  OR email LIKE :termo";

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':termo', "%{$termo}%", PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    /**
     * Calcula o total de páginas para paginação
     * 
     * @param int $itensPorPagina Número de itens por página
     * @param string $termo Termo de busca
     * @return int Total de páginas
     */
    public function calcularTotalPaginas($itensPorPagina = 10, $termo = '') {
        // Conta o total de usuários
        $totalItens = $this->countTotal($termo);
        return ceil($totalItens / $itensPorPagina);
    }
}