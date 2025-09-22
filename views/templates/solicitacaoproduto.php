<style>
    .hidden {
        display: none;
    }
    h3 {
        text-align: center;
    }  
</style>
<div class="container">
    <div class="top-bar">
        
         <input type="text" name="search" placeholder="Descrição do item" class="search-input">

        <a href="#" class="btn-cart">Finalizar Solicitação</a>
        <div id="product-results"></div> <!-- Exibe a solicitação -->
    </div>


    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="cart-content">
                <!-- O conteúdo do carrinho será carregado aqui -->
            </div>
            <form id="form-enviar-pedido" method="POST" action="<?php echo $base_url; ?>Solicitacao/enviarSolicitacao">
                <input type="hidden" name="pedidos" id="pedidos">
                    
                <?php
                // Verifica se o usuário é 'admin' ou 'infra'
                $isAdminOrInfra = ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'infra');
                ?>
                <!-- Modal de Produtos Adicionados -->
                <div id="modal-produtos-adicionados">
                    <!-- Aqui vai o conteúdo do modal -->

                    <!-- Se o usuário for admin ou infra, exibe o campo de setor destino -->
                    <div class="form-group">
                        <label for="nome">Nome do Solicitante:</label>
                       <!-- <input type="text" id="nome" name="nome"> -->
                        
                       <input type="text" id="nome" name="nome" 
                        value="<?php echo ($_SESSION['tipo'] === 'solicitante' ? htmlspecialchars($_SESSION['nome']) : ''); ?>" 
                        <?php echo ($_SESSION['tipo'] === 'solicitante' ? 'readonly' : ''); ?>>
                    </div>

                    <div class="form-group">
                    <label for="setorDestino">Setor Destino</label>
                    <select name="setorDestino" id="setorDestino" class="form-control" 
                            <?php echo ($_SESSION['tipo'] === 'solicitante' ? 'readonly' : ''); ?> required>
                        <?php if ($_SESSION['tipo'] === 'solicitante'): ?>
                            <!-- Para "solicitante", o setor é pré-selecionado e fixo -->
                            <option value="<?php echo htmlspecialchars($_SESSION['setor']); ?>" selected>
                                <?php echo htmlspecialchars($_SESSION['setor']); ?>
                            </option>
                        <?php else: ?>
                            <!-- Para "admin" ou "infra", todas as opções são mostradas -->
                            <option value="">Selecione o setor</option>
                            <option value="Escola">Escola</option>
                            <option value="Ação Cultural">Ação Cultural</option>
                            <option value="Comunicação">Comunicação</option>
                            <option value="Narte">Narte</option>
                            <option value="Administrativo">Administrativo</option>
                            <option value="Infraestrutura">Infraestrutura</option>

                        <?php endif; ?>
                    </select>
                </div>

                            <div class="form-group">
                                <label for="subsetorDestino">Subsetor Destino</label>
                                <select name="subsetorDestino" id="subsetorDestino" class="form-control" required>
                                    <option value="">Selecione o subsetor</option>
                                </select>
                            </div>

                    <!-- Outros campos do modal -->
                </div>
                <button type="submit" id="btn-enviar-pedido" style="display:none;">Enviar solicitacao</button>
            </form>
        </div>
    </div>
    <div class="notification" id="notification"></div>

    <div class="card-container">
        <?php if (isset($produtos) && !empty($produtos)): ?>
            <?php foreach ($produtos as $row): ?>
                <?php
                    $id = htmlspecialchars($row['codigo_item']);
                    $nome = htmlspecialchars($row['descricao']);
                    $saldo_final = htmlspecialchars($row['saldo']);      
                    $situacao =   htmlspecialchars($row['situacao']);           
                ?>
                <div class="card" onclick="toggleForm('<?php echo $id; ?>')">

                    <h2><?php echo $nome; ?></h2>
                    <h2><?php echo $situacao; ?></h2>

                    <h2><?php echo 'Saldo: ' . $saldo_final; ?></h2>

                    <!-- Formulário para adicionar à solicitação com campo de quantidade -->
                    <div id="form_<?php echo $id; ?>" class="hidden">
                        <form method="POST" action="<?php echo $base_url;?>Solicitacao/adicionarAoCarrinho">
                            <input type="hidden" name="id_produto" value="<?php echo $id; ?>">    
                            <input type="hidden" name="nome" value="<?php echo $nome; ?>">                      

                            <label for="quantidade_<?php echo $id; ?>"></label>
                            <input placeholder="N°" type="number" name="quantidade" id="quantidade_<?php echo $id; ?>" min="1" value="1" required class="quantity-input">
                            <button type="submit" class="btn-register">Adicionar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">
                Nenhum produto encontrado.
            </div>
        <?php endif; ?>
    </div>


