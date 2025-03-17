<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <link rel="stylesheet" href="styles.css"> <!-- Incluindo o CSS existente -->
</head>
<body>
    <div class="add-product-container">
        <h1>Cadastro de Novo Produto</h1>
        <form action="<?php echo $base_url; ?>item/item_adicionar" method="post">
            <div class="form-group">
                <label for="nome">DESCRIÇÃO DO ITEM - REF - TAM - COR MARCA ou FABRICANTE:</label>
                <input type="text" id="item" name="item" required>
            </div>
            <div class="form-group">
                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria" required>
                    <option value="" disabled selected>Selecione uma Categoria</option>
                    <option value="Expediente">Expediente</option>
                    <option value="Manutenção">Manutenção</option>
                    <option value="Gestão RH">Gestão RH</option>
                    <option value="Informática">Informática</option>
                    <option value="Limpeza">Limpeza</option>
                    <option value="Copa">Copa</option>
                    <option value="Material didático">Material didático</option>
                    <option value="Móveis e Utensílios">Móveis e Utensílos</option>

                </select>
            </div>

            <div class="form-group">
                <label for="deposito">Depósito:</label>
                <select id="deposito" name="deposito"  onchange="carregarLocais(this.value)">
                    <option value="" disabled selected>Selecione um Depósito</option>
                    <!-- Opções de depósito podem ser carregadas dinamicamente ou manualmente -->
                    <option value="1">Infra.</option>
                    <option value="2">Zeld.</option>
                    <option value="3">Almox.</option>
                    <option value="4">TI</option>
                    <option value="5">Serviço</option>

                </select>
            </div>

            <div class="form-group">
                <label for="local">Local:</label>
                <select id="local" name="local" >
                    <option value="" disabled selected>Selecione um Local</option>
                </select>
            </div>

            <div class="form-group">
                <label for="situacao">Situação:</label>
                <select id="situacao" name="situacao" required>
                    <option value="" disabled selected>Selecione a Situação</option>
                    <option value="Novo">Novo</option>
                    <option value="Usado">Usado</option>
                    <option value="Serviço">Serviço</option>
                </select>
            </div>

            <div class="form-group">
                <label for="saldo">Saldo:</label>
                <input type="number" id="saldo" name="saldo" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="custo">Custo:</label>
                <input type="number" id="custo" name="custo" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="visivel">Visível:</label>
                <select id="visivel" name="visivel" required>
                    <option value="" disabled selected>Visível para solicitante?</option>
                    <option value="Sim">SIM</option>
                    <option value="Não">NÃO</option>
                </select>
            </div>

            <div class="form-group">
                <label for="pregao">Descrição no pregão:</label>
                <input type="text" id="desc_pregao" name="desc_pregao">
            </div>

            <div class="form-group">
                <label for="unidade_medida">Unidade de medida:</label>
                <input type="text" id="unidade_medida" name="unidade_medida">
            </div>
            <button type="submit" class="btn-submit">Cadastrar Produto</button>
        </form>
    </div>

    <script>
        function carregarLocais(codigoDeposito) {
            // Limpa as opções atuais
            const selectLocal = document.getElementById('local');
            selectLocal.innerHTML = '<option value="" disabled selected>Selecione um Local</option>';

            // Faz a requisição à API
            fetch(`http://localhost/estoque_facil/Local/locais_depositos`)
                .then(response => response.json())
                .then(data => {
                    // Filtra os locais pelo depósito selecionado
                    const locaisFiltrados = data.filter(item => item.codigo_deposito == codigoDeposito);

                    // Adiciona as opções ao select de local
                    locaisFiltrados.forEach(local => {
                        const option = document.createElement('option');
                        option.value = local.codigo_local;
                        option.textContent = local.nome_local;
                        selectLocal.appendChild(option);
                    });
                })
                .catch(error => console.error('Erro ao carregar locais:', error));
        }
    </script>
</body>
</html>