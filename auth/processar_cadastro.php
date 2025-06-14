<?php
require_once '../db/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, senha) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome, $senha);

    if ($stmt->execute()) {
        header("Location: login.php");
    } else {
        header('Location: cadastro.php?erro=Erro ao cadastrar');
    }
    $stmt->close();
}
$conexao->close();