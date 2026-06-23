<?php
require_once 'config.php';

if (empty($_SESSION['usuario_admin'])) {
    header("Location: index.php");
    exit;
}

$erro    = "";
$sucesso = "";

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

function processarUpload(array $tiposPermitidos): string|false
{
    if (empty($_FILES['imagem']['name'])) {
        return false;
    }

    $arquivo = $_FILES['imagem'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return 'erro_upload';
    }

    if (!in_array($arquivo['type'], $tiposPermitidos)) {
        return 'tipo_invalido';
    }

    if ($arquivo['size'] > 5 * 1024 * 1024) {
        return 'tamanho_excedido';
    }

    $extensao  = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeUnico = uniqid('livro_', true) . '.' . strtolower($extensao);
    $destino   = 'img/' . $nomeUnico;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        return 'erro_mover';
    }

    return $destino;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao   = $_POST['acao'];
    $titulo = trim($_POST['titulo'] ?? '');

    if ($acao === 'adicionar') {
        if (empty($titulo)) {
            $erro = "Preencha o título.";
        } else {
            $resultado = processarUpload($tiposPermitidos);

            if ($resultado === false) {
                $erro = "Selecione uma imagem para o livro.";
            } elseif ($resultado === 'tipo_invalido') {
                $erro = "Formato inválido. Use JPG, PNG, GIF ou WEBP.";
            } elseif ($resultado === 'tamanho_excedido') {
                $erro = "A imagem deve ter no máximo 5 MB.";
            } elseif (in_array($resultado, ['erro_upload', 'erro_mover'])) {
                $erro = "Erro ao salvar a imagem. Tente novamente.";
            } else {
                $imagem = $resultado;
                $stmt   = mysqli_prepare($conexao, "INSERT INTO livros (titulo, imagem) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ss", $titulo, $imagem);
                mysqli_stmt_execute($stmt);
                $sucesso = "Livro adicionado!";
            }
        }

    } elseif ($acao === 'editar') {
        $id = (int) ($_POST['id'] ?? 0);

        if (empty($titulo)) {
            $erro = "Preencha o título.";
        } else {
            $resultado = processarUpload($tiposPermitidos);

            if ($resultado === 'tipo_invalido') {
                $erro = "Formato inválido. Use JPG, PNG, GIF ou WEBP.";
            } elseif ($resultado === 'tamanho_excedido') {
                $erro = "A imagem deve ter no máximo 5 MB.";
            } elseif (in_array($resultado, ['erro_upload', 'erro_mover'])) {
                $erro = "Erro ao salvar a imagem. Tente novamente.";

            } elseif ($resultado === false) {  
                $stmt = mysqli_prepare($conexao, "UPDATE livros SET titulo = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "si", $titulo, $id);
                mysqli_stmt_execute($stmt);
                $sucesso = "Livro atualizado!";

            } else {
                $stmtImg = mysqli_prepare($conexao, "SELECT imagem FROM livros WHERE id = ?");
                mysqli_stmt_bind_param($stmtImg, "i", $id);
                mysqli_stmt_execute($stmtImg);
                $resImg    = mysqli_stmt_get_result($stmtImg);
                $livroAnt  = mysqli_fetch_assoc($resImg);

                if ($livroAnt && file_exists($livroAnt['imagem'])) {
                    unlink($livroAnt['imagem']);
                }

                $imagem = $resultado;
                $stmt   = mysqli_prepare($conexao, "UPDATE livros SET titulo = ?, imagem = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ssi", $titulo, $imagem, $id);
                mysqli_stmt_execute($stmt);
                $sucesso = "Livro atualizado!";
            }
        }

    } elseif ($acao === 'deletar') {
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id > 0) {
            $stmtImg = mysqli_prepare($conexao, "SELECT imagem FROM livros WHERE id = ?");
            mysqli_stmt_bind_param($stmtImg, "i", $id);
            mysqli_stmt_execute($stmtImg);
            $resImg   = mysqli_stmt_get_result($stmtImg);
            $livroAnt = mysqli_fetch_assoc($resImg);
            if ($livroAnt && file_exists($livroAnt['imagem'])) {
                unlink($livroAnt['imagem']);
            }

            $stmt = mysqli_prepare($conexao, "DELETE FROM livros WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $sucesso = "Livro removido!";
        }
    }
}

$livroEditando = null;
if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
    $stmt     = mysqli_prepare($conexao, "SELECT id, titulo, imagem FROM livros WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idEditar);
    mysqli_stmt_execute($stmt);
    $resultado     = mysqli_stmt_get_result($stmt);
    $livroEditando = mysqli_fetch_assoc($resultado);
}

$todosLivros = mysqli_query($conexao, "SELECT id, titulo, imagem FROM livros ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicom.jpeg" type="image/x-icon">
    <title>Gerenciar livros - Biblioteca</title>
</head>
<body>
    <button id="botao-conta" class="botao-conta" aria-label="Abrir menu da conta">Conta</button>

    <aside id="painel-conta" class="painel-conta">
        <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?> (admin)</span>
        <a href="admin_usuarios.php">Gerenciar usuários</a>
        <a href="index.php">Voltar ao site</a>
        <a href="logout.php">Sair</a>
    </aside>

    <header>
        <h1>Gerenciar livros</h1>
    </header>

    <main>
        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>

        <h2><?= $livroEditando ? 'Editar livro' : 'Adicionar novo livro' ?></h2>

        <form method="POST" action="admin.php" class="form-admin" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="<?= $livroEditando ? 'editar' : 'adicionar' ?>">
            <?php if ($livroEditando): ?>
                <input type="hidden" name="id" value="<?= $livroEditando['id'] ?>">
            <?php endif; ?>

            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo"
                   value="<?= $livroEditando ? htmlspecialchars($livroEditando['titulo']) : '' ?>" required>

            <label for="imagem">
                Imagem da capa
                <?php if ($livroEditando): ?>
                    <span style="font-weight:normal;font-size:0.85rem">(deixe em branco para manter a atual)</span>
                <?php endif; ?>
            </label>

            <?php if ($livroEditando && !empty($livroEditando['imagem'])): ?>
                <img src="<?= htmlspecialchars($livroEditando['imagem']) ?>"
                     alt="Capa atual"
                     style="width:100px;border-radius:4px;margin-bottom:4px;">
            <?php endif; ?>

            <input type="file" id="imagem" name="imagem"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   <?= $livroEditando ? '' : 'required' ?>>

            <button type="submit" class="btn-box">
                <?= $livroEditando ? 'Salvar alterações' : 'Adicionar livro' ?>
            </button>
            <?php if ($livroEditando): ?>
                <a href="admin.php">Cancelar edição</a>
            <?php endif; ?>
        </form>

        <h2>Livros cadastrados</h2>

        <table class="tabela-admin">
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Capa</th>
                <th>Ações</th>
            </tr>
            <?php while ($livro = mysqli_fetch_assoc($todosLivros)): ?>
                <tr>
                    <td><?= $livro['id'] ?></td>
                    <td><?= htmlspecialchars($livro['titulo']) ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($livro['imagem']) ?>"
                             alt="Capa"
                             style="width:60px;border-radius:4px;">
                    </td>
                    <td class="acoes-tabela">
                        <a href="admin.php?editar=<?= $livro['id'] ?>">Editar</a>

                        <form method="POST" action="admin.php"
                              onsubmit="return confirm('Remover este livro?');">
                            <input type="hidden" name="acao" value="deletar">
                            <input type="hidden" name="id" value="<?= $livro['id'] ?>">
                            <button type="submit">Deletar</button>
                        </form>
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
