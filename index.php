<?php
	// Iniciar a sessão
	session_start();

	// Definir a base URL
	$autoload = function ($class) {
		// Normaliza o nome da classe para usar o separador de diretório correto
		$classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
		
		// Verifica se o arquivo existe na pasta 'src' ou na raiz
		if (file_exists($classPath)) {
			include($classPath);
		} else {
			die('Não conseguimos chamar a classe: ' . $class);
		}
	};

	spl_autoload_register($autoload);

	$application = new Application();
	$application->run();

?>