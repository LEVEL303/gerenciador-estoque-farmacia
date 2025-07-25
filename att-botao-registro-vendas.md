
documento de atualização 2 de ryam 

excluir esse documento apos atualização da documentação 

# Documentação Técnica – Atualizações no Projeto de Gerenciamento de Estoque e Vendas (PHP + MySQL)

##  Estrutura do Projeto Antes
Antes da modificação, o projeto continha:

- Falta de chamada correta da conexão com o banco de dados no arquivo `obter_registros.php`.
- Funções JavaScript não integradas corretamente com o HTML (ex: `carregarRegistroVendas()` não era acionada).
- Problemas com caminho incorreto do `require_once`.
- Erros de execução relacionados a variáveis indefinidas (`$conn`) e conexões não realizadas.

##  Arquivos Criados ou Corrigidos

### 1. **conexao.php** (movido para `/db/conexao.php`)
**Antes**:
- Caminho errado: `../conexao.php`
- Código não incluído corretamente no `obter_registros.php`.

**Agora**:
```php
<?php
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'estoque_farmacia';

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die('Erro na conexão: ' . $conexao->connect_error);
}
?>
```

**Explicação**: 
- Substituído o uso de `PDO` por `mysqli` para compatibilidade com o restante do sistema.
- A variável `$conexao` agora está definida corretamente para uso em outros scripts.

---

### 2. **vendas/obter_registros.php**
**Antes**:
- Requisição `require_once` apontava para caminho incorreto.
- Tentativa de uso de variável `$conn` inexistente.

**Agora**:
```php
require_once __DIR__ . '/../db/conexao.php'; // Caminho corrigido

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

$resultado = $conexao->query($sql);

// Exibição da tabela ou mensagem alternativa
```

**Explicação**:
- Corrigido o caminho do `require_once`.
- Substituição de `$conn->prepare()` por `$conexao->query()`.
- Ajustada a variável de conexão para compatibilidade com `mysqli`.

---

### 3. **Integração com o Modal (HTML + JS)**

#### **Antes**
- `#listaVendas` ficava com "Carregando registros..." para sempre.
- Não havia chamada da função JS `carregarRegistroVendas()` ao abrir a aba de registro.

#### **Agora**
```js
function mostrarAba(aba) {
    document.getElementById('abaVenda').style.display = aba === 'venda' ? 'block' : 'none';
    document.getElementById('abaRegistro').style.display = aba === 'registro' ? 'block' : 'none';

    if (aba === 'registro') {
        carregarRegistroVendas(); // Agora carrega os registros dinamicamente
    }
}
```

```js
function carregarRegistroVendas() {
    fetch('../vendas/obter_registros.php')
        .then(response => {
            if (!response.ok) throw new Error('Erro ao buscar registros');
            return response.text();
        })
        .then(data => {
            document.getElementById('listaVendas').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('listaVendas').innerHTML =
                `<div class="alert alert-danger">Erro ao carregar registros: ${error.message}</div>`;
        });
}
```

**Explicação**:
- Função `carregarRegistroVendas()` é agora chamada corretamente ao trocar a aba no modal.
- A resposta do PHP é inserida dinamicamente na `div#listaVendas`.

---

##  Resumo das Substituições

| Antes | Depois |
|-------|--------|
| Caminho incorreto para `conexao.php` | Corrigido com `__DIR__ . '/../db/conexao.php'` |
| Uso de `PDO` com `$conn` indefinido | Substituído por `mysqli` com `$conexao` |
| Tabela de vendas não carregava | Função JS `carregarRegistroVendas()` corrigida |
| Modal sem ação na troca de abas | Lógica `mostrarAba()` adicionada com chamada AJAX |

##  Observações Finais
- Garantido que os caminhos estejam corretos considerando a estrutura de pastas.
- Manutenção da responsividade e experiência do usuário com AJAX.
- Compatibilidade mantida com o restante do sistema baseado em `mysqli`.

---

**Última atualização:** Julho de 2025
