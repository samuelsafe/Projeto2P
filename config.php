<?php
session_start();

$host   = "localhost";
$usuario_db = "root";
$senha_db   = "";
$banco  = "login_sistema";

$conexao = mysqli_connect($host, $usuario_db, $senha_db, $banco);

if (!$conexao) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}
?>
