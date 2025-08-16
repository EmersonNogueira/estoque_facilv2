<?php
	namespace controllers;

	class Controller{

		protected $view;
		protected $model;

		public function __construct($view,$model){
			$this->view = $view;
			$this->model = $model;

			// Detecta o ambiente (localhost ou hospedagem)
			if ($_SERVER['HTTP_HOST'] == 'localhost') {
				// Ambiente local
				$this->base_url= '/estoque_facil/';
			} else {
				// Ambiente de produção
				$this->base_url = '/';
			}

		// Controle de inatividade
        $timeout = 900; // 15 minutos

        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
            session_unset();
            session_destroy();
            header("Location: {$this->base_url}Login/login");
            exit;
        }

        $_SESSION['LAST_ACTIVITY'] = time();
			

		}

		public function index(){}
	}
?>


