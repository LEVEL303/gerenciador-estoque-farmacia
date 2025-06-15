<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $quantidade = $_POST['quantidade'];

    if ($quantidade > 0) {
        $stmt = $conexao->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
        $stmt->bind_param('ii', $quantidade, $id);

        if ($stmt->execute()) {
            header('Location: listar.php?msg=Quantidade incrementada com sucesso');
        } else {
            header('Location: listar.php?erro=Erro ao incrementar quantidade');
        }

        $stmt->close();
    } else {
        header('Location: listar.php?erro=Quantidade inválida');
    }
}
$conexao->close();