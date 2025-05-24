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

		public function item_editar(){
			$item = $_POST;
			$this->view->render('item_editar.php', ['item' => $item]);


		}

		public function atualizar_item() {
			$item = $_POST;
			$this->model->atualizar_item($item);
			$_SESSION['mensagem_confirmacao'] = "Edição realizada com sucesso";
			header("Location: {$this->base_url}Item/itens_saldo");


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


		public function transferir(){
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
				$item = $_POST;
				$estoque = $this->model->estoques($item);

				$this->view->render('transferir_itens.php', ['estoque' => $estoque]);
			} else {
				// Se não houver POST, redireciona ou exibe uma mensagem de erro
				header('Location: /erro');
				exit();
			}
				
			
		}
		public function ajuste_estoque() {
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
				$item = $_POST;
				$estoque = $this->model->estoques($item);

				$this->view->render('ajuste_estoque.php', ['estoque' => $estoque]);
			} else {
				// Se não houver POST, redireciona ou exibe uma mensagem de erro
				header('Location: /erro');
				exit();
			}
		}


		public function alocar_itensbd() {
			$itens = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$totalSaldoAlocado = 0;
		
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
					$totalSaldoAlocado += $saldo;
				} else {
					echo "Erro ao processar a alocação.";
				}
			}
		
			// Atualiza saldo alocar apenas uma vez, fora do loop
			if ($totalSaldoAlocado > 0) {
				$saldo_alocar = $itens["saldo_alocar"] - $totalSaldoAlocado;
				$this->model->setSaldo_alocar($itens["codigo_item"], $saldo_alocar);
			}
		
			$_SESSION['mensagem_confirmacao'] = "Saldo alocado corretamente, confira novo estoque";
			header("Location: {$this->base_url}Item/");
			exit;
		}

		public function transferir_estoque() {
			$itens = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$totalSaldoTransferido = 0;
		
			// Extrai os dados recebidos via POST
			$quantidade = (int)$itens["quantidade"];
			$codigo_item = (int)$itens["codigo_item"];
			$saldo_atual = (int)$itens["saldo_atual"];
			$id_estoque_origem = (int)$itens["id_estoque_origem"];
			$validade_origem = !empty($itens["validade_origem"]) ? $itens["validade_origem"] : null;
			$codigo_local_origem = (int)$itens["local_origem"];
			$codigo_local_destino = (int)$itens["local_destino"];
			$codigo_deposito_origem = (int)$itens["deposito_origem"];
			$codigo_deposito_destino = (int)$itens["deposito_destino"];
		
			// Verifica se o saldo a transferir é maior que o saldo disponível
			if ($quantidade > $saldo_atual) {
				$_SESSION['mensagem_erro'] = "Erro: Saldo insuficiente para transferência.";
				header("Location: {$this->base_url}Item/");
				exit;
			}
		
			// Verifica se já existe estoque no destino
			$existe_destino = $this->model->verificarEstoque($codigo_item, $codigo_local_destino, $validade_origem);
		
			if ($existe_destino === false) {
				// Cria novo estoque no destino
				$estoque_id_destino = $this->model->inserirEstoque($codigo_item, $codigo_local_destino, $validade_origem, $quantidade);
			} else {
				// Atualiza saldo no estoque de destino
				$novoSaldoDestino = $existe_destino["saldo"] + $quantidade;
				$estoque_id_destino = $this->model->setSaldo_estoque($existe_destino["id"], $novoSaldoDestino);
			}
		
			if ($estoque_id_destino) {
				$totalSaldoTransferido += $quantidade;
		
				// Atualiza o saldo no estoque de origem
				$novoSaldoOrigem = $saldo_atual - $quantidade;
				if ($novoSaldoOrigem > 0) {

					$this->model->setSaldo_estoque($id_estoque_origem, $novoSaldoOrigem);
				} else {
					// Deleta o estoque da origem se o saldo ficar zero
			
					$this->model->deletarEstoque($id_estoque_origem);
				}
			} else {
				$_SESSION['mensagem_erro'] = "Erro ao processar a transferência.";
				header("Location: {$this->base_url}Item/");
				exit;
			}
		
			if ($totalSaldoTransferido > 0) {
				$_SESSION['mensagem_confirmacao'] = "Transferência realizada corretamente.";
			} else {
				$_SESSION['mensagem_erro'] = "Erro na transferência. Verifique os dados.";
			}
		
			header("Location: {$this->base_url}Item/");
			exit;
		}
		
		


		
		public function setValorUnitario(){
			$item = $_POST;
			$codigo_item = $item["codigo_item"];
			$valor_unitario = $item["valor_unitario"];

			$this->model->setValorUnitario($codigo_item,$valor_unitario);

		}


				
	}
?>
