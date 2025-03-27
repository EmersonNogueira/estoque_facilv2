<div class="add-product-container">
    <h1>COMPRA DE ITEM</h1>
    <form action="<?php echo $base_url; ?>Registro/registrarcompra" method="POST">

        <!-- Nome do Produto (não editável) -->
        <div class="form-group">
            <label for="produto_nome">Descrição do item:</label>
            <input type="text" id="produto_nome" name="descicao_item" value="<?php echo htmlspecialchars($item['descricao']); ?>" readonly>
        </div>

        <!-- Condição do Produto (não editável) -->
        <div class="form-group">
            <label for="condicao_produto">Condição do Item:</label>
            <input type="text" id="condicao_produto" name="situacao" value="<?php echo htmlspecialchars($item['situacao']); ?>" readonly>
        </div>



        <div class="form-group">
            <label for="custo">Valor unitário:</label>
            <input type="text" id="custo_novo" name="custo_novo"  oninput="validarCusto(this)" >
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
            <label for="data_entrada">Data da Nota:</label>
            <input class= "filter"type="date" id="data" name="data" required>
        </div>



        <!-- Campo oculto para o ID do Produto -->
        <input type="hidden" id="produto_id" name="codigo_item" value="<?php echo htmlspecialchars($item['codigo_item']); ?>">
        <input type="hidden" id="custo_atual" name="custo_atual" value="<?php echo htmlspecialchars($item['custo_atual']); ?>">
        <input type="hidden" id="saldo_atual" name="saldo_atual" value="<?php echo htmlspecialchars($item['saldo_atual']); ?>">
        <input type="hidden" id="saldo_alocar" name="saldo_alocar" value="<?php echo htmlspecialchars($item['saldo_alocar']); ?>">


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
