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
                    echo "<option value=\"" . htmlspecialchars($categoriaOption) . "\">" . htmlspecialchars($categoriaOption) . "</option>";
                }
                ?>
            </select>
            
            <!-- Filtro por Situação -->
            <label for="situacao">Situação:</label>
            <select name="situacao" id="situacao" class="filter" onchange="filtrarProdutos()">
                <option value="" selected>Todas</option>
                <?php
                $situacoes = array_unique(array_column($itens, 'situacao'));
                foreach ($situacoes as $situacaoOption) {
                    echo "<option value=\"" . htmlspecialchars($situacaoOption) . "\">" . htmlspecialchars($situacaoOption) . "</option>";
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
            $pregao = htmlspecialchars($row['desc_pregao']);
            $unidadeMedida = htmlspecialchars($row['unidade_medida']);

            $categoria = htmlspecialchars($row['categoria']);
            $saldo = isset($row['saldo_total']) ? intval($row['saldo_total']) : 0;
            $saldo_alocar = isset($row['saldo_alocar']) ? intval($row['saldo_alocar']) : 0;

            // Calcula o custo total para este item com o saldo em estoque + saldo para alocar
            $custoItem = $custoUnitario * ($saldo + $saldo_alocar);
            $custoTotal += $custoItem; // Acumula o custo total
        ?>
        <div class="card produto-item" 
             data-nome="<?php echo strtolower($descricao); ?>" 
             data-categoria="<?php echo strtolower($categoria); ?>"
             data-situacao="<?php echo strtolower($situacao); ?>">
            <h2><?php echo $descricao; ?></h2>
            <p><strong>Código do item:</strong> <?php echo $codigo; ?></p>
            <p><strong>Categoria:</strong> <?php echo $categoria; ?></p>
            <p><strong>Saldo em estoque:</strong> <?php echo $saldo; ?></p>
            <p><strong>Saldo para alocar:</strong> <?php echo $saldo_alocar; ?></p>

            <p><strong>Situação:</strong> <?php echo $situacao; ?></p>
            <p><strong>Valor Unitário:</strong> R$<?php echo number_format($custoUnitario, 2, ',', '.'); ?></p>
            <p><strong>Visível:</strong> <?php echo $visivel; ?></p>
            <p><strong>Descrição do Pregão:</strong> <?php echo $pregao; ?></p>
            <p><strong>Unidade de Medida:</strong> <?php echo $unidadeMedida; ?></p>

            <div class="card-buttons">
                <form method="POST" action="<?php echo $base_url; ?>Item/registro">
                    <input type="hidden" name="codigo_item" value="<?php echo $codigo; ?>">
                    <button type="submit" class="btn-register">Alocar produto</button>
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
        if (produto.style.display !== "none") {
            let custoUnitario = parseFloat(produto.querySelector("p:nth-of-type(6)").textContent.replace("Valor Unitário: R$", "").replace(",", ".")) || 0;
            let saldo = parseFloat(produto.querySelector("p:nth-of-type(3)").textContent.replace("Saldo em estoque:", "").trim()) || 0;
            let saldoAlocar = parseFloat(produto.querySelector("p:nth-of-type(4)").textContent.replace("Saldo para alocar:", "").trim()) || 0;
            custoTotal += custoUnitario * (saldo + saldoAlocar);
        }
    });
    document.getElementById("custo-total").textContent = custoTotal.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
}

function normalizeString(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function filtrarProdutos() {
    let searchInput = normalizeString(document.getElementById("search-input").value);
    let categoriaFiltro = normalizeString(document.getElementById("categoria").value);
    let situacaoFiltro = normalizeString(document.getElementById("situacao").value);
    
    let produtos = document.querySelectorAll(".produto-item");

    produtos.forEach(produto => {
        let nomeProduto = normalizeString(produto.getAttribute("data-nome"));
        let categoriaProduto = normalizeString(produto.getAttribute("data-categoria"));
        let situacaoProduto = normalizeString(produto.getAttribute("data-situacao"));

        let nomeMatch = nomeProduto.includes(searchInput);
        let categoriaMatch = categoriaFiltro === "" || categoriaProduto === categoriaFiltro;
        let situacaoMatch = situacaoFiltro === "" || situacaoProduto === situacaoFiltro;

        if (nomeMatch && categoriaMatch && situacaoMatch) {
            produto.style.display = "block";
        } else {
            produto.style.display = "none";
        }
    });

    calcularCustoTotal(); // Recalcula o custo total após filtrar os produtos
}

document.getElementById("search-input").addEventListener("input", filtrarProdutos);
document.getElementById("categoria").addEventListener("change", filtrarProdutos);
document.getElementById("situacao").addEventListener("change", filtrarProdutos);

window.onload = calcularCustoTotal;
</script>
