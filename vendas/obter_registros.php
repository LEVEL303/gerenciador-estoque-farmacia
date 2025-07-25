<?php
require_once __DIR__ . '/../db/conexao.php';

// Query SQL
$sql = "
    SELECT 
        p.nome AS produto_nome,
        p.cod_barras,
        v.data,
        u.nome AS usuario_nome,
        iv.quantidade,
        ROUND(p.preco * iv.quantidade, 2) AS valor_venda
    FROM itens_venda iv
    JOIN vendas v ON iv.venda_id = v.id
    JOIN produtos p ON iv.produto_id = p.id
    JOIN usuarios u ON v.id_usuario = u.id
    ORDER BY v.data DESC
";

// Executa a consulta
$resultado = $conexao->query($sql);

// Verifica e exibe os dados
if ($resultado && $resultado->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-hover">';
    echo '<thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Código de Barras</th>
                <th>Data e Hora</th>
                <th>Usuário</th>
                <th>Quantidade</th>
                <th>Valor da Venda (R$)</th>
            </tr>
          </thead><tbody>';

    while ($r = $resultado->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['produto_nome']) . '</td>';
        echo '<td>' . htmlspecialchars($r['cod_barras']) . '</td>';
        echo '<td>' . date('d/m/Y H:i', strtotime($r['data'])) . '</td>';
        echo '<td>' . htmlspecialchars($r['usuario_nome']) . '</td>';
        echo '<td>' . $r['quantidade'] . '</td>';
        echo '<td>' . number_format($r['valor_venda'], 2, ',', '.') . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
} else {
    echo '<p class="text-muted">Nenhuma venda registrada ainda.</p>';
}
?>
