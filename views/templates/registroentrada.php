<div class="container">
    <h1>Registros de entradas</h1>

    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        <script type="text/javascript">
            window.onload = function() {
                alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
            };
        </script>
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>

    <div class="filters">
        <!-- Adicionamos o onkeyup para chamar a função de filtragem ao digitar -->
        <input type="text" id="idSolicitacao" placeholder="ID da solicitação" onkeyup="aplicarFiltros()">

        <form method="GET" action="<?php echo $base_url; ?>Registro/entrada" class="search-form">
            <input type="text" name="search" placeholder="Descrição do item" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="search-input">
        </form>

        <label for="tipo">Tipo:</label>
        <select id="tipo" class="filter" name="tipo" onchange="aplicarFiltros()">
            <option value="">Todos</option>
            <?php
                $tipos = array_unique(array_column($registros, 'tipo'));
                foreach ($tipos as $tipo) {
                    $tipo = htmlspecialchars($tipo);
                    echo "<option value=\"$tipo\" " . (isset($_GET['tipo']) && $_GET['tipo'] == $tipo ? 'selected' : '') . ">$tipo</option>";
                }
            ?>
        </select>

        <label for="dataInicio">Data Início:</label>
        <input type="date" id="dataInicio" class="filter" name="dataInicio" value="<?php echo isset($_GET['dataInicio']) ? $_GET['dataInicio'] : ''; ?>" onchange="aplicarFiltros()">

        <label for="dataFim">Data Fim:</label>
        <input type="date" id="dataFim" class="filter" name="dataFim" value="<?php echo isset($_GET['dataFim']) ? $_GET['dataFim'] : date('Y-m-d'); ?>" onchange="aplicarFiltros()">
    </div>

    <div id="custoTotalContainer">
        <strong>Custo Total dos Registros Visíveis:</strong> <span id="custoTotal">R$ 0,00</span>
    </div>
    <a href="<?php echo $base_url; ?>Registro/sinteticoentrada">Tabela por Tipo</a>

    <div class="card-container" id="card-container">
        <?php if (isset($registros) && !empty($registros)): ?>
            <?php foreach ($registros as $row): ?>
                <?php
                    $id_registro = htmlspecialchars($row['codigo_registro']);
                    $tipo = htmlspecialchars($row['tipo']);
                    $quantidade = htmlspecialchars($row['quantidade']);
                    $id_produto = htmlspecialchars($row['codigo_item']);
                    $prod = htmlspecialchars($row['descricao_item']);
                    $numero_nota = htmlspecialchars($row['numero_nota']);
                    $id_solicitacao = htmlspecialchars($row['codigo_solicitacao']);
                    $tipo_compra = htmlspecialchars($row['tipo_compra']);
                    
                    $data_registro = htmlspecialchars($row['data_registro']);
                    $custo = htmlspecialchars($row['custo']);
                    $data_formatada = date("d/m/Y H:i:s", strtotime($data_registro));                    $usuario = htmlspecialchars($row['nome_usuario']);
                ?>
                <!-- Adicionamos o atributo data-id-solicitacao para possibilitar o filtro -->
                <div class="card" 
                     data-tipo="<?php echo $tipo; ?>" 
                     data-data="<?php echo $data_registro; ?>" 
                     data-custo="<?php echo $custo; ?>" 
                     data-quantidade="<?php echo $quantidade; ?>"
                     data-id-solicitacao="<?php echo $id_solicitacao; ?>">
                    <h2 style="text-align: center;"><?php echo $tipo; ?></h2>
                    <input type="hidden" name="id_registro" value="<?php echo $id_registro; ?>">
                    <p><strong>Código Solicitação:</strong> <?php echo $id_solicitacao; ?></p>
                    <p><strong>Código do item:</strong> <?php echo $id_produto; ?></p>
                    <p><strong>Descrição do item:</strong> <?php echo $prod; ?></p>
                    <p><strong>Quantidade:</strong> <?php echo $quantidade; ?></p>
                    <p><strong>Tipo de Compra:</strong> <?php echo $tipo_compra; ?></p>
                    <p><strong>Número da Nota:</strong> <?php echo $numero_nota; ?></p>
                    <p><strong>Custo:</strong> R$ <?php echo $custo; ?></p>
                    <p><strong>Data e hora do registro Registro:</strong> <?php echo $data_formatada; ?></p>
                    <p><strong>Realizado por:</strong> <?php echo $usuario; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-registros">
                Nenhum registro encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
function aplicarFiltros() {
    var idSolicitacaoFiltro = document.getElementById('idSolicitacao').value.trim();
    var tipoFiltro = document.getElementById('tipo').value.toLowerCase();
    var dataInicioFiltro = document.getElementById('dataInicio').value;
    var dataFimFiltro = document.getElementById('dataFim').value;
    var cards = document.querySelectorAll('.card');
    var custoTotal = 0;
    
    cards.forEach(function(card) {
        var mostrar = true;

        // Filtrar por ID da solicitação
        var idSolicitacaoCard = card.getAttribute('data-id-solicitacao');
        if (idSolicitacaoFiltro && !idSolicitacaoCard.toLowerCase().includes(idSolicitacaoFiltro.toLowerCase())) {
            mostrar = false;
        }

        // Filtrando por tipo
        var tipoCard = card.getAttribute('data-tipo').toLowerCase();
        if (tipoFiltro && tipoCard.indexOf(tipoFiltro) === -1) {
            mostrar = false;
        }

        // Filtrando por data
        var dataCard = card.getAttribute('data-data');
        if (dataInicioFiltro) {
            var dataInicio = new Date(dataInicioFiltro + "T00:00:00");
            if (new Date(dataCard) < dataInicio) {
                mostrar = false;
            }
        }

        if (dataFimFiltro) {
            var dataFim = new Date(dataFimFiltro + "T23:59:59");
            if (new Date(dataCard) > dataFim) {
                mostrar = false;
            }
        }

        // Calcula o custo total dos registros visíveis
        var custoCard = parseFloat(card.getAttribute('data-custo')) || 0;
        var quantidadeCard = parseInt(card.getAttribute('data-quantidade')) || 0;
        if (mostrar) {
            custoTotal += custoCard * quantidadeCard;
        }

        // Exibe ou oculta o card
        card.style.display = mostrar ? 'block' : 'none';
    });

    // Atualiza o custo total no HTML
    document.getElementById('custoTotal').innerText = `R$ ${custoTotal.toFixed(2)}`;
}

// Chama a função para aplicar os filtros assim que a página carrega
window.onload = aplicarFiltros;
</script>
