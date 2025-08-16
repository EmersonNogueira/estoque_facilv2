<style>

    .password-reset-container {
        text-align: center;
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        margin: 0 auto; /* Isso garante que o formulário ficará centralizado */
    }
    h2 {
        margin-bottom: 20px;
        font-size: 24px;
        color: #333333;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-size: 14px;
        color: #666666;
    }
    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #cccccc;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
    }
    .submit-btn {
        width: 100%;
        padding: 10px;
        background: #007bff;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }
    .submit-btn:hover {
        background: #0056b3;
    }
    .error-message {
        color: #ff0000;
        font-size: 14px;
        margin-top: 10px;
        display: none;
    }
</style>

    
    
    <!-- Formulário de Redefinir Senha -->
    <div class="password-reset-container">
        <h2>Redefinir Senha</h2>
        <form id="form-password-reset-form"-pedido" method="POST" action="<?php echo $base_url; ?>login/alterarsenha">

            <div class="form-group">
                <label for="new-password">Nova Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="submit-btn">Alterar Senha</button>
            <p id="error-message" class="error-message">Por favor, preencha o campo de senha.</p>
        </form>
    </div>
