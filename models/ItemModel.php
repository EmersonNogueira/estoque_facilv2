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
                        e.saldo, 
                        e.validade
                    FROM 
                        itens i
                    INNER JOIN 
                        estoque e ON i.codigo_item = e.codigo_item  -- INNER JOIN garante apenas itens no estoque
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
                        SUM(e.saldo) AS saldo_total
                    FROM 
                        itens i
                    LEFT JOIN 
                        estoque e ON i.codigo_item = e.codigo_item
                    GROUP BY 
                        i.codigo_item, i.descricao, i.situacao, i.visivel, i.custo_unitario, i.categoria
                    ORDER BY 
                        i.categoria ASC, i.descricao ASC;
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


        public function item_adicionar($item) {
            $sql = "INSERT INTO itens (descricao, situacao, saldo_alocar, custo_unitario, visivel, categoria) 
                    VALUES (:descricao, :situacao, :saldo_alocar, :custo_unitario, :visivel, :categoria)";
        
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindParam(':descricao', $item['descricao'], \PDO::PARAM_STR);
            $stmt->bindParam(':situacao', $item['situacao'], \PDO::PARAM_STR);
            $stmt->bindParam(':saldo_alocar', $item['saldo_alocar'], \PDO::PARAM_INT); // Se for um número inteiro
            $stmt->bindParam(':custo_unitario', $item['custo_unitario'], \PDO::PARAM_STR); // Tratado como string (se for valor em dinheiro)
            $stmt->bindParam(':visivel', $item['visivel'],  \PDO::PARAM_STR);
            $stmt->bindParam(':categoria', $item['categoria'], \PDO::PARAM_STR);
            
        
            return $stmt->execute();
        }
        
        
        
        

    }
?>