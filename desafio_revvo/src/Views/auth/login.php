<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Desafio Revvo</title>
</head>
<body>
    <h1>Login</h1>
    
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;">Email ou senha inválidos</p>
    <?php endif; ?>

    <form action="/login" method="POST">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Entrar</button>
    </form>

    <p>Não tem uma conta? <a href="/register">Registre-se</a></p>
</body>
</html>
