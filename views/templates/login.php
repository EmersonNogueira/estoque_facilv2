<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Estoque Fácil</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>stylelogin.css?v=<?php echo time(); ?>">
</head>
<?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
    <script type="text/javascript">
        window.onload = function() {
            alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
        };
    </script>
    <?php unset($_SESSION['mensagem_confirmacao']); // Limpa a mensagem após exibir ?>
<?php endif; ?>

<body>
    <div class="container">
    <div class="header">
    <img src="<?php echo $base_url; ?>img/logo2.jpg" alt="Logo Estoque Fácil" class="logo">
        <h1>Estoque Fácil</h1>
        <p class="para">Organize e controle seu estoque com facilidade.</p>
    </div>

        <form method="POST" action="<?php echo $base_url; ?>Login/logar">
            <div class="form-group">
                <label for="email">Usuário:</label>
                <input type="text" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn-submit">Entrar</button>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
