<div class="filters">
  <!-- Filtro por Tipo -->
  <label for="tipo">Tipo:</label>
  <select id="tipo" class="filter">
    <option value="">Todos</option>
    <?php
      $tipos = array_unique(array_column($registros, 'tipo'));
      sort($tipos);
      foreach ($tipos as $tipo) {
          if (!empty($tipo)) {
              $tipo = htmlspecialchars($tipo);
              echo "<option value=\"$tipo\">$tipo</option>";
          }
      }
    ?>
  </select>
  <!-- Filtro por Setor -->
  <label for="setor">Setor:</label>
  <select id="setor" class="filter">
    <option value="">Todos</option>
    <?php
        // Cria uma lista única de setores e os exibe como opções
        $setores = array_unique(array_column($registros, 'setor'));
        foreach ($setores as $set) {
            if (empty($set)) {
                $set = 'Ajuste Negativo'; // Caso o setor seja vazio ou null, substitui por "Ajuste Negativo"
            } else {
                $set = htmlspecialchars($set);
            }
            echo "<option value=\"$set\">$set</option>";
        }
    ?>
  </select>

  <!-- Filtro por Produto -->
  <label for="produto">Itens:</label>
  <select id="produto" class="filter">
    <option value="">Todos</option>
    <?php
      $produtos = array_unique(array_column($registros, 'descricao_item'));
      sort($produtos);
      foreach ($produtos as $produto) {
          if (!empty($produto)) {
              $produto = htmlspecialchars($produto);
              echo "<option value=\"$produto\">$produto</option>";
          }
      }
    ?>
  </select>

  <!-- Filtro por Data Início -->
  <label for="dataInicio">Data Início:</label>
  <input type="date" id="dataInicio" class="filter">

  <!-- Filtro por Data Fim -->
  <label for="dataFim">Data Fim:</label>
  <input type="date" id="dataFim" class="filter" value="<?php echo date('Y-m-d'); ?>">

  <!-- Botão de Impressão -->
  <button onclick="imprimirTabela()">Imprimir Tabela</button>
</div>

