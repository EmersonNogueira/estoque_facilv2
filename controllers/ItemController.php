<?php
	namespace controllers;

	class ItemController extends Controller{
	
		protected $base_url;
		

		public function index(){
			try {
				$itens = $this->model->itens_locais();
				$this->view->render('itens.php', ['itens' => $itens]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}

		public function item_novo() {
			try {


				$this->view->render('item_novo.php');

			} catch (Exception $e) {
				error_log("Erro ao carregar itens: " . $e->getMessage());
				die("Erro ao carregar itens: " . $e->getMessage());
			}
		}
		


		public function itens_saldo(){
			try {
				$itens = $this->model->itens_saldo();
				$this->view->render('itens_saldo.php', ['itens' => $itens]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}

		public function item_adicionar(){
			$postData = $_POST;

			// Exibe os dados de forma organizada
			echo "<pre>"; // Usado para formatar a saída
			print_r($postData); // Exibe o conteúdo de $postData
			echo "</pre>";

			
		}
	}

?>