</div>

<script>
     document.querySelectorAll('.btn-register').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            let form = this.closest('form');
            let idProduto = form.querySelector('input[name="id_produto"]').value;
            let quantidade = form.querySelector('input[name="quantidade"]').value;
            let nome = form.querySelector('input[name="nome"]').value;

            // Obter o saldo disponível do produto
            let saldo = parseInt(form.parentElement.previousElementSibling.textContent.match(/Saldo: (\d+)/)[1]);

            // Verifica se a quantidade desejada é maior que o saldo
 

            if (quantidade > saldo) {
                // Substitua o alert por um feedback visual
                showNotification('Quantidade desejada (' + quantidade + ') é maior que o saldo disponível (' + saldo + ').', 'error');
                return; // Interrompe o envio do formulário
            }

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $base_url;?>Solicitacao/adicionarAoCarrinho", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    // Mostra uma mensagem de sucesso
                    showNotification(response.message);
                    form.querySelector('input[name="quantidade"]').value = '';
                }
            };

            xhr.send("id_produto=" + idProduto + "&quantidade=" + quantidade + "&nome=" + nome);
        });
    });

    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.innerText = message;
        notification.style.backgroundColor = type === 'error' ? '#f44336' : '#4CAF50'; // Vermelho para erro
        notification.style.display = 'block';
        notification.style.opacity = 1;

        setTimeout(() => {
            notification.style.opacity = 0;
            setTimeout(() => {
                notification.style.display = 'none';
            }, 500); // Tempo para desaparecer
        }, 3000); // Mostra a mensagem por 3 segundos
    }
    document.querySelector('.btn-cart').addEventListener('click', function(event) {
        event.preventDefault();

        let xhr = new XMLHttpRequest();
        xhr.open("GET", "<?php echo $base_url; ?>Solicitacao/verCarrinho", true);

        xhr.onload = function() {
            if (xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);

                if (response.status === 'success') {
                    let produtos = response.data;
                    let output = '<h3>Produtos Adicionados:</h3><ul>';

                    for (let id in produtos) {
                        let produto = produtos[id];
                        output += '<li>' + produto.nome + ', Quantidade: ' + produto.quantidade + ' <button class="remove-btn" data-id="'+id+'">🗑️</button></li>';
                    }

                    output += '</ul>';
                    document.getElementById('cart-content').innerHTML = output;

                    document.getElementById('btn-enviar-pedido').style.display = 'block';

                    openModal();
                } else {
                    alert(response.message);
                }
            }
        };

        xhr.send();
    });

    document.getElementById('cart-content').addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-btn')) {
            let idProduto = event.target.getAttribute('data-id');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $base_url; ?>Solicitacao/removerDoCarrinho", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    alert(response.message);
                    location.reload(); // Recarrega a página para atualizar o carrinho
                }
            };

            xhr.send("id_produto=" + idProduto);
        }
    });

    function openModal() {
        document.getElementById('modal').style.display = 'block';
    }

    document.querySelector('.close').onclick = function() {
        document.getElementById('modal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == document.getElementById('modal')) {
            document.getElementById('modal').style.display = 'none';
        }
    }

    document.getElementById('btn-enviar-pedido').addEventListener('click', function() {
        let pedidos = [];
        document.querySelectorAll('#cart-content .remove-btn').forEach(function(item) {
            let id = item.getAttribute('data-id');
            let quantidade = item.closest('li').innerText.match(/Quantidade: (\d+)/)[1]; // Extraí a quantidade
            pedidos.push({ id: id, quantidade: quantidade });
        });

        // Enviar pedidos para o servidor como JSON
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo $base_url;?>Solicitacao/enviarPedido", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        let data = JSON.stringify(pedidos);

        xhr.onload = function() {
            if (xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                alert(response.message);
                closeModal(); // Fecha o modal após o envio
                location.reload(); // Recarrega a página
            }
        };

        xhr.send(data);
    });

    // Subsetores de acordo com o setor selecionado
    const subsetores = {
        Escola: [
            { value: 'Geral', text: 'Geral' },
            { value: 'Programa de Acessibilidade', text: 'Programa de Acessibilidade' },
            { value: 'Programa de Audiovisual', text: 'Programa de Audiovisual' },
            { value: 'Programa de Cultura Digital', text: 'Programa de Cultura Digital' },
            { value: 'Programa de Dança', text: 'Programa de Dança' },
            { value: 'Programa de Música', text: 'Programa de Música' },
            { value: 'Programa de Teatro', text: 'Programa de Teatro' }
        ],
        "Ação Cultural": [
            { value: 'Geral', text: 'Geral' },
            { value: 'Biblioteca', text: 'Biblioteca' },
            { value: 'Estúdio', text: 'Estúdio' },
            { value: 'Teatro', text: 'Teatro' }
        ],
        Comunicação: [
            { value: 'Geral', text: 'Geral' }
        ],
        Narte: [
            { value: 'Geral', text: 'Geral' },
            { value: 'Psicosocial', text: 'Psicosocial' },
            { value: 'Educadores', text: 'Educadores' },
        ],
        Infraestrutura: [
            { value: 'Geral', text: 'Geral' },
            { value: 'TI', text: 'TI' },
            { value: 'Manutenção', text: 'Manutenção' }
        ],
        Gestão: [
            { value: 'Geral', text: 'Geral' }
        ],

        Administrativo: [
            { value: 'Geral', text: 'Geral' }
        ]
    };
    function updateSubsetor() {
        const setor = document.getElementById('setorDestino').value;
        const subsetorSelect = document.getElementById('subsetorDestino');
        
        // Limpa as opções atuais do subsetor
        subsetorSelect.innerHTML = '<option value="">Selecione o subsetor</option>';
        
        // Verifica se o setor selecionado tem subsetores definidos
        if (subsetores[setor]) {
            // Adiciona as novas opções de subsetores
            subsetores[setor].forEach(subsetor => {
                const option = document.createElement('option');
                option.value = subsetor.value;
                option.text = subsetor.text;
                subsetorSelect.appendChild(option);
            });
        }
    }
    document.getElementById('setorDestino').addEventListener('change', updateSubsetor);

    window.onload = function() {
        updateSubsetor(); // Atualiza o subsetor ao carregar a página
    };
function toggleForm(productId) {
    // Esconde todos os forms primeiro
    document.querySelectorAll('[id^="form_"]').forEach(form => {
        form.classList.add('hidden');
    });

    // Mostra apenas o form do produto clicado
    const form = document.getElementById('form_' + productId);
    form.classList.remove('hidden');
    form.querySelector('.quantity-input').focus();
}



    document.querySelector('.search-input').addEventListener('input', function(event) {
        const searchTerm = event.target.value
            .trim()
            .toLowerCase()
            .normalize("NFD") // Normaliza caracteres acentuados
            .replace(/[\u0300-\u036f]/g, ""); // Remove acentos

        const cards = document.querySelectorAll('.card');

        cards.forEach(card => {
            const productName = card.querySelector('h2').textContent
                .trim()
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

            card.style.display = productName.includes(searchTerm) ? 'block' : 'none';
        });
    });

</script>
<?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        <script type="text/javascript">
            window.onload = function() {
                alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
            };
        </script>
<?php unset($_SESSION['mensagem_confirmacao']); // Limpa a mensagem após exibir ?>
<?php endif; ?>