<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

    
 		// Detecta o ambiente (localhost ou hospedagem)
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
		// Ambiente local
			$base_url= '/estoque_facil/';
		} else {
		// Ambiente de produção
			$base_url = '/';
		}
    ?>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo $base_url; ?>styles.css?v=25.0">
    <title>ESTOQUE FÁCIL</title>
</head>
<body>
<!-- Cabeçalho -->
<header>
    <div class="header-content">
        <img src="<?php echo $base_url; ?>img/logo2.jpg" alt="Logo CCBJ" class="logo">
        <h1>Estoque Fácil</h1>
        <h3>Bem vindo, <?php echo $_SESSION['nome'];?> </h3>
        <!-- Ícone do menu hambúrguer para mobile -->
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>

<nav class="header-navbar" id="navbar">
    <ul class="main-nav">
        <?php if (isset($_SESSION['tipo']) && ($_SESSION['tipo'] === 'infra' || $_SESSION['tipo'] === 'admin')): ?>
            <li>
                <span>Itens</span>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>Item/">Estoque</a></li>
                    <li><a href="<?php echo $base_url; ?>Item/itens_saldo">Itens</a></li>
                    <li><a href="<?php echo $base_url; ?>Item/Item_novo">Cadastrar novo item</a></li>
                </ul>
            </li>
            <li>
                <a>Registros</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>Registro/index">Saída</a></li>
                    <li><a href="<?php echo $base_url; ?>Registro/entrada">Entrada</a></li>
                </ul>
            </li>
            <li>
                <span>Solicitações</span>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>Solicitacao/solicitacaoproduto">Nova Solicitação</a></li>
                    <li><a href="<?php echo $base_url; ?>Solicitacao/index">Solicitações em aberto</a></li>
                    <li><a href="<?php echo $base_url; ?>Solicitacao/solicitacaofinal">Solicitações Finalizadas</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li>
                <span>Solicitações</span>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>Solicitacao/solicitacaoproduto">Nova Solicitação</a></li>

                    <li><a href="<?php echo $base_url; ?>Solicitacao/solicitacoesPorSetor" style="white-space: nowrap;"> Solicitações <?php echo  $_SESSION['setor']  ?></a></li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Sempre visíveis -->
        <li><a href="<?php echo $base_url; ?>Login/novasenha" style="white-space: nowrap;">Nova Senha</a></li>
        <li><a href="<?php echo $base_url; ?>Login/logout" style="white-space: nowrap;">Logout</a></li>
    </ul>
</nav>



</header>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        const menuToggle = document.getElementById('mobile-menu');
        const navbar = document.getElementById('navbar');

        if (menuToggle && navbar) {
            menuToggle.addEventListener('click', function() {
                navbar.classList.toggle('active');
            });
        } else {
            console.log("Erro: Elemento não encontrado.");
        }
    });


    let baseUrl = '<?php echo $base_url; ?>'; // pega o base_url do PHP

    let tempoInatividade = 10 * 60 * 1000; // 10 minutos
    let timer;

    function resetarTimer() {
        clearTimeout(timer);
        timer = setTimeout(deslogar, tempoInatividade);
    }

    function deslogar() {
        // redireciona usando o base_url
        window.location.href = baseUrl + 'Login/logoutAutomatico';
    }

    document.addEventListener('mousemove', resetarTimer);
    document.addEventListener('keypress', resetarTimer);
    document.addEventListener('click', resetarTimer);
    document.addEventListener('scroll', resetarTimer);

    resetarTimer();
</script>

