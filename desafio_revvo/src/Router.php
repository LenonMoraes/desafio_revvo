<?php

namespace App;

/**
 * Classe responsável pelo roteamento da aplicação
 * Gerencia as rotas e direciona as requisições para os controllers apropriados
 */
class Router
{
    /**
     * Array que armazena todas as rotas registradas
     * Formato: ['GET' => ['/rota' => callback], 'POST' => ['/rota' => callback]]
     */
    private array $routes = [];

    /**
     * Registra uma rota do tipo GET
     * @param string $path - Caminho da URL (ex: '/', '/produtos')
     * @param callable|array $callback - Função ou array [Controller, método] a ser executado
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Registra uma rota do tipo POST
     * @param string $path - Caminho da URL (ex: '/login', '/cadastro')
     * @param callable|array $callback - Função ou array [Controller, método] a ser executado
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Resolve a rota atual baseado na URL e método HTTP
     * Executa o callback associado à rota ou retorna erro 404
     * @return mixed - Resultado da execução do callback da rota
     */
    public function resolve()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];
        $callback = $this->routes[$method][$path] ?? null;

        if ($callback === null) {
            http_response_code(404);
            return "404 - Página não encontrada";
        }

        // Se o callback for um array [Controller, método], instancia o controller e executa o método
        if (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];
            return $controller->$method();
        }

        return $callback();
    }
}
