<?php
namespace controllers;

class SolicitacaoController extends Controller {


    public function index(){
        //$this->checkAccess();

        try {
            $solicitacao = $this->model->listarSolicitacoes();
            $this->view->render('solicitacao.php', ['solicitacao' => $solicitacao]);
        } catch (\Exception $e) {
            error_log("Erro ao carregar produtos: " . $e->getMessage());
            die("Erro ao carregar produtos: " . $e->getMessage());
        }
    }


    public function getProdutos($id){
        try {
            // Definir cabeçalho de resposta como JSON
			header('Content-Type: application/json');
		
            $produtos = $this->model->produtosSol($id);
            echo json_encode($produtos);

        } catch (\Exception $e) {
            error_log("Erro ao carregar produtos: " . $e->getMessage());
            die("Erro ao carregar produtos: " . $e->getMessage());
        }

    }

    public function itensRetirar($id,$quantidade){
        try {
            // Definir cabeçalho de resposta como JSON
			header('Content-Type: application/json');
		
            $produtos = $this->model->itensSolVal($id,$quantidade);
            echo json_encode($produtos);

        } catch (\Exception $e) {
            error_log("Erro ao carregar produtos: " . $e->getMessage());
            die("Erro ao carregar produtos: " . $e->getMessage());
        }

    }


    public function alterarQuantidade($idSolicitacao, $idProduto, $novaQuantidade) {
        try {
            // Definir cabeçalho de resposta como JSON
            header('Content-Type: application/json');
    
            // Chama a função do modelo para alterar a quantidade
            $resultado = $this->model->alterarQuantidadeProduto($idSolicitacao, $idProduto, $novaQuantidade);
            
            // Verifica se a quantidade foi atualizada corretamente
            if ($resultado) {
                // Retorna sucesso
                echo json_encode(['success' => true, 'message' => 'Quantidade atualizada com sucesso.']);
            } else {
                // Retorna falha
                echo json_encode(['success' => false, 'message' => 'Falha ao atualizar a quantidade.']);
            }
        } catch (\Exception $e) {
            // Log de erro em caso de falha
            error_log("Erro ao alterar quantidade do produto: " . $e->getMessage());
            // Retorna erro genérico
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar quantidade do produto.']);
        }
    }
    
    public function solicitacaofinal() {
        //$this->checkAccess();
    
        try {
            // Pega a página atual (default é 1)
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $itensPorPagina = 10;  // Defina o número de itens por página
            $offset = ($pagina - 1) * $itensPorPagina;
    
            // Busca as solicitações paginadas
            $solicitacao = $this->model->listarSolicitacoesfinal($offset, $itensPorPagina);
            // Conta o total de solicitações
            $totalSolicitacoes = $this->model->contarSolicitacoesfinal();
            $totalPaginas = ceil($totalSolicitacoes / $itensPorPagina);  // Calcula o total de páginas
    
            $this->view->render('solicitacaofinalizadas.php', [
                'solicitacao' => $solicitacao,
                'base_url' => $this->base_url,
                'pagina' => $pagina,
                'totalPaginas' => $totalPaginas
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao carregar solicitações: " . $e->getMessage());
            die("Erro ao carregar solicitações: " . $e->getMessage());
        }
    }    

}
?>