<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alocação de Itens</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="add-product-container">
    <h1>Alocação de Itens</h1>
    <form action="<?php echo $base_url; ?>item/alocar_itensbd" method="post">
      <input type="hidden" name="codigo_item" value="<?php echo htmlspecialchars($itens['codigo_item']); ?>">

      <div class="form-group">
        <label>Descrição:</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($itens['descricao']); ?>">
      </div>

      <div class="form-group">
        <label>Situação:</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($itens['situacao']); ?>">
      </div>

      <div class="form-group">
        <label>Saldo a Alocar:</label>
        <p><strong id="saldo_alocar_display"><?php echo htmlspecialchars($itens['saldo_alocar']); ?></strong></p>
        <input type="hidden" id="saldo_alocar" name="saldo_alocar" value="<?php echo htmlspecialchars($itens['saldo_alocar']); ?>">
      </div>

      <div id="alocacoes">
        <div class="alocacao">
          <div class="form-group">
            <label>Depósito:</label>
            <select name="deposito[]" class="deposito" required>
              <option value="" disabled selected>Selecione</option>
              <option value="1">Infra.</option>
              <option value="2">Zeld.</option>
              <option value="3">Almox.</option>
              <option value="4">TI</option>
              <option value="5">Serviço</option>
            </select>
          </div>

          <div class="form-group">
            <label>Local:</label>
            <select name="local[]" class="local" required>
              <option value="" disabled selected>Selecione um Local</option>
            </select>
          </div>

          <div class="form-group">
            <label>Saldo:</label>
            <input type="number" name="saldo[]" class="saldo" min="1" required>
          </div>

          <div class="form-group">
            <label>Validade:</label>
            <input type="date" name="validade[]" class="validade">
          </div>
        </div>
      </div>

      <!-- <button type="button" id="addAlocacaoBtn" class="btn-alocar">Adicionar Alocação</button> -->
      <button type="submit" id="submitBtn" class="btn-alocar" disabled>Alocar ITEM</button>
    </form>
  </div>

  <script>
    let base_url = window.location.hostname === 'localhost' 
        ? window.location.origin + '/estoque_facil/' 
        : window.location.origin + '/';

    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('deposito')) {
        carregarLocais(e.target);
      }
    });

    document.addEventListener('input', function(e) {
      if (e.target.classList.contains('saldo') || e.target.classList.contains('validade')) {
        verificarSaldo();
      }
    });

    // document.getElementById('addAlocacaoBtn').addEventListener('click', adicionarAlocacao);

    function carregarLocais(selectDeposito) {
      const alocacaoDiv = selectDeposito.closest('.alocacao');
      const selectLocal = alocacaoDiv.querySelector('.local');

      selectLocal.innerHTML = '<option value="" disabled selected>Carregando...</option>';

      fetch(base_url + 'Local/locais_depositos')
        .then(response => response.json())
        .then(data => {
          selectLocal.innerHTML = '<option value="" disabled selected>Selecione um Local</option>';
          const locaisFiltrados = data.filter(item => item.codigo_deposito == selectDeposito.value);
          locaisFiltrados.forEach(local => {
            const option = document.createElement('option');
            option.value = local.codigo_local;
            option.textContent = local.nome_local;
            selectLocal.appendChild(option);
          });
        })
        .catch(err => {
          console.error('Erro ao carregar locais:', err);
          selectLocal.innerHTML = '<option value="" disabled selected>Erro ao carregar</option>';
        });
    }

    function adicionarAlocacao() {
      const nova = document.querySelector('.alocacao').cloneNode(true);

      nova.querySelector('.deposito').value = '';
      nova.querySelector('.local').innerHTML = '<option value="" disabled selected>Selecione um Local</option>';
      nova.querySelector('.saldo').value = '';
      nova.querySelector('.validade').value = '';

      document.getElementById('alocacoes').appendChild(nova);
    }

    function verificarSaldo() {
      let totalAlocado = 0;
      let saldoMaximo = parseInt(document.getElementById('saldo_alocar').value);
      let duplicatas = new Set();
      let duplicado = false;

      const saldos = document.querySelectorAll('.saldo');
      const locais = document.querySelectorAll('.local');
      const validades = document.querySelectorAll('.validade');

      for (let i = 0; i < saldos.length; i++) {
        let valor = parseInt(saldos[i].value) || 0;
        let local = locais[i].value;
        let validade = validades[i].value || 'SEM_VALIDADE';

        totalAlocado += valor;

        let chave = `${local}_${validade}`;
        if (duplicatas.has(chave)) {
          duplicado = true;
        } else {
          duplicatas.add(chave);
        }
      }

      if (totalAlocado > saldoMaximo) {
        alert("O total alocado não pode ultrapassar o saldo disponível.");
        document.getElementById('submitBtn').disabled = true;
        return;
      }

      if (duplicado) {
        alert("Não é permitido alocar o mesmo local e validade mais de uma vez.");
        document.getElementById('submitBtn').disabled = true;
        return;
      }

      document.getElementById('submitBtn').disabled = totalAlocado === 0 || totalAlocado > saldoMaximo;
      document.getElementById('addAlocacaoBtn').disabled = totalAlocado === saldoMaximo;
    }
  </script>
</body>
</html>
