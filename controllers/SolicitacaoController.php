<?php
namespace controllers;

class SolicitacaoController extends Controller {


  public function __construct($view,$model){
        if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
            header('Location: ' . $this->base_url . 'Login/login');
            exit;
        }
        parent::__construct($view,$model);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    private function checkAccess() {
        // Verifica se a sessão está iniciada

    
        // Verifica o tipo de usuário e as permissões básicas
        if (isset($_SESSION['tipo'])) {
            $tipoUsuario = $_SESSION['tipo'];
    
            // Permite acesso para admin e infra
            if ($tipoUsuario === 'admin' || $tipoUsuario === 'infra' || $tipoUsuario === 'solicitante') {
                return;
            }
    
            // Permite acesso para solicitante apenas à rota específica
            if ($tipoUsuario === '' && strpos($_SERVER['REQUEST_URI'], $this->base_url . 'Solicitacao/solicitacaoproduto') === 0) {
                return;
            }
        }
    
        // Qualquer outra situação resulta em redirecionamento para a página de login
        header('Location: ' . $this->base_url . 'Solicitacao/solicitacaoproduto');
        exit;
    }


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


    public function solicitacoesPorSetor() {
        try {
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $itensPorPagina = 10;
            $offset = ($pagina - 1) * $itensPorPagina;

            $solicitacoes = $this->model->listarSolicitacoesPorSetor($offset, $itensPorPagina);
            $totalSolicitacoes = $this->model->contarSolicitacoesPorSetor();
            $totalPaginas = ceil($totalSolicitacoes / $itensPorPagina);

            $this->view->render('solicitacaomeusetor.php', [
                'solicitacoes' => $solicitacoes,
                'base_url' => $this->base_url,
                'pagina' => $pagina,
                'totalPaginas' => $totalPaginas
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao carregar solicitações do setor: " . $e->getMessage());
            die("Erro ao carregar solicitações do setor: " . $e->getMessage());
        }
    }

    
        public function solicitacaoproduto() {
        try {
            $produtos = $this->model->reader();
            $this->view->render('solicitacaoproduto.php', ['produtos' => $produtos]);
        } catch (\Exception $e) {
            error_log("Erro ao carregar produtos: " . $e->getMessage());
            die("Erro ao carregar produtos: " . $e->getMessage());
        }
    }


           public function adicionarAoCarrinho() {
            // Inicia a sessão se ainda não estiver iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        
            // Define o cabeçalho para JSON
            header('Content-Type: application/json');
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Verifica se os dados necessários foram enviados
                if (isset($_POST['id_produto']) && isset($_POST['quantidade']) && isset($_POST['nome'])) {
                    $idProduto = $_POST['id_produto'];
                    $quantidade = (int)$_POST['quantidade'];
                    $nome = $_POST['nome'];
        
                    // Verifica se a quantidade é válida
                    if ($quantidade < 1) {
                        echo json_encode(['status' => 'error', 'message' => 'Quantidade inválida.00']);
                        return;
                    }
        
                    // Adiciona ao carrinho
                    if (!isset($_SESSION['solicitacao'])) {
                        $_SESSION['solicitacao'] = [];
                    }
        
                    // Se o produto já existir no carrinho, soma a quantidade
                    if (isset($_SESSION['solicitacao'][$idProduto])) {
                            
                        $saldo = $this->model->getSaldoProduto($idProduto);
                        $temp = $_SESSION['solicitacao'][$idProduto]['quantidade'] + $quantidade;
                        if($temp>$saldo){
                            echo json_encode(['status' => 'error', 'message' => 'Quantidade inválida.ttt']);
                            return;
                        }else{
                            $_SESSION['solicitacao'][$idProduto]['quantidade'] += $quantidade;

                        }
                            
                    } else {
                        // Caso contrário, adiciona o produto ao carrinho
                        $_SESSION['solicitacao'][$idProduto] = [
                            'quantidade' => $quantidade,
                            'nome' => $nome
                        ];
                    }
        
                    echo json_encode(['status' => 'success', 'message' => 'Produto adicionado à solicitação.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Dados faltando.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
            }
        }
        
        public function verCarrinho() {
            // Inicia a sessão se ainda não estiver iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        
            // Define o cabeçalho para JSON
            header('Content-Type: application/json');
        
            // Verifica se a sessão do carrinho existe e se está preenchida
            if (!isset($_SESSION['solicitacao']) || empty($_SESSION['solicitacao'])) {
                echo json_encode(['status' => 'empty', 'message' => 'Nenhum produto na solicitação']);
            } else {
                // Recupera os produtos e suas quantidades e nomes
                $solicitacao = $_SESSION['solicitacao'];
        
                // Retorna a lista de produtos adicionados com nome e quantidade
                echo json_encode(['status' => 'success', 'data' => $solicitacao]);
            }
        }
        

        public function removerDoCarrinho() {
            // Inicia a sessão se ainda não estiver iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        
            // Define o cabeçalho para JSON
            header('Content-Type: application/json');
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['id_produto'])) {
                    $idProduto = $_POST['id_produto'];
        
                    // Verifica se o produto existe na sessão e o remove
                    if (isset($_SESSION['solicitacao'][$idProduto])) {
                        unset($_SESSION['solicitacao'][$idProduto]);
                        echo json_encode(['status' => 'success', 'message' => 'Produto removido do carrinho.']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Produto não encontrado no carrinho.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Dados faltando.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
            }
        }

     public function enviarSolicitacao() {
        // Exibir os dados recebidos (para debug)

        // Inicializando o array que irá armazenar os dados
        $dadosParaModelo = [];

        // Adicionando o id do usuário da sessão
        $dadosParaModelo['usuario_id'] = $_SESSION['id'];
        $dadosParaModelo['solicitante'] = $_POST['nome'];
        $dadosParaModelo['setor'] = $_POST['setorDestino'];
        $dadosParaModelo['subsetor'] = $_POST['subsetorDestino'];

        // Adicionando os produtos e quantidades
        $dadosParaModelo['produtos'] = []; // Array para armazenar produtos
        foreach ($_SESSION['solicitacao'] as $idProduto => $produto) {
            $quantidade = $produto['quantidade'];
            
            // Adicionando cada produto e sua quantidade ao array
            $dadosParaModelo['produtos'][] = [
                'id_produto' => $idProduto,
                'quantidade' => $quantidade
            ];
        }


        // Exemplo de visualização dos dados para debug
        /*echo "<pre>";
        print_r($dadosParaModelo);
        echo "</pre>";
                
        print_r($dadosParaModelo['produtos']);*/

        $this->model->inserirSolicitacao($dadosParaModelo);
        header("Location: {$this->base_url}Solicitacao/solicitacaoproduto");
        $_SESSION['solicitacao'] = [];

        
    } 
    
    public function pedidoSeparado($codigo_solicitacao) {
        $a = $this->model->pedidoSeparado($codigo_solicitacao);
        $_SESSION['mensagem_confirmacao'] = "Pedido separado com sucesso.";

        header("Location: {$this->base_url}Solicitacao/index");
    }



}
?>