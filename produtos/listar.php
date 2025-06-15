<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

$msg = $_GET['msg'] ?? null;
$erro = $_GET['erro'] ?? null;
$busca = $_GET['busca'] ?? '';

if (!empty($busca)) {
    $stmt = $conexao->prepare("SELECT * FROM produtos WHERE nome LIKE ? OR cod_barras LIKE ? ORDER BY nome");
    $termo = $busca . '%';
    $stmt->bind_param("ss", $termo, $termo);
} else {
    $stmt = $conexao->prepare("SELECT * FROM produtos ORDER BY nome");
}
$stmt->execute();
$produtos = $stmt->get_result();

if ($msg || $erro || $busca) {
    echo '<script>history.replaceState(null, "", "listar.php");</script>';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Produtos</h2>
        <div>
            <a href="../auth/logout.php" class="btn btn-outline-danger">Sair</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionar">Adicionar Produto</button>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <input type="text" name="busca" class="form-control mb-3" placeholder="Buscar por nome ou código de barras">
    </form>

    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Cód. Barras</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Grupo</th>
                <th>Classificação</th>
                <th>Fabricante</th>
                <th>Validade</th>
                <th>Quantidade</th>
                <th>Controlado</th>
                <th>Princípio Ativo</th>
                <th>Registro MS</th>
                <th>Preço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="tabelaProdutos">
        <?php while ($p = $produtos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['cod_barras']) ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['descricao'] ?? '') ?></td>
                <td><?= $p['grupo'] ?></td>

                <td><?= $p['grupo'] === 'medicamento' ? $p['classificacao'] : '—' ?></td>
                <td><?= htmlspecialchars($p['fabricante']) ?></td>
                <td><?= date('d/m/Y', strtotime($p['validade'])) ?></td>
                <td><?= $p['quantidade'] ?></td>

                <td><?= $p['grupo'] === 'medicamento' ? ($p['medicamento_controlado'] ? 'Sim' : 'Não') : '—' ?></td>
                <td><?= $p['grupo'] === 'medicamento' ? htmlspecialchars($p['principio_ativo'] ?? '') : '—' ?></td>
                <td><?= $p['grupo'] === 'medicamento' ? htmlspecialchars($p['registro_ms'] ?? '') : '—' ?></td>

                <td>R$<?= number_format($p['preco'], 2, ',', '.') ?></td>

                <td>
                    <div class="d-flex flex-column align-items-start gap-2">

                        <form action="incrementar_quantidade.php" method="POST" class="d-flex gap-1">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="number" name="quantidade" value="1" min="1" class="form-control form-control-sm" style="width: 80px;">
                            <button type="submit" class="btn btn-success btn-sm">+</button>
                        </form>

                        <form action="decrementar_quantidade.php" method="POST" class="d-flex gap-1">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="number" name="quantidade" value="1" min="1" max="<?= $p['quantidade'] ?>" class="form-control form-control-sm" style="width: 80px;">
                            <button class="btn btn-sm btn-warning" style="width: 27px">-</button>
                        </form>

                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditar"
                                data-id="<?= $p['id'] ?>"
                                data-cod="<?= htmlspecialchars($p['cod_barras']) ?>"
                                data-nome="<?= htmlspecialchars($p['nome']) ?>"
                                data-descricao="<?= htmlspecialchars($p['descricao'] ?? '') ?>"
                                data-grupo="<?= $p['grupo'] ?>"
                                data-classificacao="<?= $p['classificacao'] ?>"
                                data-fabricante="<?= htmlspecialchars($p['fabricante']) ?>"
                                data-validade="<?= $p['validade'] ?>"
                                data-quantidade="<?= $p['quantidade'] ?>"
                                data-controlado="<?= $p['medicamento_controlado'] ?>"
                                data-principio="<?= htmlspecialchars($p['principio_ativo'] ?? '') ?>"
                                data-ms="<?= htmlspecialchars($p['registro_ms'] ?? '') ?>"
                                data-preco="<?= $p['preco'] ?>"
                            >Editar
                            </button>

                            <button class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#modalExcluir"
                                data-id="<?= $p['id'] ?>"
                                data-nome="<?= htmlspecialchars($p['nome']) ?>"
                            >Excluir
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Modal Adicionar -->
    <div class="modal fade" id="modalAdicionar" tabindex="-1" aria-labelledby="modalAdicionarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> 
            <form action="adicionar_produto.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarLabel">Adicionar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label>Código de Barras*</label>
                        <input type="text" name="cod_barras" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Nome*</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label>Descrição</label>
                        <textarea name="descricao" class="form-control"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label>Grupo*</label>
                        <select name="grupo" class="form-select" id="grupoSelect" required>
                            <option value="">Selecione</option>
                            <option value="medicamento">Medicamento</option>
                            <option value="perfumaria">Perfumaria</option>
                            <option value="diversos">Diversos</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Classificação</label>
                        <select name="classificacao" class="form-select" id="classificacaoField">
                            <option value="">—</option>
                            <option value="generico">Genérico</option>
                            <option value="etico">Ético</option>
                            <option value="similar">Similar</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Fabricante*</label>
                        <input type="text" name="fabricante" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Validade*</label>
                        <input type="date" name="validade" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Quantidade*</label>
                        <input type="number" name="quantidade" class="form-control" min="0" required>
                    </div>

                    <div class="col-md-6">
                        <label>Controlado</label>
                        <select name="medicamento_controlado" class="form-select" id="controladoField">
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Princípio Ativo</label>
                        <input type="text" name="principio_ativo" class="form-control" id="principioAtivoField">
                    </div>

                    <div class="col-md-6">
                        <label>Registro MS</label>
                        <input type="text" name="registro_ms" class="form-control" id="registroMSField">
                    </div>

                    <div class="col-md-6">
                        <label>Preço*</label>
                        <input type="number" step="0.01" name="preco" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="editar_produto.php" method="POST" class="modal-content">
                <input type="hidden" name="id" id="edit-id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label>Código de Barras*</label>
                        <input type="text" name="cod_barras" id="edit-cod" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Nome*</label>
                        <input type="text" name="nome" id="edit-nome" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label>Descrição</label>
                        <textarea name="descricao" id="edit-descricao" class="form-control"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label>Grupo*</label>
                        <select name="grupo" class="form-select" id="edit-grupo" required>
                            <option value="">Selecione</option>
                            <option value="medicamento">Medicamento</option>
                            <option value="perfumaria">Perfumaria</option>
                            <option value="diversos">Diversos</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Classificação</label>
                        <select name="classificacao" class="form-select" id="edit-classificacao">
                            <option value="">—</option>
                            <option value="generico">Genérico</option>
                            <option value="etico">Ético</option>
                            <option value="similar">Similar</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Fabricante*</label>
                        <input type="text" name="fabricante" id="edit-fabricante" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Validade*</label>
                        <input type="date" name="validade" id="edit-validade" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Quantidade*</label>
                        <input type="number" name="quantidade" id="edit-quantidade" class="form-control" min="0" required>
                    </div>

                    <div class="col-md-6">
                        <label>Controlado</label>
                        <select name="medicamento_controlado" class="form-select" id="edit-controlado">
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Princípio Ativo</label>
                        <input type="text" name="principio_ativo" id="edit-principio" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Registro MS</label>
                        <input type="text" name="registro_ms" id="edit-ms" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Preço*</label>
                        <input type="number" step="0.01" name="preco" id="edit-preco" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Excluir -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <form action="deletar_produto.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Tem certeza que deseja excluir <strong id="excluirNome"></strong>?</p>
                    <input type="hidden" name="id" id="excluirId">
                </div>
                
                <div class="modal-footer">
                    <button class="btn btn-danger">Excluir</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEditar = document.getElementById('modalEditar');
            modalEditar.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const fields = [
                'id', 'cod', 'nome', 'descricao', 'grupo', 'classificacao',
                'fabricante', 'validade', 'quantidade', 'controlado',
                'principio', 'ms', 'preco'
                ];

                fields.forEach(f => {
                    const input = document.getElementById(`edit-${f}`);
                    if (!input) return;
                    const valor = button.getAttribute(`data-${f}`);
                    if (input.type === 'checkbox') {
                        input.checked = valor === '1';
                    } else {
                        input.value = valor || '';
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalExcluir = document.getElementById('modalExcluir');
            modalExcluir.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');

                document.getElementById('excluirId').value = id;
                document.getElementById('excluirNome').textContent = nome;
            });
        });
    </script>

</body>

</html>
