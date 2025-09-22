<div class="add-product-container">
    <h1>Editar Produto</h1>
    <form action="<?php echo $base_url; ?>Item/atualizar_item" method="post">
        <!-- Campo oculto para ID -->
        <input type="hidden" name="codigo_item" value="<?php echo htmlspecialchars($item['codigo_item']); ?>">

        <div class="form-group">
            <label for="nome">Descrição do item:</label>
            <input type="text" id="descricao" name="descricao" value="<?php echo htmlspecialchars($item['descricao']); ?>" required>
        </div>

        <div class="form-group">
            <label for="situacao">Situação:</label>
            <select id="situacao" name="situacao" disabled>
                <option value="">Selecione-</option>
                <option value="Novo" <?php echo ($item['situacao'] == 'Novo') ? 'selected' : ''; ?>>Novo</option>
                <option value="Usado" <?php echo ($item['situacao'] == 'Usado') ? 'selected' : ''; ?>>Usado</option>
            </select>
        </div>
        <div class="form-group">
        <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria" required>
                <option value="" disabled>Selecione uma Categoria</option>
                <option value="Expediente" <?php echo ($item['categoria'] == 'Expediente') ? 'selected' : ''; ?>>Expediente</option>
                <option value="Manutenção" <?php echo ($item['categoria'] == 'Manutenção') ? 'selected' : ''; ?>>Manutenção</option>
                <option value="Gestao RH" <?php echo ($item['categoria'] == 'Gestão RH') ? 'selected' : ''; ?>>Gestão RH</option>
                <option value="Informática" <?php echo ($item['categoria'] == 'Informática') ? 'selected' : ''; ?>>Informática</option>
                <option value="Limpeza" <?php echo ($item['categoria'] == 'Limpeza') ? 'selected' : ''; ?>>Limpeza</option>
                <option value="Copa" <?php echo ($item['categoria'] == 'Copa') ? 'selected' : ''; ?>>Copa</option>
                <option value="Material didático" <?php echo ($item['categoria'] == 'Material didático') ? 'selected' : ''; ?>>Material didático</option>
                <option value="Móveis e Utensílios" <?php echo ($item['categoria'] == 'Móveis e Utensílios') ? 'selected' : ''; ?>>Móveis e Utensílios</option>

            </select>
        </div>
        <div class="form-group">
            <label for="nome">Descrição no pregão:</label>
            <input type="text" id="desc_pregao" name="pregao" value="<?php echo htmlspecialchars($item['pregao']); ?>" required>
        </div>
        <div class="form-group">
            <label for="nome">Unidade de Medida:</label>
            <input type="text" id="unidade_medida" name="unidade_medida" value="<?php echo htmlspecialchars($item['unidade_medida']); ?>" required>
        </div>

        <div class="form-group">
            <label for="visivel">Visível:</label>
            <select id="visivel" name="visivel" required>
                <option value="Sim" <?php echo ($item['visivel'] == "Sim") ? 'selected' : ''; ?>>Sim</option>
                <option value="Não" <?php echo ($item['visivel'] == "Não") ? 'selected' : ''; ?>>Não</option>
            </select>
        </div>
        <button type="submit" class="btn-alocar">Salvar Alterações</button>
    </form>
</div>

<script>

document.getElementById('custo').addEventListener('input', function() {
    this.value = this.value.replace(',', '.'); // Substitui vírgula por ponto em tempo real
});

document.querySelector('form').addEventListener('submit', function() {
    let custoInput = document.getElementById('custo');
    custoInput.value = custoInput.value.replace(',', '.'); // Garante a substituição antes do envio
});
</script>