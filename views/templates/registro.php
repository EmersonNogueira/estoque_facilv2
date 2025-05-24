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
            font-size: 12px; /* Reduz o tamanho da fonte para mais informações */
        }

        .card p {
            margin: 0 0 5px 0; /* Reduz o espaçamento entre as linhas */
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

</style>

<div class="container">
    <h1>Registros de saídas</h1>
    <!-- Modal para inserção da quantidade de devolução -->
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
                </div>
                <button type="submit">Confirmar Devolução</button>
            </form>
        </div>
    </div>
    <div class="filters">
        <input type="text" id="idSolicitacao" class="" placeholder="Código da solicitação">

        <form method="GET" action="<?php echo $base_url; ?>Registro/" class="search-form">

            <input type="text" id="search-input" name="search" placeholder="Descrição do item" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="search-input">
        </form>

        <label for="tipo">Tipo:</label>
        <select id="tipo" class="filter">
            <option value="">Todos</option>
            <?php
                $tipos = array_unique(array_column($registros, 'tipo'));
                foreach ($tipos as $tipo) {
                    $tipo = htmlspecialchars($tipo);
                    echo "<option value=\"$tipo\">$tipo</option>";
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
                        $set = htmlspecialchars($set);
                        echo "<option value=\"$set\">$set</option>";
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
                        $subSet = htmlspecialchars($subSet);
                        echo "<option value=\"$subSet\">$subSet</option>";
                    }
                }
            ?>
        </select>

        <!-- Campos de Data agora dentro da nova div -->
        <div class="date-filters">
            <label for="dataInicio">Data Início:</label>
            <input type="date" id="dataInicio" class="filter">

            <label for="dataFim">Data Fim:</label>
            <input type="date" id="dataFim" class="filter" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <button id="imprimirButton" class="imprimir-btn">SALVAR</button>
    </div>


    <!-- Custo Total -->
    <div id="custoTotal">
        <strong>Custo Total:</strong> <span>R$ 0,00</span>
    </div>
    <a href="<?php echo $base_url; ?>Registro/sintetico">Tabela por setor</a>

    <div class="card-container">
        <?php if (isset($registros) && !empty($registros)): ?>
            <?php foreach ($registros as $row): ?>
                <?php
                    $id_registro = htmlspecialchars($row['id_registro']);
                    $tipo = htmlspecialchars($row['tipo']);
                    $quantidade = htmlspecialchars($row['quantidade']);
                    $codigo_item = htmlspecialchars($row['codigo_item']);
                    $codigo_solicitacao = htmlspecialchars($row['codigo_solicitacao']);
                    $data_registro = htmlspecialchars($row['data_registro']);
                    $custo = is_numeric($row['custo']) ? $row['custo'] : 0;

                    $descricao_item = htmlspecialchars($row['descricao_item']);
                    $setor = !empty($row['setor']) ? htmlspecialchars($row['setor']) : '';
                    $subSetor = !empty($row['subSetor']) ? htmlspecialchars($row['subSetor']) : '';
                    $usuario = htmlspecialchars($row['nome_usuario']);

                    // Formatar a data para o formato YYYY-MM-DD para facilitar a comparação
                    $data_formatada = date("d/m/Y", strtotime($data_registro));
                    $data_para_comparacao = date("Y-m-d", strtotime($data_registro)); // Formato correto para comparação
                ?>
                <div class="card" data-setor="<?php echo $setor; ?>" data-subSetor="<?php echo $subSetor; ?>" data-data="<?php echo $data_para_comparacao; ?>" data-custo="<?php echo $custo; ?>">
                    <p><strong>TIPO:</strong> <?php echo $tipo; ?></p>
                    <input type="hidden" name="id_registro" value="<?php echo $id_registro; ?>">
                    <p><strong>Código Solicitação:</strong> <?php echo $codigo_solicitacao; ?></p>
                    <p><strong>Código do item:</strong> <?php echo $codigo_item; ?></p>
                    <p><strong>Descrição do item:</strong> <?php echo $descricao_item; ?></p>
                    <p class="quantidade"><strong>Quantidade:</strong> <?php echo $quantidade; ?></p>
                    <p><strong>Setor:</strong> <?php echo $setor; ?></p>
                    <p><strong>Subsetor:</strong> <?php echo $subSetor; ?></p>
                    <p><strong>Custo:</strong> <?php echo $custo; ?></p>
                    <p><strong>Data Registro:</strong> <?php echo $data_formatada; ?></p>
                    <p><strong>Realizado por:</strong> <?php echo $usuario; ?></p>

                    <!-- Formulário para envio do ID Registro com o botão de devolução -->
                    <?php if (strtolower($tipo) == 'solicitação'): ?>
                    <!-- Formulário para envio do ID Registro com o botão de devolução -->
                    <form action="<?php echo $base_url; ?>Registro/mveditar" method="POST">
                        <input type="hidden" name="id_registro" value="<?php echo $id_registro; ?>">
                        <button type="button" class="btn-devolucao"
                                data-id-registro="<?php echo $id_registro; ?>"
                                data-quantidade="<?php echo $quantidade; ?>"
                                data-id-produto="<?php echo $id_produto; ?>"
                                data-id-solicitacao="<?php echo $id_solicitacao; ?>">
                            Devolução
                        </button>
                    </form>
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

    let cardSelecionado = null; // Armazena o card atualmente selecionado

    document.querySelectorAll('.card').forEach(function(card) {
        card.addEventListener('click', function() {
            // Encontra o botão de devolução dentro do card clicado
            const btnDevolucao = card.querySelector('.btn-devolucao');

            // Se houver um card selecionado anteriormente, oculta o botão de devolução dele
            if (cardSelecionado && cardSelecionado !== card) {
                const btnDevolucaoAnterior = cardSelecionado.querySelector('.btn-devolucao');
                btnDevolucaoAnterior.style.display = 'none'; // Oculta o botão do card anterior
            }

            // Alterna a visibilidade do botão de devolução do card clicado
            if (btnDevolucao.style.display === 'none' || btnDevolucao.style.display === '') {
                btnDevolucao.style.display = 'inline-block'; // Exibe o botão
                cardSelecionado = card; // Atualiza o card selecionado
            } else {
                btnDevolucao.style.display = 'none'; // Oculta o botão
                cardSelecionado = null; // Remove a referência ao card selecionado
            }
        });
    });

    document.getElementById('formDevolucao').addEventListener('submit', function(event) {
        // Obter os valores da quantidade devolvida e da quantidade disponível
        const quantidadeDevolvida = parseFloat(document.getElementById('quantidadeDevolvida').value);
        const quantidadeDisponivel = parseFloat(document.querySelector('.btn-devolucao[data-id-registro="' + 
            document.getElementById('id_registro_modal').value + '"]').getAttribute('data-quantidade'));

        // Verificar se a quantidade devolvida é válida
        if (quantidadeDevolvida > quantidadeDisponivel) {
            event.preventDefault(); // Impede o envio do formulário
            alert('A quantidade devolvida não pode ser maior que a quantidade registrada (' + quantidadeDisponivel + ').');
            return false;
        }
    });
