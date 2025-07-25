<?php
session_start();
require_once '../db/conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}
$id_usuario = $_SESSION['usuario'];

$data_inicio = $_GET['inicio'] ?? '';
$data_fim = $_GET['fim'] ?? '';

$sql = "SELECT id, data, total FROM vendas WHERE id_usuario = ?";
$params = [$id_usuario];
$types = 'i';

if (!empty($data_inicio)) {
    $sql .= " AND DATE(data) >= ?";
    $params[] = $data_inicio;
    $types .= 's';
}

if (!empty($data_fim)) {
    $sql .= " AND DATE(data) <= ?";
    $params[] = $data_fim;
    $types .= 's';
}

$sql .= " ORDER BY data DESC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

$vendas = [];
while ($venda = $resultado->fetch_assoc()) {
    $venda['data_formatada'] = date('d/m/Y H:i:s', strtotime($venda['data']));
    $venda['total_formatado'] = number_format($venda['total'], 2, ',', '.');
    $vendas[] = $venda;
}

echo json_encode($vendas);

$stmt->close();
$conexao->close();
