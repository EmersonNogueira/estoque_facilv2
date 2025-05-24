<!-- Modal para exibir produtos -->
<div id="modalProdutos" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h2>Produtos da Solicitação Finalizada</h2>

        <!-- Formulário para envio via POST -->
        <form id="formProdutos" method="post" action="<?php echo $base_url; ?>Registro/produtos">
            <!-- Campo oculto para enviar o ID da solicitação -->
            <input type="hidden" name="codigo_solicitacao" id="idSolicitacaoInput" value="">
            <input type="hidden" name="destino" id="destino" value="">

            <div id="produtosContainer">
                <!-- Conteúdo de produtos será carregado aqui -->
            </div>

            <div><strong>Solicitado por:</strong> <span id="solicitanteNome"></span></div>
            <div><strong>Setor:</strong> <span id="setor"></span></div>
            <div><strong>Subsetor:</strong> <span id="subsetor"></span></div>
            <div><strong>Data da abertura:</strong> <span id="dataSolicitacao"></span></div>

            <div><strong>Data da Finalização:</strong> <span id="dataFinalizacao"></span></div>
            <div><strong>Receptor:</strong> <span id="receptorNome"></span></div>


            <!-- Botões de ação -->
            <div class="modal-botoes">
                <button type="button" onclick="imprimirModal()">Imprimir</button>
                <button type="submit">Finalizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Solicitação -->
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
                )">
                    Produtos
                </button>

                </td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>

<!-- Paginação -->
<div class="pagination">
    <?php if ($pagina > 1): ?>
        <a href="?pagina=<?php echo $pagina - 1; ?>">&laquo; Anterior</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <a href="?pagina=<?php echo $i; ?>" <?php echo ($i == $pagina) ? 'class="active"' : ''; ?>>
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($pagina < $totalPaginas): ?>
        <a href="?pagina=<?php echo $pagina + 1; ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>

<!-- Script JavaScript -->
<script>
// Função para buscar e exibir os produtos de uma solicitação
const base_url = '<?php echo $base_url; ?>';

function mostrarProdutos(idSolicitacao, nomeSolicitante, setor, subsetor, status, dataSolicitacao, dataFinalizacao, receptor) {
    // Verificação para garantir que o receptor não seja nulo ou indefinido
    const receptorFinal = receptor || 'N/A'; // Valor padrão para receptor

    // Atualizando o conteúdo no modal
    document.querySelector("#modalProdutos h2").innerText = `Produtos da Solicitação ${idSolicitacao} Finalizada`;

    document.getElementById('solicitanteNome').innerText = nomeSolicitante;
    document.getElementById('idSolicitacaoInput').value = idSolicitacao;
    document.getElementById('destino').value = setor;
    document.getElementById('dataSolicitacao').innerText = dataSolicitacao;
    document.getElementById('dataFinalizacao').innerText = dataFinalizacao;

    document.getElementById('receptorNome').innerText = receptorFinal; // Exibe o receptor com valor padrão
    document.getElementById('setor').innerText = setor; // Atualizando o texto visível
    document.getElementById('subsetor').innerText = subsetor; // Atualizando o texto visível


    // Buscando os produtos
    fetch(`${base_url}solicitacao/getprodutos/${idSolicitacao}`)
        .then(response => response.json())
        .then(produtos => {
            const container = document.getElementById('produtosContainer');
            container.innerHTML = '';
            console.log(produtos);
            if (produtos.length > 0) {
                let tabela = '<table border="1" cellspacing="0" cellpadding="10"><thead><tr><th>Código Produto</th><th>Nome do Produto</th><th>Quantidade</th><th>Saldo atual</th><th>Depósito</th><th>Local</th></tr></thead><tbody>';
                
                produtos.forEach(produto => {
                    const estoque = produto.estoque[0] || {};
                    tabela += `<tr>
                        <td>${produto.codigo_item}</td>
                        <td>${produto.nome_produto}</td>
                        <td>
                            <span id="quantidade_${estoque.id_estoque}" style="width: 80px;">${estoque.saldo}</span>

                            <input type="hidden" name="produtos[${produto.id_produto}][id]" value="${produto.id_produto}">
                            <input type="hidden" name="produtos[${produto.id_produto}][quantidade]" id="inputQuantidade_${produto.id_produto}" value="${produto.quantidade}">
                            <input type="hidden" name="produtos[${produto.id_produto}][saldo]" value="${produto.saldo}">
                            <input type="hidden" name="produtos[${produto.id_produto}][custo]" value="${produto.custo}">
                        </td>
                        <td>${produto.saldo}</td>
                        <td>${estoque.nome_deposito}</td>
                        <td>${estoque.nome_local}</td>
                    </tr>`;
                });
                
                tabela += '</tbody></table>';
                container.innerHTML = tabela;
            } else {
                container.innerHTML = 'Nenhum produto encontrado para esta solicitação.';
            }

            // Mostra o modal
            document.getElementById('modalProdutos').style.display = 'block';

            // Gerenciamento do botão "Finalizar" com base no status
            const finalizarButton = document.querySelector('button[type="submit"]');
            if (status === 'Finalizado') {
                finalizarButton.style.display = 'none'; // Oculta o botão "Finalizar"
            } else {
                finalizarButton.style.display = 'inline-block'; // Exibe o botão "Finalizar" se o status não for "finalizado"
            }
        })
        .catch(error => {
            console.error('Erro ao buscar produtos:', error);
            alert('Erro ao buscar produtos.');
        });
}

// Função para atualizar a quantidade no input hidden
function atualizarQuantidade(idProduto, quantidade) {
    document.getElementById(`inputQuantidade_${idProduto}`).value = quantidade;
}

// Função para alterar a quantidade de um produto (com validação de saldo)
function alterarQuantidade(idSolicitacao, idProduto, saldoProduto) {
    const quantidade = document.getElementById(`quantidade_${idProduto}`).value;

    if (quantidade < 1) {
        alert('A quantidade deve ser maior que zero.');
        return;
    }

    if (quantidade > saldoProduto) {
        alert('A quantidade não pode ser maior que o saldo disponível.');
        fecharModal();
        mostrarProdutos(idSolicitacao, document.getElementById('solicitanteNome').innerText);
        return;
    }

    fetch(`${base_url}solicitacao/alterarQuantidade/${idSolicitacao}/${idProduto}/${quantidade}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Quantidade atualizada com sucesso!');
            } else {
                alert('Erro ao atualizar quantidade.');
            }
        })
        .catch(error => {
            console.error('Erro ao alterar quantidade:', error);
            alert('Erro ao alterar quantidade.');
        });
}

// Função para fechar o modal
function fecharModal() {
    document.getElementById('modalProdutos').style.display = 'none';
}

// Função para imprimir o conteúdo do modal sem a coluna "Ação" e os botões
function imprimirModal() {
    const modalContent = document.getElementById('modalProdutos').cloneNode(true);

    // Ajusta o título para manter o ID da solicitação
    const tituloModal = document.querySelector("#modalProdutos h2").innerText;
    modalContent.querySelector("h2").innerText = tituloModal;

    // Remove a coluna "Ação" da tabela
    const tabela = modalContent.querySelector('table');
    if (tabela) {
        const cabecalhoAcao = tabela.querySelector('th:last-child');
        if (cabecalhoAcao) cabecalhoAcao.remove();

        tabela.querySelectorAll('tr').forEach(linha => {
            const celulaAcao = linha.querySelector('td:last-child');
            if (celulaAcao) celulaAcao.remove();
        });
    }

    // Criar janela de impressão
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>' + tituloModal + '</title></head><body>');
    printWindow.document.write(modalContent.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

</script>
