<?php
	namespace controllers;

	class ItemController extends Controller{
	
		protected $base_url;
		
		
		public function __construct($view,$model){
			$this->checkAccess();
			parent::__construct($view,$model);
			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}
			

		}

		private function checkAccess() {
			// Verifica se a sessão está iniciada
			if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
				// Usuário não está logado, redireciona para a página de login
				header('Location: ' . $this->base_url . 'Login/login');
				exit;
			}
			//header('Location: ' . $this->base_url . 'Login/login');

			// Verifica o tipo de usuário
			if (isset($_SESSION['tipo'])) {
				if ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo']=='infra') {
					// Se o usuário for admin, permite o acesso
					return;
				} elseif ($_SESSION['tipo'] === 'solicitante') {
					// Se o usuário for solicitante, permite o acesso à rota de solicitação de produto
					if ($_SERVER['REQUEST_URI'] ===  $this->base_url.'Solicitacao/solicitacaoproduto') {
						return; // Permite o acesso à rota de solicitante
					} else {
						// Redireciona para a página de solicitação de produtos se tentar acessar outra rota
						header('Location:'. $this->base_url.'Solicitacao/solicitacaoproduto');
						exit;
					}
				} else {
					// Se não for admin nem solicitante, exibe uma mensagem de acesso negado
					die('Acesso negado.');
				}
			} else {

				// Caso o tipo de usuário não esteja definido, exibe uma mensagem de erro
				die('Tipo de usuário não definido.');
			}
		}


















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
				$validade = !empty($itens["validade"][$key]) ? $itens["validade"][$key] : null;
				$saldo = $itens["saldo"][$key] ?? 0;
		
				if (!isset($itens["codigo_item"], $codigo_local, $saldo)) {
					echo "Erro: Dados ausentes para alocação.";
					continue;
				}
		
				$codigo_item = $itens["codigo_item"];
				$existe = $this->model->verificarEstoque($codigo_item, $codigo_local, $validade);

				
		
				if ($existe === false) {
					$estoque_id = $this->model->inserirEstoque($codigo_item, $codigo_local, $validade, $saldo);
				} else {
					$novoSaldo = $existe["saldo"] + $saldo;
					$estoque_id = $this->model->setSaldo_estoque($existe["id"], $novoSaldo);
				}
		
				if ($estoque_id) {
					$saldo_alocar = $itens["saldo_alocar"] - $saldo;
					$this->model->setSaldo_alocar($codigo_item, $saldo_alocar);
					header("Location: {$this->base_url}Item/itens_saldo");
					exit;
				} else {
					echo "Erro ao processar a alocação.";
				}

				
			}
		}


		public function setValorUnitario(){
			$item = $_POST;
			$codigo_item = $item["codigo_item"];
			$valor_unitario = $item["valor_unitario"];

			$this->model->setValorUnitario($codigo_item,$valor_unitario);

		}
				
	}
?>
