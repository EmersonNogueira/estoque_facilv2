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

                $saldoFinal = $registro["saldo_atual"] + $registro["quantidade"];
                $saldoAlocar = $registro["saldo_alocar"] + $registro["quantidade"];

                // Cálculo do novo custo unitário
                $saldoTotal = $registro["saldo_atual"] + $registro["saldo_alocar"];
                if ($saldoTotal == 0) {
                    $custo = $registro["custo_novo"];
                } else {
                    $valorAtual = $registro["custo_atual"] * $saldoTotal;
                    $valorNovo = $registro["custo_novo"] * $registro["quantidade"];
                    $custo = ($saldoFinal > 0) ? ($valorAtual + $valorNovo) / $saldoFinal : $registro["custo_novo"];
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

    }

?>
		