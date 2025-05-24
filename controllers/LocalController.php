<?php
	namespace controllers;

	class LocalController extends Controller{
	
		protected $base_url;


        public function locais_depositos() {
            try {
                // Obtém os dados dos locais e depósitos
                $itens = $this->model->locais_depositos();
        
                // Define o cabeçalho Content-Type como application/json
                header('Content-Type: application/json');
        
                // Converte o array de itens para JSON e retorna a resposta
                echo json_encode($itens);
        
            } catch (\Exception $e) {
                // Registra o erro no log
                error_log("Erro ao carregar locais: " . $e->getMessage());
        
                // Define o código de status HTTP como 500 (Erro Interno do Servidor)
                http_response_code(500);
        
                // Retorna uma mensagem de erro em formato JSON
                echo json_encode(['erro' => 'Erro ao carregar locais: ' . $e->getMessage()]);
            }
        }
        
    }

?>
		