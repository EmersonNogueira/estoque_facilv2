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
                
                <label for="local">Depósito:</label>
                <select name="local" id="local" class="filter" onchange="filtrarProdutos()">
                    <option value="" <?php echo (!isset($_GET['local']) || $_GET['local'] === '') ? 'selected' : ''; ?>>Todos</option>
                    <?php
                    $locais = array_unique(array_column($itens, 'local')); // Usando $itens agora
                    foreach ($locais as $localOption) {
                        $localOption = htmlspecialchars($localOption);
                        $selected = (isset($_GET['local']) && $_GET['local'] === $localOption) ? 'selected' : '';
                        echo "<option value=\"$localOption\" $selected>$localOption</option>";
                    }
                    ?>
                </select>

                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria" class="filter" onchange="filtrarProdutos()">
                    <option value="" <?php echo (!isset($_GET['categoria']) || $_GET['categoria'] === '') ? 'selected' : ''; ?>>Todas</option>
                    <?php
                    $categorias = array_unique(array_map(function($produto) {
                        return !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : 'Sem Categoria';
                    }, $itens)); // Usando $itens agora

                    foreach ($categorias as $categoriaOption) {
                        $selected = (isset($_GET['categoria']) && $_GET['categoria'] === $categoriaOption) ? 'selected' : '';
                        echo "<option value=\"$categoriaOption\" $selected>$categoriaOption</option>";
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
        $localFiltro = isset($_GET['local']) ? htmlspecialchars($_GET['local']) : '';
        $categoriaFiltro = isset($_GET['categoria']) ? htmlspecialchars($_GET['categoria']) : '';

        $produtosFiltrados = array_filter($itens, function ($produto) use ($localFiltro, $categoriaFiltro) { // Alterado para $itens
            $produtoLocal = htmlspecialchars($produto['local']);
            $produtoCategoria = !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : 'Sem Categoria';

            $localValido = empty($localFiltro) || $produtoLocal === $localFiltro;
            $categoriaValida = empty($categoriaFiltro) || $produtoCategoria === $categoriaFiltro;

            return $localValido && $categoriaValida;
        });
        ?>

        <?php if (!empty($produtosFiltrados)): ?>
            <?php foreach ($produtosFiltrados as $row): ?>
                <?php
                    // Alterado para refletir os dados do seu array
                    $codigo = htmlspecialchars($row['codigo_item']);
                    $descricao = htmlspecialchars($row['descricao']);
                    $situacao = htmlspecialchars($row['situacao']);
                    $visivel = htmlspecialchars($row['visivel']);
                    $custoUnitario = htmlspecialchars($row['custo_unitario']);
                    $categoria = htmlspecialchars($row['categoria']);
                    $local = htmlspecialchars($row['codigo_local']);
                    $saldo = htmlspecialchars($row['saldo']);
                    $validade = htmlspecialchars($row['validade']);

                ?>
                <div class="card produto-item" 
                     data-nome="<?php echo strtolower($descricao); ?>"
                     data-categoria="<?php echo strtolower($categoria); ?>"
                     onclick="toggleButtons(this)">
                    <h2><?php echo $descricao; ?></h2>
                    <p><strong>Código:</strong> <?php echo $codigo; ?></p>
                    <p><strong>Categoria:</strong> <?php echo $categoria; ?></p>
                    <p><strong>Local:</strong> <?php echo $local; ?></p>
                    <p><strong>Saldo:</strong> <?php echo $saldo; ?></p>

                    <p><strong>Situação:</strong> <?php echo $situacao; ?></p>
                    <p><strong>Valor Unitário:</strong> R$<?php echo number_format($custoUnitario, 2, ',', '.'); ?></p>
                    <p><strong>Validade:</strong> <?php echo $validade; ?></p>

                    <p><strong>Visível:</strong> <?php echo $visivel; ?></p>
                    
                    <div class="card-buttons" style="display: none;">
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
        <?php else: ?>
            <div class="no-products">
                Nenhum produto encontrado para os filtros selecionados.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Função para calcular o custo total
function calcularCustoTotal() {
    let custoTotal = 0;
    document.querySelectorAll('.produto-item').forEach(produto => {
        let custoUnitario = parseFloat(produto.querySelector("p:nth-of-type(6)").textContent.replace("Valor Unitário: R$", "").replace(",", ".")) || 0;
        let saldo = parseFloat(produto.querySelector("p:nth-of-type(4)").textContent.replace("Saldo:", "").trim()) || 0;
        custoTotal += custoUnitario * saldo;
    });
    document.getElementById("custo-total").textContent = custoTotal.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
}

function normalizeString(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function filtrarProdutos() {
    let search = normalizeString(document.getElementById('search-input').value);
    let local = normalizeString(document.getElementById('local').value);
    let categoria = normalizeString(document.getElementById('categoria').value);

    let produtos = document.querySelectorAll('.produto-item');
    produtos.forEach(produto => {
        let nome = normalizeString(produto.getAttribute('data-nome'));
        let produtoCategoria = normalizeString(produto.getAttribute('data-categoria'));

        let nomeMatch = nome.includes(search);
        let localMatch = local === "" || produto.getAttribute('data-local') === local;
        let categoriaMatch = categoria === "" || produtoCategoria === categoria;

        if (nomeMatch && categoriaMatch) {
            produto.style.display = "block";
        } else {
            produto.style.display = "none";
        }
    });

    document.getElementById("custo-total").textContent = custoTotal.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
}

function toggleButtons(card) {
    document.querySelectorAll('.card-buttons').forEach(buttons => {
        buttons.style.display = 'none';
    });

    let buttons = card.querySelector('.card-buttons');
    if (buttons) {
        buttons.style.display = 'block';
    }
}

window.onload = calcularCustoTotal;

</script>
