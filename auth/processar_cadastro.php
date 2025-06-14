<?php
require_once '../db/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, senha) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome, $senha);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }
}