<?php
require_once 'config.php';

if (empty($_SESSION['usuario_admin'])) {
    header("Location: index.php");
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'adicionar' || $acao === 'editar') {
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $admin = isset($_POST['admin']) ? 1 : 0;
        $senha = $_POST['senha'] ?? '';

        if (empty($nome) || empty($email)) {
            $erro = "Preencha nome e e-mail.";
        } elseif ($acao === 'adicionar' && empty($senha)) {
            $erro = "Informe uma senha para o novo usuário.";
        } else {
            if ($acao === 'adicionar') {

                $stmtCheck = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ?");
                mysqli_stmt_bind_param($stmtCheck, "s", $email);
                mysqli_stmt_execute($stmtCheck);
                mysqli_stmt_store_result($stmtCheck);

                if (mysqli_stmt_num_rows($stmtCheck) > 0) {
                    $erro = "Esse e-mail já está cadastrado.";
                } else {
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($conexao, "INSERT INTO usuarios (nome, email, senha, admin) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "sssi", $nome, $email, $senhaHash, $admin);
                    if (mysqli_stmt_execute($stmt)) {
                        $sucesso = "Usuário criado com sucesso!";
                    } else {
                        $erro = "Erro ao criar usuário.";
                    }
                }
            } else {
                $id = (int) ($_POST['id'] ?? 0);

                $stmtCheck = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ? AND id != ?");
                mysqli_stmt_bind_param($stmtCheck, "si", $email, $id);
                mysqli_stmt_execute($stmtCheck);
                mysqli_stmt_store_result($stmtCheck);

                if (mysqli_stmt_num_rows($stmtCheck) > 0) {
                    $erro = "Esse e-mail já está em uso por outro usuário.";
                } elseif (!empty($senha)) {
                   
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET nome = ?, email = ?, senha = ?, admin = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "sssii", $nome, $email, $senhaHash, $admin, $id);
                    mysqli_stmt_execute($stmt);
                    $sucesso = "Usuário atualizado!";

                } else {
                    $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET nome = ?, email = ?, admin = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ssii", $nome, $email, $admin, $id);
                    mysqli_stmt_execute($stmt);
                    $sucesso = "Usuário atualizado!";
                }
            }
        }
    } elseif ($acao === 'deletar') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id === (int) $_SESSION['usuario_id']) {
            $erro = "Você não pode deletar sua própria conta.";
        } elseif ($id > 0) {
            $stmt = mysqli_prepare($conexao, "DELETE FROM usuarios WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $sucesso = "Usuário removido!";
        }
    }
}

$usuarioEditando = null;
if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
    $stmt = mysqli_prepare($conexao, "SELECT id, nome, email, admin FROM usuarios WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idEditar);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuarioEditando = mysqli_fetch_assoc($resultado);
}

$todosUsuarios = mysqli_query($conexao, "SELECT id, nome, email, admin, data_cadastro FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicom.jpeg" type="image/x-icon">
    <title>Gerenciar usuários - Biblioteca</title>
</head>
<body>
    <button id="botao-conta" class="botao-conta" aria-label="Abrir menu da conta">Conta</button>

    <aside id="painel-conta" class="painel-conta">
        <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?> (admin)</span>
        <a href="admin.php">Gerenciar livros</a>
        <a href="index.php">Voltar ao site</a>
        <a href="logout.php">Sair</a>
    </aside>

    <header>
        <h1>Gerenciar usuários</h1>
    </header>

    <main>
        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>

        <h2><?= $usuarioEditando ? 'Editar usuário' : 'Criar novo usuário' ?></h2>

        <form method="POST" action="admin_usuarios.php" class="form-admin">
            <input type="hidden" name="acao" value="<?= $usuarioEditando ? 'editar' : 'adicionar' ?>">
            <?php if ($usuarioEditando): ?>
                <input type="hidden" name="id" value="<?= $usuarioEditando['id'] ?>">
            <?php endif; ?>

            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome"
                   value="<?= $usuarioEditando ? htmlspecialchars($usuarioEditando['nome']) : '' ?>" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email"
                   value="<?= $usuarioEditando ? htmlspecialchars($usuarioEditando['email']) : '' ?>" required>

            <label for="senha">
                Senha<?= $usuarioEditando ? ' <span style="font-weight:normal;font-size:0.85rem">(deixe em branco para não alterar)</span>' : '' ?>
            </label>
            <input type="password" id="senha" name="senha"
                   <?= $usuarioEditando ? '' : 'required' ?>>

            <label style="display:flex;align-items:center;gap:0.5rem;font-weight:bold;">
                <input type="checkbox" name="admin" value="1"
                       <?= ($usuarioEditando && $usuarioEditando['admin']) ? 'checked' : '' ?>>
                Administrador
            </label>

            <button type="submit" class="btn-box">
                <?= $usuarioEditando ? 'Salvar alterações' : 'Criar usuário' ?>
            </button>

            <?php if ($usuarioEditando): ?>
                <a href="admin_usuarios.php">Cancelar edição</a>
            <?php endif; ?>
        </form>

        <h2>Usuários cadastrados</h2>

        <table class="tabela-admin">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Admin</th>
                <th>Cadastro</th>
                <th>Ações</th>
            </tr>
            <?php while ($usuario = mysqli_fetch_assoc($todosUsuarios)): ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= $usuario['admin'] ? 'Sim' : 'Não' ?></td>
                    <td><?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></td>
                    <td class="acoes-tabela">
                        <a href="admin_usuarios.php?editar=<?= $usuario['id'] ?>">Editar</a>

                        <?php if ($usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
                            <form method="POST" action="admin_usuarios.php"
                                  onsubmit="return confirm('Remover o usuário \'<?= htmlspecialchars(addslashes($usuario['nome'])) ?>\'?');">
                                <input type="hidden" name="acao" value="deletar">
                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                <button type="submit">Deletar</button>
                            </form>
                        <?php else: ?>
                            <span style="color:#aaa;font-size:0.85rem">(você)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>

    <footer>
        <p>Direitos reservados</p>
    </footer>

    <script src="js/menu.js"></script>
</body>
</html>