// Função para aplicar os filtros
// Função para aplicar os filtros
// Função para aplicar os filtros
function aplicarFiltros() {
    let tipoFiltro = document.getElementById('tipo').value.toLowerCase();
    let setorFiltro = document.getElementById('setor').value.toLowerCase();
    let subSetorFiltro = document.getElementById('subSetor').value.toLowerCase();
    let dataInicioFiltro = document.getElementById('dataInicio').value;
    let dataFimFiltro = document.getElementById('dataFim').value;
    let idSolicitacaoFiltro = document.getElementById('idSolicitacao').value.trim().toLowerCase(); // Filtro para o ID de Solicitação
    let descricaoFiltro = document.getElementById('search-input').value.trim().toLowerCase(); // Filtro para a Descrição do Item (Produto)
    let custoTotal = 0;

    let cards = document.querySelectorAll('.card');
    cards.forEach(function(card) {
        let tipo = card.querySelector('p:nth-child(1)').innerText.toLowerCase();
        let setor = card.getAttribute('data-setor').toLowerCase();
        let subSetor = card.getAttribute('data-subSetor').toLowerCase();
        let data = card.getAttribute('data-data');
        let custo = parseFloat(card.getAttribute('data-custo'));
        let quantidade = parseFloat(card.querySelector('p:nth-child(6)').innerText.split(': ')[1]);

        // Extração correta do ID Solicitação
        let idSolicitacao = card.querySelector('p:nth-child(3)').innerText.replace("Código Solicitação:", "").trim().toLowerCase(); // Pega o ID de Solicitação do card

        // Extração do nome do produto para filtro de descrição
        let nomeProduto = card.querySelector('p:nth-child(5)').innerText.toLowerCase().replace("Produto:", "").trim();

        let dataDentroDoIntervalo = true;

        // Verificar se a data está dentro do intervalo
        if (dataInicioFiltro && new Date(data) < new Date(dataInicioFiltro)) {
            dataDentroDoIntervalo = false;
        }
        if (dataFimFiltro && new Date(data) > new Date(dataFimFiltro)) {
            dataDentroDoIntervalo = false;
        }

        // Verificar se o ID de Solicitação corresponde ao filtro
        let idSolicitacaoCorreto = idSolicitacao.includes(idSolicitacaoFiltro);

        // Verificar se a descrição do produto corresponde ao filtro de descrição
        let descricaoCorreta = nomeProduto.includes(descricaoFiltro);

        // Verificar se todos os filtros aplicados são verdadeiros
        if (
            tipo.includes(tipoFiltro) &&
            setor.includes(setorFiltro) &&
            subSetor.includes(subSetorFiltro) &&
            dataDentroDoIntervalo &&
            idSolicitacaoCorreto &&
            descricaoCorreta
        ) {
            card.style.display = 'block'; // Exibe o card se passar nos filtros
            custoTotal += custo * quantidade; // Acumula o custo total
        } else {
            card.style.display = 'none'; // Oculta o card se não passar nos filtros
        }
    });

    // Atualizar o custo total
    document.getElementById('custoTotal').querySelector('span').innerText = `R$ ${custoTotal.toFixed(2)}`;
}

