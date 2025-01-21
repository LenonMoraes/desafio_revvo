<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - Desafio Revvo</title>
</head>
<body>
    <h1>Cursos Disponíveis</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Curso criado com sucesso!</p>
    <?php endif; ?>

    <a href="/courses/create">Criar Novo Curso</a>

    <?php if (empty($courses)): ?>
        <p>Nenhum curso disponível.</p>
    <?php else: ?>
        <div class="courses-list">
            <?php foreach ($courses as $course): ?>
                <div class="course-item">
                    <h2><?= htmlspecialchars($course['title']) ?></h2>
                    <p><?= htmlspecialchars($course['description']) ?></p>
                    <a href="/courses/<?= $course['id'] ?>">Ver Detalhes</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><a href="/dashboard">Voltar ao Dashboard</a></p>
</body>
</html>
