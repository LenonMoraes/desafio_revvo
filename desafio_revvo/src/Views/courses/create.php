<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Curso - Desafio Revvo</title>
</head>
<body>
    <h1>Criar Novo Curso</h1>
    
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'empty_fields'): ?>
            <p style="color: red;">Todos os campos são obrigatórios</p>
        <?php endif; ?>
    <?php endif; ?>

    <form action="/courses" method="POST">
        <div>
            <label for="title">Título:</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div>
            <label for="description">Descrição:</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <button type="submit">Criar Curso</button>
    </form>

    <p><a href="/courses">Voltar para Lista de Cursos</a></p>
</body>
</html>
