<!-- Modal para exibir produtos -->
<div id="modalProdutos" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h2>Itens da solicitação <span id="codigo_solicitacao"></span> aguardando atendimento</h2>

        <!-- Formulário para envio via POST -->
        <form id="formProdutos" method="post" action="<?php echo $base_url; ?>Registro/itens">
            <input type="hidden" name="codigo_solicitacao" id="idSolicitacaoInput" value="">
            <input type="hidden" name="destino" id="destino" value="">

            <fieldset>
                <legend>Informações da Solicitação</legend>
                <div><strong>Solicitado por:</strong> <span id="solicitanteNome"></span></div>
                <div><strong>Setor:</strong> <span id="destinoTexto"></span></div>
                <div><strong>Subsetor:</strong> <span id="subsetor"></span></div>
                <div><strong>Data da abertura:</strong> <span id="dataSolicitacao"></span></div>
            </fieldset>

            <fieldset>
                <legend>Itens</legend>
                <div id="produtosContainer">
                    <!-- Conteúdo de produtos será carregado aqui -->
                </div>
            </fieldset>

            <fieldset>
                <legend>Recebimento</legend>
                <label for="receptor">
                    <h3>Nome de quem está recebendo os itens</h3>
                </label>
                <input type="text" name="receptor" id="receptor" placeholder="Digite o nome do receptor" required>
            </fieldset>

            <div class="modal-botoes">
                <button type="button" onclick="imprimirModal()">Imprimir</button>
                <button type="submit">Finalizar</button>
            </div>
        </form>
    </div>
</div>
<!-- Tabela de Solicitação -->
<h1>Solicitações em aberto</h1>
<table border="1" cellspacing="0" cellpadding="10">
    <thead>
        <tr>
            <th>Código Solicitação</th>
            <th>Usuário Criador</th>
            <th>Solicitante</th>
            <th>Setor</th>
            <th>Subsetor</th>
            <th>Data da abetura</th>
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
                
                <td>
                    <button class="btn-verprodutos" onclick="mostrarProdutos(
                        <?php echo $sol['codigo_solicitacao']; ?>, 
                        '<?php echo addslashes($sol['solicitante']); ?>', 
                        '<?php echo addslashes($sol['setor']); ?>', 
                        '<?php echo addslashes($sol['subsetor']); ?>', 
                        '<?php echo addslashes($sol['status']); ?>', 
                        '<?php echo $sol['data']; ?>',
                        <?php echo htmlspecialchars($sol['quantidade_total']); ?>
                        
                    )">
                        Itens
                    </button>
                </td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>

<!-- Script JavaScript -->
<script>
const base_url = '<?php echo $base_url; ?>';


