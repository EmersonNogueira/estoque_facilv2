<div class="add-product-container">
    <h1>COMPRA / AJUSTES</h1>
    <form action="<?php echo $base_url; ?>Registro/novoregistro" method="POST">

        <!-- Tipo de Operação -->
        <div class="form-group">
            <label for="tipo_operacao">Tipo de Operação:</label>
            <select id="tipo_operacao" name="tipo" required>
                <option value="" disabled selected>Selecione o tipo</option>
                <?php
                // Verifica o tipo da sessão e ajusta as opções
                if ($_SESSION['tipo'] === "infra") {
                    echo '<option value="Compra">Compra</option>';
                } elseif ($_SESSION['tipo'] === "admin") {
                    echo '<option value="Compra">Compra</option>';
                    echo '<option value="Ajuste Positivo">Ajuste Positivo</option>';
                    echo '<option value="Ajuste Negativo">Ajuste Negativo</option>';
                }
                ?>
            </select>
        </div>

        <!-- Nome do Produto (não editável) -->
        <div class="form-group">
            <label for="produto_nome">Nome do Produto:</label>
            <input type="text" id="produto_nome" name="produto_nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" readonly>
        </div>

        <!-- Condição do Produto (não editável) -->
        <div class="form-group">
            <label for="condicao_produto">Condição do Produto:</label>
            <input type="text" id="condicao_produto" name="condicao_produto" value="<?php echo htmlspecialchars($produto['situacao']); ?>" readonly>
        </div>



        <!-- Saldo Atual (não editável) -->
        <div class="form-group">
            <label for="saldo_atual">Saldo Atual:</label>
            <input type="number" id="saldo_atual" name="saldo_atual" value="<?php echo htmlspecialchars($produto['saldo']); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="custo">Valor unitário:</label>
            <input type="text" id="custo_novo" name="custo_novo" value="<?php echo htmlspecialchars($produto['custo']); ?>" oninput="validarCusto(this)" >
        </div>




        <!-- Quantidade do Registro -->
        <div class="form-group">
            <label for="quantidade">Quantidade:</label>
            <input type="number" id="quantidade" name="quantidade" min="1" required>
        </div>

        <!-- Número da Nota -->
        <div class="form-group">
            <label for="numero_nota">Número da Nota:</label>
            <input type="text" id="numero_nota" name="numero_nota" >
        </div>


        <!-- Data de Entrada -->
        <div class="form-group">
            <label for="data_entrada">Data da Nota / Ajuste:</label>
            <input class= "filter"type="date" id="data" name="data" required>
        </div>



        <!-- Campo oculto para o ID do Produto -->
        <input type="hidden" id="produto_id" name="id_produto" value="<?php echo htmlspecialchars($produto['id_produto']); ?>">
        <input type="hidden" id="custo_atual" name="custo_atual" value="<?php echo htmlspecialchars($produto['custo']); ?>">

        <!-- Botão de Envio -->
        <button type="submit" class="btn-submit">Registrar</button>
    </form>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tipoOperacao = document.getElementById("tipo_operacao");
        const custoInput = document.getElementById("custo_novo");
        const custoOriginal = "<?php echo htmlspecialchars($produto['custo']); ?>";

        tipoOperacao.addEventListener("change", function () {
            if (tipoOperacao.value === "Compra") {
                custoInput.removeAttribute("readonly");
                custoInput.setAttribute("required", "required");
                custoInput.value = ""; // Deixa o campo em branco para o usuário digitar
            } else {
                custoInput.setAttribute("readonly", "readonly");
                custoInput.removeAttribute("required");
                custoInput.value = custoOriginal; // Restaura o valor original
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form'); // Seleciona o formulário
    const custoInput = document.getElementById('custo_novo'); // Seleciona o campo de custo

    form.addEventListener('submit', function (event) {
        // Formata o valor antes de enviar o formulário
        let valor = custoInput.value;

        // Substitui vírgula por ponto
        valor = valor.replace(',', '.');

        // Remove pontos de milhar (opcional, caso o usuário insira algo como 1.500,80)
        valor = valor.replace(/\.(?=.*\.)/g, '');

        // Valida se o valor é um número válido
        if (isNaN(valor)) {
            alert('Por favor, insira um valor numérico válido.');
            event.preventDefault(); // Impede o envio do formulário
        } else {
            // Atualiza o valor no campo de input
            custoInput.value = valor;
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const quantidadeInput = document.getElementById('quantidade');

    quantidadeInput.addEventListener('input', function () {
        // Remove qualquer caractere que não seja número
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    quantidadeInput.addEventListener('blur', function () {
        // Quando o campo perde o foco, garante que o valor seja pelo menos 1
        if (this.value === '' || this.value < 1) {
            this.value = 1;
        }
    });

    quantidadeInput.addEventListener('keydown', function (event) {
        // Bloqueia a entrada de caracteres inválidos (., -, e, etc.)
        const invalidChars = ['-', '+', 'e', '.', ','];
        if (invalidChars.includes(event.key)) {
            event.preventDefault();
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const dataEntrada = document.getElementById("data_entrada");

    // Obtém a data atual no formato YYYY-MM-DD
    const hoje = new Date().toISOString().split('T')[0];

    // Define o valor do campo como a data de hoje
    dataEntrada.value = hoje;
});

</script>
