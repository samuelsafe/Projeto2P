<?php
require_once 'config.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = mysqli_prepare($conexao, "SELECT id, nome, senha, admin FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($resultado);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']    = $usuario['id'];
            $_SESSION['usuario_nome']  = $usuario['nome'];
            $_SESSION['usuario_admin'] = (bool) $usuario['admin'];

            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
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
    <title>Login - Biblioteca</title>
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
            <h2>Entrar na conta</h2>

            <?php if ($erro): ?>
                <p class="erro"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="email">E-mail ou usuário</label>
                <input type="text" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

                <label for="senha">Senha</label>
                <div class="campo-senha">
                    <input type="password" id="senha" name="senha" required>
                    <button type="button" class="btn-ver-senha" onclick="alternarSenha()" title="Mostrar/ocultar senha">👁</button>
                </div>

                <button type="submit" class="btn-box">Entrar</button>
            </form>

            <p>Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
        </div>
    </main>

    <footer>
        <p>Direitos reservados</p>
    </footer>

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
</body>
</html>
