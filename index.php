<?php
require_once 'config.php';

$usuarioId = $_SESSION['usuario_id'] ?? null;
$busca     = trim($_GET['q'] ?? '');

if ($usuarioId) {
    if ($busca !== '') {
        $termoBusca = '%' . $busca . '%';
        $stmt = mysqli_prepare($conexao, "
            SELECT livros.id, livros.titulo, livros.imagem,
                   IF(favoritos.id IS NOT NULL, 1, 0) AS favoritado
            FROM livros
            LEFT JOIN favoritos ON favoritos.livro_id = livros.id AND favoritos.usuario_id = ?
            WHERE livros.titulo LIKE ?
        ");
        mysqli_stmt_bind_param($stmt, "is", $usuarioId, $termoBusca);
    } else {
        $stmt = mysqli_prepare($conexao, "
            SELECT livros.id, livros.titulo, livros.imagem,
                   IF(favoritos.id IS NOT NULL, 1, 0) AS favoritado
            FROM livros
            LEFT JOIN favoritos ON favoritos.livro_id = livros.id AND favoritos.usuario_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
    }
    mysqli_stmt_execute($stmt);
    $resultadoLivros = mysqli_stmt_get_result($stmt);
} else {
    if ($busca !== '') {
        $termoBusca = '%' . $busca . '%';
        $stmt = mysqli_prepare($conexao, "SELECT id, titulo, imagem, 0 AS salvo FROM livros WHERE titulo LIKE ?");
        mysqli_stmt_bind_param($stmt, "s", $termoBusca);
        mysqli_stmt_execute($stmt);
        $resultadoLivros = mysqli_stmt_get_result($stmt);
    } else {
        $resultadoLivros = mysqli_query($conexao, "SELECT id, titulo, imagem, 0 AS salvo FROM livros");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicom.jpeg" type="image/x-icon">
    <title>Midnith Chapters</title>
</head>
<body>
    <button id="botao-conta" class="botao-conta" aria-label="Abrir menu da conta">Conta</button>

    <aside id="painel-conta" class="painel-conta">
        <?php if (isset($_SESSION['usuario_nome'])): ?>
            <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <?php if (!empty($_SESSION['usuario_admin'])): ?>
                <a href="admin.php">Gerenciar livros</a>
                <a href="admin_usuarios.php">Gerenciar usuários</a>
            <?php endif; ?>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="cadastro.php">Criar conta</a>
        <?php endif; ?>
    </aside>

    <header>
        <h1>Biblioteca digital</h1>
        <p>Seu espaço para se destrair lendo um bom livro.</p>
        <nav class="taskbar" aria-label="Menu principal">
            <form class="barra-pesquisa" action="index.php" method="GET" role="search">
                <input
                    type="search"
                    name="q"
                    id="campoBusca"
                    placeholder="Pesquisar livro..."
                    value="<?= htmlspecialchars($busca) ?>"
                    aria-label="Pesquisar livro"
                    autocomplete="off"
                >
                <button type="submit" aria-label="Buscar">&#128269;</button>
            </form>
            <a class="home" href="index.php">Home</a>
            <a class="home" href="favoritos.php">Favoritos</a>
        </nav>
    </header>

    <main>
        <?php if ($busca !== ''): ?>
            <p class="resultado-busca">
                Resultados para: <strong><?= htmlspecialchars($busca) ?></strong>
                &mdash; <a href="index.php">Limpar busca</a>
            </p>
        <?php endif; ?>

        <div class="container" id="grade-livros">
            <?php
            $totalLivros = 0;
            while ($livro = mysqli_fetch_assoc($resultadoLivros)):
                $totalLivros++;
            ?>
                <div class="box">
                    <img src="<?= htmlspecialchars($livro['imagem']) ?>" alt="Capa do livro" class="imagem">
                    <p><?= htmlspecialchars($livro['titulo']) ?></p>
                    <a href="#" class="btn-box">Ler livro</a>

                    <?php if ($usuarioId): ?>
                        <button
                            class="btn-favoritar <?= $livro['favoritado'] ? 'ativo' : '' ?>"
                            data-livro-id="<?= $livro['id'] ?>"
                        >
                            <?= $livro['favoritado'] ? 'Salvo' : 'Salvar' ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>

            <?php if ($totalLivros === 0): ?>
                <p class="nenhum-resultado">Nenhum livro encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>Direitos reservados</p>
    </footer>

    <script src="js/favoritos.js"></script>
    <script src="js/menu.js"></script>
</body>
</html>
