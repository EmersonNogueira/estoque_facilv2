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

		public function alocar(){
			$itens = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			
			if($itens['saldo_alocar']>0){
				$this->view->render('alocar_itens.php', ['itens' => $itens]);
				
			}
			else{
				$_SESSION['mensagem_confirmacao'] = "Não a saldo para alocar registre uma compra";
				header("Location: {$this->base_url}Item/itens_saldo");
			}




		}

		public function alocar_itensbd() {
			$itens = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);


			foreach ($itens["local"] as $key => $codigo_local) {
				// Acessando os valores corretos usando o índice $key
				$validade = $itens["validade"][$key] ?? null;
				$saldo = $itens["saldo"][$key] ?? 0; // Aqui, pegamos o saldo corretamente, com valor padrão 0
		
				// Verifica se os dados necessários existem antes de chamar a função
				if (!isset($itens["codigo_item"], $validade, $codigo_local, $saldo)) {
					echo "Erro: Dados ausentes para alocação.";
					continue;
				}
		
				// Chamada correta do método
			
				$existe = $this->model->verificarEstoque($itens["codigo_item"], $codigo_local, $validade);// verifica se existe no estoque se o item já existe no mesmo local e validade 
		
				if ($existe == false) { // se não existe com mesmo local e validade
					// Criar nova linha na tabela estoque, incluindo o saldo

					$sucesso = $this->model->inserirEstoque($itens["codigo_item"], $codigo_local, $validade, $saldo);
					if ($sucesso == false) {
						echo "Erro na inserção";
					} else {
						//Atualizar saldo alocar do item apos a insercao
						$saldo_alocar = $itens["saldo_alocar"] - $saldo; 
						$this->model->setSaldo_alocar($itens["codigo_item"],$saldo_alocar);
						header("Location: {$this->base_url}Item/itens_saldo");

					}
				} else {
					// Atualizar saldo onde o id de estoque é $existe
				}
			}
		}
		

		
		
	}

			//foreach ($dados["local"] as $indice => $valor) {

			
			//if($existe){
				//$this->model->aumentar_saldo($itens['codigo_item'],$itens['saldo'][$indice]);

			//}
			//else{
				//$this->model->alocar_itens($itens['codigo_item']);

			//}

?>
