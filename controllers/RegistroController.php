<?php
	namespace controllers;

	class RegistroController extends Controller{
	
		protected $base_url;

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

                // Registrar compra e atualizar estoque apenas se a compra for bem-sucedida
                if ($this->model->compra(
                    $registro["quantidade"],
                    $registro["codigo_item"],
                    $registro["numero_nota"],
                    $registro["data"],
                    $registro["custo_novo"]
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
        
        
     
    }

?>
		