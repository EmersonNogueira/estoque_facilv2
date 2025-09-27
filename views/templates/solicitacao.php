<!-- Modal para exibir produtos -->
<div id="modalProdutos" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h2>Itens da solicitação <span id="codigo_solicitacao"></span> aguardando atendimento</h2>

        <!-- Formulário principal para envio via POST -->
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

            <!-- Campo de receptor (apenas para finalizar) -->
            <fieldset id="recebimentoFieldset" style="display:none;">
                <legend>Recebimento</legend>
                <label for="receptor"><h3>Nome de quem está recebendo os itens</h3></label>
                <input type="text" name="receptor" id="receptor" placeholder="Digite o nome do receptor">
            </fieldset>

            <div class="modal-botoes" id="modalBotoes"></div>
        </form>
    </div>
</div>
    <div id="notificacao-container"></div>

<!-- Tabela de Solicitações -->
<h1>Solicitações em aberto</h1>
<table border="1" cellspacing="0" cellpadding="10">
    <thead>
        <tr>
            <th>Código Solicitação</th>
            <th>Usuário Criador</th>
            <th>Solicitante</th>
            <th>Setor</th>
            <th>Subsetor</th>
            <th>Data da abertura</th>
            <th>Status</th>
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
                <td><?php echo htmlspecialchars($sol['status']); ?></td>
                <td>
                    <button class="btn-verprodutos" onclick="mostrarProdutos(
                        <?php echo $sol['codigo_solicitacao']; ?>,
                        '<?php echo addslashes($sol['solicitante']); ?>',
                        '<?php echo addslashes($sol['setor']); ?>',
                        '<?php echo addslashes($sol['subsetor']); ?>',
                        '<?php echo addslashes(strtolower($sol['status'])); ?>',
                        '<?php echo $sol['data']; ?>'
                    )">Itens</button>
                </td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>

<script>
const base_url = '<?php echo $base_url; ?>';

function mostrarProdutos(codigo_solicitacao, nomeSolicitante, setor, subsetor, status, dataSolicitacao) {
    document.getElementById('solicitanteNome').innerText = nomeSolicitante;
    document.getElementById('idSolicitacaoInput').value = codigo_solicitacao;
    document.getElementById('destino').value = setor + ' - ' + subsetor;
    document.getElementById('dataSolicitacao').innerText = dataSolicitacao;
    document.getElementById('destinoTexto').innerText = setor;
    document.getElementById('subsetor').innerText = subsetor;
    document.getElementById('codigo_solicitacao').innerText = codigo_solicitacao;

    const botoesContainer = document.getElementById('modalBotoes');
    botoesContainer.innerHTML = '';

    // Botão imprimir
    const btnImprimir = document.createElement('button');
    btnImprimir.type = 'button';
    btnImprimir.innerText = 'Imprimir';
    btnImprimir.onclick = imprimirModal;
    botoesContainer.appendChild(btnImprimir);

    const receptorField = document.getElementById('recebimentoFieldset');
    const receptorInput = document.getElementById('receptor');
    receptorField.style.display = 'none';
    receptorInput.required = false;

    // Botões de ação
    if (status === 'pendente') {
        // Pedido Separado (funcionando como antes)
        const formSeparado = document.createElement('form');
        formSeparado.method = 'post';
        formSeparado.action = `${base_url}Solicitacao/pedidoSeparado/${codigo_solicitacao}`;
        formSeparado.style.display = 'inline';

        const btnSeparado = document.createElement('button');
        btnSeparado.type = 'submit';
        btnSeparado.innerText = 'Pedido Separado';
        formSeparado.appendChild(btnSeparado);

        botoesContainer.appendChild(formSeparado);

    } else if (status === 'separado') {
        // Finalizar
        receptorField.style.display = 'block';
        receptorInput.required = true;

        const btnFinalizar = document.createElement('button');
        btnFinalizar.type = 'button';
        btnFinalizar.innerText = 'Finalizar';
        btnFinalizar.onclick = () => document.getElementById('formProdutos').submit();

        botoesContainer.appendChild(btnFinalizar);
    }

    // Carrega produtos
    fetch(`${base_url}solicitacao/getProdutos/${codigo_solicitacao}`)
        .then(res => res.json())
        .then(produtos => {
            const container = document.getElementById('produtosContainer');
            container.innerHTML = '';

            if (!produtos || produtos.length === 0) {
                container.innerHTML = 'Nenhum produto encontrado para esta solicitação.';
                document.getElementById('modalProdutos').style.display = 'block';
                return;
            }

            let tabela = `<table border="1" cellspacing="0" cellpadding="10">
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
                <tbody>`;

            produtos.forEach(produto => {
                let locaisHTML = 'Nenhum local disponível';
                if (produto.estoque && produto.estoque.length > 0) {
                    locaisHTML = `<table style="border-collapse: collapse; width: 100%; font-size: 12px;">
                        <thead>
                            <tr style="background-color: #f2f2f2;">
                                <th style="border:1px solid #ccc; padding:4px;">Depósito</th>
                                <th style="border:1px solid #ccc; padding:4px;">Local</th>
                                <th style="border:1px solid #ccc; padding:4px;">Validade</th>
                                <th style="border:1px solid #ccc; padding:4px;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>`;
                    produto.estoque.forEach(local => {
                        let validadeFormatada = local.validade ? new Date(local.validade).toLocaleDateString('pt-BR') : '';
                        locaisHTML += `<tr>
                            <td style="border:1px solid #ccc; padding:4px;">${local.nome_deposito}</td>
                            <td style="border:1px solid #ccc; padding:4px;">${local.nome_local}</td>
                            <td style="border:1px solid #ccc; padding:4px;">${validadeFormatada}</td>
                            <td style="border:1px solid #ccc; padding:4px;">${local.saldo}</td>
                            <input type="hidden" name="estoque[id_estoque][]" value="${local.id_estoque}">
                            <input type="hidden" name="estoque[saldo][]" value="${local.saldo}">
                        </tr>`;
                    });
                    locaisHTML += '</tbody></table>';
                }

                tabela += `<tr>
                    <td>${produto.codigo_item}</td>
                    <td>${produto.nome_produto}</td>
                    <td>
                        <input type="number" id="quantidade_${produto.codigo_item}" value="${produto.quantidade_solicitada}" min="1" style="width:80px;" onchange="document.getElementById('inputQuantidade_${produto.codigo_item}').value=this.value;">
                        <input type="hidden" name="produtos[${produto.codigo_item}][id]" value="${produto.codigo_item}">
                        <input type="hidden" name="produtos[${produto.codigo_item}][quantidade]" id="inputQuantidade_${produto.codigo_item}" value="${produto.quantidade_solicitada}">
                        <input type="hidden" name="produtos[${produto.codigo_item}][saldo]" value="${produto.saldo}">
                        <input type="hidden" name="produtos[${produto.codigo_item}][custo]" value="${produto.custo_unitario}">
                    </td>
                    <td>${produto.saldo}</td>
                    <td>${produto.custo_unitario}</td>
                    <td>${locaisHTML}</td>
                    <td>
                        <button type="button" onclick="alterarQuantidade(${codigo_solicitacao}, ${produto.codigo_item}, ${produto.saldo})" ${status === 'finalizado' ? 'disabled' : ''}>Atualizar Quantidade</button>
                    </td>
                </tr>`;
            });

            tabela += '</tbody></table>';
            container.innerHTML = tabela;
            document.getElementById('modalProdutos').style.display = 'block';
        })
        .catch(err => {
            console.error('Erro ao buscar produtos:', err);
            document.getElementById('produtosContainer').innerHTML = 'Erro ao carregar produtos';
            alert('Erro ao carregar produtos: ' + err.message);
        });
}

