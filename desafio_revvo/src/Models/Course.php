<?php

namespace App\Models;

/**
 * Modelo responsável por gerenciar os dados dos cursos
 */
class Course
{
    private $id;
    private $title;
    private $description;
    private $created_at;

    public function __construct($data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getCreatedAt() { return $this->created_at; }

    // Setters
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
}