function mostrarProdutos(codigo_solicitacao, nomeSolicitante, setor, subsetor, status, dataSolicitacao,quantidade) {
    document.getElementById('solicitanteNome').innerText = nomeSolicitante;
    document.getElementById('idSolicitacaoInput').value = codigo_solicitacao;
    document.getElementById('destino').value = setor + ' - ' + subsetor;
    document.getElementById('dataSolicitacao').innerText = dataSolicitacao;
    document.getElementById('destinoTexto').innerText = setor;
    document.getElementById('subsetor').innerText = subsetor;
    document.getElementById('codigo_solicitacao').innerText = codigo_solicitacao;

    fetch(`${base_url}solicitacao/getProdutos/${codigo_solicitacao}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição');
            }
            return response.json();
        })

        .then(produtos => {
            console.log(produtos);

            const container = document.getElementById('produtosContainer');
            container.innerHTML = '';

            if (produtos && produtos.length > 0) {
                let tabela = `
                    <table border="1" cellspacing="0" cellpadding="10">
                        <thead>
                            <tr>
                                <th>Código Item</th>
                                <th>Nome do item</th>
                                <th>Quantidade</th>
                                <th>Saldo atual</th>
                                <th>Custo Unitário</th>
                                <th>Estoque</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                produtos.forEach(produto => {
                    // Construir a lista de locais/depósitos
                    let locaisHTML = '';
                    if (produto.estoque && produto.estoque.length > 0) {
                        locaisHTML = `
                            <table style="border-collapse: collapse; width: 100%; font-size: 12px;">
                                <thead>
                                    <tr style="background-color: #f2f2f2;">
                                        <th style="border: 1px solid #ccc; padding: 4px;">Depósito</th>
                                        <th style="border: 1px solid #ccc; padding: 4px;">Local</th>
                                        <th style="border: 1px solid #ccc; padding: 4px;">Validade</th>
                                        <th style="border: 1px solid #ccc; padding: 4px;">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        produto.estoque.forEach(local => {
                            let validadeFormatada = '';
                            if (local.validade) {
                                const data = new Date(local.validade);
                                validadeFormatada = data.toLocaleDateString('pt-BR');
                            }

                            locaisHTML += `
                                <tr>
                                    <td style="border: 1px solid #ccc; padding: 4px;">${local.nome_deposito}</td>
                                    <td style="border: 1px solid #ccc; padding: 4px;">${local.nome_local}</td>
                                    <td style="border: 1px solid #ccc; padding: 4px;">${validadeFormatada}</td>
                                    <td style="border: 1px solid #ccc; padding: 4px;">${local.saldo}</td>
                                <input type="hidden" 
                                       name="estoque[id_estoque][]" 
                                       value="${local.id_estoque}">                                    
                                </tr>
                                <input type="hidden" 
                                       name="estoque[saldo][]" 
                                       value="${local.saldo}">                                    
                                </tr>
                            `;
                        });

                        locaisHTML += `
                                </tbody>
                            </table>
                        `;

                    } else {
                        locaisHTML = 'Nenhum local disponível';
                    }

                    tabela += `
                        <tr>
                            <td>${produto.codigo_item}</td>
                            <td>${produto.nome_produto}</td>
                            <td>
                                <input type="number" 
                                       id="quantidade_${produto.codigo_item}" 
                                       value="${produto.quantidade_solicitada}" 
                                       min="1" 
                                       style="width: 80px;">
                                <input type="hidden" 
                                       name="produtos[${produto.codigo_item}][id]" 
                                       value="${produto.codigo_item}">

                             
                                <input type="hidden" 
                                       name="produtos[${produto.codigo_item}][quantidade]" 
                                       id="inputQuantidade_${produto.codigo_item}" 
                                       value="${produto.quantidade_solicitada}">
                                <input type="hidden" 
                                       name="produtos[${produto.codigo_item}][saldo]" 
                                       value="${produto.saldo}">
                                <input type="hidden" 
                                       name="produtos[${produto.codigo_item}][custo]" 
                                       value="${produto.custo_unitario}">

                                <input type="hidden" 
                                       name="produtos[${produto.codigo_item}][custo]" 
                                       value="${produto.custo_unitario}">



                            </td>
                            <td>${produto.saldo}</td>
                            <td>${produto.custo_unitario}</td>
                            <td>${locaisHTML}</td>
                            <td>
                                <button type="button" 
                                        onclick="alterarQuantidade(${codigo_solicitacao}, ${produto.codigo_item}, ${produto.saldo})" 
                                        ${status === 'finalizado' ? 'disabled' : ''}>
                                    Atualizar Quantidade
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tabela += `</tbody></table>`;
                container.innerHTML = tabela;
            } else {
                container.innerHTML = 'Nenhum produto encontrado para esta solicitação.';
            }

            document.getElementById('modalProdutos').style.display = 'block';
        })
        .catch(error => {
            console.error('Erro ao buscar produtos:', error);
            document.getElementById('produtosContainer').innerHTML = 'Erro ao carregar produtos';
            alert('Erro ao carregar produtos: ' + error.message);
        });
}

    function atualizarQuantidade(idProduto, quantidade) {
        document.getElementById(`inputQuantidade_${idProduto}`).value = quantidade;
    }

    function alterarQuantidade(idSolicitacao, idProduto, saldoProduto) {
        const quantidade = document.getElementById(`quantidade_${idProduto}`).value;

        if (quantidade < 1) {
            alert('A quantidade deve ser maior que zero.');
            return;
        }

        if (quantidade > saldoProduto) {
            alert('A quantidade não pode ser maior que o saldo disponível.');
            return;
        }

        fetch(`${base_url}solicitacao/alterarQuantidade/${idSolicitacao}/${idProduto}/${quantidade}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Quantidade atualizada com sucesso!');

                    location.reload();


                } else {
                    alert('Erro ao atualizar quantidade: ' + (data.message || ''));
                }
            })
            .catch(error => {
                console.error('Erro ao alterar quantidade:', error);
                alert('Erro ao alterar quantidade.');
            });
    }

    function fecharModal() {
        document.getElementById('modalProdutos').style.display = 'none';
    }

    function imprimirModal() {
        const modalContent = document.getElementById('modalProdutos').cloneNode(true);
        const tabela = modalContent.querySelector('table');
        
        if (tabela) {
            const ths = tabela.querySelectorAll('thead tr th');
            ths.forEach((th, index) => {
                if (th.textContent === 'Ação' || th.textContent === 'Locais/Depósitos') {
                    tabela.querySelectorAll('tr').forEach(tr => {
                        if (tr.cells.length > index) {
                            tr.deleteCell(index);
                        }
                    });
                }
            });
        }


        const botoes = modalContent.querySelectorAll('.modal-botoes, button');
        botoes.forEach(botao => botao.remove());

        const janelaImpressao = window.open('', '', 'width=800, height=600');
        janelaImpressao.document.write('<html><head><title>Imprimir Solicitação</title></head><body>');
        janelaImpressao.document.write(modalContent.innerHTML);
        janelaImpressao.document.write('</body></html>');
        janelaImpressao.document.close();
        janelaImpressao.print();
        janelaImpressao.close();
    }
</script>