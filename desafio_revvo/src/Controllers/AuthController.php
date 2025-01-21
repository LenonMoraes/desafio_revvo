<?php

namespace App\Controllers;

use App\Database\Database;
use App\Models\User;

/**
 * Controller responsável pela autenticação e registro de usuários
 */
class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Exibe o formulário de login
     */
    public function loginForm()
    {
        ob_start();
        require __DIR__ . '/../Views/auth/login.php';
        return ob_get_clean();
    }

    /**
     * Processa o login do usuário
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && password_verify($password, $userData['password'])) {
            $_SESSION['user_id'] = $userData['id'];
            header('Location: /dashboard');
            exit;
        }

        header('Location: /login?error=1');
        exit;
    }

    /**
     * Exibe o formulário de registro
     */
    public function registerForm()
    {
        ob_start();
        require __DIR__ . '/../Views/auth/register.php';
        return ob_get_clean();
    }

    /**
     * Processa o registro do usuário
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validações básicas
        if (empty($name) || empty($email) || empty($password)) {
            header('Location: /register?error=empty_fields');
            exit;
        }

        // Verifica se o email já existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header('Location: /register?error=email_exists');
            exit;
        }

        // Insere o novo usuário
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute([$name, $email, $hashedPassword]);
            header('Location: /login?success=1');
        } catch (\PDOException $e) {
            header('Location: /register?error=database');
        }
        exit;
    }

    /**
     * Realiza o logout do usuário
     */
    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}
