<?php
    namespace models;

    class RegistroModel extends Model
    {


        public function reader() {
            try {
                // Verifica se o parâmetro de busca está presente na URL
                $search = isset($_GET['search']) ? $_GET['search'] : '';

                // Prepara a consulta SQL com JOIN para obter a descrição do item, setor e nome do usuário
                $sqlStr = "SELECT r.*, 
                            i.descricao AS descricao_item, 
                            s.setor, 
                            s.subSetor, 
                            u.nome AS nome_usuario
                        FROM registros r
                        JOIN itens i ON r.codigo_item = i.codigo_item
                        LEFT JOIN solicitacoes s ON r.codigo_solicitacao = s.codigo_solicitacao
                        LEFT JOIN usuario u ON r.id_usuario = u.id
                        WHERE r.tipo IN ('Solicitação', 'Ajuste Negativo')";

                // Adiciona a condição de busca se o parâmetro de pesquisa estiver presente
                if (!empty($search)) {
                    $sqlStr .= " AND i.descricao LIKE :search"; // Filtra pela descrição do item
                }

                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; 

                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);

                // Vincula o parâmetro de busca se necessário
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%');
                }

                $sql->execute();
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                return $resultados;
            } catch (\PDOException $e) {
                error_log("Erro na consulta: " . $e->getMessage());
                die("Erro na consulta: " . $e->getMessage());
            }
        }



        


        public function setValorUnitario($codigo_item, $custo_unitario) {
            $sql = "UPDATE itens SET custo_unitario = :custo_unitario WHERE codigo_item = :codigo_item";
        
            $stmt = $this->pdo->prepare($sql);
        
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
            $stmt->bindParam(':custo_unitario', $custo_unitario, \PDO::PARAM_STR); // Se for decimal, pode ser tratado como string
        
            return $stmt->execute();
        }

        public function compra($quantidade, $codigo_item, $numero_nota, $data, $custo,$tipo_compra) {
            $tipo = "Compra";
            $id_usuario = $_SESSION['id'] ?? null; // Garante que id_usuario pode ser NULL se não estiver definido
        
            try {
                // Inicia a transação
                $this->pdo->beginTransaction();
        
                // Insere o registro na tabela registros
                $sql = "INSERT INTO registros (tipo, quantidade, codigo_item, numero_nota, data_registro, custo, id_usuario, tipo_compra) 
                        VALUES (:tipo, :quantidade, :codigo_item, :numero_nota, :data_registro, :custo, :id_usuario, :tipo_compra)";
        
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':tipo', $tipo, \PDO::PARAM_STR);
                $stmt->bindParam(':tipo_compra', $tipo_compra, \PDO::PARAM_STR);

                $stmt->bindParam(':quantidade', $quantidade, \PDO::PARAM_INT);
                $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
                $stmt->bindParam(':numero_nota', $numero_nota, \PDO::PARAM_STR);
                $stmt->bindParam(':data_registro', $data, \PDO::PARAM_STR);
                $stmt->bindParam(':custo', $custo, \PDO::PARAM_STR);
                $stmt->bindParam(':id_usuario', $id_usuario, $id_usuario ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
        
                $stmt->execute();
        
                // Atualiza o custo unitário do item na tabela itens
                $this->setValorUnitario($codigo_item, $custo);
        
                // Confirma a transação
                $this->pdo->commit();
        
                return true;
            } catch (Exception $e) {
                // Em caso de erro, desfaz a transação
                $this->pdo->rollBack();
        
                // Registra o erro (pode ser alterado para log de sistema)
                error_log("Erro na compra: " . $e->getMessage());
        
                return false;
            }
        }
        

        public function setSaldo_alocar($codigo_item, $saldo_alocar) {
            $sql = "UPDATE itens SET saldo_alocar = :saldo_alocar WHERE codigo_item = :codigo_item";
        
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
            $stmt->bindParam(':saldo_alocar', $saldo_alocar, \PDO::PARAM_INT); 
        
            return $stmt->execute();
        }
        
        public function setSaldo_alocar2($codigo_item, $saldo_adicional) {
            $sql = "UPDATE itens SET saldo_alocar = saldo_alocar + :saldo_adicional WHERE codigo_item = :codigo_item";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':saldo_adicional', $saldo_adicional, \PDO::PARAM_INT);
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);

            return $stmt->execute();
        }


        public function set_saldoEstoque($id, $saldo){

            
            try {
                // Inicia a transação
                $this->pdo->beginTransaction();
                
                if ($saldo == 0) {
                    // Se o saldo for zero, remove a linha correspondente
                    $sql = "DELETE FROM estoques WHERE id = :id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
                } else {
                    // Caso contrário, apenas atualiza o saldo
                    $sql = "UPDATE estoques SET saldo = :saldo WHERE id = :id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindParam(':saldo', $saldo, \PDO::PARAM_INT);
                    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
                }
                
                $stmt->execute();
                
                // Confirma a transação
                $this->pdo->commit();
                
                return true;
            } catch (Exception $e) {
                // Em caso de erro, desfaz a transação
                $this->pdo->rollBack();
                
                // Registra o erro (pode ser alterado para log de sistema)
                error_log("Erro ao atualizar saldo do estoque: " . $e->getMessage());
                
                return false;
            }
            
        }

        public function ajuste_registro($quantidade, $codigo_item, $custo_unitario,$tipo){
            $data_registro = date("Y-m-d H:i:s");
            $id_usuario = $_SESSION['id'] ?? null; // Garante que id_usuario pode ser NULL se não estiver definido
        
            try {
                // Inicia a transação
                $this->pdo->beginTransaction();
        

        
                // Insere o registro na tabela registros
                $sql = "INSERT INTO registros (tipo, quantidade, codigo_item, data_registro, custo, id_usuario) 
                        VALUES (:tipo, :quantidade, :codigo_item, :data_registro, :custo, :id_usuario)";
        
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':tipo', $tipo, \PDO::PARAM_STR);
                $stmt->bindParam(':quantidade', $quantidade, \PDO::PARAM_INT);
                $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
                $stmt->bindParam(':data_registro', $data_registro, \PDO::PARAM_STR);
                $stmt->bindParam(':custo', $custo_unitario, \PDO::PARAM_STR);
                $stmt->bindParam(':id_usuario', $id_usuario, $id_usuario ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
        
                $stmt->execute();
        
                // Confirma a transação
                $this->pdo->commit();
        
                return true;
            } catch (Exception $e) {
                // Em caso de erro, desfaz a transação
                $this->pdo->rollBack();
        
                // Registra o erro na sessão para exibição na página
                $_SESSION['mensagem_erro'] = "Erro ao registrar ajuste de estoque: " . $e->getMessage();
        
                // Exibe a mensagem de erro
                echo "<pre>Erro: " . $e->getMessage() . "</pre>";
        
                return false;
            }
        }

        public function entrada() {
            try {
                // Verifica se o parâmetro de busca está presente na URL
                $search = isset($_GET['search']) ? $_GET['search'] : '';

                // Prepara a consulta SQL com JOIN para obter o nome do item e as informações da tabela usuario
                $sqlStr = "SELECT r.*, i.descricao AS descricao_item, u.nome AS nome_usuario
                        FROM registros r
                        JOIN itens i ON r.codigo_item = i.codigo_item
                        JOIN usuario u ON r.id_usuario = u.id
                        WHERE r.tipo IN ('Compra', 'Devolução', 'Ajuste Positivo')"; // Filtra registros do tipo compra, devolução ou ajuste positivo

                // Adiciona a condição de busca se o parâmetro de pesquisa estiver presente
                if (!empty($search)) {
                    $sqlStr .= " AND i.descricao LIKE :search"; // Filtra pelo nome do item
                }

                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes

                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);

                // Vincula o parâmetro de busca se necessário
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%'); // Utiliza o operador LIKE para pesquisar em qualquer parte do nome
                }

                $sql->execute();
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                return $resultados;
            } catch (\PDOException $e) {
                error_log("Erro na consulta: " . $e->getMessage());
                die("Erro na consulta: " . $e->getMessage());
            }
        }

        

        public function sinteticoentrada(){
            try {
                // Prepara a consulta SQL com JOIN para obter o nome do produto, setor e nome do usuário
                $sqlStr = "SELECT r.*, 
                                i.descricao AS descricao_item
                            FROM registros r
                            JOIN itens i ON r.codigo_item = i.codigo_item
                            WHERE r.tipo IN ('Compra', 'Ajuste Positivo')";

                
                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes
                
                // Log da consulta SQL para depuração
                error_log("Consulta SQL: " . $sqlStr);
                
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
                
                // Executa a consulta
                $sql->execute();
                
                // Verifica se há resultados
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                
                // Loga os resultados para depuração
                error_log("Resultados: " . print_r($resultados, true));
                
                // Se não houver resultados, retorna uma mensagem apropriada
                if (empty($resultados)) {
                    return ['message' => 'Nenhum registro encontrado.'];
                }
                
                return $resultados;
            
            } catch (\PDOException $e) {
                // Loga o erro para análise posterior
                error_log("Erro na consulta: " . $e->getMessage());
                
                // Retorna uma mensagem de erro genérica sem expor detalhes da exceção ao usuário
                return ['error' => 'Erro ao processar a consulta, tente novamente mais tarde.'];
            }
        }
        
        
        
        public function sintetico() {
            try {
                // Prepara a consulta SQL com JOIN para obter a descrição do item, setor e nome do usuário
                $sqlStr = "SELECT r.*, 
                            s.setor,
                            s.subSetor,
                            i.descricao AS descricao_item
                        FROM registros r
                        LEFT JOIN solicitacoes s ON r.codigo_solicitacao = s.codigo_solicitacao
                        JOIN itens i ON r.codigo_item = i.codigo_item
                        WHERE r.tipo IN ('Solicitação', 'Ajuste Negativo')";
                
                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes
                
                // Log da consulta SQL para depuração
                error_log("Consulta SQL: " . $sqlStr);
                
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
                
                // Executa a consulta
                $sql->execute();
                
                // Verifica se há resultados
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                
                // Loga os resultados para depuração
                error_log("Resultados: " . print_r($resultados, true));
                
                // Se não houver resultados, retorna uma mensagem apropriada
                if (empty($resultados)) {
                    return ['message' => 'Nenhum registro encontrado.'];
                }
                
                return $resultados;
                
            } catch (\PDOException $e) {
                // Loga o erro para análise posterior
                error_log("Erro na consulta: " . $e->getMessage());
                
                // Retorna uma mensagem de erro genérica sem expor detalhes da exceção ao usuário
                return ['error' => 'Erro ao processar a consulta, tente novamente mais tarde.'];
            }
        }

        public function novoregistro($tipo, $quantidade, $id_produto, $id_solicitacao, $numero_nota, $custo, $saldo, $data) {
            $id_usuario = $_SESSION['id'] ?? null;

            // Sanitize e validação
            $quantidade = is_numeric($quantidade) ? $quantidade : 0;
            $custo = is_numeric($custo) ? $custo : 0;
            $data = $data ?: date('Y-m-d H:i:s');
            try {
                $sql = "INSERT INTO registros (
                            tipo, quantidade, codigo_item, codigo_solicitacao, numero_nota, data_registro, custo, id_usuario
                        ) VALUES (
                            :tipo, :quantidade, :id_produto, :id_solicitacao, :numero_nota, :data_registro, :custo, :id_usuario
                        )";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':quantidade', $quantidade);
                $stmt->bindParam(':id_produto', $id_produto);
                $stmt->bindParam(':id_solicitacao', $id_solicitacao);
                $stmt->bindParam(':numero_nota', $numero_nota);
                $stmt->bindParam(':data_registro', $data);
                $stmt->bindParam(':custo', $custo);
                $stmt->bindParam(':id_usuario', $id_usuario, \PDO::PARAM_INT);

                if ($stmt->execute()) {
                    return true;
                } else {
                    $erro = $stmt->errorInfo();
                    echo "Erro SQL: " . $erro[2];
                    return false;
                }
            } catch (PDOException $e) {
                echo "Exceção: " . $e->getMessage();
                return false;
            }
        }

        
        
        public function getSaldoEstoque($id) {
            $sql = "SELECT saldo FROM estoques WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchColumn(); // retorna o valor da coluna "saldo"
        }

        public function setstatus($id_solicitacao) {
            $status = 'Concluída'; // Definindo o valor do status como uma variável
            $stmt = $this->pdo->prepare("
                UPDATE solicitacoes SET 
                    status = :status
                WHERE codigo_solicitacao = :id_solicitacao
            ");
            
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao, \PDO::PARAM_INT);
        
            return $stmt->execute();     
        }
        
        public function setreceptor($id_solicitacao,$receptor){
            $sql = "UPDATE solicitacoes SET receptor = :receptor WHERE codigo_solicitacao = :id_solicitacao";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id_solicitacao', $id_solicitacao, \PDO::PARAM_INT);
            $stmt->bindParam(':receptor', $receptor);

            

            return $stmt->execute();     

        }


        public function devolucao($id_registro, $quantidadeDevolvida){
            try {
                
                // Passo 1: Consultar o registro para obter a quantidade atual
                $stmt = $this->pdo->prepare("
                    SELECT quantidade 
                    FROM registros 
                    WHERE codigo_registro = :id_registro
                ");
                $stmt->bindParam(':id_registro', $id_registro, \PDO::PARAM_INT);
                $stmt->execute();

                $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($registro) {
                    $quantidadeAtual = $registro['quantidade'];
                    $novaQuantidade = $quantidadeAtual - $quantidadeDevolvida;

                    if ($novaQuantidade < 0) {
                        throw new \Exception("A quantidade não pode ser negativa.");
                    }

                    $stmtUpdate = $this->pdo->prepare("
                        UPDATE registros 
                        SET quantidade = :nova_quantidade 
                        WHERE codigo_registro = :id_registro
                    ");
                    $stmtUpdate->bindParam(':nova_quantidade', $novaQuantidade, \PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':id_registro', $id_registro, \PDO::PARAM_INT);

                    return $stmtUpdate->execute();
                } else {
                    throw new \Exception("Registro não encontrado.");
                }
            } catch (\PDOException $e) {
                // Exibe erro SQL
                echo "Erro no banco de dados: " . $e->getMessage();
                return false;
            } catch (\Exception $e) {
                // Exibe outros erros
                echo "Erro: " . $e->getMessage();
                return false;
            }
        }


        
          
        
    }
?>