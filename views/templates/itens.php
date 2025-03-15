<div class="container">
    <h1>Itens</h1>
    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        <script type="text/javascript">
            window.onload = function() {
                alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
            };
        </script>
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>

    <div class="top-bar">
        <div class="filters">
            <input type="search" name="search" id="search-input" placeholder="Descrição do item" class="search-input" oninput="filtrarProdutos()">
            

            
            <label for="deposito">Depósito:</label>
            <select name="deposito" id="deposito" class="filter" onchange="filtrarProdutos()">
                <option value="">Todos</option>
                <?php
                $depositos = array_unique(array_map(function($produto) {
                    return !empty($produto['nome_deposito']) ? htmlspecialchars($produto['nome_deposito']) : 'Sem Depósito';
                }, $itens));
                
                foreach ($depositos as $depositoOption) {
                    echo "<option value=\"" . strtolower($depositoOption) . "\">$depositoOption</option>";
                }
                ?>
            </select>

            <label for="categoria">Categoria:</label>
            <select name="categoria" id="categoria" class="filter" onchange="filtrarProdutos()">
                <option value="">Todas</option>
                <?php
                $categorias = array_unique(array_map(function($produto) {
                    return !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : 'Sem Categoria';
                }, $itens));
                
                foreach ($categorias as $categoriaOption) {
                    echo "<option value=\"" . strtolower($categoriaOption) . "\">$categoriaOption</option>";
                }
                ?>
            </select>

            <label for="situacao">Situação:</label>
            <select name="situacao" id="situacao" class="filter" onchange="filtrarProdutos()">
                <option value="">Todas</option>
                <?php
                $situacoes = array_unique(array_map(function($produto) {
                    return !empty($produto['situacao']) ? htmlspecialchars($produto['situacao']) : 'Sem Situação';
                }, $itens));
                
                foreach ($situacoes as $situacaoOption) {
                    echo "<option value=\"" . strtolower($situacaoOption) . "\">$situacaoOption</option>";
                }
                ?>
            </select>


        </div>
    </div>
    <div class="total-cost">
        <strong>Valor Total:</strong> R$<span id="custo-total">0.00</span>
    </div>

    <div class="card-container" id="produtos-container">
        <?php foreach ($itens as $row): ?>
            <div class="card produto-item" 
                 data-nome="<?php echo strtolower(htmlspecialchars($row['descricao'])); ?>"
                 data-categoria="<?php echo strtolower(htmlspecialchars($row['categoria'])); ?>"
                 data-deposito="<?php echo strtolower(!empty($row['nome_deposito']) ? htmlspecialchars($row['nome_deposito']) : 'sem depósito'); ?>"
                 data-situacao="<?php echo strtolower(htmlspecialchars($row['situacao'])); ?>"

                 data-custo="<?php echo htmlspecialchars($row['custo_unitario'] * $row['saldo']); ?>"
                 onclick="toggleButtons(this)">
                <h2><?php echo htmlspecialchars($row['descricao']); ?></h2>
                <p><strong>Código do item:</strong> <?php echo htmlspecialchars($row['codigo_item']); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($row['categoria']); ?></p>
                <p><strong>Depósito:</strong> <?php echo htmlspecialchars($row['nome_deposito']); ?></p>
                <p><strong>Local:</strong> <?php echo htmlspecialchars($row['nome_local']); ?></p>
                <p><strong>Saldo:</strong> <?php echo htmlspecialchars($row['saldo']); ?></p>
                <p><strong>Situação:</strong> <?php echo htmlspecialchars($row['situacao']); ?></p>
                <p><strong>Valor Unitário:</strong> R$<?php echo number_format(htmlspecialchars($row['custo_unitario']), 2, ',', '.'); ?></p>
                <p><strong>Validade:</strong> <?php echo !empty($row['validade']) ? date('d/m/Y', strtotime($row['validade'])) : ''; ?></p>

                <p><strong>Visível:</strong> <?php echo htmlspecialchars($row['visivel']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function normalizeString(str) {
    return str ? str.toLowerCase().trim() : "";
}

function calcularCustoTotal() {
    let produtos = document.querySelectorAll('.produto-item');
    let total = 0;
    produtos.forEach(produto => {
        if (produto.style.display !== "none") {
            total += parseFloat(produto.getAttribute('data-custo')) || 0;
        }
    });
    document.getElementById('custo-total').innerText = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function filtrarProdutos() {
    let search = normalizeString(document.getElementById('search-input').value);
    let categoria = normalizeString(document.getElementById('categoria').value);
    let deposito = normalizeString(document.getElementById('deposito').value);
    let situacao = normalizeString(document.getElementById('situacao').value);

    let produtos = document.querySelectorAll('.produto-item');
    produtos.forEach(produto => {
        let nome = normalizeString(produto.getAttribute('data-nome'));
        let produtoCategoria = normalizeString(produto.getAttribute('data-categoria'));
        let produtoDeposito = normalizeString(produto.getAttribute('data-deposito'));
        let produtoSituacao = normalizeString(produto.getAttribute('data-situacao'));

        let nomeMatch = nome.includes(search);
        let categoriaMatch = categoria === "" || produtoCategoria === categoria;
        let depositoMatch = deposito === "" || produtoDeposito === deposito;
        let situacaoMatch = situacao === "" || produtoSituacao === situacao;

        produto.style.display = (nomeMatch && categoriaMatch && depositoMatch && situacaoMatch) ? "block" : "none";
    });
    calcularCustoTotal();
}

window.onload = calcularCustoTotal;

</script>
