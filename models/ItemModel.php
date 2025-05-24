<?php
    namespace models;

    class ItemModel extends Model
    {

        public function item_novo(){
            
        }
        public function itens_locais() {
            try {
                // Captura o parâmetro de busca, se presente
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                // Consulta SQL com JOIN entre itens e estoque
                $sqlStr = "
                    SELECT 
                        i.codigo_item, 
                        i.descricao, 
                        i.situacao, 
                        i.visivel, 
                        i.custo_unitario, 
                        i.categoria, 
                        e.codigo_local, 
                        l.nome_local,  -- Nome do local
                        l.codigo_deposito,  -- Código do depósito (relacionado à tabela depositos)
                        d.nome_deposito,  -- Nome do depósito
                        e.id,
                        e.saldo, 
                        e.validade
                    FROM 
                        itens i
                    INNER JOIN 
                        estoques e ON i.codigo_item = e.codigo_item  -- INNER JOIN garante apenas itens no estoque
                    LEFT JOIN 
                        locais l ON e.codigo_local = l.codigo_local
                    LEFT JOIN 
                        depositos d ON l.codigo_deposito = d.codigo_deposito;


                ";
        
                // Adiciona o filtro de busca, se houver
                if (!empty($search)) {
                    $sqlStr .= " WHERE i.descricao LIKE :search OR i.categoria LIKE :search";
                }
        
                // Adiciona a ordenação
                $sqlStr .= " ORDER BY i.categoria ASC, i.descricao ASC";
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Se houver busca, vincula o parâmetro
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%');
                }
        
                // Executa a consulta
                $sql->execute();
        
                // Retorna os resultados como um array associativo
                return $sql->fetchAll(\PDO::FETCH_ASSOC);
        
            } catch (\PDOException $e) {
                // Registra o erro no log
                error_log("Erro na consulta SQL: " . $e->getMessage());
        
                // Retorna uma mensagem genérica
                return ['erro' => 'Ocorreu um problema ao buscar os itens. Tente novamente mais tarde.'];
            }
        }

        public function itens_saldo() {
            try {
                // Captura o parâmetro de busca, se presente
                $search = isset($_GET['search']) ? $_GET['search'] : '';
        
                // Consulta SQL agrupando por código do item e somando o saldo
                $sqlStr = "
                    SELECT 
                        i.codigo_item, 
                        i.descricao, 
                        i.situacao, 
                        i.visivel, 
                        i.custo_unitario, 
                        i.categoria,
                        i.desc_pregao,
                        i.unidade_medida,
                        i.saldo_alocar, 
                        COALESCE(SUM(e.saldo), 0) AS saldo_total
                    FROM 
                        itens i
                    LEFT JOIN 
                        estoques e ON i.codigo_item = e.codigo_item
                    GROUP BY 
                        i.codigo_item, i.descricao, i.situacao, i.visivel, i.custo_unitario, 
                        i.categoria, i.desc_pregao, i.unidade_medida, i.saldo_alocar
                    ORDER BY 
                        CASE 
                            WHEN i.saldo_alocar > 0 THEN 0 
                            ELSE 1 
                        END, 
                        i.categoria ASC, 
                        i.descricao ASC;

                ";
        
                // Adiciona o filtro de busca, se houver
                if (!empty($search)) {
                    $sqlStr .= " WHERE i.descricao LIKE :search OR i.categoria LIKE :search";
                }
        
                // Adiciona o agrupamento e a ordenação
                $sqlStr .= " GROUP BY i.codigo_item, i.descricao, i.situacao, i.visivel, i.custo_unitario, i.categoria 
                             ORDER BY i.categoria ASC, i.descricao ASC";
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Se houver busca, vincula o parâmetro
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%');
                }
        
                // Executa a consulta
                $sql->execute();
        
                // Retorna os resultados como um array associativo
                return $sql->fetchAll(\PDO::FETCH_ASSOC);
        
            } catch (\PDOException $e) {
                // Registra o erro no log
                error_log("Erro na consulta SQL: " . $e->getMessage());
        
                // Retorna uma mensagem genérica
                return ['erro' => 'Ocorreu um problema ao buscar os itens. Tente novamente mais tarde.'];
            }
        }

        public function atualizar_item($item) {
            $sql = "UPDATE itens 
                    SET descricao = :descricao, 
                        categoria = :categoria, 
                        desc_pregao = :desc_pregao, 
                        unidade_medida = :unidade_medida, 
                        visivel = :visivel
                    WHERE codigo_item = :codigo_item";
        
            $stmt = $this->pdo->prepare($sql);
        
            $stmt->bindParam(':descricao', $item['descricao'], \PDO::PARAM_STR);
            $stmt->bindParam(':categoria', $item['categoria'], \PDO::PARAM_STR);
            $stmt->bindParam(':desc_pregao', $item['pregao'], \PDO::PARAM_STR);
            $stmt->bindParam(':unidade_medida', $item['unidade_medida'], \PDO::PARAM_STR);
            $stmt->bindParam(':visivel', $item['visivel'], \PDO::PARAM_STR);
            $stmt->bindParam(':codigo_item', $item['codigo_item'], \PDO::PARAM_INT);
        
            return $stmt->execute();
        }
        
        


        public function item_adicionar($item) {
            $sql = "INSERT INTO itens (descricao, situacao, saldo_alocar, custo_unitario, visivel, categoria,desc_pregao,unidade_medida) 
                    VALUES (:descricao, :situacao, :saldo_alocar, :custo_unitario, :visivel, :categoria, :desc_pregao, :unidade_medida)";
        
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindParam(':descricao', $item['descricao'], \PDO::PARAM_STR);
            $stmt->bindParam(':situacao', $item['situacao'], \PDO::PARAM_STR);
            $stmt->bindParam(':saldo_alocar', $item['saldo_alocar'], \PDO::PARAM_INT); // Se for um número inteiro
            $stmt->bindParam(':custo_unitario', $item['custo_unitario'], \PDO::PARAM_STR); // Tratado como string (se for valor em dinheiro)
            $stmt->bindParam(':visivel', $item['visivel'],  \PDO::PARAM_STR);
            $stmt->bindParam(':desc_pregao', $item['desc_pregao'],  \PDO::PARAM_STR);
            $stmt->bindParam(':unidade_medida', $item['unidade_medida'],  \PDO::PARAM_STR);

            $stmt->bindParam(':categoria', $item['categoria'], \PDO::PARAM_STR);
            
        
            return $stmt->execute();
        }
        public function verificarEstoque($codigo_item, $codigo_local, $validade) {
            // Criando a base da query

        
            $sql = "SELECT id, saldo FROM estoques WHERE codigo_item = :codigo_item AND codigo_local = :codigo_local";
        
            // Se validade for NULL, a query precisa ser diferente
            if ($validade === null || $validade === "") {
                $sql .= " AND validade IS NULL";
            } else {
                $sql .= " AND validade = :validade";
            }
        
            $sql .= " LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
            $stmt->bindParam(':codigo_local', $codigo_local, \PDO::PARAM_INT);
        
            // Só adiciona validade se não for NULL
            if ($validade !== null) {
                $stmt->bindParam(':validade', $validade, \PDO::PARAM_STR);
            }
        
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
            return $result ? $result : false;

        
        }

        public function estoques($item) {
            try {
                // Verifica se existem IDs no array
                if (empty($item['ids']) || !is_array($item['ids'])) {
                    return ['erro' => 'Nenhum ID fornecido ou formato inválido.'];
                }
        
                // Converte os IDs para inteiros para evitar SQL Injection
                $ids = array_map('intval', $item['ids']);
        
                // Gera placeholders para a query
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
                // Monta a consulta SQL com junção entre estoques, itens, locais e depósitos
                $sqlStr = "
                    SELECT 
                        e.*, 
                        i.descricao, 
                        i.custo_unitario,
                        l.nome_local, 
                        l.codigo_deposito, 
                        d.nome_deposito 
                    FROM 
                        estoques e 
                    INNER JOIN 
                        itens i ON e.codigo_item = i.codigo_item
                    LEFT JOIN 
                        locais l ON e.codigo_local = l.codigo_local
                    LEFT JOIN 
                        depositos d ON l.codigo_deposito = d.codigo_deposito
                    WHERE 
                        e.id IN ($placeholders)
                ";
        
                // Prepara a consulta
                $stmt = $this->pdo->prepare($sqlStr);
        
                // Executa a consulta passando os valores
                $stmt->execute($ids);
        
                // Retorna os resultados
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                // Registra o erro
                error_log("Erro na consulta SQL: " . $e->getMessage());
        
                // Retorna um erro genérico
                return ['erro' => 'Ocorreu um problema ao buscar os estoques. Tente novamente mais tarde.'];
            }
        }
        
        
        
        public function inserirEstoque($codigo_item, $codigo_local, $validade, $saldo) {
            try {
                // Prepara a consulta SQL para inserir na tabela estoque, incluindo o saldo
                $sql = "INSERT INTO estoques (codigo_item, codigo_local, validade, saldo) 
                        VALUES (:codigo_item, :codigo_local, :validade, :saldo)";
                
                $stmt = $this->pdo->prepare($sql);
                
                // Vincula os parâmetros
                $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
                $stmt->bindParam(':codigo_local', $codigo_local, \PDO::PARAM_INT);
                
                // Tratamento do valor de validade (caso seja NULL ou string vazia, passará o tipo correto)
                if ($validade === null || $validade === "") {
                    $stmt->bindValue(':validade', null, \PDO::PARAM_NULL); // Passando explicitamente como NULL
                } else {
                    $stmt->bindParam(':validade', $validade, \PDO::PARAM_STR);
                }
                
                $stmt->bindParam(':saldo', $saldo, \PDO::PARAM_INT);
                
                // Executa a consulta
                if ($stmt->execute()) {
                    return $this->pdo->lastInsertId(); // Retorna o ID inserido
                } else {
                    // Se a inserção falhar, lança exceção com informações do erro
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception("Erro ao executar a consulta SQL: " . implode(", ", $errorInfo));
                }
            } catch (Exception $e) {
                // Captura qualquer exceção e exibe a mensagem de erro
                error_log("Erro: " . $e->getMessage()); // Log do erro
                echo "Erro: " . $e->getMessage();
                return false;
            }
        }

        
        public function setSaldo_alocar($codigo_item, $saldo) {
            try {
                // Prepara a consulta SQL para atualizar o saldo_alocar na tabela itens
                $sql = "UPDATE itens SET saldo_alocar = :saldo WHERE codigo_item = :codigo_item";
                
                $stmt = $this->pdo->prepare($sql);
                
                // Vincula os parâmetros
                $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
                $stmt->bindParam(':saldo', $saldo, \PDO::PARAM_INT);
                
                // Executa a consulta
                if ($stmt->execute()) {
                    return true; // Retorna verdadeiro se a atualização for bem-sucedida
                } else {
                    // Se a atualização falhar, lança exceção com informações do erro
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception("Erro ao atualizar saldo_alocar na tabela itens: " . implode(", ", $errorInfo));
                }
            } catch (Exception $e) {
                // Captura qualquer erro e exibe a mensagem
                echo "Erro: " . $e->getMessage();
                return false;
            }
        }


        public function setSaldo_estoque($id, $saldo) {
            try {
                // Prepara a consulta SQL para atualizar o saldo do estoque
                $sql = "UPDATE estoques SET saldo = :saldo WHERE id = :id";
                
                $stmt = $this->pdo->prepare($sql);
                
                // Vincula os parâmetros
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
                $stmt->bindParam(':saldo', $saldo, \PDO::PARAM_INT);
                
                // Executa a consulta
                if ($stmt->execute()) {
                    return true; // Retorna verdadeiro se a atualização for bem-sucedida
                } else {
                    // Se a atualização falhar, lança exceção com informações do erro
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception("Erro ao executar a atualização SQL: " . implode(", ", $errorInfo));
                }
            } catch (Exception $e) {
                // Captura qualquer exceção e exibe a mensagem de erro
                error_log("Erro: " . $e->getMessage()); // Log do erro
                echo "Erro: " . $e->getMessage();
                return false;

            }
        }

        public function deletarEstoque($id_estoque_origem) {
            try {
                $sql = "DELETE FROM estoques WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $id_estoque_origem, \PDO::PARAM_INT);

                if ($stmt->execute()) {
                    return true;
                } else {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception("Erro ao executar a exclusão SQL: " . implode(", ", $errorInfo));
                }
            } catch (Exception $e) {
                error_log("Erro: " . $e->getMessage());
                echo "Erro: " . $e->getMessage();
                return false;
            }
        }

        
        
        
        


        
        
        
        
        
        
        
        
        
        
        
        

    }
?>