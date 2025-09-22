<?php
    namespace controllers;

    class LoginController extends Controller{
        
        public function login(){
            $this->view->render('login.php', [
                'base_url' => $this->base_url
            ]);
        }


        public function criarlogin(){
            $this->view->render('criarlogin.php', [
                'base_url' => $this->base_url
            ]);
        }
        
        
        public function usuario(){
            $posdata = $_POST;
            $this->model->criaruser($posdata);
            
        }

        public function novasenha(){
            $this->view->render('novasenha.php');
        }

        public function logar() {

            $posdata = $_POST;
            if (session_status() == PHP_SESSION_NONE) {
                ini_set('session.cookie_lifetime', 0); // Expira ao fechar o navegador
                session_start();
            }
            $login = $this->model->verificarLogin($posdata['email'], $posdata['senha']);
            
            if ($login) {
                // Definir a variável de sessão para o usuário autenticado
                
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $login['id'];
                $_SESSION['nome'] = $login['nome'];
                $_SESSION['username'] = $login['email']; // Corrigido: guardar o email do usuário
                $_SESSION['tipo'] = $login['tipo'];
                $_SESSION['setor'] = $login['setor']; // Adiciona o setor à sessão
                $_SESSION['subsetor'] = $login['subsetor']; // Adiciona o subsetor à sessão
                header('Location: ' . $this->base_url);
                exit; // É uma boa prática chamar exit após um redirecionamento
            } else {
                $_SESSION['mensagem_confirmacao'] = 'Usuário ou senha inválidos. Tente novamente';
                header('Location: ' . $this->base_url . 'Login/login');
                exit;              
            }
        }

        public function alterarSenha(){
            $senha = $_POST; 

            $sucess = $this->model->alterarSenha($senha);

            if($sucess){
                $_SESSION['mensagem_confirmacao'] == "SENHA ALTERADA COM SUCESSO"; 

            }

            else{

                $_SESSION['mensagem_confirmacao'] == "Não foi alterar contate ADM ";


            }

            header('Location: ' . $this->base_url . 'Item');


        }



        
        


        public function logout() {
            // Iniciar a sessão
            session_start();
        
            // Limpar todas as variáveis de sessão
            $_SESSION = array();
        
            // Se o cookie de sessão existir, exclua-o
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, 
                    $params["path"], $params["domain"], 
                    $params["secure"], $params["httponly"]
                );
            }
        
            // Destruir a sessão
            session_destroy();
        
            // Redirecionar para a página de login ou home
            header('Location: ' . $this->base_url . 'Login/login');
            exit;
        }
        
        public function logoutAutomatico() {
            $this->logout(); // reaproveita a função existente
        }

        
        
    }
?>