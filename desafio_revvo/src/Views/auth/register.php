<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Desafio Revvo</title>
</head>
<body>
    <h1>Registro</h1>
    
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'empty_fields'): ?>
            <p style="color: red;">Todos os campos são obrigatórios</p>
        <?php elseif ($_GET['error'] === 'email_exists'): ?>
            <p style="color: red;">Este email já está cadastrado</p>
        <?php endif; ?>
    <?php endif; ?>

    <form action="/register" method="POST">
        <div>
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Registrar</button>
    </form>

    <p>Já tem uma conta? <a href="/login">Faça login</a></p>
</body>
</html>
