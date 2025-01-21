<?php

// Exibe erros em ambiente de desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão
session_start();

// Carrega o autoloader do composer
require __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\CourseController;

// Inicializa o roteador
$router = new Router();

// Rotas públicas
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Middleware de autenticação para rotas protegidas
$isAuthenticated = isset($_SESSION['user_id']);

// Rotas protegidas
if ($isAuthenticated) {
    // Rotas de cursos
    $router->get('/courses', [CourseController::class, 'index']);
    $router->get('/courses/create', [CourseController::class, 'create']);
    $router->post('/courses', [CourseController::class, 'store']);
    $router->get('/courses/{id}', [CourseController::class, 'show']);
} else {
    // Redireciona para login se tentar acessar rotas protegidas
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($currentPath, '/courses') === 0) {
        header('Location: /login');
        exit;
    }
}

// Resolve a requisição atual
try {
    echo $router->resolve();
} catch (Exception $e) {
    http_response_code(404);
    echo "404 - Página não encontrada";
}