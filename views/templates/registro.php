<style>
    @media print {
        /* Oculta elementos desnecessários */
        .top-bar, .filters {
            display: none;
        }

        .card-container {
            display: block;
        }

        .card {
            width: 100%;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .card p {
            margin: 0 0 5px 0;
        }

        /* Exibe o custo total na impressão */
        #custoTotal {
            display: block !important;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Ocultar os campos específicos durante a impressão */
        .card p:nth-child(2), /* ID Registro */
        .card p:nth-child(3), /* ID Solicitação */
        .card p:nth-child(4), /* ID Produto */
        .card p:nth-child(8)  /* Subsetor */
        {
            display: none;
        }

        /* Remove margens e preenchimentos desnecessários */
        body {
            margin: 0;
            padding: 0;
        }
    }

    /* Estilo para o botão de devolução */
    .btn-devolucao {
        display: none;
        background-color: #f44336;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 5px;
    }

    /* Estilo para o modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
</style>
<div class="container">
    <h1>Registros de saídas</h1>
    
    <!-- Modal para devolução -->
    <div id="modalDevolucao" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="fecharModal">&times;</span>
            <h2>Devolução de Produto</h2>
            <form action="<?php echo $base_url; ?>Registro/mvdevolucao" method="POST" id="formDevolucao">
                <input type="hidden" name="id_registro" id="id_registro_modal">
                <input type="hidden" name="id_produto" id="id_produto_modal">
                <input type="hidden" name="id_solicitacao" id="id_solicitacao_modal">

                <div class="form-group">
                    <label for="quantidadeDevolvida">Quantidade a Devolver:</label>
                    <input type="number" name="quantidadeDevolvida" id="quantidadeDevolvida" min="1" required>
                    <small id="quantidade-disponivel"></small>
                </div>
                <button type="submit">Confirmar Devolução</button>
            </form>
        </div>
    </div>

    <div class="filters">
        <input type="text" id="idSolicitacao" placeholder="Código da solicitação">

        <form method="GET" action="<?php echo $base_url; ?>Registro/" class="search-form">
            <input type="text" id="search-input" name="search" placeholder="Descrição do item" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        </form>

        <label for="tipo">Tipo:</label>
        <select id="tipo" class="filter">
            <option value="">Todos</option>
            <?php
                $tipos = array_unique(array_column($registros, 'tipo'));
                foreach ($tipos as $tipo) {
                    echo "<option value=\"".htmlspecialchars($tipo)."\">".htmlspecialchars($tipo)."</option>";
                }
            ?>
        </select>

        <label for="setor">Setor:</label>
        <select id="setor" class="filter">
            <option value="">Todos</option>
            <?php
                $setores = array_unique(array_column($registros, 'setor'));
                foreach ($setores as $set) {
                    if (!empty($set)) {
                        echo "<option value=\"".htmlspecialchars($set)."\">".htmlspecialchars($set)."</option>";
                    }
                }
            ?>
        </select>

        <label for="subSetor">Subsetor:</label>
        <select id="subSetor" class="filter">
            <option value="">Todos</option>
            <?php
                $subSetores = array_unique(array_column($registros, 'subSetor'));
                foreach ($subSetores as $subSet) {
                    if (!empty($subSet)) {
                        echo "<option value=\"".htmlspecialchars($subSet)."\">".htmlspecialchars($subSet)."</option>";
                    }
                }
            ?>
        </select>

        <div class="date-filters">
            <label for="dataInicio">Data Início:</label>
            <input type="date" id="dataInicio" class="filter">

            <label for="dataFim">Data Fim:</label>
            <input type="date" id="dataFim" class="filter" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <button id="imprimirButton" class="imprimir-btn">SALVAR</button>
    </div>

    <div id="custoTotal">
        <strong>Custo Total:</strong> <span>R$ 0,00</span>
    </div>
    <a href="<?php echo $base_url; ?>Registro/sintetico">Tabela por setor</a>

    <div class="card-container">
        <?php if (isset($registros) && !empty($registros)): ?>
            <?php foreach ($registros as $row): ?>
                <?php
                    $id_registro = htmlspecialchars($row['codigo_registro']);
                    $tipo = htmlspecialchars($row['tipo']);
                    $quantidade = htmlspecialchars($row['quantidade']);
                    $id_produto = htmlspecialchars($row['codigo_item']);
                    $id_solicitacao = htmlspecialchars($row['codigo_solicitacao']);
                    $data_registro = htmlspecialchars($row['data_registro']);
                    $custo = is_numeric($row['custo']) ? $row['custo'] : 0;
                    $prod = htmlspecialchars($row['descricao_item']);
                    $setor = !empty($row['setor']) ? htmlspecialchars($row['setor']) : '';
                    $subSetor = !empty($row['subSetor']) ? htmlspecialchars($row['subSetor']) : '';
                    $usuario = htmlspecialchars($row['nome_usuario']);
                    $data_formatada = date("d/m/Y H:i:s", strtotime($data_registro));
                    $data_para_comparacao = date("Y-m-d", strtotime($data_registro));
                ?>
                <div class="card" data-setor="<?php echo $setor; ?>" data-subSetor="<?php echo $subSetor; ?>" 
                     data-data="<?php echo $data_para_comparacao; ?>" data-custo="<?php echo $custo; ?>">
                    <p><strong>TIPO:</strong> <?php echo $tipo; ?></p>
                    <input type="hidden" name="id_registro" value="<?php echo $id_registro; ?>">
                    <p><strong>Código Solicitação:</strong> <?php echo $id_solicitacao; ?></p>
                    <p><strong>Código do item:</strong> <?php echo $id_produto; ?></p>
                    <p><strong>Descrição do item:</strong> <?php echo $prod; ?></p>
                    <p class="quantidade"><strong>Quantidade:</strong> <?php echo $quantidade; ?></p>
                    <p><strong>Setor:</strong> <?php echo $setor; ?></p>
                    <p><strong>Subsetor:</strong> <?php echo $subSetor; ?></p>
                    <p><strong>Custo:</strong> R$ <?php echo number_format($custo, 2, ',', '.'); ?></p>
                    <p><strong>Data e Hora do Registro:</strong> <?php echo $data_formatada; ?></p>
                    <p><strong>Realizado por:</strong> <?php echo $usuario; ?></p>

                    <?php if (strtolower($tipo) == 'solicitação'): ?>
                        <button type="button" class="btn-devolucao"
                                data-id-registro="<?php echo $id_registro; ?>"
                                data-quantidade="<?php echo $quantidade; ?>"
                                data-id-produto="<?php echo $id_produto; ?>"
                                data-id-solicitacao="<?php echo $id_solicitacao; ?>">
                            Devolução
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-registros">
                Nenhum registro encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Variável para controlar o card selecionado
// Variável para controlar o card selecionado
let cardSelecionado = null;

// Mostrar/ocultar botão de devolução ao clicar no card
document.querySelectorAll('.card').forEach(function(card) {
    card.addEventListener('click', function(e) {
        // Verifica se o clique foi diretamente no botão de devolução
        if (e.target.classList.contains('btn-devolucao')) {
            return; // Se foi no botão, não faz nada (o evento do botão já trata)
        }

        const tipo = card.querySelector('p:nth-child(1)').innerText.toLowerCase();
        if (!tipo.includes('solicitação')) return;

        const btnDevolucao = card.querySelector('.btn-devolucao');
        if (!btnDevolucao) return;

        // Esconde o botão do card anterior (se existir e for diferente)
        if (cardSelecionado && cardSelecionado !== card) {
            cardSelecionado.querySelector('.btn-devolucao').style.display = 'none';
        }

        // Alterna a visibilidade do botão
        const deveMostrar = btnDevolucao.style.display === 'none' || !btnDevolucao.style.display;
        btnDevolucao.style.display = deveMostrar ? 'inline-block' : 'none';
        cardSelecionado = deveMostrar ? card : null;
    });
});

// Configurar o modal de devolução (sem alterações)
document.querySelectorAll('.btn-devolucao').forEach(function(button) {
    button.addEventListener('click', function(e) {
        e.stopPropagation(); // Impede que o evento de clique do card seja acionado
        
        const idRegistro = this.getAttribute('data-id-registro');
        const quantidade = this.getAttribute('data-quantidade');
        const idProduto = this.getAttribute('data-id-produto');
        const idSolicitacao = this.getAttribute('data-id-solicitacao');

        // Preenche o modal
        document.getElementById('id_registro_modal').value = idRegistro;
        document.getElementById('quantidadeDevolvida').value = quantidade;
        document.getElementById('quantidadeDevolvida').max = quantidade;
        document.getElementById('id_produto_modal').value = idProduto;
        document.getElementById('id_solicitacao_modal').value = idSolicitacao;
        document.getElementById('quantidade-disponivel').textContent = `(Disponível: ${quantidade})`;

        // Exibe o modal
        document.getElementById('modalDevolucao').style.display = 'block';
    });
});

// Validação do formulário de devolução
document.getElementById('formDevolucao').addEventListener('submit', function(event) {
    const quantidadeDevolvida = parseFloat(document.getElementById('quantidadeDevolvida').value);
    const quantidadeDisponivel = parseFloat(document.getElementById('quantidadeDevolvida').max);

    if (isNaN(quantidadeDevolvida) || quantidadeDevolvida <= 0) {
        event.preventDefault();
        alert('Por favor, insira uma quantidade válida maior que zero.');
        return false;
    }

    if (quantidadeDevolvida > quantidadeDisponivel) {
        event.preventDefault();
        alert(`A quantidade devolvida não pode ser maior que ${quantidadeDisponivel}.`);
        return false;
    }
});

// Fechar o modal
document.getElementById('fecharModal').addEventListener('click', function() {
    document.getElementById('modalDevolucao').style.display = 'none';
});

// Fechar modal ao clicar fora
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('modalDevolucao')) {
        document.getElementById('modalDevolucao').style.display = 'none';
    }
});

// Função para aplicar filtros
function aplicarFiltros() {
    const tipoFiltro = document.getElementById('tipo').value.toLowerCase();
    const setorFiltro = document.getElementById('setor').value.toLowerCase();
    const subSetorFiltro = document.getElementById('subSetor').value.toLowerCase();
    const dataInicioFiltro = document.getElementById('dataInicio').value;
    const dataFimFiltro = document.getElementById('dataFim').value;
    const idSolicitacaoFiltro = document.getElementById('idSolicitacao').value.trim().toLowerCase();
    const descricaoFiltro = document.getElementById('search-input').value.trim().toLowerCase();
    let custoTotal = 0;

    document.querySelectorAll('.card').forEach(function(card) {
        const tipo = card.querySelector('p:nth-child(1)').innerText.toLowerCase();
        const setor = card.getAttribute('data-setor').toLowerCase();
        const subSetor = card.getAttribute('data-subSetor').toLowerCase();
        const data = card.getAttribute('data-data');
        const custo = parseFloat(card.getAttribute('data-custo'));
        const quantidade = parseFloat(card.querySelector('.quantidade').innerText.split(': ')[1]);
        const idSolicitacao = card.querySelector('p:nth-child(3)').innerText.replace("Código Solicitação:", "").trim().toLowerCase();
        const nomeProduto = card.querySelector('p:nth-child(5)').innerText.toLowerCase();

        // Verificar filtros de data
        let dataDentroDoIntervalo = true;
        if (dataInicioFiltro && new Date(data) < new Date(dataInicioFiltro)) {
            dataDentroDoIntervalo = false;
        }
        if (dataFimFiltro && new Date(data) > new Date(dataFimFiltro)) {
            dataDentroDoIntervalo = false;
        }

        // Verificar se o card passa em todos os filtros
        if (tipo.includes(tipoFiltro) &&
            setor.includes(setorFiltro) &&
            subSetor.includes(subSetorFiltro) &&
            dataDentroDoIntervalo &&
            idSolicitacao.includes(idSolicitacaoFiltro) &&
            nomeProduto.includes(descricaoFiltro)) {
            card.style.display = 'block';
            custoTotal += custo * quantidade;
        } else {
            card.style.display = 'none';
        }
    });

    // Atualizar custo total formatado
    document.getElementById('custoTotal').querySelector('span').innerText = 
        `R$ ${custoTotal.toFixed(2).replace('.', ',')}`;
}

// Event listeners para os filtros
document.querySelectorAll('.filter, #search-input, #idSolicitacao').forEach(function(input) {
    input.addEventListener('input', aplicarFiltros);
    input.addEventListener('change', aplicarFiltros);
});

// Aplicar filtros ao carregar a página
window.addEventListener('load', function() {
    aplicarFiltros();
    
    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>
});

// Botão de imprimir
document.getElementById('imprimirButton').addEventListener('click', function() {
    window.print();
});
</script>