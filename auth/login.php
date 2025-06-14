<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="processar_login.php" method="POST">
        <label for="nome">Nome de Usuário:</label><br>
        <input type="text" name="nome" required><br>

        <label for="senha">Senha:</label><br>
        <input type="password" name="senha" required><br>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>