<div id="resultados"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const setorFilter = document.getElementById('setor');
    const produtoFilter = document.getElementById('produto');
    const tipoFilter = document.getElementById('tipo');
    const dataInicioFilter = document.getElementById('dataInicio');
    const dataFimFilter = document.getElementById('dataFim');
    const resultadosContainer = document.getElementById('resultados');
    const registros = <?php echo json_encode($registros); ?>;

    function calcularCustoTotal() {
        const setorSelecionado = setorFilter.value;
        const produtoSelecionado = produtoFilter.value;
        const tipoSelecionado = tipoFilter.value;
        let dataInicio = dataInicioFilter.value;
        let dataFim = dataFimFilter.value;

        if (dataInicio) {
            dataInicio = new Date(dataInicio);
            dataInicio.setUTCHours(0, 0, 0, 0);
        }

        if (dataFim) {
            dataFim = new Date(dataFim);
            dataFim.setUTCHours(23, 59, 59, 999);
        }

        // Agrupar registros por mês
        const registrosPorMes = {};
        registros.forEach(function(registro) {
            const dataRegistro = new Date(registro.data_registro);
            if ((!dataInicio || dataRegistro >= dataInicio) &&
                (!dataFim || dataRegistro <= dataFim)) {
                const nomeSetor = registro.setor === '' || registro.setor === null ? 'Ajuste Negativo' : registro.setor;
                
                if ((!setorSelecionado || nomeSetor === setorSelecionado) &&
                    (!produtoSelecionado || registro.descricao_item === produtoSelecionado) &&
                    (!tipoSelecionado || registro.tipo === tipoSelecionado)) {
                    const mesAno = `${dataRegistro.getFullYear()}-${String(dataRegistro.getMonth() + 1).padStart(2, '0')}`;
                    if (!registrosPorMes[mesAno]) {
                        registrosPorMes[mesAno] = [];
                    }
                    registrosPorMes[mesAno].push(registro);
                }
            }
        });

        exibirTabelaPorMes(registrosPorMes);
    }

    function exibirTabelaPorMes(registrosPorMes) {
        resultadosContainer.innerHTML = '';

        if (Object.keys(registrosPorMes).length === 0) {
            resultadosContainer.innerHTML = '<p>Nenhum registro encontrado com os filtros selecionados.</p>';
            return;
        }

        for (const mesAno in registrosPorMes) {
            const [ano, mes] = mesAno.split('-');
            const nomeMes = new Date(ano, mes - 1).toLocaleString('pt-BR', { month: 'long' });

            const tabela = document.createElement('table');
            tabela.style.width = '100%';
            tabela.style.borderCollapse = 'collapse';
            tabela.border = '1';

            const thead = document.createElement('thead');
            const trHead = document.createElement('tr');
            const thSetor = document.createElement('th');
            thSetor.innerText = 'Setor';
            const thItensTotal = document.createElement('th');
            thItensTotal.innerText = 'Itens Total';
            const thCustoTotal = document.createElement('th');
            thCustoTotal.innerText = 'Custo Total';
            trHead.appendChild(thSetor);
            trHead.appendChild(thItensTotal);
            trHead.appendChild(thCustoTotal);
            thead.appendChild(trHead);
            tabela.appendChild(thead);

            const tbody = document.createElement('tbody');
            const setoresTotais = {};
            const itensTotalPorSetor = {};
            let custoTotalGeral = 0;

            registrosPorMes[mesAno].forEach(function(registro) {
                const nomeSetor = registro.setor === '' || registro.setor === null ? 'Ajuste Negativo' : registro.setor;
                const custoTotal = parseFloat(registro.custo) * parseFloat(registro.quantidade);
                const quantidadeItens = parseFloat(registro.quantidade);

                if (setoresTotais[nomeSetor]) {
                    setoresTotais[nomeSetor] += custoTotal;
                    itensTotalPorSetor[nomeSetor] += quantidadeItens;
                } else {
                    setoresTotais[nomeSetor] = custoTotal;
                    itensTotalPorSetor[nomeSetor] = quantidadeItens;
                }

                custoTotalGeral += custoTotal;
            });

            for (const setor in setoresTotais) {
                const tr = document.createElement('tr');
                const tdSetor = document.createElement('td');
                tdSetor.innerText = setor;
                const tdItensTotal = document.createElement('td');
                tdItensTotal.innerText = itensTotalPorSetor[setor].toFixed(2).replace('.', ',');
                const tdCustoTotal = document.createElement('td');
                tdCustoTotal.innerText = setoresTotais[setor].toFixed(2).replace('.', ',');
                tr.appendChild(tdSetor);
                tr.appendChild(tdItensTotal);
                tr.appendChild(tdCustoTotal);
                tbody.appendChild(tr);
            }

            const trTotalGeral = document.createElement('tr');
            const tdTotalSetor = document.createElement('td');
            tdTotalSetor.innerText = 'Total Geral';
            const tdTotalItens = document.createElement('td');
            tdTotalItens.innerText = Object.values(itensTotalPorSetor).reduce((a, b) => a + b, 0).toFixed(2).replace('.', ',');
            const tdTotalValor = document.createElement('td');
            tdTotalValor.innerText = custoTotalGeral.toFixed(2).replace('.', ',');
            trTotalGeral.appendChild(tdTotalSetor);
            trTotalGeral.appendChild(tdTotalItens);
            trTotalGeral.appendChild(tdTotalValor);
            tbody.appendChild(trTotalGeral);
            trTotalGeral.style.backgroundColor = '#f2f2f2';

            tabela.appendChild(tbody);

            const tituloMes = document.createElement('h2');
            tituloMes.innerText = `Relatório de ${nomeMes} de ${ano}`;
            resultadosContainer.appendChild(tituloMes);
            resultadosContainer.appendChild(tabela);
        }
    }

    setorFilter.addEventListener('change', calcularCustoTotal);
    produtoFilter.addEventListener('change', calcularCustoTotal);
    tipoFilter.addEventListener('change', calcularCustoTotal);
    dataInicioFilter.addEventListener('change', calcularCustoTotal);
    dataFimFilter.addEventListener('change', calcularCustoTotal);

    calcularCustoTotal();

    window.imprimirTabela = function() {
        const tabelaConteudo = resultadosContainer.innerHTML;
        const setor = setorFilter.value || 'Todos';
        const produto = produtoFilter.value || 'Todos';
        const tipo = tipoFilter.value || 'Todos';
        const dataInicio = dataInicioFilter.value 
            ? dataInicioFilter.value.split('-').reverse().join('/')
            : 'Não definido';
        const dataFim = dataFimFilter.value
            ? dataFimFilter.value.split('-').reverse().join('/')
            : 'Não definido';

        const janelaImpressao = window.open('', '', 'height=600,width=800');
        janelaImpressao.document.write('<html><head><title>Imprimir Tabela</title>');
        janelaImpressao.document.write('<style>');
        janelaImpressao.document.write('table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid black; padding: 8px; text-align: left;} th {background-color: #f2f2f2; font-weight: bold;} footer {position: fixed; bottom: 0; width: 100%; text-align: center; padding: 10px 0;} header {text-align: center; padding: 20px 0;}');
        janelaImpressao.document.write('</style>');
        janelaImpressao.document.write('</head><body>');

        janelaImpressao.document.write('<header>');
        janelaImpressao.document.write('<h1>Relatório de Totais por Setor</h1>');
        janelaImpressao.document.write('<p>Filtros Aplicados: Setor: ' + setor + ' | Produto: ' + produto + ' | Tipo: ' + tipo + ' | Data Início: ' + dataInicio + ' | Data Fim: ' + dataFim + '</p>');
        janelaImpressao.document.write('</header>');

        janelaImpressao.document.write(tabelaConteudo);

        janelaImpressao.document.write('<footer>');
        janelaImpressao.document.write('<p>Emitido em ' + new Date().toLocaleString('pt-BR') + '</p>');
        janelaImpressao.document.write('</footer>');

        janelaImpressao.document.write('</body></html>');
        janelaImpressao.document.close();
        janelaImpressao.print();
    }
});
</script>