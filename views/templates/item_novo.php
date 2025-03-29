<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Item</title>
    <link rel="stylesheet" href="styles.css"> <!-- Incluindo o CSS existente -->
</head>
<body>
    <div class="add-product-container">
        <h1>Cadastro de Novo Item</h1>
        <form action="<?php echo $base_url; ?>item/item_adicionar" method="post">
            <div class="form-group">
                <label for="nome">DESCRIÇÃO DO ITEM - REF - TAM - COR MARCA ou FABRICANTE:</label>
                <input type="text" id="descricao" name="descricao" required>
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
                <label for="saldo">Saldo Inicial:</label>
                <input type="number" id="saldo_alocar" name="saldo_alocar" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="custo">Custo Unitário:</label>
                <input type="number" id="custo" name="custo_unitario" step="0.01" required>
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
            <button type="submit" class="btn-alocar">Cadastrar Item</button>
        </form>
    </div>


</body>
</html>