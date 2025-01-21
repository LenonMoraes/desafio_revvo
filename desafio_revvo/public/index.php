<?php

// Exibe erros em ambiente de desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega o autoloader do composer
require __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Controllers\HomeController;

// Inicializa o roteador
$router = new Router();

// Define as rotas
$router->get('/', [HomeController::class, 'index']);

// Resolve a requisição atual
try {
    echo $router->resolve();
} catch (Exception $e) {
    http_response_code(500);
    echo "Erro: " . $e->getMessage();
}