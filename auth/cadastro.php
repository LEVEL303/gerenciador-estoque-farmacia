<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
</head>
<body>
    <h2>Cadastro de Usuário</h2>
    <form action="processar_cadastro.php" method="POST">
        <label for="nome">Nome de usuário:</label><br>
        <input type="text" name="nome" required><br>

        <label for="senha">Senha:</label><br>
        <input type="password" name="senha" required><br>

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>