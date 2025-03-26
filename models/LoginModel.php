<?php
	namespace models;

	class LoginModel extends Model
	{


        public function criaruser($data) {
            // Consulta SQL atualizada com as colunas novas
            $sql = "INSERT INTO usuario (nome, setor, subsetor, email, senha, tipo_usuario, data_criacao) 
                    VALUES (:nome, :setor, :subsetor, :email, :senha, :tipo_usuario, NOW())";
        
            // Prepara a consulta SQL
            $stmt = $this->pdo->prepare($sql);
        
            // Vincula os parâmetros usando os dados do array $data
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':setor', $data['setor']);
            $stmt->bindParam(':subsetor', $data['subsetor']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':tipo_usuario', $data['tipo_usuario']);
        
            // Gera o hash da senha antes de bindar o valor
            $hashedPassword = password_hash($data['senha'], PASSWORD_DEFAULT);
            $stmt->bindParam(':senha', $hashedPassword);
        
            // Executa a consulta e retorna o resultado (true ou false)
            return $stmt->execute();
        }
        

        public function verificarLogin($email, $password) {
            // Preparar a consulta para obter o hash da senha, tipo de usuário e subsetor do banco de dados
            $sql = "SELECT id, senha, tipo_usuario, subsetor, setor, nome FROM usuario WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            // Recuperar o hash da senha, tipo de usuário e subsetor armazenados
            $result = $stmt->fetch(\PDO::FETCH_ASSOC); // Obtém a linha do resultado como um array associativo
            
            if ($result) {
                $hashedPassword = $result['senha']; // Acessa o hash da senha
                $userType = $result['tipo_usuario']; // Acessa o tipo de usuário
                $id = $result['id']; // Acessa o id
                $nome = $result['nome']; // Acessa o nome
                $setor = $result['setor']; // Acessa o setor
                $subsetor = $result['subsetor']; // Acessa o subsetor
        
                // Verificar se a senha fornecida corresponde ao hash
                if (password_verify($password, $hashedPassword)) {
                    // Se a senha estiver correta, retorna um array com os dados do usuário
                    return [
                        'id' => $id,
                        'nome' => $nome,
                        'email' => $email, // Corrigido de $username para $email
                        'tipo' => $userType,
                        'setor' => $setor,
                        'subsetor' => $subsetor // Adiciona o subsetor
                    ];
                } else {
                    return false; // Senha incorreta
                }
            } else {
                return false; // Usuário não encontrado
            }
        }


        public function alterarSenha($data) {
            // Consulta SQL para atualizar a senha
            $sql = "UPDATE usuario SET senha = :senha WHERE id = :id";
            
            // Prepara a consulta SQL
            $stmt = $this->pdo->prepare($sql);
        
            // Gera o hash da nova senha antes de bindar o valor
            $hashedPassword = password_hash($data['senha'], PASSWORD_DEFAULT);
        
            // Bind dos parâmetros
            $stmt->bindParam(':senha', $hashedPassword);
            $stmt->bindParam(':id', $_SESSION['id']); // Usando o ID da sessão para localizar o usuário
        
            // Executa a consulta e retorna o resultado (true ou false)
            return $stmt->execute();
        }
        
        
        
        
        

    }

?>


