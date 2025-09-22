<?php
// Supondo que $registros já contenha os dados retornados do banco
$dadosPorMes = [];

// Processa os registros para agrupá-los por mês
foreach ($registros as $registro) {
    $dataRegistro = new DateTime($registro['data_registro']);
    $mesAno = strftime('%B %Y', $dataRegistro->getTimestamp()); // Nome do mês e ano
    
    if (!isset($dadosPorMes[$mesAno])) {
        $dadosPorMes[$mesAno] = ['compras' => 0, 'ajustes' => 0, 'quantidade' => 0];
    }
    
    $total = $registro['quantidade'] * $registro['custo'];
    if ($registro['tipo'] == 'Compra') {
        $dadosPorMes[$mesAno]['compras'] += $total;
    } elseif ($registro['tipo'] == 'Ajuste Positivo') {
        $dadosPorMes[$mesAno]['ajustes'] += $total;
    }
    
    $dadosPorMes[$mesAno]['quantidade'] += $registro['quantidade'];
}
?>

<div class="filters">
    <label for="dataInicio">Data Início:</label>
    <input type="date" id="dataInicio" class="filter">

    <label for="dataFim">Data Fim:</label>
    <input type="date" id="dataFim" class="filter" value="<?php echo date('Y-m-d'); ?>">

    <label for="produto">Produto:</label>
    <select id="produto" class="filter">
        <option value="">Todos</option>
        <?php foreach (array_unique(array_column($registros, 'nome_produto')) as $produto): ?>
            <option value="<?php echo htmlspecialchars($produto); ?>"><?php echo htmlspecialchars($produto); ?></option>
        <?php endforeach; ?>
    </select>

    <button onclick="imprimirTabela()">Imprimir Tabela</button>
</div>

<table border="1">
    <thead>
        <tr>
            <th>Mês</th>
            <th>Compras</th>
            <th>Ajuste Positivo</th>
            <th>Quantidade</th>
        </tr>
    </thead>
    <tbody id="resultados">
        <?php foreach ($dadosPorMes as $mesAno => $totais): ?>
            <tr>
                <td><?php echo ucfirst($mesAno); ?></td>
                <td><?php echo number_format($totais['compras'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($totais['ajustes'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($totais['quantidade'], 0, ',', '.'); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataInicioFilter = document.getElementById('dataInicio');
    const dataFimFilter = document.getElementById('dataFim');
    const produtoFilter = document.getElementById('produto');
    const resultadosContainer = document.getElementById('resultados');
    const registros = <?php echo json_encode($registros); ?>;
    
    function calcularTotais() {
        let dataInicio = dataInicioFilter.value ? new Date(dataInicioFilter.value) : null;
        let dataFim = new Date(dataFimFilter.value);
        dataFim.setUTCHours(23, 59, 59, 999);
        let produtoSelecionado = produtoFilter.value;
        
        let dadosPorMes = {};
        
        registros.forEach(function(registro) {
            let dataRegistro = new Date(registro.data_registro);
            if ((!dataInicio || dataRegistro >= dataInicio) && dataRegistro <= dataFim && (produtoSelecionado === "" || registro.nome_produto === produtoSelecionado)) {
                let mesAno = dataRegistro.toLocaleString('pt-BR', { month: 'long', year: 'numeric' });
                
                if (!dadosPorMes[mesAno]) {
                    dadosPorMes[mesAno] = { compras: 0, ajustes: 0, quantidade: 0 };
                }
                
                let total = parseFloat(registro.custo) * parseFloat(registro.quantidade);
                if (registro.tipo === 'Compra') {
                    dadosPorMes[mesAno].compras += total;
                } else if (registro.tipo === 'Ajuste Positivo') {
                    dadosPorMes[mesAno].ajustes += total;
                }
                
                dadosPorMes[mesAno].quantidade += parseFloat(registro.quantidade);
            }
        });
        
        resultadosContainer.innerHTML = Object.keys(dadosPorMes).map(mesAno => `
            <tr>
                <td>${mesAno.charAt(0).toUpperCase() + mesAno.slice(1)}</td>
                <td>R$ ${dadosPorMes[mesAno].compras.toFixed(2).replace('.', ',')}</td>
                <td>R$ ${dadosPorMes[mesAno].ajustes.toFixed(2).replace('.', ',')}</td>
                <td>${dadosPorMes[mesAno].quantidade.toFixed(0)}</td>
            </tr>
        `).join('');
    }
    
    window.imprimirTabela = function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.setFontSize(18);
        doc.text("Relatório de Compras e Ajustes", 14, 10);
        
        const dataInicio = dataInicioFilter.value;
        const dataFim = dataFimFilter.value;
        
        doc.setFontSize(12);
        let dataTexto = `Período: ${dataInicio ? new Date(dataInicio).toLocaleDateString('pt-BR') : 'Sem data inicial'} a ${dataFim ? new Date(dataFim).toLocaleDateString('pt-BR') : 'Sem data final'}`;
        doc.text(dataTexto, 14, 20);
        
        let tableData = Array.from(resultadosContainer.children).map(tr => [
            tr.children[0].innerText,
            tr.children[1].innerText,
            tr.children[2].innerText,
            tr.children[3].innerText
        ]);
        
        doc.autoTable({
            head: [['Mês', 'Compras', 'Ajuste Positivo', 'Quantidade']],
            body: tableData,
            startY: 30
        });
        
        doc.save('relatorio_compras_ajustes.pdf');
    };
    
    dataInicioFilter.addEventListener('change', calcularTotais);
    dataFimFilter.addEventListener('change', calcularTotais);
    produtoFilter.addEventListener('change', calcularTotais);
    calcularTotais();
});
</script>
