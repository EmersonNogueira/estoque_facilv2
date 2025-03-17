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
				error_log("Erro ao carregar itens: " . $e->getMessage());
				die("Erro ao carregar itens: " . $e->getMessage());
			}
		}

		public function item_adicionar() {
			// Sanitiza os dados de entrada
			$postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		
			try {
				$result = $this->model->item_adicionar($postData);
				
				if ($result) {
					$_SESSION['mensagem_confirmacao'] = "Item cadastrado com sucesso";
				} else {
					$_SESSION['mensagem_erro'] = "Erro ao cadastrar o produto.";
				}
		
				// Redireciona para a página de itens
				header("Location: {$this->base_url}Item/");
				exit;
				
			} catch (\Exception $e) {
				// Registra erro no log
				error_log("Erro ao adicionar item: " . $e->getMessage());
		
				// Salva mensagem de erro na sessão
				$_SESSION['mensagem_erro'] = "Ocorreu um erro ao processar sua solicitação.";
				
				// Redireciona para evitar exibição de erro na tela
				header("Location: {$this->base_url}Item/");
				exit;
			}
		}
		
	}

?>
