<div class="container">
    <h1>Cadastro de Usuário</h1>
    <form method="POST" action="<?php echo $base_url; ?>Login/usuario">
    <div class="form-group">
            <label for="nome">Nome do Usuário:</label>
            <input type="text" id="nome" name="nome" required>
        </div>

        <div class="form-group">
            <label for="setorDestino">Setor</label>
                <select name="setor" id="setor" class="form-control" required>
                        <option value="">Selecione o setor</option>
                        <option value="Escola">Escola</option>
                        <option value="Ação Cultural">Ação Cultural</option>
                        <option value="Comunicação">Comunicação</option>
                        <option value="Narte">Narte</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Infraestrutura">Infraestrutura</option>
                        <option value="Gestão">Gestão</option>
                    </select>
        </div>

        <div class="form-group">
            <label for="subsetorDestino">Subsetor</label>
            <select name="subsetor" id="subsetor" class="form-control" required>
                <option value="">Selecione o subsetor</option>
            </select>                          
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>

        <div class="form-group">
            <label for="tipo">Tipo de Usuário:</label>
            <select id="tipo" name="tipo_usuario" required>
                <option value="admin">Admin</option>
                <option value="infra">Infra</option>
                <option value="solicitante">Solicitante</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Cadastrar</button>
    </form>
</div>


<script>
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
        const setor = document.getElementById('setor').value;
        const subsetorSelect = document.getElementById('subsetor');
        
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
    document.getElementById('setor').addEventListener('change', updateSubsetor);

</script>