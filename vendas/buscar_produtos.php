<?php
session_start();
require_once '../db/conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}
$id_usuario = $_SESSION['usuario'];
$busca = $_GET['busca'] ?? '';

if (strlen($busca) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $conexao->prepare("SELECT id, nome, quantidade, preco FROM produtos WHERE id_usuario = ? AND quantidade > 0 AND (nome LIKE ? OR cod_barras LIKE ?) LIMIT 10");
$like_termo = $busca . '%';
$stmt->bind_param('iss', $id_usuario, $like_termo, $like_termo);
$stmt->execute();
$result = $stmt->get_result();
$produtos = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($produtos);

$stmt->close();
$conexao->close();