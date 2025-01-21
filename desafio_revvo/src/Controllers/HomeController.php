<?php

namespace App\Controllers;

use CodeIgniter\Session\Session;

/**
 * Controller responsável por gerenciar a página inicial
 * Controla a exibição e lógica da home page
 */
class HomeController
{
    /**
     * @var Session
     */
    private $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    /**
     * Método que renderiza a página inicial
     * Utiliza output buffering para capturar o conteúdo da view
     * @return string - HTML da página inicial
     */
    public function index()
    {
        // Inicia o buffer de saída para capturar o conteúdo da view
        ob_start();
        // Inclui o arquivo da view
        require __DIR__ . '/../Views/home.php';
        // Retorna o conteúdo capturado e limpa o buffer
        return ob_get_clean();
    }
}
