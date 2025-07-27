<?php
session_start();
require_once '../db/conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}
$id_usuario = $_SESSION['usuario'];
$id_venda = $_GET['id'] ?? 0;

if ($id_venda < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $conexao->prepare("
    SELECT 
        iv.quantidade,
        p.nome,
        p.preco AS preco_unitario 
    FROM itens_venda AS iv
    JOIN produtos AS p ON iv.produto_id = p.id
    JOIN vendas AS v ON iv.venda_id = v.id
    WHERE iv.venda_id = ? AND v.id_usuario = ?
");

$stmt->bind_param("ii", $id_venda, $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$itens = $resultado->fetch_all(MYSQLI_ASSOC);

echo json_encode($itens);

$stmt->close();
$conexao->close();
