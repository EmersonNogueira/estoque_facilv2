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
                
                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria" class="filter" onchange="filtrarProdutos()">
                    <option value="" selected>Todas</option>
                    <?php
                    $categorias = array_unique(array_column($itens, 'categoria'));
                    foreach ($categorias as $categoriaOption) {
                        echo "<option value=\"$categoriaOption\">$categoriaOption</option>";
                    }
                    ?>
                </select>
            </div>
    </div>
    <div class="total-cost">
        <strong>Valor Total:</strong> R$<span id="custo-total">0.00</span>
    </div>

    <div class="card-container" id="produtos-container">
        <?php 
        $custoTotal = 0; // Inicializa a variável para armazenar o custo total
        foreach ($itens as $row): 
            $codigo = htmlspecialchars($row['codigo_item']);
            $descricao = htmlspecialchars($row['descricao']);
            $situacao = htmlspecialchars($row['situacao']);
            $visivel = htmlspecialchars($row['visivel']);
            $custoUnitario = floatval($row['custo_unitario']);
            $categoria = htmlspecialchars($row['categoria']);
            $saldo = isset($row['saldo_total']) ? intval($row['saldo_total']) : 0;

            // Calcula o custo total para este item
            $custoItem = $custoUnitario * $saldo;
            $custoTotal += $custoItem; // Acumula o custo total
        ?>
        <div class="card produto-item" data-nome="<?php echo strtolower($descricao); ?>" data-categoria="<?php echo strtolower($categoria); ?>">
            <h2><?php echo $descricao; ?></h2>
            <p><strong>Código:</strong> <?php echo $codigo; ?></p>
            <p><strong>Categoria:</strong> <?php echo $categoria; ?></p>
            <p><strong>Saldo:</strong> <?php echo $saldo; ?></p>
            <p><strong>Situação:</strong> <?php echo $situacao; ?></p>
            <p><strong>Valor Unitário:</strong> R$<?php echo number_format($custoUnitario, 2, ',', '.'); ?></p>
            <p><strong>Visível:</strong> <?php echo $visivel; ?></p>
            
            <div class="card-buttons">
                <form method="POST" action="<?php echo $base_url; ?>Produto/mvregistro">
                    <input type="hidden" name="codigo_item" value="<?php echo $codigo; ?>">
                    <button type="submit" class="btn-register">COMPRA / AJUSTES</button>
                </form>
                <form method="POST" action="<?php echo $base_url; ?>Produto/mveditar">
                    <input type="hidden" name="codigo_item" value="<?php echo $codigo; ?>">
                    <button type="submit" class="btn-edit">EDITAR PRODUTO</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function calcularCustoTotal() {
    let custoTotal = 0;
    document.querySelectorAll('.produto-item').forEach(produto => {
        let custoUnitario = parseFloat(produto.querySelector("p:nth-of-type(5)").textContent.replace("Valor Unitário: R$", "").replace(",", ".")) || 0;
        let saldo = parseFloat(produto.querySelector("p:nth-of-type(3)").textContent.replace("Saldo:", "").trim()) || 0;
        custoTotal += custoUnitario * saldo;
    });
    document.getElementById("custo-total").textContent = custoTotal.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
}

function normalizeString(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function filtrarProdutos() {
    let searchInput = normalizeString(document.getElementById("search-input").value);
    let categoriaFiltro = normalizeString(document.getElementById("categoria").value);
    
    let produtos = document.querySelectorAll(".produto-item");
    let custoTotal = 0;

    produtos.forEach(produto => {
        let nomeProduto = normalizeString(produto.getAttribute("data-nome"));
        let categoriaProduto = normalizeString(produto.getAttribute("data-categoria"));
        let custoUnitario = parseFloat(produto.querySelector("p:nth-of-type(5)").textContent.replace("Valor Unitário: R$", "").replace(",", ".")) || 0;
        let saldo = parseFloat(produto.querySelector("p:nth-of-type(3)").textContent.replace("Saldo:", "").trim()) || 0;

        let nomeMatch = nomeProduto.includes(searchInput);
        let categoriaMatch = categoriaFiltro === "" || categoriaProduto === categoriaFiltro;

        if (nomeMatch && categoriaMatch) {
            produto.style.display = "block";
            custoTotal += custoUnitario * saldo;
        } else {
            produto.style.display = "none";
        }
    });

    document.getElementById("custo-total").textContent = custoTotal.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
}


document.getElementById("search-input").addEventListener("input", filtrarProdutos);
document.getElementById("categoria").addEventListener("change", filtrarProdutos);

window.onload = calcularCustoTotal;
</script>
