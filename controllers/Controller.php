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
			

		}

		public function index(){}
	}
?>