<?php
session_start();
require_once '../db/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $senha = $_POST['senha'];

    $stmt = $conexao->prepare("SELECT id, senha FROM usuarios WHERE nome = (?)");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario['id'];
            header('Location: ../produtos/listar.php');
            exit;
        }
    }

    echo "Usuário e/ou senha inválidos";
}