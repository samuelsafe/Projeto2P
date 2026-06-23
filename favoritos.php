<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

$stmt = mysqli_prepare($conexao, "
    SELECT livros.id, livros.titulo, livros.imagem
    FROM favoritos
    INNER JOIN livros ON livros.id = favoritos.livro_id
    WHERE favoritos.usuario_id = ?
    ORDER BY favoritos.data_adicionado DESC
");
mysqli_stmt_bind_param($stmt, "i", $usuarioId);
mysqli_stmt_execute($stmt);
$resultadoFavoritos = mysqli_stmt_get_result($stmt);
$totalFavoritos = mysqli_num_rows($resultadoFavoritos);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicom.jpeg" type="image/x-icon">
    <title>Favoritos - Biblioteca</title>
</head>
<body data-pagina="favoritos">
    <button id="botao-conta" class="botao-conta" aria-label="Abrir menu da conta">Conta</button>

    <aside id="painel-conta" class="painel-conta">
        <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <?php if (!empty($_SESSION['usuario_admin'])): ?>
            <a href="admin.php">Gerenciar livros</a>
        <?php endif; ?>
        <a href="logout.php">Sair</a>
    </aside>

    <header>
        <h1>Biblioteca digital</h1>
        <nav class="taskbar" aria-label="Menu principal">
            <a class="home" href="index.php">Home</a>
            <a class="home" href="favoritos.php">Favoritos</a>
        </nav>
    </header>

    <main>
        <?php if ($totalFavoritos === 0): ?>
            <p>Você ainda não tem livros favoritos.</p>
        <?php else: ?>
            <div class="container">
                <?php while ($livro = mysqli_fetch_assoc($resultadoFavoritos)): ?>
                    <div class="box">
                        <img src="<?= htmlspecialchars($livro['imagem']) ?>" alt="Capa do livro" class="imagem">
                        <p><?= htmlspecialchars($livro['titulo']) ?></p>
                        <a href="#" class="btn-box">Ler livro</a>
                        <button class="btn-favoritar ativo" data-livro-id="<?= $livro['id'] ?>">salvo</button>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>Direitos reservados</p>
    </footer>

    <script src="js/favoritos.js"></script>
    <script src="js/menu.js"></script>
</body>
</html>
