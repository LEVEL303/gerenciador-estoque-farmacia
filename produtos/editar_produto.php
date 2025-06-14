<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $cod_barras = $_POST['cod_barras'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $grupo = $_POST['grupo'] ?? '';
    $classificacao = !empty($_POST['classificacao']) ? $_POST['classificacao'] : null;
    $fabricante = $_POST['fabricante'] ?? '';
    $validade = $_POST['validade'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    $medicamento_controlado = $_POST['medicamento_controlado'] ?? 0;
    $principio_ativo = !empty($_POST['principio_ativo']) ? $_POST['principio_ativo'] : null;
    $registro_ms = !empty($_POST['registro_ms']) ? $_POST['registro_ms'] : null;
    $preco = $_POST['preco'] ?? 0;

    $stmt = $conexao->prepare("UPDATE produtos SET cod_barras = ?, nome = ?, descricao = ?, grupo = ?, classificacao = ?, fabricante = ?, validade = ?, quantidade = ?, medicamento_controlado = ?, principio_ativo = ?, registro_ms = ?, preco = ? WHERE id = ?");
    $stmt->bind_param('sssssssiissdi', $cod_barras, $nome, $descricao, $grupo, $classificacao, $fabricante, $validade, $quantidade, $medicamento_controlado, $principio_ativo, $registro_ms, $preco, $id);

    if ($stmt->execute()) {
        header("Location: listar.php?msg=Produto atualizado com sucesso");
    } else {
        header("Location: listar.php?erro=Erro ao atualizar produto");
    }

    $stmt->close();
}
$conexao->close();