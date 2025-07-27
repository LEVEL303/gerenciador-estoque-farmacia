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

<body class="p-4" style="background-color: #ddecfbff">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex flex-row align-items-center gap-2">
        <h2 style="font-weight: bold">Produtos</h2>
        <img src="../assets/icon.svg" alt="Logo" class="img-fluid" style="max-height: 40px;">
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionar">Adicionar Produto</button>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalVendas">Vendas</button>
            <a href="../auth/logout.php" class="btn bg-danger text-white">Sair</a>
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
    <div class="modal fade" id="modalAdicionar" tabindex="-1" aria-labelledby="modalAdicionarLabel" aria-hidden="true" >
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
    
    <!-- Modal Vendas -->
    <div class="modal fade" id="modalVendas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestão de Vendas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="vendasTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="registrar-tab" data-bs-toggle="tab" data-bs-target="#registrar-pane" type="button" role="tab">Registrar Venda</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="consultar-tab" data-bs-toggle="tab" data-bs-target="#consultar-pane" type="button" role="tab">Consultar Vendas</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="vendasTabContent">
                        <div class="tab-pane fade show active" id="registrar-pane" role="tabpanel">
                            <form action="../vendas/processar_venda.php" method="POST" id="formVenda">
                                <div class="p-3">
                                    <div class="mb-3">
                                        <label for="buscaProdutoVenda" class="form-label">Buscar Produto (Nome ou Cód. Barras)</label>
                                        <input type="text" id="buscaProdutoVenda" class="form-control" autocomplete="off">
                                        <div id="resultadoBusca" class="list-group mt-1"></div>
                                    </div>
                                    <h5>Itens da Venda</h5>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Produto</th>
                                                    <th style="width: 120px;">Qtd.</th>
                                                    <th>Preço Unit.</th>
                                                    <th>Subtotal</th>
                                                    <th>Ação</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itensVenda"></tbody>
                                        </table>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-end">
                                        <h4>Total: R$ <span id="totalVenda">0,00</span></h4>
                                    </div>
                                    <div class="modal-footer mt-3">
                                        <button type="submit" class="btn btn-success">Finalizar Venda</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="consultar-pane" role="tabpanel">
                            <div class="p-3">
                                <div class="row g-3 align-items-end mb-3">
                                    <div class="col-md-4">
                                        <label for="filtro_data_inicio" class="form-label">Data Início</label>
                                        <input type="date" id="filtro_data_inicio" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="filtro_data_fim" class="form-label">Data Fim</label>
                                        <input type="date" id="filtro_data_fim" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <button id="btn_filtrar_vendas" class="btn btn-primary w-100">Filtrar</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Data e Hora</th>
                                                <th>Total</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabelaVendas"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes de Venda -->
    <div class="modal fade" id="modalDetalhesVenda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Venda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Data:</strong> <span id="detalheVendaData"></span></p>
                    <p><strong>Total:</strong> <span id="detalheVendaTotal"></span></p>
                    <hr>
                    <h6>Itens:</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="itensDetalheVenda"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- LÓGICA PARA MODAL EDITAR ---
            const modalEditar = document.getElementById('modalEditar');
            if (modalEditar) {
                modalEditar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const fields = ['id', 'cod', 'nome', 'descricao', 'grupo', 'classificacao', 'fabricante', 'validade', 'quantidade', 'controlado', 'principio', 'ms', 'preco'];
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
            }

            // --- LÓGICA PARA MODAL EXCLUIR ---
            const modalExcluir = document.getElementById('modalExcluir');
            if (modalExcluir) {
                modalExcluir.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const nome = button.getAttribute('data-nome');
                    document.getElementById('excluirId').value = id;
                    document.getElementById('excluirNome').textContent = nome;
                });
            }

            // --- LÓGICA PARA REGISTRAR VENDA ---
            const buscaInput = document.getElementById('buscaProdutoVenda');
            const resultadoBuscaDiv = document.getElementById('resultadoBusca');
            const itensVendaTbody = document.getElementById('itensVenda');
            const totalVendaSpan = document.getElementById('totalVenda');
            const modalVendas = document.getElementById('modalVendas');
            let itensNoCarrinho = new Set();

            if (buscaInput) {
                buscaInput.addEventListener('input', function() {
                    const termo = this.value;
                    resultadoBuscaDiv.innerHTML = '';
                    if (termo.length < 2) return;

                    fetch(`../vendas/buscar_produtos.php?busca=${termo}`)
                        .then(response => response.ok ? response.json() : Promise.reject('Erro de rede'))
                        .then(produtos => {
                            produtos.forEach(p => {
                                if (itensNoCarrinho.has(p.id.toString())) return;
                                const item = document.createElement('a');
                                item.href = '#';
                                item.className = 'list-group-item list-group-item-action';
                                item.textContent = `${p.nome} (Estoque: ${p.quantidade})`;
                                item.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    adicionarItemVenda(p);
                                    buscaInput.value = '';
                                    resultadoBuscaDiv.innerHTML = '';
                                });
                                resultadoBuscaDiv.appendChild(item);
                            });
                        })
                        .catch(error => console.error('Falha na busca:', error));
                });

                function adicionarItemVenda(produto) {
                    itensNoCarrinho.add(produto.id.toString());
                    const precoFormatado = parseFloat(produto.preco).toFixed(2);
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-id', produto.id);
                    tr.innerHTML = `
                        <td>${produto.nome}<input type="hidden" name="produtos[${produto.id}][id]" value="${produto.id}"></td>
                        <td><input type="number" name="produtos[${produto.id}][qtd]" class="form-control form-control-sm qtd-venda" value="1" min="1" max="${produto.quantidade}" required></td>
                        <td class="preco-unitario">R$ ${precoFormatado.replace('.', ',')}</td>
                        <td class="subtotal">R$ ${precoFormatado.replace('.', ',')}</td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remover">Remover</button></td>
                    `;
                    itensVendaTbody.appendChild(tr);
                    atualizarTotal();
                }

                function atualizarTotal() {
                    let total = 0;
                    itensVendaTbody.querySelectorAll('tr').forEach(tr => {
                        const qtd = parseInt(tr.querySelector('.qtd-venda').value);
                        const preco = parseFloat(tr.querySelector('.preco-unitario').textContent.replace('R$ ', '').replace(',', '.'));
                        if (!isNaN(qtd) && qtd >= 1) {
                            const subtotal = preco * qtd;
                            tr.querySelector('.subtotal').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
                            total += subtotal;
                        }
                    });
                    totalVendaSpan.textContent = total.toFixed(2).replace('.', ',');
                }

                itensVendaTbody.addEventListener('click', (e) => {
                    if (e.target.classList.contains('btn-remover')) {
                        const tr = e.target.closest('tr');
                        itensNoCarrinho.delete(tr.getAttribute('data-id'));
                        tr.remove();
                        atualizarTotal();
                    }
                });

                itensVendaTbody.addEventListener('input', (e) => {
                    if (e.target.classList.contains('qtd-venda')) {
                        atualizarTotal();
                    }
                });

                modalVendas.addEventListener('hidden.bs.modal', () => {
                    itensVendaTbody.innerHTML = '';
                    totalVendaSpan.textContent = '0,00';
                    itensNoCarrinho.clear();
                    document.getElementById('formVenda').reset();
                    resultadoBuscaDiv.innerHTML = '';
                });
            }

            // --- LÓGICA PARA CONSULTAR VENDAS ---
            const consultarTab = document.getElementById('consultar-tab');
            const tabelaVendasBody = document.getElementById('tabelaVendas');
            const btnFiltrar = document.getElementById('btn_filtrar_vendas');
            const modalDetalhesVenda = new bootstrap.Modal(document.getElementById('modalDetalhesVenda'));

            function carregarVendas() {
                const dataInicio = document.getElementById('filtro_data_inicio').value;
                const dataFim = document.getElementById('filtro_data_fim').value;
                tabelaVendasBody.innerHTML = '<tr><td colspan="4">Carregando...</td></tr>';

                fetch(`../vendas/consultar_vendas.php?inicio=${dataInicio}&fim=${dataFim}`)
                    .then(response => response.ok ? response.json() : Promise.reject('Erro de rede'))
                    .then(vendas => {
                        tabelaVendasBody.innerHTML = '';
                        if (vendas.length === 0) {
                            tabelaVendasBody.innerHTML = '<tr><td colspan="4">Nenhuma venda encontrada.</td></tr>';
                            return;
                        }
                        vendas.forEach(venda => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${venda.data_formatada}</td>
                                <td>R$ ${venda.total_formatado}</td>
                                <td><button class="btn btn-sm btn-info btn-ver-detalhes" data-id="${venda.id}" data-data="${venda.data_formatada}" data-total="R$ ${venda.total_formatado}">Ver Detalhes</button></td>
                            `;
                            tabelaVendasBody.appendChild(tr);
                        });
                    })
                    .catch(error => {
                        console.error('Erro ao carregar vendas:', error);
                        tabelaVendasBody.innerHTML = '<tr><td colspan="4" class="text-danger">Erro ao carregar vendas.</td></tr>';
                    });
            }

            if (consultarTab) {
                consultarTab.addEventListener('show.bs.tab', carregarVendas, { once: true });
                btnFiltrar.addEventListener('click', carregarVendas);
                
                tabelaVendasBody.addEventListener('click', (e) => {
                    if (e.target && e.target.classList.contains('btn-ver-detalhes')) {
                        const botao = e.target;
                        const vendaId = botao.dataset.id;
                        
                        document.getElementById('detalheVendaData').textContent = botao.dataset.data;
                        document.getElementById('detalheVendaTotal').textContent = botao.dataset.total;
                        const itensTbody = document.getElementById('itensDetalheVenda');
                        itensTbody.innerHTML = '<tr><td colspan="4">Carregando itens...</td></tr>';
                        modalDetalhesVenda.show();
                        
                        fetch(`../vendas/detalhes_venda.php?id=${vendaId}`)
                        .then(response => response.ok ? response.json() : Promise.reject('Erro de rede'))
                        .then(itens => {
                            itensTbody.innerHTML = '';
                            itens.forEach(item => {
                                const subtotal = (item.quantidade * item.preco_unitario).toFixed(2).replace('.', ',');
                                const preco = parseFloat(item.preco_unitario).toFixed(2).replace('.', ',');
                                itensTbody.innerHTML += `<tr><td>${item.nome}</td><td>${item.quantidade}</td><td>R$ ${preco}</td><td>R$ ${subtotal}</td></tr>`;
                            });
                        })
                        .catch(error => {
                            console.error('Erro ao carregar detalhes da venda:', error);
                            itensTbody.innerHTML = '<tr><td colspan="4" class="text-danger">Erro ao carregar itens.</td></tr>';
                        });
                    }
                });
            }
        });
    </script>

</body>

</html>