<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

$id_usuario = $_SESSION['usuario'];
$id_produto = $_POST['produto_id'] ?? null;
$quantidade = $_POST['quantidade'] ?? null;

// Verificações básicas 
if (!$id_produto || !$quantidade || $quantidade <= 0) {
    header('Location: ../produtos/listar.php?erro=Dados inválidos para a venda');
    exit;
}

// Verificar se o produto existe e se há estoque suficiente
$stmt = $conexao->prepare("SELECT nome, quantidade, preco FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header('Location: ../produtos/listar.php?erro=Produto não encontrado');
    exit;
}

$produto = $resultado->fetch_assoc();

if ($produto['quantidade'] < $quantidade) {
    header('Location: ../produtos/listar.php?erro=Estoque insuficiente para o produto ' . urlencode($produto['nome']));
    exit;
}

// Calcular o total da venda
$total = $produto['preco'] * $quantidade;

// Iniciar transação
$conexao->begin_transaction();

try {
    // 1. Inserir na tabela de vendas
    $stmtVenda = $conexao->prepare("INSERT INTO vendas (total, id_usuario) VALUES (?, ?)");
    $stmtVenda->bind_param("di", $total, $id_usuario);
    $stmtVenda->execute();
    $id_venda = $stmtVenda->insert_id;

    // 2. Inserir item da venda
    $stmtItem = $conexao->prepare("INSERT INTO itens_venda (produto_id, venda_id, quantidade) VALUES (?, ?, ?)");
    $stmtItem->bind_param("iii", $id_produto, $id_venda, $quantidade);
    $stmtItem->execute();

    // 3. Atualizar o estoque do produto
    $stmtAtualiza = $conexao->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
    $stmtAtualiza->bind_param("ii", $quantidade, $id_produto);
    $stmtAtualiza->execute();

    // Confirmar transação
    $conexao->commit();

    header('Location: ../produtos/listar.php?msg=Venda registrada com sucesso');
    exit;

} catch (Exception $e) {
    // Desfazer tudo em caso de erro
    $conexao->rollback();
    header('Location: ../produtos/listar.php?erro=Erro ao registrar a venda: ' . urlencode($e->getMessage()));
    exit;
}
?>
