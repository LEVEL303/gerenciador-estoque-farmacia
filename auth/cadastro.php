<?php
$erro = $_GET['erro'] ?? null;

if ($erro) {
    echo '<script>history.replaceState(null, "", "cadastro.php");</script>';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Cadastro de Usuário</title>
</head>

<body>

    <div class="container mt-5" style="max-width: 400px;">
        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <h2 class="mb-4 text-center">Cadastro de Usuário</h2>
        <form action="processar_cadastro.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Nome de usuário</label>
                <input type="text" class="form-control" name="nome"  required>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" class="form-control" name="senha" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>