<div class="stock-adjustment-container">
    <div class="adjustment-header">

        <!-- Informações comuns do item -->
        <div class="product-summary">
            <h1 class="adjustment-title">AJUSTES DE ESTOQUE</h1>

            <h2 class="product-name"><?php echo htmlspecialchars($estoque[0]['descricao']); ?></h2>
            <p class="product-code">Código: <?php echo htmlspecialchars($estoque[0]['codigo_item']); ?></p>
            <div class="product-location">
                <span class="location-info">
                    <strong>Local:</strong> <?php echo htmlspecialchars($estoque[0]['nome_local']); ?>
                </span>
                <span class="deposit-info">
                    <strong>Depósito:</strong> <?php echo htmlspecialchars($estoque[0]['nome_deposito']); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="adjustment-forms-wrapper">
        <?php foreach ($estoque as $item): ?>
        <form action="<?php echo $base_url; ?>Registro/ajuste_estoque" method="POST" class="stock-adjustment-form">
            <div class="form-row">
                <!-- Informações específicas do estoque -->
                <div class="form-column stock-info">
                    <div class="stock-detail-card">
                        <div class="detail-group">
                            <span class="detail-label">Validade:</span>
                            <span class="detail-value"><?php echo $item['validade'] ? htmlspecialchars($item['validade']) : 'Sem validade'; ?></span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">Saldo Atual:</span>
                            <span class="detail-value highlight"><?php echo htmlspecialchars($item['saldo']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Controles de ajuste -->
                <div class="form-column adjustment-controls">
                    
                    <div class="form-field">
                        <label for="tipo_operacao_<?php echo $item['id']; ?>" class="field-label">Tipo de Ajuste</label>
                        <select id="tipo_operacao_<?php echo $item['id']; ?>" name="tipo" class="field-select" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Ajuste Positivo">Positivo (+)</option>
                            <option value="Ajuste Negativo">Negativo (-)</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="quantidade_<?php echo $item['id']; ?>" class="field-label">Quantidade</label>
                        <input type="number" id="quantidade_<?php echo $item['id']; ?>" name="quantidade" class="field-input quantity-input" min="1" required>
                    </div>
                    
                </div>
                
                <!-- Botão de Envio -->
                <div class="form-column action-column">
                    <button type="submit" class="adjustment-button">
                        <span class="button-icon">✓</span>
                        <span class="button-text">Aplicar Ajuste</span>
                    </button>
                </div>
            </div>

            

            <!-- Campos ocultos -->
            <input type="hidden" name="id_estoque" value="<?php echo htmlspecialchars($item['id']); ?>">
            <input type="hidden" name="custo_unitario" value="<?php echo htmlspecialchars($item['custo_unitario']); ?>">

            <input type="hidden" name="saldo_atual" value="<?php echo htmlspecialchars($item['saldo']); ?>">
            <input type="hidden" name="codigo_item" value="<?php echo htmlspecialchars($item['codigo_item']); ?>">
            <input type="hidden" name="codigo_local" value="<?php echo htmlspecialchars($item['codigo_local']); ?>">
            <input type="hidden" name="nome_local" value="<?php echo htmlspecialchars($item['nome_local']); ?>">
            <input type="hidden" name="nome_deposito" value="<?php echo htmlspecialchars($item['nome_deposito']); ?>">
        </form>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Configura a data atual para todos os campos de data
    const hoje = new Date().toISOString().split('T')[0];
    document.querySelectorAll('.date-input').forEach(input => {
        input.value = hoje;
    });

    // Validação de quantidade para todos os campos
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        input.addEventListener('blur', function () {
            if (this.value === '' || this.value < 1) {
                this.value = 1;
            }
        });

        input.addEventListener('keydown', function (event) {
            const invalidChars = ['-', '+', 'e', '.', ','];
            if (invalidChars.includes(event.key)) {
                event.preventDefault();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    calcularCustoTotal();
    
    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        mostrarNotificacao('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>', 'sucesso');
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>
});
</script>

<style>

</style>