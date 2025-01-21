<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafio Revvo</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Estilos gerais */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        /* Estilos da navegação */
        nav {
            background-color: #333;
            padding: 1rem;
            color: white;
        }

        nav .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .nav-button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: #0056b3;
        }

        /* Estilos do conteúdo principal */
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            text-align: center;
        }

        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }

        .welcome-text {
            margin-bottom: 2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <a href="/" class="logo">Revvo</a>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/courses" class="nav-button">Cursos</a>
                    <a href="/logout" class="nav-button">Sair</a>
                <?php else: ?>
                    <a href="/login" class="nav-button">Login</a>
                    <a href="/register" class="nav-button">Registrar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <h1>Bem-vindo ao Desafio Revvo</h1>
        <p class="welcome-text">
            <?php if (isset($_SESSION['user_id'])): ?>
                Acesse nossos cursos e comece a aprender!
            <?php else: ?>
                Faça login ou registre-se para acessar nossos cursos.
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
