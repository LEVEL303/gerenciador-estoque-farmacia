<?php
session_start();
require_once 'db/conexao.php';

if (isset($_SESSION['usuario'])) {
    header('Location: produtos/listar.php');
    exit;
}

$sql = "SELECT COUNT(*) as total FROM usuarios";
$resultado = $conexao->query($sql);
$dados = $resultado->fetch_assoc();

if ($dados['total'] == 0) {
    header('Location: auth/cadastro.php');
    exit;
} else {
    header('Location: auth/login.php');
    exit;
}
