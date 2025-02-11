<?php
require '../vendor/autoload.php';

use Repositories\UserRepository;

$userRepo = new UserRepository();

// Parâmetros de paginação
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$itensPorPagina = 10;
$termoPesquisa = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Buscar usuários com paginação
$usuarios = $userRepo->search($termoPesquisa, $paginaAtual, $itensPorPagina);
$totalPaginas = $userRepo->calcularTotalPaginas($itensPorPagina, $termoPesquisa);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Cadastro de Usuários">
    <title>Cadastro de Usuários</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="preload" href="js/script.js" as="script">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Cadastro de Usuários</h1>
            
            <!-- Barra de Pesquisa -->
            <form id="search-form" class="search-container">
                <input 
                    type="search" 
                    id="search-input" 
                    name="busca" 
                    placeholder="Buscar usuários..." 
                    value="<?= htmlspecialchars($termoPesquisa) ?>"
                >
                <button type="submit" class="btn btn-primary">
                    <i class="icon-search"></i> Buscar
                </button>
            </form>

            <button 
                onclick="openModal('create')" 
                class="btn btn-success" 
                aria-label="Adicionar Novo Usuário"
            >
                <i class="icon-plus"></i> Novo Usuário
            </button>
        </header>

        <main>
            <table aria-label="Lista de Usuários">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario->nome) ?></td>
                        <td><?= htmlspecialchars($usuario->email) ?></td>
                        <td>
                            <button 
                                onclick="openModal('view', <?= $usuario->id ?>)" 
                                class="btn btn-info" 
                                aria-label="Visualizar Usuário"
                            >
                                Visualizar
                            </button>
                            <button 
                                onclick="openModal('edit', <?= $usuario->id ?>)" 
                                class="btn btn-warning" 
                                aria-label="Editar Usuário"
                            >
                                Editar
                            </button>
                            <button 
                                onclick="confirmDelete(<?= $usuario->id ?>)" 
                                class="btn btn-danger" 
                                aria-label="Excluir Usuário"
                            >
                                Excluir
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Controles de Paginação -->
            <div class="pagination">
                <?php if ($paginaAtual > 1): ?>
                    <a href="?pagina=<?= $paginaAtual - 1 ?>&busca=<?= urlencode($termoPesquisa) ?>" class="btn btn-secondary">
                        Anterior
                    </a>
                <?php endif; ?>

                <span>Página <?= $paginaAtual ?> de <?= $totalPaginas ?></span>

                <?php if ($paginaAtual < $totalPaginas): ?>
                    <a href="?pagina=<?= $paginaAtual + 1 ?>&busca=<?= urlencode($termoPesquisa) ?>" class="btn btn-secondary">
                        Próxima
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($usuarios)): ?>
                <div class="alert alert-info">
                    Nenhum usuário encontrado.
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Genérico -->
    <div id="modal" class="modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title" class="modal-title"></h2>
                <button 
                    class="close" 
                    onclick="closeModal()" 
                    aria-label="Fechar Modal"
                >
                    &times;
                </button>
            </div>
            <div id="modal-body" class="modal-body">
                <!-- Conteúdo do modal será carregado via AJAX -->
            </div>
        </div>
    </div>

    <script src="js/script.js" defer></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>
</html>