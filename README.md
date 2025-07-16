# Sistema de Controle de Estoque - Farmácia

Este projeto é uma aplicação web para controle de estoque de produtos farmacêuticos, permitindo cadastro, login e gerenciamento de produtos por usuário.

## Tecnologias utilizadas

- PHP
- Bootstrap 5
- HTML/CSS/JavaScript
- MySQL

## Funcionalidades

- Cadastro e login de usuários
- CRUD de produtos (cadastrar, listar, editar, excluir)
- Filtro de busca por nome ou código de barras
- Controle de estoque (incrementar/decrementar quantidade)

## Como rodar o projeto

### Pré-requisitos

- PHP 7.4 ou superior
- MySQL (versão usada: 9.1.0)

Obs: Se estiver usando Windows, basta instalar o WampServer.

### Passos (Windows)

1. Clone o repositório no seguinte diretório: `C:\wamp64\www`
```bash 
    git clone https://github.com/LEVEL303/gerenciador-estoque-farmacia.git
```

2. Execute o script SQL `db/estoque_farmacia.sql` no MySQL

3. Configure o arquivo `db/conexao.php` com os dados do seu banco

4. Acesse `localhost/gerenciador-estoque-farmacia` no navegador

#### Passos (Linux)

1. Clone o repositório
```bash 
    git clone https://github.com/LEVEL303/gerenciador-estoque-farmacia.git
```
2. Execute o script SQL `db/estoque_farmacia.sql` no MySQL

3. Configure o arquivo `db/conexao.php` com os dados do seu banco

4. Rode o servidor PHP

```bash
    php -S localhost:8000
```

5.  Acesse `localhost:8000` no navegador

---

Desenvolvido como parte da disciplina de Engenharia de Software