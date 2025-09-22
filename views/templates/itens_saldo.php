
<style>
.saldo-alocar-maior-que-zero {
    background-color: #ffcccc !important; /* Vermelho claro */
    border: 2px solid #ff0000; /* Borda vermelha */
}

</style>


<!-- Tabela oculta que será usada para gerar o PDF -->
<table id="tabela-pdf" style="display: none;">
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Categoria</th>
            <th>Saldo</th>
            <th>Situação</th>
            <th>Valor Unitário</th>
            <th>Descrição do Pregão</th>
            <th>Unidade de Medida</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($itens as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                <td><?php echo isset($row['saldo_total']) ? intval($row['saldo_total']) : 0; ?></td>
                <td><?php echo htmlspecialchars($row['situacao'] ?? 'N/A'); ?></td>
                <td><?php echo isset($row['custo_unitario']) ? number_format($row['custo_unitario'], 2, ',', '.') : 'N/A'; ?></td>
                <td><?php echo htmlspecialchars($row['desc_pregao'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['unidade_medida'] ?? 'N/A'); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="container">

    <h1>Itens</h1>
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
        <!-- Botão para gerar PDF -->
        <button onclick="gerarPDF()" class="btn-register">Gerar PDF</button>
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
        <div class="card produto-item <?php echo ($saldo_alocar > 0) ? 'saldo-alocar-maior-que-zero' : ''; ?>" 
            data-nome="<?php echo strtolower($descricao); ?>" 
            data-categoria="<?php echo strtolower($categoria); ?>"
            data-situacao="<?php echo strtolower($situacao); ?>">
            <h2><?php echo $descricao; ?></h2>
            <p><strong>Código do item:</strong> <?php echo $codigo; ?></p>
            <p><strong>Categoria:</strong> <?php echo $categoria; ?></p>
            <p><strong>Saldo em estoque:</strong> <?php echo $saldo; ?></p>
            <p><strong>Saldo para alocar:</strong> <?php echo $saldo_alocar; ?></p>

            <p><strong>Situação:</strong> <?php echo $situacao; ?></p>
            <p>
                <strong>Valor Unitário:</strong> 
                R$<?php echo number_format($custoUnitario, 2, ',', '.'); ?>
                <!-- valor bruto para cálculo -->
                <span class="valor-unitario" data-valor="<?php echo $custoUnitario; ?>"></span>
            </p>
            <p><strong>Visível:</strong> <?php echo $visivel; ?></p>
            <p><strong>Descrição do Pregão:</strong> <?php echo $pregao; ?></p>
            <p><strong>Unidade de Medida:</strong> <?php echo $unidadeMedida; ?></p>

            

            <div class="card-buttons">
                <form method="POST" action="<?php echo $base_url; ?>Item/alocar">
                    <input type="hidden" name="codigo_item" value="<?php echo $codigo; ?>">
                    <input type="hidden" name="saldo_alocar" value="<?php echo $saldo_alocar; ?>">
                    <input type="hidden" name="descricao" value="<?php echo $descricao; ?>">
                    <input type="hidden" name="situacao" value="<?php echo $situacao; ?>">

                    <button type="submit" class="btn-register">Alocar saldo</button>
                </form>
                <form method="POST" action="<?php echo $base_url; ?>Registro/viewcompra">
                    <input type="hidden" name="codigo_item" value="<?php echo $codigo; ?>">
                    <input type="hidden" name="situacao" value="<?php echo $situacao; ?>">
                    <input type="hidden" name="descricao" value="<?php echo $descricao; ?>">

                    <input type="hidden" name="custo_atual" value="<?php echo $custoUnitario; ?>">
                    <input type="hidden" name="saldo_atual" value="<?php echo $saldo; ?>">
                    <input type="hidden" name="saldo_alocar" value="<?php echo $saldo_alocar; ?>">

                    <button type="submit" class="btn-register">Registrar Compra</button>
                </form>
                <form method="POST" action="<?php echo $base_url; ?>Item/item_editar">
                    <input type="hidden" name="codigo_item" value="<?php echo $codigo; ?>">
                    <input type="hidden" name="situacao" value="<?php echo $situacao; ?>">
                    <input type="hidden" name="descricao" value="<?php echo $descricao; ?>">
                    <input type="hidden" name="unidade_medida" value="<?php echo $unidadeMedida; ?>">
                    <input type="hidden" name="categoria" value="<?php echo $categoria; ?>">

                    <input type="hidden" name="pregao" value="<?php echo $pregao; ?>">

                    <button type="submit" class="btn-register">Editar</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Adicione isto no final do seu HTML, antes de fechar o body -->
    <div id="notificacao-container"></div>

</div>


<script>
function calcularCustoTotal() {
    let custoTotal = 0;
    document.querySelectorAll('.produto-item').forEach(produto => {
        if (produto.style.display !== "none") {
            let custoUnitario = parseFloat(produto.querySelector(".valor-unitario").dataset.valor) || 0;
            let saldo = parseInt(produto.querySelector("p:nth-of-type(3)").textContent.replace("Saldo em estoque:", "").trim()) || 0;
            let saldoAlocar = parseInt(produto.querySelector("p:nth-of-type(4)").textContent.replace("Saldo para alocar:", "").trim()) || 0;
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

<script>
function mostrarNotificacao(mensagem, tipo = 'sucesso') {
    const container = document.getElementById('notificacao-container');
    const notificacao = document.createElement('div');
    
    notificacao.className = `notificacao ${tipo}`;
    notificacao.textContent = mensagem;
    
    container.appendChild(notificacao);
    
    // Força o reflow para a animação funcionar
    void notificacao.offsetWidth;
    
    notificacao.classList.add('visivel');
    
    // Remove a notificação após 5 segundos
    setTimeout(() => {
        notificacao.classList.remove('visivel');
        setTimeout(() => {
            container.removeChild(notificacao);
        }, 300);
    }, 5000);
}

// Modificação para garantir que a notificação apareça mesmo se houver outros eventos onload
document.addEventListener('DOMContentLoaded', function() {
    calcularCustoTotal();
    
    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        mostrarNotificacao('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>', 'sucesso');
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>
});
</script>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.21/jspdf.plugin.autotable.min.js"></script>

<script>
function gerarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.text("Lista de Itens Filtrados", 14, 10);

    // Obter os filtros atuais
    const categoriaFiltro = document.getElementById("categoria").value.toLowerCase();
    const situacaoFiltro = document.getElementById("situacao").value.toLowerCase();
    
    const tabela = document.getElementById("tabela-pdf");
    const dados = [];

    // Percorre as linhas da tabela e filtra conforme os mesmos critérios da tela
    for (let i = 1; i < tabela.rows.length; i++) {
        const linha = tabela.rows[i];
        const categoria = linha.cells[1].textContent.toLowerCase();
        const situacao = linha.cells[3].textContent.toLowerCase();
        
        // Aplica os mesmos filtros que estão na tela
        const categoriaMatch = categoriaFiltro === "" || categoria === categoriaFiltro;
        const situacaoMatch = situacaoFiltro === "" || situacao === situacaoFiltro;
        
        if (categoriaMatch && situacaoMatch) {
            const descricao = linha.cells[0].textContent;
            const saldo = linha.cells[2].textContent;
            const valor_unitario = linha.cells[4].textContent;
            const descricao_pregao = linha.cells[5].textContent;
            const unidade_medida = linha.cells[6].textContent;

            dados.push([
                descricao, 
                linha.cells[1].textContent, // Categoria
                saldo, 
                linha.cells[3].textContent, // Situação
                valor_unitario, 
                descricao_pregao, 
                unidade_medida
            ]);
        }
    }

    doc.autoTable({
        head: [['Descrição', 'Categoria', 'Saldo', 'Situação', 'Valor Unitário', 'Descrição do Pregão', 'Unidade de Medida']],
        body: dados,
        startY: 20,
        theme: 'striped',
        styles: { fontSize: 10, cellPadding: 2 }
    });

    doc.save('Lista_de_Itens_Filtrados.pdf');
}
</script>