<?php

namespace App\Controllers;

use App\Database\Database;
use App\Models\Course;

/**
 * Controller responsável pelo gerenciamento de cursos
 */
class CourseController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista todos os cursos
     */
    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY created_at DESC");
        $courses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        ob_start();
        require __DIR__ . '/../Views/courses/index.php';
        return ob_get_clean();
    }

    /**
     * Exibe o formulário de criação de curso
     */
    public function create()
    {
        ob_start();
        require __DIR__ . '/../Views/courses/create.php';
        return ob_get_clean();
    }

    /**
     * Salva um novo curso
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /courses/create');
            exit;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($title) || empty($description)) {
            header('Location: /courses/create?error=empty_fields');
            exit;
        }

        $stmt = $this->db->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
        
        try {
            $stmt->execute([$title, $description]);
            header('Location: /courses?success=1');
        } catch (\PDOException $e) {
            header('Location: /courses/create?error=database');
        }
        exit;
    }

    /**
     * Exibe um curso específico
     */
    public function show($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$course) {
            header('Location: /courses?error=not_found');
            exit;
        }

        ob_start();
        require __DIR__ . '/../Views/courses/show.php';
        return ob_get_clean();
    }
}