function alterarQuantidade(idSolicitacao, idProduto, saldoProduto) {
    const quantidade = document.getElementById(`quantidade_${idProduto}`).value;
    if (quantidade < 1) return alert('A quantidade deve ser maior que zero.');
    if (quantidade > saldoProduto) return alert('A quantidade não pode ser maior que o saldo disponível.');

    fetch(`${base_url}solicitacao/alterarQuantidade/${idSolicitacao}/${idProduto}/${quantidade}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Quantidade atualizada com sucesso!');
                location.reload();
            } else {
                alert('Erro ao atualizar quantidade: ' + (data.message || ''));
            }
        })
        .catch(err => {
            console.error('Erro ao alterar quantidade:', err);
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
                    if (tr.cells.length > index) tr.deleteCell(index);
                });
            }
        });
    }

    const botoes = modalContent.querySelectorAll('.modal-botoes, button');
    botoes.forEach(botao => botao.remove());

    const janelaImpressao = window.open('', '', 'width=800,height=600');
    janelaImpressao.document.write('<html><head><title>Imprimir Solicitação</title></head><body>');
    janelaImpressao.document.write(modalContent.innerHTML);
    janelaImpressao.document.write('</body></html>');
    janelaImpressao.document.close();
    janelaImpressao.print();
    janelaImpressao.close();
}

function mostrarNotificacao(mensagem, tipo = 'sucesso') {
    const container = document.getElementById('notificacao-container');
    const notificacao = document.createElement('div');

    notificacao.className = `notificacao ${tipo}`;
    notificacao.textContent = mensagem;

    container.appendChild(notificacao);

    void notificacao.offsetWidth;

    notificacao.classList.add('visivel');

    setTimeout(() => {
        notificacao.classList.remove('visivel');
        setTimeout(() => {
            container.removeChild(notificacao);
        }, 300);
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {

    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        mostrarNotificacao('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>', 'sucesso');
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>
});
</script>
