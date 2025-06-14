<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    $stmt = $conexao->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: listar.php?msg=Produto excluído com sucesso');
    } else {
        header('Location: listar.php?erro=Erro ao excluir produto');
    }

    $stmt->close();
}
$conexao->close();