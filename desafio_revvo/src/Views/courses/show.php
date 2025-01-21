<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - Desafio Revvo</title>
</head>
<body>
    <h1><?= htmlspecialchars($course['title']) ?></h1>
    
    <div class="course-details">
        <p><strong>Descrição:</strong></p>
        <p><?= htmlspecialchars($course['description']) ?></p>
        
        <p><strong>Data de Criação:</strong> <?= date('d/m/Y H:i', strtotime($course['created_at'])) ?></p>
    </div>

    <p><a href="/courses">Voltar para Lista de Cursos</a></p>
</body>
</html>
