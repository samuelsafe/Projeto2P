<?php
require_once 'config.php';

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $erro = "Esse e-mail já está cadastrado.";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt2 = mysqli_prepare($conexao, "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt2, "sss", $nome, $email, $senhaHash);

            if (mysqli_stmt_execute($stmt2)) {
                $sucesso = "Cadastro realizado com sucesso! Você já pode entrar.";
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}
?>
<script>
    function alternarSenha() {
        const input = document.getElementById('senha');
        const btn   = document.querySelector('.btn-ver-senha');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '⌣';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    }
</script>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicom.jpeg" type="image/x-icon">
    <title>Cadastro - Biblioteca</title>
</head>
<body>
    <header>
        <h1>Biblioteca digital</h1>
        <nav class="taskbar" aria-label="Menu principal">
            <a class="home" href="index.php">Home</a>
        </nav>
    </header>

    <main>
        <div class="login-box">
            <h2>Criar conta</h2>

            <?php if ($erro): ?>
                <p class="erro"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
            <?php endif; ?>

            <form method="POST" action="cadastro.php">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>

                <label for="senha">Senha</label>
                <div class="campo-senha">
                 <input type="password" id="senha" name="senha" required>
                 <button type="button" class="btn-ver-senha"     onclick="alternarSenha()" title="Mostrar/ocultar senha">👁</button>
            </div>

                <button type="submit" class="btn-box">Cadastrar</button>
            </form>

            <p>Já tem conta? <a href="login.php">Entrar</a></p>
        </div>
    </main>

    <footer>
        <p>Direitos reservados</p>
    </footer>
</body>
</html>
