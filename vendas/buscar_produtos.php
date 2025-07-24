<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$id_usuario = $_SESSION['usuario'];
$termo = $_GET['termo'] ?? '';

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

// Busca produto pelo nome ou codigo de barrras no banco
$stmt = $conexao->prepare("SELECT id, nome, fabricante, classificacao, medicamento_controlado, preco FROM produtos WHERE id_usuario = ? AND (nome LIKE ? OR cod_barras LIKE ?) ORDER BY nome");
$like_termo = $termo . '%';
$stmt->bind_param('iss', $id_usuario, $like_termo, $like_termo);
$stmt->execute();
$result = $stmt->get_result();

$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

echo json_encode($produtos);
