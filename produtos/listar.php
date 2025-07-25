<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}
$id_usuario = $_SESSION['usuario'];

$msg = $_GET['msg'] ?? null;
$erro = $_GET['erro'] ?? null;
$busca = $_GET['busca'] ?? '';

if (!empty($busca)) {
    $stmt = $conexao->prepare("SELECT * FROM produtos WHERE id_usuario = ? AND (nome LIKE ? OR cod_barras LIKE ?) ORDER BY nome");
    $termo = $busca . '%';
    $stmt->bind_param("iss", $id_usuario, $termo, $termo);
} else {
    $stmt = $conexao->prepare("SELECT * FROM produtos WHERE id_usuario = ? ORDER BY nome");
    $stmt->bind_param("i", $id_usuario);
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
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionar">Adicionar
                Produto</button>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalVenderItem">Vender
                Item</button>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <input type="text" name="busca" class="form-control mb-3" placeholder="Buscar por nome ou código de barras"
            value="<?= htmlspecialchars($busca) ?>">
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

    <div class="table-responsive">
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
                                <button class="btn btn-sm btn-warning w-100" data-bs-toggle="modal"
                                    data-bs-target="#modalEditar" data-id="<?= $p['id'] ?>"
                                    data-cod="<?= htmlspecialchars($p['cod_barras']) ?>"
                                    data-nome="<?= htmlspecialchars($p['nome']) ?>"
                                    data-descricao="<?= htmlspecialchars($p['descricao'] ?? '') ?>"
                                    data-grupo="<?= $p['grupo'] ?>" data-classificacao="<?= $p['classificacao'] ?>"
                                    data-fabricante="<?= htmlspecialchars($p['fabricante']) ?>"
                                    data-validade="<?= $p['validade'] ?>" data-quantidade="<?= $p['quantidade'] ?>"
                                    data-controlado="<?= $p['medicamento_controlado'] ?>"
                                    data-principio="<?= htmlspecialchars($p['principio_ativo'] ?? '') ?>"
                                    data-ms="<?= htmlspecialchars($p['registro_ms'] ?? '') ?>"
                                    data-preco="<?= $p['preco'] ?>">Editar
                                </button>

                                <button class="btn btn-sm btn-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#modalExcluir" data-id="<?= $p['id'] ?>"
                                    data-nome="<?= htmlspecialchars($p['nome']) ?>">Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

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
                        <input type="number" name="quantidade" id="edit-quantidade" class="form-control" min="0"
                            required>
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

    <!-- Modal Vender Item -->
    <div class="modal fade" id="modalVenderItem" tabindex="-1" aria-labelledby="modalVenderItemLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- Cabeçalho com título e botões para trocar abas -->
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="modalVenderItemLabel">Venda</h5>
                    <div>
                        <button type="button" class="btn btn-primary" id="btnAbaVenda"
                            onclick="mostrarAba('venda')">Vender Item</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnAbaRegistro"
                            onclick="mostrarAba('registro'); carregarRegistroVendas();">Registro de Vendas</button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- Corpo do modal com abas -->
                <div class="modal-body">

                    <!-- Aba de Venda (ativa por padrão) -->
                    <form action="../vendas/processar_venda.php" method="POST" id="abaVenda" style="display: block;">
                        <!-- parte para buscar o produto -->
                        <div class="mb-3">
                            <label for="buscaProduto" class="form-label">Nome ou código do produto</label>
                            <input type="text" class="form-control" id="buscaProduto" oninput="buscarProdutos()">
                        </div>

                        <!-- tabela com o resultado da busca do produto -->
                        <div id="resultadoProdutos" class="mb-3" style="max-height: 300px; overflow-y: auto;"></div>

                        <!-- para armazenar o id do produto, tudo escondido -->
                        <input type="hidden" id="produtoSelecionado" name="produto_id" required>

                        <!-- Quantidade que vai vender -->
                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" class="form-control" name="quantidade" id="quantidade" min="1"
                                required>
                        </div>

                        <div class="modal-footer p-0 pt-3">
                            <button type="submit" class="btn btn-success">Registrar Venda</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>

                    <!-- Aba de Registro (oculta inicialmente) -->
                    <div id="abaRegistro" style="display: none;">
                        <div id="listaRegistroVendas" class="table-responsive">
                            <!-- Lista de vendas será carregada aqui via AJAX -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function mostrarAba(aba) {
            // Oculta as abas
            document.getElementById('abaVenda').style.display = 'none';
            document.getElementById('abaRegistro').style.display = 'none';

            // Remove estilos ativos dos botões
            document.getElementById('btnAbaVenda').classList.remove('btn-primary');
            document.getElementById('btnAbaVenda').classList.add('btn-outline-secondary');
            document.getElementById('btnAbaRegistro').classList.remove('btn-primary');
            document.getElementById('btnAbaRegistro').classList.add('btn-outline-secondary');

            if (aba === 'venda') {
                document.getElementById('abaVenda').style.display = 'block';
                document.getElementById('btnAbaVenda').classList.add('btn-primary');
                document.getElementById('btnAbaVenda').classList.remove('btn-outline-secondary');
            } else {
                document.getElementById('abaRegistro').style.display = 'block';
                document.getElementById('btnAbaRegistro').classList.add('btn-primary');
                document.getElementById('btnAbaRegistro').classList.remove('btn-outline-secondary');
                carregarRegistroVendas();
            }
        }

        function carregarRegistroVendas() {
            fetch('../vendas/listar_vendas.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('listaVendas').innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('listaVendas').innerHTML = '<p class="text-danger">Erro ao carregar vendas.</p>';
                });
        }
    </script>

    <script>
function carregarRegistroVendas() {
    fetch('../vendas/obter_registros.php')
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar registros');
            return response.text(); // ou .json() se preferir enviar JSON
        })
        .then(data => {
            document.getElementById('listaRegistroVendas').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('listaRegistroVendas').innerHTML =
                `<div class="alert alert-danger">Erro ao carregar registros: ${error.message}</div>`;
        });
}
</script>


    <script>
        function buscarProdutos() {
            const termo = document.getElementById('buscaProduto').value;

            if (termo.length < 2) {
                document.getElementById('resultadoProdutos').innerHTML = '';
                return;
            }

            fetch('../vendas/buscar_produtos.php?termo=' + encodeURIComponent(termo))
                .then(res => res.json())
                .then(produtos => {
                    let html = '';

                    if (produtos.length === 0) {
                        html = '<p class="text-muted">Nenhum produto encontrado.</p>';
                    } else {
                        html = produtos.map(p => `
                    <label class="border rounded p-2 d-block mb-2">
                        <input type="radio" name="produto_opcao" value="${p.id}" onclick="document.getElementById('produtoSelecionado').value = ${p.id}">
                        <strong>${p.nome}</strong> — ${p.fabricante}<br>
                        Classificação: ${p.classificacao || '-'} |
                        Controlado: ${p.medicamento_controlado == 1 ? 'Sim' : 'Não'}<br>
                        Preço: R$ ${parseFloat(p.preco).toFixed(2)}
                    </label>
                `).join('');
                    }

                    document.getElementById('resultadoProdutos').innerHTML = html;
                })
                .catch(erro => {
                    console.error('Erro ao buscar produtos:', erro);
                    document.getElementById('resultadoProdutos').innerHTML = '<p class="text-danger">Erro na busca. Tente novamente.</p>';
                });
        }
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