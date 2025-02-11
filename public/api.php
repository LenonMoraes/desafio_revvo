<?php
// Carrega o autoloader do Composer para gerenciar dependências automaticamente
require '../vendor/autoload.php';

// Desabilita o buffer de saída para prevenir saída HTML acidental
ob_end_clean();
ob_start();

// Configurações de relatório de erros
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Desabilita exibição direta de erros

// Configura cabeçalhos para resposta JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Função para enviar erros em formato JSON de maneira padronizada
function sendJsonError($message, $code = 500, $details = []) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_details' => $details
    ]);
    exit;
}

// Função para registrar erros em um arquivo de log
function logError($message, $context = []) {
    $logFile = '../error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    
    if (!empty($context)) {
        $logMessage .= "Context: " . print_r($context, true) . "\n";
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Manipulador global de exceções não tratadas
set_exception_handler(function($exception) {
    // Registra detalhes completos da exceção
    logError('Unhandled Exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    // Envia resposta de erro amigável para o cliente
    sendJsonError('Erro interno do servidor', 500, [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
});

// Converte erros PHP em exceções para tratamento consistente
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

use Repositories\UserRepository;
use Models\User;

try {
    // Inicializa o repositório de usuários
    $userRepo = new UserRepository();
    
    // Obtém a ação e o ID da requisição (GET ou POST)
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    $id = $_GET['id'] ?? $_POST['id'] ?? null;

    // Valida se uma ação foi especificada
    if (!$action) {
        sendJsonError('Ação não especificada', 400);
    }

    // Roteamento de ações
    switch ($action) {
        // Ação de listagem de usuários
        case 'list':
            try {
                // Configura parâmetros de paginação
                $paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
                $itensPorPagina = isset($_GET['itensPorPagina']) ? intval($_GET['itensPorPagina']) : 10;
                $termoPesquisa = isset($_GET['busca']) ? trim($_GET['busca']) : '';

                // Registra detalhes da requisição de listagem
                logError('List Action Request', [
                    'pagina' => $paginaAtual,
                    'itensPorPagina' => $itensPorPagina,
                    'termoPesquisa' => $termoPesquisa
                ]);

                // Valida parâmetros de paginação
                if ($paginaAtual < 1 || $itensPorPagina < 1) {
                    sendJsonError('Parâmetros de paginação inválidos', 400);
                }

                // Busca usuários com base nos parâmetros
                $usuarios = $userRepo->search($termoPesquisa, $paginaAtual, $itensPorPagina);
                $totalPaginas = $userRepo->calcularTotalPaginas($itensPorPagina, $termoPesquisa);

                // Registra resultado da listagem
                logError('List Action Result', [
                    'total_usuarios' => count($usuarios),
                    'total_paginas' => $totalPaginas
                ]);

                // Retorna resultado da listagem em JSON
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'usuarios' => $usuarios,
                        'paginacao' => [
                            'paginaAtual' => $paginaAtual,
                            'totalPaginas' => $totalPaginas,
                            'itensPorPagina' => $itensPorPagina
                        ]
                    ]
                ]);
            } catch (Exception $e) {
                // Registra erro detalhado em caso de falha na listagem
                logError('Erro na listagem de usuários', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Envia mensagem de erro amigável
                sendJsonError('Não foi possível carregar os usuários', 500, [
                    'error_details' => $e->getMessage()
                ]);
            }
            break;

        // Ação de visualização de usuário específico
        case 'view':
            // Valida se um ID foi fornecido
            if (!$id) {
                sendJsonError('ID do usuário não especificado', 400);
            }
            
            // Busca usuário pelo ID
            $usuario = $userRepo->find($id);
            if (!$usuario) {
                sendJsonError('Usuário não encontrado', 404);
            }
            
            // Retorna detalhes do usuário
            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);
            break;

        // Ação de criação de usuário
        case 'create':
            try {
                // Obtém dados da requisição (JSON ou POST)
                $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                
                // Valida campos obrigatórios
                if (!isset($data['nome']) || !isset($data['email'])) {
                    sendJsonError('Dados incompletos para criação', 400, [
                        'required_fields' => ['nome', 'email']
                    ]);
                }

                // Verifica se o email já existe
                if ($userRepo->emailExists($data['email'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email já está cadastrado'
                    ]);
                    exit;
                }

                // Cria objeto de usuário
                $usuario = new User();
                $usuario->nome = trim($data['nome']);
                $usuario->email = trim($data['email']);
                $usuario->telefone = trim($data['telefone']) ?? null;
                $usuario->data_nascimento = trim($data['data_nascimento']) ?? null;

                // Tenta criar usuário
                $result = $userRepo->create($usuario);
                
                // Busca usuário criado para retornar detalhes completos
                $usuarioCriado = $userRepo->find($result);
                
                // Retorna resposta de sucesso
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário criado com sucesso',
                    'data' => $usuarioCriado
                ]);
            } catch (Exception $e) {
                // Registra erro de criação de usuário
                logError('Erro ao criar usuário', [
                    'message' => $e->getMessage(),
                    'input_data' => $data
                ]);

                // Envia mensagem de erro amigável
                sendJsonError('Erro ao criar usuário', 400, [
                    'error' => json_decode($e->getMessage(), true) ?? $e->getMessage()
                ]);
            }
            break;

        // Ação de atualização de usuário
        case 'update':
            try {
                // Obtém dados da requisição (JSON ou POST)
                $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                
                // Registra dados de tentativa de atualização
                error_log('User Update Attempt - Input Data: ' . json_encode($data));

                // Valida campos obrigatórios
                if (!isset($data['id']) || !isset($data['nome']) || !isset($data['email'])) {
                    sendJsonError('Campos obrigatórios ausentes', 400, [
                        'required_fields' => ['id', 'nome', 'email']
                    ]);
                    exit;
                }

                // Cria objeto de usuário
                $usuario = new User();
                $usuario->id = trim($data['id']);
                $usuario->nome = trim($data['nome']);
                $usuario->email = trim($data['email']);
                $usuario->telefone = trim($data['telefone']) ?? null;
                $usuario->data_nascimento = trim($data['data_nascimento']) ?? null;
                $result = $userRepo->update($usuario);
                // Tenta atualizar usuário
                try {
                    $result = $userRepo->update($usuario);
                   
                    if ($result) {
                        // Busca usuário atualizado para retornar detalhes completos
                        $usuarioAtualizado = $userRepo->find($usuario->id);
                        
                        // Retorna resposta de sucesso
                        echo json_encode([
                            'success' => true,
                            'message' => 'Usuário atualizado com sucesso',
                            'data' => $usuarioAtualizado
                        ]);
                    } else {
                        sendJsonError('Usuário não encontrado', 404);
                    }
                } catch (Exception $updateException) {
                    // Registra erro específico de atualização
                    error_log('User Update Exception: ' . $updateException->getMessage());
                    
                    // Tenta parsear erro em JSON
                    $errorDetails = json_decode($updateException->getMessage(), true);
                    
                    // Envia mensagem de erro amigável
                    sendJsonError('Erro ao atualizar usuário', 400, [
                        'error' => $errorDetails ?? $updateException->getMessage()
                    ]);
                }
            } catch (Exception $e) {
                // Registra erros inesperados
                error_log('Unexpected User Update Error: ' . $e->getMessage());
                
                sendJsonError('Erro ao atualizar usuário', 400, [
                    'error' => $e->getMessage()
                ]);
            }
            break;

        // Ação de exclusão de usuário
        case 'delete':
            try {
                // Tenta excluir usuário
                if ($userRepo->delete($id)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Usuário excluído com sucesso'
                    ]);
                } else {
                    sendJsonError('Usuário não encontrado', 404);
                }
            } catch (Exception $e) {
                // Envia mensagem de erro em caso de falha na exclusão
                sendJsonError('Erro ao excluir usuário', 400, [
                    'error' => $e->getMessage()
                ]);
            }
            break;

        // Ação inválida
        default:
            sendJsonError('Ação inválida', 400);
    }
} catch (Exception $e) {
    // Registra erro inesperado no servidor
    logError('Erro inesperado no servidor', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    sendJsonError($e->getMessage(), 500);
} finally {
    // Limpa qualquer saída acidental
    ob_end_flush();
}