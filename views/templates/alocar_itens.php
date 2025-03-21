<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="add-product-container">
        <h1>Alocação de itens</h1>
        <form action="<?php echo $base_url; ?>item/alocar_itensbd" method="post">
            <input type="hidden" name="codigo_item" value="<?php echo htmlspecialchars($itens['codigo_item']); ?>">

            <div class="form-group">
                <label for="descricao">DESCRIÇÃO DO ITEM - REF - TAM - COR MARCA ou FABRICANTE:</label>
                <input type="text" id="descricao" name="descricao" value="<?php echo htmlspecialchars($itens['descricao']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="situacao">Situação:</label>
                <input type="text" id="situacao" name="situacao" value="<?php echo htmlspecialchars($itens['situacao']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Saldo a alocar:</label>
                <p><strong id="saldo_alocar_display"><?php echo htmlspecialchars($itens['saldo_alocar']); ?></strong></p>
                <input type="hidden" id="saldo_alocar" name ="saldo_alocar" value="<?php echo htmlspecialchars($itens['saldo_alocar']); ?>">
            </div>

            <div id="alocacoes">
                <div class="alocacao">
                    <div class="form-group">
                        <label for="deposito">Depósito:</label>
                        <select class="deposito" required name="deposito[]" onchange="carregarLocais(this)">
                            <option value="" disabled selected>Selecione um Depósito</option>
                            <option value="1">Infra.</option>
                            <option value="2">Zeld.</option>
                            <option value="3">Almox.</option>
                            <option value="4">TI</option>
                            <option value="5">Serviço</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="local">Local:</label>
                        <select class="local" required name="local[]" onchange="verificarSaldo()">
                            <option value=""  disabled selected>Selecione um Local</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="saldo">Saldo:</label>
                        <input type="number" class="saldo" name="saldo[]" step="1" min="1" required oninput="verificarSaldo()">
                    </div>

                    <div class="form-group">
                        <label for="validade">Data de Validade (opcional):</label>
                        <input type="date" class="validade" name="validade[]" onchange="verificarSaldo()">
                    </div>
                </div>
            </div>

            <button type="button" id="addAlocacaoBtn" onclick="adicionarAlocacao()">Adicionar Alocação</button>
            <button type="submit" id="submitBtn" disabled>Alocar ITEM</button>
        </form>
    </div>

    <script>
        function carregarLocais(selectDeposito) {
            const selectLocal = selectDeposito.parentNode.nextElementSibling.querySelector('.local');
            selectLocal.innerHTML = '<option value="" disabled selected>Selecione um Local</option>';

            fetch(`http://localhost/estoque_facil/Local/locais_depositos`)
                .then(response => response.json())
                .then(data => {
                    const locaisFiltrados = data.filter(item => item.codigo_deposito == selectDeposito.value);
                    locaisFiltrados.forEach(local => {
                        const option = document.createElement('option');
                        option.value = local.codigo_local;
                        option.textContent = local.nome_local;
                        selectLocal.appendChild(option);
                    });
                })
                .catch(error => console.error('Erro ao carregar locais:', error));
        }

        function adicionarAlocacao() {
            const novaAlocacao = document.querySelector('.alocacao').cloneNode(true);
            
            novaAlocacao.querySelector('.deposito').value = "";
            novaAlocacao.querySelector('.local').innerHTML = '<option value="" disabled selected>Selecione um Local</option>';
            novaAlocacao.querySelector('.saldo').value = "";
            novaAlocacao.querySelector('.validade').value = "";
            
            document.getElementById('alocacoes').appendChild(novaAlocacao);
            verificarSaldo();
        }

        function verificarSaldo() {
            let totalAlocado = 0;
            let saldoValido = true;
            let duplicatasEncontradas = false;
            let combinacoes = new Set();

            document.querySelectorAll('.saldo').forEach((input, index) => {
                const valor = parseInt(input.value) || 0;
                const local = document.querySelectorAll('.local')[index].value;
                const validade = document.querySelectorAll('.validade')[index].value || "SEM_VALIDADE"; 

                if (!Number.isInteger(valor) || valor < 1) {
                    saldoValido = false;
                    input.value = ""; 
                }

                totalAlocado += valor;

                const chave = local + "_" + validade;
                if (combinacoes.has(chave)) {
                    duplicatasEncontradas = true;
                } else {
                    combinacoes.add(chave);
                }
            });

            const saldoAlocar = parseInt(document.getElementById('saldo_alocar').value);

            if (totalAlocado > saldoAlocar) {
                alert("O saldo alocado não pode ser maior que o saldo para alocar!");
                document.querySelectorAll('.saldo').forEach(input => {
                    input.value = ""; 
                });
                totalAlocado = 0;
            }

            if (duplicatasEncontradas) {
                alert("Não é permitido alocar o mesmo local e a mesma data de validade mais de uma vez.");
            }

            document.getElementById('submitBtn').disabled = totalAlocado === 0 || totalAlocado > saldoAlocar || duplicatasEncontradas;
            document.getElementById('addAlocacaoBtn').disabled = totalAlocado === saldoAlocar;
        }
    </script>
</body>

</html>
