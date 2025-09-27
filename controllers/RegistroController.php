<?php
	namespace controllers;

	class RegistroController extends Controller{
	
		protected $base_url;

        public function __construct($view, $model) {
            // Primeiro: inicia a sessão, se ainda não tiver sido iniciada
            if (session_status() == PHP_SESSION_NONE) {
                ini_set('session.cookie_lifetime', 0); // Expira ao fechar navegador
                session_start();
            }



            // Agora pode chamar verificação de acesso e construtor pai
            $this->checkAccess();
            parent::__construct($view, $model);
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
				$registros = $this->model->reader();
				$this->view->render('registro.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}        

        public function viewcompra() {
            $item = $_POST; 
            $this->view->render("registro_compra.php",['item' => $item]);
        } 
        
        public function registrarCompra(): void {
            try {
                $registro = $_POST;

                
                $saldoFinal = $registro["saldo_atual"] + $registro["quantidade"] + $registro["saldo_alocar"];
                $saldoAlocar = $registro["saldo_alocar"] + $registro["quantidade"];

                // Cálculo do novo custo unitário
                $saldoTotal = $registro["saldo_atual"] + $registro["saldo_alocar"];
                if ($saldoTotal == 0) {
                    $custo = $registro["custo_novo"];
                } else {
                    $valorAtual = $registro["custo_atual"] * $saldoTotal;
                    $valorNovo = $registro["custo_novo"] * $registro["quantidade"];
                    $custo = ($valorAtual + $valorNovo) / $saldoFinal;
                }

                // Formatação da data
                $data = $registro["data"];
                if (strlen($data) <= 10) { // só data, sem hora
                    $data .= ' ' . date('H:i:s');
                }

                // Registrar compra e atualizar estoque apenas se a compra for bem-sucedida
                if ($this->model->compra(
                    $registro["quantidade"],
                    $registro["codigo_item"],
                    $registro["numero_nota"],
                    $data,
                    $registro["custo_novo"],
                    $registro["tipo_compra"]
                )) {
                    $this->model->setSaldo_alocar($registro["codigo_item"], $saldoAlocar);
                    $this->model->setValorUnitario($registro["codigo_item"], $custo);
                } else {
                    throw new Exception("Erro ao registrar compra no banco de dados.");
                }
                $_SESSION['mensagem_confirmacao'] = "Registro de compra efetuado com sucesso saldo já está diponivel para alocar no estoque";


                // Redirecionamento após sucesso

                header("Location: {$this->base_url}Item/itens_saldo");
                exit;

            } catch (Exception $e) {
                // Log do erro (pode ser enviado para um arquivo de log ou exibido no console)
                error_log("Erro em registrarCompra: " . $e->getMessage());

                // Exibir mensagem amigável para o usuário (opcional, dependendo do ambiente)
                echo "<p>Ocorreu um erro ao registrar a compra. Por favor, tente novamente.</p>";
            }
                   
        }

        public function ajuste_estoque(){
            session_start(); // Inicia a sessão, caso ainda não tenha sido iniciada
            
            $item = $_POST;
            $tipo = $item['tipo'];
            $quantidade = (int) $item['quantidade'];
            $saldo_atual = (int) $item['saldo_atual'];
            
            if ($tipo === "Ajuste Negativo" && $saldo_atual < $quantidade) {
                $_SESSION['mensagem_confirmacao'] = "Quantidade não pode ser maior que o saldo atual.";
            } else {
                // Calcula o novo saldo dependendo do tipo
                $saldo_final = ($tipo === "Ajuste Negativo") ? $saldo_atual - $quantidade : $saldo_atual + $quantidade;
                
                // Atualiza o saldo no estoque
                $this->model->set_saldoEstoque($item["id_estoque"], $saldo_final);
                
                // Registra o ajuste
                $this->model->ajuste_registro($quantidade, $item["codigo_item"], $item["custo_unitario"], $tipo);
                
                $_SESSION['mensagem_confirmacao'] = "Ajuste realizado com sucesso. Verifique Novo saldo:";
            }
            
            header("Location: {$this->base_url}Item/");
            exit();
        }

        public function entrada(){
			try {
				$registros = $this->model->entrada();
				$this->view->render('registroentrada.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}

        public function sinteticoentrada(){
			try {
				$registros = $this->model->sinteticoentrada();
				$this->view->render('registrosinteticoentrada.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}


        public function sintetico(){
			try {
				$registros = $this->model->sintetico();
				$this->view->render('registrosintetico.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}
        
        public function itens() {
            $dados = $_POST;                  
            $id_solicitacao = $dados['codigo_solicitacao'];
            $numero_nota = null; 
            $tipo = 'Solicitação';
            $receptor = $dados['receptor'];



            $data = isset($dados['data']) && !empty($dados['data']) 
            ? $dados['data'] 
            : date('Y-m-d H:i:s');

            $estoque_ids = $dados['estoque']['id_estoque']; // array de ids
            $estoque_saldos = $dados['estoque']['saldo'];   // array de saldos
            foreach ($dados['produtos'] as $produto) {
                $id = $produto['id'];
                $quantidade = $produto['quantidade'];
                $custo = $produto['custo'];
                $saldo_final = $produto['saldo'] - $quantidade;                 
                $this->model->novoregistro($tipo, $quantidade, $id, $id_solicitacao, $numero_nota, $custo, $saldo_final,$data);
            }

            foreach ($estoque_ids as $i => $id_estoque) {
                // Quantidade que será retirada (ou ajustada)
                $quantidade_solicitada = $estoque_saldos[$i] ?? 0;

                // Buscar saldo atual do estoque no banco de dados
                $saldo_atual = $this->model->getSaldoEstoque($id_estoque);

                // Verifica se conseguiu obter o saldo (pode retornar false ou null se o ID não existir)
                if ($saldo_atual === false || $saldo_atual === null) {
                    // Você pode registrar um erro ou pular
                    continue;
                }

                // Subtrai a quantidade retirada
                $novoSaldo = $saldo_atual - $quantidade_solicitada;

                // Atualiza o saldo no banco de dados
                $this->model->set_saldoEstoque($id_estoque, $novoSaldo);
            }



        
            $this->model->setstatus($id_solicitacao);
            $this->model->setreceptor($id_solicitacao,$receptor); 
            $_SESSION['mensagem_confirmacao'] = "Registro(s) de saída efetuado com sucesso";
            header('Location:'. $this->base_url.'Registro/index');

        

        }

        public function getSaldoAlocar($id_produto){
            return $this->model->getSaldoProd($id_produto); // Retorne o saldo diretamente
        }
        

        public function mvdevolucao(){ //ALTERAR OS DADOS DOS REGISTRO E CRIAR UM NOVO REGISTRO
            $dados = $_POST;
            $this->model->devolucao($dados['id_registro'],$dados['quantidadeDevolvida']); //ATUALIZAR REGISTROS
            //$saldo = $this->getSaldoAlocar($dados['id_produto']);
            $tipo ="Devolução";
            $quantidade = $dados['quantidadeDevolvida'];
            $id = $dados['id_produto'];
            $id_solicitacao = $dados['id_solicitacao'];
            $numero_nota = null;
            $custo = null;
            $saldo_final = null;

            $data = isset($postData['data']) && !empty($postData['data']) 
            ? $postData['data'] 
            : date('Y-m-d H:i:s');

            


            $this->model->novoregistro($tipo, $quantidade, $id, $id_solicitacao, $numero_nota, $custo, $saldo_final,$data);

            $this->model->setSaldo_alocar2($id,$quantidade);            

            $_SESSION['mensagem_confirmacao'] = "Registro(s) de devolução efetuado com sucesso";
            header('Location:'. $this->base_url.'Registro/entrada');
        }


    }

?>
		