// Adiciona event listeners aos filtros para chamar a função sempre que o filtro for alterado
document.querySelectorAll('.filter, #search-input').forEach(function(input) {
    input.addEventListener('input', aplicarFiltros);
});




// Adicionar evento para aplicar os filtros ao alterar qualquer filtro
document.querySelectorAll('.filter').forEach(function(input) {
    input.addEventListener('change', aplicarFiltros);
});
document.getElementById('idSolicitacao').addEventListener('input', aplicarFiltros);



// Adicionar event listener ao novo filtro
document.querySelectorAll('.filter').forEach(function(element) {
    element.addEventListener('change', aplicarFiltros);
});

// Aplica os filtros ao carregar a página
window.onload = aplicarFiltros;

// Exibe mensagem de confirmação se existir
<?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
    window.addEventListener('load', function() {
        alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
        // Executa novamente o cálculo de custo após o alerta
        aplicarFiltros();
    });
<?php else: ?>
    // Aplica os filtros automaticamente ao carregar a página
    window.onload = aplicarFiltros;
<?php endif; ?>

// Script para imprimir a página de forma resumida
document.getElementById('imprimirButton').addEventListener('click', function() {
    window.print(); // Chama a função para imprimir
});

// Função para abrir o modal e preencher os dados do registro
document.querySelectorAll('.btn-devolucao').forEach(function(button) {
    button.addEventListener('click', function() {
        var idRegistro = this.getAttribute('data-id-registro');
        var quantidade = this.getAttribute('data-quantidade');
        var idProduto = this.getAttribute('data-id-produto');
        var idSolicitacao = this.getAttribute('data-id-solicitacao');

        // Preenche os campos do modal com os dados
        document.getElementById('id_registro_modal').value = idRegistro;
        document.getElementById('quantidadeDevolvida').value = quantidade;
        document.getElementById('id_produto_modal').value = idProduto;
        document.getElementById('id_solicitacao_modal').value = idSolicitacao;

        // Exibe o modal
        document.getElementById('modalDevolucao').style.display = 'block';
    });
});

// Fechar o modal ao clicar no botão de fechar
document.getElementById('fecharModal').addEventListener('click', function() {
    document.getElementById('modalDevolucao').style.display = 'none';
});






// Fechar o modal se o usuário clicar fora do conteúdo do modal
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('modalDevolucao')) {
        document.getElementById('modalDevolucao').style.display = 'none';
    }
});


</script>

