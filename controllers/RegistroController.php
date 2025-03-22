<?php
	namespace controllers;

	class RegistroController extends Controller{
	
		protected $base_url;


        public function viewcompra() {
            $item = $_POST; 
            $this->view->render("registro_compra.php",['item' => $item]);
        } 
        
        public function registrarcompra(){
            $registro = $_POST;
            $saldo_final = $registro["saldo_atual"] + $registro["quantidade"];

            //var_dump($registro);

            //Verificar novo custo unitario

            if ($registro["saldo_atual"] == 0) {
                // Se o saldo atual for zero, o novo custo é apenas o custo da nova compra
                $custo = $registro["custo_novo"];
            } else {
                // Se já houver estoque, calcular o novo custo médio ponderado
                $valor_atual = $registro["custo_atual"] * $registro["saldo_atual"];
                $valor_novo = $registro["custo_novo"] * $registro["quantidade"];
                
                if ($saldo_final > 0) {
                    $custo = ($valor_atual + $valor_novo) / $saldo_final;
                } else {
                    $custo = $custo_novo; // Evita divisão por zero
                }
            }


            //Atualizar novo custo unitário
            $this->model->setValorUnitario($registro["codigo_item"],$custo);



        }
    }

?>
		