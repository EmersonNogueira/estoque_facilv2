<div class="stock-adjustment-container">
    <div class="adjustment-header">
        <!-- Informações comuns do item -->
        <div class="product-summary">
            <h1 class="adjustment-title">TRANSFERÊNCIA DE ESTOQUE</h1>

            <h2 class="product-name"><?php echo htmlspecialchars($estoque[0]['descricao']); ?></h2>
            <p class="product-code">Código: <?php echo htmlspecialchars($estoque[0]['codigo_item']); ?></p>
            <div class="product-location">
                <span class="location-info">
                    <strong>Local Atual:</strong> <?php echo htmlspecialchars($estoque[0]['nome_local']); ?>
                </span>
                <span class="deposit-info">
                    <strong>Depósito Atual:</strong> <?php echo htmlspecialchars($estoque[0]['nome_deposito']); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="adjustment-forms-wrapper">
        <?php foreach ($estoque as $item): ?>
        <form action="<?php echo $base_url; ?>item/transferir_estoque" method="POST" class="stock-adjustment-form">
            <div class="form-row">
                <!-- Informações específicas do lote (validade e saldo) -->
                <div class="form-column stock-info">
                    <div class="stock-detail-card">
                        <div class="detail-group">
                            <span class="detail-label">Validade:</span>
                            <span class="detail-value"><?php echo $item['validade'] ? htmlspecialchars($item['validade']) : 'Sem validade'; ?></span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">Saldo Disponível:</span>
                            <span class="detail-value highlight"><?php echo htmlspecialchars($item['saldo']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Controles de transferência -->
                <div class="form-column adjustment-controls">
                    <div class="form-field">
                        <label for="quantidade_<?php echo $item['id']; ?>" class="field-label">Quantidade</label>
                        <input type="number" id="quantidade_<?php echo $item['id']; ?>" name="quantidade" 
                               class="field-input quantity-input" min="1" max="<?php echo htmlspecialchars($item['saldo']); ?>" required>
                        <small class="field-hint">Máximo: <?php echo htmlspecialchars($item['saldo']); ?></small>
                    </div>
                    
                    <div class="form-field">
                        <label for="deposito_destino_<?php echo $item['id']; ?>" class="field-label">Depósito Destino</label>
                        <select id="deposito_destino_<?php echo $item['id']; ?>" name="deposito_destino" 
                                class="field-select deposito" required onchange="carregarLocaisDestino(this, '<?php echo $item['id']; ?>')">
                            <option value="" disabled selected>Selecione um Depósito</option>
                            <option value="1">Infra.</option>
                            <option value="2">Zeld.</option>
                            <option value="3">Almox.</option>
                            <option value="4">TI</option>
                            <option value="5">Serviço</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="local_destino_<?php echo $item['id']; ?>" class="field-label">Local Destino</label>
                        <select id="local_destino_<?php echo $item['id']; ?>" name="local_destino" 
                                class="field-select local" required disabled>
                            <option value="" disabled selected>Selecione um Local</option>
                        </select>
                    </div>
                </div>
                
                <!-- Botão de Envio -->
                <div class="form-column action-column">
                    <button type="submit" class="adjustment-button" id="btn-transferir_<?php echo $item['id']; ?>" disabled>
                        <span class="button-icon">⇄</span>
                        <span class="button-text">Transferir</span>
                    </button>
                </div>
            </div>

            <!-- Campos ocultos -->
            <input type="hidden" name="id_estoque_origem" value="<?php echo htmlspecialchars($item['id']); ?>">
            <input type="hidden" name="validade_origem" value="<?php echo htmlspecialchars($item['validade']); ?>">
            <input type="hidden" name="codigo_item" value="<?php echo htmlspecialchars($item['codigo_item']); ?>">
            <input type="hidden" name="saldo_atual" value="<?php echo htmlspecialchars($item['saldo']); ?>">
            <input type="hidden" name="deposito_origem" value="<?php echo htmlspecialchars($item['codigo_deposito']); ?>">
            <input type="hidden" name="local_origem" value="<?php echo htmlspecialchars($item['codigo_local']); ?>">
        </form>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Mantém o mesmo JavaScript da versão anterior
document.addEventListener("DOMContentLoaded", function () {
    // Validação de quantidade para cada item
    document.querySelectorAll('.quantity-input').forEach(input => {
        const max = parseInt(input.getAttribute('max'));
        
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (parseInt(this.value) > max) {
                this.value = max;
            }
        });
        
        input.addEventListener('blur', function() {
            if (this.value === '' || parseInt(this.value) < 1) {
                this.value = 1;
            }
        });
    });
});

function carregarLocaisDestino(selectDeposito, itemId) {
    const selectLocal = document.getElementById(`local_destino_${itemId}`);
    const btnTransferir = document.getElementById(`btn-transferir_${itemId}`);
    selectLocal.innerHTML = '<option value="" disabled selected>Selecione um Local</option>';
    selectLocal.disabled = true;
    btnTransferir.disabled = true;

    if (!selectDeposito.value) return;

    //console.log("Depósito selecionado:", selectDeposito.value);

    fetch('<?php echo $base_url; ?>Local/locais_depositos')
        .then(response => response.json())
        .then(data => {
            //console.log("Locais recebidos:", data);

            const locaisFiltrados = data.filter(item => {
                //console.log(`Comparando: ${item.codigo_deposito} === ${selectDeposito.value}`);
                return String(item.codigo_deposito).trim() === String(selectDeposito.value).trim();
            });

            //console.log("Locais filtrados:", locaisFiltrados);

            locaisFiltrados.forEach(local => {
                const option = document.createElement('option');
                option.value = local.codigo_local;
                option.textContent = local.nome_local;
                selectLocal.appendChild(option);
            });

            selectLocal.disabled = false;

            const quantidade = document.getElementById(`quantidade_${itemId}`)?.value;
            if (quantidade && quantidade > 0) {
                btnTransferir.disabled = false;
            }
        })
        .catch(error => console.error('Erro ao carregar locais:', error));
}



// Habilita o botão de transferência quando ambos campos estiverem preenchidos
document.querySelectorAll('.local').forEach(select => {
    const itemId = select.id.split('_')[2];
    
    select.addEventListener('change', function() {
        const btnTransferir = document.getElementById(`btn-transferir_${itemId}`);
        const quantidade = document.getElementById(`quantidade_${itemId}`).value;
        
        btnTransferir.disabled = !(this.value && quantidade && parseInt(quantidade) > 0);
    });
});

document.querySelectorAll('.quantity-input').forEach(input => {
    const itemId = input.id.split('_')[1];
    
    input.addEventListener('input', function() {
        const btnTransferir = document.getElementById(`btn-transferir_${itemId}`);
        const localDestino = document.getElementById(`local_destino_${itemId}`).value;
        
        btnTransferir.disabled = !(localDestino && this.value && parseInt(this.value) > 0);
    });
});
</script>