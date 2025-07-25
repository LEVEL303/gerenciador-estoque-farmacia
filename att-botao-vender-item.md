
documento de atualização 1 de ryam

excluir esse documento apos atualização da documentação 

#  Documentação da Funcionalidade: "Vender Item"
> Sistema: **Gerenciador de Estoque de Farmácia**  
> Recurso implementado: **Venda de Produtos com Registro em Banco de Dados**  

##  Estrutura de Pastas Atualizada

```
gerenciador-estoque-farmacia/
├── auth/
│   └── logout.php
├── db/
│   └── conexao.php
│   └── estoque_farmacia.sql
├── produtos/
│   └── listar.php
├── vendas/                     <-- Nova pasta adicionada
│   └── buscar_produtos.php     <-- Script para busca dinâmica no modal
│   └── registrar_venda.php     <-- Lógica de registro da venda
├── index.php
```

---

##  Mudanças no Banco de Dados

###  Antes
Tabela `vendas`:
```sql
CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

Tabela `itens_venda`:
```sql
CREATE TABLE itens_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    venda_id INT NOT NULL,
    quantidade INT NOT NULL,
    FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);
```

###  Depois
> Nenhuma alteração nas tabelas foi necessária após revisar o planejamento. O campo `id_usuario` já cobre o rastreio de vendas por usuário logado.

---

##  Lógica Implementada

###  Botão "Vender Item"
Adicionado à página `produtos/listar.php`:

```html
<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalVenderItem">Vender Item</button>
```

---

###  Modal de Venda
```html
<!-- Modal de Venda -->
<div class="modal fade" id="modalVenderItem" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../vendas/registrar_venda.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vender Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="buscaProduto" class="form-control" placeholder="Digite nome ou código" onkeyup="buscarProdutos()">
        <div id="resultadoProdutos" class="mt-3"></div>
        <input type="hidden" name="produto_id" id="produtoSelecionado">
        <input type="number" name="quantidade" class="form-control mt-3" placeholder="Quantidade" required min="1">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Registrar Venda</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
```

---

##  JavaScript – `buscarProdutos()`

```javascript
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
                        <input type="radio" name="produto_opcao" value="\${p.id}" onclick="document.getElementById('produtoSelecionado').value = \${p.id}">
                        <strong>\${p.nome}</strong> — \${p.fabricante}<br>
                        Classificação: \${p.classificacao || '-'} | Controlado: \${p.medicamento_controlado == 1 ? 'Sim' : 'Não'}<br>
                        Preço: R$ \${parseFloat(p.preco).toFixed(2)}
                    </label>
                `).join('');
            }
            document.getElementById('resultadoProdutos').innerHTML = html;
        })
        .catch(erro => {
            document.getElementById('resultadoProdutos').innerHTML = '<p class="text-danger">Erro na busca.</p>';
        });
}
```

---

##  `buscar_produtos.php`

```php
<?php
require_once '../db/conexao.php';

$termo = $_GET['termo'] ?? '';
$termo = "%$termo%";

$sql = "SELECT * FROM produtos WHERE nome LIKE ? OR cod_barras LIKE ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param('ss', $termo, $termo);
$stmt->execute();
$resultado = $stmt->get_result();

$produtos = [];
while ($row = $resultado->fetch_assoc()) {
    $produtos[] = $row;
}

echo json_encode($produtos);
```

---

##  `registrar_venda.php`

```php
<?php
session_start();
require_once '../db/conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

$id_usuario = $_SESSION['usuario'];
$produto_id = $_POST['produto_id'];
$quantidade = $_POST['quantidade'];

// Buscar o produto e validar quantidade
$sqlProduto = "SELECT preco, quantidade FROM produtos WHERE id = ?";
$stmt = $conexao->prepare($sqlProduto);
$stmt->bind_param('i', $produto_id);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();

if (!$produto || $produto['quantidade'] < $quantidade) {
    header('Location: ../produtos/listar.php?erro=quantidade');
    exit;
}

// Registrar venda
$total = $produto['preco'] * $quantidade;
$sqlVenda = "INSERT INTO vendas (total, id_usuario) VALUES (?, ?)";
$stmtVenda = $conexao->prepare($sqlVenda);
$stmtVenda->bind_param('di', $total, $id_usuario);
$stmtVenda->execute();
$id_venda = $stmtVenda->insert_id;

// Registrar item_venda
$sqlItem = "INSERT INTO itens_venda (produto_id, venda_id, quantidade) VALUES (?, ?, ?)";
$stmtItem = $conexao->prepare($sqlItem);
$stmtItem->bind_param('iii', $produto_id, $id_venda, $quantidade);
$stmtItem->execute();

// Atualizar estoque
$sqlAtualiza = "UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?";
$stmtAtualiza = $conexao->prepare($sqlAtualiza);
$stmtAtualiza->bind_param('ii', $quantidade, $produto_id);
$stmtAtualiza->execute();

header('Location: ../produtos/listar.php?msg=venda_sucesso');
exit;
```

---

##  Conclusão

A funcionalidade de **venda de item** está totalmente integrada ao sistema. Ela permite:

- Seleção de produto por nome ou código de barras.
- Busca dinâmica e visual com múltiplas opções.
- Registro da venda com data, quantidade e ID do usuário logado.
- Atualização automática do estoque.
- Separação de responsabilidades via nova pasta `vendas/`.
