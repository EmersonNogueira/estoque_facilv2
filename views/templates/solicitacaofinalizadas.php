<!-- Modal para exibir produtos -->
<div id="modalProdutos" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h2>Produtos da Solicitação</h2>

        <div id="produtosContainer">
            <!-- Produtos serão carregados aqui -->
        </div>

        <fieldset>
            <div><strong>Solicitado por:</strong> <span id="solicitanteNome"></span></div>
            <div><strong>Setor:</strong> <span id="setor"></span></div>
            <div><strong>Subsetor:</strong> <span id="subsetor"></span></div>
            <div><strong>Data da abertura:</strong> <span id="dataSolicitacao"></span></div>
            <div><strong>Data da finalização:</strong> <span id="dataFinalizacao"></span></div>
            <div><strong>Receptor:</strong> <span id="receptorNome"></span></div>
        </fieldset>

        <!-- Botões de ação -->
        <div class="modal-botoes">
            <button type="button" onclick="imprimirModal()">Imprimir</button>
        </div>
    </div>
</div>

<!-- Tabela de Solicitações Finalizadas -->
<h1>Solicitações Finalizadas</h1>
<table border="1" cellspacing="0" cellpadding="10">
    <thead>
        <tr>
            <th>Código Solicitação</th>
            <th>Usuário Criador</th>
            <th>Solicitante</th>
            <th>Setor</th>
            <th>Subsetor</th>
            <th>Data da Abertura</th>
            <th>Data da Finalização</th>
            <th>Receptor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($solicitacao as $sol): ?>
        <tr>
            <td><?php echo htmlspecialchars($sol['codigo_solicitacao']); ?></td>
            <td><?php echo htmlspecialchars($sol['usuario_criador']); ?></td>
            <td><?php echo htmlspecialchars($sol['solicitante']); ?></td>
            <td><?php echo htmlspecialchars($sol['setor']); ?></td>
            <td><?php echo htmlspecialchars($sol['subsetor']); ?></td>
            <td><?php echo htmlspecialchars($sol['data']); ?></td>
            <td><?php echo htmlspecialchars($sol['data_finalizacao']); ?></td>
            <td><?php echo htmlspecialchars($sol['receptor']); ?></td>
            <td>
                <button class="btn-verprodutos" onclick="mostrarProdutos(
                    <?php echo $sol['codigo_solicitacao']; ?>,
                    '<?php echo addslashes($sol['solicitante']); ?>',
                    '<?php echo addslashes($sol['setor']); ?>',
                    '<?php echo addslashes($sol['subsetor']); ?>',
                    '<?php echo addslashes($sol['status']); ?>',
                    '<?php echo $sol['data']; ?>',
                    '<?php echo $sol['data_finalizacao']; ?>',
                    '<?php echo htmlspecialchars($sol['receptor']); ?>'
                )">Produtos</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
const base_url = '<?php echo $base_url; ?>';

function mostrarProdutos(idSolicitacao, nomeSolicitante, setor, subsetor, status, dataSolicitacao, dataFinalizacao, receptor) {
    const receptorFinal = receptor || 'N/A';

    document.querySelector("#modalProdutos h2").innerText = `Produtos da Solicitação ${idSolicitacao} Finalizada`;

    document.getElementById('solicitanteNome').innerText = nomeSolicitante;
    document.getElementById('setor').innerText = setor;
    document.getElementById('subsetor').innerText = subsetor;
    document.getElementById('dataSolicitacao').innerText = dataSolicitacao;
    document.getElementById('dataFinalizacao').innerText = dataFinalizacao;
    document.getElementById('receptorNome').innerText = receptorFinal;

    const container = document.getElementById('produtosContainer');
    container.innerHTML = 'Carregando produtos...';

    fetch(`${base_url}solicitacao/getprodutos/${idSolicitacao}`)
        .then(res => res.json())
        .then(produtos => {
            if (!produtos || produtos.length === 0) {
                container.innerHTML = 'Nenhum produto encontrado para esta solicitação.';
                return;
            }

            let tabela = '<table border="1" cellspacing="0" cellpadding="10"><thead><tr><th>Código Produto</th><th>Nome</th><th>Quantidade</th><th>Saldo</th><th>Depósito</th><th>Local</th></tr></thead><tbody>';

            produtos.forEach(produto => {
                const estoque = produto.estoque[0] || {};
                tabela += `<tr>
                    <td>${produto.codigo_item}</td>
                    <td>${produto.nome_produto}</td>
                    <td>${estoque.saldo || 0}</td>
                    <td>${produto.saldo || 0}</td>
                    <td>${estoque.nome_deposito || '-'}</td>
                    <td>${estoque.nome_local || '-'}</td>
                </tr>`;
            });

            tabela += '</tbody></table>';
            container.innerHTML = tabela;
        })
        .catch(err => {
            console.error('Erro ao buscar produtos:', err);
            container.innerHTML = 'Erro ao carregar produtos.';
        });

    document.getElementById('modalProdutos').style.display = 'block';
}

function fecharModal() {
    document.getElementById('modalProdutos').style.display = 'none';
}

function imprimirModal() {
    const modalContent = document.getElementById('modalProdutos').cloneNode(true);
    const tabela = modalContent.querySelector('table');

    if (tabela) {
        // Remove a última coluna se houver ação
        const ths = tabela.querySelectorAll('th');
        if (ths.length && ths[ths.length-1].textContent === 'Ação') {
            tabela.querySelectorAll('tr').forEach(tr => tr.removeChild(tr.lastElementChild));
        }
    }

    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Produtos</title></head><body>');
    printWindow.document.write(modalContent.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
