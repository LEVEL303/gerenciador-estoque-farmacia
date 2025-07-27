<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['usuario'];
    $produtos_venda = $_POST['produtos'] ?? [];

    if (empty($produtos_venda)) {
        header('Location: ../produtos/listar.php?erro=Nenhum produto foi adicionado à venda');
        exit;
    }

    $conexao->begin_transaction();

    $total_venda = 0;
    $erros = [];

    foreach ($produtos_venda as $item) {
        $id_produto = $item['id'];
        $qtd_vendida = (int)$item['qtd'];

        if ($qtd_vendida < 1) {
            $erros[] = "Quantidade inválida";
            continue;
        }

        $stmt_verifica = $conexao->prepare("SELECT preco, quantidade FROM produtos WHERE id = ? AND id_usuario = ? FOR UPDATE");
        $stmt_verifica->bind_param("ii", $id_produto, $id_usuario);
        $stmt_verifica->execute();
        $produto_db = $stmt_verifica->get_result()->fetch_assoc();
        $stmt_verifica->close();

        if (!$produto_db || $produto_db['quantidade'] < $qtd_vendida) {
            $erros[] = "Estoque insuficiente para registrar a venda";
            continue;
        }

        $total_venda += $produto_db['preco'] * $qtd_vendida;
    }

    if (!empty($erros)) {
        $conexao->rollback();
        header('Location: ../produtos/listar.php?erro=' . urlencode(implode(' ', $erros)));
        exit;
    }

    $stmt_venda = $conexao->prepare("INSERT INTO vendas (total, id_usuario) VALUES (?, ?)");
    $stmt_venda->bind_param("di", $total_venda, $id_usuario);

    if (!$stmt_venda->execute()) {
        $conexao->rollback();
        header('Location: ../produtos/listar.php?erro=Falha ao registrar a venda principal.');
        exit;
    }
    $id_venda = $conexao->insert_id;
    $stmt_venda->close();

    foreach ($produtos_venda as $item) {
        $id_produto = $item['id'];
        $qtd_vendida = (int)$item['qtd'];

        $stmt_item = $conexao->prepare("INSERT INTO itens_venda (produto_id, venda_id, quantidade) VALUES (?, ?, ?)");
        $stmt_item->bind_param("iii", $id_produto, $id_venda, $qtd_vendida);
        $sucesso_item = $stmt_item->execute();
        $stmt_item->close();
        
        if (!$sucesso_item) {
            $conexao->rollback();
            header('Location: ../produtos/listar.php?erro=Falha ao registrar um item da venda.');
            exit;
        }

        $stmt_update = $conexao->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
        $stmt_update->bind_param("ii", $qtd_vendida, $id_produto);
        $sucesso_update = $stmt_update->execute();
        $stmt_update->close();

        if (!$sucesso_update) {
            $conexao->rollback();
            header('Location: ../produtos/listar.php?erro=Falha ao atualizar o estoque.');
            exit;
        }
    }
    
    $conexao->commit();
    $conexao->close();
    header('Location: ../produtos/listar.php?msg=Venda registrada com sucesso!');
}
$conexao->close();
