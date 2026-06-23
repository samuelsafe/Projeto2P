<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Você precisa estar logado para salvar.']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$livroId = isset($_POST['livro_id']) ? (int) $_POST['livro_id'] : 0;

if ($livroId <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Livro inválido.']);
    exit;
}

$stmt = mysqli_prepare($conexao, "SELECT id FROM favoritos WHERE usuario_id = ? AND livro_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $usuarioId, $livroId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $stmtDel = mysqli_prepare($conexao, "DELETE FROM favoritos WHERE usuario_id = ? AND livro_id = ?");
    mysqli_stmt_bind_param($stmtDel, "ii", $usuarioId, $livroId);
    mysqli_stmt_execute($stmtDel);

    echo json_encode(['sucesso' => true, 'salvo' => false]);
} else {
    $stmtIns = mysqli_prepare($conexao, "INSERT INTO favoritos (usuario_id, livro_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmtIns, "ii", $usuarioId, $livroId);
    mysqli_stmt_execute($stmtIns);

    echo json_encode(['sucesso' => true, 'salvo' => true]);
}
?>
