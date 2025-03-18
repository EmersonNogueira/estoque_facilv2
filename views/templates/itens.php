<style>
    .produto-item {
        transition: transform 0.3s ease; /* Transição suave para a mudança de tamanho */
    }

    .produto-item.expanded {
        transform: scale(1.1); /* Aumenta o card clicado */
        z-index: 10; /* Garante que o card clicado fique acima dos outros */
    }

    .saldo-details {
        display: none;
    }
</style>

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
                $depositos = array_unique(array_map(fn($produto) => !empty($produto['nome_deposito']) ? htmlspecialchars($produto['nome_deposito']) : 'Sem Depósito', $itens));
                foreach ($depositos as $depositoOption) {
                    echo "<option value=\"" . strtolower($depositoOption) . "\">$depositoOption</option>";
                }
                ?>
            </select>

            <label for="categoria">Categoria:</label>
            <select name="categoria" id="categoria" class="filter" onchange="filtrarProdutos()">
                <option value="">Todas</option>
                <?php
                $categorias = array_unique(array_map(fn($produto) => !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : 'Sem Categoria', $itens));
                foreach ($categorias as $categoriaOption) {
                    echo "<option value=\"" . strtolower($categoriaOption) . "\">$categoriaOption</option>";
                }
                ?>
            </select>

            <label for="situacao">Situação:</label>
            <select name="situacao" id="situacao" class="filter" onchange="filtrarProdutos()">
                <option value="">Todas</option>
                <?php
                $situacoes = array_unique(array_map(fn($produto) => !empty($produto['situacao']) ? htmlspecialchars($produto['situacao']) : 'Sem Situação', $itens));
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
        <?php 
        // Consolidar os produtos pelo código do item e local
        $produtosAgrupados = [];
        
        foreach ($itens as $row) {
            $key = $row['codigo_item'] . '|' . $row['nome_local'];

            if (!isset($produtosAgrupados[$key])) {
                $produtosAgrupados[$key] = [
                    'descricao' => $row['descricao'],
                    'codigo_item' => $row['codigo_item'],
                    'categoria' => $row['categoria'],
                    'nome_deposito' => $row['nome_deposito'],
                    'nome_local' => $row['nome_local'],
                    'situacao' => $row['situacao'],
                    'custo_unitario' => $row['custo_unitario'],
                    'saldos' => [],
                    'saldo_total' => 0
                ];
            }

            // Adiciona saldo e validade ao agrupamento
            $produtosAgrupados[$key]['saldos'][] = [
                'saldo' => $row['saldo'],
                'validade' => !empty($row['validade']) ? date('d/m/Y', strtotime($row['validade'])) : 'Sem validade'
            ];

            // Acumula o saldo total
            $produtosAgrupados[$key]['saldo_total'] += $row['saldo'];
        }

        // Exibir os produtos agrupados
        foreach ($produtosAgrupados as $produto): ?>
            <div class="card produto-item" 
                 data-nome="<?php echo strtolower(htmlspecialchars($produto['descricao'])); ?>"
                 data-categoria="<?php echo strtolower(htmlspecialchars($produto['categoria'])); ?>"
                 data-deposito="<?php echo strtolower(!empty($produto['nome_deposito']) ? htmlspecialchars($produto['nome_deposito']) : 'sem depósito'); ?>"
                 data-situacao="<?php echo strtolower(htmlspecialchars($produto['situacao'])); ?>"
                 data-custo="<?php echo htmlspecialchars($produto['custo_unitario'] * $produto['saldo_total']); ?>"
                 onclick="toggleDetails(this)">
                <h2><?php echo htmlspecialchars($produto['descricao']); ?></h2>
                <p><strong>Código do item:</strong> <?php echo htmlspecialchars($produto['codigo_item']); ?></p>
                <p><strong>Situação:</strong> <?php echo htmlspecialchars($produto['situacao']); ?></p>

                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($produto['categoria']); ?></p>

                <p><strong>Depósito:</strong> <?php echo htmlspecialchars($produto['nome_deposito']); ?></p>
                <p><strong>Local:</strong> <?php echo htmlspecialchars($produto['nome_local']); ?></p>
                <p><strong>Saldo Total:</strong> <?php echo htmlspecialchars($produto['saldo_total']); ?></p>
                <p><strong>Valor Unitário:</strong> R$<?php echo number_format(htmlspecialchars($produto['custo_unitario']), 2, ',', '.'); ?></p>

                <!-- Área de saldos e validades inicialmente oculta -->
                <div class="saldo-details" style="display: none;">
                    <strong>Validades e Saldos:</strong>
                    <ul>
                        <?php foreach ($produto['saldos'] as $saldoInfo): ?>
                            <li>Saldo: <?php echo htmlspecialchars($saldoInfo['saldo']); ?> | Validade: <?php echo htmlspecialchars($saldoInfo['validade']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
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

function toggleDetails(card) {
    let details = card.querySelector('.saldo-details');
    if (details.style.display === "none") {
        details.style.display = "block";
        card.classList.add('expanded'); // Aumenta o tamanho do card
    } else {
        details.style.display = "none";
        card.classList.remove('expanded'); // Restaura o tamanho do card
    }
}

window.onload = calcularCustoTotal;
</script>
